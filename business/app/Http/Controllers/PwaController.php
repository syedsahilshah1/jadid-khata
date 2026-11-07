<?php

namespace App\Http\Controllers;

use App\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class PwaController extends Controller
{
    /**
     * Returns a manifest that reflects the active business branding.
     */
    public function manifest(Request $request)
    {
        $business = $this->resolveBusiness($request);
        $businessId = $business?->id ?? 'default';
        $businessName = $business->name ?? session('business.name', config('app.name', 'UltimatePOS'));
        $themeColor = $this->resolveThemeColor($business);
        $startUrl = '/login' . ($business ? '?business_id=' . $businessId : '');

        // Get logo version for cache busting
        $logoVersion = $this->getLogoVersion($business);

        $iconSizes = [72, 96, 128, 144, 152, 192, 384, 512];
        $icons = array_map(function ($size) use ($businessId, $logoVersion) {
            return [
                'src' => route('pwa.icon', ['business' => $businessId, 'size' => $size], false) . '?v=' . $logoVersion,
                'sizes' => "{$size}x{$size}",
                'type' => 'image/png',
                'purpose' => 'any maskable',
            ];
        }, $iconSizes);

        $manifest = [
            'id' => 'upos-business-' . $businessId,
            'name' => $businessName,
            'short_name' => Str::limit($businessName, 24, ''),
            'description' => $businessName . ' Progressive Web App',
            'start_url' => $startUrl,
            'scope' => '/',
            'display' => 'standalone',
            'orientation' => 'portrait',
            'theme_color' => $themeColor,
            'background_color' => '#ffffff',
            'lang' => app()->getLocale(),
            'icons' => $icons,
            'shortcuts' => [
                [
                    'name' => 'Login',
                    'url' => $startUrl,
                ],
            ],
            'version' => $logoVersion, // Add version to manifest
        ];

        return response()
            ->json($manifest)
            ->header('Content-Type', 'application/manifest+json');
    }

    /**
     * Streams a resized icon for the requested business.
     */
    public function icon(Request $request, $business, $size)
    {
        $size = (int) $size;
        $allowedSizes = [72, 96, 128, 144, 152, 192, 384, 512];
        if (! in_array($size, $allowedSizes, true)) {
            abort(404);
        }

        $businessModel = $this->lookupBusiness($business);
        $logoPath = $this->resolveLogoPath($businessModel);

        return $this->streamIcon($logoPath, $size);
    }

    /**
     * Simple offline fallback page.
     */
    public function offline()
    {
        return response()
            ->view('pwa.offline')
            ->header('Cache-Control', 'no-store');
    }

    /**
     * PWA installation page.
     */
    public function install(Request $request)
    {
        $business = $this->resolveBusiness($request);
        $businessName = $business->name ?? session('business.name', config('app.name', 'UltimatePOS'));

        return view('pwa.install', [
            'businessName' => $businessName,
        ]);
    }

    /**
     * Returns a scoped service worker script.
     */
    public function serviceWorker(Request $request)
    {
        $business = $this->resolveBusiness($request);
        $businessId = $business?->id ?? 'default';
        $logoVersion = $this->getLogoVersion($business);
        $cacheName = "upos-pwa-{$businessId}-v{$logoVersion}";
        $offlineUrl = route('pwa.offline', [], false);
        $cachePrefix = 'upos-pwa-';
        $manifestUrl = route('pwa.manifest', ['business_id' => $businessId], false);

        $script = <<<JS
const CACHE_NAME = '{$cacheName}';
const CACHE_PREFIX = '{$cachePrefix}';
const OFFLINE_URL = '{$offlineUrl}';
const MANIFEST_URL = '{$manifestUrl}';
const LOGO_VERSION = '{$logoVersion}';

self.addEventListener('install', (event) => {
    console.log('[PWA] Service Worker installing, version:', LOGO_VERSION);
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => cache.addAll([OFFLINE_URL]))
    );
    self.skipWaiting();
});

self.addEventListener("fetch", (event) => {
    const req = event.request;
    const url = new URL(req.url);

    // 1. NEVER intercept non-GET, POS routes, or AJAX / JSON calls
    if (req.method !== "GET") return;
    if (url.pathname.includes("/pos") || 
        url.pathname.includes("/contacts") || 
        url.pathname.includes("/sells") || 
        url.pathname.includes("/products") ||
        url.pathname.includes("/api/") ||
        req.headers.get("accept")?.includes("application/json") ||
        req.headers.get("x-requested-with") === "XMLHttpRequest") {
        return; // Browser naturally handles or fails cleanly without injecting offline HTML
    }

    // 2. Only serve offline HTML for direct browser navigation (page loads)
    if (req.mode === "navigate") {
        event.respondWith(
            fetch(req).catch(() => caches.match(OFFLINE_URL))
        );
        return;
    }

    // 3. Static assets
    event.respondWith(
        fetch(req).catch(() => caches.match(req))
    );
});

self.addEventListener('activate', (event) => {
    console.log('[PWA] Service Worker activating, clearing old caches');
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(
                keys
                    .filter((key) => key.startsWith(CACHE_PREFIX) && key !== CACHE_NAME)
                    .map((key) => {
                        console.log('[PWA] Deleting old cache:', key);
                        return caches.delete(key);
                    })
            )
        ).then(() => {
            console.log('[PWA] Service Worker activated, version:', LOGO_VERSION);
        })
    );
    self.clients.claim();
});

// Periodically check for manifest updates (logo changes)
self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'CHECK_UPDATE') {
        fetch(MANIFEST_URL)
            .then(response => response.json())
            .then(manifest => {
                if (manifest.version && manifest.version !== LOGO_VERSION) {
                    console.log('[PWA] New version detected, updating...');
                    self.registration.update();
                    event.ports[0].postMessage({ updated: true, newVersion: manifest.version });
                } else {
                    event.ports[0].postMessage({ updated: false });
                }
            })
            .catch(err => {
                console.error('[PWA] Error checking for updates:', err);
                event.ports[0].postMessage({ error: true });
            });
    }
});

self.addEventListener("fetch", (event) => {
    const req = event.request;
    const url = new URL(req.url);

    // 1. NEVER intercept non-GET, POS routes, or AJAX / JSON calls
    if (req.method !== "GET") return;
    if (url.pathname.includes("/pos") || 
        url.pathname.includes("/contacts") || 
        url.pathname.includes("/sells") || 
        url.pathname.includes("/products") ||
        url.pathname.includes("/api/") ||
        req.headers.get("accept")?.includes("application/json") ||
        req.headers.get("x-requested-with") === "XMLHttpRequest") {
        return; // Browser naturally handles or fails cleanly without injecting offline HTML
    }

    // 2. Only serve offline HTML for direct browser navigation (page loads)
    if (req.mode === "navigate") {
        event.respondWith(
            fetch(req).catch(() => caches.match(OFFLINE_URL))
        );
        return;
    }

    // 3. Static assets
    event.respondWith(
        fetch(req).catch(() => caches.match(req))
    );
});
JS;

        return response($script, 200)
            ->header('Content-Type', 'application/javascript')
            ->header('Service-Worker-Allowed', '/')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }

    protected function resolveBusiness(Request $request): ?Business
    {
        $businessId = $this->resolveBusinessId($request);
        if ($businessId) {
            return Business::find($businessId);
        }

        return Business::first();
    }

    protected function resolveBusinessId(Request $request): ?int
    {
        $requested = $request->query('business_id');
        if (ctype_digit((string) $requested)) {
            return (int) $requested;
        }

        if (session()->has('business.id')) {
            return (int) session('business.id');
        }

        if (auth()->check() && ! empty(auth()->user()->business_id)) {
            return (int) auth()->user()->business_id;
        }

        return null;
    }

    protected function resolveThemeColor(?Business $business): string
    {
        if (! empty($business?->theme_color)) {
            return $business->theme_color;
        }

        $sessionColor = session('business.theme_color');
        if (! empty($sessionColor)) {
            return $sessionColor;
        }

        return config('app.pwa_theme_color', '#0d6efd');
    }

    protected function lookupBusiness($identifier): ?Business
    {
        if (ctype_digit((string) $identifier)) {
            return Business::find((int) $identifier);
        }

        return null;
    }

    protected function resolveLogoPath(?Business $business): string
    {
        $candidates = [];
        if (! empty($business?->logo)) {
            $candidates[] = public_path('uploads/business_logos/' . $business->logo);
            $candidates[] = storage_path('app/public/business_logos/' . $business->logo);
        }

        foreach ($candidates as $path) {
            if ($path && File::exists($path)) {
                return $path;
            }
        }

        return public_path('img/logo-small.png');
    }

    protected function streamIcon(string $sourcePath, int $size)
    {
        if (! function_exists('imagecreatetruecolor')) {
            return response()->file($sourcePath, [
                'Cache-Control' => 'public, max-age=31536000',
                'Content-Type' => 'image/png',
            ]);
        }

        $source = $this->createImageResource($sourcePath);
        if (! $source) {
            return response()->file($sourcePath, [
                'Cache-Control' => 'public, max-age=31536000',
                'Content-Type' => 'image/png',
            ]);
        }

        $icon = imagecreatetruecolor($size, $size);
        imagesavealpha($icon, true);
        $transparent = imagecolorallocatealpha($icon, 0, 0, 0, 127);
        imagefill($icon, 0, 0, $transparent);

        imagecopyresampled(
            $icon,
            $source,
            0,
            0,
            0,
            0,
            $size,
            $size,
            imagesx($source),
            imagesy($source)
        );

        ob_start();
        imagepng($icon);
        $pngData = ob_get_clean();

        imagedestroy($icon);
        imagedestroy($source);

        return response($pngData, 200)->header('Content-Type', 'image/png')
            ->header('Cache-Control', 'public, max-age=31536000');
    }

    protected function createImageResource(string $path)
    {
        $info = @getimagesize($path);
        $mime = $info['mime'] ?? null;

        try {
            return match ($mime) {
                'image/png' => imagecreatefrompng($path),
                'image/jpeg' => imagecreatefromjpeg($path),
                'image/gif' => imagecreatefromgif($path),
                default => imagecreatefromstring(File::get($path)),
            };
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Get logo version based on file modification time or business updated_at
     */
    protected function getLogoVersion(?Business $business): string
    {
        // Try to get version from logo file modification time
        $logoPath = $this->resolveLogoPath($business);
        if (File::exists($logoPath)) {
            return (string) File::lastModified($logoPath);
        }

        // Fallback to business updated_at or current timestamp
        if ($business && $business->updated_at) {
            return (string) strtotime($business->updated_at);
        }

        return (string) time();
    }
}
