@extends('layouts.registry.layout.app')

@section('title2')
    <title>New Registry Request</title>
@endsection

@section('content2')
<main>
    @php
        $items = [
            ['link' => route('registry.dashboard'), 'name' => 'Registry Dashboard', 'icon' => null],
            ['link' => route('registry.requests.index'), 'name' => 'Registry Requests', 'icon' => null],
            ['link' => route('registry.requests.create'), 'name' => 'New Request', 'icon' => null],
        ];
    @endphp
    <x-bread-crumb :items="$items"></x-bread-crumb>

    @livewire('registry.registry-request-form')
</main>
@endsection
