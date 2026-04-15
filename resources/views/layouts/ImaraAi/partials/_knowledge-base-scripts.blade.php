{{-- Knowledge Base Management Scripts --}}
<script>

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
    const totalItems = allKnowledgeItems.length;
    const manualDocs = allKnowledgeItems.filter(item => item.manual_doc_id).length;
    const lastUpdated = allKnowledgeItems.length > 0 
        ? allKnowledgeItems[0].last_synced_at 
        : 'Never';
    
    document.getElementById('statTotalItems').textContent = totalItems;
    document.getElementById('statManualDocs').textContent = manualDocs;
    document.getElementById('statLastUpdated').textContent = lastUpdated;
}

function applyFilters() {
    const searchTerm = kbSearch.value.toLowerCase();
    const collectionFilter = kbFilterCollection.value.toLowerCase();
    const typeFilter = kbFilterType.value.toLowerCase();
    const permissionFilter = kbFilterPermission.value;
    
    const filtered = allKnowledgeItems.filter(item => {
        // Search filter
        const matchesSearch = !searchTerm || 
            item.identifier.toLowerCase().includes(searchTerm) ||
            item.collection_name.toLowerCase().includes(searchTerm);
        
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
                        <button onclick="openKnowledgeModal(${item.manual_doc_id})" style="background: none; border: none; cursor: pointer; color: #4b5563; transition: color 0.2s; padding: 4px 8px;" title="Edit" onmouseover="this.style.color='#2563eb'" onmouseout="this.style.color='#4b5563'">
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
        const statusBadge = isManual
            ? `<span style="display:inline-flex;align-items:center;gap:4px;background:${sc.bg};color:${sc.color};padding:3px 8px;border-radius:9999px;font-size:0.78rem;font-weight:600;">
                <i class="mdi ${sc.icon}"></i>${sc.label}
               </span>`
            : `<span style="display:inline-flex;align-items:center;gap:4px;background:#f3f4f6;color:#6b7280;padding:3px 8px;border-radius:9999px;font-size:0.78rem;">
                <i class="mdi mdi-cog-outline"></i>System
               </span>`;

        const lastIndexedCell = item.last_indexed_at
            ? `<span style="color:#9ca3af;font-size:0.85rem;">${item.last_indexed_at}</span>`
            : `<span style="color:#d1d5db;font-size:0.85rem;">—</span>`;

        row.innerHTML = `
            <td style="padding: 12px 16px; color: #111827; font-weight: 500;">
                <span style="background: #f0f9ff; color: #0369a1; padding: 4px 8px; border-radius: 4px; font-size: 0.85rem;">${item.collection_name}</span>
            </td>
            <td style="padding: 12px 16px; color: #111827;" title="${item.identifier}">
                <div style="max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                    ${item.identifier}
                    ${isOwnDocument ? '<span style="background: #dcfce7; color: #15803d; padding: 2px 6px; border-radius: 3px; font-size: 0.75rem; margin-left: 8px;">Created by You</span>' : ''}
                </div>
            </td>
            <td style="padding: 12px 16px; color: #4b5563;">${item.entity_type || 'Manual Entry'}</td>
            <td style="padding: 12px 16px; color: #4b5563; font-size: 0.85rem;">${item.manual_doc_id ? (item.manual_doc_id === 'General.View' ? 'General' : item.manual_doc_id) : 'System'}</td>
            <td style="padding: 12px 16px; text-align: center;">
                <span style="background: #e0f2fe; color: #0369a1; padding: 2px 8px; border-radius: 9999px; font-size: 0.8rem;">
                    ${item.chunk_count}
                </span>
            </td>
            <td style="padding: 12px 16px;">${statusBadge}</td>
            <td style="padding: 12px 16px;">${lastIndexedCell}</td>
            <td style="padding: 12px 16px; text-align: right;">${actionsHtml}</td>
        `;
        kbTableBody.appendChild(row);
    });
}

async function openKnowledgeModal(id = null) {
    kbForm.reset();
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
                document.getElementById('kbContent').value = doc.content;
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
    
    knowledgeModal.style.display = 'flex';
}

function closeKnowledgeModal() {
    knowledgeModal.style.display = 'none';
}

async function reindexKnowledge(id) {
    if (!confirm('Re-index this document? The AI will re-process its content. This may take a few seconds.')) return;

    try {
        const response = await fetch(`${KB_URL}/${id}/reindex`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Content-Type': 'application/json' }
        });
        const result = await response.json();

        if (response.status === 403) { alert('You do not have permission to re-index this document.'); return; }
        if (result.status === 'ok') {
            fetchKnowledgeBase();
        } else {
            alert('Re-index failed: ' + result.message);
        }
    } catch (err) {
        console.error('Re-index failed:', err);
        alert('An error occurred while re-indexing the document.');
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

async function handleKbFormSubmit(e) {
    e.preventDefault();
    const id = document.getElementById('manualDocId').value;
    const url = id ? `${KB_URL}/${id}` : KB_URL;
    const method = id ? 'PUT' : 'POST';
    
    const formData = {
        title: document.getElementById('kbTitle').value,
        collection: document.getElementById('kbCollection').value,
        permission: document.getElementById('kbPermission').value,
        content: document.getElementById('kbContent').value,
    };

    try {
        const response = await fetch(url, {
            method: method,
            headers: {
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        });
        const result = await response.json();
        
        if (response.status === 403) {
            alert('You do not have permission to save this document.');
            return;
        }
        
        if (response.status === 404) {
            alert('Document not found.');
            return;
        }
        
        if (result.status === 'ok') {
            closeKnowledgeModal();
            fetchKnowledgeBase();
        } else {
            alert('Save failed: ' + result.message);
        }
    } catch (err) {
        console.error('Save failed:', err);
        alert('An error occurred while saving the document.');
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
