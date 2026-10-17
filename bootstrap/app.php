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
        $middleware->api(prepend: \Illuminate\Http\Middleware\HandleCors::class);
        $middleware->redirectGuestsTo(fn ($request) => $request->expectsJson() ? null : '/login');
        $middleware->alias([
            'jwt.auth' => \App\Modules\Admin\Middleware\AuthenticateJwt::class,
            'role.admin' => \App\Modules\Admin\Middleware\EnsureAdminRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
