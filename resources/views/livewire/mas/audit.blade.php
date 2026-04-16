<div>

<div class="container-fluid py-4">
    <div class="row align-items-center mb-4">
        <div class="col">
            <h1 class="h3 font-weight-bold text-dark mb-1">{{ __('mas/audit.title') }}</h1>
            <p class="text-muted small mb-0">{{ __('mas/audit.subtitle') }}</p>
        </div>
        <div class="col-auto d-flex align-items-center">
            <a href="{{ route('mas.export', 'audit') }}" class="btn btn-success btn-sm mr-2">
                <i class="mdi mdi-download"></i> {{ __('mas/common.download_report') }}
            </a>
            <span class="badge badge-secondary p-2">
                <i class="mdi mdi-database-eye mr-1"></i> {{ __('mas/audit.total_events') }}: {{ $stats['total_events'] }}
            </span>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 15px; overflow: hidden;">
                <div class="card-header bg-dark text-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="m-0 font-weight-bold"><i class="mdi mdi-history mr-2"></i>{{ __('mas/audit.recent_activity') }}</h5>
                    <a href="/audit" class="btn btn-sm btn-outline-light">{{ __('mas/audit.advanced_search') }}</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>{{ __('mas/audit.event') }}</th>
                                <th>{{ __('mas/audit.entity') }}</th>
                                <th>{{ __('mas/audit.actor') }}</th>
                                <th>{{ __('mas/audit.ip_address') }}</th>
                                <th>{{ __('mas/audit.timestamp') }}</th>
                                <th>{{ __('mas/audit.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($stats['recent_audits'] as $log)
                                <tr>
                                    <td>
                                        <span class="badge badge-{{ $log->event == 'created' ? 'success' : ($log->event == 'deleted' ? 'danger' : 'info') }}">
                                            {{ strtoupper($log->event) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="font-weight-bold">{{ class_basename($log->auditable_type) }}</div>
                                        <div class="small text-muted">ID: {{ $log->auditable_id }}</div>
                                    </td>
                                    <td>{{ $log->user_id ? 'User #'.$log->user_id : __('mas/audit.system') }}</td>
                                    <td class="small text-muted font-italic">{{ $log->ip_address }}</td>
                                    <td>{{ date('M d, Y H:i:s', strtotime($log->created_at)) }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-light border p-1" title="{{ __('mas/audit.view_changes') }}">
                                            <i class="mdi mdi-eye-outline"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">{{ __('mas/common.no_data') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</div>