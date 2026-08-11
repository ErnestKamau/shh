@extends('layouts.lab.layout.app', ['dataTable' => true])

@section('title2')
    <title>Type of Analysis</title>
@endsection

@section('content2')
    <main>
        <x-bread-crumb :items="[
            ['link' => route('home'), 'name' => 'App', 'icon' => null],
            ['link' => route('dashboard-lab'), 'name' => 'Dashboard', 'icon' => null],
            ['link' => route('type_of_analysis_index'), 'name' => 'Type of Analysis', 'icon' => null],
        ]"></x-bread-crumb>

        <livewire:lab.type-of-analysis-manager />
    </main>
@endsection
