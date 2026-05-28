@include('livewire.skills-matrix.partials.list-page-open')

@if($flashMessage)
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ $flashMessage }}
        <button type="button" class="btn-close" wire:click="$set('flashMessage', null)"></button>
    </div>
@endif

@component('livewire.skills-matrix.partials.page-header', [
    'title' => 'Skills Matrix',
    'subtitle' => 'Required competency standards by role · TS/LS/007/19',
    'icon' => 'mdi-account-star-outline',
])
    @slot('actions')
        @can('skills-matrix.components.skills-matrix.add')
            <button type="button" class="btn btn-outline-primary btn-sm" wire:click="openCreate">
                <i class="mdi mdi-plus"></i> Generate Matrix
            </button>
        @endcan
    @endslot
@endcomponent

@component('livewire.skills-matrix.partials.list-filters', ['searchPlaceholder' => 'Search matrices by name or department...'])
    @slot('filters')
        <div class="sm-list-filter-field">
            <label class="form-label fw-bold" for="sm-status-filter">Status</label>
            <select wire:model.live="statusFilter" id="sm-status-filter" class="form-select">
                <option value="">All Status</option>
                <option value="1">Active</option>
                <option value="0">Inactive</option>
            </select>
        </div>
    @endslot
@endcomponent

@component('livewire.skills-matrix.partials.list-table-card', [
    'tableTitle' => 'Skills Matrices',
    'tableSubtitle' => 'Reference competency levels defined for each lab role',
])
    @slot('body')
        @if($matrices->count() > 0)
            @component('livewire.skills-matrix.partials.list-table')
                @slot('head')
                    <th>Actions</th>
                    <th>Name</th>
                    <th>Department</th>
                    <th>Status</th>
                @endslot
                @slot('body')
                    @foreach($matrices as $m)
                        <tr wire:key="matrix-{{ $m->id }}">
                            <td>
                                <div class="sm-table-actions">
                                    <a href="{{ route('show-matrix', $m->id) }}" class="rm-act-btn rm-act-btn--view" title="View">
                                        <i class="mdi mdi-eye"></i>
                                    </a>
                                    @can('skills-matrix.components.skills-matrix.edit')
                                        <button type="button" class="rm-act-btn rm-act-btn--edit" wire:click="openEdit('{{ $m->id }}')" title="Edit">
                                            <i class="mdi mdi-pencil"></i>
                                        </button>
                                    @endcan
                                </div>
                            </td>
                            <td><strong>{{ $m->name }}</strong></td>
                            <td>{{ $m->department ?? '—' }}</td>
                            <td>
                                <span class="badge p-2 bg-{{ $m->status ? 'success' : 'secondary' }}">
                                    {{ $m->status ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                @endslot
            @endcomponent
            @include('livewire.skills-matrix.partials.list-pagination', ['paginator' => $matrices, 'entryLabel' => 'matrices'])
        @else
            <p class="text-muted mb-0 text-center py-4">No skills matrices match your filters.</p>
        @endif
    @endslot
@endcomponent

@if($showModal)
    <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.4);">
        <div class="modal-dialog">
            <div class="modal-content" style="border-radius: 12px;">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $editingMatrixId ? 'Edit' : 'Create' }} Matrix</h5>
                    <button type="button" class="close" wire:click="$set('showModal', false)">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group"><label>Name</label><input type="text" class="form-control" wire:model="name"></div>
                    <div class="form-group"><label>Department</label>
                        <select class="form-control" wire:model="departmentId"><option value="">Select</option>
                            @foreach($departments as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="form-group"><label>Job descriptions</label>
                        <div class="tag-select-container">
                            @foreach($roles as $role)
                                <label class="mr-2"><input type="checkbox" wire:model="selectedRoleIds" value="{{ $role->id }}"> {{ $role->name }}</label>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary btn-sm" wire:click="$set('showModal', false)">Cancel</button>
                    <button class="btn btn-primary btn-sm" wire:click="saveMatrix">Save</button>
                </div>
            </div>
        </div>
    </div>
@endif

@include('livewire.skills-matrix.partials.list-page-close')
