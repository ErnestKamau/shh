@extends('layouts.lab.layout.app')

@section('title2')
    <title>Purchase Order Detail</title>
@endsection

@section('content2')
    <main class="container-fluid workflow-board-page lab-panel-theme workflow-theme lab-surface-theme ls-ui-kit" data-ls-type="plex">
        @include('layouts.lab.partials.lab-panel-theme-styles')
        @include('layouts.lab.partials.lab-surface-theme-styles')
        @include('layouts.lab.partials.ls-ui.ls-ui-tokens-and-styles')

        @php
            $items = [
                ['link' => route('dashboard-lab'), 'name' => 'Dashboard', 'icon' => null],
                ['link' => route('billing.customer-purchase-orders'), 'name' => 'Purchase Orders', 'icon' => null],
                ['link' => route('billing.customer-purchase-orders.show', $purchaseOrderId), 'name' => 'Detail', 'icon' => null],
            ];
        @endphp
        <x-bread-crumb :items="$items"></x-bread-crumb>

        @livewire('billing.customer-purchase-order-show', ['id' => $purchaseOrderId])
    </main>
@endsection
