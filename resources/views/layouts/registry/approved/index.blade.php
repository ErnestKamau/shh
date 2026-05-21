@extends('layouts.registry.layout.app')

@section('title2')
    <title>Approved Requests</title>
@endsection

@section('content2')
<main>
    @php
        $items = [
            ['link' => route('registry.dashboard'), 'name' => 'Registry Dashboard', 'icon' => null],
            ['link' => route('registry.approved.index'), 'name' => 'Approved Requests', 'icon' => null],
        ];
    @endphp
    <x-bread-crumb :items="$items"></x-bread-crumb>

    @livewire('registry.registry-approved-request-table')
</main>
@endsection
