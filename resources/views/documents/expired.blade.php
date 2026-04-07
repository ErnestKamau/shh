@extends('layouts.documents.layout.app')

@section('title2')
    <title>Expired Documents - Imara LIMS</title>
@endsection

@section('content2')
<main>
    <?php
    $items = array(
        array(
            'link' => '/home',
            'name' => 'Home',
            'icon' => null
        ),
        array(
            'link' => '/documents/dashboard',
            'name' => 'Documents',
            'icon' => null
        ),
        array(
            'link' => null,
            'name' => 'Expired Documents',
            'icon' => null
        )
    );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    
    <h2 class="p-1">
        <i class="mdi mdi-calendar-alert text-danger"></i> Expired Documents
    </h2><br>
    
    @livewire('documents.expired-documents-table')
</main>
@endsection
