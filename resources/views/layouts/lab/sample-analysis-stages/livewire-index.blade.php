@extends('layouts.lab.layout.app', ['select2' => true])

@section('title2')
    <title>Lab Sections Management</title>
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
                    'link' => route('sample-analysis-stages'),
                    'name' => 'Lab Sections',
                    'icon' => null
                ]
            ];
        ?>
        <x-bread-crumb :items="$items"></x-bread-crumb>
        
        <livewire:lab.lab-section-manager />
    </main>
@endsection
