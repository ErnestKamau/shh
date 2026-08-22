@extends('layouts.lab.layout.app')

@section('title2')
    <title>Pricelists Management</title>
@endsection

@section('content2')
    <main>
        @include('layouts.lab.partials.ls-ui.ls-ui-tokens-and-styles')
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
                ]
            ];
        ?>
        <x-bread-crumb :items="$items"></x-bread-crumb>

        @livewire('billing.pricelist-manager')
    </main>
@endsection
