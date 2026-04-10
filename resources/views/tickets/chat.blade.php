@extends('layouts.tickets.client.app', ['select2'=>true])

@section('title')
<title>Chat - Ticket | Help Desk</title>
<style>
    .chat-container {
        height: calc(100vh - 200px);
        display: flex;
        flex-direction: column;
    }
    .chat-messages {
        flex: 1;
        overflow-y: auto;
        padding: 20px;
        background: #f8f9fa;
    }
    .message-bubble {
        max-width: 70%;
        margin-bottom: 15px;
        padding: 12px 16px;
        border-radius: 18px;
        word-wrap: break-word;
    }
    .message-own {
        background: #007bff;
        color: white;
        margin-left: auto;
        text-align: right;
    }
    .message-other {
        background: white;
        color: #333;
        margin-right: auto;
        box-shadow: 0 1px 2px rgba(0,0,0,0.1);
    }
    .chat-input-area {
        border-top: 1px solid #dee2e6;
        padding: 15px;
        background: white;
    }
    .ticket-info-card {
        background: white;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
</style>
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
            'name' => 'Chat',
            'icon' => null
        )
    );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    
    @livewire('ticket.ticket-chat', ['id' => $ticketId])
</main>
@endsection

