@extends('layouts.app')
@section('title', __('superadmin::lang.superadmin') . ' | ' . __('superadmin::lang.affiliates'))

@section('content')
@include('superadmin::layouts.nav')
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang('superadmin::lang.affiliates')</h1>
</section>

<!-- Main content -->
<section class="content">
    @include('superadmin::layouts.partials.currency')
    @component('components.widget')
        {{--
            Two jobs, two tabs. The accounts list opens first: it is the day to
            day screen, while the settings are set once and rarely revisited.

            The save button stays inside the settings pane, where it cannot be
            mistaken for applying to the list.
        --}}
        <ul class="nav nav-tabs">
            <li class="active">
                <a href="#affiliate_accounts_tab" data-toggle="tab">
                    <strong>@lang('superadmin::lang.affiliate_accounts')</strong>
                </a>
            </li>
            <li>
                <a href="#affiliate_settings_tab" data-toggle="tab">
                    <strong>@lang('superadmin::lang.program_settings')</strong>
                </a>
            </li>
        </ul>

        {{-- Padded and boxed, so the pane reads as attached to its tab. --}}
        <div class="tab-content tw-p-4 tw-bg-white tw-border tw-border-gray-200">
            <div class="tab-pane active" id="affiliate_accounts_tab">
                @include('superadmin::affiliate.partials.list')
            </div>

            <div class="tab-pane" id="affiliate_settings_tab">
                {!! Form::open([
                    'action' => '\Modules\Superadmin\Http\Controllers\AffiliateSettingsController@update',
                    'method' => 'put',
                    'id' => 'affiliate_settings_form',
                ]) !!}
                    <div class="row">
                        <div class="col-xs-12">
                        <div class="form-group">
                            <label>
                            {!! Form::checkbox('enabled', 1, !empty($settings['enabled']), ['class' => 'input-icheck']); !!}
                            @lang('superadmin::lang.enable_affiliate')
                            </label>
                            <br>
                            <small class="text-muted">@lang('superadmin::lang.enable_affiliate_help')</small>
                        </div>
                    </div>
                    </div>

                    <div class="row">
                    <div class="col-sm-3">
                        <div class="form-group">
                            {!! Form::label('commission_percentage', 'First subscription commission (%):') !!} {{-- AFF-RATES --}}
                            {!! Form::number('commission_percentage', $settings['commission_percentage'], ['class' => 'form-control', 'step' => '0.01', 'min' => '0', 'max' => '100', 'required']) !!}
                            <small class="text-muted">@lang('superadmin::lang.commission_percentage_help')</small>
                        </div>
                    </div>

                    <div class="col-sm-3">
                        <div class="form-group">
                            {!! Form::label('renewal_commission_percentage', 'Every renewal commission (%):') !!}
                            {{-- A select rather than a checkbox, so both choices are named. --}}
                            {!! Form::number('renewal_commission_percentage', $settings['renewal_commission_percentage'], ['class' => 'form-control', 'step' => '0.01', 'min' => '0', 'max' => '100', 'required']) !!}
                            <small class="text-muted">Each later subscription of the same business (renewal). 0 = first subscription only.</small>
                        </div>
                        {{-- AFF-TEAM2: team commission is set per affiliate (View page), not here --}}
                    </div>

                    <div class="col-sm-3">
                        <div class="form-group">
                            {!! Form::label('cookie_days', __('superadmin::lang.affiliate_cookie_duration') . ':') !!}
                            {!! Form::number('cookie_days', $settings['cookie_days'], ['class' => 'form-control', 'min' => '1', 'max' => '3650', 'required']) !!}
                            <small class="text-muted">@lang('superadmin::lang.affiliate_cookie_duration_help')</small>
                        </div>
                    </div>

                    <div class="col-sm-3">
                        <div class="form-group">
                            {!! Form::label('min_payout', __('superadmin::lang.minimum_payout') . ':') !!}
                            {{-- A money field, so written the way every other money field is. --}}
                            {!! Form::text('min_payout', @num_format($settings['min_payout']), ['class' => 'form-control input_number', 'required']) !!}
                            <small class="text-muted">@lang('superadmin::lang.minimum_payout_help')</small>
                        </div>
                    </div>
                    </div>

                    <div class="row">
                    <div class="col-xs-12">
                        <div class="form-group">
                            {!! Form::label('affiliate_terms', __('superadmin::lang.affiliate_terms_and_conditions') . ':') !!}
                            {!! Form::textarea('terms', $settings['terms'], ['class' => 'form-control', 'id' => 'affiliate_terms']) !!}
                        </div>
                    </div>

                    </div>

                    {{-- Inside the settings pane, so its scope is unmistakable. --}}
                    <div class="row">
                    <div class="col-xs-12">
                        <div class="form-group">
                            {{ Form::submit(__('messages.update'), ['class' => "tw-dw-btn tw-dw-btn-primary tw-text-white"]) }}
                        </div>
                    </div>
                    </div>
                {!! Form::close() !!}
            </div>

        </div>
    @endcomponent
</section>
@stop

@section('javascript')
<script type="text/javascript">
    $(document).ready(function() {
        if ($('#affiliate_terms').length) {
            tinymce.init({
                selector: 'textarea#affiliate_terms'
            });
        }

        $('form#affiliate_settings_form').validate({
            /*
             * The terms editor is replaced by TinyMCE, which hides the original
             * textarea; nothing here requires it, but keeping the default
             * ignore rule would also skip the icheck backed enable switch.
             */
            ignore: [],
            rules: {
                commission_percentage: {
                    required: true,
                    number: true,
                    min: 0,
                    max: 100
                },
                cookie_days: {
                    required: true,
                    digits: true,
                    min: 1,
                    max: 3650
                },
                min_payout: {
                    required: true
                }
            },
            messages: {
                commission_percentage: {
                    required: "{{ __('validation.required', ['attribute' => __('superadmin::lang.commission_percentage')]) }}"
                },
                cookie_days: {
                    required: "{{ __('validation.required', ['attribute' => __('superadmin::lang.affiliate_cookie_duration')]) }}"
                },
                min_payout: {
                    required: "{{ __('validation.required', ['attribute' => __('superadmin::lang.minimum_payout')]) }}"
                }
            },
            //Only runs once valid, so a failed check never leaves it disabled.
            submitHandler: function(form) {
                $(form).find('input[type="submit"], button[type="submit"]').prop('disabled', true);

                form.submit();
            }
        });

        var affiliates_table = $('#affiliates_table').DataTable({
            processing: true,
            serverSide: true,
            fixedHeader: false,
            /*
             * Widths are left to css rather than measured and written inline,
             * so the table fills its pane whatever the pane's width turns out
             * to be.
             */
            autoWidth: false,
            ajax: {
                url: "{{ action([\Modules\Superadmin\Http\Controllers\SuperadminAffiliatesController::class, 'index']) }}",
                data: function(d) {
                    d.status = $('#affiliate_status_filter').val();

                    d = __datatable_ajax_callback(d);
                }
            },
            {{-- created_at, the seventh column. --}}
            aaSorting: [
                [6, 'desc']
            ],
            columns: [
                { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false },
                { data: 'affiliate_name', name: 'affiliate_name' },
                { data: 'status', name: 'affiliates.status' },
                /*
                 * Sortable on the alias, which mysql allows in ORDER BY, but not
                 * searchable: a WHERE cannot reference a select alias, so letting
                 * the global search touch these would produce invalid sql.
                 */
                { data: 'to_approve_count', name: 'to_approve_count', searchable: false },
                { data: 'payable_amount', name: 'payable_amount', searchable: false },
                { data: 'paid_amount', name: 'paid_amount', searchable: false },
                { data: 'created_at', name: 'affiliates.created_at' },
                { data: 'action', name: 'action', orderable: false, searchable: false },
            ],
            //Rows arrive by ajax, so the money columns are converted per draw.
            "fnDrawCallback": function(oSettings) {
                __currency_convert_recursively($('#affiliates_table'), true);
            }
        });

        /*
         * The form wraps the table so the row checkboxes submit themselves,
         * which also leaves DataTables' own "show N entries" select inside it.
         * Dropping its name keeps it out of the submitted url. DataTables binds
         * to the element rather than the name, so the control still works.
         */
        $('#affiliates_table_wrapper').find('select[name$="_length"]').removeAttr('name');

        /*
         * The accounts pane opens first, so the table is measured against a
         * visible pane. The re-measure is kept for the trip back from the
         * settings tab, where the pane is hidden and shown again.
         */
        $('a[href="#affiliate_accounts_tab"]').on('shown.bs.tab', function () {
            affiliates_table.columns.adjust();
        });

        $('#affiliate_status_filter').change(function() {
            affiliates_table.ajax.reload();
        });

        /*
         * ---- bulk pay -------------------------------------------------------
         *
         * Only rows with something payable carry a .row-select checkbox, so the
         * shared select-all handler cannot pick an affiliate who has nothing to
         * be paid. The amount sits on each checkbox, which is what lets the bar
         * total the selection without asking the server again.
         */
        var refreshBulkBar = function() {
            var selected = $('#affiliates_table').find('input.row-select:checked');
            var total = 0;

            selected.each(function() {
                total += parseFloat($(this).data('payable')) || 0;
            });

            //The bar stays put; only the button's state and wording change.
            $('#affiliate_bulk_pay')
                .prop('disabled', selected.length === 0)
                .text(
                    "{{ __('superadmin::lang.pay_selected_count') }}"
                        .replace(':count', selected.length)
                );

            $('#affiliate_bulk_summary').text(
                selected.length === 0
                    ? "{{ __('superadmin::lang.bulk_select_hint') }}"
                    : "{{ __('superadmin::lang.bulk_selection_summary') }}"
                        .replace(':count', selected.length)
                        //Formatted by the app's own helper, like every other amount on the page.
                        .replace(':total', __currency_trans_from_en(total, true))
            );
        };

        //Delegated: the rows are replaced on every draw.
        $(document).on('change', '#affiliates_table input.row-select, #select-all-row', refreshBulkBar);

        refreshBulkBar();

        //A redraw drops the selection, so the bar must not linger.
        affiliates_table.on('draw', function() {
            $('#select-all-row').prop('checked', false);
            refreshBulkBar();
        });

        /*
         * The checkboxes are inside the form and carry their own name, so the
         * browser submits the selection. This only stops an empty submit.
         *
         * No confirmation dialog: submitting opens the payout page, where the
         * amounts are shown and can still be changed. Asking "are you sure"
         * before a screen that writes nothing asks about the wrong step.
         */
        $('#affiliate_bulk_form').submit(function() {
            return $('#affiliates_table').find('input.row-select:checked').length > 0;
        });

        /*
         * These are json encoded rather than echoed by blade. A blade echo
         * escapes for html, so an apostrophe would reach the dialog as a raw
         * html entity with nothing to decode it: the confirmation would read
         * "affiliate" followed by an entity instead of an apostrophe.
         * Encoding produces a correctly quoted js string instead.
         *
         * Note for anyone editing this block: a bare blade directive or echo
         * inside a comment is still compiled, so neither may appear above.
         */
        var affiliate_status_prompts = {
            approved: {
                title: @json(__('superadmin::lang.confirm_approve_title')),
                text: @json(__('superadmin::lang.confirm_approve_text')),
                icon: 'info'
            },
            rejected: {
                title: @json(__('superadmin::lang.confirm_reject_title')),
                text: @json(__('superadmin::lang.confirm_reject_text')),
                icon: 'warning'
            },
            suspended: {
                title: @json(__('superadmin::lang.confirm_suspend_title')),
                text: @json(__('superadmin::lang.confirm_suspend_text')),
                icon: 'warning'
            }
        };

        {{-- Matches both the Approve/Reject buttons and the dropdown items. --}}
        $(document).on('click', '.update_affiliate_status', function(e) {
            e.preventDefault();

            var button = $(this);
            var status = button.data('status');

            //Falls back to the generic wording if a new status is ever added.
            var prompt = affiliate_status_prompts[status] || {
                title: LANG.sure,
                text: @json(__('superadmin::lang.confirm_affiliate_status_change')),
                icon: 'warning'
            };

            swal({
                title: prompt.title,
                text: prompt.text,
                icon: prompt.icon,
                buttons: true,
                dangerMode: status !== 'approved',
            }).then((confirmed) => {
                if (confirmed) {
                    $.ajax({
                        method: 'POST',
                        url: button.data('href'),
                        dataType: 'json',
                        data: {
                            status: status,
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(result) {
                            if (result.success === true) {
                                toastr.success(result.msg);
                                affiliates_table.ajax.reload();
                            } else {
                                toastr.error(result.msg);
                            }
                        }
                    });
                }
            });
        });
    });
</script>
@endsection
