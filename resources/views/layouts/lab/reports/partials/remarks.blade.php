@php
    $remarkSamples = [];
    foreach (($grouped_samples ?? []) as $areaSamples) {
        foreach ($areaSamples as $sampleData) {
            $remarkSamples[] = $sampleData;
        }
    }
    foreach (($ungrouped_samples ?? []) as $sampleData) {
        $remarkSamples[] = $sampleData;
    }

    $remarkEntries = [];
    foreach ($remarkSamples as $sampleData) {
        $sample = $sampleData['sample'] ?? null;
        $headerBody = is_object($sample) ? ($sample->header_body ?? '') : '';
        if ($headerBody === null || trim(strip_tags((string) $headerBody)) === '') {
            continue;
        }
        $remarkEntries[] = [
            'sample_code' => $sampleData['sample_code']
                ?? (is_object($sample) ? ($sample->sample_code ?? null) : null),
            'header_body' => $headerBody,
        ];
    }

    $showSampleCodes = count($remarkEntries) > 1;
@endphp

@if(count($remarkEntries) > 0)
<div class="remarks-section" style="margin: 12px 0 18px 0; font-size: 10px;">
    @foreach($remarkEntries as $entry)
        <div class="sample-remarks" style="margin-bottom: 6px;">
            <strong>Remarks@if($showSampleCodes && !empty($entry['sample_code'])): {{ $entry['sample_code'] }}@endif:</strong>
            {!! str_ireplace(['not conforming', 'non-conforming'], 'Non-Conforming', $entry['header_body']) !!}
        </div>
    @endforeach
</div>
@endif
