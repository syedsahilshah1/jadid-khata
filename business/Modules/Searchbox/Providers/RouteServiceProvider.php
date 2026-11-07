<?php

namespace Modules\Searchbox\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;
use Modules\Searchbox\Http\Controllers\InstallController;

class RouteServiceProvider extends ServiceProvider
{
    protected string $moduleNamespace = 'Modules\Searchbox\Http\Controllers';

    public function boot(): void
    {
        parent::boot();
    }

    public function map(): void
    {
        $this->mapInstallRoutes();
        $this->mapWebRoutes();
    }

    /**
     * Register install lifecycle URLs with explicit FQCN so url()->action(InstallController@…) resolves
     * (same pattern as AdvancedReports). Includes authh (IsInstalled) like other modules.
     */
    protected function mapInstallRoutes(): void
    {
        Route::middleware(['web', 'authh', 'auth', 'SetSessionData', 'language', 'timezone'])
            ->group(function () {
                Route::get('searchbox/install', [InstallController::class, 'index']);
                Route::get('searchbox/install/update', [InstallController::class, 'update']);
                Route::get('searchbox/install/uninstall', [InstallController::class, 'uninstall']);
            });
    }

    protected function mapWebRoutes(): void
    {
        Route::middleware(['web', 'authh', 'auth', 'SetSessionData', 'language', 'timezone'])
            ->prefix('searchbox')
            ->namespace($this->moduleNamespace)
            ->group(module_path('Searchbox', '/Routes/web.php'));
    }
}
