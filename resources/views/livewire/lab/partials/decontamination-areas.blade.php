@php
    $embeddedInTab = $embeddedInTab ?? false;
@endphp

<div class="{{ $embeddedInTab ? '' : 'section-shell mt-4' }}">
    @unless($embeddedInTab)
        <div class="section-shell__header">
            <div>
                <h6 class="mb-1">
                    <i class="mdi mdi-spray"></i>
                    Decontamination Areas
                </h6>
                <p class="text-muted mb-0">Name physical areas or rooms and tie each one to a lab section for decontamination tracking.</p>
            </div>
            @can('laboratory.components.labs.edit')
                <button type="button"
                        class="btn btn-primary btn-sm section-add-btn"
                        wire:click="showCreateDecontaminationModal"
                        @if($lab->labSections->isEmpty()) disabled title="Create a lab section first" @endif>
                    <i class="mdi mdi-plus"></i> Add Area
                </button>
            @endcan
        </div>
    @else
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <p class="text-muted small mb-0">Name physical areas or rooms and tie each one to a lab section for decontamination tracking.</p>
            @can('laboratory.components.labs.edit')
                <button type="button"
                        class="btn btn-primary btn-sm section-add-btn"
                        wire:click="showCreateDecontaminationModal"
                        @if($lab->labSections->isEmpty()) disabled title="Create a lab section first" @endif>
                    <i class="mdi mdi-plus"></i> Add Area
                </button>
            @endcan
        </div>
    @endunless

    @if($lab->labSections->isEmpty())
        <div class="empty-section-state">
            <i class="mdi mdi-layers-off"></i>
            <p class="mb-0">Create at least one lab section before adding decontamination areas.</p>
        </div>
    @elseif($lab->decontaminationAreas->isEmpty())
        <div class="empty-section-state">
            <i class="mdi mdi-spray"></i>
            <p class="mb-0">No decontamination areas configured yet.</p>
        </div>
    @else
        @php
            $groupedAreas = $lab->decontaminationAreas
                ->sortBy([
                    fn ($area) => strtolower((string) ($area->labSection->name ?? 'zzzzzz')),
                    fn ($area) => strtolower((string) $area->name),
                ])
                ->groupBy(fn ($area) => $area->lab_section_id ?? 'unassigned');
        @endphp
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Status</th>
                        @can('laboratory.components.labs.edit')
                            <th style="width: 120px;">Actions</th>
                        @endcan
                    </tr>
                </thead>
                <tbody>
                    @foreach($groupedAreas as $groupKey => $areas)
                        @php
                            $section = $areas->first()->labSection;
                            $groupLabel = $section
                                ? trim(($section->code ? $section->code.' — ' : '').$section->name)
                                : 'Unassigned Section';
                        @endphp
                        <tr class="table-light">
                            <td colspan="@can('laboratory.components.labs.edit')3 @else 2 @endcan" class="fw-bold text-primary">
                                <i class="mdi mdi-layers-triple me-1"></i>
                                {{ $groupLabel }}
                                <span class="text-muted fw-normal ms-2">({{ $areas->count() }})</span>
                            </td>
                        </tr>
                        @foreach($areas as $area)
                            <tr wire:key="decon-area-{{ $area->id }}">
                                <td><strong>{{ $area->name }}</strong></td>
                                <td>
                                    <span class="lab-badge {{ $area->active ? 'lab-badge--active' : 'lab-badge--inactive' }}">
                                        {{ $area->active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                @can('laboratory.components.labs.edit')
                                    <td>
                                        <div class="d-flex gap-1">
                                            <button type="button" class="rm-act-btn rm-act-btn--edit" wire:click="showEditDecontaminationModal('{{ $area->id }}')" title="Edit">
                                                <i class="mdi mdi-pencil"></i>
                                            </button>
                                            <button type="button" class="rm-act-btn rm-act-btn--delete" wire:click="confirmDeleteDecontaminationArea('{{ $area->id }}')" title="Delete">
                                                <i class="mdi mdi-delete"></i>
                                            </button>
                                        </div>
                                    </td>
                                @endcan
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

@if($showDecontaminationModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content lab-section-modal">
                <div class="modal-header lab-section-modal__header">
                    <div>
                        <div class="lab-section-modal__eyebrow">Decontamination</div>
                        <h5 class="modal-title lab-section-modal__title">
                            <i class="mdi mdi-{{ $editingDecontaminationAreaId ? 'pencil-circle-outline' : 'plus-circle-outline' }}"></i>
                            {{ $editingDecontaminationAreaId ? 'Edit' : 'Add' }} Area
                        </h5>
                    </div>
                    <button type="button" class="btn-close" wire:click="closeDecontaminationModal"></button>
                </div>
                <div class="modal-body lab-section-modal__body">
                    <div class="mb-3">
                        <label class="form-label form-label--modern">Area Name <span class="text-danger">*</span></label>
                        <input type="text" wire:model.defer="decontaminationForm.name" class="form-control form-control--modern" placeholder="e.g. Biosafety Cabinet B2">
                        @error('decontaminationForm.name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label form-label--modern">Lab Section <span class="text-danger">*</span></label>
                        <select wire:model.defer="decontaminationForm.lab_section_id" class="form-control form-control--modern">
                            <option value="">Select section...</option>
                            @foreach($lab->labSections as $section)
                                <option value="{{ $section->id }}">{{ $section->code }} — {{ $section->name }}</option>
                            @endforeach
                        </select>
                        @error('decontaminationForm.lab_section_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" wire:model.defer="decontaminationForm.active" id="decon-area-active">
                        <label class="form-check-label" for="decon-area-active">Active</label>
                    </div>
                </div>
                <div class="modal-footer lab-section-modal__footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeDecontaminationModal">Cancel</button>
                    <button type="button" class="btn btn-primary btn-modal-primary" wire:click="saveDecontaminationArea">
                        <i class="mdi mdi-content-save"></i> Save
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif
