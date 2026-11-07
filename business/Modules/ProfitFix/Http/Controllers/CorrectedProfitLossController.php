<?php
/**
 * ProfitFix Module
 * * A comprehensive module that will display corrected profit for products that have 0 purchase or negative stock.
 * * Module: ProfitFix
 * Author: [Telegram @omgklrahul]
 * Version: 1.0.0
 * * This is a free module for non commercial use.
 * * @package Modules\ProfitFix
 */
namespace Modules\ProfitFix\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class CorrectedProfitLossController extends Controller
{
    /**
     * Fetch the total missed profit based on negative stock sells for profit page.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMissedProfit()
    {
        $viewExists = DB::select("SHOW FULL TABLES LIKE 'corrected_profit_lines'");
        if (!$viewExists) {
            return response()->json(['error' => 'corrected_profit_lines view does not exist. Please apply fix from settings.'], 400);
        }

        $query = DB::table('corrected_profit_lines')->where('stock_status', 'Negative Stock');
        $missedProfit = $query->sum(DB::raw('profit * qty_sold'));
        return response()->json(['missed_profit' => $missedProfit]);
    }
    /**
     * Display the corrected profit/loss report page.
     */
    public function index(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $viewExists = DB::select("SHOW FULL TABLES LIKE 'corrected_profit_lines'");
        $business_locations = [];
        if ($viewExists) {
            $business_locations = DB::table('business_locations')
                ->where('business_id', $business_id)
                ->pluck('name', 'id');
        }
        return view('profitfix::corrected_profit_loss', compact('business_locations', 'viewExists'));
    }

    /**
     * Fetch corrected profit/loss details via AJAX.
     */
    public function getDetails(Request $request)
    {
        $viewExists = DB::select("SHOW FULL TABLES LIKE 'corrected_profit_lines'");
        if (!$viewExists) {
            return response()->json(['error' => 'corrected_profit_lines view does not exist. Please apply fix from settings.'], 400);
        }

        $business_id = $request->session()->get('user.business_id');
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');
        $location_id = $request->get('location_id');
        $query = DB::table('corrected_profit_lines')
            ->where('business_id', $business_id)
            ->when($location_id, function ($q) use ($location_id) {
                return $q->where('location_id', $location_id);
            })
            ->whereBetween('transaction_date', [$start_date, $end_date])
            ->select(
                DB::raw('SUM(profit) as total_profit'),
                DB::raw('SUM(qty_sold) as total_quantity'),
                DB::raw('SUM(selling_price * qty_sold) as total_sales')
            )
            ->first();
        return view('profitfix::partials.corrected_profit_loss_details', compact('query'))->render();
    }

    /**
     * Display the detailed profit/loss report page.
     */
    public function detailedIndex()
    {
        $business_id = session('user.business_id');

        $business_locations = DB::table('business_locations')
            ->where('business_id', $business_id)
            ->pluck('name', 'id');

        return view('profitfix::detailed_profit_loss', compact('business_locations'));
    }

    /**
     * Fetch detailed profit/loss data via AJAX.
     */
    public function getDetailedData(Request $request)
    {
        $viewExists = DB::select("SHOW FULL TABLES LIKE 'corrected_profit_lines'");
        if (!$viewExists) {
            return response()->json(['error' => 'corrected_profit_lines view does not exist. Please apply fix from settings.'], 400);
        }
        $business_id = session('user.business_id');
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');
        $location_id = $request->get('location_id');
        $query = DB::table('corrected_profit_lines')
            ->where('business_id', $business_id)
            ->when($location_id, function ($q) use ($location_id) {
                return $q->where('location_id', $location_id);
            })
            ->whereBetween('transaction_date', [$start_date, $end_date])
            ->select(
                'transaction_date',
                'invoice_no',
                'product_name',
                'qty_sold',
                'selling_price',
                'unit_cost',
                'profit',
                'stock_status'
            )
            ->get();
        return response()->json(['data' => $query]);
    }

    /**
     * AJAX: Return grouped profit data for each tab.
     */
    public function getTabData(Request $request)
    {
        $viewExists = DB::select("SHOW FULL TABLES LIKE 'corrected_profit_lines'");
        if (!$viewExists) {
            return response()->json(['error' => 'corrected_profit_lines view does not exist. Please apply fix from settings.'], 400);
        }
        $business_id = $request->session()->get('user.business_id');
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');
        $location_id = $request->get('location_id');
        $tab = $request->get('tab');
        $query = DB::table('corrected_profit_lines')
            ->where('business_id', $business_id)
            ->when($location_id, function ($q) use ($location_id) {
                return $q->where('location_id', $location_id);
            })
            ->whereBetween('transaction_date', [$start_date, $end_date]);
        switch ($tab) {
            case 'product':
                $data = $query->select('product_name', DB::raw('SUM(profit) as gross_profit'))
                    ->groupBy('product_name')->get();
                break;
            case 'category':
                $data = $query->select('category_id', DB::raw('SUM(profit) as gross_profit'))
                    ->groupBy('category_id')->get();
                break;
            case 'brand':
                $data = $query->select('brand_id', DB::raw('SUM(profit) as gross_profit'))
                    ->groupBy('brand_id')->get();
                break;
            case 'location':
                $data = $query->select('location_id', DB::raw('SUM(profit) as gross_profit'))
                    ->groupBy('location_id')->get();
                break;
            case 'invoice':
                $data = $query->select('invoice_no', DB::raw('SUM(profit) as gross_profit'))
                    ->groupBy('invoice_no')->get();
                break;
            case 'date':
                $data = $query->select(DB::raw('DATE(transaction_date) as date'), DB::raw('SUM(profit) as gross_profit'))
                    ->groupBy(DB::raw('DATE(transaction_date)'))->get();
                break;
            case 'customer':
                $data = $query->select('contact_id', DB::raw('SUM(profit) as gross_profit'))
                    ->groupBy('contact_id')->get();
                break;
            case 'day':
                $data = $query->select(DB::raw('DAYNAME(transaction_date) as day'), DB::raw('SUM(profit) as gross_profit'))
                    ->groupBy(DB::raw('DAYOFWEEK(transaction_date)'))->get();
                break;
            default:
                $data = collect();
        }
        return response()->json(['data' => $data]);
    }

    // Profit by products
    public function getProfitByProduct(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');
        $location_id = $request->get('location_id');
        $query = \DB::table('corrected_profit_lines')
            ->where('business_id', $business_id)
            ->when($location_id, function ($q) use ($location_id) {
                return $q->where('location_id', $location_id);
            })
            ->whereBetween('transaction_date', [$start_date, $end_date])
            ->select('product_name as product', \DB::raw('SUM(profit) as gross_profit'))
            ->groupBy('product_name');
        return \Datatables::of($query)
            ->editColumn('gross_profit', function ($row) {
                return '<span class="gross-profit" data-orig-value="'.$row->gross_profit.'">'.number_format($row->gross_profit, 2).'</span>';
            })
            ->rawColumns(['gross_profit'])
            ->make(true);
    }

    // Profit by categories
    public function getProfitByCategory(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');
        $location_id = $request->get('location_id');
        $query = \DB::table('corrected_profit_lines')
            ->leftJoin('categories as C', 'C.id', '=', 'category_id')
            ->where('corrected_profit_lines.business_id', $business_id)
            ->when($location_id, function ($q) use ($location_id) {
                return $q->where('location_id', $location_id);
            })
            ->whereBetween('transaction_date', [$start_date, $end_date])
            ->select(\DB::raw('COALESCE(C.name, "Uncategorized") AS category'), \DB::raw('SUM(profit) as gross_profit'))
            ->groupBy('category_id');
        return \Datatables::of($query)
            ->editColumn('gross_profit', function ($row) {
                return '<span class="gross-profit" data-orig-value="'.$row->gross_profit.'">'.number_format($row->gross_profit, 2).'</span>';
            })
            ->rawColumns(['gross_profit'])
            ->make(true);
    }

    // Profit by brands
    public function getProfitByBrand(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');
        $location_id = $request->get('location_id');
        $query = \DB::table('corrected_profit_lines')
            ->leftJoin('brands as B', 'B.id', '=', 'brand_id')
            ->where('corrected_profit_lines.business_id', $business_id)
            ->when($location_id, function ($q) use ($location_id) {
                return $q->where('location_id', $location_id);
            })
            ->whereBetween('transaction_date', [$start_date, $end_date])
            ->select(\DB::raw('COALESCE(B.name, "Others") AS brand'), \DB::raw('SUM(profit) as gross_profit'))
            ->groupBy('brand_id');
        return \Datatables::of($query)
            ->editColumn('gross_profit', function ($row) {
                return '<span class="gross-profit" data-orig-value="'.$row->gross_profit.'">'.number_format($row->gross_profit, 2).'</span>';
            })
            ->rawColumns(['gross_profit'])
            ->make(true);
    }

    // Profit by locations
    public function getProfitByLocation(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');
        $location_id = $request->get('location_id');
        $query = \DB::table('corrected_profit_lines')
            ->join('business_locations as L', 'L.id', '=', 'corrected_profit_lines.location_id')
            ->where('corrected_profit_lines.business_id', $business_id)
            ->when($location_id, function ($q) use ($location_id) {
                return $q->where('corrected_profit_lines.location_id', $location_id);
            })
            ->whereBetween('transaction_date', [$start_date, $end_date])
            ->select('L.name as location', \DB::raw('SUM(profit) as gross_profit'))
            ->groupBy('corrected_profit_lines.location_id');
        return \Datatables::of($query)
            ->editColumn('gross_profit', function ($row) {
                return '<span class="gross-profit" data-orig-value="'.$row->gross_profit.'">'.number_format($row->gross_profit, 2).'</span>';
            })
            ->rawColumns(['gross_profit'])
            ->make(true);
    }

// Profit by invoice
public function getProfitByInvoice(Request $request)
{
    $business_id = $request->session()->get('user.business_id');
    $start_date = $request->get('start_date');
    $end_date = $request->get('end_date');
    $location_id = $request->get('location_id');
    $query = \DB::table('corrected_profit_lines')
        ->select('invoice_no',
            \DB::raw('SUM(profit) as gross_profit'),
            \DB::raw('MAX(stock_status) as stock_status')
        )
        ->where('business_id', $business_id)
        ->when($location_id, function ($q) use ($location_id) {
            return $q->where('location_id', $location_id);
        })
        ->whereBetween('transaction_date', [$start_date, $end_date])
        ->groupBy('invoice_no');
    return \Datatables::of($query)
        ->addColumn('gross_profit_html', function ($row) {
            $class = ($row->stock_status === 'negative') ? 'text-danger' : '';
            $formattedValue = number_format($row->gross_profit, 2);
            return '<span class="gross-profit '.$class.'" data-orig-value="'.$row->gross_profit.'">'.$formattedValue.'</span>';
        })
        ->editColumn('stock_status', function ($row) {
            if ($row->stock_status === 'negative') {
                return 'Negative Stock';
            }
            return $row->stock_status ?? '-';
        })
        ->rawColumns(['gross_profit_html','stock_status'])
        ->make(true);
}

    // Profit by date
    public function getProfitByDate(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');
        $location_id = $request->get('location_id');
        $query = \DB::table('corrected_profit_lines')
            ->where('business_id', $business_id)
            ->when($location_id, function ($q) use ($location_id) {
                return $q->where('location_id', $location_id);
            })
            ->whereBetween('transaction_date', [$start_date, $end_date])
            ->select(\DB::raw('DATE(transaction_date) as transaction_date'), \DB::raw('SUM(profit) as gross_profit'))
            ->groupBy(\DB::raw('DATE(transaction_date)'));
        return \Datatables::of($query)
            ->editColumn('gross_profit', function ($row) {
                return '<span class="gross-profit" data-orig-value="'.$row->gross_profit.'">'.number_format($row->gross_profit, 2).'</span>';
            })
            ->rawColumns(['gross_profit'])
            ->make(true);
    }

    // Profit by customer
    public function getProfitByCustomer(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');
        $location_id = $request->get('location_id');
        $query = \DB::table('corrected_profit_lines')
            ->join('contacts as CU', 'CU.id', '=', 'corrected_profit_lines.contact_id')
            ->where('corrected_profit_lines.business_id', $business_id)
            ->when($location_id, function ($q) use ($location_id) {
                return $q->where('location_id', $location_id);
            })
            ->whereBetween('transaction_date', [$start_date, $end_date])
            ->select('CU.name as customer', 'CU.supplier_business_name', \DB::raw('SUM(profit) as gross_profit'))
            ->groupBy('corrected_profit_lines.contact_id');
        return \Datatables::of($query)
            ->editColumn('customer', '@if(!empty($supplier_business_name)) {{$supplier_business_name}}, <br> @endif {{$customer}}')
            ->editColumn('gross_profit', function ($row) {
                return '<span class="gross-profit" data-orig-value="'.$row->gross_profit.'">'.number_format($row->gross_profit, 2).'</span>';
            })
            ->rawColumns(['gross_profit', 'customer'])
            ->make(true);
    }

    // Profit by day
    public function getProfitByDay(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');
        $location_id = $request->get('location_id');
        $query = \DB::table('corrected_profit_lines')
            ->where('business_id', $business_id)
            ->when($location_id, function ($q) use ($location_id) {
                return $q->where('location_id', $location_id);
            })
            ->whereBetween('transaction_date', [$start_date, $end_date])
            ->select(\DB::raw('DAYNAME(transaction_date) as day'), \DB::raw('SUM(profit) as gross_profit'))
            ->groupBy(\DB::raw('DAYOFWEEK(transaction_date)'));
        return \Datatables::of($query)
            ->editColumn('gross_profit', function ($row) {
                return '<span class="gross-profit" data-orig-value="'.$row->gross_profit.'">'.number_format($row->gross_profit, 2).'</span>';
            })
            ->rawColumns(['gross_profit'])
            ->make(true);
    }
}