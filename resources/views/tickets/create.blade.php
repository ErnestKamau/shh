@extends('layouts.tickets.client.app', ['select2'=>true])

@section('title')
<title>Create Ticket | Help Desk</title>
@endsection

@section('content2')
<main class="h-100 d-flex flex-column" style="min-height: calc(100vh - 150px);">
    <?php
    $items = array(
        array(
            'link' => route('tickets.index'),
            'name' => 'My Tickets',
            'icon' => null
        ),
        array(
            'link' => null,
            'name' => 'Create Ticket',
            'icon' => null
        )
    );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    
    <div class="flex-grow-1">
        @livewire('ticket.create-ticket')
    </div>
</main>
@endsection
