<?php

namespace App\Modules\Identity\Models;

use App\Modules\Identity\Enums\DevicePlatform;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A phone, tablet or browser the user has signed in on. A sign-in from an
 * unknown device needs an OTP (docs/01 §11). One API token per device.
 */
#[Fillable(['user_id', 'device_id', 'platform', 'name', 'app_version', 'push_token', 'token_id', 'last_seen_at'])]
class UserDevice extends Model
{
    use HasUlids;

    protected function casts(): array
    {
        return [
            'platform' => DevicePlatform::class,
            'last_seen_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
