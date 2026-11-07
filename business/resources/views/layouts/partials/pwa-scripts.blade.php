@once
@php
    $serviceWorkerUrl = route('pwa.service-worker');
    $swVersion = $asset_v ?? config('app.version', config('app.asset_version', '1.0.0'));
@endphp
<script>
(function () {
    const localHosts = ['localhost', '127.0.0.1', '::1'];
    const isLocalhost = localHosts.indexOf(window.location.hostname) !== -1;
    const canUseServiceWorker = !window.location.pathname.includes("/pos/") && 'serviceWorker' in navigator && (window.location.protocol === 'https:' || isLocalhost);

    console.log('[PWA] Service Worker Check:', {
        hasNavigator: 'serviceWorker' in navigator,
        protocol: window.location.protocol,
        isLocalhost: isLocalhost,
        canUse: canUseServiceWorker
    });

    if (!canUseServiceWorker) {
        console.warn('[PWA] Service Worker not supported or not on HTTPS/localhost');
        return;
    }

    function registerSW() {
        var businessMeta = document.querySelector('meta[name="pwa-business-id"]');
        var businessId = businessMeta ? businessMeta.getAttribute('content') : '';
        var query = [];
        if (businessId) {
            query.push('business_id=' + encodeURIComponent(businessId));
        }
        query.push('v={{ $swVersion }}');
        var swUrl = '{{ $serviceWorkerUrl }}' + (query.length ? '?' + query.join('&') : '');

        console.log('[PWA] Registering service worker:', swUrl);

        navigator.serviceWorker.register(swUrl)
            .then(function(registration) {
                console.log('[PWA] Service Worker registered successfully:', registration);
            })
            .catch(function (error) {
                console.error('[PWA] Service worker registration failed:', error);
            });
    }

    if (document.readyState === 'complete') {
        registerSW();
    } else {
        window.addEventListener('load', registerSW);
    }
})();
</script>
<script>
(function () {
    const installState = window.__pwaInstallState || (window.__pwaInstallState = {});
    let deferredPrompt = installState.deferredPrompt || null;
    const headerBtn = document.querySelector('[data-install-pwa]');
    const bannerEl = document.querySelector('[data-pwa-install-banner]');
    const bannerInstallBtn = document.querySelector('[data-pwa-install-button]');
    const bannerDismissBtn = document.querySelector('[data-pwa-install-dismiss]');
    const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
    const ua = navigator.userAgent || '';
    const isIos = /iphone|ipad|ipod/i.test(ua);
    const isSafari = /Safari/i.test(ua) && !/Chrome|CriOS|Edg/i.test(ua);

    function revealInstallCtas() {
        if (headerBtn) headerBtn.hidden = false;
        if (bannerEl) bannerEl.hidden = false;
    }

    function handlePromptReady(event) {
        deferredPrompt = event?.detail || event || null;
        if (deferredPrompt) {
            installState.deferredPrompt = deferredPrompt;
            revealInstallCtas();
        }
    }

    if (deferredPrompt) {
        revealInstallCtas();
    }

    window.addEventListener('pwa:installprompt', handlePromptReady);

    // Show guidance on iOS Safari where the prompt API is not available
    if (!isStandalone && isIos && isSafari) {
        if (bannerEl) bannerEl.hidden = false;
        if (headerBtn) headerBtn.hidden = false;
    }

    // Header button now redirects to installation page
    // No need for click handler as it's now a link

    if (bannerInstallBtn) {
        bannerInstallBtn.addEventListener('click', function () {
            // Redirect to installation page
            window.location.href = '{{ route("pwa.install") }}';
        });
    }

    if (bannerDismissBtn && bannerEl) {
        bannerDismissBtn.addEventListener('click', function () {
            bannerEl.hidden = true;
        });
    }

    // Fallback: if no beforeinstallprompt fires within a short time and not installed, show banner so users get guidance
    if (!isStandalone && bannerEl) {
        setTimeout(function () {
            if (!deferredPrompt && !installState.deferredPrompt) {
                bannerEl.hidden = false;
                if (headerBtn) headerBtn.hidden = false;
            }
        }, 2000);
    }

    window.addEventListener('appinstalled', function () {
        installState.deferredPrompt = null;
        if (headerBtn) headerBtn.hidden = true;
        if (bannerEl) bannerEl.hidden = true;
    });
})();
</script>

<script>
// Check for PWA updates (logo changes) periodically
(function() {
    if (!('serviceWorker' in navigator)) {
        return;
    }

    function checkForUpdate() {
        navigator.serviceWorker.ready.then(registration => {
            if (!registration.active) return;

            const messageChannel = new MessageChannel();
            messageChannel.port1.onmessage = (event) => {
                if (event.data.updated) {
                    console.log('[PWA] New version available, will reload on next activation');
                    // Show update notification if desired
                    // You can add a toast/notification here
                }
            };

            registration.active.postMessage(
                { type: 'CHECK_UPDATE' },
                [messageChannel.port2]
            );
        });
    }

    // Listen for service worker updates
    navigator.serviceWorker.addEventListener('controllerchange', () => {
        console.log('[PWA] New service worker activated, reloading page...');
        window.location.reload();
    });

    // Check for updates when page becomes visible
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) {
            checkForUpdate();
        }
    });

    // Check for updates every 5 minutes
    setInterval(checkForUpdate, 5 * 60 * 1000);

    // Initial check after 10 seconds
    setTimeout(checkForUpdate, 10000);
})();
</script>
@endonce
