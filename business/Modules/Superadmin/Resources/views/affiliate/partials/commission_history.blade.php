{{--
    Commission history table.
    Shared by the superadmin affiliate detail page and the affiliate dashboard.

    Expects: $commissions
    Optional: $show_actions - superadmin only, defaults to off so the affiliate
              dashboard can never render admin controls.
--}}
@php($show_actions = $show_actions ?? false)
@component('components.widget', ['title' => __('superadmin::lang.commission_history')])
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>@lang('superadmin::lang.subscription_date')</th>
                    <th>@lang('superadmin::lang.business')</th>
                    <th>@lang('superadmin::lang.package')</th>
                    <th>Type</th> {{-- AFF-RATES --}}
                    <th>@lang('superadmin::lang.sale_amount')</th>
                    <th>@lang('superadmin::lang.commission_percentage')</th>
                    <th>@lang('superadmin::lang.commission_amount')</th>
                    <th>@lang('superadmin::lang.status')</th>
                    @if ($show_actions)
                        <th>@lang('superadmin::lang.action')</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse ($commissions as $commission)
                    <tr>
                        <td>{{ @format_datetime($commission->created_at) }}</td>
                        <td>{{ optional($commission->business)->name }}</td>
                        <td>{{ optional($commission->package)->name }}</td>
                        <td>@if ($commission->commission_type === 'team') Team (level {{ $commission->level }}@if ($commission->via_user_id), via {{ optional(\App\User::find($commission->via_user_id))->user_full_name }}@endif) @elseif ($commission->commission_type === 'renewal') Renewal @elseif ($commission->commission_type === 'first') First @else - @endif</td> {{-- AFF-TEAM --}}
                        <td><span class="display_currency" data-currency_symbol="true" data-use_page_currency="true">{{ $commission->sale_amount }}</span></td>
                        <td>{{ ($commission->commission_type === 'team' && (float) $commission->commission_percentage <= 0) ? 'Fixed' : $commission->commission_percentage . '%' }}</td>
                        <td><span class="display_currency" data-currency_symbol="true" data-use_page_currency="true">{{ $commission->commission_amount }}</span></td>
                        <td>{{ \Modules\Superadmin\Entities\AffiliateCommission::statuses()[$commission->status] ?? $commission->status }}</td>
                        @if ($show_actions)
                            <td>
                                {{--
                                    Every commission stays editable: payouts settle a
                                    balance, not these rows, so none of them is ever
                                    claimed by one. AffiliateUtil::updateCommissionStatus()
                                    refuses the one change that would break the books.
                                --}}
                                @foreach (\Modules\Superadmin\Utils\AffiliateUtil::assignableCommissionStatuses() as $status)
                                    @continue($status == $commission->status)
                                    {!! Form::open([
                                        'action' => ['\Modules\Superadmin\Http\Controllers\SuperadminAffiliatesController@updateCommissionStatus', $commission->id],
                                        'method' => 'post',
                                        'class' => 'disable-on-submit',
                                        'style' => 'display:inline-block',
                                    ]) !!}
                                        {!! Form::hidden('status', $status) !!}
                                        {{-- A verb, not the status name: the button says what it does. --}}
                                        <button type="submit" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline @if($status == 'approved') tw-dw-btn-success @elseif($status == 'cancelled') tw-dw-btn-error @else tw-dw-btn-info @endif">
                                            {{ \Modules\Superadmin\Utils\AffiliateUtil::statusActionLabel($status) }}
                                        </button>
                                    {!! Form::close() !!}
                                @endforeach
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $show_actions ? 9 : 8 }}" class="text-center">@lang('superadmin::lang.no_commissions_yet')</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endcomponent
