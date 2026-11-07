@extends('layouts.app')
@section('title', 'Corrected Profit/Loss Report')

@section('content')
    <section class="content-header">
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">Corrected Profit/Loss Report</h1>
    </section>
    @php
        $viewExists = \Illuminate\Support\Facades\DB::select("SHOW FULL TABLES LIKE 'corrected_profit_lines'");
    @endphp
    <section class="content">
        @if (!$viewExists)
            <div class="alert alert-danger ">
<i class="fa fa-exclamation-circle"></i> The required SQL view <strong>corrected_profit_lines</strong> is missing. Please apply the fix from the settings page.            </div>
        @else
            @component('components.widget')
                <div class="print_section">
                    <h2>{{ session()->get('business.name') }} - Corrected Profit/Loss</h2>
                </div>
                <div class="row no-print">
                    <div class="col-md-4 col-xs-12">
                        <div class="input-group">
                            <span class="input-group-addon bg-light-blue"><i class="fa fa-map-marker"></i></span>
                            <select class="form-control select2" id="corrected_profit_loss_location_filter">
                                @foreach ($business_locations as $key => $value)
                                    <option value="{{ $key }}">{{ $value }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4 col-xs-12">
                        <div class="form-group">
                            <div class="input-group">
                                <button type="button" class="tw-dw-btn tw-dw-btn-primary tw-text-white tw-dw-btn-sm" id="corrected_profit_loss_date_filter">
                                    <span>
                                        <i class="fa fa-calendar"></i> Filter by date
                                    </span>
                                    <i class="fa fa-caret-down"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-xs-12"></div>
                    <button class="tw-dw-btn tw-dw-btn-primary tw-text-white tw-dw-btn-sm">
                        <i class="fa fa-info-circle"></i>
                        Total Missed Profit: <span id="missed_profit" class="text-danger">Loading...</span>
                    </button>
                </div>
                <div class="row no-print">
                    <div class="col-md-12">
                        <div class="nav-tabs-custom">
                            <ul class="nav nav-tabs">
                                <li class="active"><a href="#corrected_profit_by_products" data-toggle="tab" aria-expanded="true">Profit by products</a></li>
                                <li><a href="#corrected_profit_by_categories" data-toggle="tab" aria-expanded="true">Profit by categories</a></li>
                                <li><a href="#corrected_profit_by_brands" data-toggle="tab" aria-expanded="true">Profit by brands</a></li>
                                <li><a href="#corrected_profit_by_locations" data-toggle="tab" aria-expanded="true">Profit by locations</a></li>
                                <li><a href="#corrected_profit_by_invoice" data-toggle="tab" aria-expanded="true">Profit by invoice</a></li>
                                <li><a href="#corrected_profit_by_date" data-toggle="tab" aria-expanded="true">Profit by date</a></li>
                                <li><a href="#corrected_profit_by_customer" data-toggle="tab" aria-expanded="true">Profit by customer</a></li>
                                <li><a href="#corrected_profit_by_day" data-toggle="tab" aria-expanded="true">Profit by day</a></li>
                            </ul>
                            <div class="tab-content">
                                <div class="tab-pane active" id="corrected_profit_by_products">
                                    <table class="table table-bordered table-striped" id="corrected_profit_by_products_table" style="width:100%">
                                        <thead><tr><th style="width:70%">Product</th><th style="width:30%">Gross Profit</th></tr></thead>
                                        <tfoot><tr><th>Total:</th><th class="footer_total"></th></tr></tfoot>
                                    </table>
                                </div>
                                <div class="tab-pane" id="corrected_profit_by_categories">
                                    <table class="table table-bordered table-striped" id="corrected_profit_by_categories_table" style="width:100%">
                                        <thead><tr><th style="width:70%">Category</th><th style="width:30%">Gross Profit</th></tr></thead>
                                        <tfoot><tr><th>Total:</th><th class="footer_total"></th></tr></tfoot>
                                    </table>
                                </div>
                                <div class="tab-pane" id="corrected_profit_by_brands">
                                    <table class="table table-bordered table-striped" id="corrected_profit_by_brands_table" style="width:100%">
                                        <thead><tr><th style="width:70%">Brand</th><th style="width:30%">Gross Profit</th></tr></thead>
                                        <tfoot><tr><th>Total:</th><th class="footer_total"></th></tr></tfoot>
                                    </table>
                                </div>
                                <div class="tab-pane" id="corrected_profit_by_locations">
                                    <table class="table table-bordered table-striped" id="corrected_profit_by_locations_table" style="width:100%">
                                        <thead><tr><th style="width:70%">Location</th><th style="width:30%">Gross Profit</th></tr></thead>
                                        <tfoot><tr><th>Total:</th><th class="footer_total"></th></tr></tfoot>
                                    </table>
                                </div>
                                <div class="tab-pane" id="corrected_profit_by_invoice">
                                    <table class="table table-bordered table-striped" id="corrected_profit_by_invoice_table" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th style="width:40%">Invoice No</th>
                                                <th style="width:40%">Gross Profit</th>
                                                <th style="width:20%">Stock Status</th>
                                            </tr>
                                        </thead>
                                        <tfoot><tr><th>Total:</th><th class="footer_total"></th><th></th></tr></tfoot>
                                    </table>
                                </div>
                                <div class="tab-pane" id="corrected_profit_by_date">
                                    <table class="table table-bordered table-striped" id="corrected_profit_by_date_table" style="width:100%">
                                        <thead><tr><th style="width:70%">Date</th><th style="width:30%">Gross Profit</th></tr></thead>
                                        <tfoot><tr><th>Total:</th><th class="footer_total"></th></tr></tfoot>
                                    </table>
                                </div>
                                <div class="tab-pane" id="corrected_profit_by_customer">
                                    <table class="table table-bordered table-striped" id="corrected_profit_by_customer_table" style="width:100%">
                                        <thead><tr><th style="width:70%">Customer</th><th style="width:30%">Gross Profit</th></tr></thead>
                                        <tfoot><tr><th>Total:</th><th class="footer_total"></th></tr></tfoot>
                                    </table>
                                </div>
                                <div class="tab-pane" id="corrected_profit_by_day">
                                    <table class="table table-bordered table-striped" id="corrected_profit_by_day_table" style="width:100%">
                                        <thead><tr><th style="width:70%">Day</th><th style="width:30%">Gross Profit</th></tr></thead>
                                        <tfoot><tr><th>Total:</th><th class="footer_total"></th></tr></tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-12">
                        <div class="alert alert-info">
                            <b>Note:</b> Profit by products/categories/brands only considers inline discount. Invoice discount is not considered.
                        </div>
                    </div>
                </div>
            @endcomponent
        @endif
    </section>
@endsection
@section('javascript')

<script>
$(document).ready(function() {
    // Fetch total missed profit based on negative stock sells
    $.ajax({
        url: '{{ route('profitfix.corrected_profit_loss.missedprofit') }}',
        method: 'GET',
        success: function(response) {
            if (response && typeof response.missed_profit !== 'undefined') {
                const missedProfit = parseFloat(response.missed_profit);
                if (!isNaN(missedProfit)) {
                    $('#missed_profit').text(missedProfit.toFixed(2));
                } else {
                    $('#missed_profit').text('Invalid data');
                }
            } else {
                $('#missed_profit').text('No data available');
            }
        },
        error: function(xhr, status, error) {
            $('#missed_profit').text('Error fetching missed profit');
        }
    });
});
</script>
<script src="{{ asset('js/report.js?v=' . (isset($asset_v) ? $asset_v : 1)) }}"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<script>
$(document).ready(function() {
    let start_date = moment().startOf('month');
    let end_date = moment().endOf('month');

    function reloadAllTables() {
        $('#corrected_profit_by_products_table').DataTable().ajax.reload();
        $('#corrected_profit_by_categories_table').DataTable().ajax.reload();
        $('#corrected_profit_by_brands_table').DataTable().ajax.reload();
        $('#corrected_profit_by_locations_table').DataTable().ajax.reload();
        $('#corrected_profit_by_invoice_table').DataTable().ajax.reload();
        $('#corrected_profit_by_date_table').DataTable().ajax.reload();
        $('#corrected_profit_by_customer_table').DataTable().ajax.reload();
        $('#corrected_profit_by_day_table').DataTable().ajax.reload();
    }

    $('#corrected_profit_loss_date_filter').daterangepicker({
        startDate: start_date,
        endDate: end_date,
        locale: { format: 'YYYY-MM-DD' }
    }, function(start, end) {
        start_date = start;
        end_date = end;
        reloadAllTables();
        $('#corrected_profit_loss_date_filter span').html(start.format('YYYY-MM-DD') + ' - ' + end.format('YYYY-MM-DD'));
    });

    // Set initial text
    $('#corrected_profit_loss_date_filter span').html(start_date.format('YYYY-MM-DD') + ' - ' + end_date.format('YYYY-MM-DD'));

    // DataTable for products
    $('#corrected_profit_by_products_table').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 25,
        ajax: {
            url: '{{ route('profitfix.corrected_profit_loss.product') }}',
            data: function(d) {
                d.start_date = start_date.format('YYYY-MM-DD');
                d.end_date = end_date.format('YYYY-MM-DD');
                d.location_id = $('#corrected_profit_loss_location_filter').val();
            }
        },
        columns: [
            { data: 'product', name: 'product' },
            { 
                data: 'gross_profit', 
                searchable: false,
                className: 'text-right'
            }
        ],
        footerCallback: function(row, data, start, end, display) {
            var total_profit = 0;
            for (var r in data) {
                total_profit += $(data[r].gross_profit).data('orig-value') ? parseFloat($(data[r].gross_profit).data('orig-value')) : 0;
            }
            $('#corrected_profit_by_products_table .footer_total').html(total_profit.toFixed(2)).addClass('text-right');
        }
    });

    // DataTable for categories
    $('#corrected_profit_by_categories_table').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 25,
        ajax: {
            url: '{{ route('profitfix.corrected_profit_loss.category') }}',
            data: function(d) {
                d.start_date = start_date.format('YYYY-MM-DD');
                d.end_date = end_date.format('YYYY-MM-DD');
                d.location_id = $('#corrected_profit_loss_location_filter').val();
            }
        },
        columns: [
            { data: 'category', name: 'category' },
            { 
                data: 'gross_profit', 
                searchable: false,
                className: 'text-right'
            }
        ],
        footerCallback: function(row, data, start, end, display) {
            var total_profit = 0;
            for (var r in data) {
                total_profit += $(data[r].gross_profit).data('orig-value') ? parseFloat($(data[r].gross_profit).data('orig-value')) : 0;
            }
            $('#corrected_profit_by_categories_table .footer_total').html(total_profit.toFixed(2)).addClass('text-right');
        }
    });

    // DataTable for brands
    $('#corrected_profit_by_brands_table').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 25,
        ajax: {
            url: '{{ route('profitfix.corrected_profit_loss.brand') }}',
            data: function(d) {
                d.start_date = start_date.format('YYYY-MM-DD');
                d.end_date = end_date.format('YYYY-MM-DD');
                d.location_id = $('#corrected_profit_loss_location_filter').val();
            }
        },
        columns: [
            { data: 'brand', name: 'brand' },
            { 
                data: 'gross_profit', 
                searchable: false,
                className: 'text-right'
            }
        ],
        footerCallback: function(row, data, start, end, display) {
            var total_profit = 0;
            for (var r in data) {
                total_profit += $(data[r].gross_profit).data('orig-value') ? parseFloat($(data[r].gross_profit).data('orig-value')) : 0;
            }
            $('#corrected_profit_by_brands_table .footer_total').html(total_profit.toFixed(2)).addClass('text-right');
        }
    });

    // DataTable for locations
    $('#corrected_profit_by_locations_table').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 25,
        ajax: {
            url: '{{ route('profitfix.corrected_profit_loss.location') }}',
            data: function(d) {
                d.start_date = start_date.format('YYYY-MM-DD');
                d.end_date = end_date.format('YYYY-MM-DD');
                d.location_id = $('#corrected_profit_loss_location_filter').val();
            }
        },
        columns: [
            { data: 'location', name: 'location' },
            { 
                data: 'gross_profit', 
                searchable: false,
                className: 'text-right'
            }
        ],
        footerCallback: function(row, data, start, end, display) {
            var total_profit = 0;
            for (var r in data) {
                total_profit += $(data[r].gross_profit).data('orig-value') ? parseFloat($(data[r].gross_profit).data('orig-value')) : 0;
            }
            $('#corrected_profit_by_locations_table .footer_total').html(total_profit.toFixed(2)).addClass('text-right');
        }
    });

    // DataTable for invoice - UPDATED to show negative stock in red
    $('#corrected_profit_by_invoice_table').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 25,
        ajax: {
            url: '{{ route('profitfix.corrected_profit_loss.invoice') }}',
            data: function(d) {
                d.start_date = start_date.format('YYYY-MM-DD');
                d.end_date = end_date.format('YYYY-MM-DD');
                d.location_id = $('#corrected_profit_loss_location_filter').val();
            }
        },
        columns: [
            { data: 'invoice_no', name: 'invoice_no' },
            { 
                data: 'gross_profit_html', 
                orderable: false, 
                searchable: false, 
                className: 'text-right'
            },
            { 
                data: 'stock_status', 
                name: 'stock_status', 
                searchable: false,
                render: function(data, type, row) {
                    if(data === 'Negative Stock') {
                        return '<span class="label label-danger">Negative Stock</span>';
                    }
                    return data || '-';
                }
            }
        ],
        footerCallback: function(row, data, start, end, display) {
            var total_profit = 0;
            for (var r in data) {
                var profitCell = data[r].gross_profit_html;
                // Create a temporary DOM element to parse the HTML string
                var tempDiv = document.createElement('div');
                tempDiv.innerHTML = profitCell;
                var span = tempDiv.querySelector('span.gross-profit');
                var profitValue = 0;
                if (span && span.getAttribute('data-orig-value')) {
                    profitValue = parseFloat(span.getAttribute('data-orig-value'));
                } else if (span) {
                    profitValue = parseFloat(span.textContent.replace(/[^\d.-]/g, '')) || 0;
                }
                total_profit += profitValue;
            }
            $('#corrected_profit_by_invoice_table .footer_total').html(total_profit.toFixed(2)).addClass('text-right');
        }
    });

    // DataTable for date
    $('#corrected_profit_by_date_table').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 25,
        ajax: {
            url: '{{ route('profitfix.corrected_profit_loss.date') }}',
            data: function(d) {
                d.start_date = start_date.format('YYYY-MM-DD');
                d.end_date = end_date.format('YYYY-MM-DD');
                d.location_id = $('#corrected_profit_loss_location_filter').val();
            }
        },
        columns: [
            { data: 'transaction_date', name: 'transaction_date' },
            { 
                data: 'gross_profit', 
                searchable: false,
                className: 'text-right'
            }
        ],
        footerCallback: function(row, data, start, end, display) {
            var total_profit = 0;
            for (var r in data) {
                total_profit += $(data[r].gross_profit).data('orig-value') ? parseFloat($(data[r].gross_profit).data('orig-value')) : 0;
            }
            $('#corrected_profit_by_date_table .footer_total').html(total_profit.toFixed(2)).addClass('text-right');
        }
    });

    // DataTable for customer
    $('#corrected_profit_by_customer_table').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 25,
        ajax: {
            url: '{{ route('profitfix.corrected_profit_loss.customer') }}',
            data: function(d) {
                d.start_date = start_date.format('YYYY-MM-DD');
                d.end_date = end_date.format('YYYY-MM-DD');
                d.location_id = $('#corrected_profit_loss_location_filter').val();
            }
        },
        columns: [
            { data: 'customer', name: 'customer' },
            { 
                data: 'gross_profit', 
                searchable: false,
                className: 'text-right'
            }
        ],
        footerCallback: function(row, data, start, end, display) {
            var total_profit = 0;
            for (var r in data) {
                total_profit += $(data[r].gross_profit).data('orig-value') ? parseFloat($(data[r].gross_profit).data('orig-value')) : 0;
            }
            $('#corrected_profit_by_customer_table .footer_total').html(total_profit.toFixed(2)).addClass('text-right');
        }
    });

    // DataTable for day
    $('#corrected_profit_by_day_table').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 25,
        ajax: {
            url: '{{ route('profitfix.corrected_profit_loss.day') }}',
            data: function(d) {
                d.start_date = start_date.format('YYYY-MM-DD');
                d.end_date = end_date.format('YYYY-MM-DD');
                d.location_id = $('#corrected_profit_loss_location_filter').val();
            }
        },
        columns: [
            { data: 'day', name: 'day' },
            { 
                data: 'gross_profit', 
                searchable: false,
                className: 'text-right'
            }
        ],
        footerCallback: function(row, data, start, end, display) {
            var total_profit = 0;
            for (var r in data) {
                total_profit += $(data[r].gross_profit).data('orig-value') ? parseFloat($(data[r].gross_profit).data('orig-value')) : 0;
            }
            $('#corrected_profit_by_day_table .footer_total').html(total_profit.toFixed(2)).addClass('text-right');
        }
    });

    $('#corrected_profit_loss_location_filter').on('change', function() {
        reloadAllTables();
    });
});
</script>
<style>
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        padding: 0.3em 0.8em !important;
        margin-left: 2px !important;
    }
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter {
        margin-bottom: 10px;
    }
    .text-danger {
        color: #dc3545 !important;
        font-weight: bold;
    }
    .label.label-danger {
        background-color: #dc3545;
        color: white;
        padding: 3px 8px;
        border-radius: 3px;
        font-size: 12px;
    }
</style>
@endsection