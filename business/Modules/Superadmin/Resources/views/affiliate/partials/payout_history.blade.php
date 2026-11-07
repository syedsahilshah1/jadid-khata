{{--
    Payout history table.
    Shared by the superadmin affiliate detail page and the affiliate dashboard.

    A payout is a record of money that has been sent, so there is nothing to do
    to one afterwards: no actions column, on either page.

    Expects: $payouts
--}}
@component('components.widget', ['title' => __('superadmin::lang.payout_history')])
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>@lang('superadmin::lang.created_at')</th>
                    <th>@lang('superadmin::lang.amount')</th>
                    <th>@lang('superadmin::lang.status')</th>
                    <th>@lang('superadmin::lang.paid_at')</th>
                    <th>@lang('superadmin::lang.payment_transaction_id')</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($payouts as $payout)
                    <tr>
                        <td>{{ @format_datetime($payout->created_at) }}</td>
                        <td><span class="display_currency" data-currency_symbol="true" data-use_page_currency="true">{{ $payout->amount }}</span></td>
                        <td>{{ \Modules\Superadmin\Entities\AffiliatePayout::statuses()[$payout->status] ?? $payout->status }}</td>
                        <td>@if (!empty($payout->paid_at)) {{ @format_datetime($payout->paid_at) }} @endif</td>
                        <td>{{ $payout->transaction_id }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">@lang('superadmin::lang.no_payouts_yet')</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endcomponent
