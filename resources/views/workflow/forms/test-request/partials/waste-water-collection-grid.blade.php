@php
    $grid = $collectionGrid ?? [];
    $metaRows = $grid['meta_rows'] ?? [];
    $detailsColspan = (int) ($detailsColspan ?? 4);
    $apparatusColspan = (int) ($apparatusColspan ?? 4);
    $methodColspan = (int) ($methodColspan ?? 4);
    $samplingDate = (string) ($metaRows[0]['value'] ?? '');
    $samplingTime = (string) ($metaRows[1]['value'] ?? '');
    $samplingLocation = (string) ($metaRows[2]['value'] ?? '');
    $sampleDescription = (string) ($grid['sample_description'] ?? '');
    $methodOrderedKeys = ['APHA', 'US FDA', 'EPA', 'CCFRA', 'DM', 'SOP', 'OTHERS'];
@endphp
<tr>
    <th colspan="{{ $detailsColspan }}" class="trf-collection-section-header">SAMPLE DETAILS</th>
    <th colspan="{{ $apparatusColspan }}" class="trf-collection-section-header">SAMPLING APPARATUS</th>
    <th colspan="{{ $methodColspan }}" class="trf-collection-section-header">METHOD OF SAMPLING</th>
</tr>
<tr>
    <td colspan="{{ $detailsColspan }}" class="trf-meta-cell">
        <span class="trf-field-label">Sampling Date:</span>
        <span class="trf-field-value">{{ $samplingDate }}</span>
    </td>
    <td colspan="{{ $apparatusColspan }}" rowspan="3" class="trf-collection-check-cell trf-ww-stack-cell">
        @include('workflow.forms.test-request.partials.waste-water-apparatus-block', ['grid' => $grid])
    </td>
    <td colspan="{{ $methodColspan }}" rowspan="3" class="trf-collection-check-cell trf-ww-method-cell">
        @include('workflow.forms.test-request.partials.checkbox-grid', [
            'checks' => $grid['method'] ?? [],
            'orderedKeys' => $methodOrderedKeys,
            'columns' => 2,
            'bordered' => true,
        ])
    </td>
</tr>
<tr>
    <td colspan="{{ $detailsColspan }}" class="trf-meta-cell">
        <span class="trf-field-label">Sampling Time:</span>
        <span class="trf-field-value">{{ $samplingTime }}</span>
    </td>
</tr>
<tr>
    <td colspan="{{ $detailsColspan }}" class="trf-meta-cell">
        <span class="trf-field-label">Sampling Location:</span>
        <span class="trf-field-value">{{ $samplingLocation }}</span>
    </td>
</tr>
<tr>
    <td colspan="{{ $detailsColspan }}" class="trf-ww-description-cell">
        <span class="trf-field-label">SAMPLE &amp; SAMPLING POINT DESCRIPTION:</span>
        <span class="trf-field-value">{{ $sampleDescription }}</span>
    </td>
    <td colspan="{{ $apparatusColspan }}" class="trf-collection-check-cell trf-ww-stack-cell">
        @include('workflow.forms.test-request.partials.checkbox-grid', [
            'headerRow' => 'REASON OF COLLECTION',
            'checks' => $grid['reason'] ?? [],
            'orderedKeys' => $grid['reason_ordered_keys'] ?? [],
            'columns' => 1,
            'bordered' => true,
        ])
    </td>
    <td colspan="{{ $methodColspan }}" class="trf-collection-check-cell trf-ww-stack-cell">
        @include('workflow.forms.test-request.partials.checkbox-grid', [
            'headerRow' => 'SAMPLING TECHNIQUE',
            'checks' => $grid['technique'] ?? [],
            'orderedKeys' => $grid['technique_ordered_keys'] ?? [],
            'columns' => 1,
            'bordered' => true,
        ])
    </td>
</tr>
