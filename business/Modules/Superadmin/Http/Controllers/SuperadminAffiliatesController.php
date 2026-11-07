<?php

namespace Modules\Superadmin\Http\Controllers;

use DataTables;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Superadmin\Entities\Affiliate;
use Modules\Superadmin\Entities\AffiliateCommission;
use Modules\Superadmin\Entities\AffiliatePayout;
use Modules\Superadmin\Entities\AffiliateReferral;
use Modules\Superadmin\Utils\AffiliateUtil;

/**
 * Superadmin side of the affiliate system: reviewing applications and
 * inspecting an affiliate's performance.
 *
 * The affiliate facing side lives in AffiliateController.
 */
class SuperadminAffiliatesController extends Controller
{
    /**
     * SQL expression producing the affiliate's full name, used so the column
     * stays sortable and searchable server side.
     */
    const FULL_NAME_SQL = "CONCAT(COALESCE(users.surname, ''), ' ', COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, ''))";

    /**
     * @var \Modules\Superadmin\Utils\AffiliateUtil
     */
    protected $affiliateUtil;

    /**
     * @return void
     */
    public function __construct(AffiliateUtil $affiliateUtil)
    {
        $this->affiliateUtil = $affiliateUtil;
    }

    /**
     * Display a listing of the affiliates.
     *
     * @return Response
     */
    public function index()
    {
        if (! auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            //Same precision @num_format, Util::num_f() and the browser use.
            $precision = (int) session('business.currency_precision', 2);

            $affiliates = Affiliate::join('users', 'affiliates.user_id', '=', 'users.id')
                ->select(
                    'affiliates.id',
                    //The affiliate code is deliberately not exposed anywhere.
                    'affiliates.status',
                    'affiliates.created_at',
                    'users.email',
                    DB::raw(self::FULL_NAME_SQL.' as affiliate_name')
                )
                /*
                 * Totals are computed in sql rather than in php so the columns
                 * stay sortable: the datatable orders on the database side, and
                 * a value assembled after the query could not be ordered on.
                 */
                ->addSelect([
                    'to_approve_count' => AffiliateCommission::selectRaw('COUNT(*)')
                        ->whereColumn('affiliate_commissions.affiliate_user_id', 'affiliates.user_id')
                        ->where('affiliate_commissions.status', 'pending'),

                    'paid_amount' => AffiliatePayout::selectRaw('ROUND(COALESCE(SUM(amount), 0), '.$precision.')')
                        ->whereColumn('affiliate_payouts.affiliate_user_id', 'affiliates.user_id')
                        ->where('affiliate_payouts.status', 'paid'),
                ])
                /*
                 * The same balance AffiliateUtil::payableAmount() computes:
                 * approved commission less what has already gone out. Written
                 * out as raw sql because it subtracts one subquery from
                 * another, which addSelect's one-subquery-per-alias form
                 * cannot express.
                 *
                 * Rounded here to the same precision the service rounds to.
                 * Without it this column and the payout page were two different
                 * roundings of one balance, and the list offered a figure the
                 * payout page then refused to accept.
                 */
                ->addSelect(DB::raw(
                    'ROUND(COALESCE((SELECT SUM(commission_amount) FROM affiliate_commissions'
                    ." WHERE affiliate_commissions.affiliate_user_id = affiliates.user_id AND affiliate_commissions.status = 'approved'), 0)"
                    .' - COALESCE((SELECT SUM(amount) FROM affiliate_payouts'
                    ." WHERE affiliate_payouts.affiliate_user_id = affiliates.user_id AND affiliate_payouts.status = 'paid'), 0), "
                    .$precision.') as payable_amount'
                ));

            if (! empty(request()->input('status'))) {
                $affiliates->where('affiliates.status', request()->input('status'));
            }

            return DataTables::of($affiliates)
                ->addColumn('checkbox', function ($row) {
                    /*
                     * Only an affiliate with something payable gets a checkbox,
                     * so the shared select-all handler cannot tick one that
                     * cannot be paid. The amount rides along so the bulk bar can
                     * total the selection without another request.
                     */
                    if ((float) $row->payable_amount <= 0) {
                        return '<span class="text-muted">&mdash;</span>';
                    }

                    return '<input type="checkbox" class="row-select" name="affiliate_ids[]" value="'.$row->id
                        .'" data-payable="'.(float) $row->payable_amount.'">';
                })
                ->addColumn('action', function ($row) {
                    return $this->actionButtons($row);
                })
                ->editColumn('affiliate_name', function ($row) {
                    $name = trim($row->affiliate_name);
                    $name = $name === '' ? $row->email : $name;

                    return e($name).'<br><small class="text-muted">'.e($row->email).'</small>';
                })
                ->filterColumn('affiliate_name', function ($query, $keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->whereRaw(self::FULL_NAME_SQL.' like ?', ["%{$keyword}%"])
                            ->orWhere('users.email', 'like', "%{$keyword}%");
                    });
                })
                ->editColumn('status', function ($row) {
                    return $this->statusLabel($row->status);
                })
                ->editColumn('created_at', '{{@format_datetime($created_at)}}')
                //display_currency is the app's own hook for formatting amounts.
                ->editColumn('payable_amount', function ($row) {
                    return '<span class="display_currency" data-currency_symbol="true" data-use_page_currency="true">'
                        .(float) $row->payable_amount.'</span>';
                })
                ->editColumn('paid_amount', function ($row) {
                    return '<span class="display_currency" data-currency_symbol="true" data-use_page_currency="true">'
                        .(float) $row->paid_amount.'</span>';
                })
                ->removeColumn('id')
                ->removeColumn('email')
                ->rawColumns(['checkbox', 'affiliate_name', 'status', 'created_at', 'payable_amount', 'paid_amount', 'action'])
                ->make(true);
        }

        /*
         * The list is rendered as a card on the affiliate settings page rather
         * than as a page of its own, so a direct visit lands there.
         */
        return redirect()->action([\Modules\Superadmin\Http\Controllers\AffiliateSettingsController::class, 'edit']);
    }

    /**
     * Show an affiliate's details, performance and history.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        if (! auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        $affiliate = Affiliate::with('user')->findOrFail($id);

        $stats = $this->affiliateUtil->forAffiliate($affiliate);

        /*
         * Registrations show up here as soon as a business signs up through the
         * link, rather than only once it buys something: until then the
         * commission table is empty and the referral would be invisible.
         */
        $referrals = AffiliateReferral::where('affiliate_user_id', $affiliate->user_id)
            ->with('business')
            ->orderBy('registered_at', 'desc')
            ->get();

        $commissions = AffiliateCommission::where('affiliate_user_id', $affiliate->user_id)
            ->with(['business', 'package'])
            ->orderBy('created_at', 'desc')
            ->get();

        $payouts = AffiliatePayout::where('affiliate_user_id', $affiliate->user_id)
            ->orderBy('created_at', 'desc')
            ->get();

        $payable_amount = $this->affiliateUtil->payableAmount($affiliate->user_id);
        $minimum_payout = $this->affiliateUtil->minPayout();

        return view('superadmin::affiliate.show')
            ->with(compact('affiliate', 'stats', 'referrals', 'commissions', 'payouts', 'payable_amount', 'minimum_payout'));
    }

    /**
     * Approve, reject or suspend an affiliate.
     *
     * @param  int  $id
     * @return Response
     */
    public function updateStatus(Request $request, $id)
    {
        if (! auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $affiliate = Affiliate::findOrFail($id);

            /*
             * The status is checked by AffiliateUtil::updateAffiliateStatus(), which
             * refuses anything outside assignableAffiliateStatuses(). That guard belongs
             * to the domain rather than the request, so it stays.
             */
            $this->affiliateUtil->updateAffiliateStatus($affiliate, $request->input('status'));

            $output = ['success' => true,
                'msg' => __('superadmin::lang.affiliate_status_updated'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        if ($request->ajax()) {
            return $output;
        }

        return back()->with('status', $output);
    }

    /**
     * Change a commission's status.
     *
     * @param  int  $id
     * @return Response
     */
    public function updateCommissionStatus(Request $request, $id)
    {
        if (! auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $commission = AffiliateCommission::findOrFail($id);

            //AffiliateUtil::updateCommissionStatus() rejects any status it does not own.
            $this->affiliateUtil->updateCommissionStatus($commission, $request->input('status'));

            $output = ['success' => 1,
                'msg' => __('superadmin::lang.commission_status_updated'),
            ];
        } catch (\RuntimeException $e) {
            //Expected refusals, e.g. the commission is locked by a payout.
            $output = ['success' => 0, 'msg' => $e->getMessage()];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => 0, 'msg' => __('messages.something_went_wrong')];
        }

        return back()->with('status', $output);
    }

    /**
     * Pay an affiliate everything they are owed.
     *
     * The single action of the payout process: there is nothing to approve
     * beforehand and nothing to confirm afterwards.
     *
     * @param  int  $id  Affiliate id.
     * @return Response
     */
    public function storePayout(Request $request, $id)
    {
        if (! auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $affiliate = Affiliate::findOrFail($id);

            $this->affiliateUtil->pay(
                $affiliate->user_id,
                //num_uf: the field is written in the business's number format.
                $this->affiliateUtil->num_uf($request->input('amount')),
                $request->input('transaction_id'),
                auth()->user()->id
            );

            $output = ['success' => 1,
                'msg' => __('superadmin::lang.payout_completed'),
            ];
        } catch (\RuntimeException $e) {
            //Nothing payable, or below the configured minimum.
            $output = ['success' => 0, 'msg' => $e->getMessage()];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => 0, 'msg' => __('messages.something_went_wrong')];
        }

        return back()->with('status', $output);
    }

    /**
     * Step one of paying: show what each selected affiliate is owed and let the
     * amounts and transaction ids be edited before anything is written.
     *
     * A GET, taking the ids from the query string: it only reads, so the page
     * stays refreshable and reachable with the back button.
     *
     * @return Response
     */
    public function preparePayout(Request $request)
    {
        if (! auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        $ids = array_filter(array_map('intval', (array) $request->input('affiliate_ids', [])));

        $rows = Affiliate::with('user')
            ->whereIn('id', $ids)
            ->get()
            ->map(function ($affiliate) {
                $affiliate->owed = $this->affiliateUtil->payableAmount($affiliate->user_id);

                return $affiliate;
            })
            //An affiliate with nothing owed has no line to fill in.
            ->filter(function ($affiliate) {
                return $affiliate->owed > 0;
            })
            ->values();

        if ($rows->isEmpty()) {
            return back()->with('status', [
                'success' => 0,
                'msg' => __('superadmin::lang.bulk_payout_none_paid'),
            ]);
        }

        $minimum_payout = $this->affiliateUtil->minPayout();

        return view('superadmin::affiliate.payout')
            ->with(compact('rows', 'minimum_payout'));
    }

    /**
     * Step two: record a payment for each line that has an amount on it.
     *
     * @return Response
     */
    public function storeBulkPayout(Request $request)
    {
        if (! auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $lines = (array) $request->input('payouts', []);

            $paid_count = 0;
            $paid_total = 0;
            $skipped = 0;

            foreach ($lines as $affiliate_id => $line) {
                $amount = $line['amount'] ?? null;

                //A blank line means "not this one", which is not a failure.
                if ($amount === null || $amount === '') {
                    continue;
                }

                $affiliate = Affiliate::find((int) $affiliate_id);

                if (empty($affiliate)) {
                    $skipped++;

                    continue;
                }

                try {
                    $payout = $this->affiliateUtil->pay(
                        $affiliate->user_id,
                        //num_uf: the field is written in the business's number format.
                        $this->affiliateUtil->num_uf($amount),
                        $line['transaction_id'] ?? null,
                        auth()->user()->id
                    );

                    $paid_count++;
                    $paid_total += (float) $payout->amount;
                } catch (\RuntimeException $e) {
                    //Over the balance, under the minimum, or nothing owed.
                    $skipped++;
                } catch (\Exception $e) {
                    \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

                    $skipped++;
                }
            }

            if ($paid_count === 0) {
                $output = ['success' => 0, 'msg' => __('superadmin::lang.bulk_payout_none_paid')];
            } else {
                $msg = __('superadmin::lang.bulk_payout_done', [
                    'count' => $paid_count,
                    'total' => $this->affiliateUtil->formatAmount($paid_total),
                ]);

                if ($skipped > 0) {
                    $msg .= ' '.__('superadmin::lang.bulk_payout_skipped', ['count' => $skipped]);
                }

                $output = ['success' => 1, 'msg' => $msg];
            }
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => 0, 'msg' => __('messages.something_went_wrong')];
        }

        return redirect()
            ->action([\Modules\Superadmin\Http\Controllers\AffiliateSettingsController::class, 'edit'])
            ->with('status', $output);
    }

    /**
     * Coloured label for an affiliate status.
     *
     * @param  string  $status
     * @return string
     */
    protected function statusLabel($status)
    {
        $classes = [
            'approved' => 'bg-light-green',
            'pending' => 'bg-aqua',
            'suspended' => 'bg-yellow',
            'rejected' => 'bg-red',
        ];

        $class = $classes[$status] ?? 'bg-gray';
        $label = Affiliate::statuses()[$status] ?? $status;

        return '<span class="label '.$class.'">'.e($label).'</span>';
    }

    /**
     * Row actions: a view link plus every status change the row still allows,
     * gathered into a single dropdown.
     *
     * A status is never offered when the affiliate already holds it, so the
     * dropdown is left out entirely once nothing is left to change.
     *
     * @param  object  $row
     * @return string
     */
    protected function actionButtons($row)
    {
        $url = action([\Modules\Superadmin\Http\Controllers\SuperadminAffiliatesController::class, 'updateStatus'], [$row->id]);

        $html = '<a href="'.action([\Modules\Superadmin\Http\Controllers\SuperadminAffiliatesController::class, 'show'], [$row->id]).'"
            class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-primary">'.__('messages.view').'</a>';

        $statuses = array_diff(AffiliateUtil::assignableAffiliateStatuses(), [$row->status]);

        if (! empty($statuses)) {
            $items = '';
            foreach ($statuses as $status) {
                $items .= '<li><a href="#" data-href="'.$url.'" data-status="'.$status.'"
                    class="update_affiliate_status">'.e($this->statusActionLabel($status)).'</a></li>';
            }

            $html .= ' <div class="btn-group"><button type="button"
                class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-info tw-w-max dropdown-toggle"
                data-toggle="dropdown" aria-expanded="false">'.__('messages.actions')
                .'<span class="caret"></span><span class="sr-only">Toggle Dropdown</span></button>
                <ul class="dropdown-menu dropdown-menu-left" role="menu">'.$items.'</ul></div>';
        }

        return $html;
    }

    /**
     * Wording of the action that moves an affiliate into a status.
     *
     * "Approve" reads better on a button than the status name "Approved".
     *
     * @param  string  $status
     * @return string
     */
    protected function statusActionLabel($status)
    {
        $labels = [
            'approved' => __('superadmin::lang.approve'),
            'rejected' => __('superadmin::lang.reject'),
            'suspended' => __('superadmin::lang.suspend'),
        ];

        return $labels[$status] ?? $status;
    }

    /**
     * AFF-RATES: save an affiliate's own commission rates. Empty = default.
     *
     * @param  int  $id
     * @return Response
     */
    public function updateRates(Request $request, $id)
    {
        if (! auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        $affiliate = Affiliate::findOrFail($id);

        $clean = function ($v) {
            if ($v === null || trim((string) $v) === '') {
                return null;
            }

            return max(0, min(100, round((float) $v, 2)));
        };

        $affiliate->first_commission_percentage = $clean($request->input('first_commission_percentage'));
        $affiliate->renewal_commission_percentage = $clean($request->input('renewal_commission_percentage'));
        $affiliate->save();

        return back()->with('status', ['success' => 1, 'msg' => __('lang_v1.success')]);
    }

    /**
     * AFF-TEAM: an affiliate's upline and own team amount (empty = default).
     *
     * @param  int  $id
     * @return Response
     */
    public function updateTeam(Request $request, $id)
    {
        if (! auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        $affiliate = Affiliate::findOrFail($id);

        $amount = $request->input('team_commission_amount');
        $affiliate->team_commission_amount = ($amount === null || trim((string) $amount) === '') ? null : max(0, round((float) $amount, 2));
        $affiliate->team_commission_type = $request->input('team_commission_type') === 'percent' ? 'percent' : 'fixed'; //AFF-TEAM2
        if ($affiliate->team_commission_type === 'percent' && $affiliate->team_commission_amount !== null) {
            $affiliate->team_commission_amount = min(100, $affiliate->team_commission_amount);
        }

        $parent = $request->input('parent_affiliate_user_id');

        if ($parent === null || $parent === '') {
            $affiliate->parent_affiliate_user_id = null;
        } else {
            $parent = (int) $parent;
            $below = collect($this->affiliateUtil->teamOf($affiliate))->contains(function ($t) use ($parent) {
                return (int) $t['affiliate']->user_id === $parent;
            });

            if ($parent === (int) $affiliate->user_id || $below) {
                return back()->with('status', ['success' => 0, 'msg' => 'This upline would make a loop in the tree. Choose another affiliate.']);
            }
            if (! Affiliate::where('user_id', $parent)->exists()) {
                return back()->with('status', ['success' => 0, 'msg' => 'Upline affiliate not found.']);
            }
            $affiliate->parent_affiliate_user_id = $parent;
        }

        $affiliate->save();

        return back()->with('status', ['success' => 1, 'msg' => __('lang_v1.success')]);
    }

    /**
     * AFF-TEAM3: put several affiliates under this one at once.
     *
     * @param  int  $id
     * @return Response
     */
    public function addTeamMembers(Request $request, $id)
    {
        if (! auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        $affiliate = Affiliate::findOrFail($id);
        $blocked = array_merge([(int) $affiliate->user_id], $this->affiliateUtil->uplinesOf($affiliate));
        $added = 0;
        $skipped = 0;

        foreach (array_unique(array_map('intval', (array) $request->input('member_user_ids', []))) as $member_id) {
            $member = Affiliate::where('user_id', $member_id)->first();

            if (empty($member) || in_array($member_id, $blocked, true)) {
                $skipped++;

                continue;
            }

            $member->parent_affiliate_user_id = $affiliate->user_id;
            $member->save();
            $added++;
        }

        $msg = $added . ' member(s) added to the team.' . ($skipped ? ' ' . $skipped . ' skipped (would make a loop).' : '');

        return back()->with('status', ['success' => $added > 0 ? 1 : 0, 'msg' => $msg]);
    }

    /**
     * AFF-TEAM3: take one direct member out of this affiliate's team.
     *
     * @param  int  $id
     * @param  int  $member_user_id
     * @return Response
     */
    public function removeTeamMember($id, $member_user_id)
    {
        if (! auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        $affiliate = Affiliate::findOrFail($id);
        $member = Affiliate::where('user_id', (int) $member_user_id)
            ->where('parent_affiliate_user_id', $affiliate->user_id)->first();

        if (! empty($member)) {
            $member->parent_affiliate_user_id = null;
            $member->save();
        }

        return back()->with('status', ['success' => 1, 'msg' => __('lang_v1.success')]);
    }
}
