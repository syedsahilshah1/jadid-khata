<?php

/**
 * Restore backups Module
 * * This module helps restore already created backups from ultimate pos.
 * * Module: Restore
 * Author: [Telegram @omgklrahul]
 * Version: 1.0.0
 * * This is a complete free module for non commercial use.
 * * @package Modules\Restore
 */

namespace Modules\Restore\Http\Controllers;

use App\System;
use Composer\Semver\Comparator;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InstallController extends Controller
{
    protected $module_name = 'restore';
    protected $module_display_name = 'Restore Module';
    protected $appVersion = '1.0.2';

    public function index()
    {
        if (!auth()->user()->can('superadmin')) abort(403, 'Unauthorized action.');

        $is_installed = System::getProperty($this->module_name . '_version');

        if (empty($is_installed)) {
            return view('restore::install', [
                'action_url' => route('restore.install'),
                'module_display_name' => $this->module_display_name,
                'appVersion' => $this->appVersion
            ]);
        } elseif (Comparator::lessThan($is_installed, $this->appVersion)) {
            return $this->update();
        } else {
            return redirect()->back()->with('status', ['success' => true, 'msg' => 'Module is up to date.']);
        }
    }

    public function install(Request $request)
    {
        if (!auth()->user()->can('superadmin')) abort(403, 'Unauthorized action.');

        try {
            DB::beginTransaction();
            Artisan::call('module:migrate', ['module' => $this->module_name, '--force' => true]);
            Artisan::call('module:publish', ['module' => $this->module_name]);
            System::addProperty($this->module_name . '_version', $this->appVersion);
            DB::commit();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function uninstall()
    {
        if (!auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            // Remove the module version property
            System::removeProperty($this->module_name . '_version');

            // Clear the cache
            Artisan::call('cache:clear');

            // Prepare success response
            $output = ['success' => true, 'msg' => __('lang_v1.success')];
        } catch (\Exception $e) {
            // Log the error and prepare failure response
            \Log::error('Uninstall failed: ' . $e->getMessage());
            $output = ['success' => false, 'msg' => $e->getMessage()];
        }

        // Redirect back with status
        return redirect()->back()->with(['status' => $output]);
    }

    /**
     * The update action simply triggers the update logic within index().
     */
    public function update()
    {
        try {
            Artisan::call('module:migrate', ['module' => $this->module_name, '--force' => true]);
            System::setProperty($this->module_name . '_version', $this->appVersion);
            return redirect()->back()->with('status', ['success' => true, 'msg' => 'Module updated successfully.']);
        } catch (\Exception $e) {
            return redirect()->back()->with('status', ['success' => false, 'msg' => $e->getMessage()]);
        }
    }
}
