<div class="cf-signature-section mt-3">
    <div class="cf-section-head mb-2">
        <h5 class="mb-1"><i class="mdi mdi-draw-pen mr-1"></i> Signature</h5>
        <span>Optional — draw on the pad or upload an image/PDF of the signature</span>
    </div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <div class="cf-signature-card h-100">
                <label class="d-block font-weight-semibold">Upload signature</label>
                <input
                    type="file"
                    id="contactFormSignatureUpload"
                    wire:model="signatureUpload"
                    class="form-control"
                    accept="image/*,.pdf,application/pdf"
                >
                <small class="text-muted d-block mt-2">PNG, JPG, GIF, WEBP, or PDF up to 5MB. Drawn pad overrides upload.</small>
                @error('signatureUpload') <small class="text-danger d-block mt-1">{{ $message }}</small> @enderror
                @if($signatureUpload)
                    <small class="text-success d-block mt-1">
                        <i class="mdi mdi-check-circle"></i> File ready: {{ $signatureUpload->getClientOriginalName() }}
                    </small>
                @elseif($currentSignature)
                    <div class="mt-3">
                        <small class="text-muted d-block mb-1">Current signature</small>
                        @if(str_ends_with(strtolower((string) $currentSignature), '.pdf') || str_contains(strtolower((string) $currentSignature), '.pdf'))
                            <a href="{{ $currentSignature }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="mdi mdi-file-pdf-box"></i> View PDF signature
                            </a>
                        @else
                            <img src="{{ $currentSignature }}" alt="Current signature" class="img-fluid" style="max-height: 110px; border: 1px solid #e2e8f0; border-radius: 6px; padding: 4px; background: #fff;">
                        @endif
                        <button type="button" class="btn btn-sm btn-outline-danger mt-2" wire:click="clearContactSignature">
                            <i class="mdi mdi-delete-outline"></i> Remove signature
                        </button>
                    </div>
                @endif
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="cf-signature-card h-100">
                <label class="d-block font-weight-semibold">Sign using pad</label>
                <div class="cf-signature-canvas-wrap" id="contactFormSignatureCanvasWrap" wire:ignore>
                    <canvas id="contactFormSignatureCanvas" width="620" height="190"></canvas>
                    <span class="cf-signature-placeholder" id="contactFormSignaturePlaceholder">Sign here</span>
                </div>
                <input type="hidden" id="contactFormSignatureData" wire:model="signatureData">
                <div class="d-flex justify-content-between align-items-center mt-2">
                    <span id="contactFormSignatureStatus" class="cf-signature-status {{ $signatureData !== '' ? 'is-signed' : '' }}">
                        {{ $signatureData !== '' ? 'Signed' : 'Not signed' }}
                    </span>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="clearContactFormSignaturePad">
                        <i class="mdi mdi-eraser"></i> Clear
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .cf-signature-card {
        background: #fbf9fa;
        border: 1px solid #eee6ea;
        border-radius: 10px;
        padding: 12px;
    }
    .cf-signature-canvas-wrap {
        position: relative;
        width: 100%;
        border: 1px dashed #c4b5bc;
        border-radius: 8px;
        background: #fff;
        overflow: hidden;
        user-select: none;
        -webkit-user-select: none;
    }
    .cf-signature-canvas-wrap canvas {
        width: 100%;
        height: 160px;
        display: block;
        touch-action: none;
        cursor: crosshair;
        position: relative;
        z-index: 2;
        background: #fff;
    }
    .cf-signature-placeholder {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #94a3b8;
        pointer-events: none;
        z-index: 1;
    }
    .cf-signature-status.is-signed {
        color: #15803d;
        font-weight: 600;
    }
</style>
