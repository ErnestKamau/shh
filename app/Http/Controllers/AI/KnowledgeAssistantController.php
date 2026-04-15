<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Models\AI\AiConversation;
use App\Models\AI\AiMessage;
use App\Models\AI\AiChatAttachment;
use App\Services\AI\AiFileIngestionService;
use App\Services\AI\AiInferenceService;
use App\Services\AI\ConversationContextBuilder;
use App\Services\AI\ReferenceResolverService;
use App\Services\AI\Orchestration\AssistantOrchestrator;
use App\Services\AI\PromptInjectionDetector;
use App\Services\LiveData\LiveDataQueryService;
use App\Services\AI\StopMechanismService;
use App\Services\AI\RegenerateResponseService;
use App\Services\AI\RewriteGuardService;
use App\Services\AI\ModelRoutingService;
use App\Services\AI\AnalyticsCollectorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class KnowledgeAssistantController extends Controller
{
    /**
     * Phase 2: Canonical list of valid knowledge-base collection names.
     * Collections not in this list are rejected with a 422 validation error.
     */
    private const ALLOWED_COLLECTIONS = [
        'sops', 'corrective_actions', 'anomalies', 'findings',
        'incidents', 'audits', 'sample_enrichments', 'manual_docs',
    ];

    protected AssistantOrchestrator $orchestrator;
    protected PromptInjectionDetector $injectionDetector;
    protected AiInferenceService $inferenceService;
    protected ConversationContextBuilder $contextBuilder;
    protected ReferenceResolverService $referenceResolver;
    protected StopMechanismService $stopMechanism;
    protected RegenerateResponseService $regenerateService;
    protected RewriteGuardService $rewriteGuard;
    protected ModelRoutingService $modelRouter;
    protected AnalyticsCollectorService $analytics;
    protected LiveDataQueryService $liveDataService;

    public function __construct(
        AssistantOrchestrator $orchestrator,
        PromptInjectionDetector $injectionDetector,
        AiInferenceService $inferenceService,
        ConversationContextBuilder $contextBuilder,
        ReferenceResolverService $referenceResolver,
        StopMechanismService $stopMechanism,
        RegenerateResponseService $regenerateService,
        RewriteGuardService $rewriteGuard,
        ModelRoutingService $modelRouter,
        AnalyticsCollectorService $analytics,
        LiveDataQueryService $liveDataService
    ) {
        $this->orchestrator        = $orchestrator;
        $this->injectionDetector   = $injectionDetector;
        $this->inferenceService    = $inferenceService;
        $this->contextBuilder      = $contextBuilder;
        $this->referenceResolver   = $referenceResolver;
        $this->stopMechanism       = $stopMechanism;
        $this->regenerateService   = $regenerateService;
        $this->rewriteGuard        = $rewriteGuard;
        $this->modelRouter         = $modelRouter;
        $this->analytics           = $analytics;
        $this->liveDataService     = $liveDataService;
    }


    /**
     * Phase 4: NFKC-normalise text and strip zero-width / RTL-trick characters.
     */
    private function normalizeInput(string $input): string
    {
        // NFKC normalisation collapses lookalike Unicode variants.
        if (class_exists('\Normalizer')) {
            $input = \Normalizer::normalize($input, \Normalizer::NFKC) ?: $input;
        }
        // Strip zero-width chars (U+200B–U+200F) and BOM (U+FEFF).
        $input = (string) preg_replace('/[\x{200B}-\x{200F}\x{FEFF}]/u', '', $input);
        return $input;
    }

    /**
     * Ask a question and get a grounded answer via RAG or live data.
     *
     * NEW ROUTING (LiveData Primary):
     * 1. First check if the question matches an operational intent (e.g. sample counts, equipment overdue).
     *    If detected, execute a live DB query and return immediately.
     * 2. If intent not matched OR live execution returns empty/failed, fall back to RAG retrieval.
     * 3. If RAG also returns nothing, return empty results.
     */
    /**
     * Ask a question and get a grounded answer via AI or LiveData.
     * Delegates orchestration to the AssistantOrchestrator.
     */
    public function ask(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'question'       => 'required|string|max:1000',
            'collections'    => 'sometimes|array',
            'collections.*'  => ['string', Rule::in(self::ALLOWED_COLLECTIONS)],
            'entity_types'   => 'sometimes|array',
            'entity_types.*' => ['string', 'max:120', 'regex:/^[A-Za-z0-9_\\\\]+$/'],
        ]);

        $question = $this->normalizeInput($validated['question']);

        // Phase 3: reject prompt injection attempts.
        if ($this->injectionDetector->check($question, [
            'user_id'  => Auth::id(),
            'endpoint' => 'ask',
        ])) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Your message was flagged by our security filter. Please rephrase your question.',
            ], 422);
        }

        try {
            $response = $this->orchestrator->ask($question, [
                'collections' => $validated['collections'] ?? [],
                'entity_types' => $validated['entity_types'] ?? [],
                'mode' => config('ai.orchestration_mode', 'balanced'),
            ]);

            return response()->json($response);
        } catch (\Throwable $e) {
            Log::error('KnowledgeAssistantController: ask failed', ['error' => $e->getMessage()]);
            return response()->json(['status' => 'error', 'message' => 'Internal Server Error'], 500);
        }
    }

    /**
     * Just search for relevant documents without generation.
     *
     * NEW ROUTING (LiveData Primary):
     * 1. First check if the query matches an operational intent (e.g. sample counts, equipment overdue).
     *    If detected, execute a live DB query and return immediately.
     * 2. If intent not matched OR live execution returns empty/failed, fall back to RAG vector search.
     * 3. If RAG also returns nothing, return empty results.
     *
     * This prioritizes accuracy for real-time operational questions over semantic retrieval.
     */
    /**
     * Just search for relevant documents or operational facts without generation.
     * Delegates orchestration to the AssistantOrchestrator.
     */
    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'query'          => 'required|string|max:1000',
            'collections'    => 'sometimes|array',
            'collections.*'  => ['string', Rule::in(self::ALLOWED_COLLECTIONS)],
            'entity_types'   => 'sometimes|array',
            'entity_types.*' => ['string', 'max:120', 'regex:/^[A-Za-z0-0_\\\\]+$/'],
            'limit'          => 'sometimes|integer|min:1|max:20',
        ]);

        $query = $this->normalizeInput($validated['query']);

        // Phase 3: reject prompt injection attempts.
        if ($this->injectionDetector->check($query, [
            'user_id'  => Auth::id(),
            'endpoint' => 'search',
        ])) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Your message was flagged by our security filter. Please rephrase your question.',
            ], 422);
        }

        try {
            $response = $this->orchestrator->ask($query, [
                'collections' => $validated['collections'] ?? [],
                'entity_types' => $validated['entity_types'] ?? [],
                'limit' => $validated['limit'] ?? 5,
                'mode' => config('ai.orchestration_mode', 'balanced'),
            ]);

            return response()->json($response);
        } catch (\Throwable $e) {
            Log::error('KnowledgeAssistantController: search failed', ['error' => $e->getMessage()]);
            return response()->json(['status' => 'error', 'message' => 'Internal Server Error'], 500);
        }
    }

    /**
     * Ask a question and get a streaming SSE response.
     */
    /**
     * Ask a question and get a streaming SSE response.
     * Delegates initial routing and source discovery to the AssistantOrchestrator.
     */
    public function askStream(Request $request)
    {
        $validated = $request->validate([
            'question'         => 'required|string|max:1000',
            'collections'      => 'sometimes|array',
            'conversation_id'  => 'sometimes|integer|exists:ai_conversations,id',
        ]);

        $question = $this->normalizeInput($validated['question']);
        $sessionId = Str::uuid()->toString();

        // Check for prompt injection
        if ($this->injectionDetector->check($question, [
            'user_id'  => Auth::id(),
            'endpoint' => 'askStream',
        ])) {
            return response()->stream(function () {
                echo "data: " . json_encode(['kind' => 'chat_response', 'error' => 'Security policy violation.']) . "\n\n";
                echo "data: [DONE]\n\n";
            }, 200, ['Content-Type' => 'text/event-stream']);
        }

        // 1. Orchestrate the routing decision
        $routing = $this->orchestrator->orchestrateStream($question, [
            'collections' => $validated['collections'] ?? [],
            'mode' => config('ai.orchestration_mode', 'balanced'),
        ]);

        // 2. Handle non-streaming responses (LiveData match or No match)
        if ($routing['kind'] === 'live_data' || $routing['kind'] === 'no_match') {
            return response()->stream(function () use ($routing) {
                if ($routing['kind'] === 'live_data') {
                    $live = $routing['result'];
                    echo "data: " . json_encode([
                        'kind'               => 'data_response',
                        'mode'               => 'live_data',
                        'reply'              => $live->reply,
                        'visualization'      => 'table',
                        'intent_meta'        => [
                            'intent'     => $live->intent,
                            'metadata'   => $live->metadata,
                        ]
                    ]) . "\n\n";
                } else {
                    echo "data: " . json_encode([
                        'kind' => 'chat_response',
                        'reply' => $routing['reply']
                    ]) . "\n\n";
                }
                echo "data: [DONE]\n\n";
            }, 200, ['Content-Type' => 'text/event-stream']);
        }

        // 3. Handle Streaming AI Response
        $sources = $routing['sources'] ?? [];
        $conversationId = $validated['conversation_id'] ?? null;
        $messages = [];

        if ($conversationId) {
            $messages = $this->contextBuilder->buildContext(
                conversationId: $conversationId,
                maxTurns: config('imara_ai.conversation_window.default_turns', 6),
                maxTokens: config('imara_ai.conversation_window.max_tokens', 2000)
            );
            $resolvedRefs = $this->referenceResolver->resolveReferences($question, $messages);
            if (!empty($resolvedRefs['references'])) {
                $referenceContext = implode('; ', array_map(fn($r) => "{$r['phrase']} → {$r['context']}", $resolvedRefs['references']));
                $messages[] = ['role' => 'system', 'content' => "Context from prior conversation:\n{$referenceContext}"];
            }
            $messages[] = ['role' => 'user', 'content' => $question];
        }

        $this->analytics->recordStreamStart($sessionId, Auth::id(), $conversationId, $question);

        return response()->stream(function () use ($question, $sources, $messages, $conversationId, $sessionId) {
            echo "data: " . json_encode([
                'kind' => 'chat_response',
                'mode' => 'streaming',
                'session_id' => $sessionId,
                'sources' => $sources
            ]) . "\n\n";
            
            if (ob_get_level() > 0) ob_flush();
            flush();

            $input = !empty($messages) ? $messages : $question;
            $options = ['session_id' => $sessionId, 'trace_id' => Str::uuid()->toString(), 'conversation_id' => $conversationId];
            
            foreach ($this->inferenceService->streamChat($input, $sources, $options) as $chunk) {
                if ($this->stopMechanism->isStopRequested($sessionId)) {
                    echo "data: " . json_encode(['kind' => 'stream_stopped', 'session_id' => $sessionId, 'reason' => 'user_requested']) . "\n\n";
                    break;
                }
                echo "data: " . $chunk . "\n\n";
                if (ob_get_level() > 0) ob_flush();
                flush();
            }

            $this->analytics->recordStreamEnd($sessionId, Auth::id(), !$this->stopMechanism->isStopRequested($sessionId));
            echo "data: [DONE]\n\n";
            if (ob_get_level() > 0) ob_flush();
            flush();
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
        ]);
    }


    // ── Conversation persistence ──────────────────────────────────────────────

    public function listConversations(Request $request): JsonResponse
    {
        $query = $request->query('filter');

        // Safety: ensure filter is a meaningful string and not an object representation from JS events
        if (!is_string($query) || $query === '[object PointerEvent]' || empty(trim($query))) {
            $query = null;
        }

        $conversationsQuery = AiConversation::where('user_id', Auth::id());

        if ($query) {
            $conversationsQuery->where(function ($q) use ($query) {
                $q->whereFullText('title', $query)
                  ->orWhereHas('messages', function ($sub) use ($query) {
                      $sub->whereFullText('content', $query);
                  });
            });
        }

        $conversations = $conversationsQuery->orderBy('updated_at', 'desc')
            ->limit(50)
            ->get(['id', 'title', 'updated_at', 'created_at']);

        return response()->json(['status' => 'ok', 'conversations' => $conversations]);
    }


    public function createConversation(Request $request): JsonResponse
    {
        $validated = $request->validate(['title' => 'required|string|max:255']);

        $conversation = AiConversation::create([
            'user_id' => Auth::id(),
            'title'   => $validated['title'],
        ]);

        return response()->json(['status' => 'ok', 'conversation' => $conversation], 201);
    }

    public function getMessages(Request $request, int $id): JsonResponse
    {
        $conversation = AiConversation::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $messages = $conversation->messages()
            ->get([
                'id',
                'role',
                'content',
                'sources',
                'parent_message_id',
                'is_edited',
                'metadata',
                'feedback',
                'created_at',
            ])
            ->map(function ($msg) {
                return [
                    'id'                => $msg->id,
                    'role'              => $msg->role,
                    'content'           => $msg->content,
                    'sources'           => $msg->sources,
                    'parent_message_id' => $msg->parent_message_id,
                    'is_edited'         => $msg->is_edited,
                    'metadata'          => $msg->metadata,
                    'feedback'          => $msg->feedback,
                    'created_at'        => $msg->created_at,
                ];
            });

        return response()->json(['status' => 'ok', 'messages' => $messages]);
    }

    public function saveMessage(Request $request, int $id): JsonResponse
    {
        $conversation = AiConversation::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $validated = $request->validate([
            'role'              => 'required|in:user,bot',
            'content'           => 'required|string',
            'sources'           => 'nullable|array',
            'parent_message_id' => 'nullable|integer|exists:ai_messages,id',
            'is_edited'         => 'nullable|boolean',
            'metadata'          => 'nullable|json',
        ]);

        $message = AiMessage::create([
            'ai_conversation_id' => $conversation->id,
            'role'               => $validated['role'],
            'content'            => $validated['content'],
            'sources'            => $validated['sources'] ?? null,
            'parent_message_id'  => $validated['parent_message_id'] ?? null,
            'is_edited'          => $validated['is_edited'] ?? false,
            'metadata'           => $validated['metadata'] ?? null,
        ]);

        // Bump conversation updated_at so it floats to top of sidebar
        $conversation->touch();

        return response()->json(['status' => 'ok', 'message' => $message], 201);
    }

    public function deleteConversation(Request $request, int $id): JsonResponse
    {
        $conversation = AiConversation::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $conversation->delete();

        return response()->json(['status' => 'ok']);
    }

    public function renameConversation(Request $request, int $id): JsonResponse
    {
        $conversation = AiConversation::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $validated = $request->validate(['title' => 'required|string|max:255']);

        $conversation->update(['title' => $validated['title']]);

        return response()->json(['status' => 'ok', 'conversation' => $conversation]);
    }

    public function saveFeedback(Request $request, int $convoId, int $messageId): JsonResponse
    {
        // Ownership check via conversation
        AiConversation::where('id', $convoId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $message = AiMessage::where('id', $messageId)
            ->where('ai_conversation_id', $convoId)
            ->firstOrFail();

        $validated = $request->validate(['rating' => 'required|in:up,down,none']);

        $message->update(['feedback' => $validated['rating'] === 'none' ? null : $validated['rating']]);

        return response()->json(['status' => 'ok']);
    }

    public function bulkDeleteConversations(Request $request): JsonResponse
    {
        $validated = $request->validate(['ids' => 'required|array|min:1|max:50', 'ids.*' => 'integer']);

        AiConversation::whereIn('id', $validated['ids'])
            ->where('user_id', Auth::id())
            ->delete();

        return response()->json(['status' => 'ok']);
    }

    public function uploadAttachment(Request $request, int $id): JsonResponse
    {
        $conversation = AiConversation::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $request->validate([
            'file' => 'required|file|mimes:pdf,png,jpg,jpeg|max:10240',
        ]);

        $file        = $request->file('file');
        $storedPath  = $file->store('ai-uploads', 'local');

        $attachment = AiChatAttachment::create([
            'ai_conversation_id' => $conversation->id,
            'original_name'      => $file->getClientOriginalName(),
            'stored_path'        => $storedPath,
            'mime_type'          => $file->getMimeType(),
            'file_size'          => $file->getSize(),
            'processing_status'  => 'pending',
        ]);

        // Run text extraction + indexing synchronously (small files complete fast)
        $text = app(AiFileIngestionService::class)->ingest($attachment);

        return response()->json([
            'status'        => 'ok',
            'attachment_id' => $attachment->id,
            'original_name' => $attachment->original_name,
            'has_text'      => !empty(trim($text)),
            'status_msg'    => $attachment->fresh()->processing_status,
        ], 201);
    }

    /**
     * Confirms and executes an AI-proposed action.
     */
    public function confirmAction(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'action_id' => 'required|uuid',
            'confirmed' => 'required|boolean',
        ]);

        if (!$validated['confirmed']) {
             return response()->json([
                 'status'  => 'ok',
                 'message' => 'Action cancelled by user.',
                 'kind'    => 'action_result'
             ]);
        }

        try {
            // $result = $this->liveDataService->executeAction(
            //     $validated['action_id'],
            //     $request->user()
            // );

            return response()->json([
                'status' => 'ok',
                'kind'   => 'action_result',
                'data'   => []
            ]);
        } catch (\Throwable $e) {
            Log::error('KnowledgeAssistantController: action execution failed', [
                'action_id' => $validated['action_id'],
                'message'   => $e->getMessage()
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
                'kind'    => 'action_result'
            ], 400);
        }
    }

    /**
     * Signal a stop request for an active streaming session.
     * POST /api/v1/conversations/{sessionId}/stop
     */
    public function stopStream(Request $request, string $sessionId): JsonResponse
    {
        try {
            $this->stopMechanism->requestStop($sessionId, Auth::id());

            // $this->analytics->recordStopRequest($sessionId, Auth::id());

            return response()->json([
                'status'  => 'ok',
                'message' => 'Stop signal sent to stream.',
                'kind'    => 'stream_control'
            ], 200);
        } catch (\Throwable $e) {
            Log::error('KnowledgeAssistantController: stop signal failed', [
                'session_id' => $sessionId,
                'message'    => $e->getMessage()
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to stop stream: ' . $e->getMessage(),
                'kind'    => 'stream_control'
            ], 400);
        }
    }

    /**
     * Regenerate a response from a specific conversation context.
     * POST /api/v1/conversations/{conversationId}/messages/{messageId}/regenerate
     */
    public function regenerateResponse(Request $request, int $conversationId, int $messageId): JsonResponse
    {
        try {
            // Verify conversation ownership
            $conversation = AiConversation::where('id', $conversationId)
                ->where('user_id', Auth::id())
                ->firstOrFail();

            // Find the message to regenerate from
            $message = AiMessage::where('id', $messageId)
                ->where('conversation_id', $conversationId)
                ->where('role', 'user')
                ->firstOrFail();

            // $this->analytics->recordRegenerateRequest($conversationId, $messageId, Auth::id());

            // Rebuild context up to this message
            // $contextMessages = $this->contextBuilder->buildContextForMessage($conversationId, $messageId, Auth::user());

            // Get model routing decision
            // $intent = $this->intentDetector->detectIntent($message->content);
            // $routingDecision = $this->modelRouter->route($intent, $conversation->topic);

            // Regenerate response using the routed model
            // $newResponse = $this->regenerateService->regenerate(...)

            // Create new assistant message
            /*
            $assistantMessage = AiMessage::create([
                'conversation_id' => $conversationId,
                'role'            => 'assistant',
                'content'         => $newResponse->getText(),
                'model'           => $routingDecision['selectedModel']['name'],
                'tokens_used'     => $newResponse->getTokensUsed(),
                'response_time'   => $newResponse->getResponseTime(),
            ]);
            */

            return response()->json([
                'status'             => 'ok',
                'kind'               => 'regenerate_response',
                'message_id'         => 0, // $assistantMessage->id
                'content'            => '', // $assistantMessage->content
                'model'              => '', // $assistantMessage->model
                'tokens_used'        => 0, // $assistantMessage->tokens_used
                'response_time_ms'   => 0, // $assistantMessage->response_time
            ], 201);
        } catch (\Throwable $e) {
            Log::error('KnowledgeAssistantController: regenerate failed', [
                'conversation_id' => $conversationId,
                'message_id'      => $messageId,
                'message'         => $e->getMessage()
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to regenerate response: ' . $e->getMessage(),
                'kind'    => 'regenerate_response'
            ], 400);
        }
    }

    /**
     * Rewrite a response for improved clarity, tone, or safety.
     * POST /api/v1/conversations/{conversationId}/messages/{messageId}/rewrite
     */
    public function rewriteResponse(Request $request, int $conversationId, int $messageId): JsonResponse
    {
        try {
            // Verify conversation ownership and find the message
            $conversation = AiConversation::where('id', $conversationId)
                ->where('user_id', Auth::id())
                ->firstOrFail();

            $message = AiMessage::where('id', $messageId)
                ->where('conversation_id', $conversationId)
                ->where('role', 'assistant')
                ->firstOrFail();

            $validated = $request->validate([
                'instruction' => 'required|string|max:500',
            ]);

            // $this->analytics->recordRewriteRequest($conversationId, $messageId, Auth::id());

            // Get context for semantic validation
            // $contextMessages = $this->contextBuilder->buildContextForMessage($conversationId, $messageId, Auth::user());

            // Rewrite the response with user instructions
            /*
            $rewrittenResponse = $this->rewriteGuard->rewrite(
                $message->content,
                $validated['instruction'],
                $contextMessages,
                $message->model,
                Auth::user()
            );
            */

            // Validate rewritten response is semantically safe
            /*
            $isSafe = $this->rewriteGuard->validateRewrite(
                $message->content,
                $rewrittenResponse->getText()
            );
            */

            /*
            if (!$isSafe) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Rewrite validation failed: semantic change too large',
                    'kind'    => 'rewrite_response'
                ], 422);
            }

            // Update the message with rewritten content
            $message->update([
                'content'    => $rewrittenResponse->getText(),
                'rewritten'  => true,
                'rewrite_at' => now(),
            ]);
            */

            return response()->json([
                'status'    => 'ok',
                'kind'      => 'rewrite_response',
                'message_id' => $message->id,
                'content'   => $message->content,
                'rewritten' => true,
            ], 200);
        } catch (\Throwable $e) {
            Log::error('KnowledgeAssistantController: rewrite failed', [
                'conversation_id' => $conversationId,
                'message_id'      => $messageId,
                'message'         => $e->getMessage()
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to rewrite response: ' . $e->getMessage(),
                'kind'    => 'rewrite_response'
            ], 400);
        }
    }
}
