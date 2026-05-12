{{-- Analyst Performance Leaderboard --}}
<div class="pivot-card">
    <div class="pivot-header d-flex justify-content-between align-items-center">
        <span><i class="mdi mdi-account-star mr-1"></i> Analyst Performance</span>
        <small class="font-weight-normal opacity-75">Top analysts by on-time rate</small>
    </div>
    <div class="table-responsive">
        <table class="pivot-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Analyst</th>
                    <th class="text-center">Tests</th>
                    <th class="text-center">On-Time Rate</th>
                    <th class="text-center">Avg Offset (d)</th>
                    <th class="text-center">Performance</th>
                </tr>
            </thead>
            <tbody>
                @forelse($stats['analyst_performance'] ?? [] as $i => $analyst)
                    @php
                        $rate  = $analyst['on_time_rate'];
                        $label = $analyst['performance_label'];
                        $labelColor = match($label) {
                            'Excellent'       => 'success',
                            'On Track'        => 'primary',
                            'Behind Schedule' => 'warning',
                            default           => 'danger',
                        };
                    @endphp
                    <tr class="{{ $selectedAnalystId === $analyst['analyst_id'] ? 'table-active' : '' }}">
                        <td class="text-muted small">{{ $i + 1 }}</td>
                        <td class="font-weight-bold">{{ $analyst['name'] }}</td>
                        <td class="text-center">{{ number_format($analyst['total_tests']) }}</td>
                        <td class="text-center">
                            <div class="d-flex align-items-center justify-content-center" style="gap:6px;">
                                <div style="width:60px; height:6px; background:#f1f5f9; border-radius:3px; overflow:hidden;">
                                    <div style="width:{{ $rate }}%; height:100%; background:{{ $rate >= 80 ? '#22c55e' : ($rate >= 50 ? '#f59e0b' : '#ef4444') }};"></div>
                                </div>
                                <span class="small">{{ $rate }}%</span>
                            </div>
                        </td>
                        <td class="text-center {{ $analyst['avg_offset'] <= 0 ? 'text-success' : 'text-danger' }}">
                            {{ $analyst['avg_offset'] > 0 ? '+' : '' }}{{ $analyst['avg_offset'] }}
                        </td>
                        <td class="text-center">
                            <span class="badge badge-{{ $labelColor }}">{{ $label }}</span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-3">No analyst data for this period.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
