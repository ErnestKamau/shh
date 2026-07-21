@extends('layouts.lab.layout.app', ['select2' => true])

@php
    $requestForTestingLabel = __('lab.request_for_testing');
    if ($requestForTestingLabel === 'lab.request_for_testing') {
        $requestForTestingLabel = 'Request For Testing';
    }
@endphp

@section('title2')
<title>{{ $requestForTestingLabel }} | Sample WorkFlow</title>
@include('layouts.lab.partials.lab-panel-theme-styles')
@include('layouts.rft.partials.rft-theme-styles')
@endsection

@section('content2')
<main>
    @php
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
        ], key('rft-page-receive-sample-request'))
    </div>
</main>
@endsection

@section('script2')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
@stack('script2')
@endsection
