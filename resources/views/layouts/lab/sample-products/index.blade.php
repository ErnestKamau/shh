@extends('layouts.lab.layout.app')

@section('title2')
  <title>Sample Products</title>
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
          'link' => route('sample-product-index'),
          'name' => 'Sample Conditions',
          'icon' => null
        )
      );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    <livewire:samples.sample-product-manager />
  </main>
@endsection