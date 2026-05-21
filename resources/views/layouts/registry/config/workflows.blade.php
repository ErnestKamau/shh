@extends('layouts.registry.layout.app')

@section('title2')
    <title>Workflow Configuration</title>
@endsection

@section('content2')
<main>
    @php
        $items = [
            ['link' => route('registry.dashboard'), 'name' => 'Registry Dashboard', 'icon' => null],
            ['link' => route('registry.workflows.index'), 'name' => 'Workflow Configuration', 'icon' => null],
        ];
    @endphp
    <x-bread-crumb :items="$items"></x-bread-crumb>

    @livewire('registry.workflow-config-manager')
</main>
@endsection
