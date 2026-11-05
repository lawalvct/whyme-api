<?php

namespace App\Modules\Identity\Enums;

/**
 * Selling, service and employer features are added to the one account (ACC01, ACC07).
 * Buying, requesting services and applying need no capability.
 */
enum Capability: string
{
    case Vendor = 'vendor';
    case Provider = 'provider';
    case Employer = 'employer';
    case JobSeeker = 'job_seeker';

    /**
     * Seller-side capabilities need the current seller terms accepted first (ACC07, SAF09).
     */
    public function requiresSellerTerms(): bool
    {
        return $this !== self::JobSeeker;
    }
}
