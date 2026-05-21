<section class="acc-wizard-section acc-sample-config-section">
    <div class="acc-pricing-toolbar mb-3">
        <div>
            <h6 class="acc-wizard-section-title mb-1">Sample configuration</h6>
            <p class="acc-wizard-hint mb-0">
                Each sample type and analysis type combination is configured independently. Set the number of samples to generate customer sample IDs and markings.
            </p>
        </div>
        <button type="button" class="btn btn-sm acc-btn-add" wire:click="addSampleConfig">
            <i class="mdi mdi-plus"></i> Add configuration
        </button>
    </div>

    <div class="acc-sample-config-list">
        @foreach($this->sampleConfigs as $configIndex => $config)
            @php
                $configId = (string) ($config['id'] ?? '');
                $parameters = $this->filteredParametersForConfigIndex($configIndex);
                $selectedKeys = is_array($config['parameter_keys'] ?? null) ? $config['parameter_keys'] : [];
                $instances = is_array($config['instances'] ?? null) ? $config['instances'] : [];
                $analysisTypes = $this->analysisTypesForConfigIndex($configIndex);
            @endphp
            <div
                class="acc-sample-config-card"
                wire:key="sample-config-{{ $configId }}"
                x-data="{ showParameters: true, showInstances: true }"
            >
                <div class="acc-sample-config-card-head">
                    <span class="acc-sample-config-card-title">
                        <i class="mdi mdi-tune-variant"></i>
                        Configuration {{ $configIndex + 1 }}
                    </span>
                    @if(count($this->sampleConfigs) > 1)
                        <button
                            type="button"
                            class="btn btn-sm acc-btn-remove"
                            wire:click="removeSampleConfig('{{ $configId }}')"
                            title="Remove configuration"
                        >
                            <i class="mdi mdi-trash-can-outline"></i>
                        </button>
                    @endif
                </div>

                <div class="acc-sample-config-table-wrap">
                    <table class="table acc-sample-config-table mb-0">
                        <thead>
                            <tr>
                                <th>Sample type</th>
                                <th>Analysis type</th>
                                <th>Condition of sample</th>
                                <th>Main standard</th>
                                <th>Zone</th>
                                <th class="text-center" style="width: 110px;">No. of samples</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="acc-sample-config-main-row">
                                <td>
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
                                </td>
                                <td>
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
                                </td>
                                <td>
                                    <select class="form-control form-control-sm acc-input" wire:model.live="sampleConfigs.{{ $configIndex }}.sample_condition_id">
                                        <option value="">—</option>
                                        @foreach($this->configSampleConditions as $condition)
                                            <option value="{{ $condition['id'] }}">{{ $condition['name'] }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <select class="form-control form-control-sm acc-input" wire:model.live="sampleConfigs.{{ $configIndex }}.main_standard_id">
                                        <option value="">—</option>
                                        @foreach($this->configStandards as $standard)
                                            <option value="{{ $standard['id'] }}">{{ $standard['name'] }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <select class="form-control form-control-sm acc-input" wire:model.live="sampleConfigs.{{ $configIndex }}.zone_id" title="Zone">
                                        <option value="">Select zone…</option>
                                        @foreach($this->configZones as $zone)
                                            <option value="{{ $zone['id'] }}">{{ $zone['name'] }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="text-center">
                                    <input
                                        type="number"
                                        min="1"
                                        class="form-control form-control-sm acc-input-sm text-center"
                                        wire:model.live="sampleConfigs.{{ $configIndex }}.number_of_samples"
                                        wire:change="onConfigNumberOfSamplesChanged({{ $configIndex }})"
                                    >
                                </td>
                            </tr>

                            <tr class="acc-sample-config-params-row">
                                <td colspan="6">
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

                            @if(count($instances) > 0)
                                <tr class="acc-sample-config-section-row">
                                    <td colspan="6">
                                        <div class="acc-sample-config-params-panel acc-sample-config-instances-panel">
                                            <div class="acc-sample-config-params-band acc-sample-config-instances-band">
                                                <button
                                                    type="button"
                                                    class="acc-sample-config-section-toggle"
                                                    @click="showInstances = !showInstances"
                                                    :aria-expanded="showInstances"
                                                >
                                                    <span class="acc-sample-config-section-toggle-main">
                                                        <i class="mdi acc-sample-config-chevron" :class="showInstances ? 'mdi-chevron-down' : 'mdi-chevron-right'"></i>
                                                        <span class="acc-sample-config-instances-title">Sample instances</span>
                                                        <span class="acc-sample-config-section-badge acc-sample-config-instances-badge">{{ count($instances) }} {{ count($instances) === 1 ? 'sample' : 'samples' }}</span>
                                                    </span>
                                                </button>
                                            </div>
                                            <div class="acc-sample-config-section-body acc-sample-config-instances-body" x-show="showInstances" x-cloak>
                                                @foreach($instances as $instanceIndex => $instance)
                                                    <div class="acc-sample-config-instance-item" wire:key="inst-{{ $configId }}-{{ $instanceIndex }}">
                                                        <div class="acc-sample-config-instance-label">Sample {{ $instanceIndex + 1 }}</div>
                                                        <div class="acc-sample-config-instance-fields">
                                                            <div class="acc-sample-config-instance-field">
                                                                <label class="acc-label mb-1">Customer sample ID</label>
                                                                <input
                                                                    type="text"
                                                                    class="form-control form-control-sm acc-input"
                                                                    wire:model.live="sampleConfigs.{{ $configIndex }}.instances.{{ $instanceIndex }}.customer_sample_id"
                                                                    placeholder="Customer reference"
                                                                >
                                                            </div>
                                                            <div class="acc-sample-config-instance-field">
                                                                <label class="acc-label mb-1">Sample marking</label>
                                                                <input
                                                                    type="text"
                                                                    class="form-control form-control-sm acc-input"
                                                                    wire:model.live="sampleConfigs.{{ $configIndex }}.instances.{{ $instanceIndex }}.sample_marking"
                                                                    placeholder="Marking / description"
                                                                >
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
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
