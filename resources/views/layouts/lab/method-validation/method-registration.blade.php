@extends('layouts.lab.layout.app', ['select2' => true])

@section('title2')
  <title>Method Registration - Method Validation</title>
@endsection

@section('content2')
  <main class="lab-surface-theme ls-admin-page" data-ls-type="plex">
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
    
    <h2 class="px-0 pt-2 pb-3">
      <i class="mdi mdi-file-document-multiple"></i> Method Registration
      <span class="text-muted small">- Methods sent for validation</span>
    </h2>
    
    <div class="container-fluid px-0">
      @livewire('lab.method-validation.method-registration')
    </div>
  </main>
@endsection

@section('script')
@livewireScripts
@endsection
