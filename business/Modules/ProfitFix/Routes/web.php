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

use Illuminate\Support\Facades\Route;
use Modules\ProfitFix\Http\Controllers\InstallController;
use Modules\ProfitFix\Http\Controllers\ProfitFixController;
use Modules\ProfitFix\Http\Controllers\CorrectedSalesController;
use Modules\ProfitFix\Http\Controllers\CorrectedProfitLossController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Group for Admin/Management Routes (Including Install/Uninstall)
Route::middleware(['web', 'auth', 'AdminSidebarMenu'])
    ->prefix('profitfix')
    ->group(function () {

        // Installation/Update/Uninstall Routes
        Route::get('/install', [InstallController::class, 'index'])->name('profitfix.install.index');
        Route::post('/install', [InstallController::class, 'install'])->name('profitfix.install');
        Route::post('/update', [InstallController::class, 'update'])->name('profitfix.update');
        Route::get('/uninstall', [InstallController::class, 'uninstall'])->name('profitfix.uninstall');

        // Profit-related routes
        Route::get('/get-profit/{by?}', [ProfitFixController::class, 'getProfit'])->name('profitfix.get-profit');

        // Corrected Sales routes
        Route::get('/corrected-sales', [CorrectedSalesController::class, 'index'])->name('profitfix.correctedsales.index');
        Route::get('/corrected-sales/data', [CorrectedSalesController::class, 'getSalesData'])->name('profitfix.correctedsales.data');
        Route::get('/corrected-sales/missed-profit', [CorrectedSalesController::class, 'getMissedProfit'])->name('profitfix.correctedsales.missedprofit');

        // Apply/Remove Fix routes
        Route::post('/apply-fix', [ProfitFixController::class, 'applyFix'])->name('profitfix.applyfix');
        Route::post('/remove-fix', [ProfitFixController::class, 'removeFix'])->name('profitfix.removefix');

        // Settings route
        Route::get('/settings', [ProfitFixController::class, 'settings'])->name('profitfix.settings');

        // Corrected Profit/Loss Report
        Route::get('/corrected-profit-loss', [CorrectedProfitLossController::class, 'index'])->name('profitfix.corrected_profit_loss.index');
        Route::get('/corrected-profit-loss/details', [CorrectedProfitLossController::class, 'getDetails'])->name('profitfix.corrected_profit_loss.details');

        // Tabbed AJAX data for corrected profit/loss report
        Route::get('/corrected-profit-loss/tab-data', [CorrectedProfitLossController::class, 'getTabData'])->name('profitfix.corrected_profit_loss.tab_data');

        // Detailed Profit/Loss Report
        Route::get('/detailed-profit-loss', [CorrectedProfitLossController::class, 'detailedIndex'])->name('profitfix.detailed_profit_loss.index');
        Route::get('/detailed-profit-loss/data', [CorrectedProfitLossController::class, 'getDetailedData'])->name('profitfix.detailed_profit_loss.data');

        // DataTable endpoints for each profit/loss tab
        Route::get('/corrected-profit-loss/get-profit/product', [CorrectedProfitLossController::class, 'getProfitByProduct'])->name('profitfix.corrected_profit_loss.product');
        Route::get('/corrected-profit-loss/get-profit/category', [CorrectedProfitLossController::class, 'getProfitByCategory'])->name('profitfix.corrected_profit_loss.category');
        Route::get('/corrected-profit-loss/get-profit/brand', [CorrectedProfitLossController::class, 'getProfitByBrand'])->name('profitfix.corrected_profit_loss.brand');
        Route::get('/corrected-profit-loss/get-profit/location', [CorrectedProfitLossController::class, 'getProfitByLocation'])->name('profitfix.corrected_profit_loss.location');
        Route::get('/corrected-profit-loss/get-profit/invoice', [CorrectedProfitLossController::class, 'getProfitByInvoice'])->name('profitfix.corrected_profit_loss.invoice');
        Route::get('/corrected-profit-loss/get-profit/date', [CorrectedProfitLossController::class, 'getProfitByDate'])->name('profitfix.corrected_profit_loss.date');
        Route::get('/corrected-profit-loss/get-profit/customer', [CorrectedProfitLossController::class, 'getProfitByCustomer'])->name('profitfix.corrected_profit_loss.customer');
        Route::get('/corrected-profit-loss/get-profit/day', [CorrectedProfitLossController::class, 'getProfitByDay'])->name('profitfix.corrected_profit_loss.day');
        // Missed Profit for Corrected Profit/Loss
        Route::get('/corrected-profit-loss/missedprofit', [CorrectedProfitLossController::class, 'getMissedProfit'])->name('profitfix.corrected_profit_loss.missedprofit');
    });
