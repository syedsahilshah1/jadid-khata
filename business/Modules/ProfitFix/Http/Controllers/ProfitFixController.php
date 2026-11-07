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

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class ProfitFixController extends Controller
{

    /**
     * Lists profit by product, category, brand, location, invoice, date, customer, day
     *
     * @return string $by = null
     */
    public function getProfit($by = null)
    {
        if (! auth()->user()->can('profit_loss_report.view')) {
            abort(403, 'Unauthorized action.');
        }

        $request = request();

        // Validate request parameters
        $request->validate([
            'location_id' => 'nullable|integer',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $business_id = $request->session()->get('user.business_id');

        // USE CORRECTED PROFIT VIEW INSTEAD OF FIFO SQL
        $query = DB::table('corrected_profit_lines')
            ->where('business_id', $business_id);

        // LOCATION PERMISSIONS
        $permitted_locations = auth()->user()->permitted_locations();
        if ($permitted_locations != 'all') {
            $query->whereIn('location_id', $permitted_locations);
        }

        // FILTER BY LOCATION
        if (! empty($request->location_id)) {
            $query->where('location_id', $request->location_id);
        }

        // FILTER BY DATE RANGE
        if (! empty($request->start_date) && ! empty($request->end_date)) {
            $query->whereDate('transaction_date', '>=', $request->start_date)
                ->whereDate('transaction_date', '<=', $request->end_date);
        }

        // GROUPING LOGIC
        if ($by == 'product') {
            $query->select(
                'product_name as product',
                DB::raw('SUM(profit) as gross_profit')
            )->groupBy('product_name');
        }

        // Additional grouping logic for category, brand, etc., can be added here

        // DATATABLE OUTPUT
        $datatable = DataTables::of($query);

        $datatable->editColumn('gross_profit', function ($row) {
            return '<span class="gross-profit" data-orig-value="'.$row->gross_profit.'">' .
                number_format($row->gross_profit, 2) .
                '</span>';
        });

        return $datatable->rawColumns(['gross_profit'])->make(true);
    }

    /**
     * Install the ProfitFix in DB
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function install()
    {
        // Run the database seeder to create the corrected_profit_lines view
        $seeder = new \Modules\ProfitFix\Database\Seeders\ProfitFixDatabaseSeeder();
        $seeder->run();

        return redirect()->back()->with('status', 'ProfitFix module installed successfully!');
    }

    /**
     * Apply the corrected_profit_lines SQL view.
     */
    public function applyFix()
    {
        try {
            DB::statement("CREATE OR REPLACE VIEW corrected_profit_lines AS
            SELECT
                ts.id AS transaction_id,
                ts.business_id,
                ts.location_id,
                ts.contact_id,
                ts.transaction_date,
                ts.invoice_no,

                -- Product / variation references
                p.id AS product_id,
                p.name AS product_name,
                p.category_id,
                p.brand_id,
                v.id AS variation_id,
                v.name AS variation_name,
                v.sub_sku,

                -- Quantity sold and selling price
                tsl.quantity AS qty_sold,
                tsl.unit_price_inc_tax AS selling_price,

                -- COST (FIFO purchase price or fallback dpp_inc_tax)
                COALESCE(pl.purchase_price_inc_tax, v.dpp_inc_tax) AS unit_cost,

                -- Negative stock detection
                CASE
                    WHEN pl.id IS NULL THEN 'Negative Stock'
                    ELSE 'Normal Stock'
                END AS stock_status,

                -- TRUE PROFIT
                (tsl.unit_price_inc_tax - COALESCE(pl.purchase_price_inc_tax, v.dpp_inc_tax))
                * tsl.quantity AS profit

            FROM transactions ts
            JOIN transaction_sell_lines tsl
            ON ts.id = tsl.transaction_id

            LEFT JOIN transaction_sell_lines_purchase_lines tslpl
            ON tslpl.sell_line_id = tsl.id

            LEFT JOIN purchase_lines pl
            ON pl.id = tslpl.purchase_line_id

            JOIN variations v
            ON v.id = tsl.variation_id

            JOIN products p
            ON p.id = v.product_id

            WHERE ts.type = 'sell'
            AND ts.status = 'final';");
            return redirect()->back()->with('status', [
                'success' => true,
                'msg' => 'Fix applied successfully!'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error applying fix: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to apply fix.');
        }
    }

    /**
     * Remove the corrected_profit_lines SQL view.
     */
    public function removeFix()
    {
        try {
            DB::statement("DROP VIEW IF EXISTS corrected_profit_lines");
            return redirect()->back()->with('status', [
                'success' => true,
                'msg' => 'Fix removed successfully!'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error removing fix: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to remove fix.');
        }
    }

    /**
     * Display the settings page for the ProfitFix module.
     *
     * @return \Illuminate\View\View
     */
    public function settings()
    {


        return view('profitfix::settings');
    }
}