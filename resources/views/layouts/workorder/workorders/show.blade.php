@extends('layouts.workorder.layout.app', ['dataTable'=>true, 'select2'=>true, 'fullcalendar'=>true])

@section('title2')
<title>Work Orders | Work Order Management</title>
<style>
	.wo-tab{
		color: #898989;
		padding: 10px 20px;
		cursor: pointer;
	}

	.wo-tab.selected{
		color: #232323;
		padding-bottom: 16px !important;
		border-bottom: rgb(73, 93, 117) solid 4px;
	}

	.wo-tab-content{
		display: none;
	}

	.wo-tab-content.visible{
		display: unset;
	}
	.timeline {
		list-style-type: none;
		display: flex;
		align-items: center;
		/* justify-content: center; */
	}

	.li {
		transition: all 200ms ease-in;
	}

	.timestamp {
		margin-bottom: 20px;
		padding: 0px 40px;
		display: flex;
		flex-direction: column;
		align-items: center;
		font-weight: 100;
	}

	.status {
		padding: 15px 40px 0px;
		display: flex;
		justify-content: center;
		border-top: 2px solid #D6DCE0;
		position: relative;
		transition: all 200ms ease-in;
	}

	li .timestamp .author, li .timestamp .date{
		white-space: nowrap;
		text-overflow: ellipsis;
	}
	.status h6 {
		color: {{ isset($workorder->current_status) && $workorder->current_status == 'REQUEST_REJECTION' ? '#758D96' : '#ffd6d6' }};
		font-weight: 600;
	}
	.status:before {
		content: "";
		width: 25px;
		height: 25px;
		background-color: white;
		border-radius: 25px;
		position: absolute;
		top: -15px;
		left: 42%;
		transition: all 200ms ease-in;
	}

	.li.complete .status {
		border-top: 2px solid {{ isset($workorder->current_status) && $workorder->current_status == 'REQUEST_REJECTION' ? '#af4f57' : '#6275c7' }};
	}
	.li.complete .status:before {
		background-color: {{ isset($workorder->current_status) && $workorder->current_status == 'REQUEST_REJECTION' ? '#af4f57' : '#6275c7' }};
		border: none;
		transition: all 200ms ease-in;
		border: 3px solid #fff !important;
	}
	.li.complete .status h6 {
		color: {{ isset($workorder->current_status) && $workorder->current_status == 'REQUEST_REJECTION' ? '#af4f57' : '#6275c7' }};
	}

	@media (min-device-width: 320px) and (max-device-width: 700px) {
		.timeline {
			list-style-type: none;
			display: block;
		}

		.li {
			transition: all 200ms ease-in;
			display: flex;
			width: inherit;
		}

		.timestamp {
			width: 100px;
		}

		.status:before {
			left: -8%;
			top: 30%;
			transition: all 200ms ease-in;
		}
	}
	/* html, body {
		width: 100%;
		height: 100%;
		display: flex;
		justify-content: center;
		font-family: "Titillium Web", sans serif;
		color: #758D96;
	} */
</style>
<link href='https://fonts.googleapis.com/css?family=Titillium+Web:400,200,300,600,700' rel='stylesheet' type='text/css'>
<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">
@endsection
@section('content2')
<main>
	<?php
		$items = array(
			array(
				'link' => route('workorder-list'),
				'name' => 'Work Orders',
				'icon' => null
			),
			array(
				'link' => '#',
				'name' => $is_new_mo == 'yes' ? 'Create Work Order' : 'Work Order '.$workorder->ticket_no,
				'icon' => null
			)
		);

		$PERSONNEL = getPersonnel();
		$statuses = isset($workorder) ? $workorder->status() : [];

		$departmental_head_roles = getConfigByName('departmental_head_role_id');
		$departmental_head_role_id = count($departmental_head_roles) > 0 ? $departmental_head_roles[0]->value : 0;

		$engineering_head_roles = getConfigByName('engineering_head_role_id');
		$engineering_head_role_id = count($engineering_head_roles) > 0 ? $engineering_head_roles[0]->value : 0;

	?>
	<x-bread-crumb :items="$items"></x-bread-crumb>
	<h3 class="p-1 pr-4 pl-4">
		<i class="mdi {{ $is_new_mo == 'yes' ? 'mdi-clipboard-edit' : 'mdi-clipboard' }}"></i> {{ $is_new_mo == 'yes' ? 'Create Work Order' : 'Work Order '.$workorder->ticket_no }}
		<small class="badge badge-pill bg-white my-small-text">
			<i class="mdi mdi-information-outline"></i> {{ $workorder->current_status ?? 'REQUEST' }}
		</small>

		@if(!isset($workorder) || (isset($workorder) && !in_array($workorder->current_status, ["COMPLETED", "REQUEST_REJECTION"])))
			<button class="btn btn-default text-primary float-right save-details-form">
				<i class="mdi mdi-content-save"></i> SAVE
			</button>
		@endif

		@if(!isset($workorder) || (isset($workorder) && in_array($workorder->current_status, ["ACCEPTED", "ASSIGNED"])))
			<button class="btn btn-default text-success float-right" data-toggle="modal" data-target="#send-job-cards-modal">
				<i class="mdi mdi-card-account-details"></i> ISSUE JOB CARDS
			</button>

			@if (isset($workorder->current_status) && $workorder->current_status == "ASSIGNED")
				<button class="btn btn-default text-info float-right" data-toggle="modal" data-target="#wo-completion-modal">
					<i class="mdi mdi-check-bold"></i> MARK COMPLETE
				</button>
			@endif
		@endif

		@if (isset($workorder) && in_array($workorder->current_status, ['REQUEST', 'PENDING']))
			<span class="btn-group float-right btn-sm" role="group">
				@if (isset($workorder) && $workorder->approval_started == 0)
					<span class="btn btn-transparent btn-sm btn-primary" data-target="#request-approval-request-modal" data-toggle="modal">
						<i class="mdi mdi-account-check"></i> REQUEST APPROVAL
					</span>
				@else
					<button id="btnGroupDrop1" type="button" class="btn-sm btn btn-transparent dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						{{ isset($workorder->current_status) && $workorder->current_status == "REQUEST" ? "W.O. REQUEST" : "ENGINEERING APPROVAL" }}
					</button>
					@if((isset($workorder->current_status) && $workorder->current_status == 'REQUEST' && \Auth::user()->hasRole($departmental_head_role_id, true)) || ($workorder->current_status == 'PENDING' && \Auth::user()->hasRole($engineering_head_role_id, true)))
						<div class="dropdown-menu" style="cursor: pointer" aria-labelledby="btnGroupDrop1">
							<a class="dropdown-item text-success" data-target="#request-approval-modal" data-toggle="modal">
								<i class="mdi mdi-account-check"></i> {{ isset($workorder->current_status) && $workorder->current_status == "REQUEST" ? "APPROVE REQUEST" : "APPROVE" }}
							</a>
							<a class="dropdown-item text-danger" data-target="#request-reject-modal" data-toggle="modal">
								<i class="mdi mdi-cancel"></i>{{ isset($workorder->current_status) && $workorder->current_status == "REQUEST" ? "REJECT REQUEST" : "REJECT" }}
							</a>
						</div>
					@endif
				@endif
			</span>
		@endif

	</h3>
	<br>
	<form id="details-form" class="bg-light" method="POST" enctype="multipart/form-data"
		action="{{ $is_new_mo == 'yes' ? route('workorder-save') : route('workorder-save', ['id'=>$workorder->id]) }}" style="clear: both !important">
		@csrf
		<div class="card tab-card">
			<div class="card-header tab-card-header">
				<ul class="nav nav-tabs card-header-tabs" style="font-size: 14px" id="Request-tabs" role="tablist">
					<li class="nav-item">
						<a class="nav-link active" id="Information-tab" data-toggle="tab" href="#Information" role="tab" aria-controls="Information" aria-selected="true">General</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" id="Resources-tab" data-toggle="tab" href="#Resources" role="tab" aria-controls="Resources" aria-selected="true">
							Resources
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" id="Items-tab" data-toggle="tab" href="#Status-History" role="tab" aria-controls="Status-History" aria-selected="true">
							Status Timeline
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" id="Items-tab" data-toggle="tab" href="#Edit-History" role="tab" aria-controls="Edit-History" aria-selected="true">
							Edit History
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" id="Contacts-tab" data-toggle="tab" href="#Contacts" role="tab" aria-controls="Contacts" aria-selected="true">
							Contacts
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" id="Attachments-tab" data-toggle="tab" href="#Attachments" role="tab" aria-controls="Attachments" aria-selected="true">
							Attachments
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" id="Approvals-tab" data-toggle="tab" href="#Approvals" role="tab" aria-controls="Approvals" aria-selected="true">
							Approvals
						</a>
					</li>
				</ul>
			</div>
			<div class="tab-content" id="Request-tabs-content">
				<div class="tab-pane fade show active p-3" id="Information" role="tabpanel" aria-labelledby="one-tab">
					<h5 class="card-title mb-3 mt-1">
						<i class="mdi mdi-information"></i> Work Order Information
						<?php
							if(isset($workorder) && $workorder->due_date < \Carbon\Carbon::now()){
								$overdue = '';
								$overdue = '<small class="badge badge-danger badge-pill my-small-text">
									<i class="mdi mdi-alert"></i> Work Order Overdue
								</small>';
							}
						?>
						{!! $overdue ?? '' !!}
					</h5>
					@if (isset($workorder) && in_array($workorder->current_status, ['REQUEST_REJECTION', 'REJECTED']))
						<div class="alert alert-danger mt-2 mb-2">
							<b>REJECTION REASON:</b> {{ $statuses['REQUEST_REJECTION']->comments }}
						</div>
					@endif
					<hr>
					<div class="row">
						<div class="col-md-5 col-sm-6">
							<h6>Created By</h6>
							<?php $USER = isset($workorder) ? \App\User::find($workorder->created_by_id) : \Auth::user(); ?>
							<div class="row">
								<div class="col-xs-3 col-md-2">
									<img src="{{ $USER->photo }}" style="width: 100%" />
								</div>
								<div class="col-xs-9 col-md-10">
									<strong>{{ \Auth::user()->name }}</strong>
									<div><i class="mdi mdi-phone"></i> {{ $USER->phone }}</div>
									<div><i class="mdi mdi-email"></i> {{ $USER->email }}</div>
								</div>
							</div>
							<hr>
							<h6>Description</h6>
							<div id="description-tab">
								{!! $workorder->description ?? 'No description provided.' !!}
							</div>
						</div>
						<div class="col-md-7 col-sm-6">
							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										<label for="client-name">Select Department</label>
										<select class="form-control" id="department_id" name="main[department_id]" required>
											<option value="">Select Department</option>
											@foreach ($clients as $client)
												<option value="{{ $client->id }}"
													{{ (isset($workorder->client_id) && $client->id == $workorder->client_id) ? 'selected' : '' }}>
													{{ $client->name }}
												</option>
											@endforeach
										</select>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label for="client-name">Select Topology</label><br>
										<div class="btn btn-transparent text-primary btn-sm" data-target="#modal-select-topology" data-toggle="modal"
											style="margin-top: 1px;">
											<small>
												<i class="fas fa-sitemap" style="margin-right: 10px"></i>
												<span class="topology-name">SELECTED : {{ isset($workorder) ? $workorder->getSite('name') : 'No Topology'}}</span>
											</small>
										</div>
										<input type="hidden" value="{{ $workorder->site ?? '' }}" name="main[topology_id]" />
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-sm-6">
									<div class="form-group">
										<label for="service_type">Type of Service</label>
										<select class="form-control" id="service" name="main[service]" required>
											<option value="">Select Service Type</option>
											@foreach ($services as $service)
												<option value="{{ $service->id }}" {{ (isset($workorder->service_id) && $service->id == $workorder->service_id) ? 'selected' : '' }}>{{ $service->name }}</option>
											@endforeach
										</select>
									</div>
								</div>
								<div class="col-sm-6">
									<div class="form-group">
										<label for="demand-type">Select Demand Type</label>
										<select class="form-control" id="demand_type" placeholder="Select Demand Type" name="main[demand_type]" required>
											<option value="">Select Demand Type</option>
											<option value="Demand" {{ (isset($workorder->demand_type) && $workorder->demand_type=="Demand") ? 'selected' : '' }}>Demand</option>
											<option value="PM" {{ (isset($workorder->demand_type) && $workorder->demand_type=="PM") ? 'selected' : '' }}>Preventive Maintenance</option>
										</select>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-sm-6">
									<div class="form-group">
										<label for="priority">Priority</label>
										<select class="form-control" id="priority" name="main[priority]" required>
											@foreach (getRequestPriority() as $p)
												<option value="{{ $p }}" {{ $p == ($workorder->priority ?? '') ? 'selected' : '' }}>{{ $p }}</option>
											@endforeach
										</select>
									</div>
								</div>
								<div class="col-sm-6">
									<div class="form-group">
										<div class="text-muted"><label for="routine">Routine</label></div>
										<strong>
											<label class="radio-inline radio-styled text-success">
												<input type="radio" name="main[routine]" value="Yes" {{ isset($workorder->routine)&& $workorder->routine == "Yes" ? 'checked' : '' }} required>
												<span> Yes</span>
											</label>
											<label class="radio-inline radio-styled text-danger">
												<input type="radio" name="main[routine]" value="No" {{ !isset($workorder->routine) || (isset($workorder->routine)&& $workorder->routine == "No") ? 'checked' : '' }}> <span>No</span>
											</label>
										</strong>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-sm-6">
									<div class="form-group">
										<?php $wkt = date('Ymd'); ?>
										<label for="ticket-no">Work Order No*</label>
										<input type="text" class="form-control" id="ticket_no" name="main[ticket_no]"
											value="{{ isset($workorder->ticket_no) ? $workorder->ticket_no : getNamingConventionCode('WorkOrder', false, $wkt, false) }}" readonly>
									</div>
								</div>
								<div class="col-sm-6">
									<div class="form-group">
										<label>Start Date</label>
											<input type="date" class="form-control" id="start_date" name="main[start_date]"
											value="{{ isset($workorder->start_date) ? $workorder->start_date : '' }}" required>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-sm-6">
									<div class="form-group">
										<label>Due Date</label>
											<input type="date" class="form-control" id="due_date" name="main[due_date]"
											value="{{ isset($workorder->due_date) ? $workorder->due_date : '' }}" required>
									</div>
								</div>
								<div class="col-sm-6">
									<div class="form-group">
										<label id="reminder-label" for="reminder">Reminder (If routine)</label>
										<select class="form-control" id="reminder" name="main[reminder]" required>
											<option value="">Select Reminder</option>
											@foreach (getReminders() as $reminder)
												<option value="{{ $reminder }}"
												{{ (isset($workorder->reminder) && $workorder->reminder == $reminder) ? 'selected' : '' }}>{{ $reminder }}</option>
											@endforeach
										</select>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-sm-6">
									<div class="form-group">
										<label><input type="checkbox" name="main[has_external]" value="1" {{ isset($workorder) && $workorder->external_resource_id > 0 ? 'checked' : '' }} /> Has External Resource</label>
									</div>
								</div>
								<div class="col-sm-6">
									<div class="form-group">
										<label id="reminder-label" for="reminder">Supplier</label>
										<select class="form-control" name="main[external_resource_id]" {{ isset($workorder) && $workorder->external_resource_id > 0 ? '' : 'disabled' }}>
											<option value="0">No External Resource...</option>
											@foreach (getSuppliers() as $supplier)
												<option value="{{ $supplier->id }}"
												{{ isset($workorder) && $workorder->external_resource_id == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
											@endforeach
										</select>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-sm-12">
									<div class="form-group">
										<label for="ticket-no">Description</label><br>
										<textarea name="main[description]" class="form-control editor" rows="12">{{ isset($workorder->description) ? $workorder->description : '' }}</textarea>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="tab-pane fade p-3" id="Attachments" role="tabpanel" aria-labelledby="one-tab">
					<h5 class="card-title mb-3 mt-1">
						<i class="mdi mdi-paperclip"></i> Attachments
						<div class="float-right" style="clear: left">
							<span class="btn btn-sm btn-transparent text-muted disabled" id="remove-attachement-wo">
								<i class="mdi mdi-delete"></i> Remove
							</span>
							<span class="btn btn-sm btn-transparent text-info" data-target="#add-attachment-modal" data-toggle="modal">
								<i class="mdi mdi-plus"></i> Add
							</span>
						</div>
					</h5>
					<hr>
					<div class="table-holder">
						<div class="table-responsive">
							<table class="table table-condensed my-small-text server-side table-banded table-striped table-hover table-bordered table-sm">
								<thead>
									<tr>
										<th>#</th>
										<th nowrap>File</th>
										<th nowrap>Title</th>
										<th nowrap>Type</th>
										<th nowrap>Created By</th>
										<th nowrap>Created On</th>
										<th nowrap></th>
									</tr>
								</thead>
								<tbody id="attachment-items" class="notes-and-attachments" data-attachments='{{ json_encode(isset($workorder) ? $workorder->attachments() : []) }}'></tbody>
							</table>
						</div>
					</div>
				</div>
				<div class="tab-pane fade p-3" id="Contacts" role="tabpanel" aria-labelledby="one-tab">
					<h5 class="card-title mb-3 mt-1"><i class="mdi mdi-account-group"></i>
						Contacts
						<div class="float-right">
							<span class="btn btn-sm btn-transparent text-muted disabled" id="remove-wo-contact">
								<i class="mdi mdi-delete"></i> Remove
							</span>
							<span class="btn btn-sm btn-transparent text-info" id="add-contacts-resources">
								<i class="mdi mdi-plus"></i> Add
							</span>
						</div>

					</h5>
					<hr>
					<div class="table-holder">
						<div class="table-responsive">
							<table class="table table-condensed table-bordered table table-striped" data-resources="{{ json_encode(isset($workorder) ? $workorder->contacts() : []) }}"
								id="wo-contacts">
								<thead>
									<tr>
										<th>#</th>
										<th>Name</th>
										<th>Email</th>
										<th>Phone</th>
									</tr>
								</thead>
								<tbody></tbody>
							</table>
						</div>
					</div>
				</div>
				<div class="tab-pane fade p-3" id="Resources" role="tabpanel" aria-labelledby="one-tab">
					<h6 class="card-title mb-3 mt-1">
						<div class="float-right" style="clear: left">
							@if(isset($workorder) && in_array($workorder->current_status, ['PENDING', 'ACCEPTED', 'ASSIGNED']))
								<span class="btn btn-sm btn-transparent text-muted disabled wo-actions" id="remove-wo-resources">
									<i class="mdi mdi-delete"></i> Remove
								</span>
								<span class="btn btn-sm btn-transparent text-muted disabled" id="send-wo-resources-messages">
									<i class="mdi mdi-android-messages"></i> Message
								</span>
								<span class="btn btn-sm btn-transparent text-info" id="add-wo-resources">
									<i class="mdi mdi-plus"></i> Add
								</span>
							@endif
						</div>
						<div style="white-space: nowrap">
							<span class="wo-tab selected" data-content="#personnel-resources-content"><i class="mdi mdi-account-hard-hat"></i> Personnel Resources</span>
							<span class="ml-3 wo-tab" data-content="#inventory-resources-content"><i class="mdi mdi-package-variant"></i> Inventory Resources</span>
						</div>
					</h6>
					<hr>
					<div class="tab-holder">
						<div class="wo-tab-content visible" id="personnel-resources-content">
							<div class="table-responsive">
								<table class="table table-condensed table-bordered table table-striped" data-resources="{{ json_encode(isset($workorder) ? $workorder->resources('personnel') : []) }}"
									id="personnel-resources-content-table">
									<thead>
										<tr>
											<th>#</th>
											<th>Resource Type</th>
											<th>Name</th>
											<th>Email</th>
											<th>Phone</th>
											<th>Calendar</th>
										</tr>
									</thead>
									<tbody></tbody>
								</table>
							</div>
						</div>
						<div class="wo-tab-content" id="inventory-resources-content">
							<div class="table-responsive">
								<table class="table table-condensed table-bordered table table-striped" data-resources="{{ json_encode(isset($workorder) ? $workorder->resources('inventory') : []) }}"
									id="inventory-resources-content-table">
									<thead>
										<tr>
											<th>#</th>
											<th>Item</th>
											<th>Quantity</th>
										</tr>
									</thead>
									<tbody></tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
				<div class="tab-pane fade p-3" id="Status-History" role="tabpanel" aria-labelledby="one-tab">
					<h5 class="card-title mb-3 mt-1"><i class="mdi mdi-history"></i> Work Order Status Timeline</h5>
					<hr>
					<div class="table-responsive">
						<ul class="timeline" id="timeline">
							@foreach (getStatusTypes() as $stat)
								<?php $complete = isset($statuses[$stat]); ?>
								<li class="li{{ $complete ? ' complete' : '' }}">
									<div class="timestamp">
										<span class="author">{{ isset($statuses[$stat]) ? $statuses[$stat]->created_by : '-' }}</span>
										<span class="date">{{ isset($statuses[$stat]) ? $statuses[$stat]->created_at : '-' }}<span>
									</div>
									<div class="status">
										<h6> {{ $stat }} </h6>
									</div>
								</li>
							@endforeach
						 </ul>
					</div>
				</div>
				<div class="tab-pane fade p-3" id="Approvals" role="tabpanel" aria-labelledby="one-tab">
					<h5 class="card-title mb-3 mt-1"><i class="mdi mdi-check-all"></i> Approvals</h5>
					<hr>
					<div class="table-responsive">
						<table class="table table-condensed table-striped table-bordered">
							<thead>
								<tr>
									<th>#</th>
									<th>Approval</th>
									<th>Approver</th>
									<th>Email</th>
									<th>Phone</th>
									<th>Status</th>
									<th></th>
								</tr>
							</thead>
							<tbody>
								@foreach (getWOApprovals() as $name=>$item)
									<?php
										$approval = woApproverset(isset($workorder->id) ? $workorder->id : 0, $name);
									?>
									<tr class="approval-row" data-approval="{{ $name }}"
										data-role="{{ $item['role'] == "external_resource" ? 'external' : 'internal' }}">
										<td>{{ $loop->iteration }}</td>
										<td>{{ $name }}</td>
										<td class="form-group">
											<?php
												$hasData = false;
												if(($approval && $approval['status'] == "pending") || $approval == null){
													if ($item['level'] == "Initial"){
														if($item['role'] == "engineering_head_role_id"){
															$role_id = $engineering_head_role_id;
														}

														if($item['role'] == "departmental_head_role_id"){
															$role_id = $departmental_head_role_id;
														}

														$approvingUsers = getUsersByRole($role_id, true);
													}

													if ($item['level'] == "Completion"){
														if ($item['role'] == "created_by"){
															$approvingUsers = \App\User::where('id', $workorder->created_by_id)->get();
														}
														if ($item['role'] == "external_resource"){
															$approvingUsers = \App\Models\Workorder\WorkOrderResource::where('workorder_id', $workorder->id)
															->where('type', "personnel")->where('is_external', 1)->get();
														}
														if ($item['role'] == "internal_resource"){
															$approvingUsers = \App\Models\Workorder\WorkOrderResource::where('workorder_id', $workorder->id)
															->where('type', "personnel")->where('is_external', 0)->get();
														}
													}

													$hasData = $approvingUsers->count() > 0;
													if($approvingUsers->count() == 1){
														$defacto = $approvingUsers[0];
													}
												}
											?>

											@if($hasData)
												<select class="form-control selected-approver {{ isset($defacto) ? 'trigger-autoselect' : '' }}">
													<option value="">Select Approver</option>
													@foreach($approvingUsers as $u)
														<option value="{{ implode("^^", [$u->name,$u->email,$u->phone]) }}" {{ isset($defacto) ? (trim($defacto->email) == trim($approval['email']) ? 'selected' : '') : (trim($u->email) == trim($approval['email']) ? 'selected' : '') }}>{{ $u->name }}</option>
													@endforeach
												</select>
											@endif
										</td>
										<td class="email-field">{{ isset($approval['email']) ? $approval['email'] : '-' }}</td>
										<td class="phone-field">{{ isset($approval['phone']) ? $approval['phone'] : '-' }}</td>
										<td>{{ strtoupper(isset($approval['status']) ? $approval['status'] : '-') }}</td>
										<td>
											@if(($approval && $approval['status'] == "pending") || $approval == null)
												<span class="btn btn-transparent btn-sm text-muted disabled update-btn">
													<small><i class="mdi mdi-update"></i> UPDATE</small>
												</span>
											@endif
										</td>
									</tr>
								@endforeach
							</tbody>
						</table>

					</div>
				</div>
				<div class="tab-pane fade p-3" id="Edit-History" role="tabpanel" aria-labelledby="one-tab">
					<h5 class="card-title mb-3 mt-1"><i class="mdi mdi-circle-edit-outline"></i> Work Order Edit History</h5>
					<hr>
					<div class="table-responsive">
						<table class="table table-condensed table-striped table-bordered">
							<thead>
								<tr>
									<th>#</th>
									<th>Name</th>
									<th>Comment</th>
									<th>Date</th>
								</tr>
							</thead>
							<tbody>
								@foreach (isset($workorder) ? $workorder->edits() : [] as $edit)
									<tr>
										<td>{{ $loop->iteration }}</td>
										<td>{{ $edit->created_by }}</td>
										<td>{{ $edit->reason }}</td>
										<td>{{ $edit->created_at }}</td>
									</tr>
								@endforeach
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</form>
@endsection
@section('script2')
	<script type="text/javascript" src="/tinymce/tinymce.min.js"></script>
	<link rel="stylesheet" href="/fullcalendar/main.css">
	<script src="/fullcalendar/main.js"></script>
	<div id="modal-select-topology" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<!-- Modal content-->
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title"><i class="mdi mdi-check"></i> SELECT TOPOLOGY</h4>
				</div>
				<div class="modal-body">
					<div class="alert alert-info">
						<i class="fas fa-info-circle"></i> Drill-down [+] to select the topology.
					</div>
					<div id="topology-holder"></div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>
	<div id="personnel-calendar-modal" class="modal fade" role="dialog">
		<div class="modal-dialog modal-xl">
			<!-- Modal content-->
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title"><i class="mdi mdi-calendar"></i> <span id="personnel-name-in-calendar"></span> CALENDAR</h4>
				</div>
				<div class="modal-body">
					<div class="row">
						<div class="col-sm-6">
							<div class="form-group">
								<label><input type="checkbox" id="duplicate-time-slot" value="1" /> Duplicate time slot through the remaining days.</label>
							</div>
						</div>
						<div class="col-sm-6">
							<div class="form-group form-group-sm">
								<label>Clone Timeslot From</label>
								<select class="form-control" id="clone-timeslot-from-id" placeholder="Clone From...">
									<option value="">Clone From...</option>
									@foreach (isset($workorder) ? $workorder->resources('personnel') : [] as $rp)
										<option value="{{ $rp->resource_id }}">{{ $rp->name }}</option>
									@endforeach
								</select>
							</div>
						</div>
					</div>
					<div class="p-1 mb-2  alert-info">
						<small><i class="mdi mdi-information"></i> Double-Click on a timeslot to remove it.</small>
					</div>
					<div id="personnel-calendar"></div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>
	<div id="change-reason-modal" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<!-- Modal content-->
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title"><i class="mdi mdi-edit"></i> Reason for Change</h4>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<div class="alert alert-info">Please provide a reason for the changes made to the workorder description.<span id="changed-fields"></span></div>
					</div>
					<div class="form-group">
						<label class="control-label">Reason Description</label>
						<textarea class="form-control" name="reason" placeholder="Description..."></textarea>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-success" id="save-reason">Add</button>
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>
	<div id="request-approval-request-modal" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<!-- Modal content-->
			<form class="modal-content" method="POST" action="{{ route('approve-wo-request', ['id'=>isset($workorder) ? $workorder->id : 0, 'action'=>'request_approval']) }}">
				@csrf
				<div class="modal-header">
					<h4 class="modal-title"><i class="mdi mdi-check"></i> REQUEST DEPARTMENTAL HEAD APPROVAL</h4>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<div class="alert alert-info">
							<i class="mdi mdi-information fa-2x"></i> Please confirm that you have provided all the sufficient details required for the approval of the workorder.
							Notify the Departmental Head and request for approval?
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button class="btn btn-success">Yes, Proceed</button>
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				</div>
			</form>
		</div>
	</div>
	<div id="request-approval-modal" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<!-- Modal content-->
			<form class="modal-content" method="POST" action="{{ route('approve-wo-request', ['id'=>isset($workorder) ? $workorder->id : 0, 'action'=>'approve']) }}">
				@csrf
				<div class="modal-header">
					<h4 class="modal-title"><i class="mdi mdi-account-check"></i> APPROVE W.O. REQUEST</h4>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<div class="alert alert-info">
							<i class="fas fa-exclamation-triangle fa-2x"></i>
							{{ isset($workorder->current_status) && $workorder->current_status == "REQUEST" ? "Proceed with approval of this work order request?"
								: "Proceed with approving this work order?" }}
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button class="btn btn-success btn-sm"><i class="mdi mdi-check-bold"></i> Approve</button>
					<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
				</div>
			</form>
		</div>
	</div>
	<div id="send-job-cards-modal" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<!-- Modal content-->
			<form class="modal-content" method="POST" action="{{ route('assign-job-card', ['id'=>isset($workorder) ? $workorder->id : 0, 'action'=>'denied']) }}">
				@csrf
				<div class="modal-header">
					<h4 class="modal-title"><i class="mdi mdi-card-account-details-outline"></i> ASSIGN JOB CARDS TO PERSONNEL</h4>
				</div>
				<div class="modal-body">
					<div class="alert alert-info"><i class="mdi mdi-information"></i> Are you sure that you want to assign job cards?</div>
				</div>
				<div class="modal-footer">
					<button class="btn btn-success btn-sm"><i class="mdi mdi-check-bold"></i> ASSIGN</button>
					<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
				</div>
			</form>
		</div>
	</div>
	<div id="wo-completion-modal" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<!-- Modal content-->
			<form class="modal-content" method="POST" action="{{ route('upload-job-card-wo-completion', ['id'=>isset($workorder) ? $workorder->id : 0, 'action'=>'denied']) }}">
				@csrf
				<div class="modal-header">
					<h4 class="modal-title"><i class="mdi mdi-upload"></i> UPLOAD JOBCARD</h4>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<label>Upload Job Card</label>
						<input type="file" name="jcard" class="form-control" />
					</div>
				</div>
				<div class="modal-footer">
					<button class="btn btn-transparent text-info btn-sm"><i class="mdi mdi-upload"></i> UPLOAD</button>
					<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
				</div>
			</form>
		</div>
	</div>
	<div id="request-reject-modal" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<!-- Modal content-->
			<form class="modal-content" method="POST" action="{{ route('approve-wo-request', ['id'=>isset($workorder) ? $workorder->id : 0, 'action'=>'denied']) }}">
				@csrf
				<div class="modal-header">
					<h4 class="modal-title"><i class="mdi mdi-cancel"></i> REJECT W.O. REQUEST</h4>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<div class="alert alert-danger">
							<i class="fas fa-exclamation-triangle fa-2x"></i>
							{{ isset($workorder->current_status) && $workorder->current_status == "REQUEST" ? "Are you sure that you want to reject this workorder request?"
								: "Are you sure you want to reject this workorder?" }}
						</div>
					</div>
					<div class="form-group">
						<label>Provide Rejection Reason</label>
						<textarea class="form-control" name="reason" placeholder="Reason..." required></textarea>
					</div>
				</div>
				<div class="modal-footer">
					<button class="btn btn-danger btn-sm"><i class="mdi mdi-file-cancel"></i> Reject</button>
					<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
				</div>
			</form>
		</div>
	</div>
	<div id="message-personnel-modal" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<!-- Modal content-->
			<form class="modal-content" method="POST" action="{{ route('send-personnel-message', ['id'=>isset($workorder) ? $workorder->id : 0]) }}">
				@csrf
				<div class="modal-header">
					<h4 class="modal-title"><i class="mdi mdi-message"></i> Message Personnel</h4>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<label class="control-label">Message</label>
						<textarea class="form-control" name="message" placeholder="Message..." required></textarea>
					</div>
					<h6>Send to:</h6>
					<div id="persons-to-contact"></div>
				</div>
				<div class="modal-footer">
					<button class="btn btn-success"><i class="mdi mdi-send"></i> Send</button>
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				</div>
			</form>
		</div>
	</div>
	<div id="add-attachment-modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <!-- Modal content-->
      <div class="modal-content">
        @csrf
        <div class="modal-header">
          <h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Attachment</h4>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label class="control-label">Title</label>
						<input type="text" class="form-control" name="title" value="" placeholder="Title..." required />
          </div>
          <div class="form-group">
            <label class="control-label">Type</label>
						<select class="form-control" name="type" placeholder="Type..." required>

							@foreach (getAttachmentTypes() as $item)
								<option value="{{ $item }}">{{ $item }}</option>
							@endforeach
						</select>
          </div>
          <div class="form-group">
						<label class="control-label">Description</label>
						<textarea class="form-control" name="description" placeholder="File Description..."></textarea>
					</div>
          <div class="form-group">
            <label class="control-label">File</label>
						<input type="file" class="form-control" name="attachments[file][]" style="overflow: hidden" required />
          </div>
					<input type="hidden" name="current_user" value="{{ \Auth::user()->id }}" />
					<input type="hidden" name="current_user_email" value="{{ \Auth::user()->email }}" />
					<input type="hidden" name="current_user_name" value="{{ \Auth::user()->name }}" />
					<input type="hidden" name="current_time" value="{{ \Carbon\Carbon::now() }}" />
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary modal-add-data trigger-save" data-type="attachment"><i class="mdi mdi-plus"></i> Add</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
	</div>
	<script>
		var changedItems = [];
		var getAttachmentRow = function($data){
			var $row = `
				<tr class="note-row new resource-row">
					<td style="width:20px">
						<input type="checkbox" class="checkbox-resources" />
						<input type="hidden" name="attachments[attachment_id][]" value="${$data ? $data.id : ''}" />
					</td>
					<td class="note-file-div" nowrap></td>
					<td>
						<input type="text" class="note-type-val form-control" name="attachments[title][]" value="${ $data.title }" />
					</td>
					<td>
						<select class="form-control" name="attachments[type][]" placeholder="Type..." required>
							@foreach (getAttachmentTypes() as $item)
								<option value="{{ $item }}" ${ $data.type == '{{ $item }}' ? 'selected' : '' }>{{ $item }}</option>
							@endforeach
						</select>
					</td>
					<td>
						<input type="text" class="note-type-val form-control" value="${ $data.current_user_name }" readonly />
						<input type="hidden" class="note-type-val" name="created_by" value="${ $data.current_user }" />
					</td>
					<td>
						<input type="text" class="note-type-val form-control" value="${ $data.current_time }" readonly />
					</td>
					<td>
						<span class="mdi mdi-android-messages btn btn-sm btn-default text-primary" data-toggle="modal" data-target="#view-note-modal"
							data-description='${ $data.description }'> Description</span>
						<input type="hidden" name="attachments[description][]" value="${ $data.description }" />
					</td>
				</tr>
			`;

			$row =  $($row).clone();

			$row.find('.note-file-div').append($data.file);

			$row.find('[name="attachments[file][]"]').on('change', function(){
				alert($(this).val())
			});

			return $row;
		}

		var getContactRow = function($data, isNew=true){
			var row = $(`
				<tr class="resource-row">
					<td style="width:20px">
						<input type="checkbox" class="checkbox-resources" />
						<input type="hidden" name="contacts[id][]" value="${$data ? $data.id : ''}" />
					</td>
					<td>${
						isNew ?
							`<select name="contacts[pid][]" class="form-control contact select2" placeholder="Name...">
								<option selected>Select Personnel</option>
								@foreach($PERSONNEL as $personnel)
									<option value="{{ $personnel->pid }}" data-contact="{{ $personnel }}">{{ $personnel->name }}</option>
								@endforeach
							</select> <input type="hidden" name="contacts[name][]" />` :
							`${$data.name} <input type="hidden" name="contacts[pid][]" value="${$data.resource_id}" /> <input type="hidden" name="contacts[name][]" value="${$data.name}" />`
						}
					</td>
						<td class="email">${ isNew ? `<i class="mdi mdi-information"></i> Select Personnel` : `${$data.email} <input type="hidden" name="contacts[email][]" value="${$data.email}" />` }</td>
						<td class="phone">${ isNew ? `<i class="mdi mdi-information"></i> Select Personnel` : `${$data.phone} <input type="hidden" name="contacts[phone][]" value="${$data.phone}" />` }</td>
				</tr>
			`);

			row.find('select.contact').on('change', function(){
				var contact = $(this).children('option:selected').data('contact');

				row.find('td.email').html(`
					<span>${contact ? contact.email : 'N/A'}</span>
					<input type="hidden" name="contacts[email][]" value="${contact ? contact.email : ''}" />
				`);

				row.find('td.phone').html(`
					<span>${contact ? contact.phone : 'N/A'}</span>
					<input type="hidden" name="contacts[phone][]" value="${contact ? contact.phone : ''}" />
				`);

				row.find('[name="contacts[name][]"]').val(contact.name);
			});

			row.find('select.contact').select2();

			return row;
		}

		var getResourceRow = function($typ, $data, isNew=true){
			if($typ == "#personnel-resources-content"){
				var row = $(`
					<tr class="resource-row">
						<td style="width:20px">
							<input type="checkbox" ${isNew ? '' : `data-email="${$data.email}" data-name="${$data.name}"` } class="checkbox-resources" />
							<input type="hidden" name="personnel_resources[id][]" value="${$data ? $data.id : ''}" />
						</td>
						@if(isset($workorder) && $workorder->external_resource_id > 0)
							<td>
								${isNew ? `<label><input type="checkbox" name="personnel_resources[is_external][]" ${$data && $data.is_external == 1 ? 'checked' : ''} /> Is external</label>` : $data.is_external ? 'External' : 'Internal' }
							</td>
						@endif
						<td>${
							isNew ?
								`<span class="internal"><select name="personnel_resources[pid][]" class="form-control form-control-sm personnel select2" placeholder="Name...">
									<option selected>Select Personnel</option>
									@foreach($PERSONNEL as $personnel)
										<option value="{{ $personnel->pid }}" data-personnel="{{ $personnel }}">{{ $personnel->name }}</option>
									@endforeach
								</select></span>
								<input type="text" name="personnel_resources[name][]" class="form-control form-control-sm" placeholder="External Personnel Name..." />
								` :
								`${$data.name} <input type="hidden" name="personnel_resources[pid][]" value="${$data.resource_id}" /> <input type="hidden" name="personnel_resources[name][]" value="${$data.name}" />`
							}
						</td>
						<td class="email">${ isNew ?
							`<span class="internal"><i class="mdi mdi-information"></i> Select Personnel</span>
								<span class="external"><input type="text" name="personnel_resources[email][]" class="form-control form-control-sm" placeholder="External Personnel Email..." /></span>
							`
							: `${$data.email} <input type="hidden" name="personnel_resources[email][]" value="${$data.email}" />` }
						</td>
						<td class="phone">${ isNew ?
							`<span class="internal"><i class="mdi mdi-information"></i> Select Personnel</span>
							<span class="external"><input type="text" name="personnel_resources[phone][]" class="form-control form-control-sm" placeholder="External Personnel Phone..." /></span>
							`
							: `${$data.phone} <input type="hidden" name="personnel_resources[phone][]" value="${$data.phone}" />` }
						</td>
						<td>
							${ !isNew ?
							`<span class="btn btn-sm btn-transparent text-primary" data-target="#personnel-calendar-modal" data-toggle="modal" data-pname="${$data.name}" data-personnel="${$data.resource_id}" data-workorder="${$data.workorder_id}">
								<i class="mdi mdi-calendar"></i> Calendar
							</span>` : ''}
						</td>
					</tr>
				`);

				row.find('[name="personnel_resources[is_external][]"]').on('change', function(){
					if(!$(this).is(':checked')){
						row.find('.external').addClass('hidden');
						row.find('.internal').removeClass('hidden');
						row.find('[name="personnel_resources[name][]"]').attr('type', 'hidden');
					}
					else{
						row.find('.internal').addClass('hidden');
						row.find('.external').removeClass('hidden');
						row.find('[name="personnel_resources[name][]"]').attr('type', 'text');
					}
				});

				if(isNew){
					row.find('.external').addClass('hidden');
					row.find('[name="personnel_resources[name][]"]').attr('type', 'hidden');

					row.find('.personnel').on('change', function(){
						var personnel = $(this).children('option:selected').data('personnel');

						row.find('[name="personnel_resources[name][]"]').val(personnel.name);

						row.find('td.email').html(`
							<span>${personnel ? personnel.email : 'N/A'}</span>
							<input type="hidden" name="personnel_resources[email][]" value="${personnel ? personnel.email : ''}" />
						`);

						row.find('td.phone').html(`
							<span>${personnel ? personnel.phone : 'N/A'}</span>
							<input type="hidden" name="personnel_resources[phone][]" value="${personnel ? personnel.phone : ''}" />
						`);
					});
				}

				row.find('select').select2();
				return row;
			}
			else{
				var row = $(`
					<tr class="resource-row">
						<td style="width:20px">
							<input type="checkbox" class="checkbox-resources" />
							<input type="hidden" name="inventory_resources[id][]" value="${$data ? $data.id : ''}" />
						</td>
						<td>
							${
								isNew ?
								`<select name="inventory_resources[rid][]" class="form-control inventory select2" placeholder="Name..."></select>
								<input type="hidden" name="inventory_resources[name][]" />` :
								`${$data.name} <input type="hidden" name="inventory_resources[rid][]" value="${$data.resource_id}" /><input type="hidden" name="inventory_resources[name][]" value="${$data.name}" />`
							}
						</td>
						<td><input type="number" step="any" value="${$data ? $data.quantity : ''}" class="form-control" name="inventory_resources[quantity][]" placeholder="Quantity..."  /></td>
					</tr>
				`);

				row.find('select').select2({
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
					placeholder: 'Select Inventory Item...'
				});

				row.find('select').on('change', function(){
					var itemID = $(this).val();
					var $this = $(this);
					$.ajax({
						url: '/get_item_details/'+itemID,
						beforeSend: function(){

						},
						success: function(js){
							row.find('[name="inventory_resources[name][]"]').val(js.text);
						}
					});
				});

				return row;
			}
		}

		var getTopologyRow = function(data, path){
			path = path.split(' > ');
			path = path.length == 1 ? ($.trim(path[0]) == "" ? [] : path) : path;
			path.push(data.name);
			path = path.join(' > ');
      var $row = $(`
        <div class="topology-row" data-path="${path}" data-name="${data.name}" data-id="${data.id}" data-parent="${data.parent}" data-level="${data.level}"></div>
      `);

      var $text = $(`
        <div class="topology-text"></div>
      `);

      $text.append(`<div class="left-icon text-muted dropdown-toggler"><i class="fas fa-plus"></i> </div> `);

      $text.find('.dropdown-toggler').on('click', function(){
        var parnt = $(this).parents('.topology-row').first();
        var bdy = parnt.find('.topology-body');

        bdy.toggleClass('visible');

        if(bdy.is(':visible')){
          $(this).find('i').removeClass('fa-plus').addClass('fa-minus');
          fetchTopology(data.id, bdy, path);
        }
        else{
          $(this).find('i').removeClass('fa-minus').addClass('fa-plus');
        }
      });

      $text.append(`<div class="text" data-dismiss="modal">${data.name}</div>`);

      $row.append($text);

      $row.append(`<div class="topology-body"></div>`);

      return $row;
    }

    var fetchTopology = function(parentID, parent, path=""){
      $.ajax({
        url: "/topology/"+parentID,
        dataType: "json",
        beforeSend: function(){
          parent.html('<span class="text-center"><i class="fas fa-spin fa-spinner"></i> Fetching Topology...</span>');
        },
        success: function(js){
          parent.empty();
          $.each(js, function(j,s){
            var $row = getTopologyRow(s, path);
            parent.append($row);
          });
        }
      });
    }

		var generateAttachmentRow = function($modal = false, $dt = false){
			if($modal){
				var data = {
					"file": $modal.find('[name="attachments[file][]"]').clone(),
					"type": $modal.find('[name="type"]').val(),
					"title": $modal.find('[name="title"]').val(),
					"description": $modal.find('[name="description"]').val(),
					"current_user": $modal.find('[name="current_user"]').val(),
					"current_user_name": $modal.find('[name="current_user_name"]').val(),
					"current_user_email": $modal.find('[name="current_user_email"]').val(),
					"current_time": $modal.find('[name="current_time"]').val()
				}
			}

			if($dt){
				var data = {
					"file": `<span class="file-link"><a target="_blank" href="${ $dt.file }" class="btn btn-default text-primary"><i class="mdi mdi-download"></i> Download</a></span>
						<span class="hidden file-type"><input type="file" class="form-control" name="attachments[file][]" /></span>
						<span class="hidden file-type"><input type="hidden" class="form-control" value="${ $dt.file }" name="prev_file[${ $dt.id }]" /></span>
						<span class="btn btn-default btn-sm text-muted change-image" ml-2><i class="mdi mdi-sync"></i></span>
					`,
					"type": $dt.type,
					"title": $dt.title,
					"description": $dt.description,
					"current_user": $dt.user_id,
					"current_user_name": $dt.user_name,
					"current_user_email": $dt.user_email,
					"current_time": $dt.created_at
				}
			}

			var attachmentRow = getAttachmentRow(data);

			var d = new Date();
			var n = $dt ? $dt.id : d.getTime();
			var trLen = $('#attachment-items').find('tr').length;

			$('#attachment-items').append(attachmentRow);
			var attachmentRow2 = attachmentRow.clone();
			$('#attachment-items').append(attachmentRow2);
			attachmentRow2.remove();

			if($dt){
				attachmentRow.find('.change-image').on('click', function(){
					var td = $(this).parents('td');
					td.find('.file-type').toggleClass('hidden');
					td.find('.file-link').toggleClass('hidden');
				});
			}

			if($modal){
				$modal.find('.form-control').val('');
			}
		}

		$(function(){
			$('#topology-holder').on('click', '.topology-text', function(){
        var $row = $(this).parent('.topology-row');
        var topologyID = $row.data('id');
        var topologyName = $row.data('name');
        var topologyPath = $row.data('path');

        $('[name="main[topology_id]"]').val(topologyID+" >> "+topologyPath);
        $('.topology-name').text("SELECTED : "+topologyPath);
      });

			$('.selected-approver').on('change', function(){
				var parentTR = $(this).parents('tr.approval-row');
				parentTR.find('.update-btn').removeClass('disabled text-muted').addClass('text-primary');
			});

			$('.selected-approver.trigger-autoselect').on('change', function(){
				var parentTR = $(this).parents('tr.approval-row');
				parentTR.find('.update-btn').removeClass('disabled text-muted').addClass('text-primary');

				parentTR.find('.update-btn').trigger('click');
			});

			$('.update-btn').on('click', function(){
				var $this = $(this);
				var parentTR = $(this).parents('tr.approval-row');
				var role = parentTR.data('role');
				var selectedUser = parentTR.find('select.selected-approver').val();
				var approval = parentTR.data('approval');
				if(!$(this).hasClass('disabled') && confirm("Are you sure you want to change the approver?")){
					$.ajax({
						url: '/change-wo-approver/{{ $workorder->id ?? 0}}',
						data: {
							role: role,
							approval: approval,
							user: selectedUser
						},
						dataType: 'json',
						beforeSend: function(){

						},
						success: function(js){
							if(js.status){
								var uParts = selectedUser.split('^^');
								parentTR.find('.email-field').text(uParts[1]);
								parentTR.find('.phone-field').text(uParts[2]);

								$this.addClass('disabled text-muted').removeClass('text-primary');
							}
							else{
								alert(js.message);
							}
						}
					})
				}
			});

			var CALENDAR = null;
			var CALENDAREVENTS = {};

			var pushEventsToServer = function(sdata, pid, id, action='add'){
				$.ajax({
					url: '/push-personnel-schedule-slot/'+id+'/'+pid+'/'+action,
					data: sdata,
					dataType: "json",
					success: function(js){
						var strTime = js.event.key;
						if(action == 'remove'){
							delete CALENDAREVENTS[strTime];

							console.log("REMOVED", CALENDAREVENTS);
						}
						else{
							CALENDAREVENTS[strTime] = js.event;
						}

						drawCalendar(id, pid);

						if(js.failed.length > 0){
							var $failed = [];
							$.each(js.failed, function(j,s){
								$failed.push(s);
							});


							alert("The following timeslots failed: "+$failed.join(", "));
						}
					}
				});
			}

			var pushEventsToCalendar = function(){
				CALENDAR.batchRendering(() => {
					// remove all events
					CALENDAR.getEvents().forEach(event => event.remove());
					// add your new events source
					CALENDAR.addEventSource(Object.values(CALENDAREVENTS));
				});
			}

			var drawCalendar = function(wID, pID){
				if(CALENDAR){
					CALENDAR.destroy();
					CALENDAR = null;
				}

				$("#clone-timeslot-from-id").unbind('change');
				$("#clone-timeslot-from-id").val('').trigger('change');
				$("#clone-timeslot-from-id").on('change', function(){
					var pid2 = $(this).children("option:selected").val();

					if(confirm("Please confirm that you want to clone these timeslots. Please note that confirming this action will replace any configured timeslots for this user with the timeslots from the selected user. Are you sure?")){
						$.ajax({
							url: '/clone-personnel-schedule-slots/'+wID+'/'+pID+'/'+pid2,
							dataType: "json",
							success: function(js){
								drawCalendar(wID, pID);
							}
						});
					}
				});

				$.ajax({
					url: '/workorder-personnel/'+wID+'/'+pID+'/schedule',
					dataType: 'json',
					beforeSend: function(){
						CALENDAREVENTS = {};
						$('#personnel-calendar').html(`
							<div class="alert alert-info">
								<h5><i class="fas fa-spin fa-spinner"></i> Loading Personnel Schedule...</h5>
							</div>
						`);
					},
					success: function(js){
						$('#personnel-calendar').empty();
						var calendarEl = document.getElementById('personnel-calendar');

						CALENDAREVENTS = js;

						CALENDAR = new FullCalendar.Calendar(calendarEl, {
							themeSystem: 'bootstrap',
							initialView: 'timeGridWeek',
							initialDate: "{{ isset($workorder->start_date) ? \Carbon\Carbon::parse($workorder->start_date)->format('Y-m-d') : \Carbon\Carbon::now()->format('Y-m-d') }}",
							selectable: true,
							headerToolbar: {
								left: 'prev,next today',
								center: 'title',
								right: 'dayGridMonth,timeGridWeek,timeGridDay'
							},
							select: function(info) {
								pushEventsToServer({
									'start': info.startStr,
									'end': info.endStr,
									'duplicate': $('#duplicate-time-slot').is(":checked") ? 1 : 0
								}, pID, wID);
							},
							selectAllow: function(info){
								return true;
							},
							eventClick: function(info) {
								var eventObj = info.event;
								var other = eventObj.extendedProps.other;
								console.log(other ? 'yes' : 'no');
								if(other == false){
									if(confirm("Remove this time slot?")){
										pushEventsToServer({
											'start': eventObj.start,
											'end': eventObj.end
										}, pID, wID, 'remove');
									}
								}
							}
						});
						CALENDAR.render();

						pushEventsToCalendar();
					}
				});
			}

			$('#personnel-calendar-modal').on('show.bs.modal', function(e){
				var $target = $(e.relatedTarget);

				var wID = $target.data('workorder');
				var pID = $target.data('personnel');
				var pNAME = $target.data('pname');
				var lastCharInName = pNAME.charAt(pNAME.length-1).toLowerCase();

				$('#personnel-name-in-calendar').text(pNAME+(lastCharInName == 's' ? "'" : "'s"));

				drawCalendar(wID, pID);
			});

			$('[name="main[has_external]"]').on('change', function(){
				if($(this).is(':checked')){
					$('[name="main[external_resource_id]"]').removeAttr('disabled');
					$('[name="main[external_resource_id]"]').removeProp('disabled');
				}
				else{
					$('[name="main[external_resource_id]"]').val(0).trigger('change');
					$('[name="main[external_resource_id]"]').prop('disabled');
					$('[name="main[external_resource_id]"]').attr('disabled', true);
				}
			});

			// var timestampHeight = 0;

			// $('.timeline').find('.author').each(function(e){
			// 	var height = $(this).height();
			// 	if(height > timestampHeight){
			// 		timestampHeight = height;
			// 	}
			// });

			// $('.timeline').find('.author').height(timestampHeight);

			$('.modal-add-data').on('click', function(){
				var type = $(this).data('type');
				var modal = $(this).parents('.modal');

				if(type == "note"){
					generateNoteRow(modal);
				}
				else{
					generateAttachmentRow(modal);
				}

				modal.find('[data-dismiss="modal"]').trigger('click');
			});

			$('.save-details-form').on('click', function(){
				if(changedItems.length > 0){
					giveChangeReason();
				}
				else{
					$('#details-form').submit();
				}
			});

			$('#remove-wo-resources').on('click', function(){
				if($(this).hasClass('text-danger')){
					var selectedTab = $('.wo-tab.selected').data('content');

					var selected = $(selectedTab).find('.checkbox-resources:checked');

					if(selected.length == 0){
						alert("No selected");
					}
					else{
						if(confirm("Are you sure you want to remove these item(s)?")){
							selected.parents(".resource-row").remove();
						}
					}
				}
			});

			$('#remove-wo-contact').on('click', function(){
				var selected = $('#wo-contacts').find('tbody').find('.checkbox-resources:checked');

				if(selected.length == 0){
					alert("No selected");
				}
				else{
					if(confirm("Are you sure you want to remove these contact(s)?")){
						selected.parents(".resource-row").remove();
					}
				}
			});

			$('#remove-attachement-wo').on('click', function(){
				var selected = $('#attachment-items').find('.checkbox-resources:checked');

				if(selected.length == 0){
					alert("No selected");
				}
				else{
					if(confirm("Are you sure you want to remove these contact(s)?")){
						selected.parents(".resource-row").remove();
					}
				}
			});

			$('.wo-tab-content').on('change', '.checkbox-resources', function(){
				var selected = $('.wo-tab-content').find(".checkbox-resources:checked");

				if(selected.length > 0){
					$('#send-wo-resources-messages').addClass('text-success').removeClass('disabled text-muted');
					$('#remove-wo-resources').addClass('text-danger').removeClass('disabled text-muted');
				}
				else{
					$('#send-wo-resources-messages').removeClass('text-success').addClass('disabled text-muted');
					$('#remove-wo-resources').removeClass('text-danger').addClass('disabled text-muted');
				}
			});

			$('#attachment-items').on('change', '.checkbox-resources', function(){
				var selected = $('#attachment-items').find(".checkbox-resources:checked");

				if(selected.length > 0){
					// $('#add-contacts-resources').addClass('text-success').removeClass('disabled text-muted');
					$('#remove-attachement-wo').addClass('text-danger').removeClass('disabled text-muted');
				}
				else{
					// $('#add-contacts-resources').removeClass('text-success').addClass('disabled text-muted');
					$('#remove-attachement-wo').removeClass('text-danger').addClass('disabled text-muted');
				}
			});

			$('#wo-contacts').on('change', '.checkbox-resources', function(){
				var selected = $('#wo-contacts').find(".checkbox-resources:checked");

				if(selected.length > 0){
					// $('#add-contacts-resources').addClass('text-success').removeClass('disabled text-muted');
					$('#remove-wo-contact').addClass('text-danger').removeClass('disabled text-muted');
				}
				else{
					// $('#add-contacts-resources').removeClass('text-success').addClass('disabled text-muted');
					$('#remove-wo-contact').removeClass('text-danger').addClass('disabled text-muted');
				}
			});

			$('.wo-tab').on('click', function(){
				var content = $(this).data('content');

				$('.wo-tab').removeClass('selected');
				$(this).addClass('selected');

				$('.wo-tab-content').removeClass('visible');
				$(content).addClass('visible');

				if(content == "#personnel-resources-content"){
					$('#send-wo-resources-messages').removeClass('hidden');
				}
				else{
					$('#send-wo-resources-messages').addClass('hidden');
				}
			});

			$('#send-wo-resources-messages').on('click', function(){
				if($(this).hasClass('text-success')){
					$('#message-personnel-modal').modal('show');
					$('#persons-to-contact').empty();
					var selectedRows = $('#personnel-resources-content-table').find('.checkbox-resources:checked');

					var emailNames = [];
					$.each(selectedRows, function(i, e){
						var email = $(e).data('email');
						var name = $(e).data('name');

						if(email){
							emailNames.push([name, email]);
							$('#persons-to-contact').append(`<div class="form-group">
								<input type="checkbox" name="email[]" checked value="${email}" /> ${name} <small>${email}</small>
							</div>`);
						}
					});
				}
			});

			$('#add-wo-resources').on('click', function(){
				var resourceType = $('.wo-tab.selected').data('content');
				var data = $(resourceType).data('resources');
				var row = getResourceRow(resourceType, data);
				$(resourceType+"-table").find('tbody').append(row);

				$(resourceType).find('tbody').find('tr.no-data').remove();
			});

			$('#add-contacts-resources').on('click', function(){
				var data = $("#wo-contacts").data('resources');
				var row = getContactRow(data);
				$("#wo-contacts").find('tbody').append(row);

				$("#wo-contacts").find('tbody').find('tr.no-data').remove();
			});

			tinymce.init({
				selector: ".editor"
			});

			$("#save-reason").on('click', function(){
				var reason = $('#change-reason-modal').find('[name="reason"]').val();

				if($.trim(reason) == ""){
					alert("Reason is empty");
					$('#change-reason-modal').find('[name="reason"]').focus();
				}
				else{
					$('#details-form').append(`<input type="hidden" name="edit_reason[]" value="${reason}" />`);
					$('#change-reason-modal').modal('hide');
					changedItems = [];
					$('#change-reason-modal').find('[name="reason"]').val('');
				}
			});

			fetchTopology(0, $('#topology-holder'), "");

			var giveChangeReason = function(){
				$('#change-reason-modal').modal('show');
				$('#changed-fields').html(`Fields edited: ${changedItems.join(',')}.`)
			}
			@if(isset($workorder) && $workorder->current_status != "REQUEST")
				$('#Information').find('input, select, textarea').each(function(i){
					$(this).on('change', function(e){
						$(this).addClass('changed');
						changedItems.push($(this).siblings('label').text());
					});
				});
			@endif
			var getPersonnelResources = $('#personnel-resources-content-table').data('resources');
			var getInventoryResources = $('#inventory-resources-content-table').data('resources');
			var getContacts = $('#wo-contacts').data('resources');


			var preloadedData = {
				'#personnel-resources-content': getPersonnelResources,
				'#inventory-resources-content': getInventoryResources,
				'#wo-contacts': getContacts
			};

			$.each(preloadedData, function(k, v){
				if(v==null || v.length == 0){
					var colspan = $(k).find('thead tr').children().length;
					$(k+'-table').find('tbody').append(`<tr class="no-data"><td colspan="${colspan}">
						<div class="alert alert-info"><i class="mdi mdi-information"></i> No data added.</div>
					</td></tr>`);
				}
				else{
					$.each(v, function(a,b){
						var row = k == '#wo-contacts' ? getContactRow(b, false) : getResourceRow(k, b, false);
						$(k == '#wo-contacts' ? k : k+'-table').find('tbody').append(row);
					});
				}

			});

			var all_attachments = $('#attachment-items').data('attachments');

			$.each(all_attachments, function(i, e){
				generateAttachmentRow(false, e);
			});
		});

		var completes = document.querySelectorAll(".complete");
		var toggleButton = document.getElementById("toggleButton");

		function toggleComplete(){
			var lastComplete = completes[completes.length - 1];
			lastComplete.classList.toggle('complete');
		}
	</script>
@endsection