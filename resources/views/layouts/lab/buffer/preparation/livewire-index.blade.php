@extends('layouts.lab.layout.app', ['dataTable'=>true])

@section('title2')
<title>Preparation Tracking</title>
@endsection

@section('content2')
<main>
    <?php
    $items = [
        ['link' => route('lab-home'), 'name' => 'Lab', 'icon' => null],
        ['link' => null, 'name' => 'Stock Monitoring', 'icon' => null],
        ['link' => route('solutions-preparation-index'), 'name' => 'Preparation Tracking', 'icon' => null],
    ];
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    @livewire('lab.solution-preparation-manager')
</main>
@endsection
