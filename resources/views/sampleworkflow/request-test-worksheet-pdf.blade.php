@php
    $maroon = $branding['primary'] ?? \App\Services\System\ThemeService::PRIMARY;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $payload['title'] ?? 'Request Tests Worksheet' }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 13mm 12mm 15mm;
        }

        * {
            box-sizing: border-box;
            font-family: DejaVu Sans, sans-serif;
        }

        body {
            margin: 0;
            padding: 0;
            color: #000;
            font-size: 9.5px;
            line-height: 1.35;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .document-header td {
            border: 1px solid #000;
            padding: 7px;
            vertical-align: middle;
        }

        .logo-cell {
            width: 16%;
            text-align: center;
        }

        .logo {
            max-width: 86px;
            max-height: 58px;
            height: auto;
        }

        .title-cell {
            width: 56%;
            text-align: center;
        }

        .authority-name {
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.25px;
            color: {{ $maroon }};
        }

        .document-title {
            margin-top: 4px;
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .document-subtitle {
            margin-top: 2px;
            color: #333;
            font-size: 8.5px;
        }

        .official-cell {
            width: 28%;
            font-size: 8.5px;
        }

        .official-label {
            font-weight: bold;
            text-transform: uppercase;
            text-decoration: underline;
            color: {{ $maroon }};
        }

        .reference-value {
            display: block;
            margin-top: 5px;
            padding: 4px 2px 2px;
            border-bottom: 1px dotted #000;
            font-size: 10px;
            font-weight: bold;
            overflow-wrap: break-word;
        }

        .section-title {
            margin-top: 10px;
            padding: 6px 8px;
            border: 1px solid #000;
            background: {{ $maroon }};
            color: #fff;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.35px;
            text-align: center;
        }

        .info-grid {
            border: 1px solid #000;
            border-top: 0;
        }

        .info-grid td {
            width: 50%;
            padding: 5px 7px;
            border-right: 1px solid #000;
            border-bottom: 1px solid #000;
            vertical-align: top;
        }

        .info-grid td:last-child {
            border-right: 0;
        }

        .field-label {
            display: block;
            margin-bottom: 2px;
            color: {{ $maroon }};
            font-size: 7.5px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .field-value {
            color: #000;
            font-size: 9.5px;
        }

        .remarks-row {
            padding: 5px 7px;
            border-right: 0;
        }

        .lab-section {
            margin-top: 10px;
            page-break-inside: avoid;
        }

        .lab-section-heading {
            padding: 5px 7px;
            border: 1px solid #000;
            background: {{ $maroon }};
            color: #fff;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .sample-block {
            margin-top: 6px;
            page-break-inside: avoid;
        }

        .sample-heading {
            padding: 4px 6px;
            border: 1px solid #000;
            border-bottom: 0;
            background: #f3e9e9;
            font-weight: bold;
        }

        .sample-code {
            float: right;
            color: #444;
            font-size: 8px;
            font-weight: normal;
        }

        .grid {
            table-layout: fixed;
        }

        .grid th,
        .grid td {
            padding: 4px 5px;
            border: 1px solid #000;
            vertical-align: top;
            overflow-wrap: break-word;
        }

        .grid th {
            background: #d9d9d9;
            color: #000;
            font-size: 8px;
            font-weight: bold;
            text-align: left;
            text-transform: uppercase;
        }

        .serial {
            width: 6%;
            text-align: center;
        }

        .result-cell {
            height: 25px;
        }

        .status-text {
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            color: {{ $maroon }};
        }

        .muted {
            color: #555;
        }

        .empty-state {
            margin-top: 10px;
            padding: 12px;
            border: 1px solid #000;
            text-align: center;
        }

        .document-footer {
            position: fixed;
            right: 0;
            bottom: -8mm;
            left: 0;
            padding-top: 4px;
            border-top: 1px solid #000;
            color: #444;
            font-size: 7.5px;
        }

        .footer-right {
            float: right;
        }
    </style>
</head>
<body>
    @php
        $fields = is_array($payload['request_info']['fields'] ?? null) ? $payload['request_info']['fields'] : [];
        $remarks = $payload['request_info']['remarks'] ?? null;
        $sections = is_array($payload['sections'] ?? null) ? $payload['sections'] : [];
        $includeResult = (bool) ($payload['include_result_column'] ?? false);
        $fieldRows = array_chunk($fields, 2);
        $catalog = is_array($payload['catalog'] ?? null) ? $payload['catalog'] : null;
        $isIntegrity = ($payload['context'] ?? '') === 'integrity' && $catalog !== null;
        $catalogClient = is_array($catalog['client'] ?? null) ? $catalog['client'] : [];
        $catalogAnalysis = is_array($catalog['analysis'] ?? null) ? $catalog['analysis'] : [];
        $catalogSamples = is_array($catalog['samples'] ?? null) ? $catalog['samples'] : [];
        $catalogCollection = is_array($catalog['collection'] ?? null) ? $catalog['collection'] : [];
    @endphp

    <table class="document-header">
        <tr>
            <td class="logo-cell">
                @if(!empty($logoSrc))
                    <img src="{{ $logoSrc }}" class="logo" alt="Logo">
                @endif
            </td>
            <td class="title-cell">
                <div class="authority-name">{{ config('app.name', 'Laboratory Management System') }}</div>
                <div class="document-title">{{ $payload['title'] ?? 'Request Tests Worksheet' }}</div>
                <div class="document-subtitle">Sample and laboratory test assignment schedule</div>
            </td>
            <td class="official-cell">
                <div class="official-label">Official use only</div>
                <div style="margin-top: 5px;">Request / Lab No.</div>
                <span class="reference-value">{{ $payload['reference'] ?? '—' }}</span>
            </td>
        </tr>
    </table>

    @if($isIntegrity)
        <div class="section-title">1. Client / Customer information</div>
        @include('sampleworkflow.partials.integrity-pdf-field-grid', ['fields' => $catalogClient, 'empty' => 'No client information available.'])

        <div class="section-title">2. Sample details</div>
        @if($catalogSamples === [])
            <div class="empty-state muted">No sample details available.</div>
        @else
            @foreach($catalogSamples as $catalogSample)
                <div class="sample-block">
                    <div class="sample-heading">
                        Sample: {{ $catalogSample['label'] ?? 'Sample' }}
                        @if(!empty($catalogSample['customer_sample_id']))
                            <span class="sample-code">Customer sample ID: {{ $catalogSample['customer_sample_id'] }}</span>
                        @endif
                    </div>
                    @include('sampleworkflow.partials.integrity-pdf-field-grid', [
                        'fields' => is_array($catalogSample['fields'] ?? null) ? $catalogSample['fields'] : [],
                        'empty' => 'No sample attributes recorded.',
                    ])
                </div>
            @endforeach
        @endif

        <div class="section-title">3. Sample collection information</div>
        @include('sampleworkflow.partials.integrity-pdf-field-grid', ['fields' => $catalogCollection, 'empty' => 'No collection information available.'])
        @if(!empty($remarks))
            <table class="info-grid">
                <tr>
                    <td colspan="2" class="remarks-row">
                        <span class="field-label">Remarks</span>
                        <span class="field-value">{{ $remarks }}</span>
                    </td>
                </tr>
            </table>
        @endif
    @else
        <div class="section-title">Part A: Request Information</div>
        @if($fields === [])
            <div class="empty-state muted">No request information available.</div>
        @else
            <table class="info-grid">
                @foreach($fieldRows as $fieldRow)
                    <tr>
                        @foreach($fieldRow as $field)
                            <td>
                                <span class="field-label">{{ $field['label'] ?? '' }}</span>
                                <span class="field-value">{{ $field['value'] ?? '—' }}</span>
                            </td>
                        @endforeach
                        @if(count($fieldRow) === 1)
                            <td></td>
                        @endif
                    </tr>
                @endforeach
                @if(!empty($remarks))
                    <tr>
                        <td colspan="2" class="remarks-row">
                            <span class="field-label">Remarks</span>
                            <span class="field-value">{{ $remarks }}</span>
                        </td>
                    </tr>
                @endif
            </table>
        @endif
    @endif

    <div class="section-title">{{ $isIntegrity ? '4. Samples and tests by laboratory section' : 'Part B: Samples and Tests by Laboratory Section' }}</div>
    @if($sections === [])
        <div class="empty-state muted">No samples or tests found.</div>
    @else
        @foreach($sections as $section)
            <div class="lab-section">
                <div class="lab-section-heading">
                    Laboratory Section: {{ $section['lab_section_name'] ?? 'Lab section' }}
                </div>

                @foreach(($section['samples'] ?? []) as $sample)
                    <div class="sample-block">
                        <div class="sample-heading">
                            Sample: {{ $sample['sample_label'] ?? ($sample['sample_code'] ?? 'Sample') }}
                            @if(!empty($sample['sample_code']) && ($sample['sample_code'] ?? '') !== ($sample['sample_label'] ?? ''))
                                <span class="sample-code">Lab No: {{ $sample['sample_code'] }}</span>
                            @endif
                        </div>

                        <table class="grid">
                            <thead>
                                <tr>
                                    <th class="serial">S/No</th>
                                    <th style="width: {{ $includeResult ? '35%' : '43%' }};">Parameter / Test</th>
                                    <th style="width: {{ $includeResult ? '24%' : '31%' }};">Assigned Analyst(s)</th>
                                    <th style="width: {{ $includeResult ? '15%' : '20%' }};">Remarks</th>
                                    @if($includeResult)
                                        <th style="width: 20%;">Result</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse(($sample['tests'] ?? []) as $testIndex => $test)
                                    <tr>
                                        <td class="serial">{{ $testIndex + 1 }}</td>
                                        <td>{{ $test['test_label'] ?? 'Test' }}</td>
                                        <td>{{ $test['analysts'] ?? 'None assigned' }}</td>
                                        <td>
                                            @if(!empty($test['subcontracted']))
                                                <span class="status-text">Subcontracted</span>
                                            @else
                                                <span class="muted">—</span>
                                            @endif
                                        </td>
                                        @if($includeResult)
                                            <td class="result-cell">{{ $test['result'] ?? '' }}</td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $includeResult ? 5 : 4 }}" class="muted">No tests</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @endforeach
            </div>
        @endforeach
    @endif

    <div class="document-footer">
        <span>Generated from the Laboratory Management System</span>
        <span class="footer-right">Generated: {{ $generatedAt ?? now()->format('Y-m-d H:i') }}</span>
    </div>
</body>
</html>
