@extends('layouts.planner.layout.app', ['select2' => true])

@section('title2')
<title>System Planner - Sampling Schedule</title>
@endsection

@section('content2')
<main class="container-fluid px-4">
    <?php
    $items = array(
        array('link' => route('system-planner.dashboard'), 'name' => 'System Planner', 'icon' => null),
        array('link' => route('system-planner.schedule-sampling'), 'name' => 'Sampling Schedule', 'icon' => null),
    );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>

    @livewire(\App\Livewire\Planner\ScheduleSamplingManager::class)
</main>
@endsection
