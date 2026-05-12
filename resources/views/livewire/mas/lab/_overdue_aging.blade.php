{{-- Overdue Batches + Aging Buckets --}}
<div class="row">

    {{-- Overdue Batches --}}
    <div class="col-md-7">
        <div class="pivot-card h-100">
            <div class="pivot-header d-flex justify-content-between align-items-center">
                <span><i class="mdi mdi-alert-circle-outline mr-1"></i> Overdue Batches</span>
                <small class="font-weight-normal opacity-75">Top by days overdue</small>
            </div>
            <div class="table-responsive">
                <table class="pivot-table">
                    <thead>
                        <tr>
                            <th>Batch Code</th>
                            <th>Stage</th>
                            <th class="text-center">Days Overdue</th>
                            <th class="text-center">Target Date</th>
                            <th class="text-center">Type</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stats['overdue_batches'] ?? [] as $batch)
                            <tr>
                                <td class="font-weight-bold text-primary">{{ $batch['batch_code'] }}</td>
                                <td><small>{{ $batch['workflow_stage'] }}</small></td>
                                <td class="text-center">
                                    <span class="badge badge-{{ $batch['days_overdue'] > 7 ? 'danger' : 'warning' }} badge-pill">
                                        +{{ $batch['days_overdue'] }}d
                                    </span>
                                </td>
                                <td class="text-center small">{{ $batch['target_date'] ? date('d M Y', strtotime($batch['target_date'])) : '—' }}</td>
                                <td class="text-center">
                                    @if($batch['is_qc_batch'])
                                        <span class="badge badge-info">QC</span>
                                    @else
                                        <span class="text-muted small">Routine</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-3">
                                    <i class="mdi mdi-check-circle text-success mdi-24px"></i><br>
                                    <span class="text-muted small">No overdue batches — all on track!</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Aging Buckets --}}
    <div class="col-md-5">
        <div class="pivot-card h-100">
            <div class="pivot-header">
                <i class="mdi mdi-timer-sand mr-1"></i> Batch Aging Distribution
            </div>
            <div class="p-3">
                @php $maxCount = max(collect($stats['aging_buckets'] ?? [])->pluck('batch_count')->toArray() ?: [1]); @endphp
                @forelse($stats['aging_buckets'] ?? [] as $bucket)
                    @php
                        $width  = $maxCount > 0 ? round(($bucket['batch_count'] / $maxCount) * 100) : 0;
                        $color  = match($bucket['aging_bucket']) {
                            'on_time'      => '#22c55e',
                            'due_today'    => '#f59e0b',
                            '1_3_overdue'  => '#fb923c',
                            '4_7_overdue'  => '#ef4444',
                            '8_plus_overdue' => '#991b1b',
                            default        => '#94a3b8',
                        };
                    @endphp
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <small class="font-weight-bold" style="color: #334155;">{{ $bucket['label'] }}</small>
                            <small class="font-weight-bold" style="color: {{ $color }};">{{ $bucket['batch_count'] }}</small>
                        </div>
                        <div style="height: 10px; background: #f1f5f9; border-radius: 5px; overflow: hidden;">
                            <div style="width: {{ $width }}%; height: 100%; background: {{ $color }}; border-radius: 5px; transition: width 0.5s ease;"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-muted text-center py-3 small">No aging data available.</p>
                @endforelse
            </div>
        </div>
    </div>

</div>
