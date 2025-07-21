<?php

namespace App\Exceptions;

use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontReport = [];

    protected $dontFlash = ['current_password', 'password', 'password_confirmation'];

    public function render($request, Throwable $exception)
    {
        if ($exception instanceof ThrottleRequestsException) {
        return response()->json([
                'success' => false,
                'message' => 'Too many attempts. Please try again later.',
            ], 429);
        }
        return parent::render($request, $exception);
    }
    public function register(): void
    {
        //
    }
}
