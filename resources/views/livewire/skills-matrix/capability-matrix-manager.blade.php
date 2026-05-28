@include('livewire.skills-matrix.partials.list-page-open')

@if($flashMessage)
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ $flashMessage }}
        <button type="button" class="btn-close" wire:click="$set('flashMessage', null)"></button>
    </div>
@endif

@component('livewire.skills-matrix.partials.page-header', [
    'title' => 'Capability Matrix',
    'subtitle' => 'Actual competency — current team members',
    'icon' => 'mdi-account-check-outline',
])
    @slot('actions')
        @can('skills-matrix.components.capability.add')
            <button type="button" class="btn btn-outline-primary btn-sm" wire:click="openCreate">
                <i class="mdi mdi-plus"></i> Add Capability Matrix
            </button>
        @endcan
    @endslot
@endcomponent

@include('livewire.skills-matrix.partials.list-filters', ['searchPlaceholder' => 'Search by capability or skills matrix name...'])

@component('livewire.skills-matrix.partials.list-table-card', [
    'tableTitle' => 'Capability Matrices',
    'tableSubtitle' => 'Team assessments linked to skills matrix standards',
])
    @slot('body')
        @if($capabilities->count() > 0)
            @component('livewire.skills-matrix.partials.list-table')
                @slot('head')
                    <th>Actions</th>
                    <th>Name</th>
                    <th>Skills Matrix</th>
                    <th>Staff</th>
                @endslot
                @slot('body')
                    @foreach($capabilities as $c)
                        <tr wire:key="cap-{{ $c->id }}">
                            <td>
                                <a href="{{ route('capability.show', $c->id) }}" class="rm-act-btn rm-act-btn--view" title="View">
                                    <i class="mdi mdi-eye"></i>
                                </a>
                            </td>
                            <td><strong>{{ $c->name }}</strong></td>
                            <td>{{ $c->skillmatrix->name ?? '—' }}</td>
                            <td><span class="badge bg-info p-2" style="color:#fff;">{{ $c->grouproles_count }}</span></td>
                        </tr>
                    @endforeach
                @endslot
            @endcomponent
            @include('livewire.skills-matrix.partials.list-pagination', ['paginator' => $capabilities])
        @else
            <p class="text-muted mb-0 text-center py-4">No capability matrices match your filters.</p>
        @endif
    @endslot
@endcomponent

@if($showModal)
    <div class="modal fade show d-block" style="background:rgba(0,0,0,.4);">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" style="border-radius: 12px;">
                <div class="modal-header">
                    <h5 class="modal-title">Create Capability Matrix</h5>
                    <button type="button" class="close" wire:click="$set('showModal', false)">&times;</button>
                </div>
                <div class="modal-body">
                    <input class="form-control mb-2" wire:model="name" placeholder="Capability matrix name">
                    <select class="form-control mb-2" wire:model="skillsMatrixId">
                        <option value="">Skills matrix</option>
                        @foreach($skillmatrixs as $sm)<option value="{{ $sm->id }}">{{ $sm->name }}</option>@endforeach
                    </select>
                    <p class="text-muted small mb-2">Assign staff to roles after creation from the matrix detail view.</p>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-sm btn-secondary" wire:click="$set('showModal', false)">Cancel</button>
                    <button class="btn btn-sm btn-primary" wire:click="saveCapability">Save</button>
                </div>
            </div>
        </div>
    </div>
@endif

@include('livewire.skills-matrix.partials.list-page-close')
