<?php

use App\Http\Middleware\AuthenticateCompanyApiToken;
use App\Http\Middleware\EnsureFeatureEnabled;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\SubscriptionMiddleware;
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

        /*
        | Render terminates TLS before forwarding requests to the container.
        | Trust the platform proxy and its standard forwarded headers so Laravel
        | preserves the original HTTPS scheme when generating URLs.
        */
        $middleware->trustProxies(
            at: '*',
            headers: Request::HEADER_X_FORWARDED_FOR
                | Request::HEADER_X_FORWARDED_HOST
                | Request::HEADER_X_FORWARDED_PORT
                | Request::HEADER_X_FORWARDED_PROTO
                | Request::HEADER_X_FORWARDED_PREFIX,
        );

        /*
        |--------------------------------------------------------------------------
        | Middleware Aliases
        |--------------------------------------------------------------------------
        */

        $middleware->alias([

            'role' => RoleMiddleware::class,

            'subscription' => SubscriptionMiddleware::class,

            'locale' => SetLocale::class,

            'feature' => EnsureFeatureEnabled::class,
            'company.token' => AuthenticateCompanyApiToken::class,

        ]);

        /*
        |--------------------------------------------------------------------------
        | اللغة
        |--------------------------------------------------------------------------
        |
        | هذا يخلي SetLocale يشتغل تلقائياً
        | على جميع صفحات web.
        |
        */

        $middleware->web(
            append: [
                SetLocale::class,
            ]
        );

    })

    ->withExceptions(function (Exceptions $exceptions): void {

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

    })

    ->create();
