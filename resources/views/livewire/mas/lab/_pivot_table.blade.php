{{-- Pivot Table: Parameters Tested by Section × Month, plus % TAT Compliance bars --}}

{{-- Parameters Tested --}}
<div class="pivot-card mb-4">
    <div class="pivot-header d-flex justify-content-between align-items-center">
        <span>Number of Parameters Tested</span>
        <small class="font-weight-normal opacity-75">{{ $stats['pivot']['subtitle'] ?? 'Last 6 Months by Lab Section' }}</small>
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
                @forelse($stats['pivot']['rows'] ?? [] as $row)
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
</div>

{{-- % TAT Compliance per Section --}}
<div class="pivot-card">
    <div class="pivot-header">% TAT Compliance by Lab Section</div>
    <div class="table-responsive">
        <table class="pivot-table">
            <tbody>
                @forelse($stats['pivot']['compliance_rows'] ?? [] as $row)
                    @php $rate = $row['compliance_rate'] ?? 0; @endphp
                    <tr>
                        <td style="width:180px;" class="font-weight-bold">{{ $row['section'] }}</td>
                        <td>
                            <div class="compliance-bar-container" style="width: 200px;">
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
