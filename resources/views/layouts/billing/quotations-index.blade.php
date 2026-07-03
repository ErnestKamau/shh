@extends('layouts.lab.layout.app', ['select2' => true])

@section('title2')
    <title>Quotations Management</title>
@endsection

@section('content2')
    <main class="container-fluid workflow-board-page lab-panel-theme workflow-theme">
        @include('layouts.lab.partials.lab-panel-theme-styles')
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

    @include('layouts.lab.invoice.partials.add-quotation-modal', ['customers' => $customers])
@endsection

@section('script2')
    @include('layouts.lab.invoice.partials.add-quotation-modal-scripts')
@endsection
