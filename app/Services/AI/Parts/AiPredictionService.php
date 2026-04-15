<?php

namespace App\Services\AI\Parts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class AiPredictionService extends AiBaseService
{
    /**
     * Get a prediction for a given entity, using cache if available.
     */
    public function getPrediction(Model $entity, string $modelType, bool $forceFresh = false): array
    {
        if (!$forceFresh) {
            $cached = DB::connection($this->connection)
                ->table('ai.ai_predictions')
                ->where('entity_type', get_class($entity))
                ->where('entity_id', $entity->id)
                ->where('model_type', $modelType)
                ->where('is_valid', true)
                ->orderBy('created_at', 'desc')
                ->first();

            if ($cached) {
                return $this->formatResponse($cached);
            }
        }

        return $this->performInference($entity, $modelType);
    }

    /**
     * Call the FastAPI inference service and persist the result.
     */
    public function performInference(Model $entity, string $modelType): array
    {
        $startTime = microtime(true);
        $endpoint = $this->getEndpointMapping($modelType);
        
        try {
            $response = Http::timeout(10)
                ->post("{$this->apiBaseUrl}/v1/predict/{$endpoint}", [
                    'entity_id' => $entity->id,
                    'metadata' => $entity->toArray()
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $latency = (int)((microtime(true) - $startTime) * 1000);
                
                $id = $this->savePrediction($entity, $modelType, $data, $latency);
                $data['id'] = $id;

                return $data;
            }

            throw new \Exception("Inference failed: " . $response->status());
        } catch (\Throwable $e) {
            $this->log('error', "Prediction failed for {$modelType}", ['error' => $e->getMessage()]);
            return ['status' => 'error', 'message' => 'Prediction unavailable'];
        }
    }

    /**
     * Run predictions for multiple entities in a single batched call.
     */
    public function batchPredict(array $items, bool $includeReasoning = false): array
    {
        try {
            $response = Http::timeout(60)->post("{$this->apiBaseUrl}/v1/predict/batch", [
                'items' => $items,
                'include_reasoning' => $includeReasoning,
            ]);

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Throwable $e) {
            $this->log('error', 'Batch prediction failed', ['error' => $e->getMessage()]);
        }

        return ['status' => 'error', 'message' => 'Batch prediction unavailable'];
    }

    protected function savePrediction(Model $entity, string $modelType, array $data, int $latency): int
    {
        return DB::connection($this->connection)
            ->table('ai.ai_predictions')
            ->insertGetId([
                'model_type' => $modelType,
                'model_name' => $data['model_name'] ?? $modelType,
                'model_version' => $data['model_version'] ?? 'unknown',
                'entity_type' => get_class($entity),
                'entity_id' => $entity->id,
                'prediction_output' => json_encode($data['prediction'] ?? $data),
                'confidence' => $data['confidence'] ?? 0.0,
                'risk_level' => $data['risk_level'] ?? 'Low',
                'explanation' => json_encode($data['explanation'] ?? []),
                'recommendation' => $data['recommendation'] ?? null,
                'degraded_mode' => $data['degraded_mode'] ?? false,
                'latency_ms' => $latency,
                'is_valid' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
    }

    protected function getEndpointMapping(string $modelType): string
    {
        return [
            'tat_prediction' => 'tat',
            'equipment_maintenance' => 'maintenance'
        ][$modelType] ?? $modelType;
    }

    protected function formatResponse($cached): array
    {
        return [
            'id' => $cached->id,
            'prediction' => json_decode($cached->prediction_output, true),
            'confidence' => $cached->confidence,
            'risk_level' => $cached->risk_level,
            'explanation' => json_decode($cached->explanation, true),
            'recommendation' => $cached->recommendation,
            'degraded_mode' => (bool) ($cached->degraded_mode ?? false),
            'model_name' => $cached->model_name,
            'model_version' => $cached->model_version,
            'cached' => true,
            'created_at' => $cached->created_at
        ];
    }
}
