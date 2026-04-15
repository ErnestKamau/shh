<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Log;

/**
 * PerformanceDashboardService
 * 
 * Aggregates system metrics into dashboard-friendly summaries
 * 
 * Provides:
 * - Key performance indicators (KPIs)
 * - Time-series data (hourly, daily, weekly)
 * - Trend analysis
 * - Leaderboards (best models, intents)
 * - Comparison views
 * 
 * In production, queries time-series database (InfluxDB, Prometheus, etc)
 */
class PerformanceDashboardService
{
    protected AnalyticsCollectorService $analytics;
    protected MonitoringService $monitoring;

    public function __construct(
        AnalyticsCollectorService $analytics,
        MonitoringService $monitoring
    ) {
        $this->analytics = $analytics;
        $this->monitoring = $monitoring;
    }

    /**
     * Get dashboard overview (summarized KPIs)
     * 
     * @return array {
     *     'kpis': {...},
     *     'alerts_active': int,
     *     'system_status': string,
     *     'last_updated': string,
     * }
     */
    public function getDashboardOverview(): array
    {
        $health = $this->analytics->getSystemHealth();
        $sla = $this->monitoring->checkSLA();
        $quota = $this->monitoring->getQuotaUsage('daily');

        $kpis = [
            'health_score' => $health['health_score'],
            'uptime_percent' => $health['model_success_rate'],
            'error_rate_percent' => $health['error_rate'],
            'routing_accuracy_percent' => $health['routing_accuracy'],
            'quota_used_percent' => $quota['usage_percent'],
            'sla_compliant' => $sla['meets_sla'],
        ];

        $activeAlerts = 0;
        if (!$sla['meets_sla']) {
            $activeAlerts += count($sla['violations']);
        }
        if ($quota['approaching_limit']) {
            $activeAlerts++;
        }

        return [
            'kpis' => $kpis,
            'alerts_active' => $activeAlerts,
            'system_status' => $health['status'],
            'last_updated' => now()->toIso8601String(),
        ];
    }

    /**
     * Get model comparison view
     * 
     * @return array {
     *     'models': array,  // Comparison data per model
     *     'leader': string, // Best performing model
     * }
     */
    public function getModelComparison(): array
    {
        $models = $this->analytics->getModelPerformance();

        usort($models, fn($a, $b) => $b['success_rate_percent'] <=> $a['success_rate_percent']);

        $comparison = [
            'models' => array_map(fn($m) => [
                'name' => $m['model'],
                'success_rate' => $m['success_rate_percent'],
                'avg_latency_ms' => $m['avg_latency_ms'],
                'total_requests' => $m['total_requests'],
                'avg_tokens' => $m['avg_tokens_per_request'],
                'rank' => 0,  // Will be filled in order
            ], $models),
            'leader' => $models[0]['model'] ?? null,
        ];

        foreach ($comparison['models'] as $i => &$model) {
            $model['rank'] = $i + 1;
        }

        return $comparison;
    }

    /**
     * Get intent performance breakdown
     * 
     * @return array {
     *     'intents': array,
     *     'total_requests': int,
     *     'most_common': string,
     * }
     */
    public function getIntentBreakdown(): array
    {
        $routing = $this->analytics->getRoutingQuality();

        $intentStats = [];
        $totalRequests = 0;

        foreach ($routing as $r) {
            $intent = $r['intent'];
            if (!isset($intentStats[$intent])) {
                $intentStats[$intent] = [
                    'name' => $intent,
                    'count' => 0,
                    'accuracy_percent' => 0,
                    'avg_confidence' => 0,
                ];
            }

            $intentStats[$intent]['count'] += $r['total'];
            $intentStats[$intent]['accuracy_percent'] = $r['accuracy_percent'];
            $intentStats[$intent]['avg_confidence'] = $r['avg_confidence'];
            $totalRequests += $r['total'];
        }

        uasort($intentStats, fn($a, $b) => $b['count'] <=> $a['count']);

        return [
            'intents' => array_values($intentStats),
            'total_requests' => $totalRequests,
            'most_common' => array_key_first($intentStats) ?? null,
        ];
    }

    /**
     * Get cost analysis
     * 
     * @return array {
     *     'total_tokens_used': int,
     *     'estimated_cost_usd': float,
     *     'cost_per_model': array,
     *     'daily_cost_trend': array,
     * }
     */
    public function getCostAnalysis(): array
    {
        $models = $this->analytics->getModelPerformance();

        $totalTokens = 0;
        $costPerModel = [];

        foreach ($models as $model) {
            $tokensUsed = $model['avg_tokens_per_request'] * $model['total_requests'];
            $totalTokens += $tokensUsed;

            // Estimate cost: $0.001 per 1000 tokens (adjust for actual pricing)
            $cost = ($tokensUsed / 1000) * 0.001;
            $costPerModel[$model['model']] = $cost;
        }

        $totalCost = array_sum($costPerModel);

        // Placeholder: real implementation would query time-series data
        $dailyTrend = [];

        return [
            'total_tokens_used' => $totalTokens,
            'estimated_cost_usd' => round($totalCost, 4),
            'cost_per_model' => $costPerModel,
            'daily_cost_trend' => $dailyTrend,
        ];
    }

    /**
     * Get error hotspots (where errors occur most)
     * 
     * @param int $limit Top N error locations
     * @return array {errors: array, total_errors: int}
     */
    public function getErrorHotspots(int $limit = 10): array
    {
        $errors = $this->analytics->getErrorSummary();

        $hotspots = [];
        $totalErrors = 0;

        foreach ($errors as $error) {
            $totalErrors += $error['total_errors'];
            foreach ($error['error_breakdown'] as $type => $count) {
                $hotspots[] = [
                    'component' => $error['component'],
                    'error_type' => $type,
                    'count' => $count,
                    'percentage' => 0,  // Will calculate
                ];
            }
        }

        // Sort by count
        usort($hotspots, fn($a, $b) => $b['count'] <=> $a['count']);

        // Limit results
        $hotspots = array_slice($hotspots, 0, $limit);

        // Calculate percentages
        foreach ($hotspots as &$h) {
            $h['percentage'] = $totalErrors > 0 ? round(($h['count'] / $totalErrors) * 100, 1) : 0;
        }

        return [
            'errors' => $hotspots,
            'total_errors' => $totalErrors,
        ];
    }

    /**
     * Generate SLA report
     * 
     * @return array
     */
    public function generateSLAReport(): array
    {
        $sla = $this->monitoring->checkSLA();
        $health = $this->analytics->getSystemHealth();

        return [
            'period' => 'daily',
            'sla_compliance' => [
                'meets_sla' => $sla['meets_sla'],
                'compliance_percent' => $sla['meets_sla'] ? 100 : 0,
            ],
            'metrics' => array_map(fn($k, $v) => [
                'name' => ucfirst(str_replace('_', ' ', $k)),
                'actual' => $v['actual'],
                'target' => $v['limit'],
                'met' => $v['acceptable'],
            ], array_keys($sla['checks']), array_values($sla['checks'])),
            'violations' => $sla['violations'],
            'generated_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Get a/b test results (if applicable)
     * 
     * @return array
     */
    public function getABTestResults(): array
    {
        // Placeholder: In production, query A/B test database
        return [
            'active_tests' => [],
            'completed_tests' => [],
            'winner' => null,
        ];
    }

    // ── ML Model Governance ──────────────────────────────────────────────────

    /**
     * Get full ML model governance metrics for the governance dashboard.
     *
     * Returns active models from ai.ai_model_registry, performance comparison
     * from the in-memory analytics collector, and active monitoring alerts.
     *
     * @return array{models: array, performance: array, alerts: array, overview: array}
     */
    public function getMLModelMetrics(?string $modelType = null): array
    {
        // Pull active models from DB
        $models = $this->fetchRegistryModels($modelType);

        // Live performance from analytics collector
        $performance = $this->analytics->getModelPerformance($modelType);

        // Active monitoring alerts
        $sla       = $this->monitoring->checkSLA();
        $quota     = $this->monitoring->getQuotaUsage('daily');
        $alerts    = [];

        if (! $sla['meets_sla']) {
            foreach ($sla['violations'] as $violation) {
                $alerts[] = [
                    'severity' => 'warning',
                    'type'     => 'sla_violation',
                    'message'  => $violation,
                ];
            }
        }

        if ($quota['approaching_limit']) {
            $alerts[] = [
                'severity' => 'info',
                'type'     => 'quota_approaching',
                'message'  => "Daily quota at {$quota['usage_percent']}%",
            ];
        }

        return [
            'models'      => $models,
            'performance' => $performance,
            'alerts'      => $alerts,
            'overview'    => $this->getDashboardOverview(),
        ];
    }

    /**
     * Fetch model records from ai.ai_model_registry.
     */
    protected function fetchRegistryModels(?string $modelType = null): array
    {
        try {
            $query = \DB::connection('pgsql_ai')
                ->table('ai.ai_model_registry')
                ->select([
                    'id', 'model_name', 'model_type', 'version',
                    'framework', 'training_rows', 'metrics',
                    'is_active', 'is_deprecated', 'deployed_at', 'created_at',
                ])
                ->where('is_deprecated', false);

            if ($modelType) {
                $query->where('model_type', $modelType);
            }

            return $query
                ->orderBy('model_type')
                ->orderByDesc('created_at')
                ->get()
                ->map(fn($row) => (array) $row)
                ->all();
        } catch (\Throwable $e) {
            \Log::warning('PerformanceDashboardService: could not fetch registry models', [
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Detect drift in feature distributions across the two latest snapshots.
     *
     * Compares row counts per snapshot; a sudden drop (>30%) signals potential drift.
     *
     * @return array<int, array{snapshot_id: int, feature_type: string, alert: string}>
     */
    public function getFeatureDriftAlerts(): array
    {
        $alerts = [];

        $tables = [
            'ai.ai_sample_features'    => 'sample',
            'ai.ai_equipment_features' => 'equipment',
            'ai.ai_qc_features'        => 'qc',
        ];

        foreach ($tables as $table => $featureType) {
            try {
                $snapshots = \DB::connection('pgsql_ai')
                    ->table($table)
                    ->selectRaw('snapshot_id, COUNT(*) as cnt')
                    ->groupBy('snapshot_id')
                    ->orderByDesc('snapshot_id')
                    ->limit(2)
                    ->get();

                if ($snapshots->count() < 2) {
                    continue;
                }

                [$latest, $previous] = [$snapshots[0], $snapshots[1]];

                if ($previous->cnt > 0) {
                    $drop = ($previous->cnt - $latest->cnt) / $previous->cnt;
                    if ($drop > 0.30) {
                        $alerts[] = [
                            'snapshot_id'  => $latest->snapshot_id,
                            'feature_type' => $featureType,
                            'alert'        => sprintf(
                                '%s features dropped %.0f%% from snapshot %d (%d) to %d (%d)',
                                ucfirst($featureType),
                                $drop * 100,
                                $previous->snapshot_id, $previous->cnt,
                                $latest->snapshot_id, $latest->cnt
                            ),
                        ];
                    }
                }
            } catch (\Throwable $e) {
                \Log::debug("PerformanceDashboardService: drift check skipped for {$table}", [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $alerts;
    }

    /**
     * Export key AI metrics in Prometheus text format (exposition version 0.0.4).
     *
     * Scraped by Prometheus at GET /api/ai/metrics (no auth — network-restricted).
     * Metrics: system health, per-model prediction counts & latency, model
     * confidence, total error counter, drift-detected gauge.
     */
    public function exportMetricsPrometheus(): string
    {
        $lines = [];
        $ts    = (int) (microtime(true) * 1000);

        try {
            $health = $this->analytics->getSystemHealth();
            $models = $this->analytics->getModelPerformance();

            // --- system health gauge ---
            $lines[] = '# HELP imara_ai_system_health 1 = healthy, 0 = degraded';
            $lines[] = '# TYPE imara_ai_system_health gauge';
            $healthy = ($health['status'] ?? '') === 'healthy' ? '1' : '0';
            $lines[] = "imara_ai_system_health {$healthy} {$ts}";

            // --- per-model counters & latency ---
            $lines[] = '# HELP imara_ai_predictions_total Cumulative predictions per model type';
            $lines[] = '# TYPE imara_ai_predictions_total counter';
            $lines[] = '# HELP imara_ai_model_latency_ms Average inference latency in milliseconds';
            $lines[] = '# TYPE imara_ai_model_latency_ms gauge';
            $lines[] = '# HELP imara_ai_model_confidence Average prediction confidence [0-1]';
            $lines[] = '# TYPE imara_ai_model_confidence gauge';

            foreach ($models as $m) {
                $type    = preg_replace('/[^a-z0-9_]/', '_', strtolower($m['model'] ?? 'unknown'));
                $total   = $m['total_requests'] ?? 0;
                $latency = $m['avg_latency_ms']  ?? 0;
                $conf    = $m['avg_confidence']  ?? 0.0;

                $lines[] = "imara_ai_predictions_total{model_type=\"{$type}\"} {$total} {$ts}";
                $lines[] = "imara_ai_model_latency_ms{model_type=\"{$type}\"} {$latency} {$ts}";
                $lines[] = "imara_ai_model_confidence{model_type=\"{$type}\"} {$conf} {$ts}";
            }

            // --- error counter ---
            $errors      = $this->analytics->getErrorSummary();
            $totalErrors = array_sum(array_column($errors, 'total_errors'));
            $lines[]     = '# HELP imara_ai_errors_total Total prediction errors since last restart';
            $lines[]     = '# TYPE imara_ai_errors_total counter';
            $lines[]     = "imara_ai_errors_total {$totalErrors} {$ts}";

            // --- drift gauge ---
            $driftAlerts = $this->getFeatureDriftAlerts();
            $lines[]     = '# HELP imara_ai_model_drift_detected 1 if any model shows feature drift';
            $lines[]     = '# TYPE imara_ai_model_drift_detected gauge';
            $driftVal    = count($driftAlerts) > 0 ? '1' : '0';
            $lines[]     = "imara_ai_model_drift_detected {$driftVal} {$ts}";

        } catch (\Throwable $e) {
            Log::warning('PerformanceDashboardService: prometheus export error', [
                'error' => $e->getMessage(),
            ]);
            $lines[] = '# ERROR generating metrics: ' . $e->getMessage();
        }

        return implode("\n", $lines) . "\n";
    }
}
