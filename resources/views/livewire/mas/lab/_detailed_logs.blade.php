{{-- Detailed TAT Logs: paginated Lab No grouped log table --}}
@php
    $rows = $detailedLogs['rows'] ?? [];
    $groupedRows = $detailedLogs['grouped_rows'] ?? [];
    $totalLogs = $detailedLogs['total'] ?? 0;
    $sourceTotalLogs = $detailedLogs['source_total'] ?? $totalLogs;
    $isCapped = $detailedLogs['is_capped'] ?? false;
    $detailedPage = $detailedLogs['page'] ?? 1;
    $perPage = $detailedLogs['per_page'] ?? 10;
    $totalPages = $detailedLogs['total_pages'] ?? 1;
@endphp
<div class="pivot-card">
    <div class="pivot-header d-flex justify-content-between align-items-center">
        <span><i class="mdi mdi-clipboard-list-outline mr-1"></i> Detailed TAT Logs</span>
        <small class="font-weight-normal opacity-75">
            {{ $isCapped ? 'Top ' : '' }}{{ $totalLogs }} records
            @if($isCapped)
                of {{ $sourceTotalLogs }}
            @endif
            &bull; Page {{ $detailedPage }} of {{ $totalPages }}
        </small>
    </div>
    <div class="table-responsive">
        <table class="pivot-table">
            <thead>
                <tr>
                    <th>Lab No</th>
                    <th>Sample No</th>
                    <th>Sample Type</th>
                    <th>Analysis</th>
                    <th>Parameter</th>
                    <th class="text-center">Expected</th>
                    <th class="text-center">Actual</th>
                    <th class="text-center">Offset</th>
                    <th>Analyst</th>
                </tr>
            </thead>
            <tbody>
                @forelse($groupedRows as $labGroup)
                    <tr>
                        <td colspan="9" class="font-weight-bold text-primary" style="background:#eff6ff; border-left:4px solid #2563eb;">
                            Lab No: {{ $labGroup['lab_no'] ?? 'N/A' }}
                        </td>
                    </tr>
                    @foreach($labGroup['samples'] ?? [] as $sampleGroup)
                        @foreach($sampleGroup['analysis_groups'] ?? [] as $analysisGroup)
                            @foreach($analysisGroup['parameters'] ?? [] as $parameter)
                                @php $offset = (int) ($parameter['offset'] ?? 0); @endphp
                                <tr>
                                    <td></td>
                                    <td class="font-weight-bold small">{{ $sampleGroup['sample_code'] ?? 'N/A' }}</td>
                                    <td class="small">{{ $analysisGroup['sample_type'] ?? 'N/A' }}</td>
                                    <td class="small">{{ $analysisGroup['analysis_type'] ?? 'N/A' }}</td>
                                    <td class="font-weight-bold text-primary small">{{ $parameter['parameter'] ?? 'N/A' }}</td>
                                    <td class="text-center small">{{ $parameter['expected_date'] ?? '-' }}</td>
                                    <td class="text-center small">{{ $parameter['actual_date'] ?? '-' }}</td>
                                    <td class="text-center">
                                        @if($offset < 0)
                                            <span class="badge badge-success">{{ $offset }}d</span>
                                        @elseif($offset === 0)
                                            <span class="badge badge-success">0d</span>
                                        @elseif($offset <= 2)
                                            <span class="badge badge-warning">+{{ $offset }}d</span>
                                        @else
                                            <span class="badge badge-danger">+{{ $offset }}d</span>
                                        @endif
                                    </td>
                                    <td class="small">{{ $parameter['analyst'] ?? 'Unassigned' }}</td>
                                </tr>
                            @endforeach
                        @endforeach
                    @endforeach
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
