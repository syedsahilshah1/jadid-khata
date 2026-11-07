<?php

namespace Modules\Backupadv\Http\Controllers;

use App\Utils\ModuleUtil;
use App\Utils\Util;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Menu;

class DataController extends Controller
{
    /**
     * Superadmin package permissions
     *
     * @return array
     */
    public function superadmin_package()
    {
        return [
            [
                'name' => 'Backupadv_module',
                'label' => __('Backupadv::lang.Backupadv_module'),
                'default' => false,
            ],
        ];
    }

    /**
     * Adds Backupadv menus
     *
     * @return null
     */
    public function modifyAdminMenu()
    {
        $business_id = session()->get('user.business_id');
        $module_util = new ModuleUtil();

        $is_Backupadv_enabled = (bool) $module_util->hasThePermissionInSubscription($business_id, 'Backupadv_module');

        $commonUtil = new Util();
        $is_admin = $commonUtil->is_admin(auth()->user(), $business_id);

        if ($is_Backupadv_enabled) {
            $menu = Menu::instance('admin-sidebar-menu');
            $menu->url(action([\Modules\Backupadv\Http\Controllers\BackupadvController::class, 'dashboard']), __('Backupadv::lang.Backupadv_module'), ['icon' => 'fa fa-cloud-upload-alt', 'active' => request()->segment(1) == 'Backupadv'])->order(70);
        }
    }
}
