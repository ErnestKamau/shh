@php
    $status = (string) ($status ?? '');
    $badgeClass = match ($status) {
        'open' => 'reg-status-badge--open',
        'pending_approval' => 'reg-status-badge--pending',
        'returned' => 'reg-status-badge--returned',
        'closed' => 'reg-status-badge--closed',
        'cancelled' => 'reg-status-badge--cancelled',
        'draft' => 'reg-status-badge--draft',
        default => 'reg-status-badge--neutral',
    };
    $label = ucwords(str_replace('_', ' ', $status));
@endphp
<span class="reg-status-badge {{ $badgeClass }}">
    <span class="reg-status-badge__dot" aria-hidden="true"></span>
    {{ $label }}
</span>
