<?php

namespace App\Services\AI;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

/**
 * TraceIdCorrelationService
 * 
 * Manages trace IDs for request correlation across distributed system
 * 
 * Features:
 * - Generate unique trace IDs for each request
 * - Propagate trace IDs through call chain
 * - Associate logs with trace ID
 * - Debug request flow across services
 * 
 * Standard: W3C Trace Context (traceparent header format)
 * Format: version-trace_id-parent_id-trace_flags
 * Example: 00-4bf92f3577b34da6a3ce929d0e0e4736-00f067aa0ba902b7-01
 */
class TraceIdCorrelationService
{
    /**
     * Active trace IDs (per request lifecycle)
     */
    protected static array $activeTraces = [];

    /**
     * Generate new trace context
     * 
     * @param string|null $parentTraceId Parent trace ID (for propagation)
     * @return array {
     *     'trace_id': string,
     *     'parent_id': string,
     *     'span_id': string,
     *     'trace_flags': string,
     *     'timestamp': string,
     * }
     */
    public function generateTraceContext(?string $parentTraceId = null): array
    {
        $traceId = $parentTraceId ?? Str::uuid()->toString();
        $spanId = bin2hex(random_bytes(8));
        $traceFlags = '01'; // Always sampled for now

        $context = [
            'trace_id' => $traceId,
            'parent_id' => $spanId,
            'span_id' => bin2hex(random_bytes(8)),
            'trace_flags' => $traceFlags,
            'timestamp' => now()->toIso8601String(),
        ];

        self::$activeTraces[$traceId] = $context;

        Log::info('trace_context_created', [
            'trace_id' => substr($traceId, 0, 12) . '...',
            'span_id' => substr($spanId, 0, 8) . '...',
        ]);

        return $context;
    }

    /**
     * Get current trace context
     * 
     * @param string $traceId
     * @return array|null
     */
    public function getTraceContext(string $traceId): ?array
    {
        return self::$activeTraces[$traceId] ?? null;
    }

    /**
     * Create child span from trace context
     * 
     * Used when delegating to another service
     * 
     * @param string $traceId
     * @param string $operation Name of child operation
     * @return array Child span context
     */
    public function createChildSpan(string $traceId, string $operation): array
    {
        $parentContext = $this->getTraceContext($traceId);
        if (!$parentContext) {
            return $this->generateTraceContext($traceId);
        }

        $childSpan = [
            'trace_id' => $traceId,
            'parent_id' => $parentContext['span_id'],
            'span_id' => bin2hex(random_bytes(8)),
            'operation' => $operation,
            'timestamp' => now()->toIso8601String(),
        ];

        Log::info('child_span_created', [
            'trace_id' => substr($traceId, 0, 12) . '...',
            'operation' => $operation,
            'span_id' => substr($childSpan['span_id'], 0, 8) . '...',
        ]);

        return $childSpan;
    }

    /**
     * Log event within trace
     * 
     * Automatically includes trace ID
     * 
     * @param string $traceId
     * @param string $level Log level (info, warning, error, etc)
     * @param string $event Event name
     * @param array $data Event data
     * @return void
     */
    public function logTracedEvent(string $traceId, string $level, string $event, array $data = []): void
    {
        Log::log($level, $event, array_merge($data, [
            'trace_id' => substr($traceId, 0, 12) . '...',
        ]));
    }

    /**
     * Record metric for trace
     * 
     * @param string $traceId
     * @param string $metricName
     * @param float $value
     * @param string $unit
     * @return void
     */
    public function recordMetric(string $traceId, string $metricName, float $value, string $unit = 'ms'): void
    {
        Log::debug('trace_metric', [
            'trace_id' => substr($traceId, 0, 12) . '...',
            'metric' => $metricName,
            'value' => $value,
            'unit' => $unit,
        ]);
    }

    /**
     * Finish trace and generate full trace report
     * 
     * @param string $traceId
     * @return array {
     *     'trace_id': string,
     *     'duration_ms': float,
     *     'status': string,  // 'success', 'error'
     *     'error_count': int,
     * }
     */
    public function finishTrace(string $traceId): array
    {
        $context = self::$activeTraces[$traceId] ?? null;
        if (!$context) {
            return [
                'trace_id' => $traceId,
                'duration_ms' => 0,
                'status' => 'unknown',
                'error_count' => 0,
            ];
        }

        $startTime = strtotime($context['timestamp']);
        $durationMs = (time() - $startTime) * 1000;

        Log::info('trace_finished', [
            'trace_id' => substr($traceId, 0, 12) . '...',
            'duration_ms' => round($durationMs, 1),
        ]);

        unset(self::$activeTraces[$traceId]);

        return [
            'trace_id' => $traceId,
            'duration_ms' => $durationMs,
            'status' => 'success',
            'error_count' => 0,
        ];
    }

    /**
     * Format trace context for HTTP header (W3C format)
     * 
     * @param string $traceId
     * @return string Traceparent header value
     */
    public function formatTraceparentHeader(string $traceId): string
    {
        $context = $this->getTraceContext($traceId);
        if (!$context) {
            return '';
        }

        return sprintf(
            '00-%s-%s-%s',
            str_pad($traceId, 32, '0', STR_PAD_LEFT),
            $context['parent_id'],
            $context['trace_flags']
        );
    }

    /**
     * Clear all traces (testing)
     */
    public function clearAllTraces(): void
    {
        self::$activeTraces = [];
    }

    /**
     * Get active trace count
     */
    public function getActiveTraceCount(): int
    {
        return count(self::$activeTraces);
    }
}
