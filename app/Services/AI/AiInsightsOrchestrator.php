<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AiInsightsOrchestrator
{
    protected string $connection = 'pgsql_ai';

    public function __construct(
        protected AiInferenceService $inference,
        protected AnalyticsCollectorService $analytics,
        protected TraceIdCorrelationService $trace,
    ) {}

    public function getSampleInsights(int $sampleId, bool $includeReasoning = true): array
    {
        $traceId = $this->buildTraceId('sample', $sampleId);
        $features = $this->getLatestFeature('ai.ai_sample_features', 'sample_id', $sampleId);

        $prediction = $this->inference->predictByEntityId('tat_prediction', $sampleId, [
            'trace_id' => $traceId,
            'include_reasoning' => $includeReasoning,
        ]);

        $reasoning = $prediction['reasoning'] ?? null;
        if ($includeReasoning && ! $reasoning && ! isset($prediction['status'])) {
            $reasoningResult = $this->inference->reasonPrediction(
                'tat',
                $prediction,
                $features ?? [],
                []
            );
            $reasoning = $reasoningResult['reasoning'] ?? null;
        }

        $this->recordMetrics('tat_prediction', $prediction, $traceId);

        return [
            'entity_type' => 'sample',
            'entity_id' => $sampleId,
            'trace_id' => $traceId,
            'features' => $features,
            'prediction' => $prediction,
            'reasoning' => $reasoning,
        ];
    }

    public function getEquipmentInsights(int $equipmentId, bool $includeReasoning = true): array
    {
        $traceId = $this->buildTraceId('equipment', $equipmentId);
        $features = $this->getLatestFeature('ai.ai_equipment_features', 'equipment_id', $equipmentId);

        $prediction = $this->inference->predictByEntityId('equipment_maintenance', $equipmentId, [
            'trace_id' => $traceId,
            'include_reasoning' => $includeReasoning,
        ]);

        $reasoning = $prediction['reasoning'] ?? null;
        if ($includeReasoning && ! $reasoning && ! isset($prediction['status'])) {
            $reasoningResult = $this->inference->reasonPrediction(
                'maintenance',
                $prediction,
                $features ?? [],
                []
            );
            $reasoning = $reasoningResult['reasoning'] ?? null;
        }

        $this->recordMetrics('equipment_maintenance', $prediction, $traceId);

        return [
            'entity_type' => 'equipment',
            'entity_id' => $equipmentId,
            'trace_id' => $traceId,
            'features' => $features,
            'prediction' => $prediction,
            'reasoning' => $reasoning,
        ];
    }

    public function getQcInsights(int $qcResultId, bool $includeReasoning = true): array
    {
        $traceId = $this->buildTraceId('qc', $qcResultId);
        $features = $this->getLatestFeature('ai.ai_qc_features', 'qc_result_id', $qcResultId);

        $prediction = [
            'prediction' => [
                'outlier_flag' => (bool) ($features['outlier_flag'] ?? false),
                'westgard_violation' => (bool) ($features['westgard_violation'] ?? false),
            ],
            'confidence' => 0.65,
            'risk_level' => ((bool) ($features['outlier_flag'] ?? false) || (bool) ($features['westgard_violation'] ?? false)) ? 'high' : 'low',
            'explanation' => [
                'QC risk is inferred from current engineered QC features.',
            ],
            'model_name' => 'qc_rules_fallback',
            'model_version' => 'rules-1.0',
        ];

        $reasoning = null;
        if ($includeReasoning) {
            $reasoningResult = $this->inference->reasonPrediction(
                'qc',
                $prediction,
                $features ?? [],
                []
            );
            $reasoning = $reasoningResult['reasoning'] ?? null;
        }

        $this->recordMetrics('qc_anomaly', $prediction, $traceId);

        return [
            'entity_type' => 'qc',
            'entity_id' => $qcResultId,
            'trace_id' => $traceId,
            'features' => $features,
            'prediction' => $prediction,
            'reasoning' => $reasoning,
        ];
    }

    protected function getLatestFeature(string $table, string $idColumn, int $entityId): ?array
    {
        $row = DB::connection($this->connection)
            ->table($table)
            ->where($idColumn, $entityId)
            ->orderByDesc('snapshot_id')
            ->orderByDesc('created_at')
            ->first();

        return $row ? (array) $row : null;
    }

    protected function buildTraceId(string $entityType, int $entityId): string
    {
        try {
            $context = $this->trace->generateTraceContext();
            return (string) ($context['trace_id'] ?? sprintf('ai-%s-%d-%s', $entityType, $entityId, now()->format('YmdHisv')));
        } catch (\Throwable $e) {
            Log::debug('AiInsightsOrchestrator: trace service fallback', ['error' => $e->getMessage()]);
            return sprintf('ai-%s-%d-%s', $entityType, $entityId, now()->format('YmdHisv'));
        }
    }

    protected function recordMetrics(string $modelType, array $prediction, string $traceId): void
    {
        try {
            $this->analytics->recordModelPerformance(
                model: $modelType,
                latencyMs: (int) ($prediction['latency_ms'] ?? 0),
                tokensUsed: 0,
                success: ! isset($prediction['status']),
                error: isset($prediction['status']) ? (string) ($prediction['message'] ?? 'prediction_error') : null,
            );
        } catch (\Throwable $e) {
            Log::debug('AiInsightsOrchestrator: analytics record failed', ['error' => $e->getMessage()]);
        }
    }
}
