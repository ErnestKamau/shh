@php
    $value = $value ?? '—';
    $type = $type ?? 'text';
    $title = $title ?? (is_scalar($value) ? (string) $value : '');
@endphp

@if($type === 'num')
    <td class="lab-kpi-col-num" title="{{ $title }}">{{ $value }}</td>
@elseif($type === 'date')
    <td class="lab-kpi-col-date" title="{{ $title }}">{{ $value }}</td>
@elseif($type === 'client')
    <td class="lab-kpi-col-client" title="{{ $title }}">{{ $value }}</td>
@elseif($type === 'id')
    <td class="lab-kpi-col-id" title="{{ $title }}">
        <span class="lab-kpi-cell-text">{{ $value }}</span>
    </td>
@elseif($type === 'status')
    @php
        $statusText = is_scalar($value) ? (string) $value : '—';
        $statusLower = strtolower($statusText);
        $badgeClass = 'lab-kpi-badge-muted';
        if (str_starts_with($statusLower, 'complete')) {
            $badgeClass = 'lab-kpi-badge-success';
        } elseif (str_starts_with($statusLower, 'partial')) {
            $badgeClass = 'lab-kpi-badge-warning';
        } elseif (str_contains($statusLower, 'pending') || str_contains($statusLower, 'verification')) {
            $badgeClass = 'lab-kpi-badge-info';
        } elseif (str_contains($statusLower, 'approval')) {
            $badgeClass = 'lab-kpi-badge-primary';
        }
    @endphp
    <td class="lab-kpi-col-status" title="{{ $title }}">
        <span class="lab-kpi-badge {{ $badgeClass }}">{{ $statusText }}</span>
    </td>
@elseif($type === 'stack')
    @php
        $parts = is_string($value) ? array_values(array_filter(array_map('trim', explode(' / ', $value)))) : [];
    @endphp
    <td class="lab-kpi-col-wide" title="{{ $title }}">
        @if(count($parts) > 1)
            <div class="lab-kpi-cell-stack">
                <span class="lab-kpi-cell-primary">{{ $parts[0] }}</span>
                <span class="lab-kpi-cell-secondary">{{ implode(' · ', array_slice($parts, 1)) }}</span>
            </div>
        @else
            <span class="lab-kpi-cell-text">{{ $value }}</span>
        @endif
    </td>
@else
    <td class="lab-kpi-col-text" title="{{ $title }}">
        <span class="lab-kpi-cell-text">{{ $value }}</span>
    </td>
@endif
