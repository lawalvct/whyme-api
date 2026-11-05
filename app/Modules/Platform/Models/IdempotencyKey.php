<?php

namespace App\Modules\Platform\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;

#[Fillable(['owner', 'key', 'method', 'path', 'request_hash', 'status_code', 'response_body', 'created_at'])]
class IdempotencyKey extends Model
{
    use MassPrunable;

    public $timestamps = false;

    /**
     * Removed by the scheduled `model:prune` once clients can no longer be retrying.
     */
    public function prunable(): Builder
    {
        return static::where('created_at', '<', now()->subDays(config('whyme.idempotency.retention_days')));
    }

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            // Bodies can contain personal data, so they are encrypted at rest.
            'response_body' => 'encrypted',
        ];
    }

    public function toResponse(): Response
    {
        return new Response($this->response_body, $this->status_code, [
            'Content-Type' => 'application/json',
            'Idempotent-Replayed' => 'true',
        ]);
    }
}
