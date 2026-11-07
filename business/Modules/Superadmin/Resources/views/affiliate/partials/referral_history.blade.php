{{--
    Referral history table: businesses registered through the referral link.

    Expects: $referrals
--}}
@component('components.widget', ['title' => __('superadmin::lang.referral_history')])
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>@lang('superadmin::lang.business')</th>
                    <th>@lang('superadmin::lang.registered_at')</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($referrals as $referral)
                    <tr>
                        <td>{{ optional($referral->business)->name }}</td>
                        <td>@if (!empty($referral->registered_at)) {{ @format_datetime($referral->registered_at) }} @endif</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="text-center">@lang('superadmin::lang.no_referrals_yet')</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endcomponent
