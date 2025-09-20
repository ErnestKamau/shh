@extends('layouts.app')

@section('title', 'Enhanced Form Display - ' . $instance->submissionForm->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">
                    <i class="mdi mdi-file-document"></i> Enhanced Form Display
                </h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('submission-forms.index') }}">Forms</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('submission-forms.show', $instance->submissionForm) }}">{{ $instance->submissionForm->name }}</a></li>
                        <li class="breadcrumb-item active">Enhanced Display</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    {{-- Include the enhanced form display --}}
                    @include('submission-forms.partials.enhanced-form-display', ['instance' => $instance])
                </div>
            </div>
        </div>
    </div>

    {{-- Debug Information --}}
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Debug Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Form Instance Details</h6>
                            <ul class="list-unstyled">
                                <li><strong>ID:</strong> {{ $instance->id }}</li>
                                <li><strong>Form:</strong> {{ $instance->submissionForm->name }}</li>
                                <li><strong>Status:</strong> {{ $instance->status }}</li>
                                <li><strong>Priority:</strong> {{ $instance->priority }}</li>
                                <li><strong>Submitted:</strong> {{ $instance->submitted_at ? $instance->submitted_at->format('M d, Y H:i') : 'N/A' }}</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>Form Data Summary</h6>
                            <div id="form-data-summary">
                                <div class="text-center">
                                    <i class="mdi mdi-loading mdi-spin"></i> Loading...
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('script2')
{{-- Load Custom Field Loader after jQuery --}}
<script src="{{ asset('js/custom-field-loader.js') }}"></script>

<script>
$(document).ready(function() {
    // Load form data summary
    loadFormDataSummary();
});

function loadFormDataSummary() {
    const instanceId = {{ $instance->id }};
    
    $.get(`/test-form-data/${instanceId}`)
        .done(function(response) {
            if (response.success) {
                const summary = response.summary;
                const html = `
                    <ul class="list-unstyled">
                        <li><strong>Sections:</strong> ${summary.total_sections}</li>
                        <li><strong>Elements:</strong> ${summary.total_elements}</li>
                        <li><strong>Dependency Levels:</strong> ${summary.dependency_levels}</li>
                        <li><strong>Rows Data:</strong> ${summary.rows_data_count}</li>
                    </ul>
                `;
                $('#form-data-summary').html(html);
            }
        })
        .fail(function() {
            $('#form-data-summary').html('<div class="text-danger">Failed to load summary</div>');
        });
}
</script>
@endsection
