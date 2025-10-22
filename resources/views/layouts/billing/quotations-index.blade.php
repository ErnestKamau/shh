@extends('layouts.lab.layout.app')

@section('title2')
    <title>Quotations Management</title>
@endsection

@section('content2')
    <main>
        <?php
            $items = [
                [
                    'link' => route('dashboard-lab'),
                    'name' => 'Dashboard',
                    'icon' => null
                ],
                [
                    'link' => route('billing.quotations'),
                    'name' => 'Quotations',
                    'icon' => null
                ]
            ];
        ?>
        <x-bread-crumb :items="$items"></x-bread-crumb>
        
        @livewire('billing.quotation-manager')
    </main>
@endsection

