<div class="section-item mb-2 p-2 border rounded" data-section-id="{{ $section->id }}">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <strong>{{ $section->title }}</strong>
            @if($section->description)
                <br><small class="text-muted">{{ Str::limit($section->description, 50) }}</small>
            @endif
        </div>
        <div class="btn-group btn-group-sm">
            <button class="btn btn-outline-primary edit-section" data-id="{{ $section->id }}">
                <i class="mdi mdi-pencil"></i>
            </button>
            <button class="btn btn-outline-danger delete-section" data-id="{{ $section->id }}">
                <i class="mdi mdi-delete"></i>
            </button>
        </div>
    </div>
</div>




