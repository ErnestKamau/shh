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
            <small class="text-muted">
                These settings control what appears on amended / revised lab reports (COA and Test Request Report).
                Save, then raise or regenerate an amended report to see the changes.
            </small>
        </div>
        <div class="card-body">
            <div class="alert alert-light border mb-4" style="font-size: 0.9rem;">
                <strong>Placeholders</strong> (type these exactly; they are replaced when the report is generated):
                <ul class="mb-0 mt-2 pl-3">
                    <li><code>{n}</code> — revision number as entered (e.g. version 2 → <strong>2</strong>)</li>
                    <li><code>{nn}</code> — same number with a leading zero (e.g. version 2 → <strong>02</strong>)</li>
                    <li><code>{job}</code> — the job / batch number (e.g. <strong>260428001</strong>)</li>
                    <li><code>{samples}</code> — report number format only. Sample numbers on the report: a single
                        number (<strong>001</strong>), a range when they're consecutive (<strong>001 - 045</strong>),
                        or a comma list when they're not (<strong>001, 005, 045</strong>)</li>
                    <li><code>{sections}</code> — report number format only. Lab section initials on the report,
                        comma-separated (e.g. Microbiology + Chemistry → <strong>M, C</strong>)</li>
                </ul>
            </div>

            <form wire:submit.prevent="save">
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <label class="font-weight-bold" for="amendment_revision_format">1. Revision number format</label>
                        <input type="text" id="amendment_revision_format" class="form-control"
                               wire:model.live="amendment_revision_format"
                               placeholder="R{nn}">
                        <small class="text-muted d-block mt-1">
                            How the revision looks next to the revision label.<br>
                            Examples: <code>R{nn}</code> → <em>R02</em> · <code>Rev {n}</code> → <em>Rev 2</em> · <code>{nn}</code> → <em>02</em>
                        </small>
                        @error('amendment_revision_format') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6 mb-4">
                        <label class="font-weight-bold" for="amendment_revision_label">2. Revision label</label>
                        <input type="text" id="amendment_revision_label" class="form-control"
                               wire:model.live="amendment_revision_label"
                               placeholder="Revision No.">
                        <small class="text-muted d-block mt-1">
                            Fixed wording before the revision number (no placeholders).<br>
                            Example: label <code>Revision No.</code> + format <code>R{nn}</code> prints as
                            <strong>Revision No.: R02</strong>
                        </small>
                        @error('amendment_revision_label') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6 mb-4">
                        <label class="font-weight-bold" for="amendment_reason_label">3. Amendment reason label</label>
                        <input type="text" id="amendment_reason_label" class="form-control"
                               wire:model.live="amendment_reason_label"
                               placeholder="Amendment Reason">
                        <small class="text-muted d-block mt-1">
                            Heading shown above the reason typed when the amendment was raised.<br>
                            The reason text itself comes from the amendment record, not this field.
                        </small>
                        @error('amendment_reason_label') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6 mb-4">
                        <label class="font-weight-bold" for="amendment_report_number_format">4. Report number format</label>
                        <input type="text" id="amendment_report_number_format" class="form-control"
                               wire:model.live="amendment_report_number_format"
                               placeholder="{job}-R{nn}">
                        <small class="text-muted d-block mt-1">
                            Full report reference printed in the header, stored on samples, and used as the
                            downloaded PDF's filename.<br>
                            Examples: <code>{job}-R{nn}</code> → <em>260428001-R02</em> ·
                            <code>{job}/{nn}</code> → <em>260428001/02</em> ·
                            <code>{job}_({samples})_({sections})-R{nn}</code> → <em>261001055_(001 - 045)_(M, C)-R03</em>
                        </small>
                        @error('amendment_report_number_format') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-12 mb-4">
                        <label class="font-weight-bold" for="amendment_supersedes_text">5. Supersedes text</label>
                        <textarea id="amendment_supersedes_text" class="form-control" rows="2"
                                  wire:model.live="amendment_supersedes_text"
                                  placeholder="This report supersedes the original report"></textarea>
                        <small class="text-muted d-block mt-1">
                            Statement shown on amended reports to explain that this version replaces the previous one.
                            Plain text only — no placeholders.
                        </small>
                        @error('amendment_supersedes_text') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6 mb-4">
                        <label class="font-weight-bold" for="amendment_sample_number_suffix_format">6. Sample number suffix (optional)</label>
                        <input type="text" id="amendment_sample_number_suffix_format" class="form-control"
                               wire:model.live="amendment_sample_number_suffix_format"
                               placeholder="-V{nn}">
                        <small class="text-muted d-block mt-1">
                            Appended to every sample code on the amended report
                            (e.g. <code>260428001-001</code> → <code>260428001-001-V02</code>).<br>
                            Leave blank if sample codes should stay unchanged.
                            Placeholders: <code>{n}</code>, <code>{nn}</code>.
                        </small>
                        @error('amendment_sample_number_suffix_format') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>
                </div>

                <hr>

                <h6 class="font-weight-bold mb-2">Live preview</h6>
                <p class="text-muted small mb-3">
                    Change the preview version / job number below to see how an amended report would look.
                    This does not save anything.
                </p>
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
                    <div class="mb-2 text-muted small text-uppercase">What prints on the report</div>
                    <div class="mb-2"><strong>{{ $amendment_supersedes_text ?: '…' }}</strong></div>
                    <div class="mb-1">
                        <strong>{{ $amendment_revision_label ?: 'Revision' }}:</strong>
                        {{ $this->previewRevision }}
                    </div>
                    <div class="mb-1">
                        <strong>Report No:</strong> {{ $this->previewReportNumber }}
                    </div>
                    <div class="mb-1">
                        <strong>Sample example:</strong>
                        {{ $previewJobNumber }}-001{{ $this->previewSampleSuffix === '(none)' ? '' : $this->previewSampleSuffix }}
                        <span class="text-muted">(suffix: {{ $this->previewSampleSuffix }})</span>
                    </div>
                    <div>
                        <strong>{{ $amendment_reason_label ?: 'Reason' }}:</strong>
                        <em class="text-muted"> (taken from the amendment reason entered when the report was sent back)</em>
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
