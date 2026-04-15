<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Log;

/**
 * MonitoringService
 * 
 * Real-time monitoring and alerting for AI operations
 * 
 * Responsibilities:
 * - Track SLA metrics (response time, availability)
 * - Generate alerts when thresholds exceeded
 * - Track quota usage (tokens, API calls)
 * - Monitor model health and resource usage
 * 
 * In production, connects to monitoring systems (Prometheus, DataDog, etc)
 */
class MonitoringService
{
    protected AnalyticsCollectorService $analytics;

    protected const SLA_CONFIG = [
        'max_response_time_ms' => 5000,
        'min_availability_percent' => 99.0,
        'max_error_rate_percent' => 5.0,
        'token_quota_daily' => 100000,
    ];

    public function __construct(AnalyticsCollectorService $analytics)
    {
        $this->analytics = $analytics;
    }

    /**
     * Check if system meets SLA requirements
     * 
     * @return array {
     *     'meets_sla': bool,
     *     'checks': {
     *         'response_time': {acceptable: bool, actual: int, limit: int},
     *         'availability': {acceptable: bool, actual: float, limit: float},
     *         'error_rate': {acceptable: bool, actual: float, limit: float},
     *     },
     *     'violations': string[],
     * }
     */
    public function checkSLA(): array
    {
        $health = $this->analytics->getSystemHealth();
        $modelPerf = $this->analytics->getModelPerformance();

        $checks = [
            'response_time' => [
                'acceptable' => true,
                'actual' => 0,
                'limit' => self::SLA_CONFIG['max_response_time_ms'],
            ],
            'availability' => [
                'acceptable' => $health['model_success_rate'] >= self::SLA_CONFIG['min_availability_percent'],
                'actual' => $health['model_success_rate'],
                'limit' => self::SLA_CONFIG['min_availability_percent'],
            ],
            'error_rate' => [
                'acceptable' => $health['error_rate'] <= self::SLA_CONFIG['max_error_rate_percent'],
                'actual' => $health['error_rate'],
                'limit' => self::SLA_CONFIG['max_error_rate_percent'],
            ],
        ];

        // Calculate actual response time
        if (!empty($modelPerf)) {
            $avgLatency = array_reduce($modelPerf, fn($carry, $m) => $carry + $m['avg_latency_ms'], 0) / count($modelPerf);
            $checks['response_time']['actual'] = (int)$avgLatency;
            $checks['response_time']['acceptable'] = $avgLatency <= self::SLA_CONFIG['max_response_time_ms'];
        }

        // Compile violations
        $violations = [];
        foreach ($checks as $check => $result) {
            if (!$result['acceptable']) {
                $violations[] = "$check: {$result['actual']} exceeds limit {$result['limit']}";
            }
        }

        $meetsSLA = empty($violations);

        Log::info('sla_check', [
            'meets_sla' => $meetsSLA,
            'violations_count' => count($violations),
        ]);

        return [
            'meets_sla' => $meetsSLA,
            'checks' => $checks,
            'violations' => $violations,
        ];
    }

    /**
     * Generate alert if thresholds exceeded
     * 
     * @param string $metric Metric to check (routing_accuracy, model_latency, error_rate)
     * @param float $threshold Threshold value
     * @param string $operator Comparison operator ('>', '<', '>=', '<=', '==', '!=')
     * @return array {
     *     'alert_triggered': bool,
     *     'metric': string,
     *     'actual_value': float,
     *     'threshold': float,
     *     'severity': string,   // 'info', 'warning', 'critical'
     *     'message': string,
     * }
     */
    public function checkMetricThreshold(string $metric, float $threshold, string $operator = '>'): array
    {
        $health = $this->analytics->getSystemHealth();
        $actualValue = match ($metric) {
            'routing_accuracy' => $health['routing_accuracy'],
            'model_success_rate' => $health['model_success_rate'],
            'error_rate' => $health['error_rate'],
            'health_score' => $health['health_score'],
            default => 0,
        };

        $triggered = match ($operator) {
            '>' => $actualValue > $threshold,
            '<' => $actualValue < $threshold,
            '>=' => $actualValue >= $threshold,
            '<=' => $actualValue <= $threshold,
            '==' => $actualValue == $threshold,
            '!=' => $actualValue != $threshold,
            default => false,
        };

        $severity = match (true) {
            $metric === 'error_rate' && $actualValue > 10 => 'critical',
            $metric === 'model_success_rate' && $actualValue < 90 => 'critical',
            $metric === 'routing_accuracy' && $actualValue < 85 => 'warning',
            default => 'info',
        };

        $message = $triggered
            ? "{$metric} {$operator} {$threshold}: {$actualValue} triggered alert"
            : "{$metric} is within acceptable range";

        if ($triggered) {
            Log::log(
                match ($severity) {
                    'critical' => 'critical',
                    'warning' => 'warning',
                    default => 'info',
                },
                'metric_threshold_alert',
                [
                    'metric' => $metric,
                    'actual' => $actualValue,
                    'threshold' => $threshold,
                    'severity' => $severity,
                ]
            );
        }

        return [
            'alert_triggered' => $triggered,
            'metric' => $metric,
            'actual_value' => $actualValue,
            'threshold' => $threshold,
            'severity' => $severity,
            'message' => $message,
        ];
    }

    /**
     * Get quota usage summary
     * 
     * @param string $period 'daily', 'weekly', 'monthly'
     * @return array {
     *     'period': string,
     *     'tokens_used': int,
     *     'tokens_limit': int,
     *     'tokens_remaining': int,
     *     'usage_percent': float,
     *     'api_calls_used': int,
     *     'approaching_limit': bool,
     * }
     */
    public function getQuotaUsage(string $period = 'daily'): array
    {
        $modelPerf = $this->analytics->getModelPerformance();

        $tokensUsed = 0;
        $apiCalls = 0;

        foreach ($modelPerf as $model) {
            $tokensUsed += $model['avg_tokens_per_request'] * $model['total_requests'];
            $apiCalls += $model['total_requests'];
        }

        $tokenLimit = match ($period) {
            'daily' => self::SLA_CONFIG['token_quota_daily'],
            'weekly' => self::SLA_CONFIG['token_quota_daily'] * 7,
            'monthly' => self::SLA_CONFIG['token_quota_daily'] * 30,
            default => self::SLA_CONFIG['token_quota_daily'],
        };

        $usagePercent = $tokenLimit > 0 ? ($tokensUsed / $tokenLimit) * 100 : 0;
        $approachingLimit = $usagePercent >= 80;

        if ($approachingLimit) {
            Log::warning('quota_approaching_limit', [
                'period' => $period,
                'usage_percent' => round($usagePercent, 1),
            ]);
        }

        return [
            'period' => $period,
            'tokens_used' => $tokensUsed,
            'tokens_limit' => $tokenLimit,
            'tokens_remaining' => max(0, $tokenLimit - $tokensUsed),
            'usage_percent' => round($usagePercent, 1),
            'api_calls_used' => $apiCalls,
            'approaching_limit' => $approachingLimit,
        ];
    }

    /**
     * Get a comprehensive system status report
     * 
     * @return array
     */
    public function getSystemStatus(): array
    {
        $health = $this->analytics->getSystemHealth();
        $sla = $this->checkSLA();
        $quota = $this->getQuotaUsage('daily');
        $routing = $this->analytics->getRoutingQuality();
        $models = $this->analytics->getModelPerformance();
        $errors = $this->analytics->getErrorSummary();

        return [
            'timestamp' => now()->toIso8601String(),
            'overall_health' => $health,
            'sla_compliance' => $sla,
            'quota_usage' => $quota,
            'routing_stats' => $routing,
            'model_stats' => $models,
            'error_stats' => $errors,
            'status_summary' => [
                'is_healthy' => $health['status'] === 'healthy',
                'meets_sla' => $sla['meets_sla'],
                'quota_healthy' => !$quota['approaching_limit'],
            ],
        ];
    }

    /**
     * Get SLA configuration
     * 
     * @return array
     */
    public function getSLAConfig(): array
    {
        return self::SLA_CONFIG;
    }
}
