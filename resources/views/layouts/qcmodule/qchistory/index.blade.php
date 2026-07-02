@extends('layouts.lab.layout.app', ['dataTable' => true, 'datePicker' => true, 'select2' => true])

@section('title2')
<title>QC History</title>
@endsection

@section('content2')
<main>
    @php
        $items = [
            ['link' => route('dashboard-lab'), 'name' => 'Dashboard', 'icon' => null],
            ['link' => route('qcWorkflowIndex'), 'name' => 'QC History', 'icon' => null],
        ];
    @endphp

    <x-bread-crumb :items="$items" />
    @livewire('qc.history-page')
</main>
@endsection

@section('script2')
@endsection
