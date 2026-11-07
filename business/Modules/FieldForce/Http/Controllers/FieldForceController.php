<?php

namespace Modules\FieldForce\Http\Controllers;
use App\Contact;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\User;
use App\Utils\ModuleUtil;
use Yajra\DataTables\Facades\DataTables;
use DB;
use App\Media;
use Modules\FieldForce\Entities\FieldForce;

class FieldForceController extends Controller
{
    /**
     * All Utils instance.
     *
     */
    protected $moduleUtil;
    /**
     * Constructor
     *
     * @param CommonUtil
     * @return void
     */
    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;

        $this->visit_statuses = [
            'assigned' => ['label' => __('fieldforce::lang.assigned'), 'class' => 'bg-yellow'],
            'finished' => ['label' => __('fieldforce::lang.finished'), 'class' => 'bg-green'],
            'met_contact' => ['label' => __('fieldforce::lang.met_contact'), 'class' => 'bg-green'],
            'did_not_meet_contact' => ['label' => __('fieldforce::lang.did_not_meet_contact'), 'class' => 'bg-red'],
        ];
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $business_id = request()->session()->get('user.business_id');

        if (!auth()->user()->can('visit.view_own') && !auth()->user()->can('visit.view_all') && !auth()->user()->can('visit.create')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $visits = FieldForce::where('field_forces.business_id', $business_id)
                ->leftjoin('contacts as c', 'field_forces.contact_id', '=', 'c.id')
                ->leftjoin('users as U', 'field_forces.assigned_to', '=', 'U.id')
                ->select(
                    'field_forces.*',
                    'c.name as contact_name',
                    DB::raw("CONCAT(COALESCE(U.surname, ''), ' ', COALESCE(U.first_name, ''), ' ', COALESCE(U.last_name, '')) as assigned_user"),
                    'c.supplier_business_name',
                    'c.city',
                    'c.state',
                    'c.country',
                    'c.address_line_1',
                    'c.address_line_2',
                    'c.zip_code'
                );

            if (!auth()->user()->can('visit.view_all') && auth()->user()->can('visit.view_own')) {
                $visits->where('field_forces.assigned_to', request()->session()->get('user.id'));
            }

            if (!empty(request()->input('contact_id'))) {
                $visits->where('field_forces.contact_id', request()->input('contact_id'));
            }

            if (!empty(request()->input('assigned_to'))) {
                $visits->where('field_forces.assigned_to', request()->input('assigned_to'));
            }

            if (!empty(request()->input('status'))) {
                $visits->where('field_forces.status', request()->input('status'));
            }

            if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $visits->where(function ($query) {
                    $start = request()->start_date;
                    $end =  request()->end_date;

                    $query->whereRaw('(DATE(field_forces.visit_on) >= ? AND DATE(field_forces.visit_on) <= ?) OR 
                        (DATE(field_forces.visited_on) >= ? AND DATE(field_forces.visited_on) <= ?)
                        ', [$start, $end, $start, $end]);
                });
            }

            return Datatables::of($visits)
                ->editColumn('visit_on', '{{@format_datetime($visit_on)}}')
                ->editColumn('visited_on', function($visit) {
                    if (empty($visit->visited_on)) {
                        return "Not Visited Yet";
                    } else {
                        return \Carbon::parse($visit->visited_on)->format('Y-m-d H:i:s');
                    }
                })
                
                ->rawColumns(['visited_on'])
                ->addColumn('action', function ($row) {
                    if (auth()->user()->can('visit.update') || auth()->user()->can('visit.view_own') || auth()->user()->can('visit.view_all')) {
                        $html = '<button type="button" class="btn btn-primary btn-xs btn-modal" data-container="#visit_modal" data-href="' . action('\Modules\FieldForce\Http\Controllers\FieldForceController@edit', [$row->id]) . '" ><i class="fas fa-edit"></i> ' . __('messages.edit') . '</button>';
                    }

                    $html .= ' <button type="button" class="btn btn-info btn-xs btn-modal" data-container=".view_modal" data-href="' . action('\Modules\FieldForce\Http\Controllers\FieldForceController@show', [$row->id]) . '" ><i class="fas fa-eye"></i> ' . __('messages.view') . '</button>';

                    if (auth()->user()->can('visit.delete')) {
                        $html .= ' <a class="btn btn-danger btn-xs delete_visit" href="' . action('\Modules\FieldForce\Http\Controllers\FieldForceController@destroy', [$row->id]) . '" ><i class="fas fa-trash"></i> ' . __('messages.delete') . '</a>';
                    }

                    if (auth()->user()->id == $row->assigned_to) {
                        $html .= '<br><a class="btn btn-warning btn-xs mt-5 btn-modal" data-container="#update_status_modal" data-href="' . action('\Modules\FieldForce\Http\Controllers\FieldForceController@updateVisitStatus', [$row->id]) . '" ><i class="fas fa-edit"></i> ' . __('lang_v1.update_status') . '</a>';
                    }

                    return $html;
                })
                ->addColumn('visited_address', function ($row) {
                    if (!empty($row->visited_address)) {
                        return $row->visited_address;
                    }

                    if (empty($row->contact_id)) {
                        return $row->visit_address;
                    }

                    $address_array = [];
                    if (!empty($row->address_line_1)) {
                        $address_array[] = $row->address_line_1;
                    }
                    if (!empty($row->address_line_2)) {
                        $address_array[] = $row->address_line_2;
                    }
                    if (!empty($row->city)) {
                        $address_array[] = $row->city;
                    }
                    if (!empty($row->state)) {
                        $address_array[] = $row->state;
                    }
                    if (!empty($row->country)) {
                        $address_array[] = $row->country;
                    }
                    if (!empty($row->zip_code)) {
                        $address_array[] = $row->zip_code;
                    }

                    return implode(', ', $address_array);
                })
                ->editColumn('status', function ($row) {
                    $status = $row->status;
                    if (!empty($status) && isset($this->visit_statuses[$status])) {
                        return '<span class="label ' . $this->visit_statuses[$status]['class'] . '">' . $this->visit_statuses[$status]['label'] . '</span>';
                    }
                    return '<span class="label bg-yellow">' . __('fieldforce::lang.assigned') . '</span>';
                })
                ->rawColumns(['action', 'status', 'contact_name'])
                ->filterColumn('assigned_user', function ($query, $keyword) {
                    $query->whereRaw("CONCAT(COALESCE(U.surname, ''), ' ', COALESCE(U.first_name, ''), ' ', COALESCE(U.last_name, '')) like ?", ["%{$keyword}%"]);
                })
                ->editColumn('contact_name', function ($row) {
                    $html = '';
                    if (!empty($row->contact_id)) {
                        $name = !empty($row->supplier_business_name) ? $row->supplier_business_name . ', ' . $row->contact_name : $row->contact_name;
                        $html = '<small><i class="fas fa-user"></i></small> ' . $name;
                    } else {
                        $html = $row->visit_to;
                    }

                    return $html;
                })
                ->make(true);
        }

        $visit_statuses = $this->visit_statuses;
        $users = User::forDropdown($business_id, false);

        // Set default date from get parameter
        
        $default_start_date = request()->input('start_date', null);
        $default_end_date = request()->input('end_date', null);

        return view('fieldforce::field_force.index')
                ->with(compact('visit_statuses', 'users', 'default_start_date', 'default_end_date'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!auth()->user()->can('visit.create')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $contacts = Contact::all();
            $users = User::forDropdown($business_id, false);

            return view('fieldforce::field_force.create')
    ->with([
        'users' => $users,
        'contacts' => $contacts,
    ]);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
   

    /**
     * Display the specified resource.
     *
     * @param  \App\FieldForce  $fieldForce
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (!auth()->user()->can('visit.view_own') && !auth()->user()->can('visit.view_all')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');

            $visit = FieldForce::where('business_id', $business_id)
                ->with(['contact', 'user', 'media'])
                ->find($id);

            $visit_statuses = $this->visit_statuses;

            return view('fieldforce::field_force.view')->with(compact('visit', 'visit_statuses'));
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\FieldForce  $fieldForce
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!auth()->user()->can('visit.update') && !auth()->user()->can('visit.view_own') && !auth()->user()->can('visit.view_all')) {
            abort(403, 'Unauthorized action.');
        }
        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');

            $visit = FieldForce::where('business_id', $business_id)
                ->with(['contact'])
                ->find($id);

            $users = User::forDropdown($business_id, false);

            return view('fieldforce::field_force.edit')->with(compact('visit', 'users'));
        }
    }

    public function updateVisitStatus($id)
    {
        if (!auth()->user()->can('visit.update') && !auth()->user()->can('visit.view_own') && !auth()->user()->can('visit.view_all')) {
            abort(403, 'Unauthorized action.');
        }
        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');

            $visit = FieldForce::where('business_id', $business_id)
                ->find($id);

            $visit_statuses = $this->visit_statuses;

            return view('fieldforce::field_force.update_status')->with(compact('visit', 'visit_statuses'));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\FieldForce  $fieldForce
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('visit.update') && !auth()->user()->can('visit.view_own') && !auth()->user()->can('visit.view_all')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');

        try {
            $input = $request->only('contact_id', 'visit_on', 'assigned_to', 'visit_for');
            $input['visit_on'] = $this->moduleUtil->uf_date($input['visit_on'], true);

            if (empty($input['contact_id'])) {
                $input['visit_to'] = $request->input('visit_to');
                $input['visit_address'] = $request->input('visit_address');
            } else {
                $input['visit_to'] = null;
                $input['visit_address'] = null;
            }

            $visit = FieldForce::find($id);
            $visit->update($input);
            $visit->save();

            $output = [
                'success' => true,
                'msg' => __('lang_v1.success')
            ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong')
            ];
        }

        return $output;
    }


    public function store(Request $request)
{
    // if (!auth()->user()->can('visit.create')) {
    //     abort(403, 'Unauthorized action.');
    // }

    $business_id = $request->session()->get('user.business_id');

    try {
        $input = $request->only('contact_id', 'visit_on', 'assigned_to', 'visit_for');
        $input['visit_on'] = $this->moduleUtil->uf_date($input['visit_on'], true);

        if (empty($input['contact_id'])) {
            $input['visit_to'] = $request->input('visit_to');
            $input['visit_address'] = $request->input('visit_address');
        } else {
            $input['visit_to'] = null;
            $input['visit_address'] = null;
        }

        $input['business_id'] = $business_id;
        $input['status'] = 'assigned';

        // Create a new visit record
        $visit = FieldForce::create($input);
        

        $output = [
            'success' => true,
            'msg' => __('lang_v1.success')
        ];
    } catch (\Exception $e) {
        \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());

        $output = [
            'success' => false,
            'msg' => __('messages.something_went_wrong')
        ];
    }

    return $output;
}

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\FieldForce  $fieldForce
     * @return \Illuminate\Http\Response
     */
    public function postUpdateVisitStatus(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');

        try {
            $id = $request->input('visit_id');

            $input = $request->only('status', 'meet_with', 'meet_with2', 'meet_with3', 'meet_with_mobileno', 'meet_with_mobileno2', 'meet_with_mobileno3', 'meet_with_designation', 'meet_with_designation2', 'meet_with_designation3', 'visited_address_latitude', 'visited_address_longitude', 'visited_address', 'comments');
            
            $visit = FieldForce::find($id);

            if (auth()->user()->id != $visit->assigned_to) {
                abort(403, 'Unauthorized action.');
            }

            if (empty($visit->visited_on)) {
                $visit->visited_on = \Carbon::now()->format('Y-m-d H:i:s');
            }

            if (!empty($input['status'])) {
                $visit->status = $input['status'];
            }

            if (!empty($input['visited_address_latitude'])) {
                $visit->visited_address_latitude = $input['visited_address_latitude'];
            }

            if (!empty($input['visited_address_longitude'])) {
                $visit->visited_address_longitude = $input['visited_address_longitude'];
            }

            if (!empty($input['visited_address'])) {
                $visit->visited_address = $input['visited_address'];
            }

            if (!empty($input['comments'])) {
                $visit->comments = $input['comments'];
            }

            if (!empty($request->input('reason_to_not_meet_contact')) && $visit->status == 'did_not_meet_contact') {
                $visit->reason_to_not_meet_contact = $request->input('reason_to_not_meet_contact');
            }

            if (!empty($request->input('meet_with'))) {
                $visit->meet_with = $request->input('meet_with');
            }

            if (!empty($request->input('meet_with2'))) {
                $visit->meet_with2 = $request->input('meet_with2');
            }

            if (!empty($request->input('meet_with3'))) {
                $visit->meet_with3 = $request->input('meet_with3');
            }

            if (!empty($request->input('meet_with_mobileno'))) {
                $visit->meet_with_mobileno = $request->input('meet_with_mobileno');
            }

            if (!empty($request->input('meet_with_mobileno2'))) {
                $visit->meet_with_mobileno2 = $request->input('meet_with_mobileno2');
            }

            if (!empty($request->input('meet_with_mobileno3'))) {
                $visit->meet_with_mobileno3 = $request->input('meet_with_mobileno3');
            }


            if (!empty($request->input('meet_with_designation'))) {
                $visit->meet_with_designation = $request->input('meet_with_designation');
            }

            if (!empty($request->input('meet_with_designation2'))) {
                $visit->meet_with_designation2 = $request->input('meet_with_designation2');
            }

            if (!empty($request->input('meet_with_designation3'))) {
                $visit->meet_with_designation3 = $request->input('meet_with_designation3');
            }

            $visit->save();

            Media::uploadMedia($business_id, $visit, $request, 'photo', true);

            $output = [
                'success' => true,
                'msg' => __('lang_v1.success')
            ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong')
            ];
        }

        return redirect()->action('\Modules\FieldForce\Http\Controllers\FieldForceController@index')
            ->with(['status' => $output]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\FieldForce  $fieldForce
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!auth()->user()->can('visit.delete')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $business_id = request()->user()->business_id;

                $visit = FieldForce::where('business_id', $business_id)
                    ->where('id', $id)
                    ->delete();

                $output = [
                    'success' => true,
                    'msg' => __("brand.deleted_success")
                ];
            } catch (\Exception $e) {
                \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());

                $output = [
                    'success' => false,
                    'msg' => __("messages.something_went_wrong")
                ];
            }

            return $output;
        }
    }
}
