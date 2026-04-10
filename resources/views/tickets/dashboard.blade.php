@extends('layouts.tickets.client.app', ['dataTable'=>true])

@section('title')
<title>Ticket Dashboard | Help Desk</title>
@endsection

@section('content2')
<main>
    <?php
    $items = array(
        array(
            'link' => route('tickets.index'),
            'name' => 'Tickets',
            'icon' => null
        ),
        array(
            'link' => null,
            'name' => 'Dashboard',
            'icon' => null
        )
    );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    
    <!-- Dashboard Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-0">
                <i class="mdi mdi-ticket"></i> Help Desk Dashboard
            </h2>
        </div>
    </div>

    @livewire('ticket.ticket-dashboard')
</main>
@endsection

@section('script2')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
@endsection

