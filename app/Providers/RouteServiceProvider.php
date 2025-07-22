<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot()
    {
        RateLimiter::for('api-progressive', function (Request $request) {
            return Limit::perMinute(60)
                ->by($request->ip())
                ->response(function () {
                    $retryAfter = 60; // seconds
                    return response()->json([
                        'message' => 'Too many requests. Try again later.'
                    ], 429)->header('Retry-After', $retryAfter);
                });
        });


        // parent::boot(); // keep this if you're extending the base class
    }

}
