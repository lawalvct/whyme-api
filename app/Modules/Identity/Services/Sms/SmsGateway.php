<?php

namespace App\Modules\Identity\Services\Sms;

/**
 * SMS provider seam, so Termii can be swapped or backed up without touching
 * business code (docs/01 §1).
 */
interface SmsGateway
{
    public function send(string $toE164, string $message): void;
}
