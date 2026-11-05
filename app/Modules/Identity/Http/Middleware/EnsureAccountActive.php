<?php

namespace App\Modules\Identity\Http\Middleware;

use App\Modules\Identity\Models\User;
use App\Modules\Platform\Exceptions\ApiException;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * A suspended account can't do anything new, including actions queued offline
 * before the suspension that arrive afterwards (ACC05).
 */
class EnsureAccountActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user instanceof User && $user->isSuspended()) {
            throw new ApiException('account_suspended', 403);
        }

        return $next($request);
    }
}
