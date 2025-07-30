<?php

use App\Http\Middleware\EnsureEmailIsVerified;
use App\Http\Middleware\IsAdmin;
use App\Http\Middleware\SetApiLocale;
use App\Http\Responses\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Notifications\VerifyEmail;
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
        apiPrefix: '/api',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'isAdmin' => IsAdmin::class,
            'setApiLocale'=>SetApiLocale::class,
            'verifiedEmail' => EnsureEmailIsVerified::class,
        ]);
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return ApiResponse::error('Пользовать не авторизован и не имеет доступа к данным', null, 401);
            }
            return null;
        });
    })->create();
