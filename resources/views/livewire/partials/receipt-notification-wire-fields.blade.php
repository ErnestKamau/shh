@php
    $wirePrefix = $wirePrefix ?? 'receiptForm.';
    $canvasPrefix = $canvasPrefix ?? 'lab-receipt';
    $readOnly = $readOnly ?? false;
    $partLabel = $partLabel ?? null;
@endphp

<div class="card border-0" style="background: #f8fafc;">
    <div class="card-body">
        <h6 class="mb-3">{{ ($partLabel ?? null) ?: 'Sample Receipt Notification (GCLA 01)' }}</h6>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Name of the client or submitting authority</label>
                <input type="text" class="form-control" wire:model.defer="{{ $wirePrefix }}client_or_authority_name" @if($readOnly) disabled @endif>
                @error($wirePrefix.'client_or_authority_name')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Laboratory Identification Number / Lab. No. (Batch No)</label>
                <input type="text" class="form-control" wire:model.defer="{{ $wirePrefix }}laboratory_identification_number" @if($readOnly) disabled @endif>
                @error($wirePrefix.'laboratory_identification_number')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="row">
            <div class="col-md-8 mb-3">
                <label class="form-label">Description of sample(s)</label>
                <textarea class="form-control" rows="3" wire:model.defer="{{ $wirePrefix }}sample_description" @if($readOnly) disabled @endif></textarea>
                @error($wirePrefix.'sample_description')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Number of Samples</label>
                <input type="number" min="0" class="form-control" wire:model.defer="{{ $wirePrefix }}number_of_samples" @if($readOnly) disabled @endif>
                @error($wirePrefix.'number_of_samples')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
        </div>

        <hr>
        <h6 class="mb-3">Person Submitting the Sample or Exhibit</h6>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Name</label>
                <input type="text" class="form-control" wire:model.defer="{{ $wirePrefix }}submitter_name" @if($readOnly) disabled @endif>
                @error($wirePrefix.'submitter_name')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Designation</label>
                <input type="text" class="form-control" wire:model.defer="{{ $wirePrefix }}submitter_designation" @if($readOnly) disabled @endif>
                @error($wirePrefix.'submitter_designation')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
        </div>

        <label class="form-label d-block">Signature</label>
        <div class="bg-white border rounded p-2 mb-3" style="max-width: 560px;" wire:ignore>
            <canvas id="{{ $canvasPrefix }}-submitter-canvas" style="width: 100%; height: 160px; border: 1px dashed #cbd5e1;"></canvas>
            <div class="d-flex mt-2" style="gap: 8px;">
                <button type="button" class="btn btn-sm btn-outline-secondary" id="{{ $canvasPrefix }}-submitter-clear" @if($readOnly) disabled @endif>Clear</button>
            </div>
        </div>
        <input type="hidden" id="{{ $canvasPrefix }}-submitter-input" wire:model.defer="{{ $wirePrefix }}submitter_signature">
        @error($wirePrefix.'submitter_signature')<div class="text-danger small mt-1">{{ $message }}</div>@enderror

        <hr>
        <h6 class="mb-3">Receiving Person</h6>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label">Name</label>
                <input type="text" class="form-control" wire:model.defer="{{ $wirePrefix }}receiver_name" @if($readOnly) disabled @endif>
                @error($wirePrefix.'receiver_name')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Designation</label>
                <input type="text" class="form-control" wire:model.defer="{{ $wirePrefix }}receiver_designation" @if($readOnly) disabled @endif>
                @error($wirePrefix.'receiver_designation')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Sample receiving date</label>
                <input type="date" class="form-control" wire:model.defer="{{ $wirePrefix }}sample_receiving_date" @if($readOnly) disabled @endif>
                @error($wirePrefix.'sample_receiving_date')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
        </div>

        <label class="form-label d-block">Signature</label>
        <div class="bg-white border rounded p-2" style="max-width: 560px;" wire:ignore>
            <canvas id="{{ $canvasPrefix }}-receiver-canvas" style="width: 100%; height: 160px; border: 1px dashed #cbd5e1;"></canvas>
            <div class="d-flex mt-2" style="gap: 8px;">
                <button type="button" class="btn btn-sm btn-outline-secondary" id="{{ $canvasPrefix }}-receiver-clear" @if($readOnly) disabled @endif>Clear</button>
            </div>
        </div>
        <input type="hidden" id="{{ $canvasPrefix }}-receiver-input" wire:model.defer="{{ $wirePrefix }}receiver_signature">
        @error($wirePrefix.'receiver_signature')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    </div>
</div>
