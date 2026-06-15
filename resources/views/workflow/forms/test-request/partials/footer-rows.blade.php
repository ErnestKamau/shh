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
    $defaultLeftColspan = (int) round($totalCols * 0.65);
    $defaultRightColspan = $totalCols - $defaultLeftColspan;
    $footerSplits = match ($variant ?? '') {
        'waste_water' => [
            'left' => 7,
            'right' => 5,
        ],
        default => null,
    };
    if ($footerSplits !== null) {
        $leftColspan = $footerSplits['left'];
        $rightColspan = $footerSplits['right'];
    } else {
        $leftColspan = $defaultLeftColspan;
        $rightColspan = $defaultRightColspan;
    }
@endphp
<tr class="trf-footer-sign-row">
    <td colspan="{{ $leftColspan }}" class="trf-footer-cell trf-footer-conformity-cell">
        <table class="trf-footer-inline-table" width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td class="trf-footer-inline-label">
                    <span class="trf-field-label">Statement of Conformity Required in Reports:</span>
                </td>
                @foreach($statementOptions as $option)
                    <td class="trf-footer-inline-option">
                        <span class="trf-footer-check-item">
                            <span class="{{ strcasecmp($selectedStatement, $option) === 0 ? 'trf-check trf-check-on' : 'trf-check trf-check-off' }}"></span>{{ $option }}
                        </span>
                    </td>
                @endforeach
            </tr>
        </table>
    </td>
    <td colspan="{{ $rightColspan }}" class="trf-footer-cell trf-footer-lab-head">
        <span class="trf-lab-title">FOR LAB USE ONLY</span>
    </td>
</tr>
<tr class="trf-footer-sign-row">
    <td colspan="{{ $leftColspan }}" class="trf-footer-cell trf-footer-nowrap">
        <span class="trf-field-label">Sampled By:</span>
        <span class="trf-field-value">{{ $signatures['sampled_by'] ?: '' }}</span>
    </td>
    <td colspan="{{ $rightColspan }}" class="trf-footer-cell trf-footer-nowrap">
        <span class="trf-field-label">Received Date &amp; Time:</span>
        <span class="trf-field-value">{{ $labUse['lab_received_datetime'] ?: '' }}</span>
    </td>
</tr>
<tr class="trf-footer-sign-row">
    <td colspan="{{ $leftColspan }}" class="trf-footer-cell trf-footer-nowrap">
        <span class="trf-field-label">Customer Representative Name/Sign.:</span>
        <span class="trf-field-value">{{ $signatures['customer_rep_name'] ?: '' }}</span>
    </td>
    <td colspan="{{ $rightColspan }}" class="trf-footer-cell trf-footer-nowrap">
        <span class="trf-field-label">Received by:</span>
        <span class="trf-field-value">{{ $labUse['lab_received_by'] ?: '' }}</span>
    </td>
</tr>
<tr class="trf-footer-sign-row">
    <td colspan="{{ $leftColspan }}" class="trf-footer-cell trf-footer-nowrap">
        <span class="trf-field-label">{{ $contactLabel }}</span>
        <span class="trf-field-value">{{ $signatures['customer_rep_contact'] ?: '' }}</span>
    </td>
    <td colspan="{{ $rightColspan }}" class="trf-footer-cell trf-footer-condition-cell">
        <table class="trf-footer-inline-table" width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td class="trf-footer-inline-label">
                    <span class="trf-field-label">Sample Condition:</span>
                </td>
                @foreach($labConditionOptions as $option)
                    <td class="trf-footer-inline-option">
                        <span class="trf-footer-check-item">
                            <span class="{{ strcasecmp($selectedLabCondition, $option) === 0 ? 'trf-check trf-check-on' : 'trf-check trf-check-off' }}"></span>{{ $option }}
                        </span>
                    </td>
                @endforeach
            </tr>
        </table>
    </td>
</tr>
<tr class="trf-footer-sign-row">
    <td colspan="{{ $totalCols }}" class="trf-footer-cell">
        <span class="trf-field-label">Remarks:</span>
        <span class="trf-field-value">{{ $signatures['remarks'] ?: '' }}</span>
    </td>
</tr>
