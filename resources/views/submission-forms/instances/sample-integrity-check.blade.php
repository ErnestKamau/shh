@extends('layouts.lab.layout.app', ['select2' => true])

@section('title2')
    <title>Sample Integrity Check | {{ $instance->getDocumentControlNumber() ?? $instance->form_number }}</title>
@endsection

@section('content2')
<main class="container-fluid lab-surface-theme ls-admin-page integrity-check-page quotation-show-page ls-quotation-shell ls-ui-kit" data-ls-type="plex">
    @include('layouts.lab.partials.lab-surface-theme-styles')
    @include('layouts.lab.partials.ls-ui.ls-ui-tokens-and-styles')
    @include('layouts.lab.partials.ls-ui.quotation.ls-quotation-overview-styles')
    @include('layouts.lab.invoice.partials.quotation-show-styles')
    @include('layouts.lab.partials.sample-integrity-check-styles')

    @php
        $formNumber = $instance->getDocumentControlNumber() ?? $instance->form_number ?? 'Pending';
        $boardStatus = 'Samples Receiving';
        $items = [
            ['link' => route('dashboard-lab'), 'name' => 'Dashboard', 'icon' => null],
            ['link' => route('sample-workflow', ['status' => $boardStatus, 'tab' => 'sample_integrity_check']), 'name' => 'Integrity Check', 'icon' => null],
            ['link' => '#', 'name' => 'Request '.$formNumber, 'icon' => null],
        ];
    @endphp

    <x-bread-crumb :items="$items"></x-bread-crumb>

    @livewire('sampleworkflow.sample-integrity-check-page', [
        'submissionFormId' => $submissionForm->id,
        'instanceId' => $instance->id,
    ], key('sample-integrity-'.$instance->id))
</main>
@endsection
