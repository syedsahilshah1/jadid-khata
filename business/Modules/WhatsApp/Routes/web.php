<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware('web', 'authh', 'auth', 'SetSessionData', 'language', 'timezone', 'AdminSidebarMenu')->prefix('whatsapp')->group(function() {
    // -- Main
    Route::resource('/', Modules\WhatsApp\Http\Controllers\WhatsAppController::class);
    Route::get('/dashboard', 'WhatsAppController@index');
    Route::get('/{id}/delete', 'WhatsAppController@destroy');
    Route::delete('/accounts/{id}/delete', 'WhatsAppController@deleteAccounts');
    Route::delete('/{id}', 'WhatsAppController@destroy');
    Route::put('/{id}', 'WhatsAppController@update');
    Route::get('/{id}/edit', 'WhatsAppController@edit');
    Route::post('/check-default-gateway', 'WhatsAppController@checkDefaultGateway');
    // -- Settings Page
    Route::get('/settings', 'WhatsAppController@settings');
    Route::get('/{id}/settings', 'WhatsAppController@settingsShow');
    Route::post('/{id}/settings', 'WhatsAppController@settingsSave');
    Route::put('/{id}/settings', 'WhatsAppController@settingsUpdate');
    Route::post('/{id}/settings', 'WhatsAppController@settingsDelete');
    // -- Installer Controller
    // Route::get('/install', [Modules\WhatsApp\Http\Controllers\InstallController::class, 'index']);
    // Route::post('/install', [Modules\WhatsApp\Http\Controllers\InstallController::class, 'install']);
    Route::get('install', 'InstallController@index');
    Route::get('/install/uninstall', [Modules\WhatsApp\Http\Controllers\InstallController::class, 'uninstall']);
    Route::get('/install/update', [Modules\WhatsApp\Http\Controllers\InstallController::class, 'update']);
    Route::get('set-local', []);
});
