{{-- ── AI Settings Sidebar ──────────────────────────────────────────────────────── --}}
<aside class="ai-sidebar" id="aiSidebar">
    <div class="sidebar-header">
        @if(Route::is('ai.knowledge.editor'))
            <a href="{{ route('dms.active') }}" class="btn-knowledge-base" id="btnBackToKb" style="text-decoration: none; display: flex; align-items: center; gap: 10px; background: #2a2a2a; color: #efefef; border: 1px solid #333;">
                <div class="kb-icon-wrap" style="background: #1e1e1e;">
                    <i class="mdi mdi-arrow-left" style="color: #a72b2a;"></i>
                </div>
                <span>Back to Knowledge</span>
            </a>
        @else
            <a href="{{ route('imara-ai') }}" class="btn-knowledge-base" id="btnBackToChat" style="text-decoration: none; display: flex; align-items: center; gap: 10px; background: #2a2a2a; color: #efefef; border: 1px solid #333;">
                <div class="kb-icon-wrap" style="background: #1e1e1e;">
                    <i class="mdi mdi-arrow-left" style="color: #a72b2a;"></i>
                </div>
                <span>Back to Chat</span>
            </a>
        @endif
    </div>

    <div class="sidebar-section-label">AI Configuration</div>
    
    <div class="sidebar-history" style="padding-top: 4px;">
        {{-- Settings page removed; managed via DMS Active Documents --}}
    </div>

    <div class="sidebar-footer">
        <div class="sidebar-footer-link" style="cursor: default; opacity: 0.7;">
            <i class="mdi mdi-cog-outline"></i>
            <span>AI Settings Mode</span>
        </div>
    </div>
</aside>
