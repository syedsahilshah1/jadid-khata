<?php

namespace Modules\WhatsApp\Http\Controllers;

use App\System;
use Composer\Semver\Comparator;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class InstallController extends Controller
{
    protected string $module_name;
    protected mixed $appVersion;
    
    public function __construct()
    {
        $this->module_name = 'whatsapp';
        $this->appVersion = config('whatsapp.module_version');
    }

    /**
     * Display a listing of the resource.
     * @return Application|Factory|View
     */
    public function install(): Application|Factory|View
    {
        if (!auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '512M');

        $this->installSettings();

        //Check if installed or not.
        $isInstalled = System::getProperty($this->module_name . '_version');
        if (!empty($isInstalled)) {
            abort(404);
        }
        return view('install.install-module');
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
     * Installing Whatsapp Module
     */
    public function index()
    {
        try {

            if (!auth()->user()->can('superadmin')) {
                abort(403, 'Unauthorized action.');
            }
    
            ini_set('max_execution_time', 0);
            ini_set('memory_limit', '512M');
    
            $this->installSettings();
    
            //Check if installed or not.
            $isInstalled = System::getProperty($this->module_name . '_version');
            if (!empty($isInstalled)) {
                abort(404);
            }
    

            $isInstalled = System::getProperty($this->module_name . '_version');
            if (!empty($isInstalled)) {
                abort(404);
            }

            DB::statement('SET default_storage_engine=INNODB;');
            Artisan::call('module:migrate', ['module' => "WhatsApp"]);
            Artisan::call('module:publish', ['module' => 'WhatsApp']);
            System::addProperty($this->module_name . '_version', $this->appVersion);
            
            // Automatically modify NotificationUtil.php
            $this->modifyNotificationUtil();


            DB::commit();
            
            $output = ['success' => 1,
                    'msg' => 'WhatsApp module installed succesfully'
                ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => $e->getMessage()
            ];
        }
        

        return redirect()
                ->action('\App\Http\Controllers\Install\ModulesController@index')
                ->with('status', $output);
    }
    protected function modifyNotificationUtil()
    {
        $notificationUtilPath = base_path('app/Utils/NotificationUtil.php');
        $backupPath = base_path('app/Utils/NotificationUtil_Backup.php');
        // Create a backup of the original file
        File::copy($notificationUtilPath, $backupPath);

        // Make sure the file exists
        if (File::exists($notificationUtilPath)) {
            // Modify NotificationUtil.php
            $content = File::get($notificationUtilPath);

            // Add the 'use' statement after 'use Config;'
            $modifiedContent = preg_replace(
                '/^(use Config;)/m',
                "$1\nuse Modules\\WhatsApp\\Services\\WhatsAppServices;",
                $content
            );
            $modifiedContent = str_replace(
                '$whatsapp_link = $this->getWhatsappNotificationLink($data);',
                '$this->whatsAppUtil->requestData("sale", $data, "none", "-");',
                $modifiedContent
            );
            // Save the modified content back to NotificationUtil.php
            File::put($notificationUtilPath, $modifiedContent);
            // Define the content to be replaced
            $newCode = <<<PHP
            
                protected WhatsAppServices \$whatsAppUtil;
            
                public function __construct(WhatsAppServices \$whatsAppUtil)
                {
                    \$this->whatsAppUtil = \$whatsAppUtil;
                }

            PHP;
                
            // Modify NotificationUtil.php
            $content = File::get($notificationUtilPath);

            // Find the position of the class declaration line
            $classDeclarationPosition = strpos($content, 'class NotificationUtil extends Util');
            
            if ($classDeclarationPosition !== false) {
                // Find the end of the second line after the class declaration
                $secondLineEndPosition = strpos($content, "\n", $classDeclarationPosition + 1);
                
                if ($secondLineEndPosition !== false) {
                    // Insert the new code after the second line
                    $insertPosition = $secondLineEndPosition + 3;
                    
                    // Insert the new code at the calculated position
                    $modifiedContent = substr_replace(
                        $content,
                        $newCode,
                        $insertPosition,
                        0
                    );

                    // Save the modified content back to NotificationUtil.php
                    File::put($notificationUtilPath, $modifiedContent);
                }
            }
        }
    }
    /**
     * Uninstall
     * @return RedirectResponse
     */
    public function uninstall(): RedirectResponse
    {
        if (!auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            System::removeProperty($this->module_name . '_version');
            $notificationUtilPath = base_path('app/Utils/NotificationUtil.php');
            $backupPath = base_path('app/Utils/NotificationUtil_backup.php');

            // Make sure the file exists
            if (File::exists($notificationUtilPath)) {
                // Create a backup of the original file
                File::copy($backupPath, $notificationUtilPath);
            }
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
     * @return RedirectResponse
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

            $whatsapp_version = System::getProperty($this->module_name . '_version');

            if (Comparator::greaterThan($this->appVersion, $whatsapp_version)) {
                ini_set('max_execution_time', 0);
                ini_set('memory_limit', '512M');
                $this->installSettings();
                
                DB::statement('SET default_storage_engine=INNODB;');
                Artisan::call('module:migrate', ['module' => "WhatsApp"]);
                System::setProperty($this->module_name . '_version', $this->appVersion);
            } else {
                abort(404);
            }

            DB::commit();
            
            $output = ['success' => 1,
                        'msg' => 'WhatsApp module updated Succesfully to version ' . $this->appVersion . ' !!'
                    ];

            return redirect()->back()->with(['status' => $output]);
        } catch (\Exception $e) {
            DB::rollBack();
            die($e->getMessage());
        }
    }
}
