<div x-data="resolutionTab">
    <style>
        .select2-container { z-index: 100000 !important; }
        .select2-dropdown { z-index: 100000 !important; }

        /* Tab content area */
        .resolution-tab-content {
            border: var(--crm-border);
            border-top: none;
            border-radius: 0 0 var(--crm-radius-md) var(--crm-radius-md);
            padding: var(--crm-space-5) var(--crm-space-4) var(--crm-space-4);
            background: #fff;
            min-height: 240px;
            box-shadow: var(--crm-shadow-card);
        }

        /* Hint text in tab panes */
        .resolution-tab-content .tab-pane > p.text-muted {
            margin-top: 0;
            margin-bottom: 1rem;
            padding: var(--crm-space-3) var(--crm-space-4);
            background: var(--crm-neutral-50);
            border-left: 3px solid var(--crm-primary);
            border-radius: 0 var(--crm-radius-sm) var(--crm-radius-sm) 0;
            line-height: 1.5;
        }

        .resolution-editor-label {
            font-size: var(--crm-font-section);
            font-weight: 600;
            color: var(--crm-neutral-900);
            margin-bottom: 0.75rem;
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
            gap: var(--crm-space-2);
        }

        /* TinyMCE glow */
        .resolution-editor-wrap .tox-tinymce {
            border-radius: var(--crm-radius-sm);
            border: var(--crm-border) !important;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .resolution-editor-wrap .tox-tinymce:focus-within {
            border-color: var(--crm-primary) !important;
            box-shadow: var(--crm-shadow-focus) !important;
        }

        /* Officer field */
        .resolution-officer-wrap {
            background: #fff;
            border: var(--crm-border);
            border-radius: var(--crm-radius-md) var(--crm-radius-md) 0 0;
            padding: var(--crm-space-4) var(--crm-space-5);
            margin-bottom: 0;
            border-bottom: none;
        }
        .resolution-officer-wrap label {
            font-size: var(--crm-font-muted);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--crm-neutral-600);
            margin-bottom: var(--crm-space-2);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* Resolution modal overrides (align with crm.css) */
        #resolutionModal .modal-body {
            background: var(--crm-neutral-50) !important;
            padding: var(--crm-space-5) var(--crm-space-6) var(--crm-space-3) !important;
        }

        /* Validation errors */
        .tab-pane .text-danger.small {
            display: block;
            margin-top: 4px;
        }

        /* Resolution meta bar */
        .resolution-meta-bar {
            background: var(--crm-primary-light);
            border-left: 4px solid var(--crm-primary);
            padding: var(--crm-space-3) var(--crm-space-4);
            border-radius: var(--crm-radius-md);
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--crm-space-5);
        }

        .res-label {
            text-transform: uppercase;
            font-size: var(--crm-font-muted);
            font-weight: 700;
            color: var(--crm-neutral-500);
            margin-bottom: var(--crm-space-1);
            display: block;
            letter-spacing: 0.05em;
        }

        .res-value {
            font-size: var(--crm-font-body);
            font-weight: 500;
            color: var(--crm-neutral-800);
            word-break: break-word;
        }

        .res-section-title {
            font-size: var(--crm-font-body);
            font-weight: 600;
            color: var(--crm-neutral-600);
            border-bottom: var(--crm-border);
            padding-bottom: var(--crm-space-2);
            margin-bottom: var(--crm-space-3);
            display: flex;
            align-items: center;
            gap: var(--crm-space-2);
        }
        .res-section-title i {
            font-size: 1.1rem;
            color: var(--crm-primary);
        }

        /* Read-only doc text boxes */
        .res-text-box {
            background: var(--crm-neutral-50);
            border: var(--crm-border);
            border-radius: var(--crm-radius-md);
            padding: var(--crm-space-3) var(--crm-space-4);
            min-height: 60px;
            margin-bottom: var(--crm-space-5);
        }
        .res-text-box.accent-green { border-left: 4px solid var(--crm-success); }
        .res-text-box.accent-blue { border-left: 4px solid var(--crm-primary); }

        /* Person card */
        .res-person-card {
            display: flex;
            align-items: center;
            gap: var(--crm-space-3);
            padding: var(--crm-space-3);
            background: var(--crm-neutral-50);
            border-radius: var(--crm-radius-md);
            border: var(--crm-border);
            margin-bottom: var(--crm-space-5);
        }
        .res-avatar-icon {
            width: 36px;
            height: 36px;
            background: var(--crm-primary-light);
            color: var(--crm-primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }
        .car-number {
            font-family: 'Courier New', Courier, monospace;
            font-weight: 700;
            color: var(--crm-primary);
        }

        /* Prevent Bootstrap overflow trapping modal backdrops */
        .tab-content { overflow: visible !important; }
    </style>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="d-flex align-items-center">
            <span class="mr-2 d-flex align-items-center justify-content-center rounded"
                style="width:32px;height:32px;background:#f0fdf4;flex-shrink:0;">
                <i class="mdi mdi-check-decagram" style="font-size:1.1rem;color:#16a34a;"></i>
            </span>
            <div>
                <small class="font-weight-bold text-dark" style="font-size:0.82rem;">Resolution Record</small>
                <small class="text-muted d-block" style="font-size:0.67rem;">Findings, root-cause analysis &amp;
                    remediation plan</small>
            </div>
        </div>
        <div class="d-flex align-items-center" style="gap:8px;">
            <button type="button" class="btn btn-outline-success btn-sm text-nowrap" wire:click="exportToExcel">
                <i class="mdi mdi-microsoft-excel"></i> Export to Excel
            </button>
            @if($complaint->complaint_workflow == 2)
                <button type="button" class="btn btn-add btn-sm" wire:click="openResolutionModal">
                    <i class="mdi mdi-plus"></i> Create Resolution
                </button>
            @endif
        </div>
    </div>

    <div wire:loading wire:target="openResolutionModal,viewResolution,saveResolution" class="crm-loading-indicator">
        <i class="mdi mdi-loading mdi-spin"></i> Loading...
    </div>
    <x-crm.data-table class="crm-loading-overlay" wire:loading.class="opacity-50">
        <x-slot:header>
            <tr>
                <th>CAR No</th>
                <th>Officer Responsible</th>
                <th>Registered By</th>
                <th>Date</th>
                <th>Status</th>
                <th style="min-width:120px;">Actions</th>
            </tr>
        </x-slot:header>
                    @forelse($resolutions as $index => $res)
                        <tr wire:key="resolution-{{ $res->id }}">
                            <td>{{ $res->car_no }}</td>
                            <td>{{ $res->officer_responsible }}</td>
                            <td>{{ $res->registered_by }}</td>
                            <td>{{ $res->created_at->format('Y-m-d H:i') }}</td>
                            <td>
                                <span class="crm-badge crm-badge-success">Active</span>
                            </td>
                            <td nowrap>
                                <x-crm.action-buttons>
                                    <button class="btn crm-btn crm-btn-view btn-sm"
                                        wire:click="viewResolution({{ $res->id }})" title="View Resolution">
                                        <i class="mdi mdi-eye-outline"></i>
                                    </button>
                                    <button class="btn crm-btn crm-btn-edit btn-sm"
                                        wire:click="openResolutionModal({{ $res->id }})" title="Edit Resolution">
                                        <i class="mdi mdi-pencil-outline"></i>
                                    </button>
                                </x-crm.action-buttons>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-crm.empty-state
                                    icon="mdi-clipboard-check-outline"
                                    message="No resolutions recorded"
                                    help="A Resolution Record will appear here once the complaint reaches the resolution stage."
                                />
                            </td>
                        </tr>
                    @endforelse
    </x-crm.data-table>
    @teleport('body')
    <div wire:ignore.self class="modal fade" id="resolutionModal" tabindex="-1" role="dialog"
        aria-labelledby="resolutionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content border-0 shadow">
                <div class="modal-header">
                    <h5 class="modal-title" id="resolutionModalLabel">
                        <i class="mdi mdi-file-document-edit-outline text-primary"></i>
                        {{ $editingResolutionId ? 'Edit Resolution' : 'Create Resolution' }}
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">

                    {{-- Unified Content Area --}}
                    <div class="resolution-officer-wrap">
                        <label><i class="mdi mdi-account-tie mr-1"></i> Officer Responsible <span
                                class="text-danger">*</span></label>
                        <div wire:ignore>
                            <select class="form-control no-select2" id="officer_responsible_select" size="1" multiple
                                required>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @error('resolved_by_user_id') <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Tabs — clear separation from officer section above --}}
                    <ul class="crm-tab-nav"
                        style="padding: 0 16px; margin-top: 8px; margin-bottom: 0;"
                        role="tablist">
                        <li class="crm-tab-item">
                            <a class="crm-tab-link {{ $activeTab === 'findings' ? 'active' : '' }}"
                                wire:click.prevent="setActiveTab('findings')" data-toggle="tab" href="#tab-findings"
                                role="tab">
                                <i class="mdi mdi-magnify"></i> Findings
                            </a>
                        </li>
                        <li class="crm-tab-item">
                            <a class="crm-tab-link {{ $activeTab === 'root-cause' ? 'active' : '' }}"
                                wire:click.prevent="setActiveTab('root-cause')" data-toggle="tab" href="#tab-root-cause"
                                role="tab">
                                <i class="mdi mdi-chart-tree"></i> Root Cause Analysis
                            </a>
                        </li>
                        <li class="crm-tab-item">
                            <a class="crm-tab-link {{ $activeTab === 'corrective' ? 'active' : '' }}"
                                wire:click.prevent="setActiveTab('corrective')" data-toggle="tab" href="#tab-corrective"
                                role="tab">
                                <i class="mdi mdi-tools"></i> Corrective Action
                            </a>
                        </li>
                        <li class="crm-tab-item">
                            <a class="crm-tab-link {{ $activeTab === 'preventive' ? 'active' : '' }}"
                                wire:click.prevent="setActiveTab('preventive')" data-toggle="tab" href="#tab-preventive"
                                role="tab">
                                <i class="mdi mdi-shield-check"></i> Preventive Action
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content resolution-tab-content">
                        <div class="tab-pane fade {{ $activeTab === 'findings' ? 'show active' : '' }}"
                            id="tab-findings" role="tabpanel">
                            <p class="text-muted mb-3" style="font-size: 0.82rem; line-height: 1.4;">
                                <i class="mdi mdi-information-outline mr-1 text-primary"></i>
                                Summarize the key observations and evidence gathered during the investigation.
                            </p>
                            <div class="resolution-editor-wrap" wire:ignore>
                                <textarea id="findings_editor" class="resolution-editor">{{ $findings }}</textarea>
                            </div>
                            @error('findings') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="tab-pane fade {{ $activeTab === 'root-cause' ? 'show active' : '' }}"
                            id="tab-root-cause" role="tabpanel">
                            <p class="text-muted mb-3" style="font-size: 0.82rem; line-height: 1.4;">
                                <i class="mdi mdi-information-outline mr-1 text-primary"></i>
                                Determine the fundamental reason why the issue occurred to ensure it doesn't happen
                                again.
                            </p>
                            <div class="resolution-editor-wrap" wire:ignore>
                                <textarea id="root_cause_editor"
                                    class="resolution-editor">{{ $root_cause_analysis }}</textarea>
                            </div>
                            @error('root_cause_analysis') <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="tab-pane fade {{ $activeTab === 'corrective' ? 'show active' : '' }}"
                            id="tab-corrective" role="tabpanel">
                            <p class="text-muted mb-3" style="font-size: 0.82rem; line-height: 1.4;">
                                <i class="mdi mdi-information-outline mr-1 text-primary"></i>
                                Detail the specific actions taken to address the complaint and provide a final solution.
                            </p>
                            <div class="resolution-editor-wrap" wire:ignore>
                                <textarea id="corrective_editor"
                                    class="resolution-editor">{{ $corrective_action }}</textarea>
                            </div>
                            @error('corrective_action') <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="tab-pane fade {{ $activeTab === 'preventive' ? 'show active' : '' }}"
                            id="tab-preventive" role="tabpanel">
                            <p class="text-muted mb-3" style="font-size: 0.82rem; line-height: 1.4;">
                                <i class="mdi mdi-information-outline mr-1 text-primary"></i>
                                Outline long-term measures and process updates to prevent future occurrences.
                            </p>
                            <div class="resolution-editor-wrap" wire:ignore>
                                <textarea id="preventive_editor"
                                    class="resolution-editor">{{ $preventive_action }}</textarea>
                            </div>
                            @error('preventive_action') <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="mdi mdi-close mr-1"></i> Close
                    </button>
                    <button type="button" class="btn btn-primary ml-2 px-4" x-on:click="saveResolutionWithEditors()">
                        <i class="mdi mdi-content-save mr-1"></i> {{ $editingResolutionId ? 'Update' : 'Save' }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    @endteleport

    <!-- View Resolution Modal -->
    @teleport('body')
    <div wire:ignore.self class="modal fade" id="viewResolutionModal" tabindex="-1" role="dialog"
        aria-labelledby="viewResolutionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header resolution-modal-header">
                    <h5 class="modal-title d-flex align-items-center" id="viewResolutionModalLabel">
                        <i class="mdi mdi-file-check text-primary mr-2" style="font-size: 1.5rem;"></i>
                        View Full Resolution Details
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-5">
                    @if($selectedResolution)
                        <!-- Zone A: Metadata Header -->
                        <div class="resolution-meta-bar">
                            <div>
                                <span class="res-label">CAR Number</span>
                                <span class="res-value car-number">{{ $selectedResolution->car_no }}</span>
                            </div>
                            <div class="text-right">
                                <span class="res-label">Registered Date</span>
                                <span class="res-value">
                                    <i class="mdi mdi-calendar-clock mr-1 text-primary"></i>
                                    {{ $selectedResolution->created_at->format('Y-m-d H:i') }}
                                </span>
                            </div>
                        </div>

                        <!-- Zone B: Personnel Information -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="res-person-card">
                                    <div class="res-avatar-icon">
                                        <i class="mdi mdi-account-tie"></i>
                                    </div>
                                    <div>
                                        <span class="res-label">Officer Responsible</span>
                                        <span class="res-value">{{ $selectedResolution->officer_responsible }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="res-person-card">
                                    <div class="res-avatar-icon">
                                        <i class="mdi mdi-account-edit"></i>
                                    </div>
                                    <div>
                                        <span class="res-label">Registered By</span>
                                        <span class="res-value">{{ $selectedResolution->registered_by }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Zone C: Analysis Section -->
                        <div class="row mt-2">
                            <div class="col-12">
                                <h6 class="res-section-title">
                                    <i class="mdi mdi-magnify"></i> Findings
                                </h6>
                                <div class="res-text-box">
                                    <span class="res-value">
                                        @if($selectedResolution->findings || ($selectedResolution->action_taken && $selectedResolution->action_taken !== 'See detailed resolution fields.'))
                                            @php
                                                $findingsContent = $selectedResolution->findings ?: $selectedResolution->action_taken;
                                                $hasHtml = str_contains($findingsContent, '<');
                                            @endphp
                                            {!! $hasHtml ? \App\Livewire\Crm\Complaint\Tabs\ComplaintResolutionTab::sanitizeResolutionHtml($findingsContent) : nl2br(e($findingsContent)) !!}
                                        @else
                                            <span class="text-muted font-italic">No findings recorded</span>
                                        @endif
                                    </span>
                                </div>
                            </div>
                            <div class="col-12">
                                <h6 class="res-section-title">
                                    <i class="mdi mdi-chart-tree"></i> Root Cause Analysis
                                </h6>
                                <div class="res-text-box">
                                    <span class="res-value">
                                        @if($selectedResolution->root_cause_analysis)
                                            @php
                                                $rca = $selectedResolution->root_cause_analysis;
                                                $rcaHasHtml = str_contains($rca, '<');
                                            @endphp
                                            {!! $rcaHasHtml ? \App\Livewire\Crm\Complaint\Tabs\ComplaintResolutionTab::sanitizeResolutionHtml($rca) : nl2br(e($rca)) !!}
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Zone D: Action Plan -->
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="res-section-title">
                                    <i class="mdi mdi-tools"></i> Resolution
                                </h6>
                                <div class="res-text-box accent-green">
                                    <span class="res-value">
                                        @if($selectedResolution->corrective_action_taken)
                                            @php
                                                $ca = $selectedResolution->corrective_action_taken;
                                                $caHasHtml = str_contains($ca, '<');
                                            @endphp
                                            {!! $caHasHtml ? \App\Livewire\Crm\Complaint\Tabs\ComplaintResolutionTab::sanitizeResolutionHtml($ca) : nl2br(e($ca)) !!}
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="res-section-title">
                                    <i class="mdi mdi-shield-check"></i> Preventive Action
                                </h6>
                                <div class="res-text-box accent-blue">
                                    <span class="res-value">
                                        @if($selectedResolution->preventive_action)
                                            @php
                                                $pa = $selectedResolution->preventive_action;
                                                $paHasHtml = str_contains($pa, '<');
                                            @endphp
                                            {!! $paHasHtml ? \App\Livewire\Crm\Complaint\Tabs\ComplaintResolutionTab::sanitizeResolutionHtml($pa) : nl2br(e($pa)) !!}
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="mdi mdi-loading mdi-spin text-primary" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-2">Fetching resolution data...</p>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                </div>
            </div>
        </div>
    </div>
    @endteleport

    {{-- Scripts and Alpine Component --}}
    @script
    <script>
        (function () {
            if (typeof tinymce === 'undefined') {
                var s = document.createElement('script');
                s.src = '/tinymce/tinymce.min.js';
                s.onload = function () {
                    window.__tinyMCEReady = true;
                    document.dispatchEvent(new Event('tinymce:loaded'));
                };
                document.head.appendChild(s);
            } else {
                window.__tinyMCEReady = true;
            }
        })();

        Alpine.data('resolutionTab', () => ({
            initSelect2() {
                window.initSelect2('#officer_responsible_select', $wire, 'resolved_by_user_id', 'Select Officer(s)', '#resolutionModal', { multiple: true, closeOnSelect: false });
            },
            initTinyMCE() {
                const doInit = function () {
                    if (typeof tinymce === 'undefined') return;
                    const fieldMap = {
                        findings_editor: 'findings',
                        root_cause_editor: 'root_cause_analysis',
                        corrective_editor: 'corrective_action',
                        preventive_editor: 'preventive_action'
                    };
                    const wireValues = {
                        findings_editor: $wire.get('findings'),
                        root_cause_editor: $wire.get('root_cause_analysis'),
                        corrective_editor: $wire.get('corrective_action'),
                        preventive_editor: $wire.get('preventive_action')
                    };
                    tinymce.remove('.resolution-editor');
                    tinymce.init({
                        selector: '.resolution-editor',
                        height: 400,
                        menubar: false,
                        auto_focus: false,
                        plugins: 'lists link',
                        toolbar: 'bold italic underline | bullist numlist | link',
                        branding: false,
                        promotion: false,
                        skin: 'oxide',
                        content_css: 'default',
                        content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; font-size: 14px; line-height: 1.6; color: #2d3748; padding: 8px; }',
                        setup: function (editor) {
                            editor.on('init', function () {
                                const val = wireValues[editor.id];
                                if (val) editor.setContent(val);
                            });
                            editor.on('blur', function () {
                                const fid = editor.id;
                                if (fieldMap[fid]) $wire.set(fieldMap[fid], editor.getContent());
                            });
                        }
                    });

                    // Refresh TinyMCE layout when a hidden tab becomes visible
                    $('a[data-toggle="tab"]').off('shown.bs.tab.tinymce').on('shown.bs.tab.tinymce', function (e) {
                        const targetPane = $(e.target).attr('href');
                        const paneEl = $(targetPane);
                        paneEl.find('.resolution-editor').each(function () {
                            const ed = tinymce.get(this.id);
                            if (ed) ed.execCommand('mceResize');
                        });
                    });
                };
                if (typeof tinymce !== 'undefined') {
                    doInit();
                } else {
                    document.addEventListener('tinymce:loaded', doInit, { once: true });
                }
            },
            destroyTinyMCE() {
                if (typeof tinymce !== 'undefined') {
                    tinymce.remove('.resolution-editor');
                }
                $('a[data-toggle="tab"]').off('shown.bs.tab.tinymce');
            },
            saveResolutionWithEditors() {
                if (typeof tinymce !== 'undefined') {
                    tinymce.triggerSave();
                    const editors = ['findings_editor', 'root_cause_editor', 'corrective_editor', 'preventive_editor'];
                    const fields = ['findings', 'root_cause_analysis', 'corrective_action', 'preventive_action'];
                    editors.forEach((id, i) => {
                        const ed = tinymce.get(id);
                        if (ed) $wire.set(fields[i], ed.getContent());
                    });
                }
                $wire.saveResolution();
            },
            init() {
                Livewire.on("show-resolution-modal", (data) => {
                    $("#resolutionModal").modal("show");
                    $("#resolutionModal").one("shown.bs.modal", () => {
                        // Reset to Findings tab on every open
                        $('a[href="#tab-findings"]').tab("show");
                        this.initSelect2();
                        this.initTinyMCE();

                        let officer = data.officer || (data[0] ? data[0].officer : null);
                        setTimeout(() => {
                            if (officer) {
                                let values = Array.isArray(officer) ? officer : [officer];
                                $("#officer_responsible_select").val(values).trigger("change");
                            } else {
                                $("#officer_responsible_select").val(null).trigger("change");
                            }
                        }, 50);
                    });
                });

                Livewire.on("close-resolution-modal", () => {
                    $("#resolutionModal").modal("hide");
                    Livewire.dispatch("complaint-workflow-updated");
                });

                Livewire.on("show-view-resolution-modal", () => {
                    $("#viewResolutionModal").modal("show");
                });

                $("#resolutionModal").on("hidden.bs.modal", () => {
                    this.destroyTinyMCE();
                });
            }
        }));
    </script>
    @endscript
</div>