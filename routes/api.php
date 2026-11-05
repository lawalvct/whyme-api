<?php

use Illuminate\Support\Facades\Route;

// Every module registers its routes under /api/v1 (docs/01 §4, §5.6).
Route::prefix('v1')->group(function () {
    require app_path('Modules/Platform/routes.php');
    require app_path('Modules/Identity/routes.php');
});
