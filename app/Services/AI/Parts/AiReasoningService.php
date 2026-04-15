<?php

namespace App\Services\AI\Parts;

use Illuminate\Support\Facades\Http;

class AiReasoningService extends AiBaseService
{
    /**
     * Semantic intent classification via FastAPI /v1/classify.
     */
    public function classifyIntent(string $message): ?array
    {
        try {
            $response = Http::timeout(8)->post("{$this->apiBaseUrl}/v1/classify", [
                'message' => $message,
            ]);

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Throwable $e) {
            $this->log('error', 'classifyIntent failed', ['error' => $e->getMessage()]);
        }

        return null;
    }

    /**
     * Ask FastAPI reasoning endpoints for natural-language interpretation.
     */
    public function reasonPrediction(string $kind, array $prediction, array $features = [], array $context = []): array
    {
        $endpointMap = [
            'tat' => 'interpret_tat',
            'maintenance' => 'analyze_equipment',
            'qc' => 'explain_qc',
        ];

        $endpoint = $endpointMap[$kind] ?? null;
        if (!$endpoint) {
            return ['status' => 'error', 'message' => 'Unsupported reasoning kind'];
        }

        try {
            $response = Http::timeout(15)->post("{$this->apiBaseUrl}/v1/reason/{$endpoint}", [
                'kind' => $kind,
                'prediction' => $prediction,
                'features' => $features,
                'context' => $context,
            ]);

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Throwable $e) {
            $this->log('error', "reasonPrediction failed for {$kind}", ['error' => $e->getMessage()]);
        }

        return ['status' => 'error', 'message' => 'Reasoning unavailable'];
    }
}
