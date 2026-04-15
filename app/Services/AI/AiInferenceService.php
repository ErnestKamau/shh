<?php

namespace App\Services\AI;

use App\Services\AI\Parts\AiChatService;
use App\Services\AI\Parts\AiPredictionService;
use App\Services\AI\Parts\AiReasoningService;
use App\Services\AI\Parts\AiRegistryService;
use Illuminate\Database\Eloquent\Model;

/**
 * AiInferenceService - Gateway for the Modular AI Cluster.
 * 
 * This service coordinates interactions between the Laravel backend and
 * the modular FastAPI Python service. It delegates domain-specific logic
 * to specialized sub-services while maintaining a stable public API.
 */
class AiInferenceService
{
    protected AiChatService $chat;
    protected AiPredictionService $prediction;
    protected AiReasoningService $reasoning;
    protected AiRegistryService $registry;

    public function __construct(
        AiChatService $chat,
        AiPredictionService $prediction,
        AiReasoningService $reasoning,
        AiRegistryService $registry
    ) {
        $this->chat = $chat;
        $this->prediction = $prediction;
        $this->reasoning = $reasoning;
        $this->registry = $registry;
    }

    /**
     * Get an AI-specific setting from the database with fallbacks.
     */
    public function getSetting(string $key, $default = null)
    {
        return $this->chat->getSetting($key, $default);
    }

    /**
     * Get the master system default settings (from config).
     */
    public function getSystemDefaults(): array
    {
        return config('imara_ai.defaults', []);
    }

    /**
     * Sync models from local Ollama instance into the database registry.
     */
    public function syncLocalModels(): array
    {
        return $this->registry->syncLocalModels();
    }

    /**
     * Sync trained models (.joblib) from the local filesystem into the registry.
     */
    public function syncTrainedModels(): array
    {
        return $this->registry->syncTrainedModels();
    }

    /**
     * Get a prediction for a given entity, using cache if available.
     */
    public function getPrediction(Model $entity, string $modelType, bool $forceFresh = false): array
    {
        return $this->prediction->getPrediction($entity, $modelType, $forceFresh);
    }

    /**
     * Semantic intent classification.
     */
    public function classifyIntent(string $message): ?array
    {
        return $this->reasoning->classifyIntent($message);
    }

    /**
     * Ask FastAPI reasoning endpoints for natural-language interpretation.
     */
    public function reasonPrediction(string $kind, array $prediction, array $features = [], array $context = []): array
    {
        return $this->reasoning->reasonPrediction($kind, $prediction, $features, $context);
    }

    /**
     * Run predictions for multiple entities in a single batched call.
     */
    public function batchPredict(array $items, bool $includeReasoning = false): array
    {
        return $this->prediction->batchPredict($items, $includeReasoning);
    }

    /**
     * Stream chat response with SSE.
     */
    public function streamChat($messages, array $sources = [], array $options = [])
    {
        // Handle legacy string signature
        if (is_string($messages)) {
            $messages = [['role' => 'user', 'content' => $messages]];
        }

        return $this->chat->streamChat($messages, $sources, $options);
    }

    /**
     * Simple chat retrieval (non-streaming).
     */
    public function chat(string $message, array $options = []): array
    {
        return $this->chat->chat($message, $options);
    }

    /**
     * Proxy for recording feedback (Logic maintained for now)
     */
    public function recordFeedback(int $predictionId, string $action, ?string $feedback = null): bool
    {
        $userId = auth()->id();
        $hashedActor = $userId !== null
            ? hash_hmac('sha256', (string) $userId, config('app.key'))
            : null;

        return \Illuminate\Support\Facades\DB::connection('pgsql_ai')
            ->table('ai.ai_predictions')
            ->where('id', $predictionId)
            ->update([
                'user_action'  => $action,
                'user_feedback' => $feedback,
                'actor_id'     => $hashedActor,
                'action_at'    => now(),
                'updated_at'   => now(),
            ]) > 0;
    }
}
