@extends('layouts.planner.layout.app', ['select2' => false])

@section('title2')
<title>System Planner - KPI Reports</title>
@endsection

@section('content2')
<main class="container-fluid px-4">
    <?php
    $items = array(
        array('link' => route('full-calendar'), 'name' => 'System Planner', 'icon' => null),
        array('link' => route('system-planner.kpi-reports'), 'name' => 'KPI Reports', 'icon' => null),
    );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>

    @livewire(\App\Livewire\Planner\KpiReportsManager::class)
</main>
@endsection
