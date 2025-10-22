@extends('layouts.lab.layout.app')

@section('title2')
    <title>Invoicable Items Management</title>
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
                    'link' => route('billing.invoicable-items'),
                    'name' => 'Invoicable Items',
                    'icon' => null
                ]
            ];
        ?>
        <x-bread-crumb :items="$items"></x-bread-crumb>
        
        @livewire('billing.invoicable-item-manager')
    </main>
@endsection

