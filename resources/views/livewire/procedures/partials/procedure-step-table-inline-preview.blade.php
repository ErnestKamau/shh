@php
    $columns = $stepItem->tableColumns ?? collect();
    $staticRows = $stepItem->staticRows ?? collect();
    $cellValues = [];
    foreach ($staticRows as $row) {
        foreach ($row->cells ?? [] as $cell) {
            $cellValues[$row->id][$cell->column_id] = $cell->default_value ?? '';
        }
    }
    $columnCount = $columns->count();
@endphp
<div class="pw-step-table-preview">
    @if($columnCount === 0)
    <p class="text-muted small mb-0 py-2 px-3">
        <i class="mdi mdi-table-off mr-1"></i>
        No columns configured.
        <button type="button" class="btn btn-link btn-sm p-0 align-baseline" wire:click.stop="openConfigureTableModal(@js($stepItem->id))">Configure table</button>
    </p>
    @else
    <div class="table-responsive">
        <table class="table table-sm mb-0 pw-modern-table pw-step-table-preview__table">
            <thead>
                <tr>
                    <th style="width: 48px;">#</th>
                    @foreach($columns as $col)
                    @php $colArr = $col->toArray(); @endphp
                    <th>
                        <span class="d-block">{{ $col->label }}</span>
                        <span class="pw-col-type-badge pw-col-type-badge--xs {{ $this->stepTableColumnIsStaticConfigurable($colArr) ? 'pw-col-type-badge--static' : 'pw-col-type-badge--capture' }}">
                            <i class="mdi {{ $this->stepTableColumnDisplayTypeIcon($colArr) }}"></i>
                            {{ $this->stepTableColumnDisplayTypeLabel($colArr) }}
                        </span>
                    </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($staticRows as $row)
                <tr>
                    <td class="text-muted">{{ $row->order }}</td>
                    @foreach($columns as $col)
                    @php
                        $colArr = $col->toArray();
                        $isStatic = $this->stepTableColumnIsStaticConfigurable($colArr);
                        $previewVal = $cellValues[$row->id][$col->id] ?? '';
                    @endphp
                    <td>
                        @if($isStatic)
                            {{ $previewVal !== '' ? $previewVal : '—' }}
                        @else
                            <span class="pw-preview-capture-placeholder">
                                <i class="mdi mdi-account-edit-outline"></i>
                                At capture
                            </span>
                        @endif
                    </td>
                    @endforeach
                </tr>
                @empty
                <tr>
                    <td colspan="{{ $columnCount + 1 }}" class="text-muted text-center py-3 small">No static rows defined.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @endif
</div>
