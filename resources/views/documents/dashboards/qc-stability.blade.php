@extends('layouts.app')

@section('title', 'QC Stability Board')

@push('styles')
<style>
    .kpi-card { border-left: 4px solid; border-radius: .375rem; }
    .kpi-in-control  { border-color: #28a745; }
    .kpi-warning     { border-color: #ffc107; }
    .kpi-critical    { border-color: #dc3545; }
    .kpi-info        { border-color: #17a2b8; }
    .kpi-label { font-size: .75rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; }
    .kpi-value { font-size: 2rem; font-weight: 700; line-height: 1; }
    .kpi-sub   { font-size: .8rem; color: #6c757d; }
    .badge-in-control { background: #28a745; color: #fff; }
    .badge-warning    { background: #ffc107; color: #212529; }
    .badge-critical   { background: #dc3545; color: #fff; }
    .chart-container  { position: relative; height: 300px; }
    .pareto-chart-container { position: relative; height: 320px; }
    .ooc-icon-critical { color: #dc3545; }
    .ooc-icon-warning  { color: #ffc107; }
    .ooc-icon-ok       { color: #28a745; }
    .section-header { background: #f8f9fa; font-size: .875rem; font-weight: 600; padding: .6rem 1rem; border-bottom: 1px solid #dee2e6; }
</style>
@endpush

@section('content')
<div class="container-fluid py-3">

    {{-- ── Header ──────────────────────────────────────────────────────────── --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 font-weight-bold">
                <i class="fas fa-chart-line text-primary mr-2"></i>
                QC Stability Board
            </h4>
            <small class="text-muted">ISO 13528 Algorithm A · Levey-Jennings · Pareto · Westgard Rules</small>
        </div>
        <div class="d-flex gap-2">
            {{-- Day filter --}}
            <form method="GET" class="d-inline">
                <select name="days" class="form-control form-control-sm" onchange="this.form.submit()">
                    @foreach([7, 14, 30, 60, 90] as $d)
                    <option value="{{ $d }}" @if($days == $d) selected @endif>Last {{ $d }} days</option>
                    @endforeach
                </select>
            </form>
            <a href="{{ route('dashboards.qc-stability.export', ['days' => $days]) }}"
               class="btn btn-sm btn-outline-primary">
                <i class="fas fa-download mr-1"></i> Export CSV
            </a>
            <button class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
                <i class="fas fa-sync mr-1"></i> Refresh
            </button>
        </div>
    </div>

    {{-- ── KPI Cards ─────────────────────────────────────────────────────── --}}
    <div class="row mb-4">
        <div class="col-6 col-md-3 mb-3">
            <div class="card kpi-card kpi-in-control">
                <div class="card-body py-3">
                    <div class="kpi-label text-success">Pass Rate ({{ $days }}d)</div>
                    <div class="kpi-value text-success">{{ $kpis['passRate'] ?? 0 }}%</div>
                    <div class="kpi-sub">{{ $kpis['passed'] ?? 0 }} / {{ $kpis['total'] ?? 0 }} tests</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-3">
            <div class="card kpi-card kpi-critical">
                <div class="card-body py-3">
                    <div class="kpi-label text-danger">Fail Rate ({{ $days }}d)</div>
                    <div class="kpi-value text-danger">{{ $kpis['failRate'] ?? 0 }}%</div>
                    <div class="kpi-sub">{{ $kpis['failed'] ?? 0 }} failures</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-3">
            <div class="card kpi-card kpi-info">
                <div class="card-body py-3">
                    <div class="kpi-label text-info">Active Analytes</div>
                    <div class="kpi-value text-info">{{ $kpis['analytes'] ?? 0 }}</div>
                    <div class="kpi-sub">Under QC monitoring</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-3">
            <div class="card kpi-card kpi-warning">
                <div class="card-body py-3">
                    <div class="kpi-label text-warning">Drifting &gt;15% CV</div>
                    <div class="kpi-value text-warning">{{ $kpis['drifting'] ?? 0 }}</div>
                    <div class="kpi-sub">Analytes requiring review</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Per-Analyte Robust Stats Table ──────────────────────────────────── --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="section-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-table mr-1 text-primary"></i> Per-Analyte Robust Statistics (ISO 13528 Algorithm A)</span>
                    <span class="text-muted" style="font-size:.75rem">
                        UCL/LCL = Robust Mean ± 2 × Robust SD
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0" id="tblAnalytes">
                            <thead class="thead-light">
                                <tr>
                                    <th>Analyte</th>
                                    <th>Unit</th>
                                    <th class="text-right">Robust Mean (x*)</th>
                                    <th class="text-right">Robust SD (s*)</th>
                                    <th class="text-right">Robust CV%</th>
                                    <th class="text-right">UCL</th>
                                    <th class="text-right">LCL</th>
                                    <th class="text-right">Total Tests</th>
                                    <th class="text-right">Pass Rate</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($analyteStats as $s)
                                <tr>
                                    <td><strong>{{ $s['analyte'] }}</strong><br><small class="text-muted">{{ $s['code'] }}</small></td>
                                    <td>{{ $s['unit'] }}</td>
                                    <td class="text-right">{{ number_format($s['robustMean'], 4) }}</td>
                                    <td class="text-right">{{ number_format($s['robustSd'], 4) }}</td>
                                    <td class="text-right
                                        @if($s['robustCvPct'] >= 25) text-danger font-weight-bold
                                        @elseif($s['robustCvPct'] >= 15) text-warning font-weight-bold
                                        @endif">
                                        {{ number_format($s['robustCvPct'], 2) }}%
                                    </td>
                                    <td class="text-right text-danger">{{ number_format($s['ucl'], 4) }}</td>
                                    <td class="text-right text-primary">{{ number_format($s['lcl'], 4) }}</td>
                                    <td class="text-right">{{ $s['totalTests'] }}</td>
                                    <td class="text-right">{{ $s['passRate'] }}%</td>
                                    <td class="text-center">
                                        @if($s['status'] === 'critical')
                                            <span class="badge badge-danger">Critical</span>
                                        @elseif($s['status'] === 'warning')
                                            <span class="badge badge-warning">Warning</span>
                                        @else
                                            <span class="badge badge-success">In Control</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="10" class="text-center text-muted py-4">
                                    <i class="fas fa-database mr-1"></i>
                                    No QC data available — run ETL sync to populate
                                </td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Levey-Jennings Drift Trends ──────────────────────────────────────── --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="section-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-chart-line mr-1 text-info"></i> Drift Trend Chart (Levey-Jennings)</span>
                    <select id="analyteSelector" class="form-control form-control-sm" style="width:220px">
                        @foreach(array_keys($driftTrends) as $analyte)
                        <option value="{{ $analyte }}">{{ $analyte }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="card-body">
                    @if(count($driftTrends))
                    <div class="chart-container">
                        <canvas id="driftChart"></canvas>
                    </div>
                    <div class="mt-2 d-flex flex-wrap gap-3" style="font-size:.78rem">
                        <span><span style="display:inline-block;width:24px;height:3px;background:#17a2b8;vertical-align:middle"></span> Avg Result</span>
                        <span><span style="display:inline-block;width:24px;height:2px;background:#28a745;vertical-align:middle;border-top:2px dashed #28a745"></span> Center Line (x*)</span>
                        <span><span style="display:inline-block;width:24px;height:2px;background:#dc3545;vertical-align:middle;border-top:2px dashed #dc3545"></span> UCL / LCL (±2 SD)</span>
                    </div>
                    @else
                    <p class="text-muted text-center py-4">
                        <i class="fas fa-chart-area fa-2x mb-2 d-block"></i>
                        No drift trend data available for this period.
                    </p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ── Pareto Chart + OOC Events ────────────────────────────────────────── --}}
    <div class="row mb-4">

        {{-- Pareto --}}
        <div class="col-md-6 mb-3">
            <div class="card h-100">
                <div class="section-header">
                    <i class="fas fa-sort-amount-down mr-1 text-warning"></i>
                    Pareto — Failure Ranking (80/20 Rule)
                </div>
                <div class="card-body">
                    @if(count($paretoData))
                    <div class="pareto-chart-container">
                        <canvas id="paretoChart"></canvas>
                    </div>
                    <div class="mt-2" style="font-size:.78rem">
                        <span class="badge badge-danger mr-1">Vital Few</span> = analytes contributing the top 80% of failures
                        &nbsp;|&nbsp;
                        <span class="badge badge-secondary mr-1">Useful Many</span> = remaining 20%
                    </div>
                    @else
                    <p class="text-muted text-center py-4">
                        <i class="fas fa-chart-bar fa-2x mb-2 d-block"></i>
                        No failure data available.
                    </p>
                    @endif
                </div>
            </div>
        </div>

        {{-- OOC Events --}}
        <div class="col-md-6 mb-3">
            <div class="card h-100">
                <div class="section-header">
                    <i class="fas fa-exclamation-triangle mr-1 text-danger"></i>
                    Out-of-Control Events (Last {{ $days }} days)
                </div>
                <div class="card-body p-0">
                    @if($oocEvents->count())
                    <div class="table-responsive" style="max-height:320px;overflow-y:auto">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="thead-light sticky-top">
                                <tr>
                                    <th>Analyte</th>
                                    <th class="text-right">Result</th>
                                    <th>Westgard Rule</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($oocEvents as $ev)
                                @php $is3sd = str_contains($ev->westgard_rule, '3SD'); @endphp
                                <tr class="{{ $is3sd ? 'table-danger' : 'table-warning' }}">
                                    <td>{{ $ev->analyte_name }}</td>
                                    <td class="text-right font-weight-bold">{{ $ev->result_value }}</td>
                                    <td>
                                        <span class="badge {{ $is3sd ? 'badge-danger' : 'badge-warning' }}">
                                            {{ $ev->westgard_rule }}
                                        </span>
                                    </td>
                                    <td><small>{{ \Carbon\Carbon::parse($ev->occurred_at)->format('d M Y') }}</small></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-5">
                        <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                        <p class="text-muted mb-0">No out-of-control events in this period.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// ── Data passed from PHP ──────────────────────────────────────────────────────
const DRIFT_DATA  = @json($driftTrends);
const PARETO_DATA = @json($paretoData);

// ── Levey-Jennings Drift Chart ────────────────────────────────────────────────
let driftChart = null;

function buildDriftChart(analyteName) {
    const series = DRIFT_DATA[analyteName];
    if (!series || series.length === 0) return;

    const labels     = series.map(p => p.date);
    const values     = series.map(p => p.avgValue);
    const ucl        = series.map(p => p.ucl);
    const lcl        = series.map(p => p.lcl);
    const centerLine = series.map(p => p.centerLine);

    const ctx = document.getElementById('driftChart').getContext('2d');
    if (driftChart) driftChart.destroy();

    driftChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [
                {
                    label: 'Avg Result',
                    data: values,
                    borderColor: '#17a2b8',
                    backgroundColor: 'rgba(23,162,184,.08)',
                    borderWidth: 2,
                    fill: false,
                    tension: 0.3,
                    pointRadius: 4,
                    pointBackgroundColor: series.map(p =>
                        p.controlStatus === 'OUT_OF_CONTROL' ? '#dc3545' :
                        p.controlStatus === 'WARNING'        ? '#ffc107' : '#17a2b8'
                    ),
                    pointRadius: series.map(p =>
                        p.controlStatus !== 'IN_CONTROL' ? 7 : 4
                    ),
                },
                {
                    label: 'Center Line (x*)',
                    data: centerLine,
                    borderColor: '#28a745',
                    borderWidth: 1.5,
                    borderDash: [6, 3],
                    fill: false,
                    pointRadius: 0,
                    tension: 0,
                },
                {
                    label: 'UCL (+2 SD)',
                    data: ucl,
                    borderColor: '#dc3545',
                    borderWidth: 1,
                    borderDash: [4, 4],
                    fill: false,
                    pointRadius: 0,
                    tension: 0,
                },
                {
                    label: 'LCL (−2 SD)',
                    data: lcl,
                    borderColor: '#dc3545',
                    borderWidth: 1,
                    borderDash: [4, 4],
                    fill: false,
                    pointRadius: 0,
                    tension: 0,
                },
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => {
                            const s = series[ctx.dataIndex];
                            if (ctx.datasetIndex === 0) {
                                return [
                                    ` Value: ${ctx.parsed.y}`,
                                    ` Status: ${s.controlStatus}`,
                                    ` Tests: ${s.testCount}`,
                                ];
                            }
                            return ` ${ctx.dataset.label}: ${ctx.parsed.y}`;
                        }
                    }
                }
            },
            scales: {
                x: { ticks: { maxRotation: 45 } },
                y: { title: { display: true, text: 'Measurement Value' } }
            }
        }
    });
}

// Initial render with first analyte
const firstAnalyte = Object.keys(DRIFT_DATA)[0];
if (firstAnalyte) buildDriftChart(firstAnalyte);

document.getElementById('analyteSelector')?.addEventListener('change', e => {
    buildDriftChart(e.target.value);
});

// ── Pareto Chart ──────────────────────────────────────────────────────────────
if (PARETO_DATA && PARETO_DATA.length > 0) {
    const pCtx = document.getElementById('paretoChart').getContext('2d');

    // Group by analyte — aggregate failure counts
    const grouped = {};
    PARETO_DATA.forEach(r => {
        grouped[r.analyte] = (grouped[r.analyte] || 0) + r.failureCount;
    });
    const labels   = Object.keys(grouped);
    const counts   = Object.values(grouped);
    const total    = counts.reduce((a, b) => a + b, 0);
    let cum = 0;
    const cumPct = counts.map(c => { cum += c; return Math.round(cum / total * 100); });

    const vitalFewColors = PARETO_DATA
        .filter((v, i) => labels[i])
        .map(r => r.category === 'vital_few' ? '#dc3545' : '#6c757d');

    new Chart(pCtx, {
        data: {
            labels,
            datasets: [
                {
                    type: 'bar',
                    label: 'Failure Count',
                    data: counts,
                    backgroundColor: labels.map((_, i) => cumPct[i] <= 80 ? '#dc3545' : '#6c757d'),
                    yAxisID: 'y',
                    order: 2,
                },
                {
                    type: 'line',
                    label: 'Cumulative %',
                    data: cumPct,
                    borderColor: '#343a40',
                    borderWidth: 2,
                    fill: false,
                    pointRadius: 4,
                    yAxisID: 'y2',
                    order: 1,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ctx.datasetIndex === 0
                            ? ` Failures: ${ctx.parsed.y}`
                            : ` Cumulative: ${ctx.parsed.y}%`
                    }
                }
            },
            scales: {
                y:  { title: { display: true, text: 'Failure Count' }, beginAtZero: true },
                y2: { position: 'right', min: 0, max: 100,
                      title: { display: true, text: 'Cumulative %' },
                      grid: { drawOnChartArea: false } }
            }
        }
    });
}
</script>
@endpush
