<?php

namespace App\Modules\Identity\Enums;

enum DevicePlatform: string
{
    case Android = 'android';
    case Ios = 'ios';
    case Web = 'web';
}
