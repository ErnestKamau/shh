@php
    $statementOptions = ['YES', 'No', 'As per Contract', 'As per Email'];
    $selectedStatement = $signatures['statement_of_conformity'] ?? '';
    $labConditionOptions = ['Acceptable', 'Not Acceptable'];
    $selectedLabCondition = $labUse['lab_sample_condition'] ?? '';
@endphp
<div class="trf-section-title">Statement of Conformity</div>
<table class="trf-table">
    <tr>
        <td colspan="4">
            @foreach($statementOptions as $option)
                <span class="{{ strcasecmp($selectedStatement, $option) === 0 ? 'trf-check trf-check-on' : 'trf-check trf-check-off' }}"></span> {{ $option }}
            @endforeach
        </td>
    </tr>
</table>

<div class="trf-lab-title">For Lab Use Only</div>
<table class="trf-table">
    <tr>
        <td style="width:50%;"><span class="trf-field-label">Sampled By:</span> <span class="trf-field-value">{{ $signatures['sampled_by'] ?: '&nbsp;' }}</span></td>
        <td style="width:50%;"><span class="trf-field-label">Received Date &amp; Time:</span> <span class="trf-field-value">{{ $labUse['lab_received_datetime'] ?: '&nbsp;' }}</span></td>
    </tr>
    <tr>
        <td>
            <span class="trf-field-label">Customer Representative Name/Sign:</span>
            @php
                $repSignature = $signatures['customer_rep_signature'] ?? $signatures['customer_rep_name'] ?? '';
            @endphp
            @if(is_string($repSignature) && str_starts_with($repSignature, 'data:image'))
                <img src="{{ $repSignature }}" alt="Customer signature" style="max-height: 48px; max-width: 180px; display: block; margin-top: 4px;">
            @else
                <span class="trf-field-value">{{ $repSignature ?: '&nbsp;' }}</span>
            @endif
        </td>
        <td><span class="trf-field-label">Received by:</span> <span class="trf-field-value">{{ $labUse['lab_received_by'] ?: '&nbsp;' }}</span></td>
    </tr>
    <tr>
        <td><span class="trf-field-label">Customer Representative Contact:</span> <span class="trf-field-value">{{ $signatures['customer_rep_contact'] ?: '&nbsp;' }}</span></td>
        <td>
            <span class="trf-field-label">Sample Condition:</span>
            @foreach($labConditionOptions as $option)
                <span class="{{ strcasecmp($selectedLabCondition, $option) === 0 ? 'trf-check trf-check-on' : 'trf-check trf-check-off' }}"></span> {{ $option }}
            @endforeach
        </td>
    </tr>
    <tr>
        <td colspan="2"><span class="trf-field-label">Remarks:</span> <span class="trf-field-value">{{ $signatures['remarks'] ?: '&nbsp;' }}</span></td>
    </tr>
</table>
