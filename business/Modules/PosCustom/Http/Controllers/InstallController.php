<?php

namespace Modules\PosCustom\Http\Controllers;
use App\System;
use Composer\Semver\Comparator;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Faker\Provider\File;
use Illuminate\Support\Facades\Storage;

class InstallController extends Controller
{
    public function __construct()
    {
        $this->module_name = 'PosCustom';
        $this->appVersion = config('PosCustom.module_version');
    }

    /**
     * Install
     * @return Response
     */
    public function index()
    {
        if (!auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '512M');

        $this->installSettings();

        //Check if PosCustom installed or not.
        $is_installed = System::getProperty($this->module_name . '_version');
        if (!empty($is_installed)) {
            abort(404);
        }
        $this->install();
        
        $output = ['success' => 1,
        'msg' => 'PosCustom module installed Succesfully V:' . $this->appVersion . ' !!'
        ];
        
        return redirect()
            ->action('\App\Http\Controllers\Install\ModulesController@index')
            ->with('status', $output);
    
    }

    /**
     * Initialize all install functions
     */
    private function installSettings()
    {
        config(['app.debug' => true]);
        Artisan::call('config:clear');
    }


    /**
     * Installing PosCustom Module
     */
    public function install()
    {
        try {

            $is_installed = System::getProperty($this->module_name . '_version');
            if (!empty($is_installed)) {
                abort(404);
            }

            DB::statement('SET default_storage_engine=INNODB;');
            Artisan::call('module:migrate', ['module' => "PosCustom", "--force"=> true]);
            Artisan::call('module:publish', ['module' => "PosCustom"]);
            System::addProperty($this->module_name . '_version', $this->appVersion);

            DB::commit();

            $output = ['success' => 1,
                    'msg' => 'PosCustom module installed succesfully'
                ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => $e->getMessage()
            ];
        }

            //********************************* */
            /** In this section the controller and the tailwind/app.css will be install for PosCustom*/
                //Set the paths for the files to move or rename
                //Case controller
                $path_ori = app_path('Http\Controllers\SellPosController.php');
                $path_rename = app_path('Http\Controllers\SellPosController_ori.php');
                $path_module = base_path('Modules\PosCustom\Http\Controllers\SellPosController_Module.php');
                Storage::append('logPS.log', 'Installing...');
            //Case Tailwind/app.css
                $path_css_ori = public_path('css\tailwind\app.css');
                $path_css_rename = public_path('css\tailwind\app_ori.css');
                $path_css_module = base_path('Modules\PosCustom\Http\Controllers\tailwind_app_Module.css');

            //If exists is because the move is done
            if (!file_exists($path_rename)) { //If not exist Http\Controllers\SellPosController_ori.php
                //Case Controller
                    if (file_exists($path_ori)) {
                        \File::move($path_ori, $path_rename);
                        \File::copy($path_module, $path_ori);
                        Storage::append('logPS.log', 'Rename to SellPosController_ori...');
                    }
                //Case tailwind/app.css
                    if (file_exists($path_css_ori)) {
                        \File::move($path_css_ori, $path_css_rename);
                        \File::copy($path_css_module, $path_css_ori);
                        Storage::append('logPS.log', 'Rename to tailwind\app_ori...');
                    }
                }    
            /********************************** */

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
                            'msg' => 'PosCustom module uninstall succesfully'
                        ];
        } catch (\Exception $e) {
            $output = ['success' => false,
                        'msg' => $e->getMessage()
                    ];
        }

        //********************************* */
        /** In this section the controller will be install for PosCustom*/
            //Set the paths for the files to move or rename
            $path_ori = app_path('Http\Controllers\SellPosController.php');
            $path_rename = app_path('Http\Controllers\SellPosController_ori.php');
            Storage::append('logPS.log', 'UnInstalling...');
            $path_css_ori = public_path('css\tailwind\app.css');
            $path_css_rename = public_path('css\tailwind\app_ori.css');
            
        //Disable and use the original controller
        if (file_exists($path_rename)) { //If exist Http\Controllers\SellPosController_ori.php
            //Case Controller    
            \File::move($path_rename, $path_ori);
            //Case tailwind/app.css
            \File::move($path_css_rename, $path_css_ori);

            Storage::append('logPS.log', 'Rename to original...');
        }
        //********************************* */
        
        return redirect()->back()->with(['status' => $output]);
    }

    /**
     * update module
     * @return Response
     */
    public function update()
    {
        //Check if PosCustom_version is same as appVersion then 404
        //If appVersion > crm_version - run update script.
        //Else there is some problem.
        if (!auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            DB::beginTransaction();
            ini_set('max_execution_time', 0);
            ini_set('memory_limit', '512M');

            $crm_version = System::getProperty($this->module_name . '_version');

            if (Comparator::greaterThan($this->appVersion, $crm_version)) {
                ini_set('max_execution_time', 0);
                ini_set('memory_limit', '512M');
                $this->installSettings();
                
                DB::statement('SET default_storage_engine=INNODB;');
                Artisan::call('module:migrate', ['module' => "PosCustom", "--force"=> true]);
                Artisan::call('module:publish', ['module' => "PosCustom"]);
                System::setProperty($this->module_name . '_version', $this->appVersion);
            } else {
                abort(404);
            }

            DB::commit();
            
            $output = ['success' => 1,
                        'msg' => 'PosCustom module updated Succesfully to version ' . $this->appVersion . ' !!'
                    ];

            return redirect()->back()->with(['status' => $output]);
        } catch (Exception $e) {
            DB::rollBack();
            die($e->getMessage());
        }
    }
}
