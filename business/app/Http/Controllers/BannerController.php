<?php

namespace App\Http\Controllers;

use App\Banner;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Yajra\DataTables\Facades\DataTables;

class BannerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $business_id = auth()->user()->business_id; 
            $banners = Banner::where('business_id', $business_id)
                             ->orderBy('order', 'asc');
            return DataTables::eloquent($banners)
                ->addColumn('image', function ($banner) {
                    return '<img src="' . asset($banner->image) . '" width="50">';
                })
                ->addColumn('user_id', function ($log) {
                    return $log->createdBy->name ?? '';
                })
                ->addColumn('created_at', function ($banner) {
                    return $banner->created_at->format('Y-m-d H:i:s');
                })
                ->addColumn('action', function ($banner) {
                    $html = '<div class="btn-group">
                            <button type="button" class="btn btn-info dropdown-toggle btn-xs"
                                data-toggle="dropdown" aria-expanded="false">'.
                        __('business.actions').
                        '<span class="caret"></span><span class="sr-only">Toggle Dropdown
                                </span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-left" role="menu">';

                        $html .= '<li><a href="'.route('banners.edit', $banner->id).'"><i class="fas fa-edit"></i>'.__('business.edit').'</a></li>';

                        $html .= '<li><a href="'.action([\App\Http\Controllers\BannerController::class, 'destroy'], [$banner->id]).'" class="delete-sale"><i class="fas fa-trash"></i> '.__('messages.delete').'</a></li>';

                    $html .= '</ul></div>';

                    return $html;
                })
                ->editColumn('is_active', fn ($row) => $row->is_active == 1 ? __('business.is_active') : __('business.is_inactive'))
                ->rawColumns(['image','action','is_active'])
                ->make(true);
        }
        $banner=new Banner();

        return  view('banners.index',compact('banner'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $banner=new Banner();
        return view('banners.create',compact('banner'));
    }

    public function orderChange(Request $request)
    {
        $order = $request->input('order');
        foreach ($order as $key => $id) {
            Banner::where('id', $id)->update(['order' => $key + 1]);
        }
        return response()->json(['message' => 'Order updated successfully']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request): RedirectResponse
    {
        // Validate the input
        $validatedData = $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif',
            'name' => 'nullable|string|max:255',
            // 'cta_text' => [
            //     'nullable',
            //     'string',
            //     'max:255',
            //     function ($attribute, $value, $fail) use ($request) {
            //         // If `cta_text` is present, `cta` must also be present
            //         if ($value && !$request->input('cta')) {
            //             $fail(__('business.cta_required_if_cta_text'));
            //         }
            //     }
            // ],
            // 'cta' => [
            //     'nullable',
            //     'string',
            //     'max:255',
            //     function ($attribute, $value, $fail) use ($request) {
            //         // If `cta` is present, `cta_text` must also be present
            //         if ($value && !$request->input('cta_text')) {
            //             $fail(__('business.cta_text_required_if_cta'));
            //         }
            //     }
            // ],    
             'order' => 'nullable|integer',
            'is_active' => 'required',
        ]);

    
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $fileName = time() . '.' . $image->getClientOriginalExtension();
            $directory = 'imgs/banners';
    
            if (!file_exists(public_path($directory))) {
                mkdir(public_path($directory), 0777, true);
            }
    
            $image->move(public_path($directory), $fileName);
            $imageStorePath = $directory . '/' . $fileName;
        } else {
            $imageStorePath = null; 
        }
    
        $banner = new Banner();
        $banner->image = $imageStorePath;
        // $banner->name = $request->input('name');
        $banner->cta = $request->input('cta');
        // $banner->cta_text = $request->input('cta_text');
        $banner->order = $request->input('order');
        $banner->business_id =auth()->user()->business_id; 
        $banner->is_active = $request->input('is_active');
        $banner->user_id = auth()->id();
        $banner->save();
    
        $output = [
            'success' => 1,
            'msg' => __('business.upload_successfully'),
        ];
    
        // Redirect with status message
        return redirect()->route('banners.index')->with('status', $output);
    }
    

    /**
     * Display the specified resource.
     *
     * @param  \App\Banner  $banner
     * @return \Illuminate\Http\Response
     */
    public function show(Banner $banner)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Banner  $banner
     * @return \Illuminate\Http\Response
     */
    public function edit(Banner $banner)
    {
        return view('banners.edit',compact('banner'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Banner  $banner
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $banner = Banner::findOrFail($id);
    
        $validatedData = $request->validate([
            // 'name' => 'nullable|string|max:255',
            'cta' => 'nullable|string|max:255',
            'order' => 'nullable|integer',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'required',
        ]);
    
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($banner->image && file_exists(public_path($banner->image))) {
                unlink(public_path($banner->image));
            }
    
            $image = $request->file('image');
            $fileName = time() . '.' . $image->getClientOriginalExtension();
            $directory = 'imgs/banners';
            if (!file_exists(public_path($directory))) {
                mkdir(public_path($directory), 0777, true);
            }
            $image->move(public_path($directory), $fileName);
            $imageStorePath = $directory . '/' . $fileName;
            $banner->image = $imageStorePath;
        }
    
        // $banner->name = $request->input('name');
        $banner->cta = $request->input('cta');
        $banner->order = $request->input('order');
        $banner->business_id =auth()->user()->business_id; 
        $banner->is_active = $request->input('is_active');
        $banner->save();
    
        $output = ['success' => 1,
            'msg' => __('business.update_successfully'),
        ];
        return redirect()->route('banners.index')->with('status', $output);
    }
    
    public function destroy($id): RedirectResponse
    {
        $banner = Banner::findOrFail($id);
    
        // Delete the banner image from the server
        if ($banner->image && file_exists(public_path($banner->image))) {
            unlink(public_path($banner->image));
        }
    
        $banner->delete();
    
    
        $output = ['success' => 1,
            'msg' => __('business.delete_successfully'),
        ];
        return redirect()->route('banners.index')->with('status', $output);
    }
    
}
