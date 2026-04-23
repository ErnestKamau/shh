// ── Sidebar Toggles ───────────────────────────────────────────────────────
document.getElementById('toggle-main-sidebar')?.addEventListener('click', () => {
    if (window.innerWidth <= 768) {
        toggleMobileMenu();
    } else {
        sidebar.classList.toggle('collapsed');
    }
});

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
    if (!sidebarHistory) return;
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
    if (!sidebarHistory) return;
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
    if (!sidebarHistory) return;
    const checked = selectAllConvos.checked;
    conversations.forEach(c => {
        if (checked) selectedConvoIds.add(c.id);
        else selectedConvoIds.delete(c.id);
    });
    if (btnBulkDelete) btnBulkDelete.disabled = selectedConvoIds.size === 0;
    sidebarHistory.querySelectorAll('.history-checkbox').forEach(cb => {
        cb.checked = checked;
    });
});

btnBulkDelete?.addEventListener('click', () => {
    if (!selectedConvoIds.size) return;
    showBulkDeleteModal([...selectedConvoIds]);
});

// ── Modals ───────────────────────────────────────────────────────────────
const deleteConvoModal  = document.getElementById('deleteConvoModal');
const modalCancelBtn    = document.getElementById('modalCancelBtn');
const modalConfirmBtn   = document.getElementById('modalConfirmBtn');
const modalIcon         = document.getElementById('modalIcon');
const modalTitle        = document.getElementById('modalTitle');
const modalBody         = document.getElementById('modalBody');
const modalConvoList    = document.getElementById('modalConvoList');
let pendingDeleteId     = null;
let pendingBulkIds      = null;

function showDeleteModal(convoId) {
    if (!deleteConvoModal || !modalIcon || !modalTitle || !modalBody || !modalConvoList || !modalConfirmBtn) return;
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
    if (!deleteConvoModal || !modalIcon || !modalTitle || !modalBody || !modalConvoList || !modalConfirmBtn) return;
    pendingBulkIds  = ids;
    pendingDeleteId = null;
    const n = ids.length;

    modalIcon.className  = 'modal-icon bulk';
    modalIcon.innerHTML  = '<i class="mdi mdi-delete-sweep-outline"></i>';
    modalTitle.textContent = `Delete ${n} conversation${n !== 1 ? 's' : ''}?`;
    modalBody.textContent  = `These will be permanently removed. This cannot be undone.`;
    modalBody.style.display = '';

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
    if (!deleteConvoModal || !modalConvoList) return;
    deleteConvoModal.style.display = 'none';
    document.getElementById('imara-ai-root').classList.remove('modal-open');
    pendingDeleteId = null;
    pendingBulkIds  = null;
    modalConvoList.style.display = 'none';
    modalConvoList.innerHTML = '';
}

modalCancelBtn?.addEventListener('click', hideDeleteModal);
deleteConvoModal?.addEventListener('click', (e) => {
    if (e.target === deleteConvoModal) hideDeleteModal();
});

modalConfirmBtn?.addEventListener('click', async () => {
    if (pendingBulkIds !== null) {
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

// ── Search & Sidebar Interactions ──────────────────────────────────────────
const sidebarSearchClear = document.getElementById('sidebarSearchClear');
let sidebarSearchTimer = null;

sidebarSearch?.addEventListener('input', () => {
    const val = sidebarSearch.value;
    const hasValue = val.length > 0;
    sidebarSearchClear.style.display = hasValue ? 'flex' : 'none';

    clearTimeout(sidebarSearchTimer);
    if (!hasValue) {
        renderSidebarHistory();
        return;
    }
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

// ── Auto-resize textarea ──────────────────────────────────────────────────
messageInput?.addEventListener('input', function () {
    this.style.height = 'auto';
    this.style.height = Math.min(this.scrollHeight, 160) + 'px';
});

// ── Suggestion Chips ──────────────────────────────────────────────────────
function useChip(el) {
    const text = el.innerText.trim();
    if (messageInput) {
        messageInput.value = text;
        sendMessage();
    }
}

// ── Model & Tools Menu Handlers ──────────────────────────────────────────────
const modelBtn  = document.getElementById('modelBtn');
const modelMenu = document.getElementById('modelMenu');

modelBtn?.addEventListener('click', (e) => {
    e.stopPropagation();
    const isVisible = modelMenu.style.display !== 'none';
    modelMenu.style.display = isVisible ? 'none' : 'block';
    if (toolsMenu) toolsMenu.style.display = 'none'; // Close other
});

toolsBtn?.addEventListener('click', (e) => {
    e.stopPropagation();
    const isVisible = toolsMenu.style.display !== 'none';
    toolsMenu.style.display = isVisible ? 'none' : 'block';
    if (modelMenu) modelMenu.style.display = 'none'; // Close other
});

// Sync toggles

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

// Close menus when clicking anywhere else
document.addEventListener('click', (e) => {
    if (toolsMenu && !toolsMenu.contains(e.target) && e.target !== toolsBtn) {
        toolsMenu.style.display = 'none';
    }
    if (modelMenu && !modelMenu.contains(e.target) && e.target !== modelBtn) {
        modelMenu.style.display = 'none';
    }
});

// ── Chart Modal Handlers ────────────────────────────────────────────────────
let currentConfigId = null;

window.openChartModal = function(configId) {
    currentConfigId = configId;
    const rawJson = window.chartConfigs[configId];
    if (!rawJson) return;
    
    try {
        const cfg = JSON.parse(rawJson);
        const modal = document.getElementById('chartModal');
        const canvas = document.getElementById('modalChartCanvas');
        const titleEl = document.getElementById('chartModalTitle');
        const dataEl = document.getElementById('modalChartData');
        
        if (!modal || !canvas) return;

        titleEl.textContent = cfg.title || 'Visualization Analysis';
        
        // Destroy previous instance
        if (modalChartInstance) {
            modalChartInstance.destroy();
        }

        modal.style.display = 'flex';
        document.body.classList.add('modal-open');

        // Render Chart
        const palette = ['#a72b2a','#3b82f6','#10b981','#f59e0b','#8b5cf6','#ec4899','#06b6d4','#84cc16','#f97316','#6366f1'];
        const datasets = (cfg.datasets || []).map((ds, i) => {
            const dataPoints = Array.isArray(ds.data) ? ds.data : [];
            return {
                ...ds,
                data: dataPoints,
                backgroundColor: dataPoints.map((_, j) => palette[(i * 3 + j) % palette.length] + 'cc'),
                borderColor:     dataPoints.map((_, j) => palette[(i * 3 + j) % palette.length]),
                borderWidth: 2,
                borderRadius: 6,
            };
        });

        modalChartInstance = new Chart(canvas, {
            type: cfg.type || 'bar',
            data: { labels: cfg.labels || [], datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: true, position: 'bottom' },
                    title: { display: false }
                },
                scales: cfg.type === 'pie' || cfg.type === 'doughnut' ? {} : { 
                    y: { beginAtZero: true, grid: { color: '#f0f0f0' } },
                    x: { grid: { display: false } }
                }
            }
        });

        // Render Table (Drill-down)
        if (cfg.labels && cfg.datasets && cfg.datasets[0]) {
            let html = '<table class="drilldown-table"><thead><tr><th>Category</th>';
            cfg.datasets.forEach(ds => {
                html += `<th>${escapeHtml(ds.label || 'Value')}</th>`;
            });
            html += '</tr></thead><tbody>';
            
            cfg.labels.forEach((l, i) => {
                html += `<tr><td>${escapeHtml(l)}</td>`;
                cfg.datasets.forEach(ds => {
                    html += `<td><strong>${ds.data[i]}</strong></td>`;
                });
                html += '</tr>';
            });
            html += '</tbody></table>';
            dataEl.innerHTML = html;
        }

    } catch (e) {
        console.error('Failed to open chart modal', e);
    }
};

window.exportChartCSV = function() {
    if (!currentConfigId) return;
    const cfg = JSON.parse(window.chartConfigs[currentConfigId]);
    
    let csv = 'Category,' + cfg.datasets.map(ds => (ds.label || 'Value')).join(',') + '\n';
    cfg.labels.forEach((l, i) => {
        csv += `"${l}",` + cfg.datasets.map(ds => ds.data[i]).join(',') + '\n';
    });

    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `imara-data-export-${Date.now()}.csv`;
    a.click();
};

window.exportChartPDF = function() {
    window.print();
};

window.closeChartModal = function() {
    const modal = document.getElementById('chartModal');
    if (modal) modal.style.display = 'none';
    document.body.classList.remove('modal-open');
};

document.getElementById('closeChartModal')?.addEventListener('click', closeChartModal);

document.getElementById('chartModal')?.addEventListener('click', (e) => {
    if (e.target === document.getElementById('chartModal')) {
        closeChartModal();
    }
});

// ESC to close modal
window.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeChartModal();
});

// Initialize state
updateToolsUI();
if (visualsToggle) {
    visualsEnabled = visualsToggle.checked;
    syncToolsIndicator();
}
