<?php

namespace Modules\WhatsApp\Http\Controllers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\WhatsApp\Services\WhatsAppServices;
use App\Utils\{ModuleUtil};
use DB;
use Yajra\DataTables\Exceptions\Exception;

class WhatsAppController extends Controller
{
    protected WhatsAppServices $whatsAppUtil;
    protected ModuleUtil $moduleUtil;

    public function __construct(WhatsAppServices $whatsAppUtil,  ModuleUtil $moduleUtil) {
        $this->whatsAppUtil     = $whatsAppUtil;
        $this->moduleUtil       = $moduleUtil;
    }

	/**
	 * @return array
	 */
	public function testFunction(): array
	{
		return [''];
	}

    /**
     * Display a listing of the resource.
     * @return Application|Factory|View|JsonResponse
     * @throws Exception
     */
    public function index(): Application|Factory|View|JsonResponse
    {
        $business_id = request()->session()->get('user.business_id');
        if (!(auth()->user()->can('superadmin') || ($this->moduleUtil->hasThePermissionInSubscription($business_id, 'whatsapp_module') && (auth()->user()->can('whatsapp.view'))))) {
            abort(403, 'Unauthorized action.');
        }
        if (request()->ajax()) {
            $accounts = $this->whatsAppUtil->loadAccounts();
            return datatables()->eloquent($accounts)
            ->addColumn('action', function ($row) {
                $html  = '<button type="button" data-href="' .action([\Modules\WhatsApp\Http\Controllers\WhatsAppController::class, 'edit'], [$row->id]) . '"  class="btn btn-warning btn-modal btn-xs"  data-container=".whatsapp_edit_modal"><i class="fal fa fa-edit" aria-hidden="true"></i> </button>';
                $html .= '&nbsp';
                $html .= '<button type="button" data-href="' .action([\Modules\WhatsApp\Http\Controllers\WhatsAppController::class, 'destroy'], [$row->id]) . '"  class="btn btn-danger btn-xs delete-whatsapp-accounts"><i class="fal fa fa-trash" aria-hidden="true"></i> </button>';
                 return $html;
             })
             ->editColumn('is_default', function ($row) {
                $html = '';
                if ($row->is_default == 1) {
                    $html .= '<p class="btn btn-xs btn-dark">'.__("whatsapp::lang.yes").'</p>';
                }else {
                    $html .= '<p class="btn btn-xs btn-dark">'.__("whatsapp::lang.no").'</p>';
                }
                return $html;
             })
             ->rawColumns(['action','is_default'])
             ->toJson();
            }
        return view('whatsapp::whatsapp.index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create(): Renderable
    {
        $business_id = request()->session()->get('user.business_id');
        if (!(auth()->user()->can('superadmin') || ($this->moduleUtil->hasThePermissionInSubscription($business_id, 'whatsapp_module') && (auth()->user()->can('whatsapp.create'))))) {
            abort(403, 'Unauthorized action.');
        }
        $accounts = $this->whatsAppUtil->loadAccounts()->get();
        $count = $accounts->count();
        if ($count >= 2) {
            return view('whatsapp::whatsapp.not_allowed');
        }  
        else { return view('whatsapp::whatsapp.create');}
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return array
     */
    public function store(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        if (!(auth()->user()->can('superadmin') || ($this->moduleUtil->hasThePermissionInSubscription($business_id, 'whatsapp_module') && (auth()->user()->can('whatsapp.save'))))) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $input = $request->only(['sources', 'wa_server', 'is_default', 'app_key', 'auth_key', 'sender']);
            $this->whatsAppUtil->saveWhatsAppAccounts($input);
            $output = ['success' => true,
            'msg' => __('whatsapp::lang.success')
        ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            $output = ['success' => false,
                            'msg' => __("messages.something_went_wrong")
                        ];
        }
		
        return $output;
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show(int $id): Renderable
    {
        $business_id = request()->session()->get('user.business_id');
        if (!(auth()->user()->can('superadmin') || ($this->moduleUtil->hasThePermissionInSubscription($business_id, 'whatsapp_module') && (auth()->user()->can('whatsapp.view'))))) {
            abort(403, 'Unauthorized action.');
        }
        return view('whatsapp::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit(int $id): Renderable
    {
        $business_id = request()->session()->get('user.business_id');
        if (!(auth()->user()->can('superadmin') || ($this->moduleUtil->hasThePermissionInSubscription($business_id, 'whatsapp_module') && (auth()->user()->can('whatsapp.edit'))))) {
            abort(403, 'Unauthorized action.');
        }
        $loadWhatsAppAccounts = $this->whatsAppUtil->showWhatsAppAccounts($id);
        return view('whatsapp::whatsapp.edit')->with(compact(['loadWhatsAppAccounts']));
    }

	/**
	 * Update the specified resource in storage.
	 * @param Request $request
	 * @param int $id
	 * @return Renderable|array|int
	 */
    public function update(Request $request, int $id): Renderable|array|int
    {
        $business_id = request()->session()->get('user.business_id');
        if (!(auth()->user()->can('superadmin') || ($this->moduleUtil->hasThePermissionInSubscription($business_id, 'whatsapp_module') && (auth()->user()->can('whatsapp.update'))))) {
            abort(403, 'Unauthorized action.');
        }
        $output = '';
        if (request()->ajax()) {
            try {
                $data = $request->only(['sources','wa_server','is_default','app_key','auth_key', 'sender']);
                $this->whatsAppUtil->updateWhatsAppAccounts($data, $id);
                $msg = __('whatsapp::lang.update');
                $output = ['success' => true,
                'msg' => $msg ];
            } catch (\Exception $e) {
                \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
                
                $output = ['success' => false,
                'msg' => $e->getMessage()
                            ];
            }
            return $output;
        }
        return $output;
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return array|void
     */
    public function destroy(int $id)
    {
        $business_id = request()->session()->get('user.business_id');
        if (!(auth()->user()->can('superadmin') || ($this->moduleUtil->hasThePermissionInSubscription($business_id, 'whatsapp_module') && (auth()->user()->can('whatsapp.delete'))))) {
            abort(403, 'Unauthorized action.');
        }
        if (request()->ajax()) {
            try {
                $this->whatsAppUtil->deleteWhatsAppAccounts($id);
                $msg = __('whatsapp::lang.delete');
                $output = ['success' => true,
                'msg' => $msg ];
               
            } catch (\Exception $e) {
                \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
                
                $output = ['success' => false,
                                'msg' => __("messages.something_went_wrong")
                            ];
            }
            return $output;
        }
    }

    function deleteAccounts(int $id) {

        $business_id = request()->session()->get('user.business_id');
        if (!(auth()->user()->can('superadmin') || ($this->moduleUtil->hasThePermissionInSubscription($business_id, 'whatsapp_module') && (auth()->user()->can('whatsapp.delete'))))) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $output = '';
            try {
                DB::beginTransaction();
                $accounts = $this->whatsAppUtil->loadAccounts()->where('id', $id)->first();
                $msg = __('whatsapp::lang.delete');
                $accounts->delete();
                $output = ['success' => true,
                'msg' => $msg ];
                DB::commit();

            } catch (\Exception $e) {
                \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

                $output = ['success' => false,
                                'msg' => __("messages.something_went_wrong")
                            ];
            } catch (\Throwable $e) {
            }

            return $output;
        }
    }

    public function checkDefaultGateway(): void
    {

       if (request()->ajax()) {
        try {
            $valid = 'true';
            $accounts = $this->whatsAppUtil->checkAccountsDefaultGateway();
            if ($accounts) {
                $valid = 'false';
            }
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
        }
        echo $valid;
       }
    }

    /**
     * requestData is main logic when you send whatsapp text
     */

    public function requestData($type, $data, $isTransfer = null, $note = null) {
        try {
            if (empty($phone)) {
                $phone = $data->mobile;
            }
          return  $this->whatsAppUtil->requestData($type, $data, $isTransfer, $note);
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
        }
    }

    public function requestDataBot($type, $data, $isTransfer = null, $note = null, $phone = null) {
        try {
            return $this->whatsAppUtil->requestDataBot($type, $data, $isTransfer, $note, $phone);
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
        }
    }

    public function settings() {
        
    }

    public function settingsUpdate() {
        
    }

    public function settingsDelete() {
        
    }

    public function settingsShow() {
        
    }

    public function settingsSave() {
        
    }
}
