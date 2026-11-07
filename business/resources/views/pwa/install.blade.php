<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $businessName }} - Install App</title>
    @include('layouts.partials.pwa-meta')
    <style>
        :root {
            color-scheme: light dark;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Open Sans', sans-serif;
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #111827;
            padding: 1.5rem;
        }
        .card {
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 3rem 2.5rem;
            max-width: 480px;
            width: 100%;
            text-align: center;
        }
        .app-icon {
            width: 120px;
            height: 120px;
            margin: 0 auto 1.5rem;
            border-radius: 1.5rem;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }
        .app-icon img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        h1 {
            font-size: 1.75rem;
            margin-bottom: 0.5rem;
            color: #1f2937;
        }
        .subtitle {
            font-size: 1rem;
            color: #6b7280;
            margin-bottom: 2rem;
        }
        .features {
            text-align: left;
            margin: 2rem 0;
            padding: 0;
            list-style: none;
        }
        .features li {
            padding: 0.75rem 0;
            display: flex;
            align-items: center;
            color: #4b5563;
        }
        .features li:before {
            content: "✓";
            display: inline-block;
            width: 24px;
            height: 24px;
            background: #10b981;
            color: white;
            border-radius: 50%;
            text-align: center;
            line-height: 24px;
            margin-right: 0.75rem;
            font-weight: bold;
            flex-shrink: 0;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 1rem 2rem;
            border-radius: 0.5rem;
            text-decoration: none;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.2s ease;
            border: none;
            cursor: pointer;
            width: 100%;
            margin-bottom: 1rem;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
        }
        .btn-secondary {
            background: #f3f4f6;
            color: #4b5563;
        }
        .btn-secondary:hover {
            background: #e5e7eb;
        }
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        .btn:disabled:hover {
            transform: none;
            box-shadow: none;
        }
        .install-icon {
            margin-right: 0.5rem;
        }
        .status-message {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            font-size: 0.875rem;
        }
        .status-success {
            background: #d1fae5;
            color: #065f46;
        }
        .status-info {
            background: #dbeafe;
            color: #1e40af;
        }
        .status-warning {
            background: #fef3c7;
            color: #92400e;
        }
        .ios-instructions {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            padding: 1.5rem;
            margin-top: 1rem;
            text-align: left;
        }
        .ios-instructions h3 {
            font-size: 1rem;
            margin: 0 0 1rem 0;
            color: #1f2937;
        }
        .ios-instructions ol {
            margin: 0;
            padding-left: 1.5rem;
            color: #4b5563;
        }
        .ios-instructions li {
            margin-bottom: 0.5rem;
        }
        small {
            display: block;
            margin-top: 1.5rem;
            color: #9ca3af;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="app-icon">
            <img src="{{ asset('img/logo-small.png') }}" alt="{{ $businessName }}">
        </div>

        <h1>{{ $businessName }}</h1>
        <p class="subtitle">Install our app for a better experience</p>

        <div id="statusMessage" style="display: none;"></div>

        <ul class="features">
            <li>Works offline with cached data</li>
            <li>Fast and responsive interface</li>
            <li>Access from your home screen</li>
            <li>Receive notifications</li>
        </ul>

        <div id="installButtons">
            <button id="installBtn" class="btn btn-primary" style="display: none;">
                <span class="install-icon">⬇</span>
                Install App
            </button>

            <div id="iosInstructions" style="display: none;" class="ios-instructions">
                <h3>How to Install on iPhone/iPad:</h3>
                <ol>
                    <li>Tap the Share button <strong>⎋</strong> in Safari</li>
                    <li>Scroll down and tap <strong>"Add to Home Screen"</strong></li>
                    <li>Tap <strong>"Add"</strong> to confirm</li>
                </ol>
            </div>

            <div id="androidInstructions" style="display: none;" class="ios-instructions">
                <h3>How to Install:</h3>
                <ol>
                    <li>Tap the menu button (⋮) in your browser</li>
                    <li>Select <strong>"Add to Home screen"</strong> or <strong>"Install app"</strong></li>
                    <li>Tap <strong>"Add"</strong> or <strong>"Install"</strong> to confirm</li>
                </ol>
            </div>
        </div>

        <a href="{{ url('/login') }}" class="btn btn-secondary">
            Back to Home
        </a>

        <small>{{ $businessName }}</small>
    </div>

    @include('layouts.partials.pwa-scripts')

    <script>
    (function() {
        const installBtn = document.getElementById('installBtn');
        const statusMessage = document.getElementById('statusMessage');
        const iosInstructions = document.getElementById('iosInstructions');
        const androidInstructions = document.getElementById('androidInstructions');

        const ua = navigator.userAgent || '';
        const isIos = /iphone|ipad|ipod/i.test(ua);
        const isSafari = /Safari/i.test(ua) && !/Chrome|CriOS|Edg/i.test(ua);
        const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;

        function showStatus(message, type = 'info') {
            statusMessage.className = 'status-message status-' + type;
            statusMessage.textContent = message;
            statusMessage.style.display = 'block';
        }

        // Check if already installed
        if (isStandalone) {
            showStatus('App is already installed!', 'success');
            installBtn.style.display = 'none';
            return;
        }

        // Handle iOS Safari
        if (isIos && isSafari) {
            iosInstructions.style.display = 'block';
            showStatus('Follow the instructions below to install', 'info');
            return;
        }

        // Wait for deferred prompt
        let deferredPrompt = null;

        function checkForPrompt() {
            // Check if prompt is already captured
            if (window.__pwaInstallState && window.__pwaInstallState.deferredPrompt) {
                deferredPrompt = window.__pwaInstallState.deferredPrompt;
                installBtn.style.display = 'block';
                showStatus('Ready to install!', 'success');
                return;
            }

            // Listen for the prompt event
            window.addEventListener('pwa:installprompt', function(event) {
                deferredPrompt = event.detail;
                installBtn.style.display = 'block';
                showStatus('Ready to install!', 'success');
            });

            // If no prompt after a delay, show manual instructions
            setTimeout(function() {
                if (!deferredPrompt) {
                    androidInstructions.style.display = 'block';
                    showStatus('Use your browser menu to install', 'info');
                }
            }, 2000);
        }

        // Install button click handler
        installBtn.addEventListener('click', async function() {
            if (!deferredPrompt) {
                showStatus('Installation prompt not available', 'warning');
                androidInstructions.style.display = 'block';
                return;
            }

            installBtn.disabled = true;
            showStatus('Opening installation prompt...', 'info');

            try {
                await deferredPrompt.prompt();
                const choiceResult = await deferredPrompt.userChoice;

                if (choiceResult.outcome === 'accepted') {
                    showStatus('Installing app...', 'success');
                    setTimeout(function() {
                        window.location.href = '{{ url("/") }}';
                    }, 1500);
                } else {
                    showStatus('Installation cancelled', 'warning');
                    installBtn.disabled = false;
                }

                deferredPrompt = null;
                if (window.__pwaInstallState) {
                    window.__pwaInstallState.deferredPrompt = null;
                }
            } catch (error) {
                console.error('Installation error:', error);
                showStatus('Installation failed. Try using your browser menu.', 'warning');
                androidInstructions.style.display = 'block';
                installBtn.disabled = false;
            }
        });

        // Listen for successful installation
        window.addEventListener('appinstalled', function() {
            showStatus('App installed successfully!', 'success');
            installBtn.style.display = 'none';
            setTimeout(function() {
                window.location.href = '{{ url("/") }}';
            }, 2000);
        });

        // Start checking for the install prompt
        if (document.readyState === 'complete') {
            checkForPrompt();
        } else {
            window.addEventListener('load', checkForPrompt);
        }
    })();
    </script>
</body>
</html>
