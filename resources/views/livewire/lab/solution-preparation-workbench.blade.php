@php
    $prep = $this->preparation;
@endphp
<div class="solution-preparation-workbench container-fluid py-3" wire:key="prep-{{ $prep->id }}">
    <div class="scd-hero card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                <div class="d-flex gap-3">
                    <a href="{{ route('solutions-preparation-index') }}" class="scd-back-btn"><i class="mdi mdi-arrow-left"></i></a>
                    <div>
                        <p class="scd-eyebrow mb-1">{{ $prep->preparation_number }}</p>
                        <h2 class="scd-title mb-1">{{ $prep->solution?->name }}</h2>
                        <p class="scd-subtitle mb-0">Batch: {{ $prep->batch_number ?? '—' }}</p>
                    </div>
                </div>
                @php
                    $statusClass = match($prep->status) {
                        'preparing' => 'scd-status--preparing',
                        'awaiting_approval' => 'scd-status--awaiting',
                        'completed' => 'scd-status--completed',
                        default => 'scd-status--cancelled',
                    };
                @endphp
                <span class="scd-status {{ $statusClass }}">{{ str_replace('_', ' ', $prep->status) }}</span>
            </div>
            <div class="progress mt-3" style="height: 8px;">
                <div class="progress-bar" style="width: {{ $prep->progressPercent() }}%"></div>
            </div>
            <small class="text-muted">{{ $prep->progressPercent() }}% steps complete</small>
        </div>
    </div>

    @if($message)
        <div class="alert alert-{{ $messageType === 'success' ? 'success' : 'danger' }} alert-dismissible">
            {{ $message }}
            <button type="button" class="btn-close" wire:click="$set('message', '')"></button>
        </div>
    @endif

    <div class="d-flex flex-wrap gap-2 mb-3">
        @if($prep->isInProgress())
            <button wire:click="syncTemplate" class="btn btn-sm btn-outline-secondary"><i class="mdi mdi-sync"></i> Sync template</button>
            <button wire:click="$set('showAdHocModal', true)" class="btn btn-sm btn-outline-primary"><i class="mdi mdi-plus"></i> Add step</button>
            <button wire:click="forceComplete" class="btn btn-sm btn-warning">Submit for approval</button>
            <button wire:click="deletePreparation" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete preparation?')">Delete</button>
        @endif
    </div>

    @foreach($prep->steps as $step)
        <div class="scd-panel card border-0 shadow-sm mb-3">
            <div class="scd-panel__head scd-panel__head--split">
                <div>
                    <strong>Step {{ $step->step_number }}:</strong> {{ $step->step_name }}
                    <span class="badge {{ $step->isAnalysisStep() ? 'bg-info' : 'bg-secondary' }} ms-2">
                        {{ $step->isAnalysisStep() ? 'Analysis' : 'Regular' }}
                    </span>
                    @if($step->isCompleted())
                        <span class="badge bg-success ms-1">Complete</span>
                    @endif
                </div>
                @if($prep->isInProgress())
                    <div class="d-flex gap-1">
                        @if($step->isRegularStep())
                            @if(!$step->isCompleted())
                                <button wire:click="completeStep('{{ $step->id }}')" class="btn btn-sm btn-success">Complete</button>
                            @else
                                <button wire:click="uncompleteStep('{{ $step->id }}')" class="btn btn-sm btn-outline-secondary">Uncomplete</button>
                            @endif
                        @endif
                        <button wire:click="deleteStep('{{ $step->id }}')" class="btn btn-sm btn-outline-danger" onclick="return confirm('Remove step?')"><i class="mdi mdi-delete"></i></button>
                    </div>
                @endif
            </div>
            <div class="card-body">
                @if($step->description)
                    <p class="text-muted small">{{ $step->description }}</p>
                @endif
                @if($step->isRegularStep())
                    <p class="mb-0"><i class="mdi mdi-flask-outline"></i> {{ $step->ingredient?->reagent?->name ?? 'Ingredient' }}</p>
                @else
                    @php $blockers = $step->getCompletionBlockers(); @endphp
                    @if(count($blockers))
                        <div class="alert alert-warning py-2 small mb-2">
                            @foreach($blockers as $b) <div>{{ $b }}</div> @endforeach
                        </div>
                    @endif
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead><tr><th>Analyte</th><th>Sample result</th></tr></thead>
                            <tbody>
                                @foreach($step->selected_analytes ?? [] as $analyteId)
                                    @php $key = "{$step->id}-sample-{$analyteId}"; @endphp
                                    <tr>
                                        <td>{{ $this->getAnalyteName($analyteId) }}</td>
                                        <td>
                                            @if($prep->isInProgress())
                                                <input type="text" wire:model="resultInputs.{{ $key }}.result" class="form-control form-control-sm">
                                            @else
                                                {{ $resultInputs[$key]['result'] ?? '—' }}
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @foreach($step->controls as $control)
                        <h6 class="small mt-2">Control: {{ $control->controlSolution?->name }}</h6>
                        <table class="table table-sm">
                            <tbody>
                                @foreach($step->selected_analytes ?? [] as $analyteId)
                                    @php $key = "{$step->id}-control-{$control->control_solution_id}-{$analyteId}"; @endphp
                                    <tr>
                                        <td>{{ $this->getAnalyteName($analyteId) }}</td>
                                        <td>
                                            @if($prep->isInProgress())
                                                <input type="text" wire:model="resultInputs.{{ $key }}.result" class="form-control form-control-sm">
                                            @else
                                                {{ $resultInputs[$key]['result'] ?? '—' }}
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endforeach
                @endif
            </div>
        </div>
    @endforeach

    @if($prep->isInProgress() && collect($prep->steps)->contains(fn ($s) => $s->isAnalysisStep()))
        <div class="scd-panel card border-0 shadow-sm mb-3">
            <div class="scd-panel__head"><h6 class="mb-0">Analysis results</h6></div>
            <div class="card-body">
                <button wire:click="saveResults" class="btn btn-primary btn-sm">Save all results</button>
            </div>
        </div>
    @endif

    @if($prep->requiresInoculatedMedia() || $prep->isInProgress())
        <div class="scd-panel card border-0 shadow-sm mb-3">
            <div class="scd-panel__head scd-panel__head--split">
                <h6 class="mb-0">Inoculated media</h6>
                @if($prep->isInProgress())
                    <button wire:click="addInoculatedRow" class="btn btn-sm btn-outline-primary">Add row</button>
                @endif
            </div>
            <div class="card-body">
                @foreach($inoculatedRows as $i => $row)
                    <div class="row g-2 mb-2">
                        <div class="col-md-4">
                            <select wire:model="inoculatedRows.{{ $i }}.lab_category_item_id" class="form-control form-control-sm" @disabled(!$prep->isInProgress())>
                                <option value="">Media ingredient...</option>
                                @foreach($this->ingredients as $ing)
                                    <option value="{{ $ing->id }}">{{ $ing->reagent?->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <input type="text" wire:model="inoculatedRows.{{ $i }}.result" class="form-control form-control-sm" placeholder="Result" @disabled(!$prep->isInProgress())>
                        </div>
                        <div class="col-md-4">
                            <input type="text" wire:model="inoculatedRows.{{ $i }}.notes" class="form-control form-control-sm" placeholder="Notes" @disabled(!$prep->isInProgress())>
                        </div>
                    </div>
                @endforeach
                @if($prep->isInProgress())
                    <button wire:click="saveInoculatedMedia" class="btn btn-sm btn-primary mt-2">Save inoculated media</button>
                @endif
            </div>
        </div>
    @endif

    @if($prep->isAwaitingApproval())
        <div class="scd-panel card border-0 shadow-sm border-primary mb-3">
            <div class="scd-panel__head"><h6 class="mb-0"><i class="mdi mdi-check-decagram"></i> Approval</h6></div>
            <div class="card-body">
                <textarea wire:model="approvalNotes" class="form-control scd-input mb-2" rows="2" placeholder="Approval notes (optional)"></textarea>
                <textarea wire:model="rejectReason" class="form-control scd-input mb-3" rows="2" placeholder="Rejection reason"></textarea>
                <button wire:click="approve" class="btn btn-success me-2"><i class="mdi mdi-check"></i> Approve</button>
                <button wire:click="reject" class="btn btn-danger"><i class="mdi mdi-close"></i> Reject</button>
            </div>
        </div>
    @endif

    @if($showAdHocModal)
        <div class="modal fade show d-block scd-modal-backdrop">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add ad-hoc step</h5>
                        <button type="button" class="btn-close" wire:click="$set('showAdHocModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="saveAdHocStep">
                            <input type="text" wire:model="adHocForm.step_name" class="form-control scd-input mb-2" placeholder="Step name" required>
                            <select wire:model="adHocForm.step_type" class="form-control scd-input mb-2">
                                <option value="{{ \App\Models\SolutionPreparationStepTemplate::STEP_TYPE_REGULAR }}">Regular</option>
                                <option value="{{ \App\Models\SolutionPreparationStepTemplate::STEP_TYPE_ANALYSIS }}">Analysis</option>
                            </select>
                            @if($adHocForm['step_type'] === \App\Models\SolutionPreparationStepTemplate::STEP_TYPE_REGULAR)
                                <select wire:model="adHocForm.ingredient_id" class="form-control scd-input mb-2">
                                    <option value="">Ingredient...</option>
                                    @foreach($this->ingredients as $ing)
                                        <option value="{{ $ing->id }}">{{ $ing->reagent?->name }}</option>
                                    @endforeach
                                </select>
                            @endif
                            <div class="form-check mb-2">
                                <input type="checkbox" wire:model="adHocForm.save_to_template" class="form-check-input" id="save_tpl">
                                <label for="save_tpl">Save to template</label>
                            </div>
                            <button type="submit" class="btn btn-primary">Add step</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @include('livewire.lab.partials.scd-styles')
</div>
