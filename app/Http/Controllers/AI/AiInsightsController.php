<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Services\AI\AiInsightsOrchestrator;
use App\Services\AI\AiInferenceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiInsightsController extends Controller
{
    public function __construct(
        protected AiInsightsOrchestrator $orchestrator,
        protected AiInferenceService $inference,
    ) {
    }

    /**
     * Return AI-driven insights for a single laboratory sample (TAT prediction).
     */
    public function sample(Request $request, int $id): JsonResponse
    {
        $includeReasoning = filter_var($request->query('include_reasoning', true), FILTER_VALIDATE_BOOLEAN);
        return response()->json($this->orchestrator->getSampleInsights($id, $includeReasoning));
    }

    /**
     * Return AI-driven insights for a single piece of equipment (maintenance prediction).
     */
    public function equipment(Request $request, int $id): JsonResponse
    {
        $includeReasoning = filter_var($request->query('include_reasoning', true), FILTER_VALIDATE_BOOLEAN);
        return response()->json($this->orchestrator->getEquipmentInsights($id, $includeReasoning));
    }

    /**
     * Return AI-driven insights for a single QC result (anomaly detection).
     */
    public function qc(Request $request, int $id): JsonResponse
    {
        $includeReasoning = filter_var($request->query('include_reasoning', true), FILTER_VALIDATE_BOOLEAN);
        return response()->json($this->orchestrator->getQcInsights($id, $includeReasoning));
    }

    /**
     * Run predictions for multiple entities in a single batched request.
     *
     * Expected JSON body:
     *   {
     *     "items": [
     *       {"entity_id": 1, "model_type": "tat_prediction"},
     *       {"entity_id": 42, "model_type": "equipment_maintenance"},
     *       {"entity_id": 7,  "model_type": "qc_anomaly"}
     *     ],
     *     "include_reasoning": false
     *   }
     *
     * model_type must be one of: tat_prediction | equipment_maintenance | qc_anomaly
     * Maximum 50 items per request.
     */
    public function batch(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items'                  => 'required|array|min:1|max:50',
            'items.*.entity_id'      => 'required|integer|min:1',
            'items.*.model_type'     => 'required|string|in:tat_prediction,equipment_maintenance,qc_anomaly',
            'items.*.metadata'       => 'nullable|array',
            'include_reasoning'      => 'boolean',
        ]);

        $result = $this->inference->batchPredict(
            $validated['items'],
            (bool) ($validated['include_reasoning'] ?? false),
        );

        // Surface FastAPI-level errors as 502 rather than 200
        if (isset($result['status']) && $result['status'] === 'error') {
            return response()->json($result, 502);
        }

        return response()->json($result);
    }
}
