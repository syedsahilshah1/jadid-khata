@extends('layouts.app')
@section('title', __('gym::lang.subscription'))
@section('content')
    @include('gym::layouts.nav')
    <section class="content-header">
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black"> @lang('gym::lang.subscriptions')
        </h1>
    </section>
    <section class="content">
        @component('components.filters', ['title' => __('report.filters')])
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('customer_id',  __('contact.customer') . ':') !!}
                {!! Form::select('customer_id', $customers, null, ['class' => 'form-control select2', 'id' => 'customer_id', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('package_id',  __('gym::lang.packages') . ':') !!}
                {!! Form::select('package_id', $packages, null, ['class' => 'form-control select2', 'id' => 'package_id', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('payment_status',  __('purchase.payment_status') . ':') !!}
                {!! Form::select('payment_status', ['paid' => __('lang_v1.paid'), 'due' => __('lang_v1.due'), 'partial' => __('lang_v1.partial'), 'overdue' => __('lang_v1.overdue')], null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
            </div>
        </div>
        @endcomponent
        @component('components.widget')
            <table class="table table-bordered table-striped" id="subscription_table" style="width: 100%">
                <thead>
                    <tr>
                        <th>
                            @lang('gym::lang.customer')
                        </th>
                        <th>
                            @lang('gym::lang.package')
                        </th>
                        <th>
                            @lang('gym::lang.payment_status')
                        </th>
                        <th>
                            @lang('lang_v1.payment_method')
                        </th>
                        <th>
                            @lang('gym::lang.total_amount')
                        </th>
                        <th>
                            @lang('gym::lang.total_paid')
                        </th>
                        <th>
                            @lang('gym::lang.due')
                        </th>
                        <th>
                            @lang('lang_v1.created_at')
                        </th>
                    </tr>
                </thead>
            </table>
        @endcomponent

        <!-- Add HMS Extra Modal -->
        <div class="modal fade view_modal_extra" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>
        </div>

    </section>
    <!-- /.content -->
    <div class="modal fade payment_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>
    <div class="modal fade edit_payment_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">

        <!-- /.content -->
    @endsection

    @section('javascript')
        <script src="{{ asset('js/payment.js?v=' . $asset_v) }}"></script>

        <script>
            $(document).ready(function() {

                subscription_table = $('#subscription_table').DataTable({
                    processing: true,
                    serverSide: true,
                    fixedHeader: false,
                    ajax: {
                        url: "{{ action([\Modules\Gym\Http\Controllers\SubscriptionController::class, 'index']) }}",
                        "data": function(d) {
                            d.customer_id = $('#customer_id').val();
                            d.package_id = $('#package_id').val();
                            d.payment_status = $('#payment_status').val();
                        },
                    },
                    aaSorting: [
                        [7, 'desc']
                    ],
                    columns: [{
                            data: 'c_name',
                            name: 'c.name',
                        },
                        {
                            data: 'package',
                            name: 'package',
                        },
                        {
                            data: 'payment_status',
                            name: 'payment_status',
                        },
                        {
                            data: 'payment_methods',
                            orderable: false,
                            "searchable": false
                        },
                        {
                            data: 'final_total',
                            name: 'final_total'
                        },
                        {
                            data: 'total_paid',
                            name: 'total_paid',
                            "searchable": false
                        },
                        {
                            data: 'total_remaining',
                            name: 'total_remaining'
                        },
                        {
                            data: 'created_at',
                            name: 'created_at'
                        },
                    ],
                });
                $(document).on('change', '#customer_id, #payment_status, #package_id', function() {
                    subscription_table.ajax.reload();
                });

            });
        </script>
    @endsection
