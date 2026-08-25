@extends('layouts.inventory.layout.app', ['dataTable'=>true, 'select2'=>true])

@section('title2')
<title>{{ $transfer->code ?? 'New Transfer' }} - Stock Transfer | Inventory Departments</title>
<style type="text/css">
	th.bg-light{
		background-color: rgb(233, 233, 255) !important;
	}
	th.bg-dark{
		background-color: rgb(234, 245, 227) !important;
	}
</style>
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
		@if(($transfer->inventory_location_id ?? $transfer->location_id) && $transfer->status != "Completed")
			<button class="btn btn-default text-success btn-sm float-right save-form-btn" data-type="save_items"><i class="mdi mdi-content-save"></i> Save</button>
			@if ($transfer->status == "Transfer Items Updated")
				<button class="btn btn-default text-danger btn-sm float-right save-form-btn" data-type="transfer_items"><i class="mdi mdi-bank-transfer-out"></i> Transfer Items</button>
			@endif
		@endif
	</h4>
	<br>
	<div class="row">
		<div class="col-sm-12">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title"><i class="mdi mdi-information-outline"></i> Transfer Details
						<small class="text-info toggle-desc float-right {{ !isset($transfer->status) ? 'text' : '' }}"><i class="mdi mdi-pencil"></i> Edit</small>
					</h5>
					<hr>
					<form method="POST" action="{{ route('stock-transfer-update', ['id'=>$transfer->id ?? 'new']) }}" enctype="multipart/form-data">
						@csrf
						<div class="form-group">
							<div id="wyswyg-desc-text" class="text-view">{!! $transfer->description !!}</div>
							<div id="parent-wyswyg" class="text-view">
								<textarea id="wyswyg-desc" class="form-control" name="description" placeholder="Description..." required>{{ $transfer->description }}</textarea>
								<br>
								<button class="btn btn-outline-success btn-block" {!! isset($transfer->status) && in_array($transfer->status, array("Completed", "Transfer Items Updated")) ? 'disabled' : ' onclick="tinyMCE.triggerSave()"' !!}>
									<i class="mdi mdi-content-save"></i> Save
								</button>
							</div>
						</div>
					</form>
				</div>
			</div>
			<br>
		</div>
		<div class="col-sm-12">
			<form class="card" method="POST" id="save-transfer-items" action="{{ route('stock-transfer-items-update', ['id'=>$transfer->id ?? 'new']) }}">
				@csrf
				<div class="card-body">
					<h5 class="card-title"><i class="mdi mdi-package-variant"></i> Transfer Items
						@if(($transfer->inventory_location_id ?? $transfer->location_id) && $transfer->status != "Completed")
							<span class="btn btn-sm btn-default text-info float-right add-item-row"><i class="mdi mdi-plus"></i> Row</span>
						@endif
					</h5>
					<hr>
					<div class="table-responsive">
						<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-">
							<thead>
								<tr>
									<th></th>
									<th colspan="6" class="bg-light">Source</th>
									<th colspan="4" class="bg-dark">Destination</th>
									<th colspan="3"></th>
								</tr>
								<tr>
									<th></th>
									<th class="bg-light">Item</th>
									<th class="bg-light">Store</th>
									<th class="bg-light">Slot</th>
									<th class="bg-light">Lot Number</th>
									<th class="bg-light">Available Quantity</th>
									<th class="bg-light">UoM</th>
									<th class="bg-dark">Item</th>
									<th class="bg-dark">Store</th>
									<th class="bg-dark">Slot</th>
									<th class="bg-dark">Lot Number</th>
									<th>Quantity to Transfer</th>
									<th>UoM</th>
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
	<script type="text/javascript" src="/tinymce/tinymce.min.js"></script>
	<script>
		function randomIntFromInterval(min, max) { // min and max included
			return Math.floor(Math.random() * (max - min + 1) + min)
		}

		var itemrow = function(data = {}){
			var isDisabled = "{{ ($transfer->inventory_location_id ?? $transfer->location_id) && $transfer->status != 'Completed' ? '' : 'disabled' }}";

			var dataID = data.id ?? randomIntFromInterval(2345123213, 4545123213);
			var rowww = $(`
			<tr>
				<td>
					@if(($transfer->inventory_location_id ?? $transfer->location_id) && $transfer->status != "Completed")
						<span class="btn btn-default btn-sm text-danger remove-item-row" data-transfer-id="${data.id}">
							<i class="mdi mdi-delete"></i>
						</span>
					@endif
					<input type="hidden" name="items[transfer_item_id][${dataID}]" value="${data.id != undefined ? data.id : dataID}" />
				</td>
				<td style="width: 300px">
					<select data-type="source" ${isDisabled} name="items[source_sub_category_id][${dataID}]" style="width: 100%; font-size: 12px" class="form-control selected-item" data-placeholder="Select Item..." required>
						${ data.local_item_id ? '<option value="'+data.local_item_id+'" selected="selected">'+data.local_inventory_item_name+'</option>' : ''}
					</select>
					<input type="hidden" data-type="source" class="sub_category_name" name="items[source_sub_category_name][${dataID}]" />
				</td>
				<td>
					<select data-type="source" ${isDisabled} name="items[source_store_id][${dataID}]" class="form-control selected-store" data-placeholder="Select Store..." style="min-width: 130px">
						<option value="">Select Store...</option>
						@foreach (getUserStores(false, true) as $store)
							<option value="{{ $store->id }}" ${ {{ $store->id }} == data.local_store_id ? 'selected' : '' } data-slots="{{ json_encode($store->slots) }}">{{ $store->name }}</option>
						@endforeach
					</select>
				</td>
				<td>
					<select data-type="source" ${isDisabled} name="items[source_slot_id][${dataID}]" style="min-width: 100px" class="form-control slot_id" data-placeholder="Select Slot..."></select>
				</td>
				<td>
					<input type="text" ${isDisabled} class="form-control" style="min-width: 200px; font-size: 12px" value="${data.local_lot_no || ''}" name="items[source_lot_number][${dataID}]" data-type="source" placeholder="Lot Number..." />
				</td>
				<td>
					<input type="number" class="form-control available-quantity" name="items[source_available_quantity][${dataID}]" data-type="source" disabled="true" />
				</td>
				<td>
					<select data-type="source" ${isDisabled} name="items[source_uom][${dataID}]" style="min-width: 200px" class="uom form-control" data-placeholder="Select UoM..."></select>
				</td>
				<td style="width: 300px">
					<select data-type="target" ${isDisabled} name="items[target_sub_category_id][${dataID}]" style="width: 100%; font-size: 12px" class="form-control selected-item" data-placeholder="Select Item..." required>
						${ data.target_item_id ? '<option value="'+data.target_item_id+'" selected="selected">'+data.target_inventory_item_name+'</option>' : ''}
					</select>
					<input type="hidden" data-type="target" class="sub_category_name" name="items[target_sub_category_name][${dataID}]" />
				</td>
				<td>
					<select data-type="target" ${isDisabled} name="items[target_store_id][${dataID}]" class="form-control selected-store" data-placeholder="Select Store..." style="min-width: 130px">
						<option value="">Select Store...</option>
						@foreach (getUserStores(false, true) as $store)
							<option value="{{ $store->id }}" ${ {{ $store->id }} == data.target_store_id ? 'selected' : '' } data-slots="{{ json_encode($store->slots) }}">{{ $store->name }}</option>
						@endforeach
					</select>
				</td>
				<td>
					<select data-type="target" ${isDisabled} name="items[target_slot_id][${dataID}]" style="min-width: 100px" class="form-control slot_id" data-placeholder="Select Slot..."></select>
				</td>
				<td>
					<input type="text" ${isDisabled} class="form-control" style="min-width: 200px; font-size: 12px" value="${data.target_lot_no || ''}" name="items[target_lot_number][${dataID}]" data-type="target" placeholder="Lot Number..." />
				</td>
				<td>
					<input type="number" ${isDisabled} class="form-control transfer-quantity" value="${data.target_quantity}" name="items[transfer_quantity][${dataID}]" data-type="target" placeholder="Transfer Quantity" />
				</td>
				<td>
					<select data-type="target" ${isDisabled} name="items[transfer_uom][${dataID}]" style="width: 200px" class="uom form-control" data-placeholder="Select UoM..."></select>
				</td>
				<td>
					<input type="date" ${isDisabled} class="form-control" value="${data.expiry}" name="items[expiry][${dataID}]" data-type="target" placeholder="Expiry Date..." />
				</td>
			</tr>
			`);

			var $row = rowww.clone();

			$row.find('select').not('.selected-item').select2();

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

			// var hasAlreadyBeenClicked = [];

			$row.on('change', '.selected-item', function(){
				var itemID = $(this).val();
				var typ = $(this).data('type');

				$row.find('.sub_category_name[data-type="'+typ+'"]').val($(this).children('option:selected').text());

				var $this = $(this);
				$.ajax({
					url: '/get_item_details/'+itemID,
					beforeSend: function(){

					},
					success: function(js){
						var uonSel = $row.find('.uom[data-type="'+typ+'"]');
						uonSel.html(`<option value="${js.uom}" selected>${js.uom}</option>`);
						if($.trim(js.uom2)!= ""){
							uonSel.append(`<option value="${js.uom2}">${js.uom2}</option>`);
						}

						uonSel.trigger('change');

						if(typ == "source"){
							$row.find('.transfer-quantity').attr('max', js.available);
							$row.find('.available-quantity[data-type="'+typ+'"]').val(js.available);
						}
					}
				});
			});

			$row.find('.selected-store').on('change', function(){
				var selected = $(this).children('option:selected');
				var slots = selected.data('slots');
				var typ = $(this).data('type');

				var slotDiv = $row.find('.slot_id[data-type="'+typ+'"]');
				var defaultSlotValue = typ == "target" ? data.target_store_slot_id : data.local_store_slot_id;
				slotDiv.empty();
				$.each(slots, function(i, s){
					var newOption = new Option(s.name, s.id, false, false);
					slotDiv.append(newOption).trigger('change');
				});

				slotDiv.val(defaultSlotValue || '').trigger('change');
			});


			$row.find('.selected-store').trigger('change');

			$row.find('.remove-item-row').on('click', function(){
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
									$row.remove();
								}
								else{
									alert("Couldn't remove item.");
								}
							}
						})
					}
					else{
						$row.remove();
					}
				}
			});

			$row.find('.selected-item').trigger('change');
			return $row;
		}

		$(function(){
			var LOCATIONS = {"local": '{{ getCurrentUserLocation()?->id }}', "target": '{{ $transfer->inventory_location_id ?? $transfer->location_id }}'}
			var transferItems = $('#items-holder').data('items');
			var editorInstance;
			$('.toggle-desc').on('click', function(){
				$(this).toggleClass('text');
				$('.text-view').slideUp(0);

				$($(this).hasClass('text') ? "#wyswyg-desc-text" : '#parent-wyswyg').slideDown(200);

				if(!$(this).hasClass('text')){
					editorInstance = tinymce.init({
						selector: "#wyswyg-desc"
					});
				}
				else{
					if(editorInstance){
						tinymce.remove("#wyswyg-desc");
					}
				}
			});

			$('.toggle-desc').trigger('click');

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
				// $('#items-holder').parents('table').destroy();
				$('#items-holder').append(row);

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