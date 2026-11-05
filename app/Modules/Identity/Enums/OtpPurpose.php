<?php

namespace App\Modules\Identity\Enums;

enum OtpPurpose: string
{
    case Register = 'register';
    case ResetPassword = 'reset_password';
    case NewDevice = 'new_device';
}
