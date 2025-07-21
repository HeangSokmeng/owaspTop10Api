<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/login', [UserController::class, 'login']);
    // Route::prefix('login')->group(function () { });
});

Route::get('/user', function (Request $request) {

});

// Route::middleware(['auth:api', 'role:superadmin'])->group(function () {
    Route::prefix('product')->group(function () {
        Route::post('', [ProductController::class, 'store']);
        Route::get('', [ProductController::class, 'index']);
    });
// });
Route::prefix('profile')->group(function () {
    // Route::post('', [ProductController::class, 'store']);
    Route::get('/{id}', [UserController::class, 'getProfile']);
});
