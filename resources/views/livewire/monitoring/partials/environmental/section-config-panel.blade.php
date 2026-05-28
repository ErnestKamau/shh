@php
    /** @var \App\LabSection $section */
@endphp

<div class="env-panel">
    <h6 class="env-panel__title"><i class="mdi mdi-cog-outline"></i> Section configuration</h6>
    <div class="row g-3">
        <div class="col-md-4">
            <div class="env-stat">
                <span class="env-stat__label">Expected value type</span>
                <span class="env-stat__value">{{ ucfirst(str_replace('_', ' ', $section->expected_value_type ?? '—')) }}</span>
            </div>
        </div>
        <div class="col-md-4">
            <div class="env-stat">
                <span class="env-stat__label">Optimum / limits</span>
                <span class="env-stat__value">{{ $section->formattedOptimumLevel() }}</span>
            </div>
        </div>
        <div class="col-md-4">
            <div class="env-stat">
                <span class="env-stat__label">Reporting unit</span>
                <span class="env-stat__value">{{ $section->reportingUnit?->name ?? '—' }}</span>
            </div>
        </div>
        <div class="col-md-4">
            <div class="env-stat">
                <span class="env-stat__label">Result nature</span>
                <span class="env-stat__value">{{ ucfirst($section->result_nature ?? '—') }}</span>
            </div>
        </div>
        <div class="col-md-4">
            <div class="env-stat">
                <span class="env-stat__label">Reading frequency</span>
                <span class="env-stat__value">{{ $section->readingFrequencyLabel() }}</span>
            </div>
        </div>
        <div class="col-md-4">
            <div class="env-stat">
                <span class="env-stat__label">Schedule</span>
                <span class="env-stat__value">{{ $section->formattedReadingFrequencySchedule() }}</span>
            </div>
        </div>
    </div>
</div>
