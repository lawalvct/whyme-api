<?php

namespace App\Modules\Platform\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * Append-only. Never update or delete rows; correct mistakes with a new entry.
 */
#[Fillable(['actor_type', 'actor_id', 'action', 'subject_type', 'subject_id', 'old_values', 'new_values', 'ip', 'request_id', 'created_at'])]
class AuditLog extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'created_at' => 'datetime',
        ];
    }
}
