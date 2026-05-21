@extends('layouts.registry.layout.app')

@section('title2')
<title>Registry Dashboard</title>
@endsection

@section('content2')
<main class="px-2">
    @php
        $items = [
            ['link' => route('registry.dashboard'), 'name' => 'Registry Dashboard', 'icon' => null],
        ];
    @endphp
    <x-bread-crumb :items="$items"></x-bread-crumb>

    @livewire('registry.registry-dashboard')
</main>
@endsection

@section('script2')
<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('registry-charts-updated', () => {
            if (typeof window.initRegistryDashboardCharts === 'function') {
                window.initRegistryDashboardCharts();
            }
        });
    });
</script>
@endsection
