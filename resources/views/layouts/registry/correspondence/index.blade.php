@extends('layouts.registry.layout.app')

@section('title2')
    <title>Correspondence Register</title>
@endsection

@section('content2')
<main>
    @php
        $items = [
            ['link' => route('registry.dashboard'), 'name' => 'Registry Dashboard', 'icon' => null],
            ['link' => route('registry.correspondence.index'), 'name' => 'Correspondence Register', 'icon' => null],
        ];
    @endphp
    <x-bread-crumb :items="$items"></x-bread-crumb>

    @livewire('registry.registry-correspondence-register')
</main>
@endsection
