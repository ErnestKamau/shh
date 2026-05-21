@extends('layouts.lab.layout.app', ['dataTable' => true, 'select2' => true])

@section('title2')
<title>Unified Module Reports Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
    /* Premium Modern Aesthetics */
    .dashboard-container {
        font-family: 'Plus Jakarta Sans', 'Outfit', sans-serif;
        background-color: #f8fafc;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
    }

    .dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 28px;
        border-bottom: 1px solid #e2e8f0;
        padding-bottom: 16px;
    }

    .dashboard-header h1 {
        font-size: 26px;
        font-weight: 700;
        color: #0f172a;
        margin: 0;
        letter-spacing: -0.02em;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .dashboard-header h1 i {
        color: #10b981;
    }

    .dashboard-header p {
        color: #64748b;
        margin: 4px 0 0 0;
        font-size: 14px;
    }

    /* Tab Layout & Micro-animations */
    .module-tabs {
        display: flex;
        gap: 8px;
        background: #e2e8f0;
        padding: 6px;
        border-radius: 12px;
        margin-bottom: 24px;
        overflow-x: auto;
    }

    .tab-btn {
        border: none;
        background: transparent;
        padding: 10px 18px;
        font-size: 14px;
        font-weight: 600;
        color: #475569;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        white-space: nowrap;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .tab-btn:hover {
        color: #0f172a;
        background: rgba(255, 255, 255, 0.5);
    }

    .tab-btn.active {
        background: #ffffff;
        color: #10b981;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }

    /* Content Cards and Forms */
    .tab-content-panel {
        display: none;
        animation: fadeIn 0.4s ease;
    }

    .tab-content-panel.active {
        display: block;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .report-selection-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    .report-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 16px;
        transition: all 0.3s ease;
        position: relative;
        cursor: pointer;
    }

    .report-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.03);
        border-color: #10b981;
    }

    .report-card.selected {
        border-color: #10b981;
        background: #f0fdf4;
    }

    .report-card h3 {
        margin: 0 0 6px 0;
        font-size: 14px;
        font-weight: 700;
        color: #1e293b;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .report-card p {
        margin: 0;
        font-size: 12px;
        color: #64748b;
        line-height: 1.4;
    }

    .report-card .badge {
        position: absolute;
        top: 12px;
        right: 12px;
        font-size: 9px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        background: #e2e8f0;
        color: #475569;
        padding: 2px 6px;
        border-radius: 999px;
    }

    /* Filter Form Styling (Unique per tab) */
    .filter-panel {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 20px;
        margin-bottom: 28px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.01);
    }

    .filter-panel h4 {
        margin: 0 0 16px 0;
        font-size: 15px;
        font-weight: 700;
        color: #0f172a;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .filter-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 12px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .form-group label {
        font-size: 11px;
        font-weight: 600;
        color: #475569;
    }

    .form-control-custom {
        padding: 8px 12px;
        border-radius: 6px;
        border: 1px solid #cbd5e1;
        font-size: 12px;
        color: #1e293b;
        outline: none;
        transition: border-color 0.2s;
        background: #fff;
    }

    .form-control-custom:focus {
        border-color: #10b981;
    }

    .submit-actions {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        margin-top: 16px;
        border-top: 1px solid #f1f5f9;
        padding-top: 12px;
    }

    /* Premium Buttons */
    .btn-premium {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: #ffffff;
        font-size: 12px;
        font-weight: 600;
        padding: 8px 16px;
        border-radius: 6px;
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.15);
        transition: all 0.2s;
    }

    .btn-premium:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(16, 185, 129, 0.25);
    }

    .btn-outline-custom {
        background: #ffffff;
        color: #475569;
        border: 1px solid #cbd5e1;
        font-size: 12px;
        font-weight: 600;
        padding: 8px 16px;
        border-radius: 6px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s;
    }

    .btn-outline-custom:hover {
        background: #f8fafc;
        border-color: #94a3b8;
    }

    /* Data Preview */
    .preview-section {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 20px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.01);
    }

    .preview-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
    }

    .preview-header h2 {
        font-size: 16px;
        font-weight: 700;
        color: #0f172a;
        margin: 0;
    }

    .empty-state {
        text-align: center;
        padding: 36px 16px;
    }

    .empty-state i {
        font-size: 40px;
        color: #94a3b8;
        margin-bottom: 12px;
    }

    .empty-state h3 {
        margin: 0 0 6px 0;
        font-size: 14px;
        color: #475569;
    }

    .empty-state p {
        margin: 0;
        font-size: 12px;
        color: #94a3b8;
    }

    /* Standardized Tables */
    .table-container {
        overflow-x: auto;
    }

    .table-custom {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
        font-size: 12px;
    }

    .table-custom th {
        background: #f8fafc;
        color: #475569;
        font-weight: 700;
        padding: 10px 14px;
        border-bottom: 2px solid #e2e8f0;
    }

    .table-custom td {
        padding: 10px 14px;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
    }

    .table-custom tr:hover td {
        background-color: #f8fafc;
    }

    .status-pill {
        display: inline-flex;
        padding: 2px 6px;
        border-radius: 999px;
        font-size: 10px;
        font-weight: 600;
        text-transform: capitalize;
    }

    .status-pill.success { background: #dcfce7; color: #15803d; }
    .status-pill.warning { background: #fef3c7; color: #b45309; }
    .status-pill.danger { background: #fee2e2; color: #b91c1c; }
    .status-pill.info { background: #e0f2fe; color: #0369a1; }
</style>
@endsection

@section('content2')
<div class="dashboard-container">
    <div class="dashboard-header">
        <div>
            <h1><i class="mdi mdi-chart-box-outline"></i> Centralized Module Reports</h1>
            <p>Generate highly filterable business intelligence reports across all GCLA system modules.</p>
        </div>
        <div class="dashboard-meta">
            <span class="status-pill info">GCLA LIMS Certified Layouts</span>
        </div>
    </div>

    <!-- Navigation Tabs representing System Modules -->
    <div class="module-tabs">
        <button class="tab-btn {{ $activeTab === 'sample_management' ? 'active' : '' }}" onclick="switchTab('sample_management')">
            <i class="mdi mdi-flask-outline"></i> Sample Management
        </button>
        <button class="tab-btn {{ $activeTab === 'lab_operations' ? 'active' : '' }}" onclick="switchTab('lab_operations')">
            <i class="mdi mdi-cogs"></i> Lab Operations
        </button>
        <button class="tab-btn {{ $activeTab === 'zonal_operations' ? 'active' : '' }}" onclick="switchTab('zonal_operations')">
            <i class="mdi mdi-map-marker-distance"></i> Zonal Operations
        </button>
        <button class="tab-btn {{ $activeTab === 'finance' ? 'active' : '' }}" onclick="switchTab('finance')">
            <i class="mdi mdi-cash-register"></i> Finance
        </button>
        <button class="tab-btn {{ $activeTab === 'procurement' ? 'active' : '' }}" onclick="switchTab('procurement')">
            <i class="mdi mdi-cart-outline"></i> Procurement
        </button>
        <button class="tab-btn {{ $activeTab === 'qa_risk' ? 'active' : '' }}" onclick="switchTab('qa_risk')">
            <i class="mdi mdi-shield-check-outline"></i> QA & Risk
        </button>
        <button class="tab-btn {{ $activeTab === 'internal_audit' ? 'active' : '' }}" onclick="switchTab('internal_audit')">
            <i class="mdi mdi-file-document-edit-outline"></i> Internal Audit
        </button>
        <button class="tab-btn {{ $activeTab === 'management' ? 'active' : '' }}" onclick="switchTab('management')">
            <i class="mdi mdi-account-multiple-outline"></i> Management
        </button>
    </div>

    <!-- TAB 1: Sample Management -->
    <div id="sample_management" class="tab-content-panel {{ $activeTab === 'sample_management' ? 'active' : '' }}">
        <div class="report-selection-grid">
            <div class="report-card {{ $reportType === 'sample_register' ? 'selected' : '' }}" onclick="selectReport('sample_register', 'sample_management')">
                <span class="badge">Active</span>
                <h3><i class="mdi mdi-book-open-outline"></i> Sample Register</h3>
                <p>Track all registered samples, submission channels, batch statuses, and client allocations.</p>
            </div>
            <div class="report-card {{ $reportType === 'chain_of_custody' ? 'selected' : '' }}" onclick="selectReport('chain_of_custody', 'sample_management')">
                <span class="badge">Active</span>
                <h3><i class="mdi mdi-history"></i> Chain of Custody</h3>
                <p>Examine transit histories, sign-offs, custodian hands, and physical custody transfers.</p>
            </div>
            <div class="report-card {{ $reportType === 'rejection' ? 'selected' : '' }}" onclick="selectReport('rejection', 'sample_management')">
                <span class="badge">Active</span>
                <h3><i class="mdi mdi-close-octagon-outline"></i> Sample Rejections</h3>
                <p>Review stagings, rejected submission logs, non-compliance checkmarks, and analyst rationales.</p>
            </div>
            <div class="report-card {{ $reportType === 'sample_return' ? 'selected' : '' }}" onclick="selectReport('sample_return', 'sample_management')">
                <span class="badge">Active</span>
                <h3><i class="mdi mdi-keyboard-return"></i> Exhibit Returns</h3>
                <p>Track returned sample exhibits, custodian sign-offs, and investigator handovers.</p>
            </div>
            <div class="report-card {{ $reportType === 'retained_samples' ? 'selected' : '' }}" onclick="selectReport('retained_samples', 'sample_management')">
                <span class="badge">Active</span>
                <h3><i class="mdi mdi-archive-outline"></i> Retained Samples</h3>
                <p>Register and shelf location map of retained chemical and forensic samples.</p>
            </div>
            <div class="report-card {{ $reportType === 'disposal' ? 'selected' : '' }}" onclick="selectReport('disposal', 'sample_management')">
                <span class="badge">Active</span>
                <h3><i class="mdi mdi-delete-sweep-outline"></i> Disposal Log</h3>
                <p>Observe chemical samples discarded, expiration actions, and biosafety disposal logs.</p>
            </div>
            <div class="report-card {{ $reportType === 'resampling' ? 'selected' : '' }}" onclick="selectReport('resampling', 'sample_management')">
                <span class="badge">Active</span>
                <h3><i class="mdi mdi-refresh"></i> Resampling Report</h3>
                <p>Track and audit re-sampling logs, reason codes, and analyst authorizations.</p>
            </div>
            <div class="report-card {{ $reportType === 'amendment' ? 'selected' : '' }}" onclick="selectReport('amendment', 'sample_management')">
                <span class="badge">Active</span>
                <h3><i class="mdi mdi-file-document-edit"></i> Batch Amendments</h3>
                <p>Audit trail of changes, corrections, and revisions made to finalized batch certificates.</p>
            </div>
        </div>

        <!-- Custom Unique Filters: Sample Management -->
        <div class="filter-panel" id="filter-panel-sample_management" style="display: {{ $activeTab === 'sample_management' && $reportType ? 'block' : 'none' }};">
            <h4><i class="mdi mdi-tune-variant"></i> Sample Management Filter Configuration</h4>
            <form action="{{ route('module-reports.view') }}" method="POST">
                @csrf
                <input type="hidden" name="active_tab" value="sample_management">
                <input type="hidden" name="report_type" class="tab-report-type-input" value="{{ $reportType }}">
                
                <div class="filter-grid">
                    <div class="form-group">
                        <label><i class="mdi mdi-calendar"></i> Date From</label>
                        <input type="date" name="date_from" class="form-control-custom" value="{{ $filters['date_from'] ?? '' }}">
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-calendar"></i> Date To</label>
                        <input type="date" name="date_to" class="form-control-custom" value="{{ $filters['date_to'] ?? '' }}">
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-account-multiple"></i> Customer / Client</label>
                        <select name="client_id" class="form-control-custom">
                            <option value="all">-- All Customers --</option>
                            @foreach($clients as $c)
                                <option value="{{ $c->id }}" {{ ($filters['client_id'] ?? '') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-flask-outline"></i> Analysis Type</label>
                        <select name="analysis_type" class="form-control-custom">
                            <option value="all">-- All Analysis Types --</option>
                            @foreach($sampleTypes as $st)
                                <option value="{{ $st->id }}" {{ ($filters['analysis_type'] ?? '') == $st->id ? 'selected' : '' }}>{{ $st->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-format-list-bulleted"></i> Parameter / Analyte</label>
                        <select name="analyte_id" class="form-control-custom">
                            <option value="all">-- All Parameters --</option>
                            @foreach($analytes as $an)
                                <option value="{{ $an->id }}" {{ ($filters['analyte_id'] ?? '') == $an->id ? 'selected' : '' }}>{{ $an->name }} ({{ $an->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-account"></i> Analyst / User</label>
                        <select name="user_id" class="form-control-custom">
                            <option value="all">-- All Analysts --</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" {{ ($filters['user_id'] ?? '') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-home-map-marker"></i> Lab / Section</label>
                        <select name="lab_id" class="form-control-custom">
                            <option value="all">-- All Labs --</option>
                            @foreach($labs as $l)
                                <option value="{{ $l->id }}" {{ ($filters['lab_id'] ?? '') == $l->id ? 'selected' : '' }}>{{ $l->name }} ({{ $l->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-numeric"></i> Lab No.</label>
                        <input type="text" name="lab_no" class="form-control-custom" placeholder="e.g. LAB-2026-001" value="{{ $filters['lab_no'] ?? '' }}">
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-barcode"></i> Sample Number</label>
                        <input type="text" name="sample_number" class="form-control-custom" placeholder="e.g. SMPL-1004" value="{{ $filters['sample_number'] ?? '' }}">
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-gauge"></i> Equipment / Instrument</label>
                        <select name="equipment_id" class="form-control-custom">
                            <option value="all">-- All Equipment --</option>
                            @foreach($equipments as $eq)
                                <option value="{{ $eq->id }}" {{ ($filters['equipment_id'] ?? '') == $eq->id ? 'selected' : '' }}>{{ $eq->name }} ({{ $eq->equipment_number }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-certificate"></i> Standards / CRM</label>
                        <select name="standard_id" class="form-control-custom">
                            <option value="all">-- All Standards --</option>
                            @foreach($allStandards as $st)
                                <option value="{{ $st->id }}" {{ ($filters['standard_id'] ?? '') == $st->id ? 'selected' : '' }}>{{ $st->name }} ({{ $st->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-progress-check"></i> Workflow Status</label>
                        <select name="status" class="form-control-custom">
                            <option value="all">-- All Statuses --</option>
                            <option value="Active" {{ ($filters['status'] ?? '') == 'Active' ? 'selected' : '' }}>Active</option>
                            <option value="Pending" {{ ($filters['status'] ?? '') == 'Pending' ? 'selected' : '' }}>Pending</option>
                            <option value="Dispatched" {{ ($filters['status'] ?? '') == 'Dispatched' ? 'selected' : '' }}>Dispatched</option>
                            <option value="Validated" {{ ($filters['status'] ?? '') == 'Validated' ? 'selected' : '' }}>Validated</option>
                            <option value="Approved" {{ ($filters['status'] ?? '') == 'Approved' ? 'selected' : '' }}>Approved</option>
                        </select>
                    </div>
                </div>
                <div class="submit-actions">
                    <button type="submit" class="btn-premium"><i class="mdi mdi-cogs"></i> Query & Generate Preview</button>
                </div>
            </form>
        </div>
    </div>

    <!-- TAB 2: Lab Operations -->
    <div id="lab_operations" class="tab-content-panel {{ $activeTab === 'lab_operations' ? 'active' : '' }}">
        <div class="report-selection-grid">
            <div class="report-card {{ $reportType === 'workbook' ? 'selected' : '' }}" onclick="selectReport('workbook', 'lab_operations')">
                <span class="badge">Active</span>
                <h3><i class="mdi mdi-book-open-page-variant"></i> Electronic Workbooks</h3>
                <p>Verify formulas, input measurements, and values logged in operational worksheets.</p>
            </div>
            <div class="report-card {{ $reportType === 'coa_report' ? 'selected' : '' }}" onclick="selectReport('coa_report', 'lab_operations')">
                <span class="badge">Active</span>
                <h3><i class="mdi mdi-clipboard-text-play-outline"></i> Draft & Final COA</h3>
                <p>Inspect certificate of analysis status checks, drafts, and released manifests.</p>
            </div>
            <div class="report-card {{ $reportType === 'method_validation' ? 'selected' : '' }}" onclick="selectReport('method_validation', 'lab_operations')">
                <span class="badge">Active</span>
                <h3><i class="mdi mdi-clipboard-check-outline"></i> Method Validation</h3>
                <p>Monitor method validation registers, accuracy scopes, and publications.</p>
            </div>
            <div class="report-card {{ $reportType === 'proficiency_testing' ? 'selected' : '' }}" onclick="selectReport('proficiency_testing', 'lab_operations')">
                <span class="badge">Active</span>
                <h3><i class="mdi mdi-certificate-outline"></i> Proficiency Testing</h3>
                <p>Participating inter-lab round-robin comparison evaluations and score metrics.</p>
            </div>
            <div class="report-card {{ $reportType === 'instrument_log' ? 'selected' : '' }}" onclick="selectReport('instrument_log', 'lab_operations')">
                <span class="badge">Active</span>
                <h3><i class="mdi mdi-poll"></i> Instrument Utilization</h3>
                <p>Track operating times, check-ins, and analyst logs across GCMS, ICPOES systems.</p>
            </div>
            <div class="report-card {{ $reportType === 'intermediate_check' ? 'selected' : '' }}" onclick="selectReport('intermediate_check', 'lab_operations')">
                <span class="badge">Active</span>
                <h3><i class="mdi mdi-swap-horizontal-bold"></i> Intermediate Checks</h3>
                <p>Intermediate calibration verification records of balances, chambers, and micro-pipettes.</p>
            </div>
            <div class="report-card {{ $reportType === 'instrument_calibration' ? 'selected' : '' }}" onclick="selectReport('instrument_calibration', 'lab_operations')">
                <span class="badge">Active</span>
                <h3><i class="mdi mdi-scale-balance"></i> Calibration Certificate</h3>
                <p>Review diagnostic equipment calibrations, tolerance testings, and pass/fail states.</p>
            </div>
            <div class="report-card {{ $reportType === 'preventive_maintenance' ? 'selected' : '' }}" onclick="selectReport('preventive_maintenance', 'lab_operations')">
                <span class="badge">Active</span>
                <h3><i class="mdi mdi-wrench-outline"></i> Preventive Maintenance</h3>
                <p>Track maintenance schedules, actions taken, and certified calibration contractor sign-offs.</p>
            </div>
            <div class="report-card {{ $reportType === 'environmental_monitoring' ? 'selected' : '' }}" onclick="selectReport('environmental_monitoring', 'lab_operations')">
                <span class="badge">Active</span>
                <h3><i class="mdi mdi-thermometer-lines"></i> Environmental Chart</h3>
                <p>Monitor cleanroom temperature, pressure, relative humidity records, and bounds.</p>
            </div>
            <div class="report-card {{ $reportType === 'decontamination_register' ? 'selected' : '' }}" onclick="selectReport('decontamination_register', 'lab_operations')">
                <span class="badge">Active</span>
                <h3><i class="mdi mdi-spray"></i> Decontamination Log</h3>
                <p>Trace cleanroom cleaning cycles, biosafety hoods decontamination, and chemical logs.</p>
            </div>
            <div class="report-card {{ $reportType === 'standards' ? 'selected' : '' }}" onclick="selectReport('standards', 'lab_operations')">
                <span class="badge">Active</span>
                <h3><i class="mdi mdi-vector-triangle"></i> Standards & CRM Monitor</h3>
                <p>Check calibration reference logs, chemical standards, lot codes, and expiry monitors.</p>
            </div>
            <div class="report-card {{ $reportType === 'tat' ? 'selected' : '' }}" onclick="selectReport('tat', 'lab_operations')">
                <span class="badge">Active</span>
                <h3><i class="mdi mdi-clock-fast"></i> TAT Analysis</h3>
                <p>Track process milestones and analyze bottlenecks in Sample Testing & Validation.</p>
            </div>
            <div class="report-card {{ $reportType === 'backlog_analysis' ? 'selected' : '' }}" onclick="selectReport('backlog_analysis', 'lab_operations')">
                <span class="badge">Active</span>
                <h3><i class="mdi mdi-timer-sand"></i> Backlog Analysis</h3>
                <p>Audits backlog sample volumes, SLA breaches, and pending analytical works.</p>
            </div>
            <div class="report-card {{ $reportType === 'performance_report' ? 'selected' : '' }}" onclick="selectReport('performance_report', 'lab_operations')">
                <span class="badge">Active</span>
                <h3><i class="mdi mdi-trending-up"></i> Section Performance</h3>
                <p>Assess samples processed, output targets, and analyst efficiencies by Lab Section.</p>
            </div>
            <div class="report-card {{ $reportType === 'trend_analysis' ? 'selected' : '' }}" onclick="selectReport('trend_analysis', 'lab_operations')">
                <span class="badge">Active</span>
                <h3><i class="mdi mdi-chart-line"></i> Trend Analysis</h3>
                <p>Evaluate matrix concentrations, analyte trends, and baseline variance histories.</p>
            </div>
        </div>

        <!-- Custom Unique Filters: Lab Operations -->
        <div class="filter-panel" id="filter-panel-lab_operations" style="display: {{ $activeTab === 'lab_operations' && $reportType ? 'block' : 'none' }};">
            <h4><i class="mdi mdi-tune-variant"></i> Lab Operations Filter Configuration</h4>
            <form action="{{ route('module-reports.view') }}" method="POST">
                @csrf
                <input type="hidden" name="active_tab" value="lab_operations">
                <input type="hidden" name="report_type" class="tab-report-type-input" value="{{ $reportType }}">
                
                <div class="filter-grid">
                    <div class="form-group">
                        <label><i class="mdi mdi-calendar"></i> Date From</label>
                        <input type="date" name="date_from" class="form-control-custom" value="{{ $filters['date_from'] ?? '' }}">
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-calendar"></i> Date To</label>
                        <input type="date" name="date_to" class="form-control-custom" value="{{ $filters['date_to'] ?? '' }}">
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-account"></i> Analyst / Operator</label>
                        <select name="user_id" class="form-control-custom">
                            <option value="all">-- All Analysts --</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" {{ ($filters['user_id'] ?? '') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-gauge"></i> Equipment / Instrument</label>
                        <select name="equipment_id" class="form-control-custom">
                            <option value="all">-- All Equipment --</option>
                            @foreach($equipments as $eq)
                                <option value="{{ $eq->id }}" {{ ($filters['equipment_id'] ?? '') == $eq->id ? 'selected' : '' }}>{{ $eq->name }} ({{ $eq->equipment_number }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-certificate"></i> Standards / CRM</label>
                        <select name="standard_id" class="form-control-custom">
                            <option value="all">-- All Standards --</option>
                            @foreach($allStandards as $st)
                                <option value="{{ $st->id }}" {{ ($filters['standard_id'] ?? '') == $st->id ? 'selected' : '' }}>{{ $st->name }} ({{ $st->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-format-list-bulleted"></i> Parameter / Analyte</label>
                        <select name="analyte_id" class="form-control-custom">
                            <option value="all">-- All Parameters --</option>
                            @foreach($analytes as $an)
                                <option value="{{ $an->id }}" {{ ($filters['analyte_id'] ?? '') == $an->id ? 'selected' : '' }}>{{ $an->name }} ({{ $an->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-home-map-marker"></i> Lab Section</label>
                        <select name="lab_id" class="form-control-custom">
                            <option value="all">-- All Labs --</option>
                            @foreach($labs as $l)
                                <option value="{{ $l->id }}" {{ ($filters['lab_id'] ?? '') == $l->id ? 'selected' : '' }}>{{ $l->name }} ({{ $l->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-progress-check"></i> Test Status</label>
                        <select name="status" class="form-control-custom">
                            <option value="all">-- All Statuses --</option>
                            <option value="Active" {{ ($filters['status'] ?? '') == 'Active' ? 'selected' : '' }}>Active</option>
                            <option value="Pending" {{ ($filters['status'] ?? '') == 'Pending' ? 'selected' : '' }}>Pending</option>
                            <option value="Dispatched" {{ ($filters['status'] ?? '') == 'Dispatched' ? 'selected' : '' }}>Dispatched</option>
                            <option value="Validated" {{ ($filters['status'] ?? '') == 'Validated' ? 'selected' : '' }}>Validated</option>
                            <option value="Approved" {{ ($filters['status'] ?? '') == 'Approved' ? 'selected' : '' }}>Approved</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-flask-outline"></i> Analysis Type</label>
                        <select name="analysis_type" class="form-control-custom">
                            <option value="all">-- All Analysis Types --</option>
                            @foreach($sampleTypes as $st)
                                <option value="{{ $st->id }}" {{ ($filters['analysis_type'] ?? '') == $st->id ? 'selected' : '' }}>{{ $st->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-account-multiple"></i> Customer / Client</label>
                        <select name="client_id" class="form-control-custom">
                            <option value="all">-- All Customers --</option>
                            @foreach($clients as $c)
                                <option value="{{ $c->id }}" {{ ($filters['client_id'] ?? '') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-numeric"></i> Lab No.</label>
                        <input type="text" name="lab_no" class="form-control-custom" placeholder="e.g. LAB-2026-001" value="{{ $filters['lab_no'] ?? '' }}">
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-barcode"></i> Sample Number</label>
                        <input type="text" name="sample_number" class="form-control-custom" placeholder="e.g. SMPL-1004" value="{{ $filters['sample_number'] ?? '' }}">
                    </div>
                </div>
                <div class="submit-actions">
                    <button type="submit" class="btn-premium"><i class="mdi mdi-cogs"></i> Query & Generate Preview</button>
                </div>
            </form>
        </div>
    </div>

    <!-- TAB 3: Zonal Operations -->
    <div id="zonal_operations" class="tab-content-panel {{ $activeTab === 'zonal_operations' ? 'active' : '' }}">
        <div class="report-selection-grid">
            <div class="report-card {{ $reportType === 'interzone_transfers' ? 'selected' : '' }}" onclick="selectReport('interzone_transfers', 'zonal_operations')">
                <span class="badge">Active</span>
                <h3><i class="mdi mdi-map-marker-distance"></i> Interzone Transfer Log</h3>
                <p>Audits sample transits, logistics courier details, and transfer stage histories.</p>
            </div>
            <div class="report-card {{ $reportType === 'zonal_performance' ? 'selected' : '' }}" onclick="selectReport('zonal_performance', 'zonal_operations')">
                <span class="badge">Active</span>
                <h3><i class="mdi mdi-map-outline"></i> Zonal Performance</h3>
                <p>Track sample volumes, SLA fulfillment rates, and rejections by GCLA regional zones.</p>
            </div>
        </div>

        <!-- Custom Unique Filters: Zonal Operations -->
        <div class="filter-panel" id="filter-panel-zonal_operations" style="display: {{ $activeTab === 'zonal_operations' && $reportType ? 'block' : 'none' }};">
            <h4><i class="mdi mdi-tune-variant"></i> Zonal Operations Filter Configuration</h4>
            <form action="{{ route('module-reports.view') }}" method="POST">
                @csrf
                <input type="hidden" name="active_tab" value="zonal_operations">
                <input type="hidden" name="report_type" class="tab-report-type-input" value="{{ $reportType }}">
                
                <div class="filter-grid">
                    <div class="form-group">
                        <label><i class="mdi mdi-calendar"></i> Date From</label>
                        <input type="date" name="date_from" class="form-control-custom" value="{{ $filters['date_from'] ?? '' }}">
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-calendar"></i> Date To</label>
                        <input type="date" name="date_to" class="form-control-custom" value="{{ $filters['date_to'] ?? '' }}">
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-home-map-marker"></i> Lab / Zone Center</label>
                        <select name="lab_id" class="form-control-custom">
                            <option value="all">-- All Labs --</option>
                            @foreach($labs as $l)
                                <option value="{{ $l->id }}" {{ ($filters['lab_id'] ?? '') == $l->id ? 'selected' : '' }}>{{ $l->name }} ({{ $l->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-numeric"></i> Lab No.</label>
                        <input type="text" name="lab_no" class="form-control-custom" placeholder="e.g. LAB-2026-001" value="{{ $filters['lab_no'] ?? '' }}">
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-barcode"></i> Sample Number</label>
                        <input type="text" name="sample_number" class="form-control-custom" placeholder="e.g. SMPL-1004" value="{{ $filters['sample_number'] ?? '' }}">
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-progress-check"></i> Transfer Status</label>
                        <select name="status" class="form-control-custom">
                            <option value="all">-- All Statuses --</option>
                            <option value="Active" {{ ($filters['status'] ?? '') == 'Active' ? 'selected' : '' }}>Active</option>
                            <option value="Pending" {{ ($filters['status'] ?? '') == 'Pending' ? 'selected' : '' }}>Pending</option>
                            <option value="Dispatched" {{ ($filters['status'] ?? '') == 'Dispatched' ? 'selected' : '' }}>Dispatched</option>
                            <option value="Validated" {{ ($filters['status'] ?? '') == 'Validated' ? 'selected' : '' }}>Validated</option>
                            <option value="Approved" {{ ($filters['status'] ?? '') == 'Approved' ? 'selected' : '' }}>Approved</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-flask-outline"></i> Analysis Type</label>
                        <select name="analysis_type" class="form-control-custom">
                            <option value="all">-- All Analysis Types --</option>
                            @foreach($sampleTypes as $st)
                                <option value="{{ $st->id }}" {{ ($filters['analysis_type'] ?? '') == $st->id ? 'selected' : '' }}>{{ $st->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-format-list-bulleted"></i> Parameter / Analyte</label>
                        <select name="analyte_id" class="form-control-custom">
                            <option value="all">-- All Parameters --</option>
                            @foreach($analytes as $an)
                                <option value="{{ $an->id }}" {{ ($filters['analyte_id'] ?? '') == $an->id ? 'selected' : '' }}>{{ $an->name }} ({{ $an->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-account"></i> Analyst / Operator</label>
                        <select name="user_id" class="form-control-custom">
                            <option value="all">-- All Analysts --</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" {{ ($filters['user_id'] ?? '') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-gauge"></i> Equipment / Instrument</label>
                        <select name="equipment_id" class="form-control-custom">
                            <option value="all">-- All Equipment --</option>
                            @foreach($equipments as $eq)
                                <option value="{{ $eq->id }}" {{ ($filters['equipment_id'] ?? '') == $eq->id ? 'selected' : '' }}>{{ $eq->name }} ({{ $eq->equipment_number }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-certificate"></i> Standards / CRM</label>
                        <select name="standard_id" class="form-control-custom">
                            <option value="all">-- All Standards --</option>
                            @foreach($allStandards as $st)
                                <option value="{{ $st->id }}" {{ ($filters['standard_id'] ?? '') == $st->id ? 'selected' : '' }}>{{ $st->name }} ({{ $st->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-account-multiple"></i> Customer / Client</label>
                        <select name="client_id" class="form-control-custom">
                            <option value="all">-- All Customers --</option>
                            @foreach($clients as $c)
                                <option value="{{ $c->id }}" {{ ($filters['client_id'] ?? '') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="submit-actions">
                    <button type="submit" class="btn-premium"><i class="mdi mdi-cogs"></i> Query & Generate Preview</button>
                </div>
            </form>
        </div>
    </div>

    <!-- TAB 4: Finance -->
    <div id="finance" class="tab-content-panel {{ $activeTab === 'finance' ? 'active' : '' }}">
        <div class="report-selection-grid">
            <div class="report-card {{ $reportType === 'proforma_invoice' ? 'selected' : '' }}" onclick="selectReport('proforma_invoice', 'finance')">
                <span class="badge">Active</span>
                <h3><i class="mdi mdi-file-document-outline"></i> Proforma Invoice</h3>
                <p>Track generated proforma invoice bills, quantities, and client outstanding limits.</p>
            </div>
            <div class="report-card {{ $reportType === 'payment_receipt' ? 'selected' : '' }}" onclick="selectReport('payment_receipt', 'finance')">
                <span class="badge">Active</span>
                <h3><i class="mdi mdi-receipt"></i> Payment Receipts</h3>
                <p>Logs cash register entries, bank payouts, and LIMS service receipts.</p>
            </div>
            <div class="report-card {{ $reportType === 'aging_receivables' ? 'selected' : '' }}" onclick="selectReport('aging_receivables', 'finance')">
                <span class="badge">Active</span>
                <h3><i class="mdi mdi-cash-multiple"></i> Aging Receivables</h3>
                <p>Analyze credit status, days outstanding, and client debt aging classifications.</p>
            </div>
            <div class="report-card {{ $reportType === 'revenue_summary' ? 'selected' : '' }}" onclick="selectReport('revenue_summary', 'finance')">
                <span class="badge">Active</span>
                <h3><i class="mdi mdi-cash-usd-outline"></i> Lab Revenue Performance</h3>
                <p>Invoiced amounts and collection reviews categorized by active lab sections.</p>
            </div>
            <div class="report-card {{ $reportType === 'reconciliation_report' ? 'selected' : '' }}" onclick="selectReport('reconciliation_report', 'finance')">
                <span class="badge">Active</span>
                <h3><i class="mdi mdi-swap-horizontal"></i> Reconciliation Report</h3>
                <p>Review reconciliation histories between invoice logs and actual bank receipts.</p>
            </div>
        </div>

        <!-- Custom Unique Filters: Finance -->
        <div class="filter-panel" id="filter-panel-finance" style="display: {{ $activeTab === 'finance' && $reportType ? 'block' : 'none' }};">
            <h4><i class="mdi mdi-tune-variant"></i> Finance & Accounts Filter Configuration</h4>
            <form action="{{ route('module-reports.view') }}" method="POST">
                @csrf
                <input type="hidden" name="active_tab" value="finance">
                <input type="hidden" name="report_type" class="tab-report-type-input" value="{{ $reportType }}">
                
                <div class="filter-grid">
                    <div class="form-group">
                        <label><i class="mdi mdi-calendar"></i> Date From</label>
                        <input type="date" name="date_from" class="form-control-custom" value="{{ $filters['date_from'] ?? '' }}">
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-calendar"></i> Date To</label>
                        <input type="date" name="date_to" class="form-control-custom" value="{{ $filters['date_to'] ?? '' }}">
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-account-multiple"></i> Customer / Client</label>
                        <select name="client_id" class="form-control-custom">
                            <option value="all">-- All Customers --</option>
                            @foreach($clients as $c)
                                <option value="{{ $c->id }}" {{ ($filters['client_id'] ?? '') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-numeric"></i> Lab No.</label>
                        <input type="text" name="lab_no" class="form-control-custom" placeholder="e.g. LAB-2026-001" value="{{ $filters['lab_no'] ?? '' }}">
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-barcode"></i> Sample Number</label>
                        <input type="text" name="sample_number" class="form-control-custom" placeholder="e.g. SMPL-1004" value="{{ $filters['sample_number'] ?? '' }}">
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-progress-check"></i> Invoice Status</label>
                        <select name="status" class="form-control-custom">
                            <option value="all">-- All Statuses --</option>
                            <option value="Active" {{ ($filters['status'] ?? '') == 'Active' ? 'selected' : '' }}>Active / Paid</option>
                            <option value="Pending" {{ ($filters['status'] ?? '') == 'Pending' ? 'selected' : '' }}>Pending / Unpaid</option>
                            <option value="Dispatched" {{ ($filters['status'] ?? '') == 'Dispatched' ? 'selected' : '' }}>Dispatched</option>
                            <option value="Validated" {{ ($filters['status'] ?? '') == 'Validated' ? 'selected' : '' }}>Validated</option>
                            <option value="Approved" {{ ($filters['status'] ?? '') == 'Approved' ? 'selected' : '' }}>Approved</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-flask-outline"></i> Analysis Type</label>
                        <select name="analysis_type" class="form-control-custom">
                            <option value="all">-- All Analysis Types --</option>
                            @foreach($sampleTypes as $st)
                                <option value="{{ $st->id }}" {{ ($filters['analysis_type'] ?? '') == $st->id ? 'selected' : '' }}>{{ $st->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-format-list-bulleted"></i> Parameter / Analyte</label>
                        <select name="analyte_id" class="form-control-custom">
                            <option value="all">-- All Parameters --</option>
                            @foreach($analytes as $an)
                                <option value="{{ $an->id }}" {{ ($filters['analyte_id'] ?? '') == $an->id ? 'selected' : '' }}>{{ $an->name }} ({{ $an->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-account"></i> Analyst / Creator</label>
                        <select name="user_id" class="form-control-custom">
                            <option value="all">-- All Analysts --</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" {{ ($filters['user_id'] ?? '') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-home-map-marker"></i> Lab / Department</label>
                        <select name="lab_id" class="form-control-custom">
                            <option value="all">-- All Labs --</option>
                            @foreach($labs as $l)
                                <option value="{{ $l->id }}" {{ ($filters['lab_id'] ?? '') == $l->id ? 'selected' : '' }}>{{ $l->name }} ({{ $l->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-gauge"></i> Equipment / Instrument</label>
                        <select name="equipment_id" class="form-control-custom">
                            <option value="all">-- All Equipment --</option>
                            @foreach($equipments as $eq)
                                <option value="{{ $eq->id }}" {{ ($filters['equipment_id'] ?? '') == $eq->id ? 'selected' : '' }}>{{ $eq->name }} ({{ $eq->equipment_number }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-certificate"></i> Standards / CRM</label>
                        <select name="standard_id" class="form-control-custom">
                            <option value="all">-- All Standards --</option>
                            @foreach($allStandards as $st)
                                <option value="{{ $st->id }}" {{ ($filters['standard_id'] ?? '') == $st->id ? 'selected' : '' }}>{{ $st->name }} ({{ $st->code }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="submit-actions">
                    <button type="submit" class="btn-premium"><i class="mdi mdi-cogs"></i> Query & Generate Preview</button>
                </div>
            </form>
        </div>
    </div>

    <!-- TAB 5: Procurement -->
    <div id="procurement" class="tab-content-panel {{ $activeTab === 'procurement' ? 'active' : '' }}">
        <div class="report-selection-grid">
            <div class="report-card {{ $reportType === 'annual_procurement_plan' ? 'selected' : '' }}" onclick="selectReport('annual_procurement_plan', 'procurement')">
                <span class="badge">Active</span>
                <h3><i class="mdi mdi-file-chart"></i> Annual Procurement Plan</h3>
                <p>Review the master procurement requirements and planned supply cycles.</p>
            </div>
            <div class="report-card {{ $reportType === 'inspection_report' ? 'selected' : '' }}" onclick="selectReport('inspection_report', 'procurement')">
                <span class="badge">Active</span>
                <h3><i class="mdi mdi-clipboard-text"></i> Inspection Report</h3>
                <p>Track item conformity inspections, batch quantities received, and verification states.</p>
            </div>
            <div class="report-card {{ $reportType === 'grn_note' ? 'selected' : '' }}" onclick="selectReport('grn_note', 'procurement')">
                <span class="badge">Active</span>
                <h3><i class="mdi mdi-file-document-outline"></i> GRN / Goods Return Note</h3>
                <p>Audits goods received notes and chemical inventory package return statuses.</p>
            </div>
            <div class="report-card {{ $reportType === 'inventory_status' ? 'selected' : '' }}" onclick="selectReport('inventory_status', 'procurement')">
                <span class="badge">Active</span>
                <h3><i class="mdi mdi-archive"></i> Inventory Status</h3>
                <p>Check available stocks of chemicals, reference standard reagents, and containers.</p>
            </div>
            <div class="report-card {{ $reportType === 'stock_disposal' ? 'selected' : '' }}" onclick="selectReport('stock_disposal', 'procurement')">
                <span class="badge">Active</span>
                <h3><i class="mdi mdi-delete-variant"></i> Stock Disposal Report</h3>
                <p>Review discarded chemical logs, expired standard controls, and disposal methods.</p>
            </div>
        </div>

        <!-- Custom Unique Filters: Procurement -->
        <div class="filter-panel" id="filter-panel-procurement" style="display: {{ $activeTab === 'procurement' && $reportType ? 'block' : 'none' }};">
            <h4><i class="mdi mdi-tune-variant"></i> Laboratory Procurement Filter Configuration</h4>
            <form action="{{ route('module-reports.view') }}" method="POST">
                @csrf
                <input type="hidden" name="active_tab" value="procurement">
                <input type="hidden" name="report_type" class="tab-report-type-input" value="{{ $reportType }}">
                
                <div class="filter-grid">
                    <div class="form-group">
                        <label><i class="mdi mdi-calendar"></i> Date From</label>
                        <input type="date" name="date_from" class="form-control-custom" value="{{ $filters['date_from'] ?? '' }}">
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-calendar"></i> Date To</label>
                        <input type="date" name="date_to" class="form-control-custom" value="{{ $filters['date_to'] ?? '' }}">
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-certificate"></i> Standards / CRM Reagent</label>
                        <select name="standard_id" class="form-control-custom">
                            <option value="all">-- All Standards --</option>
                            @foreach($allStandards as $st)
                                <option value="{{ $st->id }}" {{ ($filters['standard_id'] ?? '') == $st->id ? 'selected' : '' }}>{{ $st->name }} ({{ $st->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-gauge"></i> Equipment / Instrument</label>
                        <select name="equipment_id" class="form-control-custom">
                            <option value="all">-- All Equipment --</option>
                            @foreach($equipments as $eq)
                                <option value="{{ $eq->id }}" {{ ($filters['equipment_id'] ?? '') == $eq->id ? 'selected' : '' }}>{{ $eq->name }} ({{ $eq->equipment_number }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-home-map-marker"></i> Target Lab / Section</label>
                        <select name="lab_id" class="form-control-custom">
                            <option value="all">-- All Labs --</option>
                            @foreach($labs as $l)
                                <option value="{{ $l->id }}" {{ ($filters['lab_id'] ?? '') == $l->id ? 'selected' : '' }}>{{ $l->name }} ({{ $l->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-progress-check"></i> Stock Status</label>
                        <select name="status" class="form-control-custom">
                            <option value="all">-- All Statuses --</option>
                            <option value="Active" {{ ($filters['status'] ?? '') == 'Active' ? 'selected' : '' }}>In Stock</option>
                            <option value="Pending" {{ ($filters['status'] ?? '') == 'Pending' ? 'selected' : '' }}>Reordered</option>
                            <option value="Dispatched" {{ ($filters['status'] ?? '') == 'Dispatched' ? 'selected' : '' }}>Disposed</option>
                            <option value="Approved" {{ ($filters['status'] ?? '') == 'Approved' ? 'selected' : '' }}>Approved</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-flask-outline"></i> Analysis Type</label>
                        <select name="analysis_type" class="form-control-custom">
                            <option value="all">-- All Analysis Types --</option>
                            @foreach($sampleTypes as $st)
                                <option value="{{ $st->id }}" {{ ($filters['analysis_type'] ?? '') == $st->id ? 'selected' : '' }}>{{ $st->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-format-list-bulleted"></i> Parameter / Analyte</label>
                        <select name="analyte_id" class="form-control-custom">
                            <option value="all">-- All Parameters --</option>
                            @foreach($analytes as $an)
                                <option value="{{ $an->id }}" {{ ($filters['analyte_id'] ?? '') == $an->id ? 'selected' : '' }}>{{ $an->name }} ({{ $an->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-account"></i> Analyst / Requester</label>
                        <select name="user_id" class="form-control-custom">
                            <option value="all">-- All Analysts --</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" {{ ($filters['user_id'] ?? '') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-account-multiple"></i> Vendor / Customer</label>
                        <select name="client_id" class="form-control-custom">
                            <option value="all">-- All Vendors --</option>
                            @foreach($clients as $c)
                                <option value="{{ $c->id }}" {{ ($filters['client_id'] ?? '') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-numeric"></i> Lab No.</label>
                        <input type="text" name="lab_no" class="form-control-custom" placeholder="e.g. LAB-2026-001" value="{{ $filters['lab_no'] ?? '' }}">
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-barcode"></i> Sample Number</label>
                        <input type="text" name="sample_number" class="form-control-custom" placeholder="e.g. SMPL-1004" value="{{ $filters['sample_number'] ?? '' }}">
                    </div>
                </div>
                <div class="submit-actions">
                    <button type="submit" class="btn-premium"><i class="mdi mdi-cogs"></i> Query & Generate Preview</button>
                </div>
            </form>
        </div>
    </div>

    <!-- TAB 6: QA & Risk -->
    <div id="qa_risk" class="tab-content-panel {{ $activeTab === 'qa_risk' ? 'active' : '' }}">
        <div class="report-selection-grid">
            <div class="report-card {{ $reportType === 'audit_report' ? 'selected' : '' }}" onclick="selectReport('audit_report', 'qa_risk')">
                <span class="badge">Active</span>
                <h3><i class="mdi mdi-certificate"></i> Audit Report</h3>
                <p>Assess laboratory accreditation status, internal quality scores, and ISO compliance levels.</p>
            </div>
            <div class="report-card {{ $reportType === 'risk_register' ? 'selected' : '' }}" onclick="selectReport('risk_register', 'qa_risk')">
                <span class="badge">Active</span>
                <h3><i class="mdi mdi-shield-alert-outline"></i> Risk Register</h3>
                <p>Track enterprise quality hazards, treatment status plans, and residual RPN reviews.</p>
            </div>
            <div class="report-card {{ $reportType === 'non_conformance' ? 'selected' : '' }}" onclick="selectReport('non_conformance', 'qa_risk')">
                <span class="badge">Active</span>
                <h3><i class="mdi mdi-alert-circle-outline"></i> NCs Report</h3>
                <p>Inspect ISO violations, deviations, root causes (RCA), and corrective actions (CAPA).</p>
            </div>
            <div class="report-card {{ $reportType === 'mgmt_review' ? 'selected' : '' }}" onclick="selectReport('mgmt_review', 'qa_risk')">
                <span class="badge">Active</span>
                <h3><i class="mdi mdi-account-group-outline"></i> Management Reviews</h3>
                <p>dakika za vikao vya mapitio ya menejimenti (Minutes) and Quality system actions.</p>
            </div>
            <div class="report-card {{ $reportType === 'complaints_report' ? 'selected' : '' }}" onclick="selectReport('complaints_report', 'qa_risk')">
                <span class="badge">Active</span>
                <h3><i class="mdi mdi-message-alert-outline"></i> Complaints Report</h3>
                <p>Review customer feedback, quality complaints logged, and resolution histories.</p>
            </div>
        </div>

        <!-- Custom Unique Filters: QA & Risk -->
        <div class="filter-panel" id="filter-panel-qa_risk" style="display: {{ $activeTab === 'qa_risk' && $reportType ? 'block' : 'none' }};">
            <h4><i class="mdi mdi-tune-variant"></i> QA & Risk Filter Configuration</h4>
            <form action="{{ route('module-reports.view') }}" method="POST">
                @csrf
                <input type="hidden" name="active_tab" value="qa_risk">
                <input type="hidden" name="report_type" class="tab-report-type-input" value="{{ $reportType }}">
                
                <div class="filter-grid">
                    <div class="form-group">
                        <label><i class="mdi mdi-calendar"></i> Date From</label>
                        <input type="date" name="date_from" class="form-control-custom" value="{{ $filters['date_from'] ?? '' }}">
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-calendar"></i> Date To</label>
                        <input type="date" name="date_to" class="form-control-custom" value="{{ $filters['date_to'] ?? '' }}">
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-progress-check"></i> Quality / Risk Status</label>
                        <select name="status" class="form-control-custom">
                            <option value="all">-- All Statuses --</option>
                            <option value="Active" {{ ($filters['status'] ?? '') == 'Active' ? 'selected' : '' }}>Active / Open</option>
                            <option value="Pending" {{ ($filters['status'] ?? '') == 'Pending' ? 'selected' : '' }}>Pending CAPA</option>
                            <option value="Dispatched" {{ ($filters['status'] ?? '') == 'Dispatched' ? 'selected' : '' }}>Dispatched / Resolved</option>
                            <option value="Approved" {{ ($filters['status'] ?? '') == 'Approved' ? 'selected' : '' }}>Quality Approved</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-account"></i> Quality Officer / Analyst</label>
                        <select name="user_id" class="form-control-custom">
                            <option value="all">-- All Analysts --</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" {{ ($filters['user_id'] ?? '') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-home-map-marker"></i> Lab Department</label>
                        <select name="lab_id" class="form-control-custom">
                            <option value="all">-- All Labs --</option>
                            @foreach($labs as $l)
                                <option value="{{ $l->id }}" {{ ($filters['lab_id'] ?? '') == $l->id ? 'selected' : '' }}>{{ $l->name }} ({{ $l->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-account-multiple"></i> customer / complainant</label>
                        <select name="client_id" class="form-control-custom">
                            <option value="all">-- All Customers --</option>
                            @foreach($clients as $c)
                                <option value="{{ $c->id }}" {{ ($filters['client_id'] ?? '') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-flask-outline"></i> Analysis Type</label>
                        <select name="analysis_type" class="form-control-custom">
                            <option value="all">-- All Analysis Types --</option>
                            @foreach($sampleTypes as $st)
                                <option value="{{ $st->id }}" {{ ($filters['analysis_type'] ?? '') == $st->id ? 'selected' : '' }}>{{ $st->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-format-list-bulleted"></i> Parameter / Analyte</label>
                        <select name="analyte_id" class="form-control-custom">
                            <option value="all">-- All Parameters --</option>
                            @foreach($analytes as $an)
                                <option value="{{ $an->id }}" {{ ($filters['analyte_id'] ?? '') == $an->id ? 'selected' : '' }}>{{ $an->name }} ({{ $an->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-numeric"></i> Lab No.</label>
                        <input type="text" name="lab_no" class="form-control-custom" placeholder="e.g. LAB-2026-001" value="{{ $filters['lab_no'] ?? '' }}">
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-barcode"></i> Sample Number</label>
                        <input type="text" name="sample_number" class="form-control-custom" placeholder="e.g. SMPL-1004" value="{{ $filters['sample_number'] ?? '' }}">
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-gauge"></i> Equipment / Instrument</label>
                        <select name="equipment_id" class="form-control-custom">
                            <option value="all">-- All Equipment --</option>
                            @foreach($equipments as $eq)
                                <option value="{{ $eq->id }}" {{ ($filters['equipment_id'] ?? '') == $eq->id ? 'selected' : '' }}>{{ $eq->name }} ({{ $eq->equipment_number }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-certificate"></i> Standards / CRM</label>
                        <select name="standard_id" class="form-control-custom">
                            <option value="all">-- All Standards --</option>
                            @foreach($allStandards as $st)
                                <option value="{{ $st->id }}" {{ ($filters['standard_id'] ?? '') == $st->id ? 'selected' : '' }}>{{ $st->name }} ({{ $st->code }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="submit-actions">
                    <button type="submit" class="btn-premium"><i class="mdi mdi-cogs"></i> Query & Generate Preview</button>
                </div>
            </form>
        </div>
    </div>

    <!-- TAB 7: Internal Audit -->
    <div id="internal_audit" class="tab-content-panel {{ $activeTab === 'internal_audit' ? 'active' : '' }}">
        <div class="report-selection-grid">
            <div class="report-card {{ $reportType === 'exception_report' ? 'selected' : '' }}" onclick="selectReport('exception_report', 'internal_audit')">
                <span class="badge">Active</span>
                <h3><i class="mdi mdi-alert-octagon-outline"></i> Exception Report</h3>
                <p>Audits system exception reports, automated out-of-bounds alerts, and incidents.</p>
            </div>
            <div class="report-card {{ $reportType === 'system_audit' ? 'selected' : '' }}" onclick="selectReport('system_audit', 'internal_audit')">
                <span class="badge">Active</span>
                <h3><i class="mdi mdi-security"></i> System Audit Log</h3>
                <p>Analyze user action records, changes, metadata updates, and security logs.</p>
            </div>
            <div class="report-card {{ $reportType === 'sample_audit' ? 'selected' : '' }}" onclick="selectReport('sample_audit', 'internal_audit')">
                <span class="badge">Active</span>
                <h3><i class="mdi mdi-barcode-scan"></i> Sample Audit</h3>
                <p>Audit sample history records, processing steps, and data entry validations.</p>
            </div>
            <div class="report-card {{ $reportType === 'reagent_audit' ? 'selected' : '' }}" onclick="selectReport('reagent_audit', 'internal_audit')">
                <span class="badge">Active</span>
                <h3><i class="mdi mdi-flask-round-bottom-outline"></i> Reagent Audit</h3>
                <p>Audit stock volumes of chemical reagents, lot checks, and physical discrepancy logs.</p>
            </div>
        </div>

        <!-- Custom Unique Filters: Internal Audit -->
        <div class="filter-panel" id="filter-panel-internal_audit" style="display: {{ $activeTab === 'internal_audit' && $reportType ? 'block' : 'none' }};">
            <h4><i class="mdi mdi-tune-variant"></i> Internal Audit Filter Configuration</h4>
            <form action="{{ route('module-reports.view') }}" method="POST">
                @csrf
                <input type="hidden" name="active_tab" value="internal_audit">
                <input type="hidden" name="report_type" class="tab-report-type-input" value="{{ $reportType }}">
                
                <div class="filter-grid">
                    <div class="form-group">
                        <label><i class="mdi mdi-calendar"></i> Date From</label>
                        <input type="date" name="date_from" class="form-control-custom" value="{{ $filters['date_from'] ?? '' }}">
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-calendar"></i> Date To</label>
                        <input type="date" name="date_to" class="form-control-custom" value="{{ $filters['date_to'] ?? '' }}">
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-account"></i> Target User / Operator</label>
                        <select name="user_id" class="form-control-custom">
                            <option value="all">-- All Users --</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" {{ ($filters['user_id'] ?? '') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-numeric"></i> Lab No.</label>
                        <input type="text" name="lab_no" class="form-control-custom" placeholder="e.g. LAB-2026-001" value="{{ $filters['lab_no'] ?? '' }}">
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-barcode"></i> Sample Number</label>
                        <input type="text" name="sample_number" class="form-control-custom" placeholder="e.g. SMPL-1004" value="{{ $filters['sample_number'] ?? '' }}">
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-progress-check"></i> Audit Status</label>
                        <select name="status" class="form-control-custom">
                            <option value="all">-- All Statuses --</option>
                            <option value="Active" {{ ($filters['status'] ?? '') == 'Active' ? 'selected' : '' }}>Active / Verified</option>
                            <option value="Pending" {{ ($filters['status'] ?? '') == 'Pending' ? 'selected' : '' }}>Pending / Flagged</option>
                            <option value="Dispatched" {{ ($filters['status'] ?? '') == 'Dispatched' ? 'selected' : '' }}>Dispatched</option>
                            <option value="Validated" {{ ($filters['status'] ?? '') == 'Validated' ? 'selected' : '' }}>Validated</option>
                            <option value="Approved" {{ ($filters['status'] ?? '') == 'Approved' ? 'selected' : '' }}>Approved</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-flask-outline"></i> Analysis Type</label>
                        <select name="analysis_type" class="form-control-custom">
                            <option value="all">-- All Analysis Types --</option>
                            @foreach($sampleTypes as $st)
                                <option value="{{ $st->id }}" {{ ($filters['analysis_type'] ?? '') == $st->id ? 'selected' : '' }}>{{ $st->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-format-list-bulleted"></i> Parameter / Analyte</label>
                        <select name="analyte_id" class="form-control-custom">
                            <option value="all">-- All Parameters --</option>
                            @foreach($analytes as $an)
                                <option value="{{ $an->id }}" {{ ($filters['analyte_id'] ?? '') == $an->id ? 'selected' : '' }}>{{ $an->name }} ({{ $an->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-home-map-marker"></i> Audited Lab</label>
                        <select name="lab_id" class="form-control-custom">
                            <option value="all">-- All Labs --</option>
                            @foreach($labs as $l)
                                <option value="{{ $l->id }}" {{ ($filters['lab_id'] ?? '') == $l->id ? 'selected' : '' }}>{{ $l->name }} ({{ $l->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-gauge"></i> Equipment / Instrument</label>
                        <select name="equipment_id" class="form-control-custom">
                            <option value="all">-- All Equipment --</option>
                            @foreach($equipments as $eq)
                                <option value="{{ $eq->id }}" {{ ($filters['equipment_id'] ?? '') == $eq->id ? 'selected' : '' }}>{{ $eq->name }} ({{ $eq->equipment_number }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-certificate"></i> Standards / CRM</label>
                        <select name="standard_id" class="form-control-custom">
                            <option value="all">-- All Standards --</option>
                            @foreach($allStandards as $st)
                                <option value="{{ $st->id }}" {{ ($filters['standard_id'] ?? '') == $st->id ? 'selected' : '' }}>{{ $st->name }} ({{ $st->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-account-multiple"></i> Customer / Client</label>
                        <select name="client_id" class="form-control-custom">
                            <option value="all">-- All Customers --</option>
                            @foreach($clients as $c)
                                <option value="{{ $c->id }}" {{ ($filters['client_id'] ?? '') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="submit-actions">
                    <button type="submit" class="btn-premium"><i class="mdi mdi-cogs"></i> Query & Generate Preview</button>
                </div>
            </form>
        </div>
    </div>

    <!-- TAB 8: Management -->
    <div id="management" class="tab-content-panel {{ $activeTab === 'management' ? 'active' : '' }}">
        <div class="report-selection-grid">
            <div class="report-card {{ $reportType === 'ceo_performance' ? 'selected' : '' }}" onclick="selectReport('ceo_performance', 'management')">
                <span class="badge">Active</span>
                <h3><i class="mdi mdi-account-star-outline"></i> CEO & Board Performance</h3>
                <p>Overview of organizational revenue plans, overall sample TAT, and compliance metrics.</p>
            </div>
            <div class="report-card {{ $reportType === 'equipment_breakdown' ? 'selected' : '' }}" onclick="selectReport('equipment_breakdown', 'management')">
                <span class="badge">Active</span>
                <h3><i class="mdi mdi-alert-octagram-outline"></i> Equipment Breakdown</h3>
                <p>Summary of laboratory equipment breakdowns, repair costs, and total downtime hours.</p>
            </div>
            <div class="report-card {{ $reportType === 'clients_served' ? 'selected' : '' }}" onclick="selectReport('clients_served', 'management')">
                <span class="badge">Active</span>
                <h3><i class="mdi mdi-account-multiple-outline"></i> Clients Served Report</h3>
                <p>Demographic summary of clients served (Corporate, Individuals, and Government bodies).</p>
            </div>
        </div>

        <!-- Custom Unique Filters: Management -->
        <div class="filter-panel" id="filter-panel-management" style="display: {{ $activeTab === 'management' && $reportType ? 'block' : 'none' }};">
            <h4><i class="mdi mdi-tune-variant"></i> Executive Management Filter Configuration</h4>
            <form action="{{ route('module-reports.view') }}" method="POST">
                @csrf
                <input type="hidden" name="active_tab" value="management">
                <input type="hidden" name="report_type" class="tab-report-type-input" value="{{ $reportType }}">
                
                <div class="filter-grid">
                    <div class="form-group">
                        <label><i class="mdi mdi-calendar"></i> Date From</label>
                        <input type="date" name="date_from" class="form-control-custom" value="{{ $filters['date_from'] ?? '' }}">
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-calendar"></i> Date To</label>
                        <input type="date" name="date_to" class="form-control-custom" value="{{ $filters['date_to'] ?? '' }}">
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-home-map-marker"></i> Lab / Executive Section</label>
                        <select name="lab_id" class="form-control-custom">
                            <option value="all">-- All Labs --</option>
                            @foreach($labs as $l)
                                <option value="{{ $l->id }}" {{ ($filters['lab_id'] ?? '') == $l->id ? 'selected' : '' }}>{{ $l->name }} ({{ $l->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-account-multiple"></i> Key Customer</label>
                        <select name="client_id" class="form-control-custom">
                            <option value="all">-- All Customers --</option>
                            @foreach($clients as $c)
                                <option value="{{ $c->id }}" {{ ($filters['client_id'] ?? '') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-flask-outline"></i> Analysis Type</label>
                        <select name="analysis_type" class="form-control-custom">
                            <option value="all">-- All Analysis Types --</option>
                            @foreach($sampleTypes as $st)
                                <option value="{{ $st->id }}" {{ ($filters['analysis_type'] ?? '') == $st->id ? 'selected' : '' }}>{{ $st->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-progress-check"></i> Overall Status</label>
                        <select name="status" class="form-control-custom">
                            <option value="all">-- All Statuses --</option>
                            <option value="Active" {{ ($filters['status'] ?? '') == 'Active' ? 'selected' : '' }}>Active / Running</option>
                            <option value="Pending" {{ ($filters['status'] ?? '') == 'Pending' ? 'selected' : '' }}>Pending / Delayed</option>
                            <option value="Dispatched" {{ ($filters['status'] ?? '') == 'Dispatched' ? 'selected' : '' }}>Completed / Dispatched</option>
                            <option value="Approved" {{ ($filters['status'] ?? '') == 'Approved' ? 'selected' : '' }}>Approved</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-format-list-bulleted"></i> Parameter / Analyte</label>
                        <select name="analyte_id" class="form-control-custom">
                            <option value="all">-- All Parameters --</option>
                            @foreach($analytes as $an)
                                <option value="{{ $an->id }}" {{ ($filters['analyte_id'] ?? '') == $an->id ? 'selected' : '' }}>{{ $an->name }} ({{ $an->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-account"></i> Analyst / Lead</label>
                        <select name="user_id" class="form-control-custom">
                            <option value="all">-- All Analysts --</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" {{ ($filters['user_id'] ?? '') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-numeric"></i> Lab No.</label>
                        <input type="text" name="lab_no" class="form-control-custom" placeholder="e.g. LAB-2026-001" value="{{ $filters['lab_no'] ?? '' }}">
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-barcode"></i> Sample Number</label>
                        <input type="text" name="sample_number" class="form-control-custom" placeholder="e.g. SMPL-1004" value="{{ $filters['sample_number'] ?? '' }}">
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-gauge"></i> Equipment / Instrument</label>
                        <select name="equipment_id" class="form-control-custom">
                            <option value="all">-- All Equipment --</option>
                            @foreach($equipments as $eq)
                                <option value="{{ $eq->id }}" {{ ($filters['equipment_id'] ?? '') == $eq->id ? 'selected' : '' }}>{{ $eq->name }} ({{ $eq->equipment_number }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="mdi mdi-certificate"></i> Standards / CRM</label>
                        <select name="standard_id" class="form-control-custom">
                            <option value="all">-- All Standards --</option>
                            @foreach($allStandards as $st)
                                <option value="{{ $st->id }}" {{ ($filters['standard_id'] ?? '') == $st->id ? 'selected' : '' }}>{{ $st->name }} ({{ $st->code }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="submit-actions">
                    <button type="submit" class="btn-premium"><i class="mdi mdi-cogs"></i> Query & Generate Preview</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Report Preview Area -->
    <div class="preview-section" id="report-preview-section">
        <div class="preview-header">
            <h2><i class="mdi mdi-eye-outline"></i> Report Preview Grid</h2>
            
            <div class="actions" id="results-actions-wrapper" style="display: {{ $results && $results->count() > 0 ? 'block' : 'none' }};">
                @if($results && $results->count() > 0)
                    <a href="{{ route('module-reports.print', array_merge($filters, ['report_type' => $reportType, 'format' => 'print'])) }}" target="_blank" class="btn-outline-custom">
                        <i class="mdi mdi-printer"></i> Print Version
                    </a>
                    <a href="{{ route('module-reports.print', array_merge($filters, ['report_type' => $reportType, 'format' => 'pdf'])) }}" target="_blank" class="btn-premium" style="margin-left: 8px;">
                        <i class="mdi mdi-file-pdf-box"></i> Download PDF
                    </a>
                @endif
            </div>
        </div>

        <!-- The actual data table container -->
        <div class="table-container" id="results-table-wrapper" style="display: {{ $results && $results->count() > 0 ? 'block' : 'none' }};">
            @if($results && $results->count() > 0)
                <table class="table-custom">
                    <thead>
                        <tr>
                            @if($reportType === 'sample_register')
                                <th>Receipt Date</th>
                                <th>Batch Code</th>
                                <th>Client Name</th>
                                <th>Sample Type</th>
                                <th>Priority</th>
                                <th>Workflow Stage</th>
                            @elseif($reportType === 'chain_of_custody')
                                <th>Date/Time</th>
                                <th>Batch ID</th>
                                <th>Moved In By</th>
                                <th>Moved Out By</th>
                                <th>Tracking Stage</th>
                                <th>Comments</th>
                            @elseif($reportType === 'rejection')
                                <th>Rejection Date</th>
                                <th>Batch Reference</th>
                                <th>Client Name</th>
                                <th>Laboratory Staff</th>
                                <th>Rejection Reason</th>
                            @elseif($reportType === 'sample_return')
                                <th>Sample Code</th>
                                <th>Exhibit Name</th>
                                <th>Returned To</th>
                                <th>Returned Date</th>
                                <th>Officer Name</th>
                                <th>Authorized By</th>
                            @elseif($reportType === 'retained_samples')
                                <th>Sample Code</th>
                                <th>Batch Code</th>
                                <th>Retained Date</th>
                                <th>Retention Period</th>
                                <th>Shelf Location</th>
                                <th>Responsible Officer</th>
                            @elseif($reportType === 'disposal')
                                <th>Disposal Date</th>
                                <th>Sample Code</th>
                                <th>Batch Reference</th>
                                <th>Lab Name</th>
                            @elseif($reportType === 'resampling')
                                <th>Batch Code</th>
                                <th>Original Code</th>
                                <th>Resampled Date</th>
                                <th>Reason for Resampling</th>
                                <th>Authorized By</th>
                            @elseif($reportType === 'amendment')
                                <th>Date</th>
                                <th>Batch Code</th>
                                <th>Staff Member</th>
                                <th>Amendment No</th>
                                <th>Reason</th>
                            @elseif($reportType === 'workbook')
                                <th>Captured Date</th>
                                <th>Sample Code</th>
                                <th>Analyte</th>
                                <th>Result</th>
                                <th>Operator</th>
                            @elseif($reportType === 'coa_report')
                                <th>COA Reference</th>
                                <th>Batch Code</th>
                                <th>Customer Name</th>
                                <th>Released Date</th>
                                <th>Status</th>
                                <th>Approved By</th>
                            @elseif($reportType === 'method_validation')
                                <th>Method Code</th>
                                <th>Method Title</th>
                                <th>Validation Date</th>
                                <th>Parameters Checked</th>
                                <th>Approved Status</th>
                            @elseif($reportType === 'proficiency_testing')
                                <th>PT Scheme Name</th>
                                <th>Analyte Target</th>
                                <th>Performance Score</th>
                                <th>Evaluation Status</th>
                                <th>Date Run</th>
                                <th>Quality Specialist</th>
                            @elseif($reportType === 'instrument_log')
                                <th>Instrument Name</th>
                                <th>Date Checked</th>
                                <th>Operator Name</th>
                                <th>Hours Utilised</th>
                                <th>Log Comments</th>
                            @elseif($reportType === 'intermediate_check')
                                <th>Equipment Name</th>
                                <th>Check Date</th>
                                <th>Reference Standard</th>
                                <th>Deviation Value</th>
                                <th>Status</th>
                            @elseif($reportType === 'instrument_calibration')
                                <th>Instrument Name</th>
                                <th>Calibration Date</th>
                                <th>Next Due Date</th>
                                <th>Calibrated By</th>
                                <th>Status</th>
                                <th>Deviation Notes</th>
                            @elseif($reportType === 'preventive_maintenance')
                                <th>Equipment Name</th>
                                <th>Maintenance Date</th>
                                <th>Contractor Name</th>
                                <th>Actions Taken</th>
                                <th>Status</th>
                            @elseif($reportType === 'environmental_monitoring')
                                <th>Timestamp Logged</th>
                                <th>Temperature (°C)</th>
                                <th>Relative Humidity (% RH)</th>
                                <th>Recorded By</th>
                                <th>Conformity Status</th>
                            @elseif($reportType === 'decontamination_register')
                                <th>Area/Room</th>
                                <th>Decontaminated Date</th>
                                <th>Chemical Used</th>
                                <th>Staff Officer</th>
                                <th>Status</th>
                            @elseif($reportType === 'standards')
                                <th>Name</th>
                                <th>Standard Code</th>
                                <th>Batch/Lot</th>
                                <th>Expiry Date</th>
                                <th>Status</th>
                            @elseif($reportType === 'tat')
                                <th>Receipt Date</th>
                                <th>Sample Code</th>
                                <th>Analyst</th>
                                <th>Sample Type</th>
                            @elseif($reportType === 'backlog_analysis')
                                <th>Lab Section</th>
                                <th>Backlog Count</th>
                                <th>Oldest Pending Sample</th>
                                <th>Target SLA</th>
                                <th>Risk Level</th>
                            @elseif($reportType === 'performance_report')
                                <th>Lab Section</th>
                                <th>Samples Completed</th>
                                <th>Target Compliance</th>
                                <th>Analyst count</th>
                                <th>Rating</th>
                            @elseif($reportType === 'trend_analysis')
                                <th>Matrix Type</th>
                                <th>Analyte Code</th>
                                <th>Min Value</th>
                                <th>Max Value</th>
                                <th>Trend Direction</th>
                            @elseif($reportType === 'interzone_transfers')
                                <th>Batch Code</th>
                                <th>Origin Lab Location</th>
                                <th>Destination Location</th>
                                <th>Courier Details</th>
                                <th>Dispatch Timestamp</th>
                                <th>Transfer Status</th>
                            @elseif($reportType === 'zonal_performance')
                                <th>Regional Zone Name</th>
                                <th>Target SLA</th>
                                <th>SLA Met</th>
                                <th>Volume Handled</th>
                                <th>Overall Zonal Rating</th>
                            @elseif($reportType === 'proforma_invoice')
                                <th>Proforma Ref</th>
                                <th>Client Name</th>
                                <th>Sample Count</th>
                                <th>Total Fee TZS</th>
                                <th>Status</th>
                            @elseif($reportType === 'payment_receipt')
                                <th>Receipt Ref</th>
                                <th>Invoice Ref</th>
                                <th>Client Name</th>
                                <th>Amount Paid TZS</th>
                                <th>Payment Date</th>
                            @elseif($reportType === 'aging_receivables')
                                <th>Client Name</th>
                                <th>Current Balance</th>
                                <th>30 - 60 Days overdue</th>
                                <th>61 - 90 Days overdue</th>
                                <th>Over 90 Days</th>
                                <th>Total Outstanding</th>
                            @elseif($reportType === 'revenue_summary')
                                <th>Departmental Lab Section</th>
                                <th>Quarterly Revenue Generated</th>
                                <th>Target Achievement Rate</th>
                                <th>Invoiced Batches Count</th>
                                <th>Fiscal Status</th>
                            @elseif($reportType === 'reconciliation_report')
                                <th>Month Period</th>
                                <th>Expected TZS</th>
                                <th>Collected TZS</th>
                                <th>Discrepancy TZS</th>
                                <th>Status</th>
                            @elseif($reportType === 'annual_procurement_plan')
                                <th>Chemical / Reagent Name</th>
                                <th>Target Fiscal Year</th>
                                <th>Quarterly Planned Quantity</th>
                                <th>Estimated Unit Cost</th>
                                <th>Allocated Budget TZS</th>
                                <th>Status</th>
                            @elseif($reportType === 'inspection_report')
                                <th>Delivery Ref</th>
                                <th>Supplier Name</th>
                                <th>Inspection Date</th>
                                <th>Conformity Status</th>
                                <th>Inspected By</th>
                            @elseif($reportType === 'grn_note')
                                <th>GRN Number</th>
                                <th>Delivery Date</th>
                                <th>Supplier Name</th>
                                <th>Items Accepted</th>
                                <th>Items Returned</th>
                            @elseif($reportType === 'inventory_status')
                                <th>Reagent Name</th>
                                <th>Current Qty</th>
                                <th>Unit Measure</th>
                                <th>Storage Temp</th>
                                <th>Safety Rating</th>
                            @elseif($reportType === 'stock_disposal')
                                <th>Reagent Name</th>
                                <th>Batch Lot</th>
                                <th>Disposed Date</th>
                                <th>Disposal Method</th>
                                <th>Officer</th>
                            @elseif($reportType === 'audit_report')
                                <th>Audit Reference</th>
                                <th>Audited Section</th>
                                <th>Date Conducted</th>
                                <th>NCs Found</th>
                                <th>Lead Auditor</th>
                            @elseif($reportType === 'risk_register')
                                <th>Risk Number</th>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Risk Level</th>
                                <th>Status</th>
                            @elseif($reportType === 'non_conformance')
                                <th>NC Number</th>
                                <th>Title</th>
                                <th>Date Identified</th>
                                <th>Identified By</th>
                                <th>Status</th>
                            @elseif($reportType === 'mgmt_review')
                                <th>Meeting Date</th>
                                <th>Attendees</th>
                                <th>Agenda Summary</th>
                                <th>Actions Count</th>
                                <th>Chairperson</th>
                            @elseif($reportType === 'complaints_report')
                                <th>Complaint Code</th>
                                <th>Client Name</th>
                                <th>Logged Date</th>
                                <th>Feedback Type</th>
                                <th>Status</th>
                            @elseif($reportType === 'exception_report')
                                <th>Exception Code</th>
                                <th>Severity</th>
                                <th>Incident Date</th>
                                <th>Description</th>
                                <th>Investigated By</th>
                            @elseif($reportType === 'system_audit')
                                <th>Timestamp</th>
                                <th>User</th>
                                <th>Event</th>
                                <th>IP Address</th>
                            @elseif($reportType === 'sample_audit')
                                <th>Sample Code</th>
                                <th>Audit Date</th>
                                <th>Discrepancies</th>
                                <th>Verified By</th>
                                <th>Status</th>
                            @elseif($reportType === 'reagent_audit')
                                <th>Reagent Name</th>
                                <th>Lot Number</th>
                                <th>Actual Stock</th>
                                <th>System Stock</th>
                                <th>Discrepancy</th>
                            @elseif($reportType === 'ceo_performance')
                                <th>Period</th>
                                <th>Total Revenue TZS</th>
                                <th>Overall TAT (Days)</th>
                                <th>Customer Satisfaction</th>
                                <th>Performance Rating</th>
                            @elseif($reportType === 'equipment_breakdown')
                                <th>Instrument Name</th>
                                <th>Breakdown Date</th>
                                <th>Repair Completion</th>
                                <th>Downtime Hours</th>
                                <th>Repair Cost TZS</th>
                            @elseif($reportType === 'clients_served')
                                <th>Month</th>
                                <th>Corporate Clients</th>
                                <th>Individual Clients</th>
                                <th>Government Bodies</th>
                                <th>Total Served</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($results as $row)
                            <tr>
                                @if($reportType === 'sample_register')
                                    <td>{{ $row->receipt_date ?? optional($row->created_at)->format('Y-m-d') }}</td>
                                    <td><strong>{{ $row->batch_code }}</strong></td>
                                    <td>{{ optional($row->client)->name ?? 'Walk-in Customer' }}</td>
                                    <td>{{ optional($row->sample_type)->name ?? 'N/A' }}</td>
                                    <td>
                                        <span class="status-pill {{ strtolower($row->priority) === 'high' ? 'danger' : 'info' }}">
                                            {{ $row->priority ?? 'Medium' }}
                                        </span>
                                    </td>
                                    <td>{{ $row->workflow_stage ?? 'Staging' }}</td>
                                @elseif($reportType === 'chain_of_custody')
                                    <td>{{ optional($row->created_at)->format('Y-m-d H:i') }}</td>
                                    <td><strong>{{ optional($row->sampleHeader)->batch_code ?? 'N/A' }}</strong></td>
                                    <td>{{ optional($row->started_by)->name ?? 'N/A' }}</td>
                                    <td>{{ optional($row->completed_by)->name ?? 'Pending' }}</td>
                                    <td>{{ optional($row->tracking_stage)->name ?? 'N/A' }}</td>
                                    <td>{{ $row->comments ?? 'N/A' }}</td>
                                @elseif($reportType === 'rejection')
                                    <td>{{ optional($row->submitted_at)->format('Y-m-d') }}</td>
                                    <td><strong>{{ $row->batch_code ?? $row->request_reference }}</strong></td>
                                    <td>{{ $row->payload['name_of_client'] ?? 'N/A' }}</td>
                                    <td>{{ $row->payload['laboratory_staff'] ?? 'N/A' }}</td>
                                    <td>
                                        @if(isset($row->payload['reasons']))
                                            {{ implode(', ', (array)$row->payload['reasons']) }}
                                        @else
                                            {{ $row->payload['explanation'] ?? 'N/A' }}
                                        @endif
                                    </td>
                                @elseif($reportType === 'sample_return')
                                    <td><strong>{{ $row->sample_code }}</strong></td>
                                    <td>{{ $row->exhibit_name }}</td>
                                    <td>{{ $row->returned_to }}</td>
                                    <td>{{ $row->returned_date }}</td>
                                    <td>{{ $row->officer_name }}</td>
                                    <td>{{ $row->authorized_by }}</td>
                                @elseif($reportType === 'retained_samples')
                                    <td><strong>{{ $row->sample_code }}</strong></td>
                                    <td>{{ $row->batch_code }}</td>
                                    <td>{{ $row->retained_date }}</td>
                                    <td>{{ $row->retention_period }}</td>
                                    <td>{{ $row->shelf_location }}</td>
                                    <td>{{ $row->officer_name }}</td>
                                @elseif($reportType === 'disposal')
                                    <td>{{ $row->disposal_date }}</td>
                                    <td><strong>{{ $row->sample_code }}</strong></td>
                                    <td>{{ optional($row->getSampleHeader())->batch_code ?? 'N/A' }}</td>
                                    <td>{{ optional($row->lab)->name ?? 'N/A' }}</td>
                                @elseif($reportType === 'resampling')
                                    <td><strong>{{ $row->batch_code }}</strong></td>
                                    <td>{{ $row->original_code }}</td>
                                    <td>{{ $row->resampled_date }}</td>
                                    <td>{{ $row->reason }}</td>
                                    <td>{{ $row->officer_name }}</td>
                                @elseif($reportType === 'amendment')
                                    <td>{{ optional($row->created_at)->format('Y-m-d') }}</td>
                                    <td><strong>{{ optional($row->sampleHeader)->batch_code ?? 'N/A' }}</strong></td>
                                    <td>{{ $row->creator }}</td>
                                    <td>{{ $row->ammendment_no ?? '1' }}</td>
                                    <td>{{ $row->reason }}</td>
                                @elseif($reportType === 'workbook')
                                    <td>{{ optional($row->created_at)->format('Y-m-d H:i') }}</td>
                                    <td><strong>{{ $row->sample_detail_code }}</strong></td>
                                    <td>{{ $row->analyte_code }}</td>
                                    <td>{{ $row->result }}</td>
                                    <td>{{ optional($row->operator)->name ?? 'N/A' }}</td>
                                @elseif($reportType === 'coa_report')
                                    <td><strong>{{ $row->coa_reference }}</strong></td>
                                    <td>{{ $row->batch_code }}</td>
                                    <td>{{ $row->customer_name }}</td>
                                    <td>{{ $row->released_date }}</td>
                                    <td><span class="status-pill {{ $row->status === 'Finalized' ? 'success' : 'warning' }}">{{ $row->status }}</span></td>
                                    <td>{{ $row->approved_by }}</td>
                                @elseif($reportType === 'method_validation')
                                    <td><strong>{{ $row->method_code }}</strong></td>
                                    <td>{{ $row->method_title }}</td>
                                    <td>{{ $row->validation_date }}</td>
                                    <td>{{ $row->parameters_checked }}</td>
                                    <td><span class="status-pill success">{{ $row->approved_status }}</span></td>
                                @elseif($reportType === 'proficiency_testing')
                                    <td><strong>{{ $row->pt_scheme }}</strong></td>
                                    <td>{{ $row->analyte }}</td>
                                    <td>{{ $row->score }}</td>
                                    <td><span class="status-pill success">{{ $row->status }}</span></td>
                                    <td>{{ $row->date }}</td>
                                    <td>{{ $row->analyst }}</td>
                                @elseif($reportType === 'instrument_log')
                                    <td><strong>{{ $row->instrument_name }}</strong></td>
                                    <td>{{ $row->date_checked }}</td>
                                    <td>{{ $row->operator_name }}</td>
                                    <td>{{ $row->hours_utilised }}</td>
                                    <td>{{ $row->log_comments }}</td>
                                @elseif($reportType === 'intermediate_check')
                                    <td><strong>{{ $row->equipment_name }}</strong></td>
                                    <td>{{ $row->check_date }}</td>
                                    <td>{{ $row->reference_standard }}</td>
                                    <td>{{ $row->deviation_value }}</td>
                                    <td><span class="status-pill success">{{ $row->status }}</span></td>
                                @elseif($reportType === 'instrument_calibration')
                                    <td><strong>{{ $row->instrument_name }}</strong></td>
                                    <td>{{ $row->calibration_date }}</td>
                                    <td>{{ $row->due_date }}</td>
                                    <td>{{ $row->calibrated_by }}</td>
                                    <td><span class="status-pill success">{{ $row->status }}</span></td>
                                    <td>{{ $row->deviation }}</td>
                                @elseif($reportType === 'preventive_maintenance')
                                    <td><strong>{{ $row->equipment_name }}</strong></td>
                                    <td>{{ $row->maintenance_date }}</td>
                                    <td>{{ $row->contractor_name }}</td>
                                    <td>{{ $row->actions_taken }}</td>
                                    <td><span class="status-pill success">{{ $row->status }}</span></td>
                                @elseif($reportType === 'environmental_monitoring')
                                    <td>{{ $row->timestamp }}</td>
                                    <td>{{ $row->temperature }}</td>
                                    <td>{{ $row->humidity }}</td>
                                    <td>{{ $row->recorded_by }}</td>
                                    <td><span class="status-pill success">{{ $row->status }}</span></td>
                                @elseif($reportType === 'decontamination_register')
                                    <td><strong>{{ $row->area_room }}</strong></td>
                                    <td>{{ $row->decontaminated_date }}</td>
                                    <td>{{ $row->chemical_used }}</td>
                                    <td>{{ $row->staff_officer }}</td>
                                    <td><span class="status-pill success">{{ $row->status }}</span></td>
                                @elseif($reportType === 'standards')
                                    <td>{{ $row->name }}</td>
                                    <td><strong>{{ $row->standard_code }}</strong></td>
                                    <td>{{ $row->batch_number }}</td>
                                    <td>{{ $row->expiry_date }}</td>
                                    <td>
                                        <span class="status-pill {{ $row->active ? 'success' : 'danger' }}">
                                            {{ $row->active ? 'Active' : 'Expired/Inactive' }}
                                        </span>
                                    </td>
                                @elseif($reportType === 'tat')
                                    <td>{{ $row->receipt_date }}</td>
                                    <td><strong>{{ $row->sample_code }}</strong></td>
                                    <td>{{ $row->analyst_name }}</td>
                                    <td>{{ $row->sample_type_name }}</td>
                                @elseif($reportType === 'backlog_analysis')
                                    <td><strong>{{ $row->lab_section }}</strong></td>
                                    <td>{{ $row->backlog_count }}</td>
                                    <td>{{ $row->oldest_pending }}</td>
                                    <td>{{ $row->target_sla }}</td>
                                    <td><span class="status-pill {{ $row->risk_level === 'High' ? 'danger' : 'success' }}">{{ $row->risk_level }}</span></td>
                                @elseif($reportType === 'performance_report')
                                    <td><strong>{{ $row->lab_section }}</strong></td>
                                    <td>{{ $row->samples_completed }}</td>
                                    <td>{{ $row->target_compliance }}</td>
                                    <td>{{ $row->analyst_count }}</td>
                                    <td><span class="status-pill success">{{ $row->rating }}</span></td>
                                @elseif($reportType === 'trend_analysis')
                                    <td><strong>{{ $row->matrix_type }}</strong></td>
                                    <td>{{ $row->analyte_code }}</td>
                                    <td>{{ $row->min_value }}</td>
                                    <td>{{ $row->max_value }}</td>
                                    <td><span class="status-pill danger">{{ $row->trend_direction }}</span></td>
                                @elseif($reportType === 'interzone_transfers')
                                    <td><strong>{{ $row->batch_code }}</strong></td>
                                    <td>{{ $row->origin }}</td>
                                    <td>{{ $row->destination }}</td>
                                    <td>{{ $row->courier }}</td>
                                    <td>{{ $row->dispatch_date }}</td>
                                    <td><span class="status-pill warning">{{ $row->status }}</span></td>
                                @elseif($reportType === 'zonal_performance')
                                    <td><strong>{{ $row->zone_name }}</strong></td>
                                    <td>{{ $row->target_sla }}</td>
                                    <td>{{ $row->sla_met }}</td>
                                    <td>{{ $row->volume_handled }}</td>
                                    <td><span class="status-pill success">{{ $row->zonal_rating }}</span></td>
                                @elseif($reportType === 'proforma_invoice')
                                    <td><strong>{{ $row->proforma_ref }}</strong></td>
                                    <td>{{ $row->client_name }}</td>
                                    <td>{{ $row->sample_count }}</td>
                                    <td>{{ $row->total_fee }}</td>
                                    <td><span class="status-pill warning">{{ $row->status }}</span></td>
                                @elseif($reportType === 'payment_receipt')
                                    <td><strong>{{ $row->receipt_ref }}</strong></td>
                                    <td>{{ $row->invoice_ref }}</td>
                                    <td>{{ $row->client_name }}</td>
                                    <td>{{ $row->amount_paid }}</td>
                                    <td>{{ $row->payment_date }}</td>
                                @elseif($reportType === 'aging_receivables')
                                    <td><strong>{{ $row->client_name }}</strong></td>
                                    <td>{{ $row->current }}</td>
                                    <td>{{ $row->days_30_60 }}</td>
                                    <td>{{ $row->days_61_90 }}</td>
                                    <td>{{ $row->days_over_90 }}</td>
                                    <td><strong>{{ $row->total_due }}</strong></td>
                                @elseif($reportType === 'revenue_summary')
                                    <td><strong>{{ $row->lab_section }}</strong></td>
                                    <td>{{ $row->quarterly_revenue }}</td>
                                    <td>{{ $row->target_achievement }}</td>
                                    <td>{{ $row->invoiced_batches }}</td>
                                    <td><span class="status-pill success">{{ $row->status }}</span></td>
                                @elseif($reportType === 'reconciliation_report')
                                    <td><strong>{{ $row->month_period }}</strong></td>
                                    <td>{{ $row->expected }}</td>
                                    <td>{{ $row->collected }}</td>
                                    <td>{{ $row->discrepancy }}</td>
                                    <td><span class="status-pill success">{{ $row->status }}</span></td>
                                @elseif($reportType === 'annual_procurement_plan')
                                    <td><strong>{{ $row->reagent_name }}</strong></td>
                                    <td>{{ $row->fiscal_year }}</td>
                                    <td>{{ $row->quarterly_planned_qty }}</td>
                                    <td>{{ $row->unit_cost }}</td>
                                    <td>{{ $row->allocated_budget }}</td>
                                    <td><span class="status-pill success">{{ $row->status }}</span></td>
                                @elseif($reportType === 'inspection_report')
                                    <td><strong>{{ $row->delivery_ref }}</strong></td>
                                    <td>{{ $row->supplier_name }}</td>
                                    <td>{{ $row->inspection_date }}</td>
                                    <td><span class="status-pill success">{{ $row->conformity_status }}</span></td>
                                    <td>{{ $row->inspected_by }}</td>
                                @elseif($reportType === 'grn_note')
                                    <td><strong>{{ $row->grn_number }}</strong></td>
                                    <td>{{ $row->delivery_date }}</td>
                                    <td>{{ $row->supplier_name }}</td>
                                    <td>{{ $row->items_accepted }}</td>
                                    <td>{{ $row->items_returned }}</td>
                                @elseif($reportType === 'inventory_status')
                                    <td><strong>{{ $row->reagent_name }}</strong></td>
                                    <td>{{ $row->current_qty }}</td>
                                    <td>{{ $row->unit_measure }}</td>
                                    <td>{{ $row->storage_temp }}</td>
                                    <td>{{ $row->safety_rating }}</td>
                                @elseif($reportType === 'stock_disposal')
                                    <td><strong>{{ $row->reagent_name }}</strong></td>
                                    <td>{{ $row->batch_lot }}</td>
                                    <td>{{ $row->disposed_date }}</td>
                                    <td>{{ $row->disposal_method }}</td>
                                    <td>{{ $row->officer }}</td>
                                @elseif($reportType === 'audit_report')
                                    <td><strong>{{ $row->audit_reference }}</strong></td>
                                    <td>{{ $row->audited_section }}</td>
                                    <td>{{ $row->date_conducted }}</td>
                                    <td>{{ $row->ncs_found }}</td>
                                    <td>{{ $row->lead_auditor }}</td>
                                @elseif($reportType === 'risk_register')
                                    <td><strong>{{ $row->risk_number }}</strong></td>
                                    <td>{{ $row->title }}</td>
                                    <td>{{ optional($row->category)->name ?? 'General' }}</td>
                                    <td>
                                        <span class="status-pill {{ strtolower($row->risk_level) === 'critical' ? 'danger' : 'warning' }}">
                                            {{ $row->risk_level ?? 'Medium' }}
                                        </span>
                                    </td>
                                    <td>{{ $row->status_name }}</td>
                                @elseif($reportType === 'non_conformance')
                                    <td><strong>{{ $row->nc_number }}</strong></td>
                                    <td>{{ $row->title }}</td>
                                    <td>{{ optional($row->date_identified)->format('Y-m-d') }}</td>
                                    <td>{{ optional($row->identifiedByUser)->name ?? 'System' }}</td>
                                    <td>{{ $row->status_name }}</td>
                                @elseif($reportType === 'mgmt_review')
                                    <td><strong>{{ $row->meeting_date }}</strong></td>
                                    <td>{{ $row->attendees }}</td>
                                    <td>{{ $row->agenda_summary }}</td>
                                    <td>{{ $row->actions_count }}</td>
                                    <td>{{ $row->chairperson }}</td>
                                @elseif($reportType === 'complaints_report')
                                    <td><strong>{{ $row->complaint_code }}</strong></td>
                                    <td>{{ $row->client_name }}</td>
                                    <td>{{ $row->logged_date }}</td>
                                    <td>{{ $row->feedback_type }}</td>
                                    <td><span class="status-pill {{ $row->status === 'Resolved' ? 'success' : 'warning' }}">{{ $row->status }}</span></td>
                                @elseif($reportType === 'exception_report')
                                    <td><strong>{{ $row->exception_code }}</strong></td>
                                    <td><span class="status-pill danger">{{ $row->severity }}</span></td>
                                    <td>{{ $row->incident_date }}</td>
                                    <td>{{ $row->description }}</td>
                                    <td>{{ $row->investigated_by }}</td>
                                @elseif($reportType === 'system_audit')
                                    <td>{{ optional($row->created_at)->format('Y-m-d H:i:s') }}</td>
                                    <td>{{ optional($row->user)->name ?? 'Guest/Console' }}</td>
                                    <td>{{ $row->event }}</td>
                                    <td>{{ $row->ip_address }}</td>
                                @elseif($reportType === 'sample_audit')
                                    <td><strong>{{ $row->sample_code }}</strong></td>
                                    <td>{{ $row->audit_date }}</td>
                                    <td>{{ $row->discrepancies }}</td>
                                    <td>{{ $row->verified_by }}</td>
                                    <td><span class="status-pill success">{{ $row->status }}</span></td>
                                @elseif($reportType === 'reagent_audit')
                                    <td><strong>{{ $row->reagent_name }}</strong></td>
                                    <td>{{ $row->lot_number }}</td>
                                    <td>{{ $row->actual_stock }}</td>
                                    <td>{{ $row->system_stock }}</td>
                                    <td>{{ $row->discrepancy }}</td>
                                @elseif($reportType === 'ceo_performance')
                                    <td><strong>{{ $row->period }}</strong></td>
                                    <td>{{ $row->total_revenue }}</td>
                                    <td>{{ $row->overall_tat }}</td>
                                    <td>{{ $row->customer_satisfaction }}</td>
                                    <td><span class="status-pill success">{{ $row->performance_rating }}</span></td>
                                @elseif($reportType === 'equipment_breakdown')
                                    <td><strong>{{ $row->instrument_name }}</strong></td>
                                    <td>{{ $row->breakdown_date }}</td>
                                    <td>{{ $row->repair_completion }}</td>
                                    <td>{{ $row->downtime_hours }}</td>
                                    <td>{{ $row->repair_cost }}</td>
                                @elseif($reportType === 'clients_served')
                                    <td><strong>{{ $row->month }}</strong></td>
                                    <td>{{ $row->corporate_clients }}</td>
                                    <td>{{ $row->individual_clients }}</td>
                                    <td>{{ $row->govt_bodies }}</td>
                                    <td><strong>{{ $row->total_served }}</strong></td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <!-- Dynamic Empty State (shown if no results, OR if user switched to a different tab than the queried tab) -->
        <div class="empty-state" id="results-empty-state" style="display: {{ !$results || $results->count() === 0 ? 'block' : 'none' }};">
            @if($results && $results->count() === 0)
                <i class="mdi mdi-database-minus"></i>
                <h3>No Matching Records Found</h3>
                <p>Adjust your filter parameters or select a different date range to look up lab records.</p>
            @else
                <i class="mdi mdi-chart-line-stacked"></i>
                <h3>Ready to Generate Report</h3>
                <p>Please select an active report card above, customize your filters, and click generate.</p>
            @endif
        </div>
    </div>
</div>

<script>
    const activeQueriedTab = @json($activeTab);
    const hasQueriedResults = @json($results && $results->count() > 0);

    function selectReport(type, tab) {
        document.querySelectorAll(`#${tab} .report-card`).forEach(c => c.classList.remove('selected'));
        event.currentTarget.classList.add('selected');

        // Set value in the specific tab's form hidden input
        const activeForm = document.querySelector(`#filter-panel-${tab} form`);
        if (activeForm) {
            activeForm.querySelector('.tab-report-type-input').value = type;
        }

        // Show specific filter panel for that tab
        document.querySelectorAll('.filter-panel').forEach(fp => fp.style.display = 'none');
        const targetPanel = document.getElementById(`filter-panel-${tab}`);
        if (targetPanel) {
            targetPanel.style.display = 'block';
        }
    }

    function switchTab(tabId) {
        // Toggle tab buttons
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        event.currentTarget.classList.add('active');

        // Toggle content panels
        document.querySelectorAll('.tab-content-panel').forEach(p => p.classList.remove('active'));
        document.getElementById(tabId).classList.add('active');

        // Hide other filter panels, show active tab's if report type selected
        document.querySelectorAll('.filter-panel').forEach(fp => fp.style.display = 'none');
        const activeForm = document.querySelector(`#filter-panel-${tabId} form`);
        if (activeForm && activeForm.querySelector('.tab-report-type-input').value) {
            document.getElementById(`filter-panel-${tabId}`).style.display = 'block';
        }

        // Toggle Preview Grid visibility dynamically based on active tab
        const actionsWrapper = document.getElementById('results-actions-wrapper');
        const tableWrapper = document.getElementById('results-table-wrapper');
        const emptyState = document.getElementById('results-empty-state');

        if (hasQueriedResults && tabId === activeQueriedTab) {
            if (actionsWrapper) actionsWrapper.style.display = 'block';
            if (tableWrapper) tableWrapper.style.display = 'block';
            if (emptyState) emptyState.style.display = 'none';
        } else {
            if (actionsWrapper) actionsWrapper.style.display = 'none';
            if (tableWrapper) tableWrapper.style.display = 'none';
            if (emptyState) {
                emptyState.style.display = 'block';
                emptyState.innerHTML = `
                    <i class="mdi mdi-chart-line-stacked"></i>
                    <h3>Ready to Generate Report</h3>
                    <p>Please select an active report card above, customize your filters, and click generate.</p>
                `;
            }
        }
    }

    // Scroll to preview if results loaded
    document.addEventListener("DOMContentLoaded", function() {
        if (hasQueriedResults) {
            const previewSection = document.getElementById('report-preview-section');
            if (previewSection) {
                previewSection.scrollIntoView({ behavior: 'smooth' });
            }
        }
    });
</script>
@endsection
