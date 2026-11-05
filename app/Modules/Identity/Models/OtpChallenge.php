<?php

namespace App\Modules\Identity\Models;

use App\Modules\Identity\Enums\OtpPurpose;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;

/**
 * A one-time code sent by SMS. Only an HMAC of the code is stored.
 */
#[Fillable(['phone', 'purpose', 'code_hash', 'attempts', 'expires_at', 'consumed_at', 'ip'])]
class OtpChallenge extends Model
{
    use HasUlids, MassPrunable;

    protected function casts(): array
    {
        return [
            'purpose' => OtpPurpose::class,
            'expires_at' => 'datetime',
            'consumed_at' => 'datetime',
        ];
    }

    public function prunable(): Builder
    {
        return static::where('created_at', '<', now()->subDays(30));
    }
}
