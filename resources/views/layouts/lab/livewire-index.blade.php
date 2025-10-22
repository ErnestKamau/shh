@extends('layouts.lab.layout.app')

@section('title2')
    <title>Labs Management</title>
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
                    'name' => 'Labs',
                    'icon' => null
                ]
            ];
        ?>
        <x-bread-crumb :items="$items"></x-bread-crumb>
        
        <livewire:lab.lab-manager />
    </main>
@endsection


