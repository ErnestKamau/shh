@extends('layouts.lab.layout.app', ['select2' => true])

@php
    $requestForTestingLabel = __('lab.request_for_testing');
    if ($requestForTestingLabel === 'lab.request_for_testing') {
        $requestForTestingLabel = 'Request For Testing';
    }
    $sampleTypeId = $sampleTypeId ?? null;
    $submissionFormId = $submissionFormId ?? null;
    $wizardKey = $submissionFormId
        ? 'rft-wizard-form-'.$submissionFormId
        : 'rft-wizard-'.$sampleTypeId;
@endphp

@section('title2')
<title>{{ $requestForTestingLabel }} | Sample WorkFlow</title>
@include('layouts.lab.partials.lab-panel-theme-styles')
@include('layouts.rft.partials.rft-theme-styles')
<style>
	.rft-theme .trf-signature-pad.acc-signature-pad {
		border: 1px solid #e2e8f0;
		border-radius: 10px;
		background: #fff;
		padding: 8px;
		min-height: 140px;
	}
	.rft-theme .trf-signature-pad.acc-signature-pad canvas.trf-signature-canvas {
		display: block !important;
		width: 100% !important;
		height: 140px !important;
		border: 1px dashed #cbd5e1;
		border-radius: 8px;
		background: #fff;
		touch-action: none;
	}
	.rft-theme .acc-signature-actions {
		margin-top: 8px;
	}
</style>
@endsection

@section('content2')
<main>
    @php
        $fillLink = $submissionFormId
            ? route('sample-workflow.request-for-testing.fill-form', ['submissionForm' => $submissionFormId])
            : route('sample-workflow.request-for-testing.fill', ['sampleType' => $sampleTypeId]);
        $items = [
            [
                'link' => route('dashboard-lab'),
                'name' => 'Dashboard',
                'icon' => null,
            ],
            [
                'link' => route('sample-workflow', ['status' => 'All Samples']),
                'name' => __('lab.sample_workflow'),
                'icon' => null,
            ],
            [
                'link' => route('sample-workflow.request-for-testing'),
                'name' => $requestForTestingLabel,
                'icon' => null,
            ],
            [
                'link' => $fillLink,
                'name' => 'Capture',
                'icon' => null,
            ],
        ];
    @endphp
    <x-bread-crumb :items="$items"></x-bread-crumb>

    <div class="container-fluid workflow-board-page rft-page-shell rft-theme lab-panel-theme px-3 px-md-4 pt-2 pb-4">
        @if ($errors->any())
            <div class="alert alert-danger mt-2">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('success') || session('error'))
            <div class="alert alert-{{ session('success') ? 'success' : 'danger' }} mt-2">
                {{ session('success') ?? session('error') }}
            </div>
        @endif

        @livewire('sampleworkflow.receive-sample-request', [
            'pageMode' => true,
            'wizardOnly' => true,
            'initialSampleTypeId' => $sampleTypeId ? (string) $sampleTypeId : null,
            'initialSubmissionFormId' => $submissionFormId ? (string) $submissionFormId : null,
        ], key($wizardKey))
    </div>
</main>
@endsection

@section('script2')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
@include('livewire.partials.walk-in-trf-page-scripts')
@stack('script2')
@endsection
