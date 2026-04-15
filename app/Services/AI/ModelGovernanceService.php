<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * ModelGovernanceService
 * ======================
 * Provides model lifecycle governance operations:
 *   - getDriftStatus()      — reads the latest drift-detection results from DB
 *                             (written by the Python Celery drift task) and from
 *                             the FastAPI /v1/governance/drift endpoint.
 *   - triggerRetraining()   — calls FastAPI to enqueue an async Celery retraining
 *                             job for the requested model type.
 *   - getRetrainingHistory() — lists recent retraining events from ai.ai_retrain_log.
 *
 * The service communicates with the FastAPI inference service for operations that
 * require computation (retraining), and reads directly from the pgsql_ai database
 * for read-only queries (drift status, retrain history).
 */
class ModelGovernanceService
{
    /** Allowed model types for governance operations. */
    private const VALID_MODEL_TYPES = [
        'tat_prediction',
        'equipment_maintenance',
        'qc_anomaly',
    ];

    protected string $apiBaseUrl;
    protected string $connection = 'pgsql_ai';

    public function __construct()
    {
        // Use the same base URL as AiInferenceService so no extra config is needed.
        $this->apiBaseUrl = rtrim(
            config('imara_ai.api_base_url',
                env('AI_INFERENCE_URL', env('AI_API_URL', 'http://localhost:8001'))
            ),
            '/'
        );
    }

    // -------------------------------------------------------------------------
    // Drift status
    // -------------------------------------------------------------------------

    /**
     * Return the latest drift-detection report for all registered model types.
     *
     * Reads from ai.ai_model_drift_log (written by the daily Celery beat task).
     * Falls back to querying the FastAPI endpoint directly if the table is empty
     * or unavailable.
     *
     * @return array  Keys: checked_at, total_models, drifted, healthy, reports[]
     */
    public function getDriftStatus(): array
    {
        // ── Primary: read from DB (fast, no network hop) ──────────────────────
        try {
            $rows = DB::connection($this->connection)
                ->select("
                    SELECT DISTINCT ON (model_type)
                        model_type, checked_at, drift_detected, severity,
                        drifted_features, feature_scores, sample_size, error
                    FROM ai.ai_model_drift_log
                    ORDER BY model_type, checked_at DESC
                ");

            if (! empty($rows)) {
                $reports  = array_map(fn ($r) => (array) $r, $rows);
                $drifted  = count(array_filter($reports, fn ($r) => $r['drift_detected'] ?? false));
                return [
                    'source'        => 'database',
                    'checked_at'    => now()->toISOString(),
                    'total_models'  => count($reports),
                    'drifted'       => $drifted,
                    'healthy'       => count($reports) - $drifted,
                    'reports'       => $reports,
                ];
            }
        } catch (\Throwable $e) {
            Log::warning('ModelGovernanceService: drift DB read failed — falling back to FastAPI', [
                'error' => $e->getMessage(),
            ]);
        }

        // ── Fallback: live call to FastAPI ─────────────────────────────────────
        return $this->fetchDriftFromFastApi();
    }

    /**
     * Call the FastAPI /v1/governance/drift endpoint directly.
     * Used as fallback when the DB table is unavailable / empty.
     */
    public function fetchDriftFromFastApi(): array
    {
        try {
            $response = Http::timeout(10)->get("{$this->apiBaseUrl}/v1/governance/drift");

            if ($response->successful()) {
                $data           = $response->json();
                $data['source'] = 'fastapi_live';
                return $data;
            }

            Log::warning('ModelGovernanceService: FastAPI drift endpoint returned non-200', [
                'status' => $response->status(),
            ]);
        } catch (\Throwable $e) {
            Log::error('ModelGovernanceService: FastAPI drift call failed', [
                'error' => $e->getMessage(),
            ]);
        }

        // Terminal fallback — return empty structure so callers don't crash.
        return [
            'source'        => 'unavailable',
            'checked_at'    => now()->toISOString(),
            'total_models'  => 0,
            'drifted'       => 0,
            'healthy'       => 0,
            'reports'       => [],
            'error'         => 'Drift data unavailable — no DB records and FastAPI unreachable.',
        ];
    }

    // -------------------------------------------------------------------------
    // Retraining
    // -------------------------------------------------------------------------

    /**
     * Enqueue an asynchronous retraining job for the given model type.
     *
     * Delegates to the FastAPI governance endpoint which dispatches a Celery task.
     * The response includes a task_id that can be used to poll for completion.
     *
     * @param  string  $modelType    One of: tat_prediction | equipment_maintenance | qc_anomaly
     * @param  string  $triggeredBy  Identifier for audit log (e.g. user email or 'schedule')
     * @return array
     * @throws \InvalidArgumentException  If $modelType is not in the allowed list.
     */
    public function triggerRetraining(string $modelType, string $triggeredBy = 'manual'): array
    {
        if (! in_array($modelType, self::VALID_MODEL_TYPES, true)) {
            throw new \InvalidArgumentException(
                "Invalid model type '{$modelType}'. "
                . "Must be one of: " . implode(', ', self::VALID_MODEL_TYPES)
            );
        }

        try {
            $response = Http::timeout(30)->post(
                "{$this->apiBaseUrl}/v1/governance/retrain/{$modelType}",
                ['triggered_by' => $triggeredBy]
            );

            if ($response->successful()) {
                $result = $response->json();
                Log::info('ModelGovernanceService: retraining enqueued', [
                    'model_type'    => $modelType,
                    'triggered_by'  => $triggeredBy,
                    'task_id'       => $result['task_id'] ?? 'unknown',
                ]);
                return $result;
            }

            Log::warning('ModelGovernanceService: retrain request returned non-200', [
                'model_type' => $modelType,
                'status'     => $response->status(),
                'body'       => substr($response->body(), 0, 300),
            ]);

            return [
                'status'  => 'error',
                'message' => "FastAPI returned HTTP {$response->status()}",
            ];

        } catch (\Throwable $e) {
            Log::error('ModelGovernanceService: triggerRetraining failed', [
                'model_type' => $modelType,
                'error'      => $e->getMessage(),
            ]);
            return [
                'status'  => 'error',
                'message' => 'Retraining request failed: ' . $e->getMessage(),
            ];
        }
    }

    // -------------------------------------------------------------------------
    // Retraining history
    // -------------------------------------------------------------------------

    /**
     * Return recent retraining events from ai.ai_retrain_log.
     *
     * @param  int  $limit  Maximum number of records to return (default 20).
     * @return array
     */
    public function getRetrainingHistory(int $limit = 20): array
    {
        try {
            $rows = DB::connection($this->connection)
                ->table('ai.ai_retrain_log')
                ->select([
                    'id', 'model_type', 'triggered_by', 'status',
                    'new_version', 'metrics', 'error',
                    'started_at', 'completed_at', 'created_at',
                ])
                ->orderByDesc('created_at')
                ->limit($limit)
                ->get();

            return $rows->map(fn ($r) => (array) $r)->toArray();

        } catch (\Throwable $e) {
            Log::warning('ModelGovernanceService: getRetrainingHistory DB read failed', [
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }
}
