@php
    $prep = $this->preparation;
    $stepTypeRegular = \App\Models\SolutionPreparationStepTemplate::STEP_TYPE_REGULAR;
    $completedCount = $prep->steps->filter(fn ($s) => $s->isCompleted())->count();
    $totalSteps = $prep->steps->count();
    $allStepsCompleted = $totalSteps > 0 && $prep->allStepsCompleted();
    $panel = $this->preparationPanel;
@endphp

<div class="solution-preparation-workbench container-fluid py-3" wire:key="prep-{{ $prep->id }}">
    {{-- Hero --}}
    <div class="spw-hero card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                <div class="spw-hero__lead">
                    <a href="{{ route('solutions-preparation-index') }}" class="scd-back-btn" title="Back to preparations">
                        <i class="mdi mdi-arrow-left"></i>
                    </a>
                    <div>
                        <p class="scd-eyebrow mb-1">Preparation workbench</p>
                        <h2 class="scd-title mb-1">{{ $prep->solution?->name }}</h2>
                        <p class="spw-hero__meta mb-0">
                            <span class="spw-meta-chip"><i class="mdi mdi-barcode"></i> {{ $prep->preparation_number }}</span>
                            <span class="spw-meta-chip"><i class="mdi mdi-package-variant"></i> Batch {{ $prep->batch_number ?? '—' }}</span>
                            @if($prep->quantity_prepared)
                                <span class="spw-meta-chip">{{ $prep->quantity_prepared }} {{ $prep->solution?->reportingUnit?->name }}</span>
                            @endif
                        </p>
                    </div>
                </div>
                <div class="spw-hero__actions d-flex flex-wrap align-items-center gap-3 ms-auto">
                    @if($prep->isInProgress() && $allStepsCompleted)
                        <button type="button"
                                wire:click="forceComplete"
                                class="btn btn-outline-success spw-btn"
                                wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="forceComplete">
                                <i class="mdi mdi-send-check-outline"></i> Submit for approval
                            </span>
                            <span wire:loading wire:target="forceComplete">Submitting...</span>
                        </button>
                    @endif
                    @php
                        $statusClass = match($prep->status) {
                            'preparing' => 'scd-status--preparing',
                            'awaiting_approval' => 'scd-status--awaiting',
                            'completed' => 'scd-status--completed',
                            default => 'scd-status--cancelled',
                        };
                    @endphp
                    <span class="scd-status {{ $statusClass }}">{{ str_replace('_', ' ', ucfirst($prep->status)) }}</span>
                    @if($prep->status === 'completed' && ($prep->approved_at || $prep->approval_notes))
                        <div class="spw-hero__approval text-end">
                            @if($prep->approved_at)
                                <div class="spw-hero__approval-date text-muted small">
                                    <i class="mdi mdi-calendar-check"></i>
                                    Approved {{ $prep->approved_at->format('M j, Y g:i A') }}
                                </div>
                            @endif
                            @if($prep->approver)
                                <div class="spw-hero__approval-by small">
                                    <i class="mdi mdi-account-check-outline"></i>
                                    {{ $prep->approver->name }}
                                </div>
                            @endif
                            @if($prep->approval_notes)
                                <div class="spw-hero__approval-notes small text-muted">
                                    <i class="mdi mdi-note-text-outline"></i>
                                    {{ $prep->approval_notes }}
                                </div>
                            @endif
                        </div>
                    @endif
                    @if($prep->isInProgress())
                        <button type="button"
                                wire:click="deletePreparation"
                                class="btn btn-outline-danger btn-sm spw-btn"
                                onclick="return confirm('Delete this preparation run?')">
                            <i class="mdi mdi-delete"></i> Delete
                        </button>
                    @endif
                </div>
            </div>
            <div class="spw-progress-wrap mt-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="spw-progress-label">Progress</span>
                    <span class="spw-progress-value">{{ $completedCount }} / {{ $totalSteps }} steps · {{ $prep->progressPercent() }}%</span>
                </div>
                <div class="spw-progress">
                    <div class="spw-progress__bar" style="width: {{ $prep->progressPercent() }}%"></div>
                </div>
            </div>
        </div>
    </div>

    @if($message)
        <div class="alert alert-{{ $messageType === 'success' ? 'success' : 'danger' }} alert-dismissible fade show spw-alert" role="alert">
            <i class="mdi mdi-{{ $messageType === 'success' ? 'check-circle' : 'alert-circle' }} me-1"></i>
            {{ $message }}
            <button type="button" class="btn-close" wire:click="dismissMessage"></button>
        </div>
    @endif

    <div class="row g-4">
        {{-- Preparation steps --}}
        <div class="col-lg-8">
            @if($prep->isInProgress())
                <div class="spw-toolbar card border-0 shadow-sm mb-3">
                    <div class="card-body py-3 d-flex flex-wrap gap-2 align-items-center justify-content-between">
                        <span class="text-muted small mb-0">
                            <i class="mdi mdi-gesture-tap"></i> Work on one step at a time — select a step below to expand it.
                        </span>
                        <div class="spw-toolbar__actions d-flex flex-wrap">
                            <button type="button" wire:click="syncTemplate" class="btn btn-sm btn-outline-secondary spw-btn">
                                <i class="mdi mdi-sync"></i> Sync template
                            </button>
                            <button type="button" wire:click="openAdHocModal" class="btn btn-sm btn-outline-primary spw-btn">
                                <i class="mdi mdi-plus"></i> Add step
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            @if($totalSteps === 0)
                <div class="spw-empty card border-0 shadow-sm text-center py-5">
                    <i class="mdi mdi-playlist-remove mdi-48px text-muted opacity-50"></i>
                    <p class="mt-3 mb-0 text-muted">No preparation steps yet. Sync from the solution template to begin.</p>
                    @if($prep->isInProgress())
                        <button type="button" wire:click="syncTemplate" class="btn btn-primary btn-sm mt-3">
                            <i class="mdi mdi-sync"></i> Sync template
                        </button>
                    @endif
                </div>
            @else
                @if($highlightedNextStepId && $prep->isInProgress())
                    @php $nextHint = $prep->steps->first(fn ($s) => (string) $s->id === (string) $highlightedNextStepId); @endphp
                    @if($nextHint)
                        <div class="spw-next-hint card border-0 shadow-sm mb-3">
                            <div class="card-body py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
                                <div>
                                    <span class="spw-next-hint__label">Next step</span>
                                    <strong class="d-block">{{ $nextHint->step_name }}</strong>
                                    <span class="text-muted small">
                                        @if($nextHint->isRegularStep())
                                            {{ $nextHint->ingredient?->reagent?->name ?? 'Ingredient step' }}
                                        @else
                                            Analysis · {{ count($nextHint->selected_analytes ?? []) }} analyte(s)
                                        @endif
                                    </span>
                                </div>
                                <button type="button"
                                        class="btn btn-sm btn-primary"
                                        wire:click="setActiveStep('{{ $nextHint->id }}')">
                                    <i class="mdi mdi-play-circle-outline"></i> Work on next step
                                </button>
                            </div>
                        </div>
                    @endif
                @endif

                <div class="spw-timeline">
                    @foreach($prep->steps as $step)
                        @php
                            $isActive = (string) $activeStepId === (string) $step->id;
                            $isDone = $step->isCompleted();
                            $isNextUp = (string) $highlightedNextStepId === (string) $step->id && ! $isActive && ! $isDone;
                            $state = $isDone ? 'done' : ($isActive ? 'active' : ($isNextUp ? 'next-up' : 'pending'));
                            $canWork = $prep->isInProgress();
                        @endphp
                        <div class="spw-timeline__item spw-timeline__item--{{ $state }}" wire:key="step-{{ $step->id }}">
                            <div class="spw-timeline__track">
                                <div class="spw-timeline__dot">
                                    @if($isDone)
                                        <i class="mdi mdi-check"></i>
                                    @elseif($isActive)
                                        <span class="spw-timeline__pulse"></span>
                                    @else
                                        <span class="spw-timeline__dot-num">{{ $step->step_number }}</span>
                                    @endif
                                </div>
                                @if(!$loop->last)
                                    <div class="spw-timeline__line"></div>
                                @endif
                            </div>

                            <div class="spw-timeline__card card border-0 shadow-sm {{ $isActive ? 'spw-timeline__card--active' : '' }}">
                                <div class="spw-timeline__card-head"
                                     @if($canWork && !$isActive)
                                         wire:click="setActiveStep('{{ $step->id }}')"
                                         role="button"
                                     @endif>
                                    <div class="spw-timeline__card-title">
                                        <span class="spw-step-num">Step {{ $step->step_number }}</span>
                                        <h6 class="mb-0">{{ $step->step_name }}</h6>
                                    </div>
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <span class="spw-type-pill {{ $step->isAnalysisStep() ? 'spw-type-pill--analysis' : 'spw-type-pill--regular' }}">
                                            {{ $step->isAnalysisStep() ? 'Analysis' : 'Ingredient' }}
                                        </span>
                                        @if($isDone)
                                            <span class="spw-state-pill spw-state-pill--done">Complete</span>
                                        @elseif($isActive)
                                            <span class="spw-state-pill spw-state-pill--active">In progress</span>
                                        @elseif($isNextUp)
                                            <span class="spw-state-pill spw-state-pill--next">Next up</span>
                                        @else
                                            <span class="spw-state-pill spw-state-pill--pending">Pending</span>
                                        @endif
                                        @if($canWork && !$isActive)
                                            <button type="button"
                                                    class="btn btn-sm btn-primary spw-work-btn"
                                                    wire:click.stop="setActiveStep('{{ $step->id }}')">
                                                Work on step
                                            </button>
                                        @endif
                                    </div>
                                </div>

                                @if($isActive || ! $canWork)
                                    <div class="spw-timeline__card-body">
                                        @if($step->description)
                                            <p class="spw-step-desc">{{ $step->description }}</p>
                                        @endif
                                        @if($step->notes)
                                            <p class="spw-step-notes text-muted small"><i class="mdi mdi-note-text-outline"></i> {{ $step->notes }}</p>
                                        @endif

                                        @if($step->isRegularStep())
                                            <div class="spw-ingredient-box">
                                                <i class="mdi mdi-flask-outline"></i>
                                                <div>
                                                    <span class="spw-ingredient-label">Reagent</span>
                                                    <strong>{{ $step->ingredient?->reagent?->name ?? '—' }}</strong>
                                                    @if($step->ingredient?->amount_used)
                                                        <span class="text-muted">· {{ $step->ingredient->amount_used }} {{ $step->ingredient->unitMeasure?->name }}</span>
                                                    @endif
                                                </div>
                                            </div>

                                            @if($canWork)
                                                <div class="spw-step-actions">
                                                    @if(!$isDone)
                                                        <button type="button"
                                                                class="btn btn-success"
                                                                wire:click="openCompleteStepModal('{{ $step->id }}', false)">
                                                            <i class="mdi mdi-check-circle-outline"></i> Mark step complete
                                                        </button>
                                                    @else
                                                        <button type="button"
                                                                class="btn btn-outline-secondary btn-sm"
                                                                wire:click="uncompleteStep('{{ $step->id }}')">
                                                            Reopen step
                                                        </button>
                                                    @endif
                                                    <button type="button"
                                                            class="btn btn-outline-danger btn-sm"
                                                            wire:click="deleteStep('{{ $step->id }}')"
                                                            onclick="return confirm('Remove this step?')">
                                                        <i class="mdi mdi-delete"></i> Remove
                                                    </button>
                                                </div>
                                            @elseif($isDone && $step->completed_at)
                                                <p class="small text-muted mb-0 mt-2">
                                                    Completed {{ $step->completed_at->format('M j, Y g:i A') }}
                                                </p>
                                            @endif
                                        @else
                                            @php $blockers = $step->getCompletionBlockers(); @endphp
                                            @if($canWork && count($blockers))
                                                <div class="spw-blockers alert alert-warning py-2 small mb-3">
                                                    <strong class="d-block mb-1">Before completing:</strong>
                                                    @foreach($blockers as $b)
                                                        <div>{{ $b }}</div>
                                                    @endforeach
                                                </div>
                                            @endif

                                            <div class="spw-results-grid">
                                                <div class="spw-results-section">
                                                    <h6 class="spw-results-title">Sample results</h6>
                                                    @forelse($step->selected_analytes ?? [] as $analyteId)
                                                        @php $key = "{$step->id}-sample-{$analyteId}"; @endphp
                                                        <div class="spw-result-row">
                                                            <label class="spw-result-label">{{ $this->getAnalyteName($analyteId) }}</label>
                                                            @if($canWork)
                                                                <div class="row g-2">
                                                                    <div class="col-md-7">
                                                                        <input type="text"
                                                                               wire:model="resultInputs.{{ $key }}.result"
                                                                               class="form-control spw-input"
                                                                               placeholder="Enter result">
                                                                    </div>
                                                                    <div class="col-md-5">
                                                                        <select wire:model="resultInputs.{{ $key }}.remark"
                                                                                class="form-select spw-input">
                                                                            <option value="">Remark…</option>
                                                                            <option value="pass">Pass</option>
                                                                            <option value="fail">Fail</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            @else
                                                                <span class="spw-result-readonly">{{ $resultInputs[$key]['result'] ?? '—' }}</span>
                                                                @if(!empty($resultInputs[$key]['remark']))
                                                                    <span class="spw-remark-badge spw-remark-badge--{{ $resultInputs[$key]['remark'] }}">
                                                                        {{ ucfirst($resultInputs[$key]['remark']) }}
                                                                    </span>
                                                                @endif
                                                            @endif
                                                        </div>
                                                    @empty
                                                        <p class="text-muted small mb-0">No analytes configured.</p>
                                                    @endforelse
                                                </div>

                                                @foreach($step->controls as $control)
                                                    <div class="spw-results-section">
                                                        <h6 class="spw-results-title">
                                                            Control · {{ $control->controlSolution?->name ?? 'Solution' }}
                                                            @if($control->label)
                                                                <span class="text-muted fw-normal">({{ $control->label }})</span>
                                                            @endif
                                                        </h6>
                                                        @foreach($step->selected_analytes ?? [] as $analyteId)
                                                            @php $key = "{$step->id}-control-{$control->control_solution_id}-{$analyteId}"; @endphp
                                                            <div class="spw-result-row">
                                                                <label class="spw-result-label">{{ $this->getAnalyteName($analyteId) }}</label>
                                                                @if($canWork)
                                                                    <div class="row g-2">
                                                                        <div class="col-md-7">
                                                                            <input type="text"
                                                                                   wire:model="resultInputs.{{ $key }}.result"
                                                                                   class="form-control spw-input"
                                                                                   placeholder="Enter result">
                                                                        </div>
                                                                        <div class="col-md-5">
                                                                            <select wire:model="resultInputs.{{ $key }}.remark"
                                                                                    class="form-select spw-input">
                                                                                <option value="">Remark…</option>
                                                                                <option value="pass">Pass</option>
                                                                                <option value="fail">Fail</option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                @else
                                                                    <span class="spw-result-readonly">{{ $resultInputs[$key]['result'] ?? '—' }}</span>
                                                                    @if(!empty($resultInputs[$key]['remark']))
                                                                        <span class="spw-remark-badge spw-remark-badge--{{ $resultInputs[$key]['remark'] }}">
                                                                            {{ ucfirst($resultInputs[$key]['remark']) }}
                                                                        </span>
                                                                    @endif
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endforeach
                                            </div>

                                            @if($canWork)
                                                <div class="spw-step-actions">
                                                    <button type="button"
                                                            class="btn btn-outline-primary"
                                                            wire:click="saveStepResultsForStep('{{ $step->id }}')"
                                                            wire:loading.attr="disabled">
                                                        <span wire:loading.remove wire:target="saveStepResultsForStep('{{ $step->id }}')">
                                                            <i class="mdi mdi-content-save-outline"></i> Save results
                                                        </span>
                                                        <span wire:loading wire:target="saveStepResultsForStep('{{ $step->id }}')">Saving...</span>
                                                    </button>
                                                    @if(!$isDone)
                                                        <button type="button"
                                                                class="btn btn-success"
                                                                wire:click="openCompleteStepModal('{{ $step->id }}', true)">
                                                            <i class="mdi mdi-check-circle-outline"></i> Save &amp; complete step
                                                        </button>
                                                    @else
                                                        <button type="button"
                                                                class="btn btn-outline-secondary btn-sm"
                                                                wire:click="uncompleteStep('{{ $step->id }}')">
                                                            Reopen step
                                                        </button>
                                                    @endif
                                                    <button type="button"
                                                            class="btn btn-outline-danger btn-sm"
                                                            wire:click="deleteStep('{{ $step->id }}')"
                                                            onclick="return confirm('Remove this step?')">
                                                        <i class="mdi mdi-delete"></i> Remove
                                                    </button>
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                @elseif($canWork)
                                    <div class="spw-timeline__card-collapsed">
                                        @if($step->isRegularStep())
                                            <span class="text-muted small">{{ $step->ingredient?->reagent?->name ?? 'Ingredient step' }}</span>
                                        @else
                                            <span class="text-muted small">{{ count($step->selected_analytes ?? []) }} analyte(s) · {{ $step->controls->count() }} control(s)</span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Preparation details panel --}}
        <div class="col-lg-4">
            <div class="spw-panel">
                <div class="spw-panel__card card border-0 shadow-sm">
                    <div class="spw-panel__head">
                        <span class="spw-panel__icon"><i class="mdi mdi-clipboard-text-outline"></i></span>
                        <div>
                            <h6 class="spw-panel__title mb-0">Preparation details</h6>
                            <p class="spw-panel__subtitle mb-0">Run summary &amp; required materials</p>
                        </div>
                    </div>

                    <div class="spw-panel__body">
                        <div class="spw-panel__identity">
                            <span class="spw-panel__prep-no">{{ $prep->preparation_number }}</span>
                            <span class="spw-panel__prep-id" title="{{ $prep->id }}">ID {{ \Illuminate\Support\Str::limit($prep->id, 13, '…') }}</span>
                        </div>

                        <dl class="spw-panel__meta">
                            <div class="spw-panel__meta-row">
                                <dt><i class="mdi mdi-account-outline"></i> Prepared by</dt>
                                <dd>{{ $prep->preparer?->name ?? '—' }}</dd>
                            </div>
                            <div class="spw-panel__meta-row">
                                <dt><i class="mdi mdi-clock-outline"></i> Started</dt>
                                <dd>{{ $prep->prepared_at?->format('M j, Y g:i A') ?? '—' }}</dd>
                            </div>
                            @if($prep->approved_at || $prep->approved_by || $prep->approval_notes)
                                <div class="spw-panel__meta-row">
                                    <dt><i class="mdi mdi-check-decagram-outline"></i> Approved by</dt>
                                    <dd>{{ $prep->approver?->name ?? '—' }}</dd>
                                </div>
                                <div class="spw-panel__meta-row">
                                    <dt><i class="mdi mdi-calendar-check"></i> Approved at</dt>
                                    <dd>{{ $prep->approved_at?->format('M j, Y g:i A') ?? '—' }}</dd>
                                </div>
                            @endif
                        </dl>

                        @if($prep->approval_notes)
                            <div class="spw-panel__section spw-panel__section--approval">
                                <h6 class="spw-panel__section-title">Approval notes</h6>
                                <p class="spw-panel__notes mb-0">{{ $prep->approval_notes }}</p>
                            </div>
                        @endif

                        <div class="spw-panel__section">
                            <h6 class="spw-panel__section-title">Yield &amp; batch</h6>
                            <div class="spw-panel__stats">
                                <div class="spw-panel__stat">
                                    <span class="spw-panel__stat-label">Quantity</span>
                                    <span class="spw-panel__stat-value">
                                        @if($prep->quantity_prepared)
                                            {{ rtrim(rtrim(number_format((float) $prep->quantity_prepared, 4, '.', ''), '0'), '.') }}
                                            <small>{{ $panel['yield_uom'] }}</small>
                                        @else
                                            —
                                        @endif
                                    </span>
                                </div>
                                <div class="spw-panel__stat">
                                    <span class="spw-panel__stat-label">Batch</span>
                                    <span class="spw-panel__stat-value">
                                        {{ $prep->batch_number ?? '—' }}
                                        @if($prep->is_new_batch)
                                            <span class="spw-panel__tag">New</span>
                                        @endif
                                    </span>
                                </div>
                                <div class="spw-panel__stat spw-panel__stat--wide">
                                    <span class="spw-panel__stat-label">Expiry</span>
                                    <span class="spw-panel__stat-value spw-panel__expiry spw-panel__expiry--{{ $panel['expiry']['status'] }}">
                                        {{ $panel['expiry']['date'] }}
                                    </span>
                                    @if($panel['expiry']['hint'])
                                        <span class="spw-panel__stat-hint">{{ $panel['expiry']['hint'] }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        @if($prep->notes)
                            <div class="spw-panel__section">
                                <h6 class="spw-panel__section-title">Notes</h6>
                                <p class="spw-panel__notes mb-0">{{ $prep->notes }}</p>
                            </div>
                        @endif

                        @if($prep->alternative_aware && $prep->sourcePreparation)
                            <div class="spw-panel__callout spw-panel__callout--alt">
                                <i class="mdi mdi-swap-horizontal"></i>
                                Paired alternative run linked to
                                <strong>{{ $prep->sourcePreparation->preparation_number }}</strong>
                            </div>
                        @endif

                        <div class="spw-panel__section spw-panel__section--ingredients">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                <h6 class="spw-panel__section-title mb-0">Required ingredients</h6>
                                <span class="spw-panel__scale-hint">{{ $panel['scale_label'] }}</span>
                            </div>
                            @if(count($panel['ingredients']) === 0)
                                <p class="text-muted small mb-0">No recipe ingredients configured for this solution.</p>
                            @else
                                <ul class="spw-ingredient-list list-unstyled mb-0">
                                    @foreach($panel['ingredients'] as $ingredient)
                                        <li class="spw-ingredient-list__item {{ $ingredient['in_workflow'] ? 'spw-ingredient-list__item--active' : '' }}">
                                            <div class="spw-ingredient-list__main">
                                                <span class="spw-ingredient-list__name">{{ $ingredient['name'] }}</span>
                                                @if($ingredient['in_workflow'])
                                                    <span class="spw-ingredient-list__badge">In steps</span>
                                                @endif
                                            </div>
                                            <div class="spw-ingredient-list__amounts">
                                                @if($panel['reference_quantity'] && abs($panel['scale_factor'] - 1) >= 0.0001)
                                                    <span class="spw-ingredient-list__recipe" title="Recipe amount">
                                                        {{ rtrim(rtrim(number_format($ingredient['recipe_amount'], 4, '.', ''), '0'), '.') }}
                                                    </span>
                                                    <i class="mdi mdi-arrow-right-thin spw-ingredient-list__arrow"></i>
                                                @endif
                                                <span class="spw-ingredient-list__calc">
                                                    {{ rtrim(rtrim(number_format($ingredient['calculated_amount'], 4, '.', ''), '0'), '.') }}
                                                    <small>{{ $ingredient['unit'] }}</small>
                                                </span>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>

                        @if($prep->solution?->stability_notes && ! $prep->notes)
                            <div class="spw-panel__callout spw-panel__callout--info">
                                <i class="mdi mdi-shield-check-outline"></i>
                                <span>{{ $prep->solution->stability_notes }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                @if($prep->isAwaitingApproval())
                    <div class="spw-panel__card card border-0 shadow-sm border-primary mt-3">
                        <div class="card-body">
                            <h6 class="spw-panel__section-title text-primary mb-3">
                                <i class="mdi mdi-check-decagram"></i> Approval
                            </h6>
                            <label class="spw-label">Approval notes</label>
                            <textarea wire:model="approvalNotes" class="form-control spw-input mb-3" rows="2" placeholder="Optional notes"></textarea>
                            <label class="spw-label">Rejection reason</label>
                            <textarea wire:model="rejectReason" class="form-control spw-input mb-3" rows="2" placeholder="Required if rejecting"></textarea>
                            <button type="button" wire:click="approve" class="btn btn-success spw-btn w-100 mb-2">
                                <i class="mdi mdi-check"></i> Approve
                            </button>
                            <button type="button" wire:click="reject" class="btn btn-danger spw-btn w-100">
                                <i class="mdi mdi-close"></i> Reject
                            </button>
                        </div>
                    </div>
                @elseif($prep->status === 'completed' && ($prep->approved_at || $prep->approval_notes || $prep->approved_by))
                    <div class="spw-panel__card card border-0 shadow-sm spw-approval-summary mt-3">
                        <div class="card-body">
                            <h6 class="spw-panel__section-title mb-3">
                                <i class="mdi mdi-check-decagram text-success"></i> Approval record
                            </h6>
                            <dl class="spw-panel__meta mb-0">
                                <div class="spw-panel__meta-row">
                                    <dt><i class="mdi mdi-account-check-outline"></i> Approved by</dt>
                                    <dd>{{ $prep->approver?->name ?? '—' }}</dd>
                                </div>
                                <div class="spw-panel__meta-row">
                                    <dt><i class="mdi mdi-clock-check-outline"></i> Date &amp; time</dt>
                                    <dd>{{ $prep->approved_at?->format('M j, Y g:i A') ?? '—' }}</dd>
                                </div>
                                @if($prep->approval_notes)
                                    <div class="spw-panel__meta-row spw-panel__meta-row--stacked">
                                        <dt><i class="mdi mdi-note-text-outline"></i> Notes</dt>
                                        <dd class="spw-panel__approval-notes-dd">{{ $prep->approval_notes }}</dd>
                                    </div>
                                @endif
                            </dl>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        @if($prep->requiresInoculatedMedia() || ($prep->isInProgress() && count($inoculatedRows)))
            <div class="col-12">
                <div class="spw-sidebar-card card border-0 shadow-sm">
                    <div class="card-header spw-sidebar-card__head border-0 bg-transparent d-flex justify-content-between align-items-center">
                        <h6 class="spw-sidebar-title mb-0"><i class="mdi mdi-bacteria-outline"></i> Inoculated media</h6>
                        @if($prep->isInProgress())
                            <button type="button" wire:click="addInoculatedRow" class="btn btn-sm btn-outline-primary spw-btn">Add</button>
                        @endif
                    </div>
                    <div class="card-body pt-0">
                        <div class="row g-3">
                            @forelse($inoculatedRows as $i => $row)
                                <div class="col-md-6 col-lg-4" wire:key="inoc-{{ $i }}">
                                    <div class="spw-inoc-row h-100 p-3 border rounded">
                                        <select wire:model="inoculatedRows.{{ $i }}.lab_category_item_id"
                                                class="form-select form-select-sm spw-input mb-2"
                                                @disabled(!$prep->isInProgress())>
                                            <option value="">Media ingredient...</option>
                                            @foreach($this->ingredients as $ing)
                                                <option value="{{ $ing->id }}">{{ $ing->reagent?->name }}</option>
                                            @endforeach
                                        </select>
                                        <input type="text"
                                               wire:model="inoculatedRows.{{ $i }}.result"
                                               class="form-control form-control-sm spw-input mb-2"
                                               placeholder="Result"
                                               @disabled(!$prep->isInProgress())>
                                        <input type="text"
                                               wire:model="inoculatedRows.{{ $i }}.notes"
                                               class="form-control form-control-sm spw-input"
                                               placeholder="Notes"
                                               @disabled(!$prep->isInProgress())>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    <p class="text-muted small mb-0">No inoculated media rows yet.</p>
                                </div>
                            @endforelse
                        </div>
                        @if($prep->isInProgress() && count($inoculatedRows))
                            <button type="button" wire:click="saveInoculatedMedia" class="btn btn-primary btn-sm spw-btn mt-3">
                                Save inoculated media
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>

    @if($showCompleteStepModal && $this->completingStep)
        @php
            $completing = $this->completingStep;
            $nextAfter = $this->nextStepAfterCompleting;
        @endphp
        <div class="modal fade show d-block spw-modal-backdrop" tabindex="-1" role="dialog" aria-modal="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0 shadow spw-modal-content">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title mb-0">
                            <i class="mdi mdi-check-circle-outline text-success"></i> Complete step
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeCompleteStepModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="spw-complete-summary">
                            <span class="spw-complete-summary__eyebrow">Step summary</span>
                            <h6 class="mb-2">{{ $completing->step_name }}</h6>
                            @if($completing->description)
                                <p class="text-muted small mb-2">{{ $completing->description }}</p>
                            @endif
                            @if($completing->isRegularStep())
                                <div class="spw-complete-summary__detail">
                                    <i class="mdi mdi-flask-outline"></i>
                                    <span>
                                        <strong>{{ $completing->ingredient?->reagent?->name ?? '—' }}</strong>
                                        @if($completing->ingredient?->amount_used)
                                            <span class="text-muted">· {{ $completing->ingredient->amount_used }} {{ $completing->ingredient->unitMeasure?->name }}</span>
                                        @endif
                                    </span>
                                </div>
                            @else
                                <div class="spw-complete-summary__detail">
                                    <i class="mdi mdi-chart-timeline-variant"></i>
                                    <span class="text-muted">
                                        {{ count($completing->selected_analytes ?? []) }} analyte(s) · {{ $completing->controls->count() }} control(s)
                                    </span>
                                </div>
                            @endif
                        </div>

                        @if($nextAfter)
                            <div class="spw-complete-next mt-3">
                                <span class="spw-complete-next__label">Up next</span>
                                <strong>{{ $nextAfter->step_name }}</strong>
                                <span class="text-muted small d-block mt-1">
                                    @if($nextAfter->isRegularStep())
                                        {{ $nextAfter->ingredient?->reagent?->name ?? 'Ingredient step' }}
                                    @else
                                        Analysis · {{ count($nextAfter->selected_analytes ?? []) }} analyte(s)
                                    @endif
                                </span>
                            </div>
                            <div class="form-check mt-3">
                                <input type="checkbox"
                                       wire:model.live="goToNextStepAfterComplete"
                                       class="form-check-input"
                                       id="go_next_step">
                                <label class="form-check-label" for="go_next_step">
                                    Continue to next step automatically
                                </label>
                            </div>
                            @if(! $goToNextStepAfterComplete)
                                <p class="text-muted small mb-0 mt-2">
                                    <i class="mdi mdi-information-outline"></i>
                                    The next step will be shown on the timeline without opening it.
                                </p>
                            @endif
                        @else
                            <p class="text-muted small mb-0 mt-3">
                                <i class="mdi mdi-flag-checkered"></i> This was the last step. You can submit for approval from the page header.
                            </p>
                        @endif
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light" wire:click="closeCompleteStepModal">Cancel</button>
                        <button type="button"
                                class="btn btn-success"
                                wire:click="confirmCompleteStep"
                                wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="confirmCompleteStep">
                                <i class="mdi mdi-check"></i> Confirm complete
                            </span>
                            <span wire:loading wire:target="confirmCompleteStep">Completing...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($showAdHocModal)
        <div class="modal fade show d-block spw-modal-backdrop" tabindex="-1" role="dialog" aria-modal="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow spw-modal-content">
                    <div class="modal-header border-0">
                        <h5 class="modal-title mb-0"><i class="mdi mdi-plus-circle-outline text-primary"></i> Add ad-hoc step</h5>
                        <button type="button" class="btn-close" wire:click="closeAdHocModal"></button>
                    </div>
                    <div class="modal-body pt-0">
                        <form wire:submit.prevent="saveAdHocStep">
                            <label class="spw-label">Step name</label>
                            <input type="text" wire:model="adHocForm.step_name" class="form-control spw-input mb-3" placeholder="Step name" required>
                            <label class="spw-label">Step type</label>
                            <select wire:model.live="adHocForm.step_type" class="form-select spw-input mb-3">
                                <option value="{{ $stepTypeRegular }}">Ingredient</option>
                                <option value="{{ \App\Models\SolutionPreparationStepTemplate::STEP_TYPE_ANALYSIS }}">Analysis</option>
                            </select>
                            @if($adHocForm['step_type'] === $stepTypeRegular)
                                <label class="spw-label">Ingredient</label>
                                <select wire:model="adHocForm.ingredient_id" class="form-select spw-input mb-3">
                                    <option value="">Select ingredient...</option>
                                    @foreach($this->ingredients as $ing)
                                        <option value="{{ $ing->id }}">{{ $ing->reagent?->name }}</option>
                                    @endforeach
                                </select>
                            @endif
                            <div class="form-check mb-3">
                                <input type="checkbox" wire:model="adHocForm.save_to_template" class="form-check-input" id="save_tpl">
                                <label class="form-check-label" for="save_tpl">Save to template</label>
                            </div>
                            <div class="d-flex gap-2 justify-content-end">
                                <button type="button" class="btn btn-light" wire:click="closeAdHocModal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Add step</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @include('livewire.lab.partials.scd-styles')

    <style>
    .solution-preparation-workbench {
        --spw-primary: #2563eb;
        --spw-primary-soft: #eff6ff;
        --spw-slate-50: #f8fafc;
        --spw-slate-100: #f1f5f9;
        --spw-slate-200: #e2e8f0;
        --spw-slate-500: #64748b;
        --spw-slate-800: #1e293b;
        --spw-success: #059669;
        --spw-success-soft: #ecfdf5;
    }

    .spw-hero { border-radius: 14px; background: linear-gradient(135deg, #fff 0%, var(--spw-slate-50) 100%); }
    .spw-hero__lead { display: flex; align-items: flex-start; gap: 1.25rem; flex: 1; min-width: 0; }
    .spw-hero__actions { flex-shrink: 0; align-items: flex-end; }
    .spw-hero__approval {
        display: flex;
        flex-direction: column;
        gap: 0.2rem;
        max-width: 300px;
        padding: 0.5rem 0.75rem;
        background: var(--spw-success-soft);
        border: 1px solid #a7f3d0;
        border-radius: 10px;
    }
    .spw-hero__approval-by { font-weight: 600; color: var(--spw-slate-800); }
    .spw-approval-summary { background: var(--spw-success-soft); border: 1px solid #a7f3d0 !important; }
    .spw-panel__section--approval {
        padding: 0.85rem 1rem;
        background: var(--spw-success-soft);
        border-radius: 10px;
        border: 1px solid #a7f3d0;
    }
    .spw-panel__meta-row--stacked dd { margin-top: 0.25rem; }
    .spw-panel__approval-notes-dd { white-space: pre-wrap; line-height: 1.45; }
    .spw-hero__meta { display: flex; flex-wrap: wrap; gap: 0.5rem; margin-top: 0.5rem; }
    .spw-meta-chip {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 4px 10px; border-radius: 999px; font-size: 0.8rem;
        background: var(--spw-slate-100); color: var(--spw-slate-800); border: 1px solid var(--spw-slate-200);
    }

    .spw-progress-wrap { max-width: 100%; }
    .spw-progress-label { font-size: 0.82rem; font-weight: 600; color: var(--spw-slate-500); text-transform: uppercase; letter-spacing: 0.04em; }
    .spw-progress-value { font-size: 0.85rem; font-weight: 600; color: var(--spw-slate-800); }
    .spw-progress {
        height: 10px; border-radius: 999px; background: var(--spw-slate-200); overflow: hidden;
    }
    .spw-progress__bar {
        height: 100%; border-radius: 999px;
        background: linear-gradient(90deg, #3b82f6, #2563eb);
        transition: width 0.35s ease;
    }

    .spw-alert { border-radius: 12px; border: none; }
    .spw-toolbar { border-radius: 12px; }
    .spw-toolbar__actions { gap: 0.75rem; }
    .solution-preparation-workbench .spw-btn { border-radius: 6px; }

    /* Timeline */
    .spw-timeline { position: relative; padding-left: 0; }
    .spw-timeline__item {
        display: grid;
        grid-template-columns: 48px 1fr;
        gap: 1rem;
        margin-bottom: 1.25rem;
        position: relative;
    }
    .spw-timeline__track {
        display: flex; flex-direction: column; align-items: center;
        position: relative;
    }
    .spw-timeline__dot {
        width: 40px; height: 40px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-weight: 700; font-size: 0.9rem; z-index: 2; flex-shrink: 0;
        border: 3px solid #fff;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.1);
    }
    .spw-timeline__item--pending .spw-timeline__dot {
        background: var(--spw-slate-100); color: var(--spw-slate-500); border-color: var(--spw-slate-200);
    }
    .spw-timeline__item--active .spw-timeline__dot {
        background: var(--spw-primary); color: #fff;
    }
    .spw-timeline__item--done .spw-timeline__dot {
        background: var(--spw-success); color: #fff;
    }
    .spw-timeline__item--next-up .spw-timeline__dot {
        background: #fff; color: var(--spw-success); border: 3px dashed var(--spw-success);
    }
    .spw-timeline__item--next-up .spw-timeline__card {
        outline: 2px dashed rgba(5, 150, 105, 0.45);
        box-shadow: 0 4px 16px rgba(5, 150, 105, 0.08) !important;
    }
    .spw-timeline__item--next-up .spw-timeline__card-head { background: var(--spw-success-soft); }
    .spw-timeline__pulse {
        width: 12px; height: 12px; border-radius: 50%; background: #fff;
        animation: spw-pulse 1.5s ease-in-out infinite;
    }
    @keyframes spw-pulse {
        0%, 100% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.2); opacity: 0.7; }
    }
    .spw-timeline__line {
        flex: 1; width: 3px; min-height: 24px; margin-top: 4px;
        background: var(--spw-slate-200); border-radius: 2px;
    }
    .spw-timeline__item--done .spw-timeline__line { background: #a7f3d0; }
    .spw-timeline__item--active .spw-timeline__line { background: #93c5fd; }

    .spw-timeline__card { border-radius: 14px; overflow: hidden; transition: box-shadow 0.2s, border-color 0.2s; }
    .spw-timeline__card--active {
        box-shadow: 0 8px 24px rgba(37, 99, 235, 0.12) !important;
        outline: 2px solid rgba(37, 99, 235, 0.25);
    }
    .spw-timeline__card-head {
        display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 0.75rem;
        padding: 1rem 1.25rem; background: var(--spw-slate-50); border-bottom: 1px solid var(--spw-slate-200);
    }
    .spw-timeline__item--active .spw-timeline__card-head { background: var(--spw-primary-soft); }
    .spw-timeline__item--done .spw-timeline__card-head { background: var(--spw-success-soft); }
    .spw-timeline__card-head[role="button"] { cursor: pointer; }
    .spw-timeline__card-head[role="button"]:hover { background: #e0f2fe; }

    .spw-timeline__card-title { display: flex; align-items: center; gap: 0.65rem; }
    .spw-step-num {
        font-size: 0.72rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.05em; color: var(--spw-slate-500); white-space: nowrap;
    }
    .spw-timeline__card-title h6 { font-weight: 700; color: var(--spw-slate-800); }

    .spw-type-pill, .spw-state-pill {
        display: inline-flex; align-items: center; padding: 3px 10px;
        border-radius: 999px; font-size: 0.75rem; font-weight: 600;
    }
    .spw-type-pill--regular { background: #ecfdf5; color: #047857; }
    .spw-type-pill--analysis { background: #e0f2fe; color: #0369a1; }
    .spw-state-pill--done { background: #d1fae5; color: #065f46; }
    .spw-state-pill--active { background: #dbeafe; color: #1d4ed8; }
    .spw-state-pill--pending { background: var(--spw-slate-100); color: var(--spw-slate-500); }
    .spw-state-pill--next { background: #d1fae5; color: #047857; border: 1px dashed #059669; }

    .spw-work-btn { font-weight: 600; border-radius: 8px; }

    .spw-timeline__card-body { padding: 1.25rem; }
    .spw-timeline__card-collapsed { padding: 0.85rem 1.25rem; }
    .spw-step-desc { color: var(--spw-slate-800); margin-bottom: 0.5rem; }
    .spw-step-notes { margin-bottom: 1rem; }

    .spw-ingredient-box {
        display: flex; align-items: flex-start; gap: 0.75rem;
        padding: 1rem; border-radius: 12px; background: var(--spw-slate-50);
        border: 1px solid var(--spw-slate-200); margin-bottom: 1rem;
    }
    .spw-ingredient-box > i { font-size: 1.5rem; color: var(--spw-primary); }
    .spw-ingredient-label { display: block; font-size: 0.72rem; font-weight: 600; text-transform: uppercase; color: var(--spw-slate-500); }

    .spw-results-grid { display: flex; flex-direction: column; gap: 1rem; margin-bottom: 1rem; }
    .spw-results-section {
        padding: 1rem; border-radius: 12px; border: 1px solid var(--spw-slate-200); background: #fff;
    }
    .spw-results-title { font-size: 0.82rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: var(--spw-slate-500); margin-bottom: 0.75rem; }
    .spw-result-row { margin-bottom: 0.65rem; }
    .spw-result-row:last-child { margin-bottom: 0; }
    .spw-result-label { display: block; font-size: 0.82rem; font-weight: 600; color: var(--spw-slate-800); margin-bottom: 0.25rem; }
    .spw-result-readonly { display: block; padding: 0.5rem 0.75rem; background: var(--spw-slate-50); border-radius: 8px; font-size: 0.9rem; }
    .spw-remark-badge {
        display: inline-flex;
        align-items: center;
        margin-top: 0.35rem;
        padding: 2px 10px;
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 600;
    }
    .spw-remark-badge--pass { background: #ecfdf5; color: #047857; }
    .spw-remark-badge--fail { background: #fef2f2; color: #b91c1c; }

    .spw-step-actions {
        display: flex; flex-wrap: wrap; gap: 0.5rem; padding-top: 1rem;
        border-top: 1px solid var(--spw-slate-200); margin-top: 0.5rem;
    }

    .spw-blockers { border-radius: 10px; }

    .spw-next-hint { border-radius: 12px; border-left: 4px solid var(--spw-success); }
    .spw-next-hint__label {
        font-size: 0.72rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.05em; color: var(--spw-success);
    }

    .spw-complete-summary {
        padding: 1.25rem; border-radius: 12px;
        border: 2px dashed var(--spw-success); background: var(--spw-success-soft);
    }
    .spw-complete-summary__eyebrow {
        display: block; font-size: 0.72rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.05em; color: var(--spw-success); margin-bottom: 0.35rem;
    }
    .spw-complete-summary__detail {
        display: flex; align-items: center; gap: 0.5rem; font-size: 0.9rem; margin-top: 0.5rem;
    }
    .spw-complete-summary__detail > i { color: var(--spw-success); font-size: 1.25rem; }
    .spw-complete-next {
        padding: 1rem; border-radius: 10px; background: var(--spw-slate-50);
        border: 1px solid var(--spw-slate-200);
    }
    .spw-complete-next__label {
        display: block; font-size: 0.72rem; font-weight: 600; text-transform: uppercase;
        color: var(--spw-slate-500); margin-bottom: 0.25rem;
    }

    .spw-sidebar-card { border-radius: 14px; }
    .spw-sidebar-title { font-size: 0.95rem; font-weight: 700; color: var(--spw-slate-800); }
    .spw-sidebar-card__head { padding: 1rem 1.25rem 0; }

    .spw-panel { position: sticky; top: 1rem; }
    .spw-panel__card { border-radius: 14px; overflow: hidden; }
    .spw-panel__head {
        display: flex; align-items: flex-start; gap: 0.85rem;
        padding: 1.15rem 1.25rem; background: linear-gradient(135deg, #f8fafc 0%, #eff6ff 100%);
        border-bottom: 1px solid var(--spw-slate-200);
    }
    .spw-panel__icon {
        width: 40px; height: 40px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        background: #fff; color: var(--spw-primary); font-size: 1.25rem;
        box-shadow: 0 2px 8px rgba(37, 99, 235, 0.12);
    }
    .spw-panel__title { font-weight: 700; color: var(--spw-slate-800); font-size: 0.95rem; }
    .spw-panel__subtitle { font-size: 0.78rem; color: var(--spw-slate-500); margin-top: 2px; }
    .spw-panel__body { padding: 1.15rem 1.25rem 1.25rem; }
    .spw-panel__identity {
        display: flex; flex-wrap: wrap; align-items: baseline; justify-content: space-between; gap: 0.35rem;
        margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid var(--spw-slate-200);
    }
    .spw-panel__prep-no {
        font-size: 0.88rem; font-weight: 700; letter-spacing: 0.02em;
        color: var(--spw-primary); font-family: ui-monospace, monospace;
    }
    .spw-panel__prep-id { font-size: 0.72rem; color: var(--spw-slate-500); }
    .spw-panel__meta { margin: 0 0 1rem; }
    .spw-panel__meta-row {
        display: grid; grid-template-columns: minmax(0, 42%) 1fr; gap: 0.35rem 0.75rem;
        padding: 0.4rem 0; border-bottom: 1px dashed var(--spw-slate-100);
    }
    .spw-panel__meta-row:last-child { border-bottom: none; }
    .spw-panel__meta-row dt {
        margin: 0; font-size: 0.75rem; font-weight: 600; color: var(--spw-slate-500);
        display: flex; align-items: center; gap: 0.25rem;
    }
    .spw-panel__meta-row dt i { font-size: 0.95rem; opacity: 0.85; }
    .spw-panel__meta-row dd { margin: 0; font-size: 0.84rem; font-weight: 600; color: var(--spw-slate-800); }
    .spw-panel__section { margin-bottom: 1.1rem; }
    .spw-panel__section:last-child { margin-bottom: 0; }
    .spw-panel__section-title {
        font-size: 0.72rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.06em; color: var(--spw-slate-500); margin-bottom: 0.65rem;
    }
    .spw-panel__stats {
        display: grid; grid-template-columns: 1fr 1fr; gap: 0.65rem;
    }
    .spw-panel__stat {
        padding: 0.65rem 0.75rem; border-radius: 10px;
        background: var(--spw-slate-50); border: 1px solid var(--spw-slate-200);
    }
    .spw-panel__stat--wide { grid-column: 1 / -1; }
    .spw-panel__stat-label {
        display: block; font-size: 0.68rem; font-weight: 600; text-transform: uppercase;
        letter-spacing: 0.04em; color: var(--spw-slate-500); margin-bottom: 0.2rem;
    }
    .spw-panel__stat-value { font-size: 0.9rem; font-weight: 700; color: var(--spw-slate-800); }
    .spw-panel__stat-value small { font-weight: 600; color: var(--spw-slate-500); }
    .spw-panel__stat-hint { display: block; font-size: 0.72rem; color: var(--spw-slate-500); margin-top: 0.25rem; line-height: 1.35; }
    .spw-panel__tag {
        display: inline-block; margin-left: 4px; padding: 1px 6px; border-radius: 4px;
        font-size: 0.65rem; font-weight: 700; text-transform: uppercase;
        background: #dbeafe; color: #1d4ed8;
    }
    .spw-panel__expiry--expired { color: #b91c1c; }
    .spw-panel__expiry--soon { color: #b45309; }
    .spw-panel__expiry--pending { color: #0369a1; }
    .spw-panel__notes { font-size: 0.84rem; color: var(--spw-slate-800); line-height: 1.45; }
    .spw-panel__scale-hint {
        font-size: 0.68rem; font-weight: 600; color: var(--spw-slate-500);
        text-align: right; line-height: 1.3; max-width: 55%;
    }
    .spw-panel__callout {
        display: flex; align-items: flex-start; gap: 0.5rem;
        padding: 0.65rem 0.75rem; border-radius: 10px; font-size: 0.78rem;
        margin-bottom: 1rem; line-height: 1.4;
    }
    .spw-panel__callout--alt { background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; }
    .spw-panel__callout--info { background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; }
    .spw-panel__callout i { font-size: 1.1rem; flex-shrink: 0; margin-top: 1px; }

    .spw-ingredient-list__item {
        padding: 0.65rem 0; border-bottom: 1px solid var(--spw-slate-100);
    }
    .spw-ingredient-list__item:last-child { border-bottom: none; padding-bottom: 0; }
    .spw-ingredient-list__item--active {
        margin: 0 -0.5rem; padding-left: 0.5rem; padding-right: 0.5rem;
        border-radius: 8px; background: var(--spw-success-soft);
        border-bottom-color: transparent;
    }
    .spw-ingredient-list__main {
        display: flex; align-items: center; justify-content: space-between; gap: 0.5rem; margin-bottom: 0.25rem;
    }
    .spw-ingredient-list__name { font-size: 0.84rem; font-weight: 600; color: var(--spw-slate-800); }
    .spw-ingredient-list__badge {
        font-size: 0.62rem; font-weight: 700; text-transform: uppercase;
        padding: 2px 6px; border-radius: 4px; background: #d1fae5; color: #047857;
    }
    .spw-ingredient-list__amounts {
        display: flex; align-items: center; gap: 0.35rem; flex-wrap: wrap;
    }
    .spw-ingredient-list__recipe {
        font-size: 0.78rem; color: var(--spw-slate-500); text-decoration: line-through;
    }
    .spw-ingredient-list__arrow { font-size: 0.85rem; color: var(--spw-slate-500); }
    .spw-ingredient-list__calc {
        font-size: 0.92rem; font-weight: 700; color: var(--spw-primary);
    }
    .spw-ingredient-list__calc small { font-weight: 600; color: var(--spw-slate-500); }

    @media (max-width: 991.98px) {
        .spw-panel { position: static; }
    }
    .spw-label { font-size: 0.82rem; font-weight: 600; color: #475569; margin-bottom: 0.35rem; display: block; }
    .spw-input { border-radius: 10px; border-color: var(--spw-slate-200); }
    .spw-input:focus { border-color: var(--spw-primary); box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12); }

    .spw-empty { border-radius: 14px; }
    .spw-modal-backdrop { background: rgba(15, 23, 42, 0.45); }
    .spw-modal-content { border-radius: 16px; }

    @media (max-width: 767px) {
        .spw-timeline__item { grid-template-columns: 36px 1fr; gap: 0.65rem; }
        .spw-timeline__dot { width: 32px; height: 32px; font-size: 0.8rem; }
    }
    </style>
</div>
