<div>
    {{-- TinyMCE note editor styling --}}
    @push('title2')
        <style>
            .note-editor-wrap .tox-tinymce {
                border-radius: 8px;
                border: 1px solid #E5E7EB !important;
                transition: border-color 0.2s, box-shadow 0.2s;
            }
            .note-editor-wrap .tox-tinymce:focus-within {
                border-color: #3B82F6 !important;
                box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1) !important;
            }
            #noteModal .modal-body {
                padding: 1.25rem 1.5rem;
            }
            #noteModal .form-group label {
                font-weight: 600;
                color: #475569;
                margin-bottom: 6px;
            }
        </style>
    @endpush
    <div x-data="{
            init() {
                if (typeof this.$wire !== 'undefined') {
                    window.__notesTabWire = this.$wire;
                }
                Livewire.on('show-note-modal', () => $('#noteModal').modal('show'));
                Livewire.on('close-note-modal', () => $('#noteModal').modal('hide'));
            }
        }">

        {{-- Section Header — ensure clickable (match workflow-actions pattern from complaint-show) --}}
        <div class="d-flex justify-content-between align-items-center mb-3" style="position:relative;z-index:10;pointer-events:auto;">
            <div class="d-flex align-items-center">
                <span class="mr-2 d-flex align-items-center justify-content-center rounded"
                    style="width:32px;height:32px;background:#f0f9ff;flex-shrink:0;">
                    <i class="mdi mdi-note-text" style="font-size:1.1rem;color:#0891b2;"></i>
                </span>
                <div>
                    <small class="font-weight-bold text-dark" style="font-size:0.82rem;">{{ __('crm.notes') }}</small>
                    <small class="text-muted d-block" style="font-size:0.67rem;">{{ __('crm.internal_observations_reminders_nonconformity') }}</small>
                </div>
            </div>
            <div class="d-flex align-items-center" style="gap:8px;">
                <button type="button" class="btn btn-outline-success btn-sm text-nowrap crm-outline-btn-sm" wire:click="exportToExcel">
                    <i class="mdi mdi-microsoft-excel"></i> {{ __('crm.export_to_excel') }}
                </button>
                <button type="button" class="btn btn-outline-primary btn-sm crm-outline-btn-sm" wire:click="openAddModal">
                    <i class="mdi mdi-plus"></i> {{ __('crm.add_note') }}
                </button>
            </div>
        </div>

        {{-- Loading Indicator --}}
        <div wire:loading wire:target="openAddModal,editNote,deleteNote,addNote,updateNote"
            class="crm-loading-indicator">
            <i class="mdi mdi-loading mdi-spin"></i> {{ __('crm.loading') }}...
        </div>

        <x-crm.data-table class="crm-loading-overlay" wire:loading.class="opacity-50" plain-rows>
            <x-slot:header>
                <tr>
                    <th style="min-width:100px;">{{ __('crm.actions') }}</th>
                    <th>{{ __('crm.type') }}</th>
                    <th>{{ __('crm.note') }}</th>
                    <th>{{ __('crm.created_by') }}</th>
                    <th>{{ __('crm.created_at') }}</th>
                </tr>
            </x-slot:header>
                        @forelse($notes as $note)
                            <tr wire:key="note-{{ $note->id }}">
                                <td nowrap>
                                    <div class="d-flex flex-nowrap">
                                        <button type="button" class="btn btn-sm rm-act-btn rm-act-btn--edit"
                                            wire:click="editNote('{{ $note->id }}')" title="{{ __('crm.edit') }}">
                                            <i class="mdi mdi-pencil-outline"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm rm-act-btn rm-act-btn--delete"
                                            wire:click="deleteNote('{{ $note->id }}')"
                                            wire:confirm="{{ __('crm.delete_note_confirm') }}" title="{{ __('crm.delete') }}">
                                            <i class="mdi mdi-trash-can-outline"></i>
                                        </button>
                                    </div>
                                </td>
                                <td>{{ $note->type }}</td>
                                <td>{!! $note->notes !!}</td>
                                <td>{{ $note->created_by }}</td>
                                <td>{{ $note->created_at->format('Y-m-d H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <x-crm.empty-state
                                        icon="mdi-note-off-outline"
                                        :message="__('crm.no_notes_recorded')"
                                        :help="__('crm.no_notes_recorded_help')"
                                    />
                                </td>
                            </tr>
                        @endforelse
        </x-crm.data-table>

        <x-crm.pagination :summary="__('crm.showing_to_of_results', ['from' => ($notes->firstItem() ?? 0), 'to' => ($notes->lastItem() ?? 0), 'total' => $notes->total()])">
            {{ $notes->links() }}
        </x-crm.pagination>

        <!-- Note Modal — teleported to body to avoid stacking context clipping backdrop -->
        @teleport('body')
        <div wire:ignore.self class="modal fade" id="noteModal" tabindex="-1" role="dialog" aria-labelledby="noteModalLabel"
            aria-hidden="true" data-notes-wire-id="{{ $this->getId() }}" x-data="noteEditorData()">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                    <h5 class="modal-title" id="noteModalLabel">
                        <i class="mdi mdi-note-edit-outline mr-1 text-primary"></i>
                        {{ $editingNoteId ? __('crm.edit_note') : __('crm.add_note') }}
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>{{ __('crm.note_type') }} <span class="text-danger">*</span>:</label>
                        <select wire:model.live="noteType" class="form-control custom-select-sm no-select2" required>
                            <option value="">{{ __('crm.select_type') }}</option>
                            @foreach($availableTypes as $type)
                                <option value="{{ $type }}">{{ $type }}</option>
                            @endforeach
                            <option value="Other">{{ __('crm.other_add_new') }}</option>
                        </select>
                        @error('noteType') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    @if($showCustomType)
                        <div class="form-group">
                            <label>{{ __('crm.custom_note_type') }} <span class="text-danger">*</span>:</label>
                            <input type="text" wire:model="customNoteType" class="form-control form-control-sm"
                                placeholder="{{ __('crm.custom_note_type_placeholder') }}" required>
                            @error('customNoteType') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    @endif
                    <div class="form-group">
                        <label>{{ __('crm.note') }} <span class="text-danger">*</span>:</label>
                        <div wire:ignore class="note-editor-wrap">
                            <textarea id="note-tinymce-editor" class="note-tinymce-editor form-control" rows="8" placeholder="{{ __('crm.note_description_placeholder') }}"></textarea>
                        </div>
                        @error($editingNoteId ? 'editingNote' : 'newNote')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="isPublicNote" wire:model="isPublic">
                            <label class="custom-control-label" for="isPublicNote">{{ __('crm.mark_public_visible_closure_report') }}</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('crm.close') }}</button>
                    @if($editingNoteId)
                        <button type="button" class="btn btn-primary" @click="syncTinyMCEToLivewire(); getWire()?.updateNote()">{{ __('crm.update') }}</button>
                    @else
                        <button type="button" class="btn btn-primary" @click="syncTinyMCEToLivewire(); getWire()?.addNote()">{{ __('crm.save_note') }}</button>
                    @endif
                </div>
                </div>
            </div>
        </div>
        @endteleport
    </div>

    @script
    <script>
        (function () {
            if (typeof tinymce === 'undefined') {
                var s = document.createElement('script');
                s.src = '/tinymce/tinymce.min.js';
                s.onload = function () { document.dispatchEvent(new Event('tinymce:loaded')); };
                document.head.appendChild(s);
            }
        })();
        Alpine.data('noteEditorData', function () {
            return {
                pendingContent: null,
                _wireId: null,
                getWire: function () {
                    if (this._wireId && typeof Livewire !== 'undefined') {
                        var c = Livewire.find(this._wireId);
                        if (c) return c;
                    }
                    if (window.__notesTabWire) return window.__notesTabWire;
                    return typeof $wire !== 'undefined' ? $wire : null;
                },
                init: function () {
                    this._wireId = (this.$el && this.$el.getAttribute('data-notes-wire-id')) || (this.$el && this.$el.closest && (function (r) { return r ? r.getAttribute('wire:id') : null; })(this.$el.closest('[wire\\:id]')));
                    var self = this;
                    Livewire.on('show-note-modal', function () { $('#noteModal').modal('show'); });
                    Livewire.on('close-note-modal', function () { $('#noteModal').modal('hide'); });
                    Livewire.on('set-quill-content', function (data) {
                        var content = typeof data === 'string' ? data : (data && data.content !== undefined ? data.content : (Array.isArray(data) ? data[0] : '')) || '';
                        self.pendingContent = content;
                        if (typeof tinymce !== 'undefined') {
                            var ed = tinymce.get('note-tinymce-editor');
                            if (ed) ed.setContent(content);
                        }
                    });
                    Livewire.on('reset-quill', function () {
                        self.pendingContent = '';
                        if (typeof tinymce !== 'undefined') {
                            var ed = tinymce.get('note-tinymce-editor');
                            if (ed) ed.setContent('');
                        }
                    });
                    $('#noteModal').on('shown.bs.modal', function () { self.initTinyMCE(); });
                    $('#noteModal').on('hidden.bs.modal', function () { self.destroyTinyMCE(); });
                },
                initTinyMCE: function () {
                    var self = this;
                    if (typeof tinymce === 'undefined') {
                        document.addEventListener('tinymce:loaded', function onLoaded() { document.removeEventListener('tinymce:loaded', onLoaded); self.initTinyMCE(); }, { once: true });
                        return;
                    }
                    if (!document.getElementById('note-tinymce-editor')) return;
                    tinymce.remove('#note-tinymce-editor');
                    tinymce.init({
                        selector: '#note-tinymce-editor',
                        height: 220,
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
                                if (self.pendingContent !== null) { editor.setContent(self.pendingContent); self.pendingContent = null; }
                            });
                            editor.on('change keyup', function () {
                                var wire = self.getWire();
                                if (wire) wire.set(wire.get('editingNoteId') ? 'editingNote' : 'newNote', editor.getContent());
                            });
                        }
                    });
                },
                destroyTinyMCE: function () {
                    if (typeof tinymce !== 'undefined') tinymce.remove('#note-tinymce-editor');
                },
                syncTinyMCEToLivewire: function () {
                    if (typeof tinymce === 'undefined') return;
                    tinymce.triggerSave();
                    var ed = tinymce.get('note-tinymce-editor');
                    var wire = this.getWire();
                    if (ed && wire) wire.set(wire.get('editingNoteId') ? 'editingNote' : 'newNote', ed.getContent());
                }
            };
        });
    </script>
    @endscript
</div>