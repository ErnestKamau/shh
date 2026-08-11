@extends('layouts.lab.layout.app', ['dataTable' => true])

@section('title2')
    <title>Parameter Groups</title>
@endsection

@section('content2')
    <main>
        <x-bread-crumb :items="[
            ['link' => route('home'), 'name' => 'App', 'icon' => null],
            ['link' => route('dashboard-lab'), 'name' => 'Dashboard', 'icon' => null],
            ['link' => route('parameter_groups_index'), 'name' => 'Parameter Groups', 'icon' => null],
        ]"></x-bread-crumb>

        <livewire:lab.parameter-group-manager />
    </main>
@endsection
