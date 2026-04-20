@extends('layouts.ImaraAi.manager')

@section('kb-content')
<div class="editor-page" style="height: calc(100vh - 100px); display: flex; flex-direction: column; background: #f8fafc;">
    {{-- Editor Header --}}
    <div style="background: white; border-bottom: 1px solid #e2e8f0; padding: 16px 32px; display: flex; justify-content: space-between; align-items: center; z-index: 10;">
        <div style="display: flex; align-items: center; gap: 16px;">
            <div>
                <input type="text" id="editorTitle" value="{{ $doc->title ?? '' }}" placeholder="Document Title..." style="font-size: 1.25rem; font-weight: 700; color: #1e293b; border: none; outline: none; width: 400px; padding: 4px 8px; border-radius: 6px; transition: background 0.2s;" onfocus="this.style.background='#f1f5f9'" onblur="this.style.background='transparent'">
                <div style="display: flex; align-items: center; gap: 12px; margin-left: 8px;">
                    <div id="saveStatus" style="font-size: 0.75rem; color: #94a3b8;">
                        @if($doc) 
                            @php $date = \Carbon\Carbon::parse($doc->created_at); @endphp
                            Last saved {{ $date->diffForHumans() }}
                        @else
                            Draft
                        @endif
                    </div>
                    <div id="cursorPosition" style="font-size: 0.75rem; color: #a72b2a; font-weight: 600; padding: 2px 8px; background: #fff1f2; border-radius: 4px; display: none;">
                        Line 1, Col 1
                    </div>
                </div>
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 12px;">
            <div style="display: flex; flex-direction: column; align-items: flex-end; margin-right: 12px;">
                <label style="font-size: 0.7rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em;">Collection</label>
                <select id="editorCollection" style="border: none; background: transparent; font-weight: 600; color: #a72b2a; outline: none; text-align: right; cursor: pointer;">
                    <option value="General Ops" {{ ($doc->collection_name ?? '') == 'General Ops' ? 'selected' : '' }}>General Ops</option>
                    <option value="Inventory" {{ ($doc->collection_name ?? '') == 'Inventory' ? 'selected' : '' }}>Inventory</option>
                    <option value="SOPs" {{ ($doc->collection_name ?? '') == 'SOPs' ? 'selected' : '' }}>SOPs</option>
                    <option value="Product Catalog" {{ ($doc->collection_name ?? '') == 'Product Catalog' ? 'selected' : '' }}>Product Catalog</option>
                </select>
            </div>
            
            <button onclick="saveDocument()" id="saveBtn" style="background: #a72b2a; color: white; border: none; padding: 10px 24px; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: all 0.2s; box-shadow: 0 4px 6px -1px rgba(167, 43, 42, 0.2);" onmouseover="this.style.background='#8e2423'; this.style.transform='translateY(-1px)'" onmouseout="this.style.background='#a72b2a'; this.style.transform='translateY(0)'">
                <i class="mdi mdi-content-save-outline"></i>
                Save Document
            </button>
        </div>
    </div>

    {{-- Main Column Layout --}}
    <div style="flex: 1; display: grid; grid-template-columns: 1fr 400px; overflow: hidden;">
        {{-- Left: Editor --}}
        <div style="background: white; border-right: 1px solid #e2e8f0; display: flex; flex-direction: column; overflow: hidden;">
            <div style="flex: 1; padding: 0 32px; overflow-y: auto;" id="editorWrapper">
                <textarea id="editorContent" style="display: none;">{{ $doc->content ?? '' }}</textarea>
            </div>
        </div>

        {{-- Right: Semantic Preview Sidebar --}}
        <div style="background: #fdfdfd; display: flex; flex-direction: column; overflow: hidden;">
            <div style="padding: 24px; border-bottom: 1px solid #e2e8f0;">
                <h3 style="font-size: 1rem; font-weight: 700; color: #1e293b; margin: 0 0 16px 0; display: flex; align-items: center; gap: 8px;">
                    <i class="mdi mdi-vector-search" style="color: #a72b2a;"></i>
                    Semantic Preview
                </h3>
                <div style="position: relative;">
                    <i class="mdi mdi-magnify" style="position: absolute; left: 12px; top: 10px; color: #94a3b8;"></i>
                    <input type="text" id="previewSearch" placeholder="Test vector retrieval..." style="width: 100%; padding: 10px 12px 10px 36px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 0.9rem; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='#a72b2a'" onkeydown="if(event.key === 'Enter') runSearchPreview()">
                </div>
                <p style="font-size: 0.75rem; color: #64748b; margin: 8px 0 0 0;">Enter a query to see which document chunks the AI retrieves as top matches.</p>
            </div>

            <div id="previewResults" style="flex: 1; overflow-y: auto; padding: 24px; border-bottom: 1px solid #e2e8f0;">
                <div style="text-align: center; color: #94a3b8; margin-top: 40px; border: 2px dashed #f1f5f9; padding: 32px; border-radius: 16px;">
                    <i class="mdi mdi-magnify-scan" style="font-size: 2rem; margin-bottom: 8px; display: block;"></i>
                    <p style="margin: 0; font-size: 0.875rem;">Run a test search to verify how your knowledge is organized.</p>
                </div>
            </div>

            {{-- New Governance Section --}}
            <div style="padding: 24px; background: #f8fafc;">
                <h3 style="font-size: 0.875rem; font-weight: 700; color: #1e293b; margin: 0 0 16px 0; display: flex; align-items: center; gap: 8px; text-transform: uppercase; letter-spacing: 0.025em;">
                    <i class="mdi mdi-shield-check-outline" style="color: #a72b2a;"></i>
                    Data Governance
                </h3>
                
                <div style="display: flex; flex-direction: column; gap: 16px;">
                    {{-- Expiry --}}
                    <div>
                        <label style="display: block; font-size: 0.75rem; font-weight: 600; color: #64748b; margin-bottom: 6px;">KNOWLEDGE EXPIRY</label>
                        <input type="date" id="expiryDate" value="{{ optional($doc)->expires_at ? date('Y-m-d', strtotime($doc->expires_at)) : '' }}" style="width: 100%; padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.875rem; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='#a72b2a'">
                        <p style="font-size: 0.65rem; color: #94a3b8; margin-top: 4px;">Leave blank for permanent knowledge.</p>
                    </div>

                    {{-- Tags --}}
                    <div>
                        <label style="display: block; font-size: 0.75rem; font-weight: 600; color: #64748b; margin-bottom: 6px;">METADATA TAGS</label>
                        <input type="text" id="metadataTags" value="{{ implode(', ', json_decode(optional($doc)->metadata ?? '{}')->tags ?? []) }}" placeholder="e.g. policy, compliance, 2024" style="width: 100%; padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.875rem; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='#a72b2a'">
                        <p style="font-size: 0.65rem; color: #94a3b8; margin-top: 4px;">Comma-separated tags for advanced filtering.</p>
                    </div>

                    {{-- External Lineage --}}
                    <div>
                        <label style="display: block; font-size: 0.75rem; font-weight: 600; color: #64748b; margin-bottom: 6px;">EXTERNAL SOURCE URL</label>
                        <input type="url" id="externalSourceUrl" value="{{ optional($doc)->external_source_url ?? '' }}" placeholder="https://sharepoint.com/policy..." style="width: 100%; padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 0.875rem; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='#a72b2a'">
                        <p style="font-size: 0.65rem; color: #94a3b8; margin-top: 4px;">Link to the authoritative original record.</p>
                    </div>

                    {{-- Lineage --}}
                    <div style="padding: 12px; background: white; border: 1px solid #e2e8f0; border-radius: 8px;">
                        <label style="display: block; font-size: 0.65rem; font-weight: 700; color: #a72b2a; margin-bottom: 4px; text-transform: uppercase;">Internal ID Lineage</label>
                        @php
                            $lineage = "/imara-ai/knowledge-editor/" . ($doc->id ?? '');
                        @endphp
                        <a href="{{ $lineage }}" target="_blank" style="font-size: 0.75rem; color: #1e293b; text-decoration: none; display: flex; align-items: center; gap: 4px; font-weight: 500;">
                            <i class="mdi mdi-link-variant"></i>
                            Internal Reference #{{ $doc->id ?? 'NEW' }}
                        </a>
                    </div>
                </div>
            </div>
            {{-- Vector Configuration Section --}}
            <div style="padding: 24px; background: #f1f5f9; border-top: 1px solid #e2e8f0;">
                <h3 style="font-size: 0.875rem; font-weight: 700; color: #1e293b; margin: 0 0 16px 0; display: flex; align-items: center; gap: 8px; text-transform: uppercase; letter-spacing: 0.025em;">
                    <i class="mdi mdi-vector-intersection" style="color: #a72b2a;"></i>
                    Vector Configuration
                </h3>

                <div style="display: flex; flex-direction: column; gap: 16px;">
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                            <label style="font-size: 0.75rem; font-weight: 600; color: #64748b;">CHUNK SIZE</label>
                            <span id="chunkSizeDisplay" style="font-size: 0.75rem; font-weight: 700; color: #a72b2a;">{{ json_decode(optional($doc)->metadata ?? '{}')->chunk_size ?? 800 }}</span>
                        </div>
                        <input type="range" id="chunkSize" min="200" max="2000" step="50" value="{{ json_decode(optional($doc)->metadata ?? '{}')->chunk_size ?? 800 }}" style="width: 100%; accent-color: #a72b2a;" oninput="document.getElementById('chunkSizeDisplay').innerText = this.value">
                        <p style="font-size: 0.65rem; color: #94a3b8; margin-top: 4px;">Target character count per chunk. Smaller = more precise.</p>
                    </div>

                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                            <label style="font-size: 0.75rem; font-weight: 600; color: #64748b;">CHUNK OVERLAP</label>
                            <span id="chunkOverlapDisplay" style="font-size: 0.75rem; font-weight: 700; color: #a72b2a;">{{ json_decode(optional($doc)->metadata ?? '{}')->chunk_overlap ?? 100 }}</span>
                        </div>
                        <input type="range" id="chunkOverlap" min="0" max="500" step="10" value="{{ json_decode(optional($doc)->metadata ?? '{}')->chunk_overlap ?? 100 }}" style="width: 100%; accent-color: #a72b2a;" oninput="document.getElementById('chunkOverlapDisplay').innerText = this.value">
                        <p style="font-size: 0.65rem; color: #94a3b8; margin-top: 4px;">Context shared between chunks. Prevents semantic gaps.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<input type="hidden" id="docId" value="{{ $doc->id ?? '' }}">

@endsection

@push('scripts')
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
<style>
    .CodeMirror { height: 100% !important; border: none !important; font-size: 16px !important; line-height: 1.6 !important; font-family: 'Inter', system-ui, -apple-system, sans-serif !important; }
    .CodeMirror-scroll { padding: 40px 0 !important; }
    .editor-toolbar { border: none !important; border-bottom: 1px solid #f1f5f9 !important; padding: 12px 32px !important; opacity: 1 !important; position: sticky; top: 0; z-index: 5; background: #ffffffcc; backdrop-filter: blur(8px); display: flex; align-items: center; flex-wrap: wrap; gap: 4px; }
    
    .editor-toolbar a { border: none !important; border-radius: 6px !important; width: 36px !important; height: 36px !important; display: inline-flex !important; align-items: center !important; justify-content: center !important; transition: all 0.2s !important; color: #475569 !important; margin: 0 2px !important; }
    .editor-toolbar a:hover { background: #f1f5f9 !important; color: #a72b2a !important; }
    .editor-toolbar a.active { background: #f1f5f9 !important; color: #a72b2a !important; }
    .editor-toolbar i { font-size: 16px !important; }
    .editor-toolbar .separator { border-left: 1px solid #e2e8f0 !important; margin: 0 8px !important; height: 18px !important; }

    .chunk-result { background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px; margin-bottom: 16px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); transition: transform 0.2s; }
    .chunk-result:hover { transform: translateY(-2px); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
    .score-badge { font-size: 0.7rem; font-weight: 800; padding: 2px 8px; border-radius: 6px; text-transform: uppercase; }
</style>

<script>
    let editor = null;

    window.addEventListener('DOMContentLoaded', () => {
        editor = new SimpleMDE({
            element: document.getElementById("editorContent"),
            spellChecker: false,
            autosave: { enabled: false },
            toolbar: [
                "bold", "italic", "heading", "|", 
                "quote", "unordered-list", "ordered-list", "|", 
                "link", "image", "table", "|", 
                "preview", "side-by-side", "fullscreen", "|", 
                "guide"
            ],
            placeholder: "Start writing your document... Use headings and tables for better AI chunking.",
        });

        // Add Drag & Drop Image Support
        const cm = editor.codemirror;
        cm.on('drop', (editor, e) => {
            const files = e.dataTransfer.files;
            if (files && files.length > 0) {
                const file = files[0];
                if (file.type.startsWith('image/')) {
                    e.preventDefault();
                    uploadEditorImage(file);
                }
            }
        });
    });

    async function uploadEditorImage(file) {
        const formData = new FormData();
        formData.append('image', file);

        const pos = editor.codemirror.getCursor();
        editor.codemirror.replaceRange("![Uploading image...]()", pos);

        try {
            const response = await fetch('{{ route("ai.knowledge.upload-image") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: formData
            });

            const result = await response.json();
            if (result.status === 'ok') {
                const markdown = `![${file.name}](${result.url})`;
                // Find and replace the temporary placeholder
                const content = editor.value();
                const newContent = content.replace("![Uploading image...]()", markdown);
                editor.value(newContent);
            } else {
                alert('Upload failed: ' + result.message);
                const content = editor.value();
                editor.value(content.replace("![Uploading image...]()", ""));
            }
        } catch (err) {
            console.error('Image upload failed:', err);
            alert('An error occurred while uploading the image.');
        }
    }

    async function saveDocument() {
        const id = document.getElementById('docId').value;
        const title = document.getElementById('editorTitle').value;
        const collection = document.getElementById('editorCollection').value;
        const content = editor.value();
        const expiresAt = document.getElementById('expiryDate').value;
        const externalSourceUrl = document.getElementById('externalSourceUrl').value;
        const tagsInput = document.getElementById('metadataTags').value;
        const tags = tagsInput.split(',').map(t => t.trim()).filter(t => t !== "");
        const chunkSize = document.getElementById('chunkSize').value;
        const chunkOverlap = document.getElementById('chunkOverlap').value;

        const data = {
            title: title,
            collection: collection,
            content: content,
            expires_at: expiresAt || null,
            external_source_url: externalSourceUrl || null,
            tags: tags,
            chunk_size: parseInt(chunkSize),
            chunk_overlap: parseInt(chunkOverlap),
            permission: 'General.View'
        };
        
        if (!title || !content) {
            alert('Title and content are required.');
            return;
        }

        const saveBtn = document.getElementById('saveBtn');
        const originalText = saveBtn.innerHTML;
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Saving...';

        try {
            const url = id 
                ? `{{ url('/imara-ai/knowledge') }}/${id}` 
                : `{{ url('/imara-ai/knowledge') }}`;
            const method = id ? 'PUT' : 'POST';

            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ title, collection, content, permission: 'General.View' })
            });

            const result = await response.json();
            if (result.status === 'ok') {
                if (!id && result.data && result.data.id) {
                    window.location.href = `{{ url('/imara-ai/knowledge-editor') }}/${result.data.id}`;
                } else {
                    document.getElementById('saveStatus').innerText = 'All changes saved just now';
                    saveBtn.innerHTML = originalText;
                    saveBtn.disabled = false;
                }
            } else {
                alert('Save failed: ' + result.message);
                saveBtn.innerHTML = originalText;
                saveBtn.disabled = false;
            }
        } catch (err) {
            console.error('Save error:', err);
            alert('Failed to save document.');
            saveBtn.innerHTML = originalText;
            saveBtn.disabled = false;
        }
    }

    async function runSearchPreview() {
        const query = document.getElementById('previewSearch').value;
        if (!query) return;

        const resultsContainer = document.getElementById('previewResults');
        resultsContainer.innerHTML = '<div style="text-align: center; padding: 40px;"><i class="mdi mdi-loading mdi-spin" style="font-size: 2rem; color: #a72b2a;"></i><p style="color: #64748b; font-size: 0.875rem; margin-top: 12px;">Searching vector index...</p></div>';

        try {
            const response = await fetch('{{ route("ai.knowledge.search-preview") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ query })
            });

            const result = await response.json();
            if (result.status === 'ok' && result.results) {
                renderPreviewResults(result.results);
            } else {
                resultsContainer.innerHTML = `<div style="color: #ef4444; text-align: center; padding: 20px;">Retrieval failed: ${result.message || 'Unknown error'}</div>`;
            }
        } catch (err) {
            console.error('Preview error:', err);
            resultsContainer.innerHTML = '<div style="color: #ef4444; text-align: center; padding: 20px;">Search unavailable.</div>';
        }
    }

    function renderPreviewResults(results) {
        const container = document.getElementById('previewResults');
        if (results.length === 0) {
            container.innerHTML = '<div style="text-align: center; color: #94a3b8; padding: 40px;">No matches found for this query.</div>';
            return;
        }

        container.innerHTML = results.map(res => {
            const rawScore = res.score;
            // Best of both: Normalized percentage + Raw score
            const percent = Math.min(Math.max((1 - rawScore) * 100, 0), 100).toFixed(0); 
            const color = percent > 80 ? '#059669' : (percent > 60 ? '#d97706' : '#64748b');
            const bg = percent > 80 ? '#d1fae5' : (percent > 60 ? '#fef3c7' : '#f1f5f9');

            return `
                <div class="chunk-result">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                        <span class="score-badge" style="background: ${bg}; color: ${color};">
                            ${percent}% Relevant
                        </span>
                        <span style="font-size: 0.65rem; color: #94a3b8; font-family: monospace;">
                            Score: ${rawScore.toFixed(4)}
                        </span>
                    </div>
                    <div style="font-size: 0.85rem; color: #334155; line-height: 1.5; background: #f8fafc; padding: 10px; border-radius: 8px;">
                        ${res.content.substring(0, 300)}${res.content.length > 300 ? '...' : ''}
                    </div>
                    <div style="margin-top: 10px; font-size: 0.7rem; color: #94a3b8; display: flex; align-items: center; gap: 4px;">
                        <i class="mdi mdi-file-document-outline"></i>
                        ${res.metadata.title || 'Unknown Source'}
                    </div>
                </div>
            `;
        }).join('');
    }
</script>
@endpush
