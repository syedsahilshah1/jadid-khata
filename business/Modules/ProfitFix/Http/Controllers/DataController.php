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

use Illuminate\Routing\Controller;

class DataController extends Controller
{
    /**
     * Modify the admin sidebar menu to include ProfitFix items.
     *
     * @return void
     */
    public function modifyAdminMenu()
    {
        try {
            // Check if module is actually installed (has version in system table)
            $is_installed = \App\System::getProperty('ProfitFix_version');
            if (empty($is_installed)) {
                return; // Module not installed, skip menu modification
            }

            if (!auth()->check()) {
                return;
            }

            $business_id = session()->get('user.business_id');
            $module_util = new \App\Utils\ModuleUtil();

            $is_profitfix_enabled = (bool) $module_util->hasThePermissionInSubscription($business_id, 'profitfix_module', 'superadmin_package');

            // Admin Menu Items
            if ($is_profitfix_enabled && auth()->user()->can('ProfitFix.access')) {
                \Menu::modify('admin-sidebar-menu', function ($menu) {
                    $menu->dropdown(
                        'ProfitFix',
                        function ($sub) {

                            $sub->url(
                                action([\Modules\ProfitFix\Http\Controllers\CorrectedSalesController::class, 'index']),
                                'Corrected Sales',
                                ['icon' => 'fa fa-chart-line']
                            );
                            $sub->url(
                                action([\Modules\ProfitFix\Http\Controllers\CorrectedProfitLossController::class, 'index']),
                                'Corrected Profit Report',
                                ['icon' => 'fa fa-chart-bar']
                            );
                                $sub->url(
                                action([\Modules\ProfitFix\Http\Controllers\ProfitFixController::class, 'settings']),
                                'Settings',
                                ['icon' => 'fa fa-cogs']
                            );
                        },
                        [
                            'icon' => 'fa fa-tools',
                            'active' => request()->is('profit-fix/*'),
                        ]
                    )->order(58);
                });
            }
        } catch (\Exception $e) {
            \Log::error('Error modifying admin menu for ProfitFix: ' . $e->getMessage());
        }
    }
}