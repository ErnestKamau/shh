@extends('layouts.lab.layout.app', ['dataTable'=>true, 'select2'=>true])

@section('title2')
<title>Lab Stock Movement</title>
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
            'name' => 'Stock Movement',
            'icon' => null
        ],
        [
            'link' => null,
            'name' => 'Lab Stock',
            'icon' => null
        ],
    ];
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    
    @livewire('lab.solutions-movement-manager')
</main>
@endsection

