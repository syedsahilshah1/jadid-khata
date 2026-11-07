<?php

namespace Modules\FieldForce\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\User;
use App\Utils\ModuleUtil;
use Yajra\DataTables\Facades\DataTables;
use DB;
use App\Media;
use Modules\FieldForce\Entities\FieldForce;

class FieldForceDashboardController extends Controller
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
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $business_id = request()->session()->get('user.business_id');

        if (!auth()->user()->can('visit.view_own') && !auth()->user()->can('visit.view_all')) {
            abort(403, 'Unauthorized action.');
        }

        $my_visit_status = $this->getMyVisitStatus();

        $my_visits = $this->getMyVisits();

        $is_admin = $this->moduleUtil->is_admin(auth()->user());

        $all_visits = null;
        $today_visits = null;
        if ($is_admin) {
            $all_visits = $this->getAllVisitStatus();
            $today_visits = $this->getTodayVisitStatus();
        }

        return view('fieldforce::field_force_dashboard.index')
            ->with(compact('is_admin', 'all_visits', 'today_visits', 'my_visits', 'my_visit_status'));
    }

    /**
     * Get the datatable of visits by users
     */
    public function visitByUsers()
    {

        if (request()->ajax()) {
            $is_admin = $this->moduleUtil->is_admin(auth()->user());

            if (!$is_admin) {
                abort(403, 'Unauthorized action.');
            }

            $business_id = request()->session()->get('user.business_id');

            $today = \Carbon::now()->format('Y-m-d');
            $yesterday = \Carbon::yesterday()->format('Y-m-d');
            $first_day_of_month = \Carbon::now()->startOfMonth()->format('Y-m-d');

            $query = FieldForce::where('field_forces.business_id', $business_id)
                ->join('users as u', 'field_forces.assigned_to', '=', 'u.id')
                ->select(
                    'u.surname',
                    'u.first_name',
                    'u.last_name',
                    DB::raw("SUM(IF(DATE(visited_on)='{$today}' AND contact_id IS NOT NULL, 1, 0)) as contact_visits_today"),
                    DB::raw("SUM(IF(DATE(visited_on)='{$today}' AND contact_id IS NULL, 1, 0)) as others_visits_today"),
                    DB::raw("SUM(IF(DATE(visited_on)='{$yesterday}' AND contact_id IS NOT NULL, 1, 0)) as contact_visits_yesterday"),
                    DB::raw("SUM(IF(DATE(visited_on)='{$yesterday}' AND contact_id IS NULL, 1, 0)) as others_visits_yesterday"),
                    DB::raw("SUM(IF(DATE(visited_on)>='{$first_day_of_month}' AND contact_id IS NOT NULL, 1, 0)) as contact_visits_this_month"),
                    DB::raw("SUM(IF(DATE(visited_on)>='{$first_day_of_month}' AND contact_id IS NULL, 1, 0)) as others_visits_this_month"),
                    DB::raw("SUM(IF(visited_on IS NOT NULL, 1, 0)) as all_visits"),
                )->groupBy('u.id');

            return Datatables::of($query)
                ->editColumn('name', function ($row) {
                    return $row->surname . ' ' . $row->first_name . ' ' . $row->last_name;
                })
                ->make(true);
        }
    }

    /**
     * Function to fetch visits statistic of the logged in user
     */
    private function getMyVisits()
    {
        $business_id = request()->session()->get('user.business_id');

        $today = \Carbon::now()->format('Y-m-d');
        $yesterday = \Carbon::yesterday()->format('Y-m-d');
        $first_day_of_month = \Carbon::now()->startOfMonth()->format('Y-m-d');

        $my_visits = FieldForce::where('business_id', $business_id)
            ->where('assigned_to', auth()->user()->id)
            ->whereDate('visited_on', '>=',  $first_day_of_month)
            ->select(
                DB::raw("SUM(IF(DATE(visited_on)='{$today}' AND contact_id IS NOT NULL, 1, 0)) as contact_visits_today"),
                DB::raw("SUM(IF(DATE(visited_on)='{$today}' AND contact_id IS NULL, 1, 0)) as others_visits_today"),
                DB::raw("SUM(IF(DATE(visited_on)='{$yesterday}' AND contact_id IS NOT NULL, 1, 0)) as contact_visits_yesterday"),
                DB::raw("SUM(IF(DATE(visited_on)='{$yesterday}' AND contact_id IS NULL, 1, 0)) as others_visits_yesterday"),
                DB::raw("SUM(IF(contact_id IS NOT NULL, 1, 0)) as contact_visits_this_month"),
                DB::raw("SUM(IF(contact_id IS NULL, 1, 0)) as others_visits_this_month"),
            )->first();

        return $my_visits;
    }

    /**
     * Function to fetch visits statuses of the logged in user
     */
    private function getMyVisitStatus()
    {
        $business_id = request()->session()->get('user.business_id');

        $my_visit_status = FieldForce::where('business_id', $business_id)
            ->where('assigned_to', auth()->user()->id)
            ->select(
                DB::raw("SUM(status='assigned') as assigned"),
                DB::raw("SUM(status='met_contact') as met_contact"),
                DB::raw("SUM(status='did_not_meet_contact') as did_not_meet_contact")
            )->first();

        return $my_visit_status;
    }

    /**
     * Function to fetch all visits statistic
     */
    private function getAllVisitStatus()
    {
        $business_id = request()->session()->get('user.business_id');

        $all_visit_status = FieldForce::where('business_id', $business_id)
            ->select(
                DB::raw("SUM(status='assigned') as assigned"),
                DB::raw("SUM(status='met_contact') as met_contact"),
                DB::raw("SUM(status='did_not_meet_contact') as did_not_meet_contact"),
                DB::raw('COUNT(id) as total_visits')
            )->first();

        return $all_visit_status;
    }

    /**
     * Function to fetch all visits of today statistic
     */
    private function getTodayVisitStatus()
    {
        $business_id = request()->session()->get('user.business_id');
        $today = \Carbon::now()->format('Y-m-d');

        $today_visit_status = FieldForce::where('business_id', $business_id)
            ->whereDate('visited_on', '=',  $today)
            ->select(
                DB::raw("SUM(status='assigned') as assigned"),
                DB::raw("SUM(status='met_contact') as met_contact"),
                DB::raw("SUM(status='did_not_meet_contact') as did_not_meet_contact"),
                DB::raw('COUNT(id) as total_visits')
            )->first();

        return $today_visit_status;
    }
}
