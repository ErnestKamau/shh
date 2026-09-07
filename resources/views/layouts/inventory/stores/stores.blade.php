@extends('layouts.inventory.layout.app', ['dataTable'=>true, 'select2'=>true])

@section('title2')
  <title>Inventory Store Details | Inventory Management</title>
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
          'link' => '#',
          'name' => $store->name,
          'icon' => null
        )
      );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>

    <div class="batch-header-bar mb-3">
      <div class="batch-header-top">
        <div class="batch-title-group">
          <span class="batch-code-label">{{ $store->name }}</span>
          <span class="batch-stage-pill">
            <i class="mdi mdi-grid-large"></i>
            {{ inventoryLabel('store_details', 'Store Details') }}
          </span>
        </div>
      </div>
    </div>

    <div>
			<div class="card tab-card">
				<div class="card-header tab-card-header">
					<ul class="nav nav-tabs card-header-tabs" id="Data-tabs" role="tablist">
						<li class="nav-item">
							<a class="nav-link active" id="Slots-tab" data-toggle="tab" href="#Slots" role="tab" aria-controls="Slots" aria-selected="true">Slots</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" id="Contacts-tab" data-toggle="tab" href="#Contacts" role="tab" aria-controls="Contacts" aria-selected="true">Contacts</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" id="Contents-tab" data-toggle="tab" href="#Contents" role="tab" aria-controls="Contents" aria-selected="true">Contents</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" id="Cost-Center-tab" data-toggle="tab" href="#Cost-Center" role="tab" aria-controls="Cost Centers" aria-selected="true">Cost Centers</a>
						</li>
					</ul>
				</div>
				<div class="tab-content" id="Data-tabs-content">
					<div class="tab-pane fade p-3" id="Cost-Center" role="tabpanel" aria-labelledby="one-tab">
						<h5>
							<i class="mdi mdi-home-city"></i> Cost Centers
							<button class="btn btn-default text-primary btn-sm float-right" data-toggle="modal" data-target="#add-cost-centers"><i class="mdi mdi-plus"></i> Add</button>
						</h5>
						<hr>
						<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
							<thead class="bg-light p-2">
								<tr>
									<th>No</th>
									<th>Cost Center</th>
									<th></th>
								</tr>
							</thead>
							<tbody>
								@foreach($store->cost_centers() ?? [] as $item)
									<tr>
										<td valign="center">{{ $loop->iteration }}</td>
										<td>{{ $item->cost_center }}</td>
										<td nowrap>
											<button class="btn btn-default text-danger btn-sm" data-cc="{{ $item->cost_center }}" data-target="#remove-cost-center" data-toggle="modal" data-item="{{ json_encode($item) }}"><i class="mdi mdi-delete-empty"></i> <small class="hidden-sm-up">Remove</small> </button>
										</td>
									</tr>
								@endforeach
							</tbody>
						</table>
					</div>
					<div class="tab-pane fade p-3" id="Contacts" role="tabpanel" aria-labelledby="one-tab">
						<h5>
							<i class="mdi mdi-account-group"></i> Contacts
							<button class="btn btn-default text-primary btn-sm float-right" data-toggle="modal" data-target="#add-store-contacts"><i class="mdi mdi-plus"></i> Add</button>
						</h5>
						<hr>
						<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
							<thead class="bg-light p-2">
								<tr>
									<th>No</th>
									<th>Name</th>
									<th>Email</th>
									<th></th>
								</tr>
							</thead>
							<tbody>
								@foreach($store->contacts() as $item)
									<tr>
										<td valign="center">{{ $loop->iteration }}</td>
										<td>{{ $item->name }}</td>
										<td>{{ $item->email }}</td>
										<td nowrap>
											<button class="btn btn-default text-danger btn-sm" data-target="#delete-store-contacts" data-toggle="modal" data-item="{{ json_encode($item) }}"><i class="mdi mdi-delete-empty"></i> <small class="hidden-sm-up">Delete</small> </button>
										</td>
									</tr>
								@endforeach
							</tbody>
						</table>
					</div>
					<div class="tab-pane fade p-3" id="Contents" role="tabpanel" aria-labelledby="one-tab">
						<h5>
							<i class="mdi mdi-package-variant"></i> Contents
						</h5>
						<hr>
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
											{{-- <td>{{ $content->created_at ."(".$content->created_at->diffInDays(getCurrentDate())."days)" }}</td> --}}
										</tr>
									@endif
								@endforeach
							</tbody>
						</table>
					</div>
					<div class="tab-pane show active p-3" id="Slots" role="tabpanel" aria-labelledby="one-tab">
						<h5>
							<i class="mdi mdi-grid-large"></i> Slots
							<button class="btn btn-default text-primary btn-sm float-right" data-toggle="modal" data-target="#add-inventory-slot"><i class="mdi mdi-plus"></i> Add</button>
						</h5>
						<hr>
						<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
							<thead class="bg-light p-2">
								<tr>
									<th>No</th>
									<th>Name</th>
									{{-- <th>Contents</th> --}}
									<th></th>
								</tr>
							</thead>
							<tbody>
								@foreach($store->slots as $slot)
									<tr>
										<td valign="center">{{ $loop->iteration }}</td>
										<td>{{ $slot->name }}</td>
										{{-- <td>{{ $slot->contents->count() }}</td> --}}
										<td nowrap>
											<button class="btn btn-default text-primary btn-sm" data-target="#edit-inventory-slot-{{ $loop->iteration }}" data-toggle="modal"><i class="mdi mdi-pencil-outline"></i> <small class="hidden-sm-up">Edit</small> </button>
											<button class="btn btn-default text-danger btn-sm" data-target="#delete-inventory-slot-{{ $loop->iteration }}" data-toggle="modal"><i class="mdi mdi-delete-empty"></i> <small class="hidden-sm-up">Delete</small> </button>
											<a class="btn btn-default text-success btn-sm" href="{{ route('inventory-slot-contents', ['slot'=>$slot->id, 'store'=>$store->id]) }}"><i class="mdi mdi-eye-outline"></i> <small class="hidden-sm-up">Show</small> </a>
											<div id="edit-inventory-slot-{{ $loop->iteration }}" class="modal fade" role="dialog">
												<div class="modal-dialog">
													<!-- Modal content-->
													<form class="modal-content" method="POST" action="{{ route('edit-inventory-store-slot', ['id'=>$slot->id]) }}" enctype="multipart/form-data">
														@csrf
														<div class="modal-header">
															<h4 class="modal-title"><i class="mdi mdi-pencil-outline"></i> Edit Slot</h4>
														</div>
														<div class="modal-body">
															<div class="form-group">
																<label class="control-label">Name</label>
																<input type="text" class="form-control" name="name" value="{{ $slot->name }}" placeholder="Name..." required />
															</div>
														</div>
														<div class="modal-footer">
															<button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
															<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
														</div>
													</form>
												</div>
											</div>
											<div id="delete-inventory-slot-{{ $loop->iteration }}" class="modal fade" role="dialog">
												<div class="modal-dialog">
													<!-- Modal content-->
													<form class="modal-content" method="POST" action="{{ route('delete-inventory-store-slot', ['id'=>$slot->id]) }}" enctype="multipart/form-data">
														@csrf
														<div class="modal-header">
															<h4 class="modal-title"><i class="mdi mdi-delete"></i> Remove Slot</h4>
														</div>
														<div class="modal-body">
															<div class="form-group">
																<div class="alert alert-danger alert-callout">
																	<i class="fas fa-exclamation-triangle"></i> Remove this slot
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
  </main>
@endsection
@section('script2')
  <div id="add-inventory-slot" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <!-- Modal content-->
      <form class="modal-content" method="POST" action="{{ route('add-inventory-store-slot', ['store'=>$store->id]) }}" enctype="multipart/form-data">
        @csrf
        <div class="modal-header">
          <h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Slot</h4>
        </div>
        <div class="modal-body">
          <div class="form-group">
						<label class="control-label">Name</label>
						<input type="text" class="form-control" name="name" value="" placeholder="Name..." required />
					</div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
  </div>
  <div id="add-cost-centers" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <!-- Modal content-->
      <form class="modal-content" method="POST" action="{{ route('add-store-cost-center', ['id'=>$store->id]) }}" enctype="multipart/form-data">
        @csrf
        <div class="modal-header">
          <h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Cost Center</h4>
        </div>
        <div class="modal-body">
          <div class="form-group">
						<label class="control-label">Select Cost Centers</label>
						<select name="cost_center[]" class="form-control ls-select2" placeholder="Select Cost Center..." data-placeholder="Select Cost Center..." multiple>
							@foreach (getCostCenter() as $cc)
								<option value="{{ $cc }}">{{ $cc }}</option>
							@endforeach
						</select>
					</div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary"><i class="mdi mdi-plus"></i> Add</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
  </div>
  <div id="remove-cost-center" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <!-- Modal content-->
      <form class="modal-content" method="POST" action="{{ route('remove-store-cost-center', ['id'=>$store->id]) }}" enctype="multipart/form-data">
        @csrf
        <div class="modal-header">
          <h4 class="modal-title"><i class="mdi mdi-delete"></i> Remove Cost Center</h4>
        </div>
        <div class="modal-body">
          <div class="alert alert-danger">
						<i class="mdi mdi-alert"></i> Remove cost center from this store?
					</div>
					<input type="hidden" name="cc_to_remove" />
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-danger"><i class="mdi mdi-delete"></i> Yes, Delete</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
  </div>
  <div id="add-store-contacts" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <!-- Modal content-->
      <form class="modal-content" method="POST" action="{{ route('add-store-contacts', ['id'=>$store->id]) }}" enctype="multipart/form-data">
        @csrf
        <div class="modal-header">
          <h4 class="modal-title"><i class="mdi mdi-account-plus"></i> Add Contact</h4>
        </div>
        <div class="modal-body">
          <div class="form-group">
						<label class="control-label">Contact</label>
						<select name="user_id" class="form-control ls-select2" placeholder="Select Contact..." data-placeholder="Select Contact...">
							<option value=""></option>
							@foreach (getUsers() as $user)
								<option value="{{ $user->id }}">{{ $user->name }}</option>
							@endforeach
						</select>
					</div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
  </div>
  <div id="delete-store-contacts" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <!-- Modal content-->
      <form class="modal-content" method="POST" action="{{ route('delete-store-contacts') }}" enctype="multipart/form-data">
        @csrf
        <div class="modal-header">
          <h4 class="modal-title"><i class="mdi mdi-account-cancel"></i> Remove Contact</h4>
        </div>
        <div class="modal-body">
					<input name="store_contact_id" type="hidden"/>
					<div class="alert alert-danger">
						<i class="mdi mdi-alert pull-left fa-2x"></i> Remove this contact from the store?
					</div>
				</div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-danger"><i class="mdi mdi-delete"></i> Yes, Remove</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
	</div>
	<script type="text/javascript">
		$(function(){
			$('#delete-store-contacts').on('show.bs.modal', function(e){
				var contact = $(e.relatedTarget).data('item');

				$(this).find('[name="store_contact_id"]').val(contact.store_contact_id);
			});

			$('#remove-cost-center').on('show.bs.modal', function(e){
				var cc = $(e.relatedTarget).data('cc');

				$(this).find('[name="cc_to_remove"]').val(cc);
			});
		});
	</script>
@endsection
