{{-- ── Main chat area ───────────────────────────────────────────────── --}}
<main class="ai-main">

    {{-- Messages --}}
    <div class="ai-messages" id="chatMessages">
        <div class="welcome-screen" id="welcomeScreen">
            <div class="welcome-icon">
                <i class="mdi mdi-head-lightbulb"></i>
            </div>
            <div class="welcome-heading">How can I help you today?</div>
            <div class="welcome-sub">
                Ask about sample tracking, equipment status, QC results, CAPAs, SOPs — or get live counts from the system.
            </div>
            <div class="suggestion-chips">
                <div class="chip" onclick="useChip(this)">
                    <i class="mdi mdi-counter"></i> How many samples since start of system?
                </div>
                <div class="chip" onclick="useChip(this)">
                    <i class="mdi mdi-clock-alert-outline"></i> How many samples are waiting for review?
                </div>
                <div class="chip" onclick="useChip(this)">
                    <i class="mdi mdi-flask-outline"></i> How many samples are in the lab?
                </div>
                <div class="chip" onclick="useChip(this)">
                    <i class="mdi mdi-table-large"></i> Give me a sample breakdown by stage
                </div>
                <div class="chip" onclick="useChip(this)">
                    <i class="mdi mdi-wrench-clock"></i> Which equipment is overdue for maintenance?
                </div>
                <div class="chip" onclick="useChip(this)">
                    <i class="mdi mdi-alert-circle-outline"></i> How many overdue samples do we have?
                </div>
                <div class="chip" onclick="useChip(this)">
                    <i class="mdi mdi-file-document-outline"></i> Find relevant SOPs
                </div>
                <div class="chip" onclick="useChip(this)">
                    <i class="mdi mdi-clipboard-check-outline"></i> Pending CAPA actions
                </div>
            </div>
        </div>
    </div>

    {{-- Input --}}
    <div class="ai-input-area">
        {{-- Attachment preview bar (hidden until file selected) --}}
        <div id="attachPreviewBar" style="display:none; align-items:center; gap:8px; padding:8px 12px; background:#f0f9ff; border:1px solid #bfdbfe; border-radius:8px; margin-bottom:8px; font-size:0.85rem; color:#1d4ed8;">
            <i class="mdi mdi-paperclip" style="font-size:1.1rem;"></i>
            <span id="attachFileName" style="flex:1; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"></span>
            <span id="attachStatusBadge" style="font-size:0.75rem; padding:2px 8px; border-radius:9999px; background:#dbeafe;">Uploading…</span>
            <button id="attachRemoveBtn" type="button" style="background:none;border:none;cursor:pointer;color:#64748b;padding:0;">&times;</button>
        </div>
        {{-- Attachment preview bar --}}
        <div id="attachPreviewBar">
            <i class="mdi mdi-paperclip attach-preview-icon"></i>
            <span id="attachFileName"></span>
            <span id="attachStatusBadge">Uploading…</span>
            <button id="attachRemoveBtn" type="button" title="Remove attachment">
                <i class="mdi mdi-close-circle"></i>
            </button>
        </div>

        <div class="input-box">
            {{-- Attach button --}}
            <button id="attachBtn" type="button" title="Attach file (PDF or image)" class="input-icon-btn">
                <i class="mdi mdi-paperclip"></i>
            </button>
            <input type="file" id="fileInput" accept=".pdf,.png,.jpg,.jpeg" style="display:none">

            {{-- Text area --}}
            <textarea
                id="messageInput"
                rows="1"
                placeholder="Ask about samples, equipment, QC, SOPs, or any live data…"
            ></textarea>

            {{-- Mic button --}}
            <button id="micBtn" type="button" title="Voice input" class="input-icon-btn">
                <i class="mdi mdi-microphone-outline"></i>
            </button>

            {{-- Send --}}
            <button id="sendButton" type="button" title="Send (Enter)">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="22" y1="2" x2="11" y2="13"></line>
                    <polygon points="22,2 15,22 11,13 2,9 22,2"></polygon>
                </svg>
            </button>

            {{-- Stop --}}
            <button id="stopButton" type="button" title="Stop generating" style="display:none">
                <i class="mdi mdi-stop-circle-outline"></i>
            </button>
        </div>
        <div class="input-hint">Enter to send &middot; Shift+Enter for new line</div>
    </div>

</main>

{{-- Delete conversation confirmation modal --}}
<div id="deleteConvoModal" class="modal-backdrop" style="display:none">
    <div class="modal-box">
        <div class="modal-icon" id="modalIcon">
            <i class="mdi mdi-delete-outline"></i>
        </div>
        <div class="modal-title" id="modalTitle">Delete conversation?</div>
        <div class="modal-body" id="modalBody">
            This conversation will be permanently removed from your history. This cannot be undone.
        </div>
        {{-- Shown in bulk mode: scrollable list of conversation titles being deleted --}}
        <div id="modalConvoList" class="modal-convo-list" style="display:none"></div>
        <div class="modal-actions">
            <button id="modalCancelBtn" class="modal-btn modal-btn-cancel">Cancel</button>
            <button id="modalConfirmBtn" class="modal-btn modal-btn-confirm">Delete</button>
        </div>
    </div>
</div>
