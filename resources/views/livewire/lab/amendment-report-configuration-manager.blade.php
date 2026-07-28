<div>
    @if($message !== '')
        <div class="alert alert-{{ $messageType === 'success' ? 'success' : 'danger' }} alert-dismissible fade show" role="alert">
            {{ $message }}
            <button type="button" class="close" wire:click="dismissMessage" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom">
            <h5 class="mb-0 font-weight-bold">Amendment Report Configuration</h5>
            <small class="text-muted">Controls wording and numbering printed on revised / amended reports.</small>
        </div>
        <div class="card-body">
            <form wire:submit.prevent="save">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="font-weight-bold" for="amendment_revision_format">Revision number format</label>
                        <input type="text" id="amendment_revision_format" class="form-control"
                               wire:model.live="amendment_revision_format"
                               placeholder="R{nn} or Rev {n}">
                        <small class="text-muted">Use <code>{n}</code> (e.g. 2) or <code>{nn}</code> (e.g. 02).</small>
                        @error('amendment_revision_format') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="font-weight-bold" for="amendment_revision_label">Revision label</label>
                        <input type="text" id="amendment_revision_label" class="form-control"
                               wire:model.live="amendment_revision_label">
                        @error('amendment_revision_label') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="font-weight-bold" for="amendment_reason_label">Amendment reason label</label>
                        <input type="text" id="amendment_reason_label" class="form-control"
                               wire:model.live="amendment_reason_label">
                        @error('amendment_reason_label') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="font-weight-bold" for="amendment_report_number_format">Report number format</label>
                        <input type="text" id="amendment_report_number_format" class="form-control"
                               wire:model.live="amendment_report_number_format"
                               placeholder="{job}-R{nn}">
                        <small class="text-muted">Use <code>{job}</code>, <code>{n}</code>, <code>{nn}</code>.</small>
                        @error('amendment_report_number_format') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-12 mb-3">
                        <label class="font-weight-bold" for="amendment_supersedes_text">Supersedes text</label>
                        <textarea id="amendment_supersedes_text" class="form-control" rows="2"
                                  wire:model.live="amendment_supersedes_text"></textarea>
                        @error('amendment_supersedes_text') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="font-weight-bold" for="amendment_sample_number_suffix_format">Sample number suffix (optional)</label>
                        <input type="text" id="amendment_sample_number_suffix_format" class="form-control"
                               wire:model.live="amendment_sample_number_suffix_format"
                               placeholder="-V{n}">
                        <small class="text-muted">Leave blank to skip sample-code suffix changes. Placeholders: <code>{n}</code>, <code>{nn}</code>.</small>
                        @error('amendment_sample_number_suffix_format') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>
                </div>

                <hr>

                <h6 class="font-weight-bold mb-3">Live preview</h6>
                <div class="row mb-3">
                    <div class="col-md-3 mb-2">
                        <label class="small font-weight-bold">Preview version</label>
                        <input type="number" min="1" max="99" class="form-control form-control-sm"
                               wire:model.live="previewVersion">
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="small font-weight-bold">Preview job number</label>
                        <input type="text" class="form-control form-control-sm"
                               wire:model.live="previewJobNumber">
                    </div>
                </div>
                <div class="border rounded p-3 bg-light mb-4" style="font-size: 0.95rem;">
                    <div class="mb-2"><strong>{{ $amendment_supersedes_text ?: '…' }}</strong></div>
                    <div class="mb-1">
                        <strong>{{ $amendment_revision_label ?: 'Revision' }}:</strong>
                        {{ $this->previewRevision }}
                    </div>
                    <div class="mb-1">
                        <strong>Report No:</strong> {{ $this->previewReportNumber }}
                    </div>
                    <div class="mb-1">
                        <strong>Sample suffix:</strong> {{ $this->previewSampleSuffix }}
                    </div>
                    <div>
                        <strong>{{ $amendment_reason_label ?: 'Reason' }}:</strong>
                        <em class="text-muted"> (reason from amendment record)</em>
                    </div>
                </div>

                <div class="d-flex" style="gap: 8px;">
                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="save">Save configuration</span>
                        <span wire:loading wire:target="save">Saving…</span>
                    </button>
                    <button type="button" class="btn btn-outline-secondary" wire:click="resetToDefaults"
                            wire:confirm="Reset all amendment report settings to defaults?">
                        Reset to defaults
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
