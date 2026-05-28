@include('livewire.skills-matrix.partials.list-page-open')

@if($flashMessage)
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ $flashMessage }}
        <button type="button" class="btn-close" wire:click="$set('flashMessage', null)"></button>
    </div>
@endif

@component('livewire.skills-matrix.partials.page-header', [
    'title' => 'Training Plan',
    'subtitle' => 'Scheduled sessions, trainers, and status',
    'icon' => 'mdi-calendar-check',
])
    @slot('actions')
        @can('skills-matrix.components.training-plan.add')
            <button type="button" class="btn btn-outline-primary btn-sm" wire:click="openCreate">
                <i class="mdi mdi-plus"></i> New Plan
            </button>
        @endcan
    @endslot
@endcomponent

@include('livewire.skills-matrix.partials.list-filters', ['searchPlaceholder' => 'Search plans by name or training need...'])

@component('livewire.skills-matrix.partials.list-table-card', [
    'tableTitle' => 'Training Plans',
])
    @slot('body')
        @if($plans->count() > 0)
            @component('livewire.skills-matrix.partials.list-table')
                @slot('head')
                    <th>Actions</th>
                    <th>Plan Name</th>
                    <th>Training Need</th>
                @endslot
                @slot('body')
                    @foreach($plans as $plan)
                        <tr wire:key="plan-{{ $plan->id }}">
                            <td>
                                <a href="{{ route('train.plan.show', $plan->id) }}" class="rm-act-btn rm-act-btn--view" title="Open">
                                    <i class="mdi mdi-eye"></i>
                                </a>
                            </td>
                            <td><strong>{{ $plan->name }}</strong></td>
                            <td>{{ $plan->trainneed->name ?? '—' }}</td>
                        </tr>
                    @endforeach
                @endslot
            @endcomponent
            @include('livewire.skills-matrix.partials.list-pagination', ['paginator' => $plans])
        @else
            <p class="text-muted mb-0 text-center py-4">No training plans match your filters.</p>
        @endif
    @endslot
@endcomponent

@if($showModal)
    <div class="modal fade show d-block" style="background:rgba(0,0,0,.4);">
        <div class="modal-dialog modal-sm"><div class="modal-content" style="border-radius: 12px;">
            <div class="modal-header">
                <h5 class="modal-title">New Training Plan</h5>
                <button type="button" class="close" wire:click="$set('showModal', false)">&times;</button>
            </div>
            <div class="modal-body">
                <input class="form-control mb-2" wire:model="planName" placeholder="Plan name">
                <select class="form-control" wire:model="trainingNeedHeaderId">
                    <option value="">Training need</option>
                    @foreach($needs as $n)<option value="{{ $n->id }}">{{ $n->name }}</option>@endforeach
                </select>
            </div>
            <div class="modal-footer">
                <button class="btn btn-sm btn-secondary" wire:click="$set('showModal', false)">Cancel</button>
                <button class="btn btn-sm btn-primary" wire:click="savePlan">Create</button>
            </div>
        </div></div>
    </div>
@endif

@include('livewire.skills-matrix.partials.list-page-close')
