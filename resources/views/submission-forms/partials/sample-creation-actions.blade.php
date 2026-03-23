@php
    // Variables $canCreateSamples and $sampleStatus are passed from parent view 'submission-forms.instances.show'
@endphp

@if($canCreateSamples)
    <div class="card mt-3">
        <div class="card-header bg-primary text-white">
            <h6 class="mb-0">
                <i class="mdi mdi-flask"></i> Sample Creation
            </h6>
        </div>
        <div class="card-body">
            @if($sampleStatus['status'] === 'created')
                <div class="alert alert-success">
                    <i class="mdi mdi-check-circle"></i>
                    <strong>Samples Created Successfully!</strong>
                    <p class="mb-0 mt-2">
                        This form has been converted to sample records. 
                        @if(isset($sampleStatus['sample_headers']) && count($sampleStatus['sample_headers']) > 0)
                            <br>
                            <strong>Batch Code:</strong> {{ $sampleStatus['sample_headers']->first()->batch_code }}
                            <br>
                            <strong>Sample Count:</strong> {{ $sampleStatus['sample_headers']->first()->samples->count() }}
                        @endif
                    </p>
                </div>
                
                @if(isset($sampleStatus['sample_headers']) && count($sampleStatus['sample_headers']) > 0)
                    <div class="mt-3">
                        <a href="{{ route('view-batch-details', ['batch' => $sampleStatus['sample_headers']->first()->id, 'client' => 0, 'portal' => 0, 'status' => $sampleStatus['sample_headers']->first()->status]) }}" 
                           class="btn btn-success">
                            <i class="mdi mdi-eye"></i> View Sample Batch
                        </a>
                    </div>
                @endif
                
            @elseif($sampleStatus['status'] === 'ready')
                <div class="alert alert-info">
                    <i class="mdi mdi-information"></i>
                    <strong>Ready to Create Samples</strong>
                    <p class="mb-0 mt-2">
                        This form has mapped elements and can be converted to sample records.
                        Click the button below to create sample headers and details.
                    </p>
                </div>
                
                <div class="mt-3">
                    <button type="button" 
                            class="btn btn-primary create-samples-btn" 
                            data-instance-id="{{ $instance->id }}">
                        <i class="mdi mdi-flask"></i> Create Batch
                    </button>
                    
                    <button type="button" 
                            class="btn btn-outline-secondary ml-2" 
                            id="check-sample-status-btn"
                            data-instance-id="{{ $instance->id }}">
                        <i class="mdi mdi-refresh"></i> Check Status
                    </button>
                </div>
                
            @else
                <div class="alert alert-warning">
                    <i class="mdi mdi-alert"></i>
                    <strong>Cannot Create Samples</strong>
                    <p class="mb-0 mt-2">{{ $sampleStatus['message'] }}</p>
                </div>
            @endif
        </div>
    </div>

@endif

@if($instance->batches()->exists() && ($linkedBatchesOutOfSyncWithForm ?? false))
    <div class="card mt-3 border-warning">
        <div class="card-header bg-warning text-dark">
            <h6 class="mb-0">
                <i class="mdi mdi-alert-decagram"></i> Linked batches — out of sync
            </h6>
        </div>
        <div class="card-body">
            <p class="text-muted small mb-2">
                Saved form data no longer matches your linked sample batch headers (or unprocessed staging). Apply to push customer, company unit, and sub-unit fields from this form.
            </p>
            <form method="POST" action="{{ route('submission-forms.instances.apply-to-batches', $instance->id) }}" onsubmit="return confirm('Update all linked batches from the current saved form data? Customer, company unit, and unprocessed staging will be refreshed.');">
                @csrf
                <button type="submit" class="btn btn-warning btn-sm">
                    <i class="mdi mdi-sync"></i> Apply form to linked batches
                </button>
            </form>
        </div>
    </div>
@endif

@if($canCreateSamples)
    @section('script2')
    <script>
    $(document).ready(function() {
        // Create samples button
        // Create samples button
        $('.create-samples-btn').on('click', function(e) {
            e.preventDefault();
            const instanceId = $(this).data('instance-id');
            const $btn = $(this);
            
            // Show loading state
            $btn.prop('disabled', true).html('<i class="mdi mdi-loading mdi-spin"></i> Creating...');
            
            $.ajax({
                url: '{{ route("submission-forms.instances.create-samples", ":instance") }}'.replace(':instance', instanceId),
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        //alert('Success! ' + response.message);
                        location.reload();
                    } else {
                        //alert('Error! ' + response.message);
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    //alert('Error! ' + (response?.message || 'An error occurred while creating samples.'));
                },
                complete: function() {
                    $btn.prop('disabled', false).html('<i class="mdi mdi-flask"></i> Create Samples');
                }
            });
        });
        
        // Check status button
        $('#check-sample-status-btn').on('click', function() {
            const instanceId = $(this).data('instance-id');
            const $btn = $(this);
            
            // Show loading state
            $btn.prop('disabled', true).html('<i class="mdi mdi-loading mdi-spin"></i> Checking...');
            
            $.ajax({
                url: '{{ route("submission-forms.instances.sample-status", ":instance") }}'.replace(':instance', instanceId),
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        //alert('Sample Status: ' + response.data.message);
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    //alert('Error! ' + (response?.message || 'An error occurred while checking status.'));
                },
                complete: function() {
                    $btn.prop('disabled', false).html('<i class="mdi mdi-refresh"></i> Check Status');
                }
            });
        });
    });
    </script>
    @endsection
@endif
