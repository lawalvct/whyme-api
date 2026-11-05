<?php

namespace App\Modules\Identity\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Which terms version a user accepted, in which language, and when (ACC07, I18N06).
 */
#[Fillable(['user_id', 'terms_version_id', 'locale', 'ip', 'user_agent', 'accepted_at'])]
class TermsAcceptance extends Model
{
    use HasUlids;

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'accepted_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<TermsVersion, $this>
     */
    public function termsVersion(): BelongsTo
    {
        return $this->belongsTo(TermsVersion::class);
    }
}
