<?php

namespace Modules\Backupadv\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display dashboard with backup statistics and charts.
     *
     * @return Response
     */
    public function index()
    {
        // Get the business ID associated with the current user
        $business_id = auth()->user()->business_id;

        // Retrieve statistics
        $totalBackups = DB::table('backupadv_logs')->where('business_id', $business_id)->count();
        $successfulBackups = DB::table('backupadv_logs')->where('business_id', $business_id)->where('status', 'success')->count();
        $failedBackups = DB::table('backupadv_logs')->where('business_id', $business_id)->where('status', 'failed')->count();
        $totalSize = DB::table('backupadv_logs')->where('business_id', $business_id)->sum('size_in_mb');

        // Retrieve backup logs for chart data
        $backupLogs = DB::table('backupadv_logs')
            ->where('business_id', $business_id)
            ->orderBy('created_at')
            ->get();

        // Prepare data for charts
        $backupDates = [];
        $backupSizes = [];
        foreach ($backupLogs as $log) {
            $backupDates[] = Carbon::parse($log->created_at)->format('Y-m-d');
            $backupSizes[] = $log->size_in_mb;
        }

        // Pass data to the view
        return view('Backupadv::dashboard', compact('totalBackups', 'successfulBackups', 'failedBackups', 'totalSize', 'backupDates', 'backupSizes'));
    }
}
