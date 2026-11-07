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
use Illuminate\Routing\Controller;
use Yajra\DataTables\DataTables;
use DB;

class CorrectedSalesController extends Controller
{
    /**
     * Display a listing of the sales based on corrected_profit_lines view.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('profitfix::corrected_sales');
    }

    /**
     * Fetch sales data for DataTables.
     *
     * @return \Yajra\DataTables\DataTables|\Illuminate\Http\JsonResponse
     */
    public function getSalesData()
    {
        $viewExists = DB::select("SHOW FULL TABLES LIKE 'corrected_profit_lines'");

        if (empty($viewExists)) {
            return response()->json([
                'error' => 'The required SQL view corrected_profit_lines is missing. Please apply the fix from the settings page.'
            ], 400);
        }

        $query = DB::table('corrected_profit_lines')
            ->select(
                'transaction_id',
                'transaction_date',
                'invoice_no',
                'product_name',
                'qty_sold',
                'selling_price',
                'unit_cost',
                'profit',
                'stock_status'
            );

        return DataTables::of($query)
            ->editColumn('transaction_date', '{{ @format_date($transaction_date) }}')
            ->editColumn('profit', function ($row) {
                return '<span class="text-' . ($row->profit < 0 ? 'danger' : 'success') . '">' . number_format($row->profit, 2) . '</span>';
            })
            ->rawColumns(['profit'])
            ->make(true);
    }

    /**
     * Fetch the total missed profit based on negative stock sells.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMissedProfit()
    {
        $viewExists = DB::select("SHOW FULL TABLES LIKE 'corrected_profit_lines'");

        if (empty($viewExists)) {
            return response()->json([
                'error' => 'The required SQL view corrected_profit_lines is missing. Please apply the fix from the settings page.'
            ], 400);
        }

        $query = DB::table('corrected_profit_lines')->where('stock_status', 'Negative Stock');

        $missedProfit = $query->sum(DB::raw('profit * qty_sold'));


        return response()->json(['missed_profit' => $missedProfit]);
    }
}