<?php

// WHY ME platform settings. Values that the owner must be able to change without
// a release live in the `settings` / `feature_flags` tables instead (MON06).

return [

    'otp' => [
        'ttl_seconds' => (int) env('OTP_TTL_SECONDS', 300),
        'max_attempts' => (int) env('OTP_MAX_ATTEMPTS', 5),
        'resend_cooldown_seconds' => (int) env('OTP_RESEND_COOLDOWN_SECONDS', 60),
    ],

    'sms' => [
        // log (local dev: the code is written to the log) | termii | array (tests)
        'driver' => env('SMS_DRIVER', 'log'),
        'termii' => [
            // Confirm the base URL and payload against Termii's current docs before going live.
            'base_url' => env('TERMII_BASE_URL', 'https://api.ng.termii.com'),
            'api_key' => env('TERMII_API_KEY'),
            'sender_id' => env('TERMII_SENDER_ID', 'WHYME'),
            'channel' => env('TERMII_CHANNEL', 'dnd'),
        ],
    ],

    'tokens' => [
        'expire_days' => (int) env('API_TOKEN_EXPIRE_DAYS', 30),
    ],

    'idempotency' => [
        'retention_days' => (int) env('IDEMPOTENCY_RETENTION_DAYS', 7),
        'lock_seconds' => 30,
    ],

    // Defaults only; the `app.min_version.*` settings override them (docs/01 §5.6).
    'app_versions' => [
        'android' => env('MIN_APP_VERSION_ANDROID', '0.0.0'),
        'ios' => env('MIN_APP_VERSION_IOS', '0.0.0'),
    ],

    'admin' => [
        // TOTP MFA for staff (NFR02). Off locally so the first login is quick; on in production.
        'mfa_required' => (bool) env('ADMIN_MFA_REQUIRED', env('APP_ENV') === 'production'),

        // First super-admin, created by `db:seed` (AdminSeeder). Remove from .env afterwards.
        'seed_email' => env('ADMIN_EMAIL'),
        'seed_password' => env('ADMIN_PASSWORD'),
    ],

];
