@extends('layouts.lab.layout.app', ['dataTable'=>false,'select2'=>false])

@section('title2')
  <title>{{ $methodId ? 'Method Details' : 'Analysis Method' }} | Analysis Methods</title>
@endsection

@section('content2')
  <main>
    @livewire('lab.method-detail', ['methodId' => $methodId])
  </main>
@endsection

