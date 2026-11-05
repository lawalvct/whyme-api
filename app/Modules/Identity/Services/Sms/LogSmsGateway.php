<?php

namespace App\Modules\Identity\Services\Sms;

use Illuminate\Support\Facades\Log;

/**
 * Local development: writes the SMS (including OTP codes) to the log instead of sending it.
 * Never use in production.
 */
class LogSmsGateway implements SmsGateway
{
    public function send(string $toE164, string $message): void
    {
        Log::info('SMS (not sent)', ['to' => $toE164, 'message' => $message]);
    }
}
