@extends('layouts.tickets.client.app', ['dataTable'=>true])

@section('title')
<title>Archived Tickets | Help Desk</title>
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
            'name' => 'Archived Tickets',
            'icon' => null
        )
    );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="p-1">
            <i class="mdi mdi-archive"></i> Archived Tickets
        </h2>
        <a href="{{ route('tickets.index') }}" class="btn btn-secondary">
            <i class="mdi mdi-arrow-left"></i> Back to Tickets
        </a>
    </div>

    @livewire('ticket.archived-tickets-table')
</main>
@endsection

