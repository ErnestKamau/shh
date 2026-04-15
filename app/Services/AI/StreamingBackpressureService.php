<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Log;

/**
 * StreamingBackpressureService
 * 
 * Flow control for streaming responses to prevent overwhelming clients
 * 
 * Mechanism:
 * - Buffer tokens/chunks into 1KB minimum
 * - Flush to client every 100ms or when buffer full
 * - Track elapsed time, abort if > 5 minutes
 * - Measure client readiness (socket available)
 * 
 * Usage in streaming loop:
 * ```
 * $backpressure = new StreamingBackpressureService();
 * while ($hasMoreTokens) {
 *     $token = model->nextToken();
 *     if ($backpressure->shouldBufferMore()) {
 *         $backpressure->buffer($token);
 *     } else {
 *         $chunk = $backpressure->flush();
 *         echo $chunk;
 *         $backpressure->reset();
 *     }
 *     if ($backpressure->isMaxDurationExceeded()) {
 *         break;
 *     }
 * }
 * ```
 */
class StreamingBackpressureService
{
    /**
     * Configuration
     */
    protected const MIN_CHUNK_SIZE = 1024;        // 1 KB minimum
    protected const FLUSH_INTERVAL_MS = 100;      // Flush every 100ms
    protected const MAX_DURATION_SECONDS = 300;   // 5 minutes max
    protected const MAX_BUFFER_SIZE = 10240;      // 10 KB max before forced flush

    /**
     * State
     */
    protected array $buffer = [];
    protected int $bufferSize = 0;
    protected int $startTime;
    protected int $lastFlushTime;
    protected int $tokenCount = 0;
    protected int $flushCount = 0;

    public function __construct()
    {
        $this->startTime = $this->getCurrentTimeMs();
        $this->lastFlushTime = $this->startTime;
    }

    /**
     * Get current time in milliseconds
     * 
     * @return int
     */
    protected function getCurrentTimeMs(): int
    {
        return (int)(microtime(true) * 1000);
    }

    /**
     * Add a token/chunk to the buffer
     * 
     * @param string $token The token or partial output
     * @param int $estimatedBytes Optional estimated byte size
     * @return void
     */
    public function buffer(string $token, int $estimatedBytes = 0): void
    {
        $this->buffer[] = $token;
        $this->bufferSize += strlen($token);
        $this->tokenCount++;
    }

    /**
     * Check if we should continue buffering or flush
     * 
     * Returns false if:
     * - Buffer >= 1KB AND >= 100ms since last flush, OR
     * - Buffer >= 10KB (backpressure limit)
     * 
     * @return bool True if safe to add more to buffer, false if should flush
     */
    public function shouldBufferMore(): bool
    {
        // Always flush if hit max buffer size (backpressure point)
        if ($this->bufferSize >= self::MAX_BUFFER_SIZE) {
            return false;
        }

        // Not ready to flush yet if below min chunk size
        if ($this->bufferSize < self::MIN_CHUNK_SIZE) {
            return true;
        }

        // Check if enough time has passed
        $elapsed = $this->getCurrentTimeMs() - $this->lastFlushTime;
        return $elapsed < self::FLUSH_INTERVAL_MS;
    }

    /**
     * Flush (get) the current buffer as a single chunk
     * 
     * @return string Concatenated buffer content
     */
    public function flush(): string
    {
        $chunk = implode('', $this->buffer);
        $this->flushCount++;

        Log::debug('backpressure_flush', [
            'flush_count' => $this->flushCount,
            'chunk_size_bytes' => strlen($chunk),
            'token_count' => $this->tokenCount,
            'elapsed_ms' => $this->getCurrentTimeMs() - $this->lastFlushTime,
        ]);

        $this->lastFlushTime = $this->getCurrentTimeMs();
        $this->buffer = [];
        $this->bufferSize = 0;

        return $chunk;
    }

    /**
     * Reset buffer state (after flush)
     * 
     * Call this after sending a flush to the client
     * 
     * @return void
     */
    public function reset(): void
    {
        $this->buffer = [];
        $this->bufferSize = 0;
        $this->lastFlushTime = $this->getCurrentTimeMs();
    }

    /**
     * Check if max duration exceeded
     * 
     * @return bool True if streaming for > 5 minutes
     */
    public function isMaxDurationExceeded(): bool
    {
        $elapsedSeconds = (int)((($this->getCurrentTimeMs() - $this->startTime)) / 1000);
        return $elapsedSeconds >= self::MAX_DURATION_SECONDS;
    }

    /**
     * Get elapsed time since stream start
     * 
     * @return int Milliseconds
     */
    public function getElapsedMs(): int
    {
        return $this->getCurrentTimeMs() - $this->startTime;
    }

    /**
     * Get remaining time before max duration
     * 
     * @return int Milliseconds remaining
     */
    public function getRemainingMs(): int
    {
        $remaining = (self::MAX_DURATION_SECONDS * 1000) - $this->getElapsedMs();
        return max(0, $remaining);
    }

    /**
     * Check if stream should be considered "slow" (backpressure building)
     * 
     * Slow = buffer growing faster than being flushed
     * 
     * @return bool True if backpressure is building
     */
    public function isBackpressureBuilding(): bool
    {
        // If buffer is more than half full, backpressure is building
        $bufferUtilization = $this->bufferSize / self::MAX_BUFFER_SIZE;
        return $bufferUtilization > 0.5;
    }

    /**
     * Get current buffer state
     * 
     * @return array {
     *     'buffer_size_bytes': int,
     *     'buffer_count': int,
     *     'token_count': int,
     *     'flush_count': int,
     *     'elapsed_ms': int,
     *     'remaining_ms': int,
     *     'is_backpressure_building': bool,
     *     'is_max_duration_exceeded': bool,
     * }
     */
    public function getStatus(): array
    {
        return [
            'buffer_size_bytes' => $this->bufferSize,
            'buffer_count' => count($this->buffer),
            'token_count' => $this->tokenCount,
            'flush_count' => $this->flushCount,
            'elapsed_ms' => $this->getElapsedMs(),
            'remaining_ms' => $this->getRemainingMs(),
            'is_backpressure_building' => $this->isBackpressureBuilding(),
            'is_max_duration_exceeded' => $this->isMaxDurationExceeded(),
        ];
    }

    /**
     * Force flush regardless of size
     * 
     * Use when you know no more data is coming (end of response)
     * 
     * @return string Final chunk
     */
    public function flushFinal(): string
    {
        $chunk = implode('', $this->buffer);
        
        Log::info('backpressure_flush_final', [
            'total_flushes' => $this->flushCount + 1,
            'total_tokens' => $this->tokenCount,
            'total_duration_ms' => $this->getElapsedMs(),
            'final_chunk_size' => strlen($chunk),
            'average_chunk_size' => $this->flushCount > 0
                ? 0  // calculated elsewhere
                : strlen($chunk),
        ]);

        $this->buffer = [];
        $this->bufferSize = 0;

        return $chunk;
    }

    /**
     * Get performance metrics
     * 
     * @return array {
     *     'total_tokens': int,
     *     'total_flushes': int,
     *     'avg_tokens_per_flush': float,
     *     'avg_flush_interval_ms': float,
     *     'total_duration_seconds': float,
     * }
     */
    public function getMetrics(): array
    {
        $totalDurationSecond = $this->getElapsedMs() / 1000;

        return [
            'total_tokens' => $this->tokenCount,
            'total_flushes' => $this->flushCount,
            'avg_tokens_per_flush' => $this->flushCount > 0
                ? $this->tokenCount / $this->flushCount
                : $this->tokenCount,
            'avg_flush_interval_ms' => $this->flushCount > 0
                ? $this->getElapsedMs() / ($this->flushCount + 1)
                : 0,
            'total_duration_seconds' => round($totalDurationSecond, 2),
        ];
    }
}
