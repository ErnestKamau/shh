@extends('layouts.lab.layout.app', ['select2' => true])

@section('title2')
    <title>Equipment Requests</title>
@endsection

@section('content2')
    <main class="container-fluid workflow-board-page lab-panel-theme lab-surface-theme" data-ls-type="plex">
        @include('layouts.lab.partials.lab-panel-theme-styles')
        @include('layouts.lab.partials.lab-surface-theme-styles')
        <?php
            $items = [
                [
                    'link' => route('dashboard-lab'),
                    'name' => 'Dashboard',
                    'icon' => null,
                ],
                [
                    'link' => route('lab.equipment-requests.index'),
                    'name' => 'Equipment Requests',
                    'icon' => null,
                ],
            ];
        ?>
        <x-bread-crumb :items="$items"></x-bread-crumb>

        @livewire('lab.equipment-requests.equipment-request-manager')
    </main>
@endsection
