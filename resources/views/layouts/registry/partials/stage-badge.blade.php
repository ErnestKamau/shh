@php
    $stage = isset($stage) ? trim((string) $stage) : '';
    $badgeClass = match (true) {
        $stage === '' => 'reg-stage-badge--empty',
        $stage === 'registry' => 'reg-stage-badge--registry',
        $stage === 'investigation' => 'reg-stage-badge--investigation',
        $stage === 'director' => 'reg-stage-badge--director',
        $stage === 'lab_manager' => 'reg-stage-badge--lab_manager',
        $stage === 'lab_receiving' => 'reg-stage-badge--lab_receiving',
        $stage === 'customer_service' => 'reg-stage-badge--customer_service',
        $stage === 'sro' => 'reg-stage-badge--sro',
        $stage === 'analyst' => 'reg-stage-badge--analyst',
        $stage === 'qa' => 'reg-stage-badge--qa',
        $stage === 'resolution' => 'reg-stage-badge--resolution',
        $stage === 'closed' => 'reg-stage-badge--closed',
        $stage === 'completed' => 'reg-stage-badge--completed',
        default => 'reg-stage-badge--variant-' . (crc32(strtolower($stage)) % 6),
    };
    $label = $stage !== '' ? ucwords(str_replace('_', ' ', $stage)) : '—';
@endphp
<span class="reg-stage-badge {{ $badgeClass }}">
    <span class="reg-stage-badge__dot" aria-hidden="true"></span>
    {{ $label }}
</span>
