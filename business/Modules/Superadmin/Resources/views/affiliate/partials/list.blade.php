{{--
    Affiliate list, rendered inside the Affiliate Accounts tab.
    Fed by SuperadminAffiliatesController@index over ajax.

    No widget wrapper and no heading of its own: the tab pane is already the
    box, and the tab already carries the title. Wrapping it again produced a
    card inside a card with the same words written twice.
--}}
<div class="row">
    <div class="col-sm-3">
        <div class="form-group">
            {!! Form::label('affiliate_status_filter', __('superadmin::lang.show') . ':') !!}
            {!! Form::select('affiliate_status_filter', $affiliate_statuses, null, ['class' => 'form-control', 'id' => 'affiliate_status_filter', 'style' => 'width:100%', 'placeholder' => __('messages.all')]) !!}
        </div>
    </div>
</div>

{{--
    The form opens here and closes after the table, so the row checkboxes sit
    inside it. They are named affiliate_ids[] and the browser submits the
    ticked ones by itself - no javascript is involved in carrying the selection
    to the server, which is what makes the payout flow work even if a script on
    the page fails.

    The form must wrap whole rows rather than start inside one, or the browser
    closes it early at the end of the element it was opened in and the table
    falls outside it.

    It is a GET because nothing is written here: it only opens the payout page,
    which stays refreshable and reachable with the back button.
--}}
{!! Form::open([
    'url' => action([\Modules\Superadmin\Http\Controllers\SuperadminAffiliatesController::class, 'preparePayout']),
    'method' => 'get',
    'id' => 'affiliate_bulk_form',
]) !!}

{{--
    Always visible, but the button is disabled until rows are ticked. Hiding it
    entirely made the capability undiscoverable: the page showed money columns
    with no apparent way to pay anyone.
--}}
<div class="row">
    <div class="col-xs-12" id="affiliate_bulk_bar">
        <div class="tw-flex tw-flex-col sm:tw-flex-row tw-items-center tw-gap-3 tw-p-3 tw-mb-3 tw-bg-gray-50 tw-rounded-lg tw-ring-1 tw-ring-gray-200">
            <span class="tw-text-sm tw-font-medium tw-text-gray-700 tw-whitespace-nowrap tw-flex-1" id="affiliate_bulk_summary"></span>

            <button type="submit" id="affiliate_bulk_pay" disabled
                class="tw-dw-btn tw-dw-btn-sm tw-shrink-0 tw-whitespace-nowrap tw-bg-gradient-to-r tw-from-indigo-600 tw-to-blue-500 tw-font-bold tw-text-white tw-border-none tw-rounded-full">
                @lang('superadmin::lang.pay_selected')
            </button>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xs-12">
        <div class="table-responsive">
            <table class="table table-bordered table-striped tw-w-full" id="affiliates_table" style="width:100%">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="select-all-row" data-table-id="affiliates_table"></th>
                        <th>@lang('superadmin::lang.affiliate')</th>
                        <th>@lang('superadmin::lang.account_status')</th>
                        <th title="@lang('superadmin::lang.to_approve_help')">@lang('superadmin::lang.to_approve')</th>
                        <th>@lang('superadmin::lang.payable_amount')</th>
                        <th>@lang('superadmin::lang.paid_amount')</th>
                        <th>@lang('superadmin::lang.applied_on')</th>
                        <th>@lang('superadmin::lang.action')</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

{!! Form::close() !!}
