@extends('layouts.lab.layout.app')

@section('title2')
  <title>Method Registration - Method Validation</title>
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
          'link' => route('method-validation.registration'),
          'name' => 'Method Registration',
          'icon' => null
        )
      );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    
    <h2 class="p-4">
      <i class="mdi mdi-file-document-multiple"></i> Method Registration
      <span class="text-muted small">- Methods sent for validation</span>
    </h2>
    
    <div class="container-fluid">
      @livewire('lab.method-validation.method-registration')
    </div>
  </main>
@endsection

@section('script')
@livewireScripts
@endsection
