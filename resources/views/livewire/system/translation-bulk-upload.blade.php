<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="mdi mdi-database-import text-primary"></i> {{ __('system.bulk_translation_import') }}</h5>
        <small class="text-muted">{{ __('system.upload_blurb') }}</small>
    </div>
    <div class="card-body">
        <div class="form-group mb-4">
            <label class="d-block text-muted font-weight-bold mb-3">{{ __('system.input_mode') }}</label>
            <div class="row">
                <div class="col-md-6 mb-3 mb-md-0">
                    <div class="card cursor-pointer border {{ $mode === 'json' ? 'border-primary bg-light' : 'border-light shadow-sm' }}" 
                         wire:click="$set('mode', 'json')" 
                         style="cursor: pointer; transition: all 0.2s;">
                        <div class="card-body text-center p-4">
                            <i class="mdi mdi-code-json {{ $mode === 'json' ? 'text-primary' : 'text-muted' }} mb-2 d-block" style="font-size: 2.5rem;"></i>
                            <h6 class="mb-1 {{ $mode === 'json' ? 'text-primary' : 'text-dark' }}">{{ __('system.json_textarea') }}</h6>
                            <p class="small text-muted mb-0">{{ __('system.paste_raw_json') }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card cursor-pointer border {{ $mode === 'file' ? 'border-primary bg-light' : 'border-light shadow-sm' }}" 
                         wire:click="$set('mode', 'file')" 
                         style="cursor: pointer; transition: all 0.2s;">
                        <div class="card-body text-center p-4">
                            <i class="mdi mdi-file-excel {{ $mode === 'file' ? 'text-primary' : 'text-muted' }} mb-2 d-block" style="font-size: 2.5rem;"></i>
                            <h6 class="mb-1 {{ $mode === 'file' ? 'text-primary' : 'text-dark' }}">{{ __('system.csv_xlsx_file') }}</h6>
                            <p class="small text-muted mb-0">{{ __('system.upload_spreadsheet') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($mode === 'json')
            <div class="form-group">
                <label>{{ __('system.json_payload') }}</label>
                <textarea class="form-control" rows="8" wire:model.defer="jsonPayload" placeholder='[{"group":"ai","key":"active","en":"Active","pt":"Ativo","ar":"نشط"}]'></textarea>
                @error('jsonPayload') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
        @else
            <div class="form-group">
                <label>{{ __('system.upload_file') }}</label>
                <div class="border-dash rounded p-4 text-center bg-light" style="border: 2px dashed #ced4da;">
                    <i class="mdi mdi-cloud-upload text-muted mb-2" style="font-size: 32px;"></i>
                    <p class="mb-2">{{ __('system.drag_drop_blurb') }}</p>
                    <input type="file" class="form-control-file d-inline-block w-auto" wire:model="uploadFile" accept=".csv,.txt,.xlsx">
                </div>
                @error('uploadFile') <small class="text-danger mt-1 d-block">{{ $message }}</small> @enderror
            </div>
        @endif

        <button type="button" class="btn btn-primary" wire:click="previewImport" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="previewImport">{{ __('system.preview_import') }}</span>
            <span wire:loading wire:target="previewImport"><i class="mdi mdi-loading mdi-spin"></i> {{ __('system.parsing') }}</span>
        </button>

        @if(!empty($previewRows))
            <button type="button" class="btn btn-success ml-2" wire:click="confirmImport" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="confirmImport">{{ __('system.confirm_import') }}</span>
                <span wire:loading wire:target="confirmImport"><i class="mdi mdi-loading mdi-spin"></i> Importing...</span>
            </button>
            <button type="button" class="btn btn-light ml-2" wire:click="clearPreview">Clear</button>
        @endif

        @if(!empty($previewRows))
            <div class="mt-3 border rounded p-3 bg-light">
                <h6 class="mb-2">Import Preview</h6>
                <div class="small">Rows Detected: {{ $previewStats['total_rows'] ?? 0 }}</div>
                <div class="small">Rows Shown: {{ $previewStats['previewed_rows'] ?? 0 }}</div>
                @if(!empty($previewStats['truncated']))
                    <div class="small text-warning">Preview limited to first 20 rows.</div>
                @endif

                <div class="table-responsive mt-2">
                    <table class="table table-sm table-striped mb-0">
                        <thead>
                            <tr>
                                @foreach(array_keys($previewRows[0] ?? []) as $header)
                                    <th>{{ strtoupper($header) }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($previewRows as $row)
                                <tr>
                                    @foreach(array_keys($previewRows[0] ?? []) as $header)
                                        <td>{{ (string) ($row[$header] ?? '') }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @if(!empty($summary))
            <div class="mt-3 border rounded p-3 bg-light">
                <h6 class="mb-2">Import Summary</h6>
                <div class="small">Created: {{ $summary['created'] ?? 0 }}</div>
                <div class="small">Updated: {{ $summary['updated'] ?? 0 }}</div>
                <div class="small">Failed: {{ $summary['failed'] ?? 0 }}</div>

                @if(!empty($summary['errors']))
                    <hr>
                    <div class="small text-danger font-weight-bold mb-1">Errors</div>
                    <ul class="small text-danger mb-0">
                        @foreach($summary['errors'] as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endif
    </div>
</div>
