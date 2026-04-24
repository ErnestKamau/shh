// ── Formatting & Helpers ───────────────────────────────────────────────────
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
    if (!str) return '';
    return str
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function scrollToBottom() {
    setTimeout(() => { chatMessages.scrollTop = chatMessages.scrollHeight; }, 80);
}

function hideWelcome() {
    if (welcomeScreen && welcomeScreen.parentNode === chatMessages) {
        welcomeScreen.remove();
    }
}

// ── Markdown ─────────────────────────────────────────────────────────────
function renderMarkdown(text, isMultiReport = false) {
    if (!text) return '';
    
    // If it's a multi-report, we split by --- and wrap each in a card
    if (isMultiReport && text.includes('---\n')) {
        const segments = text.split(/\n\n---\n\n|\n---\n/);
        return segments.map((seg, i) => {
            const rendered = renderMarkdownSegment(seg);
            if (!rendered.trim()) return '';
            return `
                <div class="multi-report-card">
                    <div class="report-badge">
                        <i class="mdi mdi-file-document-outline"></i> Report Segment ${i + 1}
                    </div>
                    ${rendered}
                </div>`;
        }).join('');
    }

    return renderMarkdownSegment(text);
}

function renderMarkdownSegment(text) {
    if (!text) return '';
    
    // Extract source metadata from comments
    let sourceMeta = '';
    const sourceRegex = /<!--\s*Source:\s*([\s\S]*?)\s*-->/g;
    const matches = [...text.matchAll(sourceRegex)];
    if (matches.length > 0) {
        sourceMeta = matches.map(m => `
            <div class="report-card-source">
                <i class="mdi mdi-database-eye-outline"></i> ${m[1]}
            </div>`).join('');
        // Remove the source comments from the text to avoid redundancy
        text = text.replace(sourceRegex, '');
    }

    const chartBlocks = [];
    if (text.includes('```chart') && (typeof visualsEnabled === 'undefined' || (typeof toolsMasterEnabled !== 'undefined' ? toolsMasterEnabled && visualsEnabled : visualsEnabled))) {
        text = text.replace(/```chart\n([\s\S]*?)\n```/g, (_, json) => {
            const idx = chartBlocks.length;
            chartBlocks.push(json.trim());
            return `__CHART_PLACEHOLDER_${idx}__`;
        });
    } else {
        // Strip chart blocks if visuals are disabled
        text = text.replace(/```chart\n([\s\S]*?)\n```/g, '');
    }

    text = text
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');

    text = text.replace(/```([\s\S]*?)```/g, '<pre><code>$1</code></pre>');
    text = text.replace(/`([^`]+)`/g, '<code>$1</code>');
    text = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
    text = text.replace(/\*(.*?)\*/g, '<em>$1</em>');
    text = text.replace(/^---$/gm, '<hr>');
    text = text.replace(/^## (.+)$/gm, '<h2>$1</h2>');
    text = text.replace(/^### (.+)$/gm, '<h3>$1</h3>');
    text = text.replace(/(\|.+\|\n\|[-| :]+\|\n(?:\|.+\|\n?)+)/g, match => {
        const rows = match.trim().split('\n');
        const header = rows[0].split('|').filter(c => c.trim() !== '').map(c => `<th>${c.trim()}</th>`).join('');
        const body = rows.slice(2).map(r =>
            '<tr>' + r.split('|').filter(c => c.trim() !== '').map(c => `<td>${c.trim()}</td>`).join('') + '</tr>'
        ).join('');
        return `<table><thead><tr>${header}</tr></thead><tbody>${body}</tbody></table>`;
    });
    text = text.replace(/^\d+\. (.+)$/gm, '<li>$1</li>');
    text = text.replace(/^[-•] (.+)$/gm, '<li>$1</li>');

    const parts = text.split('\n\n');
    text = parts.map(p => {
        p = p.trim();
        if (!p) return '';
        if (p.startsWith('<')) return p;
        return `<p>${p.replace(/\n/g, '<br>')}</p>`;
    }).join('');

    window.chartConfigs = window.chartConfigs || {};
    chartBlocks.forEach((json, idx) => {
        const id = `cfg-${Date.now()}-${idx}`;
        window.chartConfigs[id] = json;
        const canvasId = `imara-chart-${Date.now()}-${idx}`;
        const placeholder = `__CHART_PLACEHOLDER_${idx}__`;
        const canvasHtml = `
            <div class="chart-container">
                <div class="chart-badge">
                    <i class="mdi mdi-chart-box-outline"></i> Live Data Analysis
                </div>
                <button class="chart-expand-btn" onclick="openChartModal('${id}')" title="Expand Analysis">
                    <i class="mdi mdi-fullscreen"></i>
                </button>
                <div style="height: 400px; width: 100%;">
                    <canvas id="${canvasId}" data-config-id="${id}"></canvas>
                </div>
            </div>`;
        text = text.replace(placeholder, canvasHtml);
    });

    if (typeof DOMPurify !== 'undefined') {
        text = DOMPurify.sanitize(text, { ADD_ATTR: ['data-chart'] });
    }

    // Append source meta at the end of the segment
    if (sourceMeta) {
        text += `<div class="report-card-footer">${sourceMeta}</div>`;
    }

    return text;
}

function initCharts(container) {
    if (typeof Chart === 'undefined') return;
    container.querySelectorAll('canvas[data-config-id]').forEach(canvas => {
        try {
            const configId = canvas.dataset.configId;
            const rawJson = window.chartConfigs[configId];
            if (!rawJson) return;
            const cfg = JSON.parse(rawJson);
            
            const palette = ['#a72b2a','#3b82f6','#10b981','#f59e0b','#8b5cf6','#ec4899','#06b6d4','#84cc16','#f97316','#6366f1'];
            const datasets = (cfg.datasets || []).map((ds, i) => {
                const dataPoints = Array.isArray(ds.data) ? ds.data : [];
                return {
                    ...ds,
                    data: dataPoints,
                    backgroundColor: dataPoints.map((_, j) => palette[(i * 3 + j) % palette.length] + 'cc'),
                    borderColor:     dataPoints.map((_, j) => palette[(i * 3 + j) % palette.length]),
                    borderWidth: 1,
                    borderRadius: 4,
                };
            });
            // v2.x horizontal bar support
            let chartType = cfg.type || 'bar';
            let isHorizontal = false;
            if (chartType === 'bar' && (cfg.labels || []).length > 8) {
                chartType = 'horizontalBar';
                isHorizontal = true;
            }

            new Chart(canvas, {
                type: chartType,
                data: { labels: cfg.labels || [], datasets },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    legend: { 
                        display: datasets.length > 1,
                        position: 'bottom',
                        labels: { fontSize: 13, fontStyle: 'bold' } 
                    },
                    title: { 
                        display: !!cfg.title, 
                        text: cfg.title || '',
                        fontSize: 16,
                        fontStyle: 'bold',
                        padding: 20
                    },
                    tooltips: {
                        backgroundColor: 'rgba(255, 255, 255, 0.95)',
                        titleFontColor: '#1e293b',
                        bodyFontColor: '#475569',
                        borderColor: '#e2e8f0',
                        borderWidth: 1,
                        xPadding: 12,
                        yPadding: 12,
                        displayColors: true
                    },
                    scales: (chartType === 'pie' || chartType === 'doughnut') ? {} : { 
                        yAxes: [{ 
                            ticks: { beginAtZero: true, fontSize: 12, precision: 0 },
                            gridLines: { color: isHorizontal ? 'transparent' : '#f8fafc' }
                        }],
                        xAxes: [{
                            ticks: { fontSize: 12, fontStyle: '500' },
                            gridLines: { color: isHorizontal ? '#f8fafc' : 'transparent' }
                        }]
                    },
                },
            });
        } catch (e) { console.warn('Chart render failed', e); }
    });
}

// ── Sources ──────────────────────────────────────────────────────────────
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
        if (s.isLive) return `<span class="source-tag live-data-tag"><i class="mdi mdi-database-flash-outline"></i> ${escapeHtml(s.label)}</span>`;
        const url = getSourceUrl(s);
        if (url) return `<a href="${url}" target="_blank" rel="noopener noreferrer" class="source-tag source-link" title="Open in LIMS"><i class="mdi mdi-link-variant"></i> ${escapeHtml(s.label)}</a>`;
        return `<span class="source-tag">${escapeHtml(s.label)}</span>`;
    }).join('');
    return `<div class="msg-sources">${tags}</div>`;
}

// ── Message Bubbles ──────────────────────────────────────────────────────
function addUserMessage(text, isoTimestamp, metadata = {}) {
    const row = document.createElement('div');
    row.className = 'msg-row user-row';
    const editBadge = metadata.is_edited ? '<span class="msg-edit-badge">Edited</span>' : '';

    row.innerHTML = `
        <div class="user-bubble-wrap">
            <div class="user-bubble">${escapeHtml(text)}</div>
            <div class="msg-row-actions">
                <button class="msg-action-btn copy-btn" title="Copy message"><i class="mdi mdi-content-copy"></i></button>
                <button class="msg-action-btn edit-btn" title="Edit message"><i class="mdi mdi-pencil-outline"></i></button>
            </div>
            ${editBadge}
            <div class="msg-timestamp">${formatTimestamp(isoTimestamp)}</div>
        </div>`;

    row.querySelector('.copy-btn').addEventListener('click', () => {
        navigator.clipboard.writeText(text).then(() => {
            const btn = row.querySelector('.copy-btn');
            btn.innerHTML = '<i class="mdi mdi-check"></i>';
            setTimeout(() => btn.innerHTML = '<i class="mdi mdi-content-copy"></i>', 2000);
        });
    });

    const editBtn = row.querySelector('.edit-btn');
    if (editBtn) {
        editBtn.addEventListener('click', () => {
            messageInput.value = text;
            messageInput.dispatchEvent(new Event('input'));
            messageInput.focus();
        });
    }

    chatMessages.appendChild(row);
    scrollToBottom();
}

function renderPredictionBubble(text, sources, metadata) {
    const risk = metadata.risk_level || 'low';
    const conf = Math.min(100, Math.max(0, metadata.confidence || 50));
    const explain = metadata.explanation || '';
    const metric = metadata.metric || 'Risk Assessment';
    const ICON = { high: '🔴', medium: '🟡', low: '🟢' };
    const LABEL = { high: 'HIGH RISK', medium: 'MODERATE', low: 'LOW RISK' };

    return `<div class="prediction-bubble risk-${escapeHtml(risk)}">
        <span class="risk-badge risk-${escapeHtml(risk)}">${ICON[risk] || '⚪'} ${LABEL[risk] || risk.toUpperCase()}</span>
        <div class="confidence-wrap">
            <span>${escapeHtml(metric)} · ${conf}% confidence</span>
            <div class="confidence-track"><div class="confidence-fill" style="width:${conf}%"></div></div>
        </div>
        ${renderSources(sources)}
        ${explain ? `<button class="reasoning-toggle" onclick="this.nextElementSibling.classList.toggle('open'); this.classList.toggle('open');"><span class="toggle-icon mdi mdi-chevron-right"></span> Reasoning</button>
        <div class="reasoning-text">${escapeHtml(explain)}</div>` : ''}
    </div>`;
}

function addBotMessage(text, sources, isoTimestamp, msgIdRef = { id: null }, metadata = {}) {
    const row = document.createElement('div');
    row.className = 'msg-row bot-row';
    const retryLabel = (metadata.retry_count > 0) ? `<span class="msg-retry-badge">Retry ${metadata.retry_count}</span>` : '';
    const hasPrediction = !!(metadata.risk_level);
    const isMultiReport = !!(metadata.multi_report);
    const multiCount = metadata.multi_count || 0;
    
    // Multi-report summary badge
    const multiBadge = isMultiReport ? `
        <div class="report-count-badge">
            <i class="mdi mdi-layers-triple"></i> ${multiCount} Reports Found
        </div>` : '';

    row.innerHTML = `
        <div class="bot-bubble">
            <div class="msg-avatar bot-av"><i class="mdi mdi-head-lightbulb"></i></div>
            <div class="msg-body">
                <div class="msg-name">Imara AI <span class="msg-timestamp">${formatTimestamp(isoTimestamp)}</span>${retryLabel}</div>
                ${multiBadge}
                <div class="msg-content" data-raw="${escapeHtml(text)}">${renderMarkdown(text, isMultiReport)}</div>
                ${hasPrediction ? renderPredictionBubble(text, sources, metadata) : renderSources(sources)}
                <div class="msg-row-actions">
                    <button class="msg-action-btn speak-btn" title="Speak"><i class="mdi mdi-volume-high"></i></button>
                    <button class="msg-action-btn retry-btn" title="Retry"><i class="mdi mdi-refresh"></i></button>
                    <button class="msg-action-btn copy-btn" title="Copy"><i class="mdi mdi-content-copy"></i></button>
                    <button class="msg-action-btn thumb-up-btn"><i class="mdi mdi-thumb-up-outline"></i></button>
                    <button class="msg-action-btn thumb-down-btn"><i class="mdi mdi-thumb-down-outline"></i></button>
                </div>
            </div>
        </div>`;

    row.querySelector('.speak-btn').addEventListener('click', () => speakMessage(text, row.querySelector('.speak-btn')));
    row.querySelector('.copy-btn').addEventListener('click', () => {
        navigator.clipboard.writeText(text).then(() => {
            row.querySelector('.copy-btn').innerHTML = '<i class="mdi mdi-check"></i>';
            setTimeout(() => row.querySelector('.copy-btn').innerHTML = '<i class="mdi mdi-content-copy"></i>', 2000);
        });
    });
    
    row.querySelector('.retry-btn').addEventListener('click', () => sendMessage(text));

    const sendFeedback = (rating) => {
        if (!currentConvoId || !msgIdRef.id) return;
        fetch(`${CONVO_MESSAGES_BASE}/${currentConvoId}/messages/${msgIdRef.id}/feedback`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN },
            body: JSON.stringify({ rating }),
        }).catch(e => console.error('Feedback failed', e));
    };

    row.querySelector('.thumb-up-btn').addEventListener('click', () => {
        row.querySelector('.thumb-up-btn').classList.toggle('active-thumb');
        sendFeedback('up');
    });

    chatMessages.appendChild(row);
    initCharts(row);
    scrollToBottom();
}

function addErrorMessage(text) {
    const row = document.createElement('div');
    row.className = 'msg-row bot-row';
    row.innerHTML = `<div class="bot-bubble"><div class="msg-avatar bot-av"><i class="mdi mdi-head-lightbulb"></i></div><div class="msg-body"><div class="msg-name">Imara AI</div><div class="msg-content error-msg">${escapeHtml(text)}</div></div></div>`;
    chatMessages.appendChild(row);
    scrollToBottom();
}

function showThinking() {
    thinkingRow = document.createElement('div');
    thinkingRow.className = 'msg-row bot-row';
    thinkingRow.innerHTML = `<div class="bot-bubble"><div class="msg-avatar bot-av"><i class="mdi mdi-head-lightbulb"></i></div><div class="msg-body"><div class="msg-name">Imara AI</div><div class="msg-content"><div class="thinking-dots"><span></span><span></span><span></span></div></div></div></div>`;
    chatMessages.appendChild(thinkingRow);
    scrollToBottom();
}

function hideThinking() { if (thinkingRow) { thinkingRow.remove(); thinkingRow = null; } }
