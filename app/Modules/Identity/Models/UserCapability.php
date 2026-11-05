<?php

namespace App\Modules\Identity\Models;

use App\Modules\Identity\Enums\Capability;
use App\Modules\Identity\Enums\CapabilityStatus;
use App\Modules\Identity\Enums\VerificationState;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'capability', 'status', 'verification_state', 'terms_acceptance_id', 'activated_at'])]
class UserCapability extends Model
{
    use HasUlids;

    protected function casts(): array
    {
        return [
            'capability' => Capability::class,
            'status' => CapabilityStatus::class,
            'verification_state' => VerificationState::class,
            'activated_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<TermsAcceptance, $this>
     */
    public function termsAcceptance(): BelongsTo
    {
        return $this->belongsTo(TermsAcceptance::class);
    }
}
