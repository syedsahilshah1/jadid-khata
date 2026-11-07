<?php

namespace Modules\Superadmin\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Superadmin\Entities\AffiliateCommission;
use Modules\Superadmin\Entities\AffiliatePayout;
use Modules\Superadmin\Entities\AffiliateReferral;
use Modules\Superadmin\Utils\AffiliateUtil;

/**
 * The affiliate facing side of the system: applying to become an affiliate.
 *
 * The superadmin facing list and approvals live in
 * SuperadminAffiliatesController.
 */
class AffiliateController extends Controller
{
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
     * Show the affiliate's own dashboard.
     *
     * This is where the sidebar entry points. Anyone who is not an approved
     * affiliate is sent to the application form instead, so the single menu
     * item works whatever stage the user is at.
     *
     * @return Response
     */
    public function index()
    {
        if (! $this->affiliateUtil->isEnabled()) {
            abort(404);
        }

        $affiliate = $this->affiliateUtil->forUser(auth()->user()->id);

        if (empty($affiliate) || ! $affiliate->isApproved()) {
            return redirect()->action([\Modules\Superadmin\Http\Controllers\AffiliateController::class, 'create']);
        }

        $stats = $this->affiliateUtil->forAffiliate($affiliate);

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

        return view('superadmin::affiliate.dashboard')
            ->with(compact('affiliate', 'stats', 'referrals', 'commissions', 'payouts'));
    }

    /**
     * Show the become an affiliate form, or the status of an existing
     * application.
     *
     * @return Response
     */
    public function create()
    {
        if (! $this->affiliateUtil->isEnabled()) {
            abort(404);
        }

        $affiliate = $this->affiliateUtil->forUser(auth()->user()->id);

        //An approved affiliate belongs on the dashboard, not the application.
        if (! empty($affiliate) && $affiliate->isApproved()) {
            return redirect()->action([\Modules\Superadmin\Http\Controllers\AffiliateController::class, 'index']);
        }

        $terms = $this->affiliateUtil->terms();

        //Shown on the form so an applicant knows the deal before accepting it.
        $commission_percentage = $this->affiliateUtil->commissionPercentage();
        $commission_recurring = $this->affiliateUtil->isRecurring();
        $cookie_days = $this->affiliateUtil->cookieDays();
        $minimum_payout = $this->affiliateUtil->minPayout();

        return view('superadmin::affiliate.apply')
            ->with(compact('affiliate', 'terms', 'commission_percentage', 'commission_recurring', 'cookie_days', 'minimum_payout'));
    }

    /**
     * Store the affiliate application, leaving it awaiting approval.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        if (! $this->affiliateUtil->isEnabled()) {
            abort(404);
        }

        try {
            $this->affiliateUtil->apply(auth()->user());

            $output = ['success' => 1,
                'msg' => __('superadmin::lang.affiliate_application_submitted'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return redirect()
            ->action([\Modules\Superadmin\Http\Controllers\AffiliateController::class, 'create'])
            ->with('status', $output);
    }

    /**
     * AFF-TEAM5: a team head adds a member by email or affiliate code.
     * Only affiliates not yet in any team; removing is left to the superadmin.
     *
     * @return Response
     */
    public function addTeamMember(Request $request)
    {
        if (! $this->affiliateUtil->isEnabled()) {
            abort(404);
        }

        $head = $this->affiliateUtil->forUser(auth()->user()->id);

        if (empty($head) || ! $head->isApproved() || (float) $head->team_commission_amount <= 0) {
            abort(403, 'Unauthorized action.');
        }

        $key = trim((string) $request->input('member'));
        $member = null;

        if ($key !== '') {
            $member = \Modules\Superadmin\Entities\Affiliate::where('affiliate_code', strtoupper($key))->first();

            if (empty($member)) {
                $user = \App\User::where('email', $key)->first();
                $member = $user ? $this->affiliateUtil->forUser($user->id) : null;
            }
        }

        if (empty($member) || ! $member->isApproved()) {
            $msg = 'No approved affiliate found with this email or code.';
        } elseif ((int) $member->user_id === (int) $head->user_id
            || in_array((int) $member->user_id, $this->affiliateUtil->uplinesOf($head), true)) {
            $msg = 'This affiliate cannot be added to your team.';
        } elseif (! empty($member->parent_affiliate_user_id)) {
            $msg = (int) $member->parent_affiliate_user_id === (int) $head->user_id
                ? 'Already in your team.'
                : 'This affiliate is already in another team. Ask the admin to move them.';
        } else {
            $member->parent_affiliate_user_id = $head->user_id;
            $member->save();

            return back()->with('status', ['success' => 1, 'msg' => 'Added to your team.']);
        }

        return back()->with('status', ['success' => 0, 'msg' => $msg]);
    }
}
