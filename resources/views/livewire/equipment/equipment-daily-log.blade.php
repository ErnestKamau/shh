<div>
    <style>
        .dl-card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(22, 28, 34, 0.06);
            margin-bottom: 16px;
        }
        .dl-table thead th {
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: #495057;
            background: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            white-space: nowrap;
        }
        .dl-chart-wrap {
            position: relative;
            height: 280px;
        }
        .dl-status-pill {
            font-size: 0.72rem;
            font-weight: 600;
            padding: 4px 8px;
            border-radius: 999px;
        }
        .dl-status-completed {
            background: #d4edda;
            color: #155724;
        }
        .dl-status-progress {
            background: #fff3cd;
            color: #856404;
        }
        .dl-empty {
            padding: 36px 16px;
            text-align: center;
            color: #6c757d;
        }
        .text-primary {
            color: var(--color-primary) !important;
        }
        .btn-outline-primary {
            color: var(--color-primary) !important;
            border-color: var(--color-primary) !important;
        }
        .btn-outline-primary:hover {
            background-color: var(--color-primary) !important;
            border-color: var(--color-primary) !important;
            color: #fff !important;
        }
    </style>

    <div class="d-flex align-items-center justify-content-between px-4 pt-4 pb-3">
        <h4 class="mb-0 font-weight-bold" style="color:#212529; letter-spacing:-0.01em;">
            <i class="mdi mdi-notebook-outline text-primary mr-2"></i>{{ __('equipment.equipment_daily_log') }}
        </h4>
        <span class="badge badge-pill" style="background:#e9ecef; color:#495057; font-size:0.78rem; font-weight:600; padding:6px 12px;">
            {{ count($dailyUsageRows) }} Session{{ count($dailyUsageRows) === 1 ? '' : 's' }}
        </span>
    </div>

    <div class="px-4">
        <div class="dl-card p-3">
            <div class="d-flex align-items-center justify-content-between" style="gap: 12px;">
                <div class="d-flex align-items-center" style="gap: 10px;">
                    <i class="mdi mdi-calendar-clock" style="font-size:1.1rem; color:#6c757d;"></i>
                    <span style="font-size:0.82rem; font-weight:600; color:#495057;">{{ __('equipment.date') }}:</span>
                    <input
                        type="date"
                        wire:model.live="logDate"
                        class="form-control form-control-sm"
                        style="width:170px; border-radius:6px; font-size:0.82rem;"
                    >
                </div>
                <small class="text-muted">Shows equipment switched on and off for selected date</small>
            </div>
        </div>

        <div class="dl-card p-3">
            <h6 class="mb-3"><i class="mdi mdi-table-large mr-1"></i>{{ __('equipment.daily_equipment_usage') }}</h6>

            @if(empty($dailyUsageRows))
                <div class="dl-empty">
                    <i class="mdi mdi-information-outline mr-1"></i>No equipment usage sessions found for {{ \Carbon\Carbon::parse($logDate)->format('D, M j Y') }}.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-sm table-hover dl-table mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ __('equipment.name') }}</th>
                                <th>{{ __('equipment.equipment_number') }}</th>
                                <th>{{ __('equipment.time_on') }}</th>
                                <th>{{ __('equipment.time_off') }}</th>
                                <th>{{ __('equipment.duration') }}</th>
                                <th>{{ __('equipment.analyst') }}</th>
                                <th>{{ __('equipment.status') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dailyUsageRows as $index => $row)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td class="font-weight-semibold">{{ $row['equipment_name'] }}</td>
                                    <td><code>{{ $row['equipment_number'] }}</code></td>
                                    <td>{{ $row['time_on'] }}</td>
                                    <td>{{ $row['time_off'] }}</td>
                                    <td>{{ $row['duration'] }}</td>
                                    <td>{{ $row['analyst'] }}</td>
                                    <td>
                                        @if($row['status'] === 'Completed')
                                            <span class="dl-status-pill dl-status-completed">{{ __('equipment.completed') }}</span>
                                        @elseif($row['status'] === 'In Progress')
                                            <span class="dl-status-pill dl-status-progress">{{ __('equipment.in_progress') }}</span>
                                        @else
                                            <span class="dl-status-pill badge-light">{{ __('equipment.not_started') }}</span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <button type="button"
                                            wire:click="selectEquipment('{{ $row['equipment_id'] }}')"
                                            class="btn btn-sm btn-outline-primary">
                                            {{ __('equipment.view') }} {{ __('equipment.daily_equipment_usage') }}
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        @if($selectedEquipment)
            <div class="dl-card p-3">
                <div class="d-flex justify-content-between align-items-start flex-wrap" style="gap: 12px;">
                    <div>
                        <h6 class="mb-1">
                            <i class="mdi mdi-chart-line mr-1"></i>
                            {{ $selectedEquipment->name }} Usage Over Time
                        </h6>
                        <small class="text-muted">Track equipment run duration across selected dates</small>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="clearSelectedEquipment">
                        {{ __('equipment.close') }}
                    </button>
                </div>

                <div class="row mt-3">
                    <div class="col-md-3">
                        <label class="small text-muted mb-1">{{ __('equipment.from') }}</label>
                        <input type="date" class="form-control form-control-sm" wire:model.live="historyFromDate">
                    </div>
                    <div class="col-md-3">
                        <label class="small text-muted mb-1">{{ __('equipment.to') }}</label>
                        <input type="date" class="form-control form-control-sm" wire:model.live="historyToDate">
                    </div>
                </div>

                <div class="mt-3">
                    @if(empty($usageChartData['labels']))
                        <div class="alert alert-light border mb-0">
                            No chart data available for the selected date range.
                        </div>
                    @else
                        <div class="dl-chart-wrap">
                            <script id="dl-usage-chart-data" type="application/json">@json($usageChartData)</script>
                            <canvas id="dl-usage-chart"></canvas>
                        </div>
                    @endif
                </div>

                <div class="table-responsive mt-3">
                    <table class="table table-sm table-bordered table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>{{ __('equipment.date') }}</th>
                                <th>{{ __('equipment.time_on') }}</th>
                                <th>{{ __('equipment.time_off') }}</th>
                                <th>{{ __('equipment.duration') }}</th>
                                <th>{{ __('equipment.analyst') }}</th>
                                <th>{{ __('equipment.status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($selectedEquipmentUsageRows as $usage)
                                <tr>
                                    <td>{{ $usage['date'] }}</td>
                                    <td>{{ $usage['time_on'] }}</td>
                                    <td>{{ $usage['time_off'] }}</td>
                                    <td>{{ $usage['duration'] }}</td>
                                    <td>{{ $usage['analyst'] }}</td>
                                    <td>{{ $usage['status'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">{{ __('equipment.no_usage_records_found_in_selected_range') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

    <script>
        function buildDailyUsageChart() {
            var chartDataEl = document.getElementById('dl-usage-chart-data');
            var canvas = document.getElementById('dl-usage-chart');

            if (window._dlUsageChart) {
                window._dlUsageChart.destroy();
                window._dlUsageChart = null;
            }

            if (!chartDataEl || !canvas || typeof Chart === 'undefined') {
                return;
            }

            var parsed;
            try {
                parsed = JSON.parse(chartDataEl.textContent || '{}');
            } catch (e) {
                parsed = { labels: [], durations: [] };
            }

            window._dlUsageChart = new Chart(canvas.getContext('2d'), {
                type: 'line',
                data: {
                    labels: parsed.labels || [],
                    datasets: [{
                        label: 'Usage Duration (minutes)',
                        data: parsed.durations || [],
                        borderColor: '{{ \App\Services\System\ThemeService::primaryColor() }}',
                        backgroundColor: '{{ \App\Services\System\ThemeService::rgbaFromHex(\App\Services\System\ThemeService::primaryColor(), 0.12) }}',
                        pointBackgroundColor: '{{ \App\Services\System\ThemeService::primaryColor() }}',
                        pointRadius: 3,
                        borderWidth: 2,
                        tension: 0.2,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: true }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: { display: true, text: 'Duration (minutes)' }
                        }
                    }
                }
            });
        }

        document.addEventListener('livewire:initialized', function () {
            buildDailyUsageChart();
            Livewire.hook('morph.updated', function () {
                buildDailyUsageChart();
            });
        });
    </script>
</div>
