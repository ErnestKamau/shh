@extends('layouts.inventory.layout.app', ['dataTable'=>true, 'select2'=>true])

@section('title2')
  <title>{{ $stage }} | Inventory Management</title>
	<style type="text/css">
		.approval-complete{
			border-left:10px solid #6dff00!important;
		}
		.purchase-order-sent{
			border-left:10px solid #6dff00!important;
		}
		.awaiting-approval{
			border-left:10px solid #fff911!important;
		}
		.partially-approved{
			border-left:10px solid #ffbe00!important;
		}
	</style>
@endsection
@section('content2')
  <main>
		<?php
			$stages = getRequisitionWorkflow();
      $user = \Auth::user();
      $items = array(
        array(
          'link' => route('inventory-home'),
          'name' => 'Inventory Management',
          'icon' => null
        ),
        array(
          'link' => route('go_to_stage', ['stage'=>$stage]),
          'name' => $stage,
          'icon' => null
        )
			);

			$inventoryAssistantSupervisorRoles = filterExistingSpatieRoleNames([
				'Inventory Assistant Supervisor Group',
				'Assistant Supervisor',
				'Supervisor',
				'Admin',
				'admin',
				'Super Admin',
				'super admin',
				'super-admin',
				'System Admin',
				'system admin',
				'system-admin',
			]);
			$inventoryProcurementRoles = filterExistingSpatieRoleNames(['Inventory Procurement Group', 'Procurement', 'Admin', 'admin']);
			$isInventoryAssistantSupervisor = $inventoryAssistantSupervisorRoles !== [] && $user->hasAnyRole($inventoryAssistantSupervisorRoles);
			$isInventoryProcurement = $inventoryProcurementRoles !== [] && $user->hasAnyRole($inventoryProcurementRoles);
			$canCreateRequest = $isInventoryAssistantSupervisor
				|| $isInventoryProcurement
				|| isUserSomebody($user)
				|| in_array($stage, ['Purchase Request', 'Request to Store'], true);

			$lab_department_id = getConfigByName('lab_department_id');
			$lab_department_id = count($lab_department_id) > 0 ? $lab_department_id[0]->value : 0;
			$isLabDepartmentUser = intval($user->department_id) === intval($lab_department_id);

    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    <h3 class="p-4" id="has-procurement" data-procurement="{{ $isInventoryProcurement ? 'Yes' : 'No' }}">
			<i class="mdi mdi-format-list-checks"></i> {{ $stage }}
			@if(in_array($stage, array("Purchase Request", "Request to Store", "Gate Pass", "Loan", "Lend"), true) && $canCreateRequest)
				<a class="btn btn-primary btn-sm float-right" href="{{ route('view-request-details', ['stage'=>$stage, 'id'=>'new']) }}">
					<i class="mdi mdi-plus"></i> Add
				</a>
			@endif
			@if(in_array($stage,["Purchase Request"]) && $isLabDepartmentUser)
			<span class="btn btn-sm btn-transparent text-success float-right" data-toggle="modal"
				data-target="#clone-this-request" data-url="{{ route('clone-request-details', ['stage'=>$stage]) }}">
				<i class="mdi mdi-content-duplicate"></i> Clone
			</span>
			@endif
			<a class="btn btn-sm btn-danger d-none float-right" id="download-items-all"><i class="mdi mdi-download"></i> Download Items PDF</a>
		</h3>
		<div class="bg-light">
			<div class="card tab-card">
				<div class="card-header tab-card-header">
					<ul class="nav nav-tabs card-header-tabs" id="Requests-tabs" role="tablist">
						<li class="nav-item">
							<a class="nav-link active" id="Requests-tab" data-toggle="tab" href="#Requests" role="tab" aria-controls="Requests" aria-selected="true">Requests</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" id="Completed-tab" data-toggle="tab" href="#Completed" role="tab" aria-controls="Completed" aria-selected="true">Completed</a>
						</li>
						@if($isLabDepartmentUser && $stage == "Purchase Request")
							<li class="nav-item">
								<a class="nav-link" id="Lab-Kits-tab" data-toggle="tab" href="#Lab-Kits" role="tab" aria-controls="Lab Kits" aria-selected="true">Lab Kits</a>
							</li>
						@endif
						@if($stage == "Request to Store")
							<li class="nav-item">
								<a class="nav-link" id="Clonable-tab" data-toggle="tab" href="#Clonable-Requests" role="tab" aria-controls="Clonable" aria-selected="true">Frequent Requests</a>
							</li>
						@endif
						<li class="nav-item">
							<a class="nav-link" id="Approvals-tab" data-toggle="tab" href="#Approvals" role="tab" aria-controls="approvals" aria-selected="true">Approval Configuration</a>
						</li>
					</ul>
				</div>
				<div class="tab-content" id="Requests-tabs-content">
					{{-- Zoho Books integration disabled — not in use.
					@if($stage == "Purchase Orders")
					<div class="alert alert-info text-small m-2" id="zoho-sync">
						<small><i class="fas fa-spin fa-spinner"></i> Please wait as we sync with zoho...</small>
					</div>
					@endif
					--}}
					<div class="tab-pane fade show active p-3" id="Requests" role="tabpanel" aria-labelledby="one-tab">
						<h5 class="card-title mb-3">Requests</h5>
						<div class="table-responsive">
							<table data-url="{{ route('get_req_enitites_server_side', ['stage'=>$stage, 'type'=>'list']) }}" class="status-table server-side table table-condensed my-small-text table-striped table-hover table-bordered table-sm" data-fixedcls="true">
								<thead>
									<tr>
										<th>#</th>
										<th></th>
										<th nowrap>Code</th>
										<th nowrap>Items</th>
										<th nowrap>Priority</th>
										<th nowrap>Status</th>
										<th nowrap>Description</th>
										<th nowrap>Due Date</th>
										@if (array_search($stage, $stages) > 0)
											<th nowrap>Source</th>
										@endif
										@if ($stage == "Material Issuance")
											<th nowrap>Source</th>
										@endif
										@if ($stage == "Purchase Orders")
											<th nowrap>Supplier</th>
										@endif
										<th nowrap>Created By</th>
										<th nowrap>Department</th>
										<th nowrap>Created On</th>
										<th nowrap>Approvals</th>
										<th nowrap>Total Value</th>
									</tr>
								</thead>
								<tbody></tbody>
							</table>
						</div>
					</div>
					<div class="tab-pane fade p-3" id="Completed" role="tabpanel" aria-labelledby="one-tab">
						<h5 class="card-title mb-3">Completed Requests</h5>
						<div class="table-responsive">
							<table data-url="{{ route('get_req_enitites_server_side', ['stage'=>$stage, 'type'=>'completed_list']) }}" class="status-table server-side table table-condensed my-small-text table-striped table-hover table-bordered table-sm" data-fixedcls="true">
								<thead>
									<tr>
										<th>#</th>
										<th></th>
										<th nowrap>Code</th>
										<th nowrap>Items</th>
										<th nowrap>Priority</th>
										<th nowrap>Status</th>
										<th nowrap>Description</th>
										<th nowrap>Due Date</th>
										@if (array_search($stage, $stages) > 0)
											<th nowrap>Source</th>
										@endif
										@if ($stage == "Material Issuance")
											<th nowrap>Source</th>
										@endif
										@if ($stage == "Purchase Orders")
											<th nowrap>Supplier</th>
										@endif
										<th nowrap>Created By</th>
										<th nowrap>Department</th>
										<th nowrap>Created On</th>
										<th nowrap>Approvals</th>
										<th nowrap>Total Value</th>
									</tr>
								</thead>
								<tbody></tbody>
							</table>
						</div>
					</div>
					@if($isLabDepartmentUser && $stage == "Purchase Request")
						<div class="tab-pane fade p-3" id="Lab-Kits" role="tabpanel" aria-labelledby="one-tab">
							<h5 class="card-title mb-3">Lab-Kits</h5>
							<div class="table-responsive">
								<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm" data-fixedcls="true">
									<thead>
										<tr>
											<th></th>
											<th nowrap>Code</th>
											<th nowrap>Description</th>
											<th nowrap>Created By</th>
											<th nowrap>Created On</th>
										</tr>
									</thead>
									<tbody>
										@foreach ($kit_list as $l)
											<tr>
												<td nowrap>
													<input type="checkbox" name="request_id[]" class="submittable-entity-ids" data-code="{{ $l->request_code }}" value="{{ $l->id }}" />
													@if(($l->created_by == \Auth::user()->id || $isInventoryProcurement) && $l->status == "In Preparation")
														<span class="btn btn-sm btn-transparent text-danger" data-toggle="modal"
															data-target="#delete-entity-modal" data-stage="{{ $stage }}" data-id="{{ $l->id }}">
															<i class="mdi mdi-delete"></i>
														</span>
													@endif
												</td>
												<td nowrap>
													<a href="{{ route('view-request-details', ['stage'=>$stage, 'id'=>$l->id]) }}">
														{{ $l->request_code }}
													</a>
												</td>
												<td nowrap>{{ $l->description ?? 'No items set' }}</td>
												<td nowrap>{{ $l->creator_name }}</td>
												<td nowrap>{{ $l->created_at }}</td>
											</tr>
										@endforeach
									</tbody>
								</table>
							</div>
						</div>
					@endif
					@if($stage == "Request to Store")
						<div class="tab-pane fade p-3" id="Clonable-Requests" role="tabpanel" aria-labelledby="one-tab">
							<h5 class="card-title mb-3">Frequent Requests</h5>
							<div class="table-responsive">
								<table data-url="{{ route('get_req_enitites_server_side', ['stage'=>$stage, 'type'=>'kit_list']) }}" class="status-table server-side table table-condensed my-small-text table-striped table-hover table-bordered table-sm" data-fixedcls="true">
									<thead>
										<tr>
											<th>#</th>
											<th></th>
											<th nowrap>Code</th>
											<th nowrap>Items</th>
											<th nowrap>Priority</th>
											<th nowrap>Status</th>
											<th nowrap>Description</th>
											<th nowrap>Due Date</th>
											<th nowrap>Created By</th>
											<th nowrap>Department</th>
											<th nowrap>Created On</th>
											<th nowrap>Approvals</th>
											<th nowrap>Total Value</th>
										</tr>
									</thead>
									<tbody></tbody>
								</table>
							</div>
						</div>
					@endif
					<div class="tab-pane fade p-3" id="Approvals" role="tabpanel" aria-labelledby="one-tab">
						<h5 class="card-title mb-3">Approvals Configuration
							<button class="btn btn-outline-primary btn-sm float-right"
								data-toggle="modal" data-target="#add-approval-modal"><i class="mdi mdi-key-plus"></i></button>
						</h5>
						<div class="table-responsive">
							<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm" data-fixedcls="true">
								<thead>
									<tr>
										<th>#</th>
										<th nowrap>Title</th>
										<th nowrap>Users</th>
										<th></th>
									</tr>
								</thead>
								<tbody>
									@foreach ($approvals as $approval)
										<tr>
											<td>{{ $loop->iteration }}</td>
											<td>{{ $approval->title }}</td>
											<td>
												<?php $users = array(); ?>
												@foreach ($approval->user_roles() as $user)
													<?php $users[] = '<span class="my-small-text">
															<i class="mdi mdi-account"></i> '.$user->name.' <small class="ext-mutedt"><'.$user->email.'></small>
														</span>';
													?>
												@endforeach
												{!! implode(", ", $users) !!}
											</td>
											<td>
												<button class="btn btn-primary btn-sm" data-target="#edit-approval-{{ $loop->iteration }}" data-toggle="modal"><i class="mdi mdi-pencil-outline"></i> <small class="hidden-sm-up">Edit</small> </button>
												<div id="edit-approval-{{ $loop->iteration }}" class="modal fade" role="dialog">
													<div class="modal-dialog">
														<!-- Modal content-->
														<form class="modal-content" method="POST" action="{{ route('edit-approval-to-stage', ['id'=>$approval->id]) }}" enctype="multipart/form-data">
															@csrf
															<div class="modal-header">
																<h4 class="modal-title"><i class="mdi mdi-pencil-outline"></i> Edit Approval</h4>
															</div>
															<div class="modal-body">
																<div class="form-group">
																	<label class="control-label">Name</label>
																	<input type="text" class="form-control" name="name" value="{{ $approval->title }}" placeholder="Name..." required />
																</div>
																<div class="form-group">
																	<label class="control-label">Select Role</label>
																	<select name="role_id" class="form-control" placeholder="Select Approval User..." required>
																		<option></option>
																		@foreach (getRoles() as $item)
																			<option value="{{ $item->id }}" {{ ($item->name === $approval->role_group_name || (string) $item->id === (string) $approval->role_id) ? 'selected' : '' }}>{{ $item->name }}</option>
																		@endforeach
																	</select>
																</div>
																<div class="form-group">
																	<label class="control-label">Level</label>
																	<input type="number" min="1" class="form-control" name="level" value="{{ $approval->level }}" placeholder="Name..." required />
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
  <div id="add-approval-modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <!-- Modal content-->
      <form class="modal-content" method="POST" action="{{ route('add-approval-to-stage', ['stage'=>$stage]) }}" enctype="multipart/form-data">
        @csrf
        <div class="modal-header">
          <h4 class="modal-title"><i class="mdi mdi-plus"></i> Add New Approval</h4>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label class="control-label">Approval Title</label>
						<input type="text" class="form-control" name="name" value="" placeholder="Name..." required />
						<input type="hidden" name="for" value="Requisition" />
          </div>
          <div class="form-group">
						<label class="control-label">Select Role</label>
						<select name="role_id" class="form-control" placeholder="Select Approval User..." required>
							<option></option>
							@foreach (getRoles() as $item)
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
  <div id="clone-this-request" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <!-- Modal content-->
      <form class="modal-content" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="modal-header">
          <h4 class="modal-title"><i class="mdi mdi-content-duplicate"></i> Clone this {{ $stage }}</h4>
        </div>
        <div class="modal-body">
          <div class="alert alert-info">
						<i class="mdi mdi-information"></i> Are you sure you want to clone this {{ $stage }}?
					</div>
					<div id="submited-ids"></div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary"><i class="mdi mdi-content-duplicate"></i> Yes, Clone</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
  </div>
  <div id="delete-entity-modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <!-- Modal content-->
      <form class="modal-content" method="POST" action="{{ route('add-approval-to-stage', ['stage'=>$stage]) }}" enctype="multipart/form-data">
        @csrf
        <div class="modal-header">
          <h4 class="modal-title"><i class="mdi mdi-delete"></i> Delete {{ $stage }}</h4>
        </div>
        <div class="modal-body">
          <div class="alert alert-danger">
						<i class="mdi mdi-delete"></i> Are you sure you want delete this {{ $stage }}?
					</div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-danger"><i class="mdi mdi-delete"></i> Delete</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
  </div>
	<script>

		var status_colors = function($status){
			$colors = {
				'Approval Complete':'approval-complete',
				'Purchase Order Sent':'purchase-order-sent',
				'Awaiting Approval':'awaiting-approval',
				'Partially Approved':'partially-approved'
			};
			return $colors[$status] || 'border-left:10px solid rgba(0,0,0,0.3)!important';
		}

		$(function(){
			$("#clone-this-request").on('show.bs.modal', function(e){
				var clone = $(e.relatedTarget);
				var url = clone.data('url');
				$('#submited-ids').empty();

				$('.submittable-entity-ids:checked').each(function(){
					var $checkbox = $(`<label class="control-label" style="padding: 5px 10px"> </label>`);
					var name = $(this).data('code');
					$checkbox.append($(this));
					$checkbox.append(" "+name);

					$('#submited-ids').append($checkbox);
				});

				$(this).find('form').attr('action', url);
				$(this).find('form').prop('action', url);
			});

			$('.status-table').each(function(){
				var $url = $(this).data('url');
				var $dtzz = $(this);
				var hasProcurement = $('#has-procurement').data('procurement');
				var serverTable = $(this).DataTable({
					fixedHeader: true,
					lengthMenu: [[50, 100, 500, 1000, -1], [50, 100, 500, 1000, "All"]],
					dom: 'Blfrtip',
					buttons: [
						'copy', 'csv', 'excel', 'pdf', 'print'
					],
					"columnDefs": [
						{ className: "dt-nowrap", "targets": [ 3,5,6,8,10,11 ] }
					],
					"order": [[2, 'desc']],
					columns: [
						{ data: "loop", "searchable": false },
						{
							data: null,
							className: "center",
							render: function ( data, type, row ) {
								var currentUserId = @json((string) \Auth::user()->id);
								var canManage = (data.created_by == currentUserId && data.status == "In Preparation") || hasProcurement == "Yes";

								if (canManage) {
									if (data.status !== "Completed") {
										return `<input type="checkbox" class="downloadable" value="${data.id}" /> <span class="btn btn-sm btn-transparent text-danger" data-toggle="modal"
											data-target="#delete-entity-modal" data-stage="{{ $stage }}" data-id="${data.id}">
											<i class="mdi mdi-delete"></i>
										</span>`;
									}

									return `<input type="checkbox" class="downloadable" value="${data.id}" />`;
								}

								return `<input type="checkbox" class="downloadable" value="${data.id}" />`;
							}
						},
						{
							data: null, name: 'request_code',
							render: function ( data, type, row ) {
								return `<a href="/req/{{ $stage }}/${data.id}">${data.request_code}</a>`;
							}
						},
						{
							data: null, name: 'item_names',
							render: function ( data, type, row ) {
								return `<div style="max-width: 500px; overflow-x:hidden; text-overflow: ellipsis; whitespace: nowrap" data-toggle="tooltip" title="${data.item_names}">${data.item_names}</div>`;
							}
						},
						{ data: "priority" },
						{
							data: null, name: 'status',
							render: function ( data, type, row ) {
								var approvalDetail = (data.status === 'Partially Approved' && data.approval_status)
									? ` <small>${data.approval_status}</small>`
									: '';
								return `${data.status || ''}${approvalDetail}`;
							}
						},
						{ data: "description" },
						{ data: "due_date" },
						@if ((array_search($stage, $stages) > 0) || $stage == "Material Issuance")
							{
								data: null, name: 'parent_request_code',
								render: function(data, type, row){
									if(data.parent_request && data.parent_request != ''){
										var parentLabel = String(data.parent_request).replace(/s$/, '');
										return `<a href="/req/${data.parent_request}/${data.parent_request_id}">
											${parentLabel} - ${data.parent_request_code}
										</a>`;
									}
									else{
										return '-';
									}
								}
							},
						@endif
						@if($stage == "Purchase Orders")
						{ data: "supplier_name" },
						@endif
						{ data: "creator_name" },
						{ data: "departmental_name" },
						{ data: "created_at" },
						{
							data: null,
							render: function(data, type, row){
								return `${data.approval_count} / ${data.required_approvals}`
							}
						},
						{
							data: null, name: 'net_value',
							render: function(data, type, row){
								var amount = parseFloat(data.net_value);
								if (isNaN(amount)) {
									amount = 0;
								}
								var currency = data.currency_name ? `${data.currency_name} ` : '';
								return currency + amount.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
							}
						},
					],
					"rowCallback" : function(row, data, index){
						var r_css = status_colors(data.status);
						$(row).addClass(r_css);
        	},
					destroy: true,
					processing: true,
					serverSide: true,
					ajax: $url
				});
			
			});

			$('table').on('change', 'input.downloadable', function(){
				var links = [];
				if($('table').find('input.downloadable:checked').length > 0){
					$('#download-items-all').removeClass('d-none');
					$('table').find('input.downloadable:checked').each(function(){
						links.push($(this).val());
					});
				}
				else{
					$('#download-items-all').addClass('d-none');
				}

				if(links.length > 0){
					var $link = `/download-request-items/${links.join(',')}/pdf`;
					$('#download-items-all').prop('href', $link);
					$('#download-items-all').attr('href', $link);
				}
			});
			
			$("#delete-entity-modal").on('show.bs.modal', function(e){
				var btn = $(e.relatedTarget);
				var id = btn.data('id');

				$(this).find('form').attr('action', '/req/{{ $stage }}/'+id+'/delete');
				$(this).find('form').prop('action', '/req/{{ $stage }}/'+id+'/delete');
			});

			{{-- Zoho Books integration disabled — not in use.
			if($('#zoho-sync').length > 0){
				$('#zoho-sync').slideUp(0);
				$.ajax({
					url: '{{ route("zoho-purchase-orders") }}',
					dataType: "json",
					beforeSend: function(){
						$('#zoho-sync').slideDown(300);
					},
					success: function(js){
						$('#zoho-sync').html(`
							<small><i class="fas fa-info-circle"></i> ${js.message}</small>
						`);
						$('#zoho-sync').slideUp(300);
					}
				});
			}
			--}}
		});
	</script>
@endsection