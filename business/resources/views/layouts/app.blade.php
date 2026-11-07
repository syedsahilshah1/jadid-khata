@inject('request', 'Illuminate\Http\Request')

@if (
    $request->segment(1) == 'pos' &&
        ($request->segment(2) == 'create' || $request->segment(3) == 'edit' || $request->segment(2) == 'payment'))
    @php
        $pos_layout = true;
    @endphp
@else
    @php
        $pos_layout = false;
    @endphp
@endif

@php
    $whitelist = ['127.0.0.1', '::1'];
@endphp

<!DOCTYPE html>
<html class="tw-h-full tw-bg-white tw-scroll-smooth" lang="{{ app()->getLocale() }}"
    dir="{{ in_array(session()->get('user.language', config('app.locale')), config('constants.langs_rtl')) ? 'rtl' : 'ltr' }}">
<head>
    <!-- Tell the browser to be responsive to screen width -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no"
        name="viewport">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - {{ Session::get('business.name') }}</title>
    @include('layouts.partials.pwa-meta')

       @include('layouts.partials.css')
    

    @include('layouts.partials.extracss')

    @yield('css')

</head>
<body
    class="tw-h-full tw-overflow-hidden tw-font-sans tw-antialiased tw-text-gray-900 tw-bg-gray-100 @if ($pos_layout) hold-transition lockscreen @else hold-transition skin-@if (!empty(session('business.theme_color'))){{ session('business.theme_color') }}@else{{ 'blue-light' }} @endif sidebar-mini @endif">
    <div class="tw-flex tw-h-full">
        <script type="text/javascript">
            if (localStorage.getItem("upos_sidebar_collapse") == 'true') {
                var body = document.getElementsByTagName("body")[0];
                body.className += " sidebar-collapse";
            }
        </script>
        @if (!$pos_layout)
            @include('layouts.partials.sidebar')
        @endif

        @if (in_array($_SERVER['REMOTE_ADDR'], $whitelist))
            <input type="hidden" id="__is_localhost" value="true">
        @endif

        <!-- Add currency related field-->
        <input type="hidden" id="__code" value="{{ session('currency')['code'] }}">
        <input type="hidden" id="__symbol" value="{{ session('currency')['symbol'] }}">
        <input type="hidden" id="__thousand" value="{{ session('currency')['thousand_separator'] }}">
        <input type="hidden" id="__decimal" value="{{ session('currency')['decimal_separator'] }}">
        <input type="hidden" id="__symbol_placement" value="{{ session('business.currency_symbol_placement') }}">
        <input type="hidden" id="__precision" value="{{ session('business.currency_precision', 2) }}">
        <input type="hidden" id="__quantity_precision" value="{{ session('business.quantity_precision', 2) }}">
        <!-- End of currency related field-->
        @can('view_export_buttons')
            <input type="hidden" id="view_export_buttons">
        @endcan
        @if (isMobile())
            <input type="hidden" id="__is_mobile">
        @endif
        @if (session('status'))
            <input type="hidden" id="status_span" data-status="{{ session('status.success') }}"
                data-msg="{{ session('status.msg') }}">
        @endif
        <main class="tw-flex tw-flex-col tw-flex-1 tw-h-full tw-min-w-0 tw-bg-gray-100">

            @if (!$pos_layout)
                @include('layouts.partials.header')
            @else
                @include('layouts.partials.header-pos')
            @endif
            <!-- empty div for vuejs -->
            <div id="app">
                @yield('vue')
            </div>
            <div class="tw-flex-1 tw-overflow-y-auto tw-h-screen" id="scrollable-container">
                @yield('content')
                @if (!$pos_layout)
                
                    @include('layouts.partials.footer')
                @else
                    @include('layouts.partials.footer_pos')
                @endif
            </div>
            <div class='scrolltop no-print'>
                <div class='scroll icon'><i class="fas fa-angle-up"></i></div>
            </div>

            @if (config('constants.iraqi_selling_price_adjustment'))
                <input type="hidden" id="iraqi_selling_price_adjustment">
            @endif

            <!-- This will be printed -->
            <section class="invoice print_section" id="receipt_section">
            </section>
        </main>

        @include('home.todays_profit_modal')
        <!-- /.content-wrapper -->



        <audio id="success-audio">
            <source src="{{ asset('/audio/success.ogg?v=' . $asset_v) }}" type="audio/ogg">
            <source src="{{ asset('/audio/success.mp3?v=' . $asset_v) }}" type="audio/mpeg">
        </audio>
        <audio id="error-audio">
            <source src="{{ asset('/audio/error.ogg?v=' . $asset_v) }}" type="audio/ogg">
            <source src="{{ asset('/audio/error.mp3?v=' . $asset_v) }}" type="audio/mpeg">
        </audio>
        <audio id="warning-audio">
            <source src="{{ asset('/audio/warning.ogg?v=' . $asset_v) }}" type="audio/ogg">
            <source src="{{ asset('/audio/warning.mp3?v=' . $asset_v) }}" type="audio/mpeg">
        </audio>

        @if (!empty($__additional_html))
            {!! $__additional_html !!}
        @endif

        @include('layouts.partials.javascripts')

        @include('layouts.partials.pwa-install-banner')
        @include('layouts.partials.pwa-scripts')

        <div class="modal fade view_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>

        @if (!empty($__additional_views) && is_array($__additional_views))
            @foreach ($__additional_views as $additional_view)
                @includeIf($additional_view)
            @endforeach
        @endif
        <div>

            <div class="overlay tw-hidden"></div>
            <!-- Jadid Khata Voice Widget -->
</body>
<style>
    .small-view-side-active {
        display: grid !important;
        z-index: 1000;
        position: absolute;
    }
    .overlay {
        width: 100vw;
        height: 100vh;
        background: rgba(0, 0, 0, 0.8);
        position: fixed;
        top: 0;
        left: 0;
        display: none;
        z-index: 20;
    }

    .tw-dw-btn.tw-dw-btn-xs.tw-dw-btn-outline {
        width: max-content;
        margin: 2px;
    }
</style>
<style>
    div#side-bar{
        background: #071330 !important;
    }

    a.tw-flex.tw-items-center.tw-gap-3.tw-px-3.tw-py-2.tw-text-sm.tw-font-medium.tw-tracking-tight.tw-text-gray-600.tw-transition-all.tw-duration-200.tw-rounded-lg.tw-whitespace-nowrap.hover\:tw-text-gray-900.hover\:tw-bg-gray-100, a.tw-flex.tw-text-sm.tw-font-medium.tw-tracking-tight.tw-text-gray-600.tw-truncate.tw-transition-all.tw-duration-200.hover\:tw-text-gray-900.tw-whitespace-nowrap {
  color: #fff !important;
}

a.tw-flex.tw-items-center.tw-gap-3.tw-px-3.tw-py-2.tw-text-sm.tw-font-medium.tw-tracking-tight.tw-text-gray-600.tw-transition-all.tw-duration-200.tw-rounded-lg.tw-whitespace-nowrap.hover\:tw-text-gray-900.hover\:tw-bg-gray-100, a.tw-flex.tw-text-sm.tw-font-medium.tw-tracking-tight.tw-text-gray-600.tw-truncate.tw-transition-all.tw-duration-200.hover\:tw-text-gray-900.tw-whitespace-nowrap {
  color: #fff !important;
}

.tw-bg-gray-200 {
  --tw-bg-opacity: 1;
  background-color: rgb(227 232 239/var(--tw-bg-opacity));
}

.chiled.tw-relative.tw-mt-2.tw-mb-4.tw-pl-11 {
  background: #EFBF04;
}

/* Fix: regular (non-dropdown) sidebar items had white-on-white hover state */
#side-bar a.tw-flex:hover,
#side-bar li a.tw-flex:hover,
#side-bar a.tw-flex.tw-bg-gray-200 {
  background-color: #FFA500 !important;
  color: #fff !important;
}

a.drop_down.tw-flex.tw-items-center.tw-gap-3.tw-px-3.tw-py-2.tw-text-sm.tw-font-medium.tw-tracking-tight.tw-text-gray-600.tw-transition-all.tw-duration-200.tw-rounded-lg.tw-whitespace-nowrap.hover\:tw-text-gray-900.hover\:tw-bg-gray-100.focus\:tw-text-gray-900.focus\:tw-bg-gray-100:hover {
  background: #FFA500 !important;
}

a.drop_down.tw-flex.tw-items-center.tw-gap-3.tw-px-3.tw-py-2.tw-text-sm.tw-font-medium.tw-tracking-tight.tw-text-gray-600.tw-transition-all.tw-duration-200.tw-rounded-lg.tw-whitespace-nowrap.hover\:tw-text-gray-900.hover\:tw-bg-gray-100.focus\:tw-text-gray-900.focus\:tw-bg-gray-100:hover {
  background: #FFA500 !important;
}

.bg-orange, a.tw-flex.tw-items-center.tw-gap-3.tw-px-3.tw-py-2.tw-text-sm.tw-font-medium.tw-tracking-tight.tw-text-gray-600.tw-transition-all.tw-duration-200.tw-rounded-lg.tw-whitespace-nowrap.hover\:tw-text-gray-900.hover\:tw-bg-gray-100.tw-bg-gray-200.tw-text-primary-700 {
  background-color: #FFA500 !important;
}

.bg-total_sale {
  background-color: #a8a8e0 !important;
}

.bg-net {
  background-color: #FFA500 !important;
}

.bg-invoice_due {
  background-color: #75ddcd !important;
}

.bg-sale_return {
  background-color: #EE4B2B !important;
}

.bg-total_purchase {
  background-color: #FFC300 !important;
}

.bg-expense {
  background-color: #f2dede !important;
}

.bg-purchase_due {
  background-color: #FFC300 !important;
}

.bg-purchase_return {
  background-color: #FF5733 !important;
}
</style>
</html>
