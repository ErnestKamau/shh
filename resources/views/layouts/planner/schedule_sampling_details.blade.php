@extends('layouts.planner.layout.app', ['select2' => true])

@section('title2')
<title>System Planner - Schedule Details</title>
@endsection

@section('content2')
<main class="container-fluid lab-surface-theme ls-admin-page" data-ls-type="plex">
    @include('layouts.lab.partials.lab-panel-theme-styles')
    @include('layouts.lab.partials.lab-surface-theme-styles')

    <?php
    $items = array(
        array('link' => route('system-planner.dashboard'), 'name' => 'System Planner', 'icon' => null),
        array('link' => route('system-planner.schedule-sampling'), 'name' => 'Sampling Schedule', 'icon' => null),
        array('link' => route('system-planner.schedule-sampling.show', ['schedule' => $scheduleId]), 'name' => 'Schedule Details', 'icon' => null),
    );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>

    @livewire(\App\Livewire\Planner\ScheduleSamplingDetails::class, ['scheduleId' => $scheduleId])
</main>
@endsection
