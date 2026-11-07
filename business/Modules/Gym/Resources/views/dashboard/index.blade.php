@extends('layouts.app')
@section('title', __('gym::lang.gym'))
@section('content')
    @include('gym::layouts.nav')
    <section class="content no-print">
        <div class="row">
            <div class="col-md-4">
                @component('components.widget')
                    <table class="table no-margin">
                        <tr>
                            <th>@lang('gym::lang.total_member')</th>
                            <td>{{ $total_members ?? 0 }}</td>
                        </tr>
                        <tr>
                            <th>@lang('gym::lang.active_member')</th>
                            <td>{{ $active_members ?? 0 }}</td>
                        </tr>
                        <tr>
                            <th>@lang('gym::lang.new_member_this_month')</th>
                            <td>{{ $registered_this_month ?? 0 }}</td>
                        </tr>
                    </table>
                @endcomponent
            </div>
            <div class="col-md-4">
                @component('components.widget')
                    <div class="nav-tabs-custom">
                        <h3 class="box-title">@lang('gym::lang.today_attendance_summary')</h3>
                        <ul class="nav nav-tabs">
                            <li class="active">
                                <a href="#cn_1" data-toggle="tab" aria-expanded="true">
                                    @lang('gym::lang.inside_gym')
                                </a>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane active" id="cn_1">
                                @forelse ($today_attendances as $attendance)
                                    <div class="attendance-item">
                                        <strong>@lang('contact.name'):</strong> {{ $attendance->contact_name }} <br>
                                        <strong>@lang('contact.mobile'):</strong> {{ $attendance->contact_mobile }} <br>
                                        <strong>@lang('gym::lang.in_time'):</strong>
                                        {{ @format_time($attendance->in_time) }}
                                        @php
                                            $inTime = \Carbon\Carbon::parse($attendance->date . ' ' . $attendance->in_time);
                                            $diff = $inTime->diff(\Carbon\Carbon::now());
                                            $value = $diff->h > 0 ? $diff->h : $diff->i;
                                            $unit = $diff->h > 0 ? __('gym::lang.hours_ago') : __('gym::lang.minutes_ago');
                                        @endphp
                                        <span class="label bg-info"> {{ $value }}  {{ $unit }}</span>
                                        <hr>
                                    </div>
                                @empty
                                    @lang('gym::lang.no_one_in_side')
                                @endforelse
                            </div>

                        </div>
                    </div>
                @endcomponent
            </div>
            <div class="col-md-4">
                @component('components.widget')
                    <div class="nav-tabs-custom">
                        <h3 class="box-title">@lang('gym::lang.subscription_summary')</h3>
                        <ul class="nav nav-tabs">
                            <li class="active">
                                <a href="#cn_1" data-toggle="tab" aria-expanded="true">
                                    @lang('gym::lang.expiring_soon')
                                </a>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane active" id="cn_1">
                                @forelse ($expiring_soon as $value)
                                    <div class="attendance-item">
                                        <strong>@lang('contact.name'):</strong> {{ $value->contact_name }} <br>
                                        <strong>@lang('contact.mobile'):</strong> {{ $value->contact_mobile }} <br>
                                        <strong>@lang('gym::lang.end_date'):</strong>
                                        {{ @format_date($value->gym_package_end_date) }}
                                        @php
                                            $daysLeft = \Carbon\Carbon::parse($value->gym_package_end_date)->diffInDays(\Carbon\Carbon::now());
                                        @endphp
                                        <span class="label bg-info"> @lang('gym::lang.in') {{ $daysLeft }} @lang('gym::lang.days')</span>
                                        <br>
                                        <strong>@lang('gym::lang.package'):</strong> {{ $value->package_name }} <br>
                                        <hr>
                                    </div>
                                @empty
                                    @lang('gym::lang.no_records_found')
                                @endforelse
                            </div>
                        </div>
                    </div>
                @endcomponent
            </div>
        </div>
    @endsection
