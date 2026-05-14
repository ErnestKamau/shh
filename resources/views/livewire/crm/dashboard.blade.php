<div>
    <!-- CSS specifically for the Bento-Box CRM Dashboard -->
    <style type="text/css">
        :root {
            --primary-glass: #ffffff;
            --accent-blue: #0ea5e9;
            --accent-green: #10b981;
            --accent-red: #ef4444;
            --accent-orange: #f59e0b;
            --bg-color: #f8fafc;
            --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            --transition-speed: 0.2s;
        }
        .bento-card {
            background: var(--primary-glass);
            border-radius: 12px;
            border: 1px solid rgba(0,0,0,0.06);
            box-shadow: var(--card-shadow);
            transition: transform var(--transition-speed) ease, box-shadow var(--transition-speed) ease;
            overflow: hidden;
            margin-bottom: 20px;
        }
        .bento-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.025);
        }
        .pipeline-card { padding: 24px; text-align: center; position: relative; cursor: pointer; }
        .stat-value { font-size: 2.5rem; font-weight: 700; line-height: 1; margin: 10px 0; color: #1e293b; }
        .stat-label { font-size: 0.875rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; }
        .stat-subtext { font-size: 0.75rem; color: #94a3b8; margin-top: 5px; }
    
        .pulse-dot {
            height: 10px; width: 10px; border-radius: 50%; display: inline-block;
            animation: pulse 2.5s infinite;
        }
        .pulse-red { background-color: var(--accent-red); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4); }
        .pulse-orange { background-color: var(--accent-orange); box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.4); }
        .pulse-green { background-color: var(--accent-green); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4); }
    
        @keyframes pulse {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(var(--box-color), 0.5); }
            70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(var(--box-color), 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(var(--box-color), 0); }
        }
    
        .section-header { padding: 16px 20px; border-bottom: 1px solid #f8fafc; font-weight: 600; color: #334155; display: flex; align-items: center; justify-content: space-between;}
        .section-body { padding: 20px; }
        
        .client-action-btn {
            background-color: #f1f5f9;
            color: #334155;
            padding: 12px 16px;
            border-radius: 8px;
            font-weight: 600;
            display: flex;
            align-items: center;
            transition: all 0.2s;
            text-decoration: none;
            margin-bottom: 10px;
        }
        .client-action-btn:hover {
            background-color: var(--accent-blue);
            color: #fff;
            text-decoration: none;
        }
        .client-action-btn i { font-size: 18px; margin-right: 12px; }
        
        .nav-tabs.modern-tabs { border-bottom: 2px solid #e2e8f0; }
        .nav-tabs.modern-tabs .nav-link { border: none; color: #64748b; font-weight: 600; padding: 12px 24px; position: relative; background: transparent; cursor: pointer; }
        .nav-tabs.modern-tabs .nav-link.active { color: var(--accent-blue); background: transparent; }
        .nav-tabs.modern-tabs .nav-link.active::after { content: ''; position: absolute; bottom: -2px; left: 0; right: 0; height: 2px; background: var(--accent-blue); }
    
        .smart-table th { background: #f8fafc; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; border-top: none; }
        .smart-table td { vertical-align: middle; font-weight: 500; color: #334155; border-color: #f1f5f9; }
    </style>

    <div class="px-3 py-4">
        <!-- HEADER -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="mb-1" style="font-weight: 700; color: #1e293b;">{{ __('crm.welcome_back') }} 👋</h3>
                <p class="text-muted mb-0">{{ __('crm.crm_overview_summary') }}</p>
            </div>
            <div class="d-flex align-items-center gap-3">
                @if($openComplaints > 0)
                <div class="bento-card px-3 py-2 mr-2 mb-0 d-flex align-items-center">
                    <span class="pulse-dot pulse-red mr-2" style="--box-color: 239, 68, 68;"></span>
                    <span class="font-weight-bold text-danger text-sm" style="font-size: 0.85rem;">{{ __('crm.active_complaints_count', ['count' => $openComplaints]) }}</span>
                </div>
                @else
                <div class="bento-card px-3 py-2 mr-2 mb-0 d-flex align-items-center">
                    <span class="pulse-dot pulse-green mr-2" style="--box-color: 16, 185, 129;"></span>
                    <span class="font-weight-bold text-success text-sm" style="font-size: 0.85rem;">{{ __('crm.zero_active_complaints') }}</span>
                </div>
                @endif
                
                <div class="bento-card px-3 py-2 mb-0 d-flex align-items-center">
                    <span class="pulse-dot pulse-orange mr-2" style="--box-color: 245, 158, 11;"></span>
                    <span class="font-weight-bold text-warning text-sm" style="font-size: 0.85rem;">{{ __('crm.expiring_certifications', ['count' => 5]) }}</span>
                </div>
            </div>
        </div>

        <!-- HERO PIPELINE (Top Row) -->
        <div class="row mb-4">
            <!-- Active Clients -->
            <div class="col-md-3">
                <div class="bento-card pipeline-card h-100" onclick="window.location.href='@php echo route('livewire.customers'); @endphp'">
                    <div class="stat-label text-primary"><i class="fas fa-building mr-1"></i> {{ __('crm.total_clients') }}</div>
                    <div class="stat-value">{{ number_format($totalCustomers) }}</div>
                    <div class="stat-subtext">{{ __('crm.active_company_accounts') }}</div>
                </div>
            </div>
            <!-- Total Complaints -->
            <div class="col-md-3">
                <div class="bento-card pipeline-card h-100" onclick="window.location.href='@php echo route('crm.complaints-manager'); @endphp'">
                    <div class="stat-label text-danger"><i class="fas fa-exclamation-triangle mr-1"></i> {{ __('crm.open_complaints') }}</div>
                    <div class="stat-value">{{ $openComplaints }}</div>
                    <div class="stat-subtext">{{ __('crm.issues_under_investigation') }}</div>
                </div>
            </div>
            <!-- Feedback -->
            <div class="col-md-3">
                <div class="bento-card pipeline-card h-100">
                    <div class="stat-label text-warning"><i class="fas fa-comments mr-1"></i> {{ __('crm.feedback_forms') }}</div>
                    <div class="stat-value">{{ $feedbackCount }}</div>
                    <div class="stat-subtext">{{ __('crm.received_this_month') }}</div>
                </div>
            </div>
        </div>

        <!-- Analytics and Actions (Row 2) -->
        <div class="row mb-4">
            <!-- Complaints Chart -->
            <div class="col-lg-8 mb-3 mb-lg-0">
                <div class="bento-card h-100 mb-0">
                    <div class="section-header">
                        <span><i class="fas fa-chart-line text-muted mr-2"></i> {{ __('crm.complaints_resolutions_timeline') }}</span>
                    </div>
                    <div class="section-body p-4 d-flex justify-content-center align-items-center text-muted" style="min-height: 250px; background-color: #fafbfc; border-radius: 8px;">
                        <div class="text-center">
                            <i class="fas fa-chart-bar fa-3x mb-3 text-secondary" style="opacity: 0.3;"></i>
                            <p>{{ __('crm.chart_placeholder') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="col-lg-4">
                <div class="bento-card h-100 mb-0">
                    <div class="section-header">
                        <span><i class="fas fa-bolt text-warning mr-2"></i> {{ __('crm.quick_actions') }}</span>
                    </div>
                    <div class="section-body">
                        <a href="{{ route('livewire.customers') }}" class="client-action-btn">
                            <i class="fas fa-user-plus text-primary"></i> {{ __('crm.add_new_client_account') }}
                        </a>
                        <a href="{{ route('crm.complaints-manager') }}" class="client-action-btn">
                            <i class="fas fa-exclamation-circle text-danger"></i> {{ __('crm.log_new_complaint') }}
                        </a>
                        <a href="#" class="client-action-btn mb-0">
                            <i class="fas fa-certificate text-warning"></i> {{ __('crm.view_certifications') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Smart Grid Tabs (Bottom Row) -->
        <div class="row">
            <div class="col-12">
                <div class="bento-card mb-0">
                    <div class="d-flex justify-content-between align-items-center border-bottom px-4 pt-3 pb-0">
                        <h5 class="font-weight-bold mb-0" style="color:#1e293b; font-size: 1.1rem;">{{ __('crm.workspace') }}</h5>
                        <ul class="nav nav-tabs modern-tabs" id="crmActionGrid" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="tab-recent-complaints" data-toggle="tab" href="#pane-complaints" role="tab">⚠️ {{ __('crm.latest_complaints') }}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="tab-recent-feedback" data-toggle="tab" href="#pane-feedback" role="tab">💬 {{ __('crm.recent_feedback') }}</a>
                            </li>
                        </ul>
                    </div>
                    <div class="section-body p-0">
                        <div class="tab-content">
                            <!-- Complaints Grid -->
                            <div class="tab-pane fade show active p-0" id="pane-complaints" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table smart-table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th class="pl-4">{{ __('crm.complaint_id') }}</th>
                                                <th>{{ __('crm.received_date') }}</th>
                                                <th>{{ __('crm.description_status') }}</th>
                                                <th class="text-right pr-4">{{ __('crm.action') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($recentComplaints as $complaintItem)
                                            <tr>
                                                <td class="pl-4 font-weight-bold text-danger">#CMP-{{ $complaintItem->id }}</td>
                                                <td>{{ $complaintItem->created_at ? $complaintItem->created_at->format('M d, Y') : 'N/A' }}</td>
                                                <td>{{ __('crm.investigating_client_issue') }}</td>
                                                <td class="text-right pr-4"><a href="{{ route('crm.complaints-manager') }}" class="btn btn-sm btn-outline-primary rounded px-3">{{ __('crm.review') }}</a></td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="4" class="text-center py-4 text-muted">{{ __('crm.no_active_complaints') }} <i class="fas fa-glass-cheers text-success ml-1"></i></td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            <!-- Feedback Grid Placeholder -->
                            <div class="tab-pane fade p-0" id="pane-feedback" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table smart-table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th class="pl-4">{{ __('crm.client') }}</th>
                                                <th>{{ __('crm.satisfaction_score') }}</th>
                                                <th>{{ __('crm.feedback_date') }}</th>
                                                <th class="text-right pr-4">{{ __('crm.action') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr><td colspan="4" class="text-center py-4 text-muted">{{ __('crm.no_new_feedback_today') }}</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
