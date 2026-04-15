<?php

namespace App\Services\AI;

use App\Models\AI\AiConversation;
use App\Services\AI\AiInferenceService;
use App\Services\AI\AIFeatureService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * ConversationContextBuilder
 * 
 * Builds a sliding-window message history for multi-turn context awareness.
 * Formats conversations into a standard messages array with role assignment.
 * Enforces token budgets and removes duplicates.
 */
class ConversationContextBuilder
{
    protected $inferenceService;
    protected $featureService;

    public function __construct(AiInferenceService $inferenceService, AIFeatureService $featureService)
    {
        $this->inferenceService = $inferenceService;
        $this->featureService = $featureService;
    }

    /**
     * Build conversation context for a given conversation ID
     * 
     * Returns an array of messages formatted as:
     * [
     *   ['role' => 'system', 'content' => '...'],
     *   ['role' => 'user', 'content' => '...'],
     *   ['role' => 'assistant', 'content' => '...'],
     *   ...
     * ]
     * 
     * @param int $conversationId The conversation to build context from
     * @param int $maxTurns Maximum number of message turns (default: 6 = 3 user + 3 assistant pairs)
     * @param int $maxTokens Estimated max tokens for context (default: 2000)
     * @param bool $includeCompression Whether to check for compression (Phase 1b)
     * @return array Array of messages with role-based formatting
     */
    public function buildContext(
        int $conversationId,
        int $maxTurns = 6,
        int $maxTokens = 2000,
        bool $includeCompression = false
    ): array {
        try {
            $conversation = AiConversation::with('messages')
                ->findOrFail($conversationId);
            
            $allMessages = $conversation->messages()
                ->orderBy('created_at', 'asc')
                ->get(['id', 'role', 'content', 'created_at'])
                ->toArray();

            // Filter: Remove empty messages and duplicates
            $filtered = $this->filterMessages($allMessages);

            // Truncate to max turns
            $windowed = $this->applyWindowBoundary($filtered, $maxTurns);

            // Enforce token budget
            $truncated = $this->enforceTokenBudget($windowed, $maxTokens);

            // Check if messages were dropped
            if (count($truncated) < count($windowed)) {
                Log::info('context_truncated', [
                    'conversation_id' => $conversationId,
                    'dropped_messages' => count($windowed) - count($truncated),
                    'remaining' => count($truncated),
                    'token_budget' => $maxTokens,
                ]);
            }

            // 5. Selective Feature Grounding (Stage 10)
            $lastUserMessage = $this->getLastUserMessage($truncated);
            $groundingContext = $this->getGroundingContext($lastUserMessage);

            // Format into message array with system prompt
            $context = $this->formatMessageArray($truncated, $groundingContext);

            return $context;

        } catch (\Exception $e) {
            Log::error('ConversationContextBuilder: Failed to build context', [
                'conversation_id' => $conversationId,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Build context up to a specific message (used for regenerate feature)
     * 
     * Used when regenerating a response: rebuild the context as it was
     * at the time of the original message.
     * 
     * @param int $conversationId
     * @param int $beforeMessageId Messages after this ID are excluded
     * @param int $maxTurns
     * @param int $maxTokens
     * @return array
     */
    public function buildContextUpTo(
        int $conversationId,
        int $beforeMessageId,
        int $maxTurns = 6,
        int $maxTokens = 2000
    ): array {
        try {
            $conversation = AiConversation::findOrFail($conversationId);
            
            $allMessages = $conversation->messages()
                ->where('id', '<=', $beforeMessageId)
                ->orderBy('created_at', 'asc')
                ->get(['id', 'role', 'content', 'created_at'])
                ->toArray();

            $filtered = $this->filterMessages($allMessages);
            $windowed = $this->applyWindowBoundary($filtered, $maxTurns);
            $truncated = $this->enforceTokenBudget($windowed, $maxTokens);
            
            return $this->formatMessageArray($truncated);

        } catch (\Exception $e) {
            Log::error('ConversationContextBuilder: Failed to build context up to message', [
                'conversation_id' => $conversationId,
                'before_message_id' => $beforeMessageId,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Remove empty and duplicate messages
     * 
     * @param array $messages
     * @return array
     */
    private function filterMessages(array $messages): array
    {
        return array_values(
            array_filter(
                $messages,
                function ($msg) {
                    // Only keep messages with non-empty content
                    return !empty($msg['content']) && is_string($msg['content']);
                }
            )
        );
    }

    /**
     * Apply sliding window boundary (keep last N turns)
     * 
     * A "turn" is typically a user message + assistant response pair.
     * This keeps the most recent N turns.
     * 
     * @param array $messages
     * @param int $maxTurns
     * @return array
     */
    private function applyWindowBoundary(array $messages, int $maxTurns): array
    {
        if (count($messages) <= $maxTurns) {
            return $messages;
        }

        // Keep last N messages
        return array_slice($messages, -$maxTurns);
    }

    /**
     * Enforce token budget using character-based estimation
     * 
     * Approximate: 1 token ≈ 4 characters (rough estimate)
     * This is a conservative lower bound; actual tokenization may vary by model.
     * 
     * @param array $messages
     * @param int $maxTokens
     * @return array
     */
    private function enforceTokenBudget(array $messages, int $maxTokens): array
    {
        $estimatedTokens = 0;
        $charPerToken = 4;  // Conservative estimate

        $result = [];
        
        // Always preserve the last message (user question)
        $lastMessage = !empty($messages) ? array_pop($messages) : null;
        
        // Build context backwards from most recent
        foreach (array_reverse($messages) as $msg) {
            $msgTokens = ceil(strlen($msg['content'] ?? '') / $charPerToken);
            
            if ($estimatedTokens + $msgTokens <= $maxTokens) {
                array_unshift($result, $msg);
                $estimatedTokens += $msgTokens;
            } else {
                Log::debug('token_budget_limit_reached', [
                    'estimated_tokens' => $estimatedTokens,
                    'max_tokens' => $maxTokens,
                    'messages_dropped' => count($messages) - count($result)
                ]);
                break;
            }
        }
        
        // Add back the last message (user question)
        if ($lastMessage !== null) {
            $result[] = $lastMessage;
        }

        return $result;
    }

    /**
     * Format messages into standard role-based array format
     * 
     * @param array $messages
     * @param string|null $groundingContext Additional context to prepend to system prompt
     * @return array
     */
    private function formatMessageArray(array $messages, ?string $groundingContext = null): array
    {
        $systemPrompt = $this->buildSystemPrompt();
        
        if ($groundingContext) {
            $systemPrompt .= "\n\n### ADDITIONAL GROUNDING DATA (LIVEDATA)\n" . $groundingContext;
        }

        $formatted = [
            ['role' => 'system', 'content' => $systemPrompt]
        ];

        foreach ($messages as $msg) {
            $formatted[] = [
                'role' => $msg['role'] ?? 'user',
                'content' => $msg['content'] ?? ''
            ];
        }

        return $formatted;
    }

    /**
     * Determine intent and fetch relevant LiveData features
     */
    private function getGroundingContext(?string $query): ?string
    {
        if (!$query) return null;

        $query = Str::lower($query);
        $context = [];

        // 1. TAT / Sample intent
        if (Str::contains($query, ['tat', 'turnaround', 'delay', 'sample status', 'rework'])) {
            $samples = $this->featureService->getSampleFeatures(null, 10, true);
            if ($samples->isNotEmpty()) {
                $context[] = "Recent Rework Samples: " . $samples->map(fn($s) => "Sample #{$s->sample_id} status: {$s->status}")->implode(', ');
            }
        }

        // 2. Equipment intent
        if (Str::contains($query, ['equipment', 'instrument', 'maintenance', 'calibration', 'risk'])) {
            $equipment = $this->featureService->getHighRiskEquipment(0.6);
            if ($equipment->isNotEmpty()) {
                $context[] = "High Risk Equipment: " . $equipment->map(fn($e) => "{$e->equipment_id} (Risk: " . round($e->risk_score, 2) . ")")->implode(', ');
            }
        }

        // 3. QC intent
        if (Str::contains($query, ['qc', 'quality control', 'drift', 'outlier', 'violation'])) {
            $qc = $this->featureService->getQcFeatures(null, 5, true);
            if ($qc->isNotEmpty()) {
                $context[] = "Recent QC Outliers: " . $qc->map(fn($q) => "Analyte {$q->analyte_id} Z-Score: " . round($q->z_score, 2))->implode(', ');
            }
        }

        return !empty($context) ? implode("\n", $context) : null;
    }

    private function getLastUserMessage(array $messages): ?string
    {
        foreach (array_reverse($messages) as $msg) {
            if (($msg['role'] ?? 'user') === 'user') {
                return $msg['content'] ?? null;
            }
        }
        return null;
    }

    /**
     * Build the system prompt for the conversation context
     * 
     * @return string
     */
    private function buildSystemPrompt(): string
    {
        return $this->inferenceService->getSetting('system_prompt');
    }
}
