@extends('layouts.lab.layout.app', ['dataTable'=>true, 'select2'=>true])

@section('title2')
<title>Lab Stock Movement - Tracker</title>
@endsection

@section('content2')
<main>
    <?php
    $items = [
        [
            'link' => route('lab-home'),
            'name' => 'Lab',
            'icon' => null
        ],
        [
            'link' => null,
            'name' => 'Stock Monitoring',
            'icon' => null
        ],
        [
            'link' => route('solution-movement-index'),
            'name' => 'Stock-Movement',
            'icon' => null
        ],
        [
            'link' => null,
            'name' => 'Tracker',
            'icon' => null
        ],
    ];
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    
    @livewire('lab.solutions-movement-tracker', ['subCategoryId' => $id])
</main>
@endsection

