<?php

use Illuminate\Support\Facades\Route;
use Modules\Searchbox\Http\Controllers\AiConfigController;
use Modules\Searchbox\Http\Controllers\AiSearchController;
use Modules\Searchbox\Http\Controllers\UniversalSearchController;

Route::get('/universal-search', [UniversalSearchController::class, 'search'])->name('searchbox.universal-search');
Route::get('/universal-search-ai', [AiSearchController::class, 'search'])->name('searchbox.universal-search-ai');

Route::middleware(['AdminSidebarMenu'])->group(function () {
    Route::get('/ai-config', [AiConfigController::class, 'index'])->name('searchbox.ai-config');
    Route::post('/ai-config', [AiConfigController::class, 'update'])->name('searchbox.ai-config.update');
});
