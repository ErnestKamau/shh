@extends('layouts.lab.layout.app', ['dataTable'=>false,'select2'=>false])

@section('title2')
  <title>Labs Management</title>
@endsection

@section('content2')
  <main>
    @livewire('lab.lab-manager')
  </main>
@endsection
