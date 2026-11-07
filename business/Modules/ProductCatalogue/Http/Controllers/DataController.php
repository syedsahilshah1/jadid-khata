<?php

namespace Modules\ProductCatalogue\Http\Controllers;

use App\Utils\ModuleUtil;
use Illuminate\Routing\Controller;
use Menu;

class DataController extends Controller
{
    /**
     * Defines module as a superadmin package.
     *
     * @return array
     */
    public function superadmin_package()
    {
        return [
            [
                'name' => 'productcatalogue_module',
                'label' => __('productcatalogue::lang.productcatalogue_module'),
                'default' => false,
            ],
        ];
    }

    /**
     * Adds Catalogue QR menus
     *
     * @return null
     */
    public function modifyAdminMenu()
    {
        $business_id = session()->get('user.business_id');
        $module_util = new ModuleUtil();
        $is_productcatalogue_enabled = (bool) $module_util->hasThePermissionInSubscription($business_id, 'productcatalogue_module', 'superadmin_package');

        if ($is_productcatalogue_enabled) {
            Menu::modify('admin-sidebar-menu', function ($menu) {
                $menu->url(
                        action([\Modules\ProductCatalogue\Http\Controllers\ProductCatalogueController::class, 'generateQr']),
                        __('productcatalogue::lang.catalogue_qr'),
                        ['icon' => '<svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-qrcode"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 4m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v4a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z" /><path d="M7 17l0 .01" /><path d="M14 4m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v4a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z" /><path d="M7 7l0 .01" /><path d="M4 14m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v4a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z" /><path d="M17 7l0 .01" /><path d="M14 14l3 0" /><path d="M20 14l0 .01" /><path d="M14 14l0 3" /><path d="M14 20l3 0" /><path d="M17 17l3 0" /><path d="M20 17l0 3" /></svg>', 'active' => request()->segment(1) == 'product-catalogue', 'style' => config('app.env') == 'demo' ? 'background-color: #ff851b;color:white' : '']
                    )
                ->order(95);
            });
        }
    }
}
