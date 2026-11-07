<?php
use Illuminate\Http\Request;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/gym-member-scanner', [Modules\Gym\Http\Controllers\MemberController::class, 'member_scanner'])->name('member_scanner');
Route::get('/get-signed-route', [Modules\Gym\Http\Controllers\MemberController::class, 'get_signed_route'])->name('get_signed_route');


Route::get('/member-details/{id}', [Modules\Gym\Http\Controllers\MemberController::class, 'show_member_details'])->name('show_member_details');

Route::get('/member-details/{id}', [Modules\Gym\Http\Controllers\MemberController::class, 'show_member_details'])->name('show_member_details')->middleware('signed');

Route::middleware('web', 'auth', 'language', 'AdminSidebarMenu')->prefix('gym')->group(function () {
    Route::get('dashboard', [Modules\Gym\Http\Controllers\DashBoardController::class, 'index']);

    Route::resource('members', Modules\Gym\Http\Controllers\MemberController::class);

    Route::get('member/{id}/profile', [Modules\Gym\Http\Controllers\MemberController::class, 'member_profile']);
    Route::get('member/{id}/id-card', [Modules\Gym\Http\Controllers\MemberController::class, 'id_card']);

    Route::get('member/{id}/health', [Modules\Gym\Http\Controllers\MemberController::class, 'add_health']);
    Route::post('member/{id}/health-store/', [Modules\Gym\Http\Controllers\MemberController::class, 'store_health']);

    Route::get('attendance', [Modules\Gym\Http\Controllers\AttendanceController::class, 'index']);
    Route::get('get-in/{id}', [Modules\Gym\Http\Controllers\AttendanceController::class, 'get_in']);
    Route::get('get-out/{id}', [Modules\Gym\Http\Controllers\AttendanceController::class, 'get_out']);
    Route::post('add-edit-in-time', [Modules\Gym\Http\Controllers\AttendanceController::class, 'add_edit_in_time']);
    Route::post('add-edit-out-time', [Modules\Gym\Http\Controllers\AttendanceController::class, 'add_edit_out_time']);
    
    Route::resource('gym-packages', Modules\Gym\Http\Controllers\PackageController::class);
    Route::get('/gym-packages/{id}/destroy', [Modules\Gym\Http\Controllers\PackageController::class, 'destroy'])->name('delete_package');

    Route::get('add-subscription/{id}', [Modules\Gym\Http\Controllers\SubscriptionController::class, 'add_subscription']);
    Route::get('/get-end_date', [Modules\Gym\Http\Controllers\SubscriptionController::class, 'get_end_date'])->name('get_end_date');
    Route::resource('/subscriptions', Modules\Gym\Http\Controllers\SubscriptionController::class);

    Route::resource('settings', Modules\Gym\Http\Controllers\SettingController::class);

    Route::resource('classes', Modules\Gym\Http\Controllers\ClassController::class);
    Route::get('/class/{id}/destroy', [Modules\Gym\Http\Controllers\ClassController::class, 'destroy'])->name('delete_class');

    Route::resource('diets', Modules\Gym\Http\Controllers\MemberDietController::class);

    Route::get('install', [\Modules\Gym\Http\Controllers\InstallController::class, 'index']);
    Route::post('install', [\Modules\Gym\Http\Controllers\InstallController::class, 'install']);
    Route::get('install/uninstall', [\Modules\Gym\Http\Controllers\InstallController::class, 'uninstall']);
    Route::get('install/update', [\Modules\Gym\Http\Controllers\InstallController::class, 'update']);

});
