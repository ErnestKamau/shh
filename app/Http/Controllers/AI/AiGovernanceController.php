<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Services\AI\AiInferenceService;
use App\Services\AI\PerformanceDashboardService;
use App\Services\AI\ModelGovernanceService;

class AiGovernanceController extends Controller
{
    protected AiInferenceService $aiService;
    protected PerformanceDashboardService $dashboard;
    protected ModelGovernanceService $governance;

    public function __construct(
        AiInferenceService $aiService,
        PerformanceDashboardService $dashboard,
        ModelGovernanceService $governance,
    ) {
        $this->aiService  = $aiService;
        $this->dashboard  = $dashboard;
        $this->governance = $governance;
    }

    /**
     * Record user feedback for a specific AI prediction.
     * Called after a clinician accepts, rejects, or ignores a recommendation.
     */
    public function recordFeedback(Request $request, $id)
    {
        $request->validate([
            'action'   => 'required|in:accepted,rejected,ignored',
            'feedback' => 'nullable|string|max:1000',
        ]);

        $success = $this->aiService->recordFeedback(
            (int) $id,
            $request->action,
            $request->feedback
        );

        if ($success) {
            return response()->json(['message' => 'Feedback recorded successfully']);
        }

        return response()->json(['message' => 'Could not record feedback'], 400);
    }

    /**
     * List all registered AI models (active by default; include_deprecated optional).
     */
    public function listModels(Request $request): JsonResponse
    {
        $includeDeprecated = (bool) $request->boolean('include_deprecated', false);

        $query = DB::connection('pgsql_ai')
            ->table('ai.ai_model_registry')
            ->select([
                'id',
                'model_name',
                'model_type',
                'version',
                'framework',
                'is_active',
                'is_deprecated',
                'deployed_at',
                'created_at',
                'metrics',
            ]);

        if (! $includeDeprecated) {
            $query->where('is_deprecated', false);
        }

        $models = $query
            ->orderBy('model_type')
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['models' => $models]);
    }

    /**
     * Return performance metrics and comparison dashboard for a single model.
     */
    public function modelPerformance(int $id): JsonResponse
    {
        $model = DB::connection('pgsql_ai')
            ->table('ai.ai_model_registry')
            ->where('id', $id)
            ->first();

        if (! $model) {
            return response()->json(['message' => 'Model not found'], 404);
        }

        return response()->json([
            'model'     => $model,
            'dashboard' => $this->dashboard->getModelComparison(),
        ]);
    }

    /**
     * Return the full ML governance dashboard payload (metrics + drift alerts).
     * Responds as JSON for API requests; renders the Blade view for browser requests.
     */
    public function mlDashboard()
    {
        $payload                = $this->dashboard->getMLModelMetrics();
        $payload['driftAlerts'] = $this->dashboard->getFeatureDriftAlerts();

        if (request()->wantsJson() || request()->is('api/*')) {
            return response()->json($payload);
        }

        return response()->view('ai.governance.dashboard', [
            'models'      => $payload['models']      ?? [],
            'performance' => $payload['performance']  ?? [],
            'alerts'      => $payload['alerts']       ?? [],
            'overview'    => $payload['overview']     ?? [],
            'driftAlerts' => $payload['driftAlerts']  ?? [],
        ]);
    }

    /**
     * Return the latest feature-drift report for all active model types.
     *
     * Reads from ai.ai_model_drift_log (written by the daily Celery beat task).
     * Falls back to calling the FastAPI /v1/governance/drift endpoint when the
     * table is empty or unavailable.
     *
     * GET /api/ai/governance/drift
     */
    public function driftStatus(Request $request): JsonResponse
    {
        $live = $request->boolean('live', false);

        $report = $live
            ? $this->governance->fetchDriftFromFastApi()
            : $this->governance->getDriftStatus();

        return response()->json($report);
    }

    /**
     * Trigger asynchronous retraining for a specific model type.
     *
     * Dispatches a Celery task via the FastAPI governance endpoint.
     * Returns a task_id that can be polled for completion status.
     *
     * POST /api/ai/governance/models/{modelType}/retrain
     * Allowed model types: tat_prediction | equipment_maintenance | qc_anomaly
     */
    public function triggerRetraining(Request $request, string $modelType): JsonResponse
    {
        $triggeredBy = auth()->user()?->email ?? 'api';

        try {
            $result = $this->governance->triggerRetraining($modelType, $triggeredBy);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        if (($result['status'] ?? '') === 'error') {
            return response()->json($result, 502);
        }

        return response()->json($result, 202);  // 202 Accepted — task is queued
    }

    /**
     * Expose AI telemetry in Prometheus text exposition format (v0.0.4).
     *
     * Intentionally unauthenticated — access is restricted by Docker network
     * policy; this endpoint is never reachable from the public Internet.
     *
     * GET /api/ai/metrics
     */
    public function prometheusMetrics(): \Illuminate\Http\Response
    {
        $body = $this->dashboard->exportMetricsPrometheus();

        return response($body, 200, [
            'Content-Type' => 'text/plain; version=0.0.4; charset=utf-8',
        ]);
    }
}
