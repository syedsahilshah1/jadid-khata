<?php

namespace Modules\Gym\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Contact;
use Yajra\DataTables\Facades\DataTables;
use App\Utils\ContactUtil;
use App\Utils\Util;
use App\Utils\moduleUtil;
use Carbon\Carbon;
use Modules\Gym\Entities\GymAttendance;


class AttendanceController extends Controller
{
    protected $contactUtil;
    protected $commonUtil;
    protected $moduleUtil;

    public function __construct(
        ContactUtil $contactUtil,
        Util $commonUtil,
        moduleUtil $moduleUtil,
    ) {
        $this->contactUtil = $contactUtil;
        $this->commonUtil = $commonUtil;
        $this->moduleUtil = $moduleUtil;
    }
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        $business_id = request()->session()->get('user.business_id');

        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'gym_module'))) {
            abort(403, 'Unauthorized action.');
        }

        if (!auth()->user()->can('gym.manage_attendance')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {

            $query =  $query = Contact::where('contacts.business_id', $business_id)->onlyCustomers()
                ->leftJoin('gym_attendances', function ($join) {
                    $join->on('contacts.id', '=', 'gym_attendances.contact_id')
                        ->whereDate('gym_attendances.date', Carbon::today()); // Filter attendance to today's date only
                })
                ->select(['contacts.*', 'gym_attendances.date', 'gym_attendances.in_time', 'gym_attendances.out_time']);

            return Datatables::of($query)
                ->editColumn('created_at', '{{@format_date($created_at)}}')
                ->addColumn('address', '{{implode(", ", array_filter([$address_line_1, $address_line_2, $city, $state, $country, $zip_code]))}}')
                ->addColumn('in', function ($row) {
                    $html = '<a type="button" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline' . (empty($row->in_time) ? ' tw-dw-btn-primary' : ' tw-dw-btn-info') . ' btn-modal-in" href="' . action([\Modules\Gym\Http\Controllers\AttendanceController::class, 'get_in'], ['id' => $row->id]) . '">'
                        . (empty($row->in_time) ? __('gym::lang.in_time') : $this->commonUtil->format_time($row->in_time)) . '</a>';
                    return $html;
                })
                ->addColumn('out', function ($row) {
                    $html = '<a type="button" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline ' . (empty($row->out_time) ? ' tw-dw-btn-primary' : ' tw-dw-btn-info') . ' btn-modal-out" href="' . action([\Modules\Gym\Http\Controllers\AttendanceController::class, 'get_out'], ['id' => $row->id]) . '">'
                        . (empty($row->out_time) ? __('gym::lang.out_time') : $this->commonUtil->format_time($row->out_time)) . '</a>';
                    return $html;
                })
                ->rawColumns(['in', 'out'])

                ->make(true);
        }
        return view('gym::attendance.index');
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
        //
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

    public function get_in($id)
    {
        $business_id = request()->session()->get('user.business_id');

        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'gym_module'))) {
            abort(403, 'Unauthorized action.');
        }

        if (!auth()->user()->can('gym.manage_attendance')) {
            abort(403, 'Unauthorized action.');
        }
        $member = Contact::findOrFail($id);
        
        $attendance = GymAttendance::where('contact_id', $id)->whereDate('date', Carbon::today())->first();

        return view('gym::attendance.in_time', compact('member', 'attendance'));
    }

    public function add_edit_in_time(Request $request)
    {

        $business_id = request()->session()->get('user.business_id');

        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'gym_module'))) {
            abort(403, 'Unauthorized action.');
        }

        if(!auth()->user()->can( 'gym.manage_attendance')){
            abort(403, 'Unauthorized action.');
        }


        try {
            GymAttendance::updateOrCreate(
                [
                    'contact_id' => $request->contact_id, // The condition for finding the existing record
                    'date' => Carbon::today()->toDateString(), // Check for today's date
                ],
                [
                    'in_time' => $this->commonUtil->uf_time($request->in_time), // The fields to update or create
                ]
            );
            return response()->json([
                'success' => 1,
                'msg' => __('lang_v1.success'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ]);
        }
    }

    public function get_out($id)
    {
        $member = Contact::findOrFail($id);

        $attendance = GymAttendance::where('contact_id', $id)->whereDate('date', Carbon::today())->first();

        return view('gym::attendance.out_time', compact('member', 'attendance'));
    }

    public function add_edit_out_time(Request $request)
    {

        $business_id = request()->session()->get('user.business_id');

        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'gym_module'))) {
            abort(403, 'Unauthorized action.');
        }

        if(!auth()->user()->can( 'gym.manage_attendance')){
            abort(403, 'Unauthorized action.');
        }

        
        try {
            GymAttendance::updateOrCreate(
                [
                    'contact_id' => $request->contact_id, // The condition for finding the existing record
                    'date' => Carbon::today()->toDateString(), // Check for today's date
                ],
                [
                    'out_time' => $this->commonUtil->uf_time($request->out_time), // The fields to update or create
                ]
            );
            return response()->json([
                'success' => 1,
                'msg' => __('lang_v1.success'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ]);
        }
    }
}
