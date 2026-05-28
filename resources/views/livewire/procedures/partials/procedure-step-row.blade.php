@php
    $valueTypeLabels = [
        'text' => 'Text',
        'number' => 'Number',
        'date' => 'Date',
        'time' => 'Time',
        'datetime' => 'Date & Time',
        'method_select' => 'Method',
        'equipment_select' => 'Equipment',
        'custom_select' => 'Custom List',
        'custom_table' => 'Custom Table',
        'static_text' => 'Static Text',
    ];
    $vt = $stepItem->value_type ?: 'text';
    $isCustomTable = ($stepItem->value_type ?? '') === 'custom_table';
    $isExpanded = $isCustomTable && $this->isCustomTableStepExpanded($stepItem->id);
@endphp
<tr class="sortable-row {{ $isCustomTable ? 'pw-custom-table-step-row' : '' }} {{ $isExpanded ? 'pw-custom-table-step-row--expanded' : '' }}"
    data-step-id="{{ $stepItem->id }}"
    wire:key="step-row-{{ $stepItem->id }}"
    @if($isCustomTable) wire:click="toggleCustomTableStepExpanded(@js($stepItem->id))" @endif>
    <td class="drag-handle text-center" wire:click.stop>
        <i class="mdi mdi-drag-vertical text-muted" style="cursor: move; font-size: 18px;"></i>
        <span class="text-muted small ms-1">{{ $stepItem->order }}</span>
    </td>
    <td>
        <div class="d-flex align-items-start">
            @if($isCustomTable)
            <button type="button"
                    class="btn btn-link btn-sm p-0 mr-2 pw-step-expand-btn"
                    wire:click.stop="toggleCustomTableStepExpanded(@js($stepItem->id))"
                    title="{{ $isExpanded ? 'Collapse table' : 'Expand table' }}"
                    aria-expanded="{{ $isExpanded ? 'true' : 'false' }}">
                <i class="mdi {{ $isExpanded ? 'mdi-chevron-up' : 'mdi-chevron-down' }}"></i>
            </button>
            @endif
            <div class="flex-grow-1 min-w-0">
                <div>{{ $stepItem->step }}</div>
                <span class="tag-badge tag-badge--neutral mt-1">{{ $valueTypeLabels[$vt] ?? $vt }}</span>
            </div>
        </div>
    </td>
    <td>
        @if($stepItem->measurands && $stepItem->measurands->count() > 0)
            <div class="d-flex flex-wrap">
                @foreach($stepItem->measurands as $measurand)
                    <span class="tag-badge tag-badge--info mr-1 mb-1">{{ $measurand->name }}</span>
                @endforeach
            </div>
        @else
            <span class="text-muted">-</span>
        @endif
    </td>
    <td>
        @php
            $equipmentDisplay = $stepItem->equipment
                ? $stepItem->equipment
                    ->map(function ($equipment) {
                        $number = $equipment->equipment_number ?? null;
                        return $number ? "{$equipment->name} ({$number})" : $equipment->name;
                    })
                    ->filter()
                    ->join(', ')
                : '';
        @endphp
        {{ $equipmentDisplay !== '' ? $equipmentDisplay : '-' }}
    </td>
    <td>
        @php $analystNames = $stepItem->analysts?->pluck('name')->filter()->join(', '); @endphp
        {{ $analystNames !== '' ? $analystNames : '-' }}
    </td>
    <td>
        <div class="d-flex flex-wrap gap-1">
            <span class="tag-badge {{ $stepItem->is_active ? 'tag-badge--success' : 'tag-badge--neutral' }}">
                {{ $stepItem->is_active ? 'Active' : 'Inactive' }}
            </span>
            @if($stepItem->is_result_step)
                <span class="tag-badge tag-badge--warning">Result</span>
            @endif
            @if($stepItem->attracts_equipment_logbook)
                <span class="tag-badge tag-badge--info" title="Attracts equipment logbook">Logbook</span>
            @endif
        </div>
    </td>
    <td wire:click.stop>
        <div class="d-flex flex-nowrap pw-step-row-actions">
            @if($isCustomTable)
            <button wire:click="openConfigureTableModal(@js($stepItem->id))" class="btn btn-sm rm-act-btn rm-act-btn--view" title="Configure table">
                <i class="mdi mdi-table-cog"></i>
            </button>
            @endif
            <button wire:click="edit(@js($stepItem->id))" class="btn btn-sm rm-act-btn rm-act-btn--edit" title="Edit">
                <i class="mdi mdi-pencil"></i>
            </button>
            <button wire:click="confirmDelete(@js($stepItem->id))" class="btn btn-sm rm-act-btn rm-act-btn--delete" title="Delete">
                <i class="mdi mdi-delete"></i>
            </button>
        </div>
    </td>
</tr>
@if($isCustomTable && $isExpanded)
<tr class="pw-custom-table-step-preview-row" wire:key="step-table-preview-{{ $stepItem->id }}">
    <td colspan="7" class="p-0 border-0">
        @include('livewire.procedures.partials.procedure-step-table-inline-preview', ['stepItem' => $stepItem])
    </td>
</tr>
@endif
