<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    // Route::post('/login', [UserController::class, 'loginInsecure']);
    Route::post('/login', [UserController::class, 'loginSecure']);
    // Route::middleware('throttle:daily-limit')->post('/login', [UserController::class, 'loginSecure']);
    Route::post('/register/insecure', [UserController::class, 'registerInsecure']);
    Route::post('/register/secure', [UserController::class, 'registerecure']);
    // Route::prefix('login')->group(function () { });
});

Route::get('/user', function (Request $request) {

});

// Route::middleware(['auth:api', 'role:superadmin'])->group(function () {

// });
Route::middleware('isLoggin')->group(function () {
    Route::prefix('product')->group(function () {
        Route::post('', [ProductController::class, 'store']);
        Route::get('', [ProductController::class, 'index']);
    });
    Route::prefix('profile')->group(function () {  // Route::post('', [ProductController::class, 'store']);
        Route::get('/secure/{id}', [UserController::class, 'getProfileSecureID']);
        Route::get('/secure', [UserController::class, 'getProfileSecure']);
        Route::get('/{id}', [UserController::class, 'getProfile']);
    });

});

Route::middleware(['throttle:api-progressive', 'block-malicious-ips'])->prefix('view')->group(function () {
    Route::prefix('product')->group(function () {
        Route::post('', [ProductController::class, 'store']);
        Route::get('', [ProductController::class, 'index']);
    });
});
