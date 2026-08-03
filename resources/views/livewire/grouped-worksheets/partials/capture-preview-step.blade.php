@php
    $field = $step['capture_field'] ?? ['type' => 'text', 'placeholder' => 'Enter value…'];
    $fieldType = $field['type'] ?? 'text';
@endphp
<div class="gw-inner-timeline-item">
    <div class="gw-inner-timeline-marker">{{ $step['order'] }}</div>
    <div class="gw-inner-timeline-card">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-2">
            <div>
                <div class="fw-semibold">{{ $step['title'] }}</div>
                @if(!empty($step['subtitle']))
                    <div class="small text-muted">{{ $step['subtitle'] }}</div>
                @endif
            </div>
            @if(!empty($step['badges']))
                <div class="d-flex flex-wrap gap-1">
                    @foreach($step['badges'] as $badge)
                        <span class="gw-capture-badge">{{ $badge }}</span>
                    @endforeach
                </div>
            @endif
        </div>

        @switch($fieldType)

            @case('static')
                @php $content = trim((string) ($field['content'] ?? '')); @endphp
                <div class="gw-field gw-field--static">
                    <span class="gw-field__icon"><i class="mdi mdi-text-box-outline"></i></span>
                    @if($content !== '')
                        <span class="gw-field__static-text">{{ $content }}</span>
                    @else
                        <span class="gw-field__placeholder">Static text (no content set)</span>
                    @endif
                </div>
                @break

            @case('readonly')
                <div class="gw-field gw-field--readonly">
                    <i class="mdi mdi-function-variant gw-field__icon"></i>
                    <span class="gw-field__placeholder">{{ $field['placeholder'] ?? 'Calculated automatically…' }}</span>
                </div>
                @break

            @case('result')
                <div class="gw-field gw-field--result">
                    <i class="mdi mdi-check-circle-outline gw-field__icon"></i>
                    <span class="gw-field__placeholder">{{ $field['placeholder'] ?? 'Posted result…' }}</span>
                </div>
                @break

            @case('parameter_result')
                @php
                    $prAnalyteIds = $field['analyte_ids'] ?? [];
                    $prCount = is_array($prAnalyteIds) ? count($prAnalyteIds) : 0;
                @endphp
                <div class="gw-field gw-field--result">
                    <i class="mdi mdi-flask-outline gw-field__icon"></i>
                    <span class="gw-field__placeholder">
                        {{ $field['placeholder'] ?? 'Enter result…' }}
                        @if($prCount > 0)
                            · {{ $prCount }} analyte{{ $prCount === 1 ? '' : 's' }}
                        @endif
                    </span>
                </div>
                @break

            @case('checkbox')
                @php
                    $options  = $field['options'] ?? [];
                    $mode     = $field['mode'] ?? 'static';
                    $preset   = $field['preset'] ?? null;
                @endphp
                <div class="gw-field gw-field--checkbox-list">
                    @if($options !== [])
                        @foreach($options as $opt)
                            <label class="gw-checkbox-chip">
                                <input type="checkbox" disabled class="gw-checkbox-chip__input">
                                <span>{{ $opt }}</span>
                            </label>
                        @endforeach
                    @elseif($mode === 'preset' && $preset)
                        <span class="gw-field__placeholder"><i class="mdi mdi-database-outline"></i> Options from {{ ucfirst($preset) }}</span>
                    @elseif($mode === 'dataset')
                        <span class="gw-field__placeholder"><i class="mdi mdi-database-outline"></i> Options from dataset</span>
                    @else
                        <span class="gw-field__placeholder">No options configured</span>
                    @endif
                </div>
                @break

            @case('select')
                @php $selectOptions = $field['options'] ?? []; @endphp
                <div class="gw-field gw-field--select">
                    @if($selectOptions !== [])
                        <span class="text-muted small">{{ $field['placeholder'] ?? 'Select…' }}</span>
                        <div class="gw-select-options-preview mt-1">
                            @foreach(array_slice($selectOptions, 0, 5) as $opt)
                                <span class="gw-select-option-chip">{{ is_array($opt) ? ($opt['label'] ?? $opt['value'] ?? $opt) : $opt }}</span>
                            @endforeach
                            @if(count($selectOptions) > 5)
                                <span class="gw-select-option-chip gw-select-option-chip--more">+{{ count($selectOptions) - 5 }} more</span>
                            @endif
                        </div>
                    @else
                        <span class="text-muted">{{ $field['placeholder'] ?? 'Select…' }}</span>
                        <i class="mdi mdi-chevron-down"></i>
                    @endif
                </div>
                @break

            @case('table')
                @php $columns = $field['columns'] ?? []; @endphp
                <div class="gw-field gw-field--table">
                    @if($columns !== [])
                        <table class="gw-table-preview">
                            <thead>
                                <tr>
                                    <th class="gw-table-preview__index">#</th>
                                    @foreach($columns as $col)
                                        <th>{{ $col }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="gw-table-preview__index text-muted">1</td>
                                    @foreach($columns as $col)
                                        <td><div class="gw-table-preview__cell-placeholder"></div></td>
                                    @endforeach
                                </tr>
                                <tr>
                                    <td class="gw-table-preview__index text-muted">2</td>
                                    @foreach($columns as $col)
                                        <td><div class="gw-table-preview__cell-placeholder"></div></td>
                                    @endforeach
                                </tr>
                            </tbody>
                        </table>
                    @else
                        <div class="gw-field__placeholder">
                            <i class="mdi mdi-table-large"></i> Table — no columns configured yet
                        </div>
                    @endif
                </div>
                @break

            @case('plate')
                @php
                    $hasQc     = (bool) ($field['has_qc']    ?? false);
                    $standards = $field['standards'] ?? [];
                    $controls  = $field['controls']  ?? [];
                    $buffers   = $field['buffers']   ?? [];
                @endphp
                <div class="gw-field gw-field--plate">
                    <div class="gw-plate-header">
                        <i class="mdi mdi-grid-large"></i>
                        <span>96-well PCR plate map</span>
                    </div>
                    @if($hasQc && ($standards !== [] || $controls !== [] || $buffers !== []))
                        <div class="gw-plate-qc-chips">
                            @foreach($standards as $s)
                                <span class="gw-plate-chip gw-plate-chip--std">{{ $s }}</span>
                            @endforeach
                            @foreach($controls as $c)
                                <span class="gw-plate-chip gw-plate-chip--control">{{ $c }}</span>
                            @endforeach
                            @foreach($buffers as $b)
                                <span class="gw-plate-chip gw-plate-chip--buffer">{{ $b }}</span>
                            @endforeach
                        </div>
                    @elseif($hasQc)
                        <span class="gw-field__placeholder small">QC wells enabled — no labels defined yet</span>
                    @else
                        <span class="gw-field__placeholder small">Sample wells only</span>
                    @endif
                    <div class="gw-plate-grid-mini">
                        @foreach(['A','B','C','D'] as $r)
                            <div class="gw-plate-grid-mini__row">
                                @for($c = 1; $c <= 12; $c++)
                                    <div class="gw-plate-grid-mini__cell"></div>
                                @endfor
                            </div>
                        @endforeach
                        <div class="gw-plate-grid-mini__more">H12 …</div>
                    </div>
                </div>
                @break

            @case('capture')
                <div class="gw-field gw-field--capture">
                    <i class="mdi mdi-clipboard-edit-outline gw-field__icon"></i>
                    <span class="gw-field__placeholder">{{ $field['placeholder'] ?? 'Stage capture panel' }}</span>
                </div>
                @break

            @case('number')
            @case('date')
            @case('time')
            @case('datetime')
            @case('text')
            @default
                <input
                    type="{{ in_array($fieldType, ['number','date','time','datetime','text']) ? $fieldType : 'text' }}"
                    class="gw-field gw-field--input"
                    placeholder="{{ $field['placeholder'] ?? '' }}"
                    disabled
                    readonly
                >
        @endswitch
    </div>
</div>
