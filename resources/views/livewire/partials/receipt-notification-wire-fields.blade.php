@php
    $wirePrefix = $wirePrefix ?? 'receiptForm.';
    $canvasPrefix = $canvasPrefix ?? 'lab-receipt';
    $readOnly = $readOnly ?? false;
    $partLabel = $partLabel ?? null;
    $showSubmitterSection = $showSubmitterSection ?? false;
    $submitterReadOnly = $submitterReadOnly ?? $readOnly;
    $showSubmitterSigningNotice = $showSubmitterSigningNotice ?? ! $showSubmitterSection;
    $mode = $mode ?? 'wire';
    $isStatic = $mode === 'static';
    $staticNamePrefix = $staticNamePrefix ?? 'receipt_notification';
@endphp

@once
    <style>
        .receipt-notif-form {
            --rn-accent: #3b5fc0;
            --rn-accent-soft: #eef2ff;
            --rn-border: #e2e8f0;
            --rn-muted: #64748b;
            --rn-text: #0f172a;
            --rn-bg: #ffffff;
            --rn-surface: #f8fafc;
        }

        .receipt-notif-form__card {
            background: var(--rn-bg);
            border: 1px solid var(--rn-border);
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
        }

        .receipt-notif-form__header {
            padding: 1rem 1.25rem;
            background: linear-gradient(180deg, #fff 0%, var(--rn-surface) 100%);
            border-bottom: 1px solid var(--rn-border);
        }

        .receipt-notif-form__title {
            margin: 0;
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: var(--rn-muted);
        }

        .receipt-notif-form__body {
            padding: 1.15rem 1.25rem 1.25rem;
        }

        .receipt-notif-form__section-title {
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: var(--rn-muted);
            margin: 0 0 0.85rem;
        }

        .receipt-notif-form__label {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--rn-muted);
            margin-bottom: 0.35rem;
        }

        .receipt-notif-form .form-control {
            border-radius: 8px;
            border-color: var(--rn-border);
            font-size: 0.9rem;
            transition: border-color 0.15s, box-shadow 0.15s;
        }

        .receipt-notif-form .form-control:focus {
            border-color: var(--rn-accent);
            box-shadow: 0 0 0 3px rgba(59, 95, 192, 0.12);
        }

        .receipt-notif-form__divider {
            border: none;
            border-top: 1px solid var(--rn-border);
            margin: 1.25rem 0;
        }

        .receipt-notif-form__notice {
            display: flex;
            align-items: flex-start;
            gap: 0.65rem;
            padding: 0.85rem 1rem;
            background: var(--rn-accent-soft);
            border: 1px solid #c7d2fe;
            border-radius: 10px;
            color: #1e3a8a;
            font-size: 0.875rem;
            line-height: 1.45;
            margin-bottom: 0;
        }

        .receipt-notif-form__notice i {
            font-size: 1.15rem;
            flex-shrink: 0;
            margin-top: 0.1rem;
            opacity: 0.9;
        }

        .receipt-notif-form__sig-pad {
            background: #fff;
            border: 1px dashed #cbd5e1;
            border-radius: 10px;
            padding: 0.5rem;
            width: 100%;
            max-width: 560px;
        }

        .receipt-notif-form__sig-pad canvas {
            width: 100%;
            height: 160px;
            display: block;
            touch-action: none;
            border-radius: 6px;
        }

        .receipt-notif-form__receiver-sig {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            margin-top: 0.25rem;
        }

        .receipt-notif-form__receiver-sig .receipt-notif-form__label {
            width: 100%;
            max-width: 560px;
            text-align: left;
        }

        .receipt-notif-form__sig-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 0.5rem;
            gap: 0.5rem;
        }

        .receipt-notif-form__sig-actions .btn {
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 600;
        }
    </style>
@endonce

<div class="receipt-notif-form">
    <div class="receipt-notif-form__card">
        @if($partLabel)
            <div class="receipt-notif-form__header">
                <h6 class="receipt-notif-form__title">{{ $partLabel }}</h6>
            </div>
        @endif
        <div class="receipt-notif-form__body">
            @if($partLabel === null)
                <h6 class="receipt-notif-form__title mb-3">{{ 'Sample Receipt Notification (GCLA 01)' }}</h6>
            @endif

            <div class="row">
                <div class="col-md-8 mb-3">
                    <label class="receipt-notif-form__label">Name of the client or submitting authority</label>
                    @if($isStatic)
                        <input type="text" class="form-control" name="{{ $staticNamePrefix }}[client_or_authority_name]">
                    @else
                        <input type="text" class="form-control" wire:model.live="{{ $wirePrefix }}client_or_authority_name" @if($readOnly) disabled @endif>
                        @error($wirePrefix.'client_or_authority_name')<div class="text-danger small">{{ $message }}</div>@enderror
                    @endif
                </div>
                <div class="col-md-4 mb-3">
                    <label class="receipt-notif-form__label">Number of Samples</label>
                    @if($isStatic)
                        <input type="number" min="0" class="form-control" name="{{ $staticNamePrefix }}[number_of_samples]">
                    @else
                        <input type="number" min="0" class="form-control" wire:model.live="{{ $wirePrefix }}number_of_samples" @if($readOnly) disabled @endif>
                        @error($wirePrefix.'number_of_samples')<div class="text-danger small">{{ $message }}</div>@enderror
                    @endif
                </div>
            </div>

            <div class="row">
                <div class="col-12 mb-3">
                    <label class="receipt-notif-form__label">Laboratory Identification Number / Lab. No. (Batch No)</label>
                    @if($isStatic)
                        <input type="text" class="form-control" name="{{ $staticNamePrefix }}[laboratory_identification_number]" placeholder="Auto-generated on approval" readonly>
                    @else
                        <input
                            type="text"
                            class="form-control"
                            wire:model.live="{{ $wirePrefix }}laboratory_identification_number"
                            placeholder="{{ $labNumberPlaceholder ?? 'Request reference until lab batch number is assigned' }}"
                            @if($readOnly) disabled @endif
                        >
                        @if($showLabNumberPendingNote ?? false)
                            <small class="text-muted d-block mt-1">Shows the request reference until the lab batch number is generated after customer signs.</small>
                        @endif
                        @error($wirePrefix.'laboratory_identification_number')<div class="text-danger small">{{ $message }}</div>@enderror
                    @endif
                </div>
            </div>

            <div class="row">
                <div class="col-12 mb-3">
                    <label class="receipt-notif-form__label">Description of sample(s)</label>
                    @if($isStatic)
                        <textarea class="form-control" rows="3" name="{{ $staticNamePrefix }}[sample_description]"></textarea>
                    @else
                        <textarea class="form-control" rows="3" wire:model.live="{{ $wirePrefix }}sample_description" @if($readOnly) disabled @endif></textarea>
                        @error($wirePrefix.'sample_description')<div class="text-danger small">{{ $message }}</div>@enderror
                    @endif
                </div>
            </div>

            @if($showSubmitterSigningNotice)
                <hr class="receipt-notif-form__divider">
                <div class="receipt-notif-form__notice" role="status">
                    <i class="mdi mdi-information-outline" aria-hidden="true"></i>
                    <span>The personnel submitting information will be filled during signing of the document.</span>
                </div>
            @endif

            @if($showSubmitterSection)
                <hr class="receipt-notif-form__divider">
                <h6 class="receipt-notif-form__section-title">Person Submitting the Sample or Exhibit</h6>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="receipt-notif-form__label">Name</label>
                        @if($isStatic)
                            <input type="text" class="form-control" name="{{ $staticNamePrefix }}[submitter_name]">
                        @else
                            <input type="text" class="form-control" wire:model.live="{{ $wirePrefix }}submitter_name" @if($submitterReadOnly) disabled @endif>
                            @error($wirePrefix.'submitter_name')<div class="text-danger small">{{ $message }}</div>@enderror
                        @endif
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="receipt-notif-form__label">Designation</label>
                        @if($isStatic)
                            <input type="text" class="form-control" name="{{ $staticNamePrefix }}[submitter_designation]">
                        @else
                            <input type="text" class="form-control" wire:model.live="{{ $wirePrefix }}submitter_designation" @if($submitterReadOnly) disabled @endif>
                            @error($wirePrefix.'submitter_designation')<div class="text-danger small">{{ $message }}</div>@enderror
                        @endif
                    </div>
                </div>

                <label class="receipt-notif-form__label d-block">Signature</label>
                <div class="receipt-notif-form__sig-pad mb-3" @if(! $isStatic) wire:ignore @endif>
                    <canvas id="{{ $canvasPrefix }}-submitter-canvas"></canvas>
                    <div class="receipt-notif-form__sig-actions">
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="{{ $canvasPrefix }}-submitter-clear" @if($submitterReadOnly || $isStatic) disabled @endif>Clear</button>
                    </div>
                </div>
                @if($isStatic)
                    <input type="hidden" id="{{ $canvasPrefix }}-submitter-input" name="{{ $staticNamePrefix }}[submitter_signature]">
                @else
                    <input type="hidden" id="{{ $canvasPrefix }}-submitter-input" wire:model.defer="{{ $wirePrefix }}submitter_signature">
                    @error($wirePrefix.'submitter_signature')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                @endif
            @endif

            <hr class="receipt-notif-form__divider">
            <h6 class="receipt-notif-form__section-title">Receiving Person</h6>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="receipt-notif-form__label">Name</label>
                    @if($isStatic)
                        <input type="text" class="form-control" name="{{ $staticNamePrefix }}[receiver_name]">
                    @else
                        <input type="text" class="form-control" wire:model.live="{{ $wirePrefix }}receiver_name" @if($readOnly) disabled @endif>
                        @error($wirePrefix.'receiver_name')<div class="text-danger small">{{ $message }}</div>@enderror
                    @endif
                </div>
                <div class="col-md-4 mb-3">
                    <label class="receipt-notif-form__label">Designation</label>
                    @if($isStatic)
                        <input type="text" class="form-control" name="{{ $staticNamePrefix }}[receiver_designation]">
                    @else
                        <input type="text" class="form-control" wire:model.live="{{ $wirePrefix }}receiver_designation" @if($readOnly) disabled @endif>
                        @error($wirePrefix.'receiver_designation')<div class="text-danger small">{{ $message }}</div>@enderror
                    @endif
                </div>
                <div class="col-md-4 mb-3">
                    <label class="receipt-notif-form__label">Sample receiving date</label>
                    @if($isStatic)
                        <input type="date" class="form-control" name="{{ $staticNamePrefix }}[sample_receiving_date]" value="{{ now()->format('Y-m-d') }}">
                    @else
                        <input type="date" class="form-control" wire:model.live="{{ $wirePrefix }}sample_receiving_date" @if($readOnly) disabled @endif>
                        @error($wirePrefix.'sample_receiving_date')<div class="text-danger small">{{ $message }}</div>@enderror
                    @endif
                </div>
            </div>

            <div class="receipt-notif-form__receiver-sig">
                <label class="receipt-notif-form__label d-block">Signature</label>
                <div class="receipt-notif-form__sig-pad" @if(! $isStatic) wire:ignore @endif>
                    <canvas id="{{ $canvasPrefix }}-receiver-canvas"></canvas>
                    <div class="receipt-notif-form__sig-actions">
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="{{ $canvasPrefix }}-receiver-clear" @if($readOnly && ! $isStatic) disabled @endif>Clear</button>
                    </div>
                </div>
                @if($isStatic)
                    <input type="hidden" id="{{ $canvasPrefix }}-receiver-input" name="{{ $staticNamePrefix }}[receiver_signature]">
                @else
                    <input type="hidden" id="{{ $canvasPrefix }}-receiver-input" wire:model.defer="{{ $wirePrefix }}receiver_signature">
                    @error($wirePrefix.'receiver_signature')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                @endif
            </div>
        </div>
    </div>
</div>
