@extends('layouts.documents.layout.app')

@section('title2')
    <title>Notification Frequencies - Imara LIMS</title>
@endsection

@section('content2')
<main>
    <?php
    $items = array(
        array(
            'link' => '/home',
            'name' => 'Home',
            'icon' => null
        ),
        array(
            'link' => '/documents/dashboard',
            'name' => 'Documents',
            'icon' => null
        ),
        array(
            'link' => '/documents/notification-frequencies',
            'name' => 'Notification Frequencies',
            'icon' => null
        ),
    );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="mb-0">
                <i class="mdi mdi-bell"></i> Notification Frequencies
            </h2>
            <p class="text-muted mb-0">Manage document expiry notification frequencies</p>
        </div>
    </div>

    @livewire('documents.notification-frequencies-table')
</main>
@endsection
