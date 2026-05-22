<div class="solution-preparation-manager container-fluid py-3">
    <div class="scd-hero card border-0 shadow-sm mb-4">
        <div class="card-body p-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <p class="scd-eyebrow mb-1">Stock monitoring · Preparation tracking</p>
                <h2 class="scd-title mb-0">Solution preparations</h2>
                <p class="scd-subtitle mb-0 mt-1">Track and manage solution preparation runs</p>
            </div>
            <button type="button" wire:click="showNewPreparationModal" class="btn btn-primary spm-new-btn">
                <i class="mdi mdi-plus"></i> New preparation
            </button>
        </div>
    </div>

    @if($message)
        <div class="alert alert-{{ $messageType === 'success' ? 'success' : 'danger' }} alert-dismissible fade show spm-alert" role="alert">
            <i class="mdi mdi-{{ $messageType === 'success' ? 'check-circle' : 'alert-circle' }} me-1"></i>
            {{ $message }}
            <button type="button" class="btn-close" wire:click="dismissMessage"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm mb-4 spm-filters">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="spm-label">Search</label>
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control spm-input" placeholder="Prep # or batch...">
                </div>
                <div class="col-md-3">
                    <label class="spm-label">Status</label>
                    <div class="tag-select-container"
                         wire:click="openFilterStatusDropdown"
                         wire:click.outside="closeFilterStatusDropdown">
                        <div class="tag-select-input">
                            @if($this->selectedFilterStatusLabel)
                                <span class="tag-badge tag-badge--neutral">
                                    {{ $this->selectedFilterStatusLabel }}
                                    <i class="mdi mdi-close-circle" wire:click.stop="clearStatusFilter"></i>
                                </span>
                            @endif
                            <input type="text"
                                   wire:model.live.debounce.200ms="filterStatusSearch"
                                   class="tag-input"
                                   placeholder="{{ $this->selectedFilterStatusLabel ? '' : 'All statuses...' }}"
                                   autocomplete="off">
                        </div>
                        @if($showFilterStatusDropdown)
                            <div class="tag-dropdown">
                                @forelse($this->filteredStatusOptions as $statusOption)
                                    <div class="tag-dropdown-item"
                                         wire:click.stop="selectStatusFilter(@js($statusOption['value']))">
                                        {{ $statusOption['label'] }}
                                    </div>
                                @empty
                                    <div class="tag-dropdown-item text-muted">No statuses found</div>
                                @endforelse
                            </div>
                        @endif
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="spm-label">Solution</label>
                    <div class="tag-select-container"
                         wire:click="openFilterSolutionDropdown"
                         wire:click.outside="closeFilterSolutionDropdown">
                        <div class="tag-select-input">
                            @if($this->selectedFilterSolution)
                                <span class="tag-badge tag-badge--primary">
                                    {{ $this->selectedFilterSolution->name }}
                                    <i class="mdi mdi-close-circle" wire:click.stop="clearSolutionFilter"></i>
                                </span>
                            @endif
                            <input type="text"
                                   wire:model.live.debounce.200ms="filterSolutionSearch"
                                   class="tag-input"
                                   placeholder="{{ $this->selectedFilterSolution ? '' : 'All solutions...' }}"
                                   autocomplete="off">
                        </div>
                        @if($showFilterSolutionDropdown)
                            <div class="tag-dropdown">
                                @if(!$this->selectedFilterSolution)
                                    <div class="tag-dropdown-item" wire:click.stop="clearSolutionFilter">
                                        All solutions
                                    </div>
                                @endif
                                @forelse($this->filteredFilterSolutions as $sol)
                                    <div class="tag-dropdown-item" wire:click.stop="selectSolutionFilter('{{ $sol->id }}')">
                                        {{ $sol->name }}
                                    </div>
                                @empty
                                    <div class="tag-dropdown-item text-muted">No solutions found</div>
                                @endforelse
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="scd-panel card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0 scd-table">
                <thead>
                    <tr>
                        <th>Preparation #</th>
                        <th>Solution</th>
                        <th>Status</th>
                        <th>Prepared</th>
                        <th>Qty</th>
                        <th style="width: 130px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($this->preparations as $prep)
                        <tr>
                            <td><strong>{{ $prep->preparation_number }}</strong></td>
                            <td>{{ $prep->solution?->name }}</td>
                            <td>
                                @php
                                    $statusClass = match($prep->status) {
                                        'preparing' => 'scd-status--preparing',
                                        'awaiting_approval' => 'scd-status--awaiting',
                                        'completed' => 'scd-status--completed',
                                        default => 'scd-status--cancelled',
                                    };
                                @endphp
                                <span class="scd-status {{ $statusClass }}">{{ str_replace('_', ' ', ucfirst($prep->status)) }}</span>
                            </td>
                            <td>{{ $prep->prepared_at?->format('M j, Y H:i') ?? '—' }}</td>
                            <td>{{ $prep->quantity_prepared }} {{ $prep->solution?->reportingUnit?->name }}</td>
                            <td>
                                <div class="d-flex gap-1 justify-content-end">
                                    <a href="{{ route('solutions-preparation-show', $prep->id) }}"
                                       class="btn btn-sm rm-act-btn rm-act-btn--view"
                                       title="Open preparation">
                                        <i class="mdi mdi-eye"></i>
                                    </a>
                                    @if(! in_array($prep->status, ['completed', 'cancelled'], true))
                                        <button type="button"
                                                wire:click="showEditPreparationModal('{{ $prep->id }}')"
                                                class="btn btn-sm rm-act-btn rm-act-btn--edit"
                                                title="Edit preparation">
                                            <i class="mdi mdi-pencil"></i>
                                        </button>
                                    @endif
                                    @if($prep->status !== 'completed')
                                        <button type="button"
                                                wire:click="showDeletePreparationModal('{{ $prep->id }}')"
                                                class="btn btn-sm rm-act-btn rm-act-btn--delete"
                                                title="Delete preparation">
                                            <i class="mdi mdi-delete"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="mdi mdi-flask-empty-outline mdi-36px d-block mb-2 opacity-50"></i>
                                No preparations found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white border-top">{{ $this->preparations->links() }}</div>
    </div>

    @if($showCreateModal)
        <div class="modal fade show d-block spm-modal-backdrop" tabindex="-1" role="dialog" aria-modal="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content spm-modal-content border-0 shadow">
                    <div class="modal-header spm-modal-header border-0">
                        <div>
                            <h5 class="modal-title mb-0">
                                <i class="mdi mdi-flask text-primary"></i>
                                {{ $this->isEditing ? 'Edit preparation' : 'New preparation' }}
                            </h5>
                            <small class="text-muted">
                                {{ $this->isEditing ? 'Update preparation details (solution cannot be changed)' : 'Start a new solution preparation run' }}
                            </small>
                        </div>
                        <button type="button" class="btn-close" wire:click="closeCreateModal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body spm-modal-body pt-0">
                        <form wire:submit.prevent="savePreparation" id="spm-create-form">
                            <section class="spm-form-section">
                                <h6 class="spm-form-section__title">Solution &amp; quantity</h6>
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="spm-label">Solution <span class="text-danger">*</span></label>
                                        @if($this->isEditing)
                                            <div class="spm-solution-locked">
                                                <span class="tag-badge tag-badge--primary">
                                                    <i class="mdi mdi-lock-outline me-1"></i>
                                                    {{ $this->selectedSolution?->name ?? '—' }}
                                                </span>
                                                <small class="text-muted d-block mt-2">Solution cannot be changed when editing a preparation.</small>
                                            </div>
                                        @else
                                            <div class="tag-select-container @error('form.solution_id') is-invalid @enderror"
                                                 wire:click="openSolutionDropdown"
                                                 wire:click.outside="closeSolutionDropdown">
                                                <div class="tag-select-input">
                                                    @if($this->selectedSolution)
                                                        <span class="tag-badge tag-badge--primary">
                                                            {{ $this->selectedSolution->name }}
                                                            <i class="mdi mdi-close-circle" wire:click.stop="clearSolution"></i>
                                                        </span>
                                                    @endif
                                                    <input type="text"
                                                           wire:model.live.debounce.200ms="solutionSearch"
                                                           class="tag-input"
                                                           placeholder="{{ $this->selectedSolution ? '' : 'Search solutions...' }}"
                                                           autocomplete="off">
                                                </div>
                                                @if($showSolutionDropdown)
                                                    <div class="tag-dropdown">
                                                        @forelse($this->filteredSolutions as $sol)
                                                            <div class="tag-dropdown-item" wire:click.stop="selectSolution('{{ $sol->id }}')">
                                                                {{ $sol->name }}
                                                            </div>
                                                        @empty
                                                            <div class="tag-dropdown-item text-muted">No solutions found</div>
                                                        @endforelse
                                                    </div>
                                                @endif
                                            </div>
                                            @error('form.solution_id')
                                                <div class="spm-field-error">{{ $message }}</div>
                                            @enderror
                                            @if($this->selectedSolution?->alternativeSolution)
                                                <p class="spm-hint mt-2 mb-0">
                                                    <i class="mdi mdi-swap-horizontal"></i>
                                                    Alternative: <strong>{{ $this->selectedSolution->alternativeSolution->name }}</strong>
                                                    — you can create a paired run below.
                                                </p>
                                            @endif
                                        @endif
                                    </div>
                                    <div class="col-md-6">
                                        <label class="spm-label">Quantity prepared <span class="text-danger">*</span></label>
                                        <input type="number"
                                               step="0.0001"
                                               min="0.0001"
                                               wire:model="form.quantity_prepared"
                                               class="form-control spm-input @error('form.quantity_prepared') is-invalid @enderror"
                                               placeholder="0.00">
                                        @error('form.quantity_prepared')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="spm-label">Unit of measure <span class="text-danger">*</span></label>
                                        <div class="tag-select-container @error('form.uom_id') is-invalid @enderror"
                                             wire:click="openUomDropdown"
                                             wire:click.outside="closeUomDropdown">
                                            <div class="tag-select-input">
                                                @if($this->selectedReportingUnit)
                                                    <span class="tag-badge tag-badge--neutral">
                                                        {{ $this->selectedReportingUnit->name }}
                                                        <i class="mdi mdi-close-circle" wire:click.stop="clearUom"></i>
                                                    </span>
                                                @endif
                                                <input type="text"
                                                       wire:model.live.debounce.200ms="uomSearch"
                                                       class="tag-input"
                                                       placeholder="{{ $this->selectedReportingUnit ? '' : 'Search units...' }}"
                                                       autocomplete="off">
                                            </div>
                                            @if($showUomDropdown)
                                                <div class="tag-dropdown">
                                                    @forelse($this->filteredReportingUnits as $unit)
                                                        <div class="tag-dropdown-item" wire:click.stop="selectUom('{{ $unit->id }}')">
                                                            {{ $unit->name }}
                                                        </div>
                                                    @empty
                                                        <div class="tag-dropdown-item text-muted">No units found</div>
                                                    @endforelse
                                                </div>
                                            @endif
                                        </div>
                                        @error('form.uom_id')
                                            <div class="spm-field-error">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </section>

                            <section class="spm-form-section">
                                <h6 class="spm-form-section__title">Batch</h6>
                                <div class="row g-3 align-items-end">
                                    <div class="col-md-6">
                                        <div class="spm-check-card">
                                            <input type="checkbox"
                                                   wire:model.live="form.is_new_batch"
                                                   class="form-check-input"
                                                   id="spm_is_new_batch">
                                            <label class="form-check-label" for="spm_is_new_batch">
                                                <strong>New batch</strong>
                                                <small class="d-block text-muted">Assign a new batch number for this run</small>
                                            </label>
                                        </div>
                                    </div>
                                    @if($form['is_new_batch'])
                                        <div class="col-md-6">
                                            <label class="spm-label">Batch number <span class="text-danger">*</span></label>
                                            <input type="text"
                                                   wire:model="form.batch_number"
                                                   class="form-control spm-input @error('form.batch_number') is-invalid @enderror"
                                                   placeholder="e.g. BATCH-2026-001">
                                            @error('form.batch_number')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    @endif
                                </div>
                            </section>

                            <section class="spm-form-section">
                                <h6 class="spm-form-section__title">Options</h6>
                                @if(! $this->isEditing && $this->selectedSolution?->alternative_solution_id)
                                    <div class="spm-check-card mb-3">
                                        <input type="checkbox"
                                               wire:model="form.create_with_alternative"
                                               class="form-check-input"
                                               id="spm_create_alt">
                                        <label class="form-check-label" for="spm_create_alt">
                                            <strong>Also create for alternative solution</strong>
                                            <small class="d-block text-muted">Starts a linked preparation for the alternative in parallel</small>
                                        </label>
                                    </div>
                                @endif
                                <div>
                                    <label class="spm-label">Notes</label>
                                    <textarea wire:model="form.notes"
                                              class="form-control spm-input"
                                              rows="3"
                                              placeholder="Optional preparation notes..."></textarea>
                                </div>
                            </section>
                        </form>
                    </div>
                    <div class="modal-footer spm-modal-footer border-0">
                        <button type="button" class="btn btn-light" wire:click="closeCreateModal">Cancel</button>
                        <button type="submit"
                                form="spm-create-form"
                                class="btn btn-primary spm-save-btn"
                                wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="savePreparation">
                                @if($this->isEditing)
                                    <i class="mdi mdi-content-save-outline"></i> Save changes
                                @else
                                    <i class="mdi mdi-play-circle-outline"></i> Start preparation
                                @endif
                            </span>
                            <span wire:loading wire:target="savePreparation">
                                <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                                {{ $this->isEditing ? 'Saving...' : 'Starting...' }}
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($showDeleteModal && $this->deletingPreparation)
        @php $prepDelete = $this->deletingPreparation; @endphp
        <div class="modal fade show d-block spm-modal-backdrop" tabindex="-1" role="dialog" aria-modal="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content spm-modal-content border-0 shadow">
                    <div class="modal-header spm-modal-header border-0">
                        <div>
                            <h5 class="modal-title mb-0 text-danger">
                                <i class="mdi mdi-delete-alert"></i> Delete preparation
                            </h5>
                            <small class="text-muted">Review preparation and steps before deleting</small>
                        </div>
                        <button type="button" class="btn-close" wire:click="closeDeleteModal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body spm-modal-body pt-0">
                        <div class="spm-delete-summary mb-3">
                            <div class="row g-2 small">
                                <div class="col-sm-6">
                                    <span class="text-muted">Preparation #</span>
                                    <div class="fw-semibold">{{ $prepDelete->preparation_number }}</div>
                                </div>
                                <div class="col-sm-6">
                                    <span class="text-muted">Solution</span>
                                    <div class="fw-semibold">{{ $prepDelete->solution?->name ?? '—' }}</div>
                                </div>
                                <div class="col-sm-6">
                                    <span class="text-muted">Status</span>
                                    <div class="fw-semibold">{{ str_replace('_', ' ', ucfirst($prepDelete->status)) }}</div>
                                </div>
                                <div class="col-sm-6">
                                    <span class="text-muted">Batch</span>
                                    <div class="fw-semibold">{{ $prepDelete->batch_number ?: '—' }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="spm-delete-steps-info">
                            <div class="spm-delete-steps-info__head">
                                <i class="mdi mdi-playlist-check"></i>
                                <strong>Preparation steps ({{ $prepDelete->steps->count() }})</strong>
                            </div>
                            @if($prepDelete->steps->isEmpty())
                                <p class="small text-muted mb-0">No steps were materialized for this preparation.</p>
                            @else
                                <ul class="spm-delete-steps-list mb-0">
                                    @foreach($prepDelete->steps as $step)
                                        <li class="spm-delete-step-item">
                                            <div class="d-flex align-items-start gap-3">
                                                <span class="spm-step-num">{{ $step->step_number }}</span>
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1 fw-semibold">{{ $step->step_name }}</h6>
                                                    <span class="spm-type-pill {{ $step->isAnalysisStep() ? 'spm-type-pill--analysis' : 'spm-type-pill--regular' }}">
                                                        <i class="mdi mdi-{{ $step->isAnalysisStep() ? 'chart-timeline-variant' : 'flask-outline' }}"></i>
                                                        {{ $step->isAnalysisStep() ? 'Analysis step' : 'Ingredient step' }}
                                                    </span>
                                                    <p class="small text-muted mb-0 mt-2">
                                                        @if($step->isRegularStep())
                                                            <strong>Reagent:</strong> {{ $step->ingredient?->reagent?->name ?? '—' }}
                                                            @if($step->ingredient?->amount_used)
                                                                · {{ $step->ingredient->amount_used }} {{ $step->ingredient->unitMeasure?->name }}
                                                            @endif
                                                        @else
                                                            <strong>Analysis:</strong>
                                                            {{ count($step->selected_analytes ?? []) }} analyte(s),
                                                            {{ $step->controls->count() }} control(s)
                                                        @endif
                                                    </p>
                                                    @if($step->description)
                                                        <p class="small mb-0 mt-1">{{ $step->description }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>

                        <p class="small text-muted mb-0 mt-3">
                            This action cannot be undone. All step data for this preparation will be removed.
                        </p>
                    </div>
                    <div class="modal-footer spm-modal-footer border-0">
                        <button type="button" class="btn btn-light" wire:click="closeDeleteModal">Cancel</button>
                        <button type="button"
                                class="btn btn-danger"
                                wire:click="confirmDeletePreparation"
                                wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="confirmDeletePreparation">
                                <i class="mdi mdi-delete"></i> Yes, delete
                            </span>
                            <span wire:loading wire:target="confirmDeletePreparation">
                                <span class="spinner-border spinner-border-sm me-1" role="status"></span> Deleting...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @include('livewire.lab.partials.scd-styles')

    <style>
    .solution-preparation-manager {
        --spm-primary: #2563eb;
        --spm-primary-soft: #eff6ff;
        --spm-slate-50: #f8fafc;
        --spm-slate-100: #f1f5f9;
        --spm-slate-200: #e2e8f0;
        --spm-slate-500: #64748b;
        --spm-slate-800: #1e293b;
    }

    .spm-new-btn, .spm-save-btn, .spm-open-btn { border-radius: 10px; font-weight: 600; }
    .spm-alert { border-radius: 12px; border: none; }

    .spm-label {
        display: block;
        font-size: 0.82rem;
        font-weight: 600;
        color: #475569;
        margin-bottom: 0.35rem;
    }

    .spm-input {
        border-radius: 10px;
        border-color: var(--spm-slate-200);
        font-size: 0.9rem;
    }

    .spm-input:focus {
        border-color: var(--spm-primary);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
    }

    .spm-filters .card-body { padding: 1.25rem 1.5rem; }

    .spm-modal-backdrop {
        background-color: rgba(15, 23, 42, 0.45);
        overflow-y: auto;
    }

    .spm-modal-content { border-radius: 16px; }
    .spm-modal-header,
    .spm-modal-footer { padding: 1.25rem 1.5rem; }
    .spm-modal-body { padding: 0 1.5rem 1rem; }

    .spm-form-section {
        margin-bottom: 1.25rem;
        padding-bottom: 1.25rem;
        border-bottom: 1px solid var(--spm-slate-200);
    }

    .spm-form-section:last-child {
        margin-bottom: 0;
        padding-bottom: 0;
        border-bottom: none;
    }

    .spm-form-section__title {
        font-size: 0.82rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--spm-slate-500);
        margin-bottom: 0.85rem;
    }

    .spm-check-card {
        display: flex;
        align-items: flex-start;
        gap: 0.65rem;
        padding: 0.85rem 1rem;
        background: var(--spm-slate-50);
        border: 1px solid var(--spm-slate-200);
        border-radius: 12px;
    }

    .spm-check-card .form-check-input {
        margin-top: 0.2rem;
        flex-shrink: 0;
    }

    .spm-check-card label small {
        font-size: 0.78rem;
    }

    .spm-hint {
        font-size: 0.85rem;
        color: var(--spm-slate-500);
        padding: 0.5rem 0.75rem;
        background: var(--spm-primary-soft);
        border-radius: 8px;
    }

    .modal.show { display: block !important; }

    .spm-field-error {
        display: block;
        font-size: 0.8rem;
        color: #dc3545;
        margin-top: 0.25rem;
    }

    /* Tag select */
    .solution-preparation-manager .tag-select-container {
        position: relative;
        width: 100%;
        cursor: text;
    }

    .solution-preparation-manager .tag-select-input {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 6px;
        min-height: 42px;
        padding: 6px 12px;
        background: #fff;
        border: 1px solid var(--spm-slate-200);
        border-radius: 10px;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .solution-preparation-manager .tag-select-input:hover {
        border-color: #93c5fd;
    }

    .solution-preparation-manager .tag-select-input:focus-within {
        border-color: var(--spm-primary);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
    }

    .solution-preparation-manager .tag-input {
        flex: 1;
        min-width: 120px;
        border: none;
        outline: none;
        padding: 4px 0;
        font-size: 0.9rem;
        background: transparent;
    }

    .solution-preparation-manager .tag-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 0.82rem;
        font-weight: 500;
        white-space: nowrap;
    }

    .solution-preparation-manager .tag-badge--primary {
        background: var(--spm-primary-soft);
        color: #1d4ed8;
        border: 1px solid #bfdbfe;
    }

    .solution-preparation-manager .tag-badge--neutral {
        background: var(--spm-slate-100);
        color: var(--spm-slate-800);
        border: 1px solid var(--spm-slate-200);
    }

    .solution-preparation-manager .tag-badge i {
        cursor: pointer;
        font-size: 1rem;
        opacity: 0.75;
    }

    .solution-preparation-manager .tag-badge i:hover {
        opacity: 1;
    }

    .solution-preparation-manager .tag-dropdown {
        position: absolute;
        top: calc(100% + 4px);
        left: 0;
        right: 0;
        z-index: 1200;
        background: #fff;
        border: 1px solid var(--spm-slate-200);
        border-radius: 10px;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.1);
        max-height: 220px;
        overflow-y: auto;
    }

    .solution-preparation-manager .tag-dropdown-item {
        padding: 10px 14px;
        cursor: pointer;
        font-size: 0.9rem;
        border-bottom: 1px solid var(--spm-slate-100);
        transition: background 0.15s;
    }

    .solution-preparation-manager .tag-dropdown-item:last-child {
        border-bottom: none;
    }

    .solution-preparation-manager .tag-dropdown-item:hover {
        background: var(--spm-slate-50);
    }

    .solution-preparation-manager .tag-select-container.is-invalid .tag-select-input {
        border-color: #dc3545;
    }

    .solution-preparation-manager .rm-act-btn {
        border-radius: 8px;
        padding: 4px 8px;
        font-size: 12px;
    }

    .solution-preparation-manager .rm-act-btn--view {
        border: 1px solid var(--spm-slate-200);
        color: #475569;
        background: #fff;
    }

    .solution-preparation-manager .rm-act-btn--view:hover {
        background: var(--spm-slate-50);
    }

    .solution-preparation-manager .rm-act-btn--edit {
        border: 1px solid #bfdbfe;
        color: #1d4ed8;
        background: #eff6ff;
    }

    .solution-preparation-manager .rm-act-btn--edit:hover {
        background: #dbeafe;
    }

    .solution-preparation-manager .rm-act-btn--delete {
        border: 1px solid #fecaca;
        color: #b91c1c;
        background: #fef2f2;
    }

    .solution-preparation-manager .rm-act-btn--delete:hover {
        background: #fee2e2;
    }

    .spm-solution-locked {
        padding: 0.85rem 1rem;
        background: var(--spm-slate-50);
        border: 1px solid var(--spm-slate-200);
        border-radius: 12px;
    }

    .spm-delete-steps-info {
        padding: 1rem 1.1rem;
        border-radius: 12px;
        background: #fff;
        border: 2px solid #fca5a5;
    }

    .spm-delete-steps-info__head {
        display: flex;
        align-items: center;
        gap: 0.35rem;
        margin-bottom: 0.75rem;
        font-size: 0.9rem;
        color: #b91c1c;
    }

    .spm-delete-steps-list {
        list-style: none;
        padding: 0;
        margin: 0;
        max-height: 280px;
        overflow-y: auto;
    }

    .spm-delete-step-item {
        padding: 0.75rem 0;
        border-bottom: 1px solid #fee2e2;
    }

    .spm-delete-step-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .spm-step-num {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 28px;
        height: 28px;
        border-radius: 8px;
        background: #fef2f2;
        color: #b91c1c;
        font-weight: 700;
        font-size: 0.85rem;
        flex-shrink: 0;
    }

    .spm-type-pill {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .spm-type-pill--regular { background: #ecfdf5; color: #047857; }
    .spm-type-pill--analysis { background: #e0f2fe; color: #0369a1; }
    </style>

    <script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('spm-modal-opened', () => {
            document.body.classList.add('modal-open');
            document.body.style.overflow = 'hidden';
        });
        Livewire.on('spm-modal-closed', () => {
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
        });
    });
    </script>
</div>
