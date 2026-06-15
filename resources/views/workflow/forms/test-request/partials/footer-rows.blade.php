@php
    $statementOptions = ['YES', 'No', 'As per Contract', 'As per Email'];
    $selectedStatement = $signatures['statement_of_conformity'] ?? '';
    $labConditionOptions = ['Acceptable', 'Not Acceptable'];
    $selectedLabCondition = $labUse['lab_sample_condition'] ?? '';
    $contactLabel = match ($variant ?? '') {
        'food' => 'Customer Representative Contact Number:',
        default => 'Customer Representative Number:',
    };
    $totalCols = (int) ($footerColspan ?? 18);
    $leftColspan = (int) round($totalCols * 0.65);
    $rightColspan = $totalCols - $leftColspan;
@endphp
<tr class="trf-footer-sign-row">
    <td colspan="{{ $leftColspan }}" class="trf-footer-cell">
        <nobr>
            <span class="trf-field-label">Statement of Conformity Required in Reports:</span>
            @foreach($statementOptions as $option)
                <span class="trf-footer-check-item">
                    <span class="{{ strcasecmp($selectedStatement, $option) === 0 ? 'trf-check trf-check-on' : 'trf-check trf-check-off' }}"></span>{{ $option }}
                </span>
            @endforeach
        </nobr>
    </td>
    <td colspan="{{ $rightColspan }}" class="trf-footer-cell trf-footer-lab-head">
        <span class="trf-lab-title">FOR LAB USE ONLY</span>
    </td>
</tr>
<tr class="trf-footer-sign-row">
    <td colspan="{{ $leftColspan }}" class="trf-footer-cell">
        <span class="trf-field-label">Sampled By:</span>
        <span class="trf-field-value">{{ $signatures['sampled_by'] ?: '' }}</span>
    </td>
    <td colspan="{{ $rightColspan }}" class="trf-footer-cell">
        <span class="trf-field-label">Received Date &amp; Time:</span>
        <span class="trf-field-value">{{ $labUse['lab_received_datetime'] ?: '' }}</span>
    </td>
</tr>
<tr class="trf-footer-sign-row">
    <td colspan="{{ $leftColspan }}" class="trf-footer-cell">
        <span class="trf-field-label">Customer Representative Name/Sign.:</span>
        <span class="trf-field-value">{{ $signatures['customer_rep_name'] ?: '' }}</span>
    </td>
    <td colspan="{{ $rightColspan }}" class="trf-footer-cell">
        <span class="trf-field-label">Received by:</span>
        <span class="trf-field-value">{{ $labUse['lab_received_by'] ?: '' }}</span>
    </td>
</tr>
<tr class="trf-footer-sign-row">
    <td colspan="{{ $leftColspan }}" class="trf-footer-cell">
        <span class="trf-field-label">{{ $contactLabel }}</span>
        <span class="trf-field-value">{{ $signatures['customer_rep_contact'] ?: '' }}</span>
    </td>
    <td colspan="{{ $rightColspan }}" class="trf-footer-cell">
        <nobr>
            <span class="trf-field-label">Sample Condition:</span>
            @foreach($labConditionOptions as $option)
                <span class="trf-footer-check-item">
                    <span class="{{ strcasecmp($selectedLabCondition, $option) === 0 ? 'trf-check trf-check-on' : 'trf-check trf-check-off' }}"></span>{{ $option }}
                </span>
            @endforeach
        </nobr>
    </td>
</tr>
<tr class="trf-footer-sign-row">
    <td colspan="{{ $totalCols }}" class="trf-footer-cell">
        <span class="trf-field-label">Remarks:</span>
        <span class="trf-field-value">{{ $signatures['remarks'] ?: '' }}</span>
    </td>
</tr>
