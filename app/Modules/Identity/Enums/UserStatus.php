<?php

namespace App\Modules\Identity\Enums;

/**
 * Suspension is separate from deletion (ACC05); deletion uses soft deletes.
 */
enum UserStatus: string
{
    case Active = 'active';
    case Suspended = 'suspended';
}
