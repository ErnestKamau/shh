<section class="acc-wizard-section acc-sample-config-section">
    <div class="acc-sample-config-toolbar mb-3">
        <h6 class="acc-wizard-section-title mb-0">Sample configuration</h6>
        @if($allowAddRemoveConfig ?? true)
            <div class="acc-sample-config-toolbar-actions">
                @if(method_exists($this, 'syncFromContractPricelist'))
                    <button
                        type="button"
                        class="btn btn-sm btn-outline-secondary acc-sample-config-sync-btn"
                        wire:click="syncFromContractPricelist"
                        title="Sync from contract pricelist"
                        aria-label="Sync from contract pricelist"
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
                $parameters = $this->filteredParametersForConfigIndex($configIndex);
                $selectedKeys = is_array($config['parameter_keys'] ?? null) ? $config['parameter_keys'] : [];
                $analysisTypes = $this->analysisTypesForConfigIndex($configIndex);
                $sampleConditions = $this->sampleConditionsForConfigIndex($configIndex);
                $showCondition = (bool) ($showSampleConditionOnConfig ?? true);
                $showLabSection = (bool) ($showLabSectionOnConfig ?? true);
                $showMainStandard = (bool) ($showMainStandardOnConfig ?? true);
                $showSecondaryStandard = (bool) ($showSecondaryStandardOnConfig ?? false);
                $showLabId = (bool) ($showLabIdOnConfig ?? false);
                $showSampleDetails = (bool) ($showSampleDetailsOnConfig ?? ($showSampleInstancesOnConfig ?? false));
                $showQuantity = (bool) ($showQuantityOnConfig ?? false);
                $showParameters = (bool) ($showParametersOnConfig ?? true);
                $readOnlyTypes = (bool) ($readOnlyConfigTypes ?? false);
                $expandParameters = (bool) ($defaultExpandParameters ?? false);
                $expandSampleDetails = (bool) ($defaultExpandSampleDetails ?? ($defaultExpandInstances ?? false));
                $showInstancePhoto = (bool) ($showInstancePhotoOnConfig ?? false);
                $showInstanceDisposal = (bool) ($showInstanceDisposalOnConfig ?? false);
                $compactTable = (bool) ($compactConfigTable ?? false);
                $configColspan = 3
                    + ($showLabSection ? 1 : 0)
                    + ($showCondition ? 1 : 0)
                    + ($showMainStandard ? 1 : 0)
                    + ($showSecondaryStandard ? 1 : 0)
                    + ($showLabId ? 1 : 0)
                    + ($showQuantity ? 1 : 0);
                $sampleTypeName = collect($this->configSampleTypes)->firstWhere('id', (string) ($config['sample_type_id'] ?? ''))['name'] ?? '—';
                $analysisTypeName = collect($analysisTypes)->firstWhere('id', (string) ($config['analysis_type_id'] ?? ''))['name'] ?? '—';
                $photoKey = $this->instancePhotoUploadKey($configId);
            @endphp
            <div
                class="acc-sample-config-card"
                wire:key="sample-config-{{ $configId }}"
                x-data="{ showParameters: @js($expandParameters), showSampleDetails: @js($expandSampleDetails) }"
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
                                @if($showLabId)
                                    <col class="acc-col-lab">
                                @endif
                                @if($showQuantity)
                                    <col class="acc-col-qty">
                                @endif
                            </colgroup>
                        @endif
                        <thead>
                            <tr>
                                <th>Sample type</th>
                                <th>Analysis type</th>
                                @if($showLabSection)
                                    <th>Lab section</th>
                                @endif
                                @if($showCondition)
                                    <th>{{ $compactTable ? 'Condition' : 'Condition of sample' }}</th>
                                @endif
                                @if($showMainStandard)
                                    <th>{{ $compactTable ? 'Main std.' : 'Main standard' }}</th>
                                @endif
                                @if($showSecondaryStandard)
                                    <th>Secondary standard</th>
                                @endif
                                @if($showLabId)
                                    <th>Lab</th>
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
                                    @else
                                        <select
                                            class="form-control form-control-sm acc-input"
                                            wire:model.live="sampleConfigs.{{ $configIndex }}.sample_type_id"
                                            wire:change="onConfigSampleTypeChanged({{ $configIndex }})"
                                        >
                                            <option value="">Select…</option>
                                            @foreach($this->configSampleTypes as $type)
                                                <option value="{{ $type['id'] }}">{{ $type['name'] }}</option>
                                            @endforeach
                                        </select>
                                        @error('sampleConfigs.'.$configIndex.'.sample_type_id')<div class="text-danger small">{{ $message }}</div>@enderror
                                    @endif
                                </td>
                                <td>
                                    @if($readOnlyTypes)
                                        <span class="acc-config-readonly">{{ $analysisTypeName }}</span>
                                    @else
                                        <select
                                            class="form-control form-control-sm acc-input"
                                            wire:model.live="sampleConfigs.{{ $configIndex }}.analysis_type_id"
                                            wire:change="onConfigAnalysisTypeChanged({{ $configIndex }})"
                                            @disabled(empty($config['sample_type_id']))
                                        >
                                            <option value="">Select…</option>
                                            @foreach($analysisTypes as $type)
                                                <option value="{{ $type['id'] }}">{{ $type['name'] }}</option>
                                            @endforeach
                                        </select>
                                        @error('sampleConfigs.'.$configIndex.'.analysis_type_id')<div class="text-danger small">{{ $message }}</div>@enderror
                                    @endif
                                </td>
                                @if($showLabSection)
                                <td>
                                    <select
                                        class="form-control form-control-sm acc-input"
                                        wire:model.live="sampleConfigs.{{ $configIndex }}.lab_section_id"
                                        @disabled(empty($config['analysis_type_id']))
                                    >
                                        <option value="">Select…</option>
                                        @foreach($this->configLabSections as $section)
                                            <option value="{{ $section['id'] }}">{{ $section['name'] }}</option>
                                        @endforeach
                                    </select>
                                    @error('sampleConfigs.'.$configIndex.'.lab_section_id')<div class="text-danger small">{{ $message }}</div>@enderror
                                </td>
                                @endif
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
                                                    <span class="acc-sample-config-params-label">Parameters</span>
                                                    @if(count($selectedKeys) > 0)
                                                        <span class="acc-sample-config-section-badge">{{ count($selectedKeys) }} selected</span>
                                                    @endif
                                                </span>
                                            </button>
                                            <div class="acc-sample-config-params-band-actions" @click.stop>
                                                <button
                                                    type="button"
                                                    class="btn btn-sm acc-sample-config-select-all"
                                                    wire:click="selectAllConfigParameters('{{ $configId }}')"
                                                    @disabled(empty($config['analysis_type_id']))
                                                >
                                                    Select all
                                                </button>
                                            </div>
                                        </div>
                                        <div class="acc-sample-config-section-body" x-show="showParameters" x-cloak>
                                            @if(count($parameters) > 8)
                                                <div class="acc-sample-config-params-search-row">
                                                    <input
                                                        type="search"
                                                        class="form-control form-control-sm acc-input acc-sample-config-search"
                                                        placeholder="Search parameters…"
                                                        wire:model.live="sampleConfigs.{{ $configIndex }}.parameter_search"
                                                    >
                                                </div>
                                            @endif
                                            @if(empty($config['analysis_type_id']))
                                                <p class="acc-wizard-hint mb-0">Select sample type and analysis type to load parameters.</p>
                                            @elseif($parameters === [])
                                                <p class="acc-wizard-hint mb-0">No parameters found for this analysis type on the customer pricelist.</p>
                                            @else
                                                <div class="acc-sample-config-param-grid">
                                                    @foreach($parameters as $param)
                                                        @php
                                                            $paramKey = (string) ($param['analysis_element_id'] ?? $param['id'] ?? '');
                                                            $isSelected = in_array($paramKey, $selectedKeys, true);
                                                        @endphp
                                                        <label class="acc-sample-config-param-chip {{ $isSelected ? 'is-selected' : '' }}">
                                                            <input
                                                                type="checkbox"
                                                                @checked($isSelected)
                                                                wire:click="toggleConfigParameter('{{ $configId }}', '{{ $paramKey }}')"
                                                            >
                                                            <span>{{ $param['label'] ?? 'Parameter' }}</span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            @endif
                                            @error('sampleConfigs.'.$configIndex.'.parameter_keys')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
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
