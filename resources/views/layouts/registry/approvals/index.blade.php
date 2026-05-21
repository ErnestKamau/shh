@extends('layouts.registry.layout.app')

@section('title2')
    <title>Approval Queue</title>
@endsection

@section('content2')
<main>
    @php
        $items = [
            ['link' => route('registry.dashboard'), 'name' => 'Registry Dashboard', 'icon' => null],
            ['link' => route('registry.approvals.index'), 'name' => 'Approval Queue', 'icon' => null],
        ];
    @endphp
    <x-bread-crumb :items="$items"></x-bread-crumb>

    @livewire('registry.registry-approval-queue')
</main>
@endsection
