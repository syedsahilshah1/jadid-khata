@extends('layouts.app')
@section('title', __('gym::lang.subscription'))
@section('content')
    @include('gym::layouts.nav')
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-8">
                <div class="box box-solid">
                    <div class="box-header">
                        <h3 class="box-title"> @lang('gym::lang.add_package')</h3>
                    </div>
                    <div class="box-body">
                        {!! Form::open([
                            'url' => action([\Modules\Gym\Http\Controllers\SubscriptionController::class, 'store']),
                            'method' => 'post',
                            'id' => 'create_subscription',
                        ]) !!}
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="form-group">
                                    <input type="hidden" name="contact_id" value="{{ $contact->id }}" id="contact_id">
                                    {!! Form::label('name', __('contact.customer') . ':*') !!}
                                    {!! Form::text('name', $contact->name, [
                                        'class' => 'form-control name',
                                        'required',
                                        'readonly',
                                    ]) !!}
                                </div>
                                <small
                                    class="text-danger @if (empty($customer_due)) hide @endif contact_due_text"><strong>@lang('account.customer_due'):</strong>
                                    <span>{{ $customer_due ?? '' }}</span></small>
                            </div>
                            <small>
                                <strong>
                                    @lang('lang_v1.billing_address'):
                                </strong>
                                <div id="billing_address_div">
                                    {!! $contact->contact_address ?? '' !!}
                                </div>
                            </small>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('package_id', __('gym::lang.packages') . ':*') !!}
                                <select name="package_id" required class="form-control package_id" id="package_id">
                                    <option disabled selected value="">@lang('gym::lang.choose_package')</option>
                                    @foreach ($packages as $key => $package)
                                        <option value="{{ $package->id }}"> {{ $package->name }} -( @format_currency($package->amount) per
                                            {{ $package->duration }} )</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('start_date', __('gym::lang.start_date') . ':') !!}
                                {!! Form::text('start_date', null, [
                                    'class' => 'form-control date_picker',
                                    'readonly',
                                    'required',
                                    'id' => 'start_date',
                                ]) !!}
                            </div>
                        </div>
                        <div class="col-md-6 end_date">
                            <div class="form-group">
                                {!! Form::label('end_date', __('gym::lang.end_date') . ':') !!}
                                {!! Form::text('end_date', null, [
                                    'class' => 'form-control',
                                    'readonly',
                                    'required',
                                    'id' => 'end_date',
                                ]) !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                @component('components.widget')
                    <div class="panel panel-default">
                        <div class="panel-heading status-heading">
                            <h3 class="panel-title">
                                @lang('gym::lang.summary')
                            </h3>
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-xs-6">
                                    <strong>@lang('gym::lang.package_name') :</strong>
                                </div>
                                <div class="col-xs-6 text-right">
                                    <strong class="package_name"></strong>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-6">
                                    <strong>@lang('gym::lang.package_price') :</strong>
                                </div>
                                <div class="col-xs-6 text-right">
                                    <strong class="package_price"></strong>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-6">
                                    <strong>@lang('gym::lang.discount') :</strong>
                                </div>
                                <div class="col-xs-6 text-right">
                                    <strong class="total_discount_show">@format_currency(0)</strong>
                                </div>
                            </div>
                            <div class="row">
                                <hr>
                                <div class="col-xs-6">
                                    <div class="form-group">
                                        {!! Form::number('discount_percent', null, [
                                            'class' => 'form-control',
                                            'id' => 'discount_percent',
                                            'placeholder' => __('gym::lang.discount'),
                                        ]) !!}
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="package_price" id="package_price">
                            <input type="hidden" name="total_discount" id="total_discount">
                            <input type="hidden" name="final_total_input" id="final_total_input">
                            <input type="hidden" name="discount_type" value="percent" id="percent">
                            <div class="row">
                                <div class="col-xs-6">
                                    <strong>@lang('gym::lang.total'):</strong>
                                </div>
                                <div class="col-xs-6 text-right">
                                    <strong class="total">@format_currency(0)</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                @endcomponent
            </div>
            <div class="col-md-12">
                @component('components.widget')
                    <div class="row">
                        <div class="box-body payment_row">
                            @include('hms::partials.payment_row_form', [
                                'row_index' => 0,
                                'show_date' => true,
                                'show_denomination' => true,
                            ])
                        </div>
                    </div>
                    <div class="payment_row">
                        <div class="row">
                            <div class="col-md-12">
                                <hr>
                                <strong>
                                    @lang('lang_v1.change_return'):
                                </strong>
                                <br />
                                <span class="lead text-bold change_return_span">0</span>
                                {!! Form::hidden('change_return', $change_return['amount'], [
                                    'class' => 'form-control change_return input_number',
                                    'required',
                                    'id' => 'change_return',
                                ]) !!}
                                <!-- <span class="lead text-bold total_quantity">0</span> -->
                                @if (!empty($change_return['id']))
                                    <input type="hidden" name="change_return_id" value="{{ $change_return['id'] }}">
                                @endif
                            </div>
                        </div>
                        <div class="hide payment_row" id="change_return_payment_data">
                            <div class="col-md-6">
                                <div class="form-group">
                                    {!! Form::label('change_return_method', __('lang_v1.change_return_payment_method') . ':*') !!}
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="fas fa-money-bill-alt"></i>
                                        </span>
                                        @php
                                            $_payment_method =
                                                empty($change_return['method']) &&
                                                array_key_exists('cash', $payment_types)
                                                    ? 'cash'
                                                    : $change_return['method'];

                                            $_payment_types = $payment_types;
                                            if (isset($_payment_types['advance'])) {
                                                unset($_payment_types['advance']);
                                            }
                                        @endphp
                                        {!! Form::select('payment[change_return][method]', $_payment_types, $_payment_method, [
                                            'class' => 'form-control col-md-12 payment_types_dropdown',
                                            'id' => 'change_return_method',
                                            'style' => 'width:100%;',
                                        ]) !!}
                                    </div>
                                </div>
                            </div>
                            @if (!empty($accounts))
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {!! Form::label('change_return_account', __('lang_v1.change_return_payment_account') . ':') !!}
                                        <div class="input-group">
                                            <span class="input-group-addon">
                                                <i class="fas fa-money-bill-alt"></i>
                                            </span>
                                            {!! Form::select(
                                                'payment[change_return][account_id]',
                                                $accounts,
                                                !empty($change_return['account_id']) ? $change_return['account_id'] : '',
                                                ['class' => 'form-control select2', 'id' => 'change_return_account', 'style' => 'width:100%;'],
                                            ) !!}
                                        </div>
                                    </div>
                                </div>
                            @endif
                            @include('hms::partials.payment_type_details', [
                                'payment_line' => $change_return,
                                'row_index' => 'change_return',
                            ])
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="pull-right"><strong>@lang('lang_v1.balance'):</strong> <span
                                        class="balance_due">0.00</span></div>
                            </div>
                        </div>
                    </div>
                @endcomponent
            </div>

            <div class="col-md-12 text-center">
                <button type="submit" name="submit_action" value="save" id="submit_action"
                    class="tw-dw-btn tw-dw-btn-primary tw-text-white tw-dw-btn-lg">@lang('messages.save')</button>
            </div>
            {!! Form::close() !!}
        </div>
    </section>
    <div class="modal fade payment_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>
    <div class="modal fade edit_payment_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">

    <!-- /.content -->
@endsection

@section('javascript')
<script src="{{ asset('js/payment.js?v=' . $asset_v) }}"></script>

    <script>
        $(document).ready(function() {
            var currentDate = new Date();
            var currentDateTime = moment(currentDate);

            $('.date_picker').datetimepicker({
                format: moment_date_format,
                ignoreReadonly: true,
                defaultDate: currentDateTime
            });

            $('.date_picker').on('dp.change', function(e) {
                var id = $('#package_id').val();
                var start_date = $('#start_date').val();
                get_end_date(id, start_date);
            });

            $('#package_id').on('change', function(e) {
                var id = $(this).val();
                var start_date = $('#start_date').val();
                get_end_date(id, start_date);
            });

            async function get_end_date(id, start_date) {
                if (id === null) {
                    alert('select package');
                    return false;
                }
                try {
                    const result = await $.ajax({
                        url: "{{ route('get_end_date') }}",
                        dataType: 'json',
                        data: {
                            'id': id,
                            'start_date': start_date,
                        }
                    });

                    if (result.status) {
                        $('#end_date').val(result.end_date);
                        $('.package_name').text(result.package.name);
                        $('.package_price').text(__currency_trans_from_en(result.package.amount, true));
                        $('#package_price').val(result.package.amount);
                    }

                    // Call calculate_total after the data is updated
                    calculate_total();

                } catch (error) {
                    console.error("Error fetching end date:", error);
                }
            }

            $("form#create_subscription").validate();


            $("#discount_percent").keyup(function() {
                if ($('#discount_percent').val() != '' && parseFloat($('#discount_percent').val()) > 0) {
                    // Ensure discount_percent and package_price are numeric
                    var discount_percent = parseFloat($('#discount_percent').val()) || 0;
                    var package_price = parseFloat($('#package_price').val()) || 0;

                    // Calculate the total discount based on the percentage
                    var total_discount = (discount_percent * package_price) / 100;

                    // Update the total discount field and the displayed value
                    $('#total_discount').val(total_discount);
                    $('.total_discount_show').text(__currency_trans_from_en(total_discount, true));
                }
                calculate_total();
            });

            function calculate_total() {
                var package_price = parseFloat($('#package_price').val()) || 0;
                var discount = parseFloat($('#total_discount').val()) || 0;
                var total = (package_price - discount);
                $('.total').text(__currency_trans_from_en(total, true));
                $('#final_total_input').val(total);
                $('.payment-amount').val(total);
            }

            // {{-- payment code  --}}

            $(document).on('change', '.payment_types_dropdown', function(e) {
                var default_accounts = $('select#select_location_id').length ?
                    $('select#select_location_id')
                    .find(':selected')
                    .data('default_payment_accounts') : $('#location_id').data('default_payment_accounts');
                var payment_type = $(this).val();
                var payment_row = $(this).closest('.payment_row');
                if (payment_type && payment_type != 'advance') {
                    var default_account = default_accounts && default_accounts[payment_type]['account'] ?
                        default_accounts[payment_type]['account'] : '';
                    var row_index = payment_row.find('.payment_row_index').val();

                    var account_dropdown = payment_row.find('select#account_' + row_index);
                    if (account_dropdown.length && default_accounts) {
                        account_dropdown.val(default_account);
                        account_dropdown.change();
                    }
                }

                //Validate max amount and disable account if advance 
                amount_element = payment_row.find('.payment-amount');
                account_dropdown = payment_row.find('.account-dropdown');
                if (payment_type == 'advance') {
                    max_value = $('#advance_balance').val();
                    msg = $('#advance_balance').data('error-msg');
                    amount_element.rules('add', {
                        'max-value': max_value,
                        messages: {
                            'max-value': msg,
                        },
                    });
                    if (account_dropdown) {
                        account_dropdown.prop('disabled', true);
                        account_dropdown.closest('.form-group').addClass('hide');
                    }
                } else {
                    amount_element.rules("remove", "max-value");
                    if (account_dropdown) {
                        account_dropdown.prop('disabled', false);
                        account_dropdown.closest('.form-group').removeClass('hide');
                    }
                }
            });


            $(document).on('change', '.payment-amount', function() {
                calculate_balance_due();
            });

            function calculate_balance_due() {
                var total_payable = __read_number($('#final_total_input'));
                var total_paying = 0;
                $('.payment-amount')
                    .each(function() {
                        if (parseFloat($(this).val())) {
                            total_paying += __read_number($(this));
                        }
                    });
                var bal_due = total_payable - total_paying;
                var change_return = 0;

                //change_return
                if (bal_due < 0 || Math.abs(bal_due) < 0.05) {
                    __write_number($('input#change_return'), bal_due * -1);
                    $('span.change_return_span').text(__currency_trans_from_en(bal_due * -1, true));
                    change_return = bal_due * -1;
                    bal_due = 0;
                } else {
                    __write_number($('input#change_return'), 0);
                    $('span.change_return_span').text(__currency_trans_from_en(0, true));
                    change_return = 0;

                }

                if (change_return !== 0) {
                    $('#change_return_payment_data').removeClass('hide');
                } else {
                    $('#change_return_payment_data').addClass('hide');
                }

                __write_number($('input#total_paying_input'), total_paying);
                $('span.total_paying').text(__currency_trans_from_en(total_paying, true));

                __write_number($('input#in_balance_due'), bal_due);
                $('span.balance_due').text(__currency_trans_from_en(bal_due, true));

                __highlight(bal_due * -1, $('span.balance_due'));
                __highlight(change_return * -1, $('span.change_return_span'));
            }


            subscription_table = $('#subscription_table').DataTable({
                processing: true,
                serverSide: true,
                fixedHeader: false,
                ajax: {
                    url: "{{ action([\Modules\Gym\Http\Controllers\SubscriptionController::class, 'index']) }}",
                    "data": function(d) {
                            d.customer_id = $('#contact_id').val();
                    },
                },
                aaSorting: [
                    [6, 'desc']
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
        });
    </script>
@endsection
