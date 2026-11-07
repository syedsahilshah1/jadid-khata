@extends('layouts.app')
@section('title', __('partners::lang.partners'))

@section('content')
    <style>
        .table-striped th {
            background-color: #626161;
            color: #ffffff;
        }
    </style>

    @include('partners::layouts.nav')

    <section class="content-header">
        <h1>@lang('partners::lang.financial_estimation_company')</h1>
    </section>

    <section class="content">
        @component('components.widget', ['class' => 'box-primary', 'title' =>''])
            @can('assets.create')
                @slot('tool')
                    <div class="row">
                        <div class="col-md-12">
                            @component('components.widget', ['class' => 'box-solid'])
                                <table class="table no-border">
                                    <tr>
                                        <td>@lang('partners::lang.closing_stock') (@lang('lang_v1.by_purchase_price'))</td>
                                        <td>@lang('partners::lang.closing_stock') (@lang('lang_v1.by_sale_price'))</td>
                                        <td>@lang('lang_v1.potential_profit')</td>
                                        <td>@lang('partners::lang.profit_margin')</td>
                                    </tr>
                                    <tr>
                                        <td><h3 class="mb-0 mt-0">{{ number_format($closing_stock_by_pp, 2) }}</h3></td>
                                        <td><h3 class="mb-0 mt-0">{{ number_format($closing_stock_by_sp, 2) }}</h3></td>
                                        <td><h3 class="mb-0 mt-0">{{ number_format($potential_profit, 2) }}</h3></td>
                                        <td><h3 class="mb-0 mt-0">{{ number_format($profit_margin, 2) }}</h3></td>
                                    </tr>
                                </table>
                            @endcomponent

                            @component('components.widget', ['class' => 'box-solid'])
                                <table class="table no-border">
                                    <tr>
                                        <td>@lang('partners::lang.cash_bank_balance')</td>
                                        <td>@lang('partners::lang.customer_liabilities')</td>
                                        <td>@lang('partners::lang.supplier_dues')</td>
                                    </tr>
                                    <tr>
                                        <td><h3 class="mb-0 mt-0">{{ number_format($account_details, 2) }}</h3></td>
                                        <td><h3 class="mb-0 mt-0">{{ number_format($customer, 2) }}</h3></td>
                                        <td><h3 class="mb-0 mt-0">{{ number_format($supplier, 2) }}</h3></td>
                                    </tr>
                                </table>
                            @endcomponent

                            @component('components.widget', ['class' => 'box-solid'])
                                <table class="table no-border">
                                    <tr>
                                        <td>@lang('partners::lang.total_at_purchase_price'):</td>
                                        <td>@lang('partners::lang.total_at_sale_price'):</td>
                                    </tr>
                                    <tr>
                                        <td><h3 class="mb-0 mt-0">{{ number_format($account_details + $closing_stock_by_pp + $assets + $customer - $supplier, 2) }}</h3></td>
                                        <td><h3 class="mb-0 mt-0">{{ number_format($account_details + $closing_stock_by_sp + $assets + $customer - $supplier, 2) }}</h3></td>
                                    </tr>
                                </table>
                            @endcomponent

                            @if($totalshare > 0)
                                @component('components.widget', ['class' => 'box-solid'])
                                    <table class="table no-border">
                                        <tr>
                                            <td>@lang('partners::lang.number_of_shares')</td>
                                            <td>@lang('partners::lang.share_price_at_purchase_price'):</td>
                                            <td>@lang('partners::lang.share_price_at_sale_price'):</td>
                                        </tr>
                                        <tr>
                                            <td><h3 class="mb-0 mt-0">{{ number_format($totalshare, 2) }}</h3></td>
                                            <td><h2 class="mb-0 mt-0">{{ number_format(($account_details + $closing_stock_by_pp + $assets + $customer - $supplier) / $totalshare, 2) }}</h2></td>
                                            <td><h2 class="mb-0 mt-0">{{ number_format(($account_details + $closing_stock_by_sp + $assets + $customer - $supplier) / $totalshare, 2) }}</h2></td>
                                        </tr>
                                    </table>
                                @endcomponent
                            @endif
                        </div>
                    </div>
                @endslot
            @endcan
        @endcomponent
    </section>

    <div class="modal fade brands_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>
@endsection


<script>

    function assetedit(id) {
        $.ajax({
            url: '/partners/partners/'+id+'/edit',
            dataType: 'html',
            success: function(result) {
                $(".brands_modal").html(result)
                    .modal('show');
            },
        });
    }


    function  deleteasset(id) {
        swal({
            title: LANG.sure,
            text: 'Delete?',
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        }).then(willDelete => {
            if (willDelete) {
                var href = '/partners/partners/'+id;
                var data = id;
                $.ajax({
                    method: 'DELETE',
                    url: href,
                    dataType: 'json',
                    data:{
                        data:data
                    },
                    success: function(result) {
                        if (result.success == true) {
                            toastr.success(result.msg);
                            var drow = document.getElementById(id);
                            drow.parentNode.removeChild(drow);
                        } else {
                            toastr.error(result.msg);
                        }
                    },
                });
            }
        });
    }

</script>

