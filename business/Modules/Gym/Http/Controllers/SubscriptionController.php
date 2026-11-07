<?php

namespace Modules\Gym\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Contact;
use App\Utils\Util;
use Carbon\Carbon;
use App\Transaction;
use App\Business;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use App\Utils\NotificationUtil;
use App\Utils\ContactUtil;
use App\Utils\TransactionUtil;
use Modules\Hms\Notifications\CustomerNotification;
use App\Utils\ModuleUtil;
use App\Utils\ProductUtil;
use App\Utils\BusinessUtil;
use Modules\Gym\Entities\GymPackage;
use App\Account;


class SubscriptionController extends Controller
{
    protected $commonUtil;
    protected $notificationUtil;
    protected $contactUtil;
    protected $transactionUtil;
    protected $dummyPaymentLine;
    protected $productUtil;
    protected $businessUtil;
    protected $moduleUtil;

    public function __construct(
        Util $commonUtil,
        NotificationUtil $notificationUtil,
        ContactUtil $contactUtil,
        TransactionUtil $transactionUtil,
        ModuleUtil $moduleUtil,
        ProductUtil $productUtil,
        BusinessUtil $businessUtil,

    ) {
        $this->commonUtil = $commonUtil;
        $this->notificationUtil = $notificationUtil;
        $this->contactUtil = $contactUtil;
        $this->transactionUtil = $transactionUtil;
        $this->moduleUtil = $moduleUtil;
        $this->productUtil = $productUtil;
        $this->businessUtil = $businessUtil;

        $this->dummyPaymentLine = [
            'method' => 'cash',
            'amount' => 0,
            'note' => '',
            'card_transaction_number' => '',
            'card_number' => '',
            'card_type' => '',
            'card_holder_name' => '',
            'card_month' => '',
            'card_year' => '',
            'card_security' => '',
            'cheque_number' => '',
            'bank_account_number' => '',
            'is_return' => 0,
            'transaction_no' => '',
        ];
    }
    /**
     * Display a listing of the resource.
     * @return Renderable
     */

    public function index(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');

        if (request()->ajax()) {
            $payment_types = $this->transactionUtil->payment_types(null, true, $business_id);

            $subscriptions = Transaction::where('transactions.business_id', $business_id)
                ->with(['payment_lines'])
                ->leftjoin('contacts as c', 'transactions.contact_id', '=', 'c.id')
                ->leftJoin('gym_packages AS gp', 'transactions.gym_package_id', '=', 'gp.id')
                ->where('transactions.type', 'gym_subscription')
                ->select('gp.name as p_name', 'transactions.*', 'c.name as c_name', DB::raw('(SELECT SUM(IF(TP.is_return = 1,-1*TP.amount,TP.amount)) FROM transaction_payments AS TP WHERE
                        TP.transaction_id=transactions.id) as total_paid'));

                if($request->customer_id){
                    $subscriptions = $subscriptions->where('c.id',$request->customer_id);
                }

                if($request->package_id){
                    $subscriptions = $subscriptions->where('transactions.gym_package_id',$request->package_id);
                }

                if($request->payment_status){
                    $subscriptions = $subscriptions->where('transactions.payment_status',$request->payment_status);
                }         

            return Datatables::of($subscriptions)

                ->editColumn('created_at', '{{@format_datetime($created_at)}}')

                ->editColumn(
                    'payment_status',
                    function ($row) {
                        $payment_status = Transaction::getPaymentStatus($row);

                        return (string) view('sell.partials.payment_status', ['payment_status' => $payment_status, 'id' => $row->id]);
                    }
                )
                ->addColumn('payment_methods', function ($row) use ($payment_types) {
                    $methods = array_unique($row->payment_lines->pluck('method')->toArray());
                    $count = count($methods);
                    $payment_method = '';
                    if ($count == 1) {
                        $payment_method = $payment_types[$methods[0]] ?? '';
                    } elseif ($count > 1) {
                        $payment_method = __('lang_v1.checkout_multi_pay');
                    }

                    $html = ! empty($payment_method) ? '<span class="payment-method" data-orig-value="' . $payment_method . '" data-status-name="' . $payment_method . '">' . $payment_method . '</span>' : '';

                    return $html;
                })
                ->addColumn('package', function ($row) {
                    if (!$row->gym_package_start_date) {
                        return '';
                    }
                    $startDate = $this->commonUtil->format_date($row->gym_package_start_date);
                    $endDateText = '';
                    $daysLeftText = '';
                    $daysLeftClass = 'bg-info'; // Default class
                
                    if ($row->gym_package_end_date) {
                        $gymEndDate = Carbon::parse($row->gym_package_end_date);
                
                        // Calculate days left
                        $daysLeft = $gymEndDate->isPast() ? 0 : $gymEndDate->diffInDays(Carbon::now());
                        $endDateText = $this->commonUtil->format_date($gymEndDate);
                
                        // Determine days left or expiration status
                        if ($gymEndDate->isPast()) {
                            $daysLeftText = __('gym::lang.expired');
                            $daysLeftClass = 'tw-bg-red-400';
                        } else {
                            $daysLeftText = "$daysLeft " . __('gym::lang.days_remaining');
                        }
                    } else {
                        // If gym_package_end_date is null, show "Lifetime"
                        $endDateText = __('gym::lang.lifetime');
                        $daysLeftText = __('gym::lang.lifetime');
                    }

                      // Check if the package has not started yet
                      if (Carbon::parse($row->gym_package_start_date)->isAfter(Carbon::now())) {
                        $daysLeft = Carbon::parse($row->gym_package_start_date)->diffInDays(Carbon::now());
                        $daysLeftText = __('gym::lang.start_in') . ' ' . $daysLeft . ' ' . __('gym::lang.days');
                        $daysLeftClass = 'bg-info';
                    }
                
                    // Format the HTML output
                    return '
                        <div>
                            <p class="text-muted">' . e($row->p_name) . '</p>
                            <p class="text-muted">' . $startDate . ' - ' . $endDateText . '</p>
                            <span class="label ' . $daysLeftClass . '">' . $daysLeftText . '</span>
                        </div>
                    ';
                })
                ->editColumn(
                    'final_total',
                    '<span class="final-total" data-orig-value="{{$final_total}}">@format_currency($final_total)</span>'
                )
                ->editColumn(
                    'total_paid',
                    '<span class="total-paid" data-orig-value="{{$total_paid}}">@format_currency($total_paid)</span>'
                )
                ->addColumn('total_remaining', function ($row) {
                    $total_remaining = $row->final_total - $row->total_paid;
                    $total_remaining_html = '<span class="payment_due" data-orig-value="' . $total_remaining . '">' . $this->transactionUtil->num_f($total_remaining, true) . '</span>';

                    return $total_remaining_html;
                })
                ->rawColumns(['created_at', 'payment_status', 'payment_methods', 'final_total', 'total_paid', 'total_remaining', 'package'])
                ->make(true);
        }

        $customers = Contact::customersDropdown($business_id, false);

        $packages = GymPackage::get()->pluck('name', 'id');

        return view('gym::subscription.index', compact('customers', 'packages'));
    }

    public function add_subscription($id)
    {
        $business_id = request()->session()->get('user.business_id');

        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'gym_module'))) {
            abort(403, 'Unauthorized action.');
        }

        $contact = Contact::findOrFail($id);

        $customer_due = $this->transactionUtil->getContactDue($contact->id, $business_id);


        $customer_due = $customer_due != 0 ? $this->transactionUtil->num_f($customer_due, true) : '';


        $packages = GymPackage::where('business_id', $business_id)->get();

        $payment_line = $this->dummyPaymentLine;
        $payment_types = $this->productUtil->payment_types(null, true, $business_id);
        $change_return = $this->dummyPaymentLine;
        $accounts = [];
        if ($this->moduleUtil->isModuleEnabled('account')) {
            $accounts = Account::forDropdown($business_id, true, false, true);
        }

        return view('gym::subscription.create', compact('contact', 'customer_due', 'packages', 'payment_line', 'payment_types', 'change_return', 'accounts'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('gym::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {

        // return $request;
        $business_id = request()->session()->get('user.business_id');


        DB::beginTransaction();

        try {

            $business_id = request()->session()->get('user.business_id');

            $busines = Business::findOrFail($business_id);

            $prefix = json_decode($busines->hms_settings)->prefix ?? null;

            $ref_no = null;

            $ref_count = $this->commonUtil->setAndGetReferenceCount("gym", $business_id);
            //Generate reference number
            $ref_no = $this->commonUtil->generateReferenceNumber('gym', $ref_count, $business_id, $prefix);

            // store in transsaction discount_amount
            $transaction = new Transaction();
            $transaction->business_id = $business_id;
            $transaction->type = 'gym_subscription';
            $transaction->status = 'final';
            $transaction->contact_id = $request->contact_id;
            $transaction->created_by = auth()->user()->id;
            $transaction->ref_no = $ref_no;
            $transaction->total_before_tax = (is_null($request->final_total_input) ? 0 : $request->final_total_input) + (is_null($request->total_discount) ? 0 : $request->total_discount);
            $transaction->final_total = is_null($request->final_total_input) ? 0 : $request->final_total_input;

            $transaction->tax_amount = is_null($request->total_discount) ? 0 : $request->total_discount;

            $transaction->discount_amount = is_null($request->total_discount) ? 0 : $request->total_discount;

            $transaction->discount_type = is_null($request->total_discount) ? null : $request->discount_type;
            $transaction->gym_package_start_date = $this->commonUtil->uf_date($request->start_date);
            $transaction->gym_package_end_date = $request->end_date == 'Lifetime' ? null : $this->commonUtil->uf_date($request->end_date);
            $transaction->gym_package_id = $request->package_id;
            $transaction->save();


            //Add change return
            $input = $request->except('_token');
            //Add change return
            $change_return = $this->dummyPaymentLine;
            if (! empty($input['payment']['change_return'])) {
                $change_return = $input['payment']['change_return'];
                unset($input['payment']['change_return']);
            }

            $change_return['amount'] = $input['change_return'] ?? 0;
            $change_return['is_return'] = 1;

            $input['payment'][] = $change_return;


            if (! empty($input['payment'])) {
                $this->transactionUtil->createOrUpdatePaymentLines($transaction, $input['payment']);
            }

            $this->transactionUtil->updatePaymentStatus($transaction->id, $transaction->final_total);


            DB::commit();

            $output = [
                'success' => 1,
                'msg' => __('lang_v1.success'),
            ];

            return redirect()->action(
                [\Modules\Gym\Http\Controllers\MemberController::class, 'index']
            )
                ->with('status', $output);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];

            return back()->with('status', $output)->withInput();
        }
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('gym::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('gym::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        //
    }

    public function get_end_date(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');

        $start_date = $this->commonUtil->uf_date($request->start_date);

        $package = GymPackage::where('business_id', $business_id)->findOrFail($request->id);

        $end_date = $this->exp_date($package->duration, $start_date);

        return response()->json([
            'status' => true,
            'package' => $package,
            'end_date' => $end_date,
        ]);
    }

    public function exp_date($duration, $start_date)
    {
        $start_date =  Carbon::parse($start_date);
        $duration_key = $duration; // The selected duration key (e.g., 'monthly')

        // Calculate the end_date based on the duration
        switch ($duration_key) {
            case 'monthly':
                $end_date = $start_date->addMonth();
                break;
            case 'quarterly':
                $end_date = $start_date->addMonths(3);
                break;
            case 'half-yearly':
                $end_date = $start_date->addMonths(6);
                break;
            case 'yearly':
                $end_date = $start_date->addYear();
                break;
            case 'lifetime':
                $end_date = null; // Lifetime means no expiration
                break;
            default:
                throw new \Exception('Invalid duration key');
        }

        if ($end_date === null) {
            return 'Lifetime';
        }

        // Convert the end_date to a string in the required format (Y-m-d)
        $end_date_string = $end_date->toDateString();  // Y-m-d format

        return $this->commonUtil->format_date($end_date_string);
    }
}
