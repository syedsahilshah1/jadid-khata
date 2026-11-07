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

namespace Modules\Restore\Http\Controllers;

use Illuminate\Routing\Controller;
use Menu;

class DataController extends Controller
{
    /**
     * Add Restore menu to admin sidebar under Administrator > Backup
     */
    public function modifyAdminMenu()
    {
        try {
            // Check if module is actually installed (has version in system table)
            $is_installed = \App\System::getProperty('restore_version');
            if (empty($is_installed)) {
                return; // Module not installed, skip menu modification
            }

            // Check if user is an admin using session-based business ID
            $user = auth()->user();
            $is_admin = $user && $user->hasRole('Admin#' . session('business.id'));

            if ($is_admin) {
                Menu::modify('admin-sidebar-menu', function ($menu) {
                    // Immediately after Administrator Backup (order 60)
                    $menu->url(
                        action([\Modules\Restore\Http\Controllers\RestoreController::class, 'index']),
                        'Restore Backups',
                        [
                            'icon' => 'fa fa-undo',
                            'active' => request()->segment(1) == 'restore',
                        ]
                    )->order(61);
                });
            }
        } catch (\Exception $e) {
            // Fail silently
        }
    }
}
