@extends('layouts.tickets.client.app', ['dataTable'=>true])

@section('title')
<title>My Tickets | Help Desk</title>
<style>
    .ticket-card {
        border-left: 4px solid #6c757d;
        transition: all 0.3s;
    }
    .ticket-card:hover {
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }
    .ticket-card.priority-high { border-left-color: #dc3545; }
    .ticket-card.priority-medium { border-left-color: #ffc107; }
    .ticket-card.priority-low { border-left-color: #28a745; }
    .status-badge {
        font-size: 0.85rem;
        padding: 0.4em 0.8em;
    }
</style>
@endsection

@section('content2')
<main>
    <?php
    $items = array(
        array(
            'link' => route('home'),
            'name' => 'Home',
            'icon' => null
        ),
        array(
            'link' => null,
            'name' => 'My Tickets',
            'icon' => null
        )
    );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="p-1">
            <i class="mdi mdi-ticket"></i> My Tickets
        </h2>
        <div>
            <a href="{{ route('tickets.create') }}" class="btn btn-primary">
                <i class="mdi mdi-plus"></i> Create New Ticket
            </a>
        </div>
    </div>

    @livewire('ticket.my-tickets-table')
</main>
@endsection

