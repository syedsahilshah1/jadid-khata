@extends('layouts.app')
@section('title', __('superadmin::lang.become_an_affiliate'))

@section('content')
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang('superadmin::lang.become_an_affiliate')</h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-xs-12">
            @if (!empty($affiliate))
                {{-- The user already applied, so show where the application stands. --}}
                @component('components.widget', ['title' => __('superadmin::lang.affiliate_application_status')])
                    <div class="col-xs-12">
                        <p>
                            <strong>@lang('superadmin::lang.account_status'):</strong>
                            @if ($affiliate->status == 'approved')
                                <span class="label bg-light-green">{{ __('superadmin::lang.affiliate_approved') }}</span>
                            @elseif ($affiliate->status == 'pending')
                                <span class="label bg-aqua">{{ __('superadmin::lang.affiliate_pending') }}</span>
                            @elseif ($affiliate->status == 'suspended')
                                <span class="label bg-yellow">{{ __('superadmin::lang.affiliate_suspended') }}</span>
                            @else
                                <span class="label bg-red">{{ __('superadmin::lang.affiliate_rejected') }}</span>
                            @endif
                        </p>

                        <div class="alert
                            @if ($affiliate->status == 'approved') alert-success
                            @elseif ($affiliate->status == 'pending') alert-info
                            @else alert-warning @endif">
                            @if ($affiliate->status == 'approved')
                                @lang('superadmin::lang.affiliate_status_approved_msg')
                            @elseif ($affiliate->status == 'pending')
                                @lang('superadmin::lang.affiliate_status_pending_msg')
                            @elseif ($affiliate->status == 'suspended')
                                @lang('superadmin::lang.affiliate_status_suspended_msg')
                            @else
                                @lang('superadmin::lang.affiliate_status_rejected_msg')
                            @endif
                        </div>

                        @if ($affiliate->status == 'approved')
                            <div class="form-group">
                                {!! Form::label('referral_link', __('superadmin::lang.referral_link') . ':') !!}
                                {!! Form::text('referral_link', $affiliate->referral_link, ['class' => 'form-control', 'readonly']) !!}
                            </div>
                        @endif
                    </div>
                @endcomponent
            @else
                {{-- No application yet: show the deal, the terms, then require acceptance. --}}
                {!! Form::open([
                    'action' => '\Modules\Superadmin\Http\Controllers\AffiliateController@store',
                    'method' => 'post',
                    'id' => 'affiliate_apply_form',
                ]) !!}

                @component('components.widget', ['title' => __('superadmin::lang.affiliate_program_details')])
                    <div class="col-xs-12">
                        <div class="tw-grid tw-grid-cols-1 tw-gap-4 sm:tw-grid-cols-2 xl:tw-grid-cols-4 sm:tw-gap-5">
                            @include('superadmin::affiliate.partials.stat_card', [
                                'label' => __('superadmin::lang.commission_percentage'),
                                'value' => $commission_percentage . '% / ' . app(\Modules\Superadmin\Utils\AffiliateUtil::class)->renewalPercentage() . '%', //AFF-RATES: first / renewal
                                'bg' => 'tw-bg-green-100', 'text' => 'tw-text-green-600',
                                'icon' => '<path d="M17 17m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"></path><path d="M7 7m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"></path><path d="M6 18l12 -12"></path>',
                            ])

                            {{-- Short value, since the card truncates; the sentence below the cards explains it. --}}
                            @include('superadmin::affiliate.partials.stat_card', [
                                'label' => __('superadmin::lang.commission_type'),
                                'value' => $commission_recurring ? __('superadmin::lang.every_subscription') : __('superadmin::lang.first_subscription'),
                                'bg' => 'tw-bg-yellow-100', 'text' => 'tw-text-yellow-600',
                                'icon' => $commission_recurring
                                    ? '<path d="M4 12v-3a3 3 0 0 1 3 -3h13m-3 -3l3 3l-3 3"></path><path d="M20 12v3a3 3 0 0 1 -3 3h-13m3 3l-3 -3l3 -3"></path>'
                                    : '<path d="M13 20v-16l-5 5"></path>',
                            ])

                            @include('superadmin::affiliate.partials.stat_card', [
                                'label' => __('superadmin::lang.affiliate_cookie_duration'),
                                'value' => $cookie_days . ' ' . __('superadmin::lang.days'),
                                'bg' => 'tw-bg-sky-100', 'text' => 'tw-text-sky-500',
                                'icon' => '<path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0"></path><path d="M12 7v5l3 3"></path>',
                            ])

                            {{-- A minimum of zero is not a condition, so saying so is noise. --}}
                            @if ($minimum_payout > 0)
                                @include('superadmin::affiliate.partials.stat_card', [
                                    'label' => __('superadmin::lang.minimum_payout'),
                                    'value' => $minimum_payout, 'is_currency' => true,
                                    'bg' => 'tw-bg-primary-100', 'text' => 'tw-text-primary-600',
                                    'icon' => '<path d="M3 6m0 2a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v8a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2z"></path><path d="M12 12m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"></path><path d="M6 12l.01 0"></path><path d="M18 12l.01 0"></path>',
                                ])
                            @endif
                        </div>

                        <p class="tw-mt-4 tw-mb-0 text-muted">
                            {{ $commission_recurring
                                ? __('superadmin::lang.commission_recurring_applicant_msg', ['percentage' => $commission_percentage])
                                : __('superadmin::lang.commission_first_only_applicant_msg', ['percentage' => $commission_percentage]) }}
                        </p>
                    </div>

                @endcomponent

                @component('components.widget', ['title' => __('superadmin::lang.affiliate_terms_and_conditions')])
                    <div class="col-xs-12">
                        @if (!empty($terms))
                            <div class="well" style="max-height: 400px; overflow-y: auto;">
                                {!! $terms !!}
                            </div>
                        @else
                            <div class="alert alert-info">
                                @lang('superadmin::lang.affiliate_terms_not_configured')
                            </div>
                        @endif

                        <div class="form-group">
                            <label>
                                {!! Form::checkbox('accept_tc', 1, false, ['class' => 'input-icheck', 'required']); !!}
                                @lang('superadmin::lang.i_accept_the_affiliate_tc')
                            </label>
                            @if ($errors->has('accept_tc'))
                                <br><span class="text-danger">{{ $errors->first('accept_tc') }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="col-xs-12">
                        <div class="form-group">
                            {{ Form::submit(__('superadmin::lang.submit_affiliate_application'), ['class' => "tw-dw-btn tw-bg-gradient-to-r tw-from-indigo-600 tw-to-blue-500 tw-font-bold tw-text-white tw-border-none tw-rounded-full"]) }}
                        </div>
                    </div>
                @endcomponent
                {!! Form::close() !!}
            @endif
        </div>
    </div>
</section>
@stop

@section('javascript')
<script type="text/javascript">
    $(document).ready(function() {
        $('form#affiliate_apply_form').validate({
            /*
             * iCheck hides the real checkbox and shows its own markup, so the
             * default ":hidden" ignore rule would skip accept_tc entirely and
             * let the form submit without it being ticked.
             */
            ignore: [],
            rules: {
                accept_tc: {
                    required: true
                }
            },
            messages: {
                accept_tc: {
                    required: "{{ __('superadmin::lang.please_accept_affiliate_tc') }}"
                }
            },
            errorPlacement: function(error, element) {
                //The checkbox itself is hidden, so hang its error off the label.
                if (element.attr('name') === 'accept_tc') {
                    error.insertAfter(element.closest('.form-group').find('label').last());
                } else {
                    error.insertAfter(element);
                }
            },
            /*
             * Only reached once the form is valid, so a failed validation never
             * leaves the button stuck. Disabling it here is what stops a double
             * submit creating a second application.
             */
            submitHandler: function(form) {
                $(form).find('input[type="submit"], button[type="submit"]').prop('disabled', true);

                form.submit();
            }
        });
    });
</script>
@endsection
