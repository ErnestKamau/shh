<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Detailed Laboratory Report</title>
    <style>
        @page         { margin: 2cm; margin-top: 1.6cm; }
        @page :first  { margin-top: 2cm; }

        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #1e293b; line-height: 1.6; margin: 0; padding: 0; background-color: #fff; }
        .header { margin-bottom: 30px; border-bottom: 2px solid #3b82f6; padding-bottom: 20px; }
        .container { width: 100%; }
        .section-title { font-size: 14px; font-weight: 800; color: #1e40af; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 12px; border-left: 4px solid #3b82f6; padding-left: 10px; }
        .report-section { page-break-inside: avoid; margin-bottom: 35px; }
        
        /* KPI Grid */
        .kpi-row { width: 100%; margin-bottom: 20px; }
        .kpi-card { background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 15px; text-align: center; }
        .kpi-value { font-size: 26px; font-weight: 800; color: #2563eb; margin-bottom: 2px; }
        .kpi-label { font-size: 10px; text-transform: uppercase; color: #64748b; font-weight: 700; }
        
        /* Tables */
        .data-table { width: 100%; border-collapse: collapse; background-color: #fff; }
        .data-table th { background-color: #f1f5f9; padding: 12px 10px; text-align: left; font-size: 10px; font-weight: 700; color: #475569; text-transform: uppercase; border-bottom: 1px solid #e2e8f0; }
        .data-table td { padding: 12px 10px; border-bottom: 1px solid #f1f5f9; font-size: 11px; color: #334155; }
        .font-bold { font-weight: 700; }
        
        /* Status Badges */
        .badge { padding: 3px 8px; border-radius: 4px; font-size: 9px; font-weight: 700; text-transform: uppercase; }
        .badge-success { background-color: #dcfce7; color: #166534; }
        .badge-danger { background-color: #fee2e2; color: #991b1b; }
        .badge-warning { background-color: #fef9c3; color: #854d0e; }
        .badge-primary { background-color: #e0e7ff; color: #3730a3; }
        
        /* Charts */
        .chart-container { text-align: center; padding: 20px; border: 1px solid #f1f5f9; border-radius: 12px; background-color: #fafafa; margin-top: 10px; }
        .chart-img { max-width: 100%; height: auto; display: block; margin: 0 auto; max-height: 350px; }
        
        .footer { position: fixed; bottom: 0; left: 0; right: 0; text-align: center; font-size: 10px; color: #94a3b8; border-top: 1px solid #f1f5f9; padding-top: 15px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
{{-- Resolve logo path for the main first-page header. Repeated page logos are drawn by DomPDF canvas. --}}
@php
    $resolvedLogoPath = null;
    if (isset($company) && !empty($company->logo)) {
        $rawLogo = $company->logo;
        if (str_starts_with($rawLogo, 'http')) {
            $resolvedLogoPath = $rawLogo;
        } else {
            $local = public_path(ltrim($rawLogo, '/'));
            if (file_exists($local)) $resolvedLogoPath = $local;
        }
    }
    if (!$resolvedLogoPath) {
        $fallback = public_path('assets/branding/logo.jpeg');
        if (file_exists($fallback)) $resolvedLogoPath = $fallback;
    }
@endphp

    <div class="footer">
        Confidential | {{ $company->name ?? 'IMARA LIMS' }} | {{ date('Y') }}
    </div>

    <div class="container">

        <!-- Header -->
        <div class="header" style="border-bottom: 2px solid #3b82f6; padding-bottom: 15px; margin-bottom: 25px;">
            <table style="width: 100%;">
                <tr>
                    {{-- Left Cell: Filter Metadata --}}
                    <td style="width: 40%; vertical-align: middle; text-align: left;">
                        @if(isset($selectedFilters))
                        <div style="font-size: 10px; color: #475569; line-height: 1.45;">
                            <div><span style="font-size: 8px; font-weight: 800; color: #64748b; text-transform: uppercase; display: inline-block; width: 65px;">Section:</span> <strong style="color: #1e293b;">{{ $selectedFilters['lab_section'] }}</strong></div>
                            <div><span style="font-size: 8px; font-weight: 800; color: #64748b; text-transform: uppercase; display: inline-block; width: 65px;">Zone:</span> <strong style="color: #1e293b;">{{ $selectedFilters['zone'] }}</strong></div>
                            <div><span style="font-size: 8px; font-weight: 800; color: #64748b; text-transform: uppercase; display: inline-block; width: 65px;">Analyst:</span> <strong style="color: #1e293b;">{{ $selectedFilters['analyst'] }}</strong></div>
                            <div><span style="font-size: 8px; font-weight: 800; color: #64748b; text-transform: uppercase; display: inline-block; width: 65px;">Range:</span> <strong style="color: #1e293b;">{{ $selectedFilters['start_date'] }} - {{ $selectedFilters['end_date'] }}</strong></div>
                        </div>
                        @endif
                    </td>

                    {{-- Center Cell: Company Logo (reuses already-resolved path) --}}
                    <td style="width: 20%; text-align: center; vertical-align: middle;">
                        @if($resolvedLogoPath)
                            <img src="{{ $resolvedLogoPath }}" style="height: 55px; max-width: 140px; object-fit: contain;">
                        @else
                            <div style="font-size: 16px; font-weight: bold; color: #1e40af;">{{ $company->name ?? 'GCLA' }}</div>
                        @endif
                    </td>

                    {{-- Right Cell: Title & Date --}}
                    <td style="width: 40%; text-align: right; vertical-align: middle;">
                        <div style="font-size: 22px; font-weight: 900; color: #1e40af; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 2px;">TAT ANALYSIS</div>
                        <div style="font-size: 10px; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">{{ __('mas/dashboard.insights_report') }}</div>
                        <div style="font-size: 9px; color: #94a3b8;">{{ __('mas/report.generated') }}: {{ date('d M Y, H:i') }}</div>
                        <div style="font-size: 9px; color: #3b82f6; font-weight: 700; margin-top: 5px;">REF #{{ date('Ymd') }}-TAT | CONFIDENTIAL OPERATIONAL DATA</div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- KPI Overview -->
        <div class="report-section">
            <div class="section-title">TAT Performance Metrics</div>

            {{-- Row 1: Parameter & Compliance KPIs --}}
            <table style="width: 100%; border-spacing: 12px 0; margin-left: -12px; margin-bottom: 12px;">
                <tr>
                    <td style="width: 33.33%;">
                        <div class="kpi-card">
                            <div class="kpi-value">{{ number_format($stats['testing_metrics']['total_params'] ?? 0) }}</div>
                            <div class="kpi-label" style="font-size: 9px;">Number of Params</div>
                        </div>
                    </td>
                    <td style="width: 33.33%;">
                        <div class="kpi-card">
                            <div class="kpi-value" style="color: #10b981;">{{ $stats['testing_metrics']['tested_vs_requested'] ?? 0 }}%</div>
                            <div class="kpi-label" style="font-size: 9px;">% Tested vs Req</div>
                        </div>
                    </td>
                    <td style="width: 33.33%;">
                        <div class="kpi-card">
                            <div class="kpi-value" style="color: #3b82f6;">{{ $stats['testing_metrics']['tat_compliance_tes'] ?? 0 }}%</div>
                            <div class="kpi-label" style="font-size: 9px;">% TAT Compliance (TES)</div>
                        </div>
                    </td>
                </tr>
            </table>

            {{-- Row 2: TAT Durations & Delivery Compliance --}}
            <table style="width: 100%; border-spacing: 12px 0; margin-left: -12px;">
                <tr>
                    <td style="width: 33.33%;">
                        <div class="kpi-card">
                            <div class="kpi-value">{{ $stats['testing_metrics']['avg_tat_tes'] ?? 0 }} d</div>
                            <div class="kpi-label" style="font-size: 9px;">Avg TAT (Days)</div>
                        </div>
                    </td>
                    <td style="width: 33.33%;">
                        <div class="kpi-card" style="background-color: #f1f5f9; border-color: #cbd5e1;">
                            <div class="kpi-value" style="color: #334155;">{{ $stats['testing_metrics']['avg_delivery_tat'] ?? 0 }} d</div>
                            <div class="kpi-label" style="font-size: 9px;">Avg Delivery TAT</div>
                        </div>
                    </td>
                    <td style="width: 33.33%;">
                        <div class="kpi-card" style="background-color: #f1f5f9; border-color: #cbd5e1;">
                            <div class="kpi-value" style="color: #475569;">{{ $stats['testing_metrics']['delivery_compliance'] ?? 0 }}%</div>
                            <div class="kpi-label" style="font-size: 9px;">TAT Compliance (Deliv)</div>
                        </div>
                    </td>
                </tr>
            </table>

            {{-- Formulas Legend --}}
            <div style="margin-top: 20px; background-color: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; padding: 15px 24px;">
                <span style="font-size: 12px; font-weight: 900; color: #1e3a8a; text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 10px;">KPI Calculation Formulas Reference</span>
                <table style="width: 100%; border-collapse: collapse; font-size: 13px; color: #1e293b; line-height: 1.55;">
                    <tr>
                        <td style="width: 50%; padding-right: 25px; vertical-align: top; border-right: 1px solid #cbd5e1;">
                            <div><strong>1. Number of Params:</strong> Count of unique analytes/parameters tested.</div>
                            <div style="margin-top: 6px;"><strong>2. % Tested vs Req:</strong> <span style="font-family: monospace; font-size: 12px; font-weight: bold; color: #0f172a;">(Completed Parameters / Total Requested Parameters) &times; 100</span></div>
                            <div style="margin-top: 6px;"><strong>3. % TAT Compliance (TES):</strong> <span style="font-family: monospace; font-size: 12px; font-weight: bold; color: #0f172a;">(Parameters completed within SLA / Total Completed) &times; 100</span></div>
                        </td>
                        <td style="width: 50%; padding-left: 25px; vertical-align: top;">
                            <div><strong>4. Avg TAT (Days):</strong> <span style="font-family: monospace; font-size: 12px; font-weight: bold; color: #0f172a;">Average (Analysis Completion Date - Lab Receipt Date)</span></div>
                            <div style="margin-top: 6px;"><strong>5. Avg Delivery TAT:</strong> <span style="font-family: monospace; font-size: 12px; font-weight: bold; color: #0f172a;">Average (Report Collection/Release Date - Reception Date)</span></div>
                            <div style="margin-top: 6px;"><strong>6. TAT Compliance (Deliv):</strong> <span style="font-family: monospace; font-size: 12px; font-weight: bold; color: #0f172a;">(Batches released within SLA / Total Dispatched Batches) &times; 100</span></div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Workflow Stage Pipeline -->
        <div class="report-section">
            <div class="section-title">Workflow Stage Pipeline</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Workflow Stage</th>
                        <th class="text-center">Total Batches</th>
                        <th class="text-center">Completed</th>
                        <th class="text-center">Due Today</th>
                        <th class="text-center">Avg Days</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stats['stage_summary'] ?? [] as $stage)
                    @php
                        $total = (int) ($stage['total_batches'] ?? 0);
                        $completed = (int) ($stage['completed_batches'] ?? max($total - (int) ($stage['overdue_batches'] ?? 0), 0));
                        $completionRate = (int) ($stage['completion_rate'] ?? ($total > 0 ? round(($completed / $total) * 100) : 0));
                    @endphp
                    <tr>
                        <td class="font-bold">{{ $stage['workflow_stage'] ?? 'N/A' }}</td>
                        <td class="text-center">
                            <span class="badge badge-primary">{{ number_format($total) }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge badge-success">{{ number_format($completed) }}</span>
                        </td>
                        <td class="text-center">{{ number_format($stage['due_today_batches'] ?? 0) }}</td>
                        <td class="text-center">
                            {{ ($stage['avg_completion_days'] ?? null) !== null ? number_format($stage['avg_completion_days'], 1) . ' d' : '—' }}
                        </td>
                        <td class="text-center">
                            <span class="badge badge-success">{{ number_format($completed) }}/{{ number_format($total) }} ({{ $completionRate }}%)</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted small">No workflow stage data available.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(($stats['pivot']['total'] ?? 0) <= 45)
        <!-- Parameters Tested Pivot Table (<= 45 params) -->
        <div class="report-section" style="page-break-before: always;">
            <div class="section-title">Number of Parameters Tested</div>
            <p style="font-size: 10px; color: #64748b; margin-bottom: 15px;">
                This table displays the volume breakdown of analytical parameter tests performed across laboratory sections and operational periods.
            </p>
            @php
                $pivotRows = $stats['pivot']['rows'] ?? [];
                $headers = $stats['pivot']['headers'] ?? [];
            @endphp
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="min-width: 160px; text-align: left;">{{ $stats['pivot']['row_label'] ?? 'Lab Section' }}</th>
                        @foreach($headers as $header)
                            <th class="text-center">{{ $header }}</th>
                        @endforeach
                        <th class="text-right" style="padding-right: 15px;">Grand Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pivotRows as $row)
                    <tr>
                        <td class="font-bold border-left" style="border-left: 3px solid #3b82f6 !important; padding-left: 10px;">
                            {{ $row['section'] ?? $row['name'] ?? 'N/A' }}
                        </td>
                        @foreach($headers as $header)
                            <td class="text-center">{{ number_format($row['months'][$header] ?? 0) }}</td>
                        @endforeach
                        <td class="text-right font-bold" style="color: #1e40af; padding-right: 15px;">
                            {{ number_format($row['total']) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ count($headers) + 2 }}" class="text-center py-4 text-muted small">No parameters tested data available.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @endif

        <!-- Distribution & Trends -->
        <div class="report-section">
            <table style="width: 100%;">
                <tr>
                    <td style="width: 48%; vertical-align: top; padding-right: 2%;">
                        <div class="section-title">{{ __('mas/dashboard.aging_distribution') }}</div>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Status Bucket</th>
                                    <th class="text-right">Batches</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stats['aging_buckets'] ?? [] as $bucket)
                                <tr>
                                    <td>{{ $bucket['label'] }}</td>
                                    <td class="text-right font-bold">{{ $bucket['batch_count'] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </td>
                    <td style="width: 48%; vertical-align: top;">
                        <div class="section-title">Top Analyte Throughput</div>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Analyte Module</th>
                                    <th class="text-right">Volume</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(array_slice($stats['charts']['throughput_labels'] ?? [], 0, 8) as $index => $label)
                                <tr>
                                    <td class="font-bold">{{ $label }}</td>
                                    <td class="text-right">{{ number_format($stats['charts']['throughput_counts'][$index] ?? 0) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Captured Visuals -->
        @if($chartImage)
        <div class="report-section">
            <div class="section-title">Analytical Visuals</div>
            <div class="chart-container">
                <img src="{{ $chartImage }}" class="chart-img" />
            </div>
            <div class="text-center" style="font-size: 8px; color: #94a3b8; margin-top: 5px;">* Live data snapshot captured at time of export.</div>
        </div>
        @endif

        <!-- Performance Leaderboard & Workload Distribution -->
        <div class="report-section">
            <div class="section-title">Laboratory Performance & Workload Distribution</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 30%;">{{ __('mas/lab.lab_section') }}</th>
                        <th class="text-center">Tests Completed</th>
                        <th class="text-center">Share %</th>
                        <th class="text-center">Avg. TAT</th>
                        <th class="text-center">TAT Status</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totalWorkload = collect($stats['sections']['leaderboard'] ?? [])->sum('total') ?: 1;
                    @endphp
                    @foreach($stats['sections']['leaderboard'] ?? [] as $section)
                    <tr>
                        <td class="font-bold border-left" style="border-left: 3px solid #3b82f6 !important; padding-left: 10px;">
                            {{ $section['name'] }}
                            <div style="font-size: 9px; color: #64748b; margin-top: 2px;">SEC-ID: {{ $section['code'] }}</div>
                        </td>
                        <td class="text-center font-bold">{{ number_format($section['total']) }} tests</td>
                        <td class="text-center font-bold" style="color: #3b82f6;">
                            {{ number_format(($section['total'] / $totalWorkload) * 100, 1) }}%
                        </td>
                        <td class="text-center">
                            @if($section['avg_tat'] > 0)
                                {{ $section['avg_tat'] }} <span style="font-size: 8px; color: #94a3b8;">DAYS</span>
                            @else
                                <span style="color: #94a3b8;">N/A (Pending)</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @php
                                $withinTat = (int) ($section['within_tat'] ?? max(($section['total'] ?? 0) - ($section['overdue'] ?? 0), 0));
                                $complianceRate = (int) ($section['compliance_rate'] ?? (($section['total'] ?? 0) > 0 ? round(($withinTat / $section['total']) * 100) : 100));
                                $tatBadgeClass = $complianceRate >= 85 ? 'badge-success' : ($complianceRate >= 70 ? 'badge-warning' : 'badge-danger');
                            @endphp
                            <span class="badge {{ $tatBadgeClass }}">
                                {{ number_format($withinTat) }}/{{ number_format($section['total']) }} within TAT ({{ $complianceRate }}%)
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Historical Trends -->
        <div class="report-section">
            @php
                $historicalStart = $selectedFilters['start_date'] ?? null;
                $historicalEnd = $selectedFilters['end_date'] ?? null;

                if ($historicalStart && $historicalEnd && $historicalStart !== '12 Months' && $historicalEnd !== 'Present') {
                    $historicalRange = date('d M Y', strtotime($historicalStart)) . ' - ' . date('d M Y', strtotime($historicalEnd));
                } else {
                    $historicalRange = now()->subMonths(11)->format('M Y') . ' - ' . now()->format('M Y');
                }
            @endphp
            <div class="section-title">Historical Section TAT ({{ $historicalRange }})</div>
            <table class="data-table">
                            <thead>
                                <tr>
                                    <th style="font-size: 8px;">Section</th>
                                    @foreach($stats['sections']['trends']['labels'] ?? [] as $label)
                                        <th class="text-center" style="font-size: 8px;">{{ explode(' ', $label)[0] }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stats['sections']['trends']['series'] ?? [] as $series)
                                <tr>
                                    <td class="font-bold" style="font-size: 9px;">{{ $series['name'] }}</td>
                                    @foreach($series['data'] as $val)
                                        <td class="text-center">{{ $val }}</td>
                                    @endforeach
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Analyst Performance -->
        <div class="report-section">
            <div class="section-title">{{ __('mas/lab.analyst_performance') }}</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 28%;">{{ __('mas/lab.analyst') }}</th>
                        <th class="text-center">Tests Completed</th>
                        <th class="text-center">Avg. Offset</th>
                        <th class="text-center">Total Tests Provided</th>
                        <th class="text-center">Tests Delayed</th>
                        <th class="text-center">Tests Within TAT</th>
                        <th class="text-center">Efficiency Rating</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stats['analyst_performance'] ?? [] as $analyst)
                    <tr>
                        <td class="font-bold border-left" style="border-left: 3px solid #3b82f6 !important; padding-left: 10px;">
                            {{ $analyst['name'] }}
                        </td>
                        <td class="text-center">{{ $analyst['total_tests'] }}</td>
                        <td class="text-center font-bold" style="color: {{ $analyst['avg_offset'] <= 0 ? '#166534' : '#991b1b' }}">
                            {{ $analyst['avg_offset'] <= 0 ? '-' : '+' }}{{ abs($analyst['avg_offset']) }}d
                        </td>
                        <td class="text-center font-bold">
                            {{ number_format($analyst['total_tests_provided'] ?? $analyst['total_tests']) }}
                        </td>
                        <td class="text-center font-bold" style="color: {{ ($analyst['tests_delayed'] ?? 0) > 0 ? '#991b1b' : '#166534' }};">
                            {{ number_format($analyst['tests_delayed'] ?? 0) }}
                        </td>
                        <td class="text-center font-bold" style="color: #166534;">
                            {{ number_format($analyst['tests_within_tat'] ?? 0) }}
                        </td>
                        <td class="text-center">
                            @php
                                $label = $analyst['performance_label'];
                                $badgeClass = match($label) {
                                    'Excellent' => 'badge-success',
                                    'On Track' => 'badge-primary',
                                    'Behind Schedule' => 'badge-warning',
                                    'Critical Delay' => 'badge-danger',
                                    default => 'badge-primary'
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ $label }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted small">{{ __('mas/common.no_data') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Critical Watchlist -->
        <div class="report-section">
            <div class="section-title">{{ __('mas/lab.cockpit_title') }}</div>

            <table class="data-table">
                <thead>
                    <tr>
                        <th>Lab No</th>
                        <th>Client Entity</th>
                        <th>Sample Category</th>
                        <th class="text-center">Operational Priority</th>
                        <th>Workflow Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(array_slice($stats['smart_grid'] ?? [], 0, 10) as $item)
                    <tr>
                        <td class="font-bold" style="color: #1e40af;">{{ $item['batch_code'] }}</td>
                        <td style="font-size: 10px;">{{ $item['client'] }}</td>
                        <td>{{ $item['type'] }}</td>
                        <td class="text-center">
                            @if(($item['priority'] ?? '') == 'Urgent')
                                <span class="badge badge-danger">Urgent</span>
                            @else
                                <span class="badge badge-primary">Normal</span>
                            @endif
                        </td>
                        <td>
                            <span style="font-size: 10px; color: #64748b;">{{ $item['status'] }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted small">No critical alerts detected in this period.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(($stats['pivot']['total'] ?? 0) > 45)
        <!-- Parameters Tested Pivot Table (> 45 params) -->
        <div class="report-section" style="page-break-before: always;">
            <div class="section-title">Number of Parameters Tested</div>
            <p style="font-size: 10px; color: #64748b; margin-bottom: 15px;">
                This table displays the volume breakdown of analytical parameter tests performed across laboratory sections and operational periods.
            </p>
            @php
                $pivotRows = $stats['pivot']['rows'] ?? [];
                $headers = $stats['pivot']['headers'] ?? [];
            @endphp
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="min-width: 160px; text-align: left;">{{ $stats['pivot']['row_label'] ?? 'Lab Section' }}</th>
                        @foreach($headers as $header)
                            <th class="text-center">{{ $header }}</th>
                        @endforeach
                        <th class="text-right" style="padding-right: 15px;">Grand Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pivotRows as $row)
                    <tr>
                        <td class="font-bold border-left" style="border-left: 3px solid #3b82f6 !important; padding-left: 10px;">
                            {{ $row['section'] ?? $row['name'] ?? 'N/A' }}
                        </td>
                        @foreach($headers as $header)
                            <td class="text-center">{{ number_format($row['months'][$header] ?? 0) }}</td>
                        @endforeach
                        <td class="text-right font-bold" style="color: #1e40af; padding-right: 15px;">
                            {{ number_format($row['total']) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ count($headers) + 2 }}" class="text-center py-4 text-muted small">No parameters tested data available.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @endif
    </div>

        <!-- Detailed Appendix (New Page) -->
        <div style="page-break-before: always;">
            <div class="section-title">Detailed Analyte TAT Performance</div>
            <p style="font-size: 9px; color: #64748b; margin-bottom: 15px;">
                This table groups completed tests by Lab No, then breaks them down by sample, sample type, analysis, and parameter. 
                Negative (-) offsets indicate early completion, while positive (+) offsets indicate completion beyond the allowed TAT window.
            </p>
            @php
                $detailedLogPayload = $stats['detailed_logs'] ?? [];
                $groupedDetailedLogs = $detailedLogPayload['grouped_rows'] ?? [];
                $totalDetailedLogs = $detailedLogPayload['total'] ?? 0;
                $sourceDetailedLogs = $detailedLogPayload['source_total'] ?? $totalDetailedLogs;
                $isDetailedLogCapped = $detailedLogPayload['is_capped'] ?? false;
            @endphp
            <div style="font-size: 8px; color: #94a3b8; text-align: right; margin-bottom: 8px;">
                Showing {{ $isDetailedLogCapped ? 'top ' : '' }}{{ number_format($totalDetailedLogs) }} records
                @if($isDetailedLogCapped)
                    of {{ number_format($sourceDetailedLogs) }}
                @endif
                for the selected report range.
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 14%;">Lab No</th>
                        <th style="width: 12%;">Sample No</th>
                        <th style="width: 13%;">Sample Type</th>
                        <th style="width: 15%;">Analysis</th>
                        <th style="width: 16%;">Parameter</th>
                        <th style="width: 10%;">Expected</th>
                        <th style="width: 10%;">Actual</th>
                        <th class="text-center" style="width: 5%;">Offset</th>
                        <th style="width: 15%;">Analyst</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($groupedDetailedLogs as $labGroup)
                    <tr>
                        <td colspan="9" style="background-color: #eff6ff; border-left: 4px solid #2563eb; color: #1e3a8a; font-size: 10px; font-weight: 800;">
                            Lab No: {{ $labGroup['lab_no'] ?? 'N/A' }}
                        </td>
                    </tr>
                    @foreach($labGroup['samples'] ?? [] as $sampleGroup)
                        @foreach($sampleGroup['analysis_groups'] ?? [] as $analysisGroup)
                            @foreach($analysisGroup['parameters'] ?? [] as $parameter)
                            <tr>
                                <td></td>
                                <td style="font-size: 8px; font-weight: bold;">{{ $sampleGroup['sample_code'] ?? 'N/A' }}</td>
                                <td style="font-size: 8px;">{{ $analysisGroup['sample_type'] ?? 'N/A' }}</td>
                                <td style="font-size: 8px;">{{ $analysisGroup['analysis_type'] ?? 'N/A' }}</td>
                                <td style="font-size: 8px; font-weight: bold; color: #1e40af;">{{ $parameter['parameter'] ?? 'N/A' }}</td>
                                <td style="font-size: 8px; color: #64748b;">{{ $parameter['expected_date'] ?? '-' }}</td>
                                <td style="font-size: 8px;">{{ $parameter['actual_date'] ?? '-' }}</td>
                                <td class="text-center">
                                    @php $offset = (int) ($parameter['offset'] ?? 0); @endphp
                                    @if($offset < 0)
                                        <span style="color: #166534; font-weight: bold; font-size: 8px;">{{ $offset }}d</span>
                                    @elseif($offset === 0)
                                        <span style="color: #334155; font-weight: bold; font-size: 8px;">0d</span>
                                    @else
                                        <span style="color: #991b1b; font-weight: bold; font-size: 8px;">+{{ $offset }}d</span>
                                    @endif
                                </td>
                                <td style="font-size: 8px; font-weight: bold;">{{ $parameter['analyst'] ?? 'Unassigned' }}</td>
                            </tr>
                            @endforeach
                        @endforeach
                    @endforeach
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted small">No detailed records available for this period.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            <div style="margin-top: 10px; font-size: 8px; color: #94a3b8; text-align: center;">
                * Detailed records are capped at the top 100 entries for readability.
            </div>
        </div>
    </div>
</body>


</html>
