<div>
<style>
    .testing-dashboard { background-color: #f8fafc; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #1e293b; }
    .kebs-title-bar { background: linear-gradient(135deg, #6D0A0E 0%, #a8141b 100%); color: white; padding: 18px 20px 14px; border-radius: 10px; margin-bottom: 20px; box-shadow: 0 12px 24px -14px rgba(109,10,14,0.6); }
    .tat-header-top { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; flex-wrap: wrap; }
    .tat-header-title h5 { letter-spacing: 0.04em; }
    .tat-header-title small { display: inline-block; margin-top: 4px; }
    .tat-header-actions { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
    .tat-header-actions .dropdown-toggle { border-radius: 999px; padding-inline: 14px; }
    .tat-filter-bar { margin-top: 16px; padding-top: 16px; border-top: 1px solid rgba(255,255,255,0.18); display: flex; flex-wrap: wrap; gap: 12px; align-items: end; }
    .tat-filter-field { min-width: 160px; display: flex; flex-direction: column; gap: 6px; }
    .tat-filter-field.tat-filter-field-wide { min-width: 220px; flex: 1 1 280px; }
    .tat-filter-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: rgba(255,255,255,0.78); }
    .tat-filter-input,
    .tat-filter-date input { background: rgba(255,255,255,0.14) !important; color: #fff !important; border: 1px solid rgba(255,255,255,0.18) !important; border-radius: 10px !important; min-height: 38px; box-shadow: none !important; }
    .tat-filter-input:focus,
    .tat-filter-date input:focus { border-color: rgba(255,255,255,0.85) !important; box-shadow: 0 0 0 3px rgba(255,255,255,0.18) !important; }
    .tat-filter-input:disabled { opacity: 0.55; cursor: not-allowed; }
    .tat-filter-date { display: grid; grid-template-columns: 1fr auto 1fr; gap: 8px; align-items: center; }
    .tat-filter-date span { color: rgba(255,255,255,0.72); font-size: 12px; font-weight: 600; }
    .tat-filter-actions { display: flex; align-items: center; gap: 8px; margin-left: auto; flex-wrap: wrap; }
    .tat-reset-button { border-radius: 999px; padding-inline: 14px; }
    .tat-filter-summary { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 14px; }
    .tat-filter-chip { display: inline-flex; align-items: center; gap: 6px; padding: 6px 10px; border-radius: 999px; background: rgba(255,255,255,0.14); color: #fff; font-size: 12px; line-height: 1; }
    .tat-filter-chip strong { font-size: 11px; text-transform: uppercase; letter-spacing: 0.06em; color: rgba(255,255,255,0.88); }
    .tat-filter-chip-muted { background: rgba(255,255,255,0.08); color: rgba(255,255,255,0.82); }
    .metric-card { background-color: #6D0A0E; color: white; border-radius: 4px; text-align: center; padding: 15px; margin-bottom: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); transition: transform 0.2s; height: 90px; display: flex; flex-direction: column; justify-content: center; align-items: center; }
    .metric-card:hover { transform: translateY(-2px); }
    .metric-card.dark { background-color: #4a0709; }
    .metric-label { font-size: 11px; font-weight: bold; text-transform: uppercase; margin-bottom: 4px; opacity: 0.9; }
    .metric-value { font-size: 20px; font-weight: bold; }
    .pivot-card { background-color: white; border: 1px solid #e2e8f0; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); height: 100%; display: flex; flex-direction: column; width: 100%; margin-bottom: 0 !important; }
    .pivot-card .table-responsive { flex-grow: 1; }
    .pivot-header { background-color: #6D0A0E; color: white; padding: 8px 15px; font-weight: bold; font-size: 13px; border-radius: 4px 4px 0 0; }
    .pivot-table { width: 100%; border-collapse: collapse; font-size: 12px; }
    .pivot-table th { background-color: rgba(109, 10, 14, 0.08); padding: 10px; border: 1px solid #cbd5e1; text-align: left; color: #6D0A0E; }
    .pivot-table td { padding: 8px 10px; border: 1px solid #e2e8f0; color: #475569; vertical-align: middle; }
    .pivot-table tr:hover { background-color: #f8fafc; }
    .pivot-table tr.table-active td { background-color: rgba(109, 10, 14, 0.08); }
    .compliance-bar-container { height: 16px; background-color: #f1f5f9; border-radius: 2px; overflow: hidden; position: relative; }
    .compliance-bar { height: 100%; }
    .compliance-text { position: absolute; top:0; left:0; width:100%; height:100%; font-size:9px; display:flex; align-items:center; justify-content:center; font-weight:bold; color:#1e293b; }
    .scc-box { background-color: rgba(109, 10, 14, 0.08); border: 1px solid rgba(109, 10, 14, 0.2); padding: 15px; border-radius: 4px; text-align: center; margin-bottom: 10px; }
    .scc-label { font-size: 12px; font-weight: bold; color: #6D0A0E; margin-bottom: 5px; }
    .scc-value { font-size: 18px; font-weight: bold; color: #6D0A0E; }
    .opacity-75 { opacity: 0.75; }
    @media (max-width: 991.98px) {
        .tat-filter-field,
        .tat-filter-field.tat-filter-field-wide { min-width: 100%; }
        .tat-filter-actions { margin-left: 0; width: 100%; justify-content: flex-start; }
    }
    @media (max-width: 575.98px) {
        .kebs-title-bar { padding-inline: 14px; }
        .tat-header-actions { width: 100%; }
        .tat-filter-date { grid-template-columns: 1fr; }
        .tat-filter-date span { display: none; }
    }
</style>

<div class="container-fluid py-4 testing-dashboard">

    {{-- 1. Header: filters, period switcher, export --}}
    @include('livewire.mas.lab._header', [
        'available_sections' => $available_sections ?? [],
        'available_analysts' => $available_analysts ?? [],
        'available_zones' => $available_zones ?? [],
        'selectedAnalystId' => $selectedAnalystId ?? null,
        'selectedLabId' => $selectedLabId ?? null,
        'selectedZoneId' => $selectedZoneId ?? null,
        'startDate' => $startDate ?? null,
        'endDate' => $endDate ?? null,
    ])

    {{-- 2. KPI Scoreboard --}}
    @include('livewire.mas.lab._kpi_cards')

    {{-- 3. Main body --}}
    {{-- Row 1: Full Width Pivot Section --}}
    <div class="row mb-3">
        <div class="col-12">
            @include('livewire.mas.lab._pivot_table')
        </div>
    </div>

    {{-- Row 2: Compliance & Workflow Pipeline (Same Height) --}}
    <div class="row mb-3">
        {{-- Left: % TAT Compliance --}}
        <div class="col-md-6 d-flex align-items-stretch">
            @include('livewire.mas.lab._compliance_card')
        </div>

        {{-- Right: Workflow Stage Pipeline --}}
        <div class="col-md-6 d-flex align-items-stretch">
            @include('livewire.mas.lab._workflow_stages')
        </div>
    </div>

    {{-- Row 3: SCC Enclosed Table & Analyst Leaderboard (Same Height) --}}
    <div class="row mb-3">
        {{-- Left: Sample Control Performance (SCC) Enclosed Table --}}
        <div class="col-md-6 d-flex align-items-stretch">
            @include('livewire.mas.lab._scc_table')
        </div>

        {{-- Right: Analyst Performance Leaderboard --}}
        <div class="col-md-6 d-flex align-items-stretch">
            @include('livewire.mas.lab._analyst_leaderboard')
        </div>
    </div>

    {{-- Row 4: Overdue Aging Breakdown --}}
    <div class="row mb-3">
        <div class="col-12">
            @include('livewire.mas.lab._overdue_aging')
        </div>
    </div>

    {{-- 4. Detailed TAT Logs (full width) --}}
    @include('livewire.mas.lab._detailed_logs', [
        'detailedLogs' => $stats['detailed_logs'] ?? ['rows' => [], 'total' => 0, 'page' => 1, 'per_page' => 10, 'total_pages' => 1],
    ])

</div>
@stack('scripts')
</div>