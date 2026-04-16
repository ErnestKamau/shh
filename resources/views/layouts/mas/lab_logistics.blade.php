@extends('layouts.mas.layout.app')

@section('content2')
    @livewire('mas.lab-logistics', ['stats' => $stats])
@endsection
