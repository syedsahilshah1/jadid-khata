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

namespace Modules\Restore\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;
use Modules\Restore\Http\Controllers\DataController; // Import DataController

class RestoreServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     */
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'restore');

        // Call modifyAdminMenu to add menu items
        $dataController = new DataController();
        $dataController->modifyAdminMenu();
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        //
    }
}
