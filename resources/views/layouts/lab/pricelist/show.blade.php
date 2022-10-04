@extends('layouts.lab.layout.app', ['dataTable'=>true, 'select2'=>true])

@section('title2')
<title>{{ $pricelist->code }} - Pricelist | Inventory Departments</title>
@endsection
@section('content2')

<main>
	<?php
		$lab_storage_catgory_id = systemVariables('lab_samples_category_id');
		$pricelist_name = $pricelist->is_master == 1 ? $pricelist->currency()->name.' MASTER PRICELIST' : 'STANDARD '.$pricelist->currency()->name.' PRICELIST';
		$pricelist_name_code = space_underscore($pricelist_name."-".$pricelist->code, '_');
		$pricelist_file = '/storage/pricelist/'.$pricelist->pricelist_file;
		$expected_pricelist_file = '/storage/pricelist/'.$pricelist_name_code."-r".$pricelist->revision_number.".pdf";
	?>
	<?php
		$items = array(
			array(
				'link' => route('lab-home'),
				'name' => 'Lab Management',
				'icon' => null
			),
			array(
				'link' => route('view-pricelists'),
				'name' => 'Pricelists',
				'icon' => null
			),
			array(
				'link' => '#',
				'name' => $pricelist->code ?? 'Pricelist',
				'icon' => null
			)
		);
	?>
	<x-bread-crumb :items="$items"></x-bread-crumb>
	<h4 class="p-4">
		<i class="mdi mdi-currency-usd"></i> Pricelist - {{ $pricelist->code }}
		<span class="badge badge-pill bg-white my-small-text text-muted" style="font-weight: 400">
			<i class="mdi mdi-numeric-1-box"></i> Rev.{{ str_pad($pricelist->revision_number, 2,"0", STR_PAD_LEFT) }}
		</span>
		<button class="btn btn-default text-danger btn-sm float-right show-changes-btn" data-target="#save-price-changes-modal" data-toggle="modal"
			{{ $pricelist->status == 'has-changes' ? '' : 'disabled' }} id="commit-price-changes" ><i class="mdi mdi-content-save"></i> Commit Price Changes</button>
		<button class="btn btn-default text-primary btn-sm float-right" data-target="#create-pricelist-modal" data-toggle="modal"
			disabled id="create-pricelist" ><i class="mdi mdi-playlist-plus"></i> Pricelist</button>
		@if($pricelist->is_master !=1)
		<span class="btn btn-sm btn-default text-success float-right"
			data-target="#add-analysis-to-pricelist" data-toggle="modal"> <i class="mdi mdi-plus"></i> Item</span>
		@endif
	</h4>
	<div class="pl-4 pr-4">
		<a class="btn btn-default bg-light badge-pill btn-sm text-info" href="{{ route('show-pricelist', ['id'=>$pricelist->id, 'print'=>'print']) }}">
			<i class="mdi mdi-printer"></i> Print Pricelist
		</a>
		@if($pricelist_file == $expected_pricelist_file)
			<span class="btn btn-default bg-light badge-pill btn-sm text-success"
				data-target="#email-pricelist-pdf-modal" data-toggle="modal">
				<i class="mdi mdi-email-send"></i> Email Pricelist
			</span>
		@else
			<span class="btn btn-default bg-light badge-pill btn-sm text-danger"
				data-target="#upload-pricelist-pdf-modal" data-toggle="modal">
				<i class="mdi mdi-file-pdf"></i> Upload Pricelist <small class="text-muted">(*.pdf)</small>
			</span>
		@endif
	</div>
	<br>
	<div class="row no-gutters">
		<div class="col-sm-4 p-1">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title"><i class="mdi mdi-information-outline"></i> Pricelist Details</h5>
					<form method="POST" action="{{ route('update-pricelist', ['id'=>$pricelist->id]) }}" enctype="multipart/form-data">
						@csrf
						<div class="form-group">
							<label class="control-label">Description</label>
							<textarea class="form-control" name="description" placeholder="Description..." required>{{ $pricelist->description }}</textarea>
						</div>
						<div class="form-group">
							<label class="control-label">Currency*</label>
							<select name="currency_id" class="form-control" placeholder="Select Currency..." required>
								<option></option>
								@foreach (getCurrencies() as $p)
									<option value="{{ $p->id }}" {{ $pricelist->currency_id == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
								@endforeach
							</select>
						</div>
						<div class="form-group">
							<label class="control-label">Valid Until</label>
							<input type="date" name="valid_till" value="{{ $pricelist->valid_till }}" class="form-control" />
						</div>
						<div class="form-group">
							<label class="control-label">
								<input type="checkbox" name="is_master" value="1" {{ $pricelist->is_master == 1 ? 'checked' : '' }} /> Is Master Pricelist
							</label>
						</div>
						<div class="form-group">
							<label class="control-label">
								<input type="checkbox" name="active" value="1" {{ $pricelist->active == 1 ? 'checked' : '' }} /> Is Active
							</label>
						</div>
						<div class="form-group">
							<button class="btn btn-outline-success btn-block">
								<i class="mdi mdi-content-save"></i> Save
							</button>
						</div>
					</form>
				</div>
			</div>
		</div>
		<div class="col-sm-8 p-1">
			<div class="card tab-card">
        <div class="card-header tab-card-header">
          <ul class="nav nav-tabs card-header-tabs" id="sample-types-tabs" role="tablist">
            <li class="nav-item">
              <a class="nav-link active" id="pricelists-tab" data-toggle="tab" href="#save-transfer-items" role="tab" aria-controls="Pricelist-Items" aria-selected="true">Pricelist Items</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" id="pricelist-clients-tab" data-toggle="tab" href="#pricelist-clients" role="tab" aria-controls="InActive-Analysis" aria-selected="false">Customers</a>
						</li>
					</ul>
				</div>
				<div class="tab-content" id="sample-types-tabs">
          <div class="tab-pane show active p-3" id="save-transfer-items" role="tabpanel" aria-labelledby="one-tab">
            <h5 class="card-title"><i class="mdi mdi-clipboard-list"></i> Pricelist Items</h5>
            <div class="table-responsive">
							<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-">
								<thead>
									<tr>
										<th></th>
										<th>Analysis Code</th>
										<th>Category</th>
										<th>Analysis</th>
										<th>Cost Price</th>
										<th>Selling Price</th>
										<th class="{{ $pricelist->status == 'has-changes' ? '' : 'hidden' }} changes-trtd">Changes Price</th>
										<th>Profit Margin (%)</th>
										<th>VAT?</th>
										<th>Internal Use</th>
										<th>External View</th>
										<th>Active</th>
									</tr>
								</thead>
								<tbody id="items-holder" data-items="{{ $pricelist->items() }}"></tbody>
							</table>
						</div>
					</div>
          <div class="tab-pane fade p-3" id="pricelist-clients" role="tabpanel" aria-labelledby="one-tab">
            <h5 class="card-title">
							<i class="mdi mdi-account-group"></i> Customers
							<span class="btn btn-default btn-sm text-primary float-right" data-target="#add-pricelist-customers-modal" data-toggle="modal">
								<i class="mdi mdi-account-multiple-plus"></i> Customer
							</span>
						</h5>
            <div class="table-responsive">
							<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
								<thead>
									<tr>
										<th>#</th>
										<th>Code</th>
										<th>Name</th>
										<th>Email</th>
										<th>Website</th>
										<th>Phone</th>
										<th></th>
									</tr>
								</thead>
								<tbody>
									@foreach ($pricelist->customers() as $customer)
										<tr>
											<td>{{ $loop->iteration }}</td>
											<td>{{ $customer->code }}</td>
											<td>{{ $customer->name }}</td>
											<td>{{ $customer->email }}</td>
											<td>{{ $customer->website }}</td>
											<td>{{ $customer->phone }}</td>
											<td nowrap>
												<span class="btn btn-default btn-sm text-danger" data-item="{{ $customer->id }}"
													data-target="#remove-pricelist-customer-modal" data-toggle="modal">
													<i class="mdi mdi-delete"></i>
												</span>
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
	</div>
</main>
@endsection
@section('script2')
	<div id="remove-pricelist-customer-modal" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<!-- Modal content-->
			<form class="modal-content" method="POST" enctype="multipart/form-data">
				@csrf
				<div class="modal-header">
					<h4 class="modal-title"><i class="mdi mdi-delete"></i> Remove Customer</h4>
				</div>
				<div class="modal-body">
					<div class="alert alert-danger">
						<i class="mdi mdi-delete"></i> Are you sure you want to remove this customer from the pricelist?
					</div>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-danger"><i class="mdi mdi-delete"></i> Delete</button>
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				</div>
			</form>
		</div>
	</div>
	<div id="add-analysis-to-pricelist" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<!-- Modal content-->
			<div class="modal-content" method="POST" action="" enctype="multipart/form-data">
				@csrf
				<div class="modal-header">
					<h4 class="modal-title"></h4>
				</div>
				<div class="modal-body"></div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-primary add-item-row" data-dismiss="modal"><i class="mdi mdi-content-save"></i> Save</button>
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>
	<div id="add-pricelist-customers-modal" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<!-- Modal content-->
			<form class="modal-content" method="POST" action="{{ route('add-customer-to-pricelist', ['id'=>$pricelist->id]) }}" enctype="multipart/form-data">
				@csrf
				<div class="modal-header">
					<h4 class="modal-title"><i class="mdi mdi-account-group-plus"></i> Add Customers</h4>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<label class="control-label">Customers</label>
						<select class="form-control" name="customer_ids[]" multiple required placeholder="Select Customers...">
							<option></option>
							@foreach (getClients() as $item)
								<option value="{{ $item->id }}">{{ $item->name }}</option>
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
	<div id="email-pricelist-pdf-modal" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<!-- Modal content-->
			<form class="modal-content" method="POST" action="{{ route('email-pricelist-pdf', ['id'=>$pricelist->id]) }}" enctype="multipart/form-data">
				@csrf
				<div class="modal-header">
					<h4 class="modal-title"><i class="mdi mdi-account-group-plus"></i> Email Pricelist</h4>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<label class="control-label">Email Message</label>
						<textarea name="message" class="form-control" placeholder="Email Message..." ></textarea>
					</div>
					<div class="form-group">
						<label class="control-label">Customers</label>
						<br>
						@foreach ($pricelist->customers() as $cus)
							<span class="pt-1 pl-2 pb-1 pr-2">
								<input type="hidden" name="clients[{{ $loop->iteration }}][name]" value="{{ $cus->name }}" />
								<input type="checkbox" name="clients[{{ $loop->iteration }}][email]" value="{{ $cus->email }}" checked /> {{ $cus->name }}
							</span>
						@endforeach
					</div>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				</div>
			</form>
		</div>
	</div>
	<div id="save-price-changes-modal" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<!-- Modal content-->
			<form class="modal-content" method="POST" action="{{ route('save-price-changes', ['id'=>$pricelist->id]) }}" enctype="multipart/form-data">
				@csrf
				<div class="modal-header">
					<h4 class="modal-title"><i class="mdi mdi-content-save"></i> Save price changes</h4>
				</div>
				<div class="modal-body">
					<div class="alert alert-danger"><i class="fas fa-alert pull-left fa-2x"></i> Are you sure that you want to commit these price changes?</div>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-primary add-item-row"><i class="mdi mdi-content-save"></i> Save</button>
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				</div>
			</form>
		</div>
	</div>
	<div id="upload-pricelist-pdf-modal" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<!-- Modal content-->
			<form class="modal-content" method="POST" action="{{ route('upload-pricelist-pdf', ['id'=>$pricelist->id]) }}" enctype="multipart/form-data">
				@csrf
				<div class="modal-header">
					<h4 class="modal-title"><i class="mdi mdi-content-save"></i> Upload Pricelist PDF</h4>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<input type="hidden" name="name_code" value="{{ $pricelist_name_code }}" />
						<label class="control-label">Select Document</label>
						<input type="file" name="document" class="form-control" required />
					</div>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				</div>
			</form>
		</div>
	</div>
	<div id="create-pricelist-modal" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<!-- Modal content-->
			<form class="modal-content" method="POST" action="{{ route('clone-items-to-new-pricelist', ['id'=>$pricelist->id]) }}" enctype="multipart/form-data">
				@csrf
				<div class="modal-header">
					<h4 class="modal-title"><i class="mdi mdi-plus"></i> New Pricelist with selected items.</h4>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<label class="control-label">Description</label>
						<textarea class="form-control" name="description" placeholder="Description..." required></textarea>
					</div>
					<div class="form-group">
						<label class="control-label">Currency*</label>
						<select name="currency_id" class="form-control" placeholder="Select Currency..." required>
							<option></option>
							@foreach (getCurrencies() as $p)
								<option value="{{ $p->id }}">{{ $p->name }}</option>
							@endforeach
						</select>
					</div>
					<div class="form-group">
						<label class="control-label">Valid Until</label>
						<input type="date" name="valid_till"  class="form-control" />
					</div>
					<div class="form-group">
						<label class="control-label">
							<input type="checkbox" name="active" value="1" /> Is Active
						</label>
					</div>
					<div class="hidden-fields hidden"></div>
					<div class="footnote text-danger p-1"><i class="mdi mdi-alert pull-left fa-2x"></i> Are you sure that you want to create a new pricelist with the items selected?</div>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-primary add-item-row"><i class="mdi mdi-content-save"></i> Save</button>
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				</div>
			</form>
		</div>
	</div>
	<script>
		var itemForm = function(data={}){
			var formBody = $(`
				@csrf
				<div class="form-group">
					<label class="control-label text-sm">Sample Type</label>
					<select class="form-control" name="sample_type_id" required placeholder="Select Sample Type...">
						<option></option>
						@foreach (getSampleTypes() as $sample)
							<option value="{{ $sample->id }}"  ${data.sample_type_id == {{ $sample->id }} ? 'selected' :''}>{{ $sample->name }}</option>
						@endforeach
					</select>
				</div>
				<div class="form-group">
					<label class="control-label text-sm">Analysis Type</label>
					<select class="form-control" data-selected="${data.analysis_type_id}" name="analysis_type_id" required placeholder="Select Sample Type First..."><option></option></select>
				</div>
				<div class="form-group">
					<label class="control-label text-sm">Cost Price</label>
					<input type="number" step="any" min="0" class="form-control" value="${data.cost_price}" name="cost_price" placeholder="Cost Price..." required />
				</div>
				<div class="form-group">
					<label class="control-label text-sm">Selling Price</label>
					<input type="number" step="any" min="0" class="form-control" value="${data.selling_price}" placeholder="Selling Price..." name="selling_price" required />
				</div>
				<div class="form-group">
					<label class="control-label text-sm">
						<input type="checkbox" name="vat" ${data.vat == 1 ? 'checked' : ''} > Has VAT?
					</label>
				</div>
				<div class="form-group">
					<label class="control-label text-sm">
						<input type="checkbox" name="internal_use" ${data.internal_use == 1 ? 'checked' : ''} > Viewable Internally?
					</label>
				</div>
				<div class="form-group">
					<label class="control-label text-sm">
						<input type="checkbox" name="external_view" ${data.external_view == 1 ? 'checked' : ''} > Viewable Externally?
					</label>
				</div>
				<div class="form-group">
					<label class="control-label text-sm">
						<input type="checkbox" name="active" ${data.active == 1 ? 'checked' : ''} > Is Active?
					</label>
				</div>
			`);
			return formBody.clone();
		}

		var itemRow = function(data={}){
			var strData = JSON.stringify(data);
			var $row = $(`
				<tr data-element="${data.id}">
					<td nowrap>
						<input type="checkbox" class="row-checkbox" name="item_id[]" ${data.id ? '' : 'disabled'} value="${data.id}" />
						<span style="cursor: pointer">
							<i class="mdi mdi-arrow-up-drop-circle move-analyte-up move-analyte text-success" data-action="move-up"></i>
						</span>
						<span style="cursor: pointer">
							<i class="mdi mdi-arrow-down-drop-circle move-analyte-down move-analyte text-success" data-action="move-down"></i>
						</span>
						<span class="pl-1" style="cursor: pointer" data-target="#add-analysis-to-pricelist" data-toggle="modal" data-item='${strData}'>
							<i class="mdi mdi-pencil-outline text-primary" data-action="move-down"></i>
						</span>
					</td>
					<td nowrap>
						${data.analysis_type_code}
					</td>
					
					<td nowrap>
						${data.sample_type_name}
					</td>
					<td nowrap>
						${data.analysis_type_name}
					</td>
					<td nowrap>
						{{ $pricelist->currency_name }} ${data.cost_price ? parseFloat(data.cost_price).toLocaleString() : 0}
					</td>
					<td nowrap>
						{{ $pricelist->currency_name }} ${data.selling_price ? parseFloat(data.selling_price).toLocaleString() : 0}
					</td>
					<td class="{{ $pricelist->status == 'has-changes' ? '' : 'hidden' }} changes-trtd" nowrap style="color:${data.changed_price != data.selling_price ? '#b90000; font-weight: 550' : 'inherit'}">
						{{ $pricelist->currency_name }} ${data.changed_price ? parseFloat(data.changed_price).toLocaleString() : 0}
					</td>
					<td nowrap>
						${(data.selling_price && data.cost_price) ? ((parseFloat(data.selling_price-data.cost_price)/parseFloat(data.selling_price)) * 100).toFixed(2) : 0} %
					</td>
					<td>
						<i class="mdi mdi-marker-check ${data.vat == 1 ? 'text-success' : 'text-muted'}"></i>
					</td>
					<td nowrap>
						<i class="mdi mdi-marker-check ${data.internal_use == 1 ? 'text-success' : 'text-muted'}"></i>
					</td>
					<td nowrap>
						<i class="mdi mdi-marker-check ${data.external_view == 1 ? 'text-success' : 'text-muted'}"></i>
					</td>
					<td nowrap>
						<i class="mdi mdi-marker-check ${data.active == 1 ? 'text-success' : 'text-muted'}"></i>
					</td>
				</tr>
			`);

			return $row.clone();
		};

		var configureThemArrow = function(){
			$('#items-holder').find('tr').find('.move-analyte-up').addClass('text-success').removeClass('text-muted');
			$('#items-holder').find('tr:first-child').find('.move-analyte-up').addClass('text-muted').removeClass('text-success');

			$('#items-holder').find('tr').find('.move-analyte-down').addClass('text-success').removeClass('text-muted');
			$('#items-holder').find('tr:last-child').find('.move-analyte-down').addClass('text-muted').removeClass('text-success');
		}

		var generateDataObject = function($form){
			var arrOBJ = {};

			$form.find('select, input').each(function(){
				var nm = $(this).attr('name');

				var val = $(this).val();

				if($(this).attr('type') == "checkbox"){
					if($(this).is(":checked")){
						val = 1;
					}
					else{
						val = 0;
					}
				}

				arrOBJ[nm] = val;
			});

			return arrOBJ;
		}

		$(function(){
			var generateRow = function(data={}){
				var $row = itemRow(data);
				$('#items-holder').append($row);

				configureThemArrow();
			}

			$('#items-holder').on('change', '.row-checkbox', function(){
				var checkd = $('#items-holder').find('.row-checkbox:checked').length;
				if(checkd > 0){
					$('#create-pricelist').removeAttr('disabled');
				}
				else{
					$('#create-pricelist').attr('disabled', true);
				}
			});

			$('.add-item-row').on('click', function(){
				var $formBody = $("#add-analysis-to-pricelist").find('.modal-body');
				var data = generateDataObject($formBody);
				var url = "{{ route('update-pricelist-item', ['id'=>$pricelist->id]) }}";
				$.ajax({
					url: url,
					method: 'post',
					data: data,
					dataType: 'json',
					success: function(js){
						if(js.status){
							generateAvailableItems(js.items)
						}
					}
				});
			});

			$('#create-pricelist-modal').on('show.bs.modal', function(){
				var checkd = $('#items-holder').find('.row-checkbox:checked').clone();
				$(this).find('.hidden-fields').html(checkd);
			});

			$('#remove-pricelist-customer-modal').on('show.bs.modal', function(e){
				var itemID = $(e.relatedTarget).data('item');
				$(this).find('form').attr('action', '/remove-customer-to-pricelist/'+itemID);
			});

			$("#add-analysis-to-pricelist").find('.modal-body').on('change', '[name="sample_type_id"]', function(){
				var url = '/analysis-types/'+$(this).val();
				var $formBody = $("#add-analysis-to-pricelist").find('.modal-body');
				var $analysisType = $("#add-analysis-to-pricelist").find('.modal-body [name="analysis_type_id"]');
				var selected = $analysisType.data('selected');

				$.ajax({
					url: url,
					dataType: 'json',
					beforeSend: function(){
						$analysisType.attr('placeholder', 'Select Analysis Type...');
						$analysisType.html('<option></option>').trigger('change');
					},
					success: function(js){
						$.each(js, function(j,s){
							console.log(s.id, selected);
							$analysisType.append(`<option value="${s.id}" ${s.id == selected ? 'selected' : ''}>${s.name}</option>`);
						});
						$analysisType.val(selected);
						$analysisType.trigger('change');
					}
				});
			});

			$("#add-analysis-to-pricelist").on('show.bs.modal', function(e){
				var data = $(e.relatedTarget).data('item') || {};

				if(data && data.id){
					$("#add-analysis-to-pricelist").find('.modal-title').html('<i class="mdi mdi-pencil-outline"></i> Edit Pricelist Item');
				}
				else{
					$("#add-analysis-to-pricelist").find('.modal-title').html('<i class="mdi mdi-plus"></i> Add Pricelist Item');
				}
				var $form = itemForm(data);
				$("#add-analysis-to-pricelist").find('.modal-body').html($form);
				$("#add-analysis-to-pricelist").find('.modal-body [name="sample_type_id"]').trigger('change');

				$form.find('select').select2();
				$form.append(`<input type="hidden" name="pricelist_item_id" value="${data.id || -1}" />`);
			})

			var generateAvailableItems = function($newData=false){
				var availableItems = $newData ? $newData : $('#items-holder').data('items');
				$('#items-holder').empty();
				var showChanges = false;
				$.each(availableItems, function(a, data){
					generateRow(data);
					if(data.selling_price != data.changed_price){
						showChanges = true;
					}
				});

				if(showChanges){
					$('table').find('.changes-trtd').removeClass('hidden');
					$('.show-changes-btn').removeAttr('disabled');
				}
			}

			generateAvailableItems();

			// $('.add-item-row').on('click', function(){
			// 	generateRow()
			// });

			configureThemArrow();

			$('#items-holder').on('click', '.move-analyte:not(.text-muted)', function(){
				var action = $(this).data('action');
				var pTR = $(this).parents('tr');
				var i = pTR.index();
				var $element = pTR.data('element');

				if(!$element){
					return false;
				}

				$.ajax({
					url: "/move-pricelist-item/"+action+"/{{ $pricelist->id }}/"+$element,
					dataType: 'json',
					beforeSend: function(){
						$('#items-holder').find('tr').find('.move-analyte-up').addClass('text-muted');
						$('#items-holder').find('tr').find('.move-analyte-down').addClass('text-muted');
					},
					success: function(js){
						var siblingIndex = action == 'move-up' ? (i-1) : (i+1);
						siblingIndex = siblingIndex < 0 ? 0 : siblingIndex;

						var sibling = $('#items-holder').find('tr').get(siblingIndex);

						if(action == 'move-up'){
							$(sibling).before(pTR);
						}
						else{
							$(pTR).before(sibling);
						}

						configureThemArrow();
					}
				})
			});

		});
	</script>
@endsection