<?php

namespace Modules\ProfitFix\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProfitFixDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create or replace the corrected_profit_lines view
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
                p.name AS product_name || ' (Fixed)' AS product_name, -- Visual difference
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
    }
}
