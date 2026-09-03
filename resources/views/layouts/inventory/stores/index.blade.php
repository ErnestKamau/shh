@extends('layouts.inventory.layout.app', ['dataTable'=>true])

@section('title2')
  <title>Inventory Stores</title>
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
          'name' => 'Stores',
          'icon' => null
        )
      );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>

    <div class="batch-header-bar mb-3">
      <div class="batch-header-top">
        <div class="batch-title-group">
          <span class="batch-code-label">{{ inventoryLabel('stores', 'Inventory Stores') }}</span>
          <span class="batch-stage-pill">
            <i class="mdi mdi-warehouse"></i>
            {{ count($stores) }} {{ inventoryLabel('stores', 'Stores') }}
          </span>
        </div>
      </div>
      <div class="d-flex align-items-center" style="gap: 0.5rem;">
        <button class="btn btn-primary btn-sm workflow-header-receive-btn" data-toggle="modal" data-target="#add-inventory-store">
          <i class="mdi mdi-plus"></i> {{ inventoryLabel('add_store', 'Add Store') }}
        </button>
      </div>
    </div>

    <div>
			<div class="card tab-card">
				<div class="card-header tab-card-header">
					<ul class="nav nav-tabs card-header-tabs" id="Elements-tabs" role="tablist">
						<li class="nav-item">
							<a class="nav-link active" id="Stores-tab" data-toggle="tab" href="#Stores" role="tab" aria-controls="Stores" aria-selected="true">Stores</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" id="Contents-tab" data-toggle="tab" href="#Contents" role="tab" aria-controls="Contents" aria-selected="true">Contents</a>
						</li>
					</ul>
				</div>
				<div class="tab-content" id="Stores-tabs-content">
					<div class="tab-pane fade p-3" id="Contents" role="tabpanel" aria-labelledby="one-tab">
						<h5 class="card-title mb-3">Available Inventory Item List</h5>
						<div class="table-responsive">
							<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
								<thead class="bg-light p-2">
									<tr>
										<th>No</th>
										<th>Store</th>
										<th>Slot</th>
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
												<td>{{ $content->store ?? 'n/a' }}</td>
												<td>{{ $content->slot ?? 'n/a' }}</td>
												<td>{{ $content->category_name ?? 'n/a' }}</td>
												<td>{{ $content->code ?? 'n/a' }}</td>
												<td>{{ $content->name ?? 'n/a' }}</td>
												<td>{{ number_format(floatval($content->stock_in) - floatval($content->stock_out)) ?? 'n/a' }}</td>
												<td>{{ $content->state_unit_type ?? ($content->item_unit_type ?? 'n/a') }}</td>
												<td>{{ $content->storage_state ?? 'Non-Specific' }}</td>
												{{-- <td>{{ $content->created_at ."(". isset($content->created_at) ? $content->created_at->diffInDays(getCurrentDate()) : '-'."days)" }}</td> --}}
											</tr>
										@endif
									@endforeach
								</tbody>
							</table>
						</div>
					</div>
					<div class="tab-pane show active fade p-3" id="Stores" role="tabpanel" aria-labelledby="one-tab">
						<h5 class="card-title mb-3">Store List</h5>
						<div class="table-responsive">
							<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
								<thead class="bg-light p-2">
									<tr>
										<th>No</th>
										<th>Name</th>
										<th>Type</th>
										<th>Slots</th>
										<th>Status</th>
										<th></th>
									</tr>
								</thead>
								<tbody>
									@foreach($stores as $store)
										<tr>
											<td valign="center">{{ $loop->iteration }}</td>
											<td>{{ $store->name }}</td>
											<td>{{ $store->type_of_store == "inventory_store" ? "Inventory Store" : ( $store->type_of_store == "lab_store" ? "Lab Store" : "Bench-Kit Storage" ) }}</td>
											<td>{{ $store->slots->count() }}</td>
											<td>{{ $store->is_frozen == 0 ? 'Operational' : 'Frozen for stock taking.' }}</td>
											<td nowrap>
												<button class="btn btn-primary btn-sm" data-target="#edit-inventory-store-{{ $loop->iteration }}" data-toggle="modal"><i class="mdi mdi-pencil-outline"></i> <small class="hidden-sm-up">Edit</small> </button>
												<button class="btn btn-danger btn-sm" data-target="#delete-inventory-store-{{ $loop->iteration }}" data-toggle="modal"><i class="mdi mdi-delete-empty"></i> <small class="hidden-sm-up">Delete</small> </button>
												<a class="btn btn-success btn-sm" href="{{ route('inventory-store-slots', ['store'=>$store->id]) }}"><i class="mdi mdi-eye-outline"></i> <small class="hidden-sm-up">Show</small> </a>
												<div id="edit-inventory-store-{{ $loop->iteration }}" class="modal fade" role="dialog">
													<div class="modal-dialog">
														<!-- Modal content-->
														<form class="modal-content" method="POST" action="{{ route('edit-inventory-store', ['id'=>$store->id]) }}" enctype="multipart/form-data">
															@csrf
															<div class="modal-header">
																<h4 class="modal-title"><i class="mdi mdi-pencil-outline"></i> Edit Store</h4>
															</div>
															<div class="modal-body">
																<div class="form-group">
																	<label class="control-label">Name</label>
																	<input type="text" class="form-control" name="name" value="{{ $store->name }}" placeholder="Name..." required />
																</div>
																<label class="control-label">
																	<input type="checkbox" name="type_of_store" value="lab_store" {{ $store->type_of_store == "lab_store" ? "checked" : "" }} />
																	Is Lab Store
																</label>
															</div>
															<div class="modal-footer">
																<button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
																<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
															</div>
														</form>
													</div>
												</div>
												<div id="delete-inventory-store-{{ $loop->iteration }}" class="modal fade" role="dialog">
													<div class="modal-dialog">
														<!-- Modal content-->
														<form class="modal-content" method="POST" action="{{ route('delete-inventory-store', ['id'=>$store->id]) }}" enctype="multipart/form-data">
															@csrf
															<div class="modal-header">
																<h4 class="modal-title"><i class="mdi mdi-delete"></i> Remove Store</h4>
															</div>
															<div class="modal-body">
																<div class="form-group">
																	<div class="alert alert-danger alert-callout">
																		<i class="fas fa-exclamation-triangle"></i> Remove this store
																	</div>
																</div>
															</div>
															<div class="modal-footer">
																<button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
																<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
															</div>
														</form>
													</div>
												</div>
											</td>
										</tr>
									@endforeach
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
  <div id="add-inventory-store" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <!-- Modal content-->
      <form class="modal-content" method="POST" action="{{ route('add-inventory-store') }}" enctype="multipart/form-data">
        @csrf
        <div class="modal-header">
          <h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Store</h4>
        </div>
        <div class="modal-body">
          <div class="form-group">
						<label class="control-label">Name</label>
						<input type="text" class="form-control" name="name" value="" placeholder="Name..." required />
					</div>
          <div class="form-group">
						<label class="control-label">
							<input type="checkbox" name="type_of_store" value="lab_store" />
							Is Lab Store
						</label>
					</div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
  </div>
@endsection
