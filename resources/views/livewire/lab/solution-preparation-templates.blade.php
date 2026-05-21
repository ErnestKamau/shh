<div class="solution-preparation-templates">
    @if($message)
        <div class="alert alert-{{ $messageType === 'success' ? 'success' : 'danger' }} alert-dismissible fade show" role="alert">
            {{ $message }}
            <button type="button" class="btn-close" wire:click="$set('message', '')"></button>
        </div>
    @endif

    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="scd-panel card border-0 shadow-sm">
                <div class="scd-panel__head">
                    <span class="scd-panel__icon"><i class="mdi mdi-content-copy"></i></span>
                    <div><h6 class="mb-0">Clone solution</h6></div>
                </div>
                <div class="card-body p-4">
                    <input type="text" wire:model="cloneName" class="form-control scd-input mb-2" placeholder="New solution name">
                    <div class="d-flex gap-2 flex-wrap">
                        <button wire:click="cloneSolution" class="btn btn-sm btn-outline-primary">Simple clone</button>
                        <input type="number" wire:model="scaleFactor" class="form-control form-control-sm" style="max-width:100px" step="0.01" min="0.01">
                        <button wire:click="cloneScaled" class="btn btn-sm btn-outline-secondary">Scaled clone</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="scd-panel card border-0 shadow-sm">
        <div class="scd-panel__head scd-panel__head--split">
            <div class="d-flex align-items-center gap-2">
                <span class="scd-panel__icon"><i class="mdi mdi-format-list-numbered"></i></span>
                <div>
                    <h6 class="mb-0">Preparation step templates</h6>
                    <small class="text-muted">Default workflow for new preparations</small>
                </div>
            </div>
            <button wire:click="showAddTemplateModal" class="btn btn-sm btn-primary">
                <i class="mdi mdi-plus"></i> Add step
            </button>
        </div>
        <div class="card-body p-0">
            @if($this->templates->count())
                <div class="table-responsive">
                    <table class="table table-hover mb-0 scd-table">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Details</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($this->templates as $tpl)
                                <tr>
                                    <td>{{ $tpl->step_number }}</td>
                                    <td>{{ $tpl->step_name }}</td>
                                    <td>
                                        <span class="badge {{ $tpl->isAnalysisStep() ? 'bg-info' : 'bg-secondary' }}">
                                            {{ $tpl->isAnalysisStep() ? 'Analysis' : 'Regular' }}
                                        </span>
                                    </td>
                                    <td class="small text-muted">
                                        @if($tpl->isRegularStep())
                                            {{ $tpl->ingredient?->reagent?->name ?? '—' }}
                                        @else
                                            {{ count($tpl->selected_analytes ?? []) }} analyte(s),
                                            {{ $tpl->controls->count() }} control(s)
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <button wire:click="showEditTemplateModal('{{ $tpl->id }}')" class="btn btn-sm btn-outline-primary"><i class="mdi mdi-pencil"></i></button>
                                        <button wire:click="deleteTemplate('{{ $tpl->id }}')" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete template?')"><i class="mdi mdi-delete"></i></button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5 text-muted">
                    <i class="mdi mdi-playlist-plus mdi-36px"></i>
                    <p class="mb-0 mt-2">No templates yet. Add steps to define the preparation workflow.</p>
                </div>
            @endif
        </div>
    </div>

    @if($showTemplateModal)
        <div class="modal fade show d-block scd-modal-backdrop" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $editingTemplateId ? 'Edit' : 'Add' }} template step</h5>
                        <button type="button" class="btn-close" wire:click="closeTemplateModal"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="saveTemplate">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="scd-label">Step #</label>
                                    <input type="number" wire:model="templateForm.step_number" class="form-control scd-input" min="1">
                                </div>
                                <div class="col-md-9">
                                    <label class="scd-label">Step name</label>
                                    <input type="text" wire:model="templateForm.step_name" class="form-control scd-input" required>
                                </div>
                                <div class="col-12">
                                    <label class="scd-label">Step type</label>
                                    <select wire:model.live="templateForm.step_type" class="form-control scd-input">
                                        <option value="{{ \App\Models\SolutionPreparationStepTemplate::STEP_TYPE_REGULAR }}">Regular (ingredient)</option>
                                        <option value="{{ \App\Models\SolutionPreparationStepTemplate::STEP_TYPE_ANALYSIS }}">Analysis</option>
                                    </select>
                                </div>
                                @if($templateForm['step_type'] === \App\Models\SolutionPreparationStepTemplate::STEP_TYPE_REGULAR)
                                    <div class="col-12">
                                        <label class="scd-label">Ingredient</label>
                                        <select wire:model="templateForm.ingredient_id" class="form-control scd-input">
                                            <option value="">Select...</option>
                                            @foreach($ingredients as $ing)
                                                <option value="{{ $ing->id }}">{{ $ing->reagent?->name }} ({{ $ing->amount_used }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @else
                                    <div class="col-md-6">
                                        <label class="scd-label">Sample type</label>
                                        <select wire:model.live="templateForm.sample_type_id" class="form-control scd-input">
                                            <option value="">Select...</option>
                                            @foreach($sampleTypes as $st)
                                                <option value="{{ $st->id }}">{{ $st->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="scd-label">Analysis type</label>
                                        <select wire:model.live="templateForm.analysis_type_id" class="form-control scd-input">
                                            <option value="">Select...</option>
                                            @foreach($analysisTypes as $at)
                                                <option value="{{ $at->id }}">{{ $at->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="scd-label">Analytes</label>
                                        <select wire:model="templateForm.selected_analytes" class="form-control scd-input" multiple size="5">
                                            @foreach($analyteOptions as $a)
                                                <option value="{{ $a['id'] }}">{{ $a['name'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="scd-label">Controls</label>
                                        @foreach($templateControls as $i => $ctrl)
                                            <div class="d-flex gap-2 mb-2">
                                                <select wire:model="templateControls.{{ $i }}.control_solution_id" class="form-control scd-input">
                                                    <option value="">Solution...</option>
                                                    @foreach($controlSolutions as $sol)
                                                        <option value="{{ $sol->id }}">{{ $sol->name }}</option>
                                                    @endforeach
                                                </select>
                                                <input type="text" wire:model="templateControls.{{ $i }}.label" class="form-control scd-input" placeholder="Label">
                                                <button type="button" wire:click="removeControlRow({{ $i }})" class="btn btn-outline-danger btn-sm"><i class="mdi mdi-close"></i></button>
                                            </div>
                                        @endforeach
                                        <button type="button" wire:click="addControlRow" class="btn btn-sm btn-outline-secondary">Add control</button>
                                    </div>
                                @endif
                                <div class="col-12">
                                    <label class="scd-label">Description</label>
                                    <textarea wire:model="templateForm.description" class="form-control scd-input" rows="2"></textarea>
                                </div>
                            </div>
                            <div class="mt-3 text-end">
                                <button type="button" class="btn btn-light" wire:click="closeTemplateModal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Save template</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @include('livewire.lab.partials.scd-styles')
</div>
