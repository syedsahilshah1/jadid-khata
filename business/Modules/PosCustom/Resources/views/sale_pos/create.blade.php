@php
	$is_mobile = isMobile(); 
	$custom_labels = json_decode($business_details['custom_labels']);
	$theme_divider_class = 'theme_' . $business_details->theme_color . '_divider';
	/* Old style 5.40 using css/app.css */
    $theme_pos_class1 = 'theme_' . $business_details->theme_color . '_pos';
    /* New style usin css/tailwind/app.css */
    $theme_pos_class = 'tw-bg-gradient-to-r tw-from-' . $business_details->theme_color . '-800'
    .' tw-to-'.$business_details->theme_color.'-500';

    /*
    tw-bg-gradient-to-r tw-from-orange-800 tw-to-blue-500
    */

	/*
	skin-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'blue-light'}}@endif   
	*/
@endphp

{{-- {{var_dump($business_details->item_addition_method)}}
{{ var_dump($business_details->theme_color) }}
{{ var_dump($theme_divider_class) }}
{{ var_dump($theme_pos_class) }}
{{ var_dump($custom_labels->payments->custom_pay_1) }}
{{var_dump(session('business.theme_color'))}} --}}


@extends('PosCustom::layouts.app')

@section('title', __('sale.pos_sale'))

@section('content')
    <section class="content no-print">
        <input type="hidden" id="amount_rounding_method" value="{{ $pos_settings['amount_rounding_method'] ?? '' }}">
        @if (!empty($pos_settings['allow_overselling']))
            <input type="hidden" id="is_overselling_allowed">
        @endif
        @if (session('business.enable_rp') == 1)
            <input type="hidden" id="reward_point_enabled">
        @endif
        
        @php
            $is_discount_enabled = $pos_settings['disable_discount'] != 1 ? true : false;
            $is_rp_enabled = session('business.enable_rp') == 1 ? true : false;
        @endphp

        {!! Form::open([
            'url' => action([\App\Http\Controllers\SellPosController::class, 'store']),
            'method' => 'post',
            'id' => 'add_pos_sell_form',
        ]) !!}
        
        <div class="row mb-12">
            <div class="col-md-12 tw-pt-0 tw-mb-14">

       			<!-- Draw the complete panel and divide in 2 equals parts 40% y 60%-->

                <div class="row tw-flex lg:tw-flex-row md:tw-flex-col sm:tw-flex-col tw-flex-col tw-items-start md:tw-gap-4">
                    {{-- <div class="@if (empty($pos_settings['hide_product_suggestion'])) col-md-7 @else col-md-10 col-md-offset-1 @endif no-padding pr-12"> --}}
                    <div class="tw-px-3 tw-w-full lg:tw-w-[40%] lg:tw-px-0 lg:tw-pr-0">

                        <div class="tw-shadow-[rgba(17,_17,_26,_0.1)_0px_0px_16px] tw-rounded-2xl tw-bg-white tw-mb-2 md:tw-mb-8 tw-p-2">

                            {{-- <div class="box box-solid mb-12 @if (!isMobile()) mb-40 @endif"> --}}
                                <div class="box-body pb-0">
                                    {!! Form::hidden('location_id', $default_location->id ?? null, [
                                        'id' => 'location_id',
                                        'data-receipt_printer_type' => !empty($default_location->receipt_printer_type)
                                            ? $default_location->receipt_printer_type
                                            : 'browser',
                                        'data-default_payment_accounts' => $default_location->default_payment_accounts ?? '',
                                    ]) !!}
                                    <!-- sub_type -->
                                    {!! Form::hidden('sub_type', isset($sub_type) ? $sub_type : null) !!}
                                    <input type="hidden" id="item_addition_method"
                                        value="{{ $business_details->item_addition_method }}">
                                    
                                    <!-- left side-->
                                    
								@include('PosCustom::sale_pos.partials.pos_form')

       								<div class="{{$theme_divider_class}}" ></div> <!-- Divider-->

                                    @include('sale_pos.partials.pos_form_totals')

                                    @include('sale_pos.partials.payment_modal')

                                    @if (empty($pos_settings['disable_suspend']))
                                        @include('sale_pos.partials.suspend_note_modal')
                                    @endif

                                    @if (empty($pos_settings['disable_recurring_invoice']))
                                        @include('sale_pos.partials.recurring_invoice_modal')
                                    @endif
                                </div>
                            {{-- </div> --}}
                        </div>
                    </div>
                    @if (empty($pos_settings['hide_product_suggestion']) && !isMobile())
                        <div class="md:tw-no-padding tw-w-full  lg:tw-w-[60%] tw-px-3">
                            @include('PosCustom::sale_pos.partials.pos_sidebar')
                        </div>
                    @endif
                </div>
            </div>
        </div>
	@include('PosCustom::sale_pos.partials.pos_form_actions')
        {!! Form::close() !!}
    </section>

    <!-- This will be printed -->
    <section class="invoice print_section" id="receipt_section">
    </section>
    <div class="modal fade contact_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        @include('contact.create', ['quick_add' => true])
    </div>
    @if (empty($pos_settings['hide_product_suggestion']) && isMobile())
        @include('sale_pos.partials.mobile_product_suggestions')
    @endif
    
    <!-- /.content -->
    <div class="modal fade register_details_modal" tabindex="-1" role="dialog" 
    	aria-labelledby="gridSystemModalLabel">
    </div>
    <div class="modal fade close_register_modal" tabindex="-1" role="dialog" 
    	aria-labelledby="gridSystemModalLabel">
    </div>
    <!-- quick product modal -->
    <div class="modal fade quick_add_product_modal" tabindex="-1" role="dialog" aria-labelledby="modalTitle"></div>
    <div class="modal fade" id="expense_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>

    @include('sale_pos.partials.configure_search_modal')

    @include('sale_pos.partials.recent_transactions_modal')

    @include('sale_pos.partials.weighing_scale_modal')

@stop
@section('css')
    <!-- include module css -->
    @if (!empty($pos_module_data))
        @foreach ($pos_module_data as $key => $value)
            @if (!empty($value['module_css_path']))
                @includeIf($value['module_css_path'])
            @endif
        @endforeach
    @endif
@stop
@section('javascript')
{{-- #JCN--}}
{{-- INI JAVA 
	<script src="{{ asset('js/pos.js?v=' . $asset_v) }}"></script>
	I move from public/js/pos.js to PosCustom/layouts/partials/pos_js_custom.blade.php	
	This because when we are using a module the link to Module/PosCustom/Resource/assets/js doesnt work
--}}
@include('PosCustom::layouts.partials.pos_js_custom');
{{-- #JCN--}}
    <script src="{{ asset('js/printer.js?v=' . $asset_v) }}"></script>
    <script src="{{ asset('js/product.js?v=' . $asset_v) }}"></script>
    <script src="{{ asset('js/opening_stock.js?v=' . $asset_v) }}"></script>
    @include('sale_pos.partials.keyboard_shortcuts')

    <!-- Call restaurant module if defined -->
    @if (in_array('tables', $enabled_modules) ||
            in_array('modifiers', $enabled_modules) ||
            in_array('service_staff', $enabled_modules))
        <script src="{{ asset('js/restaurant.js?v=' . $asset_v) }}"></script>
    @endif
    <!-- include module js -->
    @if (!empty($pos_module_data))
        @foreach ($pos_module_data as $key => $value)
            @if (!empty($value['module_js_path']))
                @includeIf($value['module_js_path'], ['view_data' => $value['view_data']])
            @endif
        @endforeach
    @endif
@endsection
