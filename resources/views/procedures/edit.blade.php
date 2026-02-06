@extends('layouts.lab.layout.app', ['dataTable'=>true, 'select2'=>true])

@section('title2')
<title>Edit Procedure Steps | Lab Management</title>
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
            'link' => route('formulars.procedures.manage'),
            'name' => 'Procedure Worksheets',
            'icon' => null,
        ],
        [
            'link' => null,
            'name' => $procedureWorksheet->name,
            'icon' => null,
        ],
    ];
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>

    @livewire('procedures.procedure-worksheet-editor', ['procedureWorksheet' => $procedureWorksheet])

</main>
@endsection
