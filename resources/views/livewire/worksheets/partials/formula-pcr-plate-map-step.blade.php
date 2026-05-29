@php
    $pcrSteps = $formulaSteps->where('step_type', 'pcr_plate_map')->sortBy('step_number');
@endphp
@if($pcrSteps->isNotEmpty())
    @include('livewire.partials.pcr-plate-styles')
    @foreach($pcrSteps as $step)
        @php
            $config = $step->pcrPlateConfig();
            $wells = $this->pcrWellsForStep($step->id);
            $hasQc = $config['has_std_controls_buffers'];
        @endphp
        <div class="card border mt-4 pcr-plate-card" wire:key="formula-pcr-step-{{ $step->id }}">
            <div class="card-header bg-light d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h5 class="mb-0">
                        <i class="mdi mdi-grid text-primary"></i> {{ $step->label }}
                    </h5>
                    <small class="text-muted">PCR plate map — applies to all samples on this worksheet</small>
                </div>
                @if($step->description)
                    <small class="text-muted">{{ $step->description }}</small>
                @endif
            </div>
            <div class="card-body">
                @include('livewire.formulars.partials.pcr-plate-grid', [
                    'mode' => 'capture',
                    'wells' => $wells,
                    'stepId' => $step->id,
                ])

                <div class="pcr-plate-legend">
                    <span class="pcr-plate-legend__item">
                        <span class="pcr-plate-legend__swatch" style="background:#ecfdf5;border-color:#6ee7b7;"></span> Sample
                    </span>
                    @if($hasQc)
                        <span class="pcr-plate-legend__item">
                            <span class="pcr-plate-legend__swatch" style="background:#eff6ff;border-color:#93c5fd;"></span> Standard
                        </span>
                        <span class="pcr-plate-legend__item">
                            <span class="pcr-plate-legend__swatch" style="background:#fef3c7;border-color:#fcd34d;"></span> Control
                        </span>
                        <span class="pcr-plate-legend__item">
                            <span class="pcr-plate-legend__swatch" style="background:#f5f3ff;border-color:#c4b5fd;"></span> Buffer
                        </span>
                    @endif
                </div>

                @if($activePcrStepId === $step->id && $activePcrWell)
                    <div class="pcr-well-editor" wire:key="pcr-editor-{{ $step->id }}-{{ $activePcrWell }}">
                        <div class="pcr-well-editor__title">
                            <i class="mdi mdi-map-marker-radius text-primary"></i>
                            Well <strong>{{ $activePcrWell }}</strong>
                        </div>

                        <div class="pcr-kind-tabs" role="group" aria-label="Assignment type">
                            <button type="button"
                                    class="pcr-kind-tab {{ $pcrPopoverKind === 'sample' ? 'is-active' : '' }}"
                                    wire:click="setPcrPopoverKind('sample')">
                                Sample
                            </button>
                            @if($hasQc)
                                <button type="button"
                                        class="pcr-kind-tab {{ $pcrPopoverKind === 'std' ? 'is-active' : '' }}"
                                        wire:click="setPcrPopoverKind('std')">
                                    Standard
                                </button>
                                <button type="button"
                                        class="pcr-kind-tab {{ $pcrPopoverKind === 'control' ? 'is-active' : '' }}"
                                        wire:click="setPcrPopoverKind('control')">
                                    Control
                                </button>
                                <button type="button"
                                        class="pcr-kind-tab {{ $pcrPopoverKind === 'buffer' ? 'is-active' : '' }}"
                                        wire:click="setPcrPopoverKind('buffer')">
                                    Buffer
                                </button>
                            @endif
                        </div>

                        @if($pcrPopoverKind === 'sample')
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small mb-1">Batch sample</label>
                                    <select class="form-control form-control-sm"
                                            wire:model.live="pcrPopoverValue"
                                            @disabled($pcrPopoverUseCustomSample)>
                                        <option value="">Select sample…</option>
                                        @foreach($this->batchSamplePcrOptions as $option)
                                            <option value="{{ $option['code'] }}">{{ $option['label'] }}</option>
                                        @endforeach
                                    </select>
                                    @if($this->selectedPcrSampleFileId)
                                        <div class="small text-muted mt-1">
                                            Sample File ID: <strong>{{ $this->selectedPcrSampleFileId }}</strong>
                                        </div>
                                    @elseif($pcrPopoverValue !== '' && ! $pcrPopoverUseCustomSample)
                                        <div class="small text-warning mt-1">
                                            No Sample File ID assigned yet — generate file numbers on the File Registration worksheet first.
                                        </div>
                                    @endif
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small mb-1 d-flex align-items-center gap-2">
                                        <input type="checkbox" class="mr-1" wire:model.live="pcrPopoverUseCustomSample">
                                        Custom sample ID
                                    </label>
                                    <input type="text"
                                           class="form-control form-control-sm"
                                           wire:model="pcrPopoverCustomSample"
                                           placeholder="Enter sample ID"
                                           @disabled(! $pcrPopoverUseCustomSample)>
                                </div>
                            </div>
                        @else
                            <div class="mb-2">
                                <label class="form-label small mb-1">
                                    {{ ucfirst($pcrPopoverKind === 'std' ? 'standard' : $pcrPopoverKind) }} label
                                </label>
                                <input type="text"
                                       class="form-control form-control-sm"
                                       wire:model="pcrPopoverValue"
                                       placeholder="e.g. {{ $pcrPopoverKind === 'std' ? 'STD1' : ($pcrPopoverKind === 'control' ? 'NC' : 'Buffer 1') }}">
                            </div>
                            @php
                                $quickPicks = match ($pcrPopoverKind) {
                                    'std' => $config['standards'],
                                    'control' => $config['controls'],
                                    'buffer' => $config['buffers'],
                                    default => [],
                                };
                            @endphp
                            @if(count($quickPicks) > 0)
                                <div class="pcr-quick-picks">
                                    @foreach($quickPicks as $pick)
                                        <button type="button"
                                                class="btn btn-outline-secondary btn-sm pcr-quick-pick"
                                                wire:click="quickAssignPcrWell(@js($pick))">
                                            {{ $pick }}
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        @endif

                        <div class="d-flex flex-wrap gap-2 mt-3">
                            <button type="button" class="btn btn-primary btn-sm" wire:click="applyPcrWellEditor">
                                <i class="mdi mdi-check"></i> Apply
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" wire:click="closePcrWellEditor">
                                Cancel
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-sm" wire:click="clearActivePcrWell">
                                <i class="mdi mdi-eraser"></i> Clear well
                            </button>
                        </div>
                    </div>
                @else
                    <p class="text-muted small mb-0 mt-2">
                        <i class="mdi mdi-cursor-default-click"></i> Click a well to assign a sample{{ $hasQc ? ', standard, control, or buffer' : '' }}.
                    </p>
                @endif
            </div>
        </div>
    @endforeach
@endif
