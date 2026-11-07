<?php

namespace App\Http\Middleware;

use App\Utils\Util;
use Closure;
use Illuminate\Support\Facades\Auth;

class Timezone
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $util = new Util;
        $timezone = config('app.timezone');

        if (session()->has('business.time_zone')) {
            $timezone = $request->session()->get('business.time_zone');
        } elseif (Auth::check() && ! empty(Auth::user()->business)) {
            $timezone = Auth::user()->business->time_zone;
        }

        $timezone = $util->getValidTimezone($timezone);

        config(['app.timezone' => $timezone]);
        date_default_timezone_set($timezone);

        return $next($request);
    }
}
