@php
    $template = $workspace['template'];
    $matrix = $workspace['matrix'];
    $nextCapture = $matrix['next_capture'] ?? null;
    $displayColumns = $matrix['display_columns'] ?? $matrix['user_input_columns'] ?? [];
    $derivedColumns = $matrix['derived_columns'] ?? [];
    $remarkColumnIndex = collect($displayColumns)->search(function (array $column): bool {
        $needle = strtolower((string) ($column['key'] ?? '').' '.(string) ($column['label'] ?? ''));

        return str_contains($needle, 'remark');
    });
    $remarkColumnIndex = $remarkColumnIndex === false ? null : (int) $remarkColumnIndex;
    $subColCount = max(1, count($displayColumns));
    $totalColspan = 1 + (count($matrix['frequency_columns']) * $subColCount) + ($matrix['has_unslotted'] ? $subColCount : 0);
    $tid = $template->id;

    $remarkSubcellClass = function (?string $verdict): string {
        if ($verdict === 'pass') {
            return 'env-matrix-subcell--pass';
        }

        if ($verdict === 'fail') {
            return 'env-matrix-subcell--fail';
        }

        return '';
    };
@endphp

<div class="env-template-block mb-4">
    <header class="env-template-block__header">
        <div>
            <h3 class="env-template-block__title">{{ $template->name }}</h3>
            <p class="env-template-block__doc">
                Doc {{ $template->document_control_number ?: '—' }} · v{{ $template->version }}
            </p>
        </div>
        @if($nextCapture)
            <div class="env-next-capture-pill">
                <i class="mdi mdi-arrow-right-circle" aria-hidden="true"></i>
                <span>Next: <strong>{{ $nextCapture['label'] }}</strong></span>
                <span class="env-next-capture-pill__count">{{ $nextCapture['slot'] }}/{{ count($matrix['frequency_columns']) }}</span>
            </div>
        @else
            <div class="env-next-capture-pill env-next-capture-pill--complete">
                <i class="mdi mdi-check-circle" aria-hidden="true"></i>
                All readings captured today
            </div>
        @endif
    </header>

    <div class="table-responsive env-matrix-wrap">
        <table class="table table-sm table-bordered env-matrix env-matrix--nested mb-0">
            <thead class="table-light">
                <tr class="env-matrix-head-row env-matrix-head-row--freq">
                    <th rowspan="2" class="env-matrix-date-head">Date</th>
                    @foreach($matrix['frequency_columns'] as $freqCol)
                        <th colspan="{{ $subColCount }}"
                            class="env-matrix-freq-head {{ ($nextCapture && $freqCol['slot'] === $nextCapture['slot']) ? 'env-matrix-freq-head--active' : '' }}">
                            {{ $freqCol['label'] }}
                            @if($nextCapture && $freqCol['slot'] === $nextCapture['slot'])
                                <span class="env-col-capture-badge">Now</span>
                            @endif
                        </th>
                    @endforeach
                    @if($matrix['has_unslotted'])
                        <th colspan="{{ $subColCount }}" class="env-matrix-freq-head">Unslotted</th>
                    @endif
                </tr>
                <tr class="env-matrix-head-row env-matrix-head-row--fields">
                    @foreach($matrix['frequency_columns'] as $freqCol)
                        @forelse($displayColumns as $col)
                            <th class="env-matrix-field-head">{{ $col['label'] }}</th>
                        @empty
                            <th class="env-matrix-field-head">Value</th>
                        @endforelse
                    @endforeach
                    @if($matrix['has_unslotted'])
                        @forelse($displayColumns as $col)
                            <th class="env-matrix-field-head">{{ $col['label'] }}</th>
                        @empty
                            <th class="env-matrix-field-head">Value</th>
                        @endforelse
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($matrix['rows'] as $row)
                    @php
                        $hasCaptureInRow = ($row['is_today'] ?? false) && collect($matrix['frequency_columns'])->contains(
                            fn (array $freqCol) => ($row['cells'][$freqCol['slot']]['is_capture'] ?? false)
                        );
                        $hasFilledFooterInRow = collect($row['cells'] ?? [])->contains(
                            fn (array $cell) => ($cell['filled'] ?? false) && ! ($cell['is_capture'] ?? false)
                        );
                        $hasFooterInRow = $hasCaptureInRow || $hasFilledFooterInRow;
                    @endphp
                    <tr class="{{ ($row['is_today'] ?? false) ? 'env-matrix-row--today' : '' }}">
                        <td class="env-matrix-date" rowspan="{{ $hasFooterInRow ? 2 : 1 }}">{{ $row['date_label'] }}</td>

                        @foreach($matrix['frequency_columns'] as $freqCol)
                            @php
                                $cell = $row['cells'][$freqCol['slot']] ?? ['filled' => false];
                                $isEditingCell = filled($cell['log_id'] ?? null)
                                    && ($editingSavedLogId ?? null) === ($cell['log_id'] ?? null);
                                $hasDerivedValues = collect($derivedColumns)->contains(function (array $varCol) use ($cell) {
                                    $val = $cell['variables'][$varCol['key']] ?? null;
                                    return $val !== null && $val !== '';
                                });
                                $lastColIndex = count($displayColumns) - 1;
                            @endphp

                            @if($cell['is_waiting'] ?? false)
                                <td colspan="{{ $subColCount }}" class="env-matrix-cell env-matrix-cell--waiting">
                                    <span class="env-matrix-cell--empty">Up next</span>
                                </td>
                            @elseif(($cell['is_capture'] ?? false) || $isEditingCell)
                                @forelse($displayColumns as $col)
                                    <td class="env-matrix-subcell env-matrix-subcell--capture">
                                        @include('livewire.monitoring.partials.environmental.section-inline-capture-field', [
                                            'workspace' => $workspace,
                                            'template' => $template,
                                            'column' => $col,
                                            'tid' => $tid,
                                            'derivedColumns' => $derivedColumns,
                                            'savedLogId' => $isEditingCell ? $cell['log_id'] : null,
                                        ])
                                    </td>
                                @empty
                                    <td class="env-matrix-subcell env-matrix-subcell--capture">
                                        <span class="text-muted small">No capture fields configured</span>
                                    </td>
                                @endforelse
                            @else
                                @forelse($displayColumns as $index => $col)
                                    @php
                                        $columnNeedle = strtolower((string) ($col['key'] ?? '').' '.(string) ($col['label'] ?? ''));
                                        $isRemarkColumn = str_contains($columnNeedle, 'remark');
                                        $subcellClass = 'env-matrix-subcell';
                                        if ($cell['filled'] ?? false) {
                                            $subcellClass .= ' env-matrix-subcell--filled';
                                        }
                                        if ($isRemarkColumn) {
                                            $subcellClass .= ' '.$remarkSubcellClass($cell['verdict'] ?? null);
                                        }
                                    @endphp
                                    <td class="{{ trim($subcellClass) }}">
                                        @include('livewire.monitoring.partials.environmental.section-matrix-field-cell', [
                                            'cell' => $cell,
                                            'column' => $col,
                                            'derivedColumns' => $derivedColumns,
                                            'hasDerivedValues' => $hasDerivedValues,
                                            'showMore' => $remarkColumnIndex !== null ? $index === $remarkColumnIndex : $index === $lastColIndex,
                                            'showRemark' => $remarkColumnIndex !== null ? $index === $remarkColumnIndex : $index === $lastColIndex,
                                            'templateId' => $tid,
                                        ])
                                    </td>
                                @empty
                                    <td class="env-matrix-subcell">
                                        @include('livewire.monitoring.partials.environmental.section-matrix-field-cell', [
                                            'cell' => $cell,
                                            'column' => ['key' => '', 'label' => 'Value', 'is_primary' => true],
                                            'derivedColumns' => [],
                                            'hasDerivedValues' => false,
                                            'templateId' => $tid,
                                        ])
                                    </td>
                                @endforelse
                            @endif
                        @endforeach

                        @if($matrix['has_unslotted'])
                            @php
                                $cell = $row['cells']['unslotted'] ?? ['filled' => false];
                                $isEditingCell = filled($cell['log_id'] ?? null)
                                    && ($editingSavedLogId ?? null) === ($cell['log_id'] ?? null);
                                $hasDerivedValues = collect($derivedColumns)->contains(function (array $varCol) use ($cell) {
                                    $val = $cell['variables'][$varCol['key']] ?? null;
                                    return $val !== null && $val !== '';
                                });
                                $lastColIndex = count($displayColumns) - 1;
                            @endphp
                            @if($isEditingCell)
                                @forelse($displayColumns as $col)
                                    <td class="env-matrix-subcell env-matrix-subcell--capture">
                                        @include('livewire.monitoring.partials.environmental.section-inline-capture-field', [
                                            'workspace' => $workspace,
                                            'template' => $template,
                                            'column' => $col,
                                            'tid' => $tid,
                                            'derivedColumns' => $derivedColumns,
                                            'savedLogId' => $cell['log_id'],
                                        ])
                                    </td>
                                @empty
                                    <td class="env-matrix-subcell env-matrix-subcell--capture">—</td>
                                @endforelse
                            @else
                                @forelse($displayColumns as $index => $col)
                                    @php
                                        $columnNeedle = strtolower((string) ($col['key'] ?? '').' '.(string) ($col['label'] ?? ''));
                                        $isRemarkColumn = str_contains($columnNeedle, 'remark');
                                        $subcellClass = 'env-matrix-subcell';
                                        if ($cell['filled'] ?? false) {
                                            $subcellClass .= ' env-matrix-subcell--filled';
                                        }
                                        if ($isRemarkColumn) {
                                            $subcellClass .= ' '.$remarkSubcellClass($cell['verdict'] ?? null);
                                        }
                                    @endphp
                                    <td class="{{ trim($subcellClass) }}">
                                        @include('livewire.monitoring.partials.environmental.section-matrix-field-cell', [
                                            'cell' => $cell,
                                            'column' => $col,
                                            'derivedColumns' => $derivedColumns,
                                            'hasDerivedValues' => $hasDerivedValues,
                                            'showMore' => $remarkColumnIndex !== null ? $index === $remarkColumnIndex : $index === $lastColIndex,
                                            'showRemark' => $remarkColumnIndex !== null ? $index === $remarkColumnIndex : $index === $lastColIndex,
                                            'templateId' => $tid,
                                        ])
                                    </td>
                                @empty
                                    <td class="env-matrix-subcell">—</td>
                                @endforelse
                            @endif
                        @endif
                    </tr>

                    @if($hasFooterInRow)
                        <tr class="env-matrix-capture-footer-row">
                            @foreach($matrix['frequency_columns'] as $freqCol)
                                @php $cell = $row['cells'][$freqCol['slot']] ?? ['filled' => false]; @endphp
                                @if($cell['is_capture'] ?? false)
                                    <td colspan="{{ $subColCount }}" class="env-matrix-subcell env-matrix-subcell--capture-footer">
                                        @include('livewire.monitoring.partials.environmental.section-inline-capture-footer', [
                                            'workspace' => $workspace,
                                        ])
                                    </td>
                                @elseif($cell['filled'] ?? false)
                                    <td colspan="{{ $subColCount }}" class="env-matrix-subcell env-matrix-subcell--saved-footer">
                                        @include('livewire.monitoring.partials.environmental.section-saved-log-footer', [
                                            'workspace' => $workspace,
                                            'cell' => $cell,
                                        ])
                                    </td>
                                @else
                                    <td colspan="{{ $subColCount }}" class="env-matrix-subcell env-matrix-subcell--spacer"></td>
                                @endif
                            @endforeach
                            @if($matrix['has_unslotted'])
                                @php $cell = $row['cells']['unslotted'] ?? ['filled' => false]; @endphp
                                @if($cell['filled'] ?? false)
                                    <td colspan="{{ $subColCount }}" class="env-matrix-subcell env-matrix-subcell--saved-footer">
                                        @include('livewire.monitoring.partials.environmental.section-saved-log-footer', [
                                            'workspace' => $workspace,
                                            'cell' => $cell,
                                        ])
                                    </td>
                                @else
                                    <td colspan="{{ $subColCount }}" class="env-matrix-subcell env-matrix-subcell--spacer"></td>
                                @endif
                            @endif
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="{{ $totalColspan }}" class="text-center text-muted py-4">
                            No logs in the selected period.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
