@extends('layouts.lab.layout.app', ['dataTable' => true, 'datePicker' => true, 'select2' => true])

@section('title2')
<title>QC Reports</title>
@endsection

@section('content2')
<main>
    @php
        $items = [
            ['link' => route('dashboard-lab'), 'name' => 'Dashboard', 'icon' => null],
            ['link' => route('qc-reports'), 'name' => 'QC Reports', 'icon' => null],
        ];
    @endphp

    <x-bread-crumb :items="$items" />
    @livewire('qc.reports-page')
</main>
@endsection

@section('script2')
@endsection
