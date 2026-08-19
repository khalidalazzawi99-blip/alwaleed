<?php

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
        |--------------------------------------------------------------------------
        | Middleware Aliases
        |--------------------------------------------------------------------------
        */

        $middleware->alias([

            'role' =>
                \App\Http\Middleware\RoleMiddleware::class,

            'subscription' =>
                \App\Http\Middleware\SubscriptionMiddleware::class,

            'locale' =>
                \App\Http\Middleware\SetLocale::class,

            'feature' =>
                \App\Http\Middleware\EnsureFeatureEnabled::class,
            'company.token' => \App\Http\Middleware\AuthenticateCompanyApiToken::class,

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
                \App\Http\Middleware\SetLocale::class,
            ]
        );

    })

    ->withExceptions(function (Exceptions $exceptions): void {

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) =>
                $request->is('api/*'),
        );

    })

    ->create();
