<?php

namespace App\Services\AI\Orchestration;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Services\LiveData\LiveDataQueryService;
use App\Services\LiveData\DTOs\LiveDataResult;
use App\Services\AI\AiInferenceService;

/**
 * Knowledge & Operational Assistant Orchestrator.
 * Handles the high-level policy of routing between deterministic LiveData 
 * and probabilistic AI/RAG paths based on the selected mode.
 */
class AssistantOrchestrator
{
    public function __construct(
        protected LiveDataQueryService $liveDataService,
        protected AiInferenceService $inferenceService
    ) {}

    /**
     * Unified entry point for 'ask' queries.
     */
    public function ask(string $question, array $options = []): array
    {
        $mode = $options['mode'] ?? config('ai.orchestration_mode', 'balanced');
        $queryId = Str::uuid()->toString();

        // 1. LiveData Verification (Primary ONLY for live_data_only mode)
        if ($mode === 'live_data_only') {
            $liveResult = $this->liveDataService->tryExecuteFromMessage($question);
            
            if ($liveResult && $this->isSatisfactory($liveResult)) {
                $this->logLiveDataTrace($question, $liveResult);
                return $this->formatLiveDataResponse($liveResult, $question, $mode);
            }
            $this->logLiveDataTrace($question, new LiveDataResult('no_match', 'No result', null));
            return $this->formatLiveDataResponse(null, $question, $mode);
        }

        // 2. RAG/AI Pass-through
        Log::info('AssistantOrchestrator: Orchestrating AI fallback', [
            'query_id' => $queryId,
            'mode' => $mode,
            'routed_to' => 'python_ai',
            'php_prefetch_used' => false,
        ]);

        // If we reach here, it's either rag_only or balanced fallback.
        // We instruct the UI to begin an AI generation sequence without predefined sources.
        return $this->handleAIGeneration($question, [], $options);
    }

    /**
     * Orchestrate a streaming response.
     * Returns either a LiveData result (array) or an AI stream context.
     */
    public function orchestrateStream(string $question, array $options = []): array
    {
        $mode = $options['mode'] ?? config('ai.orchestration_mode', 'balanced');
        
        // 1. LiveData Prioritization (Primary ONLY for live_data_only mode)
        if ($mode === 'live_data_only') {
            $liveResult = $this->liveDataService->tryExecuteFromMessage($question);
            if ($liveResult && $this->isSatisfactory($liveResult)) {
                return [
                    'kind' => 'live_data',
                    'result' => $liveResult,
                ];
            }
        }

        // 2. Exact Match Failure handling
        if ($mode === 'live_data_only') {
            return [
                'kind' => 'no_match',
                'reply' => 'No operational intent matched.'
            ];
        }

        // 3. Delegation to Python Orchestrator
        Log::info('AssistantOrchestrator: Routing stream to Python AI', [
            'mode' => $mode,
            'routed_to' => 'python_ai',
            'php_prefetch_used' => false,
        ]);

        // Sending an 'ai_stream' with an empty sources array triggers Python's
        // native multi-hop retrieval loop.
        return [
            'kind' => 'ai_stream',
            'sources' => [], 
        ];
    }

    /**
     * Handle the generation of an AI response, optionally grounded in retrieved chunks.
     */
    protected function handleAIGeneration(string $question, array $chunks, array $options): array
    {
        $traceId = Str::uuid()->toString();
        
        $response = $this->inferenceService->chat($question, array_merge($options, [
            'sources' => $chunks,
            'trace_id' => $traceId,
        ]));

        return [
            'status' => 'ok',
            'kind' => 'ai_response',
            'reply' => $response['reply'] ?? 'I apologize, I was unable to generate a response.',
            'sources' => $chunks,
            'count' => count($chunks),
            'orchestration_mode' => $options['mode'] ?? 'balanced',
            'trace_id' => $traceId,
        ];
    }

    /**
     * Determine if a LiveData result is sufficient to stop the chain.
     */
    protected function isSatisfactory($result): bool
    {
        // For now, any successful match is satisfactory.
        // In the future, we could check for low-confidence or action-needed status.
        return $result && $result->reply !== 'I was unable to retrieve live data at this time. Please try again later.';
    }

    /**
     * Ensure the Frontend gets a consistent shape.
     */
    protected function formatLiveDataResponse($result, string $question, string $mode): array
    {
        if (!$result) {
            return [
                'status' => 'ok',
                'kind'   => 'no_match',
                'reply'  => "I couldn't find a specific operational answer for that.",
            ];
        }

        return [
            'status'     => 'ok',
            'kind'       => 'data_response',
            'mode'       => 'live_data',
            'orchestration_mode' => $mode,
            'question'   => $question,
            'count'      => 1,
            'message'    => 'Retrieved from live operational data.',
            'results'    => [
                [
                    'content'         => $result->reply,
                    'collection_name' => 'live_data',
                    'entity_type'     => 'operational_stats',
                    'metadata'        => $result->metadata,
                ],
            ],
        ];
    }

    /**
     * Log to the dedicated LiveData telemetry table.
     */
    protected function logLiveDataTrace(string $query, LiveDataResult $result): void
    {
        try {
            DB::table('ai_live_data_traces')->insert([
                'query_id' => Str::uuid(),
                'query' => $query,
                'user_id' => Auth::id(),
                'company_id' => Auth::user()?->company_id,
                'intent' => $result->intent,
                'handler_used' => 'dynamic', // Or $result->handler
                'orchestration_mode' => config('ai.orchestration_mode', 'balanced'),
                'fallback_to_ai' => false,
                'result_count' => $result->value !== null ? 1 : 0,
                'result_type' => 'data',
                'execution_time_ms' => defined('LARAVEL_START') ? (microtime(true) - LARAVEL_START) * 1000 : 0,
                'user_role' => Auth::user()?->role?->name ?? 'user',
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Orchestrator: Failed to log live data trace', ['error' => $e->getMessage()]);
        }
    }
}
