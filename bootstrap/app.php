<?php

use App\Modules\Platform\Exceptions\ApiErrorRenderer;
use App\Modules\Platform\Http\Middleware\AssignRequestId;
use App\Modules\Platform\Http\Middleware\CheckAppVersion;
use App\Modules\Platform\Http\Middleware\EnsureIdempotency;
use App\Modules\Platform\Http\Middleware\SetLocale;
use Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // The website signs in with Sanctum's cookie session; mobile uses bearer tokens.
        $middleware->statefulApi();

        $middleware->api(
            prepend: [AssignRequestId::class],
            append: [SetLocale::class, CheckAppVersion::class, EnsureIdempotency::class],
        );

        // Laravel runs auth middleware early; the language and app-version checks
        // must run before it so a 401 is translated and an old app gets 426 first.
        $middleware->prependToPriorityList(AuthenticatesRequests::class, SetLocale::class);
        $middleware->prependToPriorityList(AuthenticatesRequests::class, CheckAppVersion::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // One error shape for every API failure (docs/01 §5.6).
        $exceptions->render(fn (Throwable $e, Request $request) => ApiErrorRenderer::render($e, $request));
    })->create();
