<?php

namespace App\Modules\Platform\Http\Middleware;

use App\Modules\Platform\Exceptions\ApiException;
use App\Modules\Platform\Exceptions\RollbackWithResponse;
use App\Modules\Platform\Models\IdempotencyKey;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * Exactly-once writes for offline clients (OFF05, PAY06, AT08).
 *
 * A request with an Idempotency-Key runs inside one DB transaction, and its
 * successful response is stored in that same transaction. A retry with the same
 * key gets the stored response back instead of running again; the same key with
 * a different body is refused. Failed requests aren't stored, so the client can
 * fix the draft and resend it with the same key.
 */
class EnsureIdempotency
{
    private const METHODS = ['POST', 'PUT', 'PATCH', 'DELETE'];

    public function handle(Request $request, Closure $next): Response
    {
        $key = $request->header('Idempotency-Key');
        if (! is_string($key) || ! in_array($request->method(), self::METHODS, true)) {
            return $next($request);
        }
        if (! preg_match('/^[A-Za-z0-9-]{16,64}$/', $key)) {
            throw new ApiException('idempotency_key_invalid', 400);
        }

        $owner = (string) ($request->user('sanctum')?->getAuthIdentifier() ?? 'guest');
        $hash = hash('sha256', $request->method().' '.$request->path().' '.$request->getContent());

        $lock = Cache::lock("idempotency:$owner:$key", config('whyme.idempotency.lock_seconds'));
        if (! $lock->get()) {
            throw new ApiException('request_in_progress', 425, retryable: true);
        }

        try {
            $stored = IdempotencyKey::where('owner', $owner)->where('key', $key)->first();
            if ($stored) {
                if (! hash_equals($stored->request_hash, $hash)) {
                    throw new ApiException('idempotency_mismatch', 409);
                }

                return $stored->toResponse();
            }

            return $this->runOnce($request, $next, $owner, $key, $hash);
        } finally {
            $lock->release();
        }
    }

    private function runOnce(Request $request, Closure $next, string $owner, string $key, string $hash): Response
    {
        try {
            return DB::transaction(function () use ($request, $next, $owner, $key, $hash) {
                $response = $next($request);

                // A 5xx means the handler failed part-way: roll its writes back.
                if ($response->getStatusCode() >= 500) {
                    throw new RollbackWithResponse($response);
                }

                if ($response->isSuccessful()) {
                    IdempotencyKey::create([
                        'owner' => $owner,
                        'key' => $key,
                        'method' => $request->method(),
                        'path' => $request->path(),
                        'request_hash' => $hash,
                        'status_code' => $response->getStatusCode(),
                        'response_body' => (string) $response->getContent(),
                        'created_at' => now(),
                    ]);
                }

                return $response;
            });
        } catch (RollbackWithResponse $rollback) {
            return $rollback->response;
        }
    }
}
