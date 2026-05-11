@extends('layouts.lab.layout.app')

@section('title2')
    <title>Create Draft Invoice</title>
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
                ],
                [
                    'link' => '#',
                    'name' => 'Create Draft Invoice',
                    'icon' => null
                ]
            ];
        ?>
        <x-bread-crumb :items="$items"></x-bread-crumb>
        
        @livewire('billing.sales-order-wizard', ['batchCodes' => $batchCodes ?? []])
    </main>
@endsection

