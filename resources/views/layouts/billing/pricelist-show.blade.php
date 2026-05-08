@extends('layouts.lab.layout.app')

@section('title2')
    <title>Pricelist Details</title>
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
                    'link' => route('view-pricelists'),
                    'name' => 'Pricelists',
                    'icon' => null
                ],
                [
                    'link' => route('show-pricelist', ['id' => $pricelistId]),
                    'name' => 'Details',
                    'icon' => null
                ]
            ];
        ?>
        <x-bread-crumb :items="$items"></x-bread-crumb>

        @livewire('billing.pricelist-show-manager', ['pricelistId' => $pricelistId, 'print' => $print])
    </main>
@endsection
