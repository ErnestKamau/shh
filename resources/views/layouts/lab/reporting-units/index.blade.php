@extends('layouts.lab.layout.app')

@section('title2')
  <title>Reporting Units</title>
@endsection
@section('content2')
  <main>
    <?php
      $items = array(
        array(
          'link' => route('lab-home'),
          'name' => 'Lab Management',
          'icon' => null
        ),
        array(
          'link' => route('reporting-units'),
          'name' => ' Reporting Units',
          'icon' => null
        )
      );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    <livewire:lab.reporting-unit-manager />
  </main>
@endsection