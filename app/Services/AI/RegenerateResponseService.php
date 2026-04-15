<?php

namespace App\Services\AI;

use App\Models\AiConversation;
use App\Models\AiMessage;
use Illuminate\Support\Facades\Log;

/**
 * RegenerateResponseService
 * 
 * Handles regeneration of responses (e.g., user asks for different answer)
 * 
 * Workflow:
 * 1. User clicks regenerate on message N
 * 2. Service rebuilds context up to message N-1
 * 3. Reruns inference with same routing/model
 * 4. Creates NEW message N+1 (doesn't overwrite original)
 * 5. Marks original message N as "superseded" (soft-delete)
 * 
 * Benefits:
 * - Preserves conversation history (can view original response)
 * - No data loss or overwrite conflicts
 * - Clean A/B testing (compare responses)
 * - Works with all model routing rules
 */
class RegenerateResponseService
{
    protected ConversationContextBuilder $contextBuilder;

    public function __construct(ConversationContextBuilder $contextBuilder)
    {
        $this->contextBuilder = $contextBuilder;
    }

    /**
     * Prepare regeneration by rebuilding context up to a specific message
     * 
     * @param int $conversationId
     * @param int $targetMessageId The message ID from which we're regenerating
     * @return array {
     *     'context_messages': array,        // Full message history up to target
     *     'last_user_prompt': string,       // The user query that generated targetMessageId
     *     'original_model_used': string|null,
     *     'regeneration_counter': int,
     *     'can_regenerate': bool,
     *     'reason': string|null,            // If !can_regenerate
     * }
     */
    public function buildRegenerationContext(
        int $conversationId,
        int $targetMessageId
    ): array {
        $conversation = AiConversation::find($conversationId);
        if (!$conversation) {
            return [
                'context_messages' => [],
                'last_user_prompt' => '',
                'original_model_used' => null,
                'regeneration_counter' => 0,
                'can_regenerate' => false,
                'reason' => 'Conversation not found',
            ];
        }

        // Fetch the target message
        $targetMessage = AiMessage::find($targetMessageId);
        if (!$targetMessage || $targetMessage->conversation_id !== $conversationId) {
            return [
                'context_messages' => [],
                'last_user_prompt' => '',
                'original_model_used' => null,
                'regeneration_counter' => 0,
                'can_regenerate' => false,
                'reason' => 'Target message not found or belongs to different conversation',
            ];
        }

        // Can only regenerate assistant responses
        if ($targetMessage->role !== 'assistant') {
            return [
                'context_messages' => [],
                'last_user_prompt' => '',
                'original_model_used' => null,
                'regeneration_counter' => 0,
                'can_regenerate' => false,
                'reason' => 'Can only regenerate assistant messages',
            ];
        }

        // Build context UP TO (but not including) the target message
        $contextMessages = $this->contextBuilder->buildContextUpTo($conversationId, $targetMessageId);

        // Find the user message that preceded this assistant message
        $precedingMessages = AiMessage::where('conversation_id', $conversationId)
            ->where('id', '<', $targetMessageId)
            ->where('role', 'user')
            ->orderByDesc('id')
            ->take(1)
            ->get();

        $lastUserPrompt = $precedingMessages->isNotEmpty()
            ? $precedingMessages[0]->content
            : '';

        // Count previous regenerations of this message
        $regenerationCounter = AiMessage::where('conversation_id', $conversationId)
            ->whereNotNull('supersedes_message_id')
            ->where('supersedes_message_id', $targetMessageId)
            ->count();

        // Safety check: prevent excessive regeneration
        $canRegenerate = $regenerationCounter < 5;
        $reason = !$canRegenerate ? 'Maximum regeneration attempts exceeded (5)' : null;

        return [
            'context_messages' => $contextMessages,
            'last_user_prompt' => $lastUserPrompt,
            'original_model_used' => $targetMessage->generation_metadata['model'] ?? null,
            'regeneration_counter' => $regenerationCounter,
            'can_regenerate' => $canRegenerate,
            'reason' => $reason,
        ];
    }

    /**
     * Create a regenerated response message
     * 
     * Call after inference has completed and you have the new assistant message
     * 
     * @param int $conversationId
     * @param int $originalMessageId The message being regenerated
     * @param string $newContent The regenerated response content
     * @param array $generationMetadata Inference metadata (model, latency, etc)
     * @return AiMessage The newly created message
     */
    public function recordRegeneration(
        int $conversationId,
        int $originalMessageId,
        string $newContent,
        array $generationMetadata = []
    ): AiMessage {
        $originalMessage = AiMessage::find($originalMessageId);
        if (!$originalMessage) {
            throw new \InvalidArgumentException("Original message {$originalMessageId} not found");
        }

        // Create new message that supersedes the original
        $newMessage = AiMessage::create([
            'conversation_id' => $conversationId,
            'role' => 'assistant',
            'content' => $newContent,
            'is_internal' => false,
            'supersedes_message_id' => $originalMessageId,
            'generation_metadata' => $generationMetadata,
            'generation_session_id' => $generationMetadata['generation_session_id'] ?? null,
            'message_type' => 'text',
        ]);

        // Log the regeneration
        Log::info('message_regenerated', [
            'conversation_id' => $conversationId,
            'original_message_id' => $originalMessageId,
            'new_message_id' => $newMessage->id,
            'model' => $generationMetadata['model'] ?? 'unknown',
            'regeneration_index' => $this->countRegenerations($conversationId, $originalMessageId),
        ]);

        return $newMessage;
    }

    /**
     * Count how many times a message has been regenerated
     * 
     * @param int $conversationId
     * @param int $originalMessageId
     * @return int Number of regenerations
     */
    public function countRegenerations(int $conversationId, int $originalMessageId): int
    {
        return AiMessage::where('conversation_id', $conversationId)
            ->where('supersedes_message_id', $originalMessageId)
            ->count();
    }

    /**
     * Get all regenerations of a message (for comparison UI)
     * 
     * @param int $originalMessageId
     * @return array Collection of {id, content, created_at, model}
     */
    public function getRegenerations(int $originalMessageId): array
    {
        $original = AiMessage::find($originalMessageId);
        if (!$original) {
            return [];
        }

        // Include the original
        $regenerations = [
            [
                'id' => $original->id,
                'content' => $original->content,
                'created_at' => $original->created_at,
                'model' => $original->generation_metadata['model'] ?? null,
                'is_original' => true,
                'regeneration_index' => 0,
            ],
        ];

        // Include all regenerations
        $superseding = AiMessage::where('supersedes_message_id', $originalMessageId)
            ->orderBy('id')
            ->get();

        foreach ($superseding as $index => $msg) {
            $regenerations[] = [
                'id' => $msg->id,
                'content' => $msg->content,
                'created_at' => $msg->created_at,
                'model' => $msg->generation_metadata['model'] ?? null,
                'is_original' => false,
                'regeneration_index' => $index + 1,
            ];
        }

        return $regenerations;
    }

    /**
     * Mark a message as "current" response (used in comparison UI)
     * 
     * When user selects one regeneration as the preferred answer
     * 
     * @param int $messageId
     * @return void
     */
    public function setAsCurrentResponse(int $messageId): void
    {
        $message = AiMessage::find($messageId);
        if (!$message) {
            return;
        }

        // Mark all superseding messages as not current
        AiMessage::where('supersedes_message_id', '!=', null)
            ->where('conversation_id', $message->conversation_id)
            ->update(['is_current_response' => false]);

        // Mark this one as current
        $message->update(['is_current_response' => true]);

        Log::info('current_response_updated', [
            'message_id' => $messageId,
            'conversation_id' => $message->conversation_id,
        ]);
    }

    /**
     * Get the current response for a position in conversation
     * 
     * If original message was regenerated, returns the "current" regeneration
     * Otherwise returns the original
     * 
     * @param int $conversationId
     * @param int $messageId
     * @return AiMessage|null
     */
    public function getCurrentResponse(int $conversationId, int $messageId): ?AiMessage
    {
        $original = AiMessage::find($messageId);
        if (!$original || $original->conversation_id !== $conversationId) {
            return null;
        }

        // Check if this was superseded
        $currentResponse = AiMessage::where('conversation_id', $conversationId)
            ->where('supersedes_message_id', $messageId)
            ->where('is_current_response', true)
            ->latest()
            ->first();

        // Return the explicitly marked current, or latest regeneration, or original
        return $currentResponse ?? $original;
    }

    /**
     * Get regeneration stats for analytics
     * 
     * @param int $conversationId
     * @return array {
     *     'total_regenerations': int,
     *     'messages_with_regenerations': int,
     *     'average_regenerations_per_message': float,
     * }
     */
    public function getStats(int $conversationId): array
    {
        $totalRegenerations = AiMessage::where('conversation_id', $conversationId)
            ->whereNotNull('supersedes_message_id')
            ->count();

        $messagesWithRegenerations = AiMessage::where('conversation_id', $conversationId)
            ->whereNotNull('supersedes_message_id')
            ->distinct('supersedes_message_id')
            ->count();

        $totalMessages = AiMessage::where('conversation_id', $conversationId)
            ->where('role', 'assistant')
            ->count();

        return [
            'total_regenerations' => $totalRegenerations,
            'messages_with_regenerations' => $messagesWithRegenerations,
            'average_regenerations_per_message' => $totalMessages > 0
                ? $messagesWithRegenerations / $totalMessages
                : 0,
        ];
    }
}
