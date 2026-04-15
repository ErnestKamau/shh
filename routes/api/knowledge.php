<?php

/**
 * Knowledge Assistant API Routes
 * 
 * Conversational AI endpoints with Phase 2 response controls
 * 
 * Include in your routes/api.php:
 * require __DIR__ . '/api/knowledge.php';
 */

use App\Http\Controllers\AI\KnowledgeAssistantController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->prefix('conversations')->name('conversations.')->group(function () {
    // ── Basic conversation management ──────────────────────────────────────
    Route::get('/', [KnowledgeAssistantController::class, 'listConversations'])->name('list');
    Route::post('/', [KnowledgeAssistantController::class, 'createConversation'])->name('store');
    Route::get('{id}/messages', [KnowledgeAssistantController::class, 'getMessages'])->name('messages.get');

    // ── Chat endpoints ────────────────────────────────────────────────────
    // POST /api/conversations/ask - Single-turn Q&A (returns JSON)
    Route::post('/ask', [KnowledgeAssistantController::class, 'ask'])->name('ask');

    // POST /api/conversations/search - Retrieve only (no generation)
    Route::post('/search', [KnowledgeAssistantController::class, 'search'])->name('search');

    // GET /api/conversations/ask-stream - Server-Sent Events streaming (context-aware if conversation_id provided)
    Route::get('/ask-stream', [KnowledgeAssistantController::class, 'askStream'])->name('askStream');
    Route::post('/ask-stream', [KnowledgeAssistantController::class, 'askStream'])->name('askStream.post');

    // ── File/Attachment management ────────────────────────────────────────
    Route::post('{id}/attachments', [KnowledgeAssistantController::class, 'uploadAttachment'])->name('attachments.upload');

    // ── Phase 2: Response Control Endpoints ────────────────────────────────
    
    // POST /api/conversations/{sessionId}/stop - Stop an active stream
    Route::post('{sessionId}/stop', [KnowledgeAssistantController::class, 'stopStream'])
        ->name('stop')
        ->where('sessionId', '[0-9a-f\-]+');

    // POST /api/conversations/{conversationId}/messages/{messageId}/regenerate - Regenerate a response
    Route::post('{conversationId}/messages/{messageId}/regenerate', [KnowledgeAssistantController::class, 'regenerateResponse'])
        ->name('messages.regenerate')
        ->where('conversationId', '[0-9]+')
        ->where('messageId', '[0-9]+');

    // POST /api/conversations/{conversationId}/messages/{messageId}/rewrite - Rewrite a response
    Route::post('{conversationId}/messages/{messageId}/rewrite', [KnowledgeAssistantController::class, 'rewriteResponse'])
        ->name('messages.rewrite')
        ->where('conversationId', '[0-9]+')
        ->where('messageId', '[0-9]+');

    // ── Actions ───────────────────────────────────────────────────────────
    Route::post('actions/propose', [KnowledgeAssistantController::class, 'proposeAction'])->name('actions.propose');
    Route::post('actions/confirm', [KnowledgeAssistantController::class, 'confirmAction'])->name('actions.confirm');
});
