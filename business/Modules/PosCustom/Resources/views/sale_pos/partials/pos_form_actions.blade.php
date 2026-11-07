@php
    $is_mobile = isMobile();
    /* Class large tw-w-[8.5rem] w = fixed*/
    $button_class_lx=" 
    md:tw-flex 
    md:tw-flex-row 
    md:tw-items-center 
    md:tw-justify-center 
    md:tw-gap-1 
    tw-font-bold 
    tw-text-white 
    tw-cursor-pointer 
    tw-text-xs 
    tw-text-sm  
    tw-rounded-md 
    tw-w-[8.5rem]  
    tw-dw-btn-xs
    @if (!$is_mobile)  @endif no-print";

    /* Class medium tw-p-2 = no center*/
    $button_class_m=" 
    md:tw-flex 
    md:tw-flex-row 
    md:tw-items-center 
    md:tw-justify-center 
    md:tw-gap-1 
    tw-font-bold 
    tw-text-white 
    tw-cursor-pointer 
    md:tw-text-xs 
    md:tw-text-sm  
    tw-rounded-full 
    tw-p-2 
    tw-w-[8.5rem] 
    @if (!$is_mobile)  @endif no-print";

    /* Class test */
    $button_test=" 
    md:tw-flex 
    md:tw-flex-row 
    md:tw-items-center 
    md:tw-justify-center 
    md:tw-gap-1 
    tw-font-bold 
    tw-text-white 
    tw-cursor-pointer 
    md:tw-text-xs 
    md:tw-text-sm  
    tw-rounded-md 
    tw-dw-btn-xs
    @if (!$is_mobile)  @endif no-print";




@endphp
<div class="row ">
    <div class="pos-form-actions  {{ $theme_pos_class }} tw-rounded-tr-xl tw-rounded-tl-xl tw-shadow-[rgba(17,_17,_26,_0.1)_0px_0px_16px] tw-bg-white tw-cursor-pointer">
        {{-- To organize footer actions buttons --}}
        <div class="tw-flex tw-items-center tw-justify-between tw-flex-col sm:tw-flex-row md:tw-flex-row lg:tw-flex-row xl:tw-flex-row tw-gap-2 tw-px-4 tw-py-0 {{-- tw-overflow-x-auto --}} tw-w-full">
  
                <div class="md:!tw-w-none !tw-flex md:!tw-hidden !tw-flex-row !tw-items-center !tw-gap-3">
                    <div class="tw-pos-total tw-flex tw-items-center tw-gap-3">
                        <div class="tw-text-black tw-font-bold tw-text-sm tw-flex tw-items-center tw-flex-col tw-leading-1">
                            <div>@lang('sale.total_payable'):</div>
                            {{-- <div>Payable:</div> --}}
                        </div>
                        <input type="hidden" name="final_total" id="final_total_input" value="0.00">
                        <span id="total_payable" class="tw-text-green-900 tw-font-bold tw-text-sm number">0.00</span>
                    </div>
                </div>

                <div class=" tw-flex tw-items-center  tw-gap-4 tw-rounded-md tw-flex-row {{-- tw-overflow-x-auto --}} tw-bg-[#001F3E]">
                    @if (!$is_mobile)
                        <div class="md:tw-flex md:tw-items-center tw-rounded-md md:tw-gap-6 tw-hidden">
                            <div
                                class="tw-text-white tw-font-bold tw-text-base md:tw-text-md tw-flex tw-items-center {{-- tw-flex-col --}}">
                                <div>@lang('sale.total_payable'):</div>
                            </div>
                            <input type="hidden" name="final_total" id="final_total_input" value="0.00">
                            <span id="total_payable"
                                class="tw-bg-white tw-text-red-500 tw-rounded-md tw-font-bold tw-text-base md:tw-text-3xl number">0.00</span>
                        </div>
                    @endif                 
                </div>

                <div class="!tw-w-full md:!tw-w-none !tw-flex md:!tw-hidden !tw-flex-row !tw-items-center !tw-gap-3">
                    @if (!Gate::check('disable_pay_checkout') || auth()->user()->can('superadmin') || auth()->user()->can('admin'))
                        <button type="button"
                        class="tw-bg-[#001F3E] {{ $button_class_m }} @if (!$is_mobile)  @endif  @if ($pos_settings['disable_pay_checkout'] != 0) hide @endif"
                            id="pos-finalize" title="@lang('lang_v1.tooltip_checkout_multi_pay')"><i class="fas fa-money-check-alt"
                                aria-hidden="true"></i> @lang('lang_v1.checkout_multi_pay') </button>
                    @endif

                    @if (!Gate::check('disable_express_checkout') || auth()->user()->can('superadmin') || auth()->user()->can('admin'))
                        <button type="button"
                            class="tw-bg-green-800 {{ $button_class_m }} no-print @if ($pos_settings['disable_express_checkout'] != 0 || !array_key_exists('cash', $payment_types)) hide @endif pos-express-finalize @if ($is_mobile) col-xs-6 @endif"
                            data-pay_method="cash" title="@lang('tooltip.express_checkout')"> <i class="fas fa-money-bill-alt"
                                aria-hidden="true"></i> @lang('lang_v1.express_checkout_cash')</button>
                    @endif

                    @if (empty($edit))
                        <button type="button" class="btn-danger {{ $button_class_m }}" id="pos-cancel"> <i
                                class="fas fa-window-close"></i> @lang('sale.cancel')</button>
                    @else
                        <button type="button" class="btn-danger tw-dw-btn hide tw-dw-btn-xs" id="pos-delete"
                            @if (!empty($only_payment)) disabled @endif> <i class="fas fa-trash-alt"></i>
                            @lang('messages.delete')</button>
                    @endif
                </div>

                <div class="tw-flex tw-items-center tw-gap-4 tw-flex-row {{--tw-overflow-x-auto--}}">
                    {{-- Multiple pay--}}
                    @if (!Gate::check('disable_pay_checkout') || auth()->user()->can('superadmin') || auth()->user()->can('admin'))
                        <button type="button"
                            class="tw-hidden tw-bg-[#001F3E] hover:tw-bg-[#414aac] {{ $button_class_m }} @if ($pos_settings['disable_pay_checkout'] != 0) hide @endif"
                            id="pos-finalize" title="@lang('lang_v1.tooltip_checkout_multi_pay')">
                            <i class="fas fa-money-check-alt"aria-hidden="true"></i> 
                            @lang('lang_v1.checkout_multi_pay') 
                        </button>
                    @endif

                    {{-- cash--}}
                    @if (!Gate::check('disable_express_checkout') || auth()->user()->can('superadmin') || auth()->user()->can('admin'))
                    <button type="button"
                        class="tw-hidden tw-bg-green-800 hover:tw-bg-[#414aac]{{ $button_class_m }}   @if ($pos_settings['disable_express_checkout'] != 0 || !array_key_exists('cash', $payment_types)) hide @endif pos-express-finalize"                       
                        data-pay_method="cash" title="@lang('tooltip.express_checkout')"> 
                        <i class="fas fa-money-bill-alt" aria-hidden="true"></i> 
                        @lang('lang_v1.express_checkout_cash')</button>
                    @endif
                    {{-- Draft --}}
                    @if (!Gate::check('disable_draft') || auth()->user()->can('superadmin') || auth()->user()->can('admin'))
                        <button type="button"
                        class="tw-hidden tw-bg-sky-800 hover:tw-bg-[#414aac] {{ $button_class_m }} @if ($pos_settings['disable_draft'] != 0) hide @endif"
                        id="pos-draft" @if (!empty($only_payment)) disabled @endif>
                        <i class="fas fa-edit tw-text-[#009ce4]"></i> 
                        @lang('sale.draft')</button>
                    @endif

                    {{-- Quotation New--}}
                    @if (!Gate::check('disable_quotation') || auth()->user()->can('superadmin') || auth()->user()->can('admin'))
                    <button type="button"
                        class="tw-hidden tw-bg-yellow-800 hover:tw-bg-[#414aac]  {{ $button_class_m }} "
                        id="pos-quotation" @if (!empty($only_payment)) disabled @endif> 
                        <i class="fas fa-edit" aria-hidden="true"></i> 
                        @lang('lang_v1.quotation')</button>
                    @endif

                    {{-- Quotation 
                    @if (!Gate::check('disable_quotation') || auth()->user()->can('superadmin') || auth()->user()->can('admin'))
                        <button type="button"
                            class="tw-font-bold tw-text-gray-700 tw-cursor-pointer tw-text-xs md:tw-text-sm tw-flex tw-flex-col tw-items-center tw-justify-center tw-gap-1 @if ($is_mobile) col-xs-6 @endif"
                            id="pos-quotation" @if (!empty($only_payment)) disabled @endif>
                            <i class="fas fa-edit tw-text-[#E7A500]"></i> 
                            @lang('lang_v1.quotation')</button>
                    @endif
                    --}}
                    {{-- Suspend Sale--}}
                    @if (!Gate::check('disable_suspend_sale') || auth()->user()->can('superadmin') || auth()->user()->can('admin'))
                        @if (empty($pos_settings['disable_suspend']))
                            <button type="button"
                                {{-- class="tw-font-bold tw-text-gray-700 tw-cursor-pointer tw-text-xs md:tw-text-sm tw-flex tw-flex-col tw-items-center tw-justify-center tw-gap-1  no-print pos-express-finalize" --}}
                                class="tw-bg-orange-800 hover:tw-bg-[#414aac] {{$button_class_m}} pos-express-finalize "
                                data-pay_method="suspend" title="@lang('lang_v1.tooltip_suspend')"
                                @if (!empty($only_payment)) disabled @endif>
                                <i class="fas fa-pause " aria-hidden="true"></i>
                                @lang('lang_v1.suspend')
                            </button>
                        @endif
                    @endif

                    {{-- Credit Sale --}}
                    @if (!Gate::check('disable_credit_sale') || auth()->user()->can('superadmin') || auth()->user()->can('admin'))
                        @if (empty($pos_settings['disable_credit_sale_button']))
                            <input type="hidden" name="is_credit_sale" value="0" id="is_credit_sale">
                            <button type="button"
                                {{-- class=" tw-font-bold tw-text-gray-700 tw-cursor-pointer tw-text-xs md:tw-text-sm tw-flex tw-flex-col tw-items-center tw-justify-center tw-gap-1 no-print pos-express-finalize @if ($is_mobile) col-xs-6 @endif" --}}
                                class="tw-bg-sky-800 hover:tw-bg-[#414aac] {{ $button_class_m }} pos-express-finalize "
                                data-pay_method="credit_sale" title="@lang('lang_v1.tooltip_credit_sale')"
                                @if (!empty($only_payment)) disabled @endif>
                                <i class="fas fa-check tw-text-[#5E5CA8]" aria-hidden="true"></i> @lang('lang_v1.credit_sale')
                            </button>
                        @endif
                    @endif
                    {{-- Card Credit--}}
                    @if (!Gate::check('disable_card') || auth()->user()->can('superadmin') || auth()->user()->can('admin'))
                        <button type="button"
                            {{-- class="tw-font-bold tw-text-gray-700 tw-cursor-pointer tw-text-xs md:tw-text-sm tw-flex tw-flex-col tw-items-center tw-justify-center tw-gap-1  no-print @if (!empty($pos_settings['disable_suspend']))  @endif pos-express-finalize @if (!array_key_exists('card', $payment_types)) hide @endif @if ($is_mobile) col-xs-6 @endif" --}}
                            class="tw-bg-sky-800 hover:tw-bg-[#414aac]  {{ $button_class_m }}  @if (!empty($pos_settings['disable_suspend']))  @endif pos-express-finalize @if (!array_key_exists('card', $payment_types)) hide @endif "
                            data-pay_method="card" title="@lang('lang_v1.tooltip_express_checkout_card')">
                            <i class="fas fa-credit-card tw-text-[#D61B60]" aria-hidden="true"></i> @lang('lang_v1.express_checkout_card')
                        </button>
                    @endif

                    @if (empty($edit))
                        <button type="button"
                            class="tw-hidden btn-danger hover:tw-bg-[#414aac]  {{ $button_class_m }} "
                            id="pos-cancel"> <i class="fas fa-window-close"></i> @lang('sale.cancel')</button>
                    @else
                        <button type="button"
                            {{-- class="btn-danger tw-dw-btn hide @if ($is_mobile) col-xs-6 @else tw-dw-btn-xs @endif" --}}
                            class="btn-danger tw-dw-btn hide "

                            id="pos-delete" @if (!empty($only_payment)) disabled @endif> <i
                                class="fas fa-trash-alt"></i> @lang('messages.delete')</button>
                    @endif


                </div>
                <div class="tw-w-full md:tw-w-fit tw-flex tw-flex-col tw-items-end tw-gap-3 tw-hidden md:tw-block">
                    @if (!isset($pos_settings['hide_recent_trans']) || $pos_settings['hide_recent_trans'] == 0)
                        <button type="button"
                            class="tw-font-bold tw-bg-[#646EE4] hover:tw-bg-[#414aac] tw-rounded-full tw-text-white tw-w-full md:tw-w-fit tw-px-5 {{-- tw-h-11 --}} tw-p-2 tw-cursor-pointer tw-text-xs md:tw-text-sm"
                            data-toggle="modal" data-target="#recent_transactions_modal" id="recent-transactions"> <i
                                class="fas fa-clock"></i> @lang('lang_v1.recent_transactions')</button>
                    @endif
                </div>

        </div>
    </div>
</div>
@if (isset($transaction))
    @include('sale_pos.partials.edit_discount_modal', [
        'sales_discount' => $transaction->discount_amount,
        'discount_type' => $transaction->discount_type,
        'rp_redeemed' => $transaction->rp_redeemed,
        'rp_redeemed_amount' => $transaction->rp_redeemed_amount,
        'max_available' => !empty($redeem_details['points']) ? $redeem_details['points'] : 0,
    ])
@else
    @include('sale_pos.partials.edit_discount_modal', [
        'sales_discount' => $business_details->default_sales_discount,
        'discount_type' => 'percentage',
        'rp_redeemed' => 0,
        'rp_redeemed_amount' => 0,
        'max_available' => 0,
    ])
@endif

@if (isset($transaction))
    @include('sale_pos.partials.edit_order_tax_modal', ['selected_tax' => $transaction->tax_id])
@else
    @include('sale_pos.partials.edit_order_tax_modal', [
        'selected_tax' => $business_details->default_sales_tax,
    ])
@endif

@include('sale_pos.partials.edit_shipping_modal')
