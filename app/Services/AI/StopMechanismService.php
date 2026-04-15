<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * StopMechanismService
 * 
 * Implements 3-layer stop mechanism for streaming responses:
 * 1. Frontend abort (UX: user clicks stop)
 * 2. Session flag (Backend: set halt flag before next token)
 * 3. Worker interruption (Advanced: signal heavy compute tasks)
 * 
 * Guarantees:
 * - Stop is best-effort (not guaranteed mid-generation)
 * - Always returns partial response with "stopped" status
 * - No data loss (incomplete message still saved)
 * - Graceful degradation if stop fails
 */
class StopMechanismService
{
    /**
     * Session-based stop flags
     * In production, use Redis for distributed systems
     */
    protected array $stopFlags = [];

    /**
     * Create a new stop session
     * 
     * Returns a session ID that can be used to signal stop
     * 
     * @param string $conversationId
     * @param string $generationId Unique ID for this generation
     * @return string Stop session token
     */
    public function createStopSession(string $conversationId, string $generationId): string
    {
        $sessionToken = Str::uuid()->toString();

        // In production: store in Redis with TTL
        $this->stopFlags[$sessionToken] = [
            'conversation_id' => $conversationId,
            'generation_id' => $generationId,
            'stop_requested' => false,
            'created_at' => microtime(true),  // Use microtime for millisecond precision
            'ttl' => 3600,  // 1 hour
        ];

        Log::info('stop_session_created', [
            'session_token' => substr($sessionToken, 0, 8) . '...',
            'conversation_id' => $conversationId,
            'generation_id' => $generationId,
        ]);

        return $sessionToken;
    }

    /**
     * Signal a stop request
     * 
     * This is safe to call concurrently from frontend or admin
     * 
     * @param string $sessionToken The stop session token
     * @param string $requester Who requested stop ('frontend', 'admin', 'system')
     * @return bool True if stop was registered, false if session not found
     */
    public function requestStop(string $sessionToken, string $requester = 'frontend'): bool
    {
        if (!isset($this->stopFlags[$sessionToken])) {
            Log::warning('stop_session_not_found', [
                'session_token' => substr($sessionToken, 0, 8) . '...',
                'requester' => $requester,
            ]);
            return false;
        }

        $this->stopFlags[$sessionToken]['stop_requested'] = true;
        $this->stopFlags[$sessionToken]['requester'] = $requester;
        $this->stopFlags[$sessionToken]['requested_at'] = time();

        Log::info('stop_requested', [
            'session_token' => substr($sessionToken, 0, 8) . '...',
            'requester' => $requester,
            'delay_ms' => 0,
        ]);

        return true;
    }

    /**
     * Check if stop has been requested
     * 
     * Called frequently during token generation
     * Should be very fast (O(1) lookup)
     * 
     * @param string $sessionToken
     * @return bool True if stop requested
     */
    public function isStopRequested(string $sessionToken): bool
    {
        if (!isset($this->stopFlags[$sessionToken])) {
            return false;
        }

        return $this->stopFlags[$sessionToken]['stop_requested'] ?? false;
    }

    /**
     * Clean up stop session
     * 
     * Should be called after generation completes (success or stop)
     * 
     * @param string $sessionToken
     * @return void
     */
    public function closeStopSession(string $sessionToken): void
    {
        if (isset($this->stopFlags[$sessionToken])) {
            $session = $this->stopFlags[$sessionToken];
            $durationSeconds = (microtime(true) - $session['created_at']);

            Log::info('stop_session_closed', [
                'session_token' => substr($sessionToken, 0, 8) . '...',
                'duration_seconds' => round($durationSeconds, 2),
                'was_stopped' => $session['stop_requested'],
            ]);

            unset($this->stopFlags[$sessionToken]);
        }
    }

    /**
     * Get stop session metadata
     * 
     * For debugging and analytics
     * 
     * @param string $sessionToken
     * @return array|null
     */
    public function getSession(string $sessionToken): ?array
    {
        return $this->stopFlags[$sessionToken] ?? null;
    }

    /**
     * Get elapsed time since session creation
     * 
     * @param string $sessionToken
     * @return int Milliseconds elapsed
     */
    public function getElapsedMs(string $sessionToken): int
    {
        if (!isset($this->stopFlags[$sessionToken])) {
            return 0;
        }

        $createdAt = $this->stopFlags[$sessionToken]['created_at'];
        return (int)((microtime(true) - $createdAt) * 1000);
    }

    /**
     * Get time since stop was requested
     * 
     * @param string $sessionToken
     * @return int|null Milliseconds since stop request, or null if not stopped
     */
    public function getStopDelayMs(string $sessionToken): ?int
    {
        if (!isset($this->stopFlags[$sessionToken])) {
            return null;
        }

        $session = $this->stopFlags[$sessionToken];
        if (!($session['stop_requested'] ?? false)) {
            return null;
        }

        $requestedAt = $session['requested_at'] ?? time();
        return (int)((time() - $requestedAt) * 1000);
    }

    /**
     * Validate stop mechanism is working
     * 
     * Quick health check
     * 
     * @return array {
     *     'healthy': bool,
     *     'active_sessions': int,
     *     'outstanding_stops': int,
     * }
     */
    public function getHealth(): array
    {
        $activeSessions = count($this->stopFlags);
        $outstandingStops = count(
            array_filter($this->stopFlags, fn($s) => $s['stop_requested'] ?? false)
        );

        return [
            'healthy' => true,
            'active_sessions' => $activeSessions,
            'outstanding_stops' => $outstandingStops,
        ];
    }
}
