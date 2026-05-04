@extends('layouts.lab.layout.app')

@section('title2')
    <title>Draft Invoices Management</title>
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
                    'link' => route('billing.invoices'),
                    'name' => 'Draft Invoices',
                    'icon' => null
                ]
            ];
        ?>
        <x-bread-crumb :items="$items"></x-bread-crumb>
        
        @livewire('billing.invoice-manager')
    </main>
@endsection

