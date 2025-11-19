@extends('layouts.lab.layout.app')

@section('title2')
<title>Certificate Templates | Lab Management</title>
@endsection

@section('content2')
<main>
    <?php
    $items = array(
        array(
            'link' => route('lab-home'),
            'name' => 'Lab Management',
            'icon' => null
        ),
        array(
            'link' => '#',
            'name' => 'Certificate Templates',
            'icon' => null
        )
    );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    
    @livewire('certificate-templates.certificate-template-manager')
</main>
@endsection
