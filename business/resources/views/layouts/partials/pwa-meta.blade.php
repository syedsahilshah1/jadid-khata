@once
@php
    $pwaBusinessId = session('business.id') ?? (optional(auth()->user())->business_id ?? null);
    $manifestParams = [];
    if ($pwaBusinessId) {
        $manifestParams['business_id'] = $pwaBusinessId;
    }
    // Add timestamp to manifest URL to ensure fresh fetch (browser will check for updates)
    $manifestParams['_t'] = time();
    if (! empty($asset_v)) {
        $manifestParams['v'] = $asset_v;
    }
    $manifestUrl = route('pwa.manifest', $manifestParams);

    // Get logo version for apple icon
    $logoVersion = null;
    if ($pwaBusinessId) {
        $business = \App\Business::find($pwaBusinessId);
        if ($business && $business->logo && file_exists(public_path('uploads/business_logos/' . $business->logo))) {
            $logoVersion = filemtime(public_path('uploads/business_logos/' . $business->logo));
        }
    }

    $appleIcon = $pwaBusinessId
        ? route('pwa.icon', ['business' => $pwaBusinessId, 'size' => 192]) . ($logoVersion ? '?v=' . $logoVersion : '')
        : asset('img/logo-small.png');
    $themeColor = session('business.theme_color', config('app.pwa_theme_color', '#0d6efd'));
    $appName = session('business.name', config('app.name', 'UltimatePOS'));
@endphp
<link rel="manifest" href="{{ $manifestUrl }}" crossorigin="use-credentials">
<meta name="theme-color" content="{{ $themeColor }}">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="{{ $appName }}">
<meta name="pwa-business-id" content="{{ $pwaBusinessId }}">
<link rel="apple-touch-icon" href="{{ $appleIcon }}">
<script>
    (function() {
        // Check manifest is loaded
        var manifestLink = document.querySelector('link[rel="manifest"]');
        console.log('[PWA] Manifest check:', {
            manifestElement: !!manifestLink,
            manifestHref: manifestLink ? manifestLink.href : 'N/A',
            themeColor: document.querySelector('meta[name="theme-color"]')?.content
        });

        window.__pwaInstallState = window.__pwaInstallState || {};
        if (!window.__pwaInstallState.bound) {
            window.__pwaInstallState.bound = true;
            window.__pwaInstallState.deferredPrompt = null;
            window.__pwaInstallState.promptCaptured = false;

            window.addEventListener('beforeinstallprompt', function (event) {
                console.log('[PWA] beforeinstallprompt event fired');
                event.preventDefault();
                window.__pwaInstallState.deferredPrompt = event;
                window.__pwaInstallState.promptCaptured = true;
                window.dispatchEvent(new CustomEvent('pwa:installprompt', { detail: event }));
            });

            // Debug: Log state after a delay
            setTimeout(function() {
                console.log('[PWA] State check after 3s:', {
                    promptCaptured: window.__pwaInstallState.promptCaptured,
                    hasDeferredPrompt: !!window.__pwaInstallState.deferredPrompt,
                    isStandalone: window.matchMedia('(display-mode: standalone)').matches,
                    alreadyInstalled: window.navigator.standalone || window.matchMedia('(display-mode: standalone)').matches
                });
            }, 3000);
        }
    })();
</script>
@endonce
