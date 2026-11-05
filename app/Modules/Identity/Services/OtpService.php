<?php

namespace App\Modules\Identity\Services;

use App\Modules\Identity\Enums\OtpPurpose;
use App\Modules\Identity\Models\OtpChallenge;
use App\Modules\Identity\Services\Sms\SmsGateway;
use App\Modules\Platform\Exceptions\ApiException;
use Illuminate\Support\Facades\DB;

/**
 * Six-digit SMS codes: hashed at rest, short-lived, limited attempts, resend
 * cooldown (ACC03). Used only for signup, recovery, new devices and sensitive changes.
 */
class OtpService
{
    public function __construct(private readonly SmsGateway $sms) {}

    public function send(string $phone, OtpPurpose $purpose, ?string $ip, ?string $locale = null): void
    {
        $this->assertCooldownPassed($phone, $purpose);

        $code = str_pad((string) random_int(0, 999_999), 6, '0', STR_PAD_LEFT);
        $ttl = config('whyme.otp.ttl_seconds');

        OtpChallenge::create([
            'phone' => $phone,
            'purpose' => $purpose,
            'code_hash' => $this->hash($phone, $code),
            'expires_at' => now()->addSeconds($ttl),
            'ip' => $ip,
        ]);

        $this->sms->send($phone, __('sms.otp', ['code' => $code, 'minutes' => intdiv($ttl, 60)], $locale));
    }

    /**
     * Sends a plain notice instead of a code, e.g. "you already have an account".
     */
    public function notify(string $phone, string $translationKey, ?string $locale = null): void
    {
        $this->sms->send($phone, __($translationKey, [], $locale));
    }

    /**
     * Consumes the latest valid code or throws otp_invalid / otp_expired.
     * Failed attempts are committed even though the request fails.
     */
    public function verify(string $phone, OtpPurpose $purpose, string $code): void
    {
        $result = DB::transaction(function () use ($phone, $purpose, $code) {
            $challenge = OtpChallenge::where('phone', $phone)
                ->where('purpose', $purpose)
                ->whereNull('consumed_at')
                ->where('expires_at', '>', now())
                ->latest()
                ->lockForUpdate()
                ->first();

            if (! $challenge || $challenge->attempts >= config('whyme.otp.max_attempts')) {
                return 'otp_expired';
            }

            $challenge->increment('attempts');

            if (! hash_equals($challenge->code_hash, $this->hash($phone, $code))) {
                return 'otp_invalid';
            }

            $challenge->forceFill(['consumed_at' => now()])->save();

            return 'ok';
        });

        if ($result !== 'ok') {
            throw ApiException::field($result, 'code');
        }
    }

    private function assertCooldownPassed(string $phone, OtpPurpose $purpose): void
    {
        $cooldown = config('whyme.otp.resend_cooldown_seconds');
        $last = OtpChallenge::where('phone', $phone)->where('purpose', $purpose)->latest()->first();

        if ($last && $last->created_at->gt(now()->subSeconds($cooldown))) {
            $wait = (int) ceil($cooldown - $last->created_at->diffInSeconds(now()));

            throw new ApiException('too_many_requests', 429, retryable: true, headers: ['Retry-After' => (string) max(1, $wait)]);
        }
    }

    private function hash(string $phone, string $code): string
    {
        return hash_hmac('sha256', $phone.'|'.$code, (string) config('app.key'));
    }
}
