@extends('layouts.documents.layout.app')

@section('title2')
    <title>Document Types - Imara LIMS</title>
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
            'name' => 'Document Types',
            'icon' => null
        )
    );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    
    <h2 class="p-1">
        <i class="mdi mdi-book-open-page-variant text-deep-orange"></i> Document Types
    </h2><br>
    
    @livewire('documents.document-types-table')
</main>
@endsection
