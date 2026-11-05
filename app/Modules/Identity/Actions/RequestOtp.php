<?php

namespace App\Modules\Identity\Actions;

use App\Modules\Identity\Enums\OtpPurpose;
use App\Modules\Identity\Models\User;
use App\Modules\Identity\Services\OtpService;

/**
 * Sends a code, or a notice, without revealing whether the phone has an
 * account (ACC03): the API response is the same either way.
 */
class RequestOtp
{
    public function __construct(private readonly OtpService $otp) {}

    public function handle(string $phone, OtpPurpose $purpose, ?string $ip, string $locale): void
    {
        $exists = User::where('phone', $phone)->exists();

        match ($purpose) {
            // An existing user who tries to sign up gets a "sign in instead" SMS, not a code.
            OtpPurpose::Register => $exists
                ? $this->otp->notify($phone, 'sms.already_registered', $locale)
                : $this->otp->send($phone, $purpose, $ip, $locale),
            // No SMS to numbers without an account: nothing to recover, and it saves SMS cost.
            OtpPurpose::ResetPassword, OtpPurpose::NewDevice => $exists
                ? $this->otp->send($phone, $purpose, $ip, $locale)
                : null,
        };
    }
}
