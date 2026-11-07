@extends('layouts.app')
@section('title', __('superadmin::lang.affiliate_dashboard'))

@section('content')
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">
        @lang('superadmin::lang.affiliate_dashboard')
    </h1>
</section>

<!-- Main content -->
<section class="content">
    @include('superadmin::layouts.partials.currency')
    <div class="row">
        <div class="col-xs-12">
            @component('components.widget', ['title' => __('superadmin::lang.referral_link')])
                @include('superadmin::affiliate.partials.referral_link', [
                    'link' => $affiliate->referral_link,
                    'help' => __('superadmin::lang.referral_link_help'),
                ])
            @endcomponent
        </div>
    </div>

    {{--
        The same four figures, in the same order, as the superadmin's affiliate
        page: both screens describe one affiliate, and two different summaries
        of the same account is how the two start disagreeing.
    --}}
        {{-- AFF-RATES: what this affiliate earns --}}
    @php($aff_rates = app(\Modules\Superadmin\Utils\AffiliateUtil::class)->ratesFor($affiliate))
    <div class="alert alert-info">
        Your commission: <b>{{ $aff_rates['first'] }}%</b> on a business's first subscription
        @if ($aff_rates['renewal'] > 0)
            and <b>{{ $aff_rates['renewal'] }}%</b> on every renewal after that.
        @else
            (renewals earn no commission).
        @endif
    </div>
        <div class="tw-grid tw-grid-cols-1 tw-gap-4 tw-mb-4 sm:tw-grid-cols-2 xl:tw-grid-cols-4 sm:tw-gap-5">
        @include('superadmin::affiliate.partials.stat_card', [
            'label' => __('superadmin::lang.click_count'),
            'value' => $stats['click_count'],
            'bg' => 'tw-bg-sky-100', 'text' => 'tw-text-sky-500',
            'icon' => '<path d="M7.904 17.563a1.2 1.2 0 0 0 2.228 .308l2.09 -3.093l4.907 4.907a1.067 1.067 0 0 0 1.509 0l1.047 -1.047a1.067 1.067 0 0 0 0 -1.509l-4.907 -4.907l3.113 -2.09a1.2 1.2 0 0 0 -.309 -2.228l-13.582 -3.904l3.904 13.563z"></path>',
        ])

        @include('superadmin::affiliate.partials.stat_card', [
            'label' => __('superadmin::lang.registration_count'),
            'value' => $stats['registration_count'],
            'bg' => 'tw-bg-green-100', 'text' => 'tw-text-green-600',
            'icon' => '<path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0"></path><path d="M16 19h6"></path><path d="M19 16v6"></path><path d="M6 21v-2a4 4 0 0 1 4 -4h4"></path>',
        ])

        @include('superadmin::affiliate.partials.stat_card', [
            {{-- What is owed right now: approved commission less what has been paid. --}}
            'label' => __('superadmin::lang.payable_amount'),
            'value' => $stats['payable_commission'], 'is_currency' => true,
            'bg' => 'tw-bg-yellow-100', 'text' => 'tw-text-yellow-600',
            'icon' => '<path d="M6.5 7h11"></path><path d="M6.5 17h11"></path><path d="M6 20v-2a6 6 0 1 1 12 0v2a1 1 0 0 1 -1 1h-10a1 1 0 0 1 -1 -1z"></path><path d="M6 4v2a6 6 0 1 0 12 0v-2a1 1 0 0 0 -1 -1h-10a1 1 0 0 0 -1 1z"></path>',
        ])

        @include('superadmin::affiliate.partials.stat_card', [
            'label' => __('superadmin::lang.paid_amount'),
            'value' => $stats['paid_commission'], 'is_currency' => true,
            'bg' => 'tw-bg-sky-100', 'text' => 'tw-text-sky-600',
            'icon' => '<path d="M3 6m0 2a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v8a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2z"></path><path d="M12 12m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"></path><path d="M6 12l.01 0"></path><path d="M18 12l.01 0"></path>',
        ])
    </div>

    <div class="row">
        <div class="col-xs-12">
            @include('superadmin::affiliate.partials.referral_history')
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12">
            {{-- AFF-TEAM5: team head adds members; a member sees its head --}}
            @if (! empty($affiliate->parent_affiliate_user_id))
                <div class="alert alert-success">
                    Your team head: <b>{{ optional(\App\User::find($affiliate->parent_affiliate_user_id))->user_full_name }}</b>
                </div>
            @endif
            @if ((float) $affiliate->team_commission_amount > 0)
                @component('components.widget', ['title' => 'Add a member to your team'])
                    {!! Form::open(['url' => route('affiliate.team.add'), 'method' => 'post']) !!}
                        <div class="col-sm-8">
                            <div class="form-group">
                                {!! Form::label('member', "Member's email or affiliate code:") !!}
                                {!! Form::text('member', null, ['class' => 'form-control', 'required', 'placeholder' => 'e.g. ali@example.com or AB12CD']) !!}
                                <small class="text-muted">The person must already be an approved affiliate and not in another team. Only the admin can remove members.</small>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group" style="margin-top: 25px;">
                                <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-text-white">Add to my team</button>
                            </div>
                        </div>
                    {!! Form::close() !!}
                @endcomponent
            @endif
            @include('superadmin::affiliate.partials.team') {{-- AFF-TEAM --}}
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12">
            @include('superadmin::affiliate.partials.commission_history')
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12">
            @include('superadmin::affiliate.partials.payout_history')
        </div>
    </div>
</section>
@stop

@section('javascript')
    @include('superadmin::affiliate.partials.referral_link_js')
@endsection
