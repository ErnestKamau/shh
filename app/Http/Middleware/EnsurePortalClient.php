<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Ensures that only CRM customer contacts (is_client = 1) can access portal
 * endpoints.  Internal staff tokens are rejected with a 403.
 */
class EnsurePortalClient
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user || (int) $user->is_client !== 1) {
            return response()->json([
                'message' => 'Access restricted to customer portal users.',
            ], 403);
        }

        return $next($request);
    }
}
