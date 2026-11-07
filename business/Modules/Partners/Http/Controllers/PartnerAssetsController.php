<?php

namespace Modules\Partners\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Partners\Entities\PartnerAsset;

class PartnerAssetsController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        if (!auth()->user()->can('assets.view') && !auth()->user()->can('assets.create')) {
            abort(403, 'Unauthorized action.');
        }


            $business_id = request()->session()->get('user.business_id');
            $partnerassets= PartnerAsset::where('business_id', $business_id)->get();

            $price=PartnerAsset::where('business_id', $business_id)->where('status','<',3)->sum('price');
            $curentprice=PartnerAsset::where('business_id', $business_id)->where('status','<',3)->sum('curentprice');


       return view('partners::assets.index',['partnerassets'=>$partnerassets,'price'=>$price,'curentprice'=>$curentprice]);
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {


        return view('partners::assets.create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');

        /*if (!(auth()->user()->can('superadmin'))) {
            abort(403, 'Unauthorized action.');
        }*/


        try {
            $data=PartnerAsset::create([
                'assetcode'     =>$request->assetcode,
                'description'   =>$request->description,
                'purchasedate'  =>$request->purchasedate,
                'price'         =>$request->price,
                'curentprice'   =>$request->curentprice,
                'changedate'    =>$request->changedate,
                'status'    =>$request->status,
                'business_id'   => $business_id,
                'quantity'=>$request->quantity,

            ]);

            $output = [
                'success' => true,
                'msg' => __('lang_v1.success')
            ];
        } catch (Exception $e) {

            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong')
            ];
        }

        return redirect()->action('\Modules\Partners\Http\Controllers\PartnerAssetsController@index');
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        return view('partners::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {

        if (!auth()->user()->can('assets.view') && !auth()->user()->can('assets.create')) {
            abort(403, 'Unauthorized action.');
        }
      $partnerasset= PartnerAsset::find($id);
        //dd($partnerasset);
      return view('partners::assets.edit',['partnerasset'=>$partnerasset]);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        try {
            $data = PartnerAsset::findOrFail($id);

                $data->assetcode=$request->assetcode;
                $data->description=$request->description;
                $data->purchasedate=$request->purchasedate;
                $data->price=$request->price;
                $data->curentprice=$request->curentprice;
                $data->changedate=$request->changedate;
                $data->status=$request->status;
                $data->quantity=$request->quantity;
$data->save();


            $output = [
                'success' => true,
                'msg' => __('lang_v1.success')
            ];
        } catch (Exception $e) {

            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong')
            ];
        }
        return redirect()->action('\Modules\Partners\Http\Controllers\PartnerAssetsController@index');
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {

        $partnerasset= PartnerAsset::where('id',$id)->delete();
        $output = ['success' => true,
            'msg' => 'Deleted Successfully'
        ];

        return $output;
    }
}
