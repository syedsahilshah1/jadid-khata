<?php

namespace Modules\Gym\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Utils\moduleUtil;
use App\Utils\BusinessUtil;
use App\Business;
use App\System;

class SettingController extends Controller
{
    protected $moduleUtil;
    protected $businessUtil;


    public function __construct(ModuleUtil $moduleUtil, BusinessUtil $businessUtil)
    {
        $this->moduleUtil = $moduleUtil;
        $this->businessUtil = $businessUtil;
    }

    public function index()
    {
        $business_id = request()->session()->get('user.business_id');

        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'gym_module'))) {
            abort(403, 'Unauthorized action.');
        }

        $module_version = System::getProperty('gym_version');

        $busines = Business::findOrFail($business_id);

        return view('gym::setting.setting', compact('busines', 'module_version'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('gym::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request){

        $business_id = request()->session()->get('user.business_id');
        
        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'gym_module'))) {
            abort(403, 'Unauthorized action.');
        }
        
        try {

            $business_id = session()->get('user.business_id');
            $busines = Business::findOrFail($business_id);
            $gym_settings = json_decode($busines->gym_settings, true);
            $gym_settings['gym']['address'] = $request->address;
            $gym_settings['gym']['phone'] = $request->phone;
            $gym_settings['gym']['email'] = $request->email;
            $gym_settings['gym']['website'] = $request->website;
            $busines->gym_settings = json_encode($gym_settings);
  
            $busines->update();
    
            $output = [
                'success' => 1,
                'msg' => __('lang_v1.success'),
            ];
    
            return redirect()
                ->action([\Modules\Gym\Http\Controllers\SettingController::class, 'index'])
                ->with('status', $output);
                
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = [
                'success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];

            return back()->with('status', $output)->withInput();
        }

    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('gym::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('gym::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        //
    }
}
