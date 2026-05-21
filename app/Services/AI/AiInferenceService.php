<?php

namespace App\Services\AI;

use App\Services\AI\Parts\AiChatService;
use Illuminate\Database\Eloquent\Model;

/**
 * AiInferenceService - Industrialized Gateway for Imara AI.
 * 
 * This service coordinates interactions between the Laravel backend and
 * the simplified Python AI service. It focuses exclusively on context-aware
 * chat and knowledge management.
 */
class AiInferenceService
{
    protected AiChatService $chat;

    public function __construct(AiChatService $chat) {
        $this->chat = $chat;
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
     * Simple chat retrieval (non-streaming).
     */
    public function chat(string $message, array $options = []): array
    {
        return $this->chat->chat($message, $options);
    }

    /**
     * Stream chat response with SSE.
     */
    public function streamChat($messages, array $options = [])
    {
        // Handle legacy string signature
        if (is_string($messages)) {
            $messages = [['role' => 'user', 'content' => $messages]];
        }

        return $this->chat->streamChat($messages, $options);
    }

    /**
     * Record interaction feedback.
     */
    public function recordFeedback(int $id, string $action, ?string $feedback = null): bool
    {
        return $this->chat->recordFeedback($id, $action, $feedback);
    }

    /**
     * Index knowledge content via the Python service.
     */
    public function indexKnowledge(array $data): array
    {
        return $this->chat->indexKnowledge($data);
    }

    public function deleteKnowledge(string $entityType, $entityId, int $companyId = 0): array
    {
        return $this->chat->deleteKnowledge($entityType, $entityId, $companyId);
    }

    /**
     * Test semantic search retrieval via the Python service.
     */
    public function searchKnowledge(string $query, array $options = []): array
    {
        return $this->chat->searchKnowledge($query, $options);
    }

    /**
     * Upload and index a file via the Python service.
     */
    public function uploadKnowledgeFile($file, array $data): array
    {
        return $this->chat->uploadKnowledgeFile($file, $data);
    }

    /**
     * Cancel running database queries for a given trace ID.
     */
    public function cancel(string $traceId): array
    {
        return $this->chat->cancel($traceId);
    }
}
