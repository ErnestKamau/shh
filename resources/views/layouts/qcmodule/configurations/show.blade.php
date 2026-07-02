@extends('layouts.lab.layout.app', ['dataTable' => true, 'select2' => true])

@section('title2')
<title>QC Standard | Analytes</title>
@endsection

@section('content2')
<main>
    @php
        $items = [
            ['link' => route('dashboard-lab'), 'name' => 'Dashboard', 'icon' => null],
            ['link' => route('qc_configuration_index'), 'name' => 'QC Configuration', 'icon' => null],
            ['link' => route('qc_StandardShow', ['id' => $standard->id]), 'name' => 'Standard Analytes', 'icon' => null],
        ];
    @endphp

    <x-bread-crumb :items="$items" />
    @livewire('qc.standard-analytes-page', ['standardId' => (string) $standard->id], key('qc-standard-analytes-' . $standard->id))
</main>
@endsection

@section('script2')
@endsection
