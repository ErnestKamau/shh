@php
    $labOptions = ['Acceptable', 'Not Acceptable'];
    $selectedLab = $labUse['lab_sample_condition'] ?? '';
@endphp

<div class="trf-section-title">For Lab Use Only</div>
<table class="trf-table">
    <tr>
        <td style="width:25%;"><span class="trf-field-label">Received Date &amp; Time:</span><br>{{ $labUse['lab_received_datetime'] }}</td>
        <td style="width:25%;"><span class="trf-field-label">Received by:</span><br>{{ $labUse['lab_received_by'] }}</td>
        <td style="width:50%;">
            <span class="trf-field-label">Sample Condition:</span><br>
            @foreach($labOptions as $option)
                @php $on = strcasecmp($selectedLab, $option) === 0; @endphp
                <span class="{{ $on ? 'trf-check trf-check-on' : 'trf-check trf-check-off' }}"></span> {{ $option }} &nbsp;
            @endforeach
        </td>
    </tr>
</table>
