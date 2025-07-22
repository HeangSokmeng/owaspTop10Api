<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\BlockedIP; // Create this model/table

class BlockMaliciousIP
{
    public function handle(Request $request, Closure $next): Response
    {
        if (BlockedIP::where('ip', $request->ip())->exists()) {
            abort(403, 'Forbidden: Your IP has been blocked.');
        }

        return $next($request);
    }
}
