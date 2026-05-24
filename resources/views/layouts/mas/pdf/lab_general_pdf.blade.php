<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ __('mas/dashboard.general_report') }}</title>
    <style>
        /* Stable Architecture Reset */
        @page { margin: 20px; }
        body { 
            font-family: 'Helvetica', 'Arial', sans-serif; 
            color: #1e293b; 
            line-height: 1.4; 
            margin: 0; 
            padding: 0; 
            background-color: #ffffff;
        }

        /* Essential Layout Elements */
        .page-accent { 
            height: 8px; 
            background: linear-gradient(90deg, #1e3a8a 0%, #3b82f6 100%); 
            width: 100%;
            margin-bottom: 25px;
        }
        .container { padding: 0 40px; }
        
        /* Table-Based Layout Elements */
        .layout-grid { width: 100%; border-collapse: collapse; border: none; margin-bottom: 20px; }
        .layout-grid td { vertical-align: top; padding: 0; border: none; }

        /* Header Style */
        .header-box { border-bottom: 2px solid #1e3a8a; padding-bottom: 15px; margin-bottom: 25px; }
        .brand-title { font-size: 18px; font-weight: bold; color: #1e3a8a; text-transform: uppercase; letter-spacing: 1px; }
        .report-meta { font-size: 9px; color: #64748b; line-height: 1.6; }
        .meta-value { color: #1e293b; font-weight: bold; }

        /* KPI Scorecard Grid */
        .kpi-table { width: 100%; border-collapse: separate; border-spacing: 10px 0; margin-left: -10px; margin-bottom: 25px; }
        .kpi-box { 
            background-color: #f8fafc; 
            border: 1px solid #e2e8f0; 
            border-radius: 8px; 
            padding: 15px; 
            text-align: left;
            min-height: 60px;
        }
        .kpi-label { font-size: 8px; text-transform: uppercase; color: #64748b; font-weight: bold; margin-bottom: 5px; }
        .kpi-value { font-size: 22px; font-weight: bold; color: #1e3a8a; }
        .kpi-subtext { font-size: 7px; color: #94a3b8; margin-top: 3px; }

        /* Section Headings */
        .section-title { 
            font-size: 11px; 
            font-weight: bold; 
            color: #1e3a8a; 
            text-transform: uppercase; 
            margin-bottom: 12px; 
            letter-spacing: 0.5px;
            border-left: 3px solid #3b82f6;
            padding-left: 10px;
        }
        .report-section { page-break-inside: auto; margin-bottom: 30px; }

        /* Premium Data Tables */
        .data-table { width: 100%; border-collapse: collapse; margin-top: 5px; }
        .data-table th { 
            background-color: #f1f5f9; 
            padding: 10px 12px; 
            text-align: left; 
            font-size: 9px; 
            font-weight: bold; 
            border-bottom: 2px solid #e2e8f0; 
            color: #475569; 
            text-transform: uppercase;
        }
        .data-table td { 
            padding: 10px 12px; 
            border-bottom: 1px solid #f1f5f9; 
            font-size: 10px; 
            color: #334155; 
        }
        .data-table tr:nth-child(even) { background-color: #fcfcfc; }
        
        /* Status Elements */
        .badge { padding: 3px 8px; border-radius: 4px; font-size: 8px; font-weight: bold; text-transform: uppercase; display: inline-block; }
        .badge-danger { background-color: #fee2e2; color: #991b1b; }
        .badge-success { background-color: #dcfce7; color: #166534; }
        .stage-badge { background-color: #eff6ff; color: #1e40af; border: 1px solid #dbeafe; }

        /* Visuals */
        .chart-container { border: 1px solid #e2e8f0; border-radius: 10px; padding: 15px; background: #ffffff; text-align: center; }
        .chart-img { max-width: 100%; height: 300px; object-fit: contain; }

        /* Footer Alignment */
        .footer { 
            position: fixed; 
            bottom: 20px; 
            width: 100%; 
            padding: 0 40px;
            font-size: 8px; 
            color: #94a3b8; 
            border-top: 1px solid #f1f5f9; 
            padding-top: 15px; 
        }
    </style>
</head>
<body>
    <div class="page-accent"></div>
    
    <div class="container">
        <!-- Header Section -->
        <div class="header-box">
            <table class="layout-grid">
                <tr>
                    {{-- Left Cell: Filter Metadata --}}
                    <td style="width: 40%; vertical-align: middle; text-align: left;">
                        @if(isset($selectedFilters))
                        <div style="font-size: 10px; color: #475569; line-height: 1.45;">
                            <div>
                                <span style="font-size: 8px; font-weight: 800; color: #64748b; text-transform: uppercase; display: inline-block; width: 65px;">Range:</span>
                                <strong style="color: #1e293b;">{{ $selectedFilters['start_date'] }} - {{ $selectedFilters['end_date'] }}</strong>
                            </div>
                        </div>
                        @endif
                    </td>

                    {{-- Center Cell: Company Logo --}}
                    <td style="width: 20%; text-align: center; vertical-align: middle;">
                        @php
                            $logoSrc = null;
                            if (isset($company) && !empty($company->logo)) {
                                $logoRaw = $company->logo;
                                if (str_starts_with($logoRaw, 'http')) {
                                    $logoSrc = $logoRaw;
                                } else {
                                    $localPath = public_path(ltrim($logoRaw, '/'));
                                    if (file_exists($localPath)) {
                                        $logoSrc = $localPath;
                                    }
                                }
                            }
                            if (!$logoSrc) {
                                $defaultBranding = public_path('assets/branding/logo.jpeg');
                                if (file_exists($defaultBranding)) {
                                    $logoSrc = $defaultBranding;
                                }
                            }
                        @endphp
                        @if($logoSrc)
                            <img src="{{ $logoSrc }}" style="height: 55px; max-width: 140px; object-fit: contain;">
                        @else
                            <div style="font-size: 16px; font-weight: bold; color: #1e40af;">{{ $company->name ?? 'GCLA' }}</div>
                        @endif
                    </td>

                    {{-- Right Cell: Title & Date --}}
                    <td style="width: 40%; text-align: right; vertical-align: middle;">
                        <div style="font-size: 22px; font-weight: 900; color: #1e40af; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 2px;">LAB ANALYTICS</div>
                        <div style="font-size: 10px; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">GENERAL ANALYTICS REPORT</div>
                        <div style="font-size: 9px; color: #94a3b8;">{{ __('mas/report.generated') }}: {{ date('d M Y, H:i') }}</div>
                        <div style="font-size: 9px; color: #3b82f6; font-weight: 700; margin-top: 5px;">REF #{{ date('Ymd') }}-GEN | CONFIDENTIAL OPERATIONAL DATA</div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- KPI Scorecard -->
        <table class="kpi-table">
            <tr>
                <td style="width: 25%;">
                    <div class="kpi-box">
                        <div class="kpi-label">{{ __('mas/lab.workload_volume') }}</div>
                        <div class="kpi-value">{{ $stats['summary']['active_batches'] ?? 0 }}</div>
                        <div class="kpi-subtext">{{ __('mas/dashboard.active_batches') }}</div>
                    </div>
                </td>
                <td style="width: 25%;">
                    <div class="kpi-box">
                        <div class="kpi-label">{{ __('mas/lab.overdue') }}</div>
                        <div class="kpi-value" style="color: #dc2626;">{{ $stats['summary']['overdue_batches'] ?? 0 }}</div>
                        <div class="kpi-subtext">{{ __('mas/lab.overdue_watchlist') }}</div>
                    </div>
                </td>
                <td style="width: 25%;">
                    <div class="kpi-box">
                        <div class="kpi-label">{{ __('mas/lab.bucket_due_today') }}</div>
                        <div class="kpi-value" style="color: #f59e0b;">{{ $stats['summary']['due_today_batches'] ?? 0 }}</div>
                        <div class="kpi-subtext">{{ __('mas/lab.efficiency_monitoring') }}</div>
                    </div>
                </td>
                <td style="width: 25%;">
                    <div class="kpi-box">
                        <div class="kpi-label">{{ __('mas/lab.tests_processed') }}</div>
                        <div class="kpi-value" style="color: #10b981;">{{ $stats['summary']['tests_completed'] ?? 0 }}</div>
                        <div class="kpi-subtext">{{ __('mas/lab.completion_ratio') }}</div>
                    </div>
                </td>
            </tr>
        </table>

        <!-- Laboratory Workflow Profile -->
        <div class="report-section">
            <div class="section-title">{{ __('mas/lab.workflow_stages') }}</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ __('mas/lab.workflow_stage') }}</th>
                        <th style="text-align: center;">{{ __('mas/lab.batch_count') }}</th>
                        <th style="text-align: center;">Completed</th>
                        <th style="text-align: center;">Due Today</th>
                        <th style="text-align: center;">Status</th>
                        <th style="text-align: right;">{{ __('mas/lab.avg_days_tat') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($stats['stage_summary'] as $stage)
                    @php
                        $total = (int) ($stage['total_batches'] ?? 0);
                        $completed = (int) ($stage['completed_batches'] ?? max($total - (int) ($stage['overdue_batches'] ?? 0), 0));
                        $completionRate = (int) ($stage['completion_rate'] ?? ($total > 0 ? round(($completed / $total) * 100) : 0));
                    @endphp
                    <tr>
                        <td style="font-weight: bold;">{{ $stage['workflow_stage'] }}</td>
                        <td style="text-align: center;">
                            <span class="badge stage-badge">{{ number_format($total) }}</span>
                        </td>
                        <td style="text-align: center;">
                            <span class="badge badge-success">{{ number_format($completed) }}</span>
                        </td>
                        <td style="text-align: center;">
                            {{ number_format($stage['due_today_batches'] ?? 0) }}
                        </td>
                        <td style="text-align: center;">
                            <span class="badge badge-success">{{ number_format($completed) }}/{{ number_format($total) }} ({{ $completionRate }}%)</span>
                        </td>
                        <td style="text-align: right; color: #64748b; font-weight: bold;">{{ $stage['avg_days_to_target'] ?? '—' }} <span style="font-size: 8px;">Days</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Visual Intelligence Section -->
        @if($chartImage)
        <div class="report-section" style="page-break-inside: avoid;">
            <div class="section-title">{{ __('mas/lab.sample_type_distribution') }}</div>
            <div class="chart-container">
                <img src="{{ $chartImage }}" class="chart-img" />
            </div>
        </div>
        @endif

        <!-- Distribution Grids -->
        <div class="report-section">
            <table class="layout-grid">
                <tr>
                    <td style="width: 48%; padding-right: 15px;">
                        <div class="section-title">{{ __('mas/lab.historical_trends') }}</div>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>{{ __('mas/common.month') }}</th>
                                    <th style="text-align: right;">{{ __('mas/common.volume') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(array_reverse($stats['monthly_trends']['labels'] ?? []) as $index => $label)
                                @php $actualIndex = count($stats['monthly_trends']['labels']) - 1 - $index; @endphp
                                <tr>
                                    <td>{{ $label }}</td>
                                    <td style="text-align: right; font-weight: bold; color: #1e3a8a;">{{ $stats['monthly_trends']['data'][$actualIndex] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </td>
                    <td style="width: 4%;"><!-- Spacer --></td>
                    <td style="width: 48%;">
                        <div class="section-title">{{ __('mas/lab.geographic_density') }}</div>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>{{ __('mas/dashboard.density_cluster') }}</th>
                                    <th style="text-align: right;">{{ __('mas/dashboard.weight') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse(array_slice($stats['geographic_data'] ?? [], 0, 8) as $point)
                                <tr>
                                    <td style="font-size: 8px;">Point ({{ number_format($point['lat'], 2) }}, {{ number_format($point['lng'], 2) }})</td>
                                    <td style="text-align: right; font-weight: bold;">{{ $point['intensity'] }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="2" style="text-align: center; color: #94a3b8;">{{ __('mas/common.no_data') }}</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Legal Footer -->
    <div class="footer">
        <table style="width: 100%;">
            <tr>
                <td style="text-align: left;">
                    {{ __('mas/report.confidential') }} | {{ __('mas/dashboard.general_report') }}
                </td>
                <td style="text-align: right;">
                    {{ __('mas/common.brand_footer') }}
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
