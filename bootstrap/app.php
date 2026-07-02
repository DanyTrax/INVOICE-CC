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
        $middleware->web(append: [
            \App\Http\Middleware\SecurityHeaders::class,
        ]);
        $middleware->alias([
            'two_factor.pending' => \App\Http\Middleware\EnsureTwoFactorLoginPending::class,
            'client' => \App\Http\Middleware\EnsureUserIsClient::class,
            'client.portal.access' => \App\Http\Middleware\EnsureClientCanAccessPortal::class,
            'not.client' => \App\Http\Middleware\EnsureUserIsNotClient::class,
            'module.permission' => \App\Http\Middleware\CheckModulePermission::class,
            'admin.no-cache' => \App\Http\Middleware\PreventAdminCache::class,
            'log.admin.mutations' => \App\Http\Middleware\LogAdminMutationActivity::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->report(function (\Throwable $e): void {
            app(\App\Services\AppErrorLogService::class)->record($e);
        });
    })->create();
