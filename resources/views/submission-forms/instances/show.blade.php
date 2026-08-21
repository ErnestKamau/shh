@extends('layouts.lab.layout.app', ['select2' => true])

@section('title2')
    <title>Request {{ $instance->getDocumentControlNumber() ?? $instance->form_number }} | {{ $submissionForm->name }}</title>
@endsection

@section('content2')
<main class="container-fluid workflow-board-page lab-panel-theme request-view-page workflow-theme lab-surface-theme ls-ui-kit" data-ls-type="plex">
    @include('layouts.lab.partials.lab-panel-theme-styles')
    @include('layouts.lab.partials.lab-surface-theme-styles')
    @include('layouts.partials.page-header-styles')
    @include('layouts.lab.partials.request-view-page-styles')
    @include('layouts.lab.partials.ls-ui.ls-ui-tokens-and-styles')

    @php
        $formNumber = $instance->getDocumentControlNumber() ?? $instance->form_number ?? 'Pending';
        $boardStatus = 'Samples Receiving';
        $items = [
            ['link' => route('dashboard-lab'), 'name' => 'Dashboard', 'icon' => null],
            ['link' => route('sample-workflow', ['status' => $boardStatus]), 'name' => 'Workflow board', 'icon' => null],
            ['link' => '#', 'name' => 'Request '.$formNumber, 'icon' => null],
        ];
    @endphp

    <div class="request-view-shell">
    <x-bread-crumb :items="$items"></x-bread-crumb>

    @livewire('submission-forms.request-view-page', [
        'submissionFormId' => $submissionForm->id,
        'instanceId' => $instance->id,
    ], key('request-view-'.$instance->id))
    </div>
</main>
@endsection

@section('script2')
<script>
$(document).on('click', '.create-samples-btn', function (e) {
    e.preventDefault();
    const instanceId = $(this).data('instance-id');
    const $btn = $(this);
    $btn.prop('disabled', true);
    console.log('Create Job No. clicked for instance:', instanceId);
    
    $.ajax({
        url: '{{ route('submission-forms.instances.create-samples', ':instance') }}'.replace(':instance', instanceId),
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        success: function (response) {
            console.log('Create Job No. response:', response);
            if (response.success) {
                location.reload();
            } else {
                alert(response.message || 'Failed to create Job No.');
            }
        },
        error: function (xhr, status, error) {
            console.error('Create Job No. error:', error);
            console.error('Response:', xhr.responseText);
            alert('Error creating Job No.: ' + error);
        },
        complete: function () {
            $btn.prop('disabled', false);
        }
    });
});
</script>
@endsection
