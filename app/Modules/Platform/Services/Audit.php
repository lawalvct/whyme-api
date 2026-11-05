<?php

namespace App\Modules\Platform\Services;

use App\Modules\Identity\Models\Admin;
use App\Modules\Platform\Models\AuditLog;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

/**
 * Records who did what, with old and new values (ADM02, OPS04).
 */
class Audit
{
    /**
     * @param  array<string, mixed>|null  $old
     * @param  array<string, mixed>|null  $new
     */
    public static function record(
        string $action,
        ?Model $subject = null,
        ?array $old = null,
        ?array $new = null,
        ?Authenticatable $actor = null,
    ): AuditLog {
        $request = request();
        $actor ??= $request->user('admin') ?? $request->user('sanctum');

        return AuditLog::create([
            'actor_type' => match (true) {
                $actor instanceof Admin => 'admin',
                $actor !== null => 'user',
                default => 'system',
            },
            'actor_id' => $actor?->getAuthIdentifier(),
            'action' => $action,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'old_values' => $old,
            'new_values' => $new,
            'ip' => $request->ip(),
            'request_id' => $request->attributes->get('request_id'),
            'created_at' => now(),
        ]);
    }
}
