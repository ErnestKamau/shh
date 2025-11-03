@extends('layouts.lab.layout.app', ['dataTable'=>true, 'select2'=>true])

@section('title2')
<title>Lab Stock Management - Details</title>
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
            'link' => route('stock_management_index'),
            'name' => 'Stock-Management',
            'icon' => null
        ],
        [
            'link' => null,
            'name' => 'Details',
            'icon' => null
        ],
    ];
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    
    @livewire('lab.lab-sub-category-details', ['subCategoryId' => $id])
</main>
@endsection

