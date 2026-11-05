<?php

namespace App\Modules\Platform\Http\Controllers;

use App\Modules\Platform\Models\Language;
use App\Modules\Platform\Services\Features;
use App\Modules\Platform\Services\Languages;
use App\Modules\Platform\Services\Settings;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\App;

/**
 * Everything the apps need before (and without) signing in, cached on the
 * device so it works offline: languages, minimum versions, public flags and settings.
 */
class ConfigController
{
    public function __invoke(Languages $languages, Settings $settings, Features $features): JsonResponse
    {
        return response()->json(['data' => [
            'locale' => App::getLocale(),
            'default_language' => $languages->default(),
            'languages' => $languages->enabled()->map(fn (Language $l) => [
                'code' => $l->code,
                'name' => $l->name,
                'native_name' => $l->native_name,
            ])->values(),
            'min_supported_version' => [
                'android' => $settings->get('app.min_version.android', config('whyme.app_versions.android')),
                'ios' => $settings->get('app.min_version.ios', config('whyme.app_versions.ios')),
            ],
            'features' => (object) $features->public(),
            'settings' => (object) $settings->public(),
            'server_time' => now()->toIso8601String(),
        ]]);
    }
}
