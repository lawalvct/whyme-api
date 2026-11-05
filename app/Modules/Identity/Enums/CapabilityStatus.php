<?php

namespace App\Modules\Identity\Enums;

enum CapabilityStatus: string
{
    case Active = 'active';
    case Suspended = 'suspended';
}
