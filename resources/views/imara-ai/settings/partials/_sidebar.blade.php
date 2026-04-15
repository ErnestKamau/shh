{{-- ── AI Settings Sidebar ──────────────────────────────────────────────────────── --}}
<aside class="ai-sidebar" id="aiSidebar">
    <div class="sidebar-header">
        <a href="{{ route('imara-ai') }}" class="btn-knowledge-base" id="btnBackToChat" style="text-decoration: none; display: flex; align-items: center; gap: 10px; background: #2a2a2a; color: #efefef; border: 1px solid #333;">
            <div class="kb-icon-wrap" style="background: #1e1e1e;">
                <i class="mdi mdi-arrow-left" style="color: #a72b2a;"></i>
            </div>
            <span>Back to Chat</span>
        </a>
    </div>

    <div class="sidebar-section-label">AI Configuration</div>
    
    <div class="sidebar-history" style="padding-top: 4px;">
        <a href="{{ route('ai.settings.index') }}" class="history-item {{ Route::is('ai.settings.index') ? 'active' : '' }}" style="text-decoration: none;">
            <i class="mdi mdi-view-dashboard-outline"></i>
            <span class="history-label">General Settings</span>
        </a>
        
        <a href="{{ route('ai.knowledge.manager') }}" class="history-item {{ Route::is('ai.knowledge.manager') ? 'active' : '' }}" style="text-decoration: none;">
            <i class="mdi mdi-database-search"></i>
            <span class="history-label">Knowledge Base</span>
        </a>

        <a href="{{ route('ai.settings.agent-personality') }}" class="history-item {{ Route::is('ai.settings.agent-personality') ? 'active' : '' }}" style="text-decoration: none;">
            <i class="mdi mdi-robot-outline"></i>
            <span class="history-label">Agent Personality</span>
        </a>

        <a href="{{ route('ai.settings.model-config') }}" class="history-item {{ Route::is('ai.settings.model-config') ? 'active' : '' }}" style="text-decoration: none;">
            <i class="mdi mdi-tune-vertical"></i>
            <span class="history-label">Model Config</span>
        </a>

        <a href="{{ route('ai.governance.dashboard') }}" class="history-item {{ Route::is('ai.governance.dashboard') ? 'active' : '' }}" style="text-decoration: none;">
            <i class="mdi mdi-shield-check-outline"></i>
            <span class="history-label">Governance</span>
        </a>
    </div>

    <div class="sidebar-footer">
        <div class="sidebar-footer-link" style="cursor: default; opacity: 0.7;">
            <i class="mdi mdi-cog-outline"></i>
            <span>AI Settings Mode</span>
        </div>
    </div>
</aside>
