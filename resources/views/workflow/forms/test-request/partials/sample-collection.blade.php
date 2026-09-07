@php
    $renderChecks = function (array $checks) {
        $html = '';
        foreach ($checks as $label => $checked) {
            $class = $checked ? 'trf-check trf-check-on' : 'trf-check trf-check-off';
            $html .= '<span class="' . $class . '"></span> ' . e($label) . ' &nbsp; ';
        }
        return $html;
    };
@endphp

<div class="trf-section-title">Sample Collection Data</div>
<table class="trf-table">
    <tr>
        <td style="width: 50%;">
            <span class="trf-field-label">Sampling Date:</span> {{ $collection['sampling_date'] }}<br>
            <span class="trf-field-label">Sampling Time:</span> {{ $collection['sampling_time'] }}<br>
            <span class="trf-field-label">Sampling Location:</span> {{ $collection['sampling_location'] }}
        </td>
        <td style="width: 50%;">
            <span class="trf-field-label">Equipment ID:</span> {{ $collection['thermometer_id'] }}
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <span class="trf-field-label">Sampling Apparatus</span><br>
            {!! $renderChecks($collection['sampling_apparatus']) !!}
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <span class="trf-field-label">Method of Sampling</span><br>
            {!! $renderChecks($collection['method_of_sampling']) !!}
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <span class="trf-field-label">Reason of Collection</span><br>
            {!! $renderChecks($collection['reason_of_collection']) !!}
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <span class="trf-field-label">Transport Condition</span><br>
            {!! $renderChecks($collection['transport_condition']) !!}
        </td>
    </tr>
</table>
