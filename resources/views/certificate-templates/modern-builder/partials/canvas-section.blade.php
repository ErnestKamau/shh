@php
    $modernSection = new \App\Models\ModernCertificateTemplateSection($section);
    $layout = $modernSection->getLayoutStructure();
@endphp

<div class="canvas-section mb-4 border rounded p-3 bg-white" data-section-id="{{ $section->id }}">
    <div class="section-header mb-3">
        <h5>{{ $section->title }}</h5>
        <div class="btn-group btn-group-sm">
            <button class="btn btn-sm btn-outline-primary add-row-btn" data-section-id="{{ $section->id }}">
                <i class="mdi mdi-plus"></i> Add Row
            </button>
            <button class="btn btn-sm btn-outline-secondary edit-section-btn" data-section-id="{{ $section->id }}">
                <i class="mdi mdi-pencil"></i> Edit
            </button>
        </div>
    </div>
    
    <div class="section-content">
        @if(!empty($layout['rows']))
            @foreach($layout['rows'] as $row)
                @include('certificate-templates.modern-builder.partials.canvas-row', ['row' => $row, 'section' => $section])
            @endforeach
        @else
            <div class="text-center text-muted py-3">
                <p>No rows yet. Click "Add Row" to start building.</p>
            </div>
        @endif
    </div>
</div>




