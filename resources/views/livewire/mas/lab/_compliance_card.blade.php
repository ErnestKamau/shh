{{-- % TAT Compliance --}}
<div class="pivot-card">
    <div class="pivot-header d-flex justify-content-between align-items-center">
        <span>% TAT Compliance</span>
        <small class="font-weight-normal opacity-75">
            @if(!empty($selectedLabId) && !empty($selectedZoneId))
                by Parameter
            @elseif(!empty($selectedLabId))
                by Zone
            @else
                by Lab Section
            @endif
        </small>
    </div>
    <div class="table-responsive">
        <table class="pivot-table">
            <tbody>
                @forelse($stats['pivot']['compliance_rows'] ?? [] as $row)
                    @php $rate = $row['compliance_rate'] ?? 0; @endphp
                    <tr>
                        <td style="max-width:180px;" class="font-weight-bold text-truncate">{{ $row['section'] }}</td>
                        <td>
                            <div class="compliance-bar-container" style="width: 100%;">
                                <div class="compliance-bar" style="width: {{ $rate }}%; background-color: {{ $rate >= 80 ? '#22c55e' : ($rate >= 50 ? '#f59e0b' : '#ef4444') }};"></div>
                                <div class="compliance-text">{{ $rate }}%</div>
                            </div>
                        </td>
                        <td class="text-right font-weight-bold" style="width:60px;">
                            <span class="badge badge-{{ $rate >= 80 ? 'success' : ($rate >= 50 ? 'warning' : 'danger') }}">{{ $rate }}%</span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center text-muted py-3">No compliance data available.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
