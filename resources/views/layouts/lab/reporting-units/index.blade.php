@php
    $module = $module ?? request()->route('module');
    $isInventory = $module === 'inventory' || request()->is('reporting-units/inventory*');
    $layout = $isInventory ? 'layouts.inventory.layout.app' : 'layouts.lab.layout.app';
@endphp
@extends($layout, ['select2' => true])

@section('title2')
  <title>{{ $isInventory ? inventoryLabel('unit_of_measure', 'Unit of Measure') : 'Reporting Units' }}</title>
@endsection
@section('content2')
  <main>
    <?php
      $items = $isInventory ? array(
        array(
          'link' => route('inventory-home'),
          'name' => inventoryLabel('module_name', 'Inventory Management'),
          'icon' => null
        ),
        array(
          'link' => route('inventory-reporting-units', ['module' => 'inventory']),
          'name' => ' ' . inventoryLabel('unit_of_measure', 'Unit of Measure'),
          'icon' => null
        )
      ) : array(
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