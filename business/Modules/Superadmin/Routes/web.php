<?php

// use App\Http\Controllers\BusinessController;
// use App\Http\Controllers\Modules;
// use Illuminate\Support\Facades\Route;

Route::get('/pricing', [Modules\Superadmin\Http\Controllers\PricingController::class, 'index'])->name('pricing');
Route::get('/package-duration-update', [Modules\Superadmin\Http\Controllers\PricingController::class, 'package_duration_update'])->name('package_duration_update');

Route::middleware('web', 'auth', 'language', 'AdminSidebarMenu', 'superadmin')->prefix('superadmin')->group(function () {
    Route::get('/install', [Modules\Superadmin\Http\Controllers\InstallController::class, 'index']);
    Route::post('/install', [Modules\Superadmin\Http\Controllers\InstallController::class, 'install']);
    Route::get('/install/update', [Modules\Superadmin\Http\Controllers\InstallController::class, 'update']);
    Route::get('/install/uninstall', [Modules\Superadmin\Http\Controllers\InstallController::class, 'uninstall']);
    Route::post('/install/update', [Modules\Superadmin\Http\Controllers\InstallController::class, 'updateExecute']);

    Route::get('/', [Modules\Superadmin\Http\Controllers\SuperadminController::class, 'index']);
    Route::get('/stats', [Modules\Superadmin\Http\Controllers\SuperadminController::class, 'stats']);

    Route::get('/{business_id}/toggle-active/{is_active}', [Modules\Superadmin\Http\Controllers\BusinessController::class, 'toggleActive']);

    Route::get('/users/{business_id}', [Modules\Superadmin\Http\Controllers\BusinessController::class, 'usersList']);
    Route::post('/update-password', [Modules\Superadmin\Http\Controllers\BusinessController::class, 'updatePassword']);

    Route::resource('/business', Modules\Superadmin\Http\Controllers\BusinessController::class);
    Route::get('/business/{id}/destroy', [Modules\Superadmin\Http\Controllers\BusinessController::class, 'destroy']);

    Route::resource('/packages', 'Modules\Superadmin\Http\Controllers\PackagesController');
    Route::get('/packages/{id}/destroy', [Modules\Superadmin\Http\Controllers\PackagesController::class, 'destroy']);

    Route::resource('/coupons', 'Modules\Superadmin\Http\Controllers\CouponController');
    Route::get('/coupons/{id}/destroy', [Modules\Superadmin\Http\Controllers\CouponController::class, 'destroy']);
    
    Route::get('/settings', [Modules\Superadmin\Http\Controllers\SuperadminSettingsController::class, 'edit']);
    Route::put('/settings', [Modules\Superadmin\Http\Controllers\SuperadminSettingsController::class, 'update']);

    Route::get('/affiliate-settings', [Modules\Superadmin\Http\Controllers\AffiliateSettingsController::class, 'edit']);
    Route::put('/affiliate-settings', [Modules\Superadmin\Http\Controllers\AffiliateSettingsController::class, 'update']);

    Route::get('/affiliates', [Modules\Superadmin\Http\Controllers\SuperadminAffiliatesController::class, 'index']);
    /*
     * Declared before /affiliates/{id}: routes match in the order they are
     * listed, so the other way round "payout" would be read as an affiliate id.
     */
    Route::get('/affiliates/payout', [Modules\Superadmin\Http\Controllers\SuperadminAffiliatesController::class, 'preparePayout']);
    Route::post('/affiliates/payout', [Modules\Superadmin\Http\Controllers\SuperadminAffiliatesController::class, 'storeBulkPayout']);
    Route::get('/affiliates/{id}', [Modules\Superadmin\Http\Controllers\SuperadminAffiliatesController::class, 'show']);
    Route::post('/affiliates/{id}/status', [Modules\Superadmin\Http\Controllers\SuperadminAffiliatesController::class, 'updateStatus']);
    Route::post('/affiliates/{id}/payouts', [Modules\Superadmin\Http\Controllers\SuperadminAffiliatesController::class, 'storePayout']);
    Route::post('/affiliates/{id}/rates', [Modules\Superadmin\Http\Controllers\SuperadminAffiliatesController::class, 'updateRates']); //AFF-RATES
    Route::post('/affiliates/{id}/team', [Modules\Superadmin\Http\Controllers\SuperadminAffiliatesController::class, 'updateTeam']); //AFF-TEAM
    Route::post('/affiliates/{id}/team-members', [Modules\Superadmin\Http\Controllers\SuperadminAffiliatesController::class, 'addTeamMembers']); //AFF-TEAM3
    Route::post('/affiliates/{id}/team-members/{member}/remove', [Modules\Superadmin\Http\Controllers\SuperadminAffiliatesController::class, 'removeTeamMember']); //AFF-TEAM3
    Route::post('/affiliate-commissions/{id}/status', [Modules\Superadmin\Http\Controllers\SuperadminAffiliatesController::class, 'updateCommissionStatus']);

    Route::get('/edit-subscription/{id}', [Modules\Superadmin\Http\Controllers\SuperadminSubscriptionsController::class, 'editSubscription']);
    Route::post('/update-subscription', [Modules\Superadmin\Http\Controllers\SuperadminSubscriptionsController::class, 'updateSubscription']);
    Route::resource('/superadmin-subscription', 'Modules\Superadmin\Http\Controllers\SuperadminSubscriptionsController');

    Route::get('/communicator', [Modules\Superadmin\Http\Controllers\CommunicatorController::class, 'index']);
    Route::post('/communicator/send', [Modules\Superadmin\Http\Controllers\CommunicatorController::class, 'send']);
    Route::get('/communicator/get-history', [Modules\Superadmin\Http\Controllers\CommunicatorController::class, 'getHistory']);

    Route::resource('/frontend-pages', 'Modules\Superadmin\Http\Controllers\PageController');
});

Route::middleware('web', 'SetSessionData', 'auth', 'language', 'timezone', 'AdminSidebarMenu')->group(function () {
    //Routes related to paypal checkout
    Route::post('/paypal-express-checkout', [Modules\Superadmin\Http\Controllers\SubscriptionController::class, 'paypalExpressCheckout'])->name('paypalExpressCheckout');

    Route::post('/capture-paypal-order', [Modules\Superadmin\Http\Controllers\SubscriptionController::class, 'capturePaypalOrder'])->name('capturePaypalOrder');


    Route::get('/subscription/post-flutterwave-payment', [Modules\Superadmin\Http\Controllers\SubscriptionController::class, 'postFlutterwavePaymentCallback']);

    Route::post('/subscription/pay-stack', [Modules\Superadmin\Http\Controllers\SubscriptionController::class, 'getRedirectToPaystack']);
    Route::get('/subscription/post-payment-pay-stack-callback', [Modules\Superadmin\Http\Controllers\SubscriptionController::class, 'postPaymentPaystackCallback']);

    //Routes related to pesapal checkout
    Route::get('/subscription/{package_id}/pesapal-callback', [Modules\Superadmin\Http\Controllers\SubscriptionController::class, 'pesapalCallback'])->name('pesapalCallback');

    Route::get('/subscription/{package_id}/pay', [Modules\Superadmin\Http\Controllers\SubscriptionController::class, 'pay']);
    Route::any('/subscription/{package_id?}/confirm', [Modules\Superadmin\Http\Controllers\SubscriptionController::class, 'confirm'])->name('subscription-confirm');
    Route::get('/all-subscriptions', [Modules\Superadmin\Http\Controllers\SubscriptionController::class, 'allSubscriptions']);

    Route::get('/subscription/{package_id}/register-pay', [Modules\Superadmin\Http\Controllers\SubscriptionController::class, 'registerPay'])->name('register-pay');

    Route::resource('/subscription', 'Modules\Superadmin\Http\Controllers\SubscriptionController');

    Route::get('/subscription/{subcription_id}/force-active', [Modules\Superadmin\Http\Controllers\SubscriptionController::class, 'forceActive'])->name('force-active');
    Route::get('/myfatoorah-callback', [Modules\Superadmin\Http\Controllers\SubscriptionController::class, 'myfatoorahcallback'])->name('myfatoorah_callback');

    //Affiliate facing routes
    Route::get('/affiliate', [Modules\Superadmin\Http\Controllers\AffiliateController::class, 'index'])->name('affiliate.dashboard');
    Route::get('/affiliate/apply', [Modules\Superadmin\Http\Controllers\AffiliateController::class, 'create'])->name('affiliate.apply');
    Route::post('/affiliate/apply', [Modules\Superadmin\Http\Controllers\AffiliateController::class, 'store'])->name('affiliate.store');
    Route::post('/affiliate/team', [Modules\Superadmin\Http\Controllers\AffiliateController::class, 'addTeamMember'])->name('affiliate.team.add'); //AFF-TEAM5

});

Route::get('/page/{slug}', [Modules\Superadmin\Http\Controllers\PageController::class, 'showPage'])->name('frontend-pages');
