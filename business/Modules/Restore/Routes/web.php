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

use Illuminate\Support\Facades\Route;
use Modules\Restore\Http\Controllers\RestoreController;
use Modules\Restore\Http\Controllers\RestoreProgressController;
use Modules\Restore\Http\Controllers\InstallController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Main application routes for the Restore module.
| These routes are automatically prefixed with 'restore' by RouteServiceProvider.
| Installation routes are in install.php
|
*/

// Ensure session middleware is applied
Route::middleware(['web', 'authh', 'SetSessionData', 'language', 'timezone', 'AdminSidebarMenu'])->group(function () {

    // ==================== RESTORE ROUTES ====================
    Route::prefix('restore')->name('restore.')->group(function () {
        Route::get('/', [RestoreController::class, 'index'])->name('index');
        Route::post('/run', [RestoreController::class, 'run'])->name('run');
        Route::get('/progress', [RestoreProgressController::class, 'get'])->name('progress');
    });

    // ==================== INSTALLATION ROUTES ====================
    Route::prefix('restore')->name('restore.')->group(function () {
        Route::get('/install', [InstallController::class, 'index'])->name('index');
        Route::post('/install', [InstallController::class, 'install'])->name('install');
        Route::get('/uninstall', [InstallController::class, 'uninstall'])->name('uninstall');
    });
});
