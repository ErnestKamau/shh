@include('livewire.skills-matrix.partials.list-page-open')

@component('livewire.skills-matrix.partials.page-header', [
    'title' => 'Staff Profiles',
    'subtitle' => 'Individual skill assessments and scores',
    'icon' => 'mdi-account-circle',
])
@endcomponent

@include('livewire.skills-matrix.partials.list-filters', ['searchPlaceholder' => 'Search staff by name or role...'])

@component('livewire.skills-matrix.partials.list-table-card', [
    'tableTitle' => 'Team Members',
    'tableSubtitle' => 'Click a profile to view competency detail',
])
    @slot('body')
        @if($staff->count() > 0)
            <div class="staff-grid">
                @foreach($staff as $role)
                    @php $uid = (string) $role->user_id; @endphp
                    <a href="{{ route('matrix.staff.show', $uid) }}" class="staff-card text-decoration-none text-dark" wire:key="staff-{{ $uid }}">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width:40px;height:40px;font-size:12px;font-weight:600;">
                            {{ strtoupper(substr($role->user->name ?? 'U', 0, 2)) }}
                        </div>
                        <div class="flex-grow-1 min-width-0">
                            <div class="font-weight-bold text-truncate">{{ $role->user->name ?? 'User' }}</div>
                            <small class="text-muted">{{ $role->jobdescription->name ?? '' }}</small>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <div class="font-weight-bold" style="font-size:18px;">{{ $averages[$uid] ?? '—' }}</div>
                            <small class="text-muted">avg level</small>
                        </div>
                    </a>
                @endforeach
            </div>
            @include('livewire.skills-matrix.partials.list-pagination', ['paginator' => $staff, 'entryLabel' => 'staff'])
        @else
            <p class="text-muted mb-0 text-center py-4">No staff profiles match your search.</p>
        @endif
    @endslot
@endcomponent

@include('livewire.skills-matrix.partials.list-page-close')
