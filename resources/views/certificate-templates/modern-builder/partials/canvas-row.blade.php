<div class="canvas-row mb-2 border rounded p-2" data-row-id="{{ $row['id'] }}">
    <div class="row-header mb-2 d-flex justify-content-between align-items-center">
        <small class="text-muted">Row: {{ $row['id'] }}</small>
        <div class="btn-group btn-group-sm">
            <button class="btn btn-sm btn-outline-primary add-column-btn" data-row-id="{{ $row['id'] }}" data-section-id="{{ $section->id }}">
                <i class="mdi mdi-plus"></i> Add Column
            </button>
            <button class="btn btn-sm btn-outline-danger delete-row-btn" data-row-id="{{ $row['id'] }}" data-section-id="{{ $section->id }}">
                <i class="mdi mdi-delete"></i>
            </button>
        </div>
    </div>
    
    <div class="row-content d-flex">
        @if(!empty($row['columns']))
            @foreach($row['columns'] as $column)
                @include('certificate-templates.modern-builder.partials.canvas-column', ['column' => $column, 'section' => $section])
            @endforeach
        @else
            <div class="col-12 text-center text-muted py-2">
                <small>No columns yet. Click "Add Column" to add one.</small>
            </div>
        @endif
    </div>
</div>




