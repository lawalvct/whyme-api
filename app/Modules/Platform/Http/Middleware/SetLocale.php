<?php

namespace App\Modules\Platform\Http\Middleware;

use App\Modules\Platform\Services\Languages;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * Clients always send the language the user chose (I18N02). Falls back to the
 * signed-in user's saved language, then the default (English, I18N01).
 */
class SetLocale
{
    public function __construct(private readonly Languages $languages) {}

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->fromHeader($request->header('Accept-Language'))
            ?? $this->enabledOrNull($request->user('sanctum')?->locale)
            ?? $this->languages->default();

        App::setLocale($locale);

        $response = $next($request);
        $response->headers->set('Content-Language', $locale);

        return $response;
    }

    private function fromHeader(?string $header): ?string
    {
        if (! $header) {
            return null;
        }

        // "ig, en;q=0.8" → ["ig", "en"]; region suffixes ("en-NG") reduce to the language.
        foreach (explode(',', $header) as $part) {
            $code = strtolower(trim(explode('-', explode(';', $part)[0])[0]));
            if ($this->languages->isEnabled($code)) {
                return $code;
            }
        }

        return null;
    }

    private function enabledOrNull(?string $code): ?string
    {
        return $this->languages->isEnabled($code) ? $code : null;
    }
}
