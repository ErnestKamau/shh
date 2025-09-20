@extends('layouts.app')

@section('title', 'Debug Enhanced Form Display - ' . $instance->submissionForm->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">
                    <i class="mdi mdi-bug"></i> Debug Enhanced Form Display
                </h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('submission-forms.index') }}">Forms</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('submission-forms.show', $instance->submissionForm) }}">{{ $instance->submissionForm->name }}</a></li>
                        <li class="breadcrumb-item active">Debug Display</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    {{-- Debug Information --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Debug Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Authentication Status</h6>
                            <ul class="list-unstyled">
                                <li><strong>User ID:</strong> {{ auth()->id() ?? 'Not authenticated' }}</li>
                                <li><strong>User Email:</strong> {{ auth()->user()->email ?? 'Not authenticated' }}</li>
                                <li><strong>Auth Check:</strong> {{ auth()->check() ? 'Yes' : 'No' }}</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>Form Instance Details</h6>
                            <ul class="list-unstyled">
                                <li><strong>ID:</strong> {{ $instance->id }}</li>
                                <li><strong>Form:</strong> {{ $instance->submissionForm->name }}</li>
                                <li><strong>Status:</strong> {{ $instance->status }}</li>
                                <li><strong>Priority:</strong> {{ $instance->priority }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Raw Data Display --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Raw Form Data (JSON)</h5>
                </div>
                <div class="card-body">
                    <pre style="max-height: 400px; overflow-y: auto; background: #f8f9fa; padding: 15px; border-radius: 4px;"><code>{{ json_encode($formData, JSON_PRETTY_PRINT) }}</code></pre>
                </div>
            </div>
        </div>
    </div>

    {{-- Elements Metadata --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Elements Metadata</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Element ID</th>
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>Custom Type</th>
                                    <th>Depends On</th>
                                    <th>Dependency Level</th>
                                    <th>Saved Values Count</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($formData['elements_metadata'] as $elementId => $elementData)
                                <tr>
                                    <td>{{ $elementId }}</td>
                                    <td>{{ $elementData['name'] }}</td>
                                    <td>{{ $elementData['element_type'] }}</td>
                                    <td>{{ $elementData['custom_element_type'] ?? 'N/A' }}</td>
                                    <td>{{ $elementData['depends_on'] ?? 'None' }}</td>
                                    <td>{{ $elementData['dependency_level'] }}</td>
                                    <td>{{ count($elementData['saved_values']) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Dependency Chain --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Dependency Chain</h5>
                </div>
                <div class="card-body">
                    @foreach($formData['dependency_chain'] as $level)
                    <div class="mb-3">
                        <h6>Level {{ $level['level'] }} {{ $level['is_independent'] ? '(Independent)' : '(Dependent)' }}</h6>
                        <div class="row">
                            @foreach($level['elements'] as $elementId)
                                @php $elementData = $formData['elements_metadata'][$elementId] @endphp
                                <div class="col-md-3 mb-2">
                                    <span class="badge badge-{{ $level['is_independent'] ? 'success' : 'info' }}">
                                        {{ $elementData['name'] }} ({{ $elementData['custom_element_type'] ?? $elementData['element_type'] }})
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Rendered Form Display --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Rendered Form Display</h5>
                </div>
                <div class="card-body">
                    {{-- Include the enhanced form display --}}
                    @include('submission-forms.partials.enhanced-form-display', ['instance' => $instance])
                </div>
            </div>
        </div>
    </div>

    {{-- JavaScript Debug --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">JavaScript Console Debug</h5>
                </div>
                <div class="card-body">
                    <div id="js-debug-output" style="background: #f8f9fa; padding: 15px; border-radius: 4px; min-height: 200px;">
                        <div class="text-center">
                            <i class="mdi mdi-loading mdi-spin"></i> Loading JavaScript debug information...
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('script2')
<script>
$(document).ready(function() {
    const formInstanceId = {{ $instance->id }};
    const formData = @json($formData);
    
    console.log('=== DEBUG ENHANCED FORM ===');
    console.log('Instance ID:', formInstanceId);
    console.log('User authenticated:', {{ auth()->check() ? 'true' : 'false' }});
    console.log('User ID:', {{ auth()->id() ?? 'null' }});
    console.log('Form Data:', formData);
    
    // Debug JavaScript output
    let debugOutput = '<h6>JavaScript Debug Information:</h6>';
    debugOutput += '<ul>';
    debugOutput += '<li><strong>Instance ID:</strong> ' + formInstanceId + '</li>';
    debugOutput += '<li><strong>User Authenticated:</strong> ' + {{ auth()->check() ? 'true' : 'false' }} + '</li>';
    debugOutput += '<li><strong>User ID:</strong> ' + {{ auth()->id() ?? 'null' }} + '</li>';
    debugOutput += '<li><strong>Form Data Sections:</strong> ' + formData.sections.length + '</li>';
    debugOutput += '<li><strong>Form Data Elements:</strong> ' + Object.keys(formData.elements_metadata).length + '</li>';
    debugOutput += '<li><strong>Dependency Levels:</strong> ' + formData.dependency_chain.length + '</li>';
    debugOutput += '</ul>';
    
    // Test dynamic options loading
    debugOutput += '<h6>Testing Dynamic Options Loading:</h6>';
    debugOutput += '<ul>';
    
    // Test a few element types
    const testElementTypes = ['client_select', 'sample_type_select', 'sample_point_select'];
    testElementTypes.forEach(function(elementType) {
        debugOutput += '<li>Testing ' + elementType + '...</li>';
        
        $.ajax({
            url: '/submission-forms/dynamic-options',
            method: 'GET',
            data: { element_type: elementType },
            success: function(response) {
                console.log('Dynamic options for ' + elementType + ':', response);
                debugOutput += '<li style="color: green;">✓ ' + elementType + ' loaded successfully (' + (response.options ? response.options.length : 0) + ' options)</li>';
                $('#js-debug-output').html(debugOutput);
            },
            error: function(xhr, status, error) {
                console.error('Error loading ' + elementType + ':', error);
                debugOutput += '<li style="color: red;">✗ ' + elementType + ' failed: ' + error + '</li>';
                $('#js-debug-output').html(debugOutput);
            }
        });
    });
    
    $('#js-debug-output').html(debugOutput);
    
    // Initialize the custom field loader
    if (window.customFieldLoader) {
        console.log('Initializing custom field loader...');
        window.customFieldLoader.initializeFormData(formData);
    } else {
        console.error('Custom field loader not found!');
        debugOutput += '<li style="color: red;">✗ Custom field loader not found!</li>';
        $('#js-debug-output').html(debugOutput);
    }
});
</script>
@endsection
