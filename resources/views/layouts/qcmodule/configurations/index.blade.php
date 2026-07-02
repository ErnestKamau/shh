@extends('layouts.lab.layout.app', ['dataTable' => true, 'select2' => true])

@section('title2')
<title>Quality Control | Configuration</title>
@endsection

@section('content2')
<main>
    @php
        $items = [
            ['link' => route('dashboard-lab'), 'name' => 'Dashboard', 'icon' => null],
            ['link' => route('qc_configuration_index'), 'name' => 'QC Configuration', 'icon' => null],
        ];
    @endphp

    <x-bread-crumb :items="$items" />
    @livewire('qc.configurations-page')
</main>
@endsection

@section('script2')
@endsection
