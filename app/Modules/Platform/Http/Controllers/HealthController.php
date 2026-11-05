<?php

namespace App\Modules\Platform\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * The outbox calls this before sending: a network connection isn't proof the
 * backend is reachable (OFF03). Kept cheap: one DB round trip.
 */
class HealthController
{
    public function __invoke(): JsonResponse
    {
        try {
            DB::select('select 1');
        } catch (Throwable) {
            return response()->json(['status' => 'degraded', 'time' => now()->toIso8601String()], 503);
        }

        return response()->json(['status' => 'ok', 'time' => now()->toIso8601String()]);
    }
}
