<div class="info-section">
    <div class="info-row">
        <div class="info-label" style="width: 15%;">Sample Description:</div>
        <div class="info-value">
            {{ $sample_type_name ?? 'Water Sample' }}
        </div>
    </div>

    <div class="info-row">
        <div class="info-label" style="width: 15%;">Lab No:</div>
        <div class="info-value">{{ $batch->batch_code ?? 'N/A' }}</div>
    </div>

    <div class="info-row">
        <div class="info-label" style="width: 15%;">Customer Reference:</div>
        <div class="info-value">
            @php
                $fallbackReference = $batch->reference_number ?? ($customer->name ?? null);
            @endphp
            {{ $customerReference ?? $fallbackReference ?? 'N/A' }}
        </div>
    </div>

    <div class="info-row">
        <div class="info-label" style="width: 15%;">Sample Receiving Date:</div>
        <div class="info-value">
            {{ $batch->receipt_date ? date('d/m/y', strtotime($batch->receipt_date)) : 'N/A' }}
        </div>
    </div>

    <div class="info-row">
        <div class="info-label" style="width: 15%;">Date of Sampling:</div>
        <div class="info-value">
            {{ $batch->date_collected ? date('d/m/Y', strtotime($batch->date_collected)) : 'N/A' }}
        </div>
    </div>

    <div class="info-row">
        <div class="info-label" style="width: 15%;">Date Tested:</div>
        <div class="info-value">
            @php
                $analysisStart = $analysis_date?->start_analysis_date;
                $analysisEnd = $analysis_date?->end_analysis_date;
            @endphp
            @if($analysisStart && $analysisEnd)
                {{ date('d', strtotime($analysisStart)) . ' – ' . date('d F Y', strtotime($analysisEnd)) }}
            @elseif($analysisStart)
                {{ date('d', strtotime($analysisStart)) . ' – ' . date('d F Y', strtotime($analysisStart)) }}
            @elseif($analysisEnd)
                {{ date('d', strtotime($analysisEnd)) . ' – ' . date('d F Y', strtotime($analysisEnd)) }}
            @else
                N/A
            @endif
        </div>
    </div>

    <div class="info-row">
        <div class="info-label" style="width: 15%;">Date of Report:</div>
        <div class="info-value">{{ $date ?? date('d M Y') }}</div>
    </div>
</div>