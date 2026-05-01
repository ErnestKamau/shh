<div>
    <div class="workflow-board-panel">
        <div class="workflow-board-panel-header d-flex align-items-center justify-content-between flex-wrap" style="gap: 8px;">
            <h5><i class="mdi mdi-file-document-edit-outline"></i> Laboratory Analysis Acceptance Form (GCLA/F/03)</h5>
            <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
                @if($attachmentUrl)
                    <a href="{{ $attachmentUrl }}" target="_blank" class="btn btn-outline-secondary btn-action-sm">
                        <i class="mdi mdi-eye"></i> View Latest Attachment
                    </a>
                @endif
                <span class="badge badge-info">Part {{ $currentPart }} of 4</span>
            </div>
        </div>

        <div class="workflow-board-panel-body">
            @if (session()->has('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session()->has('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="d-flex flex-wrap mb-3" style="gap: 6px;">
                <button type="button" class="btn btn-sm {{ $currentPart === 1 ? 'btn-primary' : 'btn-outline-primary' }}" wire:click="setPart(1)">Part A</button>
                <button type="button" class="btn btn-sm {{ $currentPart === 2 ? 'btn-primary' : 'btn-outline-primary' }}" wire:click="setPart(2)">Part B</button>
                <button type="button" class="btn btn-sm {{ $currentPart === 3 ? 'btn-primary' : 'btn-outline-primary' }}" wire:click="setPart(3)">Part C</button>
                <button type="button" class="btn btn-sm {{ $currentPart === 4 ? 'btn-primary' : 'btn-outline-primary' }}" wire:click="setPart(4)">Part D</button>
            </div>

            @if($currentPart === 1)
                <div class="card border-0" style="background: #f8fafc;">
                    <div class="card-body">
                        <h6 class="mb-3">Part A: Sample Details</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Name of Customer</label>
                                <input type="text" class="form-control" wire:model.defer="form.customer_name" @if($readOnly) disabled @endif>
                                @error('form.customer_name')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Address</label>
                                <input type="text" class="form-control" wire:model.defer="form.customer_address" @if($readOnly) disabled @endif>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" wire:model.defer="form.customer_email" @if($readOnly) disabled @endif>
                                @error('form.customer_email')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Number of Samples</label>
                                <input type="number" class="form-control" wire:model.defer="form.number_of_samples" min="0" @if($readOnly) disabled @endif>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Type of Samples</label>
                                <input type="text" class="form-control" wire:model.defer="form.type_of_samples" @if($readOnly) disabled @endif>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Date of Sampling (if applicable)</label>
                                <input type="date" class="form-control" wire:model.defer="form.date_of_sampling" @if($readOnly) disabled @endif>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Date</label>
                                <input type="date" class="form-control" wire:model.defer="form.date" @if($readOnly) disabled @endif>
                                @error('form.date')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Tel</label>
                                <input type="text" class="form-control" wire:model.defer="form.tel" @if($readOnly) disabled @endif>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label d-block">Mode of Work</label>
                            <div class="d-flex" style="gap: 18px;">
                                <label class="mb-0"><input type="radio" wire:model.defer="form.mode_of_work" value="Normal" @if($readOnly) disabled @endif> Normal</label>
                                <label class="mb-0"><input type="radio" wire:model.defer="form.mode_of_work" value="Express" @if($readOnly) disabled @endif> Express</label>
                            </div>
                            @error('form.mode_of_work')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead>
                                    <tr>
                                        <th style="width: 32px;"></th>
                                        <th>Parameter(s) Requested</th>
                                        <th style="width: 180px;">Accepted</th>
                                        <th style="width: 180px;">Rejected</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($parameters as $idx => $row)
                                        <tr>
                                            <td>
                                                <input type="checkbox" wire:model.live="parameters.{{ $idx }}.selected" @if($readOnly) disabled @endif>
                                            </td>
                                            <td>{{ $row['label'] }}</td>
                                            <td>
                                                @if(!empty($row['selected']))
                                                    TZS {{ number_format((float) ($row['price'] ?? 0), 2) }}
                                                @endif
                                            </td>
                                            <td>
                                                @if(empty($row['selected']))
                                                    TZS {{ number_format((float) ($row['price'] ?? 0), 2) }}
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">No requested parameters found for this batch.</td>
                                        </tr>
                                    @endforelse
                                    <tr>
                                        <td colspan="2" class="text-right"><strong>Total Accepted</strong></td>
                                        <td><strong>TZS {{ number_format((float) $this->acceptedTotal, 2) }}</strong></td>
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            <label class="form-label d-block">Any deviation from specified conditions?</label>
                            <div class="d-flex" style="gap: 18px;">
                                <label class="mb-0"><input type="radio" wire:model.defer="form.deviation_answer" value="Yes" @if($readOnly) disabled @endif> Yes</label>
                                <label class="mb-0"><input type="radio" wire:model.defer="form.deviation_answer" value="No" @if($readOnly) disabled @endif> No</label>
                            </div>
                            @error('form.deviation_answer')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            @endif

            @if($currentPart === 2)
                <div class="card border-0" style="background: #f8fafc;">
                    <div class="card-body">
                        <h6 class="mb-3">Part B: Customer Certified</h6>
                        <div class="mb-2">{{ $form['customer_certification_text'] }}</div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Customer Name</label>
                                <input type="text" class="form-control" wire:model.defer="form.customer_name_certified" @if($readOnly) disabled @endif>
                                @error('form.customer_name_certified')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Date</label>
                                <input type="date" class="form-control" wire:model.defer="form.customer_date" @if($readOnly) disabled @endif>
                                @error('form.customer_date')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <label class="form-label d-block">Customer Signature</label>
                        <div class="bg-white border rounded p-2" style="max-width: 560px;" wire:ignore>
                            <canvas id="customer-signature-canvas" style="width: 100%; height: 160px; border: 1px dashed #cbd5e1;"></canvas>
                            <div class="d-flex mt-2" style="gap: 8px;">
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="customer-sign-clear" @if($readOnly) disabled @endif>Clear</button>
                            </div>
                        </div>
                        <input type="hidden" id="customer-signature-input" wire:model.defer="form.customer_signature">
                        @error('form.customer_signature')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                </div>
            @endif

            @if($currentPart === 3)
                <div class="card border-0" style="background: #f8fafc;">
                    <div class="card-body">
                        <h6 class="mb-3">Part C: Conformity Assessment</h6>
                        <label class="form-label d-block">Customer requests a statement of conformity to specification/standard</label>
                        <div class="d-flex" style="gap: 18px;">
                            <label class="mb-0"><input type="radio" wire:model.defer="form.conformity_request" value="requested" @if($readOnly) disabled @endif> Requested</label>
                            <label class="mb-0"><input type="radio" wire:model.defer="form.conformity_request" value="not_requested" @if($readOnly) disabled @endif> Not requested</label>
                        </div>
                        @error('form.conformity_request')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                </div>
            @endif

            @if($currentPart === 4)
                <div class="card border-0" style="background: #f8fafc;">
                    <div class="card-body">
                        <h6 class="mb-3">Part D: Laboratory Manager</h6>

                        <label class="form-label d-block">I certify that the laboratory has/has not capability and resources to meet customer requirements</label>
                        <div class="d-flex mb-3" style="gap: 18px;">
                            <label class="mb-0"><input type="radio" wire:model.defer="form.manager_capability" value="has" @if($readOnly) disabled @endif> Has</label>
                            <label class="mb-0"><input type="radio" wire:model.defer="form.manager_capability" value="has_not" @if($readOnly) disabled @endif> Has not</label>
                        </div>
                        @error('form.manager_capability')<div class="text-danger small">{{ $message }}</div>@enderror

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Laboratory</label>
                                <input type="text" class="form-control" wire:model.defer="form.laboratory_name" @if($readOnly) disabled @endif>
                                @error('form.laboratory_name')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Laboratory Manager Name</label>
                                <input type="text" class="form-control" wire:model.defer="form.laboratory_manager_name" @if($readOnly) disabled @endif>
                                @error('form.laboratory_manager_name')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Date</label>
                                <input type="date" class="form-control" wire:model.defer="form.manager_date" @if($readOnly) disabled @endif>
                                @error('form.manager_date')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <label class="form-label d-block">Manager Signature</label>
                        <div class="bg-white border rounded p-2" style="max-width: 560px;" wire:ignore>
                            <canvas id="manager-signature-canvas" style="width: 100%; height: 160px; border: 1px dashed #cbd5e1;"></canvas>
                            <div class="d-flex mt-2" style="gap: 8px;">
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="manager-sign-clear" @if($readOnly) disabled @endif>Clear</button>
                            </div>
                        </div>
                        <input type="hidden" id="manager-signature-input" wire:model.defer="form.manager_signature">
                        @error('form.manager_signature')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                </div>
            @endif

            <div class="d-flex justify-content-between mt-3" style="gap: 8px;">
                <div>
                    @if($currentPart > 1)
                        <button type="button" class="btn btn-outline-secondary btn-action-sm" wire:click="previousPart">Previous</button>
                    @endif
                </div>
                <div class="d-flex" style="gap: 8px;">
                    @if(!$readOnly)
                        <button type="button" class="btn btn-outline-primary btn-action-sm" wire:click="saveDraft">
                            <i class="mdi mdi-content-save-outline"></i> Save Draft
                        </button>
                    @endif
                    @if($currentPart < 4)
                        <button type="button" class="btn btn-primary btn-action-sm" wire:click="nextPart">Next</button>
                    @else
                        @if(!$readOnly)
                            <button type="button" class="btn btn-success btn-action-sm" wire:click="submitForm">
                                <i class="mdi mdi-check-circle-outline"></i> Submit & Attach
                            </button>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
    <script>
        (function () {
            let customerPad = null;
            let managerPad = null;

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
                const customerInput = document.getElementById('customer-signature-input');
                const managerInput = document.getElementById('manager-signature-input');

                customerPad = setupPad('customer-signature-canvas', 'customer-signature-input', 'customer-sign-clear', customerInput ? customerInput.value : '');
                managerPad = setupPad('manager-signature-canvas', 'manager-signature-input', 'manager-sign-clear', managerInput ? managerInput.value : '');
            }

            document.addEventListener('livewire:navigated', initPads);
            document.addEventListener('livewire:initialized', initPads);
            document.addEventListener('DOMContentLoaded', initPads);
            document.addEventListener('shown.bs.tab', function (event) {
                if (event.target && event.target.id === 'laboratory-acceptance-tab') {
                    initPads();
                }
            });

            window.addEventListener('resize', function () {
                if (customerPad || managerPad) {
                    initPads();
                }
            });
        })();
    </script>
</div>
