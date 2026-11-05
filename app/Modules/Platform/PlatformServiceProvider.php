<?php

namespace App\Modules\Platform;

use App\Modules\Platform\Services\Features;
use App\Modules\Platform\Services\Languages;
use App\Modules\Platform\Services\Settings;
use Illuminate\Support\ServiceProvider;

class PlatformServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Settings::class);
        $this->app->singleton(Features::class);
        $this->app->singleton(Languages::class);
    }
}
