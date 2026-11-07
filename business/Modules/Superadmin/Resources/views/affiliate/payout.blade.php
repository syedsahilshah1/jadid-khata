{{--
    Step two of paying affiliates: the amounts are shown and can be changed
    before anything is written.

    Reached from the affiliate list, which passes the ticked ids in the query
    string. Nothing here is written until the form below is submitted, so the
    page is a plain GET: it can be refreshed, and coming back to it re-reads the
    balances instead of asking the browser to send the selection again.

    Expects: $rows (affiliates carrying ->owed), $minimum_payout
--}}
@extends('layouts.app')
@section('title', __('superadmin::lang.superadmin') . ' | ' . __('superadmin::lang.record_payouts'))

@section('content')
    @include('superadmin::layouts.nav')

    <section class="content-header">
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">
            @lang('superadmin::lang.record_payouts')
        </h1>
    </section>

    <section class="content">
    @include('superadmin::layouts.partials.currency')
        <div class="row">
            <div class="col-xs-12">
                {{-- No title: the page heading above already says it. --}}
                @component('components.widget')
                    <div class="col-xs-12">
                        <p class="text-muted">@lang('superadmin::lang.record_payouts_help')</p>

                        {!! Form::open([
                            'url' => action([\Modules\Superadmin\Http\Controllers\SuperadminAffiliatesController::class, 'storeBulkPayout']),
                            'method' => 'post',
                            'id' => 'affiliate_payout_form',
                        ]) !!}
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped tw-w-full" id="affiliate_payout_table">
                                <thead>
                                    <tr>
                                        <th>@lang('superadmin::lang.affiliate')</th>
                                        <th class="tw-text-right">@lang('superadmin::lang.payable_amount')</th>
                                        <th>@lang('superadmin::lang.paying_amount')</th>
                                        <th>@lang('superadmin::lang.payment_transaction_id')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($rows as $row)
                                        {{--
                                            An affiliate owed less than the minimum cannot be paid at
                                            all, so the row states why instead of offering an input
                                            no value could satisfy: the allowed range would run from
                                            the minimum up to a smaller number.
                                        --}}
                                        @php
                                            $below_minimum = $row->owed < $minimum_payout;
                                        @endphp
                                        <tr>
                                            <td>
                                                <strong>{{ optional($row->user)->user_full_name }}</strong><br>
                                                <small class="text-muted">{{ optional($row->user)->email }}</small>
                                            </td>
                                            <td class="tw-text-right">
                                                <span class="display_currency tw-font-mono" data-currency_symbol="true" data-use_page_currency="true">{{ $row->owed }}</span>
                                            </td>
                                            <td>
                                                @if ($below_minimum)
                                                    <span class="text-muted">
                                                        {{ __('superadmin::lang.below_minimum_payout', ['minimum' => @num_format($minimum_payout)]) }}
                                                    </span>
                                                @else
                                                    {{--
                                                        The same money input the rest of the app uses:
                                                        a text field with .input_number, its value run
                                                        through the num_format directive, and the
                                                        ceiling enforced by the app's own max-value
                                                        validator.

                                                        A number field rejected the very value it had
                                                        been given, because a balance of 19.999 is not
                                                        a multiple of a 0.01 step. No field in this
                                                        application takes money that way.

                                                        Rule params are raw numbers and the messages
                                                        are formatted, matching how the POS screens
                                                        write their min-value rules. Note that a bare
                                                        directive inside a blade comment is still
                                                        compiled, so none may appear above.
                                                    --}}
                                                    {!! Form::text('payouts[' . $row->id . '][amount]', @num_format($row->owed), [
                                                        'class' => 'form-control input_number payout-amount',
                                                        'data-rule-max-value' => $row->owed,
                                                        'data-msg-max-value' => __('superadmin::lang.payout_exceeds_owed', ['owed' => @num_format($row->owed)]),
                                                        'data-rule-min-value' => $minimum_payout,
                                                        'data-msg-min-value' => __('superadmin::lang.below_minimum_payout', ['minimum' => @num_format($minimum_payout)]),
                                                        'data-min-value' => __('superadmin::lang.below_minimum_payout', ['minimum' => @num_format($minimum_payout)]),
                                                    ]) !!}
                                                @endif
                                            </td>
                                            <td>
                                                @if (! $below_minimum)
                                                    {!! Form::text('payouts[' . $row->id . '][transaction_id]', null, [
                                                        'class' => 'form-control',
                                                        'maxlength' => 255,
                                                        'placeholder' => __('superadmin::lang.payment_transaction_id_placeholder'),
                                                    ]) !!}
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th class="tw-text-right">@lang('superadmin::lang.total')</th>
                                        <th class="tw-text-right">
                                            <span class="display_currency tw-font-mono" data-currency_symbol="true" data-use_page_currency="true">{{ $rows->sum('owed') }}</span>
                                        </th>
                                        {{-- Recomputed as the amounts are edited, so it always matches what will be paid. --}}
                                        <th class="tw-text-right">
                                            <span id="payout_total" class="tw-font-mono"></span>
                                        </th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <p class="text-muted">@lang('superadmin::lang.record_payouts_skip_help')</p>

                        <div class="tw-flex tw-gap-3 tw-mt-4">
                            <button type="submit"
                                class="tw-dw-btn tw-bg-gradient-to-r tw-from-indigo-600 tw-to-blue-500 tw-font-bold tw-text-white tw-border-none tw-rounded-full">
                                @lang('superadmin::lang.record_payments')
                            </button>
                            <a href="{{ action([\Modules\Superadmin\Http\Controllers\AffiliateSettingsController::class, 'edit']) }}"
                                class="tw-dw-btn tw-dw-btn-outline tw-rounded-full">
                                @lang('messages.cancel')
                            </a>
                        </div>
                        {!! Form::close() !!}
                    </div>
                @endcomponent
            </div>
        </div>
    </section>
@endsection

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            /*
             * The running total is the sum of what is actually in the boxes,
             * not of what is owed: a cleared or reduced amount has to be
             * visible in the figure before the payments are recorded.
             */
            var refreshTotal = function() {
                var total = 0;

                /*
                 * __number_uf reads a value written in the business's own
                 * number format, and __currency_trans_from_en writes it back
                 * the same way. parseFloat and toFixed would disagree with the
                 * rest of the page anywhere a comma is the decimal separator.
                 */
                $('.payout-amount').each(function() {
                    total += __number_uf($(this).val()) || 0;
                });

                $('#payout_total').text(__currency_trans_from_en(total, true));
            };

            $('#affiliate_payout_table').on('input', '.payout-amount', refreshTotal);

            refreshTotal();

            $('#affiliate_payout_form').validate({
                submitHandler: function(form) {
                    //Every box cleared means nothing was chosen to pay.
                    var filled = $('.payout-amount').filter(function() {
                        return $.trim($(this).val()) !== '';
                    });

                    if (filled.length === 0) {
                        toastr.error(@json(__('superadmin::lang.payout_amount_required')));

                        return false;
                    }

                    //Disabled only now that the form is known to be valid.
                    $(form).find('button[type="submit"]').prop('disabled', true);

                    form.submit();
                }
            });
        });
    </script>
@endsection
