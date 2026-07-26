@extends('layouts.planner.layout.app')

@section('title2')
<title>System Planner - Dashboard</title>
@endsection

@section('content2')
<main class="container-fluid px-4">
    <?php
    $items = array(
        array('link' => route('system-planner.dashboard'), 'name' => 'System Planner', 'icon' => null),
        array('link' => route('system-planner.dashboard'), 'name' => 'Dashboard', 'icon' => null),
    );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>

    @livewire(\App\Livewire\Planner\PlannerDashboard::class)
</main>
@endsection
