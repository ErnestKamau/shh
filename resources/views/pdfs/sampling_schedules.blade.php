<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sampling Schedules Report</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #333;
            margin: 20px;
        }
        
        /* Simple Header */
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #333;
        }
        
        .header-logo {
            margin-bottom: 10px;
        }
        
        .header-logo img {
            max-height: 50px;
            max-width: 150px;
        }
        
        .company-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .report-title {
            font-size: 12px;
            color: #666;
        }
        
        .generated-at {
            font-size: 9px;
            color: #999;
            margin-top: 5px;
        }
        
        /* Simple Table */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        th {
            background: #f5f5f5;
            padding: 8px 6px;
            text-align: left;
            font-weight: bold;
            font-size: 9px;
            border-bottom: 2px solid #333;
            border-top: 1px solid #ddd;
        }
        
        td {
            padding: 8px 6px;
            border-bottom: 1px solid #ddd;
            vertical-align: top;
        }
        
        tbody tr:nth-child(even) {
            background: #fafafa;
        }
        
        /* Column Styles */
        .col-num {
            text-align: center;
            width: 30px;
            font-weight: bold;
            color: #666;
        }
        
        .col-title {
            font-weight: bold;
        }
        
        .col-client {
            font-size: 9px;
        }
        
        .contact-info {
            color: #666;
            font-size: 8px;
        }
        
        .col-datetime {
            font-size: 9px;
            white-space: nowrap;
        }
        
        .col-samples {
            text-align: center;
            font-weight: bold;
        }
        
        .frequency-badge {
            background: #e9ecef;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 8px;
        }
        
        /* Sample Details - Clean and Simple */
        .sample-box {
            margin-bottom: 8px;
            padding: 6px;
            background: #f8f9fa;
            border-left: 3px solid #666;
        }
        
        .sample-box:last-child {
            margin-bottom: 0;
        }
        
        .sample-type {
            font-weight: bold;
            color: #333;
            margin-bottom: 3px;
        }
        
        .analysis-type {
            font-size: 9px;
            color: #555;
            margin-bottom: 3px;
        }
        
        .parameters {
            font-size: 8px;
            color: #666;
        }
        
        .param-tag {
            display: inline-block;
            background: #e9ecef;
            padding: 1px 5px;
            border-radius: 2px;
            margin-right: 3px;
            margin-bottom: 2px;
        }
        
        /* Footer */
        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            font-size: 8px;
            color: #999;
            text-align: center;
        }
        
        /* Empty State */
        .no-records {
            text-align: center;
            padding: 40px;
            color: #666;
            font-style: italic;
        }
    </style>
</head>
<body>
    <!-- Simple Header -->
    <div class="header">
        <div class="header-logo">
            @if($logoPath && file_exists($logoPath))
                <img src="{{ $logoPath }}" alt="Company Logo">
            @endif
        </div>
        <div class="company-name">{{ $company->name ?? 'IMARA LIMS' }}</div>
        <div class="report-title">Sampling Schedules Report</div>
        <div class="generated-at">Generated on {{ $generatedAt }}</div>
    </div>

    @if($schedules->count() > 0)
    <table>
        <thead>
            <tr>
                <th class="col-num">#</th>
                <th>Title</th>
                <th>Client</th>
                <th>Date & Time</th>
                <th>Location</th>
                <th>Sample Details</th>
                <th>Samples</th>
                <th>Frequency</th>
                <th>Personnel</th>
            </tr>
        </thead>
        <tbody>
            @foreach($schedules as $index => $schedule)
            <tr>
                <td class="col-num">{{ $index + 1 }}</td>
                <td class="col-title">{{ $schedule->title }}</td>
                <td class="col-client">
                    {{ $schedule->client->name ?? 'N/A' }}
                    @php
                        $pdfContacts = $schedule->contacts();
                        $pdfContactNames = $pdfContacts->map(fn ($c) => trim(($c->first_name ?? '').' '.($c->last_name ?? '')))->filter()->implode(', ');
                        if ($pdfContactNames === '' && $schedule->contact) {
                            $pdfContactNames = trim(($schedule->contact->first_name ?? '').' '.($schedule->contact->last_name ?? ''));
                        }
                    @endphp
                    @if($pdfContactNames !== '')
                        <div class="contact-info">{{ $pdfContactNames }}</div>
                    @endif
                </td>
                <td class="col-datetime">
                    {{ $schedule->sampling_datetime ? $schedule->sampling_datetime->format('M d, Y') : 'N/A' }}
                    <br>
                    {{ $schedule->sampling_datetime ? $schedule->sampling_datetime->format('h:i A') : '' }}
                </td>
                <td>{{ $schedule->locationDisplayName() }}</td>
                <td>
                    @php
                        $sampleDetailsList = [];
                        if (!empty($schedule->sample_details) && is_array($schedule->sample_details)) {
                            foreach ($schedule->sample_details as $entry) {
                                $st = \App\SampleType::find($entry['sample_type_id'] ?? null);
                                $at = \App\AnalysisType::find($entry['analysis_type_id'] ?? null);
                                $parameterIds = $entry['parameters'] ?? [];
                                $paramNames = [];
                                if (!empty($parameterIds)) {
                                    $paramNames = \App\Analyte::whereIn('id', $parameterIds)->pluck('name')->toArray();
                                }
                                if ($st) {
                                    $sampleDetailsList[] = [
                                        'sample_type' => $st->name,
                                        'analysis_type' => $at ? $at->name : null,
                                        'parameters' => $paramNames,
                                    ];
                                }
                            }
                        } elseif ($schedule->sample_type) {
                            $parameterIds = $schedule->parameters ?? [];
                            $paramNames = [];
                            if (!empty($parameterIds)) {
                                $paramNames = \App\Analyte::whereIn('id', $parameterIds)->pluck('name')->toArray();
                            }
                            $sampleDetailsList[] = [
                                'sample_type' => $schedule->sample_type->name,
                                'analysis_type' => $schedule->analysis_type ? $schedule->analysis_type->name : null,
                                'parameters' => $paramNames,
                            ];
                        }
                    @endphp
                    @foreach($sampleDetailsList as $detail)
                        <div class="sample-box">
                            <div class="sample-type">{{ $detail['sample_type'] }}</div>
                            @if($detail['analysis_type'])
                            <div class="analysis-type">Analysis: {{ $detail['analysis_type'] }}</div>
                            @endif
                            @if(!empty($detail['parameters']))
                            <div class="parameters">
                                <strong>Parameters ({{ count($detail['parameters']) }}):</strong><br>
                                @foreach($detail['parameters'] as $paramName)
                                    <span class="param-tag">{{ $paramName }}</span>
                                @endforeach
                            </div>
                            @endif
                        </div>
                    @endforeach
                    @if(empty($sampleDetailsList))
                        <span style="color: #999;">—</span>
                    @endif
                </td>
                <td class="col-samples">{{ $schedule->number_of_samples ?? 1 }}</td>
                <td><span class="frequency-badge">{{ $schedule->frequency }}</span></td>
                <td>{{ $schedule->personnelNames() }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="footer">
        Total Records: {{ $schedules->count() }} | Page {PAGE_NUM} of {PAGE_COUNT}
    </div>
    @else
    <div class="no-records">
        No sampling schedules found.
    </div>
    @endif
</body>
</html>
