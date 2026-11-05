<?php

namespace App\Modules\Identity\Enums;

/**
 * Admin-verified documents, separate from phone verification (TRU01).
 */
enum VerificationState: string
{
    case Unverified = 'unverified';
    case Pending = 'pending';
    case Verified = 'verified';
    case Rejected = 'rejected';
}
