<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title') - {{ config('app.name', 'POS') }}</title>

    @include('layouts.partials.css')
    @include('layouts.partials.pwa-meta')

    @include('layouts.partials.extracss_auth')

    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    <script src='https://www.google.com/recaptcha/api.js'></script>

</head>

<body class="pace-done" data-new-gr-c-s-check-loaded="14.1172.0" data-gr-ext-installed="" cz-shortcut-listen="true">
    @inject('request', 'Illuminate\Http\Request')
    @if (session('status') && session('status.success'))
        <input type="hidden" id="status_span" data-status="{{ session('status.success') }}"
            data-msg="{{ session('status.msg') }}">
    @endif
    {{-- Header Bar --}}
    <style>
    /* AUTH2-HEADER-RESPONSIVE */
    .auth2-header-outer { background: linear-gradient(to right, #4f46e5, #3b82f6); padding: 10px 0; box-shadow: 0 4px 6px -1px rgba(0,0,0,.1); }
    .auth2-header-row { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; max-width: 1200px; margin: 0 auto; padding: 0 15px; }
    .auth2-header-row > div { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
    .auth2-header-row a { color: #fff; font-weight: 500; font-size: 13px; text-decoration: none; }
    .auth2-header-row a:hover { color: #fff; }
    .auth2-header-row .pill { border: 2px solid #fff; border-radius: 9999px; height: 34px; padding: 0 14px; display: flex; align-items: center; justify-content: center; }
    @media (max-width: 768px) {
      .auth2-header-row { justify-content: center; gap: 8px; }
      .auth2-header-row > div { justify-content: center; gap: 6px; width: 100%; }
      .auth2-header-row a { font-size: 12px; }
      .auth2-header-row .pill { height: 28px; padding: 0 10px; font-size: 12px; }
    }
    </style>
    <div class="auth2-header-outer">
        <div class="auth2-header-row">

            {{-- LEFT: Logo + links --}}
            <div class="tw-flex tw-items-center tw-gap-4">
                <a href="{{ url('/') }}">
                    <img src="{{ asset('img/logo-small.png') }}" alt="Logo"
                         class="tw-w-11 tw-h-11 tw-rounded-full tw-object-cover tw-bg-white tw-p-0.5 tw-shadow"
                         style="width:44px;height:44px;object-fit:cover;border-radius:50%;background:#fff;padding:2px;">
                </a>

                @if(config('constants.SHOW_REPAIR_STATUS_LOGIN_SCREEN') && Route::has('repair-status'))
                    <a class="tw-text-white tw-font-medium tw-text-sm hover:tw-text-white"
                       href="{{ action([\Modules\Repair\Http\Controllers\CustomerRepairStatusController::class, 'index']) }}">
                        @lang('repair::lang.repair_status')
                    </a>
                @endif

                @if(Route::has('member_scanner'))
                    <a class="tw-text-white tw-font-medium tw-text-sm hover:tw-text-white"
                       href="{{ action([\Modules\Gym\Http\Controllers\MemberController::class, 'member_scanner']) }}">
                        @lang('gym::lang.gym_member_profile')
                    </a>
                @endif

                {{-- FBR Verify --}}
                <div class="pill">
                    <a href="https://iris.fbr.gov.pk/#verifications" target="_blank"
                       title="{{ __('Under the 10TH tab (POS Invoice Verification)') }}"
                       class="tw-text-white tw-font-medium tw-text-sm hover:tw-text-white tw-whitespace-nowrap">
                        {{ __('Verify FBR Invoice') }}
                    </a>
                </div>

                <a class="tw-text-white tw-font-medium tw-text-sm hover:tw-text-white"
                   href="https://support.jadidkhata.com/" target="_blank" rel="noopener">
                    @lang('Support')
                </a>

                {{-- Install App --}}
                @if(Route::has('pwa.install'))
                    <div class="pill">
                        <a href="{{ route('pwa.install') }}"
                           class="tw-text-white tw-font-medium tw-text-sm hover:tw-text-white tw-flex tw-items-center tw-gap-1 tw-whitespace-nowrap">
                            <svg xmlns="http://www.w3.org/2000/svg" class="tw-w-4 tw-h-4 tw-flex-shrink-0" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <path d="M12 3v12"/><path d="M8 11l4 4l4 -4"/><path d="M6 19h12"/>
                            </svg>
                            Install App
                        </a>
                    </div>
                @endif
            </div>

            {{-- RIGHT: Register + Pricing + Language --}}
            <div class="tw-flex tw-items-center tw-gap-4">
                @if (!($request->segment(1) == 'business' && $request->segment(2) == 'register'))
                    @if (config('constants.allow_registration'))
                        <div class="pill">
                            <a href="{{ route('business.getRegister') }}@if(!empty(request()->lang)){{'?lang='.request()->lang}}@endif"
                               class="tw-text-white tw-font-medium tw-text-sm hover:tw-text-white">
                                {{ __('business.register') }}
                            </a>
                        </div>
                        @if (Route::has('pricing') && config('app.env') != 'demo' && $request->segment(1) != 'pricing')
                            <a class="tw-text-white tw-font-medium tw-text-sm hover:tw-text-white"
                               href="{{ action([\Modules\Superadmin\Http\Controllers\PricingController::class, 'index']) }}">
                                @lang('superadmin::lang.pricing')
                            </a>
                        @endif
                    @endif
                @endif
                @if ($request->segment(1) != 'login')
                    <a class="tw-text-white tw-font-medium tw-text-sm hover:tw-text-white"
                       href="{{ action([\App\Http\Controllers\Auth\LoginController::class, 'login']) }}@if(!empty(request()->lang)){{'?lang='.request()->lang}}@endif">
                        {{ __('business.sign_in') }}
                    </a>
                @endif
                @include('layouts.partials.language_btn')
            </div>
        </div>
    </div>

    {{-- Page content --}}
    <div class="container-fluid">
        <div class="row eq-height-row">
            <div class="col-md-12 col-sm-12 col-xs-12 right-col tw-pb-10 tw-px-5">
                @yield('content')
            </div>
        </div>
        <footer class="row footer">
            <h3>For any Assistance 24/7 SUPPORT Please Call/Whatsapp: +44 7853748116</h3>
        </footer>
    </div>


    @include('layouts.partials.javascripts')

    <!-- Scripts -->
    <script src="{{ asset('js/login.js?v=' . $asset_v) }}"></script>

    @yield('javascript')

    @include('layouts.partials.pwa-scripts')

    <script type="text/javascript">
        $(document).ready(function() {
            $('.select2_register').select2();

            // $('input').iCheck({
            //     checkboxClass: 'icheckbox_square-blue',
            //     radioClass: 'iradio_square-blue',
            //     increaseArea: '20%' // optional
            // });
        });
    </script>
    <style>
        .wizard>.content {
            background-color: white !important;
        }
                 body {
            /* background-image: url('https://business.jadidkhata.com/img/bg_img.jpeg') !important; */
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center;
            height: auto;
        } 
        .footer {
            text-align: center;
            font-weight: bold;
            font-size: larger !important;
            background-color: orange;

            bottom: 0;
            height: 60px; /* Adjust the height as needed */
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            box-shadow: 0 -2px 5px rgba(0,0,0,0.1); /* Optional shadow for better visual */
             
        }
        .footer h3 {
            margin: 0;
        }
    </style>
    
    <!-- Jadid Khata Voice Widget -->

        @if (Route::currentRouteName() === 'login' || $request->segment(1) === 'login' || $request->segment(1) === null)
    <style>
      #jk-widget-dismiss {
        position: fixed; z-index: 2147483647;
        width: 22px; height: 22px; border-radius: 50%; border: 2px solid #fff;
        background: #e53935; color: #fff; font-size: 14px; font-weight: bold;
        line-height: 18px; text-align: center; cursor: pointer; padding: 0;
        box-shadow: 0 2px 6px rgba(0,0,0,.35);
        display: none; /* shown once the widget is found */
      }
      #jk-widget-dismiss:hover { background: #b71c1c; }
      body.jk-widget-hidden iframe[src*="aiplus.codes"],
      body.jk-widget-hidden [id^="vw"],
      body.jk-widget-hidden div[class*="widget" i][style*="position"],
      body.jk-widget-hidden #jk-widget-dismiss { display: none !important; }
    </style>
    <script>
      (function(){
        if (localStorage.getItem('jk_widget_dismissed') === '1') {
          document.body.classList.add('jk-widget-hidden');
          return;
        }
        (function(w,d,s,o,f,js){
          w[o]=w[o]||function(){(w[o].q=w[o].q||[]).push(arguments)};
          js=d.createElement(s);js.id=o;js.src=f;js.async=1;
          (d.head||d.body).appendChild(js);
        }(window,document,'script','vw','https://aiplus.codes/widget/embed.js'));
        vw('init', 'wgt_t4sK2CUuClK0kpvay8NPQnFE');

        var btn = document.createElement('button');
        btn.id = 'jk-widget-dismiss';
        btn.type = 'button';
        btn.title = 'Hide widget';
        btn.innerHTML = '&times;';
        btn.onclick = function(){
          document.body.classList.add('jk-widget-hidden');
          localStorage.setItem('jk_widget_dismissed', '1');
        };
        document.body.appendChild(btn);

        // Locate the widget container and stick the X to its top-right corner.
        function findWidget(){
          var candidates = document.querySelectorAll(
            'iframe[src*="aiplus.codes"], [id^="vw"], div[class*="voice" i], div[class*="widget" i]'
          );
          var best = null, bestArea = 0;
          for (var i=0; i<candidates.length; i++){
            var el = candidates[i];
            if (el === btn) continue;
            var r = el.getBoundingClientRect();
            if (r.width < 80 || r.height < 30) continue; // skip tiny elements
            var area = r.width * r.height;
            if (area > bestArea){ best = el; bestArea = area; }
          }
          return best;
        }

        function positionBtn(){
          var w = findWidget();
          if (!w){ btn.style.display = 'none'; return; }
          var r = w.getBoundingClientRect();
          // place X overlapping the top-right corner of the widget
          btn.style.top    = (window.scrollY + r.top  - 10) + 'px';
          btn.style.left   = (window.scrollX + r.right - 12) + 'px';
          btn.style.display = 'block';
        }

        // Poll for ~10s while the widget loads, then keep reacting to resize.
        var tries = 0, iv = setInterval(function(){
          positionBtn();
          if (++tries > 40) clearInterval(iv);
        }, 250);
        window.addEventListener('resize', positionBtn);
        window.addEventListener('scroll',  positionBtn, true);
      })();
    </script>
    @endif
    
</body>

</html>