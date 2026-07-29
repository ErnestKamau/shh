<div class="results-section">
    <div class="table-responsive">
        @foreach($grouped_samples ?? [] as $sampleCode => $group)
        <div class="mb-4">
            <table class="results-table table table-bordered" style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                <thead>
                    <tr style="background-color: #f8f9fa;">
                        <td colspan="5" style="padding: 10px; font-weight: bold; font-size: 11px; text-transform: uppercase;">
                            Laboratory Number: {{ $sampleCode }} |
                            Sample Description: {{ $group['sample']->sample_point ?? $group['sample']->description ?? 'N/A' }} |
                            Batch/Lot/Ref: {{ $group['sample']->reference_number ?? 'N/A' }}
                        </td>
                    </tr>
                    <tr style="background-color: #e9ecef;">
                        <th style="padding: 8px;">Analysis Element</th>
                        <th style="padding: 8px;">Lab Section</th>
                        <th style="padding: 8px;">Units</th>
                        <th style="padding: 8px;">Results</th>
                        <th style="padding: 8px;">Specifications</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($group['results'] as $analyteCode => $result)
                    @php
                    $parameter = collect($parameters ?? [])->first(function($p) use ($analyteCode) {
                    return ($p->analyte_code ?? $p->name) === $analyteCode;
                    });
                    $specification = $standards[$analyteCode] ?? 'NS';
                    $isFailed = is_non_conforming_remark($result['remark'] ?? null);
                    $labSection = $result['lab_section'] ?? $parameter->lab_section_name ?? '-';
                    @endphp
                    <tr>
                        <td style="padding: 6px; text-align: left;">{{ $analyteCode }}</td>
                        <td style="padding: 6px;">{{ $labSection ?: '-' }}</td>
                        <td style="padding: 6px;">{{ $parameter->reporting_unit_name ?? $parameter->reporting_unit_id ?? '-' }}</td>
                        <td style="padding: 6px;" class="{{ $isFailed ? 'failed-result text-danger' : '' }} fw-bold">
                            {{ $result['value'] }}
                        </td>
                        <td style="padding: 6px;">{{ $specification }}</td>
                    </tr>
                    @endforeach
                    <tr style="background-color: #f8f9fa;">
                        <td colspan="5" style="padding: 8px; font-weight: bold;">
                            Action/Recommendation:
                            <span class="{{ is_conforming_remark($group['conformity'] ?? null) ? 'text-success' : 'text-danger' }}">
                                {{ format_result_remark($group['conformity'] ?? 'PASS') }}
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        @endforeach

        @foreach($ungrouped_samples ?? [] as $sampleData)
        @php $sampleCode = $sampleData['sample']->sample_code; @endphp
        <div class="mb-4">
            <table class="results-table table table-bordered" style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                <thead>
                    <tr style="background-color: #f8f9fa;">
                        <td colspan="5" style="padding: 10px; font-weight: bold; font-size: 11px; text-transform: uppercase;">
                            Laboratory Number: {{ $sampleCode }} |
                            Sample Description: {{ $sampleData['sample']->sample_point ?? $sampleData['sample']->description ?? 'N/A' }} |
                            Batch/Lot/Ref: {{ $sampleData['sample']->reference_number ?? 'N/A' }}
                        </td>
                    </tr>
                    <tr style="background-color: #e9ecef;">
                        <th style="padding: 8px;">Analysis Element</th>
                        <th style="padding: 8px;">Lab Section</th>
                        <th style="padding: 8px;">Units</th>
                        <th style="padding: 8px;">Results</th>
                        <th style="padding: 8px;">Specifications</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sampleData['results'] as $analyteCode => $result)
                    @php
                    $parameter = collect($parameters ?? [])->first(function($p) use ($analyteCode) {
                    return ($p->analyte_code ?? $p->name) === $analyteCode;
                    });
                    $specification = $standards[$analyteCode] ?? 'NS';
                    $isFailed = is_non_conforming_remark($result['remark'] ?? null);
                    $labSection = $result['lab_section'] ?? $parameter->lab_section_name ?? '-';
                    @endphp
                    <tr>
                        <td style="padding: 6px; text-align: left;">{{ $analyteCode }}</td>
                        <td style="padding: 6px;">{{ $labSection ?: '-' }}</td>
                        <td style="padding: 6px;">{{ $parameter->reporting_unit_name ?? $parameter->reporting_unit_id ?? '-' }}</td>
                        <td style="padding: 6px;" class="{{ $isFailed ? 'failed-result text-danger' : '' }} fw-bold">
                            {{ $result['value'] }}
                        </td>
                        <td style="padding: 6px;">{{ $specification }}</td>
                    </tr>
                    @endforeach
                    <tr style="background-color: #f8f9fa;">
                        <td colspan="5" style="padding: 8px; font-weight: bold;">
                            Action/Recommendation:
                            <span class="{{ is_conforming_remark($sampleData['conformity'] ?? null) ? 'text-success' : 'text-danger' }}">
                                {{ format_result_remark($sampleData['conformity'] ?? 'PASS') }}
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        @endforeach
    </div>

    <div class="decision-rule mt-3">
        <p><strong>Decision Rule/Key:</strong></p>
        <p>• NS - No Specification</p>
        <p>• ND - Not Detected</p>
    </div>
</div>
