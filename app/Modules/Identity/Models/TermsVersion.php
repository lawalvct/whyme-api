<?php

namespace App\Modules\Identity\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * A published version of the platform or seller terms in one language (I18N06).
 */
#[Fillable(['type', 'version', 'locale', 'title', 'body', 'published_at'])]
class TermsVersion extends Model
{
    public const TYPE_PLATFORM = 'platform';

    public const TYPE_SELLER = 'seller';

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    /**
     * @param  Builder<TermsVersion>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }
}
