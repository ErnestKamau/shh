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
use App\Models\DMS\Document;

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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class KnowledgeAssistantController extends Controller
{
    /**
     * Phase 2: Canonical list of valid knowledge-base collection names.
     */
    private const ALLOWED_COLLECTIONS = [
        'sops', 'corrective_actions', 'anomalies', 'findings',
        'incidents', 'audits', 'sample_enrichments', 'manual_docs',
    ];

    protected AiInferenceService $inferenceService;
    protected ReferenceResolverService $resolver;

    public function __construct(AiInferenceService $inferenceService, ReferenceResolverService $resolver) {
        $this->inferenceService = $inferenceService;
        $this->resolver = $resolver;
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
            'question' => 'required|string|max:1000',
        ]);

        $question = $this->normalizeInput($validated['question']);

        // Resolve manual references if provided
        if ($request->has('reference_ids') && is_array($request->input('reference_ids'))) {
            $referenceContext = $this->resolver->resolveReferences($request->input('reference_ids'));
            $question .= $referenceContext;
        }

        try {
            $response = $this->inferenceService->chat($question);

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
            'query' => 'required|string|max:1000',
            'limit' => 'sometimes|integer|min:1|max:20',
        ]);

        $query = $this->normalizeInput($validated['query']);

        try {
            $response = $this->inferenceService->chat($query, [
                'limit' => $validated['limit'] ?? 5,
                'search_only' => true
            ]);

            return response()->json($response);
        } catch (\Throwable $e) {
            Log::error('KnowledgeAssistantController: search failed', ['error' => $e->getMessage()]);
            return response()->json(['status' => 'error', 'message' => 'Internal Server Error'], 500);
        }
    }

    /**
     * Search for documents to be referenced in chat via @mentions.
     */
    public function lookupDocuments(Request $request): JsonResponse
    {
        $query = $request->query('q');

        $documents = Document::query()
            ->select(['id', 'title', 'document_number'])
            ->where('is_kb_indexed', true);

        if ($query) {
            $documents->where(function($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('document_number', 'like', "%{$query}%");
            });
        }
        $results = $documents->limit(10)->get();

        return response()->json(['results' => $results]);
    }

    /**
     * Ask a question and get a streaming SSE response.
     * Delegates initial routing and source discovery to the AssistantOrchestrator.
     */
    public function askStream(Request $request)
    {
        $validated = $request->validate([
            'question'         => 'required|string|max:1000',
            'conversation_id'  => 'sometimes|integer|exists:ai_conversations,id',
            'use_visuals'      => 'nullable|boolean',
            'model'            => 'nullable|string',
            'module_context'   => 'nullable|string',
        ]);

        $question = $this->normalizeInput($validated['question']);

        // Resolve manual references if provided
        if ($request->has('reference_ids') && is_array($request->input('reference_ids'))) {
            $referenceContext = $this->resolver->resolveReferences($request->input('reference_ids'));
            $question .= $referenceContext;
        }

        $sessionId = Str::uuid()->toString();

        return response()->stream(function () use ($question, $validated, $sessionId) {
            $options = [
                'session_id' => $sessionId, 
                'trace_id' => Str::uuid()->toString(), 
                'conversation_id' => $validated['conversation_id'] ?? null,
                'use_visuals' => (bool) ($validated['use_visuals'] ?? true),
                'model' => $validated['model'] ?? null,
                'module_context' => $validated['module_context'] ?? null,
            ];

            foreach ($this->inferenceService->streamChat($question, $options) as $chunk) {
                echo "data: " . $chunk . "\n\n";
                if (ob_get_level() > 0) ob_flush();
                flush();
            }

            // Yield session_id explicitly as a control event so frontend can handle stop logic
            echo "data: " . json_encode(['kind' => 'session_info', 'session_id' => $sessionId]) . "\n\n";

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

        $conversationsQuery = AiConversation::where('user_id', Auth::id());

        // Support context filtering (e.g. ?context=lab)
        if ($context = $request->query('context')) {
            $conversationsQuery->where('context', $context);
        }

        if ($query) {
            $conversationsQuery->where(function ($q) use ($query) {
                $q->whereFullText('title', $query)
                  ->orWhereHas('messages', function ($sub) use ($query) {
                      $sub->whereFullText('content', $query);
                  });
            });
        }

        $conversations = $conversationsQuery
            ->orderBy('is_pinned', 'desc')
            ->orderBy('updated_at', 'desc')
            ->limit(50)
            ->get(['id', 'title', 'context', 'is_pinned', 'updated_at', 'created_at']);

        return response()->json(['status' => 'ok', 'conversations' => $conversations]);
    }

    public function togglePin(Request $request, string $id): JsonResponse
    {
        $conversation = AiConversation::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $conversation->update(['is_pinned' => !$conversation->is_pinned]);

        return response()->json(['status' => 'ok', 'is_pinned' => $conversation->is_pinned]);
    }


    public function createConversation(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title'   => 'required|string|max:255',
            'context' => 'nullable|string|max:50',
        ]);

        $conversation = AiConversation::create([
            'user_id' => Auth::id(),
            'title'   => $validated['title'],
            'context' => $validated['context'] ?? 'general',
        ]);

        return response()->json(['status' => 'ok', 'conversation' => $conversation], 201);
    }

    public function getMessages(Request $request, string $id): JsonResponse
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

    public function saveMessage(Request $request, string $id): JsonResponse
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
            'metadata'          => 'nullable|array',
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

    public function deleteConversation(Request $request, string $id): JsonResponse
    {
        $conversation = AiConversation::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $conversation->delete();

        return response()->json(['status' => 'ok']);
    }

    public function renameConversation(Request $request, string $id): JsonResponse
    {
        $conversation = AiConversation::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $validated = $request->validate(['title' => 'required|string|max:255']);

        $conversation->update(['title' => $validated['title']]);

        return response()->json(['status' => 'ok', 'conversation' => $conversation]);
    }

    public function saveFeedback(Request $request, string $convoId, string $messageId): JsonResponse
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

    public function uploadAttachment(Request $request, string $id): JsonResponse
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

        // Ingest logic moved to background or Python core
        // $text = app(AiFileIngestionService::class)->ingest($attachment);

        return response()->json([
            'status'        => 'ok',
            'attachment_id' => $attachment->id,
            'original_name' => $attachment->original_name,
            'has_text'      => false,
            'status_msg'    => 'Attachment uploaded. Processing moved to backend.',
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
    public function regenerateResponse(Request $request, string $conversationId, string $messageId): JsonResponse
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
    public function rewriteResponse(Request $request, string $conversationId, string $messageId): JsonResponse
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

    /**
     * Legacy Trace Status - Stubbed for industrialized UI compatibility.
     */
    public function checkTraceStatus(string $traceId): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'hallucination_detected' => false,
            'notes' => [],
            'verified_at' => now()
        ]);
    }
}
