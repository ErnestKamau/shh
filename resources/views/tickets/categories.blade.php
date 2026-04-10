@extends('layouts.tickets.client.app')

@section('title')
<title>Ticket Categories | Help Desk</title>
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
            'name' => 'Categories',
            'icon' => null
        )
    );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    
    @livewire('ticket.ticket-categories')
</main>
@endsection
