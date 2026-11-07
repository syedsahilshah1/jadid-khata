<table align="center" style="border-spacing: {{$barcode_details->col_distance * 1}}in {{$barcode_details->row_distance * 1}}in; overflow: hidden !important;">
@foreach($page_products as $page_product)

    @if($loop->index % $barcode_details->stickers_in_one_row == 0)
        <tr>
    @endif
    <td align="center" valign="center">
    <div style="width: 2.6in; height: 1.6in; padding: 0.1in; font-family: Arial, sans-serif; position: relative; border: 1px dotted lightgray; box-sizing: border-box; display: flex;">
        
        <!-- Left Section (70%) -->
        <div style="width: 70%; display: flex; flex-direction: column; justify-content: space-between;">
            <!-- Top Content -->
            <div>
                <!-- Brand Name -->
                <div style="font-size: 14px; font-weight: bold; text-align: left; margin-bottom: 0.05in;">
                    @if(!empty($print['business_name']))
                        {{$business_name}}
                    @endif
                </div>
                
                <!-- Date -->
                <div style="font-size: 11px; text-align: left; margin-bottom: 0.1in;">
                    Date: {{$page_product->packing_date}}
                </div>
                
                <!-- Barcode -->
                <div style="text-align: center; margin: 0.05in 0;">
                    <img src="data:image/png;base64,{{ DNS1D::getBarcodePNG($page_product->sub_sku, 'C128', 1, 35, [0, 0, 0], false) }}"
                         style="max-width: 100%; height: 0.45in;">
                </div>
                
                <!-- Barcode Text -->
                <div style="text-align: center; font-size: 10px; margin-bottom: 0.05in;">
                    {{$page_product->sub_sku}}
                </div>
            </div>
            
            <!-- Product Name (Bottom) -->
            <div style="font-size: 16px; font-weight: bold; text-align: left; margin-top: auto;">
                {{$page_product->product_actual_name}}
            </div>
        </div>
        
        <!-- Right Section (30%) -->
        <div style="width: 30%; display: flex; flex-direction: column; justify-content: space-between; align-items: flex-end;">
            <!-- Top Content -->
            <div>
                <!-- Price -->
                <div style="font-size: 14px; font-weight: bold; text-align: right; margin-bottom: 0.1in;">
                    Price: 
                    @if($print['price_type'] == 'inclusive')
                        {{@num_format($page_product->sell_price_inc_tax)}}
                    @else
                        {{@num_format($page_product->default_sell_price)}}
                    @endif
                </div>
            </div>
            
            <!-- Bottom Content -->
            <div style="margin-top: auto;">
                <!-- Size -->
                <div style="font-size: 38px; font-weight: bold; line-height: 1;">
                    40
                </div>
                
                <!-- Type -->
                <div style="font-size: 11px; text-align: right; margin-top: 0.05in;">
                    Peshawri
                </div>
            </div>
        </div>
    </div>
</td>

    @if($loop->iteration % $barcode_details->stickers_in_one_row == 0)
        </tr>
    @endif
@endforeach
</table>

<style type="text/css">
    td {
        border: 1px dotted lightgray;
    }
    @media print {
        table {
            page-break-after: always;
        }
        @page {
            size: {{$paper_width}}in {{$paper_height}}in;
            margin-top: {{$margin_top}}in !important;
            margin-bottom: {{$margin_top}}in !important;
            margin-left: {{$margin_left}}in !important;
            margin-right: {{$margin_left}}in !important;
        }
    }
</style>