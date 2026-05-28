@extends('layouts.lab.layout.app', ['dataTable' => false, 'select2' => false])

@section('title2')
    <title>{{ $labName ?? 'Lab Profile' }} — Lab Profile</title>
@endsection

@section('content2')
    <main>
        @php
            $breadcrumbItems = [
                [
                    'link' => route('home'),
                    'name' => 'App',
                    'icon' => null,
                ],
                [
                    'link' => route('dashboard-lab'),
                    'name' => 'Dashboard',
                    'icon' => null,
                ],
                [
                    'link' => route('livewire.labs'),
                    'name' => 'Labs',
                    'icon' => null,
                ],
                [
                    'link' => null,
                    'name' => $labName ?? 'Lab Profile',
                    'icon' => null,
                ],
            ];
        @endphp

        <x-bread-crumb :items="$breadcrumbItems"></x-bread-crumb>

        @livewire('lab.lab-profile', ['labId' => $labId])
    </main>
@endsection
