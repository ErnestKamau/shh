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
			'link' => route('supplier-dashboard-home'),
          'name' => 'Dashboard',
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
			<i class="mdi mdi-mdi-user"></i> {{ $supplier->name }} <small class="badge {{ $supplier->average_rating() < 6 ? 'badge-warning' : 'badge-success' }}">{{ $supplier->average_rating() }}<i class="mdi mdi-star"></i> </small> <small class="text-muted"> | Suppliers</small>
		</h2>
		<div class="row mb-3">
        <div class="col-xl-4 col-sm-6 ">
            <div class="card  bg-success text-white text-center  no-overflow" style="height:100%">
                <div class="card-body bg-success">
                    <div class="rotate">
                        <i class="mdi mdi-test-tube fa-4x"></i>
                    </div>
                    <h6 class="text-uppercase">Request Of Quotations11</h6>
                    <br><br>
                    <h1 class="display-4">{{ $rfqs->count() }}</h1>
                </div>
            </div>
        </div>


        <div class="col-xl-4 col-sm-6">
            <div class="card bg-danger text-white text-center h-100 no-overflow">
                <div class="card-body bg-danger">
                    <div class="rotate">
                        <i class="fas fa-list fa-4x"></i>
                    </div>
                    <h6 class="text-uppercase">Local Purchase Orders</h6>
                    <br><br>
                    <h1 class="display-4">{{ $lpos_awarded->count() }}</h1>
                </div>
            </div>
        </div>


        <div class="col-xl-3 col-sm-6">
            <div class="card bg-info text-white text-center h-100 no-overflow">
                <div class="card-body bg-info">
                    <div class="rotate">
                        <i class="mdi mdi-test-tube fa-4x"></i>
                    </div>
                    <h6 class="text-uppercase">Goods Receipt</h6>
                    <br><br>
                    <h1 class="display-4">{{$goods_receipt->count() }}</h1>
                </div>
            </div>
        </div>


       
    </div>
		
				<div class="card tab-card">
					<div class="card-header tab-card-header">
						<ul class="nav nav-tabs card-header-tabs" id="Categories-tabs" role="tablist">
							<li class="nav-item">
								<a class="nav-link active" id="Activity-tab" data-toggle="tab" href="#Categories" role="tab" aria-controls="Activity" aria-selected="true"><i class="mdi mdi-star-box-multiple"></i> Request of Quotation</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" id="Categories-tab" data-toggle="tab" href="#Activity" role="tab" aria-controls="Categories" aria-selected="true"><i class="mdi mdi-star-box"></i> Local Purchase Order</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" id="Orders-tab" data-toggle="tab" href="#Orders" role="tab" aria-controls="Orders" aria-selected="true"><i class="mdi mdi-sticker-check"></i> Goods Receipt</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" id="Ratings-tab" data-toggle="tab" href="#Ratings" role="tab" aria-controls="Ratings" aria-selected="true"><i class="mdi mdi-account-heart"></i> Ratings</a>
							</li>
						</ul>
					</div>
					<div class="tab-content" id="Orders-tabs-content">
						<div class="tab-pane fade p-3" id="Ratings" role="tabpanel" aria-labelledby="one-tab">
							<h5 class="card-title"><i class="mdi mdi-account-heart"></i> Ratings </h5>
							<div class="table-responsive">
								<table
									class="table table-condensed my-small-text table-striped  table-hover table-bordered table-sm">
									<thead class="bg-light p-2">
										<tr>
											<th>No</th>
											<th>Inventory Item</th>
											<th>Date</th>
											<th>Rating</th>
											<th>Title</th>
											<th>Comments</th>
											<th>Rated By</th>
											<th></th>
										</tr>
									</thead>
									<tbody>
										@foreach ($supplier->ratings as $item)
											<tr>
												<td>{{ $loop->iteration }}</td>
												<td>{{ $item->inventory_item->batch_code ?? '' }}</td>
												<td>{{ $item->inventory_item->created_at ?? '' }}</td>
												<td>{{ $item->rating }}</td>
												<td>{{ $item->title }}</td>
												<td>{{ $item->comments }}</td>
												<td>{{ $item->creator->name }}</td>
												<td></td>
											</tr>
										@endforeach
									</tbody>
								</table>
							</div>
						</div>
						<div class="tab-pane fade p-3" id="Orders" role="tabpanel" aria-labelledby="one-tab">
							<h5 class="card-title"><i class="mdi mdi-sticker-check"></i> Goods Receipts </h5>
							<div class="table-responsive">
								<table id="" class="table table-condensed my-small-text table-striped server-side table-hover table-bordered table-sm">
									
									<thead class="bg-light p-2">
										<tr>
											<th>No</th>
											<th>Priority</th>
											<th>Request Code</th>
											<th>Due Date</th>
											<th>Parent Request</th>
											<th>Request Type</th>
											<th>Description</th>
											<th>Net Value</th>
											<th>Submission Deadline</th>
											<th>Status</th>											
											<th>Created On</th>
											
										</tr>
									</thead>
									<tbody>
										@foreach($goods_receipt as $good)
										<tr>
											<td>{{$loop->iteration}}</td>
											<td>{{$good->status}}</td>
											<td>{{$good->request_code}}</td>
											<td>{{$good->due_date}}</td>
											<td>{{$good->parent_request}}</td>
											<td>{{$good->request_type}}</td>
											<td>{{$good->description}}</td>
											<td>{{$good->net_value}}</td>
											<td>{{$good->submission_deadline}}</td>
											<td>{{$good->status}}</td>
											<td>{{$good->created_at}}</td>
										</tr>
										@endforeach
									</tbody>
								</table>
								@if(count($goods_receipt) == 0)
									<div class="alert alert-info">
										<i class="mdi mdi-alert"></i> No Orders added yet.
									</div>
								@endif
							</div>
						</div>
						<div class="tab-pane fade show active p-3" id="Categories" role="tabpanel" aria-labelledby="one-tab">
							<h5 class="card-title"><i class="mdi mdi-star-box-multiple"></i> Request Of Quotations</h5>
							<div class="table-responsive">
							<table class="table table-condensed my-small-text table-striped server-side table-hover table-bordered table-sm">
									
									<thead class="bg-light p-2">
										<tr>
											<th>No</th>
											<th>Priority</th>
											<th>Request Code</th>
											<th>Due Date</th>
											<th>Parent Request</th>
											<th>Request Type</th>
											<th>Description</th>
											<th>Net Value</th>
											<th>Submission Deadline</th>
											<th>Status</th>											
											<th>Created On</th>
											
										</tr>
									</thead>
									<tbody>
										@foreach($rfqs as $rfq)
										<tr>
											<td>{{$loop->iteration}}</td>
											<td>
                                                {!! $rfq->priority == 'high' ? '<i style="color:red;" class="mdi mdi-star-four-points"></i><span style="color: red;">high</span>':$rfq->priority !!}
                                            </td>
											<td>
												<a href="{{ route('get-rfqs-item',['id'=>$rfq->id]) }}">{{$rfq->request_code}}</a>
											</td>

											<td>{{$rfq->due_date}}</td>
											<td>{{$rfq->parent_request}}</td>
											<td>{{$rfq->request_type}}</td>
											<td>{{$rfq->description}}</td>
											<td>{{$rfq->net_value}}</td>
											<td>{{$rfq->submission_deadline}}</td>
											<td>{{$rfq->status}}</td>
											<td>{{$rfq->created_at}}</td>
										</tr>
										@endforeach
									</tbody>
								</table>
								
							</div>
						</div>
						<div class="tab-pane fade p-3" id="Activity" role="tabpanel" aria-labelledby="one-tab">
							<h5 class="card-title"><i class="mdi mdi-star-box"></i> Local Purchase Order </h5>
							<div class="table-responsive">
								<table
									class="table table-condensed my-small-text table-striped server-side table-hover table-bordered table-sm">
									<thead class="bg-light p-2">
										<tr>
											<th>No</th>
											<th>RFQS</th>
											<th>Store</th>
											<th>Slot</th>
                                            <th>Quote Amount</th>
                                            <th>Awarded At</th>	
											<th>Created at</th>
										</tr>
									</thead>
									<tbody>
										
											@foreach($lpos_awarded as $item)
	  											<tr>
													<td>{{$loop->iteration}}</td>
													<?php
                                                    $rfq = getRfqsById($item->request_id);
                                                    $request_item = getRfqsItemById($item->request_item_id);
													$store = getStoreById($request_item->store_id);
													$slot = getSlotById($request_item->slot_id);
													
													
													?>
													<td>{{$rfq->request_code}}</td>
													<td>{{$store->name}}</td>
													<td>{{$slot->name}}</td>
													
													
													<td>{{$item->quote_amount}}</td>
													<td>{{$item->awarded_at}}</td>
													<td>{{$item->created_at}}</td>
																					
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

<script>
	var rowHTML = `<div class="row dynamic-row" style="margin-top: 12px; position: relative">
		<div class="col-sm-7">
			<input type="hidden" name="items[order_item_id][]" />
			<label class="control-label">Category <small class="fulfilled-msg text-info"></small></label>
			<select class="form-control" name="items[sub_category_id][]" required>
				<option value="">Select Category...</option>
				@foreach ($cats as $item)
					<optgroup label="{{ $item->name }}">
						@foreach ($item->subcategories as $it)
							<option value="{{ $item->id }} {{ $it->id }}"><em>{{ $item->name }}</em> > {{ $it->name }}</option>
						@endforeach
					</optgroup>
				@endforeach
			</select>
		</div>
		<div class="col-sm-5">
			<label class="control-label">Quantity <small class="fulfilled-msg text-info"></small></label>
			<input type="number" min="0" class="form-control" name="items[quantity][]" placeholder="Enter Quantity" required />
		</div>
		<span class="removeThis"><i class="mdi mdi-delete"></i></span>
	</div>`;

	var rowReceivedHTML = `<div class="row dynamic-row" style="margin-top: 12px; position: relative">
		<div class="col-sm-4">
			<input type="hidden" name="items[order_item_id][]" />
			<label class="control-label">Category <small class="fulfilled-msg text-info"></small></label>
			<select class="form-control" name="items[sub_category_id][]" required>
				<option value="">Select Category...</option>
				@foreach ($cats as $item)
					<optgroup label="{{ $item->name }}">
						@foreach ($item->subcategories as $it)
							<option value="{{ $item->id }} {{ $it->id }}"><em>{{ $item->name }}</em> > {{ $it->name }}</option>
						@endforeach
					</optgroup>
				@endforeach
			</select>
		</div>
		<div class="col-sm-4">
			<label class="control-label">Quantity <small class="fulfilled-msg text-info"></small></label>
			<input type="number" min="0" class="form-control" name="items[quantity][]" placeholder="Enter Quantity" required />
		</div>
		<div class="col-sm-4">
			<label class="control-label">Price <small class="fulfilled-msg text-info"></small></label>
			<input type="number" min="0" class="form-control" name="items[price][]" placeholder="Enter Price" required />
		</div>
		<div class="col-sm-4">
			<label class="control-label">Expiry <small class="fulfilled-msg text-info"></small></label>
			<input type="date" min="0" value="2099-12-31" class="form-control" name="items[expiry][]" placeholder="Expiry date..." required />
		</div>
		<div class="col-sm-4">
			<label class="control-label">Slot <small class="fulfilled-msg text-info"></small></label>
			<select class="form-control" name="items[slot][]" required placeholder="Select Slot" required>
				<option></option>
				@foreach ($stores as $item)
					<optgroup label="{{ $item->name }}">
						@foreach ($item->slots as $it)
							<option value="{{ $item->id }} {{ $it->id }}">{{ $it->name }}</option>
						@endforeach
					</optgroup>
				@endforeach
			</select>
		</div>
		<div class="col-sm-2 checkbox-holder pt-1">
			<label class="control-label">Receive </label><br>
			<input type="hidden" name="items[receive][]" value="0">
			<input type="checkbox" class="items-receive">
		</div>
		<div class="col-sm-2 qc-holder pt-1">
			<label class="control-label" title="Requires Quality Control">Quality Control</label><br>
			<input type="hidden" name="items[requires_qc][]" value="0">
			<input type="checkbox" class="items-requires_qc">
		</div>
	</div>`;

	$(function(){
		//triggered when edit-an-order modal is about to be shown
		$('#accept-goods').on('show.bs.modal', function(e) {

			//get data-id attribute of the clicked element
			var orderDetails = $(e.relatedTarget).data('order');
			$('#accept-goods-order-number').text(orderDetails.order_number);

			$('#accept-goods-form').prop('action', '/accept-order-items/'+orderDetails.id);

			$.ajax({
				url: "/get-order-items/inventory_order_id/"+orderDetails.id,
				dataType: "json",
				beforeSend: function(){
					$('#accept-goods-items').append(`<div class="alert alert-primary">
						<i class="fas fa-spin fa-spinner"></i> Loading order items...
					</div>`);
				},
				success: function(js){
					$('#accept-goods-items').empty();

					$.each(js, function(j,s){
						createNewReceivedRow(s);
					});

				}
			})
		});

		$('#edit-an-order').on('show.bs.modal', function(e) {

			//get data-id attribute of the clicked element
			var orderDetails = $(e.relatedTarget).data('order');
			$('#edit-order-number').text(" | "+orderDetails.order_number);
			$('#edit_order_comments').val(orderDetails.comments);
			$('#edit-an-order-form').prop('action', '/edit-order/{{ $supplier->id }}/'+orderDetails.id);

			$.ajax({
				url: "/get-order-items/inventory_order_id/"+orderDetails.id,
				dataType: "json",
				beforeSend: function(){
					$('#edit-an-order-msg').html(`<div class="alert alert-primary">
						<i class="fas fa-spin fa-spinner"></i> Loading order items...
					</div>`);

					$('#edit-an-order').find('.dynamic-row').remove();
				},
				success: function(js){
					$('#edit-an-order-msg').html('');

					$.each(js, function(j,s){
						createNewRow($('#edit-an-order-form').find('.add-category-btn'), s);
					});

				}
			})
		});

		var createNewReceivedRow = function(data=false){
			var $row = $(rowReceivedHTML).clone(true, true);

			$receivedprop = data.fulfilled == 1 ? 'disabled' : 'readonly';

			if($receivedprop == 'disabled'){
				$row.find('.fulfilled-msg').text('Fulfilled');
				$row.find('.qc-holder').remove();
				$row.find('.checkbox-holder').html(`
					<i class="fas fa-check-circle text-success mt-4"></i> Items Delivered
				`).removeClass('col-sm-2 col-sm-4');
			}

			$row.find('.items-requires_qc').on('click', function(){
				if($(this).is(":checked")){
					$row.find('[name="items[requires_qc][]"]').val(1);
				}
				else{
					$row.find('[name="items[requires_qc][]"]').val(0);
				}
			});

			$row.find('.items-receive').on('click', function(){
				if($(this).is(":checked")){
					$row.find('[name="items[receive][]"]').val(1);
				}
				else{
					$row.find('[name="items[receive][]"]').val(0);
				}
			});

			$row.find('[name="items[order_item_id][]"]').val(data.id).prop($receivedprop, true);
			$row.find('[name="items[quantity][]"]').val(data.quantity).prop($receivedprop, true);
			$row.find('[name="items[sub_category_id][]"]').val(data.inventory_category_id+" "+data.inventory_sub_category_id).prop($receivedprop, true).trigger('change');

			$row.find('select').select2();

			$('#accept-goods-items').append($row);
		}

		var createNewRow = function($ts=false,data=false){
			var $row = $(rowHTML).clone(true, true);

			$row.find('.removeThis').on('click', function(){
				if(confirm("Are you sure you want to delete this?")){
					if(data.id){
						$.ajax({
							url: "/delete-order-items/"+data.id,
							dataType: "json",
							method: "POST",
							success: function(js){
								if(js.message){
									alert(js.message);
								}

								if(js.status){
									$row.remove();
								}
							}
						})
					}
					else{
						$row.remove();
					}
				}
			});

			if(data){
				$row.find('[name="items[order_item_id][]"]').val(data.id);
				$row.find('[name="items[quantity][]"]').val(data.quantity);
				$row.find('[name="items[sub_category_id][]"]').val(data.inventory_category_id+" "+data.inventory_sub_category_id).trigger('change');

				if(data.fulfilled != 0){
					$row.find('.fulfilled-msg').text('Fulfilled');
					$row.find('[name="items[quantity][]"]').prop('readonly', true);
					$row.find('[name="items[sub_category_id][]"]').prop('readonly', true);
				}

			}

			$row.find('select').select2();

			$ts.before($row);
		}


		$('.add-category-btn').on('click', function(){
			createNewRow($(this), false);
		});

		var $url = $('#server-side-orders').data('url');
		var serverTable = $('#server-side-orders').DataTable({
			lengthMenu: [[10, 25, 50, 100, 500, 1000, -1], [10, 25, 50, 100, 500, 1000, "All"]],
			dom: 'Blfrtip',
			buttons: [
				'copy', 'csv', 'excel', 'pdf', 'print'
			],
			columns: [
				{ data: "loop", "searchable": false },
				{ data: "order_number" },
				{ data: "supplier" },
				{ data: "creator" },
				{ data: "status" },
				{ data: "order_items" },
				{ data: "created_at" },
				{
					data: null,
					className: "center",
					render: function ( data, type, row ) {
						return `<span data-toggle="modal" data-target="#edit-an-order"
							class="btn btn-sm btn-primary btn-flat edit-order-details"
							data-order='${JSON.stringify(data)}'><i class="mdi mdi-pencil"></i></span>&nbsp;
							<span data-toggle="modal" data-target="#accept-goods"
								class="btn btn-sm btn-success btn-flat edit-order-details"
								data-order='${JSON.stringify(data)}'><i class="mdi mdi-dolly"></i>
							</span>
						`;
					}
				}
			],
			processing: true,
			serverSide: true,
			ajax: $url
		});
	})
</script>

@endsection
