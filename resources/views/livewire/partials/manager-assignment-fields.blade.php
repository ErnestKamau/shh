@php
    $analystOptions = $analystOptions ?? [];
    $signatoryOptions = $signatoryOptions ?? [];
    $assignedAnalystIds = $assignedAnalystIds ?? [];
    $leadAnalystOptions = $leadAnalystOptions ?? [];
@endphp

<section class="acc-wizard-section acc-manager-assignments-section">
    <h6 class="acc-wizard-section-title">
        Laboratory manager — assignments
        <span class="text-danger">*</span>
    </h6>
    <p class="acc-wizard-hint mb-3">
        Search and select analysts for this batch, then choose the <strong>lead analyst</strong> from that list.
        The lead is not assigned automatically. Also select the technical signatory for the report.
    </p>

    <div class="form-group mb-3">
        <label class="acc-label d-block">Analysts assigned to this batch <span class="text-danger">*</span></label>
        <div
            class="tag-select-container @error('assignedAnalystIds') is-invalid @enderror"
            wire:click="openAssignedAnalystDropdown"
            wire:click.outside="closeAssignedAnalystDropdown"
        >
            <div class="tag-select-input">
                @foreach($assignedAnalystIds as $analystId)
                    @php($analystName = $this->managerAssignmentUserName($analystId, 'analyst'))
                    @if($analystName)
                        <span class="tag-badge tag-badge--success" wire:key="mgr-assigned-tag-{{ $analystId }}">
                            {{ $analystName }}
                            <i class="mdi mdi-close-circle" wire:click.stop="removeAssignedAnalyst('{{ $analystId }}')"></i>
                        </span>
                    @endif
                @endforeach
                <input
                    type="text"
                    wire:model.live="assignedAnalystSearch"
                    wire:focus="openAssignedAnalystDropdown"
                    class="tag-input"
                    placeholder="{{ count($assignedAnalystIds) > 0 ? 'Add more analysts…' : 'Search analysts…' }}"
                    autocomplete="off"
                >
            </div>
            @if($showAssignedAnalystDropdown)
                <div class="tag-dropdown">
                    @forelse($this->filteredAnalystPickerOptions as $option)
                        <div
                            class="tag-dropdown-item d-flex justify-content-between align-items-center"
                            wire:key="mgr-analyst-pick-{{ $option['id'] }}"
                            wire:click.stop="toggleAssignedAnalyst('{{ $option['id'] }}')"
                        >
                            <span>{{ $option['name'] }}</span>
                            @if(in_array($option['id'], $assignedAnalystIds, true))
                                <i class="mdi mdi-check text-success"></i>
                            @endif
                        </div>
                    @empty
                        <div class="tag-dropdown-item text-muted">No analysts found</div>
                    @endforelse
                </div>
            @endif
        </div>
        @error('assignedAnalystIds') <small class="text-danger d-block">{{ $message }}</small> @enderror
        @error('assignedAnalystIds.*') <small class="text-danger d-block">{{ $message }}</small> @enderror
    </div>

    <div class="row acc-wizard-fields">
        <div class="col-md-6 form-group">
            <label class="acc-label d-block">Lead analyst <span class="text-danger">*</span></label>
            <div
                class="tag-select-container @error('leadAnalystId') is-invalid @enderror {{ count($assignedAnalystIds) === 0 ? 'tag-select-container--disabled' : '' }}"
                wire:click="openLeadAnalystDropdown"
                wire:click.outside="closeLeadAnalystDropdown"
            >
                <div class="tag-select-input">
                    @if($leadAnalystId !== '' && $this->managerAssignmentUserName($leadAnalystId, 'analyst'))
                        <span class="tag-badge tag-badge--primary">
                            {{ $this->managerAssignmentUserName($leadAnalystId, 'analyst') }}
                            <i class="mdi mdi-close-circle" wire:click.stop="clearLeadAnalyst"></i>
                        </span>
                    @endif
                    <input
                        type="text"
                        wire:model.live="leadAnalystSearch"
                        wire:focus="openLeadAnalystDropdown"
                        class="tag-input"
                        placeholder="{{ count($assignedAnalystIds) === 0 ? 'Select assigned analysts first…' : ($leadAnalystId !== '' ? 'Change lead analyst…' : 'Search lead analyst…') }}"
                        autocomplete="off"
                        @disabled(count($assignedAnalystIds) === 0)
                    >
                </div>
                @if($showLeadAnalystDropdown && count($assignedAnalystIds) > 0)
                    <div class="tag-dropdown">
                        @forelse($this->filteredLeadAnalystPickerOptions as $option)
                            <div
                                class="tag-dropdown-item d-flex justify-content-between align-items-center"
                                wire:key="mgr-lead-pick-{{ $option['id'] }}"
                                wire:click.stop="selectLeadAnalyst('{{ $option['id'] }}')"
                            >
                                <span>{{ $option['name'] }}</span>
                                @if($leadAnalystId === $option['id'])
                                    <i class="mdi mdi-check text-success"></i>
                                @endif
                            </div>
                        @empty
                            <div class="tag-dropdown-item text-muted">No matching assigned analysts</div>
                        @endforelse
                    </div>
                @endif
            </div>
            @error('leadAnalystId') <small class="text-danger d-block">{{ $message }}</small> @enderror
        </div>

        <div class="col-md-6 form-group">
            <label class="acc-label d-block">Technical signatory <span class="text-danger">*</span></label>
            <div
                class="tag-select-container @error('technicalSignatoryId') is-invalid @enderror"
                wire:click="openSignatoryDropdown"
                wire:click.outside="closeSignatoryDropdown"
            >
                <div class="tag-select-input">
                    @if($technicalSignatoryId !== '' && $this->managerAssignmentUserName($technicalSignatoryId, 'signatory'))
                        <span class="tag-badge tag-badge--primary">
                            {{ $this->managerAssignmentUserName($technicalSignatoryId, 'signatory') }}
                            <i class="mdi mdi-close-circle" wire:click.stop="clearTechnicalSignatory"></i>
                        </span>
                    @endif
                    <input
                        type="text"
                        wire:model.live="signatorySearch"
                        wire:focus="openSignatoryDropdown"
                        class="tag-input"
                        placeholder="{{ $technicalSignatoryId !== '' ? 'Change signatory…' : 'Search signatory…' }}"
                        autocomplete="off"
                    >
                </div>
                @if($showSignatoryDropdown)
                    <div class="tag-dropdown">
                        @forelse($this->filteredSignatoryPickerOptions as $option)
                            <div
                                class="tag-dropdown-item d-flex justify-content-between align-items-center"
                                wire:key="mgr-signatory-pick-{{ $option['id'] }}"
                                wire:click.stop="selectTechnicalSignatory('{{ $option['id'] }}')"
                            >
                                <span>{{ $option['name'] }}</span>
                                @if($technicalSignatoryId === $option['id'])
                                    <i class="mdi mdi-check text-success"></i>
                                @endif
                            </div>
                        @empty
                            <div class="tag-dropdown-item text-muted">No signatories found</div>
                        @endforelse
                    </div>
                @endif
            </div>
            @error('technicalSignatoryId') <small class="text-danger d-block">{{ $message }}</small> @enderror
        </div>
    </div>
</section>
