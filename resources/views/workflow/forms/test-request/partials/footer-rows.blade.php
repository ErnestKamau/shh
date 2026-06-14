@php
    $statementOptions = ['YES', 'No', 'As per Contract', 'As per Email'];
    $selectedStatement = $signatures['statement_of_conformity'] ?? '';
    $labConditionOptions = ['Acceptable', 'Not Acceptable'];
    $selectedLabCondition = $labUse['lab_sample_condition'] ?? '';
    $contactLabel = match ($variant ?? '') {
        'food' => 'Customer Representative Contact Number:',
        default => 'Customer Representative Number:',
    };
    $halfColspan = (int) floor(($footerColspan ?? 13) / 2);
    $otherColspan = (int) ceil(($footerColspan ?? 13) / 2);
@endphp
<tr>
    <td colspan="{{ $footerColspan ?? 13 }}">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="border: none; padding: 0;">
                    <span class="trf-field-label">Statement of Conformity Required in Reports:</span>
                    @foreach($statementOptions as $option)
                        <span class="{{ strcasecmp($selectedStatement, $option) === 0 ? 'trf-check trf-check-on' : 'trf-check trf-check-off' }}"></span> {{ $option }}
                    @endforeach
                </td>
                <td style="border: none; padding: 0;" class="trf-right trf-lab-title">FOR LAB USE ONLY</td>
            </tr>
        </table>
    </td>
</tr>
<tr>
    <td colspan="{{ $halfColspan }}">
        <span class="trf-field-label">Sampled By: Name and Employee ID</span>
        <span class="trf-field-value">{{ $signatures['sampled_by'] ?: '' }}</span>
    </td>
    <td colspan="{{ $otherColspan }}">
        <span class="trf-field-label">Received Date &amp; Time:</span>
        <span class="trf-field-value">{{ $labUse['lab_received_datetime'] ?: '' }}</span>
    </td>
</tr>
<tr>
    <td colspan="{{ $halfColspan }}">
        <span class="trf-field-label">Customer Representative Name/Sign.:</span>
        <span class="trf-field-value">{{ $signatures['customer_rep_name'] ?: '' }}</span>
    </td>
    <td colspan="{{ $otherColspan }}">
        <span class="trf-field-label">Received by:</span>
        <span class="trf-field-value">{{ $labUse['lab_received_by'] ?: '' }}</span>
    </td>
</tr>
<tr>
    <td colspan="{{ $halfColspan }}">
        <span class="trf-field-label">{{ $contactLabel }}</span>
        <span class="trf-field-value">{{ $signatures['customer_rep_contact'] ?: '' }}</span>
    </td>
    <td colspan="{{ $otherColspan }}">
        <span class="trf-field-label">Sample Condition:</span>
        @foreach($labConditionOptions as $option)
            <span class="{{ strcasecmp($selectedLabCondition, $option) === 0 ? 'trf-check trf-check-on' : 'trf-check trf-check-off' }}"></span> {{ $option }}
        @endforeach
    </td>
</tr>
<tr>
    <td colspan="{{ $footerColspan ?? 13 }}">
        <span class="trf-field-label">Remarks:</span>
        <span class="trf-field-value">{{ $signatures['remarks'] ?: '' }}</span>
    </td>
</tr>
