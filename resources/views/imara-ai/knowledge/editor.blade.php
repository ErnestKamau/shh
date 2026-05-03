@extends('layouts.ImaraAi.manager')

@section('kb-content')

<div class="modal fade" id="imageUploadModal" tabindex="-1" role="dialog" aria-labelledby="imageUploadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content docs-modal-shell">
            <div class="modal-header docs-modal-header">
                <h5 class="modal-title docs-modal-title" id="imageUploadModalLabel">
                    <i class="fa fa-picture-o docs-modal-title-icon"></i>
                    Insert Image
                </h5>
                <button type="button" class="close docs-modal-close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body docs-modal-body">
                <ul class="nav nav-pills docs-modal-tabs" id="imageModalTabs">
                    <li class="nav-item">
                        <a class="nav-link active" id="tab-upload" data-toggle="pill" href="#pane-upload">
                            <i class="fa fa-upload"></i>
                            Upload File
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tab-url" data-toggle="pill" href="#pane-url">
                            <i class="fa fa-link"></i>
                            Browse URL
                        </a>
                    </li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane fade show active" id="pane-upload">
                        <div id="imgDropZone" class="docs-image-dropzone">
                            <i class="fa fa-cloud-upload docs-image-dropzone-icon"></i>
                            <p class="docs-image-dropzone-title">Drop image here or click to browse</p>
                            <p class="docs-image-dropzone-copy">PNG, JPG, GIF, WEBP up to 10MB</p>
                            <input type="file" id="imgFileInput" accept="image/*" style="display: none;">
                        </div>
                        <div id="imgUploadPreview" class="docs-image-preview" style="display: none;">
                            <img id="imgUploadPreviewImg" src="" alt="Preview" class="docs-image-preview-img">
                            <p id="imgUploadFileName" class="docs-image-preview-name"></p>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="pane-url">
                        <div class="docs-form-block">
                            <label class="docs-form-label">IMAGE URL</label>
                            <input type="url" id="imgUrlInput" placeholder="https://example.com/image.png" class="docs-form-input" oninput="previewUrlImage(this.value)">
                        </div>
                        <div id="imgUrlPreviewWrap" class="docs-image-preview" style="display: none;">
                            <img id="imgUrlPreviewImg" src="" alt="Preview" class="docs-image-preview-img">
                        </div>
                    </div>
                </div>

                <div class="docs-form-block docs-form-block-last">
                    <label class="docs-form-label">ALT TEXT (optional)</label>
                    <input type="text" id="imgAltText" placeholder="Describe this image..." class="docs-form-input">
                </div>
            </div>
            <div class="modal-footer docs-modal-footer">
                <button type="button" class="btn btn-light btn-sm docs-modal-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" id="imgInsertBtn" onclick="insertImageFromModal()" class="btn btn-sm docs-modal-primary">
                    <i class="fa fa-check"></i>
                    Insert Image
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="saveConfirmModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 520px;">
        <div class="modal-content docs-modal-shell">
            <div class="modal-header docs-modal-header">
                <h5 class="modal-title docs-modal-title">
                    <i class="fa fa-save docs-modal-title-icon docs-modal-title-icon-save"></i>
                    Save Document
                </h5>
                <button type="button" class="close docs-modal-close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body docs-modal-body">
                <div class="docs-form-block">
                    <label class="docs-form-label">DOCUMENT TITLE</label>
                    <input type="text" id="saveModalTitle" class="docs-form-input docs-form-input-strong">
                </div>

                <div class="docs-form-block">
                    <label class="docs-form-label">DETECTED DOCUMENT TYPE</label>
                    <div id="saveModalTypeChips" class="docs-type-chip-list"></div>
                </div>

                <div class="docs-save-summary-card">
                    <p class="docs-save-summary-label">COLLECTION</p>
                    <p id="saveModalCollection" class="docs-save-summary-value"></p>
                </div>
            </div>
            <div class="modal-footer docs-modal-footer docs-modal-footer-split">
                <button type="button" onclick="downloadAsMarkdown()" class="btn btn-light btn-sm docs-modal-secondary">
                    <i class="fa fa-download"></i>
                    Download .md
                </button>
                <div class="docs-modal-actions-inline">
                    <button type="button" class="btn btn-light btn-sm docs-modal-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" onclick="confirmSaveFromModal()" class="btn btn-sm docs-modal-primary">
                        <i class="fa fa-save"></i>
                        Save Document
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="docsFullscreenScope">
<div id="templatePickerBackdrop" class="docs-panel-backdrop" onclick="closeTemplatePicker()"></div>
<div id="templatePickerPanel" class="docs-template-panel">
    <div class="docs-panel-header">
        <h3 class="docs-panel-title">
            <i class="fa fa-th-large"></i>
            Document Templates
        </h3>
        <button type="button" class="docs-panel-close" onclick="closeTemplatePicker()">&times;</button>
    </div>
    <div class="docs-panel-body">
        <p class="docs-panel-copy">Select a template to pre-fill the editor. You can edit it freely after loading.</p>
        <div id="templateList" class="docs-template-list"></div>
    </div>
</div>

<div id="semanticDrawerBackdrop" class="docs-panel-backdrop docs-panel-backdrop-light" onclick="closeSemanticDrawer()"></div>
<div id="semanticDrawer" class="docs-semantic-drawer">
    <div class="docs-panel-header docs-panel-header-light">
        <h3 class="docs-panel-title">
            <i class="fa fa-search"></i>
            Knowledge Panel
        </h3>
        <button type="button" class="docs-panel-close" onclick="closeSemanticDrawer()">&times;</button>
    </div>
    <div class="docs-semantic-scroll">
        <div class="docs-sidebar-section">
            <h4 class="docs-sidebar-title">Semantic Preview</h4>
            <div class="docs-search-wrap">
                <i class="fa fa-search docs-search-icon"></i>
                <input type="text" id="previewSearch" placeholder="Test vector retrieval..." class="docs-search-input" onkeydown="if(event.key === 'Enter') runSearchPreview()">
            </div>
            <p class="docs-sidebar-copy">Enter a query to see which chunks the AI retrieves.</p>
            <div id="previewResults" class="docs-preview-results">
                <div class="docs-preview-placeholder">
                    <i class="fa fa-search-plus"></i>
                    <p>Run a test search to verify how your knowledge is organized.</p>
                </div>
            </div>
        </div>

        <div class="docs-sidebar-section docs-sidebar-section-muted">
            <h4 class="docs-sidebar-mini-title">
                <i class="fa fa-shield"></i>
                Data Governance
            </h4>
            <div class="docs-sidebar-stack">
                <div>
                    <label class="docs-sidebar-label">KNOWLEDGE EXPIRY</label>
                    <input type="date" id="expiryDate" value="{{ optional($doc)->expires_at ? date('Y-m-d', strtotime($doc->expires_at)) : '' }}" class="docs-sidebar-input">
                    <p class="docs-sidebar-note">Leave blank for permanent knowledge.</p>
                </div>
                <div>
                    <label class="docs-sidebar-label">METADATA TAGS</label>
                    <input type="text" id="metadataTags" value="{{ implode(', ', json_decode(optional($doc)->metadata ?? '{}')->tags ?? []) }}" placeholder="e.g. policy, compliance, 2024" class="docs-sidebar-input">
                    <p class="docs-sidebar-note">Comma-separated tags for advanced filtering.</p>
                </div>
                <div>
                    <label class="docs-sidebar-label">EXTERNAL SOURCE URL</label>
                    <input type="url" id="externalSourceUrl" value="{{ optional($doc)->external_source_url ?? '' }}" placeholder="https://sharepoint.com/policy..." class="docs-sidebar-input">
                    <p class="docs-sidebar-note">Link to the authoritative original record.</p>
                </div>
                <div class="docs-lineage-card">
                    <label class="docs-lineage-label">Internal ID Lineage</label>
                    @php $lineage = '/imara-ai/knowledge-editor/' . ($doc->id ?? ''); @endphp
                    <a href="{{ $lineage }}" target="_blank" class="docs-lineage-link">
                        <i class="fa fa-link"></i>
                        Internal Reference #{{ $doc->id ?? 'NEW' }}
                    </a>
                </div>
            </div>
        </div>

        <div class="docs-sidebar-section docs-sidebar-section-soft">
            <h4 class="docs-sidebar-mini-title">
                <i class="fa fa-sliders"></i>
                Vector Configuration
            </h4>
            <div class="docs-sidebar-stack">
                <div>
                    <div class="docs-range-head">
                        <label class="docs-sidebar-label docs-sidebar-label-tight">CHUNK SIZE</label>
                        <span id="chunkSizeDisplay" class="docs-range-value">{{ json_decode(optional($doc)->metadata ?? '{}')->chunk_size ?? 800 }}</span>
                    </div>
                    <input type="range" id="chunkSize" min="200" max="2000" step="50" value="{{ json_decode(optional($doc)->metadata ?? '{}')->chunk_size ?? 800 }}" class="docs-range-input" oninput="document.getElementById('chunkSizeDisplay').innerText = this.value">
                    <p class="docs-sidebar-note">Target character count per chunk.</p>
                </div>
                <div>
                    <div class="docs-range-head">
                        <label class="docs-sidebar-label docs-sidebar-label-tight">CHUNK OVERLAP</label>
                        <span id="chunkOverlapDisplay" class="docs-range-value">{{ json_decode(optional($doc)->metadata ?? '{}')->chunk_overlap ?? 100 }}</span>
                    </div>
                    <input type="range" id="chunkOverlap" min="0" max="500" step="10" value="{{ json_decode(optional($doc)->metadata ?? '{}')->chunk_overlap ?? 100 }}" class="docs-range-input" oninput="document.getElementById('chunkOverlapDisplay').innerText = this.value">
                    <p class="docs-sidebar-note">Context shared between chunks.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="docs-editor-page" id="docsEditorPage">
    <div class="docs-topbar">
        <div class="docs-topbar-main">
            <div class="docs-title-wrap">
                <i class="fa fa-file-text-o docs-file-icon"></i>
                <div>
                    <input type="text" id="editorTitle" value="{{ $doc->title ?? '' }}" placeholder="Untitled document" class="docs-title-input">
                    <div class="docs-meta-line">
                        <span id="saveStatus" class="docs-save-status">
                            @if($doc)
                                @php $date = \Carbon\Carbon::parse($doc->updated_at ?? $doc->created_at); @endphp
                                Last edit {{ $date->diffForHumans() }}
                            @else
                                Draft unsaved
                            @endif
                        </span>
                        <span id="viewModeBadge" class="docs-mode-badge">Editing</span>
                        <span id="cursorPosition" class="docs-cursor-pill">Line 1, Col 1</span>
                        <span id="wordCount" class="docs-word-count">0 words</span>
                    </div>
                </div>
            </div>

            <div class="docs-actions">
                <button type="button" onclick="openTemplatePicker()" class="docs-btn docs-btn-ghost docs-edit-only" title="Templates">
                    <i class="fa fa-th-large"></i>
                    Templates
                </button>

                <button type="button" onclick="toggleSemanticDrawer()" class="docs-btn docs-btn-ghost docs-edit-only" id="semanticDrawerToggle" title="Knowledge Panel">
                    <i class="fa fa-search"></i>
                    Knowledge
                </button>

                <button type="button" id="viewToggleBtn" class="docs-btn docs-btn-ghost" onclick="toggleReadingMode()">
                    <i class="fa fa-eye"></i>
                    Reading view
                </button>

                <button type="button" onclick="openSaveModal()" id="saveBtn" class="docs-btn docs-btn-primary docs-edit-only">
                    <i class="fa fa-save"></i>
                    Save
                </button>
            </div>
        </div>

        <div class="docs-menubar">
            <div class="docs-menubar-nav">
                <a href="{{ route('ai.knowledge.manager') }}" class="docs-menubar-icon-link" title="Back to Knowledge Base">
                    <i class="fa fa-arrow-left"></i>
                </a>
                <a href="{{ route('ai.knowledge.manager') }}" class="docs-menubar-icon-link" title="Knowledge Manager">
                    <i class="fa fa-folder-open-o"></i>
                </a>
                <span class="docs-menubar-label">Knowledge Base</span>
            </div>
            <span class="docs-shortcut-tip">Save: Ctrl/Cmd + S &nbsp;|&nbsp; Templates: Alt + T</span>
        </div>
    </div>

    <div class="docs-main-layout" id="docsMainLayout">
        <div class="docs-editor-column" id="docsEditorColumn">
            <div id="editorWrapper" class="docs-editor-wrapper">
                <textarea id="editorContent" style="display: none;">{{ $doc->content ?? '' }}</textarea>
            </div>
        </div>

        <div id="splitDragHandle" class="docs-split-handle" style="display: none;"></div>

        <div id="customSideBySidePane" class="docs-preview-pane" style="display: none;">
            <div id="customSideBySideContent" class="prose-preview"></div>
        </div>
    </div>
</div>
</div>

<input type="hidden" id="docId" value="{{ $doc->id ?? '' }}">

@endsection

@push('scripts')
<link rel="stylesheet" href="{{ asset('assets/css/theme-default/font-awesome.min.css') }}">
<style>
    .docs-editor-page {
        height: calc(100vh - 100px);
        display: flex;
        flex-direction: column;
        background: linear-gradient(180deg, #f3f6fb 0%, #eef2f8 100%);
    }

    .docs-topbar {
        background: #fff;
        border-bottom: 1px solid #e2e8f0;
        z-index: 10;
        flex-shrink: 0;
        box-shadow: 0 1px 0 rgba(15, 23, 42, 0.04);
    }

    .docs-topbar-main {
        padding: 14px 26px 10px 26px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
    }

    .docs-title-wrap {
        display: flex;
        gap: 10px;
        align-items: flex-start;
        min-width: 0;
    }

    .docs-file-icon {
        font-size: 24px;
        color: #a72b2a;
        margin-top: 2px;
    }

    .docs-title-input {
        font-size: 1.1rem;
        font-weight: 600;
        color: #1f2937;
        border: 1px solid transparent;
        outline: none;
        min-width: 260px;
        width: 420px;
        max-width: 48vw;
        padding: 6px 10px;
        border-radius: 8px;
    }

    .docs-title-input:focus {
        border-color: #f5c0bf;
        background: #fff8f8;
    }

    .docs-meta-line {
        margin-top: 3px;
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .docs-save-status,
    .docs-word-count {
        font-size: 0.73rem;
        color: #64748b;
    }

    .docs-cursor-pill {
        font-size: 0.73rem;
        color: #7f1d1d;
        font-weight: 600;
        padding: 2px 8px;
        background: #fff1f2;
        border-radius: 999px;
    }

    .docs-mode-badge {
        font-size: 0.7rem;
        font-weight: 700;
        padding: 2px 8px;
        border-radius: 999px;
        color: #065f46;
        background: #ecfdf5;
    }

    .docs-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .docs-btn {
        border: 1px solid transparent;
        border-radius: 8px;
        padding: 9px 14px;
        font-weight: 600;
        font-size: 0.82rem;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s;
        white-space: nowrap;
    }

    .docs-btn-primary {
        background: #a72b2a;
        color: #fff;
    }

    .docs-btn-primary:hover {
        background: #8e2423;
    }

    .docs-btn-ghost {
        background: #fff;
        border-color: #cbd5e1;
        color: #334155;
    }

    .docs-btn-ghost:hover {
        background: #f8fafc;
    }

    .docs-btn-ghost.active {
        background: #fff8f8;
        border-color: #f5c0bf;
        color: #a72b2a;
    }

    .docs-menubar {
        padding: 4px 26px 8px 26px;
        display: flex;
        align-items: center;
        gap: 12px;
        border-top: 1px solid #f1f5f9;
        background: #f8fafc;
    }

    .docs-menubar-nav {
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .docs-menubar-icon-link {
        width: 30px;
        height: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        border: 1px solid #f5c0bf;
        background: #fff;
        color: #a72b2a;
        text-decoration: none;
        transition: all 0.15s;
    }

    .docs-menubar-icon-link:hover {
        background: #fff1f2;
        color: #8e2423;
        border-color: #e59b99;
    }

    .docs-menubar-label {
        font-size: 0.8rem;
        font-weight: 700;
        color: #7f1d1d;
        margin-left: 2px;
    }

    .docs-shortcut-tip {
        margin-left: auto;
        color: #94a3b8;
        font-size: 0.72rem;
    }

    .docs-main-layout {
        flex: 1;
        display: flex;
        overflow: hidden;
    }

    .docs-main-layout.reading-mode .docs-editor-column {
        border-right: none;
    }

    .docs-main-layout.reading-mode .editor-toolbar {
        display: none !important;
    }

    .docs-main-layout.reading-mode .CodeMirror {
        box-shadow: none;
        max-width: 980px;
    }

    .docs-main-layout.reading-mode .CodeMirror-scroll {
        cursor: default;
    }

    .docs-main-layout.reading-mode ~ .docs-semantic-drawer,
    .docs-main-layout.reading-mode ~ .docs-template-panel {
        display: none;
    }

    .docs-main-layout.reading-mode .docs-edit-only {
        display: none;
    }

    .docs-editor-column {
        flex: 1;
        min-width: 0;
        border-right: 1px solid #e2e8f0;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }

    .docs-editor-wrapper {
        flex: 1;
        overflow-y: auto;
        padding: 14px 28px 44px 28px;
    }

    .docs-preview-pane {
        flex: 1;
        min-width: 320px;
        overflow-y: auto;
        padding: 40px 48px;
        background: #fff;
        border-left: 1px solid #e2e8f0;
    }

    .docs-split-handle {
        width: 6px;
        background: #e2e8f0;
        cursor: col-resize;
        transition: background 0.2s;
        z-index: 5;
    }

    .docs-split-handle:hover {
        background: #a72b2a;
    }

    .CodeMirror {
        border: none !important;
        min-height: calc(100vh - 250px) !important;
        font-size: 16px !important;
        line-height: 1.7 !important;
        font-family: 'Noto Sans', 'Segoe UI', sans-serif !important;
        max-width: 900px;
        margin: 0 auto;
        background: #fff;
        border-radius: 6px;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.08), 0 10px 28px rgba(15, 23, 42, 0.06);
    }

    .CodeMirror-scroll {
        min-height: calc(100vh - 250px) !important;
        padding: 54px 78px !important;
    }

    .editor-toolbar {
        border: 1px solid #dbe4f1 !important;
        border-radius: 10px;
        margin: 10px auto 14px auto;
        padding: 7px 10px !important;
        opacity: 1 !important;
        position: sticky;
        top: 8px;
        z-index: 6;
        max-width: 900px;
        background: #fff;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.07);
    }

    .editor-toolbar a {
        border: none !important;
        border-radius: 6px !important;
        width: 32px !important;
        height: 32px !important;
        color: #475569 !important;
    }

    .editor-toolbar a:hover,
    .editor-toolbar a.active {
        background: #fff1f2 !important;
        color: #a72b2a !important;
    }

    .editor-toolbar .separator {
        border-left: 1px solid #dbe4f1 !important;
        margin: 0 7px !important;
    }

    .editor-toolbar a.fa {
        font-size: 0;
    }

    .editor-toolbar a.fa:before {
        font-size: 18px;
        line-height: 1;
    }

    .prose-preview h1 {
        font-size: 1.8rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0 0 16px 0;
        line-height: 1.3;
    }

    .prose-preview h2 {
        font-size: 1.4rem;
        font-weight: 700;
        color: #1e293b;
        margin: 28px 0 12px 0;
    }

    .prose-preview h3 {
        font-size: 1.1rem;
        font-weight: 700;
        color: #1e293b;
        margin: 20px 0 8px 0;
    }

    .prose-preview p {
        font-size: 1rem;
        color: #334155;
        line-height: 1.7;
        margin: 0 0 14px 0;
    }

    .prose-preview ul,
    .prose-preview ol {
        padding-left: 22px;
        margin: 0 0 14px 0;
        color: #334155;
        line-height: 1.7;
    }

    .prose-preview blockquote {
        border-left: 4px solid #a72b2a;
        margin: 0 0 14px 0;
        padding: 8px 16px;
        background: #fef9f9;
        color: #7f1d1d;
        border-radius: 0 6px 6px 0;
    }

    .prose-preview code {
        background: #f1f5f9;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 0.875rem;
        color: #a72b2a;
        font-family: monospace;
    }

    .prose-preview pre {
        background: #1e293b;
        color: #e2e8f0;
        padding: 16px 20px;
        border-radius: 8px;
        overflow-x: auto;
        margin: 0 0 14px 0;
    }

    .prose-preview pre code {
        background: none;
        color: inherit;
        padding: 0;
    }

    .prose-preview table {
        width: 100%;
        border-collapse: collapse;
        margin: 0 0 14px 0;
        font-size: 0.9rem;
    }

    .prose-preview th {
        background: #f1f5f9;
        font-weight: 700;
        text-align: left;
        padding: 8px 12px;
        border: 1px solid #e2e8f0;
        color: #1e293b;
    }

    .prose-preview td {
        padding: 8px 12px;
        border: 1px solid #e2e8f0;
        color: #334155;
    }

    .prose-preview img {
        max-width: 100%;
        border-radius: 8px;
        margin: 8px 0;
    }

    .prose-preview a {
        color: #a72b2a;
        text-decoration: underline;
    }

    .prose-preview hr {
        border: none;
        border-top: 1px solid #e2e8f0;
        margin: 24px 0;
    }

    .chunk-result {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 14px;
        margin-bottom: 12px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
        transition: transform 0.2s;
    }

    .chunk-result:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.08);
    }

    .score-badge {
        font-size: 0.7rem;
        font-weight: 800;
        padding: 2px 8px;
        border-radius: 6px;
        text-transform: uppercase;
    }

    .docs-modal-shell {
        border-radius: 12px;
        border: none;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
    }

    .docs-modal-header {
        border-bottom: 1px solid #e2e8f0;
        padding: 18px 24px;
    }

    .docs-modal-title {
        font-weight: 700;
        color: #1e293b;
        font-size: 1rem;
    }

    .docs-modal-title-icon {
        color: #a72b2a;
        margin-right: 8px;
    }

    .docs-modal-title-icon-save {
        color: #a72b2a;
    }

    .docs-modal-close {
        color: #64748b;
    }

    .docs-modal-body {
        padding: 24px;
    }

    .docs-modal-footer {
        border-top: 1px solid #e2e8f0;
        padding: 14px 24px;
    }

    .docs-modal-footer-split {
        justify-content: space-between;
    }

    .docs-modal-tabs {
        gap: 6px;
        margin-bottom: 16px;
    }

    .docs-modal-tabs .nav-link {
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 600;
        color: #64748b;
        background: #f1f5f9;
        padding: 8px 14px;
    }

    .docs-modal-tabs .nav-link.active {
        background: #fff1f2;
        color: #a72b2a;
    }

    .docs-modal-tabs .nav-link i {
        margin-right: 6px;
    }

    .docs-modal-primary {
        background: #a72b2a;
        color: #fff;
        border-radius: 8px;
        font-weight: 600;
        padding: 8px 18px;
    }

    .docs-modal-primary i,
    .docs-modal-secondary i {
        margin-right: 6px;
    }

    .docs-modal-secondary {
        border-radius: 8px;
        font-weight: 600;
    }

    .docs-modal-actions-inline {
        display: flex;
        gap: 8px;
    }

    .docs-form-block {
        margin-bottom: 18px;
    }

    .docs-form-block-last {
        margin-bottom: 0;
    }

    .docs-form-label {
        display: block;
        font-size: 0.75rem;
        font-weight: 600;
        color: #64748b;
        margin-bottom: 6px;
    }

    .docs-form-input {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        font-size: 0.875rem;
        outline: none;
    }

    .docs-form-input-strong {
        font-size: 0.95rem;
        font-weight: 600;
        color: #1e293b;
    }

    .docs-image-dropzone {
        border: 2px dashed #cbd5e1;
        border-radius: 10px;
        padding: 40px 20px;
        text-align: center;
        cursor: pointer;
        transition: border-color 0.2s, background 0.2s;
    }

    .docs-image-dropzone.drag-over {
        border-color: #a72b2a;
        background: #fff8f8;
    }

    .docs-image-dropzone-icon {
        font-size: 2rem;
        color: #94a3b8;
        display: block;
        margin-bottom: 10px;
    }

    .docs-image-dropzone-title {
        margin: 0 0 8px 0;
        font-size: 0.9rem;
        color: #475569;
        font-weight: 600;
    }

    .docs-image-dropzone-copy {
        margin: 0;
        font-size: 0.75rem;
        color: #94a3b8;
    }

    .docs-image-preview {
        margin-top: 12px;
        text-align: center;
    }

    .docs-image-preview-img {
        max-width: 100%;
        max-height: 180px;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
    }

    .docs-image-preview-name {
        font-size: 0.75rem;
        color: #64748b;
        margin-top: 6px;
    }

    .docs-save-summary-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 12px 16px;
    }

    .docs-save-summary-label {
        font-size: 0.75rem;
        color: #64748b;
        margin: 0 0 4px 0;
        font-weight: 600;
    }

    .docs-save-summary-value {
        font-size: 0.875rem;
        color: #1e293b;
        margin: 0;
        font-weight: 600;
    }

    .docs-type-chip-list {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .type-chip {
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        cursor: pointer;
        border: 2px solid transparent;
        transition: all 0.15s;
    }

    .type-chip.selected {
        border-color: #0b57d0;
        box-shadow: 0 0 0 2px rgba(11, 87, 208, 0.2);
    }

    .docs-panel-backdrop {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.3);
        z-index: 1040;
    }

    .docs-panel-backdrop-light {
        background: rgba(0, 0, 0, 0.2);
        z-index: 1035;
    }

    .docs-template-panel {
        position: fixed;
        top: 0;
        left: -420px;
        width: 400px;
        height: 100vh;
        background: #fff;
        z-index: 1050;
        box-shadow: 4px 0 24px rgba(0, 0, 0, 0.12);
        transition: left 0.3s ease;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .docs-semantic-drawer {
        position: fixed;
        top: 0;
        right: -460px;
        width: 440px;
        height: 100vh;
        background: #fdfdfd;
        z-index: 1045;
        box-shadow: -4px 0 24px rgba(0, 0, 0, 0.10);
        transition: right 0.3s ease;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .docs-panel-header {
        padding: 20px 24px 16px 24px;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .docs-panel-header-light {
        background: #fff;
    }

    .docs-panel-title {
        margin: 0;
        font-size: 1rem;
        font-weight: 700;
        color: #1e293b;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .docs-panel-title i {
        color: #a72b2a;
    }

    .docs-panel-close {
        background: none;
        border: none;
        font-size: 1.2rem;
        color: #64748b;
        cursor: pointer;
        padding: 4px;
    }

    .docs-panel-body {
        flex: 1;
        overflow-y: auto;
        padding: 16px 24px;
    }

    .docs-panel-copy {
        font-size: 0.8rem;
        color: #64748b;
        margin: 0 0 16px 0;
    }

    .docs-template-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .docs-template-card {
        padding: 14px 16px;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        cursor: pointer;
        display: flex;
        align-items: flex-start;
        gap: 12px;
        transition: all 0.15s;
        background: #fff;
    }

    .docs-template-card:hover {
        border-color: #0b57d0;
        background: #f8fafc;
    }

    .docs-template-icon {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .docs-template-name {
        margin: 0 0 3px 0;
        font-weight: 700;
        font-size: 0.875rem;
        color: #1e293b;
    }

    .docs-template-copy {
        margin: 0;
        font-size: 0.75rem;
        color: #64748b;
    }

    .docs-semantic-scroll {
        flex: 1;
        overflow-y: auto;
    }

    .docs-sidebar-section {
        padding: 20px 24px;
        border-bottom: 1px solid #e2e8f0;
    }

    .docs-sidebar-section-muted {
        background: #f8fafc;
    }

    .docs-sidebar-section-soft {
        background: #f1f5f9;
    }

    .docs-sidebar-title {
        font-size: 0.875rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0 0 12px 0;
    }

    .docs-sidebar-mini-title {
        font-size: 0.75rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0 0 14px 0;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .docs-sidebar-mini-title i {
        color: #a72b2a;
    }

    .docs-search-wrap {
        position: relative;
    }

    .docs-search-icon {
        position: absolute;
        left: 12px;
        top: 10px;
        color: #94a3b8;
    }

    .docs-search-input,
    .docs-sidebar-input {
        width: 100%;
        padding: 10px 12px 10px 36px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        font-size: 0.875rem;
        outline: none;
    }

    .docs-sidebar-input {
        padding: 8px 12px;
    }

    .docs-search-input:focus,
    .docs-sidebar-input:focus {
        border-color: #a72b2a;
    }

    .docs-sidebar-copy {
        font-size: 0.72rem;
        color: #64748b;
        margin: 8px 0 0 0;
    }

    .docs-preview-results {
        margin-top: 16px;
    }

    .docs-preview-placeholder {
        text-align: center;
        color: #94a3b8;
        border: 2px dashed #f1f5f9;
        padding: 24px;
        border-radius: 12px;
    }

    .docs-preview-placeholder i {
        font-size: 1.5rem;
        margin-bottom: 8px;
        display: block;
    }

    .docs-preview-placeholder p {
        margin: 0;
        font-size: 0.8rem;
    }

    .docs-sidebar-stack {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .docs-sidebar-label {
        display: block;
        font-size: 0.72rem;
        font-weight: 600;
        color: #64748b;
        margin-bottom: 5px;
    }

    .docs-sidebar-label-tight {
        margin-bottom: 0;
    }

    .docs-sidebar-note {
        font-size: 0.65rem;
        color: #94a3b8;
        margin-top: 4px;
    }

    .docs-lineage-card {
        padding: 10px 14px;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
    }

    .docs-lineage-label {
        display: block;
        font-size: 0.65rem;
        font-weight: 700;
        color: #a72b2a;
        margin-bottom: 4px;
        text-transform: uppercase;
    }

    .docs-lineage-link {
        font-size: 0.75rem;
        color: #1e293b;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 4px;
        font-weight: 500;
    }

    .docs-range-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 6px;
    }

    .docs-range-value {
        font-size: 0.72rem;
        font-weight: 700;
        color: #a72b2a;
    }

    .docs-range-input {
        width: 100%;
        accent-color: #a72b2a;
    }

    @media (max-width: 1280px) {
        .docs-preview-pane,
        .docs-split-handle {
            display: none !important;
        }
    }

    @media (max-width: 900px) {
        .docs-topbar-main {
            flex-direction: column;
            align-items: flex-start;
        }

        .docs-actions {
            width: 100%;
            justify-content: flex-start;
        }

        .docs-menubar {
            overflow-x: auto;
            white-space: nowrap;
        }

        .docs-title-input {
            max-width: calc(100vw - 80px);
        }

        .CodeMirror-scroll {
            padding: 34px 24px !important;
        }

        .docs-semantic-drawer,
        .docs-template-panel {
            width: min(100vw, 420px);
        }
    }
</style>

<script>
    let editor = null;
    let isDirty = false;
    let isSaving = false;
    let isReadingMode = false;
    let autosaveTimer = null;
    let isSideBySide = false;
    let pendingUploadFile = null;
    let pendingUploadUrl = null;
    let selectedDocType = null;

    const TEMPLATES = [
        {
            name: 'General Ops',
            icon: 'fa-briefcase',
            color: '#0b57d0',
            bg: '#eff6ff',
            description: 'Daily operations, notes, and general reference documents.',
            content: `# General Operations Document\n\n## Overview\n\n_Describe the purpose of this document._\n\n## Key Contacts\n\n| Name | Role | Contact |\n|------|------|---------|\n| | | |\n\n## Procedures\n\n1. Step one\n2. Step two\n3. Step three\n\n## Notes\n\n> Add any important notes or caveats here.\n`
        },
        {
            name: 'Inventory',
            icon: 'fa-cubes',
            color: '#7c3aed',
            bg: '#f5f3ff',
            description: 'Stock levels, SKUs, locations, and inventory policies.',
            content: `# Inventory Reference\n\n## Product Overview\n\n_Describe the product category or inventory segment._\n\n## Stock Details\n\n| SKU | Description | Unit | Current Stock | Reorder Level |\n|-----|-------------|------|---------------|---------------|\n| | | | | |\n\n## Storage Locations\n\n- **Location A:** \n- **Location B:** \n\n## Reorder Policy\n\n_Describe reorder triggers and supplier contacts._\n`
        },
        {
            name: 'SOPs',
            icon: 'fa-list-ol',
            color: '#059669',
            bg: '#f0fdf4',
            description: 'Step-by-step standard operating procedures with approval flows.',
            content: `# Standard Operating Procedure\n\n**Version:** 1.0  \n**Effective Date:** ${new Date().toISOString().split('T')[0]}  \n**Approved By:**  \n\n---\n\n## Purpose\n\n_State the purpose and scope of this SOP._\n\n## Responsibilities\n\n| Role | Responsibility |\n|------|----------------|\n| | |\n\n## Procedure\n\n### Step 1\n\n_Describe step 1 in detail._\n\n### Step 2\n\n_Describe step 2 in detail._\n\n### Step 3\n\n_Describe step 3 in detail._\n\n## Quality Checks\n\n- [ ] Checkpoint 1\n- [ ] Checkpoint 2\n\n## References\n\n_Link to related documents or policies._\n`
        },
        {
            name: 'Product Catalog',
            icon: 'fa-tag',
            color: '#d97706',
            bg: '#fffbeb',
            description: 'Product specifications, pricing, and descriptions.',
            content: `# Product Catalog Entry\n\n## Product Name\n\n_Enter full product name._\n\n## Specifications\n\n| Attribute | Value |\n|-----------|-------|\n| SKU | |\n| Category | |\n| Unit of Measure | |\n| Weight | |\n| Dimensions | |\n\n## Pricing\n\n| Tier | Price |\n|------|-------|\n| Retail | |\n| Wholesale | |\n\n## Description\n\n_Full product description for the knowledge base._\n\n## Images\n\n_Add image references here._\n`
        },
        {
            name: 'General Report',
            icon: 'fa-bar-chart',
            color: '#a72b2a',
            bg: '#fef2f2',
            description: 'Structured report with summary, findings, and recommendations.',
            content: `# Report Title\n\n**Prepared By:**  \n**Date:** ${new Date().toISOString().split('T')[0]}  \n**Period:**  \n\n---\n\n## Executive Summary\n\n_Provide a concise summary of the report findings._\n\n## Background\n\n_Context and background for this report._\n\n## Findings\n\n### Finding 1\n\n_Detail your first finding._\n\n### Finding 2\n\n_Detail your second finding._\n\n## Recommendations\n\n1. Recommendation one\n2. Recommendation two\n3. Recommendation three\n\n## Conclusion\n\n_Closing summary and next steps._\n\n## Appendix\n\n_Supporting data, tables, or references._\n`
        }
    ];

    const DOC_TYPES = [
        { label: 'SOPs', color: '#059669', bg: '#d1fae5', keywords: ['sop', 'procedure', 'step', 'steps', 'checklist', 'process', 'workflow', 'instruction'] },
        { label: 'Inventory', color: '#7c3aed', bg: '#ede9fe', keywords: ['inventory', 'stock', 'sku', 'warehouse', 'reorder', 'quantity', 'unit', 'product', 'catalog'] },
        { label: 'General Ops', color: '#0b57d0', bg: '#dbeafe', keywords: ['operations', 'policy', 'general', 'daily', 'report', 'meeting', 'ops'] },
        { label: 'Product Catalog', color: '#d97706', bg: '#fef3c7', keywords: ['catalog', 'price', 'pricing', 'specification', 'dimension', 'weight', 'retail', 'wholesale'] },
        { label: 'General Report', color: '#a72b2a', bg: '#fee2e2', keywords: ['report', 'summary', 'findings', 'recommendation', 'executive', 'analysis', 'appendix'] }
    ];

    function setSaveStatus(message, tone = 'default') {
        const saveStatus = document.getElementById('saveStatus');
        if (!saveStatus) {
            return;
        }

        saveStatus.innerText = message;
        if (tone === 'saving') saveStatus.style.color = '#b45309';
        if (tone === 'saved') saveStatus.style.color = '#047857';
        if (tone === 'error') saveStatus.style.color = '#b91c1c';
        if (tone === 'default') saveStatus.style.color = '#64748b';
    }

    function updateCursorPosition() {
        if (!editor) {
            return;
        }

        const pos = editor.codemirror.getCursor();
        document.getElementById('cursorPosition').innerText = `Line ${pos.line + 1}, Col ${pos.ch + 1}`;
    }

    function updateWordCount() {
        if (!editor) {
            return;
        }

        const text = editor.value().trim();
        const words = text ? text.split(/\s+/).length : 0;
        document.getElementById('wordCount').innerText = `${words} word${words === 1 ? '' : 's'}`;
    }

    function markDirty() {
        isDirty = true;
        setSaveStatus('Unsaved changes', 'default');
        scheduleAutosave();
    }

    function scheduleAutosave() {
        const id = document.getElementById('docId').value;
        if (!id || isSaving || isReadingMode) {
            return;
        }

        clearTimeout(autosaveTimer);
        autosaveTimer = setTimeout(() => {
            if (isDirty) {
                saveDocument(true);
            }
        }, 2200);
    }

    function toggleReadingMode() {
        isReadingMode = !isReadingMode;

        const mainLayout = document.getElementById('docsMainLayout');
        const toolbar = document.querySelector('.editor-toolbar');
        const toggleBtn = document.getElementById('viewToggleBtn');
        const titleInput = document.getElementById('editorTitle');
        const editOnlyNodes = document.querySelectorAll('.docs-edit-only');
        const modeBadge = document.getElementById('viewModeBadge');

        mainLayout.classList.toggle('reading-mode', isReadingMode);
        if (toolbar) {
            toolbar.style.display = isReadingMode ? 'none' : 'flex';
        }

        editOnlyNodes.forEach((node) => {
            node.style.display = isReadingMode ? 'none' : 'inline-flex';
        });

        editor.codemirror.setOption('readOnly', isReadingMode ? 'nocursor' : false);
        titleInput.readOnly = isReadingMode;
        titleInput.style.pointerEvents = isReadingMode ? 'none' : 'auto';
        if (isReadingMode) {
            closeSemanticDrawer();
            closeTemplatePicker();
            toggleBtn.innerHTML = '<i class="fa fa-pencil"></i> Switch to Editing';
            modeBadge.innerText = 'Reading';
            modeBadge.style.color = '#1e3a8a';
            modeBadge.style.background = '#dbeafe';
            setSaveStatus('Reading mode enabled', 'default');
        } else {
            toggleBtn.innerHTML = '<i class="fa fa-eye"></i> Reading view';
            modeBadge.innerText = 'Editing';
            modeBadge.style.color = '#065f46';
            modeBadge.style.background = '#ecfdf5';
            setSaveStatus(isDirty ? 'Unsaved changes' : 'All changes saved', isDirty ? 'default' : 'saved');
        }

        if (editor && editor.codemirror) {
            setTimeout(() => editor.codemirror.refresh(), 80);
        }
    }

    function toggleFullscreen() {
        const fullscreenScope = document.getElementById('docsFullscreenScope');
        if (!document.fullscreenElement && fullscreenScope && fullscreenScope.requestFullscreen) {
            fullscreenScope.requestFullscreen().then(() => {
                setTimeout(() => editor.codemirror.refresh(), 150);
            }).catch(() => {
                editor.codemirror.refresh();
            });
            return;
        }

        if (document.fullscreenElement && document.exitFullscreen) {
            document.exitFullscreen().finally(() => {
                setTimeout(() => editor.codemirror.refresh(), 150);
            });
        }
    }

    document.addEventListener('fullscreenchange', () => {
        if (editor) {
            setTimeout(() => editor.codemirror.refresh(), 150);
        }
    });

    function toggleCustomSideBySide() {
        isSideBySide = !isSideBySide;
        const pane = document.getElementById('customSideBySidePane');
        const handle = document.getElementById('splitDragHandle');

        if (isSideBySide) {
            pane.style.display = 'block';
            handle.style.display = 'block';
            updateSideBySidePreview();
        } else {
            pane.style.display = 'none';
            handle.style.display = 'none';
        }

        setTimeout(() => editor.codemirror.refresh(), 50);
    }

    function renderMarkdown(content) {
        if (window.SimpleMDE && typeof SimpleMDE.prototype.markdown === 'function') {
            return SimpleMDE.prototype.markdown(content);
        }

        return content;
    }

    function updateSideBySidePreview() {
        if (!isSideBySide || !editor) {
            return;
        }

        document.getElementById('customSideBySideContent').innerHTML = renderMarkdown(editor.value());
    }

    function setupSplitDragHandle() {
        const handle = document.getElementById('splitDragHandle');
        let dragging = false;
        let startX = 0;
        let startWidth = 0;

        handle.addEventListener('mousedown', (event) => {
            dragging = true;
            startX = event.clientX;
            startWidth = document.getElementById('docsEditorColumn').offsetWidth;
            document.body.style.cursor = 'col-resize';
            document.body.style.userSelect = 'none';
        });

        document.addEventListener('mousemove', (event) => {
            if (!dragging) {
                return;
            }

            const column = document.getElementById('docsEditorColumn');
            const delta = event.clientX - startX;
            const nextWidth = Math.max(320, startWidth + delta);
            column.style.flex = 'none';
            column.style.width = `${nextWidth}px`;
        });

        document.addEventListener('mouseup', () => {
            if (!dragging) {
                return;
            }

            dragging = false;
            document.body.style.cursor = '';
            document.body.style.userSelect = '';
            if (editor) {
                editor.codemirror.refresh();
            }
        });
    }

    function openTemplatePicker() {
        const list = document.getElementById('templateList');
        list.innerHTML = TEMPLATES.map((template, index) => `
            <div class="docs-template-card" onclick="selectTemplate(${index})">
                <div class="docs-template-icon" style="background: ${template.bg}; color: ${template.color};">
                    <i class="fa ${template.icon}"></i>
                </div>
                <div>
                    <p class="docs-template-name">${template.name}</p>
                    <p class="docs-template-copy">${template.description}</p>
                </div>
            </div>
        `).join('');

        document.getElementById('templatePickerBackdrop').style.display = 'block';
        document.getElementById('templatePickerPanel').style.left = '0';
    }

    function closeTemplatePicker() {
        document.getElementById('templatePickerPanel').style.left = '-420px';
        setTimeout(() => {
            document.getElementById('templatePickerBackdrop').style.display = 'none';
        }, 300);
    }

    function selectTemplate(index) {
        const template = TEMPLATES[index];
        const currentContent = editor.value().trim();
        if (currentContent && !confirm(`Replace the current content with the "${template.name}" template?`)) {
            return;
        }

        editor.value(template.content);
        markDirty();
        closeTemplatePicker();
        editor.codemirror.focus();
    }

    function toggleSemanticDrawer() {
        const drawer = document.getElementById('semanticDrawer');
        const isOpen = drawer.style.right === '0px';
        if (isOpen) {
            closeSemanticDrawer();
            return;
        }

        openSemanticDrawer();
    }

    function openSemanticDrawer() {
        document.getElementById('semanticDrawer').style.right = '0px';
        document.getElementById('semanticDrawerBackdrop').style.display = 'block';
        document.getElementById('semanticDrawerToggle').classList.add('active');
    }

    function closeSemanticDrawer() {
        document.getElementById('semanticDrawer').style.right = '-460px';
        setTimeout(() => {
            document.getElementById('semanticDrawerBackdrop').style.display = 'none';
        }, 300);
        document.getElementById('semanticDrawerToggle').classList.remove('active');
    }

    function openImageModal() {
        pendingUploadFile = null;
        pendingUploadUrl = null;
        document.getElementById('imgFileInput').value = '';
        document.getElementById('imgAltText').value = '';
        document.getElementById('imgUrlInput').value = '';
        document.getElementById('imgUploadPreview').style.display = 'none';
        document.getElementById('imgUrlPreviewWrap').style.display = 'none';
        $('#imageUploadModal').modal('show');
    }

    function previewUrlImage(url) {
        const wrap = document.getElementById('imgUrlPreviewWrap');
        const img = document.getElementById('imgUrlPreviewImg');
        if (url && /^https?:\/\/.+/i.test(url)) {
            img.src = url;
            wrap.style.display = 'block';
            pendingUploadUrl = url;
            return;
        }

        wrap.style.display = 'none';
        pendingUploadUrl = null;
    }

    function handleImageFileSelected(file) {
        pendingUploadFile = file;
        const reader = new FileReader();
        reader.onload = (event) => {
            document.getElementById('imgUploadPreviewImg').src = event.target.result;
            document.getElementById('imgUploadFileName').innerText = `${file.name} (${(file.size / 1024).toFixed(1)} KB)`;
            document.getElementById('imgUploadPreview').style.display = 'block';
        };
        reader.readAsDataURL(file);
    }

    async function insertImageFromModal() {
        const alt = document.getElementById('imgAltText').value.trim() || 'image';
        const activeTab = document.querySelector('#imageModalTabs .nav-link.active').getAttribute('href');

        if (activeTab === '#pane-upload') {
            if (!pendingUploadFile) {
                alert('Please select a file to upload.');
                return;
            }

            const formData = new FormData();
            formData.append('image', pendingUploadFile);

            const button = document.getElementById('imgInsertBtn');
            button.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Uploading...';
            button.disabled = true;

            try {
                const response = await fetch('{{ route("ai.knowledge.upload-image") }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: formData
                });
                const result = await response.json();
                if (result.status === 'ok') {
                    editor.codemirror.replaceSelection(`![${alt}](${result.url})`);
                    markDirty();
                    $('#imageUploadModal').modal('hide');
                } else {
                    alert('Upload failed: ' + result.message);
                }
            } catch (error) {
                alert('Upload failed. Please try again.');
            } finally {
                button.innerHTML = '<i class="fa fa-check"></i> Insert Image';
                button.disabled = false;
            }
            return;
        }

        if (!pendingUploadUrl) {
            alert('Please enter a valid image URL.');
            return;
        }

        editor.codemirror.replaceSelection(`![${alt}](${pendingUploadUrl})`);
        markDirty();
        $('#imageUploadModal').modal('hide');
    }

    async function uploadEditorImage(file) {
        const formData = new FormData();
        formData.append('image', file);

        const placeholder = '![Uploading image...]()';
        const position = editor.codemirror.getCursor();
        editor.codemirror.replaceRange(placeholder, position);

        try {
            const response = await fetch('{{ route("ai.knowledge.upload-image") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: formData
            });

            const result = await response.json();
            const content = editor.value();
            if (result.status === 'ok') {
                editor.value(content.replace(placeholder, `![${file.name}](${result.url})`));
            } else {
                editor.value(content.replace(placeholder, ''));
                alert('Upload failed: ' + result.message);
            }
        } catch (error) {
            const content = editor.value();
            editor.value(content.replace(placeholder, ''));
            console.error('Image upload failed:', error);
        }
    }

    function detectDocType(content) {
        const lower = content.toLowerCase();
        const scores = DOC_TYPES.map((type) => ({
            ...type,
            score: type.keywords.filter((keyword) => lower.includes(keyword)).length
        }));

        scores.sort((left, right) => right.score - left.score);
        return scores;
    }

    function openSaveModal() {
        const title = document.getElementById('editorTitle').value.trim();
        const content = editor ? editor.value() : '';

        document.getElementById('saveModalTitle').value = title;

        const scores = detectDocType(content);
        selectedDocType = scores[0].score > 0 ? scores[0].label : null;

        document.getElementById('saveModalTypeChips').innerHTML = scores.map((type) => `
            <button
                type="button"
                class="type-chip ${type.label === selectedDocType ? 'selected' : ''}"
                style="background: ${type.bg}; color: ${type.color};"
                onclick="selectDocTypeChip(this, '${type.label}')"
            >
                ${type.label}${type.score > 0 ? ` <span style="opacity: 0.6; font-size: 0.7rem;">(${type.score})</span>` : ''}
            </button>
        `).join('');

        $('#saveConfirmModal').modal('show');
    }

    function selectDocTypeChip(element, type) {
        document.querySelectorAll('.type-chip').forEach((chip) => chip.classList.remove('selected'));
        element.classList.add('selected');
        selectedDocType = type;
    }

    function confirmSaveFromModal() {
        const modalTitle = document.getElementById('saveModalTitle').value.trim();
        if (modalTitle) {
            document.getElementById('editorTitle').value = modalTitle;
        }

        $('#saveConfirmModal').modal('hide');
        saveDocument(false);
    }

    function downloadAsMarkdown() {
        const title = document.getElementById('saveModalTitle').value.trim() || 'document';
        const blob = new Blob([editor ? editor.value() : ''], { type: 'text/markdown;charset=utf-8' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = title.replace(/[^a-z0-9_\- ]/gi, '_') + '.md';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
    }

    async function saveDocument(isAuto = false) {
        if (isSaving) {
            return;
        }

        const id = document.getElementById('docId').value;
        const title = document.getElementById('editorTitle').value.trim();
        const collection = 'General Ops';
        const content = editor.value().trim();
        const expiresAt = document.getElementById('expiryDate').value;
        const externalSourceUrl = document.getElementById('externalSourceUrl').value;
        const tagsInput = document.getElementById('metadataTags').value;
        const tags = tagsInput.split(',').map((tag) => tag.trim()).filter((tag) => tag !== '');
        const chunkSize = document.getElementById('chunkSize').value;
        const chunkOverlap = document.getElementById('chunkOverlap').value;

        if (!title || !content) {
            if (!isAuto) {
                alert('Title and content are required.');
            }
            return;
        }

        const data = {
            title,
            collection,
            content,
            expires_at: expiresAt || null,
            external_source_url: externalSourceUrl || null,
            tags,
            chunk_size: parseInt(chunkSize, 10),
            chunk_overlap: parseInt(chunkOverlap, 10),
            permission: 'general.view'
        };

        const saveBtn = document.getElementById('saveBtn');
        const originalText = saveBtn.innerHTML;
        isSaving = true;

        if (!isAuto) {
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Saving...';
        }
        setSaveStatus('Saving...', 'saving');

        try {
            const url = id
                ? `{{ url('/imara-ai/knowledge') }}/${id}`
                : `{{ url('/imara-ai/knowledge') }}`;
            const method = id ? 'PUT' : 'POST';

            const response = await fetch(url, {
                method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();
            if (result.status === 'ok') {
                isDirty = false;
                if (!isAuto) {
                    window.location.href = '{{ route("ai.knowledge.manager") }}';
                } else {
                    setSaveStatus('All changes saved just now', 'saved');
                }
            } else {
                setSaveStatus('Save failed', 'error');
                if (!isAuto) {
                    alert('Save failed: ' + result.message);
                }
            }
        } catch (error) {
            console.error('Save error:', error);
            setSaveStatus('Save failed. Retry.', 'error');
            if (!isAuto) {
                alert('Failed to save document.');
            }
        } finally {
            isSaving = false;
            saveBtn.disabled = false;
            saveBtn.innerHTML = originalText;
        }
    }

    async function runSearchPreview() {
        const query = document.getElementById('previewSearch').value;
        if (!query) {
            return;
        }

        const resultsContainer = document.getElementById('previewResults');
        resultsContainer.innerHTML = '<div style="text-align:center;padding:30px;"><i class="fa fa-spinner fa-spin" style="font-size:1.8rem;color:#a72b2a;"></i><p style="color:#64748b;font-size:0.875rem;margin-top:10px;">Searching vector index...</p></div>';

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
                resultsContainer.innerHTML = `<div style="color:#ef4444;text-align:center;padding:20px;">Retrieval failed: ${result.message || 'Unknown error'}</div>`;
            }
        } catch (error) {
            console.error('Preview error:', error);
            resultsContainer.innerHTML = '<div style="color:#ef4444;text-align:center;padding:20px;">Search unavailable.</div>';
        }
    }

    function renderPreviewResults(results) {
        const container = document.getElementById('previewResults');
        if (results.length === 0) {
            container.innerHTML = '<div style="text-align:center;color:#94a3b8;padding:30px;">No matches found for this query.</div>';
            return;
        }

        container.innerHTML = results.map((result) => {
            const rawScore = result.score;
            const percent = Math.min(Math.max((1 - rawScore) * 100, 0), 100).toFixed(0);
            const color = percent > 80 ? '#059669' : (percent > 60 ? '#d97706' : '#64748b');
            const bg = percent > 80 ? '#d1fae5' : (percent > 60 ? '#fef3c7' : '#f1f5f9');

            return `
                <div class="chunk-result">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:10px;">
                        <span class="score-badge" style="background:${bg};color:${color};">${percent}% Relevant</span>
                        <span style="font-size:0.65rem;color:#94a3b8;font-family:monospace;">Score: ${rawScore.toFixed(4)}</span>
                    </div>
                    <div style="font-size:0.82rem;color:#334155;line-height:1.5;background:#f8fafc;padding:8px;border-radius:6px;">
                        ${result.content.substring(0, 280)}${result.content.length > 280 ? '...' : ''}
                    </div>
                    <div style="margin-top:8px;font-size:0.7rem;color:#94a3b8;display:flex;align-items:center;gap:4px;">
                        <i class="fa fa-file-text-o"></i>${result.metadata.title || 'Unknown Source'}
                    </div>
                </div>
            `;
        }).join('');
    }

    window.addEventListener('DOMContentLoaded', () => {
        editor = new SimpleMDE({
            element: document.getElementById('editorContent'),
            spellChecker: false,
            autosave: { enabled: false },
            status: false,
            previewRender: (plainText) => renderMarkdown(plainText),
            toolbar: [
                {
                    name: 'undo',
                    action: (instance) => instance.codemirror.execCommand('undo'),
                    className: 'fa fa-undo',
                    title: 'Undo (Ctrl+Z)'
                },
                {
                    name: 'redo',
                    action: (instance) => instance.codemirror.execCommand('redo'),
                    className: 'fa fa-repeat',
                    title: 'Redo (Ctrl+Y)'
                },
                '|',
                { name: 'bold', action: SimpleMDE.toggleBold, className: 'fa fa-bold', title: 'Bold' },
                { name: 'italic', action: SimpleMDE.toggleItalic, className: 'fa fa-italic', title: 'Italic' },
                { name: 'heading', action: SimpleMDE.toggleHeadingSmaller, className: 'fa fa-header', title: 'Heading' },
                '|',
                { name: 'quote', action: SimpleMDE.toggleBlockquote, className: 'fa fa-quote-right', title: 'Quote' },
                { name: 'unordered-list', action: SimpleMDE.toggleUnorderedList, className: 'fa fa-list-ul', title: 'Bulleted List' },
                { name: 'ordered-list', action: SimpleMDE.toggleOrderedList, className: 'fa fa-list-ol', title: 'Numbered List' },
                '|',
                { name: 'link', action: SimpleMDE.drawLink, className: 'fa fa-link', title: 'Insert Link' },
                { name: 'image', action: () => openImageModal(), className: 'fa fa-picture-o', title: 'Insert Image' },
                { name: 'table', action: SimpleMDE.drawTable, className: 'fa fa-table', title: 'Insert Table' },
                '|',
                { name: 'preview', action: SimpleMDE.togglePreview, className: 'fa fa-eye', title: 'Preview' },
                { name: 'side-by-side', action: () => toggleCustomSideBySide(), className: 'fa fa-columns', title: 'Side by Side' },
                { name: 'fullscreen', action: () => toggleFullscreen(), className: 'fa fa-arrows-alt', title: 'Fullscreen' },
                '|',
                { name: 'save', action: () => openSaveModal(), className: 'fa fa-save', title: 'Save' }
            ],
            placeholder: 'Start writing your document. Use headings and structure so retrieval is cleaner and more accurate.'
        });

        const cm = editor.codemirror;

        cm.on('drop', (instance, event) => {
            const files = event.dataTransfer.files;
            if (files && files.length > 0 && files[0].type.startsWith('image/')) {
                event.preventDefault();
                uploadEditorImage(files[0]);
            }
        });

        cm.on('cursorActivity', updateCursorPosition);
        cm.on('change', () => {
            updateCursorPosition();
            updateWordCount();
            markDirty();
            updateSideBySidePreview();
        });

        ['editorTitle', 'expiryDate', 'externalSourceUrl', 'metadataTags', 'chunkSize', 'chunkOverlap'].forEach((id) => {
            const node = document.getElementById(id);
            if (!node) {
                return;
            }

            node.addEventListener('input', markDirty);
            node.addEventListener('change', markDirty);
        });

        const titleInput = document.getElementById('editorTitle');
        titleInput.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
                titleInput.blur();
                saveDocument();
            }
        });
        titleInput.addEventListener('dblclick', () => titleInput.select());

        document.addEventListener('keydown', (event) => {
            if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 's') {
                event.preventDefault();
                if (isReadingMode) {
                    return;
                }
                saveDocument();
            }

            if (event.altKey && event.key.toLowerCase() === 't') {
                event.preventDefault();
                openTemplatePicker();
            }
        });

        const dropZone = document.getElementById('imgDropZone');
        const fileInput = document.getElementById('imgFileInput');

        dropZone.addEventListener('click', () => fileInput.click());
        dropZone.addEventListener('dragover', (event) => {
            event.preventDefault();
            dropZone.classList.add('drag-over');
        });
        dropZone.addEventListener('dragleave', () => dropZone.classList.remove('drag-over'));
        dropZone.addEventListener('drop', (event) => {
            event.preventDefault();
            dropZone.classList.remove('drag-over');
            const file = event.dataTransfer.files[0];
            if (file && file.type.startsWith('image/')) {
                handleImageFileSelected(file);
            }
        });
        fileInput.addEventListener('change', (event) => {
            if (event.target.files[0]) {
                handleImageFileSelected(event.target.files[0]);
            }
        });

        setupSplitDragHandle();
        updateCursorPosition();
        updateWordCount();
    });
</script>
@endpush
