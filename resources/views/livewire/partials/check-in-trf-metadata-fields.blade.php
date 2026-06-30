@php
    $instanceId = $instanceId ?? '';
    $canvasId = 'checkin-trf-sig-'.$instanceId;
@endphp
<div
    class="check-in-trf-metadata"
    x-data="{ open: true }"
    wire:key="checkin-trf-{{ $instanceId }}"
>
    <button
        type="button"
        class="btn btn-link btn-sm p-0 mb-2 font-weight-bold text-dark text-decoration-none d-flex align-items-center w-100 justify-content-between"
        @click="open = !open; $nextTick(() => { if (typeof window.initTrfSignaturePads === 'function') window.initTrfSignaturePads(true); })"
    >
        <span><i class="mdi mdi-draw-pen mr-1"></i> Signatures & conformity</span>
        <i class="mdi" :class="open ? 'mdi-chevron-down' : 'mdi-chevron-right'"></i>
    </button>

    <div x-show="open" x-collapse>
        <div class="row small">
            <div class="col-md-6 form-group">
                <label class="font-weight-bold">Statement of Conformity Required in Reports</label>
                <select class="form-control form-control-sm" wire:model="checkInTrfFields.{{ $instanceId }}.statement_of_conformity">
                    <option value="">Select option</option>
                    <option value="YES">YES</option>
                    <option value="No">No</option>
                    <option value="As per Contract">As per Contract</option>
                    <option value="As per Email">As per Email</option>
                </select>
            </div>
            <div class="col-md-6 form-group">
                <label class="font-weight-bold">Sampled By: Name and Employee ID</label>
                <input type="text" class="form-control form-control-sm" wire:model="checkInTrfFields.{{ $instanceId }}.sampled_by" placeholder="Name and employee ID">
            </div>
            <div class="col-md-6 form-group">
                <label class="font-weight-bold">Customer Representative Contact Number</label>
                <input type="text" class="form-control form-control-sm" wire:model="checkInTrfFields.{{ $instanceId }}.customer_rep_contact" placeholder="Contact number">
            </div>
            <div class="col-md-6 form-group">
                <label class="font-weight-bold">Remarks</label>
                <textarea class="form-control form-control-sm" rows="2" wire:model="checkInTrfFields.{{ $instanceId }}.remarks" placeholder="Remarks"></textarea>
            </div>
            <div class="col-12 form-group mb-0">
                <label class="font-weight-bold d-block">Customer Representative Name/Sign.</label>
                <div class="acc-signature-pad trf-signature-pad" wire:ignore>
                    <canvas
                        id="{{ $canvasId }}-canvas"
                        class="trf-signature-canvas checkin-trf-signature-canvas"
                        data-field="checkInTrfFields_{{ $instanceId }}_customer_rep_signature"
                        data-livewire-model="checkInTrfFields.{{ $instanceId }}.customer_rep_signature"
                        style="width: 100%; height: 120px; touch-action: none;"
                    ></canvas>
                    <div class="acc-signature-actions">
                        <button type="button" class="btn btn-sm btn-outline-secondary trf-signature-clear" data-canvas="{{ $canvasId }}-canvas" data-input="field_checkInTrfFields_{{ $instanceId }}_customer_rep_signature">Clear</button>
                    </div>
                </div>
                <input type="hidden" id="field_checkInTrfFields_{{ $instanceId }}_customer_rep_signature" wire:model="checkInTrfFields.{{ $instanceId }}.customer_rep_signature">
            </div>
        </div>
    </div>

    <div
        class="check-in-trf-collection-extras mt-3"
        x-data="{ openExtras: false }"
        wire:key="checkin-trf-extras-{{ $instanceId }}"
    >
        <button
            type="button"
            class="btn btn-link btn-sm p-0 mb-2 font-weight-bold text-dark text-decoration-none d-flex align-items-center w-100 justify-content-between"
            @click="openExtras = !openExtras"
        >
            <span><i class="mdi mdi-package-variant-closed mr-1"></i> Sample collection — shipment details</span>
            <i class="mdi" :class="openExtras ? 'mdi-chevron-down' : 'mdi-chevron-right'"></i>
        </button>

        <div x-show="openExtras" x-collapse>
            <p class="text-muted small mb-2">Additional shipment details (applicable where relevant).</p>
            <div class="row small">
                @foreach (config('test_request_form_fields.collection_extra_fields', []) as $extraField)
                    <div class="col-md-6 form-group">
                        <label class="font-weight-bold">{{ $extraField['label'] }}</label>
                        @if (($extraField['type'] ?? 'text') === 'date')
                            <input
                                type="date"
                                class="form-control form-control-sm"
                                wire:model="checkInTrfFields.{{ $instanceId }}.{{ $extraField['name'] }}"
                            >
                        @else
                            <input
                                type="text"
                                class="form-control form-control-sm"
                                wire:model="checkInTrfFields.{{ $instanceId }}.{{ $extraField['name'] }}"
                            >
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
