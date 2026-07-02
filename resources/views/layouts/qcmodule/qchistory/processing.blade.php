@extends('layouts.lab.layout.app', ['dataTable' => true, 'datePicker' => true, 'select2' => true])

@section('title2')
<title>QC Processing</title>
@endsection

@section('content2')
<main>
    @php
        $items = [
            ['link' => route('dashboard-lab'), 'name' => 'Dashboard', 'icon' => null],
            ['link' => route('showUnProcessed'), 'name' => 'QC Unprocessed', 'icon' => null],
        ];
    @endphp

    <x-bread-crumb :items="$items" />
    @livewire('qc.processing-page')
</main>
@endsection

@section('script2')
@endsection
