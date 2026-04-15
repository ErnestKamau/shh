<?php

namespace App\Services\AI;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * AIFeatureService
 *
 * Provides Laravel-side access to engineered features stored in the
 * PostgreSQL ai schema (pgsql_ai connection).
 *
 * Two access methods are available:
 *   1. Direct DB queries  — fast, synchronous, no API overhead
 *   2. FastAPI HTTP calls — decoupled, useful when PHP cannot reach Postgres
 *
 * ⚠️ No computation is performed in this class.
 *    All features are pre-computed by Python and stored in ai.* tables.
 */
class AIFeatureService
{
    /** @var string Laravel database connection name */
    protected string $connection = 'pgsql_ai';

    /** @var string FastAPI base URL (for HTTP-based access) */
    protected string $apiBaseUrl;

    public function __construct()
    {
        $this->apiBaseUrl = config('imara_ai.api_base_url', 'http://127.0.0.1:8081');
    }

    // =========================================================================
    // SNAPSHOTS
    // =========================================================================

    /**
     * Get the most recent feature snapshot.
     */
    public function getLatestSnapshot(): ?array
    {
        try {
            $row = DB::connection($this->connection)
                ->table('ai.ai_feature_snapshots')
                ->orderByDesc('snapshot_time')
                ->first();

            return $row ? (array) $row : null;
        } catch (\Throwable $e) {
            Log::warning('AIFeatureService: getLatestSnapshot failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Get all feature snapshots.
     */
    public function getAllSnapshots(int $limit = 50): Collection
    {
        try {
            return DB::connection($this->connection)
                ->table('ai.ai_feature_snapshots')
                ->orderByDesc('snapshot_time')
                ->limit($limit)
                ->get();
        } catch (\Throwable $e) {
            Log::warning('AIFeatureService: getAllSnapshots failed', ['error' => $e->getMessage()]);
            return collect();
        }
    }

    // =========================================================================
    // SAMPLE FEATURES
    // =========================================================================

    /**
     * Get engineered sample features.
     *
     * @param int|null $snapshotId  Uses latest snapshot if omitted
     * @param int      $limit       Max rows to return
     * @param bool     $reworkOnly  Filter to rework/repeat samples
     */
    public function getSampleFeatures(
        ?int $snapshotId = null,
        int $limit = 100,
        bool $reworkOnly = false
    ): Collection {
        try {
            $snapshotId = $snapshotId ?? $this->resolveLatestSnapshotId();
            if (! $snapshotId) {
                return collect();
            }

            $query = DB::connection($this->connection)
                ->table('ai.ai_sample_features')
                ->where('snapshot_id', $snapshotId)
                ->orderByDesc('created_at')
                ->limit($limit);

            if ($reworkOnly) {
                $query->where('rework_flag', true);
            }

            return $query->get();
        } catch (\Throwable $e) {
            Log::warning('AIFeatureService: getSampleFeatures failed', ['error' => $e->getMessage()]);
            return collect();
        }
    }

    /**
     * Get feature record for a specific sample.
     */
    public function getSampleFeature(int $sampleId, ?int $snapshotId = null): ?object
    {
        try {
            $snapshotId = $snapshotId ?? $this->resolveLatestSnapshotId();
            if (! $snapshotId) {
                return null;
            }

            return DB::connection($this->connection)
                ->table('ai.ai_sample_features')
                ->where('sample_id', $sampleId)
                ->where('snapshot_id', $snapshotId)
                ->first();
        } catch (\Throwable $e) {
            Log::warning('AIFeatureService: getSampleFeature failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    // =========================================================================
    // EQUIPMENT FEATURES
    // =========================================================================

    /**
     * Get engineered equipment features.
     *
     * @param int|null  $snapshotId  Uses latest snapshot if omitted
     * @param int       $limit       Max rows to return
     * @param float|null $minRisk    Minimum risk score (0.0–1.0)
     * @param bool      $overdueOnly Filter to overdue-maintenance equipment
     */
    public function getEquipmentFeatures(
        ?int $snapshotId = null,
        int $limit = 100,
        ?float $minRisk = null,
        bool $overdueOnly = false
    ): Collection {
        try {
            $snapshotId = $snapshotId ?? $this->resolveLatestSnapshotId();
            if (! $snapshotId) {
                return collect();
            }

            $query = DB::connection($this->connection)
                ->table('ai.ai_equipment_features')
                ->where('snapshot_id', $snapshotId)
                ->orderByRaw('risk_score DESC NULLS LAST')
                ->limit($limit);

            if ($minRisk !== null) {
                $query->where('risk_score', '>=', $minRisk);
            }

            if ($overdueOnly) {
                $query->where('days_until_due', '<', 0);
            }

            return $query->get();
        } catch (\Throwable $e) {
            Log::warning('AIFeatureService: getEquipmentFeatures failed', ['error' => $e->getMessage()]);
            return collect();
        }
    }

    /**
     * Get feature record for a specific equipment item.
     */
    public function getEquipmentFeature(int $equipmentId, ?int $snapshotId = null): ?object
    {
        try {
            $snapshotId = $snapshotId ?? $this->resolveLatestSnapshotId();
            if (! $snapshotId) {
                return null;
            }

            return DB::connection($this->connection)
                ->table('ai.ai_equipment_features')
                ->where('equipment_id', $equipmentId)
                ->where('snapshot_id', $snapshotId)
                ->first();
        } catch (\Throwable $e) {
            Log::warning('AIFeatureService: getEquipmentFeature failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Get high-risk equipment (risk_score >= threshold).
     */
    public function getHighRiskEquipment(float $threshold = 0.7, ?int $snapshotId = null): Collection
    {
        return $this->getEquipmentFeatures(
            snapshotId: $snapshotId,
            minRisk: $threshold,
            limit: 50
        );
    }

    // =========================================================================
    // QC FEATURES
    // =========================================================================

    /**
     * Get engineered QC features.
     */
    public function getQcFeatures(
        ?int $snapshotId = null,
        int $limit = 100,
        bool $outliersOnly = false,
        bool $violationsOnly = false,
        ?int $analyteId = null
    ): Collection {
        try {
            $snapshotId = $snapshotId ?? $this->resolveLatestSnapshotId();
            if (! $snapshotId) {
                return collect();
            }

            $query = DB::connection($this->connection)
                ->table('ai.ai_qc_features')
                ->where('snapshot_id', $snapshotId)
                ->orderByRaw('ABS(z_score) DESC NULLS LAST')
                ->limit($limit);

            if ($outliersOnly) {
                $query->where('outlier_flag', true);
            }

            if ($violationsOnly) {
                $query->where('westgard_violation', true);
            }

            if ($analyteId !== null) {
                $query->where('analyte_id', $analyteId);
            }

            return $query->get();
        } catch (\Throwable $e) {
            Log::warning('AIFeatureService: getQcFeatures failed', ['error' => $e->getMessage()]);
            return collect();
        }
    }

    // =========================================================================
    // RISK SUMMARY (combined for dashboard widgets)
    // =========================================================================

    /**
     * Get consolidated risk summary for dashboard display.
     *
     * Returns rework samples, high-risk equipment, and QC violations
     * from the latest snapshot, ready for the Laravel UI to render.
     */
    public function getRiskSummary(?int $snapshotId = null): array
    {
        $snapshotId = $snapshotId ?? $this->resolveLatestSnapshotId();

        return [
            'snapshot_id'       => $snapshotId,
            'rework_samples'    => $this->getSampleFeatures($snapshotId, 20, reworkOnly: true),
            'high_risk_equipment' => $this->getHighRiskEquipment(0.7, $snapshotId),
            'qc_violations'     => $this->getQcFeatures($snapshotId, 20, violationsOnly: true),
        ];
    }

    /**
     * Get counts summary for dashboard KPI tiles.
     */
    public function getFeatureCounts(?int $snapshotId = null): array
    {
        $snapshotId = $snapshotId ?? $this->resolveLatestSnapshotId();
        if (! $snapshotId) {
            return ['sample' => 0, 'equipment' => 0, 'qc' => 0, 'rework' => 0, 'high_risk_equipment' => 0, 'qc_violations' => 0];
        }

        try {
            $db = DB::connection($this->connection);

            return [
                'sample'               => $db->table('ai.ai_sample_features')->where('snapshot_id', $snapshotId)->count(),
                'equipment'            => $db->table('ai.ai_equipment_features')->where('snapshot_id', $snapshotId)->count(),
                'qc'                   => $db->table('ai.ai_qc_features')->where('snapshot_id', $snapshotId)->count(),
                'rework'               => $db->table('ai.ai_sample_features')->where('snapshot_id', $snapshotId)->where('rework_flag', true)->count(),
                'high_risk_equipment'  => $db->table('ai.ai_equipment_features')->where('snapshot_id', $snapshotId)->where('risk_score', '>=', 0.7)->count(),
                'qc_violations'        => $db->table('ai.ai_qc_features')->where('snapshot_id', $snapshotId)->where('westgard_violation', true)->count(),
            ];
        } catch (\Throwable $e) {
            Log::warning('AIFeatureService: getFeatureCounts failed', ['error' => $e->getMessage()]);
            return [];
        }
    }

    // =========================================================================
    // REMOTE TRIGGER (calls FastAPI to start feature engineering)
    // =========================================================================

    /**
     * Trigger feature engineering via the FastAPI endpoint.
     *
     * Returns a Celery task ID for status polling.
     *
     * @param string $pipelines Comma-separated: "all" | "sample,equipment,qc"
     */
    public function triggerFeatureEngineering(string $pipelines = 'all'): array
    {
        try {
            $response = Http::timeout(10)
                ->post("{$this->apiBaseUrl}/ai/features/engineer?pipelines={$pipelines}");

            if ($response->successful()) {
                return $response->json();
            }

            return ['status' => 'error', 'message' => $response->body()];
        } catch (\Throwable $e) {
            Log::error('AIFeatureService: triggerFeatureEngineering failed', ['error' => $e->getMessage()]);
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Check the status of a feature engineering Celery task.
     */
    public function getFeatureEngineeringStatus(string $taskId): array
    {
        try {
            $response = Http::timeout(5)
                ->get("{$this->apiBaseUrl}/ai/features/status/{$taskId}");

            return $response->successful() ? $response->json() : ['status' => 'unknown'];
        } catch (\Throwable $e) {
            return ['status' => 'unknown', 'error' => $e->getMessage()];
        }
    }

    /**
     * Submit feedback on a prediction for continuous model improvement.
     */
    public function submitFeedback(int $predictionId, float $actualValue, ?string $feedback = null): array
    {
        try {
            $response = Http::timeout(5)->post("{$this->apiBaseUrl}/ai/feedback", [
                'prediction_id' => $predictionId,
                'actual_value'  => $actualValue,
                'user_feedback' => $feedback,
                'feedback_type' => 'correct',
            ]);

            return $response->successful() ? $response->json() : ['status' => 'error'];
        } catch (\Throwable $e) {
            Log::warning('AIFeatureService: submitFeedback failed', ['error' => $e->getMessage()]);
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    // =========================================================================
    // INTERNAL HELPERS
    // =========================================================================

    protected function resolveLatestSnapshotId(): ?int
    {
        $snapshot = $this->getLatestSnapshot();
        return $snapshot ? (int) $snapshot['id'] : null;
    }
}
