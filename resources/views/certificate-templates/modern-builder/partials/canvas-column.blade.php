<div class="canvas-column border rounded p-2 mr-2" data-column-id="{{ $column['id'] }}" style="width: {{ $column['width'] ?? '50%' }};">
    <div class="column-header mb-2 d-flex justify-content-between align-items-center">
        <small class="text-muted">Column: {{ $column['id'] }}</small>
        <div class="btn-group btn-group-sm">
            <button class="btn btn-sm btn-outline-primary add-cell-btn" data-column-id="{{ $column['id'] }}" data-section-id="{{ $section->id }}">
                <i class="mdi mdi-plus"></i> Add Cell
            </button>
            <button class="btn btn-sm btn-outline-danger delete-column-btn" data-column-id="{{ $column['id'] }}" data-section-id="{{ $section->id }}">
                <i class="mdi mdi-delete"></i>
            </button>
        </div>
    </div>
    
    <div class="column-content">
        @if(!empty($column['cells']))
            @foreach($column['cells'] as $cell)
                @include('certificate-templates.modern-builder.partials.canvas-cell', ['cell' => $cell, 'section' => $section])
            @endforeach
        @else
            <div class="text-center text-muted py-2">
                <small>No cells yet. Click "Add Cell" to add one.</small>
            </div>
        @endif
    </div>
</div>




