<?php

namespace Modules\Gym\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Gym\Entities\GymPackage;
use Yajra\DataTables\Facades\DataTables;
use App\Transaction;
use Modules\Gym\Entities\GymClass;

class PackageController extends Controller
{

    public $duration;

    public function __construct(){
        $this->duration = $durations = [
            'monthly' => __('gym::lang.monthly'),
            'quarterly' => __('gym::lang.quarterly'),
            'half-yearly' => __('gym::lang.half_yearly'),
            'yearly' => __('gym::lang.yearly'),
            'lifetime' => __('gym::lang.lifetime'),
        ];        
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        $business_id = request()->session()->get('user.business_id');

        if (request()->ajax()) {
            $packages = GymPackage::where('business_id', $business_id);
            return Datatables::of($packages)
                ->editColumn('created_at', '{{@format_datetime($created_at)}}')
                ->editColumn('amount', '<span>@format_currency($amount)')
                ->editColumn('duration', function ($row) {
                    return $this->duration[$row->duration] ?? $row->duration;
                })
                ->addColumn('action', function ($row) {
                    $html = '<a type="button" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline  tw-dw-btn-primary btn-modal-extra" href="' . action([\Modules\Gym\Http\Controllers\PackageController::class, 'edit'], ['gym_package' => $row->id]) . '">'
                        . __('messages.edit') . '</a>';
                    if(Transaction::where('gym_package_id', $row->id)->count() == 0){
                        $html .= ' <a href="' . route('delete_package', $row->id) . '"
                        class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline  tw-dw-btn-error delete_package_confirmation">' . __('messages.delete') . '</a>';    
                    }

                    return $html;
                })
                ->rawColumns(['created_at', 'action', 'amount'])
                ->make(true);
        }
        return view('gym::packages.index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        $business_id = request()->session()->get('user.business_id');
        $durations = $this->duration;
        $classes = GymClass::where('business_id', $business_id)->get();
        return view('gym::packages.create', compact('durations','classes'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {

        try {
            $input =  $request->except(['_token']);
            $input['enabled'] = $request->has('enable') ? 1 : 0;
            $input['created_by'] = auth()->user()->id;
            $input['classes'] = $request->classes ? json_encode($request->classes) : json_encode([]);
            $input['business_id'] = request()->session()->get('user.business_id');
            $package = GymPackage::create($input);

            $output = [
                'success' => 1,
                'msg' => __('lang_v1.success'),
            ];

            return redirect()
                ->action([\Modules\Gym\Http\Controllers\PackageController::class, 'index'])
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
        $business_id = request()->session()->get('user.business_id');

        $package = GymPackage::findOrFail($id);

        $durations = $this->duration;

        $classes = GymClass::where('business_id', $business_id)->get();

        return view('gym::packages.edit', compact('durations', 'package', 'classes'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        try {

            $package = GymPackage::findOrFail($id);
            $input =  $request->except(['_token']);
            $input['enabled'] = $request->has('enable') ? 1 : 0;
            $input['classes'] = $request->classes ? json_encode($request->classes) : json_encode([]);
            $input['business_id'] = request()->session()->get('user.business_id');
            $package = $package->update($input);

            $output = [
                'success' => 1,
                'msg' => __('lang_v1.success'),
            ];

            return redirect()
                ->action([\Modules\Gym\Http\Controllers\PackageController::class, 'index'])
                ->with('status', $output);
                
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];

            return back()->with('status', $output);
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        try {

            GymPackage::where('id', $id)->delete();

            $output = ['success' => 1, 'msg' => __('lang_v1.success')];
            return redirect()
                ->action([\Modules\Gym\Http\Controllers\PackageController::class, 'index'])
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
}
