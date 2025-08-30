<?php

use App\Http\Middleware\CheckChatIsDeleteMiddleware;
use App\Http\Middleware\CheckExistRoomMiddleware;
use App\Http\Middleware\EnsureEmailIsVerifiedMiddleware;
use App\Http\Middleware\IsAdminMiddleware;
use App\Http\Middleware\SetApiLocaleMiddleware;
use App\Http\Responses\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
        apiPrefix: '/api',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'isAdmin' => IsAdminMiddleware::class,
            'setApiLocale'=>SetApiLocaleMiddleware::class,
            'verifiedEmail' => EnsureEmailIsVerifiedMiddleware::class,
            'checkExistRoom'=> CheckExistRoomMiddleware::class,
            'checkChatIsDelete'=>CheckChatIsDeleteMiddleware::class,
        ]);
        $middleware->trustProxies(at: '*');
        $middleware->appendToGroup('api', HandleCors::class);
        //$middleware->append(HandleCors::class);

    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return ApiResponse::error('Пользовать не авторизован и не имеет доступа к данным', null, 401);
            }
            return null;
        });
    })->create();
