@extends('layouts.inventory.suppliers.dashboard.layout.app', ['dataTable'=>true, 'select2'=>true])

@section('title2')
	<title> {{ $supplier->name }} | Supplier </title>
	<style type="text/css">
		.tab-card {
			border:1px solid #eee;
		}

		.tab-card-header {
			background:none;
		}
		/* Default mode */
		.tab-card-header > .nav-tabs {
			border: none;
			margin: 0px;
		}
		.tab-card-header > .nav-tabs > li {
			margin-right: 2px;
		}
		.tab-card-header > .nav-tabs > li > a {
			border: 0;
			border-bottom:2px solid transparent;
			margin-right: 0;
			color: #737373;
			padding: 2px 15px;
		}

		.tab-card-header > .nav-tabs > li > a.show {
			border-bottom:2px solid #007bff;
			color: #007bff;
		}
		.tab-card-header > .nav-tabs > li > a:hover {
			color: #007bff;
		}

		.tab-card .nav-link.active{
			background-color: #dadccd !important;
			border: 1px solid #cccebf !important;
		}

		.tab-card-header > .tab-content {
			padding-bottom: 0;
		}

		.my-small-text{
			font-size: 13px !important;
		}

		.removeThis {
			z-index: 12;
			position: absolute;
			cursor: pointer;
			top: 0px;
			right: 2px;
			padding: 1px 4px;
			font-size: 12px;
			background-color: red;
			border-radius: 50%;
			color: #fff;
			box-shadow: 0px 0px 5px rgba(0,0,0,0.08);
		}

	</style>
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
          'link' => route('inventory-suppliers'),
          'name' => 'Suppliers',
          'icon' => null
        ),
				array(
          'link' => '#',
          'name' => $supplier->name,
          'icon' => null
        )
      );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
		<h2 class="p-4">
			Request Of Quotation Items.
		</h2>
		
			
			<div>
				<div class="card tab-card">
					<div class="card-header tab-card-header">
						<ul class="nav nav-tabs card-header-tabs" id="Categories-tabs" role="tablist">
							
							<li class="nav-item">
								<a class="nav-link" id="Ratings-tab" data-toggle="tab" href="#Ratings" role="tab" aria-controls="Ratings" aria-selected="true">Request Quotation Items</a>
							</li>
						</ul>
					</div>
					<div class="tab-content" id="Orders-tabs-content">
						<div class="tab-pane fade p-3" id="Ratings" role="tabpanel" aria-labelledby="one-tab">
							<h5 class="card-title">Ratings </h5>
							<div class="table-responsive">
								<table
									class="table table-condensed my-small-text table-striped server-side table-hover table-bordered table-sm">
									<thead class="bg-light p-2">
										<tr>
											<th>No</th>
											<th>RFQS</th>
											<th>Store</th>
											<th>Slot</th>
											<th>Inventory Sub Category</th>
											<th>Quantity</th>
											<th>Net Value</th>
											<th>Action</th>
											<th>Status</th>
											<th>Inventory Item</th>
											<th>Created at</th>
										</tr>
									</thead>
									<tbody>
										@if(!isset($rfqs_items[$id]))
										<h3>No items for the specified request of quotation</h3>
										@endif
										@if(isset($rfqs_items[$id]))
											@foreach($rfqs_items[$id] as $item)
	  											<tr>
													<td>{{$loop->iteration}}</td>
													<?php
													$rfq = getRfqsById($item->request_id);
													$store = getStoreById($item->store_id);
													$slot = getSlotById($item->slot_id);
													$sub = getInventorySubCatByID($item->inventory_sub_category_id);
													$inve_item = getInventoryItemById($item->inventory_item_id);
													?>
													<td>{{$rfq->request_code}}</td>
													<td>{{$store->name}}</td>
													<td>{{$slot->name}}</td>
													<td>{{$sub->name}}</td>
													<td>{{$item->quantity}}</td>
													<td>{{$item->net_value}}</td>
													<td>{{$item->action}}</td>
													<td>{{$item->status}}</td>
													<td>{{$inve_item->batch_code}}</td>
													<td>{{$item->created_at}}</td>									
												  </tr>
											@endforeach
										@endif
									</tbody>
								</table>
							</div>
						</div>
						
					</div>
				</div>
			</div>
		
	</main>
@endsection
@section('script2')



@endsection
