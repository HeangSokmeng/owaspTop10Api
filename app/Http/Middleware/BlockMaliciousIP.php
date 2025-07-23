<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class BlockMaliciousIP
{
    public function handle(Request $request, Closure $next)
    {
        $ip = $request->ip();

        // DEBUG: Always log this to see if middleware is running
        Log::info("🔥 MIDDLEWARE IS RUNNING! IP: {$ip}");

        if ($request->isMethod('post') && str_contains($request->path(), 'login')) {

            Log::info("🔥 LOGIN REQUEST DETECTED! IP: {$ip}");

            // Daily rate limiting key (resets every day)
            $today = now()->format('Y-m-d');
            $dailyKey = "daily_login_attempts:{$ip}:{$today}";

            // Short term rate limiting key (5 minutes)
            $shortTermKey = "login_attempts:{$ip}";

            // Check if IP is permanently blocked for today
            $dailyBlockKey = "daily_blocked_ip:{$ip}:{$today}";
            if (Cache::has($dailyBlockKey)) {
                Log::warning("🚫 DAILY BLOCKED IP {$ip} tried to access");

                return response()->json([
                    'message' => 'You have exceeded the daily login attempt limit. Try again tomorrow.',
                    'blocked_until' => 'tomorrow'
                ], 429)->header('Retry-After', 86400); // 24 hours
            }

            // Check if IP is temporarily blocked (short term)
            $tempBlockKey = "temp_blocked_ip:{$ip}";
            if (Cache::has($tempBlockKey)) {
                $blockedUntil = Cache::get($tempBlockKey);
                Log::warning("🚫 TEMP BLOCKED IP {$ip} tried to access. Blocked until: {$blockedUntil}");

                return response()->json([
                    'message' => 'Too many rapid attempts. You are temporarily blocked.',
                    'blocked_until' => $blockedUntil
                ], 429)->header('Retry-After', 300); // 5 minutes
            }

            // Increment daily attempts
            $dailyAttempts = Cache::increment($dailyKey, 1);
            if ($dailyAttempts === 1) {
                // Set expiration for 24 hours on first increment
                Cache::put($dailyKey, 1, 86400); // 24 hours in seconds
            }

            // Increment short-term attempts
            RateLimiter::hit($shortTermKey, 300); // 5 minutes decay
            $shortTermAttempts = RateLimiter::attempts($shortTermKey);

            Log::info("🔢 Daily attempts for IP {$ip}: {$dailyAttempts}");
            Log::info("🔢 Short-term attempts for IP {$ip}: {$shortTermAttempts}");

            // Block for the entire day if more than 6 attempts in 24 hours
            if ($dailyAttempts > 6) {
                Cache::put($dailyBlockKey, true, 86400); // Block for 24 hours

                Log::warning("🚫 IP {$ip} has been BLOCKED for 24 hours due to {$dailyAttempts} daily attempts");

                return response()->json([
                    'message' => 'You have exceeded the daily login attempt limit (6 attempts). Try again tomorrow.',
                    'daily_attempts' => $dailyAttempts,
                    'blocked_until' => 'tomorrow'
                ], 429)->header('Retry-After', 86400);
            }

            // Temporary block for rapid attempts (more than 3 in 5 minutes)
            if ($shortTermAttempts > 3) {
                $blockUntil = now()->addMinutes(5)->toDateTimeString();
                Cache::put($tempBlockKey, $blockUntil, 300); // 5 minutes

                Log::warning("🚫 IP {$ip} temporarily blocked for 5 minutes due to {$shortTermAttempts} rapid attempts");

                return response()->json([
                    'message' => 'Too many rapid login attempts. You are blocked for 5 minutes.',
                    'blocked_until' => $blockUntil
                ], 429)->header('Retry-After', 300);
            }
        }

        return $next($request);
    }
}
