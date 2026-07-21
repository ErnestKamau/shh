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

@if(empty($config['analysis_type_id']))
    <p class="acc-wizard-hint mb-0">Select sample type and analysis type to load parameters.</p>
@elseif($allParameters === [])
    <p class="acc-wizard-hint mb-0">No parameters found for this analysis type on the customer pricelist.</p>
@elseif($pickerView === 'tags')
    <div
        class="acc-param-tags"
        wire:key="param-tags-{{ $configId }}"
        x-data="{
            open: false,
            query: '',
            panelStyle: '',
            openPanel() {
                this.open = true;
                this.$nextTick(() => {
                    const input = this.$refs.input;
                    if (input) {
                        input.scrollIntoView({ block: 'nearest', inline: 'nearest' });
                    }
                    requestAnimationFrame(() => this.updatePosition());
                });
            },
            updatePosition() {
                const input = this.$refs.input;
                const control = this.$refs.control;
                if (!input || !control) {
                    return;
                }
                const rect = input.getBoundingClientRect();
                const controlRect = control.getBoundingClientRect();
                const gap = 4;
                // Always open above the input / control.
                const spaceAbove = Math.max(96, rect.top - 12);
                const height = Math.min(240, spaceAbove);
                const width = Math.max(220, controlRect.width);
                const left = Math.min(
                    Math.max(8, controlRect.left),
                    Math.max(8, window.innerWidth - width - 8)
                );
                const top = Math.max(8, rect.top - height - gap);
                this.panelStyle = [
                    'position:fixed',
                    `top:${top}px`,
                    `left:${left}px`,
                    `width:${width}px`,
                    `max-height:${height}px`,
                    'z-index:2050',
                ].join(';');
            },
            closeIfOutside(event) {
                if (!this.open) {
                    return;
                }
                const t = event.target;
                if (this.$refs.control?.contains(t) || this.$refs.panel?.contains(t)) {
                    return;
                }
                this.open = false;
            },
            init() {
                this._reposition = () => { if (this.open) this.updatePosition(); };
                this._outside = (e) => this.closeIfOutside(e);
                window.addEventListener('resize', this._reposition);
                document.addEventListener('scroll', this._reposition, true);
                document.addEventListener('mousedown', this._outside, true);
            },
            destroy() {
                window.removeEventListener('resize', this._reposition);
                document.removeEventListener('scroll', this._reposition, true);
                document.removeEventListener('mousedown', this._outside, true);
            }
        }"
        @keydown.escape.window="open = false"
    >
        <div
            class="acc-param-tags__control"
            x-ref="control"
            @click="openPanel(); $refs.input?.focus()"
        >
            <input
                type="text"
                class="acc-param-tags__input"
                x-ref="input"
                placeholder="Add parameter…"
                x-model="query"
                @focus="openPanel()"
                @click.stop="openPanel()"
                @input="openPanel()"
            >
            <div class="acc-param-tags__chips">
                @forelse($selectedParams as $param)
                    @php
                        $paramKey = (string) ($param['analysis_element_id'] ?? $param['id'] ?? '');
                        $display = $paramCode($param);
                    @endphp
                    <span class="acc-param-tags__chip" wire:key="tag-{{ $configId }}-{{ $paramKey }}" title="{{ $param['label'] ?? $display }}">
                        {{ $display }}
                        <button
                            type="button"
                            class="acc-param-tags__remove"
                            title="Remove"
                            wire:click.stop="toggleConfigParameter('{{ $configId }}', '{{ $paramKey }}')"
                        >&times;</button>
                    </span>
                @empty
                    <span class="acc-param-tags__placeholder">No parameters selected</span>
                @endforelse
            </div>
        </div>
        <template x-teleport="body">
            <div
                class="acc-param-tags__panel acc-param-tags__panel--floating"
                x-ref="panel"
                x-show="open"
                x-cloak
                :style="panelStyle"
            >
                <div class="acc-param-tags__panel-hint">
                    {{ count($availableParams) }} available · click to add
                </div>
                <div class="acc-param-tags__options">
                    @forelse($availableParams as $param)
                        @php
                            $paramKey = (string) ($param['analysis_element_id'] ?? $param['id'] ?? '');
                            $display = $paramCode($param);
                            $searchHaystack = strtolower(trim($display.' '.($param['label'] ?? '')));
                        @endphp
                        <button
                            type="button"
                            class="acc-param-tags__option"
                            wire:key="tag-opt-{{ $configId }}-{{ $paramKey }}"
                            data-label="{{ $searchHaystack }}"
                            title="{{ $param['label'] ?? $display }}"
                            x-show="!query || ($el.dataset.label || '').includes(query.toLowerCase())"
                            @click="query = ''; $wire.toggleConfigParameter('{{ $configId }}', '{{ $paramKey }}')"
                        >
                            {{ $display }}
                        </button>
                    @empty
                        <p class="acc-wizard-hint mb-0 px-2 py-2">All parameters are selected.</p>
                    @endforelse
                </div>
            </div>
        </template>
    </div>
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
