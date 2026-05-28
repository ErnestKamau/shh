@include('livewire.skills-matrix.partials.list-page-open')

@if($flashMessage)
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ $flashMessage }}
        <button type="button" class="btn-close" wire:click="$set('flashMessage', null)"></button>
    </div>
@endif

@component('livewire.skills-matrix.partials.page-header', [
    'title' => 'Education Requirements',
    'subtitle' => 'Qualifications and induction standards',
    'icon' => 'mdi-certificate',
])
    @slot('actions')
        <button type="button" class="btn btn-outline-primary btn-sm" wire:click="openCreateChecklist">
            <i class="mdi mdi-plus"></i> Add Checklist Item
        </button>
    @endslot
@endcomponent

@include('livewire.skills-matrix.partials.list-filters', ['searchPlaceholder' => 'Search roles or checklist items...'])

@component('livewire.skills-matrix.partials.list-table-card', [
    'tableTitle' => 'Role Education Standards',
    'tableSubtitle' => 'Job descriptions configured for this location',
])
    @slot('body')
        @if($roles->count() > 0)
            @component('livewire.skills-matrix.partials.list-table')
                @slot('head')
                    <th>Role</th>
                    <th>Description</th>
                @endslot
                @slot('body')
                    @foreach($roles as $role)
                        <tr wire:key="role-{{ $role->id }}">
                            <td class="font-weight-bold">{{ $role->name }}</td>
                            <td class="text-muted">{{ $role->description ?? '—' }}</td>
                        </tr>
                    @endforeach
                @endslot
            @endcomponent
            @include('livewire.skills-matrix.partials.list-pagination', ['paginator' => $roles, 'entryLabel' => 'roles'])
        @else
            <p class="text-muted mb-0 text-center py-4">No roles match your search.</p>
        @endif
    @endslot
@endcomponent

@component('livewire.skills-matrix.partials.list-table-card', [
    'tableTitle' => 'Induction Checklist',
    'tableSubtitle' => 'Items required for new staff onboarding',
])
    @slot('body')
        @if($checklist->count() > 0)
            <div class="row">
                @foreach($checklist as $item)
                    <div class="col-md-6 mb-3" wire:key="check-{{ $item->id }}">
                        <div class="d-flex align-items-center border rounded p-3 bg-white h-100" style="border-radius: 10px !important;">
                            <i class="mdi mdi-check-circle text-success mr-2" style="font-size:20px;"></i>
                            <span class="flex-grow-1">{{ $item->name }}</span>
                            <button type="button" class="rm-act-btn rm-act-btn--edit mr-1" wire:click="openEditChecklist('{{ $item->id }}')" title="Edit">
                                <i class="mdi mdi-pencil"></i>
                            </button>
                            <button type="button" class="rm-act-btn rm-act-btn--delete" wire:click="deleteChecklistItem('{{ $item->id }}')" title="Remove">
                                <i class="mdi mdi-delete"></i>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-3">
                @include('livewire.skills-matrix.partials.list-pagination', ['paginator' => $checklist, 'entryLabel' => 'items'])
            </div>
        @else
            <p class="text-muted mb-0 text-center py-4">No checklist items match your search.</p>
        @endif
    @endslot
@endcomponent

@if($showChecklistModal)
    <div class="modal fade show d-block" style="background:rgba(0,0,0,.4);">
        <div class="modal-dialog modal-sm"><div class="modal-content" style="border-radius: 12px;">
            <div class="modal-header">
                <h5 class="modal-title">{{ $editingItemId ? 'Edit' : 'Add' }} Checklist Item</h5>
                <button type="button" class="close" wire:click="$set('showChecklistModal', false)">&times;</button>
            </div>
            <div class="modal-body">
                <input class="form-control" wire:model="checklistName" placeholder="Item name">
            </div>
            <div class="modal-footer">
                <button class="btn btn-sm btn-secondary" wire:click="$set('showChecklistModal', false)">Cancel</button>
                <button class="btn btn-sm btn-primary" wire:click="saveChecklistItem">Save</button>
            </div>
        </div></div>
    </div>
@endif

@include('livewire.skills-matrix.partials.list-page-close')
