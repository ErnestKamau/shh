@extends('layouts.lab.layout.app')

@section('title2')
    <title>Tax Regime Management</title>
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
                    'link' => route('billing.tax-regime'),
                    'name' => 'Tax Regime',
                    'icon' => null
                ]
            ];
        ?>
        <x-bread-crumb :items="$items"></x-bread-crumb>
        
        @livewire('billing.tax-regime-manager')
    </main>
@endsection

