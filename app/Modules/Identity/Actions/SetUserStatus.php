<?php

namespace App\Modules\Identity\Actions;

use App\Modules\Identity\Enums\UserStatus;
use App\Modules\Identity\Models\Admin;
use App\Modules\Identity\Models\User;
use App\Modules\Platform\Services\Audit;
use Illuminate\Support\Facades\DB;

/**
 * Staff suspend or reactivate an account (ADM01). Suspension signs the user out
 * everywhere; queued offline work is refused when it reconnects (ACC05).
 */
class SetUserStatus
{
    public function handle(User $user, UserStatus $status, Admin $admin, ?string $reason = null): void
    {
        $old = $user->status;
        if ($old === $status) {
            return;
        }

        DB::transaction(function () use ($user, $status) {
            $user->forceFill(['status' => $status])->save();

            if ($status === UserStatus::Suspended) {
                $user->tokens()->delete();
                $user->devices()->update(['token_id' => null]);
            }
        });

        Audit::record(
            $status === UserStatus::Suspended ? 'user.suspended' : 'user.reactivated',
            $user,
            ['status' => $old->value],
            ['status' => $status->value, 'reason' => $reason],
            $admin,
        );
    }
}
