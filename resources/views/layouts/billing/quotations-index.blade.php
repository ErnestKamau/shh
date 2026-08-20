@extends('layouts.lab.layout.app', ['select2' => true, 'dataTable' => false])

@section('title2')
    <title>Quotations Management</title>
@endsection

@section('content2')
    <main class="container-fluid lab-surface-theme ls-admin-page" data-ls-type="plex">
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

        @include('layouts.lab.invoice.partials.add-quotation-modal', ['customers' => $customers, 'labSections' => $labSections ?? collect()])
@endsection

@section('script2')
    @include('layouts.lab.invoice.partials.add-quotation-modal-scripts')
@endsection
