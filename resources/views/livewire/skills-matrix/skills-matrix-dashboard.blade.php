<div class="container-fluid skills-matrix-dashboard {{ $isPlaceholder ? 'sm-dashboard--placeholder' : '' }}">
    @if($flashMessage)
        <div class="alert alert-{{ $flashType === 'success' ? 'success' : 'danger' }} mb-3">{{ $flashMessage }}</div>
    @endif

    @component('livewire.skills-matrix.partials.page-header', [
        'title' => 'Dashboard',
        'subtitle' => 'Lab team skills overview · TS/LS/007/19',
        'icon' => 'mdi-view-dashboard',
    ])
        @slot('actions')
            <label for="sm-capability-select">Capability matrix</label>
            <select id="sm-capability-select" wire:model.live="selectedCapabilityId" class="form-control form-control-sm tag-select-container" style="max-width:280px;min-height:36px;">
                <option value="">— Sample preview —</option>
                @foreach($capabilities as $cap)
                    <option value="{{ $cap->id }}">{{ $cap->name }}</option>
                @endforeach
            </select>
        @endslot
    @endcomponent

    @if($isPlaceholder)
        <div class="sm-dashboard-preview-hint alert alert-light border mb-3 py-2 px-3 mb-3" role="status">
            <i class="mdi mdi-information-outline me-1" aria-hidden="true"></i>
            Sample layout from the skills matrix design. Select a capability matrix above to load your lab data.
        </div>
    @endif

    <div class="metric-grid">
            <div class="metric-card">
                <div class="metric-label">
                    <i class="mdi mdi-account-group" style="font-size:13px;" aria-hidden="true"></i> Team Members
                </div>
                <div class="metric-value">{{ $metrics['team_members'] }}</div>
                <div class="metric-delta">
                    @if($metrics['roles_with_backup'] > 0)
                        <span class="delta-up">
                            <i class="mdi mdi-arrow-up" style="font-size:10px;"></i>
                            {{ $metrics['roles_with_backup'] }} {{ Str::plural('role', $metrics['roles_with_backup']) }}
                        </span>
                        <span style="color:var(--color-text-secondary,#6b7280);"> covered with backup</span>
                    @else
                        <span style="color:var(--color-text-secondary,#6b7280);">Active capability team</span>
                    @endif
                </div>
            </div>
            <div class="metric-card">
                <div class="metric-label">
                    <i class="mdi mdi-checklist" style="font-size:13px;" aria-hidden="true"></i> Total Skills Tracked
                </div>
                <div class="metric-value">{{ $metrics['skills_tracked'] }}</div>
                <div class="metric-delta">
                    <span style="color:var(--color-text-secondary,#6b7280);">
                        Across {{ $metrics['domain_count'] }} {{ Str::plural('domain', $metrics['domain_count']) }}
                    </span>
                </div>
            </div>
            <div class="metric-card">
                <div class="metric-label">
                    <i class="mdi mdi-alert-triangle" style="font-size:13px;color:#D97706;" aria-hidden="true"></i> Training Gaps
                </div>
                <div class="metric-value" style="color:#DC2626;">{{ $metrics['training_gaps'] }}</div>
                <div class="metric-delta">
                    <span class="delta-down">
                        <i class="mdi mdi-arrow-up" style="font-size:10px;"></i> Needs attention
                    </span>
                </div>
                <div class="metric-bar-bg">
                    <div class="metric-bar-fill" style="width:{{ $metrics['training_gaps_pct'] }}%;background:#EF4444;"></div>
                </div>
            </div>
            <div class="metric-card">
                <div class="metric-label">
                    <i class="mdi mdi-star" style="font-size:13px;color:#D97706;" aria-hidden="true"></i> Avg Competency
                </div>
                <div class="metric-value">{{ number_format($metrics['avg_competency'], 1) }}</div>
                <div class="metric-delta">
                    <span style="color:var(--color-text-secondary,#6b7280);">out of 3.0 possible</span>
                </div>
                <div class="metric-bar-bg">
                    <div class="metric-bar-fill" style="width:{{ $metrics['avg_competency_pct'] }}%;background:#1D4ED8;"></div>
                </div>
            </div>
        </div>

        <div class="two-col section-gap">
            <div class="card">
                <div class="card-header">
                    <div>
                        <div class="card-title">Competency distribution by role</div>
                        <div class="card-subtitle">Average level across all skills (1–3 scale)</div>
                    </div>
                </div>
                <div class="chart-wrap">
                    <canvas id="smRoleChart" wire:ignore></canvas>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <div>
                        <div class="card-title">Skills by domain</div>
                        <div class="card-subtitle">Avg team competency per domain</div>
                    </div>
                </div>
                <div class="chart-wrap">
                    <canvas id="smDomainChart" wire:ignore></canvas>
                </div>
            </div>
        </div>

        <div class="two-col section-gap">
            <div class="card">
                <div class="card-header">
                    <div>
                        <div class="card-title">Training needs by role</div>
                        <div class="card-subtitle">Count of skills requiring training</div>
                    </div>
                </div>
                <div class="chart-wrap-sm">
                    <canvas id="smGapChart" wire:ignore></canvas>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <div>
                        <div class="card-title">Skill level breakdown</div>
                        <div class="card-subtitle">Team-wide distribution</div>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:0;">
                    <div class="chart-wrap-sm" style="flex:1;">
                        <canvas id="smPieChart" wire:ignore></canvas>
                    </div>
                    <div style="flex-shrink:0;font-size:11px;color:var(--color-text-secondary,#6b7280);padding-left:8px;">
                        <div style="margin-bottom:8px;display:flex;align-items:center;gap:6px;">
                            <span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#DCFCE7;border:1px solid #16A34A;"></span>
                            Level 3 · {{ $charts['levelBreakdown']['percents'][0] }}%
                        </div>
                        <div style="margin-bottom:8px;display:flex;align-items:center;gap:6px;">
                            <span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#FEF3C7;border:1px solid #D97706;"></span>
                            Level 2 · {{ $charts['levelBreakdown']['percents'][1] }}%
                        </div>
                        <div style="display:flex;align-items:center;gap:6px;">
                            <span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#FEE2E2;border:1px solid #EF4444;"></span>
                            Level 1 · {{ $charts['levelBreakdown']['percents'][2] }}%
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card section-gap">
            <div class="card-header">
                <div>
                    <div class="card-title">Domain proficiency by role</div>
                    <div class="card-subtitle">Average competency level per domain per role</div>
                </div>
            </div>
            <div>
                <div class="legend">
                    <div class="legend-item">
                        <div class="legend-dot" style="background:#DCFCE7;border:1px solid #16A34A;"></div>
                        Level 3 — Highly proficient, can teach
                    </div>
                    <div class="legend-item">
                        <div class="legend-dot" style="background:#FEF3C7;border:1px solid #D97706;"></div>
                        Level 2 — Competent, minimal help
                    </div>
                    <div class="legend-item">
                        <div class="legend-dot" style="background:#FEE2E2;border:1px solid #EF4444;"></div>
                        Level 1 — No experience
                    </div>
                </div>
                <div style="overflow-x:auto;">
                    <table class="skill-heatmap">
                        <thead>
                            <tr>
                                <th>Domain</th>
                                @foreach($heatmap['role_labels'] as $roleLabel)
                                    <th class="text-center">{{ $roleLabel }}</th>
                                @endforeach
                                <th class="text-center">Team Avg</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($heatmap['rows'] as $row)
                                <tr>
                                    <td style="font-weight:500;font-size:12px;">{{ $row['domain'] }}</td>
                                    @foreach($row['cells'] as $cell)
                                        <td class="text-center">
                                            @if($cell['value'] > 0)
                                                <span class="level-dot {{ $cell['class'] }}">{{ is_float($cell['value']) && floor($cell['value']) != $cell['value'] ? number_format($cell['value'], 1) : $cell['value'] }}</span>
                                            @else
                                                <span class="level-dot gap-neutral">—</span>
                                            @endif
                                        </td>
                                    @endforeach
                                    <td class="text-center">
                                        @if($row['team_avg']['value'] > 0)
                                            @php $avg = $row['team_avg']['value']; @endphp
                                            <span class="level-dot {{ $row['team_avg']['class'] }}">{{ is_float($avg) && floor($avg) != $avg ? number_format($avg, 1) : $avg }}</span>
                                        @else
                                            <span class="level-dot gap-neutral">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ max(count($heatmap['role_labels']) + 2, 3) }}" class="text-muted text-center py-3">
                                        No competency data for this capability matrix yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    <div id="sm-chart-config" class="d-none" data-config='@json($charts)' wire:key="sm-charts-{{ $selectedCapabilityId ?: 'preview' }}"></div>
</div>

@push('skills-matrix-scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.js"></script>
<script>
    (function () {
        let roleChart = null;
        let domainChart = null;
        let gapChart = null;
        let pieChart = null;

        const tickStyle = { color: '#888', font: { size: 10 } };
        const gridLight = { color: 'rgba(0,0,0,0.05)' };

        function readChartData() {
            const el = document.getElementById('sm-chart-config');
            if (!el || !el.dataset.config) {
                return null;
            }
            try {
                return JSON.parse(el.dataset.config);
            } catch (e) {
                return null;
            }
        }

        window.initSkillsMatrixDashboardCharts = function () {
            if (typeof Chart === 'undefined') return;

            const data = readChartData();
            if (!data) return;

            const roleCtx = document.getElementById('smRoleChart');
            if (roleCtx) {
                if (roleChart) roleChart.destroy();
                const labels = data.byRole.labels.length ? data.byRole.labels : ['No data'];
                const values = data.byRole.data.length ? data.byRole.data : [0];
                const colors = data.byRole.colors.length ? data.byRole.colors : ['#6B7280'];
                roleChart = new Chart(roleCtx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Avg competency',
                            data: values,
                            backgroundColor: colors,
                            borderRadius: 5,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { grid: { display: false }, ticks: tickStyle },
                            y: { min: 0, max: 3, grid: gridLight, ticks: { ...tickStyle, stepSize: 1 } },
                        },
                    },
                });
            }

            const domainCtx = document.getElementById('smDomainChart');
            if (domainCtx) {
                if (domainChart) domainChart.destroy();
                domainChart = new Chart(domainCtx, {
                    type: 'bar',
                    data: {
                        labels: data.byDomain.labels.length ? data.byDomain.labels : ['No data'],
                        datasets: [
                            {
                                label: 'Avg',
                                data: data.byDomain.actual.length ? data.byDomain.actual : [0],
                                backgroundColor: '#1D4ED8',
                                borderRadius: 4,
                            },
                            {
                                label: 'Required',
                                data: data.byDomain.required.length ? data.byDomain.required : [0],
                                backgroundColor: '#DBEAFE',
                                borderRadius: 4,
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                labels: { font: { size: 10 }, color: '#888', boxWidth: 10, boxHeight: 10 },
                            },
                        },
                        scales: {
                            x: { grid: { display: false }, ticks: tickStyle },
                            y: { min: 0, max: 3, grid: gridLight, ticks: tickStyle },
                        },
                    },
                });
            }

            const gapCtx = document.getElementById('smGapChart');
            if (gapCtx) {
                if (gapChart) gapChart.destroy();
                const gapLabels = data.gapsByRole.labels.length ? data.gapsByRole.labels : ['No data'];
                gapChart = new Chart(gapCtx, {
                    type: 'bar',
                    data: {
                        labels: gapLabels,
                        datasets: [
                            {
                                label: 'Critical gaps',
                                data: data.gapsByRole.critical,
                                backgroundColor: '#EF4444',
                                borderRadius: 3,
                            },
                            {
                                label: 'Minor gaps',
                                data: data.gapsByRole.minor,
                                backgroundColor: '#FBBF24',
                                borderRadius: 3,
                            },
                            {
                                label: 'Exceeds',
                                data: data.gapsByRole.exceed,
                                backgroundColor: '#1D4ED8',
                                borderRadius: 3,
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top',
                                labels: { font: { size: 10 }, boxWidth: 10, boxHeight: 10, color: '#888' },
                            },
                        },
                        scales: {
                            x: { stacked: true, grid: { display: false }, ticks: tickStyle },
                            y: { stacked: true, grid: gridLight, ticks: { ...tickStyle, stepSize: 1 } },
                        },
                    },
                });
            }

            const pieCtx = document.getElementById('smPieChart');
            if (pieCtx) {
                if (pieChart) pieChart.destroy();
                pieChart = new Chart(pieCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Level 3', 'Level 2', 'Level 1'],
                        datasets: [{
                            data: data.levelBreakdown.counts,
                            backgroundColor: ['#16A34A', '#D97706', '#EF4444'],
                            borderWidth: 2,
                            borderColor: '#fff',
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '65%',
                        plugins: { legend: { display: false } },
                    },
                });
            }
        };

        document.addEventListener('livewire:init', () => {
            Livewire.on('skills-matrix-charts-updated', () => {
                setTimeout(window.initSkillsMatrixDashboardCharts, 50);
            });
            Livewire.hook('morph.updated', ({ el }) => {
                if (el.querySelector && (el.querySelector('#smRoleChart') || el.id === 'sm-chart-config')) {
                    setTimeout(window.initSkillsMatrixDashboardCharts, 50);
                }
            });
        });

        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(window.initSkillsMatrixDashboardCharts, 100);
        });
    })();
</script>
@endpush
