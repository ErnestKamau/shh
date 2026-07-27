@extends('layouts.planner.layout.app')

@section('title2')
<title>System Planner - Actual Collections</title>
@endsection

@section('content2')
<main class="container-fluid lab-surface-theme ls-admin-page" data-ls-type="plex">
    <?php
    $items = array(
        array('link' => route('system-planner.dashboard'), 'name' => 'System Planner', 'icon' => null),
        array('link' => route('system-planner.actual-collections'), 'name' => 'Actual Collections', 'icon' => null),
    );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>

    @livewire(\App\Livewire\Planner\ActualCollectionsManager::class)
</main>
@endsection
