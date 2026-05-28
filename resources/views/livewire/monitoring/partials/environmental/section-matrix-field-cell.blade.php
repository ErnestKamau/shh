@php
    $val = $cell['variables'][$column['key']] ?? null;
    $columnNeedle = strtolower((string) ($column['key'] ?? '').' '.(string) ($column['label'] ?? ''));
    $isRemarkColumn = str_contains($columnNeedle, 'remark');
    $statusLabel = filled($cell['status'] ?? null) ? ucfirst(strtolower((string) $cell['status'])) : null;
    if ($statusLabel === null) {
        foreach (($cell['variables'] ?? []) as $key => $candidate) {
            $keyNeedle = strtolower((string) $key);
            $candidateString = is_scalar($candidate) ? trim((string) $candidate) : '';
            if ($candidateString === '') {
                continue;
            }
            if (
                (str_contains($keyNeedle, 'status') || str_contains($keyNeedle, 'result'))
                && in_array(strtolower($candidateString), ['pass', 'passed', 'fail', 'failed'], true)
            ) {
                $statusLabel = ucfirst(strtolower($candidateString));
                break;
            }
        }
    }
    $displayValue = $val;
    $displayString = is_scalar($displayValue) ? trim((string) $displayValue) : '';
    $isDashPlaceholder = in_array($displayString, ['-', '—'], true);

    if (($displayValue === null || $displayValue === '' || $isDashPlaceholder) && $isRemarkColumn && $statusLabel !== null) {
        $displayValue = $statusLabel;
    }

    $detailRows = collect($derivedColumns ?? [])
        ->map(function (array $varCol) use ($cell): ?array {
            $derivedVal = $cell['variables'][$varCol['key']] ?? null;
            if ($derivedVal === null || $derivedVal === '') {
                return null;
            }

            return [
                'label' => $varCol['label'],
                'value' => $derivedVal,
                'muted' => false,
            ];
        })
        ->filter()
        ->values();

    if (! empty($cell['remark'])) {
        $detailRows->push(['label' => 'Remark', 'value' => $cell['remark'], 'muted' => true]);
    }
    if ($statusLabel) {
        $detailRows->push(['label' => 'Status', 'value' => $statusLabel, 'muted' => true]);
    }
    if (! empty($cell['user'])) {
        $detailRows->push(['label' => 'By', 'value' => $cell['user'], 'muted' => true]);
    }

    $canShowMore = ($showMore ?? false) && $detailRows->isNotEmpty() && ! $isRemarkColumn;
    $logId = $cell['log_id'] ?? null;
    $showFormulaTimeline = filled($logId) && ($showRemark ?? false) && $isRemarkColumn;
@endphp

@if($cell['filled'] ?? false)
    <div class="env-matrix-field-cell {{ $isRemarkColumn ? 'env-matrix-field-cell--remark' : '' }}">
        <span class="env-matrix-field-value {{ ($column['is_primary'] ?? false) ? 'is-primary' : '' }}">
            {{ $displayValue !== null && $displayValue !== '' ? $displayValue : '—' }}
        </span>
        @if($showFormulaTimeline)
            <button type="button"
                    class="env-more-btn env-more-btn--remark"
                    wire:click="openSavedLogFormulaTimeline('{{ $logId }}')"
                    title="View steps breakdown"
                    aria-label="View steps breakdown">
                <i class="mdi mdi-dots-vertical" aria-hidden="true"></i>
            </button>
        @elseif($canShowMore)
            <details class="env-cell-more env-cell-more--inline">
                <summary class="env-cell-more__trigger" title="More values">
                    <i class="mdi mdi-dots-vertical" aria-hidden="true"></i>
                </summary>
                <div class="env-cell-more__body">
                    @foreach($detailRows as $detailRow)
                        <div class="env-matrix-cell__row {{ $detailRow['muted'] ? 'env-matrix-cell__row--muted' : '' }}">
                            <span class="env-matrix-cell__key">{{ $detailRow['label'] }}</span>
                            <span class="env-matrix-cell__value">{{ $detailRow['value'] }}</span>
                        </div>
                    @endforeach
                </div>
            </details>
        @elseif(!empty($cell['remark']) && ($showRemark ?? false))
            <span class="env-matrix-field-remark" title="{{ $cell['remark'] }}">
                <i class="mdi mdi-comment-text-outline" aria-hidden="true"></i>
            </span>
        @endif
    </div>
@else
    <span class="env-matrix-field-value env-matrix-field-value--empty">—</span>
@endif
