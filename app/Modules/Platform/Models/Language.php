<?php

namespace App\Modules\Platform\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * Adding a language (e.g. Pidgin) is a row here plus translation files (I18N08).
 */
#[Fillable(['code', 'name', 'native_name', 'enabled', 'is_default', 'sort'])]
class Language extends Model
{
    protected $primaryKey = 'code';

    protected $keyType = 'string';

    public $incrementing = false;

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'is_default' => 'boolean',
        ];
    }
}
