@extends('crm::layouts.app')
@section('title', __( 'crm::lang.order_request'))
@section('content')

<!-- Content Header (Page header) -->
<section class="content-header no-print">
    <h1>@lang('crm::lang.order_request')</h1>
</section>

<!-- Main content -->
<section class="content no-print">

    @if(!empty($catalogue_url))
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-solid" style="background: linear-gradient(135deg, #1a7a4a 0%, #28a745 60%, #20c050 100%); border:none; border-radius:6px; margin-bottom:16px;">
                <div class="box-body" style="padding: 18px 24px;">
                    <div class="row" style="display:flex; align-items:center;">
                        <div class="col-xs-8 col-sm-9">
                            <div style="color:#fff;">
                                <p style="font-size:18px; font-weight:700; margin:0 0 4px 0; letter-spacing:0.3px;">
                                    <i class="fas fa-store" style="margin-right:8px; opacity:0.9;"></i>
                                    {{ __('productcatalogue::lang.shop_now') ?: 'Shop / Order Again' }}
                                </p>
                                <p style="font-size:13px; margin:0; opacity:0.88; font-weight:400;">
                                    {{ __('productcatalogue::lang.visit_catalogue_hint') ?: 'Browse our product catalogue and place a new order instantly.' }}
                                </p>
                            </div>
                        </div>
                        <div  style="text-align:right;">
                            <a href="{{ $catalogue_url }}"
                               style="display:inline-block; background:#fff; color:#1a7a4a; font-weight:700; font-size:14px; padding:10px 22px; border-radius:5px; text-decoration:none; box-shadow:0 2px 6px rgba(0,0,0,0.15); letter-spacing:0.2px; white-space:nowrap;">
                                <i class="fas fa-shopping-cart" style="margin-right:6px;"></i> Order Now
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    @component('components.filters', ['title' => __('report.filters')])
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('sell_list_filter_location_id',  __('purchase.business_location') . ':') !!}
                {!! Form::select('sell_list_filter_location_id', $business_locations, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all') ]); !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('so_list_filter_status',  __('sale.status') . ':') !!}
                {!! Form::select('so_list_filter_status', $order_statuses, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('sell_list_filter_date_range', __('report.date_range') . ':') !!}
                {!! Form::text('sell_list_filter_date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'readonly']); !!}
            </div>
        </div>
    @endcomponent

    @component('components.widget', ['class' => 'box-primary'])
        @slot('tool')
            <div class="box-tools">
                <a class="btn btn-block btn-primary" href="{{action([\Modules\Crm\Http\Controllers\OrderRequestController::class, 'create'])}}">
                <i class="fa fa-plus"></i> @lang('crm::lang.add_order_request')</a>
            </div>
        @endslot
        <div class="table-responsive">
            <table class="table table-bordered table-striped ajax_view" id="sell_table">
                <thead>
                    <tr>
                        <th>@lang('messages.date')</th>
                        <th>@lang('restaurant.order_no')</th>
                        <th>@lang('sale.location')</th>
                        <th>@lang('sale.status')</th>
                        <th>@lang('lang_v1.quantity_remaining')</th>
                    </tr>
                </thead>
            </table>
        </div>
    @endcomponent
</section>
<!-- /.content -->
@stop
@section('javascript')
<script type="text/javascript">
$(document).ready( function(){
    $('#sell_list_filter_date_range').daterangepicker(
        dateRangeSettings,
        function (start, end) {
            $('#sell_list_filter_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
            sell_table.ajax.reload();
        }
    );
    $('#sell_list_filter_date_range').on('cancel.daterangepicker', function(ev, picker) {
        $('#sell_list_filter_date_range').val('');
        sell_table.ajax.reload();
    });
    sell_table = $('#sell_table').DataTable({
        processing: true,
        serverSide: true,
        aaSorting: [[1, 'desc']],
        "ajax": {
            "url": '{{action([\Modules\Crm\Http\Controllers\OrderRequestController::class, 'index'])}}',
            "data": function ( d ) {
                if($('#sell_list_filter_date_range').val()) {
                    var start = $('#sell_list_filter_date_range').data('daterangepicker').startDate.format('YYYY-MM-DD');
                    var end = $('#sell_list_filter_date_range').data('daterangepicker').endDate.format('YYYY-MM-DD');
                    d.start_date = start;
                    d.end_date = end;
                }

                if($('#sell_list_filter_location_id').length) {
                    d.location_id = $('#sell_list_filter_location_id').val();
                }
                d.customer_id = $('#sell_list_filter_customer_id').val();

                if ($('#so_list_filter_status').length) {
                    d.status = $('#so_list_filter_status').val();
                }
            }
        },
        columns: [
            { data: 'transaction_date', name: 'transaction_date'  },
            { data: 'invoice_no', name: 'invoice_no'},
            { data: 'business_location', name: 'bl.name'},
            { data: 'status', name: 'status'},
            { data: 'so_qty_remaining', name: 'so_qty_remaining', "searchable": false},
        ]
    });
    $(document).on('change', '#sell_list_filter_location_id, #sell_list_filter_customer_id, #so_list_filter_status',  function() {
        sell_table.ajax.reload();
    });
});
</script>

@endsection
