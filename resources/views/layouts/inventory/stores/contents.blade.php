@extends('layouts.inventory.layout.app', ['dataTable'=>true, 'select2'=>true])

@section('title2')
  <title>Inventory Slot Contents</title>
@endsection
@section('content2')
  <main>
    <?php
      $items = array(
        array(
          'link' => route('inventory-home'),
          'name' => 'Inventory Management',
          'icon' => null
        ),
        array(
          'link' => route('inventory-stores'),
          'name' => 'Store',
          'icon' => null
        ),
        array(
          'link' => route('inventory-store-slots', ['store'=>$store->id]),
          'name' => $store->name,
          'icon' => null
        ),
				array(
          'link' => '#',
          'name' => $slot->name,
          'icon' => null
        )
      );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    <h2 class="p-4">
			<i class="mdi mdi-clipboard-list"></i>Slot Contents
      {{-- <button class="btn btn-primary btn-sm float-right" data-toggle="modal" data-target="#add-inventory-slot-content"><i class="mdi mdi-plus"></i> Add</button> --}}
    </h2>
    <br>
    <div class="table-responsive bg-light p-4">
      <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
        <thead class="bg-light p-2">
          <tr>
            <th>No</th>
            <th>Category</th>
            <th>Item Code</th>
            <th>Item</th>
            <th>Quantity</th>
            <th>UoM</th>
						<th>Material State</th>
						{{-- <th>Date</th> --}}
          </tr>
        </thead>
        <tbody>
					@foreach($contents as $content)
						<?php $quantity = floatval($content->stock_in) - floatval($content->stock_out); ?>
						@if($quantity > 0)
						<tr>
							<td valign="center">{{ $loop->iteration }}</td>
							<td>{{ $content->category_name ?? 'n/a' }}</td>
							<td>{{ $content->code ?? 'n/a' }}</td>
							<td>{{ $content->name ?? 'n/a' }}</td>
							<td>{{ number_format($quantity) ?? 'n/a' }}</td>
							<td>{{ $content->state_unit_type ?? ($content->item_unit_type ?? 'n/a') }}</td>
							<td>{{ $content->storage_state ?? 'Non-Specific' }}</td>
							{{-- <td>{{ $content->created_at ."(".$content->created_at->diffInDays(getCurrentDate())."days)" }}</td> --}}
						</tr>
						@endif
					@endforeach
        </tbody>
      </table>
    </div>
  </main>
@endsection