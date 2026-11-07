<?php

namespace Modules\Backupadv\Http\Controllers;

use App\System;
use Composer\Semver\Comparator;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class InstallController extends Controller
{
    public function __construct()
    {
        $this->module_name = config('Backupadv.name');
        $this->appVersion = config('Backupadv.module_version');
    }
    
    /**
     * Install
     *
     * @return Response
     */
    public function index()
    {
        if (! auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '512M');

        $this->installSettings();

        //Check if installed or not.
        $is_installed = System::getProperty(strtolower($this->module_name) . '_version');
        if (empty($is_installed)) {
            DB::statement('SET default_storage_engine=INNODB;');
            Artisan::call('module:migrate',       ['module' => $this->module_name]);
            System::addProperty(strtolower($this->module_name).'_version', $this->appVersion);
        }

        $output = ['success' => 1,
            'msg' => $this->module_name .' module installed succesfully',
        ];

        return redirect()
            ->action([\App\Http\Controllers\Install\ModulesController::class, 'index'])
            ->with('status', $output);
    }

    /**
     * Initialize all install functions
     */
    private function installSettings()
    {
        config(['app.debug' => true]);
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
    }

    /**
     * Uninstall
     *
     * @return Response
     */
    public function uninstall()
    {
        if (!auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            System::removeProperty(strtolower($this->module_name) . '_version');

            $output = ['success' => true,
                'msg' => __("lang_v1.success")
            ];
        } catch (\Exception $e) {
            $output = ['success' => false,
                'msg' => $e->getMessage()
            ];
        }

        return redirect()->back()->with(['status' => $output]);
    }

     /**
     * update module
     * @return Response
     */
    public function update()
    {
        if (!auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            DB::beginTransaction();
            ini_set('max_execution_time', 0);
            ini_set('memory_limit', '512M');

            $version = System::getProperty(strtolower($this->module_name) . '_version');

            if (Comparator::greaterThan($this->appVersion, $version)) {
                ini_set('max_execution_time', 0);
                ini_set('memory_limit', '512M');
                $this->installSettings();

                DB::statement('SET default_storage_engine=INNODB;');
                Artisan::call('module:migrate', ['module' => $this->module_name]);
                System::setProperty(strtolower($this->module_name) . '_version', $this->appVersion);
            } else {
                abort(404);
            }

            DB::commit();

            $output = ['success' => 1,
                'msg' => $this->module_name . ' module updated Succesfully to version ' . $this->appVersion . ' !!'
            ];

            return redirect()->back()->with(['status' => $output]);
        } catch (Exception $e) {
            DB::rollBack();
            die($e->getMessage());
        }
    }
}
