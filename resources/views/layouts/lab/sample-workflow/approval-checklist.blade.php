@extends('layouts.lab.layout.app')

@section('title2')
<title>Checklist Approval | {{ $sample->batch_code ?? $sample->id }}</title>
@endsection

@section('content2')
<main>
    @php
        $items = [
            ['link' => route('dashboard-lab'), 'name' => 'Dashboard', 'icon' => null],
            ['link' => route('sample-workflow', ['status' => $stageName]), 'name' => 'Sample Workflow', 'icon' => null],
            ['link' => '#', 'name' => 'Checklist Approval', 'icon' => null],
        ];
    @endphp

    <x-bread-crumb :items="$items"></x-bread-crumb>

    @livewire('sampleworkflow.approval-checklist', ['sampleId' => $sample->id, 'stageName' => $stageName])
</main>
@endsection