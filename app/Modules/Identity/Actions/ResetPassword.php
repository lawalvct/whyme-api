<?php

namespace App\Modules\Identity\Actions;

use App\Modules\Identity\Enums\OtpPurpose;
use App\Modules\Identity\Models\User;
use App\Modules\Identity\Services\OtpService;
use App\Modules\Platform\Exceptions\ApiException;
use App\Modules\Platform\Services\Audit;
use Illuminate\Support\Facades\DB;

/**
 * Account recovery by SMS code. Signs out every device (ACC02, ACC03).
 */
class ResetPassword
{
    public function __construct(private readonly OtpService $otp) {}

    public function handle(string $phone, string $code, string $password): void
    {
        $this->otp->verify($phone, OtpPurpose::ResetPassword, $code);

        $user = User::where('phone', $phone)->first();
        if (! $user) {
            throw ApiException::field('otp_invalid', 'code');
        }

        DB::transaction(function () use ($user, $password) {
            $user->forceFill(['password' => $password, 'remember_token' => null])->save();
            $user->tokens()->delete();
            $user->devices()->update(['token_id' => null]);
        });

        Audit::record('user.password_reset', $user, actor: $user);
    }
}
