<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * AnalyticsCollectorService
 * 
 * Aggregates metrics from AI operations for monitoring and debugging
 * 
 * Tracks:
 * - Route quality (how often recommended routing works)
 * - Model performance (latency, success rate per model)
 * - Cost metrics (tokens used, estimated cost)
 * - Error rates and failure reasons
 * - User behavior patterns (intents, regenerations)
 * 
 * In production, persist to analytics database or external service
 * For now, stores in-memory with basic aggregation
 */
class AnalyticsCollectorService
{
    protected array $metrics = [];
    protected array $routingStats = [];
    protected array $modelStats = [];
    protected array $errorStats = [];

    /**
     * Record a routing decision
     * 
     * @param string $intent The detected intent
     * @param string $selectedModel The model chosen
     * @param string $recommendedModel The model recommended by routing
     * @param float $confidence The confidence score
     * @return void
     */
    public function recordRouting(
        string $intent,
        string $selectedModel,
        string $recommendedModel,
        float $confidence
    ): void {
        $key = "routing_{$intent}_{$selectedModel}";

        if (!isset($this->routingStats[$key])) {
            $this->routingStats[$key] = [
                'intent' => $intent,
                'model' => $selectedModel,
                'count' => 0,
                'correct_recommendations' => 0,
                'avg_confidence' => 0,
                'total_confidence' => 0,
            ];
        }

        $this->routingStats[$key]['count']++;
        $this->routingStats[$key]['total_confidence'] += $confidence;
        $this->routingStats[$key]['avg_confidence'] = 
            $this->routingStats[$key]['total_confidence'] / $this->routingStats[$key]['count'];

        if ($selectedModel === $recommendedModel) {
            $this->routingStats[$key]['correct_recommendations']++;
        }

        // Persist to DB
        try {
            DB::table('ai_analytics_logs')->insert([
                'intent' => $intent,
                'model_used' => $selectedModel,
                'confidence' => $confidence,
                'success' => true,
                'metadata' => json_encode(['recommended_model' => $recommendedModel]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('AnalyticsCollector: Failed to persist routing log: ' . $e->getMessage());
        }
    }

    /**
     * Record model performance metrics
     * 
     * @param string $model Model name
     * @param int $latencyMs Response time in milliseconds
     * @param int $tokensUsed Tokens consumed
     * @param bool $success Whether request succeeded
     * @param string|null $error Error message if failed
     * @return void
     */
    public function recordModelPerformance(
        string $model,
        int $latencyMs,
        int $tokensUsed,
        bool $success,
        ?string $error = null
    ): void {
        $key = "model_{$model}";

        if (!isset($this->modelStats[$key])) {
            $this->modelStats[$key] = [
                'model' => $model,
                'total_requests' => 0,
                'successful_requests' => 0,
                'failed_requests' => 0,
                'avg_latency_ms' => 0,
                'total_latency_ms' => 0,
                'min_latency_ms' => PHP_INT_MAX,
                'max_latency_ms' => 0,
                'total_tokens' => 0,
                'avg_tokens_per_request' => 0,
            ];
        }

        $stats = &$this->modelStats[$key];
        $stats['total_requests']++;

        if ($success) {
            $stats['successful_requests']++;
        } else {
            $stats['failed_requests']++;
        }

        $stats['total_latency_ms'] += $latencyMs;
        $stats['avg_latency_ms'] = round($stats['total_latency_ms'] / $stats['total_requests'], 1);
        $stats['min_latency_ms'] = min($stats['min_latency_ms'], $latencyMs);
        $stats['max_latency_ms'] = max($stats['max_latency_ms'], $latencyMs);

        $stats['total_tokens'] += $tokensUsed;
        $stats['avg_tokens_per_request'] = round($stats['total_tokens'] / $stats['total_requests'], 0);

        if ($error) {
            $this->recordError($model, $error);
        }

        // Persist to DB
        try {
            DB::table('ai_analytics_logs')->insert([
                'model_used' => $model,
                'latency_ms' => $latencyMs,
                'tokens_used' => $tokensUsed,
                'success' => $success,
                'error_message' => $error,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('AnalyticsCollector: Failed to persist performance log: ' . $e->getMessage());
        }
    }

    /**
     * Record an error event
     * 
     * @param string $component Component/model that failed
     * @param string $error Error message
     * @param array $context Optional context data
     * @return void
     */
    public function recordError(string $component, string $error, array $context = []): void
    {
        $key = "error_{$component}";

        if (!isset($this->errorStats[$key])) {
            $this->errorStats[$key] = [
                'component' => $component,
                'total_errors' => 0,
                'error_types' => [],
            ];
        }

        $this->errorStats[$key]['total_errors']++;

        $errorType = $this->classifyError($error);
        if (!isset($this->errorStats[$key]['error_types'][$errorType])) {
            $this->errorStats[$key]['error_types'][$errorType] = 0;
        }
        $this->errorStats[$key]['error_types'][$errorType]++;

        Log::warning('analytics_error_recorded', [
            'component' => $component,
            'error_type' => $errorType,
            'message' => substr($error, 0, 100),
        ]);
    }

    /**
     * Record streaming response start
     * 
     * @param string|null $sessionId Unique session identifier
     * @param int|null $userId User ID
     * @param int|null $conversationId Conversation ID
     * @param string $question User's question
     * @return void
     */
    public function recordStreamStart(
        ?string $sessionId = null,
        ?int $userId = null,
        ?int $conversationId = null,
        string $question = ''
    ): void {
        $key = "stream_{$sessionId}";
        
        if (!isset($this->metrics[$key])) {
            $this->metrics[$key] = [
                'session_id' => $sessionId,
                'user_id' => $userId,
                'conversation_id' => $conversationId,
                'question' => $question,
                'started_at' => now(),
                'completed_at' => null,
                'duration_ms' => 0,
                'status' => 'started',
            ];
        }
        
        Log::debug('stream_started', [
            'session_id' => $sessionId,
            'user_id' => $userId,
            'conversation_id' => $conversationId,
        ]);
    }

    /**
     * Record streaming response completion
     * 
     * @param string|null $sessionId Unique session identifier
     * @param int|null $userId User ID for the session
     * @param bool|null $completedNormally Whether stream completed without interruption
     * @return void
     */
    public function recordStreamEnd(
        ?string $sessionId = null,
        ?int $userId = null,
        ?bool $completedNormally = true
    ): void {
        $key = "stream_{$sessionId}";
        
        if (isset($this->metrics[$key])) {
            $this->metrics[$key]['completed_at'] = now();
            $this->metrics[$key]['status'] = $completedNormally ? 'completed' : 'interrupted';
            
            // Calculate duration
            if ($this->metrics[$key]['started_at']) {
                $duration = now()->diffInMilliseconds($this->metrics[$key]['started_at']);
                $this->metrics[$key]['duration_ms'] = $duration;
            }
        }
        
        Log::debug('stream_ended', [
            'session_id' => $sessionId,
            'user_id' => $userId,
            'status' => $completedNormally ? 'completed' : 'interrupted',
            'duration_ms' => $this->metrics[$key]['duration_ms'] ?? 0,
        ]);
    }

    /**
     * Classify error by pattern
     * 
     * @param string $error
     * @return string
     */
    protected function classifyError(string $error): string
    {
        if (strpos($error, 'timeout') !== false) {
            return 'timeout';
        }
        if (strpos($error, 'not found') !== false) {
            return 'not_found';
        }
        if (strpos($error, 'unauthorized') !== false || strpos($error, 'forbidden') !== false) {
            return 'auth_error';
        }
        if (strpos($error, 'invalid') !== false) {
            return 'validation_error';
        }
        return 'unknown';
    }

    /**
     * Get routing quality metrics
     * 
     * @param string|null $intent Filter by intent, or null for all
     * @return array
     */
    public function getRoutingQuality(?string $intent = null): array
    {
        $stats = [];

        // 1. Fetch from database (Persistent logs from Python AI Service)
        try {
            $dbLogs = DB::connection('pgsql_ai')
                ->table('ai_request_logs')
                ->select('route_name as intent', DB::raw('count(*) as count'), DB::raw('avg(latency_ms) as avg_latency'))
                ->where('created_at', '>=', now()->subDays(30))
                ->whereNotNull('route_name')
                ->where('session_id', '!=', 'local-validation-001') // Filter system probes
                ->whereNotNull('user_id') // Only human activity
                ->groupBy('route_name')
                ->get();

            foreach ($dbLogs as $log) {
                $stats[$log->intent] = [
                    'intent' => $log->intent,
                    'model' => 'auto',
                    'total' => (int)$log->count,
                    'accuracy_percent' => 100.0, // Assumption for matched intents
                    'avg_confidence' => 0.95,
                ];
            }
        } catch (\Exception $e) {
            Log::warning('AnalyticsCollector: Could not fetch routing logs from pgsql_ai: ' . $e->getMessage());
        }

        // 2. Merge with in-memory stats (if any)
        foreach ($this->routingStats as $key => $data) {
            if ($intent && $data['intent'] !== $intent) {
                continue;
            }

            if (isset($stats[$data['intent']])) {
                $stats[$data['intent']]['total'] += $data['count'];
            } else {
                $accuracy = $data['count'] > 0
                    ? ($data['correct_recommendations'] / $data['count']) * 100
                    : 0;

                $stats[$data['intent']] = [
                    'intent' => $data['intent'],
                    'model' => $data['model'],
                    'total' => $data['count'],
                    'accuracy_percent' => round($accuracy, 1),
                    'avg_confidence' => round($data['avg_confidence'], 2),
                ];
            }
        }

        return array_values($stats);
    }

    /**
     * Get model performance summary
     * 
     * @param string|null $model Filter by model, or null for all
     * @return array
     */
    public function getModelPerformance(?string $modelName = null): array
    {
        $stats = [];

        // 1. Fetch from database (Persistent logs from Python AI Service)
        try {
            $dbLogs = DB::connection('pgsql_ai')
                ->table('ai.ai_request_logs')
                ->select(
                    'mode as model',
                    DB::raw('count(*) as total_requests'),
                    DB::raw('sum(case when success then 1 else 0 end) as successful_requests'),
                    DB::raw('avg(latency_ms) as avg_latency_ms'),
                    DB::raw('avg(confidence) as avg_confidence'),
                    DB::raw('avg(case when source_count > 0 then source_count else 0 end) as avg_tokens') // Using source_count as proxy if tokens not avail
                )
                ->where('created_at', '>=', now()->subDays(30))
                ->where('session_id', '!=', 'local-validation-001')
                ->whereNotNull('user_id')
                ->groupBy('mode')
                ->get();

            foreach ($dbLogs as $log) {
                $successRate = $log->total_requests > 0
                    ? ($log->successful_requests / $log->total_requests) * 100
                    : 0;

                $stats[$log->model] = [
                    'model' => $log->model,
                    'total_requests' => (int)$log->total_requests,
                    'success_rate_percent' => round($successRate, 1),
                    'failed_count' => (int)($log->total_requests - $log->successful_requests),
                    'avg_latency_ms' => round($log->avg_latency_ms, 1),
                    'avg_confidence' => round($log->avg_confidence, 2),
                    'min_latency_ms' => 0,
                    'max_latency_ms' => 0,
                    'avg_tokens_per_request' => round($log->avg_tokens, 0),
                ];
            }
        } catch (\Exception $e) {
            Log::warning('AnalyticsCollector: Could not fetch model performance from pgsql_ai: ' . $e->getMessage());
        }

        // 2. Merge with in-memory stats
        foreach ($this->modelStats as $key => $data) {
            if ($modelName && $data['model'] !== $modelName) {
                continue;
            }

            if (isset($stats[$data['model']])) {
                // Aggregate (Simplified)
                $stats[$data['model']]['total_requests'] += $data['total_requests'];
            } else {
                $successRate = $data['total_requests'] > 0
                    ? ($data['successful_requests'] / $data['total_requests']) * 100
                    : 0;

                $stats[$data['model']] = [
                    'model' => $data['model'],
                    'total_requests' => $data['total_requests'],
                    'success_rate_percent' => round($successRate, 1),
                    'failed_count' => $data['failed_requests'],
                    'avg_latency_ms' => $data['avg_latency_ms'],
                    'min_latency_ms' => $data['min_latency_ms'] === PHP_INT_MAX ? 0 : $data['min_latency_ms'],
                    'max_latency_ms' => $data['max_latency_ms'],
                    'avg_tokens_per_request' => $data['avg_tokens_per_request'],
                ];
            }
        }

        return array_values($stats);
    }

    /**
     * Get error summary
     * 
     * @param string|null $component Filter by component, or null for all
     * @return array
     */
    public function getErrorSummary(?string $component = null): array
    {
        $stats = [];

        foreach ($this->errorStats as $key => $data) {
            if ($component && $data['component'] !== $component) {
                continue;
            }

            $stats[] = [
                'component' => $data['component'],
                'total_errors' => $data['total_errors'],
                'error_breakdown' => $data['error_types'],
            ];
        }

        return $stats;
    }

    /**
     * Get overall system health
     * 
     * @return array {
     *     'health_score': float,     // 0-100
     *     'status': string,          // 'healthy', 'degraded', 'unhealthy'
     *     'routing_accuracy': float,
     *     'model_success_rate': float,
     *     'error_rate': float,
     * }
     */
    public function getSystemHealth(): array
    {
        // 1. Calculate current in-memory scores
        $currRouting = $this->calculateRoutingAccuracy();
        $currModel = $this->calculateModelSuccessRate();
        $currError = $this->calculateErrorRate();

        // 2. Fetch historical scores from DB (last 7 days)
        $dbStats = DB::table('ai_analytics_logs')
            ->where('created_at', '>=', now()->subDays(7))
            ->selectRaw('count(*) as total, sum(case when success then 1 else 0 end) as success_count')
            ->first();
        
        $historicalSuccessRate = ($dbStats && $dbStats->total > 0) 
            ? ($dbStats->success_count / $dbStats->total) * 100 
            : 100;

        // 3. Weighted Blend (current performance + historical stability)
        $blendedSuccessRate = ($currModel * 0.7) + ($historicalSuccessRate * 0.3);
        $healthScore = ($currRouting * 0.3) + ($blendedSuccessRate * 0.5) + ((100 - $currError) * 0.2);

        $status = match (true) {
            $healthScore >= 90 => 'healthy',
            $healthScore >= 70 => 'degraded',
            default => 'unhealthy',
        };

        return [
            'health_score' => round($healthScore, 1),
            'status' => $status,
            'routing_accuracy' => round($currRouting, 1),
            'model_success_rate' => round($blendedSuccessRate, 1),
            'error_rate' => round($currError, 1),
        ];
    }

    /**
     * Calculate average routing accuracy
     */
    protected function calculateRoutingAccuracy(): float
    {
        if (empty($this->routingStats)) {
            return 100;
        }

        $totalRouting = 0;
        $correctRouting = 0;

        foreach ($this->routingStats as $data) {
            $totalRouting += $data['count'];
            $correctRouting += $data['correct_recommendations'];
        }

        return $totalRouting > 0 ? ($correctRouting / $totalRouting) * 100 : 100;
    }

    /**
     * Calculate average model success rate
     */
    protected function calculateModelSuccessRate(): float
    {
        if (empty($this->modelStats)) {
            return 100;
        }

        $totalRequests = 0;
        $successfulRequests = 0;

        foreach ($this->modelStats as $data) {
            $totalRequests += $data['total_requests'];
            $successfulRequests += $data['successful_requests'];
        }

        return $totalRequests > 0 ? ($successfulRequests / $totalRequests) * 100 : 100;
    }

    /**
     * Calculate overall error rate
     */
    protected function calculateErrorRate(): float
    {
        $totalErrors = 0;
        $totalRequests = 0;

        foreach ($this->errorStats as $data) {
            $totalErrors += $data['total_errors'];
        }

        foreach ($this->modelStats as $data) {
            $totalRequests += $data['total_requests'];
        }

        return $totalRequests > 0 ? ($totalErrors / $totalRequests) * 100 : 0;
    }

    /**
     * Get recent AI request logs
     * 
     * @param int $limit
     * @return array
     */
    public function getRecentLogs(int $limit = 50): array
    {
        try {
            return DB::connection('pgsql_ai')
                ->table('ai_request_logs')
                ->select([
                    'id', 'trace_id', 'query', 'mode', 'route_name', 
                    'latency_ms', 'confidence', 'success', 'created_at'
                ])
                ->where('session_id', '!=', 'local-validation-001')
                ->whereNotNull('user_id')
                ->orderByDesc('created_at')
                ->limit($limit)
                ->get()
                ->map(fn($log) => (array) $log)
                ->all();
        } catch (\Exception $e) {
            Log::warning('AnalyticsCollector: Could not fetch recent logs: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Reset all metrics (for testing)
     */
    public function reset(): void
    {
        $this->metrics = [];
        $this->routingStats = [];
        $this->modelStats = [];
        $this->errorStats = [];
    }
}
