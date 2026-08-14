<section class="acc-wizard-section acc-sample-config-section">
    <div class="acc-sample-config-toolbar mb-3">
        <h6 class="acc-wizard-section-title mb-0">Sample configuration</h6>
        @if($allowAddRemoveConfig ?? true)
            <div class="acc-sample-config-toolbar-actions">
                @if(method_exists($this, 'syncFromCustomerPricelist') || method_exists($this, 'syncFromContractPricelist'))
                    <button
                        type="button"
                        class="btn btn-sm btn-outline-secondary acc-sample-config-sync-btn"
                        wire:click="syncFromCustomerPricelist"
                        title="Sync from customer pricelist"
                        aria-label="Sync from customer pricelist"
                    >
                        <i class="mdi mdi-sync"></i>
                    </button>
                @endif
                <button type="button" class="btn btn-sm acc-btn-add" wire:click="addSampleConfig">
                    <i class="mdi mdi-plus"></i> Add sample
                </button>
            </div>
        @endif
    </div>

    <div class="acc-sample-config-list">
        @foreach($this->sampleConfigs as $configIndex => $config)
            @php
                $configId = (string) ($config['id'] ?? '');
                $allParameters = $this->parametersForConfigIndex($configIndex);
                $parameters = $this->filteredParametersForConfigIndex($configIndex);
                $parameterSearch = trim((string) ($config['parameter_search'] ?? ''));
                $showParameterSearch = count($allParameters) > 8 || $parameterSearch !== '';
                $selectedKeys = is_array($config['parameter_keys'] ?? null) ? $config['parameter_keys'] : [];
                $analysisTypes = $this->analysisTypesForConfigIndex($configIndex);
                $sampleConditions = $this->sampleConditionsForConfigIndex($configIndex);
                $showCondition = (bool) ($showSampleConditionOnConfig ?? true);
                $showLabSection = (bool) ($showLabSectionOnConfig ?? true);
                $showMainStandard = (bool) ($showMainStandardOnConfig ?? true);
                $showSecondaryStandard = (bool) ($showSecondaryStandardOnConfig ?? false);
                $showLabId = (bool) ($showLabIdOnConfig ?? false);
                $showAssignedUser = (bool) ($showAssignedUserOnConfig ?? false);
                $showSampleDetails = (bool) ($showSampleDetailsOnConfig ?? ($showSampleInstancesOnConfig ?? false));
                $showQuantity = (bool) ($showQuantityOnConfig ?? false);
                $showParameters = (bool) ($showParametersOnConfig ?? true);
                $showParameterLabSections = (bool) ($showParameterLabSectionsOnConfig ?? false);
                $showSectionAnalysts = (bool) ($showSectionAnalystsOnConfig ?? false);
                $readOnlyTypes = (bool) ($readOnlyConfigTypes ?? false);
                $expandParameters = (bool) ($defaultExpandParameters ?? false);
                $expandSampleDetails = (bool) ($defaultExpandSampleDetails ?? ($defaultExpandInstances ?? false));
                $showInstancePhoto = (bool) ($showInstancePhotoOnConfig ?? false);
                $showInstanceDisposal = (bool) ($showInstanceDisposalOnConfig ?? false);
                $compactTable = (bool) ($compactConfigTable ?? false);
                // Always-present columns: Sample type + Analysis type(s).
                $configColspan = 2
                    + ($showLabSection ? 1 : 0)
                    + ($showCondition ? 1 : 0)
                    + ($showMainStandard ? 1 : 0)
                    + ($showSecondaryStandard ? 1 : 0)
                    + ($showLabId ? 1 : 0)
                    + ($showAssignedUser ? 1 : 0)
                    + ($showQuantity ? 1 : 0);
                $sampleConfigService = app(\App\Services\Sampleworkflow\AcceptanceFormSampleConfigService::class);
                $selectedSampleTypeIds = $sampleConfigService->sampleTypeIdsFromConfig($config);
                $selectedSampleTypes = collect($this->configSampleTypes)->whereIn('id', $selectedSampleTypeIds)->values();
                $selectedSampleTypeNames = $selectedSampleTypes->pluck('name')->filter()->values()->all();
                $sampleTypeName = $selectedSampleTypeNames !== [] ? implode(', ', $selectedSampleTypeNames) : '—';
                $allowsMultipleSampleTypes = (bool) ($config['allows_multiple_sample_types'] ?? false);
                $availableSampleTypes = collect($this->configSampleTypes)
                    ->reject(fn (array $type): bool => in_array((string) ($type['id'] ?? ''), $selectedSampleTypeIds, true))
                    ->values();
                $selectedAnalysisTypeIds = $sampleConfigService->analysisTypeIdsFromConfig($config);
                $selectedAnalysisTypeNames = collect($analysisTypes)
                    ->whereIn('id', $selectedAnalysisTypeIds)
                    ->pluck('name')
                    ->filter()
                    ->values()
                    ->all();
                $analysisTypeName = $selectedAnalysisTypeNames !== []
                    ? implode(', ', $selectedAnalysisTypeNames)
                    : '—';
                $availableAnalysisTypes = collect($analysisTypes)
                    ->reject(fn (array $type): bool => in_array((string) ($type['id'] ?? ''), $selectedAnalysisTypeIds, true))
                    ->values()
                    ->all();
                // Never surface a raw id if the analysis type no longer exists in the catalog.
                $selectedAnalysisTypeChips = collect($selectedAnalysisTypeIds)
                    ->map(function (string $selectedTypeId) use ($analysisTypes): array {
                        $match = collect($analysisTypes)->firstWhere('id', $selectedTypeId);
                        $label = trim((string) ($match['name'] ?? ''));

                        return [
                            'id' => $selectedTypeId,
                            'name' => $label !== '' ? $label : 'Unknown analysis type',
                        ];
                    })
                    ->all();
                $photoKey = $this->instancePhotoUploadKey($configId);
                $selectedParamRows = $showParameterLabSections ? $this->selectedParametersWithLabSections($configIndex) : [];
                $sectionAnalystRows = $showSectionAnalysts ? $this->labSectionsForAnalystAssignment($configIndex) : [];
            @endphp
            <div
                class="acc-sample-config-card"
                wire:key="sample-config-{{ $configId }}"
                x-data="{
                    showParameters: @js($expandParameters),
                    showLabSections: @js($expandParameters),
                    showAnalysts: @js($expandParameters),
                    showSampleDetails: @js($expandSampleDetails)
                }"
            >
                <div class="acc-sample-config-card-head">
                    <span class="acc-sample-config-card-title">
                        <i class="mdi mdi-flask-outline"></i>
                        Sample {{ $configIndex + 1 }}
                    </span>
                    @if(($allowAddRemoveConfig ?? true) && count($this->sampleConfigs) > 1)
                        <button
                            type="button"
                            class="btn btn-sm acc-btn-remove"
                            wire:click="removeSampleConfig('{{ $configId }}')"
                            title="Remove sample"
                        >
                            <i class="mdi mdi-trash-can-outline"></i>
                        </button>
                    @endif
                </div>

                <div class="acc-sample-config-table-wrap">
                    <table class="table acc-sample-config-table mb-0 {{ $compactTable ? 'acc-sample-config-table--acceptance' : ($showCondition ? 'acc-sample-config-table--with-condition' : 'acc-sample-config-table--compact') }}">
                        @if($compactTable)
                            <colgroup>
                                <col class="acc-col-sample-type">
                                <col class="acc-col-analysis-type">
                                @if($showCondition)
                                    <col class="acc-col-condition">
                                @endif
                                @if($showMainStandard)
                                    <col class="acc-col-main-standard">
                                @endif
                                @if($showAssignedUser)
                                    <col class="acc-col-assigned-user">
                                @endif
                                @if($showLabId)
                                    <col class="acc-col-lab">
                                @endif
                                @if($showLabSection)
                                    <col class="acc-col-lab-section">
                                @endif
                                @if($showQuantity)
                                    <col class="acc-col-qty">
                                @endif
                            </colgroup>
                        @endif
                        <thead>
                            <tr>
                                <th>Sample type</th>
                                <th>Analysis type(s)</th>
                                @if($showCondition)
                                    <th>{{ $compactTable ? 'Condition' : 'Condition of sample' }}</th>
                                @endif
                                @if($showMainStandard)
                                    <th>{{ $compactTable ? 'Specification' : 'Specification' }}</th>
                                @endif
                                @if($showSecondaryStandard)
                                    <th>Secondary standard</th>
                                @endif
                                @if($showAssignedUser)
                                    <th>{{ $compactTable ? 'User' : 'Assigned user' }}</th>
                                @endif
                                @if($showLabId)
                                    <th>Lab</th>
                                @endif
                                @if($showLabSection)
                                    <th>Lab section</th>
                                @endif
                                @if($showQuantity)
                                    <th class="text-center">{{ $compactTable ? 'Qty' : 'No. of samples' }}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="acc-sample-config-main-row">
                                <td>
                                    @if($readOnlyTypes)
                                        <span class="acc-config-readonly">{{ $sampleTypeName }}</span>
                                    @elseif($allowsMultipleSampleTypes)
                                        @include('livewire.partials.acc-tag-combobox', [
                                            'comboKey' => 'sample-types-'.$configId,
                                            'comboClass' => 'acc-sample-type-tags',
                                            'comboOptions' => $availableSampleTypes,
                                            'comboSelected' => $selectedSampleTypes,
                                            'comboToggleMethod' => 'toggleConfigSampleType',
                                            'comboToggleArgs' => [$configId],
                                            'comboAriaLabel' => 'Search sample types',
                                            'comboPlaceholder' => 'Search sample type…',
                                            'comboChipsEmptyText' => 'No sample types selected',
                                            'comboOptionsEmptyText' => 'All sample types are selected.',
                                        ])
                                        @error('sampleConfigs.'.$configIndex.'.sample_type_id')<div class="text-danger small">{{ $message }}</div>@enderror
                                    @else
                                        @include('livewire.partials.acc-tag-combobox', [
                                            'comboKey' => 'sample-type-'.$configId,
                                            'comboClass' => 'acc-sample-type-tags',
                                            'comboOptions' => $availableSampleTypes,
                                            'comboSelected' => $selectedSampleTypes,
                                            'comboToggleMethod' => 'setConfigSampleType',
                                            'comboToggleArgs' => [$configIndex],
                                            'comboRemoveValue' => '',
                                            'comboAriaLabel' => 'Search sample types',
                                            'comboPlaceholder' => 'Search sample type…',
                                            'comboChipsEmptyText' => 'No sample type selected',
                                            'comboOptionsEmptyText' => 'No sample types available.',
                                        ])
                                        @error('sampleConfigs.'.$configIndex.'.sample_type_id')<div class="text-danger small">{{ $message }}</div>@enderror
                                    @endif
                                </td>
                                <td>
                                    @if($readOnlyTypes)
                                        <span class="acc-config-readonly">{{ $analysisTypeName }}</span>
                                    @else
                                        @include('livewire.partials.acc-tag-combobox', [
                                            'comboKey' => 'analysis-types-'.$configId,
                                            'comboClass' => 'acc-analysis-type-tags',
                                            'comboOptions' => $availableAnalysisTypes,
                                            'comboSelected' => $selectedAnalysisTypeChips,
                                            'comboToggleMethod' => 'toggleConfigAnalysisType',
                                            'comboToggleArgs' => [$configId],
                                            'comboDisabled' => $selectedSampleTypeIds === [],
                                            'comboAriaLabel' => 'Search analysis types',
                                            'comboPlaceholder' => 'Search analysis type…',
                                            'comboDisabledPlaceholder' => 'Select sample type first…',
                                            'comboChipsEmptyText' => 'No analysis types selected',
                                            'comboOptionsEmptyText' => $selectedSampleTypeIds === []
                                                ? 'Select a sample type first.'
                                                : 'All analysis types are selected.',
                                        ])
                                        @error('sampleConfigs.'.$configIndex.'.analysis_type_id')<div class="text-danger small">{{ $message }}</div>@enderror
                                    @endif
                                </td>
                                @if($showCondition)
                                <td>
                                    <select class="form-control form-control-sm acc-input" wire:model.live="sampleConfigs.{{ $configIndex }}.sample_condition_id">
                                        <option value="">&mdash;</option>
                                        @foreach($sampleConditions as $condition)
                                            <option value="{{ $condition['id'] }}">{{ $condition['name'] }}</option>
                                        @endforeach
                                    </select>
                                    @error('sampleConfigs.'.$configIndex.'.sample_condition_id')<div class="text-danger small">{{ $message }}</div>@enderror
                                </td>
                                @endif
                                @if($showMainStandard)
                                <td>
                                    <select class="form-control form-control-sm acc-input" wire:model.live="sampleConfigs.{{ $configIndex }}.main_standard_id">
                                        <option value="">—</option>
                                        @foreach($this->configStandards as $standard)
                                            <option value="{{ $standard['id'] }}">{{ $standard['name'] }}</option>
                                        @endforeach
                                    </select>
                                    @error('sampleConfigs.'.$configIndex.'.main_standard_id')<div class="text-danger small">{{ $message }}</div>@enderror
                                </td>
                                @endif
                                @if($showSecondaryStandard)
                                <td>
                                    <select class="form-control form-control-sm acc-input" wire:model.live="sampleConfigs.{{ $configIndex }}.secondary_standard_id">
                                        <option value="">—</option>
                                        @foreach($this->configStandards as $standard)
                                            <option value="{{ $standard['id'] }}">{{ $standard['name'] }}</option>
                                        @endforeach
                                    </select>
                                    @error('sampleConfigs.'.$configIndex.'.secondary_standard_id')<div class="text-danger small">{{ $message }}</div>@enderror
                                </td>
                                @endif
                                @if($showAssignedUser)
                                <td>
                                    <select class="form-control form-control-sm acc-input" wire:model.live="sampleConfigs.{{ $configIndex }}.assigned_user_id">
                                        <option value="">Select…</option>
                                        @foreach($this->configAssignableUsers as $user)
                                            <option value="{{ $user['id'] }}">{{ $user['name'] }}</option>
                                        @endforeach
                                    </select>
                                    @error('sampleConfigs.'.$configIndex.'.assigned_user_id')<div class="text-danger small">{{ $message }}</div>@enderror
                                </td>
                                @endif
                                @if($showLabId)
                                <td>
                                    <select class="form-control form-control-sm acc-input" wire:model.live="sampleConfigs.{{ $configIndex }}.lab_id">
                                        <option value="">Select…</option>
                                        @foreach($this->configLabs as $lab)
                                            <option value="{{ $lab['id'] }}">{{ $lab['name'] }}</option>
                                        @endforeach
                                    </select>
                                    @error('sampleConfigs.'.$configIndex.'.lab_id')<div class="text-danger small">{{ $message }}</div>@enderror
                                </td>
                                @endif
                                @if($showLabSection)
                                <td>
                                    <select
                                        class="form-control form-control-sm acc-input"
                                        wire:model.live="sampleConfigs.{{ $configIndex }}.lab_section_id"
                                        @disabled($selectedAnalysisTypeIds === [])
                                    >
                                        <option value="">Select…</option>
                                        @foreach($this->configLabSections as $section)
                                            <option value="{{ $section['id'] }}">{{ $section['name'] }}</option>
                                        @endforeach
                                    </select>
                                    @error('sampleConfigs.'.$configIndex.'.lab_section_id')<div class="text-danger small">{{ $message }}</div>@enderror
                                </td>
                                @endif
                                @if($showQuantity)
                                <td class="text-center">
                                    @if($readOnlyTypes)
                                        <span class="acc-config-readonly">{{ (int) ($config['number_of_samples'] ?? 1) }}</span>
                                    @else
                                        <input
                                            type="number"
                                            min="1"
                                            class="form-control form-control-sm acc-input-sm text-center"
                                            wire:model.live="sampleConfigs.{{ $configIndex }}.number_of_samples"
                                        >
                                    @endif
                                </td>
                                @endif
                            </tr>

                            @if($showParameters)
                            <tr class="acc-sample-config-params-row">
                                <td colspan="{{ $configColspan }}">
                                    <div class="acc-sample-config-params-panel">
                                        <div class="acc-sample-config-params-band">
                                            <button
                                                type="button"
                                                class="acc-sample-config-section-toggle"
                                                @click="showParameters = !showParameters"
                                                :aria-expanded="showParameters"
                                            >
                                                <span class="acc-sample-config-section-toggle-main">
                                                    <i class="mdi acc-sample-config-chevron" :class="showParameters ? 'mdi-chevron-down' : 'mdi-chevron-right'"></i>
                                                    <span class="acc-sample-config-params-label">Test parameters</span>
                                                    @if(count($selectedKeys) > 0)
                                                        <span class="acc-sample-config-section-badge">{{ count($selectedKeys) }} selected</span>
                                                    @endif
                                                </span>
                                            </button>
                                            <div class="acc-sample-config-params-band-actions" @click.stop>
                                                <button
                                                    type="button"
                                                    class="btn btn-sm acc-sample-config-select-all"
                                                    wire:click.prevent="selectAllConfigParameters('{{ $configId }}')"
                                                    @disabled($selectedAnalysisTypeIds === [])
                                                >
                                                    Select all
                                                </button>
                                                <button
                                                    type="button"
                                                    class="btn btn-sm acc-sample-config-select-all"
                                                    wire:click.prevent="deselectAllConfigParameters('{{ $configId }}')"
                                                    @disabled($selectedAnalysisTypeIds === [] || count($selectedKeys) === 0)
                                                >
                                                    Deselect all
                                                </button>
                                            </div>
                                        </div>
                                        <div class="acc-sample-config-section-body" x-show="showParameters" x-cloak>
                                            @include('livewire.partials.acceptance-sample-config-parameters')
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endif

                            @if($showParameterLabSections)
                            <tr class="acc-sample-config-section-row">
                                <td colspan="{{ $configColspan }}">
                                    <div class="acc-sample-config-params-panel">
                                        <div class="acc-sample-config-params-band">
                                            <button
                                                type="button"
                                                class="acc-sample-config-section-toggle"
                                                @click="showLabSections = !showLabSections"
                                                :aria-expanded="showLabSections"
                                            >
                                                <span class="acc-sample-config-section-toggle-main">
                                                    <i class="mdi acc-sample-config-chevron" :class="showLabSections ? 'mdi-chevron-down' : 'mdi-chevron-right'"></i>
                                                    <span class="acc-sample-config-params-label">2. Lab section for each test</span>
                                                    @if(count($selectedParamRows) > 0)
                                                        <span class="acc-sample-config-section-badge">{{ count($selectedParamRows) }}</span>
                                                    @endif
                                                </span>
                                            </button>
                                        </div>
                                        <div class="acc-sample-config-section-body" x-show="showLabSections" x-cloak>
                                            @if($selectedParamRows === [])
                                                <p class="acc-wizard-hint mb-0">Select test parameters first.</p>
                                            @else
                                                <div class="acc-param-lab-section-list">
                                                    @foreach($selectedParamRows as $paramRow)
                                                        @php
                                                            $paramKey = (string) $paramRow['id'];
                                                            $display = trim((string) ($paramRow['code'] ?? '')) !== ''
                                                                ? (string) $paramRow['code']
                                                                : (string) ($paramRow['name'] ?? 'Parameter');
                                                        @endphp
                                                        <div class="acc-param-lab-section-row" wire:key="param-section-{{ $configId }}-{{ $paramKey }}">
                                                            <div class="acc-param-lab-section-label" title="{{ $paramRow['label'] ?? $display }}">
                                                                {{ $display }}
                                                            </div>
                                                            <select
                                                                class="form-control form-control-sm acc-input"
                                                                wire:change="setParameterLabSection('{{ $configId }}', '{{ $paramKey }}', $event.target.value)"
                                                            >
                                                                <option value="">Select lab section…</option>
                                                                @foreach($this->configLabSections as $section)
                                                                    <option
                                                                        value="{{ $section['id'] }}"
                                                                        @selected((string) ($paramRow['lab_section_id'] ?? '') === (string) $section['id'])
                                                                    >{{ $section['name'] }}</option>
                                                                @endforeach
                                                            </select>
                                                            @error('sampleConfigs.'.$configIndex.'.parameter_lab_sections.'.$paramKey)
                                                                <div class="text-danger small">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endif

                            @if($showSectionAnalysts)
                            <tr class="acc-sample-config-section-row">
                                <td colspan="{{ $configColspan }}">
                                    <div class="acc-sample-config-params-panel">
                                        <div class="acc-sample-config-params-band">
                                            <button
                                                type="button"
                                                class="acc-sample-config-section-toggle"
                                                @click="showAnalysts = !showAnalysts"
                                                :aria-expanded="showAnalysts"
                                            >
                                                <span class="acc-sample-config-section-toggle-main">
                                                    <i class="mdi acc-sample-config-chevron" :class="showAnalysts ? 'mdi-chevron-down' : 'mdi-chevron-right'"></i>
                                                    <span class="acc-sample-config-params-label">3. Assign analyst(s)</span>
                                                    @if(count($sectionAnalystRows) > 0)
                                                        <span class="acc-sample-config-section-badge">{{ count($sectionAnalystRows) }} section{{ count($sectionAnalystRows) === 1 ? '' : 's' }}</span>
                                                    @endif
                                                </span>
                                            </button>
                                        </div>
                                        <div class="acc-sample-config-section-body" x-show="showAnalysts" x-cloak>
                                            @if($sectionAnalystRows === [])
                                                <p class="acc-wizard-hint mb-0">Choose lab sections for the selected tests to load analysts.</p>
                                            @else
                                                <div class="acc-section-analyst-list">
                                                    @foreach($sectionAnalystRows as $sectionRow)
                                                        @php
                                                            $sectionId = (string) $sectionRow['id'];
                                                            $assignedIds = $sectionRow['analyst_ids'] ?? [];
                                                            $analystOptions = $this->analystsForLabSection($sectionId);
                                                        @endphp
                                                        <div class="acc-section-analyst-block" wire:key="section-analysts-{{ $configId }}-{{ $sectionId }}">
                                                            <div class="acc-section-analyst-heading">
                                                                <strong>{{ $sectionRow['name'] }}</strong>
                                                                <span class="acc-wizard-hint">
                                                                    {{ count($assignedIds) }} selected
                                                                </span>
                                                            </div>
                                                            @if($analystOptions === [])
                                                                <p class="acc-wizard-hint mb-0">No analysts are tied to this lab section.</p>
                                                            @else
                                                                <div class="acc-section-analyst-grid">
                                                                    @foreach($analystOptions as $analyst)
                                                                        @php $isChecked = in_array($analyst['id'], $assignedIds, true); @endphp
                                                                        <label class="acc-sample-config-param-chip {{ $isChecked ? 'is-selected' : '' }}">
                                                                            <input
                                                                                type="checkbox"
                                                                                @checked($isChecked)
                                                                                wire:click="toggleSectionAnalyst('{{ $configId }}', '{{ $sectionId }}', '{{ $analyst['id'] }}')"
                                                                            >
                                                                            <span>{{ $analyst['name'] }}</span>
                                                                        </label>
                                                                    @endforeach
                                                                </div>
                                                            @endif
                                                            @error('sampleConfigs.'.$configIndex.'.analysts_by_lab_section.'.$sectionId)
                                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endif

                            @if($showSampleDetails)
                                <tr class="acc-sample-config-section-row">
                                    <td colspan="{{ $configColspan }}">
                                        <div class="acc-sample-config-params-panel acc-sample-config-instances-panel">
                                            <div class="acc-sample-config-params-band acc-sample-config-instances-band">
                                                <button
                                                    type="button"
                                                    class="acc-sample-config-section-toggle"
                                                    @click="showSampleDetails = !showSampleDetails"
                                                    :aria-expanded="showSampleDetails"
                                                >
                                                    <span class="acc-sample-config-section-toggle-main">
                                                        <i class="mdi acc-sample-config-chevron" :class="showSampleDetails ? 'mdi-chevron-down' : 'mdi-chevron-right'"></i>
                                                        <span class="acc-sample-config-instances-title">Sample details</span>
                                                    </span>
                                                </button>
                                            </div>
                                            <div class="acc-sample-config-section-body acc-sample-config-instances-body" x-show="showSampleDetails" x-cloak>
                                                <div class="acc-sample-config-instance-item" wire:key="details-{{ $configId }}">
                                                    <div class="acc-sample-config-instance-fields">
                                                        <div class="acc-sample-config-instance-field">
                                                            <label class="acc-label mb-1">Customer sample ID</label>
                                                            <input
                                                                type="text"
                                                                class="form-control form-control-sm acc-input"
                                                                wire:model.live="sampleConfigs.{{ $configIndex }}.customer_sample_id"
                                                                placeholder="Customer reference"
                                                            >
                                                        </div>
                                                        <div class="acc-sample-config-instance-field">
                                                            <label class="acc-label mb-1">Sample marking</label>
                                                            <input
                                                                type="text"
                                                                class="form-control form-control-sm acc-input"
                                                                wire:model.live="sampleConfigs.{{ $configIndex }}.sample_marking"
                                                                placeholder="Marking / description"
                                                            >
                                                        </div>
                                                        @if($showInstanceDisposal)
                                                        <div class="acc-sample-config-instance-field">
                                                            <label class="acc-label mb-1">Disposal date</label>
                                                            <input
                                                                type="date"
                                                                class="form-control form-control-sm acc-input"
                                                                wire:model.live="sampleConfigs.{{ $configIndex }}.disposal_date"
                                                            >
                                                        </div>
                                                        @endif
                                                        @if($showInstancePhoto)
                                                        <div class="acc-sample-config-instance-field">
                                                            <label class="acc-label mb-1">Sample photo</label>
                                                            <input
                                                                type="file"
                                                                class="form-control-file form-control-sm"
                                                                accept="image/*"
                                                                wire:model="instancePhotoUploads.{{ $photoKey }}"
                                                            >
                                                            @if(!empty($config['photo_path']))
                                                                <small class="text-muted d-block mt-1">Photo attached</small>
                                                            @endif
                                                            @error('instancePhotoUploads.'.$photoKey)<div class="text-danger small">{{ $message }}</div>@enderror
                                                        </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    </div>
</section>
