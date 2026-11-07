<?php

namespace Modules\Searchbox\Http\Controllers;

use App\System;
use Composer\Semver\Comparator;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class InstallController extends Controller
{
    protected string $module_name = 'searchbox';

    protected string $moduleStudly = 'Searchbox';

    public function __construct()
    {
        $this->middleware('auth');
    }

    protected function authorizeModuleManager(): void
    {
        if (! auth()->user()->can('manage_modules') && ! auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }
    }

    /**
     * Version from config file (read before config:clear during install).
     */
    protected function versionFromModuleConfig(): string
    {
        $path = module_path($this->moduleStudly, 'Config/config.php');
        if (! is_file($path)) {
            return '1.0';
        }
        $cfg = require $path;

        return (string) ($cfg['module_version'] ?? '1.0');
    }

    public function index(): \Illuminate\Http\RedirectResponse
    {
        $this->authorizeModuleManager();

        ini_set('max_execution_time', '0');
        ini_set('memory_limit', '512M');

        $is_installed = System::getProperty($this->module_name.'_version');
        if (! empty($is_installed)) {
            abort(404);
        }

        $version = $this->versionFromModuleConfig();

        $this->installSettings();

        try {
            DB::beginTransaction();

            DB::statement('SET default_storage_engine=INNODB;');
            Artisan::call('module:migrate', ['module' => $this->moduleStudly, '--force' => true]);
            try {
                Artisan::call('module:publish', ['module' => $this->moduleStudly, '--force' => true]);
            } catch (\Throwable $e) {
                Artisan::call('module:publish', ['module' => $this->moduleStudly]);
            }

            System::addProperty($this->module_name.'_version', $version);

            DB::commit();

            Cache::forget('module_assets');

            $output = ['success' => 1,
                'msg' => __('searchbox::lang.installed_successfully'),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('Searchbox install: '.$e->getMessage());

            $output = [
                'success' => false,
                'msg' => $e->getMessage(),
            ];
        }

        return redirect()
            ->action([\App\Http\Controllers\Install\ModulesController::class, 'index'])
            ->with('status', $output);
    }

    public function update(): \Illuminate\Http\RedirectResponse
    {
        $this->authorizeModuleManager();

        try {
            DB::beginTransaction();

            ini_set('max_execution_time', '0');
            ini_set('memory_limit', '512M');

            $installed = System::getProperty($this->module_name.'_version');
            $app_version = $this->versionFromModuleConfig();

            if (empty($installed)) {
                abort(404);
            }

            if (Comparator::greaterThan($app_version, $installed)) {
                $this->installSettings();

                DB::statement('SET default_storage_engine=INNODB;');
                Artisan::call('module:migrate', ['module' => $this->moduleStudly, '--force' => true]);
                try {
                    Artisan::call('module:publish', ['module' => $this->moduleStudly, '--force' => true]);
                } catch (\Throwable $e) {
                    Artisan::call('module:publish', ['module' => $this->moduleStudly]);
                }

                System::setProperty($this->module_name.'_version', $app_version);
            } else {
                abort(404);
            }

            DB::commit();

            Cache::forget('module_assets');

            $output = ['success' => 1,
                'msg' => __('searchbox::lang.updated_successfully', ['version' => $app_version]),
            ];

            return redirect()
                ->action([\App\Http\Controllers\Install\ModulesController::class, 'index'])
                ->with('status', $output);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('Searchbox update: '.$e->getMessage());

            return redirect()
                ->action([\App\Http\Controllers\Install\ModulesController::class, 'index'])
                ->with('status', ['success' => false, 'msg' => $e->getMessage()]);
        }
    }

    public function uninstall(): \Illuminate\Http\RedirectResponse
    {
        $this->authorizeModuleManager();

        try {
            System::removeProperty($this->module_name.'_version');
            Cache::forget('module_assets');

            $output = ['success' => true,
                'msg' => __('lang_v1.success'),
            ];
        } catch (\Exception $e) {
            $output = ['success' => false,
                'msg' => $e->getMessage(),
            ];
        }

        return redirect()->back()->with(['status' => $output]);
    }

    private function installSettings(): void
    {
        config(['app.debug' => true]);
        Artisan::call('config:clear');
    }
}
