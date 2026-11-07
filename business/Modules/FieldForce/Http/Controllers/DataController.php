<?php

namespace Modules\FieldForce\Http\Controllers;

use App\Utils\ModuleUtil;
use Illuminate\Routing\Controller;
use Menu;

class DataController extends Controller
{

    /**
     * Adds Field Force menus
     * @return null
     */
    public function modifyAdminMenu()
    {
        $business_id = session()->get('user.business_id');
        $module_util = new ModuleUtil();
        
        $is_fieldforce_enabled = (boolean)$module_util->hasThePermissionInSubscription($business_id, 'fieldforce_module');

        if ($is_fieldforce_enabled) {
            Menu::modify(
                'admin-sidebar-menu',
                function ($menu){
                    $background_color = '';
                    if (config('app.env') == 'demo') {
                        $background_color = '#ff00ff !important';
                    }
                    
                    if (auth()->user()->can('visit.view_own') || auth()->user()->can('visit.view_all') ) {
                        $menu->url(action('\Modules\FieldForce\Http\Controllers\FieldForceDashboardController@index'), __('fieldforce::lang.field_force'), ['icon' => 'fa fas fa-users-cog', 'active' => request()->segment(1) == 'field-force', 'style' => 'background-color:'.$background_color])->order(91);
                    }
                }
            );
        }
    }

    /**
     * Superadmin package permissions
     * @return array
     */
    public function superadmin_package()
    {
        return [
            [
                'name' => 'fieldforce_module',
                'label' => __('fieldforce::lang.fieldforce_module'),
                'default' => false
            ]
        ];
    }

    /**
     * Defines user permissions for the module.
     * @return array
     */
    public function user_permissions()
    {
        $permissions = [
            [
                'value' => 'visit.view_all',
                'label' => __('fieldforce::lang.view_all_visits'),
                'default' => false,
                'is_radio' => true,
                'radio_input_name' => 'visit_view'
            ],
            [
                'value' => 'visit.view_own',
                'label' => __('fieldforce::lang.view_own_visits'),
                'default' => false,
                'is_radio' => true,
                'radio_input_name' => 'visit_view'
            ],
            [
                'value' => 'visit.create',
                'label' => __('fieldforce::lang.create_visit'),
                'default' => false
            ],
            [
                'value' => 'visit.update',
                'label' => __('fieldforce::lang.edit_visit'),
                'default' => false
            ],
            [
                'value' => 'visit.delete',
                'label' => __('fieldforce::lang.delete_visit'),
                'default' => false
            ],
        ];

        return $permissions;
    }
}
