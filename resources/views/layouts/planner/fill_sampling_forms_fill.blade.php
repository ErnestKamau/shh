@extends('layouts.planner.layout.app', ['select2' => true])

@php
    $fillSamplingFormsLabel = __('planner.fill_sampling_forms');
    if ($fillSamplingFormsLabel === 'planner.fill_sampling_forms') {
        $fillSamplingFormsLabel = 'Fill Sampling Forms';
    }
@endphp

@section('title2')
<title>{{ $fillSamplingFormsLabel }} | System Planner</title>
@include('layouts.rft.partials.rft-theme-styles')
<style>
	.planner-fsf-page {
		--fsf-accent: var(--color-primary, #8a1a1f);
		--fsf-accent-soft: var(--color-primary-soft, #f3e8e9);
		--fsf-accent-border: var(--color-primary-highlight, #e2b8bb);
		--workflow-accent: var(--fsf-accent);
		--workflow-accent-soft: var(--fsf-accent-soft);
		--workflow-accent-border: var(--fsf-accent-border);
		--rft-support: var(--fsf-accent);
		--rft-support-deep: var(--fsf-accent);
		--rft-support-soft: var(--fsf-accent-soft);
		max-width: 1180px;
		margin-left: auto;
		margin-right: auto;
	}
	.planner-fsf-page .workflow-board-panel {
		border: 1px solid #e8ecf1;
		border-radius: 14px;
		overflow: hidden;
		box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
	}
	.planner-fsf-page .workflow-board-panel-header h5 .mdi {
		color: var(--fsf-accent);
	}
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
<main class="lab-surface-theme ls-admin-page" data-ls-type="plex">
    @php
        $items = [
            [
                'link' => route('system-planner.dashboard'),
                'name' => __('planner.module_name') === 'planner.module_name' ? 'System Planner' : __('planner.module_name'),
                'icon' => null,
            ],
            [
                'link' => route('system-planner.fill-sampling-forms'),
                'name' => $fillSamplingFormsLabel,
                'icon' => null,
            ],
            [
                'link' => route('system-planner.fill-sampling-forms.fill', ['sampleType' => $sampleTypeId]),
                'name' => __('planner.fill_form') === 'planner.fill_form' ? 'Fill form' : __('planner.fill_form'),
                'icon' => null,
            ],
        ];
    @endphp
    <x-bread-crumb :items="$items"></x-bread-crumb>

    <div class="container-fluid workflow-board-page rft-page-shell rft-theme planner-fsf-page px-3 px-md-4 pt-2 pb-4">
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
            'plannerMode' => true,
            'initialSampleTypeId' => (string) $sampleTypeId,
            'initialScheduleId' => isset($scheduleId) ? (string) $scheduleId : null,
        ], key('planner-fill-wizard-'.$sampleTypeId.'-'.($scheduleId ?? 'none')))
    </div>
</main>
@endsection

@section('script2')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
@include('livewire.partials.walk-in-trf-page-scripts')
@stack('script2')
@endsection
