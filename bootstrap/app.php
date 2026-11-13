<?php

use App\Http\Middleware\CheckAdminAccess;
use App\Http\Middleware\NoStore;
use App\Http\Middleware\RefreshSession;
use App\Http\Middleware\SessionGuard;
use App\Http\Middleware\TrackVisitors;
use App\Http\Middleware\VerifyTwoFactorAuth;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Vercel terminates TLS in front of the function; trust its
        // forwarded IP/proto headers so $request->ip() and secure URLs work.
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'two-factor' => VerifyTwoFactorAuth::class,
            'track.visitors' => TrackVisitors::class,
            'no.store' => NoStore::class,
            'admin.access' => CheckAdminAccess::class,
            'session.refresh' => RefreshSession::class,
            'session.guard' => SessionGuard::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
