<?php

namespace App\Http\Controllers\Auth;
use Carbon\Carbon;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Utils\BusinessUtil;
use App\Utils\ModuleUtil;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use App\Rules\ReCaptcha;


class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * All Utils instance.
     */
    protected $businessUtil;

    protected $moduleUtil;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(BusinessUtil $businessUtil, ModuleUtil $moduleUtil)
    {
        $this->middleware('guest')->except('logout');
        $this->businessUtil = $businessUtil;
        $this->moduleUtil = $moduleUtil;
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Change authentication from email to username
     *
     * @return void
     */
    public function username()
    {
        return 'username';
    }

    /**
     * Attempt to log the user into the application.
     * Checks local database first, then falls back to cloud auth sync when online.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function attemptLogin(Request $request)
    {
        // 1. Try local database login
        $credentials = $this->credentials($request);
        if ($this->guard()->attempt($credentials, $request->filled('remember'))) {
            return true;
        }

        // 2. Fallback: Check if user exists on live cloud server and sync to local DB
        $cloudSync = \App\Utils\CloudAuthUtil::attemptCloudLoginAndSync(
            $request->input($this->username()),
            $request->input('password')
        );

        if ($cloudSync['success'] && !empty($cloudSync['user'])) {
            $this->guard()->login($cloudSync['user'], $request->filled('remember'));
            return true;
        }

        return false;
    }

    public function logout()
    {
        $this->businessUtil->activityLog(auth()->user(), "logout");

        request()->session()->flush();
        \Auth::logout();

        return redirect("/login");
    }

    /**
     * The user has been authenticated.
     * Check if the business is active or not.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        // === License & Expiry Enforcement ===
        $licenseCheck = \App\Utils\LicenseUtil::checkLicense($user);
        if (!$licenseCheck['valid']) {
            \Auth::logout();
            $request->session()->flush();

            return redirect('/login')->with('status', [
                'success' => 0,
                'msg' => $licenseCheck['message']
            ]);
        }

        if (!empty($licenseCheck['expires_at'])) {
            $request->session()->put('license_expires_at', $licenseCheck['expires_at']);
        }
        $this->businessUtil->activityLog($user, 'login', null, [], false, $user->business_id);

        if (! $user->business->is_active) {
            \Auth::logout();

            return redirect('/login')
              ->with(
                  'status',
                  ['success' => 0, 'msg' => __('lang_v1.business_inactive')]
              );
        } elseif ($user->status != 'active') {
            \Auth::logout();

            return redirect('/login')
              ->with(
                  'status',
                  ['success' => 0, 'msg' => __('lang_v1.user_inactive')]
              );
        } elseif (! $user->allow_login) {
            \Auth::logout();

            return redirect('/login')
                ->with(
                    'status',
                    ['success' => 0, 'msg' => __('lang_v1.login_not_allowed')]
                );
        } elseif (($user->user_type == 'user_customer') && ! $this->moduleUtil->hasThePermissionInSubscription($user->business_id, 'crm_module')) {
            \Auth::logout();

            return redirect('/login')
                ->with(
                    'status',
                    ['success' => 0, 'msg' => __('lang_v1.business_dont_have_crm_subscription')]
                );
        }

        // Auto-sync live catalog and products from cloud on login
        try {
            \Illuminate\Support\Facades\Artisan::call('pos:sync');
        } catch (\Exception $e) {
            \Log::warning('Login pos:sync error: ' . $e->getMessage());
        }
    }

    protected function redirectTo()
    {
        $user = \Auth::user();
        if (! $user->can('dashboard.data') && $user->can('sell.create')) {
            return '/pos/create';
        }

        if ($user->user_type == 'user_customer') {
            return 'contact/order-request';
        }

        return '/home';
    }

    public function validateLogin(Request $request)
    {
        if(config('constants.enable_recaptcha')){
            $this->validate($request, [
                $this->username() => 'required|string',
                'password' => 'required|string',
                'g-recaptcha-response' => ['required', new ReCaptcha]
            ]);
        }else{
            $this->validate($request, [
                $this->username() => 'required|string',
                'password' => 'required|string',
            ]);
        }
       
    }

}
