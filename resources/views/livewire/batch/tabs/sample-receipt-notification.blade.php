<div>
    <div class="workflow-board-panel">
        <div class="workflow-board-panel-header d-flex align-items-center justify-content-between flex-wrap" style="gap: 8px;">
            <h5><i class="mdi mdi-file-document-outline"></i> Sample Receipt Notification (GCLA 01)</h5>
            <div>
                @if($attachmentUrl)
                    <a href="{{ $attachmentUrl }}" target="_blank" class="btn btn-outline-secondary btn-action-sm">
                        <i class="mdi mdi-eye"></i> View Latest Attachment
                    </a>
                @endif
            </div>
        </div>

        <div class="workflow-board-panel-body">
            @if (session()->has('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session()->has('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="card border-0" style="background: #f8fafc;">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Name of the client or submitting authority</label>
                            <input type="text" class="form-control" wire:model.defer="form.client_or_authority_name" @if($readOnly) disabled @endif>
                            @error('form.client_or_authority_name')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Laboratory Identification Number / Lab. No. (Batch No)</label>
                            <input type="text" class="form-control" wire:model.defer="form.laboratory_identification_number" @if($readOnly) disabled @endif>
                            @error('form.laboratory_identification_number')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Description of sample(s)</label>
                            <textarea class="form-control" rows="3" wire:model.defer="form.sample_description" @if($readOnly) disabled @endif></textarea>
                            @error('form.sample_description')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Number of Samples</label>
                            <input type="number" min="0" class="form-control" wire:model.defer="form.number_of_samples" @if($readOnly) disabled @endif>
                            @error('form.number_of_samples')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <hr>
                    <h6 class="mb-3">Person Submitting the Sample or Exhibit</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" wire:model.defer="form.submitter_name" @if($readOnly) disabled @endif>
                            @error('form.submitter_name')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Designation</label>
                            <input type="text" class="form-control" wire:model.defer="form.submitter_designation" @if($readOnly) disabled @endif>
                            @error('form.submitter_designation')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <label class="form-label d-block">Signature</label>
                    <div class="bg-white border rounded p-2 mb-3" style="max-width: 560px;" wire:ignore>
                        <canvas id="submitter-signature-canvas" style="width: 100%; height: 160px; border: 1px dashed #cbd5e1;"></canvas>
                        <div class="d-flex mt-2" style="gap: 8px;">
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="submitter-sign-clear" @if($readOnly) disabled @endif>Clear</button>
                        </div>
                    </div>
                    <input type="hidden" id="submitter-signature-input" wire:model.defer="form.submitter_signature">
                    @error('form.submitter_signature')<div class="text-danger small mt-1">{{ $message }}</div>@enderror

                    <hr>
                    <h6 class="mb-3">Receiving Person</h6>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" wire:model.defer="form.receiver_name" @if($readOnly) disabled @endif>
                            @error('form.receiver_name')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Designation</label>
                            <input type="text" class="form-control" wire:model.defer="form.receiver_designation" @if($readOnly) disabled @endif>
                            @error('form.receiver_designation')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Sample receiving date</label>
                            <input type="date" class="form-control" wire:model.defer="form.sample_receiving_date" @if($readOnly) disabled @endif>
                            @error('form.sample_receiving_date')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <label class="form-label d-block">Signature</label>
                    <div class="bg-white border rounded p-2" style="max-width: 560px;" wire:ignore>
                        <canvas id="receiver-signature-canvas" style="width: 100%; height: 160px; border: 1px dashed #cbd5e1;"></canvas>
                        <div class="d-flex mt-2" style="gap: 8px;">
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="receiver-sign-clear" @if($readOnly) disabled @endif>Clear</button>
                        </div>
                    </div>
                    <input type="hidden" id="receiver-signature-input" wire:model.defer="form.receiver_signature">
                    @error('form.receiver_signature')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="d-flex justify-content-end mt-3" style="gap: 8px;">
                @if(!$readOnly)
                    <button type="button" class="btn btn-outline-primary btn-action-sm" wire:click="saveDraft">
                        <i class="mdi mdi-content-save-outline"></i> Save Draft
                    </button>
                    <button type="button" class="btn btn-success btn-action-sm" wire:click="submitForm">
                        <i class="mdi mdi-check-circle-outline"></i> Submit & Attach
                    </button>
                @endif
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
    <script>
        (function () {
            let submitterPad = null;
            let receiverPad = null;

            function setupPad(canvasId, inputId, clearBtnId, existingData) {
                const canvas = document.getElementById(canvasId);
                const input = document.getElementById(inputId);
                const clearBtn = document.getElementById(clearBtnId);
                if (!canvas || !input || !window.SignaturePad) {
                    return null;
                }

                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                const rect = canvas.getBoundingClientRect();
                const width = rect.width > 10 ? rect.width : 520;
                canvas.width = width * ratio;
                canvas.height = rect.height * ratio;
                canvas.getContext('2d').scale(ratio, ratio);

                const pad = new SignaturePad(canvas, { backgroundColor: 'rgb(255,255,255)' });

                if (existingData && existingData.startsWith('data:image')) {
                    pad.fromDataURL(existingData);
                }

                pad.addEventListener('endStroke', function () {
                    input.value = pad.isEmpty() ? '' : pad.toDataURL('image/png');
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                });

                if (clearBtn) {
                    clearBtn.addEventListener('click', function () {
                        pad.clear();
                        input.value = '';
                        input.dispatchEvent(new Event('input', { bubbles: true }));
                        input.dispatchEvent(new Event('change', { bubbles: true }));
                    });
                }

                return pad;
            }

            function initPads() {
                const submitterInput = document.getElementById('submitter-signature-input');
                const receiverInput = document.getElementById('receiver-signature-input');

                submitterPad = setupPad('submitter-signature-canvas', 'submitter-signature-input', 'submitter-sign-clear', submitterInput ? submitterInput.value : '');
                receiverPad = setupPad('receiver-signature-canvas', 'receiver-signature-input', 'receiver-sign-clear', receiverInput ? receiverInput.value : '');
            }

            document.addEventListener('livewire:navigated', initPads);
            document.addEventListener('livewire:initialized', initPads);
            document.addEventListener('DOMContentLoaded', initPads);
            document.addEventListener('shown.bs.tab', function (event) {
                if (event.target && event.target.id === 'sample-receipt-notification-tab') {
                    initPads();
                }
            });

            window.addEventListener('resize', function () {
                if (submitterPad || receiverPad) {
                    initPads();
                }
            });
        })();
    </script>
</div>
