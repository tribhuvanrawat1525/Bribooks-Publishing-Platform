<?php

use App\Http\Middleware\ApiLogMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Http\Middleware\Authenticate;
use App\Http\Middleware\RoleMiddleware;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'jwt.auth' => Authenticate::class,
            'role' => RoleMiddleware::class,
            'api.log' => ApiLogMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {

        $exceptions->render(function (
            TokenExpiredException $e,
            $request
        ) {

            return response()->json([
                'code' => 401,
                'message' => 'Token has expired'
            ], 401);

        });

        $exceptions->render(function (
            TokenInvalidException $e,
            $request
        ) {

            return response()->json([
                'code' => 401,
                'message' => 'Invalid token'
            ], 401);

        });

        $exceptions->render(function (
            UnauthorizedHttpException $e,
            $request
        ) {

            return response()->json([
                'code' => 401,
                'message' => 'Authorization token is required'
            ], 401);

        });

    })->create();
