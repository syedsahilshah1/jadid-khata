@extends('layouts.app')
@section('title', $contact->name . ' - ' . __('gym::lang.id_card'))
@section('content')
    @include('gym::layouts.nav')
    @php
        $settings = json_decode($business->gym_settings);
    @endphp
    <section class="content">
        <div class="center-container">
            <div class="id-card" id="id-card">
                <!-- Gym Logo -->
                <div class="gym-logo">
                    <img src="{{ asset('img/logo-small.png') }}" alt="Gym Logo">
                </div>

                <!-- Gym Details -->
                <div class="gym-details">
                    <h2> {{ $business->name }}</h2>
                    <p>{!! nl2br(e($settings->gym->address ?? '')) !!}</p>
                    @if(!empty($settings->gym->phone) || !empty($settings->gym->email))
                        <p>
                            @if(!empty($settings->gym->phone))
                                @lang('contact.mobile') : {{ $settings->gym->phone }}
                            @endif
                            @if(!empty($settings->gym->phone) && !empty($settings->gym->email))
                                |
                            @endif
                            @if(!empty($settings->gym->email))
                                @lang('business.email'): {{ $settings->gym->email }}
                            @endif
                        </p>
                    @endif
                </div>

                <!-- Separator -->
                <hr class="divider">

                <!-- Member Photo -->
                <div class="member-photo">
                    <img  @if($contact->gym_member_profile_photo) src="{{ asset('uploads/gym/' . $contact->gym_member_profile_photo) }}" @else src="{{ asset('img/gym_profile.png') }}" @endif alt="Member Photo">
                </div>

                <!-- Member Details -->
                <div class="member-details">
                    <h3 class="member-name">{{ $contact->name }}</h3>
                    <p><strong>@lang('gym::lang.member_id'):</strong> {{ $contact->contact_id }}</p>
                </div>
                <div class="member-details">
                    <img class="center-block" src="data:image/png;base64,{{DNS2D::getBarcodePNG(Crypt::encryptString($contact->id), 'QRCODE', 4, 4)}}" width="100" height="100">
                </div>
                <!-- Footer -->
                <div class="footer">@lang('gym::lang.stay_fit_stay_healthy')</div>

                <!-- Print Button -->
            </div>
        </div>

        <div class="row mt-3" style="margin-top: 20px;">
            <div class="col-md-12 text-center">
                <button onclick="printCard()" class="btn btn-primary mt-3 no-print">@lang('gym::lang.print_id_card')</button>
            </div>
        </div>

    </section>
@endsection

<script>
    function printCard() {
        window.print();
    }
</script>

<style>
    body {
        background-color: #f5f5f5;
        font-family: 'Arial', sans-serif;
    }

    .center-container {
        display: flex;
        justify-content: center;
        align-items: center;
        /* min-height: 100vh; */
    }

    .id-card {
        width: 350px;
        border: 2px solid #00aaff;
        border-radius: 12px;
        background-color: #ffffff;
        padding: 20px;
        box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.15);
        text-align: center;
    }

    .gym-logo {
        display: flex;
        justify-content: center;
        align-items: center;
        margin-bottom: 10px;
    }

    .gym-logo img {
        width: 80px;
        height: 80px;
    }

    .gym-details h2 {
        margin: 0;
        font-size: 1.4rem;
        color: #00aaff;
        font-weight: bold;
    }

    .gym-details p {
        margin: 5px 0;
        font-size: 0.9rem;
        color: #555;
    }

    .gym-details a {
        color: #00aaff;
        text-decoration: none;
    }

    .gym-details a:hover {
        text-decoration: underline;
    }

    .divider {
        border: none;
        border-top: 1px solid #ddd;
        margin: 15px 0;
    }

    .member-photo {
        display: flex;
        justify-content: center;
        align-items: center;
        margin: 15px 0;
    }

    .member-photo img {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #00aaff;
    }

    .member-details h3 {
        margin: 10px 0;
        font-size: 1.2rem;
        color: #333;
        font-weight: bold;
    }

    .member-details p {
        margin: 5px 0;
        font-size: 0.9rem;
        color: #555;
    }

    .health-info {
        margin-top: 15px;
        text-align: left;
    }

    .health-info h4 {
        font-size: 1rem;
        color: #00aaff;
        font-weight: bold;
        margin-bottom: 10px;
    }

    .health-info p {
        margin: 5px 0;
        font-size: 0.9rem;
        color: #555;
    }

    .footer {
        font-size: 0.8rem;
        color: #777;
        margin-top: 20px;
        border-top: 1px solid #ddd;
        padding-top: 10px;
        font-style: italic;
    }

    .btn {
        font-size: 0.9rem;
        padding: 8px 20px;
        background-color: #00aaff;
        color: #fff;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }

    .btn:hover {
        background-color: #0088cc;
    }

    
</style>
