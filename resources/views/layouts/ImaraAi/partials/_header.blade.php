{{-- ── Header (hidden on desktop, shown on mobile) ─────────────────── --}}
<header class="ai-header" id="aiHeader">
    <div class="header-left">
        <button class="hamburger-btn" id="hamburgerBtn" aria-label="Toggle menu">
            <i class="mdi mdi-menu"></i>
        </button>
        <div class="header-brand">
            <i class="mdi mdi-head-lightbulb"></i>
            <span>ImaraChat AI</span>
        </div>
    </div>
    <div class="header-right">
        @if(Route::currentRouteName() === 'ai.knowledge.manager')
             <a href="{{ route('imara-ai') }}" class="header-btn" title="Back to Chat">
                <i class="mdi mdi-chat-outline"></i>
                <span>Chat</span>
            </a>
        @else
            <button class="header-btn" id="btnHeaderNewChat" title="New conversation">
                <i class="mdi mdi-plus"></i>
                <span>New</span>
            </button>
        @endif
    </div>
</header>
