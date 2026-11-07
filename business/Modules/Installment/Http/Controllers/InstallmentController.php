<?php

namespace Modules\Installment\Http\Controllers;

use App\Account;
use App\Business;
use App\Contact;
use App\InvoiceLayout;
use App\AccountTransaction;
use App\Transaction;
use App\TransactionPayment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use DB;
use Modules\Installment\Entities\installmentdb;
use Modules\Installment\Entities\Installments;
use Yajra\DataTables\DataTables;
use App\Utils\ModuleUtil;
use App\Utils\TransactionUtil;

class InstallmentController extends Controller
{
    protected $transactionUtil;
    protected $moduleUtil;

    /**
     * Constructor
     *
     * @param TransactionUtil $transactionUtil
     * @return void
     */
    public function __construct(TransactionUtil $transactionUtil, ModuleUtil $moduleUtil)
    {
        $this->transactionUtil = $transactionUtil;
        $this->moduleUtil = $moduleUtil;
    }

    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index(Request $request)
    {
        $business_id=auth()->user()->business_id;
        $customers=Contact::where('business_id','=',$business_id)->where('type','!=','supplier')->where('created_by',session('user.id'))->pluck('name','id');
        $customers->prepend(__('lang_v1.select_all'), '0');
     return view('installment::reports.index',['customers'=>$customers,'installment_id'=>$request->id]);
    }



    public function instalments(Request $request){
        $business_id = request()->session()->get('user.business_id');


        $installments =Installments::where('installments.business_id', $business_id)
        ->join('transactions as t','t.id','installments.transaction_id')
              ->where(function ($query) use($request){
                  if($request->id>0)
                      $query->where('installments.contact_id','=',$request->id);


                  if($request->installment_id)
                      $query->where('installment_id','=',$request->installment_id);

                if($request->installment_status==1)
                    $query->where('payment_id','>',0);

                if($request->installment_status==2){
                    $query->where('installmentdate','>=',Carbon::now()->addDays(-1));
                    $query->where('payment_id','=',0);
                }
                $is_admin = auth()->user()->hasRole('Admin#' . session('business.id')) ? true : false;
                if(!$is_admin)
                    $query->where('t.created_by','=',session('user.id'));

                      if ($request->installment_status == 3) {
                          $query->where('installmentdate', '<', Carbon::now()->addDays(-1));
                          $query->where('payment_id', '=', 0);
                      }




                  if(!$request->installment_id) {
                      if ($request->datefrom && $request->dateto)
                          $query->whereBetween('installmentdate', [$request->datefrom, $request->dateto]);
                  }
            })
            ->join('contacts','installments.contact_id','=','contacts.id')
            ->select(['payment_id','installment_number','contacts.name','installmentdate','installment_value','benefit_value',DB::raw('installment_value+benefit_value as total_value'), 'paid_date', 'installments.id'])
            ->orderby('installmentdate');

        return DataTables::of($installments)
             ->editcolumn('paid_date',function ($row) {
                  $daylats=0;
                $currentdat=Carbon::now();

                 $daylats=Carbon::parse($row->installmentdate)->diffInDays($currentdat);
                 if(Carbon::parse($row->installmentdate)>$currentdat)
                    

                 $monthlats=Carbon::parse($row->installmentdate)->diffInMonths($currentdat);
                 $yearlats=Carbon::parse($row->installmentdate)->diffInYears($currentdat);

                 if($daylats>0 && $row->payment_id==0 && $row->installmentdate<date("Y-m-d")){
                        $st='Delayed';
                        return $daylats;
                }else{
                    return '-';
                }

                
            })
            ->addColumn('payment_status',function ($row){
                 $daylats=0;
                if($row->payment_id>0)
                     $st='Paid';


                if($daylats>0 && $row->payment_id==0)
                        $st='Late';



                 if($daylats==0 && $row->payment_id==0)
                            $st='Due';
                            
                            
                            return $st;

            })
            ->addColumn(
                'latdays',function ($row){
                        return $row->paid_date;
            }
            )
            ->addColumn(
                'action',

                '<button type="button" class="btn btn-xs btn-primary" onclick="tprint({{$id}})"  >
                                    <i class="fa fa-print"></i> @lang( \'messages.print\' )</button>
                     @if($payment_id==0)
                       @can("installment.add_Collection")
                        <button data-href="{{action(\'\Modules\Installment\Http\Controllers\InstallmentController@addpayment\', [$id])}}" class="btn btn-xs btn-primary add_payment"><i class="glyphicon glyphicon-edit"></i> @lang("installment::lang.pebt_Collection")</button>
                            &nbsp;
                        @endcan
                    @endif
                    @if($payment_id>0)
                       @can("installment.delete_Collection")
                        <button data-href="{{action(\'\Modules\Installment\Http\Controllers\InstallmentController@paymentdelete\', [$id])}}" class="btn btn-xs  btn-warning paymentdelete"><i class="glyphicon glyphicon-edit"></i> @lang("installment::lang.de1_Collection")</button>
                            &nbsp;
                        @endcan
                    @endif
                    @can("installment.delete")
                        <button data-href="{{action(\'\Modules\Installment\Http\Controllers\InstallmentController@installmentdelete\', [$id])}}" class="btn btn-xs btn-danger installmentdelete"><i class="glyphicon glyphicon-trash"></i> @lang("messages.delete")</button>
                    @endcan'
            )

            ->removeColumn('id')
            ->removeColumn('payment_id')
            ->rawColumns([9])
            ->make(false);
    }

public function installments_for_home(Request $request){
    
        $business_id = request()->session()->get('user.business_id');


        $installments =Installments::where('installments.business_id', $business_id)
        ->join('transactions as t','t.id','installments.transaction_id')
              ->where(function ($query) use($request){
                  if($request->id>0)
                      $query->where('installments.contact_id','=',$request->id);


                  if($request->installment_id)
                      $query->where('installment_id','=',$request->installment_id);

                if($request->installment_status==1)
                    $query->where('payment_id','>',0);

                if($request->installment_status==2){
                    $query->where('installmentdate','>=',Carbon::now()->addDays(-1));
                    $query->where('payment_id','=',0);
                }
                $is_admin = auth()->user()->hasRole('Admin#' . session('business.id')) ? true : false;
                if(!$is_admin)
                    $query->where('t.created_by','=',session('user.id'));

                      if ($request->installment_status == 3) {
                          $query->where('installmentdate', '<', Carbon::now()->addDays(-1));
                          $query->where('payment_id', '=', 0);
                      }


                $query->whereNull('paid_date');
                $query->where('installmentdate','<=',date("Y-m-d"));
                  if(!$request->installment_id) {
                      if ($request->datefrom && $request->dateto)
                          $query->whereBetween('installmentdate', [$request->datefrom, $request->dateto]);
                  }
            })
            ->join('contacts','installments.contact_id','=','contacts.id')
            ->leftJoin('customer_groups as cg','cg.id','contacts.customer_group_id')
            ->select(['payment_id','installment_number','contacts.name','cg.name as cg_name','installmentdate','installment_value','benefit_value',DB::raw('installment_value+benefit_value as total_value'), 'installments.id']);
            //->orderby('installmentdate');

        return DataTables::of($installments)
             
            ->addColumn(
                'installmentdate_late',function ($row){
                  
                        return $this->cal_days_diff($row->installmentdate,date("Y-m-d"));
                   
                    
            }
            )
            

            ->removeColumn('id')
            ->removeColumn('payment_id')
            ->removeColumn('installment_value')
            ->removeColumn('benefit_value')
            ->rawColumns([4])
            ->make(false);
    
}
    public function cal_days_diff($first_date,$second_date){
        $date1 = new \DateTime($first_date);
        $date2 = new \DateTime($second_date);
        $interval = $date1->diff($date2);
       // echo "difference " . $interval->y . " years, " . $interval->m." months, ".$interval->d." days "; 
    
    // shows the total amount of days (not divided into years, months and days like above)
       return $interval->days;
    }
    /*
     * show payment.blade.php
     * */
    public function addpayment(Request $request){

       $data=Installments::where('id','=',$request->id)->first();

        $currentdat=Carbon::now();
        $daylats=0;
        $latfines_value=0;

        $contact=Contact::where('id','=',$data->contact_id)->first();

        if(Carbon::parse($data->installmentdate)<$currentdat){
           $daylats=Carbon::parse($data->installmentdate)->diffInDays($currentdat);
           $monthlats=Carbon::parse($data->installmentdate)->diffInMonths($currentdat);
           $yearlats=Carbon::parse($data->installmentdate)->diffInYears($currentdat);

            $installment_value=$data->installment_value;
            $latfines=$data->latfines;
            $latfinestype=$data->latfinestype;

            if($latfinestype=='day'){
                $latfines_value= $installment_value*$latfines*$daylats/100;
                $daylats= $daylats;
            }

            if($latfinestype=='month'){
                $latfines_value= $installment_value*$latfines* $monthlats/100;
                $daylats= $monthlats;
            }

            if($latfinestype=='year'){
                $latfines_value= $installment_value*$latfines*$yearlats/100;
                $daylats= $yearlats;
            }
        }

        $business_id=auth()->user()->business_id;
        $accounts =Account::where('business_id', $business_id)->pluck('name','id');

    /*   $accounts->prepend(__('lang_v1.select_all'), '0');*/


        return view('installment::customer.payment',['data'=>$data,'daylats'=>$daylats,'latfines_value'=>$latfines_value,'accounts'=>$accounts,'contact'=>$contact]);
    }

public function storepayment(Request $request){


        $business_id=auth()->user()->business_id;

try{
    DB::beginTransaction();
        // get transaction_id
        $currDate=date("Y-m-d");
        $newDate="";
        if($request->installmentdate != ""){
            $newDate = $request->installmentdate;
        }
        $transaction_id=Installments::select('transaction_id','installment_value','installment_id','benefit_value')->where('id',$request->installment_id)->first();
        $result=[
            'success'=>true,
            'msg'=>'Added Successfully'
        ];
        if($request->totallpaid > 0){
            $payment=TransactionPayment::create([
                     'business_id'=>$business_id,
                     'transaction_id'=>$transaction_id->transaction_id,
                     'is_return'=>0,
                     'is_advance'=>0,
                     'payment_for'=>$request->contact_id,
                     'amount'=>$request->totallpaid,
                     'paid_on'=>$currDate,
                     'method'=>'cash',
                     'account_id'=>$request->account_id,
                     'created_by'=>auth()->user()->id,
                     'note'=>'Installment value without interest'
                 ]);
      
             $credit_data = [
                         'amount'            => $request->totallpaid,
                         'account_id'        => $request->account_id,
                         'type'              => 'credit',
                         'transaction_payment_id'=>$payment->id,
                         'operation_date'    =>$currDate,
                         'created_by'        => session()->get('user.id'),
                         'note'              => "Installment Interest + Payment"
                     ];
                    
             $credit = AccountTransaction::createAccountTransaction($credit_data);
     
             // update installment with payment_id
             $insatllment=Installments::findorfail($request->installment_id);
             $insatllment->payment_id=$payment->id;
             $insatllment->paid_date=$currDate;
             $insatllment->latfines_value=$request->latfines;
             $insatllment->save();
            $this->updateinstallmentdb($transaction_id->installment_id);
             
     
             if($request->totallpaid > ($transaction_id->installment_value +  $transaction_id->benefit_value)){
                 $data = DB::select('select * from installments where installment_id=?  and payment_id = ? ORDER BY installmentdate',[$transaction_id->installment_id,'0']);
                 $nn = 0;
                 
                 foreach($data as $d){
                     if($nn == 0 && $newDate != ""){
                         $newIdFist = $d->id;
                         $nextNewDate = $request->installmentdate;
                         DB::update('update installments set installmentdate = ? where id = ?',[$nextNewDate,$newIdFist]);    
                     }
                     $nn=$nn+1;
                 }
                 
                 $exess_amount = ($request->totallpaid - ($transaction_id->installment_value +  $transaction_id->benefit_value)) * 1.0;
                 $amount_to = ($exess_amount / $nn)*1.0;
                 $new_amount = (($transaction_id->installment_value) - $amount_to);
     
                 DB::update('update installments set installment_value = ? where installment_id = ? and payment_id=?',[$new_amount,$transaction_id->installment_id,'0']);
     
             }
             if($request->totallpaid < ($transaction_id->installment_value +  $transaction_id->benefit_value)){
                 $data = DB::select('select * from installments where installment_id=?  and payment_id = ? ORDER BY installmentdate',[$transaction_id->installment_id,'0']);
                 $nn = 0;
                 foreach($data as $d){
                     if($nn == 0 && $newDate != ""){
                         $newIdFist = $d->id;
                         $nextNewDate = $request->installmentdate;
                         DB::update('update installments set installmentdate = ? where id = ?',[$nextNewDate,$newIdFist]);  
                     }
                     $nn=$nn+1;
                 }
                 
                 $exess_amount = (  ($transaction_id->installment_value +  $transaction_id->benefit_value) - $request->totallpaid) * 1.0;
                 $amount_to = ($exess_amount / $nn)*1.0;
                 $new_amount = (($transaction_id->installment_value) + $amount_to);
     
                 DB::update('update installments set installment_value = ? where installment_id = ? and payment_id=?',[$new_amount,$transaction_id->installment_id,'0']);
     
             
             }
             if($request->totallpaid == ($transaction_id->installment_value +  $transaction_id->benefit_value)){
                 $data = DB::select('select * from installments where installment_id=?  and payment_id = ? ORDER BY installmentdate',[$transaction_id->installment_id,'0']);
                 $nn = 0;
                 foreach($data as $d){
                     if($nn == 0 && $newDate != ""){
                         $newIdFist = $d->id;
                         $nextNewDate = $request->installmentdate;
                         DB::update('update installments set installmentdate = ? where id = ?',[$nextNewDate,$newIdFist]);    
                     }
                     $nn=$nn+1;
                 }
                 
                 
             
             }
              DB::update('update installments set installment_value = ? where id = ?',[($request->totallpaid-$transaction_id->benefit_value),$request->installment_id]);
             \DB::commit();  
        }else{
            $data = DB::select('select * from installments where installment_id=?  and payment_id = ? ORDER BY installmentdate',[$transaction_id->installment_id,'0']);
                 $nn = 0;
                 foreach($data as $d){
                     if($nn == 0 && $newDate != ""){
                         $newIdFist = $d->id;
                         $nextNewDate = $request->installmentdate;
                         DB::update('update installments set installmentdate = ? where id = ?',[$nextNewDate,$newIdFist]);  
                     }
                     $nn=$nn+1;
                 }  
                 \DB::commit();    
        }

       
    }catch(\Exception $e){
        DB::rollback();
        $result=[
                'success'=>false,
                'msg'=>$e->getMessage()."Line : ".$e->getLine()
            ];
    }
        return $result;
    }
    public function installmentdelete(Request $request){

        if (!auth()->user()->can('installment.delete')) {
            $output = [ 'success' => false,
                'msg' =>'Permission Denied'
            ];
            return $output;
        }
        $data=Installments::where('id','=',$request->id)->first();
        $payment=TransactionPayment::where('id','=',$data->payment_id)->delete();
        Installments::where('id','=',$request->id)->delete();
        
        
          

            $data->delete();
            $result=[
                'success'=>true,
                'msg'=>__('installment:lang.deleted_success')
            ];
            return $result;



    }
   
    public function paymentdelete(Request $request){

        if (!auth()->user()->can('installment.delete_Collection')) {
            $output = [ 'success' => false,
                'msg' =>'Permission Denied'
            ];
            return $output;
        }
       
        $data=Installments::where('id','=',$request->id)->first();
         $transaction_payment_id=$data->payment_id;
        TransactionPayment::where('id','=',$data->payment_id)->delete();

        $data->payment_id=0;
        $data->paid_date='NULL';
        $data->save();

  $ac_tr=AccountTransaction::where('transaction_payment_id',$transaction_payment_id)->delete(); //->update(['deleted_at'=>date("Y-m-d H:i:s")]);
        $this->updateinstallmentdb($data->installment_id);
        $result=[
            'success'=>true,
            'msg'=>__('installment:lang.deleted_success')
        ];
        return $result;



    }
    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
    public function show($id)
    {

    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {

    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }

    public function system(){

       return view('installment::systems.index');
    }

    public function system_create(){
        return view('installment::systems.create');
    }


    public function updateinstallmentdb($id){
   $paidnumber=Installments::where('installment_id','=',$id)->where('payment_id','>',0)->count();

        // installment
        $installmentdb=installmentdb::findorfail($id);
        $installmentdb->paidnumber=$paidnumber;
        $installmentdb->save();

        }


   public function printinstallment($id){

        $receipt_details=Installments::findorfail($id);
        $client=Contact::find( $receipt_details->contact_id);

        $business_id=auth()->user()->business_id;
        $business=Business::find($business_id);
        $logo = InvoiceLayout::find($business_id);
       $receipt_details['logo'] = !empty( $logo->logo) && file_exists(public_path('uploads/invoice_logos/' .  $logo->logo)) ? asset('uploads/invoice_logos/' .  $logo->logo) : false;

       $receipt_details['total']= $receipt_details->installment_value + $receipt_details->benefit_value + $receipt_details->latfines_value;
       $receipt_details['business_name'] =$business->name;

        $installments=Installments::where('installment_id','=',$receipt_details->installment_id)->get();


        $html=view( 'installment::print.installment', compact(['receipt_details','client','installments']))->render();

        return $html;
   }

public function business(){
        $data=Business::select('business.id','business.name',DB::raw('count(products.id) as total'))->join('products','business.id','=','products.business_id')->groupby('business.id')->get();
        return $data;
}
}
