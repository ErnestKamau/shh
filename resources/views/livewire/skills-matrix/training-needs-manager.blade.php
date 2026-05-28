@include('livewire.skills-matrix.partials.list-page-open')

@if($flashMessage)
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ $flashMessage }}
        <button type="button" class="btn-close" wire:click="$set('flashMessage', null)"></button>
    </div>
@endif

@component('livewire.skills-matrix.partials.page-header', [
    'title' => 'Training Needs Assessment',
    'subtitle' => 'Gaps between required and actual competency',
    'icon' => 'mdi-school',
])
    @slot('actions')
        @can('skills-matrix.components.training-needs.add')
            <button type="button" class="btn btn-outline-primary btn-sm" wire:click="openCreate">
                <i class="mdi mdi-plus"></i> New Assessment
            </button>
        @endcan
    @endslot
@endcomponent

@include('livewire.skills-matrix.partials.list-filters', ['searchPlaceholder' => 'Search assessments by name or capability...'])

@component('livewire.skills-matrix.partials.list-table-card', [
    'tableTitle' => 'Training Needs Assessments',
])
    @slot('body')
        @if($trainings->count() > 0)
            @component('livewire.skills-matrix.partials.list-table')
                @slot('head')
                    <th>Actions</th>
                    <th>Name</th>
                    <th>Capability Matrix</th>
                @endslot
                @slot('body')
                    @foreach($trainings as $t)
                        <tr wire:key="tn-{{ $t->id }}">
                            <td>
                                <a href="{{ route('train.needs.show', $t->id) }}" class="rm-act-btn rm-act-btn--view" title="View">
                                    <i class="mdi mdi-eye"></i>
                                </a>
                            </td>
                            <td><strong>{{ $t->name }}</strong></td>
                            <td>{{ $t->capability->name ?? '—' }}</td>
                        </tr>
                    @endforeach
                @endslot
            @endcomponent
            @include('livewire.skills-matrix.partials.list-pagination', ['paginator' => $trainings])
        @else
            <p class="text-muted mb-0 text-center py-4">No training needs assessments match your filters.</p>
        @endif
    @endslot
@endcomponent

@if($showModal)
    <div class="modal fade show d-block" style="background:rgba(0,0,0,.4);">
        <div class="modal-dialog"><div class="modal-content" style="border-radius: 12px;">
            <div class="modal-header">
                <h5 class="modal-title">New Training Needs Assessment</h5>
                <button type="button" class="close" wire:click="$set('showModal', false)">&times;</button>
            </div>
            <div class="modal-body">
                <input class="form-control mb-2" wire:model="name" placeholder="Assessment name">
                <select class="form-control mb-2" wire:model.live="capabilityId">
                    <option value="">Capability matrix</option>
                    @foreach($capabilities as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                </select>
                @if($capabilityId)
                <div class="tag-select-container">
                    @php $capRoles = \App\Models\SkillsMatrix\CapabilityMatrixRoles::where('capability_id', $capabilityId)->whereNull('deleted_at')->with('user')->get(); @endphp
                    @foreach($capRoles as $r)
                    <label class="d-block"><input type="checkbox" wire:model="userRoleIds" value="{{ $r->id }}"> {{ $r->user->name ?? '' }}</label>
                    @endforeach
                </div>
                @endif
            </div>
            <div class="modal-footer">
                <button class="btn btn-sm btn-secondary" wire:click="$set('showModal', false)">Cancel</button>
                <button class="btn btn-sm btn-primary" wire:click="saveTrainingNeed">Create</button>
            </div>
        </div></div>
    </div>
@endif

@include('livewire.skills-matrix.partials.list-page-close')
