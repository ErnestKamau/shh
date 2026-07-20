<div class="signature-section mt-2">
    <div class="signature-section-head d-flex justify-content-between align-items-center mb-3">
        <div>
            <h6 class="mb-1"><i class="mdi mdi-draw-pen mr-1"></i>{{ __('personnel.signature_attachment') ?? 'Digital Signature' }}</h6>
            <small class="text-muted">{{ __('personnel.signature_section_intro') ?? 'Upload a signature image or draw one on the pad. A drawn signature takes priority.' }}</small>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <div class="signature-card h-100">
                <label class="signature-label d-block">{{ __('personnel.upload_signature') ?? 'Upload signature' }}</label>
                <input type="file" id="{{ $uploadId }}" wire:model="{{ $signatureUploadProperty }}" class="form-control" accept="image/*">
                <small class="text-muted d-block mt-2">{{ __('personnel.accepted_signature_formats') ?? 'PNG, JPG, GIF, or WEBP up to 3MB.' }}</small>
                @error($signatureUploadProperty)<small class="text-danger d-block mt-1">{{ $message }}</small>@enderror
                @if(!empty($currentSignature))
                    <div class="signature-preview mt-3">
                        <small class="text-muted d-block mb-1">{{ __('personnel.current_signature') ?? 'Current signature' }}</small>
                        <img src="{{ $currentSignature }}" alt="{{ __('personnel.current_signature') ?? 'Current signature' }}" class="img-fluid">
                    </div>
                @endif
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="signature-card h-100">
                <label class="signature-label d-block">{{ __('personnel.sign_using_pad') ?? 'Sign using pad' }}</label>
                <div class="signature-canvas-wrap" id="{{ $wrapId }}" wire:ignore>
                    <canvas id="{{ $canvasId }}" width="620" height="190"></canvas>
                    <span class="signature-canvas-placeholder" id="{{ $placeholderId }}">{{ __('personnel.sign_here') ?? 'Sign here' }}</span>
                </div>
                <input type="hidden" id="{{ $hiddenId }}" wire:model="{{ $signatureDataProperty }}">
                <div class="d-flex justify-content-between align-items-center mt-2">
                    <div class="d-flex align-items-center" style="gap: 8px;">
                        <small class="text-muted">{{ __('personnel.signature_draw_overrides_upload') ?? 'Drawn signature overrides upload.' }}</small>
                        <span id="{{ $statusId }}" class="signature-status signature-status-empty" aria-live="polite">Not signed</span>
                    </div>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="{{ $clearId }}">
                        <i class="mdi mdi-eraser"></i> {{ __('personnel.clear') ?? 'Clear' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
