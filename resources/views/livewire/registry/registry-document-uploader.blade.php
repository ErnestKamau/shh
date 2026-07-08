<div class="rr-tab-pane-content"
     x-data="{ uploading: false, progress: 0 }"
     x-on:livewire-upload-start="uploading = true; progress = 0"
     x-on:livewire-upload-finish="uploading = false; progress = 100"
     x-on:livewire-upload-cancel="uploading = false; progress = 0"
     x-on:livewire-upload-error="uploading = false; progress = 0"
     x-on:livewire-upload-progress="progress = $event.detail.progress">
    <form wire:submit.prevent="saveDocument" class="rr-upload-zone mb-3">
        <label class="rr-upload-zone__label">
            <i class="mdi mdi-cloud-upload-outline"></i>
            <span>
                @if ($file)
                    {{ method_exists($file, 'getClientOriginalName') ? $file->getClientOriginalName() : 'File selected' }}
                @else
                    Choose a file to upload
                @endif
            </span>
            <input
                type="file"
                wire:model="file"
                wire:key="registry-doc-input-{{ $fileInputKey }}"
                class="rr-upload-zone__input"
                accept=".pdf,.docx,.xlsx,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png"
            >
        </label>

        @error('file')
            <span class="text-danger small d-block mt-2">{{ $message }}</span>
        @enderror

        <div x-show="uploading" x-cloak class="small text-muted mt-2">
            Uploading to server… <span x-text="progress + '%'"></span>
        </div>

        <div wire:loading wire:target="file" class="small text-muted mt-2">
            Processing file…
        </div>

        @if ($file)
            <div class="d-flex align-items-center mt-2">
                <span class="small text-success mr-2">
                    <i class="mdi mdi-check-circle-outline"></i>
                    Ready to save
                </span>
                <button type="button" class="btn btn-link btn-sm p-0" wire:click="clearFile">
                    Clear
                </button>
            </div>
        @endif

        <button
            type="submit"
            class="btn btn-primary rr-btn-upload mt-2"
            wire:loading.attr="disabled"
            wire:target="saveDocument,file"
            @disabled(! $file)
            x-bind:disabled="uploading"
        >
            <span wire:loading.remove wire:target="saveDocument"><i class="mdi mdi-upload"></i> Upload</span>
            <span wire:loading wire:target="saveDocument">Saving…</span>
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
