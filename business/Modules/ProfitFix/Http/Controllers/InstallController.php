<?php
/**
 * ProfitFix Module
 * * A comprehensive module that will display corrected profit for products that have 0 purchase or negative stock.
 * * Module: ProfitFix
 * Author: [Telegram @omgklrahul]
 * Version: 1.0.0
 * * This is a free module for non commercial use.
 * * @package Modules\ProfitFix
 */
namespace Modules\ProfitFix\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\ProfitFix\Database\Seeders\ProfitFixDatabaseSeeder;

class InstallController extends Controller
{
    protected $module_name;
    protected $module_display_name;
    protected $appVersion;

    public function __construct()
    {
        $this->module_name = 'profitfix';
        $this->module_display_name = 'ProfitFix';
        $this->appVersion = config('profitfix.module_version') ?? '1.0.0';
    }

    /**
     * Show the installation screen and handle update logic.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        if (!auth()->user()->can('superadmin')) abort(403, 'Unauthorized action.');

        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '512M');

        $is_installed = \App\System::getProperty($this->module_name . '_version');

        if (empty($is_installed)) {
            // New Installation
            $action_url = route('profitfix.install');
            $module_display_name = $this->module_display_name;
            $appVersion = $this->appVersion;
            return view('profitfix::install.index', compact('action_url', 'module_display_name', 'appVersion'));
        } elseif (version_compare($is_installed, $this->appVersion, '<')) {
            // Update logic
            try {
                \Artisan::call('module:migrate', ['module' => $this->module_name, '--force' => true]);
                \Artisan::call('module:publish', ['module' => $this->module_name]);
                \App\System::setProperty($this->module_name . '_version', $this->appVersion);
                $message = $this->module_display_name . ' module updated successfully to version ' . $this->appVersion . '!!';
                $output = ['success' => 1, 'msg' => $message];
            } catch (\Exception $e) {
                $output = ['success' => false, 'msg' => 'Update failed: ' . $e->getMessage()];
            }
            return redirect()->action(['\\App\\Http\\Controllers\\Install\\ModulesController', 'index'])->with('status', $output);
        } else {
            $message = $this->module_display_name . ' module is already up to date.';
            $output = ['success' => 1, 'msg' => $message];
            return redirect()->action(['\\App\\Http\\Controllers\\Install\\ModulesController', 'index'])->with('status', $output);
        }
    }

    /**
     * Install the ProfitFix module.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function install(Request $request)
    {
        if (!auth()->user()->can('superadmin')) abort(403, 'Unauthorized action.');
        try {
            \DB::beginTransaction();
            \DB::statement('SET default_storage_engine=INNODB;');
            // Run migrations
            \Artisan::call('module:migrate', ['module' => $this->module_name, '--force' => true]);
            // Publish assets
            \Artisan::call('module:publish', ['module' => $this->module_name]);
            // Save module version
            \App\System::addProperty($this->module_name . '_version', $this->appVersion);
            \DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error($this->module_display_name . ' installation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Installation failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the ProfitFix module.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update()
    {
        try {
            \DB::beginTransaction();

            // Run migrations
            \Artisan::call('module:migrate', ['module' => $this->module_name, '--force' => true]);

            // Publish assets
            \Artisan::call('module:publish', ['module' => $this->module_name]);

            // Update module version
            \App\System::setProperty($this->module_name . '_version', $this->appVersion);

            \DB::commit();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error($this->module_display_name . ' update failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Update failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Uninstall the ProfitFix module.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function uninstall()
    {
        if (!auth()->user()->can('superadmin')) abort(403, 'Unauthorized action.');

        try {
            \DB::beginTransaction();

            // Rollback migrations
            \Artisan::call('module:migrate-rollback', ['module' => $this->module_name, '--force' => true]);

            // Remove module version
            \App\System::removeProperty($this->module_name . '_version');

            \DB::commit();

            \Artisan::call('cache:clear');

            $output = ['success' => true, 'msg' => $this->module_display_name . ' module uninstalled successfully.'];
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error($this->module_display_name . ' uninstallation failed: ' . $e->getMessage());

            $output = ['success' => false, 'msg' => 'Uninstallation failed: ' . $e->getMessage()];
        }

        return redirect()->action(['\\App\\Http\\Controllers\\Install\\ModulesController', 'index'])->with('status', $output);
    }
}