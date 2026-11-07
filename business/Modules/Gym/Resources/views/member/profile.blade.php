@extends('layouts.app')
@section('title', __('gym::lang.profile'))
@section('content')
@include('gym::layouts.nav')
    <section class="content-header">
        <h1>@lang('gym::lang.profile')</h1>
    </section>
    <section class="content">
        @component('components.widget')
            <div class="row">
                <div class="col-md-3">
                    <img @if($contact->gym_member_profile_photo) src="{{ asset('uploads/gym/' . $contact->gym_member_profile_photo) }}" @else src="{{ asset('img/gym_profile.png') }}" @endif  alt="User profile picture"
                        style="height:275px;">
                </div>
                <div class="col-md-4">
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
                <div class="col-md-5">
                    <ul class="list-group list-group-unbordered">
                        <li class="list-group-item">
                            <b>@lang('lang_v1.dob')</b> <span class="pull-right">{{ $contact->dob ? @format_date($contact->dob) : '' }}</span>
                        </li>
                        <li class="list-group-item">
                            <b>@lang('gym::lang.age')</b> <span class="pull-right">{{ $contact->dob ? \Carbon\Carbon::parse($contact->dob)->age . ' ' . __('gym::lang.years') : '' }}</span>
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
            </div>
            <div class="row">
                <div class="col-md-12 text-center">
                    <a href="{{ action([\Modules\Gym\Http\Controllers\MemberController::class, 'id_card'], ['id'=> $contact->id]) }}"
                        class="tw-dw-btn tw-dw-btn-primary tw-text-white submit_form"> <i class="fas fa-id-card"> </i> @lang('gym::lang.id_card')</a>
                </div>
            </div>
        @endcomponent
    </section>
@endsection
