@extends('layouts.lab.layout.app')

@section('title2')
<title>Preview - {{ $template->name }} | Lab Management</title>
@endsection

@section('content2')
<main>
    <?php
    $items = array(
        array(
            'link' => route('lab-home'),
            'name' => 'Lab Management',
            'icon' => null
        ),
        array(
            'link' => route('certificate-templates.index'),
            'name' => 'Certificate Templates',
            'icon' => null
        ),
        array(
            'link' => route('certificate-templates.show', $template),
            'name' => $template->name,
            'icon' => null
        ),
        array(
            'link' => '#',
            'name' => 'Preview',
            'icon' => null
        )
    );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    
    <div class="bg-light p-4">
        <div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="card-title mb-0">
                                <i class="mdi mdi-eye"></i> Template Preview
                                <small class="text-muted">{{ $template->name }}</small>
                            </h5>
                        </div>
                        <div class="col-auto">
                            <div class="btn-group" role="group">
                                <a href="{{ route('certificate-templates.builder', $template) }}" class="btn btn-primary">
                                    <i class="mdi mdi-pencil"></i> Edit Template
                                </a>
                                <a href="{{ route('certificate-templates.pdf-preview', $template) }}" target="_blank" class="btn btn-info">
                                    <i class="mdi mdi-file-pdf"></i> PDF Preview
                                </a>
                                <a href="{{ route('certificate-templates.show', $template) }}" class="btn btn-secondary">
                                    <i class="mdi mdi-arrow-left"></i> Back to Template
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="preview-container">
                        <div class="preview-content">
                            @if($template->sections->count() > 0)
                                @foreach($template->rootSections as $section)
                                    @include('certificate-templates.partials.preview-section', ['section' => $section])
                                @endforeach
                            @else
                                <div class="text-center py-5 text-muted">
                                    <i class="mdi mdi-file-document-edit" style="font-size: 3rem;"></i>
                                    <h5 class="mt-3">No content yet</h5>
                                    <p>This template doesn't have any sections or elements yet.</p>
                                    <a href="{{ route('certificate-templates.builder', $template) }}" class="btn btn-primary">
                                        <i class="mdi mdi-pencil"></i> Start Building
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
</main>
@endsection

@push('styles')
<style>
    .preview-container {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 0.375rem;
    }
    
    .preview-content {
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        padding: 40px;
        min-height: 600px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    
    .preview-section {
        margin-bottom: 30px;
    }
    
    .preview-section h1,
    .preview-section h2,
    .preview-section h3,
    .preview-section h4 {
        color: #495057;
        margin-bottom: 15px;
    }
    
    .preview-section p {
        color: #6c757d;
        line-height: 1.6;
        margin-bottom: 15px;
    }
    
    .preview-element {
        margin-bottom: 15px;
    }
    
    .data-field {
        background: #e9ecef;
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        padding: 8px 12px;
        font-family: monospace;
        color: #495057;
    }
    
    .signature-field {
        border-bottom: 2px solid #000;
        height: 50px;
        margin: 20px 0;
    }
    
    .image-preview {
        max-width: 100%;
        height: auto;
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
    }
    
    .table-preview {
        width: 100%;
        border-collapse: collapse;
        margin: 15px 0;
    }
    
    .table-preview th,
    .table-preview td {
        border: 1px solid #dee2e6;
        padding: 8px 12px;
        text-align: left;
    }
    
    .table-preview th {
        background-color: #f8f9fa;
        font-weight: 600;
    }
</style>
@endpush
