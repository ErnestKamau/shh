<div x-data="investigationTab">
    <style>
        .investigation-summary-card {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: #fff;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .investigation-section-header {
            background: #f8fafc;
            padding: 12px 20px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .investigation-section-content {
            padding: 20px;
        }
        .investigation-label {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 8px;
            display: block;
            letter-spacing: 0.025em;
        }
        .investigation-text {
            font-size: 0.9rem;
            color: #1e293b;
            line-height: 1.6;
            background: #fff;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #f1f5f9;
        }
        .investigation-date-badge {
            font-size: 0.75rem;
            background: #eff6ff;
            color: #2563eb;
            padding: 4px 10px;
            border-radius: 9999px;
            font-weight: 600;
        }
        .modal-tab-content {
            padding: 20px;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-top: none;
            border-radius: 0 0 8px 8px;
            min-height: 300px;
        }
        .tox-tinymce {
            border-radius: 8px !important;
            border: 1px solid #e2e8f0 !important;
        }
    </style>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <span class="mr-2 d-flex align-items-center justify-content-center rounded"
                style="width:32px;height:32px;background:#fefce8;flex-shrink:0;">
                <i class="mdi mdi-briefcase-search-outline" style="font-size:1.1rem;color:#ca8a04;"></i>
            </span>
            <div>
                <small class="font-weight-bold text-dark" style="font-size:0.82rem;">Initial Investigation</small>
                <small class="text-muted d-block" style="font-size:0.67rem;">Immediate investigation details and CAR determination</small>
            </div>
        </div>
        <div class="d-flex align-items-center" style="gap:8px;">
            <button type="button" class="btn btn-outline-primary btn-sm crm-outline-btn-sm" wire:click="generateReport">
                <i class="mdi mdi-file-pdf-outline"></i> Generate Report
            </button>
            @if($complaint->complaint_workflow <= 2)
                <button type="button" class="btn btn-outline-secondary btn-sm crm-outline-btn-sm" wire:click="openInvestigationModal">
                    <i class="mdi mdi-pencil-outline"></i> Edit Investigation
                </button>
            @endif
        </div>
    </div>

    <!-- Summary View -->
    <div class="investigation-summary-card mb-4">
        <div class="investigation-section-header">
            <span class="font-weight-bold text-dark small text-uppercase">Investigation Findings</span>
            @if($resolution && $resolution->updated_at)
                <span class="text-muted tiny">Last updated: {{ $resolution->updated_at->format('d M Y H:i') }}</span>
            @endif
        </div>
        <div class="investigation-section-content">
            <div class="row">
                <div class="col-12 mb-4">
                    <label class="investigation-label">Cause of Complaint / Comments</label>
                    <div class="investigation-text">
                        {!! $cause_of_complaint ? $cause_of_complaint : '<span class="text-muted italic">No findings recorded yet.</span>' !!}
                    </div>
                </div>
                
                <div class="col-md-6 mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="investigation-label mb-0">Immediate Action Taken</label>
                        @if($action_taken_date)
                            <span class="investigation-date-badge">Completed: {{ \Carbon\Carbon::parse($action_taken_date)->format('d-M-Y') }}</span>
                        @endif
                    </div>
                    <div class="investigation-text">
                        {!! $action_taken ? $action_taken : '<span class="text-muted italic">No action recorded.</span>' !!}
                    </div>
                </div>

                <div class="col-md-6 mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="investigation-label mb-0">Corrective Action Taken</label>
                        @if($corrective_action_date)
                            <span class="investigation-date-badge">Target Date: {{ \Carbon\Carbon::parse($corrective_action_date)->format('d-M-Y') }}</span>
                        @endif
                    </div>
                    <div class="investigation-text">
                        {!! $corrective_action_taken ? $corrective_action_taken : '<span class="text-muted italic">No corrective action recorded.</span>' !!}
                    </div>
                </div>
            </div>

            <!-- CAR Status Banner -->
            <div class="mt-2 p-3 rounded d-flex align-items-center {{ $car_required ? 'bg-soft-danger border-left-danger' : 'bg-soft-success border-left-success' }}" style="border: 1px solid rgba(0,0,0,0.05); border-left-width: 4px;">
                <i class="mdi {{ $car_required ? 'mdi-alert-circle text-danger' : 'mdi-check-circle text-success' }} mr-3" style="font-size: 1.5rem;"></i>
                <div>
                    <h6 class="mb-0 {{ $car_required ? 'text-danger' : 'text-success' }} font-weight-bold" style="font-size: 0.9rem;">Corrective Action Request (CAR)</h6>
                    <p class="mb-0 text-muted" style="font-size: 0.8rem;">
                        {{ $car_required ? 'A Corrective Action Request (CAR No: ' . ($resolution->car_no ?? 'Pending') . ') has been initiated.' : 'Investigation concluded that no CAR is required for this issue.' }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    @teleport('body')
    <div wire:ignore.self class="modal fade" id="investigationModal" tabindex="-1" role="dialog" aria-labelledby="investigationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-white border-bottom">
                    <h5 class="modal-title font-weight-bold text-dark" id="investigationModalLabel">
                        <i class="mdi mdi-file-document-edit-outline text-warning mr-2"></i>
                        Investigation Details
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body bg-light-faint p-4">
                    
                    {{-- Tabs Navigation --}}
                    <ul class="nav nav-pills crm-tab-nav mb-0" id="investigationTab" role="tablist">
                        <li class="nav-item mr-2">
                            <a class="nav-link py-2 px-4 {{ $activeModalTab === 'cause' ? 'active' : '' }}" 
                               wire:click.prevent="setActiveModalTab('cause')" 
                               data-toggle="pill" href="#tab-cause" role="tab">
                                <i class="mdi mdi-magnify mr-1"></i> Cause & Comments
                            </a>
                        </li>
                        <li class="nav-item mr-2">
                            <a class="nav-link py-2 px-4 {{ $activeModalTab === 'immediate' ? 'active' : '' }}" 
                               wire:click.prevent="setActiveModalTab('immediate')" 
                               data-toggle="pill" href="#tab-immediate" role="tab">
                                <i class="mdi mdi-flash-outline mr-1"></i> Immediate Action
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link py-2 px-4 {{ $activeModalTab === 'corrective' ? 'active' : '' }}" 
                               wire:click.prevent="setActiveModalTab('corrective')" 
                               data-toggle="pill" href="#tab-corrective" role="tab">
                                <i class="mdi mdi-tools mr-1"></i> Corrective Action
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content modal-tab-content shadow-sm">
                        {{-- Tab 1: Cause --}}
                        <div class="tab-pane fade {{ $activeModalTab === 'cause' ? 'show active' : '' }}" id="tab-cause" role="tabpanel">
                            <div class="p-2">
                                <label class="investigation-label">Cause of Complaint / Comments <span class="text-danger">*</span></label>
                                <div wire:ignore>
                                    <textarea id="cause_editor" class="investigation-editor">{{ $cause_of_complaint }}</textarea>
                                </div>
                                @error('cause_of_complaint') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        {{-- Tab 2: Immediate Action --}}
                        <div class="tab-pane fade {{ $activeModalTab === 'immediate' ? 'show active' : '' }}" id="tab-immediate" role="tabpanel">
                            <div class="p-2">
                                <div class="row">
                                    <div class="col-md-8">
                                        <label class="investigation-label">Immediate Action Taken <span class="text-danger">*</span></label>
                                        <div wire:ignore>
                                            <textarea id="immediate_editor" class="investigation-editor">{{ $action_taken }}</textarea>
                                        </div>
                                        @error('action_taken') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="investigation-label">Completion Date <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control form-control-lg border-2" wire:model="action_taken_date">
                                        @error('action_taken_date') <span class="text-danger small">{{ $message }}</span> @enderror
                                        
                                        <div class="mt-4 p-3 bg-light rounded border">
                                            <small class="text-muted d-block mb-1">Guiding Note:</small>
                                            <small class="text-dark d-block" style="line-height:1.4">Detail the steps taken immediately to contain or mitigate the issue since it was reported.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Tab 3: Corrective Action --}}
                        <div class="tab-pane fade {{ $activeModalTab === 'corrective' ? 'show active' : '' }}" id="tab-corrective" role="tabpanel">
                            <div class="p-2">
                                <div class="row">
                                    <div class="col-md-8">
                                        <label class="investigation-label">Corrective Action Taken <span class="text-danger">*</span></label>
                                        <div wire:ignore>
                                            <textarea id="corrective_editor" class="investigation-editor">{{ $corrective_action_taken }}</textarea>
                                        </div>
                                        @error('corrective_action_taken') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="investigation-label">Completion Date <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control form-control-lg border-2" wire:model="corrective_action_date">
                                        @error('corrective_action_date') <span class="text-danger small">{{ $message }}</span> @enderror

                                        <div class="mt-4 p-3 bg-light rounded border">
                                            <small class="text-muted d-block mb-1">Guiding Note:</small>
                                            <small class="text-dark d-block" style="line-height:1.4">Outline the long-term actions intended to eliminate the root cause and prevent recurrence.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- CAR Requirement Toggle REMOVED - Now handled at Stage 1 Approval --}}

                </div>
                <div class="modal-footer bg-white">
                    <button type="button" class="btn btn-secondary px-4" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary px-5 shadow-sm" x-on:click="saveWithEditors()">
                        <i class="mdi mdi-content-save mr-1"></i> 
                        Submit Investigation
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endteleport

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

        Alpine.data('investigationTab', () => ({
            initTinyMCE() {
                const doInit = function () {
                    if (typeof tinymce === 'undefined') return;
                    
                    const fieldMap = {
                        cause_editor: 'cause_of_complaint',
                        immediate_editor: 'action_taken',
                        corrective_editor: 'corrective_action_taken'
                    };

                    const wireValues = {
                        cause_editor: $wire.get('cause_of_complaint'),
                        immediate_editor: $wire.get('action_taken'),
                        corrective_editor: $wire.get('corrective_action_taken')
                    };

                    tinymce.remove('.investigation-editor');
                    tinymce.init({
                        selector: '.investigation-editor',
                        height: 350,
                        menubar: false,
                        auto_focus: false,
                        plugins: 'lists link',
                        toolbar: 'bold italic underline | bullist numlist | link',
                        branding: false,
                        promotion: false,
                        skin: 'oxide',
                        content_css: 'default',
                        content_style: 'body { font-family: Inter, -apple-system, sans-serif; font-size: 14px; line-height: 1.6; color: #1e293b; padding: 12px; }',
                        setup: function (editor) {
                            editor.on('init', function () {
                                const val = wireValues[editor.id];
                                if (val) editor.setContent(val);
                            });
                            editor.on('change keyup input', function () {
                                const fid = editor.id;
                                if (fieldMap[fid]) $wire.set(fieldMap[fid], editor.getContent());
                            });
                        }
                    });

                    // Refresh TinyMCE layout when tab changes
                    $('a[data-toggle="pill"]').off('shown.bs.tab.tinymce').on('shown.bs.tab.tinymce', function (e) {
                        const targetPane = $(e.target).attr('href');
                        $(targetPane).find('.investigation-editor').each(function () {
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
                    tinymce.remove('.investigation-editor');
                }
                $('a[data-toggle="pill"]').off('shown.bs.tab.tinymce');
            },
            saveWithEditors() {
                if (typeof tinymce !== 'undefined') {
                    tinymce.triggerSave();
                    const editors = ['cause_editor', 'immediate_editor', 'corrective_editor'];
                    const fields = ['cause_of_complaint', 'action_taken', 'corrective_action_taken'];
                    editors.forEach((id, i) => {
                        const ed = tinymce.get(id);
                        if (ed) $wire.set(fields[i], ed.getContent());
                    });
                }
                $wire.saveInvestigation();
            },
            init() {
                Livewire.on("show-investigation-modal", () => {
                    $("#investigationModal").modal("show");
                    $("#investigationModal").one("shown.bs.modal", () => {
                        this.initTinyMCE();
                        // Bootstrap pill reset to first
                        $('#investigationTab a[href="#tab-cause"]').tab('show');
                    });
                });

                Livewire.on("close-investigation-modal", () => {
                    $("#investigationModal").modal("hide");
                });

                $("#investigationModal").on("hidden.bs.modal", () => {
                    this.destroyTinyMCE();
                });
            }
        }));
    </script>
    @endscript
</div>
