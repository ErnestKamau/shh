@php
    $chipAccent = match ($status) {
        'pending' => '#f59e0b',
        'approved' => '#16a34a',
        'rejected' => '#dc3545',
        'cancelled' => '#64748b',
        default => '#64748b',
    };
@endphp
<span class="workflow-status-chip" style="--chip-accent: {{ $chipAccent }};">{{ ucfirst($status) }}</span>
