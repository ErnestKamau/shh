/* Global resets removed to prevent breaking host pages. 
   These are now handled in standalone layouts. */


#imara-ai-root {
    display: flex;
    flex-direction: column;
    height: calc(100vh - 56px); /* subtract navbar height */
    background: #f9f9f9;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    overflow: hidden;
}

.ai-body {
    display: flex;
    flex: 1;
    overflow: hidden;
    position: relative;
}

/* ── Header ──────────────────────────────────────────────────────── */
.ai-header {
    display: none; /* hidden on desktop; shown on mobile via media query */
    align-items: center;
    justify-content: space-between;
    height: 60px;
    padding: 0 20px;
    background: #fff;
    border-bottom: 1px solid #eee;
    z-index: 20;
}

.header-left, .header-right {
    display: flex;
    align-items: center;
    gap: 15px;
}

.hamburger-btn {
    display: none;
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: #444;
    padding: 5px;
    border-radius: 8px;
    transition: background 0.2s;
}

.hamburger-btn:hover {
    background: #f3f4f6;
}

.header-brand {
    display: flex;
    align-items: center;
    gap: 10px;
}

.header-brand i {
    font-size: 1.4rem;
    color: #a72b2a;
}

.header-brand span {
    font-weight: 700;
    font-size: 1.1rem;
    color: #111;
}

.header-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    border-radius: 10px;
    border: 1px solid #e0e0e0;
    background: #fff;
    color: #333;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.2s;
}

.header-btn:hover {
    background: #f9f9f9;
    border-color: #d1d5db;
}

.header-btn i {
    font-size: 1.1rem;
    color: #444;
}


/* ── Sidebar ─────────────────────────────────────────────────────── */
.ai-sidebar {
    width: 260px;
    min-width: 260px;
    background: #1e1e1e;
    display: flex;
    flex-direction: column;
    transition: width 0.25s ease, min-width 0.25s ease;
    overflow: hidden;
    z-index: 10;
}

.ai-sidebar.collapsed {
    width: 0;
    min-width: 0;
}

.sidebar-header {
    padding: 18px 12px 10px;
}

.btn-knowledge-base {
    width: 100%;
    border-radius: 12px;
    padding: 10px 14px;
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    background: #2a2a2a;
    color: #efefef;
    border: 1px solid #333;
    display: flex;
    align-items: center;
    justify-content: flex-start;
    gap: 10px;
}

.btn-knowledge-base:hover {
    background: #333;
    border-color: #444;
}

.kb-icon-wrap {
    background: #1e1e1e;
    border-radius: 6px;
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.kb-icon-wrap i {
    font-size: 1.1rem;
    color: #a72b2a;
}

.btn-new-chat {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    margin: 0;
    padding: 11px 14px;
    background: #a72b2a;
    border: none;
    border-radius: 10px;
    color: #fff;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    box-shadow: 0 4px 12px rgba(167, 43, 42, 0.15);
}

.btn-new-chat:hover {
    background: #8e1c1b;
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(167, 43, 42, 0.2);
}

.btn-new-chat i { font-size: 1.1rem; color: #fff; }

/* Select toggle button (sits beside New conversation) */
.sidebar-action-wrap {
    display: flex;
    gap: 6px;
    padding: 0 10px 10px;
}
.sidebar-action-wrap .btn-new-chat {
    flex: 1;
    width: auto;
    margin: 0;
}
.btn-select-toggle {
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 38px;
    height: 38px;
    background: #1e1e1e;
    border: 1px solid #2e2e2e;
    border-radius: 10px;
    color: #888;
    cursor: pointer;
    transition: background .2s, color .2s, border-color .2s;
}
.btn-select-toggle:hover,
.btn-select-toggle.active { background: #2e1a1a; border-color: #a72b2a; color: #a72b2a; }

/* Bulk delete toolbar */
.bulk-delete-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 6px 12px 6px;
    background: #1a1a1a;
    border-bottom: 1px solid #2e2e2e;
    font-size: 12px;
    color: #aaa;
}
.bulk-select-all-wrap {
    display: flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
}
.btn-bulk-delete {
    display: flex;
    align-items: center;
    gap: 4px;
    padding: 4px 10px;
    background: #7f1d1d;
    border: none;
    border-radius: 7px;
    color: #fff;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: background .2s, opacity .2s;
}
.btn-bulk-delete:disabled { opacity: .45; cursor: not-allowed; }
.btn-bulk-delete:not(:disabled):hover { background: #991b1b; }

/* Checkbox on each history item */
.history-checkbox {
    flex-shrink: 0;
    width: 15px;
    height: 15px;
    cursor: pointer;
    accent-color: #a72b2a;
    display: none;
}
.select-mode .history-checkbox { display: block; }
.select-mode .history-item { cursor: default; }

.sidebar-section-label {
    padding: 12px 14px 6px;
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #717171;
    font-weight: 700;
}

/* ── Sidebar search ── */
.sidebar-search-wrap {
    display: flex;
    align-items: center;
    gap: 7px;
    margin: 0 10px 8px;
    background: #141414;
    border: 1px solid #2e2e2e;
    border-radius: 10px;
    padding: 7px 10px;
    transition: border-color 0.18s, box-shadow 0.18s;
}

.sidebar-search-wrap:focus-within {
    border-color: #7a1a1a;
    box-shadow: 0 0 0 2px rgba(167, 43, 42, 0.18);
}

.sidebar-search-icon {
    color: #484848;
    font-size: 1rem;
    flex-shrink: 0;
    transition: color 0.18s;
}

.sidebar-search-wrap:focus-within .sidebar-search-icon {
    color: #a72b2a;
}

#sidebarSearch {
    flex: 1;
    min-width: 0;
    background: transparent;
    border: none;
    outline: none;
    color: #d0d0d0;
    font-size: 0.84rem;
    font-family: inherit;
    caret-color: #a72b2a;
}

#sidebarSearch::placeholder { color: #444; }

.sidebar-search-clear {
    display: none;
    align-items: center;
    justify-content: center;
    background: none;
    border: none;
    padding: 0;
    cursor: pointer;
    color: #444;
    flex-shrink: 0;
    border-radius: 50%;
    transition: color 0.15s;
    line-height: 1;
}

.sidebar-search-clear:hover { color: #aaa; }
.sidebar-search-clear i { font-size: 0.95rem; pointer-events: none; }

.sidebar-history {
    flex: 1;
    overflow-y: auto;
    padding: 4px 8px 12px;
}

.sidebar-history::-webkit-scrollbar { width: 4px; }
.sidebar-history::-webkit-scrollbar-thumb { background: #333; border-radius: 2px; }

.pin-convo-btn {
    background: none;
    border: none;
    cursor: pointer;
    color: #9ca3af;
    padding: 4px;
    margin-right: 4px;
    border-radius: 4px;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.pin-convo-btn:hover {
    background: #f1f5f9;
    color: #4f46e5;
}

.pin-convo-btn.pinned {
    color: #4f46e5;
}

.pin-convo-btn i {
    font-size: 1rem;
}

/* ── Sidebar skeleton loader ── */
@keyframes skshimmer {
    0%   { background-position: -200px 0; }
    100% { background-position: 200px 0; }
}

.history-skeleton {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 9px 10px;
    border-radius: 7px;
}

.history-skeleton .sk-icon {
    width: 16px;
    height: 16px;
    border-radius: 4px;
    flex-shrink: 0;
    background: linear-gradient(90deg, #2a2a2a 25%, #333 50%, #2a2a2a 75%);
    background-size: 400px 100%;
    animation: skshimmer 1.4s infinite linear;
}

.history-skeleton .sk-line {
    flex: 1;
    height: 12px;
    border-radius: 6px;
    background: linear-gradient(90deg, #2a2a2a 25%, #333 50%, #2a2a2a 75%);
    background-size: 400px 100%;
    animation: skshimmer 1.4s infinite linear;
}

/* Stagger animation delays on each skeleton row */
.history-skeleton:nth-child(1) .sk-icon,
.history-skeleton:nth-child(1) .sk-line { animation-delay: 0s; }
.history-skeleton:nth-child(2) .sk-icon,
.history-skeleton:nth-child(2) .sk-line { animation-delay: 0.1s; }
.history-skeleton:nth-child(3) .sk-icon,
.history-skeleton:nth-child(3) .sk-line { animation-delay: 0.2s; }
.history-skeleton:nth-child(4) .sk-icon,
.history-skeleton:nth-child(4) .sk-line { animation-delay: 0.3s; }

/* ── Sidebar load error ── */
.sidebar-load-error {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    padding: 20px 14px;
    text-align: center;
    color: #555;
    font-size: 0.8rem;
}

.sidebar-load-error i {
    font-size: 1.4rem;
    color: #444;
}

.sidebar-load-error button {
    margin-top: 4px;
    background: #2a2a2a;
    border: 1px solid #3a3a3a;
    color: #aaa;
    font-size: 0.78rem;
    padding: 4px 12px;
    border-radius: 6px;
    cursor: pointer;
    transition: background 0.15s, color 0.15s;
}

.sidebar-load-error button:hover {
    background: #333;
    color: #e0e0e0;
}

.history-item {
    padding: 9px 10px;
    border-radius: 7px;
    color: #c0c0c0;
    font-size: 0.9rem;
    cursor: pointer;
    transition: background 0.12s;
    display: flex;
    align-items: center;
    gap: 8px;
}

.history-item:hover { background: #2a2a2a; }
.history-item.active { background: #2d2d2d; color: #fff; }
.history-item i { font-size: 0.85rem; color: #555; flex-shrink: 0; }

.history-label {
    flex: 1;
    min-width: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.delete-convo-btn {
    opacity: 0;
    pointer-events: none;
    background: none;
    border: none;
    cursor: pointer;
    color: #555;
    padding: 2px 4px;
    border-radius: 4px;
    line-height: 1;
    flex-shrink: 0;
    transition: opacity 0.12s, color 0.12s;
    display: flex;
    align-items: center;
}

.history-item:hover .delete-convo-btn {
    opacity: 1;
    pointer-events: auto;
}

.delete-convo-btn:hover { color: #e53e3e; }
.delete-convo-btn i { font-size: 0.85rem; pointer-events: none; }

/* Inline rename input inside sidebar */
.rename-input {
    flex: 1;
    min-width: 0;
    background: #2a2a2a;
    border: 1px solid #a72b2a;
    border-radius: 4px;
    color: #fff;
    font-size: 0.9rem;
    padding: 1px 6px;
    outline: none;
    font-family: inherit;
}

.sidebar-footer {
    padding: 12px 14px;
    border-top: 1px solid #2a2a2a;
    font-size: 0.78rem;
    color: #555;
    display: flex;
    align-items: center;
    gap: 8px;
}

.sidebar-footer-link {
    display: flex;
    align-items: center;
    gap: 12px;
    color: #e0e0e0;
    text-decoration: none !important;
    font-weight: 500;
    font-size: 0.85rem;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    width: 100%;
}

.sidebar-footer-link:hover {
    color: #fff;
}

.sidebar-footer-link i {
    font-size: 1.15rem;
    color: #888;
    transition: color 0.2s, transform 0.3s;
}

.sidebar-footer-link:hover i {
    color: #a72b2a;
    transform: rotate(45deg);
}



/* ── Main area ───────────────────────────────────────────────────── */
.ai-main {
    flex: 1;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    background: #fff;
    position: relative;
}

/* ── Messages ────────────────────────────────────────────────────── */
.ai-messages {
    flex: 1;
    overflow-y: auto;
    padding: 20px 0 0;
    scroll-behavior: smooth;
}

.ai-messages::-webkit-scrollbar { width: 6px; }
.ai-messages::-webkit-scrollbar-thumb { background: #ddd; border-radius: 3px; }

/* ── Welcome screen ──────────────────────────────────────────────── */
.welcome-screen {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 100%;
    padding: 40px 20px 120px;
    text-align: center;
}

.welcome-icon {
    width: 64px;
    height: 64px;
    background: linear-gradient(135deg, #a72b2a, #d9534f);
    border-radius: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 20px;
    box-shadow: 0 4px 20px rgba(167, 43, 42, 0.25);
}

.welcome-icon i { font-size: 2rem; color: #fff; }

.welcome-heading {
    font-size: 1.75rem;
    font-weight: 700;
    color: #111;
    margin-bottom: 8px;
}

.welcome-sub {
    font-size: 1rem;
    color: #666;
    max-width: 480px;
    margin-bottom: 32px;
    line-height: 1.6;
}

.suggestion-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    justify-content: center;
    max-width: 680px;
}

.chip {
    padding: 10px 16px;
    border: 1px solid #e0e0e0;
    border-radius: 22px;
    font-size: 0.84rem;
    color: #333;
    cursor: pointer;
    background: #fff;
    transition: all 0.15s;
    display: flex;
    align-items: center;
    gap: 7px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}

.chip:hover {
    background: #fef2f2;
    border-color: #a72b2a;
    color: #a72b2a;
}

.chip i { font-size: 0.9rem; }

/* ── Message rows ────────────────────────────────────────────────── */
.msg-row {
    width: 100%;
    padding: 6px 48px;
    display: flex;
}

.msg-row:last-child { padding-bottom: 40px; }

/* Bot: avatar left, bubble left-aligned */
.msg-row.bot-row {
    justify-content: flex-start;
}

/* User: bubble right-aligned, no avatar */
.msg-row.user-row {
    justify-content: flex-end;
}

/* Wrapper keeps bubble + timestamp together, right-aligned */
.user-bubble-wrap {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 3px;
}

/* Row-level action buttons (edit for user, feedback for bot) */
.msg-row-actions {
    display: flex;
    gap: 4px;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.15s;
}

.user-bubble-wrap:hover .msg-row-actions,
.msg-body:hover .msg-row-actions {
    opacity: 1;
    pointer-events: auto;
}

.msg-action-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 26px;
    height: 26px;
    border-radius: 6px;
    border: none;
    background: none;
    cursor: pointer;
    color: #aaa;
    font-size: 0.82rem;
    transition: background 0.12s, color 0.12s;
}

.msg-action-btn:hover { background: #f3f4f6; color: #555; }
.msg-action-btn.active-thumb { color: #22c55e !important; }
.msg-action-btn.active-thumb-down { color: #ef4444 !important; }

/* Edit, Retry & Speak features */
.msg-action-btn.speak-btn { color: #6366f1; }
.msg-action-btn.speak-btn:hover { background: #e0e7ff; color: #4338ca; }
.msg-action-btn.speaking { color: #dc2626 !important; animation: speak-pulse 1.5s infinite; }

@keyframes speak-pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}

.msg-body:has(.speaking) .msg-row-actions {
    opacity: 1;
    pointer-events: auto;
}

.msg-action-btn.retry-btn { color: #3b82f6; }
.msg-action-btn.retry-btn:hover { background: #dbeafe; color: #1e40af; }
.msg-action-btn.edit-confirm-btn { color: #059669 !important; }
.msg-action-btn.edit-confirm-btn:hover { background: #d1fae5; color: #065f46; }


.msg-edit-badge, .msg-retry-badge {
    font-size: 0.65rem;
    font-weight: 600;
    padding: 2px 6px;
    border-radius: 2px;
    margin: 0 4px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.msg-edit-badge {
    background: #fef3c7;
    color: #875d08;
}

.msg-retry-badge {
    background: #dbeafe;
    color: #1e40af;
    margin-left: auto;
    font-size: 0.68rem;
}

/* ── Bot bubble ── */
.bot-bubble {
    display: flex;
    gap: 10px;
    align-items: flex-start;
    max-width: 75%;
}

.msg-avatar {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.95rem;
    margin-top: 2px;
}

.msg-avatar.bot-av {
    background: linear-gradient(135deg, #a72b2a, #d9534f);
    color: #fff;
}

.msg-avatar.bot-av i { font-size: 1rem; }

/* ── User bubble ── */
.user-bubble {
    max-width: 85%;
    background: #a72b2a;
    color: #fff;
    border-radius: 18px 18px 4px 18px;
    padding: 12px 20px;
    font-size: 1.05rem;
    line-height: 1.6;
    word-wrap: break-word;
    box-shadow: 0 4px 15px rgba(167, 43, 42, 0.15);
}

/* ── Shared content area ── */
.msg-body { min-width: 0; }

.msg-name {
    font-size: 0.82rem;
    font-weight: 600;
    color: #aaa;
    margin-bottom: 4px;
    display: flex;
    align-items: center;
    gap: 8px;
}

/* Timestamp shown inside msg-name (bot) or below user bubble */
.msg-timestamp {
    font-size: 0.72rem;
    color: #bbb;
    font-weight: 400;
    white-space: nowrap;
}

.msg-content {
    font-size: 1.05rem;
    line-height: 1.75;
    color: #1a1a1a;
}

.msg-content.error-msg {
    color: #a72b2a;
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-radius: 10px;
    padding: 10px 14px;
}

/* ── Copy button (now inside .msg-row-actions — visibility handled there) ── */
.copy-btn {
    background: none;
    border: none;
    cursor: pointer;
    padding: 4px 6px;
    border-radius: 6px;
    color: #aaa;
    font-size: 0.82rem;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    transition: background 0.15s, color 0.15s;
}

.copy-btn:hover { background: #f3f4f6; color: #555; }
.copy-btn.copied { color: #22c55e; }
.copy-btn i { font-size: 0.95rem; }

/* Markdown rendering inside bot messages */
.msg-content h2 { font-size: 1.15rem; font-weight: 700; margin: 16px 0 8px; color: #a72b2a; }
.msg-content h3 { font-size: 1.05rem; font-weight: 700; margin: 12px 0 6px; }
.msg-content strong { font-weight: 600; }
.msg-content em { font-style: italic; color: #555; }
.msg-content table { border-collapse: collapse; width: 100%; margin: 10px 0; font-size: 1rem; }
.msg-content th, .msg-content td { border: 1px solid #e5e7eb; padding: 6px 12px; text-align: left; }
.msg-content th { background: #fef2f2; color: #a72b2a; font-weight: 600; }
.msg-content tr:nth-child(even) { background: #fafafa; }
.msg-content code {
    background: #f3f4f6;
    padding: 2px 6px;
    border-radius: 4px;
    font-family: 'Courier New', monospace;
    font-size: 0.88em;
    color: #a72b2a;
}
.msg-content pre {
    background: #1e1e1e;
    color: #e5e7eb;
    padding: 14px 16px;
    border-radius: 10px;
    overflow-x: auto;
    font-size: 0.88rem;
    margin: 10px 0;
}
.msg-content ul, .msg-content ol { margin: 8px 0 8px 20px; }
.msg-content li { margin: 4px 0; }
.msg-content p { margin: 6px 0; }
.msg-content hr { border: none; border-top: 1px solid #e5e7eb; margin: 12px 0; }

/* Sources */
.msg-sources {
    margin-top: 10px;
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.source-tag {
    font-size: 0.72rem;
    padding: 3px 8px;
    border-radius: 10px;
    background: #f3f4f6;
    color: #555;
    border: 1px solid #e5e7eb;
}

.source-tag.live-data-tag {
    background: #ecfdf5;
    color: #065f46;
    border-color: #a7f3d0;
}

.source-tag.source-link {
    text-decoration: none;
    cursor: pointer;
    background: #eff6ff;
    color: #1d4ed8;
    border-color: #bfdbfe;
    transition: background 0.15s, color 0.15s;
}

.source-tag.source-link:hover {
    background: #dbeafe;
    color: #1e40af;
}

/* ── Chart container ─────────────────────────────────────────────── */
.chart-container {
    width: 100%;
    max-width: 100%;
    margin: 25px 0;
    padding: 20px;
    background: #fff;
    border-radius: 16px;
    border: 1px solid #eef2f7;
    position: relative;
    box-shadow: 0 4px 20px rgba(0,0,0,0.04);
    transition: all 0.2s ease;
}

.chart-badge {
    display: inline-flex;
    align-items: center;
    padding: 4px 12px;
    background: #f0f7ff;
    color: #3b82f6;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 15px;
    border: 1px solid #dbeafe;
}

.chart-badge i {
    margin-right: 6px;
}

.chart-container:hover {
    border-color: #3b82f6;
}

.chart-expand-btn {
    position: absolute;
    top: 10px;
    right: 10px;
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.9);
    border: 1px solid #eee;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: #666;
    transition: all 0.2s ease;
    z-index: 10;
}

.chart-expand-btn:hover {
    background: #3b82f6;
    color: #fff;
    border-color: #3b82f6;
    transform: scale(1.1);
}

.chart-modal-box {
    width: 90%;
    max-width: 1000px;
    padding: 30px;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
}

.modal-chart-data {
    max-height: 200px;
    overflow-y: auto;
    margin-top: 20px;
    border-top: 1px solid #eee;
    padding-top: 20px;
}

.modal-chart-data table {
    width: 100%;
    font-size: 0.9rem;
}

@media (max-width: 768px) {
    .chart-modal-box {
        width: 95%;
        padding: 15px;
    }
    .modal-chart-container {
        height: 300px !important;
    }
}

.chart-container canvas {
    max-height: 280px;
}

/* Prediction bubbles and Level 5 specific UI removed in industrialization. */

/* Internal reasoning toggles removed. */

/* ── Thinking indicator ──────────────────────────────────────────── */
.thinking-dots {
    display: flex;
    gap: 5px;
    align-items: center;
    padding: 4px 0;
}

.thinking-dots span {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #a72b2a;
    animation: bounce 1.2s infinite ease-in-out;
}

.thinking-dots span:nth-child(1) { animation-delay: -0.3s; }
.thinking-dots span:nth-child(2) { animation-delay: -0.15s; }
.thinking-dots span:nth-child(3) { animation-delay: 0s; }

@keyframes bounce {
    0%, 80%, 100% { transform: scale(0.6); opacity: 0.5; }
    40% { transform: scale(1.1); opacity: 1; }
}

/* ── Input area ──────────────────────────────────────────────────── */
/* ── Input area ─────────────────────────────────────────────────── */
.ai-input-area {
    border-top: 1px solid #ebebeb;
    padding: 12px 20px 14px;
    background: #fff;
    flex-shrink: 0;
}

/* Attachment preview bar */
#attachPreviewBar {
    max-width: 950px;
    margin: 0 auto 8px;
    display: none;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    border-radius: 10px;
    font-size: 0.82rem;
    color: #1d4ed8;
}
#attachPreviewBar .attach-preview-icon {
    font-size: 1rem;
    flex-shrink: 0;
    color: #3b82f6;
}
#attachFileName {
    flex: 1;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-weight: 500;
}
#attachStatusBadge {
    font-size: 0.72rem;
    padding: 2px 8px;
    border-radius: 20px;
    background: #dbeafe;
    color: #1e40af;
    font-weight: 600;
    white-space: nowrap;
}
#attachStatusBadge.ready   { background: #dcfce7; color: #15803d; }
#attachStatusBadge.failed  { background: #fee2e2; color: #b91c1c; }
#attachRemoveBtn {
    background: none;
    border: none;
    cursor: pointer;
    color: #93c5fd;
    font-size: 1rem;
    line-height: 1;
    padding: 0 2px;
    transition: color .15s;
}
#attachRemoveBtn:hover { color: #1d4ed8; }

/* Input box pill */
.input-box {
    max-width: 950px;
    margin: 0 auto;
    background: #f8f8f8;
    border: 1.5px solid #e3e3e3;
    border-radius: 18px;
    display: flex;
    align-items: flex-end;
    gap: 4px;
    padding: 8px 8px 8px 6px;
    transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
}

.input-box:focus-within {
    border-color: #a72b2a;
    box-shadow: 0 0 0 3px rgba(167, 43, 42, 0.09);
    background: #fff;
}

/* Left icon buttons (attach + mic) */
.input-icon-btn {
    flex-shrink: 0;
    width: 32px;
    height: 32px;
    border: none;
    background: transparent;
    border-radius: 9px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: #64748b;
    font-size: 1.25rem;
    transition: all 0.2s;
}

#modelBtn i { color: #4f46e5; }
#modelBtn:hover { background: #eef2ff; }
#modelBtn:hover i { color: #4338ca; }

#toolsBtn:hover { background: #f1f5f9; }
#toolsBtn:hover i { color: #a72b2a; }

.input-icon-btn:hover { background: #f0f0f0; color: #374151; }
.input-icon-btn.mic-active { color: #a72b2a; background: #fee2e2; animation: mic-pulse 1.2s infinite; }

@keyframes mic-pulse {
    0%, 100% { box-shadow: 0 0 0 0 rgba(167,43,42,0.25); }
    50%       { box-shadow: 0 0 0 5px rgba(167,43,42,0); }
}

#messageInput {
    flex: 1;
    border: none;
    background: transparent;
    outline: none;
    font-size: 0.92rem;
    line-height: 1.55;
    resize: none;
    max-height: 180px;
    min-height: 22px;
    font-family: inherit;
    color: #111;
    padding: 4px 4px;
    align-self: center;
}

#messageInput::placeholder { color: #b0b0b0; }

/* Send / Stop */
#sendButton, #stopButton {
    flex-shrink: 0;
    width: 36px;
    height: 36px;
    min-width: 36px;
    border-radius: 12px;
    border: none;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background 0.15s, transform 0.1s, opacity .15s;
}
#sendButton {
    background: #a72b2a;
    margin-left: 2px;
}
#sendButton:hover:not(:disabled) { background: #8e1c1b; transform: scale(1.06); }
#sendButton:disabled { background: #d1d5db; cursor: not-allowed; transform: none; opacity: .7; }
#sendButton svg { pointer-events: none; }

#stopButton {
    background: #374151;
}
#stopButton:hover { background: #1f2937; transform: scale(1.06); }
#stopButton i { font-size: 1.15rem; pointer-events: none; }

.input-hint {
    max-width: 760px;
    margin: 5px auto 0;
    text-align: center;
    font-size: 0.7rem;
    color: #c7c7c7;
    letter-spacing: .01em;
}

/* ── Tools Dropup ───────────────────────────────────────────────── */
.input-box { position: relative; } /* Ensure dropup is relative to input-box */

.tools-dropup {
    position: absolute;
    bottom: calc(100% + 12px);
    left: 8px;
    width: 280px;
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(12px) saturate(180%);
    -webkit-backdrop-filter: blur(12px) saturate(180%);
    border: 1px solid rgba(255, 255, 255, 0.4);
    border-radius: 16px;
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.12), 0 0 0 1px rgba(0,0,0,0.05);
    padding: 8px;
    z-index: 500;
    animation: dropupSlide 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    transform-origin: bottom left;
}

@keyframes dropupSlide {
    from { opacity: 0; transform: translateY(10px) scale(0.95); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}

.tools-menu-header {
    padding: 10px 12px 8px;
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: #940000;
    border-bottom: 1px solid rgba(0,0,0,0.04);
    margin-bottom: 4px;
}

.tools-menu-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px;
    border-radius: 10px;
    cursor: pointer;
    transition: background 0.2s;
}

.tools-menu-item:hover { background: rgba(0,0,0,0.03); }
.tools-menu-item.disabled {
    opacity: 0.5;
    pointer-events: none;
    filter: grayscale(1);
}

.tool-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.tool-info i {
    font-size: 1.3rem;
    color: #a72b2a;
    background: #fff;
    width: 38px;
    height: 38px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(167, 43, 42, 0.12);
}

.tool-details {
    display: flex;
    flex-direction: column;
}

.tool-name {
    font-size: 0.88rem;
    font-weight: 600;
    color: #1a1a1a;
}

.tool-desc {
    font-size: 0.72rem;
    color: #6b7280;
}

.tools-menu-footer {
    padding: 10px 12px;
    font-size: 0.68rem;
    color: #9ca3af;
    text-align: center;
    font-style: italic;
    border-top: 1px solid rgba(0,0,0,0.04);
    margin-top: 4px;
}

/* Premium Toggle Switch */
.premium-switch {
    position: relative;
    display: inline-block;
    width: 42px;
    height: 24px;
}

.premium-switch input {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
    z-index: 2;
    margin: 0;
}

.switch-slider {
    position: absolute;
    cursor: pointer;
    inset: 0;
    background-color: #e5e7eb;
    transition: .3s cubic-bezier(0.4, 0, 0.2, 1);
    border-radius: 24px;
}

.switch-slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: .3s cubic-bezier(0.4, 0, 0.2, 1);
    border-radius: 50%;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

input:checked + .switch-slider {
    background-color: #a72b2a;
}

input:checked + .switch-slider:before {
    transform: translateX(18px);
}

/* Small Variant for Master Toggle */
.premium-switch.small { width: 34px; height: 18px; }
.premium-switch.small .switch-slider:before { height: 12px; width: 12px; left: 3px; bottom: 3px; }
input:checked + .premium-switch.small .switch-slider:before { transform: translateX(16px); }
/* Specific fix for input:checked inside small */
.premium-switch.small input:checked + .switch-slider:before { transform: translateX(16px); }

/* Button & indicator */
#toolsBtn { position: relative; }
.tools-indicator {
    position: absolute;
    top: 6px;
    right: 6px;
    width: 8px;
    height: 8px;
    background: #22c55e;
    border: 2px solid #f8f8f8;
    border-radius: 50%;
    display: none;
    transition: border-color 0.2s;
}
.input-box:focus-within .tools-indicator { border-color: #fff; }

@media (prefers-color-scheme: dark) {
    .tools-dropup {
        background: rgba(30, 30, 30, 0.8);
        border: 1px solid rgba(255, 255, 255, 0.1);
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.4);
    }
    .tools-menu-header { color: #fca5a5; border-bottom-color: rgba(255,255,255,0.05); }
    .tool-name { color: #f3f4f6; }
    .tool-desc { color: #9ca3af; }
    .tool-info i { background: #262626; color: #fca5a5; }
    .tools-menu-item:hover { background: rgba(255,255,255,0.03); }
    .switch-slider { background-color: #3f3f3f; }
    .tools-indicator { border-color: #1e1e1e; }
}

/* ── Delete confirmation modal ──────────────────────────────────── */
.modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.35);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    animation: fadeInBackdrop 0.15s ease;
}

@keyframes fadeInBackdrop {
    from { opacity: 0; }
    to   { opacity: 1; }
}

.modal-box {
    background: #fff;
    border-radius: 16px;
    padding: 32px 28px 24px;
    max-width: 380px;
    width: calc(100% - 40px);
    text-align: center;
    box-shadow: 0 20px 60px rgba(0,0,0,0.2);
    animation: slideUpModal 0.2s ease;
}

@keyframes slideUpModal {
    from { transform: translateY(20px); opacity: 0; }
    to   { transform: translateY(0);   opacity: 1; }
}

.modal-icon {
    width: 52px;
    height: 52px;
    border-radius: 50%;
    background: #fef2f2;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 16px;
}

.modal-icon i {
    font-size: 1.6rem;
    color: #a72b2a;
}

.modal-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: #111;
    margin-bottom: 8px;
}

.modal-body {
    font-size: 0.9rem;
    color: #666;
    line-height: 1.6;
    margin-bottom: 24px;
}

/* ── Chart Expansion Modal ───────────────────────────────────────── */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.75);
    backdrop-filter: blur(8px);
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.chart-modal-content {
    background: #fff;
    width: 100%;
    max-width: 1200px;
    height: 90vh;
    border-radius: 20px;
    display: flex;
    flex-direction: column;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
    overflow: hidden;
    animation: scaleUp 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

@keyframes scaleUp {
    from { transform: scale(0.9) translateY(20px); opacity: 0; }
    to { transform: scale(1) translateY(0); opacity: 1; }
}

.chart-modal-header {
    padding: 20px 30px;
    background: #fff;
    border-bottom: 1px solid #f1f5f9;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}

.chart-modal-header .header-left {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.chart-modal-header h2 {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 800;
    color: #0f172a;
    letter-spacing: -0.02em;
}

.modal-close-btn {
    background: #f1f5f9;
    border: none;
    font-size: 24px;
    color: #64748b;
    cursor: pointer;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.modal-close-btn:hover {
    background: #e2e8f0;
    color: #0f172a;
    transform: rotate(90deg);
}

.chart-modal-body {
    flex: 1;
    overflow-y: auto;
    padding: 30px;
    background: #fbfcfe;
}

.modal-canvas-wrap {
    background: #fff;
    padding: 24px;
    border-radius: 16px;
    border: 1px solid #f1f5f9;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    height: 500px;
    margin-bottom: 30px;
}

.modal-actions {
    display: flex;
    gap: 10px;
    justify-content: center;
}

.modal-btn {
    flex: 1;
    padding: 10px 16px;
    border-radius: 9px;
    border: none;
    font-size: 0.92rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.15s, transform 0.1s;
}

.modal-btn:active { transform: scale(0.97); }

.modal-btn-cancel {
    background: #f3f4f6;
    color: #444;
}

.modal-btn-cancel:hover { background: #e5e7eb; }

.modal-btn-confirm {
    background: #a72b2a;
    color: #fff;
}

.modal-btn-confirm:hover { background: #8e1c1b; }

/* Bulk delete: scrollable list of affected conversation titles */
.modal-convo-list {
    max-height: 140px;
    overflow-y: auto;
    margin: -8px 0 20px;
    padding: 6px 0;
    border: 1px solid #f0f0f0;
    border-radius: 8px;
    background: #fafafa;
    text-align: left;
}
.modal-convo-list-item {
    display: flex;
    align-items: center;
    gap: 7px;
    padding: 5px 12px;
    font-size: 0.82rem;
    color: #444;
    border-bottom: 1px solid #f3f3f3;
}
.modal-convo-list-item:last-child { border-bottom: none; }
.modal-convo-list-item i { color: #a72b2a; font-size: 0.9rem; flex-shrink: 0; }
.modal-convo-list-item span {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
/* Bulk icon variant */
.modal-icon.bulk { background: #fff7ed; }
.modal-icon.bulk i { color: #c2410c; }

/* Blur effect applied to page root when modal is open */
#imara-ai-root.modal-open {
    filter: blur(4px) brightness(0.9);
    transition: filter 0.2s ease;
}

/* ── Mobile & Hamburger Menu ─────────────────────────────────────── */
@media (max-width: 768px) {
    .ai-header {
        display: flex;
    }

    .hamburger-btn, .header-right .header-btn {
        display: flex;
        position: absolute;
        top: 12px;
        z-index: 400;
        background: #fff;
        border: 1px solid #ddd;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        width: 40px;
        height: 40px;
        border-radius: 10px;
        justify-content: center;
        align-items: center;
    }

    .hamburger-btn {
        left: 12px;
    }

    .header-right {
        position: absolute;
        top: 12px;
        right: 12px;
        z-index: 400;
    }

    .header-right .header-btn {
        position: static;
        width: 40px;
        height: 40px;
        padding: 0;
    }

    .header-btn span {
        display: none;
    }

    .ai-sidebar {
        position: absolute;
        top: 0;
        left: 0;
        height: 100%;
        width: 280px;
        transform: translateX(-100%);
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 10px 0 30px rgba(0,0,0,0.1);
        z-index: 1000;
    }

    .ai-sidebar.active {
        transform: translateX(0);
        display: flex;
    }
    
    /* Overlay background when mobile menu is open */
    .menu-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.4);
        backdrop-filter: blur(2px);
        z-index: 450;
        animation: fadeIn 0.3s forwards;
    }

    .menu-overlay.active {
        display: block;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .ai-messages {
        padding-top: 64px;
    }

    .msg-inner { padding: 0 14px; }
    .welcome-heading { font-size: 1.3rem; }
    .suggestion-chips { gap: 8px; }
    .chip { font-size: 0.8rem; padding: 8px 12px; }
}

/* ── Tools Dropup ───────────────────────────────────────────────── */
.tools-dropup {
    position: absolute;
    bottom: 70px;
    left: 20px;
    width: 280px;
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.12), 0 0 1px rgba(0,0,0,0.1);
    z-index: 1000;
    overflow: hidden;
    animation: slideUp 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    border: 1px solid #f1f5f9;
}

@keyframes slideUp {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.menu-header {
    padding: 15px 20px;
    background: #f8fafc;
    border-bottom: 1px solid #f1f5f9;
    font-size: 0.85rem;
    font-weight: 700;
    color: #475569;
    display: flex;
    align-items: center;
    gap: 8px;
}

.menu-header i {
    color: #a72b2a;
    font-size: 1.1rem;
}

.menu-group {
    padding: 15px 20px;
    border-bottom: 1px solid #f1f5f9;
}

.menu-group:last-of-type {
    border-bottom: none;
}

.group-label {
    font-size: 0.65rem;
    font-weight: 800;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    margin-bottom: 12px;
}

.tools-menu-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 2px 0;
}

.tool-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.tool-info i {
    font-size: 1.25rem;
    color: #64748b;
}

.tool-details {
    display: flex;
    flex-direction: column;
}

.tool-name {
    font-size: 0.88rem;
    font-weight: 600;
    color: #1e293b;
}

#modelBtn i { 
    color: #4f46e5; 
    font-size: 1.35rem;
}
#modelBtn:hover i { color: #4338ca; }

.tools-menu-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 0;
}

/* ── Premium Selection & Model Picker ── */
.premium-select {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 8px 12px;
    font-size: 0.85rem;
    color: #1e293b;
    cursor: pointer;
    transition: all 0.2s;
    outline: none;
    width: 100%;
    margin-top: 4px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}

.tools-menu-item.ghosted {
    opacity: 0.45;
    pointer-events: none;
    filter: grayscale(0.5);
    transition: all 0.3s;
}

.menu-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 20px;
    background: #f8fafc;
    border-bottom: 1px solid #f1f5f9;
    font-size: 0.78rem;
    font-weight: 800;
    color: #475569;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

/* ── BI Tools & Analytical Exports ── */
.chart-modal-actions {
    display: flex;
    gap: 8px;
    margin-left: 20px;
}

.modal-action-btn {
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 0.78rem;
    font-weight: 600;
    color: #475569;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s;
}

.modal-action-btn:hover {
    background: #e2e8f0;
    color: #1e293b;
    border-color: #cbd5e1;
}

.modal-action-btn i {
    font-size: 1.1rem;
    color: #a72b2a;
}

.modal-data-section {
    margin-top: 30px;
    border: 1px solid #f1f5f9;
    border-radius: 10px;
    overflow: hidden;
    background: #fff;
}

.modal-data-section .section-header {
    padding: 12px 20px;
    background: #f8fafc;
    border-bottom: 1px solid #f1f5f9;
    display: flex;
    justify-content: space-between;
    align-items: center;
    cursor: pointer;
    user-select: none;
}

.modal-data-section .section-header h3 {
    margin: 0;
    font-size: 0.9rem;
    color: #334155;
    display: flex;
    align-items: center;
    gap: 8px;
}

.drilldown-table-wrap {
    max-height: 400px;
    overflow-y: auto;
    transition: max-height 0.3s ease;
}

.drilldown-table-wrap.collapsed {
    max-height: 0;
}

.drilldown-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.85rem;
}

.drilldown-table th {
    position: sticky;
    top: 0;
    background: #fff;
    padding: 12px 20px;
    text-align: left;
    color: #64748b;
    font-weight: 600;
    border-bottom: 2px solid #f1f5f9;
    z-index: 1;
}

.drilldown-table td {
    padding: 10px 20px;
    border-bottom: 1px solid #f8fafc;
    color: #334155;
}

.drilldown-table tr:hover td {
    background: #f1f7ff;
}

/* ── Multi-Query Report Cards ── */
.multi-report-card {
    background: #fdfdfd;
    border: 1px solid #eef0f2;
    border-left: 4px solid #a72b2a;
    border-radius: 8px;
    padding: 16px;
    margin: 16px 0;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    transition: transform 0.2s, box-shadow 0.2s;
}

.multi-report-card:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.06);
}

.multi-report-card h2, 
.multi-report-card h3 {
    margin-top: 0 !important;
}

.report-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #fef2f2;
    color: #a72b2a;
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    padding: 3px 8px;
    border-radius: 4px;
    margin-bottom: 12px;
    letter-spacing: 0.02em;
}

.report-count-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background: #eef2ff;
    color: #4338ca;
    font-size: 0.75rem;
    font-weight: 600;
    padding: 4px 10px;
    border-radius: 20px;
    margin-bottom: 10px;
}

.report-card-footer {
    margin-top: 15px;
    padding-top: 10px;
    border-top: 1px dashed #eee;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.report-card-source {
    font-size: 0.75rem;
    color: #888;
    background: #fdfdfd;
    border: 1px solid #f0f0f0;
    padding: 3px 10px;
    border-radius: 5px;
    display: flex;
    align-items: center;
    gap: 6px;
    box-shadow: inset 0 1px 2px rgba(0,0,0,0.02);
}

.report-card-source i {
    color: #a72b2a;
    font-size: 0.95rem;
}

/* ── Print Styles for Report Export ── */
@media print {
    body * { visibility: hidden; }
    #chartModal, #chartModal * { visibility: visible; }
    #chartModal {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        height: auto;
        display: block !important;
        background: white !important;
    }
    .chart-modal-content {
        box-shadow: none !important;
        width: 100% !important;
        max-width: none !important;
        margin: 0 !important;
        border: none !important;
    }
    .modal-close-btn, .chart-modal-actions, .modal-indicator {
        display: none !important;
    }
    .drilldown-table-wrap {
        max-height: none !important;
        overflow: visible !important;
    }
    .drilldown-table-wrap.collapsed {
        max-height: none !important;
    }
}


