@extends('gym::layouts.scanner')
@section('title', __('gym::lang.member_details'))
@section('content')
    <section class="content-header">
        <h1>@lang('gym::lang.member_details')</h1>
    </section>
    <section class="content">
        @component('components.widget')
            <div class="row">
                <div class="col-md-3">
                    <img @if ($contact->gym_member_profile_photo) src="{{ asset('uploads/gym/' . $contact->gym_member_profile_photo) }}" @else src="{{ asset('img/gym_profile.png') }}" @endif
                        alt="User profile picture" style="height:275px;">
                </div>
                <div class="col-md-3">
                    <h3 class="profile-username">{{ $contact->name }}</h3>
                    <p class="text-muted"> @lang('gym::lang.member_id') : {{ $contact->contact_id }}</p>
                    <ul class="list-group list-group-unbordered mt-5">
                        <li class="list-group-item">
                            <b>@lang('business.email')</b> <span class="pull-right">{{ $contact->email }}</span>
                        </li>
                        <li class="list-group-item">
                            <b>@lang('contact.mobile')</b> <span class="pull-right">{{ $contact->mobile }}</span>
                        </li>
                        <li class="list-group-item">
                            <b>@lang('contact.alternate_contact_number')</b> <span class="pull-right">{{ $contact->alternate_number }}</span>
                        </li>
                        <li class="list-group-item">
                            <b>@lang('contact.landline')</b> <span class="pull-right">{{ $contact->landline }}</span>
                        </li>
                        <li class="list-group-item">
                            <b>@lang('lang_v1.address_line_1')</b> <span class="pull-right">{{ $contact->address_line_1 }}</span>
                        </li>
                        <li class="list-group-item">
                            <b>@lang('lang_v1.address_line_2')</b> <span class="pull-right">{{ $contact->address_line_2 }}</span>
                        </li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <ul class="list-group list-group-unbordered">
                        <li class="list-group-item">
                            <b>@lang('lang_v1.dob')</b> <span
                                class="pull-right">{{ $contact->dob ? $commonUtil->format_date($contact->dob, false, $business) : '' }}</span>
                        </li>
                        <li class="list-group-item">
                            <b>@lang('gym::lang.age')</b> <span
                                class="pull-right">{{ $contact->dob ? \Carbon\Carbon::parse($contact->dob)->age . ' ' . __('gym::lang.years') : '' }}</span>
                        </li>
                        <li class="list-group-item">
                            <b>@lang('business.city')</b> <span class="pull-right">{{ $contact->city }}</span>
                        </li>
                        <li class="list-group-item">
                            <b>@lang('business.state')</b> <span class="pull-right">{{ $contact->state }}</span>
                        </li>
                        <li class="list-group-item">
                            <b>@lang('business.country')</b> <span class="pull-right">{{ $contact->country }}</span>
                        </li>
                        <li class="list-group-item">
                            <b>@lang('business.zip_code')</b> <span class="pull-right">{{ $contact->zip_code }}</span>
                        </li>
                    </ul>
                </div>

                <div class="col-md-3">
                    <h3 class="profile-username">
                        @lang('gym::lang.package_details')
                    </h3>
                    <ul class="list-group list-group-unbordered mt-4">
                        <li class="list-group-item">
                            <b>@lang('gym::lang.package')</b> <span class="pull-right">{{ $package->package_name ?? '' }}</span>
                        </li>
                        <li class="list-group-item">
                            <b>@lang('gym::lang.start_date')</b> <span class="pull-right">
                                @if (isset($package->gym_package_start_date))
                                    {{ $commonUtil->format_date($package->gym_package_start_date, false, $business) }}
                                    @php
                                        $startDate = \Carbon\Carbon::parse($package->gym_package_start_date);
                                        $now = \Carbon\Carbon::now();

                                    @endphp
                                    @if ($startDate->isAfter($now))
                                        @php
                                            $diffInDays = $startDate->diffInDays($now);
                                        @endphp
                                        <span class="label bg-info"> @lang('gym::lang.start_in') {{ $diffInDays }}
                                            @lang('gym::lang.days')</span>
                                    @endif
                                @endif
                            </span>
                        </li>
                        <li class="list-group-item">
                            <b>@lang('gym::lang.end_date')</b> <span class="pull-right">
                                @if (isset($package->gym_package_end_date))
                                    {{ $commonUtil->format_date($package->gym_package_end_date, false, $business) }}
                                    @php
                                        $daysLeft = \Carbon\Carbon::parse($package->gym_package_end_date)->diffInDays(
                                            \Carbon\Carbon::now(),
                                        );
                                    @endphp

                                    @php
                                        $startDate = \Carbon\Carbon::parse($package->gym_package_start_date);
                                        $now = \Carbon\Carbon::now();

                                    @endphp
                                    @if ($startDate->isAfter($now))
                                        @php
                                            $diffInDays = $startDate->diffInDays($now);
                                        @endphp
                                        <span class="label bg-info"> @lang('gym::lang.start_in') {{ $diffInDays }}
                                            @lang('gym::lang.days')</span>
                                    @elseif ($package->gym_package_end_date < $now)
                                        <span class="label tw-bg-red-400"> @lang('gym::lang.expired')</span>
                                    @else
                                        <span class="label bg-info"> @lang('gym::lang.in') {{ $daysLeft }}
                                            @lang('gym::lang.days')</span>
                                    @endif
                                @endif
                            </span>
                        </li>
                    </ul>
                    <h3 class="profile-username">
                        @lang('gym::lang.upcoming_class')
                    </h3>
                    <ul class="list-group list-group-unbordered mt-4">
                        @foreach ($classes as $key => $class)
                            <li class="list-group-item">
                                <b>{{ $class->name }}</b> <span class="pull-right">{{ @format_time($class->start_time) }} -
                                    {{ @format_time($class->end_time) }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endcomponent

        @component('components.widget')
            <h3 class="profile-username text-center">
                @lang('gym::lang.diet_plan')
            </h3>
            <div class="row">
                <div class="col-md-6">
                    <ul class="list-group list-group-unbordered mt-5">
                        <li class="list-group-item">
                            <b>@lang('gym::lang.morning')</b> <span class="pull-right">{{ $diets->morning ?? '' }}</span>
                        </li>
                        <li class="list-group-item">
                            <b>@lang('gym::lang.breakfast')</b> <span class="pull-right">{{ $diets->breakfast ?? '' }}</span>
                        </li>
                        <li class="list-group-item">
                            <b>@lang('gym::lang.before_lunch')</b> <span class="pull-right">{{ $diets->before_lunch ?? '' }}</span>
                        </li>
                        <li class="list-group-item">
                            <b>@lang('gym::lang.lunch')</b> <span class="pull-right">{{ $diets->lunch ?? '' }}</span>
                        </li>
                        <li class="list-group-item">
                            <b>@lang('gym::lang.afternoon')</b> <span class="pull-right">{{ $diets->afternoon ?? '' }}</span>
                        </li>
                        <li class="list-group-item">
                            <b>@lang('gym::lang.evening')</b> <span class="pull-right">{{ $diets->evening ?? '' }}</span>
                        </li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <ul class="list-group list-group-unbordered mt-5">
                        <li class="list-group-item">
                            <b>@lang('gym::lang.dinner')</b> <span class="pull-right">{{ $diets->dinner ?? '' }}</span>
                        </li>
                        <li class="list-group-item">
                            <b>@lang('gym::lang.before_sleep')</b> <span class="pull-right">{{ $diets->before_sleep ?? '' }}</span>
                        </li>
                        <li class="list-group-item">
                            <b>@lang('gym::lang.before_workout')</b> <span class="pull-right">{{ $diets->before_workout ?? '' }}</span>
                        </li>
                        <li class="list-group-item">
                            <b>@lang('gym::lang.after_workout')</b> <span class="pull-right">{{ $diets->after_workout ?? '' }}</span>
                        </li>
                        <li class="list-group-item">
                            <b>@lang('gym::lang.remarks')</b> <span class="pull-right">{{ $diets->remarks ?? '' }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        @endcomponent

        @component('components.widget')
            <h3 class="profile-username text-center">
                @lang('gym::lang.health_record')
            </h3>
            <div class="table-responsive">

                <table class="table table-bordered table-striped" id="health_table">
                    <thead>
                        <tr>
                            <th>@lang('gym::lang.date')</th>
                            <th>@lang('gym::lang.weight')</th>
                            <th>@lang('gym::lang.height')</th>
                            <th>@lang('gym::lang.bmi')</th>
                            <th>@lang('gym::lang.body_fat_percentage')</th>
                            <th>@lang('gym::lang.muscle_mass_percentage')</th>
                            <th>@lang('gym::lang.neck')</th>
                            <th>@lang('gym::lang.left_arm')</th>
                            <th>@lang('gym::lang.right_arm')</th>
                            <th>@lang('gym::lang.chest')</th>
                            <th>@lang('gym::lang.upper_waist')</th>
                            <th>@lang('gym::lang.lower_waist')</th>
                            <th>@lang('gym::lang.hips')</th>
                            <th>@lang('gym::lang.left_thigh')</th>
                            <th>@lang('gym::lang.right_thigh')</th>
                            <th>@lang('gym::lang.calf')</th>
                            <th>@lang('gym::lang.shoulders')</th>
                            <th>@lang('gym::lang.visceral_fat')</th>
                            <th>@lang('gym::lang.subcutaneous_fat')</th>
                            <th>@lang('gym::lang.remarks')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($health as $record)
                            <tr>
                                <td>{{ $commonUtil->format_date($record->date, false, $business) }}</td>
                                <td>{{ $record->weight }}</td>
                                <td>{{ $record->height }}</td>
                                <td>{{ $record->bmi }}</td>
                                <td>{{ $record->body_fat_percentage }}</td>
                                <td>{{ $record->muscle_mass_percentage }}</td>
                                <td>{{ $record->neck }}</td>
                                <td>{{ $record->left_arm }}</td>
                                <td>{{ $record->right_arm }}</td>
                                <td>{{ $record->chest }}</td>
                                <td>{{ $record->upper_waist }}</td>
                                <td>{{ $record->lower_waist }}</td>
                                <td>{{ $record->hips }}</td>
                                <td>{{ $record->left_thigh }}</td>
                                <td>{{ $record->right_thigh }}</td>
                                <td>{{ $record->calf }}</td>
                                <td>{{ $record->shoulders }}</td>
                                <td>{{ $record->visceral_fat }}</td>
                                <td>{{ $record->subcutaneous_fat }}</td>
                                <td>{{ $record->remarks }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

            </div>
        @endcomponent

        @component('components.widget')
            <h3 class="profile-username text-center">
                @lang('gym::lang.attendance')
            </h3>
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="attendances_table">
                    <thead>
                        <tr>
                            <th>@lang('gym::lang.date')</th>
                            <th>@lang('gym::lang.in_time')</th>
                            <th>@lang('gym::lang.out_time')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($attendances as $attendance)
                            <tr>
                                <td>{{ isset($attendance->date) ? $commonUtil->format_date($attendance->date, false, $business) : '' }}
                                </td>
                                <td>{{ isset($attendance->in_time) ? @format_time($attendance->in_time) : '' }}</td>
                                <td>{{ isset($attendance->out_time) ? @format_time($attendance->out_time) : '' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endcomponent
    </section>

@endsection
@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {

            $("form#create_health_tracking").validate();

            $('#health_table').DataTable({

            });

            $('#attendances_table').DataTable({

            });
        });
    </script>
@endsection
