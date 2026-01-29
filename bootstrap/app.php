<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'client' => \App\Http\Middleware\EnsureUserIsClient::class,
            'not.client' => \App\Http\Middleware\EnsureUserIsNotClient::class,
            'module.permission' => \App\Http\Middleware\CheckModulePermission::class,
            'admin.no-cache' => \App\Http\Middleware\PreventAdminCache::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
