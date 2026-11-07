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

Route::group(['middleware' => ['web', 'authh', 'auth', 'SetSessionData', 'language', 'timezone', 'AdminSidebarMenu', 'CheckUserLogin'], 'namespace' => '\Modules\FieldForce\Http\Controllers'], function () {

    Route::group(['prefix' => 'field-force'], function () {
        Route::get('update-visit-status/{id}', 'FieldForceController@updateVisitStatus');
        Route::post('update-visit-status', 'FieldForceController@postUpdateVisitStatus');

        Route::resource('visits', 'FieldForceController');
        Route::get('dashboard', 'FieldForceDashboardController@index');

        Route::get('visit-by-users', 'FieldForceDashboardController@visitByUsers');

        Route::get('install', 'InstallController@index');
        Route::post('install', 'InstallController@install');
        Route::get('install/uninstall', 'InstallController@uninstall');
        Route::get('install/update', 'InstallController@update');
    });
});
