<?php

namespace Modules\Gym\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Menu;
use App\Utils\ModuleUtil;

class DataController extends Controller
{
    /**
     * Defines user permissions for the module.
     *
     * @return array
     */
    public function user_permissions()
    {
        return [
            [
                'value' => 'gym.manage_member',
                'label' => __('gym::lang.manage_member'),
                'default' => false,
            ],
            [
                'value' => 'gym.manage_subscription',
                'label' => __('gym::lang.manage_subscription'),
                'default' => false,
            ],
            [
                'value' => 'gym.manage_attendance',
                'label' => __('gym::lang.manage_attendance'),
                'default' => false,
            ],
            [
                'value' => 'gym.manage_package',
                'label' => __('gym::lang.manage_package'),
                'default' => false,
            ],
            [
                'value' => 'gym.manage_health',
                'label' => __('gym::lang.manage_health'),
                'default' => false,
            ],
            [
                'value' => 'gym.manage_setting',
                'label' => __('gym::lang.manage_setting'),
                'default' => false,
            ],
            [
                'value' => 'gym.manage_class',
                'label' => __('gym::lang.manage_class'),
                'default' => false,
            ],
            [
                'value' => 'gym.add_subscription_payment',
                'label' => __('gym::lang.add_subscription_payment'),
                'default' => false,
            ],
            [
                'value' => 'gym.edit_subscription_payment',
                'label' => __('gym::lang.edit_subscription_payment'),
                'default' => false,
            ],
            [
                'value' => 'gym.delete_subscription_payment',
                'label' => __('gym::lang.delete_subscription_payment'),
                'default' => false,
            ],
           
        ];
    }

    public function superadmin_package()
    {
        return [
            [
                'name' => 'gym_module',
                'label' => __('gym::lang.gym_module'),
                'default' => false,
            ],
        ];
    }

    public function modifyAdminMenu()
    {
        $module_util = new ModuleUtil();
        $business_id = session()->get('user.business_id');
        $is_gym_enabled = (bool) $module_util->hasThePermissionInSubscription($business_id, 'gym_module');

        if ($is_gym_enabled) {
            Menu::modify('admin-sidebar-menu', function ($menu) {
                $menu->url(
                    action([\Modules\Gym\Http\Controllers\DashBoardController::class, 'index']),
                    __('gym::lang.gym'),
                    ['icon' => '<svg  xmlns="http://www.w3.org/2000/svg" class="tw-size-5 tw-shrink-0"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-barbell"><path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                    <path d="M2 12h1" /><path d="M6 8h-2a1 1 0 0 0 -1 1v6a1 1 0 0 0 1 1h2" /><path d="M6 7v10a1 1 0 0 0 1 1h1a1 1 0 0 0 1 -1v-10a1 1 0 0 0 -1 -1h-1a1 1 0 0 0 -1 1z" />
                    <path d="M9 12h6" /><path d="M15 7v10a1 1 0 0 0 1 1h1a1 1 0 0 0 1 -1v-10a1 1 0 0 0 -1 -1h-1a1 1 0 0 0 -1 1z" /><path d="M18 8h2a1 1 0 0 1 1 1v6a1 1 0 0 1 -1 1h-2" /><path d="M22 12h-1" />
                    </svg>', 'style' => config('app.env') == 'demo' ? 'background-color: #A9A9A9 !important;' : '', 'active' => request()->segment(1) == 'gym']
                )->order(51);
            });
        }
    }
}
