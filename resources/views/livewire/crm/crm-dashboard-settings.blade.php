<main>
    @php
        $breadcrumbItems = [
            ['link' => route('crm-dashboard'), 'name' => 'CRM', 'icon' => null],
            ['link' => route('crm-dashboard-settings'), 'name' => 'Settings & Configuration', 'icon' => null],
        ];
    @endphp

    <div class="container-fluid">
        <x-crm.page-header
            :breadcrumbItems="$breadcrumbItems"
            title="System Configurations"
            subtitle="Manage dashboard insights, visual preferences, and configure automated SLA alert rules."
            icon="mdi-cog"
        >
            <x-slot name="actions">
                <a href="{{ route('crm-dashboard') }}" class="btn btn-sm btn-light shadow-sm" style="border-radius:20px; font-weight:600;">
                    <i class="mdi mdi-arrow-left"></i> Back to Dashboard
                </a>
            </x-slot>
        </x-crm.page-header>

        <div class="row">
            {{-- Navigation Sidebar --}}
            <div class="col-md-3 mb-4">
                <div class="crm-card h-100 p-3">
                    <ul class="nav flex-column nav-pills" style="gap: 5px;">
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab === 'widgets' ? 'active shadow-sm' : 'text-muted' }}" 
                               wire:click.prevent="changeTab('widgets')" 
                               href="#" 
                               style="border-radius: 8px; font-weight: 500; transition: all 0.2s;">
                                <i class="mdi mdi-view-dashboard-variant-outline mr-2"></i> Dashboard Visualizations
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab === 'alerts' ? 'active shadow-sm' : 'text-muted' }}" 
                               wire:click.prevent="changeTab('alerts')" 
                               href="#" 
                               style="border-radius: 8px; font-weight: 500; transition: all 0.2s;">
                                <i class="mdi mdi-bell-alert-outline mr-2"></i> SLA & System Alerts
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab === 'scoreFormula' ? 'active shadow-sm' : 'text-muted' }}" 
                               wire:click.prevent="changeTab('scoreFormula')" 
                               href="#" 
                               style="border-radius: 8px; font-weight: 500; transition: all 0.2s;">
                                <i class="mdi mdi-calculator-variant-outline mr-2"></i> Customer Score Metrics Builder
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Main Content Area --}}
            <div class="col-md-9 mb-5">
                
                @if($activeTab === 'widgets')
                {{-- ════════════════════════════════════════════════════════
                     WIDGETS MANAGER
                ════════════════════════════════════════════════════════ --}}
                <div class="crm-card">
                    <div class="crm-dash-card-header p-4 border-bottom">
                        <div class="crm-dash-card-header-icon" style="background:rgba(99,102,241,.1);">
                            <i class="mdi mdi-chart-donut-variant" style="color:#6366f1;"></i>
                        </div>
                        <div>
                            <h5 class="crm-dash-section-title mb-1" style="font-size: 1.1rem;">Dashboard Visualizations</h5>
                            <div class="text-muted" style="font-size: 0.85rem;">Configure the layout, format, and insights visible on the CRM Dashboard canvas.</div>
                        </div>
                        @if(!$isEditingInsight)
                        <div class="ml-auto">
                            <button type="button" class="btn btn-sm btn-primary shadow-sm" wire:click="toggleInsightForm" style="border-radius:20px; font-weight:600;">
                                <i class="mdi mdi-plus"></i> Add New Insight
                            </button>
                        </div>
                        @endif
                    </div>

                    <div class="crm-card-body p-4">
                        @if(session('widget_success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius: 8px;">
                                <i class="mdi mdi-check-circle-outline mr-1"></i> {{ session('widget_success') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif

                        @if($isEditingInsight)
                            <div class="p-4" style="background:#f8fafc; border-radius:12px; border: 1px dashed #cbd5e1;">
                                <h6 style="color:var(--crm-primary); font-weight:600; margin-bottom:1.5rem;">
                                    <i class="mdi {{ $insightForm['id'] ? 'mdi-pencil' : 'mdi-auto-fix' }}"></i> 
                                    {{ $insightForm['id'] ? 'Edit Dashboard Element' : 'Build Custom Dashboard Element' }}
                                </h6>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label style="font-size:0.8rem; font-weight:600; color:var(--crm-neutral-700);">Insight Title</label>
                                            <input type="text" class="form-control form-control-sm" wire:model="insightForm.title" placeholder="e.g. Most Active Customers" style="border-radius:6px;">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label style="font-size:0.8rem; font-weight:600; color:var(--crm-neutral-700);">Data Target <span class="text-danger">*</span></label>
                                            <select class="form-control form-control-sm" wire:model="insightForm.data_source" style="border-radius:6px;">
                                                <option value="">Select telemetry source...</option>
                                                <option value="top_customers">Top Customers (By Interaction Score)</option>
                                                <option value="least_interactive_customers">At Risk Customers (Low Interaction Score)</option>
                                                <option value="complaints_by_type_priority">Complaint Issue Types</option>
                                                <option value="rating_vs_issues_trend">Overall Satisfaction vs Issues</option>
                                                <option value="complaints_by_priority">Complaints Priority Scan</option>
                                                <option value="sample_volume_trend">Sample Volume Trend</option>
                                                {{-- Add default KPIs --}}
                                                <option value="total_customers">Total Customers KPI</option>
                                                <option value="open_complaints">Open Complaints KPI</option>
                                                <option value="nps_score">NPS Score KPI</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label style="font-size:0.8rem; font-weight:600; color:var(--crm-neutral-700);">Insight Layout Target</label>
                                            <select class="form-control form-control-sm" wire:model="insightForm.type" style="border-radius:6px;">
                                                <option value="kpi_card">Top KPI Ribbon Card (Small Box)</option>
                                                <option value="bar_stacked">Stacked Bar Chart</option>
                                                <option value="horizontalBar">Horizontal Tally Graph</option>
                                                <option value="doughnut">Doughnut Scan</option>
                                                <option value="line">Trend Line / Area</option>
                                                <option value="line_dual">Dual Axis Trend Line</option>
                                                <option value="leaderboard">Ranked Leaderboard List</option>
                                                <option value="table">Raw Tabular Data Grid</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        @if(($insightForm['type'] ?? '') === 'kpi_card')
                                        <div class="form-group">
                                            <label style="font-size:0.8rem; font-weight:600; color:var(--crm-neutral-700);">KPI Accent Color</label>
                                            <select class="form-control form-control-sm" wire:model="insightForm.color" style="border-radius:6px;">
                                                <option value="primary">Deep Blue (Primary)</option>
                                                <option value="success">Green (Positive)</option>
                                                <option value="warning">Orange (Caution)</option>
                                                <option value="danger">Red (Urgent)</option>
                                                <option value="indigo">Indigo (Highlight)</option>
                                            </select>
                                        </div>
                                        @else
                                        <div class="form-group">
                                            <label style="font-size:0.8rem; font-weight:600; color:var(--crm-neutral-700);">Graph Grid Size</label>
                                            <select class="form-control form-control-sm" wire:model="insightForm.grid_width" style="border-radius:6px;">
                                                <option value="4">Small Size (1/3 Width)</option>
                                                <option value="5">Medium-Small Size</option>
                                                <option value="6">Medium Size (1/2 Width)</option>
                                                <option value="7">Medium-Large Size</option>
                                                <option value="8">Large Size (2/3 Width)</option>
                                                <option value="12">Full Width (Panoramic)</option>
                                            </select>
                                        </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="mt-3 text-right">
                                    <button class="btn btn-sm btn-light shadow-sm mr-2" wire:click="cancelInsightForm" style="border-radius:20px; font-weight:600;">Cancel</button>
                                    <button class="btn btn-sm btn-primary shadow-sm" wire:click="saveInsight" style="border-radius:20px; font-weight:600;">Save Selection</button>
                                </div>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover crm-table" style="vertical-align: middle;">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="border-0">Insight Title</th>
                                            <th class="border-0 text-center">Type</th>
                                            <th class="border-0 text-center">Format / Size</th>
                                            <th class="border-0 text-center">Status</th>
                                            <th class="border-0 text-right">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($widgetsList as $index => $widget)
                                            <tr>
                                                <td>
                                                    <div style="font-weight:600; font-size:0.9rem; color:var(--crm-neutral-800);">{{ $widget['title'] }}</div>
                                                    <div class="text-muted" style="font-size:0.75rem;">Source: {{ str_replace('_', ' ', \Illuminate\Support\Str::title($widget['data_source'])) }}</div>
                                                </td>
                                                <td class="text-center">
                                                    @if($widget['type'] === 'kpi_card')
                                                        <span class="badge" style="background:#e0e7ff; color:#4338ca;">KPI Card</span>
                                                    @else
                                                        <span class="badge" style="background:#f1f5f9; color:#475569;">Graph Pivot</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if($widget['type'] === 'kpi_card')
                                                        <div class="d-inline-flex align-items-center justify-content-center" style="width: 24px; height: 24px; border-radius: 50%; background: var(--crm-{{ $widget['color'] }}-light); color: var(--crm-{{ $widget['color'] }}); font-size: 10px;">
                                                            <i class="mdi mdi-palette"></i>
                                                        </div>
                                                    @else
                                                        <span class="text-muted" style="font-size: 0.8rem; font-weight:500;">
                                                            {{ $widget['grid_width'] < 6 ? 'Small' : ($widget['grid_width'] == 6 ? 'Medium' : ($widget['grid_width'] < 12 ? 'Large' : 'Full Width')) }} (Grid: {{ $widget['grid_width'] }})
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <div class="custom-control custom-switch d-inline-block">
                                                        <input type="checkbox" class="custom-control-input" id="widgetSwitch{{ $widget['id'] }}" wire:model="widgetsList.{{ $index }}.is_active" wire:click="toggleWidgetActive({{ $index }})">
                                                        <label class="custom-control-label" for="widgetSwitch{{ $widget['id'] }}"></label>
                                                    </div>
                                                </td>
                                                <td class="text-right">
                                                    <button class="btn btn-sm btn-outline-secondary" wire:click="toggleInsightForm({{ $widget['id'] }})" style="border-radius:8px; padding: 4px 8px;" title="Edit Insight">
                                                        <i class="mdi mdi-pencil-outline"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="5" class="text-center text-muted py-4">No widgets tracked.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
                @endif

                @if($activeTab === 'alerts')
                {{-- ════════════════════════════════════════════════════════
                     ALERTS & SLA MANAGER
                ════════════════════════════════════════════════════════ --}}
                <div class="crm-card fade-in">
                    <div class="crm-dash-card-header p-4 border-bottom">
                        <div class="crm-dash-card-header-icon" style="background:rgba(220,38,38,.1);">
                            <i class="mdi mdi-bell-ring-outline" style="color:#dc2626;"></i>
                        </div>
                        <div>
                            <h5 class="crm-dash-section-title mb-1" style="font-size: 1.1rem;">SLA & System Alerts Configuration</h5>
                            <div class="text-muted" style="font-size: 0.85rem;">Establish automatic time-bound SLAs and condition thresholds that trigger system alerts for complaints and lab issues.</div>
                        </div>
                        @if(!$isEditingAlertRule)
                        <div class="ml-auto">
                            <button type="button" class="btn btn-sm btn-danger shadow-sm" style="border-radius:20px; font-weight:600; background: #dc2626; border-color:#dc2626;" wire:click="toggleAlertRuleForm">
                                <i class="mdi mdi-plus"></i> Add SLA Rule
                            </button>
                        </div>
                        @endif
                    </div>

                    <div class="crm-card-body p-4">
                        @if(session('alert_success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius: 8px;">
                                <i class="mdi mdi-check-circle-outline mr-1"></i> {{ session('alert_success') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif

                        @if($isEditingAlertRule)
                            <div class="p-4" style="background:#fff5f5; border-radius:12px; border: 1px dashed #fca5a5;">
                                <h6 style="color:#dc2626; font-weight:600; margin-bottom:1.5rem;">
                                    <i class="mdi {{ $alertRuleForm['id'] ? 'mdi-pencil' : 'mdi-plus-circle-outline' }}"></i> 
                                    {{ $alertRuleForm['id'] ? 'Edit SLA Rule' : 'Create New SLA Condition' }}
                                </h6>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label style="font-size:0.8rem; font-weight:600; color:var(--crm-neutral-700);">Rule Title / Description</label>
                                            <input type="text" class="form-control form-control-sm" wire:model="alertRuleForm.rule_name" placeholder="e.g. Complaint SLA Breach Warning" style="border-radius:6px;">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label style="font-size:0.8rem; font-weight:600; color:var(--crm-neutral-700);">Condition Trigger <span class="text-danger">*</span></label>
                                            <select class="form-control form-control-sm" wire:model="alertRuleForm.condition_type" style="border-radius:6px;">
                                                <option value="">Select condition type...</option>
                                                <option value="complaint_sla_overdue_days">Complaint Open Duration (Days)</option>
                                                <option value="feedback_sla_overdue_days">Feedback Pending Duration (Days)</option>
                                                <option value="nps_critical_threshold">NPS Drops Below Critical Score</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label style="font-size:0.8rem; font-weight:600; color:var(--crm-neutral-700);">Threshold Value <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control form-control-sm" wire:model="alertRuleForm.threshold_value" placeholder="e.g. 14" style="border-radius:6px;">
                                            <small class="form-text text-muted">For SLAs, enter the number of days (e.g., 14 for two weeks).</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label style="font-size:0.8rem; font-weight:600; color:var(--crm-neutral-700);">Alert Delivery Mechanism</label>
                                            <select class="form-control form-control-sm" wire:model="alertRuleForm.action" style="border-radius:6px;">
                                                <option value="system_alert_feed">System Alert Timeline Flag</option>
                                                <option value="email_escalation">Direct Email Escalation</option>
                                                <option value="manager_notification">Manager In-App Notice</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-3 text-right">
                                    <button class="btn btn-sm btn-light shadow-sm mr-2" wire:click="cancelAlertRuleForm" style="border-radius:20px; font-weight:600;">Cancel</button>
                                    <button class="btn btn-sm btn-danger shadow-sm" wire:click="saveAlertRule" style="border-radius:20px; font-weight:600; background: #dc2626; border-color:#dc2626;">Save Alert Rule</button>
                                </div>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover crm-table" style="vertical-align: middle;">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="border-0">Rule Title</th>
                                            <th class="border-0">Condition</th>
                                            <th class="border-0 text-center">Threshold</th>
                                            <th class="border-0 text-center">Action</th>
                                            <th class="border-0 text-center">Active</th>
                                            <th class="border-0 text-right">Edit</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($alertRulesList as $index => $rule)
                                            <tr>
                                                <td>
                                                    <div style="font-weight:600; font-size:0.9rem; color:var(--crm-neutral-800);">{{ $rule['rule_name'] }}</div>
                                                </td>
                                                <td>
                                                    <span style="font-size:0.85rem; color:var(--crm-neutral-600);">{{ str_replace('_', ' ', \Illuminate\Support\Str::title($rule['condition_type'])) }}</span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge" style="background:#fef2f2; color:#b91c1c; border:1px solid #fca5a5;">{{ $rule['threshold_value'] }}</span>
                                                </td>
                                                <td class="text-center">
                                                    <span style="font-size:0.75rem;" class="text-muted"><i class="mdi mdi-bell-check-outline"></i> {{ str_replace('_', ' ', \Illuminate\Support\Str::title($rule['action'])) }}</span>
                                                </td>
                                                <td class="text-center">
                                                    <div class="custom-control custom-switch d-inline-block">
                                                        <input type="checkbox" class="custom-control-input" id="alertSwitch{{ $rule['id'] }}" wire:model="alertRulesList.{{ $index }}.is_active" wire:click="toggleAlertRuleActive({{ $index }})">
                                                        <label class="custom-control-label" for="alertSwitch{{ $rule['id'] }}"></label>
                                                    </div>
                                                </td>
                                                <td class="text-right">
                                                    <button class="btn btn-sm btn-outline-secondary" wire:click="toggleAlertRuleForm({{ $rule['id'] }})" style="border-radius:8px; padding: 4px 8px;" title="Edit Rule">
                                                        <i class="mdi mdi-pencil-outline"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="6" class="text-center text-muted py-4">No SLA alert rules configured.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
                @endif

                @if($activeTab === 'scoreFormula')
                {{-- ════════════════════════════════════════════════════════
                     SCORE FORMULA MANAGER
                ════════════════════════════════════════════════════════ --}}
                <div class="crm-card fade-in">
                    <div class="crm-dash-card-header p-4 border-bottom">
                        <div class="crm-dash-card-header-icon" style="background:rgba(99,102,241,.1);">
                            <i class="mdi mdi-calculator-variant-outline" style="color:#6366f1;"></i>
                        </div>
                        <div>
                            <h5 class="crm-dash-section-title mb-1" style="font-size: 1.1rem;">Customer Score Metrics Builder</h5>
                            <div class="text-muted" style="font-size: 0.85rem;">Adjust the mathematical weights that determine a client's Interaction Score and Tier Ranking.</div>
                        </div>
                    </div>

                    <div class="crm-card-body p-4">
                        @if(session('score_success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius: 8px;">
                                <i class="mdi mdi-check-circle-outline mr-1"></i> {{ session('score_success') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif

                        <div class="p-4 mb-4" style="background:#f8fafc; border-radius:12px; border: 1px dashed #cbd5e1;">
                            <h6 style="color:var(--crm-neutral-800); font-weight:600; margin-bottom:1rem;">
                                Formula Preview
                            </h6>
                            <div style="font-family: monospace; font-size: 1.1rem; background: #fff; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0; text-align: center;">
                                <span class="text-primary" style="font-weight:700;">Score</span> = 
                                <span class="text-muted">{{ $scoreConfig['base_score'] ?? 50 }}</span> + 
                                (Samples <span class="text-muted">× {{ $scoreConfig['sample_weight'] ?? 10 }}</span>) + 
                                (Feedback <span class="text-muted">× {{ $scoreConfig['feedback_weight'] ?? 5 }}</span>) + 
                                (Complaints <span class="text-muted">× {{ $scoreConfig['complaint_weight'] ?? -5 }}</span>)
                            </div>
                            <small class="form-text text-muted text-center mt-2">Any changes saved will immediately recalculate all customer rankings across the system.</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label style="font-weight:600; color:var(--crm-neutral-700);">Base Starting Score</label>
                                    <input type="number" class="form-control" wire:model.lazy="scoreConfig.base_score" style="border-radius:6px;">
                                    <small class="text-muted">The default score every client starts with.</small>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label style="font-weight:600; color:var(--crm-neutral-700);">Points per Sample</label>
                                    <input type="number" class="form-control" wire:model.lazy="scoreConfig.sample_weight" style="border-radius:6px;">
                                    <small class="text-muted">Points awarded for every sample submitted.</small>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label style="font-weight:600; color:var(--crm-neutral-700);">Points per Feedback</label>
                                    <input type="number" class="form-control" wire:model.lazy="scoreConfig.feedback_weight" style="border-radius:6px;">
                                    <small class="text-muted">Points awarded when a client returns feedback.</small>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label style="font-weight:600; color:var(--crm-neutral-700);">Points per Complaint</label>
                                    <input type="number" class="form-control" wire:model.lazy="scoreConfig.complaint_weight" style="border-radius:6px;">
                                    <small class="text-muted">Typically negative (e.g., -5) to reduce score on issues.</small>
                                </div>
                            </div>
                        </div>

                        <hr>
                        <div class="text-right">
                            <button class="btn btn-primary shadow-sm" wire:click="saveCustomFormula" wire:loading.attr="disabled" style="border-radius:20px; font-weight:600;">
                                <span wire:loading.remove wire:target="saveCustomFormula"><i class="mdi mdi-calculator"></i> Save Formula & Recalculate</span>
                                <span wire:loading wire:target="saveCustomFormula"><i class="mdi mdi-loading mdi-spin"></i> Recalculating...</span>
                            </button>
                        </div>
                    </div>
                </div>
                @endif
                
            </div>
        </div>
    </div>
</main>

@section('script2')
<style>
    .fade-in {
        animation: fadeIn 0.3s ease-in-out;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(5px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endsection
