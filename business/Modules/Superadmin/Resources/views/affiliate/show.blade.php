@extends('layouts.app')
@section('title', __('superadmin::lang.superadmin') . ' | ' . __('superadmin::lang.affiliate_details'))

@section('content')
    @include('superadmin::layouts.nav')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">
            @lang('superadmin::lang.affiliate_details')
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
    @include('superadmin::layouts.partials.currency')
        <div class="row">
            <div class="col-xs-12">
                @component('components.widget', ['title' => __('superadmin::lang.account')])
                    {{-- No "Affiliate:" label: the name and email speak for themselves. --}}
                    <div class="col-sm-4">
                        <strong>{{ optional($affiliate->user)->user_full_name }}</strong><br>
                        <small class="text-muted">{{ optional($affiliate->user)->email }}</small>
                    </div>
                    <div class="col-sm-4">
                        <strong>@lang('superadmin::lang.account_status'):</strong><br>
                        @if ($affiliate->status == 'approved')
                            <span class="label bg-light-green">{{ __('superadmin::lang.affiliate_approved') }}</span>
                        @elseif ($affiliate->status == 'pending')
                            <span class="label bg-aqua">{{ __('superadmin::lang.affiliate_pending') }}</span>
                        @elseif ($affiliate->status == 'suspended')
                            <span class="label bg-yellow">{{ __('superadmin::lang.affiliate_suspended') }}</span>
                        @else
                            <span class="label bg-red">{{ __('superadmin::lang.affiliate_rejected') }}</span>
                        @endif
                    </div>
                    <div class="clearfix"></div>
                    <div class="col-xs-12" style="margin-top: 15px;">
                        @include('superadmin::affiliate.partials.referral_link', [
                            'link' => $affiliate->referral_link,
                        ])
                    </div>
                @endcomponent
            </div>
        </div>

                {{-- AFF-RATES: this affiliate's own commission rates --}}
        @php($aff_util = app(\Modules\Superadmin\Utils\AffiliateUtil::class))
        @php($aff_rates = $aff_util->ratesFor($affiliate))
        <div class="row">
            <div class="col-xs-12">
                @component('components.widget', ['title' => 'Commission rates'])
                    {!! Form::open(['url' => action([\Modules\Superadmin\Http\Controllers\SuperadminAffiliatesController::class, 'updateRates'], [$affiliate->id]), 'method' => 'post']) !!}
                        <div class="col-sm-4">
                            <div class="form-group">
                                {!! Form::label('first_commission_percentage', 'First subscription (%):') !!}
                                {!! Form::number('first_commission_percentage', $affiliate->first_commission_percentage, ['class' => 'form-control', 'step' => '0.01', 'min' => '0', 'max' => '100', 'placeholder' => 'Default: ' . $aff_util->commissionPercentage() . '%']) !!}
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                {!! Form::label('renewal_commission_percentage', 'Every renewal (%):') !!}
                                {!! Form::number('renewal_commission_percentage', $affiliate->renewal_commission_percentage, ['class' => 'form-control', 'step' => '0.01', 'min' => '0', 'max' => '100', 'placeholder' => 'Default: ' . $aff_util->renewalPercentage() . '%']) !!}
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group" style="margin-top: 25px;">
                                <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-text-white">Save rates</button>
                            </div>
                        </div>
                        <div class="col-xs-12">
                            <small class="text-muted">
                                Now applied: <b>{{ $aff_rates['first'] }}%</b> on the first subscription, <b>{{ $aff_rates['renewal'] }}%</b> on every renewal
                                ({{ $aff_rates['custom'] ? 'own rates' : 'default rates' }}). Leave a box empty to use the default from Affiliate settings.
                            </small>
                        </div>
                    {!! Form::close() !!}
                @endcomponent
            </div>
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
                {{-- Payable, not pending: this is the figure a payout settles. --}}
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
                @component('components.widget', ['title' => __('superadmin::lang.pay_this_affiliate')])
                    <div class="col-xs-12">
                        <p class="text-muted">@lang('superadmin::lang.payout_help')</p>

                        @if ($payable_amount > 0 && $payable_amount >= $minimum_payout)
                            {{-- Shown here too: this is the figure the button is about to pay. --}}
                            <p class="tw-text-sm tw-font-medium tw-text-gray-500 tw-mb-1">
                                @lang('superadmin::lang.payable_amount')
                            </p>
                            <p class="tw-block tw-mb-3 tw-text-2xl tw-font-semibold tw-text-gray-900 tw-font-mono display_currency"
                                data-currency_symbol="true">{{ $payable_amount }}</p>

                            {{--
                                Same two fields as the bulk payout page, so paying one
                                affiliate from here and paying them from the list record
                                the same thing.
                            --}}
                            {!! Form::open([
                                'action' => ['\Modules\Superadmin\Http\Controllers\SuperadminAffiliatesController@storePayout', $affiliate->id],
                                'method' => 'post',
                                'id' => 'affiliate_payout_form',
                            ]) !!}
                                <div class="row">
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            {!! Form::label('amount', __('superadmin::lang.paying_amount') . ':') !!}
                                            {{-- Prefilled with the full balance: paying it all is the usual case. --}}
                                            {!! Form::text('amount', @num_format($payable_amount), [
                                                'class' => 'form-control input_number',
                                                'required',
                                                'data-rule-max-value' => $payable_amount,
                                                'data-msg-max-value' => __('superadmin::lang.payout_exceeds_owed', ['owed' => @num_format($payable_amount)]),
                                                'data-rule-min-value' => $minimum_payout,
                                                'data-msg-min-value' => __('superadmin::lang.below_minimum_payout', ['minimum' => @num_format($minimum_payout)]),
                                                'data-min-value' => __('superadmin::lang.below_minimum_payout', ['minimum' => @num_format($minimum_payout)]),
                                            ]) !!}
                                        </div>
                                    </div>
                                    <div class="col-sm-5">
                                        <div class="form-group">
                                            {!! Form::label('transaction_id', __('superadmin::lang.payment_transaction_id') . ':') !!}
                                            {!! Form::text('transaction_id', null, [
                                                'class' => 'form-control',
                                                'maxlength' => 255,
                                                'placeholder' => __('superadmin::lang.payment_transaction_id_placeholder'),
                                            ]) !!}
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group" style="margin-top: 25px;">
                                            <button type="submit" class="tw-dw-btn tw-bg-gradient-to-r tw-from-indigo-600 tw-to-blue-500 tw-font-bold tw-text-white tw-border-none tw-rounded-full">
                                                @lang('superadmin::lang.payout')
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            {!! Form::close() !!}
                        @else
                            <div class="alert alert-info">
                                @if ($payable_amount <= 0)
                                    @lang('superadmin::lang.no_payable_commission')
                                @else
                                    {{ __('superadmin::lang.below_minimum_payout', ['minimum' => @num_format($minimum_payout)]) }}
                                @endif
                            </div>
                        @endif
                    </div>
                @endcomponent
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12">
                @include('superadmin::affiliate.partials.referral_history')
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12">
                
        {{-- AFF-TEAM: upline and team commission of this affiliate --}}
        @php($aff_util2 = app(\Modules\Superadmin\Utils\AffiliateUtil::class))
        @php($aff_below = collect($aff_util2->teamOf($affiliate))->map(function ($t) { return (int) $t['affiliate']->user_id; })->all())
        @php($aff_uplines = \Modules\Superadmin\Entities\Affiliate::with('user')->where('user_id', '!=', $affiliate->user_id)->whereNotIn('user_id', $aff_below ?: [0])->get())
        <div class="row">
            <div class="col-xs-12">
                @component('components.widget', ['title' => 'Team commission & upline'])
                    {!! Form::open(['url' => action([\Modules\Superadmin\Http\Controllers\SuperadminAffiliatesController::class, 'updateTeam'], [$affiliate->id]), 'method' => 'post']) !!}
                        <div class="col-sm-4">
                            <div class="form-group">
                                {!! Form::label('team_commission_amount', 'Team commission (only this affiliate):') !!} {{-- AFF-TEAM2 --}}
                                <div class="input-group">
                                    {!! Form::number('team_commission_amount', $affiliate->team_commission_amount !== null ? (float) $affiliate->team_commission_amount : null, ['class' => 'form-control', 'step' => '0.01', 'min' => '0', 'placeholder' => 'Empty = no team commission']) !!}
                                    <span class="input-group-btn" style="width: 45%;">
                                        <select name="team_commission_type" class="form-control">
                                            <option value="fixed" @if ($affiliate->team_commission_type !== 'percent') selected @endif>Fixed amount</option>
                                            <option value="percent" @if ($affiliate->team_commission_type === 'percent') selected @endif>% of sale</option>
                                        </select>
                                    </span>
                                </div>
                                <small class="text-muted">Only affiliates with a value here earn from their team (their direct members' referrals). Others get only their normal commission.</small>
                            </div>
                        </div>
                        <div class="col-sm-5">
                            <div class="form-group">
                                {!! Form::label('parent_affiliate_user_id', 'Upline (joined under):') !!}
                                <select name="parent_affiliate_user_id" id="parent_affiliate_user_id" class="form-control">
                                    <option value="">— None (top of the tree) —</option>
                                    @foreach ($aff_uplines as $up)
                                        <option value="{{ $up->user_id }}" @if ((int) $affiliate->parent_affiliate_user_id === (int) $up->user_id) selected @endif>
                                            {{ optional($up->user)->user_full_name }} ({{ optional($up->user)->email }})
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Set automatically when the affiliate's business registered through another affiliate's link.</small>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group" style="margin-top: 25px;">
                                <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-text-white">Save team settings</button>
                            </div>
                        </div>
                    {!! Form::close() !!}
                @endcomponent
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12">
                {{-- AFF-TEAM3: add several members under this affiliate at once --}}
                @php($aff_block = array_merge([(int) $affiliate->user_id], app(\Modules\Superadmin\Utils\AffiliateUtil::class)->uplinesOf($affiliate)))
                @php($aff_candidates = \Modules\Superadmin\Entities\Affiliate::with('user')->whereNotIn('user_id', $aff_block)->where(function ($q) use ($affiliate) { $q->whereNull('parent_affiliate_user_id')->orWhere('parent_affiliate_user_id', '!=', $affiliate->user_id); })->get())
                @component('components.widget', ['title' => 'Add team members'])
                    {!! Form::open(['url' => action([\Modules\Superadmin\Http\Controllers\SuperadminAffiliatesController::class, 'addTeamMembers'], [$affiliate->id]), 'method' => 'post']) !!}
                        <div class="col-sm-9">
                            <div class="form-group">
                                <label for="member_user_ids">Affiliates to put under {{ optional($affiliate->user)->user_full_name }}:</label>
                                <select name="member_user_ids[]" id="member_user_ids" class="form-control select2" multiple style="width: 100%;">
                                    @foreach ($aff_candidates as $c)
                                        <option value="{{ $c->user_id }}">{{ optional($c->user)->user_full_name }} ({{ optional($c->user)->email }}){{ $c->parent_affiliate_user_id ? ' - now under someone else' : '' }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Pick as many as you want (10, 20 ...). This affiliate earns team commission only on their direct referrals, not on people further down. Someone already under another affiliate is moved here.</small>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group" style="margin-top: 25px;">
                                <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-text-white">Add to team</button>
                            </div>
                        </div>
                    {!! Form::close() !!}
                @endcomponent
                @include('superadmin::affiliate.partials.team', ['team_admin' => true])
            </div>
        </div>
        <div class="row"><div class="col-xs-12">
                @include('superadmin::affiliate.partials.commission_history', ['show_actions' => true])
        </div></div>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12">
                @include('superadmin::affiliate.partials.payout_history')
            </div>
        </div>
    </section>
@endsection

@section('javascript')
    @include('superadmin::affiliate.partials.referral_link_js')
    @include('superadmin::affiliate.partials.disable_on_submit_js')
    <script type="text/javascript">
        $(document).ready(function() {
            /*
             * The payout form is only present when there is something to pay,
             * so the rules are attached from the markup and the button is
             * disabled from the submit handler rather than by
             * .disable-on-submit, which fires even when the form is invalid.
             */
            $('#affiliate_payout_form').validate({
                submitHandler: function(form) {
                    $(form).find('button[type="submit"]').prop('disabled', true);

                    form.submit();
                }
            });
        });
    </script>
@endsection
