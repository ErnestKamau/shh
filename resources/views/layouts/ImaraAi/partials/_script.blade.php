{{-- 
    Imara AI Chat Industrialized Script 
    Modularized for better maintainability.
--}}

{{-- Global State & Config --}}
@include('layouts.ImaraAi.partials.scripts._state')

{{-- Rendering Engine (Markdown, Charts, Sources, Message Bubbles) --}}
@include('layouts.ImaraAi.partials.scripts._rendering_engine')

{{-- Diagnostic Tool (Raw Stream Logging) --}}
@include('layouts.ImaraAi.partials.scripts._diagnostic')

{{-- UI Handlers (Sidebar, Modals, Resizing, Chips, Search UI) --}}
@include('layouts.ImaraAi.partials.scripts._ui_handlers')

{{-- Conversation Management (CRUD & History) --}}
@include('layouts.ImaraAi.partials.scripts._convo_manager')

{{-- API Client (Fetch Knowledge, Send Message, Streaming SSE Reader, Persistence) --}}
@include('layouts.ImaraAi.partials.scripts._api_client')

{{-- Voice I/O (TTS, STT) --}}
@include('layouts.ImaraAi.partials.scripts._voice_io')

{{-- File Attachments --}}
@include('layouts.ImaraAi.partials.scripts._attachments')

// ── Final Event Listeners & Initialization ──────────────────────────
sendButton?.addEventListener('click', () => sendMessage());

messageInput?.addEventListener('keydown', function (e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
    }
});

window.addEventListener('load', async function () {
    // Initial history load
    await loadConversationsFromBackend();
    
    // Setup initial view
    if (messageInput) {
        startNewConversation();
        messageInput.focus();
    }
    
    // Modal safety: move to body for proper rendering
    const deleteModal = document.getElementById('deleteConvoModal');
    if (deleteModal) {
        document.body.appendChild(deleteModal);
    }
    
    console.log('[Imara AI] Modularized script initialized.');
});
