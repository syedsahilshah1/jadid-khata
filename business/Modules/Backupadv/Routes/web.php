<?php

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

use Modules\Backupadv\Http\Controllers\BackupadvController;

Route::prefix('Backupadv')->group(function() {
    Route::get('/', [BackupadvController::class, 'index']);
});

Route::middleware(['web', 'SetSessionData', 'auth', 'language', 'timezone', 'AdminSidebarMenu'])
    ->prefix('Backupadv')->group(function () {

    // Dashboard route
    Route::get('dashboard', [BackupadvController::class, 'dashboard'])->name('backup.dashboard');

    // النسخ الاحتياطي
    Route::get('/backup', [BackupadvController::class, 'backup'])->name('backup');

    // تنزيل النسخة الاحتياطية - بدون معرّف الشركة في المسار
    Route::get('/backup/download', [BackupadvController::class, 'downloadBackup'])->name('backup.download');

    // رفع النسخة الاحتياطية
    Route::get('/upload-backup', [BackupadvController::class, 'uploadBackup'])->name('upload.backup');
    Route::post('/backup/upload', [BackupadvController::class, 'storeBackup'])->name('backup.store'); 

    // سجل النسخ الاحتياطي
    Route::get('/backup-logs', [BackupadvController::class, 'backupLogs'])->name('backup.logs');

    // مسارات إدارة النسخ الاحتياطي
    Route::get('delete/{filename}', [BackupadvController::class, 'deleteBackup'])->name('backup.delete');
    Route::get('create', [BackupadvController::class, 'createNewBackup'])->name('backup.create');
    Route::post('bulk_download', [BackupadvController::class, 'bulkDownload'])->name('backup.bulk_download');
    Route::post('bulk_delete', [BackupadvController::class, 'bulkDelete'])->name('backup.bulk_delete'); // إضافة هذا السطر
    Route::get('restore/{filename}', [BackupadvController::class, 'restoreBackup'])->name('backup.restore'); // يجب عليك تنفيذ هذه الدالة في BackupadvController

    // Installation routes
    Route::get('/install', [\Modules\Backupadv\Http\Controllers\InstallController::class, 'index']);
    Route::get('/install/update', [\Modules\Backupadv\Http\Controllers\InstallController::class, 'update']);
    Route::get('/install/uninstall', [\Modules\Backupadv\Http\Controllers\InstallController::class, 'uninstall']);
});
