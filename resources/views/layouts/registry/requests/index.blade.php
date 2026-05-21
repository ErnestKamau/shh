@extends('layouts.registry.layout.app')

@section('title2')
    <title>Registry Requests</title>
@endsection

@section('content2')
<main>
    @php
        $items = [
            ['link' => route('registry.dashboard'), 'name' => 'Registry Dashboard', 'icon' => null],
            ['link' => route('registry.requests.index'), 'name' => 'Registry Requests', 'icon' => null],
        ];
    @endphp
    <x-bread-crumb :items="$items"></x-bread-crumb>

    @livewire('registry.registry-request-table')
</main>
@endsection
