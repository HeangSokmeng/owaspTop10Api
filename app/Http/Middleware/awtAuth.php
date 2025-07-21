<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;

class awtAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (\Exception $e) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $user->loadMissing('roles');
        $userRoles = $user->roles->pluck('name')->toArray();
        if (!array_intersect($roles, $userRoles)) {
            return response()->json(['message' => 'Forbidden: Insufficient role'], 403);
        }
        return $next($request);
    }
}
