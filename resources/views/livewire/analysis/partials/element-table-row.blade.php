@props([
    'element',
    'showCheckbox' => false,
    'showDragHandle' => false,
    'selectedElementIds' => [],
])

<tr @class(['sortable-row' => $showDragHandle]) data-element-id="{{ $element->id }}" wire:key="element-row-{{ $element->id }}">
    @if($showCheckbox)
        <td class="text-center" style="width: 40px;">
            <input
                type="checkbox"
                class="form-check-input"
                wire:click="toggleElementSelection('{{ $element->id }}')"
                @checked(in_array($element->id, $selectedElementIds, true))
            >
        </td>
    @endif
    <td class="em-actions-cell">
        <div class="d-flex align-items-center flex-nowrap em-actions-inner">
            @if($showDragHandle)
                <span class="drag-handle d-inline-flex align-items-center justify-content-center text-muted" title="Drag to reorder" style="cursor: move; min-width: 28px;">
                    <i class="mdi mdi-drag-vertical" style="font-size: 18px;"></i>
                </span>
            @endif
            <button type="button"
                wire:click="showEditElementModal('{{ $element->id }}')"
                class="btn btn-sm rm-act-btn rm-act-btn--edit"
                title="Edit parameter">
                <i class="mdi mdi-pencil-outline"></i>
            </button>
            <button type="button"
                wire:click="deleteElement('{{ $element->id }}')"
                class="btn btn-sm rm-act-btn rm-act-btn--delete"
                title="Delete"
                onclick="return confirm('Are you sure you want to delete this parameter?')">
                <i class="mdi mdi-delete"></i>
            </button>
        </div>
    </td>
    <td>
        <span class="em-pill em-pill--level">{{ $element->level ?? 'N/A' }}</span>
    </td>
    <td>{{ $element->analyte->name ?? 'N/A' }}</td>
    <td>{{ $element->mmethod->name ?? $element->ltmethod->name ?? 'N/A' }}</td>
    <td>{{ $element->equipment->name ?? 'N/A' }}</td>
    <td>{{ $element->operator->name ?? 'N/A' }}</td>
    <td>{{ $element->reporting_unit }}</td>
    <td>{{ $element->lod ?? '—' }}</td>
    <td>{{ $element->hod ?? '—' }}</td>
    <td>
        @if($element->reporting_time)
            <span class="badge bg-info text-white">{{ $element->reporting_time }}d</span>
        @else
            —
        @endif
    </td>
    <td>
        @if($element->result_is_calculated)
            <span class="em-pill em-pill--calc em-pill--on" title="Result is Calculated">
                <i class="mdi mdi-calculator"></i> Yes
            </span>
            @if($element->formular)
                <br><small class="text-muted">{{ $element->formular->name }}</small>
            @endif
        @else
            <span class="em-pill em-pill--calc em-pill--off">No</span>
        @endif
    </td>
    <td>
        @if($element->has_method_sequence)
            <span class="em-pill em-pill--sequence em-pill--on" title="Has Method Sequence">
                <i class="mdi mdi-timeline-check"></i> Yes
            </span>
            @if($element->methodSequence)
                <br><small class="text-muted">
                    {{ $element->methodSequence->name }}
                    @php
                        $activeVersion = $element->methodSequence->activeVersion->first();
                        $latestVersion = $element->methodSequence->latestVersion->first();
                    @endphp
                    @if($activeVersion)
                        <br><span class="em-pill em-pill--ver em-pill--ver-active">v{{ $activeVersion->version_number }} · Active</span>
                    @elseif($latestVersion)
                        <br><span class="em-pill em-pill--ver em-pill--ver-latest">v{{ $latestVersion->version_number }} · Latest</span>
                    @endif
                </small>
            @endif
        @else
            <span class="em-pill em-pill--sequence em-pill--off">No</span>
        @endif
    </td>
    <td>
        @if($element->active)
            <span class="em-pill em-pill--status-active">Active</span>
        @else
            <span class="em-pill em-pill--status-inactive">Inactive</span>
        @endif
    </td>
</tr>
