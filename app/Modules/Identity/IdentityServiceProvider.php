<?php

namespace App\Modules\Identity;

use App\Modules\Identity\Models\Admin;
use App\Modules\Identity\Services\PhoneNumber;
use App\Modules\Identity\Services\Sms\ArraySmsGateway;
use App\Modules\Identity\Services\Sms\LogSmsGateway;
use App\Modules\Identity\Services\Sms\SmsGateway;
use App\Modules\Identity\Services\Sms\TermiiSmsGateway;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class IdentityServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SmsGateway::class, fn () => match (config('whyme.sms.driver')) {
            'termii' => new TermiiSmsGateway(config('whyme.sms.termii')),
            'array' => new ArraySmsGateway,
            default => new LogSmsGateway,
        });
    }

    public function boot(): void
    {
        $phone = fn (Request $request): string => PhoneNumber::normalise(
            is_string($request->input('phone')) ? $request->input('phone') : null,
        ) ?? 'invalid';

        // OTP sending costs money and can be abused: limit per phone and per IP (ACC03).
        RateLimiter::for('otp', fn (Request $request) => [
            Limit::perHour(5)->by('otp-phone:'.$phone($request)),
            Limit::perHour(20)->by('otp-ip:'.$request->ip()),
        ]);

        // Password and code guessing.
        RateLimiter::for('auth', fn (Request $request) => [
            Limit::perMinute(5)->by('auth-phone:'.$phone($request).'|'.$request->ip()),
            Limit::perMinute(20)->by('auth-ip:'.$request->ip()),
        ]);

        // Super-admins pass every permission check; everyone else needs explicit permissions.
        Gate::before(fn ($user) => $user instanceof Admin && $user->hasRole('super-admin') ? true : null);
    }
}
