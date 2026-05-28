@extends('layouts.lab.layout.app')

@section('title2')
    <title>Draft Invoice Details</title>
@endsection

@section('content2')
    <main>
        <?php
            $items = [
                [
                    'link' => route('dashboard-lab'),
                    'name' => 'Dashboard',
                    'icon' => null,
                ],
                [
                    'link' => route('billing.invoices'),
                    'name' => 'Draft Invoices',
                    'icon' => null,
                ],
                [
                    'link' => null,
                    'name' => 'Invoice Details',
                    'icon' => null,
                ],
            ];
        ?>
        <x-bread-crumb :items="$items"></x-bread-crumb>

        @livewire('billing.invoice-show-manager', ['invoiceId' => $invoiceId])
    </main>
@endsection
