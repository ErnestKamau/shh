<div class="container-fluid px-2" wire:poll.15s="refreshMetrics">
    <style>
        .sys-admin-bg {
            background:
                radial-gradient(circle at 12% 8%, rgba(253, 224, 71, 0.18), transparent 42%),
                radial-gradient(circle at 88% 12%, rgba(14, 165, 233, 0.16), transparent 45%),
                linear-gradient(160deg, #f8fafc, #eef6ff 46%, #fdf5e8);
            border-radius: 16px;
            border: 1px solid rgba(15, 23, 42, 0.06);
        }

        .sys-admin-card {
            background: #ffffff;
            border: 1px solid rgba(15, 23, 42, 0.08);
            border-radius: 14px;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05);
            overflow: hidden;
        }

        .sys-admin-stat {
            border-radius: 12px;
            border: 1px solid rgba(15, 23, 42, 0.08);
            background: linear-gradient(135deg, #ffffff, #f8fafc);
        }

        .sys-admin-stat .value {
            font-size: 1.55rem;
            line-height: 1;
            font-weight: 700;
            color: #0f172a;
        }

        .sys-admin-pill {
            border-radius: 999px;
            font-size: 0.75rem;
            padding: 0.35rem 0.7rem;
            font-weight: 600;
        }

        .status-healthy { background-color: #dcfce7; color: #166534; }
        .status-degraded { background-color: #fff7d6; color: #9a6700; }
        .status-critical { background-color: #ffe4e6; color: #be123c; }

        .timeline-item {
            border-left: 2px solid #e2e8f0;
            padding-left: 0.8rem;
            margin-bottom: 0.75rem;
        }

        .mono {
            font-family: "JetBrains Mono", "Fira Code", Consolas, monospace;
            font-size: 0.8rem;
        }

        .chart-wrap {
            min-height: 220px;
        }

        .chart-wrap canvas {
            width: 100% !important;
            height: 220px !important;
        }

        .sys-admin-log-meta {
            background: linear-gradient(180deg, #fffdf7, #f8fafc);
        }

        .sys-admin-log-body {
            background: linear-gradient(180deg, #0f172a, #111827);
            color: #e2e8f0;
            max-height: 380px;
            overflow-y: auto;
        }

        .sys-admin-log-line {
            white-space: pre-wrap;
            word-break: break-word;
            line-height: 1.45;
            padding: 0.2rem 0;
            border-bottom: 1px solid rgba(148, 163, 184, 0.12);
        }

        .sys-admin-log-line:last-child {
            border-bottom: 0;
        }

        .sys-admin-connection-item {
            border: 1px solid rgba(15, 23, 42, 0.08);
            border-radius: 12px;
            background: linear-gradient(180deg, #ffffff, #f8fafc);
        }

        .sys-admin-driver-badge {
            border-radius: 999px;
            padding: 0.2rem 0.55rem;
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.04em;
        }

        .sys-admin-driver-pgsql {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .sys-admin-driver-mysql,
        .sys-admin-driver-mariadb {
            background: #dcfce7;
            color: #166534;
        }

        .sys-admin-driver-unknown {
            background: #e5e7eb;
            color: #374151;
        }

        .sys-admin-action-disabled {
            opacity: 0.65;
            cursor: not-allowed;
        }
    </style>

    @php
        $overview = $metrics['overview'] ?? [];
        $queue = $metrics['queue'] ?? [];
        $scheduler = $metrics['scheduler'] ?? [];
        $complaints = $metrics['complaints'] ?? [];
        $resolution = $metrics['complaint_resolution'] ?? [];
        $latency = $metrics['command_latency'] ?? [];
        $health = $metrics['health'] ?? [];
        $databaseConnections = $metrics['database_connections'] ?? [];
        $serviceIncidents = $metrics['service_incidents'] ?? [];

        $healthStatus = $health['status'] ?? 'healthy';
        $healthClass = 'status-' . $healthStatus;

        $incidentStatus = $serviceIncidents['status'] ?? 'healthy';
        $incidentClass = 'status-' . $incidentStatus;

        $configuredDb = (int) ($databaseConnections['configured_count'] ?? 0);
        $connectedDb = (int) ($databaseConnections['connected_count'] ?? 0);
        $disconnectedDb = (int) ($databaseConnections['disconnected_count'] ?? 0);
        $connectedDatabaseItems = $databaseConnections['connected'] ?? [];

        $logStatus = $logStream['status'] ?? 'missing';
        $logFileName = $logStream['file_name'] ?? null;
        $logUpdatedAt = $logStream['updated_at'] ?? null;
        $logLines = $logStream['lines'] ?? [];
    @endphp

    <div class="sys-admin-bg p-3 p-md-4">
        @if(session()->has('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if(session()->has('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="d-flex flex-wrap justify-content-between align-items-start mb-3">
            <div>
                <h4 class="mb-1"><i class="mdi mdi-monitor-dashboard text-primary"></i> {{ __('system.system_admin_dashboard') }}</h4>
                <p class="mb-0 text-muted">{{ __('system.system_admin_dashboard_blurb') }}</p>
            </div>
            <div class="d-flex align-items-center mt-2 mt-md-0">
                <span class="sys-admin-pill {{ $healthClass }} mr-2 text-uppercase">{{ __('system.' . $healthStatus) }}</span>
                <small class="text-muted">{{ __('system.last_updated') }}: {{ $metrics['generated_at'] ?? now()->toDateTimeString() }}</small>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-6 col-lg-3 col-xl-2 mb-3">
                <div class="p-3 sys-admin-stat h-100">
                    <small class="text-muted d-block">{{ __('system.users') }}</small>
                    <div class="value">{{ number_format((int) ($overview['users_total'] ?? 0)) }}</div>
                </div>
            </div>
            <div class="col-6 col-lg-3 col-xl-2 mb-3">
                <div class="p-3 sys-admin-stat h-100">
                    <small class="text-muted d-block">{{ __('system.active_modules') }}</small>
                    <div class="value">{{ number_format((int) ($overview['active_modules'] ?? 0)) }}</div>
                </div>
            </div>
            <div class="col-6 col-lg-3 col-xl-2 mb-3">
                <div class="p-3 sys-admin-stat h-100">
                    <small class="text-muted d-block">{{ __('system.pending_jobs') }}</small>
                    <div class="value">{{ number_format((int) ($overview['pending_jobs'] ?? 0)) }}</div>
                </div>
            </div>
            <div class="col-6 col-lg-3 col-xl-2 mb-3">
                <div class="p-3 sys-admin-stat h-100">
                    <small class="text-muted d-block">{{ __('system.failed_jobs') }}</small>
                    <div class="value">{{ number_format((int) ($overview['failed_jobs'] ?? 0)) }}</div>
                </div>
            </div>
            <div class="col-6 col-lg-3 col-xl-2 mb-3">
                <div class="p-3 sys-admin-stat h-100">
                    <small class="text-muted d-block">{{ __('system.open_complaints') }}</small>
                    <div class="value">{{ number_format((int) ($overview['open_complaints'] ?? 0)) }}</div>
                </div>
            </div>
            <div class="col-6 col-lg-3 col-xl-2 mb-3">
                <div class="p-3 sys-admin-stat h-100">
                    <small class="text-muted d-block">{{ __('system.connected_databases') }}</small>
                    <div class="value">{{ number_format((int) ($overview['connected_databases'] ?? 0)) }}/{{ number_format((int) ($overview['configured_databases'] ?? 0)) }}</div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-4 mb-3">
                <div class="sys-admin-card h-100">
                    <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                        <strong>{{ __('system.queue_observability') }}</strong>
                        @if($this->canManageActions)
                            <button class="btn btn-sm btn-outline-danger" wire:click="retryFailedJobs">{{ __('system.retry_failed_jobs') }}</button>
                        @endif
                    </div>
                    <div class="p-3">
                        <div class="d-flex justify-content-between mb-2"><span>{{ __('system.failed_jobs') }}</span><strong>{{ (int) ($queue['failed_jobs'] ?? 0) }}</strong></div>
                        <div class="d-flex justify-content-between mb-2"><span>{{ __('system.failed_last_hour') }}</span><strong>{{ (int) ($queue['failed_last_hour'] ?? 0) }}</strong></div>
                        <div class="d-flex justify-content-between mb-2"><span>{{ __('system.pending_jobs') }}</span><strong>{{ (int) ($queue['pending_jobs'] ?? 0) }}</strong></div>
                        <div class="d-flex justify-content-between"><span>{{ __('system.reserved_jobs') }}</span><strong>{{ (int) ($queue['reserved_jobs'] ?? 0) }}</strong></div>

                        <hr>
                        <small class="text-muted d-block mb-2">{{ __('system.queue_breakdown') }}</small>
                        @forelse(($queue['queue_breakdown'] ?? []) as $queueItem)
                            <div class="d-flex justify-content-between mono mb-1">
                                <span>{{ $queueItem['queue'] }}</span>
                                <span>{{ $queueItem['total'] }}</span>
                            </div>
                        @empty
                            <small class="text-muted">{{ __('system.no_queue_data') }}</small>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="col-xl-4 mb-3">
                <div class="sys-admin-card h-100">
                    <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                        <strong>{{ __('system.scheduler_watch') }}</strong>
                        @if($this->canManageActions)
                            <button class="btn btn-sm btn-outline-primary" wire:click="runSchedulerNow">{{ __('system.run_scheduler_now') }}</button>
                        @endif
                    </div>
                    <div class="p-3">
                        <div class="d-flex justify-content-between mb-3"><span>{{ __('system.total_scheduled_events') }}</span><strong>{{ (int) ($scheduler['total_events'] ?? 0) }}</strong></div>

                        @forelse(($scheduler['next_runs'] ?? []) as $event)
                            <div class="timeline-item">
                                <div class="font-weight-bold small">{{ $event['summary'] }}</div>
                                <div class="mono text-muted">{{ $event['expression'] }}</div>
                                <small class="text-muted">{{ __('system.next_run') }}: {{ $event['next_run'] ?? __('system.unavailable') }}</small>
                            </div>
                        @empty
                            <small class="text-muted">{{ __('system.no_scheduler_data') }}</small>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="col-xl-4 mb-3">
                <div class="sys-admin-card h-100">
                    <div class="p-3 border-bottom">
                        <strong>{{ __('system.complaint_oversight') }}</strong>
                    </div>
                    <div class="p-3">
                        <div class="d-flex justify-content-between mb-2"><span>{{ __('system.total_complaints') }}</span><strong>{{ (int) ($complaints['total_count'] ?? 0) }}</strong></div>
                        <div class="d-flex justify-content-between mb-2"><span>{{ __('system.open_complaints') }}</span><strong>{{ (int) ($complaints['open_count'] ?? 0) }}</strong></div>
                        <div class="d-flex justify-content-between mb-2"><span>{{ __('system.high_priority_open') }}</span><strong>{{ (int) ($complaints['high_priority_open'] ?? 0) }}</strong></div>
                        <div class="d-flex justify-content-between"><span>{{ __('system.unresolved_48h') }}</span><strong>{{ (int) ($complaints['unresolved_older_48h'] ?? 0) }}</strong></div>

                        <hr>
                        <small class="text-muted d-block mb-2">{{ __('system.recent_complaints') }}</small>
                        @forelse(($complaints['recent'] ?? []) as $row)
                            <div class="d-flex justify-content-between mb-1 mono">
                                <span>#{{ $row['complaint_id'] }}</span>
                                <span class="text-capitalize">{{ $row['priority'] }}</span>
                            </div>
                        @empty
                            <small class="text-muted">{{ __('system.no_complaints_data') }}</small>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-4 mb-3">
                <div class="sys-admin-card h-100">
                    <div class="p-3 border-bottom"><strong>{{ __('system.complaint_resolution_queue') }}</strong></div>
                    <div class="p-3">
                        <div class="d-flex justify-content-between mb-2"><span>{{ __('system.open_complaints') }}</span><strong>{{ (int) ($resolution['open_total'] ?? 0) }}</strong></div>
                        <div class="d-flex justify-content-between mb-2"><span>{{ __('system.stalled_24h') }}</span><strong>{{ (int) ($resolution['stalled_24h'] ?? 0) }}</strong></div>
                        <div class="d-flex justify-content-between mb-2"><span>{{ __('system.overdue_sla') }}</span><strong>{{ (int) ($resolution['overdue_sla'] ?? 0) }}</strong></div>
                        <div class="d-flex justify-content-between"><span>{{ __('system.escalated_open') }}</span><strong>{{ (int) ($resolution['escalated_open'] ?? 0) }}</strong></div>

                        <hr>
                        <small class="text-muted d-block mb-2">{{ __('system.stage_breakdown') }}</small>
                        @forelse(($resolution['stage_breakdown'] ?? []) as $stage)
                            <div class="d-flex justify-content-between mono mb-1">
                                <span>{{ $stage['stage'] }}</span>
                                <span>{{ $stage['value'] }}</span>
                            </div>
                        @empty
                            <small class="text-muted">{{ __('system.no_complaints_data') }}</small>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="col-lg-4 mb-3">
                <div class="sys-admin-card h-100">
                    <div class="p-3 border-bottom"><strong>{{ __('system.command_latency') }}</strong></div>
                    <div class="p-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span>{{ __('system.avg_wait_time') }}</span>
                            <strong>{{ (int) ($latency['avg_wait_seconds'] ?? 0) }}s</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>{{ __('system.oldest_pending_job') }}</span>
                            <strong>{{ (int) round(((int) ($latency['oldest_pending_seconds'] ?? 0)) / 60) }}m</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>{{ __('system.failed_last_24_hours') }}</span>
                            <strong>{{ (int) ($latency['failed_last_24h'] ?? 0) }}</strong>
                        </div>

                        <hr>
                        <small class="text-muted d-block mb-2">{{ __('system.backlog_distribution') }}</small>
                        @forelse(($latency['backlog_buckets'] ?? []) as $bucket)
                            <div class="d-flex justify-content-between mono mb-1">
                                <span>{{ $bucket['label'] }}</span>
                                <span>{{ $bucket['value'] }}</span>
                            </div>
                        @empty
                            <small class="text-muted">{{ __('system.no_queue_data') }}</small>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="col-lg-4 mb-3">
                <div class="sys-admin-card h-100">
                    <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                        <strong>{{ __('system.service_incidents') }}</strong>
                        <span class="sys-admin-pill {{ $incidentClass }} text-uppercase">{{ __('system.' . $incidentStatus) }}</span>
                    </div>
                    <div class="p-3">
                        <div class="d-flex justify-content-between mb-2"><span>{{ __('system.incident_severity') }}</span><strong>{{ (int) ($serviceIncidents['severity_score'] ?? 0) }}</strong></div>
                        <div class="d-flex justify-content-between mb-2"><span>{{ __('system.open_signals') }}</span><strong>{{ (int) ($serviceIncidents['open_signals'] ?? 0) }}</strong></div>

                        <hr>
                        <small class="text-muted d-block mb-2">{{ __('system.recent_signals') }}</small>
                        @forelse(($serviceIncidents['recent'] ?? []) as $signal)
                            <div class="d-flex justify-content-between mono mb-1">
                                <span>{{ $signal['label'] }}</span>
                                <span>{{ $signal['value'] }}</span>
                            </div>
                        @empty
                            <small class="text-muted">{{ __('system.no_recent_signals') }}</small>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-7 mb-3">
                <div class="sys-admin-card h-100">
                    <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                        <strong>{{ __('system.database_export') }}</strong>
                        <span class="badge badge-light">{{ __('system.database') }}: {{ config('database.default') }}</span>
                    </div>
                    <div class="p-3">
                        <p class="text-muted mb-3">{{ __('system.download_whole_database_blurb') }}</p>
                        <div class="d-flex flex-wrap">
                            @if($this->canExport)
                                <a href="{{ route('system-settings.database-export', ['format' => 'sql']) }}" class="btn btn-outline-primary mr-2 mb-2">
                                    <i class="mdi mdi-download"></i> {{ __('system.download_sql_dump') }}
                                </a>
                                <a href="{{ route('system-settings.database-export', ['format' => 'dump']) }}" class="btn btn-outline-dark mb-2">
                                    <i class="mdi mdi-database-export"></i> {{ __('system.download_custom_dump') }}
                                </a>
                            @else
                                <small class="text-muted">{{ __('system.no_permission') }}</small>
                            @endif
                        </div>

                        @if($this->canExport)
                            <hr>
                            <small class="text-muted d-block mb-2">{{ __('system.connected_databases_export') }}</small>
                            @forelse($connectedDatabaseItems as $connection)
                                @include('livewire.system.partials.database-connection-export-row', ['connection' => $connection, 'canExport' => $this->canExport])
                            @empty
                                <small class="text-muted">{{ __('system.no_disconnected_databases') }}</small>
                            @endforelse
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-xl-5 mb-3">
                <div class="sys-admin-card h-100">
                    <div class="p-3 border-bottom"><strong>{{ __('system.database_connections') }}</strong></div>
                    <div class="p-3">
                        <div class="d-flex justify-content-between mb-2"><span>{{ __('system.configured_databases') }}</span><strong>{{ $configuredDb }}</strong></div>
                        <div class="d-flex justify-content-between mb-2"><span>{{ __('system.connected_databases') }}</span><strong>{{ $connectedDb }}</strong></div>
                        <div class="d-flex justify-content-between mb-2"><span>{{ __('system.disconnected_databases') }}</span><strong>{{ $disconnectedDb }}</strong></div>

                        <hr>
                        <small class="text-muted d-block mb-2">{{ __('system.connected_databases_export') }}</small>
                        @forelse($connectedDatabaseItems as $connection)
                            @include('livewire.system.partials.database-connection-export-row', ['connection' => $connection, 'canExport' => $this->canExport])
                        @empty
                            <small class="text-muted d-block mb-3">{{ __('system.no_disconnected_databases') }}</small>
                        @endforelse

                        <hr>
                        <small class="text-muted d-block mb-2">{{ __('system.disconnected_connections') }}</small>
                        @forelse(($databaseConnections['disconnected'] ?? []) as $item)
                            <div class="mb-2">
                                <div class="d-flex align-items-center mb-1">
                                    <div class="font-weight-bold small mr-2">{{ $item['name'] }}</div>
                                    <span class="sys-admin-driver-badge sys-admin-driver-{{ in_array(($item['driver'] ?? 'unknown'), ['pgsql', 'mysql', 'mariadb'], true) ? ($item['driver'] ?? 'unknown') : 'unknown' }}">{{ strtoupper($item['driver'] ?? 'n/a') }}</span>
                                </div>
                                <div class="text-muted small">{{ $item['database'] ?? 'n/a' }}</div>
                                <div class="text-muted small">{{ $item['reason'] }}</div>
                            </div>
                        @empty
                            <small class="text-muted">{{ __('system.no_disconnected_databases') }}</small>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6 mb-3">
                <div class="sys-admin-card">
                    <div class="p-3 border-bottom"><strong>{{ __('system.resolution_stage_chart') }}</strong></div>
                    <div class="p-3 chart-wrap" wire:ignore>
                        <canvas id="complaintResolutionChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mb-3">
                <div class="sys-admin-card">
                    <div class="p-3 border-bottom"><strong>{{ __('system.failure_trend_chart') }}</strong></div>
                    <div class="p-3 chart-wrap" wire:ignore>
                        <canvas id="commandFailuresChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mb-3">
                <div class="sys-admin-card">
                    <div class="p-3 border-bottom"><strong>{{ __('system.db_connection_chart') }}</strong></div>
                    <div class="p-3 chart-wrap" wire:ignore>
                        <canvas id="databaseConnectionsChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mb-3">
                <div class="sys-admin-card">
                    <div class="p-3 border-bottom"><strong>{{ __('system.incident_signals_chart') }}</strong></div>
                    <div class="p-3 chart-wrap" wire:ignore>
                        <canvas id="incidentSignalsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="sys-admin-card mb-3" wire:poll.5s="refreshLogStream">
                    <div class="p-3 border-bottom d-flex flex-wrap justify-content-between align-items-center">
                        <div>
                            <strong>Laravel Log Stream</strong>
                            <div class="text-muted small">Streaming the latest entries from the current application log.</div>
                        </div>
                        <div class="text-md-right mt-2 mt-md-0">
                            @if($logFileName)
                                <div class="mono">{{ $logFileName }}</div>
                            @endif
                            <small class="text-muted">Auto-refreshing every 5 seconds</small>
                        </div>
                    </div>
                    <div class="p-3 border-bottom sys-admin-log-meta">
                        <div class="row">
                            <div class="col-md-6 mb-2 mb-md-0">
                                <small class="text-muted d-block">Log File</small>
                                <span class="mono">{{ $logFileName ?? 'Unavailable' }}</span>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted d-block">Last Modified</small>
                                <span class="mono">{{ $logUpdatedAt ?? 'Unavailable' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="sys-admin-log-body p-3 mono">
                        @if($logStatus === 'ok')
                            @forelse($logLines as $line)
                                @php
                                    $logLineClass = 'text-light';

                                    if (stripos($line, 'critical') !== false || stripos($line, 'emergency') !== false || stripos($line, '.ERROR:') !== false || stripos($line, ' error ') !== false) {
                                        $logLineClass = 'text-danger';
                                    } elseif (stripos($line, 'warning') !== false) {
                                        $logLineClass = 'text-warning';
                                    } elseif (stripos($line, 'info') !== false || stripos($line, 'notice') !== false || stripos($line, 'debug') !== false) {
                                        $logLineClass = 'text-info';
                                    }
                                @endphp
                                <div class="sys-admin-log-line {{ $logLineClass }}">@if($line === '')&nbsp;@else{{ $line }}@endif</div>
                            @empty
                                <div class="text-muted">The current Laravel log file is available, but it does not have any entries yet.</div>
                            @endforelse
                        @elseif($logStatus === 'unreadable')
                            <div class="text-warning">The current Laravel log exists, but the application cannot read it.</div>
                        @else
                            <div class="text-muted">No Laravel log file is currently available in storage/logs.</div>
                        @endif
                    </div>
                </div>

                <div class="sys-admin-card">
                    <div class="p-3 border-bottom"><strong>{{ __('system.system_health') }}</strong></div>
                    <div class="p-3">
                        <div class="row">
                            <div class="col-md-3 mb-2">
                                <small class="text-muted d-block">{{ __('system.database') }}</small>
                                <span class="badge badge-{{ ($health['database_reachable'] ?? false) ? 'success' : 'danger' }}">{{ ($health['database_reachable'] ?? false) ? __('system.reachable') : __('system.unreachable') }}</span>
                            </div>
                            <div class="col-md-3 mb-2">
                                <small class="text-muted d-block">{{ __('system.storage') }}</small>
                                <span class="badge badge-{{ ($health['storage_writable'] ?? false) ? 'success' : 'danger' }}">{{ ($health['storage_writable'] ?? false) ? __('system.writable') : __('system.not_writable') }}</span>
                            </div>
                            <div class="col-md-2 mb-2">
                                <small class="text-muted d-block">{{ __('system.queue_driver') }}</small>
                                <span class="mono">{{ $health['queue_driver'] ?? 'n/a' }}</span>
                            </div>
                            <div class="col-md-2 mb-2">
                                <small class="text-muted d-block">{{ __('system.broadcast_driver') }}</small>
                                <span class="mono">{{ $health['broadcast_driver'] ?? 'n/a' }}</span>
                            </div>
                            <div class="col-md-2 mb-2">
                                <small class="text-muted d-block">{{ __('system.cache_store') }}</small>
                                <span class="mono">{{ $health['cache_store'] ?? 'n/a' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:init', () => {
            if (window.systemAdminChartsInitialized) {
                return;
            }

            window.systemAdminChartsInitialized = true;
            window.systemAdminCharts = window.systemAdminCharts || {};

            const initialData = @json($metrics['charts'] ?? []);

            const ensureChartJs = (callback) => {
                if (window.Chart) {
                    callback();
                    return;
                }

                const existing = document.getElementById('system-admin-chartjs-loader');
                if (existing) {
                    existing.addEventListener('load', callback, { once: true });
                    return;
                }

                const script = document.createElement('script');
                script.id = 'system-admin-chartjs-loader';
                script.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js';
                script.onload = callback;
                document.head.appendChild(script);
            };

            const renderOrUpdateChart = (key, canvasId, configBuilder) => {
                const canvas = document.getElementById(canvasId);
                if (!canvas || !window.Chart) {
                    return;
                }

                if (window.systemAdminCharts[key]) {
                    window.systemAdminCharts[key].destroy();
                }

                window.systemAdminCharts[key] = new window.Chart(canvas.getContext('2d'), configBuilder());
            };

            const renderCharts = (chartData) => {
                ensureChartJs(() => {
                    const stageChart = chartData.complaint_resolution_stages || { labels: [], values: [] };
                    const failuresChart = chartData.command_failures_trend || { labels: [], values: [] };
                    const dbChart = chartData.database_connections || { labels: [], values: [] };
                    const signalsChart = chartData.incident_signals || { labels: [], values: [] };

                    renderOrUpdateChart('resolution', 'complaintResolutionChart', () => ({
                        type: 'bar',
                        data: {
                            labels: stageChart.labels,
                            datasets: [{
                                label: '{{ __('system.open_complaints') }}',
                                data: stageChart.values,
                                backgroundColor: '#0ea5e9',
                                borderRadius: 6,
                            }],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false } },
                            scales: { y: { beginAtZero: true } },
                        },
                    }));

                    renderOrUpdateChart('failures', 'commandFailuresChart', () => ({
                        type: 'line',
                        data: {
                            labels: failuresChart.labels,
                            datasets: [{
                                label: '{{ __('system.failed_jobs') }}',
                                data: failuresChart.values,
                                borderColor: '#ef4444',
                                backgroundColor: 'rgba(239, 68, 68, 0.15)',
                                fill: true,
                                tension: 0.3,
                            }],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: { y: { beginAtZero: true } },
                        },
                    }));

                    renderOrUpdateChart('dbConnections', 'databaseConnectionsChart', () => ({
                        type: 'doughnut',
                        data: {
                            labels: dbChart.labels,
                            datasets: [{
                                data: dbChart.values,
                                backgroundColor: ['#10b981', '#f97316'],
                                borderWidth: 2,
                                borderColor: '#ffffff',
                            }],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { position: 'bottom' } },
                        },
                    }));

                    renderOrUpdateChart('signals', 'incidentSignalsChart', () => ({
                        type: 'bar',
                        data: {
                            labels: signalsChart.labels,
                            datasets: [{
                                label: '{{ __('system.open_signals') }}',
                                data: signalsChart.values,
                                backgroundColor: ['#ef4444', '#f97316', '#eab308', '#3b82f6'],
                            }],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false } },
                            scales: { y: { beginAtZero: true } },
                        },
                    }));
                });
            };

            renderCharts(initialData);

            Livewire.on('system-dashboard-charts-refresh', ({ chartData }) => {
                renderCharts(chartData || {});
            });
        });
    </script>
</div>
