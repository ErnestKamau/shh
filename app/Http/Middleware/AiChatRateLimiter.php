<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AiChatRateLimiter
{
    public function __construct(private readonly RateLimiter $limiter) {}

    /**
     * Allow at most $maxAttempts requests per $decayMinutes per authenticated user (by ID).
     * Falls back to IP address for unauthenticated requests.
     */
    public function handle(Request $request, Closure $next, int $maxAttempts = 20, int $decayMinutes = 1): Response
    {
        $key = 'ai-chat:' . ($request->user()?->id ?? $request->ip());

        if ($this->limiter->tooManyAttempts($key, $maxAttempts)) {
            $retryAfter = $this->limiter->availableIn($key);

            return response()->json([
                'status'  => 'error',
                'message' => 'Too many requests. Please wait before sending another message.',
            ], 429)->header('Retry-After', $retryAfter);
        }

        $this->limiter->hit($key, $decayMinutes * 60);

        return $next($request);
    }
}
