<div x-data="ncTab()">
    <script src="{{ asset('tinymce/tinymce.min.js') }}"></script>

    <style>
        .capa-editor { opacity: 0; transition: opacity 0.2s ease-in; min-height: 150px; }
        .tox-tinymce { border-radius: var(--crm-radius-md) !important; border-color: var(--crm-neutral-200) !important; }
        .nc-badge-active { background: var(--crm-primary); color: #fff; }
        .nc-card {
            border: 1px solid var(--crm-neutral-200);
            border-radius: var(--crm-radius-lg);
            background: #fff;
            overflow: hidden;
            box-shadow: var(--crm-shadow-card);
        }
    </style>

    <div class="lab-ledger-container">
        {{-- Context Bar / Header --}}
        <div class="capa-context-bar mb-4 shadow-sm border p-3 bg-white" style="border-radius: var(--crm-radius-lg); display: flex; justify-content: space-between; align-items: center;">
            <div>
                <div class="capa-context-breadcrumb mb-1" style="font-size: 0.9rem; color: var(--crm-neutral-500);">
                    <span style="font-weight: 500;">Non-Conformance Investigation</span>
                    <span class="crm-badge crm-badge-primary ml-2">{{ $resolution->car_no ?? 'Pending' }}</span>
                </div>
                <div class="capa-context-meta d-flex align-items-center" style="font-size: 0.85rem; color: var(--crm-neutral-600);">
                    <span>Status:
                        <span class="crm-badge crm-badge-primary ml-1" style="font-size: 0.7rem; text-transform: uppercase; font-weight: 700;">
                            {{ $this->next_pending_action }}
                        </span>
                    </span>
                    <span class="mx-2 text-muted">&middot;</span>
                    <span class="meta-item d-flex align-items-center">
                        <i class="mdi mdi-calendar-range-outline mr-1"></i>
                        {{ $resolution->created_at ? $resolution->created_at->format('d M Y') : now()->format('d M Y') }}
                    </span>
                </div>
            </div>
        </div>

        {{-- WHY-WHY ANALYSIS (RCA) --}}
        <div class="lab-ledger-card {{ $isEditing ? 'capa-editing-active' : '' }}">
            <div class="lab-ledger-header crm-glass d-flex justify-content-between align-items-center">
                <span class="font-weight-bold text-dark d-flex align-items-center">
                    WHY-WHY ANALYSIS (RCA)
                </span>
                <div class="d-flex align-items-center" style="gap: 12px;">
                    @if($isEditing)
                        <div x-show="lastSaved" class="animate__animated animate__fadeIn" style="display: none;">
                            <span class="crm-badge crm-badge-success">
                                <i class="mdi mdi-check-all mr-1"></i> Autosaved <span x-text="lastSaved"></span>
                            </span>
                        </div>
                    @endif
                </div>
            </div>
            
            <div class="lab-ledger-body p-4">
                @if($isEditing)
                    <div wire:key="nc-edit-mode">
                        <div class="col-md-12 mb-4 px-0" wire:key="problem-statement-editor-container">
                            <label class="investigation-label font-weight-bold d-block mb-2">Problem Statement <span class="text-danger">*</span></label>
                            <div wire:ignore x-init="initTinyMCE()">
                                <textarea id="problem_statement_editor" class="capa-editor">{{ $problem_statement }}</textarea>
                                @error('problem_statement') <span class="text-danger small mb-2 d-block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="why-study-container mt-4">
                            @foreach($whys as $index => $why)
                                <div class="mb-4 px-0" wire:key="why-editor-container-{{ $index }}">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="badge badge-pill badge-soft-warning px-3 py-2" style="font-size: 0.7rem; background: rgba(245, 158, 11, 0.1); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.2);">
                                            <i class="mdi mdi-help-circle-outline mr-1"></i> WHY {{ $index + 1 }} <span class="ml-1 text-danger">*</span>
                                        </span>
                                        @if(count($whys) > 1)
                                            <button type="button" class="btn btn-link btn-sm text-danger p-0" wire:click="removeWhy({{ $index }})">
                                                <i class="mdi mdi-delete-outline mr-1"></i> Remove
                                            </button>
                                        @endif
                                    </div>
                                    <div wire:ignore x-init="initTinyMCE()">
                                        <textarea id="why_{{ $index }}_editor" class="capa-editor why-dynamic-editor">{{ $why }}</textarea>
                                    </div>
                                </div>
                            @endforeach

                            <div class="mb-4 text-center">
                                <button type="button" class="btn btn-primary btn-sm px-4 shadow-sm font-weight-bold" wire:click="addWhy">
                                    <i class="mdi mdi-plus-circle-outline mr-1"></i> Add Another Why Analysis Step
                                </button>
                            </div>

                            <div class="mb-4 px-0" wire:key="root-cause-editor-container">
                                <label class="investigation-label font-weight-bold mb-2 text-info" style="font-size: 0.75rem; letter-spacing: 0.05em;">
                                    <i class="mdi mdi-magnify mr-1"></i> ROOT CAUSE SUMMARY <span class="text-danger">*</span>
                                </label>
                                <div wire:ignore x-init="initTinyMCE()">
                                    <textarea id="root_cause_summary_editor" class="capa-editor">{{ $root_cause_analysis }}</textarea>
                                </div>
                                @error('root_cause_analysis') <span class="text-danger small d-block">{{ $message }}</span> @enderror
                                <div class="row mt-2">
                                    <div class="col-md-7 mb-0">
                                        <label class="investigation-label">Identified By <span class="text-danger">*</span></label>
                                        <div wire:ignore>
                                            <select class="form-control capa-select2" multiple="multiple"
                                                    x-data="{
                                                        init() {
                                                            let el = $(this.$el);
                                                            el.select2({ placeholder: 'Select Root Cause Identifier...', width: '100%' });
                                                            this.$watch('$wire.root_cause_by', value => {
                                                                el.val(value).trigger('change.select2');
                                                            });
                                                            el.on('change', () => {
                                                                this.$wire.set('root_cause_by', el.val());
                                                            });
                                                            el.val(this.$wire.root_cause_by).trigger('change.select2');
                                                        }
                                                    }">
                                                @foreach($users as $user)
                                                    <option value="{{ $user->name }}">{{ $user->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        @error('root_cause_by') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="col-md-5 mb-0">
                                        <input type="date" class="form-control" wire:model="root_cause_date">
                                        @error('root_cause_date') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4 px-0" wire:key="corrective-action-editor-container">
                                <label class="investigation-label font-weight-bold mb-2 text-success" style="font-size: 0.75rem; letter-spacing: 0.05em;">
                                    <i class="mdi mdi-check-decagram-outline mr-1"></i> PROPOSED CORRECTIVE ACTION <span class="text-danger">*</span>
                                </label>
                                <div wire:ignore x-init="initTinyMCE()">
                                    <textarea id="corrective_action_taken_editor" class="capa-editor">{{ $corrective_action_taken }}</textarea>
                                </div>
                                @error('corrective_action_taken') <span class="text-danger small d-block">{{ $message }}</span> @enderror
                                <div class="row mt-2">
                                    <div class="col-md-7 mb-0">
                                        <label class="investigation-label">Identified By <span class="text-danger">*</span></label>
                                        <div wire:ignore>
                                            <select class="form-control capa-select2" multiple="multiple"
                                                    x-data="{
                                                        init() {
                                                            let el = $(this.$el);
                                                            el.select2({ placeholder: 'Select Corrective Action Identifier...', width: '100%' });
                                                            this.$watch('$wire.corrective_action_by', value => {
                                                                el.val(value).trigger('change.select2');
                                                            });
                                                            el.on('change', () => {
                                                                this.$wire.set('corrective_action_by', el.val());
                                                            });
                                                            el.val(this.$wire.corrective_action_by).trigger('change.select2');
                                                        }
                                                    }">
                                                @foreach($users as $user)
                                                    <option value="{{ $user->name }}">{{ $user->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        @error('corrective_action_by') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="col-md-5 mb-0">
                                        <input type="date" class="form-control" wire:model="corrective_action_date">
                                        @error('corrective_action_date') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4 border-top pt-3">
                            <button type="button" class="btn btn-primary btn-sm px-4 font-weight-bold shadow-sm"
                                x-on:click="saveAllAndContinue()" wire:loading.attr="disabled">
                                <span wire:loading.remove><i class="mdi mdi-content-save-outline mr-1"></i>Save & Proceed to CAPA</span>
                                <span wire:loading><i class="mdi mdi-loading mdi-spin mr-1"></i>Saving...</span>
                            </button>
                        </div>
                    </div>
                @else
                    <div wire:key="nc-view-mode">
                        <div class="mb-4">
                            <h6 class="crm-read-label mb-2">Problem Statement</h6>
                            <div class="investigation-text bg-soft-light rounded border p-3">
                                {!! $problem_statement ?: '<span class="text-muted">Not provided.</span>' !!}
                            </div>
                        </div>

                        <div class="row mb-5">
                            @foreach($whys as $index => $why)
                                <div class="{{ count($whys) > 1 ? 'col-md-6 mb-3' : 'col-md-12 mb-3' }}">
                                    <div class="p-3 rounded border {{ $index % 2 == 0 ? 'bg-soft-warning' : 'bg-soft-danger' }}" style="height: 100%; border-left: 4px solid {{ $index % 2 == 0 ? 'var(--crm-warning)' : 'var(--crm-danger)' }} !important;">
                                        <h6 class="crm-read-label mb-2"><i class="mdi mdi-help-circle-outline mr-1"></i> Why {{ $index + 1 }}</h6>
                                        <div class="investigation-text bg-transparent border-0 p-0" style="min-height: auto;">{!! $why ?: '<span class="text-muted">Not recorded</span>' !!}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-4 pt-4 border-top">
                            <div class="mb-4">
                                <h6 class="crm-read-label text-info mb-2"><i class="mdi mdi-magnify mr-1"></i> Root Cause Summary</h6>
                                <div class="investigation-text bg-soft-info rounded border p-3" style="border-left: 4px solid #0ea5e9 !important;">
                                    {!! $root_cause_analysis ?: '<span class="text-muted">Analysis in progress...</span>' !!}
                                    <div class="crm-attribution-bar mt-3">
                                        <div><span class="attr-label"><i class="mdi mdi-account-circle-outline mr-1"></i>By:</span> <span class="attr-value">{{ is_array($root_cause_by) ? implode(', ', $root_cause_by) : ($root_cause_by ?: 'Not Set') }}</span></div>
                                        <div><span class="attr-label"><i class="mdi mdi-calendar-outline mr-1"></i>On:</span> <span class="attr-value">{{ $root_cause_date ?: 'No Date' }}</span></div>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <h6 class="crm-read-label text-success mb-2"><i class="mdi mdi-check-decagram-outline mr-1"></i> Proposed Corrective Action</h6>
                                <div class="investigation-text bg-soft-success rounded border p-3" style="border-left: 4px solid #10b981 !important;">
                                    {!! $corrective_action_taken ?: '<span class="text-muted">Action plan pending...</span>' !!}
                                    <div class="crm-attribution-bar mt-3">
                                        <div><span class="attr-label"><i class="mdi mdi-account-circle-outline mr-1"></i>By:</span> <span class="attr-value">{{ is_array($corrective_action_by) ? implode(', ', $corrective_action_by) : ($corrective_action_by ?: 'Not Set') }}</span></div>
                                        <div><span class="attr-label"><i class="mdi mdi-calendar-outline mr-1"></i>On:</span> <span class="attr-value">{{ $corrective_action_date ?: 'No Date' }}</span></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @script
    <script>
        Alpine.data('ncTab', () => ({
            initTinyMCE() {
                if (typeof tinymce === 'undefined') {
                    setTimeout(() => this.initTinyMCE(), 100);
                    return;
                }

                this.$nextTick(() => {
                    const selectors = [
                        '#problem_statement_editor',
                        '#root_cause_summary_editor',
                        '#corrective_action_taken_editor'
                    ];

                    selectors.forEach(selector => this.setupEditor(selector));
                    document.querySelectorAll('.why-dynamic-editor').forEach(textarea => {
                         this.setupEditor('#' + textarea.id);
                    });
                });
            },
            setupEditor(selector) {
                const textarea = document.querySelector(selector);
                if (!textarea) return;

                if (tinymce.get(textarea.id)) {
                     textarea.style.opacity = '1';
                     return;
                }

                tinymce.init({
                    selector: selector,
                    height: 180,
                    menubar: false,
                    plugins: 'lists link',
                    toolbar: 'bold italic underline | bullist numlist | link',
                    branding: false,
                    promotion: false,
                    setup: (editor) => {
                        editor.on('init', () => {
                            textarea.style.opacity = '1';
                        });
                    },
                    content_style: 'body { font-family: Inter, sans-serif; font-size: 14px; line-height: 1.6; color: #1e293b; padding: 12px; }'
                });
            },
            lastSaved: '',
            autosaveInterval: null,
            triggerAutosave() {
                if (!this.$wire.isEditing) return;
                
                const whys = [];
                document.querySelectorAll('.why-dynamic-editor').forEach(textarea => {
                    whys.push(tinymce.get(textarea.id)?.getContent() || '');
                });

                const data = {
                    problem_statement: tinymce.get('problem_statement_editor')?.getContent() || '',
                    whys: whys,
                    root_cause_analysis: tinymce.get('root_cause_summary_editor')?.getContent() || '',
                    corrective_action_taken: tinymce.get('corrective_action_taken_editor')?.getContent() || ''
                };
                this.$wire.applyRichTextData(data);
                this.$wire.persistDraft();
            },
            async saveAllAndContinue() {
                const whys = [];
                document.querySelectorAll('.why-dynamic-editor').forEach(textarea => {
                    whys.push(tinymce.get(textarea.id)?.getContent() || '');
                });

                const data = {
                    problem_statement: tinymce.get('problem_statement_editor')?.getContent() || '',
                    whys: whys,
                    root_cause_analysis: tinymce.get('root_cause_summary_editor')?.getContent() || '',
                    corrective_action_taken: tinymce.get('corrective_action_taken_editor')?.getContent() || ''
                };
               await $wire.saveNcDetails(data);
            },
            init() {
                this.$wire.on('sync-and-save-draft', () => {
                   if (this.$wire.isEditing) {
                        this.triggerAutosave();
                   }
                });

                this.$wire.on('progress-saved-silently', (eventData) => {
                    let data = eventData;
                    if (Array.isArray(eventData)) data = eventData[0];
                    if (data && data.time) {
                        this.lastSaved = data.time;
                    }
                });

                if (this.$wire.isEditing) {
                    this.initTinyMCE();
                    this.startAutosave();
                }

                this.$watch('$wire.isEditing', value => {
                    if (value) {
                        this.initTinyMCE();
                        this.startAutosave();
                    } else {
                        this.stopAutosave();
                        tinymce.remove();
                    }
                });
            },
            startAutosave() {
                if (this.autosaveInterval) return;
                this.autosaveInterval = setInterval(() => {
                    this.triggerAutosave();
                }, 30000); // 30 seconds
            },
            stopAutosave() {
                if (this.autosaveInterval) {
                    clearInterval(this.autosaveInterval);
                    this.autosaveInterval = null;
                }
            },
        }));
    </script>
    @endscript
</div>
