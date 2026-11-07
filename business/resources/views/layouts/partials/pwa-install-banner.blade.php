@php
    $installBusinessId = session('business.id') ?? (optional(auth()->user())->business_id ?? null);
    $installBusinessName = session('business.name', config('app.name', 'UltimatePOS'));
    $installBusinessLogo = $installBusinessId
        ? route('pwa.icon', ['business' => $installBusinessId, 'size' => 128])
        : asset('img/logo-small.png');
@endphp

{{-- @if (request()->routeIs('home'))
    
<div class="pwa-install-banner" data-pwa-install-banner hidden>
    <div class="pwa-install-card">
        <div class="pwa-install-body">
            <img class="pwa-install-logo" src="{{ $installBusinessLogo }}" alt="{{ $installBusinessName }} logo">
            <div class="pwa-install-copy">
                <p class="pwa-install-title">{{ $installBusinessName }}</p>
                <p class="pwa-install-subtitle">Install this app to access {{ $installBusinessName }} faster on this device.</p>
            </div>
        </div>
        <div class="pwa-install-actions">
            <button type="button" class="pwa-install-btn" data-pwa-install-button>Install app</button>
            <button type="button" class="pwa-install-dismiss" data-pwa-install-dismiss aria-label="Dismiss">&times;</button>
        </div>
    </div>
</div>
@endif --}}

<style>
    .pwa-install-banner {
        position: fixed;
        bottom: 1.5rem;
        right: 1.5rem;
        z-index: 2147483647;
        max-width: 360px;
        width: calc(100% - 3rem);
    }
    .pwa-install-card {
        background: #111827;
        color: #fff;
        border-radius: 1rem;
        box-shadow: 0 20px 40px rgba(15, 23, 42, 0.35);
        padding: 1rem 1.25rem;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }
    .pwa-install-body {
        display: flex;
        gap: 0.75rem;
        align-items: center;
    }
    .pwa-install-logo {
        width: 48px;
        height: 48px;
        border-radius: 0.75rem;
        object-fit: cover;
        background: #fff;
        padding: 0.25rem;
    }
    .pwa-install-copy {
        flex: 1;
    }
    .pwa-install-title {
        font-weight: 600;
        margin: 0;
        font-size: 1rem;
    }
    .pwa-install-subtitle {
        margin: 0;
        font-size: 0.85rem;
        opacity: 0.85;
    }
    .pwa-install-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.75rem;
    }
    .pwa-install-btn {
        flex: 1;
        border: none;
        background: #10b981;
        color: #fff;
        font-weight: 600;
        padding: 0.6rem 1rem;
        border-radius: 999px;
        cursor: pointer;
    }
    .pwa-install-btn:hover {
        background: #0ea271;
    }
    .pwa-install-dismiss {
        border: none;
        background: transparent;
        color: rgba(255, 255, 255, 0.7);
        font-size: 1.5rem;
        line-height: 1;
        cursor: pointer;
    }
    @media (max-width: 640px) {
        .pwa-install-banner {
            left: 1rem;
            right: 1rem;
            width: calc(100% - 2rem);
        }
        .pwa-install-card {
            flex-direction: column;
        }
        .pwa-install-body {
            align-items: flex-start;
        }
        .pwa-install-actions {
            flex-direction: row;
        }
    }
</style>
