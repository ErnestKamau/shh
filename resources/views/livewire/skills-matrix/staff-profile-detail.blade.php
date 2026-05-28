@include('livewire.skills-matrix.partials.list-page-open')

@component('livewire.skills-matrix.partials.page-header', [
    'title' => $user->name,
    'subtitle' => 'Average competency level: ' . $avg . ' · Individual skill profile',
    'icon' => 'mdi-account-circle',
])
    @slot('actions')
        <a href="{{ route('view-personnel', $user->id) }}" class="btn btn-outline-primary btn-sm">
            <i class="mdi mdi-account"></i> View in Personnel
        </a>
    @endslot
@endcomponent

@component('livewire.skills-matrix.partials.list-table-card', [
    'tableTitle' => 'Domain Breakdown',
    'tableSubtitle' => 'Average proficiency by competency domain',
])
    @slot('body')
        @forelse($byDomain as $domain => $score)
            <div class="d-flex align-items-center mb-3" style="gap:12px;">
                <div style="min-width:140px;font-size:13px;font-weight:500;">{{ $domain }}</div>
                <div class="progress flex-grow-1" style="height:8px;border-radius:4px;">
                    <div class="progress-bar bg-primary" style="width:{{ min(100, ($score / 3) * 100) }}%;"></div>
                </div>
                <div style="min-width:36px;text-align:right;font-weight:600;">{{ $score }}</div>
            </div>
        @empty
            <p class="text-muted mb-0 text-center py-3">No competency data recorded for this staff member.</p>
        @endforelse
    @endslot
@endcomponent

@include('livewire.skills-matrix.partials.list-page-close')
