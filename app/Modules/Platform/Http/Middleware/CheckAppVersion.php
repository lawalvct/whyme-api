<?php

namespace App\Modules\Platform\Http\Middleware;

use App\Modules\Platform\Exceptions\ApiException;
use App\Modules\Platform\Services\Settings;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks mobile builds older than the minimum the owner sets, so the app shows
 * "update required" while keeping its queued work (docs/01 §5.6). /health and
 * /config stay open so an old app can still learn what it must do.
 */
class CheckAppVersion
{
    public function __construct(private readonly Settings $settings) {}

    public function handle(Request $request, Closure $next): Response
    {
        $platform = strtolower((string) $request->header('X-Platform'));
        $version = $request->header('X-App-Version');

        if (in_array($platform, ['android', 'ios'], true) && is_string($version) && ! $request->routeIs('platform.*')) {
            $minimum = (string) $this->settings->get("app.min_version.$platform", config("whyme.app_versions.$platform"));

            if (version_compare($version, $minimum, '<')) {
                throw new ApiException('app_update_required', 426, data: ['min_version' => $minimum]);
            }
        }

        return $next($request);
    }
}
