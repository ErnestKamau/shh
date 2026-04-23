async function persistMessage(convoId, role, content, sources, metadata = {}) {
    try {
        const res  = await fetch(`${CONVO_MESSAGES_BASE}/${convoId}/messages`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
            body: JSON.stringify({ role, content, sources: sources || null, metadata }),
        });
        const json = await res.json();
        return json.message?.id ?? null;
    } catch (e) {
        console.error('Failed to persist message', e);
        return null;
    }
}

async function fetchKnowledge(userInput, attachmentId) {
    currentAbortController = new AbortController();
    const body = { query: userInput, limit: 5 };
    if (attachmentId) body.attachment_id = attachmentId;
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
        if (!results.length) {
            return {
                reply: 'No relevant knowledge found for that query.',
                sources: [],
            };
        }

        const lines = results.map((row, i) => {
            const src = [row.collection_name, row.entity_type].filter(Boolean).join(' › ');
            return `**${i + 1}. ${src || 'Knowledge Base'}**\n${row.content}`;
        }).join('\n\n---\n\n');

        const sources = results.map(r => ({
            label: [r.collection_name, r.entity_type, r.entity_id ? `#${r.entity_id}` : null].filter(Boolean).join(' · '),
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

async function sendMessage(manualText = null) {
    if (isCleaningUp || isSending) return;
    
    const message = manualText || messageInput.value.trim();
    if (!message) return;

    hideWelcome();
    const historyReady = saveToHistory(message);
    addUserMessage(message);
    if (!manualText) {
        messageInput.value        = '';
        messageInput.style.height = 'auto';
    }

    // Await history mapping (crucial for first prompt to have a currentConvoId)
    await historyReady;
    if (currentConvoId) {
        persistMessage(currentConvoId, 'user', message, null);
    }
    
    sendButton.style.display  = 'none';
    stopButton.style.display  = 'flex';
    messageInput.readOnly     = true;
    isSending                 = true;
    showThinking();

    currentAbortController = new AbortController();
    let fullResponse = '';
    let sourcesFound = [];
    let botMsgIdRef  = { id: null };
    let hasAddedBotRow = false;
    let botMsgContentEl = null;

    try {
        const response = await fetch(ASK_STREAM_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'text/event-stream',
            },
            body: JSON.stringify({ 
                question: message, 
                conversation_id: currentConvoId,
                trace_id: crypto.randomUUID(),
                use_visuals: (typeof toolsMasterEnabled !== 'undefined' ? toolsMasterEnabled : true) && 
                             (typeof visualsEnabled !== 'undefined' ? visualsEnabled : true),
                model: document.getElementById('modelSelector')?.value || null,
                module_context: (typeof ImaraAiState !== 'undefined' ? ImaraAiState.module_context : null)
            }),
            signal: currentAbortController.signal,
        });

        if (!response.ok) {
            const errBody = await response.text();
            throw new Error(`Server error (${response.status}): ${errBody || response.statusText}`);
        }

        const reader = response.body.getReader();
        const decoder = new TextDecoder();
        hideThinking();

        let buffer = '';
        while (true) {
            const { value, done } = await reader.read();
            if (done) break;

            buffer += decoder.decode(value, { stream: true });
            const lines = buffer.split('\n');
            buffer = lines.pop();

            for (const line of lines) {
                const trimmed = line.trim();
                if (!trimmed || !trimmed.startsWith('data: ')) continue;
                
                const dataStr = trimmed.substring(6).trim();
                if (dataStr === '[DONE]') break;

                // DIAGNOSTIC LOGGING
                if (typeof AI_DIAGNOSTICS !== 'undefined') {
                    AI_DIAGNOSTICS.logChunk(trimmed, buffer);
                }

                // Hardening: Only attempt to parse if it looks like a JSON object
                if (!dataStr.startsWith('{')) {
                    if (typeof AI_DIAGNOSTICS !== 'undefined') {
                        console.warn('[Stream] Skipping non-JSON chunk:', dataStr);
                    }
                    continue;
                }

                try {
                    const data = JSON.parse(dataStr);
                    if (data.error) {
                        addErrorMessage(data.error);
                        return;
                    }

                    if (data.kind === 'session_info') {
                        currentStreamSessionId = data.session_id;
                        continue;
                    }

                    const token = data.token || data.reply; 
                    if (token) fullResponse += token;
                    if (data.sources && data.sources.length) sourcesFound = data.sources;

                    if (!hasAddedBotRow && fullResponse) {
                        addBotMessage('', sourcesFound, null, botMsgIdRef, {});
                        const botRows = chatMessages.querySelectorAll('.bot-row');
                        const lastBotRow = botRows[botRows.length - 1];
                        botMsgContentEl = lastBotRow.querySelector('.msg-content');
                        hasAddedBotRow = true;
                    }

                    if (botMsgContentEl && token) {
                        botMsgContentEl.innerHTML = renderMarkdown(fullResponse);
                        botMsgContentEl.dataset.raw = fullResponse;
                        scrollToBottom();
                    }
                } catch (e) {
                    console.warn('[Stream] Fragment parse error:', e);
                }
            }
        }

        if (hasAddedBotRow) {
            const botRows = chatMessages.querySelectorAll('.bot-row');
            const lastBotRow = botRows[botRows.length - 1];
            
            if (sourcesFound.length) {
                const sourcesContainer = lastBotRow.querySelector('.msg-sources');
                if (sourcesContainer) sourcesContainer.innerHTML = renderSources(sourcesFound);
            }

            // [FIX] Initial Render: Trigger Chart rendering immediately after stream ends
            if (typeof initCharts === 'function') {
                initCharts(lastBotRow);
            }
        }

        if (currentConvoId && fullResponse) {
            persistMessage(currentConvoId, 'bot', fullResponse, sourcesFound)
                .then(id => { botMsgIdRef.id = id; });
        }

        if (currentAttachmentId) {
            currentAttachmentId = null;
            if (fileInput) fileInput.value = '';
            if (attachPreview) attachPreview.style.display = 'none';
        }

    } catch (err) {
        hideThinking();
        if (err.name !== 'AbortError') {
            addErrorMessage('Connection error: ' + err.message);
        } else {
            console.log('[Stream] User cancelled stream via stop button');
        }
    } finally {
        clearTimeout(cleanupTimeoutId);
        cleanupTimeoutId = null;
        sendButton.style.display  = 'flex';
        stopButton.style.display  = 'none';
        sendButton.disabled = false;
        stopButton.disabled = false;
        currentStreamSessionId = null;
        currentAbortController = null;
        isSending             = false;
        isCleaningUp          = false;
        
        setTimeout(() => {
            messageInput.disabled = false;
            messageInput.readOnly = false;
            if (document.activeElement === document.body || document.activeElement === messageInput) {
                messageInput.focus();
            }
        }, 300);
    }
}

function resetStopButtonState() {
    sendButton.style.display  = 'flex';
    stopButton.style.display  = 'none';
    sendButton.disabled = false;
    stopButton.disabled = false;
    messageInput.disabled = false;
    isCleaningUp = false;
    currentStreamSessionId = null;
    currentAbortController = null;
    messageInput.focus();
}

stopButton?.addEventListener('click', () => {
    if (isCleaningUp) return;
    messageInput.readOnly = true;
    sendButton.disabled = true;
    stopButton.disabled = true;
    isCleaningUp = true;
    
    if (currentAbortController) {
        try {
            currentAbortController.abort();
        } catch (err) {
            console.error('[Stop Button] Abort failed:', err.message);
        }
    }
    
    if (currentStreamSessionId) {
        fetch(`/api/conversations/${currentStreamSessionId}/stop`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
        }).catch(err => console.warn('[Stop Button] Signal failed:', err.message));
    }
    
    clearTimeout(cleanupTimeoutId);
    cleanupTimeoutId = setTimeout(resetStopButtonState, 500);
});

function pollVerificationStatus(traceId, rowElement) {
    let attempts = 0;
    const maxAttempts = 6;
    async function check() {
        try {
            const res = await fetch(`/api/conversations/traces/${traceId}/status`, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN }
            });
            if (res.ok) {
                const json = await res.json();
                if (json.status === 'ok') {
                    if (json.hallucination_detected) {
                        injectHallucinationWarning(rowElement, json.notes);
                        return;
                    }
                    return;
                }
            }
        } catch (e) { console.warn('[Safety] Status check failed:', e); }
        attempts++;
        if (attempts < maxAttempts) setTimeout(check, 5000);
    }
    setTimeout(check, 3000);
}

function injectHallucinationWarning(rowElement, notes) {
    const body = rowElement.querySelector('.msg-body');
    if (!body || rowElement.querySelector('.hallucination-alert')) return;
    const alert = document.createElement('div');
    alert.className = 'hallucination-alert';
    alert.innerHTML = `
        <div class="alert-header"><i class="mdi mdi-alert-decagram"></i><span>Safety Warning: Potential Contradiction</span></div>
        <div class="alert-content">This response was flagged as potentially containing factual inconsistencies. Please cross-reference manually.</div>
    `;
    body.insertBefore(alert, body.querySelector('.msg-content'));
}
