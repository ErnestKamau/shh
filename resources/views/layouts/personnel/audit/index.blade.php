@extends('layouts.personnel.layout.app')

@section('title2')
  <title>Audit Trails</title>
@endsection
@section('content2')
  <main>
    <?php
      $items = array(
        array(
          'link' => route('personnel-home'),
          'name' => 'Personnel Management',
          'icon' => null
        ),
        array(
          'link' => route('get-audit-logs'),
          'name' => 'Audit Trails',
          'icon' => null
        )
      );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    @livewire('personnel.audit-log-manager')
  </main>
@endsection
@section('script2')
@endsection