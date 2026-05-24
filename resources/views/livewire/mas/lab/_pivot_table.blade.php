{{-- Pivot Table: Parameters Tested by Section × Month, plus % TAT Compliance bars --}}
@php
    $pivotRows = $stats['pivot']['rows'] ?? [];
    $totalPivot = $stats['pivot']['total'] ?? 0;
    $pivotPage = $stats['pivot']['page'] ?? 1;
    $perPivotPage = $stats['pivot']['per_page'] ?? 10;
    $totalPivotPages = $stats['pivot']['total_pages'] ?? 1;
@endphp

{{-- Parameters Tested --}}
<div class="pivot-card mb-4">
    <div class="pivot-header d-flex justify-content-between align-items-center">
        <span>Number of Parameters Tested</span>
        <small class="font-weight-normal opacity-75">
            {{ $totalPivot }} parameters &bull; Page {{ $pivotPage }} of {{ $totalPivotPages }}
        </small>
    </div>
    <div class="table-responsive">
        <table class="pivot-table">
            <thead>
                <tr>
                    <th style="min-width:160px;">{{ $stats['pivot']['row_label'] ?? 'Lab Section' }}</th>
                    @foreach($stats['pivot']['headers'] ?? [] as $header)
                        <th class="text-center">{{ $header }}</th>
                    @endforeach
                    <th class="text-right">Grand Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pivotRows as $row)
                    <tr>
                        <td class="font-weight-bold">{{ $row['section'] ?? $row['name'] ?? 'N/A' }}</td>
                        @foreach($stats['pivot']['headers'] ?? [] as $header)
                            <td class="text-center">{{ number_format($row['months'][$header] ?? 0) }}</td>
                        @endforeach
                        <td class="text-right font-weight-bold text-primary">{{ number_format($row['total']) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="99" class="text-center text-muted py-3">No parameter data for this period.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($totalPivot > $perPivotPage)
        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top">
            <small class="text-muted">
                Showing {{ (($pivotPage - 1) * $perPivotPage) + 1 }}–{{ min($pivotPage * $perPivotPage, $totalPivot) }}
                of {{ $totalPivot }}
            </small>
            <div class="btn-group btn-group-sm">
                @if($pivotPage > 1)
                    <button wire:click="setPivotPage({{ $pivotPage - 1 }})" class="btn btn-outline-secondary">
                        <i class="mdi mdi-chevron-left"></i>
                    </button>
                @endif
                @php
                    $lastWasDots = false;
                @endphp
                @for($p = 1; $p <= $totalPivotPages; $p++)
                    @if($totalPivotPages <= 8 || abs($p - $pivotPage) <= 2 || $p == 1 || $p == $totalPivotPages)
                        <button wire:click="setPivotPage({{ $p }})"
                            class="btn btn-{{ $p === $pivotPage ? 'primary' : 'outline-secondary' }}">{{ $p }}</button>
                        @php $lastWasDots = false; @endphp
                    @else
                        @if(!$lastWasDots)
                            <span class="px-2 text-muted" style="align-self: center;">...</span>
                            @php $lastWasDots = true; @endphp
                        @endif
                    @endif
                @endfor
                @if($pivotPage < $totalPivotPages)
                    <button wire:click="setPivotPage({{ $pivotPage + 1 }})" class="btn btn-outline-secondary">
                        <i class="mdi mdi-chevron-right"></i>
                    </button>
                @endif
            </div>
        </div>
    @endif
</div>

