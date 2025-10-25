@extends('layouts.lab.layout.app', ['dataTable'=>false,'select2'=>false])

@section('title2')
  <title>Analysis Methods</title>
@endsection

@section('content2')
  <main>
    @livewire('lab.method-manager')
  </main>
@endsection

