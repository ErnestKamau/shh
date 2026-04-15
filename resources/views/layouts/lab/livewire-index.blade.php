@extends('layouts.lab.layout.app')

@section('title2')
    <title>Directorate Management</title>
@endsection

@section('content2')
    <main>
        <?php
            $items = [
                [
                    'link' => route('lab-home'),
                    'name' => 'Lab Management',
                    'icon' => null
                ],
                [
                    'link' => route('labs'),
                    'name' => 'Directorates',
                    'icon' => null
                ]
            ];
        ?>
        <x-bread-crumb :items="$items"></x-bread-crumb>
        
        <livewire:directorate.directorate-manager />
    </main>
@endsection


