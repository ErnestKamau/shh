<div class="rr-tab-pane-content">
        <form wire:submit="upload" class="rr-upload-zone mb-3">
            <label class="rr-upload-zone__label">
                <i class="mdi mdi-cloud-upload-outline"></i>
                <span>Choose a file to upload</span>
                <input type="file" wire:model="file" class="rr-upload-zone__input">
            </label>
            @error('file')
                <span class="text-danger small d-block mt-2">{{ $message }}</span>
            @enderror
            <div wire:loading wire:target="file" class="small text-muted mt-2">Processing file…</div>
            <button type="submit" class="btn btn-primary rr-btn-upload mt-2" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="upload"><i class="mdi mdi-upload"></i> Upload</span>
                <span wire:loading wire:target="upload">Uploading…</span>
            </button>
        </form>

        @if($documents->isNotEmpty())
            <ul class="rr-doc-list">
                @foreach($documents as $doc)
                    <li class="rr-doc-list__item">
                        <div class="rr-doc-list__icon">
                            <i class="mdi mdi-file-document-outline"></i>
                        </div>
                        <div class="rr-doc-list__info">
                            <span class="rr-doc-list__name">{{ $doc->original_name }}</span>
                            <span class="rr-doc-list__meta">Version {{ $doc->version }}</span>
                        </div>
                        <a href="{{ route('registry.documents.download', $doc->id) }}"
                           class="btn btn-sm rr-doc-download"
                           title="Download">
                            <i class="mdi mdi-download-outline"></i>
                        </a>
                    </li>
                @endforeach
            </ul>
        @else
            <p class="rr-doc-empty mb-0">No documents uploaded yet.</p>
        @endif
</div>
