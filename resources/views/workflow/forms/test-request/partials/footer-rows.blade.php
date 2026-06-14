@php
    $statementOptions = ['YES', 'No', 'As per Contract', 'As per Email'];
    $selectedStatement = $signatures['statement_of_conformity'] ?? '';
    $labConditionOptions = ['Acceptable', 'Not Acceptable'];
    $selectedLabCondition = $labUse['lab_sample_condition'] ?? '';
    $contactLabel = match ($variant ?? '') {
        'food' => 'Customer Representative Contact Number:',
        default => 'Customer Representative Number:',
    };
    $leftColspan = (int) floor(($footerColspan ?? 18) * 0.55);
    $rightColspan = (int) ceil(($footerColspan ?? 18) * 0.45);
@endphp
<tr>
    <td colspan="{{ $leftColspan }}">
        <span class="trf-field-label">Statement of Conformity Required in Reports:</span>
        @foreach($statementOptions as $option)
            <span class="{{ strcasecmp($selectedStatement, $option) === 0 ? 'trf-check trf-check-on' : 'trf-check trf-check-off' }}"></span> {{ $option }}
        @endforeach
    </td>
    <td colspan="{{ $rightColspan }}" rowspan="4" class="trf-lab-box">
        <div class="trf-lab-title">FOR LAB USE ONLY</div>
        <span class="trf-field-label">Received Date &amp; Time:</span>
        <span class="trf-field-value">{{ $labUse['lab_received_datetime'] ?: '' }}</span>
        <span class="trf-field-label">Received by:</span>
        <span class="trf-field-value">{{ $labUse['lab_received_by'] ?: '' }}</span>
        <span class="trf-field-label">Sample Condition:</span>
        @foreach($labConditionOptions as $option)
            <span class="{{ strcasecmp($selectedLabCondition, $option) === 0 ? 'trf-check trf-check-on' : 'trf-check trf-check-off' }}"></span> {{ $option }}
        @endforeach
    </td>
</tr>
<tr>
    <td colspan="{{ $leftColspan }}">
        <span class="trf-field-label">Sampled By: Name and Employee ID</span>
        <span class="trf-field-value">{{ $signatures['sampled_by'] ?: '' }}</span>
    </td>
</tr>
<tr>
    <td colspan="{{ $leftColspan }}">
        <span class="trf-field-label">Customer Representative Name/Sign.:</span>
        <span class="trf-field-value">{{ $signatures['customer_rep_name'] ?: '' }}</span>
    </td>
</tr>
<tr>
    <td colspan="{{ $leftColspan }}">
        <span class="trf-field-label">{{ $contactLabel }}</span>
        <span class="trf-field-value">{{ $signatures['customer_rep_contact'] ?: '' }}</span>
    </td>
</tr>
<tr>
    <td colspan="{{ $footerColspan ?? 18 }}">
        <span class="trf-field-label">Remarks:</span>
        <span class="trf-field-value">{{ $signatures['remarks'] ?: '' }}</span>
    </td>
</tr>
