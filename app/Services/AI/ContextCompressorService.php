<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Log;

/**
 * ContextCompressorService
 * 
 * PHASE 1B SCAFFOLD: Future-proofing for long conversations
 * 
 * When a conversation exceeds a threshold (e.g., >15 turns),
 * this service will summarize older turns into a compact system message.
 * This prevents "context cliffs" where sliding window truncation suddenly
 * loses important context.
 * 
 * Currently disabled by default. Will be enabled in Phase 1b after
 * production monitoring shows need.
 */
class ContextCompressorService
{
    protected AiInferenceService $inferenceService;

    public function __construct(AiInferenceService $inferenceService)
    {
        $this->inferenceService = $inferenceService;
    }

    /**
     * Compress messages by summarizing older turns
     * 
     * If compression is disabled, returns messages unchanged (no-op).
     * Otherwise, if message count exceeds threshold, summarizes old turns.
     * 
     * @param array $messages Array of [['role' => '...', 'content' => '...'], ...]
     * @param int $targetTurns Target number of turns after compression
     * 
     * @return array Compressed messages array
     */
    public function compress(array $messages, int $targetTurns = 8): array
    {
        // PHASE 1: Feature-flagged, default disabled
        if (!config('ai.enable_context_compression', false)) {
            return $messages;
        }

        // Count non-system turns
        $turnCount = count(array_filter($messages, fn($m) => $m['role'] !== 'system'));

        // No need to compress if under threshold
        if ($turnCount <= $targetTurns) {
            return $messages;
        }

        // Calculate how many old turns to summarize
        $turnsToCompress = $turnCount - $targetTurns;

        Log::info('context_compression_triggered', [
            'total_turns' => $turnCount,
            'target_turns' => $targetTurns,
            'turns_to_compress' => $turnsToCompress,
        ]);

        try {
            return $this->performCompression($messages, $turnsToCompress);
        } catch (\Exception $e) {
            Log::warning('context_compression_failed', ['error' => $e->getMessage()]);
            // Graceful degradation: return uncompressed
            return $messages;
        }
    }

    /**
     * Perform actual compression by summarizing old turns
     * 
     * IMPLEMENTATION NOTES (Phase 1b):
     * 1. Extract oldest N turns (non-system messages)
     * 2. Call LLM with compression prompt
     * 3. Replace old turns with single summary message
     * 4. Preserve most recent turns unchanged
     * 
     * @param array $messages
     * @param int $turnsToCompress Number of turns to summarize
     * 
     * @return array Compressed messages
     */
    private function performCompression(array $messages, int $turnsToCompress): array
    {
        // Separate system message from others
        $systemMsg = null;
        $otherMsgs = [];

        foreach ($messages as $msg) {
            if ($msg['role'] === 'system') {
                $systemMsg = $msg;
            } else {
                $otherMsgs[] = $msg;
            }
        }

        // Extract turns to compress (oldest N)
        $turnsToCompressionPool = array_slice($otherMsgs, 0, $turnsToCompress);
        $recentTurns = array_slice($otherMsgs, $turnsToCompress);

        // Build compression prompt
        $compressionPrompt = $this->buildCompressionPrompt($turnsToCompressionPool);

        // Call LLM to summarize (use small model for speed)
        $summaryRequest = [
            ['role' => 'system', 'content' => 'You are a conversation summarizer. Extract key entities, decisions, and findings from the conversation below. Be concise (under 300 words).'],
            ['role' => 'user', 'content' => $compressionPrompt]
        ];

        try {
            // PHASE 1b: Implement this call
            // $summary = $this->inferenceService->generateCompletion(
            //     $summaryRequest,
            //     model: 'qwen2.5:1.5b',  // Use small model
            //     temperature: 0.3
            // );

            // For Phase 1: Scaffold only, not implemented
            Log::info('context_compression_scaffolded', [
                'turns_to_compress' => $turnsToCompress,
                'note' => 'Compression implementation deferred to Phase 1b'
            ]);

            // Return uncompressed (scaffold phase)
            return $messages;

        } catch (\Exception $e) {
            Log::error('context_compression_error', ['error' => $e->getMessage()]);
            // Graceful fallback
            return $messages;
        }
    }

    /**
     * Build the prompt for compression LLM
     * 
     * @param array $turnsToCompress
     * @return string
     */
    private function buildCompressionPrompt(array $turnsToCompress): string
    {
        $conversation = "CONVERSATION TO SUMMARIZE:\n\n";

        foreach ($turnsToCompress as $msg) {
            $role = $msg['role'] === 'assistant' ? 'Assistant' : 'User';
            $conversation .= "{$role}: {$msg['content']}\n\n";
        }

        return $conversation . "\nPLEASE SUMMARIZE KEY POINTS:\n";
    }

    /**
     * Get compression configuration
     * 
     * @return array
     */
    public static function getConfig(): array
    {
        return [
            'enabled' => config('ai.enable_context_compression', false),
            'threshold_turns' => config('ai.compression_threshold_turns', 15),
            'target_turns' => config('ai.compression_target_turns', 8),
            'model' => config('ai.compression_model', 'qwen2.5:1.5b'),
            'max_summary_tokens' => 300,
        ];
    }
}
