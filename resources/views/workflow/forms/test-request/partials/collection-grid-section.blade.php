@php
    $grid = $collectionGrid ?? [];
    $metaRows = $grid['meta_rows'] ?? [];
    $rowspan = max(1, count($metaRows));
    $detailsColspan = (int) ($detailsColspan ?? 2);
    $apparatusColspan = (int) ($apparatusColspan ?? 4);
    $methodColspan = (int) ($methodColspan ?? 4);
    $reasonColspan = (int) ($reasonColspan ?? 3);
    $transportColspan = (int) ($transportColspan ?? 3);
    $totalColspan = $detailsColspan + $apparatusColspan + $methodColspan + $reasonColspan + $transportColspan;
    $thermometerId = (string) ($grid['thermometer_id'] ?? '');
    $collectionExtras = $grid['collection_extras'] ?? [];
    $hasCollectionExtras = (bool) ($grid['has_collection_extras'] ?? false);
    $thermometerHtml = $thermometerId !== ''
        ? '<span class="trf-check trf-check-on"></span> EQUIPMENT ID: ' . e($thermometerId)
        : '<span class="trf-check trf-check-off"></span> EQUIPMENT ID: <span class="trf-dotted-leader">................</span>';
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
@if($hasCollectionExtras)
    <tr>
        <td colspan="{{ $totalColspan }}" class="trf-collection-section-header">ADDITIONAL SHIPMENT DETAILS (APPLICABLE WHERE RELEVANT)</td>
    </tr>
    <tr>
        <td colspan="{{ (int) floor($totalColspan / 4) }}" class="trf-meta-cell">
            <span class="trf-field-label">Date received:</span>
            <span class="trf-field-value">{{ $collectionExtras['date_received'] ?? '' }}</span>
        </td>
        <td colspan="{{ (int) floor($totalColspan / 4) }}" class="trf-meta-cell">
            <span class="trf-field-label">Packaging:</span>
            <span class="trf-field-value">{{ $collectionExtras['packaging'] ?? '' }}</span>
        </td>
        <td colspan="{{ (int) floor($totalColspan / 4) }}" class="trf-meta-cell">
            <span class="trf-field-label">Sample weight:</span>
            <span class="trf-field-value">{{ $collectionExtras['sample_weight'] ?? '' }}</span>
        </td>
        <td colspan="{{ $totalColspan - ((int) floor($totalColspan / 4) * 3) }}" class="trf-meta-cell">
            <span class="trf-field-label">Sample information:</span>
            <span class="trf-field-value">{{ $collectionExtras['sample_information'] ?? '' }}</span>
        </td>
    </tr>
    <tr>
        <td colspan="{{ (int) floor($totalColspan / 4) }}" class="trf-meta-cell">
            <span class="trf-field-label">Ship / vessel:</span>
            <span class="trf-field-value">{{ $collectionExtras['ship_name'] ?? '' }}</span>
        </td>
        <td colspan="{{ (int) floor($totalColspan / 4) }}" class="trf-meta-cell">
            <span class="trf-field-label">Port of loading:</span>
            <span class="trf-field-value">{{ $collectionExtras['port_of_loading'] ?? '' }}</span>
        </td>
        <td colspan="{{ (int) floor($totalColspan / 4) }}" class="trf-meta-cell">
            <span class="trf-field-label">Port of discharge:</span>
            <span class="trf-field-value">{{ $collectionExtras['port_of_discharge'] ?? '' }}</span>
        </td>
        <td colspan="{{ $totalColspan - ((int) floor($totalColspan / 4) * 3) }}" class="trf-meta-cell">
            <span class="trf-field-label">Seal:</span>
            <span class="trf-field-value">{{ $collectionExtras['seal_number'] ?? '' }}</span>
        </td>
    </tr>
@endif
