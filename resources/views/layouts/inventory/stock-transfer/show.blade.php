@extends('layouts.inventory.layout.app', ['dataTable'=>true, 'select2'=>true])

@section('title2')
<title>{{ $transfer->code ?? 'New Transfer' }} - Stock Transfer | Inventory Departments</title>
@endsection
@section('content2')

<main>
	<?php $lab_storage_catgory_id = systemVariables('lab_samples_category_id'); ?>
	<?php
		$items = array(
			array(
				'link' => route('inventory-home'),
				'name' => 'Inventory Management',
				'icon' => null
			),
			array(
				'link' => route('stock-transfer-list'),
				'name' => 'Stock Transfer',
				'icon' => null
			),
			array(
				'link' => '#',
				'name' => $transfer->code ?? 'New Transfer',
				'icon' => null
			)
		);
	?>
	<x-bread-crumb :items="$items"></x-bread-crumb>
	<h4 class="p-4">
		<i class="mdi mdi-format-list-bulleted-type"></i> Stock Transfer - {{ $transfer->code ?? 'New Transfer' }} <small class="badge badge-light badge-pill text-muted" style="font-weight: 500"><i class="mdi mdi-information"></i> {{ $transfer->status ?? "In Preparation" }}</small>
		@if(isset($transfer->location_id) && $transfer->status != "Completed")
			<button class="btn btn-default text-success btn-sm float-right save-form-btn" data-type="save_items"><i class="mdi mdi-content-save"></i> Save</button>
			@if ($transfer->status == "Transfer Items Updated")
				<button class="btn btn-default text-danger btn-sm float-right save-form-btn" data-type="transfer_items"><i class="mdi mdi-bank-transfer-out"></i> Transfer Items</button>
			@endif
		@endif
	</h4>
	<br>
	<div class="row no-gutters">
		<div class="col-sm-4 p-2">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title"><i class="mdi mdi-information-outline"></i> Transfer Details</h5>
					<form method="POST" action="{{ route('stock-transfer-update', ['id'=>$transfer->id ?? time()]) }}" enctype="multipart/form-data">
						@csrf
						<div class="form-group">
							<label class="control-label">Location*</label>
							<select class="form-control location-selector" data-selected="{{ $transfer->location_id }}" name="location_id" placeholder="Location..." required>
								<option></option>
								@foreach (viewableLocations() as $key=>$item)
									@if(isset($item->level))
										<option value="{{ $item->id }}">
											{{ $key }}
										</option>
									@else
										@foreach ($item as $key1=>$item1)
											@if(isset($item1->level))
											<option value="{{ $item1->id }}">
												{{ $key }} <small class="text-muted"> > </small> {{ $key1 }}
											</option>
											@else
												@foreach ($item1 as $key2=>$item2)
													@if(isset($item2->level))
														<option value="{{ $item2->id }}">
															{{ $key }} <small class="text-muted"> > </small> {{ $key1 }} <small class="text-muted"> > </small> {{ $key2 }}
														</option>
													@else
														@foreach ($item2 as $key3=>$item3)
															<option value="{{ $item3->id }}">
																{{ $key }} <small class="text-muted"> > </small> {{ $key1 }} <small class="text-muted"> > </small> {{ $key2 }} <small class="text-muted"> > </small> {{ $key3 }}
															</option>
														@endforeach
													@endif
												@endforeach
											@endif
										@endforeach
									@endif
								@endforeach
							</select>
						</div>
						<div class="form-group">
							<label>Department*</label>
							<select class="form-control location-departments" data-selected="{{ $transfer->department_id }}" name="department_id" placeholder="Department..." required><option></option></select>
						</div>
						<div class="form-group">
							<label>Description*</label>
							<textarea class="form-control" name="description" placeholder="Description..." required>{{ $transfer->description }}</textarea>
						</div>
						<div class="form-group">
							<button class="btn btn-outline-success btn-block" {{ isset($transfer->status) && in_array($transfer->status, array("Completed", "Transfer Items Updated")) ? 'disabled' : '' }}>
								<i class="mdi mdi-content-save"></i> Save
							</button>
						</div>
					</form>
				</div>
			</div>
		</div>
		<div class="col-sm-8">
			<form class="card" method="POST" id="save-transfer-items" action="{{ route('stock-transfer-items-update', ['id'=>$transfer->id ?? time()]) }}">
				@csrf
				<div class="card-body">
					<h5 class="card-title"><i class="mdi mdi-package-variant"></i> Transfer Items
						@if(isset($transfer->location_id) && $transfer->status != "Completed")
							<span class="btn btn-sm btn-default text-info float-right add-item-row"><i class="mdi mdi-plus"></i> Row</span>
						@endif
					</h5>
					<hr>
					<div class="table-responsive">
						<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-">
							<thead>
								<tr>
									<th></th>
									<th>Local Item</th>
									<th>Local UoM</th>
									<th>Local Store</th>
									<th>Local Slot</th>
									<th>Current State</th>
									<th>Available Quantity</th>
									<th>Target Item</th>
									<th>Target UoM</th>
									<th>Target Store</th>
									<th>Target Slot</th>
									<th>Target State</th>
									<th>Local Quantity to Transfer</th>
									<th>Expiry/Target Date</th>
								</tr>
							</thead>
							<tbody id="items-holder" data-items="{{ $transfer_items }}"></tbody>
						</table>
					</div>
				</div>
			</form>
		</div>
	</div>
</main>
@endsection
@section('script2')
	<script>
		var itemrow = function(data = {}){
			var isDisabled = "{{ isset($transfer->location_id) && $transfer->status != "Completed" ? '' : 'disabled' }}";
			var r = $(`
			<tr>
				<td>
					@if(isset($transfer->location_id) && $transfer->status != "Completed")
						<span class="btn btn-default btn-sm text-danger remove-item-row" data-transfer-id="${data.id}">
							<i class="mdi mdi-delete"></i>
						</span>
					@endif
					<input type="hidden" name="items[transfer_item_id][]" value="${data.id != undefined ? data.id : 0}" />
				</td>
				<td>
					<div class="form-group">
						<select ${isDisabled} name="items[local_item_id][]" data-selected="${data.local_item_id != undefined ? data.local_item_id : 0}" style="min-width: 150px; font-size: 12px" class="form-control selected-item" data-type="local" placeholder="Select Item..." required>
							<option selected></option>
							@foreach (getInventoryItems(0, true) as $item)
								<option value="{{ $item->id }}" data-unit_val="{{ $item->unit_price }}" data-text="{{ $item->name }}"
									data-material_type_id="{{ $item->material_type_id }}" data-unit-type="{{ $item->unit_type }}">{{ $item->code }} - {{ $item->name }}</option>
							@endforeach
						</select>
					</div>
				</td>
				<td>
					<div class="form-group">
						<input ${isDisabled} type="text" disabled class="form-control local-unit-type" style="width: 120px" placeholder="UoM..." />
					</div>
				</td>
				<td>
					<div class="form-group">
						<select ${isDisabled} style="min-width: 150px" data-selected="${data.local_store_id != undefined ? data.local_store_id : 0}" name="items[local_store_id][]" class="form-control selected-store" data-location="local" placeholder="Select Store..." required>
							<option selected></option>
							@foreach (getUserStores(getCurrentUserLocation()->id) as $store)
								<option value="{{ $store->id }}" data-slots="{{ json_encode($store->slots) }}">{{ $store->name }}</option>
							@endforeach
						</select>
					</div>
				</td>
				<td>
					<div class="form-group">
						<select ${isDisabled} style="min-width: 150px" data-selected="${data.local_store_slot_id != undefined ? data.local_store_slot_id : 0}" data-location="local" name="items[local_slot_id][]" style="min-width: 100px" class="form-control store-slots" placeholder="Select Store First..." required><option></option></select>
					</div>
				</td>
				<td>
					<div class="form-group">
						<select ${isDisabled} style="min-width: 150px" data-selected="${data.local_state_id != undefined ? data.local_state_id : 0}" data-location="local" name="items[local_state_id][]" style="min-width: 100px" class="form-control local-states" placeholder="Select Local State..."><option></option></select>
					</div>
				</td>
				<td>
					<div class="form-group">
						<input ${isDisabled} style="min-width: 150px" step="any" type="number" class="form-control" name="items[local_quantity][]" placeholder="Quantity" readonly=true />
					</div>
				</td>
				<td>
					<div class="form-group">
						<select ${isDisabled} name="items[target_item_id][]" data-selected="${data.target_item_id != undefined ? data.target_item_id : 0}" style="min-width: 150px; font-size: 12px" class="form-control selected-item" data-type="target" placeholder="Select Item..." required>
							<option selected></option>
							@foreach (getInventoryItems(0, true, $transfer->location_id) as $item)
								<option value="{{ $item->id }}" data-material_type_id="{{ $item->material_type_id }}" data-unit_val="{{ $item->unit_price }}" data-text="{{ $item->name }}"
									data-unit-type="{{ $item->unit_type }}">{{ $item->code }} - {{ $item->name }}</option>
							@endforeach
						</select>
					</div>
				</td>
				<td>
					<div class="form-group">
						<input ${isDisabled} type="text" disabled class="form-control target-unit-type" style="width: 120px" placeholder="UoM..." />
					</div>
				</td>
				<td>
					<div class="form-group">
						<select ${isDisabled} style="min-width: 150px" data-selected="${data.target_store_id != undefined ? data.target_store_id : 0}" data-location="target" name="items[target_store_id][]" class="form-control selected-store" placeholder="Select Store..." required>
							<option selected></option>
							@foreach (getUserStores($transfer->location_id) as $store)
								<option value="{{ $store->id }}" data-slots="{{ json_encode($store->slots) }}">{{ $store->name }}</option>
							@endforeach
						</select>
					</div>
				</td>
				<td>
					<div class="form-group">
						<select ${isDisabled} style="min-width: 150px" data-selected="${data.target_store_slot_id != undefined ? data.target_store_slot_id : 0}" data-location="target" name="items[target_slot_id][]" class="form-control store-slots" placeholder="Select Store First..." required><option></option></select>
					</div>
				</td>
				<td>
					<div class="form-group">
						<select ${isDisabled} style="min-width: 150px" data-selected="${data.target_state_id != undefined ? data.target_state_id : 0}" data-location="local" name="items[target_state_id][]" style="min-width: 100px" class="form-control target-states" placeholder="Select Target State..."><option></option></select>
					</div>
				</td>
				<td>
					<div class="form-group">
						<input ${isDisabled} style="min-width: 150px" step="any" value="${data.target_quantity != undefined ? data.target_quantity : 0}" type="number" class="form-control" name="items[target_quantity][]" placeholder="Quantity" required />
					</div>
				</td>
				<td>
					<div class="form-group">
						<input ${isDisabled} style="min-width: 150px" value="${data.expiry != undefined ? data.expiry : ''}" type="date" class="form-control" name="items[expiry][]" placeholder="Expiry Date..." />
					</div>
				</td>
			</tr>
			`);
			return r.clone();
		}
		$(function(){
			var LOCATIONS = {"local": '{{ getCurrentUserLocation()->id }}', "target": '{{ $transfer->location_id }}'}
			var transferItems = $('#items-holder').data('items');

			$('.location-selector').on('change', function(){
				var val = $(this).val();
				$.ajax({
					url: '{{ route("stock-transfer-json") }}',
					dataType: 'json',
					data: {
						type: 'departments',
						location: val
					},
					beforeSend: function(){
						$('select.location-departments').html('<option>Loading...</option>').trigger('change');
					},
					success: function(js){
						$('select.location-departments').html('<option selected></option>').trigger('change');
						var selectedDef = $('select.location-departments').data('selected');
						$.each(js, function(j,s){
							$('select.location-departments').append(`<option value="${s.id}" ${ selectedDef == s.id ? 'selected' : '' }>${s.name}</option>`).trigger('change')
						});
					}
				});

			});

			$('select.location-selector').val($('select.location-selector').data('selected')).trigger('change');

			$('#items-holder').on('change', 'tr select.selected-store', function(){
				var selected = $(this).children('option:selected');
				var slots = selected.data('slots');
				var location = $(this).data('location');

				var slotDiv = $(this).parents('tr').find('[name="items['+location+'_slot_id][]"]');
				slotDiv.attr('placeholder', 'Select Slot...')
				slotDiv.html(`<option></option>`);
				var selectedSlot = slotDiv.data('selected')
				$.each(slots, function(i, s){
					var newOption = new Option(s.name, s.id, false, false);
					slotDiv.append(newOption).trigger('change');
				});
				slotDiv.val(selectedSlot).trigger('change');
			});

			var pull_material_type_states = function(material_type_id, typ, parentTr){
				$.ajax({
					url: '{{ route("get-material-type-states") }}',
					dataType: 'json',
					data: {
						material_type_id: material_type_id,
					},
					beforeSend: function(){
						parentTr.find('select.'+typ+'-states').html('<option>Loading...</option>').trigger('change');
					},
					success: function(js){
						parentTr.find('select.'+typ+'-states').html('<option selected></option>').trigger('change');
						var selectedDef = parentTr.find('select.'+typ+'-states').data('selected');
						$.each(js, function(j,s){
							parentTr.find('select.'+typ+'-states').append(`<option value="${s.id}" ${ selectedDef == s.id ? 'selected' : '' }>${s.name} (${s.uom})</option>`).trigger('change')
						});
					}
				});
			}

			$('#items-holder').on('change', 'tr select.selected-item', function(){
				var unit_type = $(this).children('option:selected').data('unit-type');
				var material_type_id = $(this).children('option:selected').data('material_type_id');
				var typ = $(this).data('type')
				var parentTr = $(this).parents('tr');


				parentTr.find('input.'+typ+'-unit-type').val(unit_type);

				pull_material_type_states(material_type_id, typ, parentTr);
			});

			$('#items-holder').on('change', 'tr select.store-slots:not([name="items[target_slot_id][]"])', function(){
				var val = $(this).val();
				var typ = $(this).data('location');
				var parentTr = $(this).parents('tr');
				var itemSel = parentTr.find('select[data-type="'+typ+'"]').children('option:selected').val();
				if($.trim(val) !== ""){
					$.ajax({
						url: '{{ route("stock-transfer-json") }}',
						dataType: 'json',
						data: {
							type: 'stock',
							location: LOCATIONS[typ],
							slot_id: val,
							item_id: itemSel
						},
						beforeSend: function(){
							parentTr.find('[name="items['+typ+'_quantity][]"]').val('');
						},
						success: function(js){
							console.log(js);
							parentTr.find('[name="items['+typ+'_quantity][]"]').val(js.available);
							parentTr.find('[name="items[target_quantity][]"]').attr('max', js.available);
						}
					})
				}
			});

			$('.save-form-btn').on('click', function(){
				var typ = $(this).data('type');

				if(typ == "transfer_items"){
					if(transferItems.length != $('#items-holder').find('tr').length){
						alert("It seems that you might have some un-saved items. Please save before proceeding with transfer.");
						return false;
					}
				}

				var missingVals = [];

				$('#items-holder').find('[required]').each(function(){
					var val = $(this).val();
					console.log($(this).attr('name'), val)
					if($.trim(val) == ""){
						var parentTD = $(this).parents('td');
						var titleTH = parentTD.parents('table').find('thead th').eq(parentTD.index());
						missingVals.push([titleTH.text()]);
						var borderStyle = $(this).css('border');
						$(this).css('border', '1px solid red').focus();
						var el = $(this);
						$(this).on('change', function(){
							el.css('border', borderStyle);
						});
					}
				});

				if(missingVals.length > 0){
					alert("Missing data: "+missingVals.join(","));
					return false;
				}

				$('#save-transfer-items').append(`<input type="hidden" name="${typ}" value="1" />`);

				$('#save-transfer-items').submit();
			});

			var itemRowCreator = function (data={}){
				var row = itemrow(data);
				row.find('select').select2();
				// $('#items-holder').parents('table').destroy();
				$('#items-holder').append(row);

				row.find('.remove-item-row').on('click', function(){
					if(confirm("Are you sure you want to remove this item?")){
						if(data.id){
							$.ajax({
								url: "{{ route('stock-transfer-item-delete') }}",
								dataType: 'json',
								method: 'POST',
								data: {
									transfer_item_id: data.id,
									'_token': "{{ csrf_token() }}"
								},
								success: function(js){
									if(js && js.status){
										row.remove();
									}
									else{
										alert("Couldn't remove item.")
									}
								}
							})
						}
						else{
							row.remove();
						}
					}
				});

				if(data.id){
					var t=0;
					row.find('select').each(function(){
						t+=100;
						var selected = $(this).data('selected');
						$(this).val(selected);
						var ths = $(this);
						window.setTimeout(function(){
							ths.trigger('change');
						}, t);
					});
				}

			}

			$('.add-item-row').on('click', function(){
				itemRowCreator();
			});

			$.each(transferItems, function(t, it){
				itemRowCreator(it);
			});

			if($('#items-holder').find('tr').length == 0){
				$('.add-item-row').trigger('click'); //create default row on page load
			}
		});
	</script>
@endsection