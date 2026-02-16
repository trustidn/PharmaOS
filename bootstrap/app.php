<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Additional route files are required from routes/web.php
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\SetTenantContext::class,
        ]);

        $middleware->alias([
            'tenant.user' => \App\Http\Middleware\EnsureTenantUser::class,
            'super.admin' => \App\Http\Middleware\EnsureSuperAdmin::class,
            'owner' => \App\Http\Middleware\EnsureOwner::class,
            'subscription.active' => \App\Http\Middleware\EnsureSubscriptionActive::class,
            'check.feature' => \App\Http\Middleware\CheckFeatureLimit::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
