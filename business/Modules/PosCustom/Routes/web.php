<?php

use Illuminate\Support\Facades\Route;

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

Route::middleware('web', 'authh', 'auth', 'SetSessionData', 'language', 'timezone', 'AdminSidebarMenu')->group(function () {
 
    Route::prefix('poscustom')->group(function () {
       
        Route::resource('/pos', Modules\PosCustom\Http\Controllers\SellPosController::class);
        Route::get('/sells/pos/get-product-suggestion-custom', [Modules\PosCustom\Http\Controllers\SellPosController::class, 'getProductSuggestion']);

        Route::get('/sells/pos/get-recent-transactions-custom', [Modules\PosCustom\Http\Controllers\SellPosController::class, 'getRecentTransactions']);
        Route::get('/sells/pos/get-featured-products-custom/{location_id}', [Modules\PosCustom\Http\Controllers\SellPosController::class, 'getFeaturedProducts']);
        
        //Route included for the module when it begin without cash register open
        Route::resource('cash-register', Modules\PosCustom\Http\Controllers\CashRegisterController::class);

        //Route to show suspend orders and redirect
        Route::get('/index', [Modules\PosCustom\Http\Controllers\SellController::class, 'index']);
        
          
        Route::get('/', [Modules\PosCustom\Http\Controllers\PosCustomController::class, 'index']);
        Route::get('edit/{id}', [Modules\PosCustom\Http\Controllers\PosCustomController::class, 'edit']);
        Route::put('update/{id}', [Modules\PosCustom\Http\Controllers\PosCustomController::class, 'update']);
        
       // Route::get('create', [\Modules\PosCustom\Http\Controllers\SellPosController::class, 'create']);
		
//         Route::post('/store', [Modules\PosCustom\Http\Controllers\SellPosCCController::class, 'store'])->name('store');
       
       //Install Controller
        Route::get('/install', [Modules\PosCustom\Http\Controllers\InstallController::class, 'index']);
        Route::get('/install/update', [Modules\PosCustom\Http\Controllers\InstallController::class, 'update']);
        Route::get('/install/uninstall', [Modules\PosCustom\Http\Controllers\InstallController::class, 'uninstall']);


    });
    
});
