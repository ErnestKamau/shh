@php
    $receiptForm = $receiptForm ?? [];
    $partLabel = $partLabel ?? null;

    $display = static function (string $key) use ($receiptForm): string {
        $value = $receiptForm[$key] ?? '';

        if ($key === 'number_of_samples') {
            return is_numeric($value) ? (string) (int) $value : '—';
        }

        if ($key === 'sample_receiving_date' && is_string($value) && trim($value) !== '') {
            try {
                return \Illuminate\Support\Carbon::parse($value)->format('d M Y');
            } catch (\Throwable) {
                return $value;
            }
        }

        $text = trim((string) $value);

        return $text !== '' ? $text : '—';
    };

    $signature = static function (string $key) use ($receiptForm): ?string {
        $value = trim((string) ($receiptForm[$key] ?? ''));

        return $value !== '' && str_starts_with($value, 'data:image') ? $value : null;
    };
@endphp

<div class="receipt-notif-form receipt-notif-form--review">
    <div class="receipt-notif-form__card">
        @if($partLabel)
            <div class="receipt-notif-form__header">
                <h6 class="receipt-notif-form__title">{{ $partLabel }}</h6>
            </div>
        @endif
        <div class="receipt-notif-form__body">
            @if($partLabel === null)
                <h6 class="receipt-notif-form__title mb-3">Sample Receipt Notification (GCLA 01)</h6>
            @endif

            <div class="row">
                <div class="col-md-8 mb-3">
                    <span class="receipt-notif-form__label">Name of the client or submitting authority</span>
                    <p class="receipt-notif-review-value mb-0">{{ $display('client_or_authority_name') }}</p>
                </div>
                <div class="col-md-4 mb-3">
                    <span class="receipt-notif-form__label">Number of Samples</span>
                    <p class="receipt-notif-review-value mb-0">{{ $display('number_of_samples') }}</p>
                </div>
            </div>

            <div class="row">
                <div class="col-12 mb-3">
                    <span class="receipt-notif-form__label">Laboratory Identification Number / Lab. No. (Batch No)</span>
                    <p class="receipt-notif-review-value mb-0">{{ $display('laboratory_identification_number') }}</p>
                </div>
            </div>

            <div class="row">
                <div class="col-12 mb-3">
                    <span class="receipt-notif-form__label">Description of sample(s)</span>
                    <p class="receipt-notif-review-value mb-0" style="white-space: pre-wrap;">{{ $display('sample_description') }}</p>
                </div>
            </div>

            <hr class="receipt-notif-form__divider">
            <h6 class="receipt-notif-form__section-title">Person Submitting the Sample or Exhibit</h6>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <span class="receipt-notif-form__label">Name</span>
                    <p class="receipt-notif-review-value mb-0">{{ $display('submitter_name') }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <span class="receipt-notif-form__label">Designation</span>
                    <p class="receipt-notif-review-value mb-0">{{ $display('submitter_designation') }}</p>
                </div>
            </div>
            <div class="mb-3 acc-collapsible-signature-field">
                <span class="receipt-notif-form__label d-block">Signature</span>
                @if($signature('submitter_signature'))
                    <div class="receipt-notif-review-sig">
                        <img src="{{ $signature('submitter_signature') }}" alt="Submitter signature">
                    </div>
                @else
                    <p class="receipt-notif-review-value mb-0 text-muted">—</p>
                @endif
            </div>

            <hr class="receipt-notif-form__divider">
            <h6 class="receipt-notif-form__section-title">Receiving Person</h6>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <span class="receipt-notif-form__label">Name</span>
                    <p class="receipt-notif-review-value mb-0">{{ $display('receiver_name') }}</p>
                </div>
                <div class="col-md-4 mb-3">
                    <span class="receipt-notif-form__label">Designation</span>
                    <p class="receipt-notif-review-value mb-0">{{ $display('receiver_designation') }}</p>
                </div>
                <div class="col-md-4 mb-3">
                    <span class="receipt-notif-form__label">Sample receiving date</span>
                    <p class="receipt-notif-review-value mb-0">{{ $display('sample_receiving_date') }}</p>
                </div>
            </div>
            <div class="mb-0 acc-collapsible-signature-field">
                <span class="receipt-notif-form__label d-block">Signature</span>
                @if($signature('receiver_signature'))
                    <div class="receipt-notif-review-sig">
                        <img src="{{ $signature('receiver_signature') }}" alt="Receiving person signature">
                    </div>
                @else
                    <p class="receipt-notif-review-value mb-0 text-muted">—</p>
                @endif
            </div>
        </div>
    </div>
</div>

@once
    <style>
        .receipt-notif-review-value {
            font-size: 0.9rem;
            font-weight: 600;
            color: #0f172a;
        }

        .receipt-notif-review-sig {
            border: 1px dashed #cbd5e1;
            border-radius: 10px;
            padding: 0.5rem;
            max-width: 560px;
            background: #fff;
        }

        .receipt-notif-review-sig img {
            max-width: 100%;
            max-height: 140px;
            display: block;
        }
    </style>
@endonce
