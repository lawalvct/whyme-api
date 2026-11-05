<?php

namespace App\Modules\Platform\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['key', 'enabled', 'is_public', 'description'])]
class FeatureFlag extends Model
{
    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'is_public' => 'boolean',
        ];
    }
}
