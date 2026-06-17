@php
    $grid = $collectionGrid ?? [];
    $metaRows = $grid['meta_rows'] ?? [];
    $rowspan = max(1, count($metaRows));
    $detailsColspan = (int) ($detailsColspan ?? 2);
    $apparatusColspan = (int) ($apparatusColspan ?? 4);
    $methodColspan = (int) ($methodColspan ?? 4);
    $reasonColspan = (int) ($reasonColspan ?? 3);
    $transportColspan = (int) ($transportColspan ?? 3);
    $thermometerId = (string) ($grid['thermometer_id'] ?? '');
    $thermometerHtml = $thermometerId !== ''
        ? '<span class="trf-check trf-check-on"></span> THERMOMETER ID ' . e($thermometerId)
        : '<span class="trf-check trf-check-off"></span> THERMOMETER ID <span class="trf-dotted-leader">................</span>';
@endphp
<tr>
    <th colspan="{{ $detailsColspan }}" class="trf-collection-section-header">SAMPLE DETAILS</th>
    <th colspan="{{ $apparatusColspan }}" class="trf-collection-section-header">SAMPLING APPARATUS</th>
    <th colspan="{{ $methodColspan }}" class="trf-collection-section-header">METHOD OF SAMPLING</th>
    <th colspan="{{ $reasonColspan }}" class="trf-collection-section-header">REASON OF COLLECTION</th>
    <th colspan="{{ $transportColspan }}" class="trf-collection-section-header">TRANSPORT CONDITION</th>
</tr>
@foreach($metaRows as $index => $metaRow)
    <tr>
        <td colspan="{{ $detailsColspan }}" class="trf-meta-cell">
            <span class="trf-field-label">{{ $metaRow['label'] ?? '' }}</span>
            <span class="trf-field-value">{{ $metaRow['value'] ?? '' }}</span>
        </td>
        @if($index === 0)
            <td colspan="{{ $apparatusColspan }}" rowspan="{{ $rowspan }}" class="trf-collection-check-cell">
                @include('workflow.forms.test-request.partials.checkbox-grid', [
                    'checks' => $grid['apparatus'] ?? [],
                    'orderedKeys' => $grid['apparatus_ordered_keys'] ?? [],
                    'columns' => 2,
                    'bordered' => true,
                    'trailingHtml' => $thermometerHtml,
                ])
            </td>
            <td colspan="{{ $methodColspan }}" rowspan="{{ $rowspan }}" class="trf-collection-check-cell">
                @include('workflow.forms.test-request.partials.checkbox-grid', [
                    'checks' => $grid['method'] ?? [],
                    'orderedKeys' => $grid['method_ordered_keys'] ?? [],
                    'columns' => 2,
                    'bordered' => true,
                ])
            </td>
            <td colspan="{{ $reasonColspan }}" rowspan="{{ $rowspan }}" class="trf-collection-check-cell">
                @include('workflow.forms.test-request.partials.checkbox-grid', [
                    'checks' => $grid['reason'] ?? [],
                    'orderedKeys' => $grid['reason_ordered_keys'] ?? [],
                    'columns' => 1,
                    'bordered' => true,
                ])
            </td>
            <td colspan="{{ $transportColspan }}" rowspan="{{ $rowspan }}" class="trf-collection-check-cell">
                @include('workflow.forms.test-request.partials.checkbox-grid', [
                    'checks' => $grid['transport'] ?? [],
                    'orderedKeys' => $grid['transport_ordered_keys'] ?? [],
                    'columns' => 1,
                    'bordered' => true,
                ])
            </td>
        @endif
    </tr>
@endforeach
