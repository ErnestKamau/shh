@extends('layouts.inventory.layout.app', ['dataTable'=>true, 'select2'=>true])

@section('title2')
<title>{{ $taking->code }} - Stock Taking | Inventory Departments</title>
@endsection
@section('content2')
<?php $disabled = (isset($taking->status) && in_array($taking->status, array("Awaiting Adjustment Approval", "Completed", "Rejected"))) ? 'readonly' : ''; ?>
<main>
	<?php
	$inventoryProcurementRoles = ['Inventory Procurement Group', 'Procurement', 'Admin'];
	$isInventoryProcurement = \Auth::user()->hasAnyRole($inventoryProcurementRoles);

		$items = array(
			array(
				'link' => route('inventory-home'),
				'name' => 'Inventory Management',
				'icon' => null
			),
			array(
				'link' => route('stock-taking-list'),
				'name' => 'Stock Taking',
				'icon' => null
			),
			array(
				'link' => '#',
				'name' => $taking->code,
				'icon' => null
			)
		);
	?>
	<x-bread-crumb :items="$items"></x-bread-crumb>
	<h4 class="p-4">
		<i class="mdi mdi-format-list-bulleted-type"></i>Stock Taking - {{ $taking->code }} <small class="text-muted"><i class="mdi mdi-warehouse"></i> {{ $taking->store_names }}</small>
		@if(isset($taking->status) && !in_array($taking->status, array("Rejected", "Completed")))
			<small class="btn bg-white text-info badge-pill btn-sm" data-target="#store-freeze-modal" data-toggle="modal">{!! $taking->stores_frozen == "0" ? '<i class="mdi mdi-home-lock"></i> Freeze' : '<i class="mdi mdi-home-lock-open"></i> Un-Freeze' !!}</small>
			<a target="_blank" href="{{ route('stock-taking-sheet', ['id'=>$taking->id, 'print'=>'print']) }}" class="btn btn-sm btn-default  text-primary float-right"><i class="mdi mdi-printer"></i> Print Sheet</a>
			@if(isset($taking->status) && in_array($taking->status, array("Awaiting Adjustment Approval")))
				<button class="btn btn-default text-success btn-sm float-right save-capture-btn" {{ $taking->stores_frozen == "0" ? 'disabled': '' }}><i class="mdi mdi-backup-restore"></i> Return to Capture</button>
			@else
				<button class="btn btn-default text-success btn-sm float-right save-capture-btn" {{ $taking->stores_frozen == "0" ? 'disabled': '' }}><i class="mdi mdi-content-save"></i> Save Capture</button>
			@endif
		@endif
	</h4>
	<div class="pl-4 pb-4">
		<span class="badge badge-pill bg-white pl-4 p-2 pr-4" style="font-weight: 500; font-size: 13px"><i class="mdi mdi-information"></i> {{ $taking->status }} </span>
		@if(isset($taking->status) && in_array($taking->status, array("In Quantity Capture")))
			@if ($isInventoryProcurement)
				<button class="ml-2 btn badge-pill btn-outline-danger btn-sm save-capture-btn" data-type="request_adjustment" {{ $taking->stores_frozen == "0" ? 'disabled': '' }}>
					<i class="mdi mdi-check-decagram"></i> Request Adjustment Approval
				</button>
			@endif
		@endif
		@if(isset($taking->status) && $taking->status == "Awaiting Adjustment Approval")
			@if ($isInventoryProcurement)
				<span class="ml-2 badge-pill badge-success pl-4 pt-2 pr-4 pb-2" style="cursor: pointer" data-type="approve" data-target="#approve-deny-adjustment-modal" data-toggle="modal" {{ $taking->stores_frozen == "0" ? 'disabled': '' }}>
					<i class="mdi mdi-check-bold"></i> Approve Adjustment
				</span>
				<span class="ml-2 badge-pill badge-danger pl-4 pt-2 pr-4 pb-2" style="cursor: pointer" data-type="deny" data-target="#approve-deny-adjustment-modal" data-toggle="modal" {{ $taking->stores_frozen == "0" ? 'disabled': '' }}>
					<i class="mdi mdi-window-close"></i> Reject Adjustment
				</span>
			@endif
		@endif
	</div>
	<br>
	<div class="card tab-card">
		<div class="card-header tab-card-header">
			<ul class="nav nav-tabs card-header-tabs" style="font-size: 14px" id="Request-tabs" role="tablist">
				<li class="nav-item">
					<a class="nav-link active" id="stock-taking-items-tab" data-toggle="tab" href="#stock-taking-items" role="tab" aria-controls="stock-taking-items" aria-selected="true">Items</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" id="Counters-tab" data-toggle="tab" href="#Counters" role="tab" aria-controls="Counters" aria-selected="Counters">
						Counters
					</a>
				</li>
			</ul>
		</div>
		<div class="tab-content" id="stock-taking-tabs-content">
			<div class="tab-pane fade p-3" id="Counters" role="tabpanel" aria-labelledby="one-tab">
				<h5 class="card-title mb-3 mt-1"><i class="mdi mdi-account-group"></i> Counters
					<span class="btn btn-transparent text-primary float-right" data-toggle="modal" data-target="#add-counter-modal">
						<i class="mdi mdi-plus"></i> Counter
					</span>
				</h5>
				<div class="table-responsive">
					<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
						<thead>
							<th>#</th>
							<th>Photo</th>
							<th>Name</th>
							<th>Email</th>
							<th>Phone</th>
							<th></th>
						</thead>
						<tbody>
							@foreach ($taking->counters() as $counter)
								<tr>
									<td>{{ $loop->iteration }}</td>
									<td><img src="{{ asset($counter->photo ?? '/images/user.png') }}" style="max-width: 30px" /></td>
									<td>{{ $counter->name }}</td>
									<td>{{ $counter->email }}</td>
									<td>{{ $counter->phone ?? 'n/a' }}</td>
									<td>
										<span class="btn btn-transparent text-danger" data-target="#remove-counter-modal" data-toggle="modal" data-counter="{{ $counter->id }}">
											<i class="mdi mdi-delete"></i>
										</span>
									</td>
								</tr>
							@endforeach
						</tbody>
					</table>
				</div>
			</div>
			<div class="tab-pane fade show active p-3" id="stock-taking-items" role="tabpanel" aria-labelledby="one-tab">
				@if ($taking->reviewed || $taking->status == "Completed")
					<a href="?print=true" class="btn btn-sm btn-info pull-right" target="_blank"><i class="mdi mdi-printer"></i> Print</a>
				@endif
				<form method="POST" id="captured-quantities-form" action="{{ route('stock-taking-save-capture', ['id'=>$taking->id]) }}" class="bg-light p-4">
					@csrf
					@foreach ($dataByStore as $key=>$items)
						<div class="store-holder">
							<hr>
							<h6>
								<i class="mdi mdi-warehouse mt-2"></i> {{ $items[0]->store }}
								@if(isset($taking->status) && !in_array($taking->status, array("Rejected", "Completed")))
								<small class="btn btn-transparent btn-sm text-primary float-right pull-right add-row-item" data-store="{{ $items[0]->store_id }}">
									<i class="mdi mdi-plus"></i> Add Row
								</small>
								@endif
							</h6>
							<div class="table-responsive mb-2">
								<div class="table-responsive">
									<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-s">
										<thead>
											<th>No.</th>
											<th>Code</th>
											<th>Item</th>
											<th>Uom</th>
											<th>Store</th>
											<th>Slot</th>
											@if ($taking->reviewed)
												<th>System Quantity</th>
											@endif
											<th>Recorded Quantity</th>
											<th>Lot No</th>
											<th>Expiry</th>
											@if ($taking->reviewed)
												<th>Dev. Quantity</th>
												<th>Dev. Value</th>
											@endif
											<th>Comments</th>
										</thead>
										<tbody class="items-body">
											@foreach($items as $item)
												<tr>
													<td>{{ $loop->iteration }}</td>
													<td>
														{{ $item->code }}
														<input type="hidden" name="sid[]" value="{{ $item->sid }}" />
														<input type="hidden" name="code[]" value="{{ $item->code }}" />
													</td>
													<td>
														{{ $item->name }}
														<input type="hidden" name="inventory_sub_category_id[]" value="{{ $item->item_id }}" />
													</td>
													<td>
														{{ $item->unit_type }}
													</td>
													<td>
														{{ $item->store }}
														<input type="hidden" name="store_name[]" value="{{ $item->store }}" />
														<input type="hidden" name="store_id[]" value="{{ $item->store_id }}" />
													</td>
													<td>
														{{ $item->slot }}
														<input type="hidden" name="slot_id[]" value="{{ $item->slot_id }}" />
														<input type="hidden" name="slot_name[]" value="{{ $item->slot }}" />
													</td>
													@if ($taking->reviewed)
													<td>{{ $item->quantity }}</td>
													@endif
													<td>
														<input type="hidden" name="system_quantity[]" value="{{ $item->quantity }}" />
														<input type="number" step="any" min="0" class="form-control input-sm" placeholder="Quantity" name="available_quantity[]" value="{{ $item->available_quantity }}"  {{ $disabled }}/>
													</td>
													<td>
														<input type="text" name="lot_no[]" placeholder="Lot Number..." value="{{ $item->lot_no }}" class="form-control" style="min-width: 150px" />
													</td>
													<td>
														<input type="date" name="expiry[]" placeholder="Expiry..." value="{{ $item->expiry }}" class="form-control" style="min-width: 150px" />
													</td>
													@if ($taking->reviewed)
														<?php
															$variation = floatval($item->quantity) - floatval($item->available_quantity);
															$var_direction = $variation > 0 ? 'below' : 'above';
															$var_perc = number_format(floatval($item->quantity) > 0 ? abs($variation)/floatval($item->quantity)*100 : 100, 2);
														?>
														<td nowrap>
															@if($item->available_quantity !==null)
																<span class="{{ $var_direction == 'below' ? 'text-danger' : 'text-success' }}">
																	<i class="mdi mdi-{{ $var_direction == 'below' ? 'menu-down' : 'menu-up' }}"></i> {{ $var_perc }}%
																	<small>({{ abs($variation) }})</small>
																</span>
															@else
																-
															@endif
														</td>
														<td nowrap>
															@if($item->available_quantity !==null)
																<span class="{{ $var_direction == 'below' ? 'text-danger' : 'text-success' }}">
																	<i class="mdi mdi-{{ $var_direction == 'below' ? 'menu-down' : 'menu-up' }}"></i> {{ number_format($item->unit_price*abs($variation), 2) }}/=
																</span>
															@else
																-
															@endif
														</td>
													@endif
													<td><textarea class="form-control input-sm" style="min-width: 150px" name="comments[]" {{ $disabled }}>{{ $item->comments }}</textarea></td>
												</tr>
											@endforeach
										</tbody>
									</table>
								</div>
							</div>
						</div>
					@endforeach
				</form>
			</div>
		</div>
	</div>
	<br>
</main>
@endsection
@section('script2')
<div id="store-freeze-modal" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<form class="modal-content" method="POST" action="{{ route('stock-taking-freeze-stores', ['id'=>$taking->id]) }}" enctype="multipart/form-data">
			@csrf
			<input type="hidden" value="{{ $taking->stores_frozen == '0' ? '1' : '0' }}" name="action" />
			@if($taking->stores_frozen == "0")
				<div class="modal-header">
					<h4 class="modal-title"><i class="mdi mdi-home-lock"></i> Freeze Stores</h4>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<div class="alert alert-light text-info" style="font-weight: 300; font-size: 16px">
							<i class="mdi mdi-information fa-2x pull-left"></i> Are you sure that you want to freeze the stores? By doing so, items can not be received or issued
							out of these stores.
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-primary"><i class="mdi mdi-lock"></i> Freeze</button>
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				</div>
			@else
				<div class="modal-header">
					<h4 class="modal-title"><i class="mdi mdi-home-lock-open"></i> Un-Freeze Stores</h4>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<div class="alert alert-light text-info" style="font-weight: 300; font-size: 16px">
							<i class="mdi mdi-information fa-2x pull-left"></i> Proceed with un-freezing the stores? <b>Please note</b> that any previously captured quantities will be cleared.
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-primary"><i class="mdi mdi-home-lock-open"></i> Proceed with Un-Freeze</button>
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				</div>
			@endif
		</form>
	</div>
</div>
<div id="add-counter-modal" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<form class="modal-content" method="POST" action="{{ route('add-stock-taking-counter', ['id'=>$taking->id]) }}" enctype="multipart/form-data">
			@csrf
			<div class="modal-header">
				<h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Counter(s)</h4>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<label>Select Counter(s)</label>
					<select name="counter[]" multiple class="form-control" required placeholder="Select Counter...">
						<option value="">Select Counter</option>
						@foreach (getAllUsers() as $user)
							<option value="{{ $user->id  }}">{{  $user->name  }}</option>
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
<div id="remove-counter-modal" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<form class="modal-content" method="POST" action="{{ route('remove-stock-taking-counter', ['id'=>$taking->id]) }}" enctype="multipart/form-data">
			<div class="modal-header">
				<h4 class="modal-title"><i class="mdi mdi-delete"></i> Remove Counter(s)</h4>
			</div>
			@csrf
			<div class="modal-body">
				<div class="form-group">
					<div class="alert alert-danger">
						<i class="fas fa-exclamation-triangle"></i> Are you sure you want to remove this counter?
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-danger"><i class="mdi mdi-delete"></i> Remove</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>

		</form>
	</div>
</div>
@if($taking->stores_frozen == "1")
<div id="approve-deny-adjustment-modal" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<div class="modal-content"></div>
	</div>
</div>
@endif
<script>
	var deny_approve = function(typ){
		if(typ == "deny"){
			var $body = $(`
				<div class="modal-header">
					<h4 class="modal-title"><i class="mdi mdi-cancel"></i> Reject Adjustment Request</h4>
				</div>
				<div class="modal-body">
					@csrf
					<div class="form-group">
						<div class="alert alert-light text-info" style="font-weight: 300; font-size: 16px">
							<i class="mdi mdi-cancel text-danger fa-2x pull-left"></i> Are you sure that you want to reject and close this adjustment?
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-danger save-capture-btn btn-sm"><i class="mdi mdi-window-close"></i> Yes, Reject Adjustment</button>
					<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
				</div>
			`);
		}
		else{
			var $body = $(`
				<div class="modal-header">
					<h4 class="modal-title"><i class="mdi mdi-check-bold"></i> Approve Adjustment Request</h4>
				</div>
				<div class="modal-body">
					@csrf
					<div class="form-group">
						<div class="alert alert-light text-info" style="font-weight: 300; font-size: 16px">
							<i class="mdi mdi-check-circle fa-2x pull-left text-success"></i> Are you sure you want to proceed with making the captured stock quantity adjustments?.
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-primary save-capture-btn btn-sm"><i class="mdi mdi-check"></i> Yes, Make Stock Adjustments</button>
					<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
				</div>
			`);
		}

		return $body.clone();
	}

	var getItemRow = function(store){
		var $lrow = $(`
			<tr class="del-item-row">
				<td><span class="btn btn-transparent btn-sm text-danger delete-row"><i class="mdi mdi-delete"></i></span></td>
				<td>
					<span class="item_code"></span>
					<input type="hidden" name="sid[]" value="-2" />
					<input type="hidden" class="item_code" name="code[]" value="" />
				</td>
				<td>
					<select name="inventory_sub_category_id[]" style="min-width: 200px; font-size: 12px" class="form-control selected-item" data-placeholder="Select Item..." required></select>
				</td>
				<td class="unit_type"></td>
				<td>
					<select name="store_id[]" class="form-control selected-store" data-placeholder="Select Store..." style="min-width: 100px">
						@foreach (getUserStores(false, true) as $store)
							<option value="{{ $store->id }}" ${ store == '{{ $store->id }}' ? 'selected' : '' } data-slots="{{ json_encode($store->slots) }}">{{ $store->name }}</option>
						@endforeach
					</select>
					<input type="hidden" name="store_name[]" />
				</td>
				<td>
					<select name="slot_id[]" style="min-width: 100px" class="form-control" data-placeholder="Select Slot..."></select>
					<input type="hidden" name="slot_name[]" />
				</td>
				@if ($taking->reviewed)
				<td>0</td>
				@endif
				<td>
					<input type="hidden" name="system_quantity[]" value="0" />
					<input type="number" step="any" min="0" class="form-control input-sm" placeholder="Quantity" name="available_quantity[]" value="" />
				</td>
				<td>
					<input type="text" name="lot_no[]" placeholder="Lot Number..." class="form-control" style="min-width: 150px" />
				</td>
				<td>
					<input type="date" name="expiry[]" placeholder="Expiry..." class="form-control" style="min-width: 150px" />
				</td>
				@if ($taking->reviewed)
					<td nowrap></td>
					<td nowrap></td>
				@endif
				<td><textarea class="form-control input-sm" style="min-width: 150px" name="comments[]"></textarea></td>
			</tr>
		`);

		var $row = $lrow.clone();

		$row.find('.selected-item').select2({
			ajax: {
				url: '{{ route("get_items_via_ajax") }}',
				data: function (params) {
					var query = {
						search: params.term,
						page: params.page || 1
					}
					return query;
				}
			},
			placeholder: 'Please Select Inventory Item...'
		});

		$row.find('[name="slot_id[]"]').on('change', function(){
			var selected = $(this).children('option:selected');
			$row.find('[name="slot_name[]"]').val(selected.text());
		});

		$row.find('.selected-store').on('change', function(){
			var selected = $(this).children('option:selected');
			$row.find('[name="store_name[]"]').val(selected.text());
			var slots = selected.data('slots');

			var slotDiv = $row.find('[name="slot_id[]"]');

			$.each(slots, function(i, s){
				var newOption = new Option(s.name, s.id, false, false);
					slotDiv.append(newOption).trigger('change');
			});

			slotDiv.val('').trigger('change');
		});

		$row.on('change', '.selected-item', function(){
			var itemID = $(this).val();
			var txt = $(this).text();
			var parts = txt.split('-');

			$row.find('.item_code').text(parts[0]);
			$row.find('.item_code').val(parts[0]);

			var $this = $(this);
			$.ajax({
				url: window.location.origin + '/get_item_details/' + encodeURIComponent(itemID),
				dataType: 'json',
				success: function(js){
					$row.find('.unit_type').text(js.uom);
				},
				error: function(xhr) {
					console.error('Failed to load item details.', xhr);
				}
			});
		});

		$row.find('[name="slot_id[]"]').select2();
		$row.find('.selected-store').select2().trigger('change');

		console.log(store);

		return $row;
	}

	$(function(){
		$('.add-row-item').on('click', function(){
			var store = $(this).data('store');
			var itemRow = getItemRow(store);
			var form = $(this).parents('.store-holder');
			form.find('.items-body').append(itemRow);

			form.find('.items-body').find('.delete-row').on('click', function(){
				if(confirm('Are you sure you want to delete this row?')){
					var parentTr = $(this).parents('tr.del-item-row');
					parentTr.remove();
				}
			});
		});


		$('.save-capture-btn').on('click', function(){
			var typ = $(this).data('type');

			if(typ == "request_adjustment"){
				$('#captured-quantities-form').append(`<input type="hidden" name="set_approval" value="1" />`);
				$('#captured-quantities-form').submit();
			}
			else{
				if(confirm('Save currently captured quantities?')){
					$('#captured-quantities-form').submit();
				}
			}
		});

		$('#remove-counter-modal').on('show.bs.modal', function(e){
			var counter = $(e.relatedTarget).data('counter');
			$(this).find('form').append(`<input type="hidden" name="counter_id" value="${counter}" />`);
		});

		$('#approve-deny-adjustment-modal').on('show.bs.modal', function(e){
			var typ = $(e.relatedTarget).data('type');
			var $body = deny_approve(typ);

			$(this).find('.modal-content').html($body);

			$body.find('.save-capture-btn').on('click', function(){
				$('#captured-quantities-form').append(`<input type="hidden" name="make_adjustments" value="${typ}" />`);
				$('#captured-quantities-form').submit();
			});
		});
	});
</script>
@endsection