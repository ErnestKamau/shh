{{-- Knowledge Base Management Scripts --}}
<script>
let allKnowledgeItems = [];
let simplemde = null;
let activeKnowledgeTab = 'text'; // 'text' or 'file'
let selectedFile = null;

async function fetchKnowledgeBase() {
    console.log('🔄 Fetching knowledge base from:', KB_URL);
    if (!kbTableBody) {
        console.error('❌ kbTableBody element not found!');
        return;
    }
    
    kbTableBody.innerHTML = '<tr style="border-bottom: 1px solid #e5e7eb;"><td colspan="8" style="padding: 40px; text-align: center; color: #9ca3af;"><i class="mdi mdi-loading mdi-spin" style="font-size: 1.5rem;"></i><div style="margin-top: 8px;">Loading knowledge base...</div></td></tr>';

    try {
        const response = await fetch(KB_URL);
        console.log('✓ Response status:', response.status);
        
        const result = await response.json();
        console.log('✓ Response data:', result);

        allKnowledgeItems = [];
        
        if (result.status === 'ok' && result.data && result.data.length > 0) {
            console.log('✓ Items found:', result.data.length);
            allKnowledgeItems = result.data;
            updateStats();
            applyFilters();
        } else if (result.status === 'ok') {
            console.log('ℹ️ No items in knowledge base');
            kbTableBody.innerHTML = '';
            kbEmptyState.style.display = 'block';
        } else {
            console.warn('⚠️ Unexpected response status:', result);
            kbTableBody.innerHTML = '';
            kbEmptyState.style.display = 'block';
        }
    } catch (err) {
        console.error('❌ Failed to fetch Knowledge Base:', err);
        kbTableBody.innerHTML = '<tr style="border-bottom: 1px solid #e5e7eb;"><td colspan="7" style="padding: 40px; text-align: center; color: #ef4444;">Error loading knowledge base: ' + err.message + '</td></tr>';
    }
}

function updateStats() {
    const totalChunks = allKnowledgeItems.reduce((acc, item) => acc + (parseInt(item.chunk_count) || 0), 0);
    const manualDocs = allKnowledgeItems.filter(item => item.manual_doc_id).length;
    const lastUpdated = allKnowledgeItems.length > 0 
        ? allKnowledgeItems[0].last_synced_at 
        : 'Never';
    
    document.getElementById('statTotalItems').textContent = totalChunks;
    document.getElementById('statManualDocs').textContent = manualDocs;
    document.getElementById('statLastUpdated').textContent = lastUpdated;
}

function applyFilters() {
    const searchTerm = kbSearch.value.toLowerCase();
    const collectionFilter = kbFilterCollection ? kbFilterCollection.value.toLowerCase() : '';
    const typeFilter = kbFilterType ? kbFilterType.value.toLowerCase() : '';
    const permissionFilter = kbFilterPermission ? kbFilterPermission.value : '';
    
    const filtered = allKnowledgeItems.filter(item => {
        // Search filter
        const matchesSearch = !searchTerm || 
            item.identifier.toLowerCase().includes(searchTerm) ||
            item.collection_name.toLowerCase().includes(searchTerm) ||
            (item.tags && item.tags.some(t => t.toLowerCase().includes(searchTerm)));
        
        // Collection filter
        const matchesCollection = !collectionFilter || 
            item.collection_name.toLowerCase() === collectionFilter;
        
        // Type filter
        const itemType = item.manual_doc_id ? 'manual entry' : 'system managed';
        const matchesType = !typeFilter || itemType.includes(typeFilter);
        
        // Permission filter
        const matchesPermission = !permissionFilter || 
            (item.manual_doc_id && permissionFilter === 'manual entry') ||
            (!item.manual_doc_id && permissionFilter === 'system managed');
        
        return matchesSearch && matchesCollection && matchesType && matchesPermission;
    });
    
    renderKnowledgeTable(filtered);
}

function renderKnowledgeTable(items) {
    if (items.length === 0) {
        kbEmptyState.style.display = 'block';
        kbTableBody.innerHTML = '';
        return;
    }
    
    kbEmptyState.style.display = 'none';
    kbTableBody.innerHTML = '';
    
    items.forEach(item => {
        const row = document.createElement('tr');
        row.style.borderBottom = '1px solid #e5e7eb';
        row.style.transition = 'background 0.2s';
        row.addEventListener('mouseenter', () => row.style.background = '#f9fafb');
        row.addEventListener('mouseleave', () => row.style.background = 'transparent');
        
        // Determine permissions
        const isManual = !!item.manual_doc_id;
        const canEdit = isManual && item.can_edit;
        const canDelete = isManual && item.can_delete;
        const isOwnDocument = isManual && item.can_edit;
        
        // Actions HTML
        let actionsHtml = '';
        if (isManual) {
            actionsHtml = `
                <div style="display: flex; gap: 8px; justify-content: flex-end;">
                    ${canEdit ? `
                        <button onclick="reindexKnowledge(${item.manual_doc_id})" style="background: none; border: none; cursor: pointer; color: #4b5563; transition: color 0.2s; padding: 4px 8px;" title="Re-index" onmouseover="this.style.color='#059669'" onmouseout="this.style.color='#4b5563'">
                            <i class="mdi mdi-sync" style="font-size: 1.1rem;"></i>
                        </button>
                        <button onclick="window.location.href = '{{ url('/imara-ai/knowledge-editor') }}/${item.manual_doc_id}'" style="background: none; border: none; cursor: pointer; color: #4b5563; transition: color 0.2s; padding: 4px 8px;" title="Edit" onmouseover="this.style.color='#2563eb'" onmouseout="this.style.color='#4b5563'">
                            <i class="mdi mdi-pencil" style="font-size: 1.1rem;"></i>
                        </button>
                    ` : `
                        <button disabled style="background: none; border: none; cursor: not-allowed; color: #d1d5db; padding: 4px 8px;" title="You don't have permission to edit">
                            <i class="mdi mdi-pencil" style="font-size: 1.1rem;"></i>
                        </button>
                    `}
                    ${canDelete ? `
                        <button onclick="deleteKnowledge(${item.manual_doc_id})" style="background: none; border: none; cursor: pointer; color: #ef4444; transition: color 0.2s; padding: 4px 8px;" title="Delete" onmouseover="this.style.color='#dc2626'" onmouseout="this.style.color='#ef4444'">
                            <i class="mdi mdi-trash-can-outline" style="font-size: 1.1rem;"></i>
                        </button>
                    ` : `
                        <button disabled style="background: none; border: none; cursor: not-allowed; color: #d1d5db; padding: 4px 8px;" title="You don't have permission to delete">
                            <i class="mdi mdi-trash-can-outline" style="font-size: 1.1rem;"></i>
                        </button>
                    `}
                </div>
            `;
        } else {
            actionsHtml = `
                <div style="display: flex; gap: 8px; justify-content: flex-end;">
                    <button disabled style="background: none; border: none; cursor: not-allowed; color: #d1d5db; padding: 4px 8px;" title="System-managed content">
                        <i class="mdi mdi-pencil" style="font-size: 1.1rem;"></i>
                    </button>
                    <button disabled style="background: none; border: none; cursor: not-allowed; color: #d1d5db; padding: 4px 8px;" title="System-managed content">
                        <i class="mdi mdi-trash-can-outline" style="font-size: 1.1rem;"></i>
                    </button>
                </div>
            `;
        }
        
        // Get permission label
        const permissionMap = {
            'general.view': 'General Access',
            'laboratory.samples.view': 'Lab Staff',
            'laboratory.admin': 'Lab Admin',
            'quality.control.manage': 'QC Team',
            // Legacy values kept for backward compatibility with existing rows.
            'General.View': 'General Access',
            'Laboratory.Samples.View': 'Lab Staff',
            'Laboratory.Admin': 'Lab Admin',
            'Quality.Control.Manage': 'QC Team'
        };

        // Build indexing status badge
        const statusConfig = {
            indexed: { label: 'Indexed', color: '#065f46', bg: '#d1fae5', icon: 'mdi-check-circle-outline' },
            pending: { label: 'Pending',  color: '#92400e', bg: '#fef3c7', icon: 'mdi-clock-outline' },
            failed:  { label: 'Failed',   color: '#991b1b', bg: '#fee2e2', icon: 'mdi-alert-circle-outline' },
            system:  { label: 'System',   color: '#374151', bg: '#f3f4f6', icon: 'mdi-cog-outline' },
        };
        const sc = statusConfig[item.indexing_status] || statusConfig.system;
        let statusBadge = isManual
            ? `<span style="display:inline-flex;align-items:center;gap:4px;background:${sc.bg};color:${sc.color};padding:3px 8px;border-radius:9999px;font-size:0.78rem;font-weight:600;">
                <i class="mdi ${sc.icon}"></i>${sc.label}
               </span>`
            : `<span style="display:inline-flex;align-items:center;gap:4px;background:#f3f4f6;color:#6b7280;padding:3px 8px;border-radius:9999px;font-size:0.78rem;">
                <i class="mdi mdi-cog-outline"></i>System
               </span>`;

        if (item.is_expired) {
            statusBadge = `
                <div style="display:flex;flex-direction:column;gap:4px;">
                    ${statusBadge}
                    <span style="display:inline-flex;align-items:center;gap:4px;background:#fee2e2;color:#991b1b;padding:3px 8px;border-radius:9999px;font-size:0.7rem;font-weight:700;text-transform:uppercase;">
                        <i class="mdi mdi-clock-alert-outline"></i>Expired
                    </span>
                </div>
            `;
        }

        const lastIndexedCell = item.last_indexed_at
            ? `<span style="color:#9ca3af;font-size:0.85rem;">${item.last_indexed_at}</span>`
            : `<span style="color:#d1d5db;font-size:0.85rem;">—</span>`;

        row.innerHTML = `
            <td style="padding: 12px 16px; width: 48px;">
                ${isManual ? `<input type="checkbox" class="kb-row-checkbox" value="${item.manual_doc_id}" style="cursor: pointer;">` : ''}
            </td>
            <td style="padding: 12px 16px; color: #111827; font-weight: 500;">
                <span style="background: #f0f9ff; color: #0369a1; padding: 4px 8px; border-radius: 4px; font-size: 0.85rem;">${item.collection_name}</span>
            </td>
            <td style="padding: 12px 16px; color: #111827;" title="${item.identifier}">
                <div style="max-width: 250px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-weight: 600; font-size: 0.95rem; color: #1e293b; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${item.identifier}</span>
                        ${item.external_source_url ? `<a href="${item.external_source_url}" target="_blank" title="External Source" style="color:#a72b2a;text-decoration:none;"><i class="mdi mdi-link-variant"></i></a>` : ''}
                    </div>
                    ${isOwnDocument ? '<div style="margin-top:2px;"><span style="background: #dcfce7; color: #15803d; padding: 2px 6px; border-radius: 3px; font-size: 0.7rem;">Created by You</span></div>' : ''}
                    ${item.tags && item.tags.length ? `
                        <div style="display:flex;flex-wrap:wrap;gap:4px;margin-top:6px;">
                            ${item.tags.map(t => `<span style="background:#f1f5f9;color:#475569;font-size:0.65rem;padding:1px 6px;border-radius:4px;font-weight:600;border:1px solid #e2e8f0;">${t}</span>`).join('')}
                        </div>
                    ` : ''}
                </div>
            </td>
            <td style="padding: 12px 16px; color: #4b5563;">${item.entity_type || 'Manual Entry'}</td>
            <td style="padding: 12px 16px; text-align: center;">
                <span style="background: #e0f2fe; color: #0369a1; padding: 2px 8px; border-radius: 9999px; font-size: 0.8rem;">
                    ${item.chunk_count}
                </span>
            </td>
            <td style="padding: 12px 16px;">${statusBadge}</td>
            <td style="padding: 12px 16px;">${lastIndexedCell}</td>
            <td style="padding: 12px 16px; text-align: right;">${actionsHtml}</td>
        `;

        // Selection logic
        const checkbox = row.querySelector('input[type="checkbox"]');
        if (checkbox) {
            checkbox.addEventListener('change', () => {
                updateBulkActionBar();
            });
        }

        kbTableBody.appendChild(row);
    });
}

let selectedKbIds = [];

function toggleAllKbSelection(master) {
    const checkboxes = kbTableBody.querySelectorAll('.kb-row-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = master.checked;
    });
    updateBulkActionBar();
}

function updateBulkActionBar() {
    const checkboxes = kbTableBody.querySelectorAll('.kb-row-checkbox:checked');
    selectedKbIds = Array.from(checkboxes).map(cb => cb.value);
    
    const bar = document.getElementById('bulkActionBar');
    const count = document.getElementById('selectedCount');
    
    if (selectedKbIds.length > 0) {
        count.innerText = selectedKbIds.length;
        bar.style.transform = 'translateX(-50%) translateY(0)';
    } else {
        bar.style.transform = 'translateX(-50%) translateY(100px)';
        const master = document.getElementById('selectAllKb');
        if (master) master.checked = false;
    }
}

function deselectAllKb() {
    const checkboxes = kbTableBody.querySelectorAll('.kb-row-checkbox');
    checkboxes.forEach(cb => cb.checked = false);
    updateBulkActionBar();
}

async function bulkDelete() {
    if (!confirm(`Are you sure you want to delete ${selectedKbIds.length} documents? This action cannot be undone.`)) return;
    
    try {
        const response = await fetch(`${KB_URL}/bulk-delete`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ ids: selectedKbIds })
        });
        
        const result = await response.json();
        if (result.status === 'ok') {
            deselectAllKb();
            fetchKnowledgeBase();
        } else {
            alert('Bulk delete failed: ' + result.message);
        }
    } catch (err) {
        console.error('Bulk delete error:', err);
        alert('An error occurred during bulk deletion.');
    }
}

async function bulkReindex() {
    if (!confirm(`Re-index ${selectedKbIds.length} documents? This will rebuild the AI vector cache for these items.`)) return;
    
    try {
        const response = await fetch(`${KB_URL}/bulk-reindex`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ ids: selectedKbIds })
        });
        
        const result = await response.json();
        if (result.status === 'ok') {
            deselectAllKb();
            fetchKnowledgeBase();
            alert('Bulk re-indexing started. This may take a minute to complete in the background.');
        } else {
            alert('Bulk re-index failed: ' + result.message);
        }
    } catch (err) {
        console.error('Bulk re-index error:', err);
        alert('An error occurred during bulk re-indexing.');
    }
}

async function openKnowledgeModal(id = null) {
    kbForm.reset();
    if (simplemde) simplemde.value(''); // Clear editor
    
    document.getElementById('manualDocId').value = '';
    document.getElementById('kbCreatedBy').value = '';
    document.getElementById('kbCreatedAt').value = '';
    document.getElementById('kbModalTitle').innerText = id ? 'Edit Knowledge' : 'Add Knowledge';
    document.getElementById('kbCreatorInfo').style.display = 'none';
    
    if (id) {
        try {
            const response = await fetch(`${KB_URL}/${id}`);
            const result = await response.json();
            
            if (response.status === 403) {
                alert('You do not have permission to edit this document.');
                return;
            }
            
            if (response.status === 404) {
                alert('Document not found.');
                return;
            }
            
            if (result.status === 'ok') {
                const doc = result.data;
                document.getElementById('manualDocId').value = doc.id;
                document.getElementById('kbTitle').value = doc.title;
                document.getElementById('kbCollection').value = doc.collection_name;
                document.getElementById('kbPermission').value = doc.required_permission;
                if (simplemde) simplemde.value(doc.content);
                document.getElementById('kbCreatedBy').value = doc.created_by || '';
                document.getElementById('kbCreatedAt').value = doc.created_at || '';
                
                // Show creator info if available
                if (doc.created_by) {
                    document.getElementById('kbCreatorInfo').style.display = 'block';
                    // Format date for display
                    const createdDate = new Date(doc.created_at).toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                    document.getElementById('kbCreatorLabel').innerText = `Last edited on ${createdDate}`;
                }
            } else {
                alert('Failed to load document: ' + result.message);
                return;
            }
        } catch (err) {
            console.error('Error fetching doc:', err);
            alert('Failed to fetch document.');
            return;
        }
    }
    
    switchKnowledgeTab('file');
    knowledgeModal.style.display = 'flex';
}

function closeKnowledgeModal() {
    knowledgeModal.style.display = 'none';
    clearSelectedFile();
    switchKnowledgeTab('file');
}

function switchKnowledgeTab(tab) {
    activeKnowledgeTab = tab;
    const tabText = document.getElementById('tabManualText');
    const tabFile = document.getElementById('tabFileUpload');
    const textInput = document.getElementById('manualTextInput');
    const fileInput = document.getElementById('fileUploadInput');
    const sharedFields = document.getElementById('kbSharedFields');

    if (tab === 'text') {
        tabText.style.background = '#fff';
        tabText.style.color = '#a72b2a';
        tabText.style.fontWeight = '700';
        tabText.style.boxShadow = '0 1px 2px rgba(0,0,0,0.05)';
        tabText.dataset.active = 'true';

        tabFile.style.background = 'transparent';
        tabFile.style.color = '#6b7280';
        tabFile.style.fontWeight = '600';
        tabFile.style.boxShadow = 'none';
        tabFile.dataset.active = 'false';

        textInput.style.display = 'block';
        fileInput.style.display = 'none';
        if (sharedFields) sharedFields.style.display = 'none';
    } else {
        tabFile.style.background = '#fff';
        tabFile.style.color = '#a72b2a';
        tabFile.style.fontWeight = '700';
        tabFile.style.boxShadow = '0 1px 2px rgba(0,0,0,0.05)';
        tabFile.dataset.active = 'true';

        tabText.style.background = 'transparent';
        tabText.style.color = '#6b7280';
        tabText.style.fontWeight = '600';
        tabText.style.boxShadow = 'none';
        tabText.dataset.active = 'false';

        textInput.style.display = 'none';
        fileInput.style.display = 'block';
        if (sharedFields) sharedFields.style.display = 'block';
    }
}

function handleFileSelect(file) {
    if (!file) return;
    
    selectedFile = file;
    document.getElementById('fileName').innerText = file.name;
    document.getElementById('fileSize').innerText = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
    document.getElementById('fileInfo').style.display = 'flex';
    document.getElementById('dropZone').style.display = 'none';
    
    // Auto-fill title if empty
    const titleInput = document.getElementById('kbTitle');
    if (!titleInput.value) {
        titleInput.value = file.name.split('.').slice(0, -1).join('.');
    }
}

function clearSelectedFile() {
    selectedFile = null;
    document.getElementById('kbFile').value = '';
    document.getElementById('fileInfo').style.display = 'none';
    document.getElementById('dropZone').style.display = 'block';
}

async function reindexKnowledge(id) {
    if (!confirm('Re-index this document? The AI will re-process its content. This may take a few seconds.')) return;

    try {
        const response = await fetch(`${KB_URL}/${id}/reindex`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        });
        const result = await response.json();

        if (response.status === 403) { alert('You do not have permission to re-index this document.'); return; }
        
        if (result.status === 'ok') {
            markReindexQueued(id);
            setTimeout(fetchKnowledgeBase, 2000);
        } else {
            alert('Re-index failed: ' + result.message);
        }
    } catch (err) {
        console.error('Re-index failed:', err);
        alert('An error occurred while re-indexing the document.');
    }
}

async function batchReindex() {
    const selectedIds = Array.from(document.querySelectorAll('.kb-checkbox:checked')).map(cb => cb.value);
    if (selectedIds.length === 0) {
        alert('Please select items to re-index.');
        return;
    }

    if (!confirm(`Re-index ${selectedIds.length} items?`)) return;

    try {
        const response = await fetch(`${KB_URL}/batch-reindex`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ ids: selectedIds })
        });

        const result = await response.json();
        if (result.status === 'ok') {
            fetchKnowledgeBase();
        } else {
            alert('Batch re-indexing failed: ' + result.message);
        }
    } catch (err) {
        console.error('Batch re-indexing failed:', err);
    }
}

function markReindexQueued(itemId) {
    const statusPill = document.querySelector(`.status-pill[data-id="${itemId}"]`);
    if (statusPill) {
        statusPill.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> QUEUED...';
        statusPill.style.background = '#fef3c7';
        statusPill.style.color = '#92400e';
    }
}

async function deleteKnowledge(id) {
    if (!confirm('Are you sure you want to delete this knowledge entry? This will also remove the AI chunks associated with it.')) {
        return;
    }

    try {
        const response = await fetch(`${KB_URL}/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Content-Type': 'application/json'
            }
        });
        const result = await response.json();
        
        if (response.status === 403) {
            alert('You do not have permission to delete this document.');
            return;
        }
        
        if (response.status === 404) {
            alert('Document not found.');
            return;
        }
        
        if (result.status === 'ok') {
            fetchKnowledgeBase();
        } else {
            alert('Failed to delete: ' + result.message);
        }
    } catch (err) {
        console.error('Delete failed:', err);
        alert('An error occurred while deleting the document.');
    }
}

// Initialize
window.addEventListener('DOMContentLoaded', () => {
    // Initialize SimpleMDE
    const contentArea = document.getElementById("kbContent");
    if (contentArea) {
        simplemde = new SimpleMDE({ 
            element: contentArea,
            spellChecker: false,
            autosave: {
                enabled: false,
            },
            placeholder: "Type or paste your document content here (Markdown supported)...",
            status: ["lines", "words", "cursor"],
            hideIcons: ["guide", "fullscreen", "side-by-side"],
        });

        // Add real-time cursor position tracking
        const cursorDisplay = document.getElementById('cursorPosition');
        if (cursorDisplay && simplemde.codemirror) {
            cursorDisplay.style.display = 'block';
            simplemde.codemirror.on("cursorActivity", () => {
                const pos = simplemde.codemirror.getCursor();
                cursorDisplay.innerText = `Line ${pos.line + 1}, Col ${pos.ch + 1}`;
            });
        }
    }

    fetchKnowledgeBase();

    // Setup filter listeners
    if (kbSearch) kbSearch.addEventListener('input', applyFilters);
    if (kbFilterCollection) kbFilterCollection.addEventListener('change', applyFilters);
    if (kbFilterType) kbFilterType.addEventListener('change', applyFilters);
    if (typeof kbFilterPermission !== 'undefined' && kbFilterPermission) kbFilterPermission.addEventListener('change', applyFilters);

    // Setup Drag and Drop
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('kbFile');

    if (dropZone && fileInput) {
        dropZone.onclick = () => fileInput.click();
        fileInput.onchange = (e) => handleFileSelect(e.target.files[0]);

        dropZone.ondragover = (e) => {
            e.preventDefault();
            dropZone.style.borderColor = '#a72b2a';
            dropZone.style.background = '#fff8f8';
        };

        dropZone.ondragleave = () => {
            dropZone.style.borderColor = '#d1d5db';
            dropZone.style.background = '#f9fafb';
        };

        dropZone.ondrop = (e) => {
            e.preventDefault();
            dropZone.style.borderColor = '#d1d5db';
            dropZone.style.background = '#f9fafb';
            handleFileSelect(e.dataTransfer.files[0]);
        };
    }
});

async function handleKbFormSubmit(e) {
    e.preventDefault();
    const id = document.getElementById('manualDocId').value;
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const originalBtnText = submitBtn.innerText;

    if (activeKnowledgeTab === 'file' && !selectedFile && !id) {
        alert('Please select a file to upload.');
        return;
    }

    try {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="mdi mdi-loading mdi-spin" style="margin-right:8px;"></i>Processing...';

        let response;
        if (activeKnowledgeTab === 'file' && selectedFile) {
            // Multipart Upload
            const formData = new FormData();
            formData.append('file', selectedFile);
            formData.append('collection', document.getElementById('kbCollection').value);
            formData.append('permission', document.getElementById('kbPermission').value);
            // Metadata as JSON string
            formData.append('metadata', JSON.stringify({
                title: document.getElementById('kbTitle').value
            }));

            response = await fetch(`${KB_URL}/upload`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF_TOKEN },
                body: formData
            });
        } else {
            // Standard JSON Update/Create
            const url = id ? `${KB_URL}/${id}` : KB_URL;
            const method = id ? 'PUT' : 'POST';
            const payload = {
                title: document.getElementById('kbTitle').value,
                collection: document.getElementById('kbCollection').value,
                permission: document.getElementById('kbPermission').value,
                content: simplemde ? simplemde.value() : document.getElementById('kbContent').value,
            };

            response = await fetch(url, {
                method: method,
                headers: {
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            });
        }

        const result = await response.json();
        
        if (response.status === 403) {
            alert('You do not have permission to perform this action.');
        } else if (response.status === 413) {
            alert('File is too large. Max size is 20MB.');
        } else if (result.status === 'ok') {
            closeKnowledgeModal();
            fetchKnowledgeBase();
        } else {
            alert('Action failed: ' + (result.message || 'Unknown error'));
        }
    } catch (err) {
        console.error('KB Submission failed:', err);
        alert('An unexpected error occurred. Please check console for details.');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerText = originalBtnText;
    }
}

function clearFilters() {
    kbSearch.value = '';
    kbFilterCollection.value = '';
    kbFilterType.value = '';
    kbFilterPermission.value = '';
    applyFilters();
}

</script>
