@extends('layouts.lab.layout.app', ['dataTable' => true, 'datePicker' => true, 'select2' => true])

@section('title2')
<title>QC Report Chart</title>
@endsection

@section('content2')
<main>
    @php
        $items = [
            ['link' => route('dashboard-lab'), 'name' => 'Dashboard', 'icon' => null],
            ['link' => route('qc-reports'), 'name' => 'QC Reports', 'icon' => null],
            ['link' => route('qc-result-show', ['result_id' => $results->id]), 'name' => 'Chart', 'icon' => null],
        ];
    @endphp

    <x-bread-crumb :items="$items" />
    @livewire('qc.report-show-page', ['resultId' => (string) $results->id], key('qc-report-show-' . $results->id))
</main>
@endsection

@section('script2')
@endsection
