<div class="results-section">
    <div class="section-title">{{ $custom_title ?? 'Results :' }}</div>
    <table class="results-table">
        <thead>
            <tr>
                <th rowspan="2">Sample ID</th>
                <th rowspan="2">Sample Point</th>
                @foreach($parameters ?? [] as $parameter)
                <th>{{ $parameter->analyte_code ?? $parameter->name }}</th>
                @endforeach
                <th rowspan="2">Statement of Conformity. Pass/Fail</th>
            </tr>
            <tr>
                @foreach($parameters ?? [] as $parameter)
                <th>({{ $parameter->reporting_unit_name ?? $parameter->reporting_unit_id ?? '-' }})</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($grouped_samples ?? [] as $areaName => $areaSamples)
            <tr>
                <td class="sample-area-header" colspan="{{ 3 + count($parameters ?? []) }}">
                    {{ $areaName }}
                </td>
            </tr>
            @foreach($areaSamples as $sampleData)
            <tr>
                <td class="sample-id">{{ $sampleData['sample']->sample_code }}</td>
                <td class="sample-id">{!! $sampleData['sample']->sample_point ?? 'Sample' !!}</td>
                @foreach($parameters as $parameter)
                @php
                $analyteCode = $parameter->analyte_code ?? $parameter->name;
                $resultData = $sampleData['results'][$analyteCode] ?? null;
                $value = $resultData['value'] ?? 'N/A';
                $remark = strtolower($resultData['remark'] ?? 'pass');
                $isFailed = in_array($remark, ['fail', 'failed', 'failure', 'rejected']);
                @endphp
                <td class="{{ $isFailed ? 'failed-result' : '' }}">
                    {{ $value }}
                </td>
                @endforeach
                <td class="{{ strtolower($sampleData['conformity'] ?? 'pass') == 'pass' ? 'conformity-pass' : 'conformity-fail' }}">
                    {{ $sampleData['conformity'] ?? 'Pass' }}
                </td>
            </tr>
            @endforeach
            @endforeach

            @foreach($ungrouped_samples ?? [] as $sampleData)
            <tr>
                <td class="sample-id">{{ $sampleData['sample']->sample_code }}</td>
                <td class="sample-id">{!! $sampleData['sample']->sample_point ?? 'Sample' !!}</td>
                @foreach($parameters as $parameter)
                @php
                $analyteCode = $parameter->analyte_code ?? $parameter->name;
                $resultData = $sampleData['results'][$analyteCode] ?? null;
                $value = $resultData['value'] ?? 'N/A';
                $remark = strtolower($resultData['remark'] ?? 'pass');
                $isFailed = in_array($remark, ['fail', 'failed', 'failure', 'rejected']);
                @endphp
                <td class="{{ $isFailed ? 'failed-result' : '' }}">
                    {{ $value }}
                </td>
                @endforeach
                <td class="{{ strtolower($sampleData['conformity'] ?? 'pass') == 'pass' ? 'conformity-pass' : 'conformity-fail' }}">
                    {{ $sampleData['conformity'] ?? 'Pass' }}
                </td>
            </tr>
            @endforeach

            <tr class="standards-row">
                <td class="standard-label" colspan="2">
                    {{ $standard_codes ?? 'Standards' }}
                </td>
                @foreach($parameters as $parameter)
                <td>
                    {{ $standards[$parameter->analyte_code ?? $parameter->name] ?? 'NS' }}
                </td>
                @endforeach
                <td>-</td>
            </tr>
        </tbody>
    </table>

    <div class="decision-rule">
        <p><strong>Decision Rule:</strong></p>
        <p>• <strong>PASS</strong> - Sample meets the acceptable standards</p>
        <p>• <strong>FAIL</strong> - Sample does not meet one or more acceptable standards</p>
    </div>
</div>