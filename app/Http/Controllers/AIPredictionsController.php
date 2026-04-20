<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\AI\AiEndpointResolver;

/**
 * Controller for AI-powered predictions.
 * Proxies requests to the FastAPI inference service.
 */
class AIPredictionsController extends Controller
{
    protected $inferenceApiUrl;

    public function __construct()
    {
        $this->inferenceApiUrl = AiEndpointResolver::resolve();
    }

    /**
     * Get TAT prediction for a sample.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function predictTAT(Request $request)
    {
        $validated = $request->validate([
            'sample_id' => 'required|integer',
            'metadata' => 'nullable|array',
        ]);

        try {
            $metadata = $validated['metadata'] ?? [];

            $response = Http::timeout(10)
                ->post("{$this->inferenceApiUrl}/v1/predict/tat", [
                    'entity_id' => $validated['sample_id'],
                    'metadata' => empty($metadata) ? (object) [] : $metadata,
                ])
                ->throw()
                ->json();

            return response()->json($response, 200);
        } catch (\Exception $e) {
            Log::error('AI TAT prediction failed', [
                'sample_id' => $validated['sample_id'],
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to fetch prediction',
                'message' => $e->getMessage(),
            ], 503);
        }
    }

    /**
     * Get equipment maintenance prediction.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function predictMaintenance(Request $request)
    {
        $validated = $request->validate([
            'equipment_id' => 'required|integer',
            'metadata' => 'nullable|array',
        ]);

        try {
            $metadata = $validated['metadata'] ?? [];

            $response = Http::timeout(10)
                ->post("{$this->inferenceApiUrl}/v1/predict/maintenance", [
                    'entity_id' => $validated['equipment_id'],
                    'metadata' => empty($metadata) ? (object) [] : $metadata,
                ])
                ->throw()
                ->json();

            return response()->json($response, 200);
        } catch (\Exception $e) {
            Log::error('AI maintenance prediction failed', [
                'equipment_id' => $validated['equipment_id'],
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to fetch prediction',
                'message' => $e->getMessage(),
            ], 503);
        }
    }

    /**
     * Health check for AI inference service.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function health()
    {
        try {
            $response = Http::timeout(5)
                ->get("{$this->inferenceApiUrl}/health")
                ->throw()
                ->json();

            return response()->json($response, 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'offline',
                'error' => $e->getMessage(),
            ], 503);
        }
    }
}
