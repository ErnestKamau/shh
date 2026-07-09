@extends('layouts.planner.layout.app', ['select2' => true])

@section('title2')
<title>System Planner - Schedule Sampling</title>
@endsection

@section('content2')
<main class="container-fluid px-4">
    <?php
    $items = array(
        array('link' => route('full-calendar'), 'name' => 'System Planner', 'icon' => null),
        array('link' => route('system-planner.schedule-sampling'), 'name' => 'Schedule Sampling', 'icon' => null),
    );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>

    @livewire(\App\Livewire\Planner\ScheduleSamplingManager::class)
</main>
@endsection
