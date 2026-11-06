<?php

use App\Modules\Identity\IdentityServiceProvider;
use App\Modules\Platform\PlatformServiceProvider;
use App\Providers\AppServiceProvider;
use App\Providers\Filament\AdminPanelProvider;

return [
    AppServiceProvider::class,
    PlatformServiceProvider::class,
    IdentityServiceProvider::class,
    AdminPanelProvider::class,
];
