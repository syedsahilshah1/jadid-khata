<?php

namespace Modules\Installment\Http\Controllers;


use App\System; 
use Composer\Semver\Comparator;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class InstallController extends Controller
      {
    /**
     * Display a listing of the resource.
     * @return Response
     */

    public function __construct()
    {
        $this->module_name = 'installment';
        $this->appVersion =config('installment.module_version');
    }

    public function index()
    {
        if (!auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '512M');

        $this->installSettings();

        //Check if installed or not.
        $is_installed = System::getProperty($this->module_name . '_version');


        if (!empty($is_installed)) {
            abort(404);
        }

        $action_url = action('\Modules\Installment\Http\Controllers\InstallController@install');

        return view('install.install-module')
            ->with(compact('action_url'));
    }

    private function installSettings()
    {
        config(['app.debug' => true]);
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
    }

    /**
     * Installing installment Module
     */
    public function install()
    {























        $is_installed = System::getProperty($this->module_name . '_version');
        if (!empty($is_installed)) {
            abort(404);
        }

        Artisan::call('module:migrate-reset', ['module' => "installment"]);
        Artisan::call('module:migrate', ['module' => "installment"]);

        DB::statement('SET default_storage_engine=INNODB;');


        System::addProperty($this->module_name . '_version', $this->appVersion);

        DB::commit();

        $output = ['success' => 1,
            'msg' => 'installment module installed succesfully'
        ];
        /*} catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => $e->getMessage()
            ];
        }*/

        return redirect()
            ->action('\App\Http\Controllers\Install\ModulesController@index')
            ->with('status', $output);
    }

    /**
     * Uninstall
     * @return Response
     */
    public function uninstall()
    {
        if (!auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            System::removeProperty($this->module_name . '_version');

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
        //Check if installment_version is same as appVersion then 404
        //If appVersion > installment_version - run update script.
        //Else there is some problem.
        if (!auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            DB::beginTransaction();
            ini_set('max_execution_time', 0);
            ini_set('memory_limit', '512M');

            $installment_version = System::getProperty($this->module_name . '_version');

            if (Comparator::greaterThan($this->appVersion, $installment_version)) {
                ini_set('max_execution_time', 0);
                ini_set('memory_limit', '512M');
                $this->installSettings();

                DB::statement('SET default_storage_engine=INNODB;');
                Artisan::call('module:migrate', ['module' => "installment"]);
                System::setProperty($this->module_name . '_version', $this->appVersion);
            } else {
                abort(404);
            }

            DB::commit();

            $output = ['success' => 1,
                'msg' => 'installment module updated Succesfully to version ' . $this->appVersion . ' !!'
            ];

            return redirect()->back()->with(['status' => $output]);
        } catch (Exception $e) {
            DB::rollBack();
            die($e->getMessage());
        }
    }
}
