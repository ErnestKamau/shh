{{-- Detailed TAT Logs: paginated analyte-level log table --}}
@php
    $rows = $detailedLogs['rows'] ?? [];
    $totalLogs = $detailedLogs['total'] ?? 0;
    $detailedPage = $detailedLogs['page'] ?? 1;
    $perPage = $detailedLogs['per_page'] ?? 10;
    $totalPages = $detailedLogs['total_pages'] ?? 1;
@endphp
<div class="pivot-card">
    <div class="pivot-header d-flex justify-content-between align-items-center">
        <span><i class="mdi mdi-clipboard-list-outline mr-1"></i> Detailed TAT Logs</span>
        <small class="font-weight-normal opacity-75">
            {{ $totalLogs }} records &bull; Page {{ $detailedPage }} of {{ $totalPages }}
        </small>
    </div>
    <div class="table-responsive">
        <table class="pivot-table">
            <thead>
                <tr>
                    <th>Analyte</th>
                    <th>Sample Code</th>
                    <th>Type</th>
                    <th class="text-center">Received</th>
                    <th class="text-center">Expected</th>
                    <th class="text-center">Completed</th>
                    <th class="text-center">Offset</th>
                    <th>Analyst</th>
                    <th>Remark</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $log)
                    @php $offset = $log['offset']; @endphp
                    <tr>
                        <td class="font-weight-bold">{{ $log['analyte'] }}</td>
                        <td class="text-primary">{{ $log['sample_code'] }}</td>
                        <td><small>{{ $log['sample_type'] }}</small></td>
                        <td class="text-center small">{{ $log['receipt_date'] }}</td>
                        <td class="text-center small">{{ $log['expected_date'] }}</td>
                        <td class="text-center small">{{ $log['actual_date'] }}</td>
                        <td class="text-center">
                            @if($offset <= 0)
                                <span class="badge badge-success">{{ $offset }}d</span>
                            @elseif($offset <= 2)
                                <span class="badge badge-warning">+{{ $offset }}d</span>
                            @else
                                <span class="badge badge-danger">+{{ $offset }}d</span>
                            @endif
                        </td>
                        <td class="small">{{ $log['analyst'] }}</td>
                        <td class="small text-muted">{{ $log['remark'] ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">
                            <i class="mdi mdi-database-off mdi-24px d-block mb-1"></i>
                            No TAT log records for this period and filter.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($totalLogs > $perPage)
        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top">
            <small class="text-muted">
                Showing {{ (($detailedPage - 1) * $perPage) + 1 }}–{{ min($detailedPage * $perPage, $totalLogs) }}
                of {{ $totalLogs }}
            </small>
            <div class="btn-group btn-group-sm">
                @if($detailedPage > 1)
                    <button wire:click="setDetailedPage({{ $detailedPage - 1 }})" class="btn btn-outline-secondary">
                        <i class="mdi mdi-chevron-left"></i>
                    </button>
                @endif
                @for($p = 1; $p <= $totalPages; $p++)
                    <button wire:click="setDetailedPage({{ $p }})"
                        class="btn btn-{{ $p === $detailedPage ? 'primary' : 'outline-secondary' }}">{{ $p }}</button>
                @endfor
                @if($detailedPage < $totalPages)
                    <button wire:click="setDetailedPage({{ $detailedPage + 1 }})" class="btn btn-outline-secondary">
                        <i class="mdi mdi-chevron-right"></i>
                    </button>
                @endif
            </div>
        </div>
    @endif
</div>
