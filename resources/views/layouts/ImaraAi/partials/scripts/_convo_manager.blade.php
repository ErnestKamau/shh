function startNewConversation() {
    currentConvoId      = null;
    currentConvoCreated = false;
    if (chatMessages) {
        chatMessages.innerHTML = '';
        chatMessages.appendChild(welcomeScreen);
        welcomeScreen.style.display = 'flex';
    }
    messageInput?.focus();
}

function showSidebarSkeleton() {
    if (!sidebarHistory) return;
    sidebarHistory.innerHTML = [1, 2, 3, 4].map(() => `
        <div class="history-skeleton">
            <div class="sk-icon"></div>
            <div class="sk-line"></div>
        </div>`).join('');
}

function hideSidebarSkeleton() {
    if (!sidebarHistory) return;
    sidebarHistory.querySelectorAll('.history-skeleton').forEach(el => el.remove());
}

function showSidebarError() {
    if (!sidebarHistory) return;
    sidebarHistory.innerHTML = `
        <div class="sidebar-load-error">
            <i class="mdi mdi-wifi-off"></i>
            <span>Couldn't load history</span>
            <button id="retrySidebarLoad">Retry</button>
        </div>`;
    document.getElementById('retrySidebarLoad')?.addEventListener('click', loadConversationsFromBackend);
}

async function loadConversationsFromBackend(filter) {
    if (!sidebarHistory) return;
    showSidebarSkeleton();
    try {
        const url = filter ? `${CONVOS_URL}?filter=${encodeURIComponent(filter)}` : CONVOS_URL;
        const res  = await fetch(url, { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN } });
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const json = await res.json();
        if (!filter) conversations = json.conversations ?? [];
        renderSidebarHistory(null, json.conversations ?? []);
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
                addUserMessage(msg.content, msg.created_at);
            } else {
                addBotMessage(msg.content, msg.sources || [], msg.created_at, { id: msg.id });
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
    if (currentConvoCreated) return;
    try {
        const convo = await createConversationOnServer(userMessage.slice(0, 60));
        currentConvoId      = convo.id;
        currentConvoCreated = true;
        conversations.unshift(convo);
        renderSidebarHistory();
    } catch (e) {
        console.error('Failed to create conversation', e);
    }
}

function renderSidebarHistory(filter, list) {
    if (!sidebarHistory) return;
    hideSidebarSkeleton();
    const source = list ?? conversations;
    const q = (filter || '').toLowerCase().trim();
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

        const pinBtn = document.createElement('button');
        pinBtn.className = 'pin-convo-btn' + (c.is_pinned ? ' pinned' : '');
        pinBtn.title = c.is_pinned ? 'Unpin from top' : 'Pin to top';
        pinBtn.innerHTML = c.is_pinned ? '<i class="mdi mdi-pin"></i>' : '<i class="mdi mdi-pin-outline"></i>';
        pinBtn.addEventListener('click', async (e) => {
            e.stopPropagation();
            try {
                const res = await fetch(`${CONVO_MESSAGES_BASE}/${c.id}/toggle-pin`, {
                    method: 'PATCH',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN }
                });
                if (!res.ok) throw new Error('Failed to toggle pin');
                const json = await res.json();
                c.is_pinned = json.is_pinned;
                
                // Re-sort local array: pinned first, then by updated_at
                conversations.sort((a, b) => {
                    if (a.is_pinned !== b.is_pinned) return b.is_pinned ? 1 : -1;
                    return new Date(b.updated_at) - new Date(a.updated_at);
                });
                renderSidebarHistory();
            } catch (err) {
                console.error('Pin toggle failed', err);
            }
        });

        const delBtn = document.createElement('button');
        delBtn.className = 'delete-convo-btn';
        delBtn.title = 'Delete conversation';
        delBtn.innerHTML = '<i class="mdi mdi-close"></i>';
        delBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            showDeleteModal(c.id);
        });

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
        item.appendChild(pinBtn);
        item.appendChild(label);
        item.appendChild(delBtn);

        let clickTimer = null;
        item.addEventListener('click', (e) => {
            if (e.target.closest('.delete-convo-btn') || e.target.closest('.history-checkbox')) return;
            if (bulkSelectMode) {
                checkbox.checked = !checkbox.checked;
                checkbox.dispatchEvent(new Event('change'));
                return;
            }
            if (clickTimer) {
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
                if (window.innerWidth <= 768 && sidebar.classList.contains('active')) {
                    toggleMobileMenu();
                }
            }, 280);
        });
        sidebarHistory.appendChild(item);
    });
}

function startRename(labelEl, convo) {
    if (!sidebarHistory) return;
    const input = document.createElement('input');
    input.type  = 'text';
    input.value = convo.title;
    input.className = 'rename-input';
    labelEl.replaceWith(input);
    input.focus();
    input.select();

    async function commitRename() {
        const newTitle = input.value.trim() || convo.title;
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
