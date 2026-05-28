    @if($showLabSectionModal && $activeLabSectionLabId)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content lab-section-modal">
                    <div class="modal-header lab-section-modal__header">
                        <div>
                            <div class="lab-section-modal__eyebrow">Lab Configuration</div>
                            <h5 class="modal-title lab-section-modal__title">
                                <i class="mdi mdi-{{ $editingLabSection ? 'pencil-circle-outline' : 'plus-circle-outline' }}"></i>
                                {{ $editingLabSection ? 'Edit' : 'Create' }} Lab Section
                            </h5>
                            <p class="lab-section-modal__subtitle mb-0">Define monitoring logic, expected values, and reporting rules for this lab section.</p>
                        </div>
                        <button type="button" class="btn-close" wire:click="closeLabSectionModal"></button>
                    </div>
                    <div class="modal-body lab-section-modal__body">
                        <div class="lab-section-form-grid">
                            <section class="lab-section-panel">
                                <div class="lab-section-panel__head">
                                    <div>
                                        <p class="lab-section-panel__kicker mb-1">Identity</p>
                                        <h6 class="mb-0">Section Definition</h6>
                                    </div>
                                    <label class="lab-switch mb-0" for="modal_env_analysis">
                                        <input type="checkbox" wire:model.live="labSectionForms.{{ $activeLabSectionLabId }}.does_environmental_analysis" class="form-check-input d-none" id="modal_env_analysis">
                                        <span class="lab-switch__track"></span>
                                        <span class="lab-switch__label">Environmental Monitoring</span>
                                    </label>
                                </div>

                                <div class="row">
                                    <div class="col-md-7">
                                        <label class="form-label form-label--modern">Section Name <span class="text-danger">*</span></label>
                                        <input type="text" wire:model.defer="labSectionForms.{{ $activeLabSectionLabId }}.name" class="form-control form-control--modern" placeholder="e.g. Air Quality Monitoring">
                                        @error('labSectionForms.' . $activeLabSectionLabId . '.name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label form-label--modern">Section Code <span class="text-danger">*</span></label>
                                        <input type="text" wire:model.defer="labSectionForms.{{ $activeLabSectionLabId }}.code" class="form-control form-control--modern" placeholder="AQM">
                                        @error('labSectionForms.' . $activeLabSectionLabId . '.code') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label form-label--modern">Description</label>
                                        <textarea wire:model.defer="labSectionForms.{{ $activeLabSectionLabId }}.description" class="form-control form-control--modern form-control--modern-textarea" rows="4" placeholder="Short scope, monitoring context, and what this section is responsible for."></textarea>
                                        @error('labSectionForms.' . $activeLabSectionLabId . '.description') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </section>

                            @if(data_get($labSectionForms, $activeLabSectionLabId . '.does_environmental_analysis', false))
                                <section class="lab-section-panel lab-section-panel--accent">
                                    <div class="lab-section-panel__head">
                                        <div>
                                            <p class="lab-section-panel__kicker mb-1">Monitoring</p>
                                            <h6 class="mb-0">Environmental Monitoring</h6>
                                        </div>
                                        <span class="lab-section-chip">Required</span>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <label class="form-label form-label--modern">Monitoring Equipment <span class="text-danger">*</span></label>
                                            <div class="tag-select-container" wire:click="$set('showLabSectionEquipmentDropdown', true)" wire:click.outside="$set('showLabSectionEquipmentDropdown', false)">
                                                <div class="tag-select-input modern-filter-tag-input lab-tag-select-input">
                                            @if($this->selectedLabSectionEquipment)
                                                <span class="tag-badge">
                                                    {{ $this->selectedLabSectionEquipment->name }}{{ $this->selectedLabSectionEquipment->equipment_number ? ' (' . $this->selectedLabSectionEquipment->equipment_number . ')' : '' }}
                                                    <i class="mdi mdi-close-circle" wire:click.stop="clearLabSectionEquipment"></i>
                                                </span>
                                            @endif

                                            <input type="text"
                                                   wire:model.live.debounce.200ms="labSectionEquipmentSearch"
                                                   class="tag-input"
                                                   placeholder="{{ $this->selectedLabSectionEquipment ? '' : 'Search equipment...' }}"
                                                   autocomplete="off">
                                                </div>

                                                @if($showLabSectionEquipmentDropdown)
                                                    <div class="tag-dropdown">
                                                        @forelse($this->filteredLabSectionEquipments as $equipment)
                                                            <div class="tag-dropdown-item" wire:click.stop="selectLabSectionEquipment('{{ $equipment->id }}')">
                                                                {{ $equipment->name }}{{ $equipment->equipment_number ? ' (' . $equipment->equipment_number . ')' : '' }}
                                                            </div>
                                                        @empty
                                                            <div class="tag-dropdown-item text-muted">No equipment found</div>
                                                        @endforelse
                                                    </div>
                                                @endif
                                            </div>
                                            @error('labSectionForms.' . $activeLabSectionLabId . '.equipment_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label form-label--modern">Expected Value Type <span class="text-danger">*</span></label>
                                            <div class="tag-select-container" wire:click="$set('showLabSectionExpectedValueTypeDropdown', true)" wire:click.outside="$set('showLabSectionExpectedValueTypeDropdown', false)">
                                                <div class="tag-select-input modern-filter-tag-input lab-tag-select-input">
                                            @if($this->selectedLabSectionExpectedValueType)
                                                <span class="tag-badge">
                                                    {{ $this->selectedLabSectionExpectedValueType['label'] }}
                                                    <i class="mdi mdi-close-circle" wire:click.stop="clearLabSectionExpectedValueType"></i>
                                                </span>
                                            @endif

                                            <input type="text"
                                                   wire:model.live.debounce.200ms="labSectionExpectedValueTypeSearch"
                                                   class="tag-input"
                                                   placeholder="{{ $this->selectedLabSectionExpectedValueType ? '' : 'Search type...' }}"
                                                   autocomplete="off">
                                                </div>

                                                @if($showLabSectionExpectedValueTypeDropdown)
                                                    <div class="tag-dropdown">
                                                        @forelse($this->filteredLabSectionExpectedValueTypes as $option)
                                                            <div class="tag-dropdown-item" wire:click.stop="selectLabSectionExpectedValueType('{{ $option['id'] }}')">
                                                                {{ $option['label'] }}
                                                            </div>
                                                        @empty
                                                            <div class="tag-dropdown-item text-muted">No value types found</div>
                                                        @endforelse
                                                    </div>
                                                @endif
                                            </div>
                                            @error('labSectionForms.' . $activeLabSectionLabId . '.expected_value_type') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                        </div>

                                        <div class="w-100"></div>

                                        @if(data_get($labSectionForms, $activeLabSectionLabId . '.expected_value_type') === 'constant')
                                            <div class="col-md-4">
                                                <label class="form-label form-label--modern">Expected Constant <span class="text-danger">*</span></label>
                                                <input type="number" step="0.0001" wire:model.defer="labSectionForms.{{ $activeLabSectionLabId }}.expected_value" class="form-control form-control--modern" placeholder="e.g. 7.0000">
                                                @error('labSectionForms.' . $activeLabSectionLabId . '.expected_value') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label form-label--modern">Optimum Level <span class="text-danger">*</span></label>
                                                <input type="text" wire:model.defer="labSectionForms.{{ $activeLabSectionLabId }}.optimum_level" class="form-control form-control--modern" placeholder="e.g. WHO Preferred Band">
                                                @error('labSectionForms.' . $activeLabSectionLabId . '.optimum_level') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label form-label--modern">Reporting Unit <span class="text-danger">*</span></label>
                                                <div class="tag-select-container" wire:click="$set('showLabSectionReportingUnitDropdown', true)" wire:click.outside="$set('showLabSectionReportingUnitDropdown', false)">
                                                    <div class="tag-select-input modern-filter-tag-input lab-tag-select-input">
                                                        @if($this->selectedLabSectionReportingUnit)
                                                            <span class="tag-badge">
                                                                {{ $this->selectedLabSectionReportingUnit->name }}
                                                                <i class="mdi mdi-close-circle" wire:click.stop="$set('labSectionForms.{{ $activeLabSectionLabId }}.reporting_unit', '')"></i>
                                                            </span>
                                                        @endif
                                                        <input type="text"
                                                               wire:model.live.debounce.200ms="labSectionReportingUnitSearch"
                                                               class="tag-input"
                                                               placeholder="{{ $this->selectedLabSectionReportingUnit ? '' : 'Search reporting unit...' }}"
                                                               autocomplete="off">
                                                    </div>
                                                    @if($showLabSectionReportingUnitDropdown)
                                                        <div class="tag-dropdown">
                                                            @forelse($this->filteredLabSectionReportingUnits as $unit)
                                                                <div class="tag-dropdown-item" wire:click.stop="$set('labSectionForms.{{ $activeLabSectionLabId }}.reporting_unit', '{{ $unit->id }}')">
                                                                    {{ $unit->name }}
                                                                </div>
                                                            @empty
                                                                <div class="tag-dropdown-item text-muted">No active reporting units found</div>
                                                            @endforelse
                                                        </div>
                                                    @endif
                                                </div>
                                                @error('labSectionForms.' . $activeLabSectionLabId . '.reporting_unit') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                            </div>
                                        @elseif(data_get($labSectionForms, $activeLabSectionLabId . '.expected_value_type') === 'range')
                                            <div class="col-md-4">
                                                <label class="form-label form-label--modern">Low <span class="text-danger">*</span></label>
                                                <input type="number" step="0.0001" wire:model.defer="labSectionForms.{{ $activeLabSectionLabId }}.expected_min" class="form-control form-control--modern" placeholder="e.g. 6.5000">
                                                @error('labSectionForms.' . $activeLabSectionLabId . '.expected_min') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label form-label--modern">High <span class="text-danger">*</span></label>
                                                <input type="number" step="0.0001" wire:model.defer="labSectionForms.{{ $activeLabSectionLabId }}.expected_max" class="form-control form-control--modern" placeholder="e.g. 8.5000">
                                                @error('labSectionForms.' . $activeLabSectionLabId . '.expected_max') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label form-label--modern">Reporting Unit <span class="text-danger">*</span></label>
                                                <div class="tag-select-container" wire:click="$set('showLabSectionReportingUnitDropdown', true)" wire:click.outside="$set('showLabSectionReportingUnitDropdown', false)">
                                                    <div class="tag-select-input modern-filter-tag-input lab-tag-select-input">
                                                        @if($this->selectedLabSectionReportingUnit)
                                                            <span class="tag-badge">
                                                                {{ $this->selectedLabSectionReportingUnit->name }}
                                                                <i class="mdi mdi-close-circle" wire:click.stop="$set('labSectionForms.{{ $activeLabSectionLabId }}.reporting_unit', '')"></i>
                                                            </span>
                                                        @endif
                                                        <input type="text"
                                                               wire:model.live.debounce.200ms="labSectionReportingUnitSearch"
                                                               class="tag-input"
                                                               placeholder="{{ $this->selectedLabSectionReportingUnit ? '' : 'Search reporting unit...' }}"
                                                               autocomplete="off">
                                                    </div>
                                                    @if($showLabSectionReportingUnitDropdown)
                                                        <div class="tag-dropdown">
                                                            @forelse($this->filteredLabSectionReportingUnits as $unit)
                                                                <div class="tag-dropdown-item" wire:click.stop="$set('labSectionForms.{{ $activeLabSectionLabId }}.reporting_unit', '{{ $unit->id }}')">
                                                                    {{ $unit->name }}
                                                                </div>
                                                            @empty
                                                                <div class="tag-dropdown-item text-muted">No active reporting units found</div>
                                                            @endforelse
                                                        </div>
                                                    @endif
                                                </div>
                                                @error('labSectionForms.' . $activeLabSectionLabId . '.reporting_unit') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                            </div>
                                        @endif

                                        <div class="w-100"></div>

                                        <div class="col-md-6">
                                            <label class="form-label form-label--modern">Result Nature <span class="text-danger">*</span></label>
                                            <div class="tag-select-container" wire:click="$set('showLabSectionResultNatureDropdown', true)" wire:click.outside="$set('showLabSectionResultNatureDropdown', false)">
                                                <div class="tag-select-input modern-filter-tag-input lab-tag-select-input">
                                            @if(data_get($labSectionForms, $activeLabSectionLabId . '.result_nature'))
                                                <span class="tag-badge">
                                                    {{ data_get($labSectionForms, $activeLabSectionLabId . '.result_nature') }}
                                                    <i class="mdi mdi-close-circle" wire:click.stop="$set('labSectionForms.{{ $activeLabSectionLabId }}.result_nature', null)"></i>
                                                </span>
                                            @endif

                                            <input type="text"
                                                   wire:model.live.debounce.200ms="labSectionResultNatureSearch"
                                                   class="tag-input"
                                                   placeholder="{{ data_get($labSectionForms, $activeLabSectionLabId . '.result_nature') ? '' : 'Select nature...' }}"
                                                   autocomplete="off">
                                                </div>

                                                @if($showLabSectionResultNatureDropdown)
                                                    <div class="tag-dropdown">
                                                        @foreach(['Qualitative', 'Quantitative'] as $nature)
                                                            <div class="tag-dropdown-item" wire:click.stop="$set('labSectionForms.{{ $activeLabSectionLabId }}.result_nature', '{{ $nature }}')">
                                                                {{ $nature }}
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                            @error('labSectionForms.' . $activeLabSectionLabId . '.result_nature') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label form-label--modern" for="lab-section-reading-frequency">Reading Frequency <span class="text-danger">*</span></label>
                                            <select wire:model.live="labSectionForms.{{ $activeLabSectionLabId }}.reading_frequency"
                                                    id="lab-section-reading-frequency"
                                                    class="form-control form-control--modern">
                                                @for($freq = 1; $freq <= 5; $freq++)
                                                    <option value="{{ $freq }}">
                                                        @switch($freq)
                                                            @case(1) Once daily @break
                                                            @case(2) Twice daily @break
                                                            @case(3) Three times daily @break
                                                            @case(4) Four times daily @break
                                                            @case(5) Five times daily @break
                                                        @endswitch
                                                    </option>
                                                @endfor
                                            </select>
                                            @error('labSectionForms.' . $activeLabSectionLabId . '.reading_frequency') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                        </div>

                                        @php
                                            $frequencySchedule = data_get($labSectionForms, $activeLabSectionLabId . '.reading_frequency_schedule', []);
                                        @endphp
                                        @if(is_array($frequencySchedule) && count($frequencySchedule) > 0)
                                            <div class="col-12">
                                                <label class="form-label form-label--modern mb-2">Reading Frequency Schedule</label>
                                                <div class="frequency-schedule-card">
                                                    <div class="table-responsive">
                                                        <table class="table table-sm mb-0 frequency-schedule-table">
                                                            <thead>
                                                                <tr>
                                                                    <th style="width: 110px;">Frequency</th>
                                                                    <th style="width: 160px;">Interval (h)</th>
                                                                    <th>Label</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($frequencySchedule as $index => $row)
                                                                    @php $slot = (int) ($row['frequency'] ?? ($index + 1)); @endphp
                                                                    <tr wire:key="lab-section-frequency-{{ $activeLabSectionLabId }}-{{ $slot }}">
                                                                        <td>
                                                                            <span class="frequency-pill">{{ $slot }}</span>
                                                                        </td>
                                                                        <td>
                                                                            @if($slot === 1)
                                                                                <span class="frequency-interval-muted">—</span>
                                                                            @else
                                                                                <input type="number"
                                                                                       step="0.01"
                                                                                       min="0.01"
                                                                                       wire:model.live="labSectionForms.{{ $activeLabSectionLabId }}.reading_frequency_schedule.{{ $index }}.interval"
                                                                                       class="form-control form-control--modern frequency-interval-input"
                                                                                       placeholder="e.g. 4">
                                                                                @error('labSectionForms.' . $activeLabSectionLabId . '.reading_frequency_schedule.' . $index . '.interval')
                                                                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                                                                @enderror
                                                                            @endif
                                                                        </td>
                                                                        <td>
                                                                            <input type="text"
                                                                                   wire:model.live="labSectionForms.{{ $activeLabSectionLabId }}.reading_frequency_schedule.{{ $index }}.label"
                                                                                   class="form-control form-control--modern frequency-label-input"
                                                                                   placeholder="e.g. Morning check">
                                                                            @error('labSectionForms.' . $activeLabSectionLabId . '.reading_frequency_schedule.' . $index . '.label')
                                                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                                                            @enderror
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                                <small class="text-muted d-block mt-2">Assign a label to each reading. For readings after the first, set the interval in hours since the previous reading.</small>
                                            </div>
                                        @endif
                                    </div>
                                </section>
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer lab-section-modal__footer">
                        <button type="button" class="btn btn-outline-secondary btn-modal-soft" wire:click="resetLabSectionForm('{{ $activeLabSectionLabId }}')">Reset</button>
                        <button type="button" class="btn btn-secondary" wire:click="closeLabSectionModal">Cancel</button>
                        <button type="button" class="btn btn-primary btn-modal-primary" wire:click="saveLabSection('{{ $activeLabSectionLabId }}')">
                            <i class="mdi mdi-content-save"></i> Save Section
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
