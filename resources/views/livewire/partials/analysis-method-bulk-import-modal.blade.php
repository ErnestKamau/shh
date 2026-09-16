{{-- Analysis methods bulk import — same flow as System Settings → Analysis Methods. --}}
@if($showBulkImportModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5); z-index: 1050;" wire:key="analysis-method-bulk-import-modal">
        <div class="modal-dialog modal-lg modal-dialog-centered" style="max-height: 90vh;">
            <div class="modal-content" style="max-height: 90vh; display: flex; flex-direction: column;">
                <div class="modal-header" style="flex-shrink: 0;">
                    <h5 class="modal-title">
                        <i class="mdi mdi-file-excel text-success"></i>
                        Bulk Upload Methods
                    </h5>
                    <button type="button" class="btn-close" wire:click="closeBulkImportModal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="overflow-y: auto; flex: 1 1 auto;">
                    <div class="alert alert-info">
                        <i class="mdi mdi-information"></i>
                        <strong>Instructions:</strong>
                        <ol class="mb-0 mt-2">
                            <li>Download the Excel template below, or use your AmSpec Parameters workbook (methods-only extraction)</li>
                            <li>Fill in the required fields (marked with *)</li>
                            <li>Upload the completed file</li>
                            <li>Review the import results</li>
                        </ol>
                    </div>

                    <div class="mb-3 d-grid gap-2">
                        <button type="button" wire:click="downloadBulkImportCurrentData" class="btn btn-primary w-100">
                            <i class="mdi mdi-database-export"></i> Download current data
                        </button>
                        <button type="button" wire:click="downloadBulkImportTemplate" class="btn btn-outline-primary w-100">
                            <i class="mdi mdi-download"></i> Download blank template
                        </button>
                        <button type="button" wire:click="openBulkImportVersionsModal" class="btn btn-outline-secondary w-100">
                            <i class="mdi mdi-history"></i> Version history
                        </button>
                    </div>

                    <div class="alert alert-danger mb-4">
                        <strong>Replace existing analysis methods</strong>
                        <p class="mb-2 small">
                            When enabled, all analysis methods for your company will be permanently deleted before import.
                            Other data (stage headers, samples, analysis elements, results) is left unchanged.
                            You can upload the dedicated template or your AmSpec Parameters workbook (methods-only extraction).
                        </p>
                        <div class="form-check mb-3">
                            <input type="checkbox" wire:model.live="replaceExisting" class="form-check-input" id="replace_existing_analysis_methods">
                            <label class="form-check-label" for="replace_existing_analysis_methods">
                                Replace all existing analysis methods for this company before import
                            </label>
                        </div>
                        @if ($replaceExisting)
                            <div class="mb-0">
                                <label class="form-label" for="purge_confirmation_methods">
                                    Type <code>DELETE ALL METHODS</code> or your company name to confirm
                                </label>
                                <input type="text"
                                       id="purge_confirmation_methods"
                                       wire:model="purgeConfirmation"
                                       class="form-control"
                                       placeholder="DELETE ALL METHODS">
                                @error('purgeConfirmation') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        @endif
                    </div>

                    <hr>

                    <div class="form-group mb-3 text-start">
                        <label class="form-label fw-bold">Upload Excel file <span class="text-danger">*</span></label>
                        <input type="file" wire:model="bulkFile" class="form-control" accept=".xlsx,.xls,.csv">
                        @error('bulkFile') <span class="text-danger">{{ $message }}</span> @enderror

                        <div wire:loading wire:target="bulkFile" class="mt-2">
                            <small class="text-muted">
                                <i class="mdi mdi-loading mdi-spin"></i> Uploading file…
                            </small>
                        </div>
                    </div>

                    <div class="alert alert-warning mb-0">
                        <i class="mdi mdi-alert"></i>
                        <small>
                            <strong>Note:</strong> Existing rows are updated when a matching code is found unless replace is enabled.
                            Maximum file size: 15MB. Supported formats: .xlsx, .xls, .csv
                        </small>
                    </div>
                </div>
                <div class="modal-footer" style="flex-shrink: 0;">
                    <button type="button" class="btn btn-secondary" wire:click="closeBulkImportModal">Cancel</button>
                    <button type="button" class="btn btn-success" wire:click="processBulkImport" wire:loading.attr="disabled" wire:target="processBulkImport">
                        <span wire:loading.remove wire:target="processBulkImport">
                            <i class="mdi mdi-upload"></i> Upload &amp; Import
                        </span>
                        <span wire:loading wire:target="processBulkImport">
                            <i class="mdi mdi-loading mdi-spin"></i> Processing…
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif

@if($showBulkImportVersionsModal ?? false)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.55); z-index: 1060;" wire:key="analysis-method-bulk-import-versions">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="mdi mdi-history mr-1"></i> Method import versions</h5>
                    <button type="button" class="btn-close" wire:click="closeBulkImportVersionsModal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if(($bulkImportVersions ?? []) === [])
                        <p class="text-muted mb-0">No versions yet. Download current data, edit, and import to create version 1.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Version</th>
                                        <th>Status</th>
                                        <th>Applied</th>
                                        <th>By</th>
                                        <th>Summary</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($bulkImportVersions as $versionRow)
                                        <tr>
                                            <td><strong>v{{ $versionRow['version'] }}</strong></td>
                                            <td>
                                                <span class="badge text-bg-{{ $versionRow['status'] === 'applied' ? 'success' : ($versionRow['status'] === 'rolled_back' ? 'warning' : 'secondary') }}">
                                                    {{ $versionRow['status'] }}
                                                </span>
                                            </td>
                                            <td nowrap>{{ $versionRow['applied_at'] ?? '—' }}</td>
                                            <td>{{ $versionRow['user_name'] ?? '—' }}</td>
                                            <td>{{ $versionRow['summary'] }}</td>
                                            <td class="text-end" nowrap>
                                                <button type="button" class="btn btn-sm btn-outline-primary"
                                                    wire:click="downloadBulkImportVersion('{{ $versionRow['id'] }}')">
                                                    <i class="mdi mdi-download"></i>
                                                </button>
                                                @if($versionRow['status'] !== 'applied')
                                                    <button type="button" class="btn btn-sm btn-outline-warning"
                                                        wire:click="restoreBulkImportVersion('{{ $versionRow['id'] }}')"
                                                        wire:confirm="Restore version {{ $versionRow['version'] }}? Live method data will match that Excel.">
                                                        <i class="mdi mdi-restore"></i> Restore
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" wire:click="closeBulkImportVersionsModal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endif
