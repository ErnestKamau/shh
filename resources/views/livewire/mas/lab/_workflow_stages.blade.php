{{-- Workflow Stage Funnel: active batches, completed counts, avg completion per stage --}}
<div class="pivot-card">
    <div class="pivot-header d-flex justify-content-between align-items-center">
        <span><i class="mdi mdi-transit-connection-variant mr-1"></i> Workflow Stage Pipeline</span>
        <small class="font-weight-normal opacity-75">
            {{ $stats['summary']['active_batches'] ?? 0 }} active &bull;
            <span class="text-success">{{ $stats['summary']['completed_batches'] ?? 0 }} completed</span>
        </small>
    </div>
    <div class="table-responsive">
        <table class="pivot-table">
            <thead>
                <tr>
                    <th>Stage</th>
                    <th class="text-center">Batches</th>
                    <th class="text-center">Overdue</th>
                    <th class="text-center">Due Today</th>
                    <th class="text-center">Avg Days</th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($stats['stage_summary'] ?? [] as $stage)
                    @php
                        $total     = $stage['total_batches'];
                        $completed = $stage['completed_batches'] ?? max($total - ($stage['overdue_batches'] ?? 0), 0);
                        $overdue   = $stage['overdue_batches'];
                        $pct       = $stage['completion_rate'] ?? ($total > 0 ? round(($completed / $total) * 100) : 0);
                    @endphp
                    <tr>
                        <td class="font-weight-bold">{{ $stage['workflow_stage'] }}</td>
                        <td class="text-center">
                            <span class="badge badge-primary badge-pill">{{ number_format($total) }}</span>
                        </td>
                        <td class="text-center">
                            @if($overdue > 0)
                                <span class="badge badge-danger badge-pill">{{ $overdue }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($stage['due_today_batches'] > 0)
                                <span class="badge badge-warning badge-pill">{{ $stage['due_today_batches'] }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            {{ $stage['avg_completion_days'] ? number_format($stage['avg_completion_days'], 1) . 'd' : '—' }}
                        </td>
                        <td class="text-center">
                            @if($total > 0)
                                <div style="min-width:80px; height:8px; background:#f1f5f9; border-radius:4px; overflow:hidden; display:inline-block; width:80px;">
                                    <div style="width:{{ min($pct, 100) }}%; height:100%; background:#22c55e;"></div>
                                </div>
                                <small class="text-success ml-1">
                                    {{ number_format($completed) }}/{{ number_format($total) }} ({{ $pct }}%)
                                </small>
                            @else
                                <span class="text-muted small">No data</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-3">No workflow stage data available.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
