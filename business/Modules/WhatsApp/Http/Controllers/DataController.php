<?php

namespace Modules\WhatsApp\Http\Controllers;

use App\Utils\ModuleUtil;
use App\Utils\Util;
use Illuminate\Routing\Controller;
use Menu;

class DataController extends Controller
{
    public function superadmin_package()
    {
        return [
            [
                'name' => 'whatsapp',
                'label' => __('WhatsApp Gateway'),
                'default' => false,
            ],
        ];
    }

    /**
      * Defines user permissions for the module.
      * @return array
      */
      public function user_permissions()
      {
          return [
              [
                  'value' => 'whatsapp.create',
                  'label' => __('whatsapp::lang.create_accounts'),
                  'default' => false
              ],
              [
                  'value' => 'whatsapp.save',
                  'label' => __('whatsapp::lang.save_accounts'),
                  'default' => false
              ],
              [
                  'value' => 'whatsapp.update',
                  'label' => __('whatsapp::lang.delete_accounts'),
                  'default' => false
              ],
              [
                  'value' => 'whatsapp.delete',
                  'label' => __('whatsapp::lang.view_accounts'),
                  'default' => false
              ],
              [
                  'value' => 'whatsapp.view',
                  'label' => __('whatsapp::lang.delete_accounts'),
                  'default' => false
              ]
          ];
      }
  

    /**
     * Adds cms menus
     *
     * @return null
     */
    public function modifyAdminMenu()
    {
        $business_id = session()->get('user.business_id');
        $module_util = new ModuleUtil();

        $isWhatsAppEnable = (bool) $module_util->hasThePermissionInSubscription($business_id, 'whatsapp');

        $commonUtil = new Util();
        $is_admin = $commonUtil->is_admin(auth()->user(), $business_id);

        if (auth()->user()->can('whatsapp.view') && $isWhatsAppEnable) {
            Menu::modify(
                'admin-sidebar-menu',
                function ($menu) use ($is_admin) {
                    $menu->url(action([\Modules\WhatsApp\Http\Controllers\WhatsAppController::class, 'index']), ''. __('whatsapp::lang.app_name'), ['icon' => 'fab fa-lg fa-whatsapp', 'style' => config('app.env') == 'demo' ? 'background-color: #128C7E;' : '', 'active' => request()->segment(1) == 'whatsapp'])->order(50);
                }
            );
        }
    }
}