@php
    /** @var \App\LabSection $section */
    $equipment = $section->equipment;
    $calibration = $equipment?->latestCalibration;
    $certificateUrl = null;
    if ($calibration && filled($calibration->certificate) && $calibration->certificate !== 'no-document') {
        $certificateUrl = str_starts_with($calibration->certificate, 'http')
            ? $calibration->certificate
            : url($calibration->certificate);
    }
    $formatDecimal = static function ($value): string {
        if ($value === null || $value === '') {
            return '—';
        }
        if (! is_numeric($value)) {
            return (string) $value;
        }

        return rtrim(rtrim(number_format((float) $value, 6, '.', ''), '0'), '.') ?: '0';
    };

    // Check calibration validity
    $calibrationValid = false;
    if ($calibration && $calibration->date) {
        $calibrationExpiry = $calibration->date->copy()->addYear();
        $calibrationValid = now()->lte($calibrationExpiry);
    }
@endphp

<div class="env-panel">
    <h6 class="env-panel__title"><i class="mdi mdi-tools"></i> Equipment & calibration</h6>
    @if($equipment)
        <div class="row g-3">
            <div class="col-md-4">
                <div class="env-stat">
                    <span class="env-stat__label">Thermometer ID (Equipment ID)</span>
                    <span class="env-stat__value">
                        {{ $equipment->equipment_number ?? '—' }}
                    </span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="env-stat">
                    <span class="env-stat__label">Equipment Name</span>
                    <span class="env-stat__value">{{ $equipment->name }}</span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="env-stat">
                    <span class="env-stat__label">Model / serial</span>
                    <span class="env-stat__value">{{ $equipment->model ?? '—' }} / {{ $equipment->serial_number ?? '—' }}</span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="env-stat">
                    <span class="env-stat__label">Tolerance</span>
                    <span class="env-stat__value">{{ $equipment->daily_log_tolerance ? $formatDecimal($equipment->daily_log_tolerance) : '—' }}</span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="env-stat">
                    <span class="env-stat__label">Thermometer calibration valid</span>
                    <span class="env-stat__value">
                        @if($calibration)
                            @if($calibrationValid)
                                <span class="env-badge env-badge--success">Yes</span>
                            @else
                                <span class="env-badge env-badge--danger">No (Expired)</span>
                            @endif
                        @else
                            <span class="env-badge env-badge--danger">No (No calibration record)</span>
                        @endif
                    </span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="env-stat">
                    <span class="env-stat__label">Prescribed temperature ranges</span>
                    <span class="env-stat__value">
                        @if($equipment->daily_log_value_types && is_array($equipment->daily_log_value_types))
                            @foreach($equipment->daily_log_value_types as $vt)
                                @if(($vt['value_type'] ?? '') === 'range' && isset($vt['expected_min']) && isset($vt['expected_max']))
                                    <div class="mb-1">
                                        {{ $formatDecimal($vt['expected_min']) }} – {{ $formatDecimal($vt['expected_max']) }}
                                        @if(isset($vt['reporting_unit'])) {{ $vt['reporting_unit'] }} @endif
                                    </div>
                                @elseif(($vt['value_type'] ?? '') === 'constant' && isset($vt['expected_value']))
                                    <div class="mb-1">
                                        Expected: {{ $vt['expected_value'] }}
                                        @if(isset($vt['reporting_unit'])) {{ $vt['reporting_unit'] }} @endif
                                    </div>
                                @endif
                            @endforeach
                        @else
                            —
                        @endif
                    </span>
                </div>
            </div>
            @if($calibration)
                <div class="col-md-4">
                    <div class="env-stat">
                        <span class="env-stat__label">Calibration date</span>
                        <span class="env-stat__value">{{ optional($calibration->date)->format('Y-m-d') ?? '—' }}</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="env-stat">
                        <span class="env-stat__label">Correction factor</span>
                        <span class="env-stat__value">{{ $formatDecimal($calibration->correction_factor) }}</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="env-stat">
                        <span class="env-stat__label">U.M (uncertainty)</span>
                        <span class="env-stat__value">
                            <span class="env-badge env-badge--um">{{ $formatDecimal($calibration->uncertainty_of_measure) }}</span>
                        </span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="env-stat">
                        <span class="env-stat__label">Reference / certificate</span>
                        <span class="env-stat__value env-stat__value--with-action">
                            @if(filled($calibration->reference_number))
                                <span class="env-stat__text">{{ $calibration->reference_number }}</span>
                            @elseif($certificateUrl)
                                <span class="env-stat__text">{{ __('Certificate on file') }}</span>
                            @else
                                <span class="env-stat__text">—</span>
                            @endif
                            @if($certificateUrl)
                                <a
                                    href="{{ $certificateUrl }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="env-cert-open"
                                    title="{{ __('Open calibration certificate') }}"
                                    aria-label="{{ __('Open calibration certificate') }}"
                                >
                                    <i class="mdi mdi-arrow-expand" aria-hidden="true"></i>
                                </a>
                            @endif
                        </span>
                    </div>
                </div>
                @if(filled($calibration->service_provider))
                    <div class="col-md-4">
                        <div class="env-stat">
                            <span class="env-stat__label">Service provider</span>
                            <span class="env-stat__value">{{ $calibration->service_provider }}</span>
                        </div>
                    </div>
                @endif
            @else
                <div class="col-12">
                    <p class="text-muted small mb-0">No calibration record on file for this equipment.</p>
                </div>
            @endif
        </div>
    @else
        <p class="text-muted mb-0">No equipment linked to this lab section.</p>
    @endif
</div>
