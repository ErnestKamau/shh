@extends('layouts.audit.layout.app')

@section('title2')
<title>Create Audit Report</title>
@endsection

@section('content2')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h4><i class="mdi mdi-file-document-plus"></i> Create New Audit Summary Report</h4>
                <a href="{{ route('audit.index') }}" class="btn btn-secondary">
                    <i class="mdi mdi-arrow-left"></i> Back to List
                </a>
            </div>
        </div>
        <div class="card-body">
            @livewire('audit.audit-summary-report-form')
        </div>
    </div>
</div>
@endsection

@section('script2')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Select2 for all select fields
    function initializeSelect2() {
        $('.select2-basic').each(function() {
            var $select = $(this);
            
            // Destroy existing Select2 instance if it exists
            if ($select.hasClass('select2-hidden-accessible')) {
                $select.select2('destroy');
            }
            
            // Initialize Select2
            $select.select2({
                placeholder: 'Select an option',
                allowClear: true,
                width: '100%',
                dropdownParent: $('body')
            });
            
            // Sync with Livewire when selection changes
            $select.on('select2:select select2:clear', function() {
                $select[0].dispatchEvent(new Event('change', { bubbles: true }));
            });
        });
    }
    
    // Initialize on page load
    initializeSelect2();
    
    // Re-initialize when Livewire loads
    document.addEventListener('livewire:init', function() {
        initializeSelect2();
    });
    
    // Re-initialize after Livewire updates
    document.addEventListener('livewire:update', function() {
        setTimeout(initializeSelect2, 100);
    });
});
</script>
@endsection

