@extends('layouts.lab.layout.app', ['dataTable'=>true, 'select2'=>true])

@section('title2')
<title>Procedure Capture Worksheets | Lab Management</title>
@endsection

@section('content2')
<main>
    <?php
    $items = [
        [
            'link' => route('lab-home'),
            'name' => 'Lab',
            'icon' => null,
        ],
        [
            'link' => route('formulars.index'),
            'name' => 'Worksheet Engine',
            'icon' => null,
        ],
        [
            'link' => null,
            'name' => 'Procedure Worksheets',
            'icon' => null,
        ],
    ];
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>

    @livewire('procedures.procedure-worksheet-manager')

</main>
@endsection
