@extends('layouts.app')

@section('title', 'Corrected Sales')

@section('content')
<section class="content-header">
    <h1>
        Corrected Sales
        <small>Analyze your sales and profits</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="{{ url('/') }}"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Corrected Sales</li>
    </ol>
</section>

@php
    $viewExists = \Illuminate\Support\Facades\DB::select("SHOW FULL TABLES LIKE 'corrected_profit_lines'");
@endphp

<section class="content">
    <div class="row">
        <div class="col-md-12">
            @if (!$viewExists)
                <div class="alert alert-danger ">
                    <i class="fa fa-exclamation-circle"></i> The required SQL view <strong>corrected_profit_lines</strong> is missing. Please apply the fix from the settings page.
                </div>
            @else
                @component('components.widget')
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="corrected_sales_table">
                            <thead>
                                <tr>
                                    <th>Transaction ID</th>
                                    <th>Date</th>
                                    <th>Invoice No</th>
                                    <th>Product</th>
                                    <th>Quantity Sold</th>
                                    <th>Selling Price</th>
                                    <th>Unit Cost</th>
                                    <th>Profit</th>
                                    <th>Stock Status</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                @endcomponent

                <div class="well well-sm mt-3">
                    <i class="fa fa-info-circle"></i>
                    Total Missed Profit: <span id="missed_profit" class="text-danger">Loading...</span>
                </div>
            @endif
        </div>
    </div>
</section>
@endsection

@section('javascript')

<script>
$(document).ready(function() {
    $('#corrected_sales_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('profitfix.correctedsales.data') }}',
        columns: [
            { data: 'transaction_id', name: 'transaction_id' },
            { data: 'transaction_date', name: 'transaction_date' },
            { data: 'invoice_no', name: 'invoice_no' },
            { data: 'product_name', name: 'product_name' },
            { data: 'qty_sold', name: 'qty_sold' },
            { data: 'selling_price', name: 'selling_price' },
            { data: 'unit_cost', name: 'unit_cost' },
            { data: 'profit', name: 'profit' },
            { data: 'stock_status', name: 'stock_status' }
        ]
    });

    // Fetch total missed profit based on negative stock sells
    $.ajax({
        url: '{{ route('profitfix.correctedsales.missedprofit') }}',
        method: 'GET',
        success: function(response) {
            console.log('Missed Profit Response:', response);
            if (response && typeof response.missed_profit !== 'undefined') {
                const missedProfit = parseFloat(response.missed_profit);
                if (!isNaN(missedProfit)) {
                    $('#missed_profit').text(missedProfit.toFixed(2));
                } else {
                    console.error('Invalid missed_profit value:', response.missed_profit);
                    $('#missed_profit').text('Invalid data');
                }
            } else {
                console.error('Response format invalid:', response);
                $('#missed_profit').text('No data available');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error fetching missed profit:', error);
            $('#missed_profit').text('Error fetching missed profit');
        }
    });

});
</script>
@endsection