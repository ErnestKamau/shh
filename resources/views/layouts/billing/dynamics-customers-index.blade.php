@extends('layouts.lab.layout.app')

@section('title2')
    <title>Dynamics Customers Management</title>
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
                    'link' => route('billing.dynamics-customers'),
                    'name' => 'Dynamics Customers',
                    'icon' => null
                ]
            ];
        ?>
        <x-bread-crumb :items="$items"></x-bread-crumb>
        
        @livewire('billing.dynamics-customer-manager')
    </main>
@endsection

