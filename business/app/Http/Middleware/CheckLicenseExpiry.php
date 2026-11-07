<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Utils\LicenseUtil;

class CheckLicenseExpiry
{
    /**
     * Handle an incoming request.
     * Enforces active license status on every request (online or offline).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $check = LicenseUtil::checkLicense($user);

            if (!$check['valid']) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => 0,
                        'license_expired' => true,
                        'msg' => $check['message'],
                        'redirect' => url('/login')
                    ], 403);
                }

                return redirect('/login')->with('status', [
                    'success' => 0,
                    'msg' => $check['message']
                ]);
            }

            // Expose earliest expiry in session for client-side timers
            if (!empty($check['expires_at'])) {
                $request->session()->put('license_expires_at', $check['expires_at']);
            }
        }

        return $next($request);
    }
}
