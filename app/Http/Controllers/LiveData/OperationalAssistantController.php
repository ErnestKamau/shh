<?php

namespace App\Http\Controllers\LiveData;

use App\Http\Controllers\Controller;
use App\Services\LiveData\LiveDataQueryService;
use App\Services\LiveData\Support\IntentRegistry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller for direct operational data queries and telemetry.
 * Provides deterministic access to LIMS facts, bypassing the AI reasoning layer.
 */
class OperationalAssistantController extends Controller
{
    public function __construct(
        protected LiveDataQueryService $liveDataService,
        protected IntentRegistry $intentRegistry
    ) {}

    /**
     * List all registered operational intents.
     */
    public function listIntents(): JsonResponse
    {
        return response()->json([
            'status'  => 'ok',
            'intents' => $this->intentRegistry->getIntents(),
        ]);
    }

    /**
     * Execute a direct query via a known intent name.
     */
    public function query(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'intent'   => 'required|string',
            'question' => 'sometimes|string',
        ]);

        $result = $this->liveDataService->execute($validated['intent'], $validated['question'] ?? null);

        return response()->json([
            'status' => 'ok',
            'result' => [
                'intent'   => $result->intent,
                'reply'    => $result->reply,
                'value'    => $result->value,
                'metadata' => $result->metadata,
            ]
        ]);
    }

    /**
     * Smart operational search (Detect + Execute).
     */
    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'query' => 'required|string',
        ]);

        $result = $this->liveDataService->tryExecuteFromMessage($validated['query']);

        if (!$result) {
            return response()->json([
                'status'  => 'ok',
                'matched' => false,
                'message' => 'No operational intent detected for this query.',
            ]);
        }

        return response()->json([
            'status'  => 'ok',
            'matched' => true,
            'result'  => $result,
        ]);
    }
}
