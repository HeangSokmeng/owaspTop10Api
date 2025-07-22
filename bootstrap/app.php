<?php

use App\Http\Middleware\BlockMaliciousIP;
use App\Http\Middleware\isLoggin;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use App\Http\Middleware\awtAuth;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'jwtAuth' => awtAuth::class,
            'isLoggin' => isLoggin::class,
            'block-malicious-ips' => BlockMaliciousIP::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Illuminate\Http\Exceptions\ThrottleRequestsException $e, $request) {
            return response()->json([
                'success' => false,
                'message' => 'Daily request limit reached. Try again tomorrow.',
            ], 429);
        });
    })
    ->create();
