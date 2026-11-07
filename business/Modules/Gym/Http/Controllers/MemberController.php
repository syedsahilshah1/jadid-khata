<?php

namespace Modules\Gym\Http\Controllers;

use App\Contact;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Utils\ContactUtil;
use App\Utils\Util;
use App\Utils\ModuleUtil;
use DB;
use Yajra\DataTables\Facades\DataTables;
use Modules\Gym\Entities\GymHealthTracking;
use Modules\Gym\Entities\GymAttendance;
use Modules\Gym\Entities\GymClass;
use Carbon\Carbon;
use App\Business;
use App\Transaction;
use Illuminate\Support\Facades\URL;
use Modules\Gym\Entities\GymMemberDiet;

/**
 * Class MemberController
 * @package Modules\Gym\Http\Controllers
 * 
 * Controller for managing gym members including CRUD operations, health tracking and profiles
 */
class MemberController extends Controller
{
    /**
     * Contact utility instance
     * @var ContactUtil
     */
    protected $contactUtil;

    /**
     * Common utility instance
     * @var Util  
     */
    protected $commonUtil;

    /**
     * Module utility instance
     * @var ModuleUtil
     */
    protected $moduleUtil;

    /**
     * Available gender options
     * @var array
     */
    protected $genders;

    /**
     * Constructor
     * 
     * @param ContactUtil $contactUtil Contact utility instance
     * @param Util $commonUtil Common utility instance
     * @param ModuleUtil $moduleUtil Module utility instance
     */
    public function __construct(
        ContactUtil $contactUtil,
        Util $commonUtil,
        ModuleUtil $moduleUtil
    ) {
        $this->contactUtil = $contactUtil;
        $this->commonUtil = $commonUtil;
        $this->moduleUtil = $moduleUtil;

        $this->genders = [
            'male' => __('gym::lang.male'),
            'female' => __('gym::lang.female'),
            'other' => __('gym::lang.other'),
        ];
    }

    /**
     * Display a listing of gym members
     * 
     * @return Renderable
     */
    public function index()
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'gym_module'))) {
            abort(403, 'Unauthorized action.');
        }

        if (!auth()->user()->can('gym.manage_member')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            // Query to get latest transaction for each member
            $subquery = DB::table('transactions AS t')
                ->where('t.type', 'gym_subscription')
                ->select('t.contact_id', DB::raw('MAX(t.created_at) as latest_created_at'))
                ->groupBy('t.contact_id');

            $query = Contact::leftJoinSub($subquery, 'latest_transactions', function ($join) {
                $join->on('contacts.id', '=', 'latest_transactions.contact_id');
            })
            ->leftJoin('transactions AS t', function ($join) {
                $join->on('contacts.id', '=', 't.contact_id')
                    ->on('t.created_at', '=', 'latest_transactions.latest_created_at');
            })
            ->leftJoin('gym_packages AS gp', 't.gym_package_id', '=', 'gp.id')
            ->where('contacts.business_id', $business_id)
            ->onlyCustomers();

            $query->select([
                'contacts.*',
                't.id as latest_transaction_id',
                't.created_at as latest_created_at', 
                't.gym_package_start_date as gym_package_start_date',
                't.gym_package_end_date as gym_package_end_date',
                't.final_total as latest_transaction_total',
                'gp.name as package_name',
            ]);

            $query->groupBy('contacts.id');
            
            return Datatables::of($query)
                ->editColumn('created_at', '{{@format_date($created_at)}}')
                ->addColumn('address', '{{implode(", ", array_filter([$address_line_1, $address_line_2, $city, $state, $country, $zip_code]))}}')
                ->addColumn('age', function($row) {
                    if ($row->dob) {
                        return Carbon::parse($row->dob)->age . ' ' . __('gym::lang.years');
                    }
                    return '';
                })
                ->addColumn('action', function ($row) {
                    $html = '<a type="button" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-primary" href="' . action([\Modules\Gym\Http\Controllers\MemberController::class, 'add_health'], ['id' => $row->id]) . '">'
                        . __('gym::lang.health') . '</a>';
                    $html .= '<a type="button" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-info" href="' . action([\Modules\Gym\Http\Controllers\MemberController::class, 'member_profile'], ['id' => $row->id]) . '">'
                        . __('gym::lang.profile') . '</a>';
                    $html .= '<a type="button" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-primary" href="' . action([\Modules\Gym\Http\Controllers\MemberController::class, 'edit'], ['member' => $row->id]) . '">'
                        . __('messages.edit') . '</a>';
                    $html .= '<a type="button" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-warning" href="' . action([\Modules\Gym\Http\Controllers\SubscriptionController::class, 'add_subscription'], ['id' => $row->id]) . '">'
                        . __('gym::lang.subscription') . '</a>';
                    $html .= '<a type="button" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-info" href="' . action([\Modules\Gym\Http\Controllers\MemberDietController::class, 'edit'], ['diet' => $row->id]) . '">'
                        . __('gym::lang.diets') . '</a>';
                    return $html;

                })
                ->addColumn('package', function ($row) {
                    // Check if the start date is set
                    if (!$row->gym_package_start_date) {
                        return '';
                    }

                    // Format the start date
                    $startDate = $this->commonUtil->format_date($row->gym_package_start_date);
                    $endDateText = '';
                    $daysLeftText = '';
                    $daysLeftClass = 'bg-info'; // Default class

                    // Check if the end date is set
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
                            <p class="text-muted">' . ($row->package_name) . '</p>
                            <p class="text-muted">' . $startDate . ' - ' . $endDateText . '</p>
                            <span class="label ' . $daysLeftClass . '">' . $daysLeftText . '</span>
                        </div>
                    ';
                })
                ->rawColumns(['in', 'action', 'package'])
                ->make(true);
        }

        return view('gym::member.index');
    }

    /**
     * Show form to create new member
     * 
     * @return Renderable
     */
    public function create()
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'gym_module'))) {
            abort(403, 'Unauthorized action.');
        }

        if (!auth()->user()->can('gym.manage_member')) {
            abort(403, 'Unauthorized action.');
        }

        $genders = $this->genders;
        return view('gym::member.create', compact('genders'));
    }

    /**
     * Store a newly created member
     * 
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'gym_module'))) {
            abort(403, 'Unauthorized action.');
        }

        if (!auth()->user()->can('gym.manage_member')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $input = $request->only([
                'prefix',
                'first_name',
                'middle_name',
                'last_name',
                'mobile',
                'landline',
                'alternate_number',
                'address_line_1',
                'address_line_2',
                'city',
                'state',
                'country',
                'zip_code',
                'email',
                'dob',
                'gym_member_gender',
                'submit_type'
            ]);

            $name_array = [];

            $input['type'] = 'customer';

            if (!empty($input['prefix'])) {
                $name_array[] = $input['prefix'];
            }
            if (!empty($input['first_name'])) {
                $name_array[] = $input['first_name'];
            }
            if (!empty($input['middle_name'])) {
                $name_array[] = $input['middle_name'];
            }
            if (!empty($input['last_name'])) {
                $name_array[] = $input['last_name'];
            }

            $input['name'] = trim(implode(' ', $name_array));

            if (!empty($input['dob'])) {
                $input['dob'] = $this->commonUtil->uf_date($input['dob']);
            }

            $input['business_id'] = $business_id;
            $input['contact_type'] = 'individual';
            $input['created_by'] = $request->session()->get('user.id');

            DB::beginTransaction();

            //Update reference count
            $ref_count = $this->contactUtil->setAndGetReferenceCount('contacts', $input['business_id']);

            if (empty($input['contact_id'])) {
                //Generate reference number
                $input['contact_id'] = $this->contactUtil->generateReferenceNumber('contacts', $ref_count, $input['business_id']);
            }
            
            $profile_photo = $this->contactUtil->uploadFile($request, 'profile_photo', 'gym', 'image');

            if (!empty($profile_photo)) {
                $input['gym_member_profile_photo'] = $profile_photo;
            }
            
            $contact = Contact::create($input);
            
            DB::commit();
            
            $output = [
                'success' => true,
                'msg' => __('contact.added_success'),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        if ($input['submit_type'] == 'save_and_health') {
            return redirect()->action([\Modules\Gym\Http\Controllers\MemberController::class, 'add_health'], ['id' => $contact->id]);
        }

        return redirect()->action([\Modules\Gym\Http\Controllers\MemberController::class, 'index'])
            ->with('status', $output)
            ->withInput();
    }

    /**
     * Show member details
     * 
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('gym::show');
    }

    /**
     * Show form to edit member
     * 
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'gym_module'))) {
            abort(403, 'Unauthorized action.');
        }

        if (!auth()->user()->can('gym.manage_member')) {
            abort(403, 'Unauthorized action.');
        }

        $contact = Contact::findOrFail($id);
        $genders = $this->genders;

        return view('gym::member.edit', compact('contact', 'genders'));
    }

    /**
     * Update member details
     * 
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        try {
            $business_id = $request->session()->get('user.business_id');

            $input = $request->only([
                'prefix',
                'first_name',
                'middle_name',
                'last_name',
                'mobile',
                'landline',
                'alternate_number',
                'address_line_1',
                'address_line_2',
                'city',
                'state',
                'country',
                'zip_code',
                'email',
                'dob',
                'gym_member_gender',
            ]);

            $name_array = [];

            if (!empty($input['prefix'])) {
                $name_array[] = $input['prefix'];
            }
            if (!empty($input['first_name'])) {
                $name_array[] = $input['first_name'];
            }
            if (!empty($input['middle_name'])) {
                $name_array[] = $input['middle_name'];
            }
            if (!empty($input['last_name'])) {
                $name_array[] = $input['last_name'];
            }

            $input['name'] = trim(implode(' ', $name_array));

            if (!empty($input['dob'])) {
                $input['dob'] = $this->commonUtil->uf_date($input['dob']);
            }

            $input['business_id'] = $business_id;
            $input['updated_by'] = $request->session()->get('user.id');

            DB::beginTransaction();

            // Retrieve the existing contact by ID
            $contact = Contact::where('business_id', $business_id)->findOrFail($id);

            // Handle file uploads
            $profile_photo = $this->contactUtil->uploadFile($request, 'profile_photo', 'gym', 'image');
            if (!empty($profile_photo)) {
                $input['gym_member_profile_photo'] = $profile_photo;
            }

            // Update the contact with the new data
            $contact->update($input);

            DB::commit();

            $output = [
                'success' => true,
                'msg' => __('lang_v1.success'),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return redirect()->action([\Modules\Gym\Http\Controllers\MemberController::class, 'index'])
            ->with('status', $output)
            ->withInput();
    }

    /**
     * Remove member
     * 
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        //
    }

    /**
     * Show form to add health tracking data
     * 
     * @param int $id
     * @return Renderable
     */
    public function add_health($id)
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'gym_module'))) {
            abort(403, 'Unauthorized action.');
        }

        if (!auth()->user()->can('gym.manage_health')) {
            abort(403, 'Unauthorized action.');
        }

        $member = Contact::findOrFail($id);
        $health = GymHealthTracking::where('contact_id', $id)->get();
        $attendances = GymAttendance::join('contacts', 'gym_attendances.contact_id', '=', 'contacts.id')
            ->where('gym_attendances.contact_id', $id)
            ->get();

        return view('gym::member.health', compact('member', 'health'));
    }

    /**
     * Store health tracking data
     * 
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function store_health(Request $request, $id)
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'gym_module'))) {
            abort(403, 'Unauthorized action.');
        }

        if (!auth()->user()->can('gym.manage_health')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            GymHealthTracking::create([
                'contact_id' => $id,
                'date' => $this->commonUtil->uf_date($request->input('date')),
                'neck' => $request->input('neck'),
                'left_arm' => $request->input('left_arm'),
                'right_arm' => $request->input('right_arm'),
                'chest' => $request->input('chest'),
                'upper_waist' => $request->input('upper_waist'),
                'lower_waist' => $request->input('lower_waist'),
                'hips' => $request->input('hips'),
                'left_thigh' => $request->input('left_thigh'),
                'right_thigh' => $request->input('right_thigh'),
                'calf' => $request->input('calf'),
                'height' => $request->input('height'),
                'weight' => $request->input('weight'),
                'shoulders' => $request->input('shoulders'),
                'body_fat_percentage' => $request->input('body_fat_percentage'),
                'visceral_fat' => $request->input('visceral_fat'),
                'subcutaneous_fat' => $request->input('subcutaneous_fat'),
                'bmi' => $request->input('bmi'),
                'muscle_mass_percentage' => $request->input('muscle_mass_percentage'),
                'remarks' => $request->input('remarks'),
            ]);  

            $output = [
                'success' => 1,
                'msg' => __('lang_v1.success'),
            ];

            return redirect()->action([\Modules\Gym\Http\Controllers\MemberController::class, 'index'])
                ->with('status', $output);
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            
            $output = [
                'success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];

            return back()->with('status', $output)->withInput();
        }
    }

    /**
     * Show member profile
     * 
     * @param int $id
     * @return Renderable
     */
    public function member_profile($id)
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'gym_module'))) {
            abort(403, 'Unauthorized action.');
        }

        if (!auth()->user()->can('gym.manage_member')) {
            abort(403, 'Unauthorized action.');
        }

        $contact = Contact::findOrFail($id);
        return view('gym::member.profile', compact('contact'));
    }

    /**
     * Generate member ID card
     * 
     * @param int $id
     * @return Renderable
     */
    public function id_card($id)
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'gym_module'))) {
            abort(403, 'Unauthorized action.');
        }

        if (!auth()->user()->can('gym.manage_member')) {
            abort(403, 'Unauthorized action.');
        }

        $business = Business::findOrFail($business_id);
        $contact = Contact::findOrFail($id);
        
        return view('gym::member.id_card', compact('contact', 'business'));
    }

    public function member_scanner(){
        return view('gym::member.scan_qr_code');
    }

    public function get_signed_route(Request $request){

        $id = $request->memberId;

        $signedUrl = URL::signedRoute('show_member_details', ['id' => $id]);

        return response()->json(['signedUrl' => $signedUrl]);
    }

    public function show_member_details($id)
    {
        $id = \Illuminate\Support\Facades\Crypt::decryptString($id);
        
        $contact = Contact::findOrFail($id);

        $business = Business::find($contact->business_id);

        $health = GymHealthTracking::where('contact_id', $id)->get();
        
        $attendances = GymAttendance::where('contact_id', $id)->get();

        $package = Transaction::where('contact_id', $id)
            ->leftJoin('gym_packages', 'transactions.gym_package_id', '=', 'gym_packages.id')
            ->select('transactions.*', 'gym_packages.name as package_name', 'gym_packages.classes as classes')
            ->latest()->first();
        
        $classes_id =  json_decode($package->classes, true) ?? [];

        $classes = GymClass::whereIn('id', $classes_id)->where('start_time', '>=', now())->get();

        $diets = GymMemberDiet::where('contact_id', $id)->first();
        $commonUtil = $this->commonUtil;
        return view('gym::member.show_member_details', compact('contact', 'health', 'attendances', 'package', 'classes', 'diets', 'commonUtil', 'business'));
    }


}
