<?php

namespace App\Modules\Identity\Services\Sms;

/**
 * Tests: keeps sent messages in memory so tests can read the OTP.
 */
class ArraySmsGateway implements SmsGateway
{
    /** @var list<array{to: string, message: string}> */
    public array $sent = [];

    public function send(string $toE164, string $message): void
    {
        $this->sent[] = ['to' => $toE164, 'message' => $message];
    }

    public function lastCodeFor(string $toE164): ?string
    {
        foreach (array_reverse($this->sent) as $sms) {
            if ($sms['to'] === $toE164 && preg_match('/\b(\d{6})\b/', $sms['message'], $m)) {
                return $m[1];
            }
        }

        return null;
    }
}
