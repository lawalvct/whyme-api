<?php

use App\Modules\Platform\Http\Controllers\ConfigController;
use App\Modules\Platform\Http\Controllers\HealthController;
use Illuminate\Support\Facades\Route;

Route::name('platform.')->group(function () {
    Route::get('health', HealthController::class)->name('health');
    Route::get('config', ConfigController::class)->name('config');
});
