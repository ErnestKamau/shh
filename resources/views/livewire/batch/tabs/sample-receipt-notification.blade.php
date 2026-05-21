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

            @include('livewire.partials.receipt-notification-wire-fields', [
                'wirePrefix' => 'form.',
                'canvasPrefix' => 'batch-receipt',
                'readOnly' => $readOnly,
                'partLabel' => null,
                'showSubmitterSection' => $readOnly,
                'showSubmitterSigningNotice' => ! $readOnly,
            ])

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
                const submitterCanvas = document.getElementById('batch-receipt-submitter-canvas');
                const receiverInput = document.getElementById('batch-receipt-receiver-input');

                if (submitterCanvas) {
                    const submitterInput = document.getElementById('batch-receipt-submitter-input');
                    submitterPad = setupPad('batch-receipt-submitter-canvas', 'batch-receipt-submitter-input', 'batch-receipt-submitter-clear', submitterInput ? submitterInput.value : '');
                }

                receiverPad = setupPad('batch-receipt-receiver-canvas', 'batch-receipt-receiver-input', 'batch-receipt-receiver-clear', receiverInput ? receiverInput.value : '');
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
