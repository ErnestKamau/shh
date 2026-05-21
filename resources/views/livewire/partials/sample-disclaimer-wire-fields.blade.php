@php
    $wirePrefix = $wirePrefix ?? 'disclaimerForm.';
    $canvasPrefix = $canvasPrefix ?? 'acc-wizard-disclaimer';
    $readOnly = $readOnly ?? false;
    $claimantOnly = $claimantOnly ?? false;
    $disclaimantDisplay = $disclaimantDisplay ?? '';
    $claimantSignatureDisplay = $claimantSignatureDisplay ?? '';
    $analystSignatureDisplay = $analystSignatureDisplay ?? '';
@endphp

<div class="acc-disclaimer-form">
    @unless($claimantOnly)
    <div class="acc-disclaimer-header-grid">
        <div class="row acc-wizard-fields">
            <div class="col-md-6 form-group">
                <label class="acc-label">Name of client</label>
                <input type="text" class="form-control acc-input" wire:model.defer="{{ $wirePrefix }}client_name" @if($readOnly) disabled @endif>
                @error($wirePrefix.'client_name')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
            <div class="col-md-6 form-group">
                <label class="acc-label">LAB NO / Sample ID</label>
                <input type="text" class="form-control acc-input" wire:model.defer="{{ $wirePrefix }}lab_no" placeholder="Auto-generated after customer signs" @if($readOnly) disabled @endif>
                @error($wirePrefix.'lab_no')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
            <div class="col-md-6 form-group">
                <label class="acc-label">Date</label>
                <input type="date" class="form-control acc-input" wire:model.defer="{{ $wirePrefix }}date" @if($readOnly) disabled @endif>
                @error($wirePrefix.'date')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
            <div class="col-md-6 form-group">
                <label class="acc-label">Time</label>
                <input type="time" class="form-control acc-input" wire:model.defer="{{ $wirePrefix }}time" @if($readOnly) disabled @endif>
                @error($wirePrefix.'time')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
            <div class="col-md-8 form-group">
                <label class="acc-label">Types of sample</label>
                <input type="text" class="form-control acc-input" wire:model.defer="{{ $wirePrefix }}sample_types" @if($readOnly) disabled @endif>
                @error($wirePrefix.'sample_types')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
            <div class="col-md-4 form-group">
                <label class="acc-label">Number of sample</label>
                <input type="number" min="1" class="form-control acc-input" wire:model.defer="{{ $wirePrefix }}number_of_samples" @if($readOnly) disabled @endif>
                @error($wirePrefix.'number_of_samples')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
        </div>
    </div>

    @endunless

    <div class="acc-disclaimer-legal acc-cert-card">
        <p class="acc-disclaimer-legal-text mb-2">
            {{ \App\Services\Sampleworkflow\SampleReceivingDisclaimerService::INTEGRITY_STATEMENT_PREFIX }}
            @if($claimantOnly)
                <strong>{{ $disclaimantDisplay }}</strong>
            @elseif($readOnly)
                <strong>{{ $disclaimantDisplay }}</strong>
            @else
            <input
                type="text"
                class="acc-disclaimer-inline-name form-control form-control-sm d-inline-block"
                wire:model.defer="{{ $wirePrefix }}disclaimant_name"
                placeholder="Name"
                @if($readOnly) disabled @endif
            >
            @endif
            {{ \App\Services\Sampleworkflow\SampleReceivingDisclaimerService::INTEGRITY_STATEMENT_SUFFIX }}
        </p>
        @error($wirePrefix.'disclaimant_name')<small class="text-danger d-block">{{ $message }}</small>@enderror
    </div>

    <div class="acc-disclaimer-signatures">
        <h6 class="acc-wizard-section-title">Claimant</h6>
        @unless($claimantOnly)
        <p class="acc-wizard-hint">Optional here if the customer will sign on the portal. Required before the report is issued if not captured in the lab.</p>
        @endunless
        <div class="row acc-wizard-fields">
            <div class="col-md-6 form-group">
                <label class="acc-label">Name</label>
                <input type="text" class="form-control acc-input" wire:model.defer="{{ $wirePrefix }}claimant_name" @if($readOnly) disabled @endif>
                @error($wirePrefix.'claimant_name')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
            <div class="col-md-6 form-group">
                <label class="acc-label">Date</label>
                <input type="date" class="form-control acc-input" wire:model.defer="{{ $wirePrefix }}claimant_signed_at" @if($readOnly) disabled @endif>
                @error($wirePrefix.'claimant_signed_at')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
        </div>
        <label class="acc-label d-block">Signature</label>
        @if($readOnly && !empty($claimantSignatureDisplay))
            <div class="bg-white border rounded p-2" style="max-width: 560px;">
                <img src="{{ $claimantSignatureDisplay }}" alt="Claimant signature" style="max-width: 100%; max-height: 140px;">
            </div>
        @else
        <div class="acc-signature-pad bg-white border rounded p-2" style="max-width: 560px;" wire:ignore>
            <canvas id="{{ $canvasPrefix }}-claimant-canvas" style="width: 100%; height: 140px; border: 1px dashed #cbd5e1;"></canvas>
            <div class="d-flex mt-2" style="gap: 8px;">
                <button type="button" class="btn btn-sm btn-outline-secondary" id="{{ $canvasPrefix }}-claimant-clear" @if($readOnly) disabled @endif>Clear</button>
            </div>
        </div>
        <input type="hidden" id="{{ $canvasPrefix }}-claimant-input" wire:model.defer="{{ $wirePrefix }}claimant_signature">
        @endif
        @error($wirePrefix.'claimant_signature')<small class="text-danger d-block mt-1">{{ $message }}</small>@enderror

        @unless($claimantOnly)
        <hr class="my-4">

        <h6 class="acc-wizard-section-title">Laboratory analyst</h6>
        <div class="row acc-wizard-fields">
            <div class="col-md-6 form-group">
                <label class="acc-label">Laboratory analyst</label>
                <input type="text" class="form-control acc-input" wire:model.defer="{{ $wirePrefix }}analyst_name" @if($readOnly) disabled @endif>
                @error($wirePrefix.'analyst_name')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
            <div class="col-md-6 form-group">
                <label class="acc-label">Date</label>
                <input type="date" class="form-control acc-input" wire:model.defer="{{ $wirePrefix }}analyst_signed_at" @if($readOnly) disabled @endif>
                @error($wirePrefix.'analyst_signed_at')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
        </div>
        <label class="acc-label d-block">Signature</label>
        @if($readOnly && !empty($analystSignatureDisplay))
            <div class="bg-white border rounded p-2" style="max-width: 560px;">
                <img src="{{ $analystSignatureDisplay }}" alt="Analyst signature" style="max-width: 100%; max-height: 140px;">
            </div>
        @else
        <div class="acc-signature-pad bg-white border rounded p-2" style="max-width: 560px;" wire:ignore>
            <canvas id="{{ $canvasPrefix }}-analyst-canvas" style="width: 100%; height: 140px; border: 1px dashed #cbd5e1;"></canvas>
            <div class="d-flex mt-2" style="gap: 8px;">
                <button type="button" class="btn btn-sm btn-outline-secondary" id="{{ $canvasPrefix }}-analyst-clear" @if($readOnly) disabled @endif>Clear</button>
            </div>
        </div>
        <input type="hidden" id="{{ $canvasPrefix }}-analyst-input" wire:model.defer="{{ $wirePrefix }}analyst_signature">
        @endif
        @error($wirePrefix.'analyst_signature')<small class="text-danger d-block mt-1">{{ $message }}</small>@enderror
        @endunless
    </div>

    <div class="acc-disclaimer-footer-note">
        <i class="mdi mdi-information-outline"></i>
        <span>{{ \App\Services\Sampleworkflow\SampleReceivingDisclaimerService::COA_FOOTER_NOTE }}</span>
    </div>
</div>
