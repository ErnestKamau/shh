<div class="info-section">
    <div class="three-column">
        <div class="column">
            <div class="info-row">
                <div class="info-label">Sample Description:</div>
                <div class="info-value">
                    {{ $sample_type_name ?? 'Water Sample' }} x {{ array_sum(array_map('count', $grouped_samples ?? [])) + count($ungrouped_samples ?? []) }}*
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Sample Receiving Date:</div>
                <div class="info-value">{{ $batch->receipt_date ? date('d/m/y', strtotime($batch->receipt_date)) : 'N/A' }}</div>
            </div>
        </div>
        <div class="column">
            <div class="info-row">
                <div class="info-label">Lab Number:</div>
                <div class="info-value">{{ $batch->batch_code ?? 'N/A' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Date Tested:</div>
                <div class="info-value">{{ isset($analysis_date) ? date('d', strtotime($analysis_date->start_analysis_date)) . ' – ' . date('d F Y', strtotime($analysis_date->start_analysis_date)) : 'N/A' }}</div>
            </div>
        </div>
        <div class="column">
            <div class="info-row">
                <div class="info-label">Customer Reference:</div>
                <div class="info-value">{{ $batch->reference_number ?? ($customer->name ?? 'N/A') }}*</div>
            </div>
            <div class="info-row">
                <div class="info-label">Date of Sampling:</div>
                <div class="info-value">{{ $batch->date_collected ? date('d/m/Y', strtotime($batch->date_collected)) : 'N/A' }}</div>
            </div>
        </div>
    </div>
    <div class="three-column">
        <div class="column">
            <div class="info-row">
                <div class="info-label">Date of Report:</div>
                <div class="info-value">{{ $date ?? date('d M Y') }}</div>
            </div>
        </div>
        <div class="column"></div>
        <div class="column"></div>
    </div>
</div>