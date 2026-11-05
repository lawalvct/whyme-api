<?php

use App\Modules\Identity\Http\Controllers\AuthController;
use App\Modules\Identity\Http\Controllers\MeController;
use App\Modules\Identity\Http\Middleware\EnsureAccountActive;
use App\Modules\Platform\Http\Middleware\EnsureIdempotency;
use Illuminate\Support\Facades\Route;

// Sign-in responses carry tokens, so they are never stored for idempotent replay.
Route::prefix('auth')->name('auth.')->withoutMiddleware(EnsureIdempotency::class)->group(function () {
    Route::post('otp', [AuthController::class, 'requestOtp'])->middleware('throttle:otp')->name('otp');
    Route::post('register', [AuthController::class, 'register'])->middleware('throttle:auth')->name('register');
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:auth')->name('login');
    Route::post('password/reset', [AuthController::class, 'resetPassword'])->middleware('throttle:auth')->name('password.reset');
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum')->name('logout');
});

Route::middleware(['auth:sanctum', EnsureAccountActive::class])->prefix('me')->name('me.')->group(function () {
    Route::get('/', [MeController::class, 'show'])->name('show');
    Route::patch('/', [MeController::class, 'update'])->name('update');
    Route::post('capabilities', [MeController::class, 'activateCapability'])->name('capabilities.store');
});
