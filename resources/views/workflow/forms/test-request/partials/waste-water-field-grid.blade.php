@php
    $ww = $wasteWaterFields ?? [];
    $fieldColspan = (int) ($fieldColspan ?? 3);
    $fieldRows = [
        'QUANTITY' => (string) ($ww['field_data_quantity'] ?? ''),
        'APPEARANCE' => (string) ($ww['field_data_appearance'] ?? ''),
        'COLOR' => (string) ($ww['field_data_color'] ?? ''),
        'ODOR' => (string) ($ww['field_data_odor'] ?? ''),
        'pH' => (string) ($ww['field_data_ph'] ?? ''),
        'TEMPERATURE' => (string) ($ww['field_data_temperature'] ?? ''),
        'FREE CHLORINE' => (string) ($ww['field_data_free_chlorine'] ?? ''),
    ];
    $requirementChecks = $ww['field_data_requirement_checks'] ?? [];
    $requirementKeys = ['MICROBIOLOGY', 'CHEMISTRY'];
@endphp
<tr>
    <th colspan="{{ $fieldColspan }}" class="trf-collection-section-header">SAMPLING SOURCE</th>
    <th colspan="{{ $fieldColspan }}" class="trf-collection-section-header">SAMPLE TYPES</th>
    <th colspan="{{ $fieldColspan }}" class="trf-collection-section-header">FIELD DATA</th>
    <th colspan="{{ $fieldColspan }}" class="trf-collection-section-header">FIELD DATA</th>
</tr>
<tr>
    <td colspan="{{ $fieldColspan }}" class="trf-collection-check-cell">
        @include('workflow.forms.test-request.partials.checkbox-grid', [
            'checks' => $ww['sampling_source'] ?? [],
            'orderedKeys' => $options['sampling_source'] ?? [],
            'columns' => 1,
            'bordered' => true,
        ])
    </td>
    <td colspan="{{ $fieldColspan }}" class="trf-collection-check-cell trf-ww-stack-cell">
        <table class="trf-check-grid trf-check-grid-bordered trf-check-grid-1" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;width:100%;table-layout:fixed;">
            @include('workflow.forms.test-request.partials.checkbox-grid', [
                'checks' => $ww['sample_types_ww'] ?? [],
                'orderedKeys' => $options['sample_types_ww'] ?? [],
                'columns' => 1,
                'bordered' => true,
                'asRows' => true,
            ])
            <tr>
                <td class="trf-check-grid-subheader-cell">TRANSPORT CONDITION</td>
            </tr>
            @include('workflow.forms.test-request.partials.checkbox-grid', [
                'checks' => $ww['transport_condition'] ?? [],
                'orderedKeys' => $options['transport_condition'] ?? [],
                'columns' => 1,
                'bordered' => true,
                'asRows' => true,
            ])
        </table>
    </td>
    <td colspan="{{ $fieldColspan }}" class="trf-ww-field-data-cell">
        <table class="trf-check-grid trf-check-grid-bordered trf-check-grid-1" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;width:100%;table-layout:fixed;">
            @foreach($fieldRows as $label => $value)
                <tr>
                    <td>
                        <span class="trf-field-label">{{ $label }}:</span>
                        @if($value !== '')
                            <span class="trf-field-value">{{ $value }}</span>
                        @else
                            <span class="trf-dotted-leader">................</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </table>
    </td>
    <td colspan="{{ $fieldColspan }}" class="trf-collection-check-cell trf-ww-requirements-cell">
        @include('workflow.forms.test-request.partials.checkbox-grid', [
            'checks' => [
                'MICROBIOLOGY' => (bool) ($requirementChecks['MICROBIOLOGY'] ?? false),
                'CHEMISTRY' => (bool) ($requirementChecks['CHEMISTRY'] ?? false),
            ],
            'orderedKeys' => $requirementKeys,
            'columns' => 1,
            'bordered' => true,
        ])
    </td>
</tr>
