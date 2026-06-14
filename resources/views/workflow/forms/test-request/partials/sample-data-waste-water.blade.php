@php
    $renderChecks = function (array $checks) {
        $html = '';
        foreach ($checks as $label => $checked) {
            $class = $checked ? 'trf-check trf-check-on' : 'trf-check trf-check-off';
            $html .= '<span class="' . $class . '"></span> ' . e($label) . ' ';
        }
        return $html;
    };
    $ww = $wasteWaterFields ?? [];
    $wwColspan = 12;
@endphp
<table class="trf-table trf-no-gap">
    <tr class="trf-banner-row">
        <td colspan="{{ $wwColspan }}">SAMPLE COLLECTION DATA</td>
    </tr>
    <tr>
        <td colspan="3"><span class="trf-field-label">Sampling Date:</span> {{ $collection['sampling_date'] ?? '' }}</td>
        <td colspan="3"><span class="trf-field-label">Sampling Time:</span> {{ $collection['sampling_time'] ?? '' }}</td>
        <td colspan="3"><span class="trf-field-label">Sample Number:</span> {{ $ww['sample_number'] ?? '' }}</td>
        <td colspan="3"><span class="trf-field-label">Transport Condition:</span> {!! $renderChecks($ww['transport_condition'] ?? []) !!}</td>
    </tr>
    <tr>
        <td colspan="6"><span class="trf-field-label">Sampling Location:</span> {{ $collection['sampling_location'] ?? '' }}</td>
        <td colspan="6"><span class="trf-field-label">Sample &amp; Sampling Point Description:</span> {{ $ww['sample_description'] ?? '' }}</td>
    </tr>
    <tr class="trf-banner-row">
        <td colspan="{{ $wwColspan }}">SAMPLING APPARATUS &amp; TECHNIQUES</td>
    </tr>
    <tr>
        <td colspan="4"><span class="trf-field-label">Sampling Apparatus:</span> {!! $renderChecks($ww['sampling_apparatus'] ?? []) !!}</td>
        <td colspan="4"><span class="trf-field-label">Method of Sampling:</span> {!! $renderChecks($ww['method_of_sampling'] ?? []) !!}</td>
        <td colspan="4"><span class="trf-field-label">Reason of Collection:</span> {!! $renderChecks($ww['reason_of_collection'] ?? []) !!}</td>
    </tr>
    <tr>
        <td colspan="3"><span class="trf-field-label">Thermometer ID:</span> {{ $ww['thermometer_id'] ?? '' }}</td>
        <td colspan="3"><span class="trf-field-label">pH Meter:</span> {{ $ww['ph_meter_id'] ?? '' }}</td>
        <td colspan="3"><span class="trf-field-label">Chlorine Meter ID:</span> {{ $ww['chlorine_meter_id'] ?? '' }}</td>
        <td colspan="3"><span class="trf-field-label">Sampling Technique:</span> {!! $renderChecks($ww['sampling_technique'] ?? []) !!}</td>
    </tr>
    <tr>
        <td colspan="12"><span class="trf-field-label">Sampling Apparatus Others:</span> {{ $ww['sampling_apparatus_others'] ?? '' }}</td>
    </tr>
    <tr class="trf-banner-row">
        <td colspan="{{ $wwColspan }}">SAMPLING SOURCE &amp; TYPES</td>
    </tr>
    <tr>
        <td colspan="6"><span class="trf-field-label">Sampling Source:</span> {!! $renderChecks($ww['sampling_source'] ?? []) !!}</td>
        <td colspan="6"><span class="trf-field-label">Sample Types:</span> {!! $renderChecks($ww['sample_types_ww'] ?? []) !!}</td>
    </tr>
    <tr class="trf-banner-row">
        <td colspan="{{ $wwColspan }}">FIELD DATA &amp; REQUIREMENTS</td>
    </tr>
    <tr>
        <th>QUANTITY</th>
        <th>APPEARANCE</th>
        <th>COLOR</th>
        <th>ODOR</th>
        <th>pH</th>
        <th>TEMPERATURE</th>
        <th>FREE CHLORINE</th>
        <th colspan="5">REQUIREMENTS</th>
    </tr>
    <tr>
        <td class="trf-center">{{ $ww['field_data_quantity'] ?? '' }}</td>
        <td class="trf-center">{{ $ww['field_data_appearance'] ?? '' }}</td>
        <td class="trf-center">{{ $ww['field_data_color'] ?? '' }}</td>
        <td class="trf-center">{{ $ww['field_data_odor'] ?? '' }}</td>
        <td class="trf-center">{{ $ww['field_data_ph'] ?? '' }}</td>
        <td class="trf-center">{{ $ww['field_data_temperature'] ?? '' }}</td>
        <td class="trf-center">{{ $ww['field_data_free_chlorine'] ?? '' }}</td>
        <td colspan="5">{!! $renderChecks($ww['field_data_requirements'] ?? []) !!}</td>
    </tr>
    @include('workflow.forms.test-request.partials.footer-rows', ['footerColspan' => $wwColspan, 'variant' => 'waste_water'])
</table>
