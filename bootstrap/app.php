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
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
            'member' => \App\Http\Middleware\EnsureUserIsMember::class,
            'member.active' => \App\Http\Middleware\EnsureMemberIsActive::class,
            'check.reservation.limit' => \App\Http\Middleware\CheckReservationLimit::class,
            'check.unpaid.fines' => \App\Http\Middleware\CheckUnpaidFines::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
