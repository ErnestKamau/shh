@extends('layouts.configuration.layout.app')

@section('title2')
<title>{{ __('system.registered_companies') }}</title>
@endsection
@section('content2')
<?php
$items = array(
  array(
    'link' => route('companies'),
    'name' => __('system.companies'),
    'icon' => null
  ),
  array(
    'link' => '#',
    'name' => "system-users",
    'icon' => null
  )

);
?>
<main class="container-fluid py-2">
  <x-bread-crumb :items="$items"></x-bread-crumb>
  <div class="px-3 pt-2 pb-3">
    @livewire('system.company-manager')
  </div>
</main>
@endsection