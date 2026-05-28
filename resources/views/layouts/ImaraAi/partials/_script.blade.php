const KNOWLEDGE_SEARCH_URL  = '{{ route('ai.knowledge.search') }}';
const ASK_STREAM_URL        = '{{ route('ai.knowledge.ask-stream') }}';
const LOOKUP_DOCS_URL       = '{{ route('ai.lookup-documents') }}';
const CSRF_TOKEN            = '{{ csrf_token() }}';
const CONVOS_URL            = '{{ route('ai.conversations.list') }}';
const CREATE_CONVO_URL      = '{{ route('ai.conversations.create') }}';
const CONVO_MESSAGES_BASE   = '/imara-ai/conversations';
const IMARA_AI_LANGUAGE     = {!! json_encode(in_array(app()->getLocale(), ['en', 'sw'], true) ? app()->getLocale() : 'auto') !!};

let selectedReferences      = []; 
let mentionSearchActive     = false;
let mentionResults          = [];
let mentionIndex            = -1;
@auth
const USER_INITIALS = '{{ strtoupper(substr(auth()->user()->name ?? "U", 0, 1)) }}';
@else
const USER_INITIALS = 'U';
@endauth

const chatMessages   = document.getElementById('chatMessages');
const welcomeScreen  = document.getElementById('welcomeScreen');
const messageInput   = document.getElementById('messageInput');
const sendButton     = document.getElementById('sendButton');
const stopButton     = document.getElementById('stopButton');
const sidebar        = document.getElementById('aiSidebar');
const sidebarHistory = document.getElementById('sidebarHistory');
const sidebarSearch  = document.getElementById('sidebarSearch');
const aiMain = document.querySelector('.ai-main');
const aiInputArea = document.querySelector('.ai-input-area');
const hamburgerBtn = document.getElementById('hamburgerBtn');
const menuOverlay = document.getElementById('menuOverlay');
const btnHeaderNewChat = document.getElementById('btnHeaderNewChat');

let thinkingRow              = null;
let currentAbortController   = null;
let currentStreamSessionId    = null;  // Tracks the sessionId of active stream for proper cleanup
let currentStreamTraceId      = null;  // Tracks the traceId of active stream for proper query cancellation
let isCleaningUp             = false;  // Prevents new submissions during cleanup
let isSending                = false;  // Prevents concurrent submissions
let cleanupTimeoutId         = null;   // Watchdog timeout for stuck cleanup states
let conversations            = [];
let currentConvoId           = null; // server-generated integer id once conversation is created
let currentConvoCreated      = false; // true once a server record exists for this session
let selectedConvoIds         = new Set(); // ids checked in bulk-select mode
let bulkSelectMode           = false;
let toolsMasterEnabled       = true;
let visualsEnabled           = true;
window.chartConfigs          = window.chartConfigs || {};

// ── Sidebar ───────────────────────────────────────────────────────────────
// Hook the existing app-wide navbar hamburger to toggle the AI sidebar.
// On desktop, it collapses/expands. On mobile, it triggers the active overlay.
document.getElementById('toggle-main-sidebar')?.addEventListener('click', () => {
    if (window.innerWidth <= 768) {
        toggleMobileMenu();
    } else {
        sidebar.classList.toggle('collapsed');
    }
});


// Imara AI specific Mobile Hamburger
const toggleMobileMenu = () => {
    sidebar.classList.toggle('active');
    menuOverlay.classList.toggle('active');
};

hamburgerBtn?.addEventListener('click', toggleMobileMenu);
menuOverlay?.addEventListener('click', toggleMobileMenu);

document.getElementById('btnNewChat')?.addEventListener('click', () => {
    startNewConversation();
    if (window.innerWidth <= 768) toggleMobileMenu();
});

btnHeaderNewChat?.addEventListener('click', () => {
    startNewConversation();
});


// ── Bulk-select toolbar ───────────────────────────────────────────────────
const btnSelectToggle  = document.getElementById('btnSelectToggle');
const bulkDeleteToolbar = document.getElementById('bulkDeleteToolbar');
const selectAllConvos  = document.getElementById('selectAllConvos');
const btnBulkDelete    = document.getElementById('btnBulkDelete');

function enterSelectMode() {
    bulkSelectMode = true;
    selectedConvoIds.clear();
    if (btnSelectToggle) btnSelectToggle.classList.add('active');
    if (bulkDeleteToolbar) bulkDeleteToolbar.style.display = 'flex';
    if (selectAllConvos) selectAllConvos.checked = false;
    if (btnBulkDelete) btnBulkDelete.disabled = true;
    sidebarHistory.classList.add('select-mode');
    renderSidebarHistory();
}

function exitSelectMode() {
    bulkSelectMode = false;
    selectedConvoIds.clear();
    if (btnSelectToggle) btnSelectToggle.classList.remove('active');
    if (bulkDeleteToolbar) bulkDeleteToolbar.style.display = 'none';
    sidebarHistory.classList.remove('select-mode');
    renderSidebarHistory();
}

btnSelectToggle?.addEventListener('click', () => {
    bulkSelectMode ? exitSelectMode() : enterSelectMode();
});

selectAllConvos?.addEventListener('change', () => {
    const checked = selectAllConvos.checked;
    conversations.forEach(c => {
        if (checked) selectedConvoIds.add(c.id);
        else selectedConvoIds.delete(c.id);
    });
    if (btnBulkDelete) btnBulkDelete.disabled = selectedConvoIds.size === 0;
    // Sync visual checkboxes
    sidebarHistory.querySelectorAll('.history-checkbox').forEach(cb => {
        cb.checked = checked;
    });
});

btnBulkDelete?.addEventListener('click', () => {
    if (!selectedConvoIds.size) return;
    showBulkDeleteModal([...selectedConvoIds]);
});

function startNewConversation() {
    currentConvoId      = null;
    currentConvoCreated = false;
    if (chatMessages) {
        chatMessages.innerHTML = '';
        chatMessages.appendChild(welcomeScreen);
        welcomeScreen.style.display = 'flex';
    }
    const basePageUrl = window.location.origin + '/imara-ai';
    window.history.pushState(null, '', basePageUrl);
    messageInput?.focus();
}

// ── Delete conversation modal ─────────────────────────────────────────────
const deleteConvoModal  = document.getElementById('deleteConvoModal');
const modalCancelBtn    = document.getElementById('modalCancelBtn');
const modalConfirmBtn   = document.getElementById('modalConfirmBtn');
const modalIcon         = document.getElementById('modalIcon');
const modalTitle        = document.getElementById('modalTitle');
const modalBody         = document.getElementById('modalBody');
const modalConvoList    = document.getElementById('modalConvoList');
let pendingDeleteId     = null;   // set for single-delete mode
let pendingBulkIds      = null;   // set for bulk-delete mode

function showDeleteModal(convoId) {
    pendingDeleteId = convoId;
    pendingBulkIds  = null;
    modalIcon.className  = 'modal-icon';
    modalIcon.innerHTML  = '<i class="mdi mdi-delete-outline"></i>';
    modalTitle.textContent = 'Delete conversation?';
    modalBody.textContent  = 'This conversation will be permanently removed from your history. This cannot be undone.';
    modalBody.style.display = '';
    modalConvoList.style.display = 'none';
    modalConvoList.innerHTML = '';
    modalConfirmBtn.textContent = 'Delete';
    deleteConvoModal.style.display = 'flex';
    document.getElementById('imara-ai-root').classList.add('modal-open');
}

function showBulkDeleteModal(ids) {
    pendingBulkIds  = ids;
    pendingDeleteId = null;
    const n = ids.length;

    modalIcon.className  = 'modal-icon bulk';
    modalIcon.innerHTML  = '<i class="mdi mdi-delete-sweep-outline"></i>';
    modalTitle.textContent = `Delete ${n} conversation${n !== 1 ? 's' : ''}?`;
    modalBody.textContent  = `These will be permanently removed. This cannot be undone.`;
    modalBody.style.display = '';

    // Populate the scrollable list of titles
    const titles = ids.map(id => {
        const c = conversations.find(x => x.id === id);
        return c ? c.title : `Conversation #${id}`;
    });
    modalConvoList.innerHTML = titles.map(t =>
        `<div class="modal-convo-list-item"><i class="mdi mdi-chat-outline"></i><span title="${escapeHtml(t)}">${escapeHtml(t)}</span></div>`
    ).join('');
    modalConvoList.style.display = 'block';

    modalConfirmBtn.textContent = `Delete ${n}`;
    deleteConvoModal.style.display = 'flex';
    document.getElementById('imara-ai-root').classList.add('modal-open');
}

function hideDeleteModal() {
    deleteConvoModal.style.display = 'none';
    document.getElementById('imara-ai-root').classList.remove('modal-open');
    pendingDeleteId = null;
    pendingBulkIds  = null;
    modalConvoList.style.display = 'none';
    modalConvoList.innerHTML = '';
}

modalCancelBtn.addEventListener('click', hideDeleteModal);

deleteConvoModal.addEventListener('click', (e) => {
    if (e.target === deleteConvoModal) hideDeleteModal();
});

modalConfirmBtn.addEventListener('click', async () => {
    if (pendingBulkIds !== null) {
        // ── Bulk delete ──
        const ids = pendingBulkIds;
        hideDeleteModal();
        try {
            const res = await fetch('{{ route("ai.conversations.bulk-delete") }}', {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                body: JSON.stringify({ ids }),
            });
            if (!res.ok) throw new Error('Server error');
            conversations = conversations.filter(c => !ids.includes(c.id));
            if (ids.includes(currentConvoId)) startNewConversation();
        } catch (e) {
            console.error('Failed to bulk delete conversations', e);
        }
        exitSelectMode();
    } else if (pendingDeleteId !== null) {
        // ── Single delete ──
        const idToDelete = pendingDeleteId;
        const wasActive  = currentConvoId === idToDelete;
        hideDeleteModal();
        try {
            await fetch(`${CONVO_MESSAGES_BASE}/${idToDelete}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
            });
        } catch (e) {
            console.error('Failed to delete conversation', e);
        }
        conversations = conversations.filter(x => x.id !== idToDelete);
        renderSidebarHistory();
        if (wasActive) startNewConversation();
    }
});

// ── Conversation history ──────────────────────────────────────────────────
function showSidebarSkeleton() {
    sidebarHistory.innerHTML = [1, 2, 3, 4].map(() => `
        <div class="history-skeleton">
            <div class="sk-icon"></div>
            <div class="sk-line"></div>
        </div>`).join('');
}

function hideSidebarSkeleton() {
    sidebarHistory.querySelectorAll('.history-skeleton').forEach(el => el.remove());
}

function showSidebarError() {
    sidebarHistory.innerHTML = `
        <div class="sidebar-load-error">
            <i class="mdi mdi-wifi-off"></i>
            <span>Couldn't load history</span>
            <button id="retrySidebarLoad">Retry</button>
        </div>`;
    document.getElementById('retrySidebarLoad')?.addEventListener('click', loadConversationsFromBackend);
}

async function loadConversationsFromBackend(filter) {
    showSidebarSkeleton();
    try {
        const url = filter ? `${CONVOS_URL}?filter=${encodeURIComponent(filter)}` : CONVOS_URL;
        const res  = await fetch(url, { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN } });
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const json = await res.json();
        if (!filter) conversations = json.conversations || []; // only cache unfiltered full list
        renderSidebarHistory(null, json.conversations || []);
    } catch (e) {
        console.error('Failed to load conversations', e);
        showSidebarError();
    }
}


async function createConversationOnServer(title) {
    const res = await fetch(CREATE_CONVO_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
        body: JSON.stringify({ title }),
    });
    if (!res.ok) throw new Error(`Server returned ${res.status}`);
    const json = await res.json();
    if (!json.conversation || !json.conversation.id) throw new Error('Invalid conversation response');
    return json.conversation;
}

async function persistMessage(convoId, role, content, sources, metadata) {
    try {
        const res  = await fetch(`${CONVO_MESSAGES_BASE}/${convoId}/messages`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
            body: JSON.stringify({ 
                role, 
                content, 
                sources: sources || null,
                metadata: metadata || null
            }),
        });
        const json = await res.json();
        return json.message?.id ?? null;
    } catch (e) {
        console.error('Failed to persist message', e);
        return null;
    }
}

async function loadConversationMessages(convoId) {
    chatMessages.innerHTML = '';
    showThinking();
    try {
        const res  = await fetch(`${CONVO_MESSAGES_BASE}/${convoId}/messages`, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
        });
        const json = await res.json();
        hideThinking();
        (json.messages || []).forEach(msg => {
            if (msg.role === 'user') {
                addUserMessage(msg.content, msg.created_at, msg.metadata);
            } else {
                addBotMessage(msg.content, msg.sources || [], msg.created_at, { id: msg.id }, msg.metadata);
            }
        });
        if (!json.messages || !json.messages.length) {
            chatMessages.appendChild(welcomeScreen);
            welcomeScreen.style.display = 'flex';
        }
    } catch (e) {
        hideThinking();
        console.error('Failed to load messages', e);
    }
}

async function saveToHistory(userMessage) {
    if (currentConvoCreated) return; // already created
    try {
        const convo = await createConversationOnServer(userMessage.slice(0, 60));
        currentConvoId      = convo.id;
        currentConvoCreated = true;
        conversations.unshift(convo);
        renderSidebarHistory();
        
        const basePageUrl = window.location.origin + '/imara-ai';
        const newUrl = `${basePageUrl}/${convo.id}`;
        window.history.pushState({ convoId: convo.id }, '', newUrl);
    } catch (e) {
        console.error('Failed to create conversation', e);
    }
}

function renderSidebarHistory(filter, list) {
    hideSidebarSkeleton();
    const source = list ?? conversations;
    const q = (filter || '').toLowerCase().trim();
    // Client-side title filter when no server search list was passed
    const visible = (!list && q) ? source.filter(c => c.title.toLowerCase().includes(q)) : source;

    sidebarHistory.innerHTML = '';
    if (!conversations.length) {
        sidebarHistory.innerHTML = '<div style="padding:8px 10px;font-size:0.78rem;color:#444;">No conversations yet</div>';
        return;
    }
    if (!visible.length) {
        sidebarHistory.innerHTML = '<div style="padding:8px 10px;font-size:0.78rem;color:#666;">No matches</div>';
        return;
    }
    visible.forEach(c => {
        const item = document.createElement('div');
        item.className = 'history-item' + (c.id === currentConvoId ? ' active' : '');
        item.title = c.title;

        const label = document.createElement('span');
        label.className = 'history-label';
        label.textContent = c.title;
        label.title = 'Double-click to rename';

        // Add context badge (e.g. LAB, MAS)
        let contextBadge = null;
        if (c.context && c.context !== 'general') {
            contextBadge = document.createElement('span');
            contextBadge.className = `context-badge context-${c.context}`;
            contextBadge.textContent = c.context;
        }

        const delBtn = document.createElement('button');
        delBtn.className = 'delete-convo-btn';
        delBtn.title = 'Delete conversation';
        delBtn.innerHTML = '<i class="mdi mdi-close"></i>';
        delBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            showDeleteModal(c.id);
        });

        // Bulk-select checkbox (visible only in select mode via CSS)
        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.className = 'history-checkbox';
        checkbox.checked = selectedConvoIds.has(c.id);
        checkbox.addEventListener('change', (e) => {
            e.stopPropagation();
            if (checkbox.checked) selectedConvoIds.add(c.id);
            else selectedConvoIds.delete(c.id);
            if (btnBulkDelete) btnBulkDelete.disabled = selectedConvoIds.size === 0;
            if (selectAllConvos) {
                selectAllConvos.checked = selectedConvoIds.size === conversations.length;
                selectAllConvos.indeterminate = selectedConvoIds.size > 0 && selectedConvoIds.size < conversations.length;
            }
        });

        item.appendChild(checkbox);
        item.appendChild(document.createElement('i')).className = 'mdi mdi-chat-outline';
        item.appendChild(label);
        if (contextBadge) item.appendChild(contextBadge);
        item.appendChild(delBtn);

        // Use a timer to distinguish single-click (load) from double-click (rename).
        // Without this, dblclick fires two 'click' events first, loading the conversation twice.
        let clickTimer = null;
        item.addEventListener('click', (e) => {
            // Ignore clicks that originated from the delete button or checkbox
            if (e.target.closest('.delete-convo-btn') || e.target.closest('.history-checkbox')) return;
            if (bulkSelectMode) {
                checkbox.checked = !checkbox.checked;
                checkbox.dispatchEvent(new Event('change'));
                return;
            }
            if (clickTimer) {
                // Second click within 280ms → treat as rename intent
                clearTimeout(clickTimer);
                clickTimer = null;
                startRename(label, c);
                return;
            }
            clickTimer = setTimeout(async () => {
                clickTimer = null;
                currentConvoId      = c.id;
                currentConvoCreated = true;
                renderSidebarHistory();
                await loadConversationMessages(c.id);
                
                const basePageUrl = window.location.origin + '/imara-ai';
                const newUrl = `${basePageUrl}/${c.id}`;
                window.history.pushState({ convoId: c.id }, '', newUrl);

                // Auto-close menu on mobile after selection
                if (window.innerWidth <= 768 && sidebar.classList.contains('active')) {
                    toggleMobileMenu();
                }
            }, 280);

        });
        sidebarHistory.appendChild(item);
    });
}

function startRename(labelEl, convo) {
    const input = document.createElement('input');
    input.type  = 'text';
    input.value = convo.title;
    input.className = 'rename-input';
    labelEl.replaceWith(input);
    input.focus();
    input.select();

    async function commitRename() {
        const newTitle = input.value.trim() || convo.title;
        // Replace input back with label immediately (optimistic)
        const newLabel = document.createElement('span');
        newLabel.className = 'history-label';
        newLabel.textContent = newTitle;
        newLabel.title = 'Double-click to rename';
        input.replaceWith(newLabel);

        if (newTitle === convo.title) return;
        convo.title = newTitle;
        try {
            await fetch(`${CONVO_MESSAGES_BASE}/${convo.id}`, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
                body: JSON.stringify({ title: newTitle }),
            });
        } catch (e) {
            console.error('Failed to rename conversation', e);
        }
    }

    input.addEventListener('blur', commitRename);
    input.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') { e.preventDefault(); input.blur(); }
        if (e.key === 'Escape') { input.value = convo.title; input.blur(); }
    });
}

// ── Suggestion chips ──────────────────────────────────────────────────────
function useChip(el) {
    // Clone, strip icons, get clean text only
    const clone = el.cloneNode(true);
    clone.querySelectorAll('i, svg').forEach(n => n.remove());
    const text = clone.innerText.trim();
    if (!text) return;
    messageInput.value = text;
    messageInput.dispatchEvent(new Event('input'));
    sendMessage();
}
window.useChip = useChip;

// ── Auto-resize textarea ──────────────────────────────────────────────────
messageInput?.addEventListener('input', function () {
    this.style.height = 'auto';
    this.style.height = Math.min(this.scrollHeight, 160) + 'px';
});

// ── API call ──────────────────────────────────────────────────────────────
// ── @Mention Logic ────────────────────────────────────────────────────────
const mentionSuggestions = document.getElementById('mentionSuggestions');
const selectedReferencesBar = document.getElementById('selectedReferencesBar');

function updateMentionSuggestions(query) {
    console.log('[Mention] Searching for:', query);
    // Trigger search immediately, even if query is empty (just @)
    const url = query ? `${LOOKUP_DOCS_URL}?q=${encodeURIComponent(query)}` : LOOKUP_DOCS_URL;

    fetch(url)
        .then(res => res.json())
        .then(data => {
            mentionResults = data.results || [];
            if (mentionResults.length === 0) {
                mentionSuggestions.style.display = 'none';
                return;
            }
            renderMentionList();
        })
        .catch(err => console.error('Mention lookup failed', err));
}

function renderMentionList() {
    mentionIndex = -1;
    mentionSuggestions.innerHTML = mentionResults.map((doc, i) => `
        <div class="mention-item" data-index="${i}" onclick="selectMention(${i})">
            <i class="mdi mdi-file-document-outline"></i>
            <span>${escapeHtml(doc.title)}</span>
        </div>
    `).join('');
    mentionSuggestions.style.display = 'block';
}

function selectMention(index) {
    const doc = mentionResults[index];
    if (!doc) return;

    // Add to selected array if not already there
    if (!selectedReferences.find(r => r.id === doc.id)) {
        selectedReferences.push(doc);
        renderSelectedReferences();
    }

    // Replace @search with just a space
    const val = messageInput.value;
    const lastAt = val.lastIndexOf('@');
    messageInput.value = val.substring(0, lastAt).trim() + ' ';
    
    mentionSuggestions.style.display = 'none';
    mentionSearchActive = false;
    messageInput.focus();
}

function renderSelectedReferences() {
    if (!selectedReferencesBar) return;
    if (selectedReferences.length === 0) {
        selectedReferencesBar.style.display = 'none';
        return;
    }

    selectedReferencesBar.innerHTML = selectedReferences.map((ref, i) => `
        <div class="reference-tag">
            <i class="mdi mdi-file-link-outline"></i>
            <span>${escapeHtml(ref.title)}</span>
            <span class="remove-ref" onclick="removeReference(${i})">&times;</span>
        </div>
    `).join('');
    selectedReferencesBar.style.display = 'flex';
}

function removeReference(index) {
    selectedReferences.splice(index, 1);
    renderSelectedReferences();
}
window.selectMention = selectMention;
window.removeReference = removeReference;

async function fetchKnowledge(userInput, attachmentId, manualReferenceIds) {
    currentAbortController = new AbortController();
    const body = { query: userInput, limit: 5, language: IMARA_AI_LANGUAGE };
    if (attachmentId) body.attachment_id = attachmentId;
    
    // Support passed manualReferenceIds (for retries) or use currently selected ones
    const refIds = manualReferenceIds || (selectedReferences ? selectedReferences.map(r => r.id) : []);
    if (refIds.length > 0) {
        body.reference_ids = refIds;
    }
    try {
    const response = await fetch(KNOWLEDGE_SEARCH_URL, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF_TOKEN,
            'Accept': 'application/json',
        },
        body: JSON.stringify(body),
        signal: currentAbortController.signal,
    });

    if (!response.ok) {
        const err = await response.text();
        throw new Error(`Server error (${response.status}): ${err || response.statusText}`);
    }

    const json = await response.json();

    if (!json || json.status !== 'ok') {
        return { reply: json?.message || 'AI search returned no status.', sources: [] };
    }

    const results = Array.isArray(json.results) ? json.results : [];
    const isLiveData = json.mode === 'live_data';

    if (!results.length) {
        return {
            reply: 'No relevant knowledge found for that query. Try indexing knowledge first:\n`php artisan ai:index-knowledge`',
            sources: [],
        };
    }

    if (isLiveData) {
        // Single live-data result — render reply directly without the numbered list wrapper
        const row  = results[0];
        const meta = row.metadata || {};
        const sources = [{
            label: 'Live Database',
            isLive: true,
        }];
        if (meta.intent) {
            sources.push({ label: `intent: ${meta.intent}`, isLive: true });
        }
        // Propagate prediction metadata (risk_level, confidence, explanation, metric) if present
        const returnMeta = {};
        if (meta.risk_level)  returnMeta.risk_level  = meta.risk_level;
        if (meta.confidence)  returnMeta.confidence  = meta.confidence;
        if (meta.explanation) returnMeta.explanation = meta.explanation;
        if (meta.metric)      returnMeta.metric      = meta.metric;
        return { reply: row.content, sources, metadata: returnMeta };
    }

    // RAG path — numbered list of retrieved chunks
    const lines = results.map((row, i) => {
        const src = [row.collection_name, row.entity_type].filter(Boolean).join(' › ');
        return `**${i + 1}. ${src || 'Knowledge Base'}**\n${row.content}`;
    }).join('\n\n---\n\n');

    const sources = results.map(r => ({
        label: [r.collection_name, r.entity_type, r.entity_id ? `#${r.entity_id}` : null].filter(Boolean).join(' · '),
        isLive: false,
        entityId: r.entity_id,
        collectionName: r.collection_name,
        entityType: r.entity_type,
    }));

    return { reply: lines, sources };
    } catch (err) {
        if (err.name === 'AbortError') return { aborted: true };
        throw err;
    }
}

// ── Markdown renderer ─────────────────────────────────────────────────────
// Chart fenced blocks (```chart ... ```) are extracted FIRST,
// replaced with a placeholder, then re-injected as canvas after full parse.
function renderMarkdown(text) {
    // Extract chart blocks before other processing
    const chartBlocks = [];
    text = text.replace(/```chart\n([\s\S]*?)\n```/g, (_, json) => {
        const idx = chartBlocks.length;
        chartBlocks.push(json.trim());
        return `__CHART_PLACEHOLDER_${idx}__`;
    });

    // ── HTML-escape all raw text before regex transforms.
    // This neutralises any injected markup (e.g. <img onerror=...>) that
    // may arrive via direct user input or RAG-poisoned documents.
    // Chart placeholders (__CHART_PLACEHOLDER_N__) contain no HTML-special
    // characters, so they survive this step unchanged.
    text = text
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');

    // Code blocks (must come before inline code)
    text = text.replace(/```([\s\S]*?)```/g, '<pre><code>$1</code></pre>');
    // Inline code
    text = text.replace(/`([^`]+)`/g, '<code>$1</code>');
    // Bold
    text = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
    // Italic
    text = text.replace(/\*(.*?)\*/g, '<em>$1</em>');
    // HR
    text = text.replace(/^---$/gm, '<hr>');
    // H2
    text = text.replace(/^## (.+)$/gm, '<h2>$1</h2>');
    // H3
    text = text.replace(/^### (.+)$/gm, '<h3>$1</h3>');
    // Markdown tables: detect header row followed by separator row
    text = text.replace(
        /(\|.+\|\n\|[-| :]+\|\n(?:\|.+\|\n?)+)/g,
        match => {
            const rows = match.trim().split('\n');
            const header = rows[0].split('|').filter(c => c.trim() !== '').map(c => `<th>${c.trim()}</th>`).join('');
            const body = rows.slice(2).map(r =>
                '<tr>' + r.split('|').filter(c => c.trim() !== '').map(c => `<td>${c.trim()}</td>`).join('') + '</tr>'
            ).join('');
            return `<table><thead><tr>${header}</tr></thead><tbody>${body}</tbody></table>`;
        }
    );
    // Numbered list items
    text = text.replace(/^\d+\. (.+)$/gm, '<li>$1</li>');
    // Bullet list items
    text = text.replace(/^[-•] (.+)$/gm, '<li>$1</li>');
    // Paragraphs (wrap non-tag lines in <p>)
    const parts = text.split('\n\n');
    text = parts.map(p => {
        p = p.trim();
        if (!p) return '';
        if (p.startsWith('<')) return p;
        return `<p>${p.replace(/\n/g, '<br>')}</p>`;
    }).join('');

    // Re-inject chart placeholders as canvas elements
    chartBlocks.forEach((json, idx) => {
        const canvasId = `imara-chart-${Date.now()}-${idx}`;
        const placeholder = `__CHART_PLACEHOLDER_${idx}__`;
        const canvasHtml = `<div class="chart-container"><canvas id="${canvasId}" data-chart='${json.replace(/'/g, "&#39;")}'></canvas></div>`;
        text = text.replace(placeholder, canvasHtml);
    });

    // ── DOMPurify sanitize the final HTML as defence-in-depth.
    // Strips any event handlers or dangerous elements that escaped Phase 1.
    // data-chart is explicitly allowed so Chart.js canvas injection works.
    if (typeof DOMPurify !== 'undefined') {
        text = DOMPurify.sanitize(text, { ADD_ATTR: ['data-chart'] });
    }

    return text;
}

// Chart initialization is now dynamically handled via window.initCharts in the visualization script.

// ── DOM helpers ───────────────────────────────────────────────────────────
function formatTimestamp(isoOrDate) {
    const d = isoOrDate ? new Date(isoOrDate) : new Date();
    if (isNaN(d)) return '';
    const now   = new Date();
    const today = now.toDateString() === d.toDateString();
    const hm    = d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    if (today) return hm;
    return d.toLocaleDateString([], { month: 'short', day: 'numeric' }) + ' · ' + hm;
}

function escapeHtml(str) {
    return str
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

// ── Source citation helpers ────────────────────────────────────────────────
function getSourceUrl(source) {
    if (source.isLive || !source.entityId) return null;
    const col  = (source.collectionName || '').toLowerCase().trim();
    const type = (source.entityType     || '').split('\\').pop().toLowerCase().trim();
    const id   = source.entityId;
    const map  = {
        'sops': `/documents/${id}`,
        'corrective_actions': `/audit/corrective-actions/${id}`,
        'anomalies': `/audit/non-conformances/${id}`,
        'findings': `/audit/non-conformances/${id}`,
        'incidents': `/show-complaint/${id}`,
        'audits': `/audit/audits/${id}`,
    };
    const typeMap = {
        'document': `/documents/${id}`,
        'correctiveaction': `/audit/corrective-actions/${id}`,
        'nonconformance': `/audit/non-conformances/${id}`,
        'complaint': `/show-complaint/${id}`,
    };
    return map[col] || typeMap[type] || null;
}

function renderSources(sources) {
    if (!sources || !sources.length) return '';
    const tags = sources.map(s => {
        if (s.isLive) {
            return `<span class="source-tag live-data-tag"><i class="mdi mdi-database-flash-outline"></i> ${escapeHtml(s.label)}</span>`;
        }
        const url = getSourceUrl(s);
        if (url) {
            return `<a href="${url}" target="_blank" rel="noopener noreferrer" class="source-tag source-link" title="Open in LIMS"><i class="mdi mdi-link-variant"></i> ${escapeHtml(s.label)}</a>`;
        }
        return `<span class="source-tag">${escapeHtml(s.label)}</span>`;
    }).join('');
    return `<div class="msg-sources">${tags}</div>`;
}

function renderManualReferences(refs) {
    if (!refs || refs.length === 0) return '';
    return `
        <div class="message-manual-references">
            <div class="ref-label"><i class="mdi mdi-book-open-variant"></i> Referenced Documents:</div>
            ${refs.map(r => `<span class="msg-ref-tag">${escapeHtml(r.title)}</span>`).join('')}
        </div>`;
}

function addUserMessage(text, isoTimestamp, metadata) {
    metadata = metadata || {};
    const row = document.createElement('div');
    row.className = 'msg-row user-row';
    row.id = `user-msg-${Date.now()}`; // Unique ID for linking edited messages
    if (metadata.parent_message_id) {
        row.setAttribute('data-parent-id', metadata.parent_message_id);
    }
    if (metadata.is_edited) {
        row.setAttribute('data-edited', 'true');
    }

    let editBadge = '';
    if (metadata.is_edited) {
        editBadge = '<span class="msg-edit-badge">Edited</span>';
    }

    const row_innerHTML = `
        <div class="user-bubble-wrap">
            <div class="user-bubble" data-refs='${JSON.stringify(metadata.manual_references || [])}'>
                ${escapeHtml(text)}
                ${renderManualReferences(metadata.manual_references)}
            </div>
            <div class="msg-row-actions">
                <button class="msg-action-btn copy-btn" title="Copy message"><i class="mdi mdi-content-copy"></i></button>
                <button class="msg-action-btn edit-btn" title="Edit message"><i class="mdi mdi-pencil-outline"></i></button>
            </div>
            ${editBadge}
            <div class="msg-timestamp">${formatTimestamp(isoTimestamp)}</div>
        </div>`;
    row.innerHTML = row_innerHTML;

    // Edit: populate input and show confirm button
    let isEditingMode = false;
    const editBtn = row.querySelector('.edit-btn');
    editBtn.addEventListener('click', () => {
        if (!isEditingMode) {
            // Enter edit mode
            isEditingMode = true;
            messageInput.value = text;
            messageInput.dispatchEvent(new Event('input')); // resize textarea
            messageInput.focus();
            editBtn.innerHTML = '<i class="mdi mdi-check"></i>';
            editBtn.title = 'Confirm edit';
            editBtn.classList.add('edit-confirm-btn');
        } else {
            // Confirm edit: save as new message with parent_message_id
            const editedText = messageInput.value.trim();
            if (!editedText) {
                alert('Message cannot be empty');
                return;
            }
            // Clear edit mode UI
            messageInput.value = '';
            messageInput.dispatchEvent(new Event('input'));
            isEditingMode = false;
            editBtn.innerHTML = '<i class="mdi mdi-pencil-outline"></i>';
            editBtn.title = 'Edit message';
            editBtn.classList.remove('edit-confirm-btn');
            
            // Save original message as edited and send new query
            persistMessage(currentConvoId || 'new', 'user', text, null, {
                parent_message_id: null,
                is_edited: true,
            });
            // Send edited message as new user query
            addUserMessage(editedText, new Date().toISOString(), {
                parent_message_id: metadata.id || null,
                is_edited: false,
            });
            // Fetch new response
            sendMessage(editedText);
        }
    });
    
    // Copy logic for user message
    const userCopyBtn = row.querySelector('.copy-btn');
    userCopyBtn?.addEventListener('click', () => {
        navigator.clipboard.writeText(text).then(() => {
            userCopyBtn.innerHTML = '<i class="mdi mdi-check"></i>';
            userCopyBtn.classList.add('copied');
            setTimeout(() => {
                userCopyBtn.innerHTML = '<i class="mdi mdi-content-copy"></i>';
                userCopyBtn.classList.remove('copied');
            }, 2000);
        });
    });

    chatMessages.appendChild(row);
    scrollToBottom();
}

// ── Prediction bubble helpers ─────────────────────────────────────────────────

function isPredictionResponse(result) {
    const meta = result && result.results && result.results[0] && result.results[0].metadata;
    return !!(meta && meta.risk_level);
}

function renderPredictionBubble(text, sources, metadata) {
    const risk      = (metadata && metadata.risk_level) || 'low';
    const conf      = Math.min(100, Math.max(0, (metadata && metadata.confidence) || 50));
    const explain   = (metadata && metadata.explanation) || '';
    const metric    = (metadata && metadata.metric)      || 'Risk Assessment';

    const ICON = { high: '🔴', medium: '🟡', low: '🟢' };
    const LABEL = { high: 'HIGH RISK', medium: 'MODERATE', low: 'LOW RISK' };
    const icon  = ICON[risk]  || '⚪';
    const label = LABEL[risk] || risk.toUpperCase();

    const sourcesHtml = renderSources(sources);

    return `<div class="prediction-bubble risk-${escapeHtml(risk)}">
        <span class="risk-badge risk-${escapeHtml(risk)}">${icon} ${label}</span>
        <div class="confidence-wrap">
            <span>${escapeHtml(metric)} · ${conf}% confidence</span>
            <div class="confidence-track">
                <div class="confidence-fill" style="width:${conf}%"></div>
            </div>
        </div>
        ${sourcesHtml}
        ${explain ? `<button class="reasoning-toggle" onclick="(function(btn){
            const txt = btn.nextElementSibling;
            const open = txt.classList.toggle('open');
            btn.classList.toggle('open', open);
        })(this)"><span class="toggle-icon mdi mdi-chevron-right"></span> Reasoning</button>
        <div class="reasoning-text">${escapeHtml(explain)}</div>` : ''}
    </div>`;
}

function addBotMessage(text, sources, isoTimestamp, msgIdRef, metadata) {
    // msgIdRef = { id: null|number } — populated after server persist, read at feedback click-time
    msgIdRef = msgIdRef || { id: null };
    metadata = metadata || {};
    sources = sources || [];
    const row = document.createElement('div');
    row.className = 'msg-row bot-row';
    if (metadata.parent_message_id) {
        row.setAttribute('data-parent-id', metadata.parent_message_id);
    }
    if (metadata.retry_count) {
        row.setAttribute('data-retry-count', metadata.retry_count);
    }

    let retryLabel = '';
    if (metadata.retry_count && metadata.retry_count > 0) {
        retryLabel = `<span class="msg-retry-badge">Retry ${metadata.retry_count}</span>`;
    }

    const hasPrediction = !!(metadata.risk_level);
    const predictionHtml = hasPrediction
        ? renderPredictionBubble(text, sources, metadata)
        : '';

    row.innerHTML = `
        <div class="bot-bubble">
            <div class="msg-avatar bot-av"><i class="mdi mdi-head-lightbulb"></i></div>
            <div class="msg-body">
                <div class="msg-name">Imara AI <span class="msg-timestamp">${formatTimestamp(isoTimestamp)}</span>${retryLabel}</div>
                <div class="msg-content" data-raw="${escapeHtml(text)}">
                    ${renderMarkdown(text)}
                    ${renderManualReferences(metadata.manual_references)}
                </div>
                ${hasPrediction ? predictionHtml : renderSources(sources)}
                <div class="msg-row-actions">
                    <button class="msg-action-btn speak-btn" title="Speak response"><i class="mdi mdi-volume-high"></i></button>
                    <button class="msg-action-btn retry-btn" title="Retry for alternative answer"><i class="mdi mdi-refresh"></i></button>
                    <button class="msg-action-btn copy-btn" title="Copy response"><i class="mdi mdi-content-copy"></i></button>
                    <button class="msg-action-btn thumb-up-btn" title="Good response"><i class="mdi mdi-thumb-up-outline"></i></button>
                    <button class="msg-action-btn thumb-down-btn" title="Bad response"><i class="mdi mdi-thumb-down-outline"></i></button>
                </div>

            </div>
        </div>`;
    chatMessages.appendChild(row);

    // Initialise any Chart.js canvases injected by the markdown renderer
    window.initCharts(row);

    const speakBtn   = row.querySelector('.speak-btn');
    const retryBtn   = row.querySelector('.retry-btn');
    const copyBtn    = row.querySelector('.copy-btn');
    const thumbUpBtn = row.querySelector('.thumb-up-btn');
    const thumbDnBtn = row.querySelector('.thumb-down-btn');
    const msgContent = row.querySelector('.msg-content');

    // TTS
    speakBtn?.addEventListener('click', () => {
        if (speakBtn.classList.contains('speaking')) {
            window.speechSynthesis.cancel();
            speakBtn.classList.remove('speaking');
            speakBtn.innerHTML = '<i class="mdi mdi-volume-high"></i>';
            return;
        }
        
        // Stop any other active speech
        window.speechSynthesis.cancel();
        document.querySelectorAll('.speak-btn.speaking').forEach(btn => {
            btn.classList.remove('speaking');
            btn.innerHTML = '<i class="mdi mdi-volume-high"></i>';
        });

        speakMessage(text, speakBtn);
    });


    // Retry: find original user message and resend
    retryBtn.addEventListener('click', async () => {
        // Find the user message that preceded this bot message
        const siblings = [...chatMessages.children];
        const msgRowIdx = siblings.indexOf(row);
        let userMessage = null;
        for (let i = msgRowIdx - 1; i >= 0; i--) {
            if (siblings[i].classList.contains('user-row')) {
                userMessage = siblings[i];
                break;
            }
        }
        if (!userMessage) {
            console.error('Could not find user message for retry');
            return;
        }
        const userBubble = userMessage.querySelector('.user-bubble');
        const userText = userBubble?.innerText || '';
        const userRefs = JSON.parse(userBubble?.getAttribute('data-refs') || '[]');
        const userRefIds = userRefs.map(r => r.id);

        const currentRetryCount = (metadata.retry_count || 0) + 1;
        
        // Show thinking indicator
        showThinking();
        
        try {
            // Fetch new response with same references
            const result = await fetchKnowledge(userText, null, userRefIds);
            hideThinking();
            
            if (result.aborted) return; // silently cancelled
            if (result.error) {
                addBotMessage(result.error, [], new Date().toISOString(), {}, {
                    parent_message_id: msgIdRef.id,
                    retry_count: currentRetryCount,
                    manual_references: userRefs
                });
            } else {
                const retryMsgIdRef = { id: null };
                const retryMeta = Object.assign({}, result.metadata || {}, {
                    parent_message_id: msgIdRef.id,
                    retry_count: currentRetryCount,
                    manual_references: userRefs
                });
                addBotMessage(result.reply, result.sources, new Date().toISOString(), retryMsgIdRef, retryMeta);
                if (currentConvoId) {
                    persistMessage(currentConvoId, 'bot', result.reply, result.sources, retryMeta)
                    .then(id => { retryMsgIdRef.id = id; });
                }
            }
        } catch (e) {
            hideThinking();
            console.error('Retry fetch failed:', e);
            addBotMessage('Error fetching alternative response. Please try again.', [], new Date().toISOString());
        }
    });

    // Copy
    copyBtn.addEventListener('click', () => {
        const raw = msgContent.dataset.raw || msgContent.innerText;
        navigator.clipboard.writeText(raw).then(() => {
            copyBtn.innerHTML = '<i class="mdi mdi-check"></i>';
            copyBtn.classList.add('copied');
            setTimeout(() => {
                copyBtn.innerHTML = '<i class="mdi mdi-content-copy"></i>';
                copyBtn.classList.remove('copied');
            }, 2000);
        });
    });

    // Feedback — reads msgIdRef.id at click-time (populated async after persist)
    function sendFeedback(rating) {
        if (!currentConvoId || !msgIdRef.id) return;
        fetch(`${CONVO_MESSAGES_BASE}/${currentConvoId}/messages/${msgIdRef.id}/feedback`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
            body: JSON.stringify({ rating }),
        }).catch(e => console.error('Feedback save failed', e));
    }

    thumbUpBtn.addEventListener('click', () => {
        const wasActive = thumbUpBtn.classList.contains('active-thumb');
        thumbUpBtn.classList.toggle('active-thumb', !wasActive);
        thumbUpBtn.innerHTML = !wasActive ? '<i class="mdi mdi-thumb-up"></i>' : '<i class="mdi mdi-thumb-up-outline"></i>';
        thumbDnBtn.classList.remove('active-thumb-down');
        thumbDnBtn.innerHTML = '<i class="mdi mdi-thumb-down-outline"></i>';
        sendFeedback(wasActive ? 'none' : 'up');
    });

    thumbDnBtn.addEventListener('click', () => {
        const wasActive = thumbDnBtn.classList.contains('active-thumb-down');
        thumbDnBtn.classList.toggle('active-thumb-down', !wasActive);
        thumbDnBtn.innerHTML = !wasActive ? '<i class="mdi mdi-thumb-down"></i>' : '<i class="mdi mdi-thumb-down-outline"></i>';
        thumbUpBtn.classList.remove('active-thumb');
        thumbUpBtn.innerHTML = '<i class="mdi mdi-thumb-up-outline"></i>';
        sendFeedback(wasActive ? 'none' : 'down');
    });

    scrollToBottom();
}

function addErrorMessage(text) {
    const row = document.createElement('div');
    row.className = 'msg-row bot-row';
    row.innerHTML = `
        <div class="bot-bubble">
            <div class="msg-avatar bot-av"><i class="mdi mdi-head-lightbulb"></i></div>
            <div class="msg-body">
                <div class="msg-name">ImaraChat AI</div>
                <div class="msg-content error-msg">${escapeHtml(text)}</div>
            </div>
        </div>`;
    chatMessages.appendChild(row);
    scrollToBottom();
}

function showThinking() {
    thinkingRow = document.createElement('div');
    thinkingRow.className = 'msg-row bot-row';
    thinkingRow.innerHTML = `
        <div class="bot-bubble">
            <div class="msg-avatar bot-av"><i class="mdi mdi-head-lightbulb"></i></div>
            <div class="msg-body">
                <div class="msg-name">ImaraChat AI</div>
                <div class="msg-content">
                    <div class="thinking-dots">
                        <span></span><span></span><span></span>
                    </div>
                </div>
            </div>
        </div>`;
    chatMessages.appendChild(thinkingRow);
    scrollToBottom();
}

function hideThinking() {
    if (thinkingRow) { thinkingRow.remove(); thinkingRow = null; }
}

function scrollToBottom() {
    setTimeout(() => { chatMessages.scrollTop = chatMessages.scrollHeight; }, 80);
}

function hideWelcome() {
    if (welcomeScreen && welcomeScreen.parentNode === chatMessages) {
        welcomeScreen.remove();
    }
}

// ── Send ──────────────────────────────────────────────────────────────────
async function sendMessage() {
    // Guard: Prevent submission while cleanup or sending is in progress
    if (isCleaningUp || isSending) {
        console.warn('[Send] Submission blocked - sending or cleanup in progress');
        return;
    }
    
    const message = messageInput.value.trim();
    if (!message) return;

    hideWelcome();

    const refsToPersist = selectedReferences.map(r => ({ id: r.id, title: r.title }));
    const refIdsToSend  = selectedReferences.map(r => r.id);

    const historyReady = saveToHistory(message);
    addUserMessage(message, null, { manual_references: refsToPersist });
    historyReady.then(() => {
        if (currentConvoId) persistMessage(currentConvoId, 'user', message, null, { manual_references: refsToPersist });
    });

    messageInput.value        = '';
    selectedReferences        = [];
    renderSelectedReferences();
    messageInput.style.height = 'auto';
    sendButton.style.display  = 'none';
    stopButton.style.display  = 'flex';
    
    // Use readOnly instead of disabled to keep keyboard open on mobile
    messageInput.readOnly     = true;
    isSending                 = true;
    showThinking();

    currentAbortController = new AbortController();
    console.log('[Send] Message submitted, AbortController created:', {
        hasAbortController: !!currentAbortController,
        message: message.substring(0, 50) + (message.length > 50 ? '...' : '')
    });

    try {
        const res = await fetch(ASK_STREAM_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'text/event-stream',
            },
            body: JSON.stringify({ 
                question: message,
                reference_ids: refIdsToSend,
                conversation_id: currentConvoId || null,
                use_visuals: toolsMasterEnabled && visualsEnabled,
                model: document.getElementById('modelSelector')?.value || null,
                language: IMARA_AI_LANGUAGE
            }),
            signal: currentAbortController.signal,
        });

        if (!res.ok) {
            console.error('[Stream] Fetch failed:', {
                status: res.status,
                statusText: res.statusText,
                sessionId: currentStreamSessionId
            });
            hideThinking();
            addErrorMessage(`Server error (${res.status}): ${res.statusText}`);
            return;
        }

        const reader  = res.body.getReader();
        const decoder = new TextDecoder();
        let buffer    = '';
        let accText   = '';
        let sources   = [];
        let msgIdRef  = { id: null };
        let bubbleEl  = null;
        let firstChunk = true;
        let eventCount = 0;  // Track total events received

        while (true) {
            const { done, value } = await reader.read();
            if (done) {
                console.log('[Stream] Reader reached EOF. Total events received:', eventCount);
                break;
            }

            buffer += decoder.decode(value, { stream: true });

            // Split on SSE double-newline delimiter
            let idx;
            while ((idx = buffer.indexOf('\n\n')) !== -1) {
                const raw = buffer.slice(0, idx).trim();
                buffer = buffer.slice(idx + 2);

                if (!raw.startsWith('data: ')) {
                    console.log('[Stream] Non-data event, skipping:', raw.substring(0, 50));
                    continue;
                }
                const payload = raw.slice(6).trim();
                eventCount++;

                if (payload === '[DONE]') {
                    console.log('[Stream] [DONE] event received, stream completed normally');
                    break;
                }

                let data;
                try { 
                    data = JSON.parse(payload); 
                } catch (e) {
                    console.warn('[Stream] Failed to parse JSON payload:', payload.substring(0, 100), e.message);
                    continue;
                }

                // Log ALL payloads for debugging
                console.log('[Stream] Raw SSE payload:', payload.substring(0, 200) + (payload.length > 200 ? '...' : ''));

                // Handle explicit error payloads from backend
                if (data.error) {
                    console.error('[Stream] Error payload received:', data.error);
                    hideThinking();
                    addErrorMessage(data.error);
                    return; // Terminate stream processing
                }

                // Capture trace_id from upstream connection event
                if (data.kind === 'upstream_connected' && data.trace_id) {
                    currentStreamTraceId = data.trace_id;
                    console.log('[Stream] Captured traceId from backend:', data.trace_id);
                    continue;
                }

                // First SSE event carries mode + sources + sessionId
                if (data.mode === 'streaming' && data.sources !== undefined) {
                    // Capture session ID for stop mechanism
                    if (data.session_id) {
                        currentStreamSessionId = data.session_id;
                        console.log('[Stream] Captured sessionId from backend:', data.session_id);
                    }
                    
                    sources = (data.sources || []).map(r => ({
                        label: [r.collection_name, r.entity_type, r.entity_id ? `#${r.entity_id}` : null].filter(Boolean).join(' · '),
                        isLive: false,
                        entityId: r.entity_id,
                        collectionName: r.collection_name,
                        entityType: r.entity_type,
                    }));
                    console.log('[Stream] Mode=streaming event received, sources loaded:', sources.length);
                    continue;
                }

                // Live-data shortcut: single full reply
                if (data.mode === 'live_data' && data.reply) {
                    console.log('[Stream] ✓ Live data mode detected, showing full reply');
                    hideThinking();
                    const liveSource = [{ label: 'Live Database', isLive: true }];
                    addBotMessage(data.reply, liveSource, null, msgIdRef, {});
                    // Clear attachment
                    if (currentAttachmentId) {
                        currentAttachmentId = null;
                        fileInput.value = '';
                        attachPreview.style.display = 'none';
                    }
                    if (currentConvoId) {
                        persistMessage(currentConvoId, 'bot', data.reply, liveSource)
                            .then(id => { msgIdRef.id = id; });
                    }
                    return; // done
                }

                // Streaming token: data may contain { token: '...' } or just a string
                const token = typeof data === 'string' ? data : (data.token ?? data.text ?? data.content ?? '');
                
                if (!token) {
                    console.warn('[Stream] ✗ No token extracted from payload. Data keys:', Object.keys(data), 'Full data:', JSON.stringify(data));
                    continue;
                }

                console.log('[Stream] Token received:', token.substring(0, 50) + (token.length > 50 ? '...' : ''));

                if (firstChunk) {
                    firstChunk = false;
                    hideThinking();
                    console.log('[Stream] ✓ First token received! Creating message bubble. Sources:', sources.length);
                    // Create the bubble now (empty) and grab its content el
                    addBotMessage('', sources, null, msgIdRef, {});
                    // The bubble was just appended — grab the last .msg-content
                    bubbleEl = chatMessages.querySelector('.msg-row.bot-row:last-child .msg-content');
                    console.log('[Stream] ✓ Message bubble element found:', !!bubbleEl);
                }

                accText += token;
                if (bubbleEl) {
                    bubbleEl.innerHTML = renderMarkdown(accText);
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                } else {
                    console.error('[Stream] ✗ bubbleEl is null! Cannot render token.');
                }
            }
        }

        // Hydrate charts in the final bubble
        const lastRow = chatMessages.querySelector('.msg-row.bot-row:last-child');
        if (lastRow) window.initCharts(lastRow);

        // Persist the completed message
        if (currentConvoId && accText) {
            persistMessage(currentConvoId, 'bot', accText, sources)
                .then(id => { msgIdRef.id = id; });
        }

        // Clear attachment after send
        if (currentAttachmentId) {
            currentAttachmentId = null;
            fileInput.value = '';
            attachPreview.style.display = 'none';
        }

    } catch (err) {
        hideThinking();
        if (err.name !== 'AbortError') {
            addErrorMessage('Connection error: ' + err.message);
        } else {
            console.log('[Stream] User cancelled stream via stop button');
        }
    } finally {
        // Clear the timeout watchdog since finally is executing
        clearTimeout(cleanupTimeoutId);
        cleanupTimeoutId = null;
        
        // Always reset button states immediately
        sendButton.style.display  = 'flex';
        stopButton.style.display  = 'none';
        sendButton.disabled = false;
        stopButton.disabled = false;
        
        // Clear session tracking
        currentStreamSessionId = null;
        currentStreamTraceId = null;
        currentAbortController = null;
        
        // Re-enable input with a short delay to ensure backend cleanup completes
        setTimeout(() => {
            messageInput.disabled = false;
            messageInput.readOnly = false;
            isCleaningUp          = false;
            isSending             = false;
            
            // Only re-focus if user didn't intentionally blur (safari mobile fix)
            if (document.activeElement === document.body || document.activeElement === messageInput) {
                messageInput.focus();
            }
            console.log('[Stream] Cleanup complete, input re-enabled');
        }, 300);
    }
}

// ── Helper function to reset button state (safety net if finally doesn't fire) ──
function resetStopButtonState() {
    console.log('[resetStopButtonState] Forcing UI reset');
    sendButton.style.display  = 'flex';
    stopButton.style.display  = 'none';
    sendButton.disabled = false;
    stopButton.disabled = false;
    messageInput.disabled = false;
    isCleaningUp = false;
    currentStreamSessionId = null;
    currentStreamTraceId = null;
    currentAbortController = null;
    messageInput.focus();
}

// ── Stop button handler with proper abort & cleanup management ──
stopButton?.addEventListener('click', () => {
    if (isCleaningUp) {
        console.warn('[Stop Button] Already cleaning up, ignoring duplicate stop click');
        return;
    }
    
    console.log('[Stop Button] Stop requested by user', {
        abortControllerExists: !!currentAbortController,
        sessionId: currentStreamSessionId,
        traceId: currentStreamTraceId,
        isCleaningUp: isCleaningUp
    });
    
    // Guard: prevent accidental double-clicks
    messageInput.readOnly = true;
    sendButton.disabled = true;
    stopButton.disabled = true;
    
    // Set cleanup flag to block new submissions
    isCleaningUp = true;
    
    // Abort the fetch immediately on frontend
    if (currentAbortController) {
        try {
            currentAbortController.abort();
            console.log('[Stop Button] AbortController.abort() called successfully');
        } catch (err) {
            console.error('[Stop Button] Failed to abort fetch:', err.message);
        }
    } else {
        console.warn('[Stop Button] AbortController was null, cannot abort fetch');
    }
    
    // Signal backend to cancel active queries using out-of-band signaling
    if (currentStreamTraceId) {
        console.log('[Stop Button] Sending out-of-band cancel signal for trace_id:', currentStreamTraceId);
        fetch('/imara-ai/cancel', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
            },
            body: JSON.stringify({
                trace_id: currentStreamTraceId
            })
        })
        .then(res => res.json())
        .then(data => {
            console.log('[Stop Button] Out-of-band cancel response:', data);
        })
        .catch(err => {
            console.warn('[Stop Button] Out-of-band cancel signal failed:', err.message);
        });
    }

    // Signal backend to stop streaming (optional double-signal for reliability)
    if (currentStreamSessionId) {
        // Fire and forget - don't block UI on backend response
        fetch(`/api/conversations/${currentStreamSessionId}/stop`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
            },
        })
        .then(res => {
            console.log('[Stop Button] Backend stop signal sent:', res.status);
        })
        .catch(err => {
            console.warn('[Stop Button] Backend stop signal failed:', err.message);
        });
    } else {
        console.warn('[Stop Button] No sessionId available, skipping backend stop signal');
    }
    
    // Schedule cleanup reset after 500ms to allow fetch error handling and finally block
    // If the finally block doesn't reset things, this acts as a safety net
    clearTimeout(cleanupTimeoutId);
    cleanupTimeoutId = setTimeout(() => {
        console.log('[Stop Button] Cleanup timeout triggered, forcing UI reset');
        resetStopButtonState();
    }, 500);
});

// ── File attachment ───────────────────────────────────────────────────────
const attachBtn       = document.getElementById('attachBtn');
const fileInput       = document.getElementById('fileInput');
const attachPreview   = document.getElementById('attachPreviewBar');
const attachFileName  = document.getElementById('attachFileName');
const attachStatus    = document.getElementById('attachStatusBadge');
const attachRemoveBtn = document.getElementById('attachRemoveBtn');

let currentAttachmentId = null;

attachBtn?.addEventListener('click', () => fileInput?.click());

attachRemoveBtn?.addEventListener('click', () => {
    currentAttachmentId = null;
    fileInput.value = '';
    attachPreview.style.display = 'none';
});

fileInput?.addEventListener('change', async () => {
    const file = fileInput.files[0];
    if (!file) return;

    const maxMb = 10;
    if (file.size > maxMb * 1024 * 1024) {
        alert(`File too large. Maximum size is ${maxMb} MB.`);
        fileInput.value = '';
        return;
    }

    attachFileName.textContent = file.name;
    attachStatus.textContent   = 'Uploading…';
    attachStatus.style.background = '#dbeafe';
    attachStatus.style.color      = '#1d4ed8';
    attachPreview.style.display   = 'flex';

    // Need a conversation to attach the file to; create one if needed
    if (!currentConvoCreated) {
        try {
            const convo       = await createConversationOnServer('Attachment: ' + file.name.slice(0, 40));
            currentConvoId      = convo.id;
            currentConvoCreated = true;
            conversations.unshift(convo);
            renderSidebarHistory();
        } catch (e) {
            attachStatus.textContent   = 'Error';
            attachStatus.style.background = '#fee2e2';
            attachStatus.style.color      = '#991b1b';
            console.error('Failed to create conversation for attachment', e);
            return;
        }
    }

    const formData = new FormData();
    formData.append('file', file);
    formData.append('_token', CSRF_TOKEN);

    try {
        const res  = await fetch(`${CONVO_MESSAGES_BASE}/${currentConvoId}/attachments`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF_TOKEN },
            body: formData,
        });
        const json = await res.json();

        if (res.ok && json.status === 'ok') {
            currentAttachmentId         = json.attachment_id;
            attachStatus.textContent    = json.has_text ? 'Ready' : 'No text extracted';
            attachStatus.style.background = json.has_text ? '#d1fae5' : '#fef3c7';
            attachStatus.style.color      = json.has_text ? '#065f46' : '#92400e';
        } else {
            attachStatus.textContent      = 'Upload failed';
            attachStatus.style.background = '#fee2e2';
            attachStatus.style.color      = '#991b1b';
            currentAttachmentId = null;
        }
    } catch (e) {
        attachStatus.textContent      = 'Network error';
        attachStatus.style.background = '#fee2e2';
        attachStatus.style.color      = '#991b1b';
        currentAttachmentId = null;
        console.error('File upload failed', e);
    }
});

// ── Voice Output (TTS) ───────────────────────────────────────────────────
function speakMessage(text, btn) {
    if (!window.speechSynthesis) return;

    // Strip markdown tags and artifacts for cleaner speech
    const cleanText = text
        .replace(/```[\s\S]*?```/g, ' [code block skipped] ')
        .replace(/__CHART_PLACEHOLDER_\d+__/g, ' [chart data] ')
        .replace(/\*\*|__|`|#/g, '');

    const utterance = new SpeechSynthesisUtterance(cleanText);
    utterance.lang = 'en-US';
    utterance.rate = 1.0;
    utterance.pitch = 1.0;

    utterance.onstart = () => {
        btn.classList.add('speaking');
        btn.innerHTML = '<i class="mdi mdi-stop"></i>';
    };

    utterance.onend = () => {
        btn.classList.remove('speaking');
        btn.innerHTML = '<i class="mdi mdi-volume-high"></i>';
    };

    utterance.onerror = (e) => {
        console.error('TTS Error:', e);
        btn.classList.remove('speaking');
        btn.innerHTML = '<i class="mdi mdi-volume-high"></i>';
    };

    window.speechSynthesis.speak(utterance);
}

// ── Voice input ───────────────────────────────────────────────────────────
const micBtn = document.getElementById('micBtn');
let recognition = null;

if (micBtn) {
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    if (!SpeechRecognition) {
        micBtn.title = 'Voice input not supported in this browser';
        micBtn.style.opacity = '0.4';
        micBtn.disabled = true;
    } else {
        recognition = new SpeechRecognition();
        recognition.continuous      = false;
        recognition.interimResults  = false;
        recognition.lang            = 'en-US';

        recognition.onstart = () => {
            micBtn.classList.add('mic-active');
            micBtn.querySelector('i').className = 'mdi mdi-microphone';
            console.log('Voice recognition started...');
        };

        recognition.onresult = (e) => {
            const transcript = e.results[0][0].transcript;
            messageInput.value = transcript;
            messageInput.dispatchEvent(new Event('input'));
            console.log('Voice Result:', transcript);
        };

        recognition.onerror = (e) => {
            console.error('Voice Recognition Error:', e.error);
            micBtn.classList.remove('mic-active');
            micBtn.querySelector('i').className = 'mdi mdi-microphone-outline';
            
            if (e.error === 'not-allowed') {
                alert('Microphone access denied. Please check your browser permissions and security headers.');
            }
        };

        recognition.onend = () => {
            micBtn.classList.remove('mic-active');
            micBtn.querySelector('i').className = 'mdi mdi-microphone-outline';
            console.log('Voice recognition ended.');
        };

        micBtn.addEventListener('click', () => {
            if (micBtn.classList.contains('mic-active')) {
                recognition.stop();
            } else {
                // Ensure TTS is stopped if user starts speaking
                if (window.speechSynthesis) window.speechSynthesis.cancel();
                
                try {
                    recognition.start();
                } catch (err) {
                    console.error('Recognition Start Failed:', err);
                }
            }
        });
    }
}


// ── Sidebar search ────────────────────────────────────────────────────────
const sidebarSearchClear = document.getElementById('sidebarSearchClear');

let sidebarSearchTimer = null;

sidebarSearch?.addEventListener('input', () => {
    const val = sidebarSearch.value;
    const hasValue = val.length > 0;
    sidebarSearchClear.style.display = hasValue ? 'flex' : 'none';

    clearTimeout(sidebarSearchTimer);
    if (!hasValue) {
        // Instantly restore the full cached list
        renderSidebarHistory();
        return;
    }
    // Debounce: hit server for full-text content search after 350ms
    sidebarSearchTimer = setTimeout(() => {
        loadConversationsFromBackend(val);
    }, 350);
});


sidebarSearchClear?.addEventListener('click', () => {
    if (sidebarSearch) {
        sidebarSearch.value = '';
        sidebarSearchClear.style.display = 'none';
        sidebarSearch.focus();
    }
    renderSidebarHistory();
});

// ── Events ────────────────────────────────────────────────────────────────
sendButton?.addEventListener('click', sendMessage);

messageInput?.addEventListener('keydown', function (e) {
    // Mention navigation
    if (mentionSearchActive && mentionSuggestions.style.display !== 'none') {
        const items = mentionSuggestions.querySelectorAll('.mention-item');
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            mentionIndex = (mentionIndex + 1) % items.length;
            updateMentionActiveState(items);
            return;
        }
        if (e.key === 'ArrowUp') {
            e.preventDefault();
            mentionIndex = (mentionIndex - 1 + items.length) % items.length;
            updateMentionActiveState(items);
            return;
        }
        if (e.key === 'Enter' || e.key === 'Tab') {
            if (mentionIndex >= 0) {
                e.preventDefault();
                selectMention(mentionIndex);
                return;
            }
        }
        if (e.key === 'Escape') {
            mentionSuggestions.style.display = 'none';
            mentionSearchActive = false;
            return;
        }
    }

    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
    }
});

function updateMentionActiveState(items) {
    items.forEach((item, idx) => {
        item.classList.toggle('active', idx === mentionIndex);
        if (idx === mentionIndex) item.scrollIntoView({ block: 'nearest' });
    });
}

document.addEventListener('input', function (e) {
    if (e.target.id !== 'messageInput') return;
    
    const input = e.target;
    const val = input.value;
    const cursorPos = input.selectionStart;
    const textBefore = val.substring(0, cursorPos);
    const lastAt = textBefore.lastIndexOf('@');

    console.log('[Mention] Delegated input detected. Last @ at:', lastAt);

    if (lastAt !== -1 && (lastAt === 0 || /\s/.test(textBefore[lastAt - 1]))) {
        const query = textBefore.substring(lastAt + 1);
        console.log('[Mention] Trigger active. Query:', query);
        if (!/\s/.test(query)) {
            mentionSearchActive = true;
            updateMentionSuggestions(query);
        } else {
            mentionSearchActive = false;
            mentionSuggestions.style.display = 'none';
        }
    } else {
        mentionSearchActive = false;
        mentionSuggestions.style.display = 'none';
    }
});

// ── Drop-up Menus (Models & Tools) ─────────────────────────────────────────
const modelBtn    = document.getElementById('modelBtn');
const modelMenu   = document.getElementById('modelMenu');
const toolsBtn    = document.getElementById('toolsBtn');
const toolsMenu   = document.getElementById('toolsMenu');

function toggleMenu(menu, otherMenu) {
    const isVisible = menu.style.display === 'block';
    
    // Hide others
    if (otherMenu) otherMenu.style.display = 'none';
    
    // Toggle current
    menu.style.display = isVisible ? 'none' : 'block';
}

modelBtn?.addEventListener('click', (e) => {
    e.stopPropagation();
    if (modelMenu) toggleMenu(modelMenu, toolsMenu);
});

toolsBtn?.addEventListener('click', (e) => {
    e.stopPropagation();
    if (toolsMenu) toggleMenu(toolsMenu, modelMenu);
});

// Close menus when clicking outside
document.addEventListener('click', (e) => {
    if (modelMenu && modelMenu.style.display === 'block' && !modelMenu.contains(e.target)) {
        modelMenu.style.display = 'none';
    }
    if (toolsMenu && toolsMenu.style.display === 'block') {
        if (!toolsMenu.contains(e.target) && e.target !== toolsBtn) {
            toolsMenu.style.display = 'none';
        }
    }
});

// Sync toggles
const toggleMasterTools = document.getElementById('toggleMasterTools');
const visualsToggle  = document.getElementById('visualsToggle');

toggleMasterTools?.addEventListener('change', () => {
    toolsMasterEnabled = toggleMasterTools.checked;
    updateToolsUI();
});

visualsToggle?.addEventListener('change', () => {
    visualsEnabled = visualsToggle.checked;
    syncToolsIndicator();
});

function updateToolsUI() {
    const visualItem = document.getElementById('btnToggleVisuals');
    if (visualItem) {
        visualItem.classList.toggle('ghosted', !toolsMasterEnabled);
        const inputs = visualItem.querySelectorAll('input');
        inputs.forEach(i => i.disabled = !toolsMasterEnabled);
    }
    syncToolsIndicator();
}

function syncToolsIndicator() {
    const effectiveVisuals = toolsMasterEnabled && visualsEnabled;
    const indicator = document.getElementById('toolsIndicator');
    if (indicator) {
        indicator.style.display = effectiveVisuals ? 'block' : 'none';
    }
}

window.addEventListener('load', async function () {
    await loadConversationsFromBackend();
    
    const pathParts = window.location.pathname.split('/');
    const lastPart = pathParts[pathParts.length - 1];
    // UUID pattern matching (36 characters: 8-4-4-4-12 hex chars)
    const uuidRegex = /^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/;
    const convoIdFromUrl = (lastPart && uuidRegex.test(lastPart)) ? lastPart : null;
    
    if (convoIdFromUrl) {
        const convoExists = conversations.some(c => String(c.id) === String(convoIdFromUrl));
        if (convoExists) {
            currentConvoId = convoIdFromUrl;
            currentConvoCreated = true;
            renderSidebarHistory();
            await loadConversationMessages(convoIdFromUrl);
        } else {
            startNewConversation();
        }
    } else {
        startNewConversation();
    }

    if (messageInput) {
        messageInput.focus();
    }
    // Move the modal to body so it's not inside overflow:hidden (#imara-ai-root),
    // which causes backdrop-filter/compositing to render black in Chrome.
    if (deleteConvoModal) {
        document.body.appendChild(deleteConvoModal);
    }
    if (document.getElementById('chartModal')) {
        document.body.appendChild(document.getElementById('chartModal'));
    }
    updateToolsUI();
});
