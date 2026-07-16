@php
    $stepTypeRegular = \App\Models\SolutionPreparationStepTemplate::STEP_TYPE_REGULAR;
    $stepTypeAnalysis = \App\Models\SolutionPreparationStepTemplate::STEP_TYPE_ANALYSIS;
    $isRegularStep = ($templateForm['step_type'] ?? $stepTypeRegular) === $stepTypeRegular;
@endphp

<div class="solution-preparation-templates">
    @if($message)
        <div class="alert alert-{{ $messageType === 'success' ? 'success' : 'danger' }} alert-dismissible fade show spt-alert" role="alert">
            <i class="mdi mdi-{{ $messageType === 'success' ? 'check-circle' : 'alert-circle' }} me-1"></i>
            {{ $message }}
            <button type="button" class="btn-close" wire:click="dismissMessage"></button>
        </div>
    @endif

    <div class="scd-panel card border-0 shadow-sm">
        <div class="scd-panel__head scd-panel__head--split">
            <div class="d-flex align-items-center gap-2">
                <span class="scd-panel__icon"><i class="mdi mdi-format-list-numbered"></i></span>
                <div>
                    <h6 class="mb-0">Preparation step templates</h6>
                    <small class="text-muted">Default workflow steps for new preparations</small>
                </div>
            </div>
            <button wire:click="showAddTemplateModal" class="btn btn-sm btn-primary spt-add-btn">
                <i class="mdi mdi-plus"></i> Add preparation step
            </button>
        </div>
        <div class="card-body p-0">
            @if($this->templates->count())
                <div class="table-responsive">
                    <table class="table table-hover mb-0 scd-table spt-table">
                        <thead>
                            <tr>
                                <th style="width: 96px;">Actions</th>
                                <th style="width: 56px;">#</th>
                                <th>Step name</th>
                                <th>Type</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($this->templates as $tpl)
                                <tr>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <button type="button"
                                                    wire:click="showEditTemplateModal('{{ $tpl->id }}')"
                                                    class="btn btn-sm rm-act-btn rm-act-btn--edit"
                                                    title="Edit step">
                                                <i class="mdi mdi-pencil"></i>
                                            </button>
                                            <button type="button"
                                                    wire:click="showDeleteTemplateModal('{{ $tpl->id }}')"
                                                    class="btn btn-sm rm-act-btn rm-act-btn--delete"
                                                    title="Delete step">
                                                <i class="mdi mdi-delete"></i>
                                            </button>
                                        </div>
                                    </td>
                                    <td><span class="spt-step-num">{{ $tpl->step_number }}</span></td>
                                    <td class="fw-semibold">{{ $tpl->step_name }}</td>
                                    <td>
                                        <span class="spt-type-pill {{ $tpl->isAnalysisStep() ? 'spt-type-pill--analysis' : 'spt-type-pill--regular' }}">
                                            <i class="mdi mdi-{{ $tpl->isAnalysisStep() ? 'chart-timeline-variant' : 'flask-outline' }}"></i>
                                            {{ $tpl->isAnalysisStep() ? 'Analysis' : 'Ingredient' }}
                                        </span>
                                    </td>
                                    <td class="small text-muted">
                                        @if($tpl->isRegularStep())
                                            {{ $tpl->ingredient?->reagent?->name ?? '—' }}
                                            @if($tpl->ingredient?->amount_used)
                                                <span class="text-muted">· {{ $tpl->ingredient->amount_used }} {{ $tpl->ingredient->unitMeasure?->name }}</span>
                                            @endif
                                        @else
                                            {{ count($tpl->selected_analytes ?? []) }} analyte(s),
                                            {{ $tpl->controls->count() }} control(s)
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="spt-empty text-center py-5 px-4">
                    <div class="spt-empty__icon"><i class="mdi mdi-playlist-plus"></i></div>
                    <h6 class="mt-3 mb-1">No preparation steps yet</h6>
                    <p class="text-muted mb-3">Define the default steps used when preparing this solution.</p>
                    <button wire:click="showAddTemplateModal" class="btn btn-outline-primary btn-sm">
                        <i class="mdi mdi-plus"></i> Add first preparation step
                    </button>
                </div>
            @endif
        </div>
    </div>

    @if($showDeleteModal && $this->deletingTemplate)
        @php $tplDelete = $this->deletingTemplate; @endphp
        <div class="modal fade show d-block spt-modal-backdrop" tabindex="-1" role="dialog" aria-modal="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content spt-modal-content border-0 shadow">
                    <div class="modal-header spt-modal-header border-0">
                        <div>
                            <h5 class="modal-title mb-0 text-danger">
                                <i class="mdi mdi-delete-alert"></i> Delete preparation step
                            </h5>
                            <small class="text-muted">Review this step before removing it from the template workflow</small>
                        </div>
                        <button type="button" class="btn-close" wire:click="closeDeleteTemplateModal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body spt-modal-body pt-0">
                        <div class="spt-delete-step card border-0 bg-light mb-3">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-start gap-3">
                                    <span class="spt-step-num spt-step-num--lg">{{ $tplDelete->step_number }}</span>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 fw-semibold">{{ $tplDelete->step_name }}</h6>
                                        <span class="spt-type-pill {{ $tplDelete->isAnalysisStep() ? 'spt-type-pill--analysis' : 'spt-type-pill--regular' }}">
                                            <i class="mdi mdi-{{ $tplDelete->isAnalysisStep() ? 'chart-timeline-variant' : 'flask-outline' }}"></i>
                                            {{ $tplDelete->isAnalysisStep() ? 'Analysis step' : 'Ingredient step' }}
                                        </span>
                                        <p class="small text-muted mb-0 mt-2">
                                            @if($tplDelete->isRegularStep())
                                                <strong>Reagent:</strong> {{ $tplDelete->ingredient?->reagent?->name ?? '—' }}
                                                @if($tplDelete->ingredient?->amount_used)
                                                    · {{ $tplDelete->ingredient->amount_used }} {{ $tplDelete->ingredient->unitMeasure?->name }}
                                                @endif
                                            @else
                                                <strong>Analysis:</strong>
                                                {{ count($tplDelete->selected_analytes ?? []) }} analyte(s),
                                                {{ $tplDelete->controls->count() }} control(s)
                                            @endif
                                        </p>
                                        @if($tplDelete->description)
                                            <p class="small mb-0 mt-1">{{ $tplDelete->description }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if($deleteImpact['total_count'] > 0)
                            <div class="spt-delete-disclaimer">
                                <div class="spt-delete-disclaimer__head">
                                    <i class="mdi mdi-alert-circle-outline"></i>
                                    <strong>Used in existing preparations</strong>
                                </div>
                                <p class="mb-2 small">
                                    This step appears in <strong>{{ $deleteImpact['total_count'] }}</strong>
                                    preparation{{ $deleteImpact['total_count'] === 1 ? '' : 's' }} for this solution.
                                    Removing the template will not change completed or in-progress preparations unless you choose to remove the step from active ones below.
                                </p>
                                <ul class="spt-delete-prep-list mb-0">
                                    @foreach($deleteImpact['preparations'] as $prep)
                                        <li>
                                            <span class="fw-semibold">{{ $prep['preparation_number'] }}</span>
                                            @if(!empty($prep['batch_number']))
                                                <span class="text-muted">· Batch {{ $prep['batch_number'] }}</span>
                                            @endif
                                            <span class="spt-prep-status spt-prep-status--{{ $prep['status'] }}">
                                                {{ str_replace('_', ' ', ucfirst($prep['status'])) }}
                                            </span>
                                            @if(!empty($prep['prepared_at']))
                                                <span class="text-muted small">· {{ $prep['prepared_at'] }}</span>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                                @if($deleteImpact['total_count'] > count($deleteImpact['preparations']))
                                    <p class="small text-muted mb-0 mt-2">
                                        Showing {{ count($deleteImpact['preparations']) }} of {{ $deleteImpact['total_count'] }} preparations.
                                    </p>
                                @endif
                            </div>

                            @if($deleteImpact['preparing_count'] > 0)
                                <div class="form-check mt-3 spt-delete-option">
                                    <input class="form-check-input"
                                           type="checkbox"
                                           id="deleteFromPreparations"
                                           wire:model.live="deleteFromPreparations">
                                    <label class="form-check-label small" for="deleteFromPreparations">
                                        Also remove this step from <strong>{{ $deleteImpact['preparing_count'] }}</strong>
                                        in-progress preparation{{ $deleteImpact['preparing_count'] === 1 ? '' : 's' }}
                                    </label>
                                </div>
                            @endif
                        @else
                            <div class="spt-delete-disclaimer spt-delete-disclaimer--safe">
                                <i class="mdi mdi-check-circle-outline"></i>
                                <span>This step is not used in any existing preparations. Only the template workflow will be updated.</span>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer spt-modal-footer border-0">
                        <button type="button" class="btn btn-light" wire:click="closeDeleteTemplateModal">Cancel</button>
                        <button type="button"
                                class="btn btn-danger"
                                wire:click="confirmDeleteTemplate"
                                wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="confirmDeleteTemplate">
                                <i class="mdi mdi-delete"></i> Delete step
                            </span>
                            <span wire:loading wire:target="confirmDeleteTemplate">
                                <span class="spinner-border spinner-border-sm me-1" role="status"></span> Deleting...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($showTemplateModal)
        <div class="modal fade show d-block spt-modal-backdrop" tabindex="-1" role="dialog" aria-modal="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content spt-modal-content border-0 shadow">
                    <div class="modal-header spt-modal-header border-0">
                        <div>
                            <h5 class="modal-title mb-0">
                                <i class="mdi mdi-{{ $editingTemplateId ? 'pencil' : 'playlist-plus' }} text-primary"></i>
                                {{ $editingTemplateId ? 'Edit' : 'Add' }} preparation step
                            </h5>
                            <small class="text-muted">Configure a default step for the preparation workflow</small>
                        </div>
                        <button type="button" class="btn-close" wire:click="closeTemplateModal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body spt-modal-body pt-0">
                        <form wire:submit.prevent="saveTemplate" class="spt-form">
                            {{-- Step basics --}}
                            <section class="spt-form-section">
                                <h6 class="spt-form-section__title">Step details</h6>
                                <div class="row g-3">
                                    <div class="col-sm-3">
                                        <label class="spt-label">Step # <span class="text-danger">*</span></label>
                                        <input type="number"
                                               wire:model="templateForm.step_number"
                                               class="form-control spt-input @error('templateForm.step_number') is-invalid @enderror"
                                               min="1">
                                        @error('templateForm.step_number')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-sm-9">
                                        <label class="spt-label">Step name <span class="text-danger">*</span></label>
                                        <input type="text"
                                               wire:model="templateForm.step_name"
                                               class="form-control spt-input @error('templateForm.step_name') is-invalid @enderror"
                                               placeholder="e.g. Add buffer, Run analysis">
                                        @error('templateForm.step_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </section>

                            {{-- Step type --}}
                            <section class="spt-form-section">
                                <h6 class="spt-form-section__title">Step type</h6>
                                <div class="spt-type-picker">
                                    <button type="button"
                                            class="spt-type-picker__option {{ $isRegularStep ? 'spt-type-picker__option--active' : '' }}"
                                            wire:click="setTemplateStepType(@js($stepTypeRegular))">
                                        <span class="spt-type-picker__icon spt-type-picker__icon--regular">
                                            <i class="mdi mdi-flask-outline"></i>
                                        </span>
                                        <span class="spt-type-picker__text">
                                            <strong>Ingredient step</strong>
                                            <small>Use a linked reagent from this solution</small>
                                        </span>
                                    </button>
                                    <button type="button"
                                            class="spt-type-picker__option {{ ! $isRegularStep ? 'spt-type-picker__option--active' : '' }}"
                                            wire:click="setTemplateStepType(@js($stepTypeAnalysis))">
                                        <span class="spt-type-picker__icon spt-type-picker__icon--analysis">
                                            <i class="mdi mdi-chart-timeline-variant"></i>
                                        </span>
                                        <span class="spt-type-picker__text">
                                            <strong>Analysis step</strong>
                                            <small>Sample type, analytes &amp; controls</small>
                                        </span>
                                    </button>
                                </div>
                            </section>

                            @if($isRegularStep)
                                <section class="spt-form-section spt-form-section--accent">
                                    <h6 class="spt-form-section__title">
                                        <i class="mdi mdi-flask-outline"></i> Ingredient
                                    </h6>
                                    @if($ingredients->count())
                                        <label class="spt-label">Linked reagent <span class="text-danger">*</span></label>
                                        <div class="tag-select-container @error('ingredient_id') is-invalid @enderror"
                                             wire:click="openIngredientDropdown"
                                             wire:click.outside="closeIngredientDropdown">
                                            <div class="tag-select-input">
                                                @if($this->selectedIngredient)
                                                    <span class="tag-badge tag-badge--success">
                                                        {{ $this->selectedIngredient->reagent?->name ?? 'Unknown' }}
                                                        @if($this->selectedIngredient->amount_used)
                                                            <span class="tag-badge__meta">
                                                                {{ $this->selectedIngredient->amount_used }}
                                                                {{ $this->selectedIngredient->unitMeasure?->name }}
                                                            </span>
                                                        @endif
                                                        <i class="mdi mdi-close-circle" wire:click.stop="clearIngredient"></i>
                                                    </span>
                                                @endif
                                                <input type="text"
                                                       wire:model.live.debounce.200ms="ingredientSearch"
                                                       class="tag-input"
                                                       placeholder="{{ $this->selectedIngredient ? '' : 'Search linked reagents...' }}"
                                                       autocomplete="off">
                                            </div>
                                            @if($showIngredientDropdown)
                                                <div class="tag-dropdown">
                                                    @forelse($this->filteredIngredients as $ing)
                                                        <div class="tag-dropdown-item"
                                                             wire:click.stop="selectIngredient('{{ $ing->id }}')">
                                                            <span>{{ $ing->reagent?->name ?? 'Unknown' }}</span>
                                                            @if($ing->amount_used)
                                                                <small class="text-muted ms-1">
                                                                    — {{ $ing->amount_used }} {{ $ing->unitMeasure?->name ?? '' }}
                                                                </small>
                                                            @endif
                                                            @if($ing->reagent?->code)
                                                                <small class="text-muted ms-1">({{ $ing->reagent->code }})</small>
                                                            @endif
                                                        </div>
                                                    @empty
                                                        <div class="tag-dropdown-item text-muted">No matching reagents</div>
                                                    @endforelse
                                                </div>
                                            @endif
                                        </div>
                                        @error('ingredient_id')
                                            <div class="spt-field-error">{{ $message }}</div>
                                        @enderror
                                    @else
                                        <div class="spt-hint-box">
                                            <i class="mdi mdi-information-outline"></i>
                                            <span>No reagents linked to this solution yet. Add reagents on the <strong>Reagents</strong> tab first.</span>
                                        </div>
                                    @endif
                                </section>
                            @else
                                <section class="spt-form-section spt-form-section--accent">
                                    <h6 class="spt-form-section__title">
                                        <i class="mdi mdi-chart-timeline-variant"></i> Analysis configuration
                                    </h6>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="spt-label">Sample type</label>
                                            <x-searchable-select
                                                wire:model.live="templateForm.sample_type_id"
                                                :options="collect($sampleTypes)->map(fn($st) => ['id' => $st->id, 'name' => $st->name])"
                                                placeholder="Search sample types..."
                                                empty-label="Select sample type..."
                                            />
                                        </div>
                                        <div class="col-md-6">
                                            <label class="spt-label">Analysis type</label>
                                            <x-searchable-select
                                                wire:model.live="templateForm.analysis_type_id"
                                                :options="collect($analysisTypes)->map(fn($at) => ['id' => $at->id, 'name' => $at->name])"
                                                placeholder="Search analysis types..."
                                                empty-label="Select analysis type..."
                                                :disabled="! $templateForm['sample_type_id']"
                                            />
                                        </div>
                                    </div>

                                    @if(count($analyteOptions))
                                        <div class="mt-3">
                                            <label class="spt-label">Analytes</label>
                                            <div class="spt-analyte-grid">
                                                @foreach($analyteOptions as $a)
                                                    <label class="spt-analyte-chip">
                                                        <input type="checkbox"
                                                               class="spt-analyte-chip__input"
                                                               wire:model="templateForm.selected_analytes"
                                                               value="{{ $a['id'] }}">
                                                        <span class="spt-analyte-chip__label">{{ $a['name'] }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    @elseif($templateForm['analysis_type_id'])
                                        <p class="spt-muted-note mt-2 mb-0">No analytes available for this analysis type.</p>
                                    @endif

                                    <div class="mt-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label class="spt-label mb-0">Quality controls</label>
                                            <button type="button" wire:click="addControlRow" class="btn btn-sm btn-outline-primary">
                                                <i class="mdi mdi-plus"></i> Add control
                                            </button>
                                        </div>
                                        @forelse($templateControls as $i => $ctrl)
                                            <div class="spt-control-row" wire:key="control-row-{{ $i }}">
                                                <x-searchable-select
                                                    wire:model="templateControls.{{ $i }}.control_solution_id"
                                                    :options="collect($controlSolutions)->map(fn($sol) => ['id' => $sol->id, 'name' => $sol->name])"
                                                    placeholder="Search control solutions..."
                                                    empty-label="Control solution..."
                                                    size="sm"
                                                />
                                                <input type="text"
                                                       wire:model="templateControls.{{ $i }}.label"
                                                       class="form-control spt-input"
                                                       placeholder="Label (optional)">
                                                <button type="button"
                                                        wire:click="removeControlRow({{ $i }})"
                                                        class="btn btn-sm spt-control-row__remove"
                                                        title="Remove control">
                                                    <i class="mdi mdi-close"></i>
                                                </button>
                                            </div>
                                        @empty
                                            <p class="spt-muted-note mb-0">No controls added. Optional reference solutions for this step.</p>
                                        @endforelse
                                    </div>
                                </section>
                            @endif

                            <section class="spt-form-section">
                                <h6 class="spt-form-section__title">Instructions</h6>
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="spt-label">Description</label>
                                        <textarea wire:model="templateForm.description"
                                                  class="form-control spt-input"
                                                  rows="2"
                                                  placeholder="What should be done in this step?"></textarea>
                                    </div>
                                    <div class="col-12">
                                        <label class="spt-label">Notes</label>
                                        <textarea wire:model="templateForm.notes"
                                                  class="form-control spt-input"
                                                  rows="2"
                                                  placeholder="Internal notes for preparers (optional)"></textarea>
                                    </div>
                                </div>
                            </section>
                        </form>
                    </div>
                    <div class="modal-footer spt-modal-footer border-0">
                        <button type="button" class="btn btn-light" wire:click="closeTemplateModal">Cancel</button>
                        <button type="button"
                                class="btn btn-primary spt-save-btn"
                                wire:click="saveTemplate"
                                wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="saveTemplate">
                                <i class="mdi mdi-content-save"></i>
                                {{ $editingTemplateId ? 'Save changes' : 'Add preparation step' }}
                            </span>
                            <span wire:loading wire:target="saveTemplate">
                                <span class="spinner-border spinner-border-sm me-1" role="status"></span> Saving...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @include('livewire.lab.partials.scd-styles')

    <style>
    .solution-preparation-templates {
        --spt-primary: #2563eb;
        --spt-primary-soft: #eff6ff;
        --spt-slate-50: #f8fafc;
        --spt-slate-100: #f1f5f9;
        --spt-slate-200: #e2e8f0;
        --spt-slate-500: #64748b;
        --spt-slate-800: #1e293b;
        --spt-radius: 12px;
    }

    .spt-alert { border-radius: var(--spt-radius); border: none; }
    .spt-add-btn { border-radius: 10px; font-weight: 600; }

    .spt-table thead th {
        font-size: 0.78rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--spt-slate-500);
        background: var(--spt-slate-50);
        border-bottom: 1px solid var(--spt-slate-200);
    }

    .spt-step-num {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 28px;
        height: 28px;
        border-radius: 8px;
        background: var(--spt-primary-soft);
        color: #1d4ed8;
        font-weight: 700;
        font-size: 0.85rem;
    }

    .spt-type-pill {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .spt-type-pill--regular { background: #ecfdf5; color: #047857; }
    .spt-type-pill--analysis { background: #e0f2fe; color: #0369a1; }

    .solution-preparation-templates .rm-act-btn {
        border-radius: 8px;
        padding: 4px 8px;
        font-size: 12px;
    }

    .solution-preparation-templates .rm-act-btn--edit {
        border: 1px solid #bfdbfe;
        color: #1d4ed8;
        background: #eff6ff;
    }

    .solution-preparation-templates .rm-act-btn--edit:hover {
        background: #dbeafe;
    }

    .solution-preparation-templates .rm-act-btn--delete {
        border: 1px solid #fecaca;
        color: #b91c1c;
        background: #fef2f2;
    }

    .solution-preparation-templates .rm-act-btn--delete:hover {
        background: #fee2e2;
    }

    .spt-step-num--lg {
        min-width: 36px;
        height: 36px;
        font-size: 1rem;
    }

    .spt-delete-step {
        border-radius: 12px;
    }

    .spt-delete-disclaimer {
        padding: 1rem 1.1rem;
        border-radius: 12px;
        background: #fffbeb;
        border: 1px solid #fde68a;
        color: #78350f;
    }

    .spt-delete-disclaimer--safe {
        display: flex;
        align-items: flex-start;
        gap: 0.5rem;
        background: #ecfdf5;
        border-color: #a7f3d0;
        color: #047857;
        font-size: 0.9rem;
    }

    .spt-delete-disclaimer__head {
        display: flex;
        align-items: center;
        gap: 0.35rem;
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
    }

    .spt-delete-prep-list {
        list-style: none;
        padding: 0;
        margin: 0;
        max-height: 140px;
        overflow-y: auto;
    }

    .spt-delete-prep-list li {
        padding: 0.45rem 0;
        border-bottom: 1px solid rgba(253, 230, 138, 0.6);
        font-size: 0.85rem;
    }

    .spt-delete-prep-list li:last-child {
        border-bottom: none;
    }

    .spt-prep-status {
        display: inline-block;
        margin-left: 0.35rem;
        padding: 2px 8px;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 600;
        text-transform: capitalize;
        background: #f1f5f9;
        color: #475569;
    }

    .spt-prep-status--preparing {
        background: #fef3c7;
        color: #b45309;
    }

    .spt-prep-status--awaiting_approval {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .spt-prep-status--completed,
    .spt-prep-status--approved {
        background: #ecfdf5;
        color: #047857;
    }

    .spt-delete-option {
        padding: 0.75rem 1rem;
        background: var(--spt-slate-50);
        border-radius: 10px;
        border: 1px solid var(--spt-slate-200);
    }

    .spt-empty__icon {
        width: 56px;
        height: 56px;
        margin: 0 auto;
        border-radius: 14px;
        background: var(--spt-slate-100);
        color: var(--spt-slate-500);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
    }

    .spt-modal-backdrop {
        background-color: rgba(15, 23, 42, 0.45);
        overflow-y: auto;
    }

    .spt-modal-content { border-radius: 16px; }
    .spt-modal-header,
    .spt-modal-footer { padding: 1.25rem 1.5rem; }
    .spt-modal-body { padding: 0 1.5rem 1rem; }

    .spt-form-section {
        margin-bottom: 1.25rem;
        padding-bottom: 1.25rem;
        border-bottom: 1px solid var(--spt-slate-200);
    }

    .spt-form-section:last-child {
        margin-bottom: 0;
        padding-bottom: 0;
        border-bottom: none;
    }

    .spt-form-section--accent {
        background: var(--spt-slate-50);
        margin-left: -1.5rem;
        margin-right: -1.5rem;
        padding: 1rem 1.5rem 1.25rem;
        border-bottom: 1px solid var(--spt-slate-200);
    }

    .spt-form-section__title {
        font-size: 0.82rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--spt-slate-500);
        margin-bottom: 0.85rem;
    }

    .spt-label {
        display: block;
        font-size: 0.82rem;
        font-weight: 600;
        color: #475569;
        margin-bottom: 0.35rem;
    }

    .spt-input {
        border-radius: 10px;
        border-color: var(--spt-slate-200);
        font-size: 0.9rem;
    }

    .spt-input:focus {
        border-color: var(--spt-primary);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
    }

    .spt-type-picker {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.75rem;
    }

    @media (max-width: 576px) {
        .spt-type-picker { grid-template-columns: 1fr; }
    }

    .spt-type-picker__option {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        padding: 0.85rem 1rem;
        border: 2px solid var(--spt-slate-200);
        border-radius: 12px;
        background: #fff;
        text-align: left;
        cursor: pointer;
        transition: border-color 0.2s, background 0.2s, box-shadow 0.2s;
    }

    .spt-type-picker__option:hover {
        border-color: #93c5fd;
        background: var(--spt-slate-50);
    }

    .spt-type-picker__option--active {
        border-color: var(--spt-primary);
        background: var(--spt-primary-soft);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    .spt-type-picker__icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        flex-shrink: 0;
    }

    .spt-type-picker__icon--regular { background: #ecfdf5; color: #047857; }
    .spt-type-picker__icon--analysis { background: #e0f2fe; color: #0369a1; }

    .spt-type-picker__text {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .spt-type-picker__text strong {
        font-size: 0.9rem;
        color: var(--spt-slate-800);
    }

    .spt-type-picker__text small {
        font-size: 0.78rem;
        color: var(--spt-slate-500);
    }

    .spt-hint-box {
        display: flex;
        align-items: flex-start;
        gap: 0.5rem;
        padding: 0.85rem 1rem;
        border-radius: 10px;
        background: #fffbeb;
        border: 1px solid #fde68a;
        color: #92400e;
        font-size: 0.88rem;
    }

    .spt-field-error {
        display: block;
        font-size: 0.8rem;
        color: #dc3545;
        margin-top: 0.25rem;
    }

    .spt-muted-note {
        font-size: 0.85rem;
        color: var(--spt-slate-500);
    }

    .spt-analyte-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        max-height: 160px;
        overflow-y: auto;
        padding: 0.5rem;
        background: #fff;
        border: 1px solid var(--spt-slate-200);
        border-radius: 10px;
    }

    .spt-analyte-chip {
        display: inline-flex;
        align-items: center;
        margin: 0;
        cursor: pointer;
    }

    .spt-analyte-chip__input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .spt-analyte-chip__label {
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 0.82rem;
        font-weight: 500;
        border: 1px solid var(--spt-slate-200);
        background: #fff;
        color: var(--spt-slate-800);
        transition: background 0.15s, border-color 0.15s, color 0.15s;
    }

    .spt-analyte-chip__input:checked + .spt-analyte-chip__label {
        background: var(--spt-primary);
        border-color: var(--spt-primary);
        color: #fff;
    }

    .spt-control-row {
        display: grid;
        grid-template-columns: 1fr 1fr auto;
        gap: 0.5rem;
        margin-bottom: 0.5rem;
        align-items: center;
    }

    @media (max-width: 768px) {
        .spt-control-row { grid-template-columns: 1fr; }
    }

    .spt-control-row__remove {
        width: 36px;
        height: 36px;
        padding: 0;
        border-radius: 10px;
        border: 1px solid #fecaca;
        color: #b91c1c;
        background: #fff;
    }

    .spt-save-btn {
        border-radius: 10px;
        font-weight: 600;
        padding-left: 1.25rem;
        padding-right: 1.25rem;
    }

    .modal.show { display: block !important; }

    /* Tag select (linked reagent) */
    .solution-preparation-templates .tag-select-container {
        position: relative;
        width: 100%;
        cursor: text;
    }

    .solution-preparation-templates .tag-select-input {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 6px;
        min-height: 42px;
        padding: 6px 12px;
        background: #fff;
        border: 1px solid var(--spt-slate-200);
        border-radius: 10px;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .solution-preparation-templates .tag-select-input:hover {
        border-color: #93c5fd;
    }

    .solution-preparation-templates .tag-select-input:focus-within {
        border-color: var(--spt-primary);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
    }

    .solution-preparation-templates .tag-input {
        flex: 1;
        min-width: 120px;
        border: none;
        outline: none;
        padding: 4px 0;
        font-size: 0.9rem;
        background: transparent;
    }

    .solution-preparation-templates .tag-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 0.82rem;
        font-weight: 500;
        white-space: nowrap;
    }

    .solution-preparation-templates .tag-badge--success {
        background: #ecfdf5;
        color: #047857;
        border: 1px solid #a7f3d0;
    }

    .solution-preparation-templates .tag-badge__meta {
        font-size: 0.75rem;
        opacity: 0.85;
        font-weight: 400;
    }

    .solution-preparation-templates .tag-badge i {
        cursor: pointer;
        font-size: 1rem;
        opacity: 0.75;
    }

    .solution-preparation-templates .tag-badge i:hover {
        opacity: 1;
    }

    .solution-preparation-templates .tag-dropdown {
        position: absolute;
        top: calc(100% + 4px);
        left: 0;
        right: 0;
        z-index: 1200;
        background: #fff;
        border: 1px solid var(--spt-slate-200);
        border-radius: 10px;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.1);
        max-height: 220px;
        overflow-y: auto;
    }

    .solution-preparation-templates .tag-dropdown-item {
        padding: 10px 14px;
        cursor: pointer;
        font-size: 0.9rem;
        border-bottom: 1px solid var(--spt-slate-100);
        transition: background 0.15s;
    }

    .solution-preparation-templates .tag-dropdown-item:last-child {
        border-bottom: none;
    }

    .solution-preparation-templates .tag-dropdown-item:hover {
        background: var(--spt-slate-50);
    }

    .solution-preparation-templates .tag-select-container.is-invalid .tag-select-input {
        border-color: #dc3545;
    }
    </style>

    <script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('spt-modal-opened', () => {
            document.body.classList.add('modal-open');
            document.body.style.overflow = 'hidden';
        });
        Livewire.on('spt-modal-closed', () => {
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
        });
    });
    </script>
</div>
