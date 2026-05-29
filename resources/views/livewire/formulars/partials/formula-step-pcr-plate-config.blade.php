@include('livewire.partials.pcr-plate-styles')
<section class="fs-config-panel fs-config-panel--pcr mb-3">
    <div class="fs-config-panel-head">
        <span class="fs-config-panel-icon"><i class="mdi mdi-grid"></i></span>
        <div>
            <h6 class="mb-0">PCR plate map</h6>
            <p class="mb-0 small text-muted">Worksheet-wide 96-well plate (A1–H12) for sample placement and optional QC wells.</p>
        </div>
    </div>
    <div class="fs-config-panel-body">
        <label class="fm-checkbox-card mb-3">
            <input type="checkbox" wire:model.live="pcrHasStdControlsBuffers">
            <span>This plate uses standards, controls, or buffers</span>
        </label>
        <p class="text-muted small mb-3">
            When enabled, define STD / control / buffer labels below, then click wells in the layout to assign them. Analysts still place samples on remaining wells during capture.
        </p>

        @if($pcrHasStdControlsBuffers)
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Standards</label>
                    <div class="pcr-chip-add d-flex gap-2 mb-2">
                        <input type="text"
                               class="form-control form-control-sm"
                               wire:model="pcrNewStandard"
                               wire:keydown.enter.prevent="addPcrStandard"
                               placeholder="e.g. STD1">
                        <button type="button" class="btn btn-sm btn-outline-primary" wire:click="addPcrStandard">
                            <i class="mdi mdi-plus"></i>
                        </button>
                    </div>
                    <ul class="pcr-chip-list list-unstyled mb-0">
                        @foreach($pcrStandards as $index => $label)
                            <li class="pcr-chip-list__item" wire:key="pcr-std-{{ $index }}">
                                <span>{{ $label }}</span>
                                <button type="button" class="btn btn-sm btn-link text-danger p-0" wire:click="removePcrStandard({{ $index }})" title="Remove">
                                    <i class="mdi mdi-close"></i>
                                </button>
                            </li>
                        @endforeach
                    </ul>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Controls</label>
                    <div class="pcr-chip-add d-flex gap-2 mb-2">
                        <input type="text"
                               class="form-control form-control-sm"
                               wire:model="pcrNewControl"
                               wire:keydown.enter.prevent="addPcrControl"
                               placeholder="e.g. NC">
                        <button type="button" class="btn btn-sm btn-outline-primary" wire:click="addPcrControl">
                            <i class="mdi mdi-plus"></i>
                        </button>
                    </div>
                    <ul class="pcr-chip-list list-unstyled mb-0">
                        @foreach($pcrControls as $index => $label)
                            <li class="pcr-chip-list__item" wire:key="pcr-ctrl-{{ $index }}">
                                <span>{{ $label }}</span>
                                <button type="button" class="btn btn-sm btn-link text-danger p-0" wire:click="removePcrControl({{ $index }})" title="Remove">
                                    <i class="mdi mdi-close"></i>
                                </button>
                            </li>
                        @endforeach
                    </ul>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Buffers</label>
                    <div class="pcr-chip-add d-flex gap-2 mb-2">
                        <input type="text"
                               class="form-control form-control-sm"
                               wire:model="pcrNewBuffer"
                               wire:keydown.enter.prevent="addPcrBuffer"
                               placeholder="e.g. Buffer 1">
                        <button type="button" class="btn btn-sm btn-outline-primary" wire:click="addPcrBuffer">
                            <i class="mdi mdi-plus"></i>
                        </button>
                    </div>
                    <ul class="pcr-chip-list list-unstyled mb-0">
                        @foreach($pcrBuffers as $index => $label)
                            <li class="pcr-chip-list__item" wire:key="pcr-buf-{{ $index }}">
                                <span>{{ $label }}</span>
                                <button type="button" class="btn btn-sm btn-link text-danger p-0" wire:click="removePcrBuffer({{ $index }})" title="Remove">
                                    <i class="mdi mdi-close"></i>
                                </button>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <div class="pcr-plate-preview-wrap">
                <p class="small text-muted mb-2">
                    <strong>Plate layout</strong> — click a well to assign a standard, control, or buffer.
                </p>
                @include('livewire.formulars.partials.pcr-plate-grid', [
                    'mode' => 'configure',
                    'wells' => $pcrPresetWells,
                ])

                <div class="pcr-plate-legend mt-2">
                    <span class="pcr-plate-legend__item">
                        <span class="pcr-plate-legend__swatch" style="background:#eff6ff;border-color:#93c5fd;"></span> Standard
                    </span>
                    <span class="pcr-plate-legend__item">
                        <span class="pcr-plate-legend__swatch" style="background:#fef3c7;border-color:#fcd34d;"></span> Control
                    </span>
                    <span class="pcr-plate-legend__item">
                        <span class="pcr-plate-legend__swatch" style="background:#f5f3ff;border-color:#c4b5fd;"></span> Buffer
                    </span>
                </div>

                @if($pcrConfigActiveWell)
                    <div class="pcr-well-editor mt-3" wire:key="pcr-config-editor-{{ $pcrConfigActiveWell }}">
                        <div class="pcr-well-editor__title">
                            <i class="mdi mdi-map-marker-radius text-primary"></i>
                            Well <strong>{{ $pcrConfigActiveWell }}</strong>
                        </div>

                        <div class="pcr-kind-tabs" role="group" aria-label="QC assignment type">
                            <button type="button"
                                    class="pcr-kind-tab {{ $pcrConfigPopoverKind === 'std' ? 'is-active' : '' }}"
                                    wire:click="setPcrConfigPopoverKind('std')">
                                Standard
                            </button>
                            <button type="button"
                                    class="pcr-kind-tab {{ $pcrConfigPopoverKind === 'control' ? 'is-active' : '' }}"
                                    wire:click="setPcrConfigPopoverKind('control')">
                                Control
                            </button>
                            <button type="button"
                                    class="pcr-kind-tab {{ $pcrConfigPopoverKind === 'buffer' ? 'is-active' : '' }}"
                                    wire:click="setPcrConfigPopoverKind('buffer')">
                                Buffer
                            </button>
                        </div>

                        <div class="mb-2">
                            <label class="form-label small mb-1">
                                {{ $pcrConfigPopoverKind === 'std' ? 'Standard' : ucfirst($pcrConfigPopoverKind) }} label
                            </label>
                            <input type="text"
                                   class="form-control form-control-sm @error('pcrConfigPopoverValue') is-invalid @enderror"
                                   wire:model="pcrConfigPopoverValue"
                                   wire:keydown.enter.prevent="applyPcrConfigWell"
                                   placeholder="e.g. {{ $pcrConfigPopoverKind === 'std' ? 'STD1' : ($pcrConfigPopoverKind === 'control' ? 'NC' : 'Buffer 1') }}">
                            @error('pcrConfigPopoverValue')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        @php
                            $configQuickPicks = match ($pcrConfigPopoverKind) {
                                'std' => $pcrStandards,
                                'control' => $pcrControls,
                                'buffer' => $pcrBuffers,
                                default => [],
                            };
                        @endphp
                        @if(count($configQuickPicks) > 0)
                            <div class="pcr-quick-picks">
                                @foreach($configQuickPicks as $pick)
                                    <button type="button"
                                            class="btn btn-outline-secondary btn-sm pcr-quick-pick"
                                            wire:click="quickAssignPcrConfigWell(@js($pcrConfigPopoverKind), @js($pick))">
                                        {{ $pick }}
                                    </button>
                                @endforeach
                            </div>
                        @endif

                        <div class="d-flex flex-wrap gap-2 mt-3">
                            <button type="button" class="btn btn-primary btn-sm" wire:click="applyPcrConfigWell">
                                <i class="mdi mdi-check"></i> Apply
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" wire:click="closePcrConfigWellEditor">
                                Cancel
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-sm" wire:click="clearPcrConfigWell">
                                <i class="mdi mdi-eraser"></i> Clear well
                            </button>
                        </div>
                    </div>
                @else
                    <p class="text-muted small mb-0 mt-2">
                        <i class="mdi mdi-cursor-default-click"></i> Click a well to tie it to a standard, control, or buffer label.
                    </p>
                @endif
            </div>
        @else
            <div class="pcr-plate-preview-wrap">
                <p class="small text-muted mb-2"><strong>Layout preview</strong> — analysts fill all wells during worksheet capture.</p>
                @include('livewire.formulars.partials.pcr-plate-grid', ['mode' => 'preview', 'wells' => []])
            </div>
        @endif
    </div>
</section>
