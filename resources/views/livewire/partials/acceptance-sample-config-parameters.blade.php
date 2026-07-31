{{-- Parameter picker: tags (Process Enquiry) or grid chips (Acceptance / default) --}}
@php
    $pickerView = $parameterPickerView ?? 'grid';
    $allParameters = $this->parametersForConfigIndex($configIndex);
    $selectedParams = [];
    $availableParams = [];
    foreach ($allParameters as $param) {
        $paramKey = (string) ($param['analysis_element_id'] ?? $param['id'] ?? '');
        if ($paramKey === '') {
            continue;
        }
        if (in_array($paramKey, $selectedKeys, true)) {
            $selectedParams[] = $param;
        } else {
            $availableParams[] = $param;
        }
    }
    $paramCode = static function (array $param): string {
        $code = trim((string) ($param['code'] ?? ''));
        if ($code !== '') {
            return $code;
        }

        return trim((string) ($param['label'] ?? 'Parameter')) ?: 'Parameter';
    };
@endphp

@php
    $configAnalysisTypeIds = app(\App\Services\Sampleworkflow\AcceptanceFormSampleConfigService::class)
        ->analysisTypeIdsFromConfig($config);
@endphp
@if($configAnalysisTypeIds === [])
    <p class="acc-wizard-hint mb-0">Select sample type and at least one analysis type to load parameters.</p>
@elseif($allParameters === [])
    <p class="acc-wizard-hint mb-0">No parameters found for the selected analysis type(s) on the customer pricelist.</p>
@elseif($pickerView === 'tags')
    @php
        $comboOptionFromParam = static function (array $param) use ($paramCode): array {
            $display = $paramCode($param);
            $fullLabel = trim((string) ($param['label'] ?? '')) !== '' ? (string) $param['label'] : $display;

            return [
                'id' => (string) ($param['analysis_element_id'] ?? $param['id'] ?? ''),
                'name' => $display,
                'title' => $fullLabel,
                'search' => $display.' '.$fullLabel,
            ];
        };
    @endphp
    @include('livewire.partials.acc-tag-combobox', [
        'comboKey' => 'params-'.$configId,
        'comboOptions' => array_map($comboOptionFromParam, $availableParams),
        'comboSelected' => array_map($comboOptionFromParam, $selectedParams),
        'comboToggleMethod' => 'toggleConfigParameter',
        'comboToggleArgs' => [$configId],
        'comboAriaLabel' => 'Search parameters',
        'comboPlaceholder' => 'Search parameter…',
        'comboChipsEmptyText' => 'No parameters selected',
        'comboOptionsEmptyText' => 'All parameters are selected.',
    ])
@else
    @if(count($allParameters) > 8)
        <div class="acc-sample-config-params-search-row">
            <input
                type="search"
                class="form-control form-control-sm acc-input acc-sample-config-search"
                placeholder="Search parameters…"
                wire:model.live="sampleConfigs.{{ $configIndex }}.parameter_search"
            >
        </div>
    @endif
    <div class="acc-sample-config-param-grid">
        @foreach($parameters as $param)
            @php
                $paramKey = (string) ($param['analysis_element_id'] ?? $param['id'] ?? '');
                $isSelected = in_array($paramKey, $selectedKeys, true);
                $display = $paramCode($param);
            @endphp
            <label class="acc-sample-config-param-chip {{ $isSelected ? 'is-selected' : '' }}" title="{{ $param['label'] ?? $display }}">
                <input
                    type="checkbox"
                    @checked($isSelected)
                    wire:click="toggleConfigParameter('{{ $configId }}', '{{ $paramKey }}')"
                >
                <span>{{ $display }}</span>
            </label>
        @endforeach
    </div>
@endif

@error('sampleConfigs.'.$configIndex.'.parameter_keys')
    <div class="text-danger small mt-2">{{ $message }}</div>
@enderror
