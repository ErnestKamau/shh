@extends('layouts.tickets.client.app', ['select2'=>true])

@section('title')
<title>Ticket | Help Desk</title>
@endsection

@section('content2')
<main>
    <?php
    $items = array(
        array(
            'link' => route('tickets.index'),
            'name' => 'My Tickets',
            'icon' => null
        ),
        array(
            'link' => null,
            'name' => 'Ticket Details',
            'icon' => null
        )
    );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    
    @livewire('ticket.show-ticket', ['id' => $ticketId])
</main>
@endsection
