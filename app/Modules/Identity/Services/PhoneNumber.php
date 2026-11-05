<?php

namespace App\Modules\Identity\Services;

use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberType;
use libphonenumber\PhoneNumberUtil;

final class PhoneNumber
{
    /**
     * Normalises a Nigerian mobile number to E.164 (+234…), or null if it isn't one.
     * Accepts 0803…, 803…, 234803…, +234 803 … with spaces or dashes.
     */
    public static function normalise(?string $input): ?string
    {
        if ($input === null || trim($input) === '') {
            return null;
        }

        $util = PhoneNumberUtil::getInstance();

        try {
            $number = $util->parse($input, 'NG');
        } catch (NumberParseException) {
            return null;
        }

        if (! $util->isValidNumberForRegion($number, 'NG')) {
            return null;
        }

        $type = $util->getNumberType($number);
        if ($type !== PhoneNumberType::MOBILE && $type !== PhoneNumberType::FIXED_LINE_OR_MOBILE) {
            return null;
        }

        return $util->format($number, PhoneNumberFormat::E164);
    }
}
