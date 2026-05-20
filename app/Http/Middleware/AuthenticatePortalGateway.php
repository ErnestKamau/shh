<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticatePortalGateway
{
    public function handle(Request $request, Closure $next): Response
    {
        $configuredKey = (string) config('services.portal_gateway.api_key', '');

        if ($configuredKey === '') {
            return response()->json([
                'message' => 'Portal gateway API is not configured.',
            ], 503);
        }

        $providedKey = $request->bearerToken()
            ?? $request->header('X-Portal-Gateway-Key')
            ?? $request->header('X-Api-Key');

        if (! is_string($providedKey) || ! hash_equals($configuredKey, $providedKey)) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        return $next($request);
    }
}
