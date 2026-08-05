<div class="container-fluid">
    <div class="lab-dashboard-subtitle mb-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <p class="text-muted mb-0">{{ __('personnel.personnel_overview') }}</p>
            </div>
            <div class="col-md-4 text-right">
                <p class="mb-0 text-primary" style="font-weight: 500">
                    <i class="mdi mdi-calendar"></i>
                    {{ now()->format('l, F j, Y') }}
                </p>
            </div>
        </div>
    </div>

    <div class="card tab-card">
        <div class="tab-content p-3">
            <div class="tab-pane show active">
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="kpi-card" style="border-left: 4px solid #0d6efd">
                            <div class="kpi-card-body">
                                <div class="kpi-card-row">
                                    <h4 class="kpi-card-value">{{ $totalPersonnel }}</h4>
                                    <div class="kpi-card-icon"><i class="mdi mdi-account-group" style="color: #0d6efd"></i></div>
                                </div>
                                <div class="kpi-card-row"><p class="kpi-card-label">{{ __('personnel.total_personnel') }}</p></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="kpi-card" style="border-left: 4px solid #28a745">
                            <div class="kpi-card-body">
                                <div class="kpi-card-row">
                                    <h4 class="kpi-card-value">{{ $activePersonnel }}</h4>
                                    <div class="kpi-card-icon"><i class="mdi mdi-account-check" style="color: #28a745"></i></div>
                                </div>
                                <div class="kpi-card-row"><p class="kpi-card-label">{{ __('personnel.active_personnel') }}</p></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="kpi-card" style="border-left: 4px solid #dc3545">
                            <div class="kpi-card-body">
                                <div class="kpi-card-row">
                                    <h4 class="kpi-card-value">{{ $inactivePersonnel }}</h4>
                                    <div class="kpi-card-icon"><i class="mdi mdi-account-off" style="color: #dc3545"></i></div>
                                </div>
                                <div class="kpi-card-row"><p class="kpi-card-label">{{ __('personnel.inactive_personnel') }}</p></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="kpi-card" style="border-left: 4px solid #17a2b8">
                            <div class="kpi-card-body">
                                <div class="kpi-card-row">
                                    <h4 class="kpi-card-value">{{ $newThisMonth }}</h4>
                                    <div class="kpi-card-icon"><i class="mdi mdi-account-plus" style="color: #17a2b8"></i></div>
                                </div>
                                <div class="kpi-card-row"><p class="kpi-card-label">{{ __('personnel.new_this_month') }}</p></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-12">
                        <div class="chart-container">
                            <h5 class="mb-3"><i class="mdi mdi-lightning-bolt"></i> {{ __('personnel.quick_actions') }}</h5>
                            <div class="row">
                                <div class="col-md-3 col-sm-6 mb-2 mb-md-0">
                                    <button type="button" class="btn btn-sm btn-outline-primary quick-action-btn w-100" wire:click="$dispatchTo('personnel.personnel-table-manager', 'personnel-open-add-modal')">
                                        <i class="mdi mdi-plus"></i> {{ __('personnel.add_personnel') }}
                                    </button>
                                </div>
                                <div class="col-md-3 col-sm-6 mb-2 mb-md-0">
                                    <a href="{{ route('organizational-roles') }}" class="btn btn-sm btn-outline-primary quick-action-btn w-100">
                                        <i class="mdi mdi-account-key"></i> {{ __('personnel.manage_roles') }}
                                    </a>
                                </div>
                                <div class="col-md-3 col-sm-6 mb-2 mb-md-0">
                                    <a href="{{ route('show-organizational-departments') }}" class="btn btn-sm btn-outline-primary quick-action-btn w-100">
                                        <i class="mdi mdi-home-group"></i> {{ __('personnel.departments') }}
                                    </a>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <a href="{{ route('personnel-certification-home') }}" class="btn btn-sm btn-outline-primary quick-action-btn w-100">
                                        <i class="mdi mdi-certificate"></i> {{ __('personnel.certifications') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="chart-container h-100">
                            <h5 class="mb-3"><i class="mdi mdi-office-building"></i> {{ __('personnel.active_by_department') }}</h5>
                            @forelse ($departmentDistribution as $department)
                                <div class="d-flex justify-content-between border-bottom py-2">
                                    <span>{{ $department['name'] }}</span>
                                    <span class="badge badge-light">{{ $department['count'] }}</span>
                                </div>
                            @empty
                                <p class="text-muted mb-0">{{ __('personnel.no_department_records') }}</p>
                            @endforelse
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="chart-container h-100">
                            <h5 class="mb-3"><i class="mdi mdi-badge-account-horizontal"></i> {{ __('personnel.active_by_designation') }}</h5>
                            @forelse ($designationDistribution as $designation)
                                <div class="d-flex justify-content-between border-bottom py-2">
                                    <span>{{ $designation['name'] }}</span>
                                    <span class="badge badge-light">{{ $designation['count'] }}</span>
                                </div>
                            @empty
                                <p class="text-muted mb-0">{{ __('personnel.no_designation_records') }}</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="row mb-2">
                    <div class="col-md-6">
                        <div class="chart-container h-100">
                            <h5 class="mb-3"><i class="mdi mdi-chart-line"></i> {{ __('personnel.hiring_trend') }}</h5>
                            @foreach ($hireTrend as $trend)
                                @php
                                    $maxTrend = max(array_column($hireTrend, 'count')) ?: 1;
                                    $width = (int) round(($trend['count'] / $maxTrend) * 100);
                                @endphp
                                <div class="mb-2">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="text-muted">{{ $trend['month'] }}</span>
                                        <span>{{ $trend['count'] }}</span>
                                    </div>
                                    <div class="progress" style="height: 8px">
                                        <div class="progress-bar bg-info" role="progressbar" style="width: {{ $width }}%" aria-valuenow="{{ $width }}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="chart-container h-100">
                            <h5 class="mb-3"><i class="mdi mdi-account-clock"></i> {{ __('personnel.recent_joiners') }}</h5>
                            @forelse ($recentJoiners as $joiner)
                                <div class="border rounded p-2 mb-2">
                                    <a href="{{ route('view-personnel', ['id' => $joiner['id']]) }}" class="font-weight-bold">{{ $joiner['name'] }}</a>
                                    <div class="small text-muted">{{ $joiner['department'] }}</div>
                                    <div class="small">{{ $joiner['date'] }}</div>
                                </div>
                            @empty
                                <p class="text-muted mb-0">{{ __('personnel.no_recent_joiners') }}</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="row mb-2">
                    <div class="col-12">
                        <div class="chart-container h-100">
                            <h5 class="mb-3"><i class="mdi mdi-office-building"></i> Lab Assignments</h5>
                            @php
                                $structureRows = $organizationStructure;
                                $labCount = count($structureRows);
                                $maxValue = max(1, collect($structureRows)->max(fn ($row) => $row['users']) ?? 1);

                                $leftPad = 36;
                                $rightPad = 18;
                                $topPad = 20;
                                $chartHeight = 210;
                                $baseY = $topPad + $chartHeight;
                                $chartWidth = max(760, $leftPad + $rightPad + (max($labCount, 1) * 150));
                                $plotWidth = $chartWidth - $leftPad - $rightPad;
                                $step = $plotWidth / max($labCount, 1);
                                $barWidth = 34;
                            @endphp

                            @if($labCount === 0)
                                <p class="text-muted mb-0">No labs available for the current company.</p>
                            @else
                                <div class="org-chart-wrap">
                                    <svg class="org-structure-chart" viewBox="0 0 {{ $chartWidth }} {{ $baseY + 42 }}" preserveAspectRatio="xMidYMid meet" role="img" aria-label="Lab assignments chart">
                                        @for($i = 0; $i <= 4; $i++)
                                            @php
                                                $lineY = $topPad + (($chartHeight / 4) * $i);
                                                $lineValue = (int) round($maxValue - (($maxValue / 4) * $i));
                                            @endphp
                                            <line x1="{{ $leftPad }}" y1="{{ $lineY }}" x2="{{ $chartWidth - $rightPad }}" y2="{{ $lineY }}" stroke="#e2e8f0" stroke-width="1" />
                                            <text x="{{ $leftPad - 8 }}" y="{{ $lineY + 4 }}" text-anchor="end" class="org-axis-label">{{ $lineValue }}</text>
                                        @endfor

                                        @foreach($structureRows as $i => $row)
                                            @php
                                                $x = $leftPad + ($i * $step) + ($step / 2);
                                                $barHeight = ($row['users'] / $maxValue) * $chartHeight;
                                                $barY = $baseY - $barHeight;
                                                $barX = $x - ($barWidth / 2);
                                                $labLabel = strlen($row['lab']) > 24 ? substr($row['lab'], 0, 24) . '...' : $row['lab'];
                                            @endphp
                                            <rect x="{{ $barX }}" y="{{ $barY }}" width="{{ $barWidth }}" height="{{ $barHeight }}" rx="6" class="org-bar" />
                                            <text x="{{ $x }}" y="{{ max($barY - 6, 12) }}" text-anchor="middle" class="org-bar-value">{{ $row['users'] }}</text>
                                            <text x="{{ $x }}" y="{{ $baseY + 20 }}" text-anchor="middle" class="org-zone-label">{{ $labLabel }}</text>
                                        @endforeach
                                    </svg>
                                </div>

                                <div class="org-chart-legend mt-2">
                                    <span class="org-legend-item"><span class="org-legend-swatch org-legend-swatch--bar"></span> Assigned users per lab</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .kpi-card { background: #ffffff; border: 1px solid #e5e7eb; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .kpi-card-body { padding: 0.75rem; }
        .kpi-card-row { display: flex; justify-content: space-between; align-items: center; }
        .kpi-card-value { font-size: 2rem; font-weight: 700; margin-bottom: 0; color: #2d3748; line-height: 1; }
        .kpi-card-label { font-size: 1rem; font-weight: 500; color: #6b7280; margin-bottom: 0; }
        .kpi-card-icon { font-size: 2rem; }
        .chart-container { background: white; border-radius: 15px; padding: 20px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08); }
        .quick-action-btn { display: block; width: 100%; padding: 12px; text-align: center; font-weight: 500; border-radius: var(--ls-radius-sm, 6px); }
        .quick-action-btn:hover { color: white !important; background-color: #0d6efd; border-color: #0d6efd; }

        .org-chart-wrap { width: 100%; overflow-x: auto; overflow-y: hidden; padding-bottom: 6px; }
        .org-structure-chart { width: 100%; min-width: 100%; height: 320px; }
        .org-axis-label { font-size: 11px; fill: #64748b; font-weight: 600; }
        .org-bar { fill: #3b82f6; opacity: 0.88; }
        .org-bar-value { font-size: 11px; fill: #1e3a8a; font-weight: 700; }
        .org-zone-label { font-size: 10px; fill: #334155; font-weight: 600; }
        .org-area { fill: rgba(16, 185, 129, 0.2); }
        .org-line { fill: none; stroke: #10b981; stroke-width: 2.5; }
        .org-line-dot { fill: #10b981; stroke: #ffffff; stroke-width: 1.5; }
        .org-line-value { font-size: 11px; fill: #047857; font-weight: 700; }

        .org-chart-legend {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        .org-legend-item {
            font-size: var(--ls-text-sm, 0.75rem);
            color: #334155;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-weight: 600;
        }

        .org-legend-swatch {
            width: 16px;
            height: 10px;
            border-radius: 3px;
            display: inline-block;
        }

        .org-legend-swatch--bar { background: #3b82f6; }
        .org-legend-swatch--line { background: rgba(16, 185, 129, 0.28); border: 2px solid #10b981; }

    </style>

    <div id="personnel-list" class="mt-4">
        @livewire('personnel.personnel-table-manager', ['embedded' => true])
    </div>

</div>
