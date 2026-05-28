@if($cell['filled'] ?? false)
    @php
        $userColumns = $userInputColumns ?? $variableColumns ?? [];
        $derivedColumns = $derivedColumns ?? [];
        $hasDerivedValues = collect($derivedColumns)->contains(function (array $varCol) use ($cell) {
            $val = $cell['variables'][$varCol['key']] ?? null;

            return $val !== null && $val !== '';
        });
    @endphp
    <div class="env-matrix-cell__content">
        @foreach($userColumns as $varCol)
            @php $val = $cell['variables'][$varCol['key']] ?? null; @endphp
            @if($val !== null && $val !== '')
                <div class="env-matrix-cell__row">
                    <span class="env-matrix-cell__key">{{ $varCol['label'] }}</span>
                    <span class="{{ $varCol['is_primary'] ? 'env-matrix-cell__value--primary' : 'env-matrix-cell__value' }}">{{ $val }}</span>
                </div>
            @endif
        @endforeach

        @if(!empty($cell['remark']))
            <div class="env-matrix-cell__row env-matrix-cell__row--muted">
                <span class="env-matrix-cell__key">Remark</span>
                <span class="env-matrix-cell__value">{{ $cell['remark'] }}</span>
            </div>
        @endif

        @if($hasDerivedValues)
            <details class="env-cell-more">
                <summary class="env-cell-more__trigger" title="More values">
                    <i class="mdi mdi-dots-vertical" aria-hidden="true"></i>
                </summary>
                <div class="env-cell-more__body">
                    @foreach($derivedColumns as $varCol)
                        @php $val = $cell['variables'][$varCol['key']] ?? null; @endphp
                        @if($val !== null && $val !== '')
                            <div class="env-matrix-cell__row">
                                <span class="env-matrix-cell__key">{{ $varCol['label'] }}</span>
                                <span class="{{ $varCol['is_primary'] ? 'env-matrix-cell__value--primary' : 'env-matrix-cell__value' }}">{{ $val }}</span>
                            </div>
                        @endif
                    @endforeach
                    @if(!empty($cell['user']))
                        <div class="env-matrix-cell__row env-matrix-cell__row--muted">
                            <span class="env-matrix-cell__key">Captured by</span>
                            <span class="env-matrix-cell__value">{{ $cell['user'] }}</span>
                        </div>
                    @endif
                </div>
            </details>
        @elseif(!empty($cell['user']))
            <div class="env-matrix-cell__row env-matrix-cell__row--muted">
                <span class="env-matrix-cell__key">By</span>
                <span class="env-matrix-cell__value">{{ $cell['user'] }}</span>
            </div>
        @endif
    </div>
@else
    <span class="env-matrix-cell--empty">Pending</span>
@endif
