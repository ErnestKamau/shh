{{-- ── Sidebar ──────────────────────────────────────────────────────── --}}
<aside class="ai-sidebar" id="aiSidebar">
    <div class="sidebar-header">
        {{-- KB link removed and moved to settings sidebar --}}
        <div style="height: 10px;"></div>
    </div>

    @if(Route::currentRouteName() !== 'ai.knowledge.manager' && Route::currentRouteName() !== 'ai.settings')
        <div class="sidebar-action-wrap">
            <button class="btn-new-chat" id="btnNewChat">
                <i class="mdi mdi-plus"></i>
                <span>New conversation</span>
            </button>
            <button class="btn-select-toggle" id="btnSelectToggle" title="Select conversations to delete">
                <i class="mdi mdi-checkbox-multiple-outline"></i>
            </button>
        </div>

        <div class="bulk-delete-toolbar" id="bulkDeleteToolbar" style="display:none">
            <label class="bulk-select-all-wrap">
                <input type="checkbox" id="selectAllConvos"> Select all
            </label>
            <button class="btn-bulk-delete" id="btnBulkDelete" disabled>
                <i class="mdi mdi-trash-can-outline"></i> Delete
            </button>
        </div>
    @endif

    <div class="sidebar-section-label">Recent conversations</div>

    @if(Route::currentRouteName() !== 'ai.knowledge.manager' && Route::currentRouteName() !== 'ai.settings')
        <div class="sidebar-search-wrap" id="sidebarSearchWrap">
            <i class="mdi mdi-magnify sidebar-search-icon"></i>
            <input id="sidebarSearch" type="text" placeholder="Search…" autocomplete="off" spellcheck="false">
            <button id="sidebarSearchClear" class="sidebar-search-clear" aria-label="Clear search" style="display:none">
                <i class="mdi mdi-close-circle"></i>
            </button>
        </div>
    @endif

    <div class="sidebar-history" id="sidebarHistory">
        {{-- populated by JS --}}
    </div>

    <div class="sidebar-footer">
        <a href="{{ route('ai.settings.index') }}" class="sidebar-footer-link">
            <i class="mdi mdi-cog-outline"></i>
            <span>Settings</span>
        </a>
    </div>
</aside>
