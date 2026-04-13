@extends('layouts.equipment.asset.layout.app', ['dataTable' => true, 'select2' => true])

@section('title2')
<title> {{ $equipment->name }} | Equipments</title>

<style type="text/css">
	.no-header th {
		color: #454545;
	}

	.hidden {
		display: none;
	}

	.btn-default {
		background-color: white !important;
		margin: 3px;
		padding: 3px !important;
		font-size: 13px !important;
	}
</style>
@endsection
@section('content2')
<main>
	<?php
$items = array(
	array(
		'link' => route('equipment-home'),
		'name' => 'Equipment Management',
		'icon' => null
	),
	array(
		'link' => '#',
		'name' => $equipment->name,
		'icon' => null
	)
);
	?>
	<x-bread-crumb :items="$items"></x-bread-crumb>
	<h2 class="p-4">
		<i class="mdi mdi-tools"></i> {{ $equipment->name }} <small class="text-muted"> | Equipment</small>
		<button class="btn btn-default float-right" data-toggle="modal" data-target="#edit-equipment"><i
				class="mdi mdi-pencil-box-outline text-primary"></i> Edit</button>
		<button class="btn btn-default float-right" data-toggle="modal" data-target="#create-attachment"><i
				class="mdi mdi-plus text-dark"></i>Attachment</button>
		<button class="btn btn-default float-right" data-toggle="modal" data-type="Verification"
			data-target="#add-operator-modal"><i class="mdi mdi-plus text-warning"></i> Operator</button>
		<button class="btn btn-default float-right" data-toggle="modal" data-type="Verification"
			data-target="#create-verification-log"><i class="mdi mdi-plus text-info"></i> Verification Log</button>
		<button class="btn btn-default float-right" data-toggle="modal" data-type="Repairment"
			data-target="#create-new-repairment"><i class="mdi mdi-plus text-default"></i> Repair Log</button>
		<button class="btn btn-default float-right" data-toggle="modal" data-type="Calibration"
			data-target="#create-new-calibration"><i class="mdi mdi-plus text-success"></i> Calibrate Log</button>
		<button class="btn btn-default float-right" data-toggle="modal" data-type="Maintainance"
			data-target="#create-new-maintainance"><i class="mdi mdi-plus text-danger"></i> Maintainance Log</button>
	</h2>
	<div class="row no-gutters" style="clear: both;">
		<div class="col-sm-3 p-2">
			<div class="card">
				<div class="card-body">
					<div class="p-3 center text-center align-content-center">
						<img src="{{ $equipment->picture }}" style="max-width: 80%">
					</div>
					<h5 class="p-3 text-bold text-lg text-center bg-light-gray border-bottom">
						{{ $equipment->equipment_number }}
					</h5>
					<p>{{ $equipment->description }}</p>
					@if($equipment->calibration_date()['status'] == 'text-warning')
						<small class="p-3">
							<i class="mdi mdi-alert-decagram text-warning"></i> Schedule Equipment Calibration
						</small>
					@endif
					@if($equipment->calibration_date()['status'] == 'text-danger')
						<small class="p-3">
							<i class="mdi mdi-alert text-danger"></i> Equipment Calibration Required
						</small>
					@endif
					@if($equipment->maintainance_date()['status'] == 'text-warning')
						<small class="p-3">
							<i class="mdi mdi-alert-decagram text-warning"></i> Schedule Equipment Maintainance
						</small>
					@endif
					@if($equipment->maintainance_date()['status'] == 'text-danger')
						<small class="p-3">
							<i class="mdi mdi-alert text-danger"></i> Equipment Maintainance Required
						</small>
					@endif
					<table class="table table-condensed table-borderless table-banded table-sm table-striped no-header">
						<tr>
							<th><i class="mdi mdi-information"></i> Make</th>
							<td>{{ $equipment->make }}</td>
						</tr>
						<tr>
							<th><i class="mdi mdi-information-outline"></i> Model</th>
							<td>{{ $equipment->model }}</td>
						</tr>
						<tr>
							<th><i class="mdi mdi-calendar-month"></i> Purchased On</th>
							<td>{{ $equipment->date_purchased }}</td>
						</tr>
						<tr>
							<th><i class="mdi mdi-ruler-square-compass"></i> Next Calibration</th>
							<td>
								{{ $equipment->calibration_date()['date']->toDateString() }}
								<small
									class="ml-1 badge {{ $equipment->calibration_date()['status'] }}">{{ number_format(intval($equipment->calibration_date()['remaining_days'])) }}
									days</small>
							</td>
						</tr>
						<tr>
							<th><i class="mdi mdi-pipe-wrench"></i> Next Maintainance</th>
							<td>
								{{ $equipment->maintainance_date()['date']->toDateString() }}
								<small
									class="ml-1 badge {{ $equipment->maintainance_date()['status'] }}">{{ number_format(intval($equipment->maintainance_date()['remaining_days'])) }}
									days</small>
							</td>
						</tr>
					</table>
				</div>
			</div>
		</div>
		<div class="col-sm-9 p-2">
			<div class="card tab-card">
				<div class="card-header tab-card-header">
					<ul class="nav nav-tabs card-header-tabs" id="equipment-tabs" role="tablist">
						<li class="nav-item">
							<a class="nav-link active" id="maintainance-log-tab" data-toggle="tab"
								href="#maintainance-log" role="tab" aria-controls="maintainance-log"
								aria-selected="true">Maintainance Log</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" id="calibration-log-tab" data-toggle="tab" href="#calibration-log"
								role="tab" aria-controls="calibration-log" aria-selected="true">Calibration Log</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" id="usage-log-tab" data-toggle="tab" href="#repairement-log" role="tab"
								aria-controls="Usage" aria-selected="false">Repair Log</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" id="usage-log-tab" data-toggle="tab" href="#verification-log" role="tab"
								aria-controls="Usage" aria-selected="false">Verification Log</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" id="equipment-operators-tab" data-toggle="tab"
								href="#equipment-operators" role="tab" aria-controls="Usage"
								aria-selected="false">Operators</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" id="equipment-attachment-tab" data-toggle="tab"
								href="#equipment-attachment" role="tab" aria-controls="Usage"
								aria-selected="false">Attachments</a>
						</li>
						<li class="nav-item">
							<a href="#notification-frequency-tab" class="nav-link" id="notification-frequecy"
								data-toggle="tab" role="tab" aria-controls="Usage" aria-selected="false">Notification
								Frequency</a>
						</li>
					</ul>
				</div>

				<div class="tab-content" id="equipment-tabs-content">
					<!-- --------------------------Notification-------------------- -->
					<div class="tab-pane fade show p-3" id="notification-frequency-tab" role="tabpanel"
						aria-labelledby="one-tab">
						<h5 class="card-title">
							Notification Frequency
							<span class="btn btn-outline-primary btn-sm float-right" data-toggle="modal"
								data-target="#add-notification" data-action="add"><i class="mdi mdi-plus"></i>
								Add</span>
						</h5>
						<div class="table-responsive">
							<table
								class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
								<thead>
									<th>#</th>
									<th>Frequency</th>
									<th>Notification Type</th>
									<th>Notification Date</th>
									<th>Status</th>
								</thead>
								<tbody>
									@foreach ($notifications as $notification)
										<tr>
											<td>
												<span class="btn btn-sm btn-default text-primary" data-toggle="modal"
													data-target="#add-notification" data-action="edit"
													data-record="{{$notification}}"><i class="mdi mdi-pencil"></i></span>
												<span class="btn btn-sm btn-default text-danger" data-toggle="modal"
													data-target="#delete-notification" data-record="{{$notification}}"><i
														class="mdi mdi-delete-empty"></i></span>
											</td>
											<td>{{$notification->value}} {{$notification->frequency}}</td>
											<td>{{ucwords($notification->notification_type)}}</td>
											<td>{{$notification->next_date}}</td>
											<td>{!!$notification->is_sent != '' ? '<span class="badge badge-pill p-2 badge-success">Sent</span>' : '<span class="badge badge-pill p-2 badge-warning">Not Sent</span>' !!}
											</td>
										</tr>
									@endforeach
								</tbody>
							</table>
						</div>
					</div>
					<!-- ----------------------attach----------------------- -->
					<div class="tab-pane fade show p-3" id="equipment-attachment" role="tabpanel"
						aria-labelledby="one-tab">
						<h5 class="card-title">Attachments </h5>
						<div class="table-responsive">
							<table
								class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
								<thead class="bg-light p-2">
									<tr>
										<th>No</th>
										<th>Title</th>
										<th>Attachment</th>
										<th>Uploaded By</th>
										<th nowrap>Edited By</th>
										<th nowrap>Description</th>
										<th></th>
									</tr>
								</thead>
								<tbody>
									@foreach($attachments as $a)
										<tr>

											<td>{{$loop->iteration}}</td>
											<td>{{$a->title}}</td>
											<td class="text-center"><a target="_blank" href="{{$a->attachment}}"
													class="btn btn-sm"><i class="mdi mdi-download"></i></a></td>
											<td>{{ getUserById($a->upload_by)->name ?? 'n/a'  }}</td>
											<td>{{ getUserById($a->edit_by)->name ?? 'n/a'  }}</td>
											<td class="text-center"><span class="btn btn-dark btn-sm"
													data-description="{{$a->description}}"
													data-target="#attachment-description" data-toggle="modal"
													data-toggle="tooltip" title="View Description"><i
														class="mdi mdi-clipboard-text"></i></span></td>
											<td><span class="btn btn-outline-primary btn-sm"><i class="mdi mdi-pencil"
														data-attachment="{{json_encode($a)}}" data-toggle="modal"
														data-target="#edit-attachment" data-toggle="tooltip"
														title="Edit"></i></span></td>
										</tr>
									@endforeach
								</tbody>
							</table>
						</div>
					</div>
					<!-- ------------------maintainance------------------- -->
					<div class="tab-pane fade show active p-3" id="maintainance-log" role="tabpanel"
						aria-labelledby="one-tab">
						<h5 class="card-title">Maintainance Logs </h5>
						<div class="table-responsive">
							<table
								class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
								<thead class="bg-light p-2">
									<tr>
										<th></th>
										<th>Type</th>
										<th nowrap>Service Provider</th>
										<th nowrap>Date</th>
										<th>Certificate</th>
										<th nowrap>Overseen By</th>
										<th nowrap>Edited By</th>
										<th nowrap>Notes</th>

									</tr>
								</thead>
								<tbody>
									<?php $lp = 0 ?>
									@foreach ($equipment->maintainance_Calibration_logs as $item)
										@if ($item->type == "Maintainance")
											<?php $lp += 1; ?>
											<tr>
												<td style="min-width: 70px;">
													<span class="btn btn-outline-info btn-sm" data-toggle="modal"
														id="edit-maintenance" data-target="#edit-maintainance"
														data-cal="{{json_encode($item)}}"
														data-suppliers="{{json_encode($suppliers)}}"
														data-employees="{{json_encode($employees)}}" data-toggle="tooltip"
														title="Edit"> <i class="mdi mdi-pencil"></i></span>
													<span class="btn btn-sm btn-outline-danger" data-toggle="modal"
														data-target="#delete-maintainance" data-toggle="tooltip"
														data-item="{{json_encode($item->id)}}" data-name="Maintainance Log"
														title="Delete"><i class="mdi mdi-delete-empty"></i></span>

												</td>
												<td>{{$item->maintainance_type == 'in-house' ? 'In house' : 'External'}}</td>
												<td>

													{{ $item->maintainance_type != 'in-house' ? getSupplierByID($item->supplier_id)->name ?? '-' : getUserById($item->employee_id)->name ?? '-'}}

												</td>
												<td>{{ $item->date  }}</td>

												<td><a href="{{ $item->certificate }}" class="btn btn-sm btn-transparent"
														target="_blank"><i class="mdi mdi-download text-success"></i>
														Download</a></td>
												<td>{{ $item->overseer()->name ?? '' }}</td>
												<td>{{ getUserById($item->edit_by)->name ?? 'n/a'  }}</td>
												<td>
													<span class="btn btn-info btn-sm" data-toggle="modal"
														data-target="#content"><i class="mdi mdi-eye"></i></span>
													<div id="content" class="modal fade" role="dialog">
														<div class="modal-dialog">
															<!-- Modal content-->
															<div class="modal-content">
																<div class="modal-header">
																	<h3 class="modal-title">Maintainance {{$lp}}.</h4>
																</div>
																<div class="modal-body">
																	<h5>Maintainance Notes</h5>
																	<div class="panel panel-default">
																		<div class="panel-body">
																			<p>{{$item->notes}}</p>
																		</div>
																	</div>
																</div>
																<div class="modal-footer">
																	<button type="button" class="btn btn-danger"
																		data-dismiss="modal">Close</button>
																</div>
															</div>

														</div>
													</div>
												</td>

											</tr>
										@endif
									@endforeach
								</tbody>
							</table>
						</div>
					</div>
					<!-- -----verify---  -->
					<div class="tab-pane fade show  p-3" id="verification-log" role="tabpanel"
						aria-labelledby="one-tab">
						<h5 class="card-title">Verification Logs </h5>
						<div class="table-responsive">
							<table
								class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
								<thead class="bg-light p-2">
									<tr>
										<th></th>
										<th>Type</th>
										<th>Reference Standards</th>
										<th>Service Performer</th>
										<th nowrap>Verification Date</th>

										<th>Description</th>



									</tr>
								</thead>
								<tbody>

									@foreach ($verifys as $item)
										@if($item->is_delete == 0 and $item->equipment_id == $equipment->id)

											<tr>
												<td style="min-width:70px">
													<span class="btn btn-outline-info btn-sm" data-toggle="modal"
														data-target="#edit-verification-{{ $lp }}"> <i class="mdi mdi-pencil"
															data-toggle="tooltip" title="Edit"></i> </span>
													<a href="{{ route('delete-verification', ['id' => $item->id]) }}"
														class="btn btn-outline-danger btn-sm" data-toggle="tooltip"
														title="Delete"> <i class="mdi mdi-delete"></i></a>
													<div id="edit-verification-{{ $lp }}" class="modal fade" role="dialog">
														<div class="modal-dialog">
															<!-- Modal content-->
															<form class="modal-content" method="POST"
																action="{{ route('edit-verification', ['id' => $item->id]) }}"
																enctype="multipart/form-data">
																@csrf
																<div class="modal-header">
																	<h4 class="modal-title"><i class="mdi mdi-pencil"></i> Edit
																		Verification Log {{$lp}}</h4>
																</div>
																<div class="modal-body">
																	<div class="form-group">
																		<label class="control-label">Reference Standards</label>
																		<input type="text" class="form-control" name="reference"
																			value="{{$item->reference_standard}}"
																			placeholder="Reference Standards..." required />
																	</div>
																	<div class="form-group hidden">
																		<label class="control-label">Equipment</label>
																		<input type="text" class="form-control" name="equipment"
																			value="{{$equipment->id}}"
																			placeholder="equipment..." required />
																	</div>
																	<div class="form-group ">
																		<label class="control-label">Date of Verifiation</label>
																		<input class="form-control" type="date" name="date"
																			value="{{$item->verification_date}}"
																			placeholder="Date of Verification...">
																	</div>

																	<div class="form-group">
																		<label class="control-label">Operator</label>
																		<select name="operator" class="form-control"
																			placeholder="Operator...">
																			@foreach($employees as $employee)
																				<option value="{{$employee->id}}">
																					{{$employee->name}}
																				</option>
																			@endforeach
																		</select>
																	</div>

																	<div class="form-group">
																		<label class="control-label">Procedure</label>
																		<textarea class="form-control" value="" rows="4"
																			name="procedure" placeholder="Procedure..."
																			required>{{$item->procedure}}</textarea>
																	</div>

																	<div class="form-group">
																		<label class="control-label">Responses/Readings</label>
																		<textarea class="form-control" rows="4" name="response"
																			value="" placeholder="Responses..."
																			required>{{$item->response}}</textarea>
																	</div>

																	<div class="form-group">
																		<label class="control-label">Remarks</label>
																		<textarea class="form-control" rows="4" name="remark"
																			value="" placeholder="Remarks..."
																			required>{{$item->remarks}}</textarea>
																	</div>


																</div>
																<div class="modal-footer">
																	<button type="submit" class="btn btn-primary"><i
																			class="mdi mdi-content-save"></i> Save</button>
																	<button type="button" class="btn btn-danger"
																		data-dismiss="modal">Close</button>
																</div>
															</form>
														</div>
													</div>
												</td>
												<td>{{$item->maintainance_type == 'in-house' ? 'In house' : 'External'}}</td>
												<td>{{ $item->reference_standard  }}</td>
												<td>

													{{ $item->maintainance_type != 'in-house' ? getSupplierByID($item->supplier_id)->name ?? '' : getUserById($item->operator_id)->name}}

												</td>
												<td>{{ $item->verification_date }}</td>

												<td>
													<span style="margin-right: 30px;" class="btn btn-success btn-sm"
														data-toggle="modal" data-target="#view-verification-{{ $lp }}"> <i
															class="mdi mdi-eye"></i> </span>

													<div id="view-verification-{{$lp}}" class="modal fade" role="dialog">
														<div class="modal-dialog">
															<div class="modal-content">
																<div class="modal-header">
																	<h4 class="modal-title">Verification {{$lp}}</h4>
																</div>
																<div class="modal-body">
																	<h5>Verification Procedure</h5>
																	<div class="panel panel-default">
																		<div class="panel-body">
																			{{$item->procedure}}
																		</div>
																	</div>
																	<br>

																	<h5>Responses</h5>
																	<div class="panel panel-default">
																		<div class="panel-body">
																			{{$item->response}}
																		</div>
																	</div>
																	<br>

																	<h5>Remarks</h5>
																	<div class="panel panel-default">
																		<div class="panel-body">
																			{{$item->remarks}}
																		</div>
																	</div>
																</div>
																<div class="modal-footer">
																	<button type="button" class="btn btn-danger"
																		data-dismiss="modal">Close</button>
																</div>
															</div>
														</div>
													</div>
												</td>

											</tr>
										@endif
									@endforeach
								</tbody>
							</table>
						</div>
					</div>
					<!-- -----verify end---  -->
					<!-- -------------repair----------------------------------- -->
					<div class="tab-pane fade p-3" id="repairement-log" role="tabpanel" aria-labelledby="one-tab">
						<h5 class="card-title">Repair Logs </h5>
						<div class="table-responsive">
							<table
								class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
								<thead class="bg-light p-2">
									<tr>
										<th>No</th>
										<th>Type</th>
										<th nowrap>Service Performer</th>
										<th nowrap>Date</th>

										<th>Certificate</th>
										<th nowrap>Overseen By</th>
										<th nowrap>Edited By</th>
										<th nowrap>Repaired Parts</th>
										<th>Description</th>


									</tr>
								</thead>
								<tbody>

									@foreach ($equipment->maintainance_Calibration_logs as $item)
										@if ($item->type == "Repairement")
											<?php $part_ = getRepairLogParts($item->id) ?>
											<tr>
												<td style="min-width: 70px;">
													<span class="btn btn-outline-info btn-sm" data-toggle="modal"
														data-employees="{{json_encode($employees)}}"
														data-suppliers="{{json_encode($suppliers)}}"
														data-parts="{{json_encode($part_)}}"
														data-repair="{{json_encode($item)}}" data-target="#edit-new-repairment"
														data-backdrop="static" data-keyboard="false" data-toggle="tooltip"
														title="Edit"> <i class="mdi mdi-pencil"></i></span>
													<span class="btn btn-sm btn-outline-danger" data-toggle="modal"
														data-target="#delete-maintainance" data-toggle="tooltip"
														data-item="{{json_encode($item->id)}}" data-name="Repair Log"
														title="Delete"><i class="mdi mdi-delete-empty"></i></span>

												</td>
												<td>{{$item->maintainance_type == 'in-house' ? 'In house' : 'External'}}</td>
												<td>

													{{ $item->maintainance_type != 'in-house' ? getSupplierByID($item->supplier_id)->name ?? '-' : getUserById($item->employee_id)->name ?? '-'}}

												</td>
												<td>{{ $item->date  }}</td>

												<td><a href="{{ $item->certificate }}" class="btn btn-sm btn-transparent"
														target="_blank"><i class="mdi mdi-download text-success"></i>
														Download</a></td>
												<td>{{ $item->overseer()->name }}</td>
												<td>{{ $item->editor()->name ?? 'n/a'  }}</td>

												<td>
													<!-- --------------  -->
													<span class="btn btn-info btn-sm"
														data-item="{{json_encode($equipment->name)}}"
														data-parts="{{json_encode($part_)}}" data-toggle="modal"
														data-target="#repair-content"><i class="mdi mdi-eye"></i></span>

													<!-- --------------  -->


												</td>
												<td>
													<span style="margin-right: 30px;" class="btn btn-success btn-sm"
														data-toggle="modal" data-target="#view-repair-{{ $lp }}"> <i
															class="mdi mdi-eye"></i> </span>
													<div id="view-repair-{{$lp}}" class="modal fade" role="dialog">
														<div class="modal-dialog">
															<div class="modal-content">
																<div class="modal-header">
																	<h5 class="modal-title">Repair Description</h5>
																</div>
																<div class="modal-body">

																	<div class="panel panel-default">
																		<div class="panel-body">
																			{{$item->description}}
																		</div>
																	</div>
																	<br>


																</div>
																<div class="modal-footer">
																	<button type="button" class="btn btn-danger"
																		data-dismiss="modal">Close</button>
																</div>
															</div>
														</div>
													</div>
												</td>


											</tr>
										@endif
									@endforeach
								</tbody>
							</table>
						</div>
					</div>
					<!-- ---------------------------------------------------------- -->
					<div class="tab-pane fade p-3" id="equipment-operators" role="tabpanel" aria-labelledby="one-tab">
						<h5 class="card-title">
							Operators
							<button class="btn btn-primary float-right btn-sm" data-toggle="modal"
								data-target="#add-operator-modal"><i class="mdi mdi-plus"></i> Add</button>
						</h5>
						<div class="p-0">
							@foreach ($equipment->operators() as $item)
								<button type="button" class="btn btn-default-light btn-sm m-2"
									style="border: 1px solid #ccc">
									<i class="mdi mdi-account"></i> {{ $item->operator()->name }}
									<small>{{ $item->operator()->email }}</small>
									<i class="mdi mdi-close-circle text-danger" style="font-size: 16px; padding-top: 2px"
										data-toggle="modal" data-target="#delete-operator-{{ $loop->iteration }}"></i>
								</button>
								<div id="delete-operator-{{ $loop->iteration }}" class="modal fade" role="dialog">
									<div class="modal-dialog">
										<!-- Modal content-->
										<form class="modal-content" method="POST"
											action="{{ route('remove-operator', ['id' => $item->id]) }}"
											enctype="multipart/form-data">
											@csrf
											<div class="modal-header">
												<h4 class="modal-title"><i class="mdi mdi-delete"></i> Remove Operator</h4>
											</div>
											<div class="modal-body">
												<div class="form-group">
													<div class="alert alert-callout alert-danger">
														<i class="fas fa-exclamation-triangle"></i> Are you sure that you
														want to remove this operator?
													</div>
												</div>
											</div>
											<div class="modal-footer">
												<button type="submit" class="btn btn-danger remove-operator"><i
														class="mdi mdi-trash"></i> Remove</button>
												<button type="button" class="btn btn-default"
													data-dismiss="modal">Close</button>
											</div>
										</form>
									</div>
								</div>
							@endforeach
							@if(count($equipment->operators()) == 0)
								<div class="alert alert-info">
									<i class="mdi mdi-alert"></i> No Equipment Operators added yet.
								</div>
							@endif
						</div>
					</div>
					<div class="tab-pane fade p-3" id="calibration-log" role="tabpanel" aria-labelledby="one-tab">
						<h5 class="card-title">Calibration Logs </h5>
						<div class="table-responsive">
							<table
								class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
								<thead class="bg-light p-2">
									<tr>
										<th></th>
										<th>Type</th>
										<th nowrap>Service Provider</th>
										<th nowrap>Date</th>
										<th>Certificate</th>
										<th nowrap>Overseen By</th>
										<th nowrap>Edited By</th>
										<th nowrap>Notes</th>

									</tr>
								</thead>
								<tbody>

									@foreach ($equipment->maintainance_Calibration_logs as $item)
										@if ($item->type == "Calibration")

											<tr>
												<td style="min-width: 70px;">
													<span class="btn btn-outline-info btn-sm" data-toggle="modal"
														data-target="#edit-calibration"
														data-calibration="{{json_encode($item)}}"
														data-employees="{{json_encode($employees)}}"
														data-suppliers="{{json_encode($suppliers)}}" data-toggle="tooltip"
														title="Edit"> <i class="mdi mdi-pencil"></i></span>
													<span class="btn btn-sm btn-outline-danger" data-toggle="modal"
														data-target="#delete-maintainance" data-toggle="tooltip"
														data-item="{{json_encode($item->id)}}" data-name="Calibration Log"
														title="Delete"><i class="mdi mdi-delete-empty"></i></span>

												</td>
												<td>{{$item->maintainance_type == 'in-house' ? 'In house' : 'External'}}</td>
												<td>
													{{ $item->maintainance_type != 'in-house' ? getSupplierByID($item->supplier_id)->name : getUserById($item->employee_id)->name}}
												</td>
												<td>{{ $item->date  }}</td>
												<td><a href="{{ $item->certificate }}" class="btn btn-sm btn-transparent"
														target="_blank"><i class="mdi mdi-download text-success"></i>
														Download</a></td>
												<td>{{ $item->overseer()->name ?? '' }}</td>
												<td>{{ getUserById($item->edit_by)->name ?? 'n/a'  }}</td>
												<td>
													<span class="btn btn-info btn-sm" data-toggle="modal"
														data-target="#content-cal"><i class="mdi mdi-eye"></i></span>
													<div id="content-cal" class="modal fade" role="dialog">
														<div class="modal-dialog">
															<!-- Modal content-->
															<div class="modal-content">
																<div class="modal-header">
																	<h3 class="modal-title">Calibration {{$lp}} .</h4>
																</div>
																<div class="modal-body">
																	<h5>Calibration Notes</h5>
																	<div class="panel panel-default">
																		<div class="panel-body">
																			<p>{{$item->notes}}</p>
																		</div>
																	</div>
																</div>
																<div class="modal-footer">
																	<button type="button" class="btn btn-danger"
																		data-dismiss="modal">Close</button>
																</div>
															</div>

														</div>
													</div>
												</td>

											</tr>
										@endif
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
<div class="modal fade" id="delete-notification" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<form action="{{route('delete-equipment-frequency')}}" method="post">
				@csrf
				<div class="modal-body">
					
				</div>
				<div class="modal-footer">
					<button class="btn btn-sm btn-outline-danger" type="submit"><i class="mdi mdi-thumbs-up"></i> Yes, Delete</button>
					<span class="btn btn-sm btn-default" data-dismiss="modal">Close</span>
				</div>
			</form>
		</div>
	</div>
</div>
<div class="modal fade" id="add-notification" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<form action="{{route('add-equipment-frequency')}}" method="post">
				@csrf
				<div class="modal-body">

				</div>
				<div class="modal-footer">
					<button class="btn btn-sm btn-outline-primary save-notification" type="submit">Save</button>
					<span class="btn btn-sm btn-default" data-dismiss="modal">Close</span>
				</div>
			</form>
		</div>
	</div>
</div>
<div class="modal fade" id="delete-maintainance" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<form action="{{route('delete-logs')}}" method="post">
				@csrf
				<div class="modal-body">

				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-outline-success btn-sm"><i class="mdi mdi-content-save"></i>
						Confirm</button>
					<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
				</div>
			</form>
		</div>
	</div>
</div>
<div id="attachment-description" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-body">

			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
			</div>
		</div>

	</div>
</div>
<div class="modal fade" id="create-attachment" role="dialog">
	<div class="modal-dialog">''
		<form action="{{route('add_equipment_attachment')}}" enctype="multipart/form-data" method="post"
			class="modal-content">
			@csrf
			<div class="modal-header">
				<h5 class="modal-title">Add attachment for equipment {{$equipment->name}}</h5>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<label class="control-label">Title <span class="text-danger">*</span></label>
					<input type="text" name="title" required placeholder="Title..." class="form-control">
					<input type="hidden" name="equipment_id" value="{{$equipment->id}}">
					<input type="hidden" name="attach_id" value="">
				</div>
				<div class="form-group">
					<label class="control-label">Attachment <span class="text-danger">*</span></label>
					<input type="file" name="attachment" class="form-control" required>
				</div>
				<div class="form-group">
					<label class="control-label">Description</label>
					<textarea class="form-control" rows="6" name="description" placeholder="Notes..."></textarea>
				</div>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-outline-primary btn-sm"><i class="mdi mdi-content-save"></i>
					Save</button>
				<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
			</div>
		</form>
	</div>
</div>
<div class="modal fade" id="edit-attachment" role="dialog">
	<div class="modal-dialog">
		<form action="{{route('add_equipment_attachment')}}" enctype="multipart/form-data" method="post"
			class="modal-content">
			@csrf
			<div class="modal-header"></div>
			<div class="modal-body"></div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-outline-primary btn-sm"><i class="mdi mdi-content-save"></i>
					Save</button>
				<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
			</div>
		</form>
	</div>
</div>
<div id="edit-calibration" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<form class="modal-content" method="POST" action="{{ route('edit-maintainance') }}"
			enctype="multipart/form-data">
			@csrf
			<div class="modal-header">
				<h4 class="modal-title"><i class="mdi mdi-pencil"></i> Edit Calibration Log</h4>
			</div>
			<div class="modal-body" id="edit-calibration-body"></div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</form>
	</div>
</div>
<div id="edit-maintainance" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<form class="modal-content" method="POST" action="{{ route('edit-maintainance') }}"
			enctype="multipart/form-data">
			@csrf
			<div class="modal-header">

			</div>
			<div class="modal-body" id="edit-maintainance-body"></div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</form>
	</div>
</div>
<div id="edit-equipment" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<form class="modal-content" method="POST" action="{{ route('edit-equipment', ['id' => $equipment->id]) }}"
			enctype="multipart/form-data">
			@csrf
			<div class="modal-header">
				<h4 class="modal-title"><i class="mdi mdi-pencil"></i> Edit Equipment</h4>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<label class="control-label">Name <span class="text-danger">*</span></label>
					<input type="text" class="form-control" name="name" value="{{ $equipment->name }}"
						placeholder="Name..." required />
				</div>
				<div class="form-group">
					<label class="control-label">Number <span class="text-danger">*</span></label>
					<input type="text" class="form-control" name="equipment_number"
						value="{{ $equipment->equipment_number }}" placeholder="Equipment Number..." required />
				</div>
				<div class="form-group">
					<label class="control-label">Photo <small style="color: red;">*leave it blank to maintain the
							present image</small></label>
					<input type="file" class="form-control" name="photo" />
				</div>
				<div class="form-group">
					<label class="control-label">Description <span class="text-danger">*</span></label>
					<textarea class="form-control" name="description" placeholder="Equipment Description..."
						required>{{ $equipment->description }}</textarea>
				</div>
				<div class="form-group">
					<label class="control-label">Make <span class="text-danger">*</span></label>
					<input type="text" class="form-control" name="make" value="{{ $equipment->make }}"
						placeholder="Equipment Make..." required />
				</div>
				<div class="form-group">
					<label class="control-label">Model <span class="text-danger">*</span></label>
					<input type="text" class="form-control" name="model" value="{{ $equipment->model }}"
						placeholder="Equipment Model..." required />
				</div>
				<!-- ------------------ -->
				<?php
$asset_types = getAssetTypes();
$locations = getAssetLocation();
				?>
				<div class="form-group">
					<label class="control-label">Asset Type</label>
					<select name="asset_type_id" aria-readonly="true" aria-placeholder="Choose Asset Type"
						class="form-control">
						@foreach($asset_types as $type)
							@if($type->is_active == 1)
								<option value="{{$type->id}}" {{$equipment->asset_type_id == $type->id ? 'selected' : '' }}>
									{{$type->asset_code}} ({{$type->descripton}})
								</option>
							@endif
						@endforeach
					</select>
				</div>
				<div class="form-group">
					<label class="control-label">Asset Location</label>
					<select name="location_id" aria-readonly="true" id="" class="form-control">
						@foreach($locations as $location)
							@if($location->is_active == 1)
								<option value="{{$location->id}}" {{$equipment->asset_location_id == $location->id ? 'selected' : ''}}>{{$location->name}}</option>
							@endif
						@endforeach
					</select>
				</div>
				<div class="form-group">
					<label class="control-label">Serial Number <span class="text-danger">*</span></label>
					<input type="text" class="form-control" name="serial" value="{{$equipment->serial_number}}"
						placeholder="Serial Number..." required />
				</div>
				<div class="form-group">
					<label class="control-label">Barcode Number</label>
					<input type="text" class="form-control" name="barcode" value="{{$equipment->barcode_number}}"
						placeholder="Barcode Number..." />
				</div>
				<div class="form-group">
					<label class="control-label">Manufacturer</label>
					<input type="text" class="form-control" name="manufacturer" value="{{$equipment->manufacturer}}"
						placeholder="Manufacturer..." />
				</div>
				<div class="form-group">
					<label class="control-label">Status</label>
					<select name="status" id="assign-status" class="form-control" readonly="true"
						placeholder="Assign Status...">
						@foreach ($statuses as $status)
							<option value="{{$status}}" {{$equipment->status == $status ? 'selected' : ''}}>{{$status}}
							</option>
						@endforeach
					</select>
				</div>
				<div class="form-group">
					<label class="control-label">Equipment Condition <span class="text-danger">*</span></label>
					<input type="text" class="form-control" name="condition" value="{{$equipment->condition}}"
						placeholder="Equipment Condition..." required />
				</div>
				<div class="form-group">
					<label class="control-label">Assign department <span class="text-danger">*</span></label>
					<select name="department" id="" class="form-control" placeholder="Assign Department..." required>
						<option value="">Choose Department...</option>
						@foreach($departments as $department)
							<option value="{{$department->id}}" {{$equipment->assigned_department == $department->id ? 'selected' : ''}}>{{$department->name}}</option>
						@endforeach
					</select>

				</div>
				<div class="form-group">
					<label class="control-label">Assign Employee</label>
					<select name="employee" id="assign-employee" class="form-control" readonly="true"
						placeholder="Assign Employee...">
						@foreach ($employees as $employee)
							<option value="{{$employee->id}}">{{$employee->name}}</option>
						@endforeach
					</select>
				</div>
				<div class="form-group">
					<label class="control-label">Warranty Date <span class="text-danger">*</span></label>
					<input type="date" class="form-control" name="warranty" value="{{$equipment->warranty_date}}"
						placeholder="Warranty Date..." required />
				</div>
				<!-- -------------------- -->
				<div class="form-group">
					<label class="control-label">Date Purchased <span class="text-danger">*</span></label>
					<input type="date" class="form-control" name="date_purchased"
						value="{{ $equipment->date_purchased }}" placeholder="Date Purchased..." required />
				</div>
				<div class="form-group">
					<div class="row no-gutters">
						<div class="col-xs-7">
							<label class="control-label">Maintainance After<small>(In Days)</small></label>
							<input type="number" min="0" class="form-control" name="maintainance_in_days"
								value="{{ $equipment->maintainance_days }}" placeholder="Maintanance In Days..."
								required />
						</div>
						<div class="col-xs-5 pl-2">
							<label class="control-label">Notification (In Days)</small></label>
							<input type="number" min="0" class="form-control" name="maintainance_notification_in_days"
								value="{{ $equipment->maintainance_notification_in_days }}"
								placeholder="Notification Days..." required />
						</div>
					</div>
				</div>
				<div class="form-group">
					<div class="row no-gutters">
						<div class="col-xs-7">
							<label class="control-label">Calibration After<small>(In Days)</small></label>
							<input type="number" min="0" class="form-control" name="calibration_in_days"
								value="{{ $equipment->calibration_days }}" placeholder="Calibration In Days..."
								required />
						</div>
						<div class="col-xs-5 pl-2">
							<label class="control-label">Notification (In Days)</small></label>
							<input type="number" min="0" class="form-control" name="calibration_notification_in_days"
								value="{{ $equipment->calibration_notification_in_days }}"
								placeholder="Notification Days..." required />
						</div>
					</div>
				</div>
				<div class="form-group">
					<label class="control-label"><input name="active" value="1" type="checkbox" {{ $equipment->active == 1 ? 'checked' : '' }} /> Is Active</label>
				</div>
				<div class="form-group">
					<label class="control-label"><input name="requires_daily_log" value="1" type="checkbox" {{ $equipment->requires_daily_log ? 'checked' : '' }} /> Requires Daily Log</label>
					<small class="form-text text-muted">When checked, this equipment will appear on the Equipment Daily Log page.</small>
				</div>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</form>
	</div>
</div>
<div id="create-new-maintainance" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<form class="modal-content" method="POST" action="{{ route('new-maintainance', ['id' => $equipment->id]) }}"
			enctype="multipart/form-data">
			@csrf
			<div class="modal-header">
				<h4 class="modal-title"><i class="mdi mdi-pencil"></i> Create <span
						class="select-maintainance-type"></span> Log</h4>
			</div>
			<div class="modal-body">
				<div class="form-group hidden">
					<label class="control-label">Service Provider <span class="text-danger">*</span></label>
					<select name="service_provider" id="assign-supplier" placeholder="Service Provider"
						class="form-control" required readonly="true">
						@foreach($suppliers as $supplier)
							<option value="{{$supplier->id}}">{{$supplier->name}}</option>
						@endforeach
					</select>
				</div>
				<div class="form-group hidden">
					<label class="control-label">Type</label>
					<input class="form-control" type="text" name="type" value="Maintainance" placeholder="Type...">

				</div>

				<div class="form-group">
					<label class="control-label">Date <span class="text-danger">*</span></label>
					<input type="date" class="form-control" name="date" value="" placeholder="Date..." required />
				</div>
				<div class="form-group">
					<label class="control-label">Description <span class="text-danger">*</span></label>
					<textarea class="form-control" rows="6" name="description" placeholder="Description..."
						required></textarea>
				</div>
				<div class="form-group">
					<label class="control-label">Reference Number </label>
					<input type="text" class="form-control" name="reference" placeholder="Reference Number..." />
				</div>
				<!-- -----------  -->

				<div class="form-check">
					<input class="form-check-input" type="radio" id="is-house" class="form-control"
						name="maintainance_type" value="in_house" onclick="inhouse()" />
					<label class="form-check-label" for="is-house">
						In House Service Performer
					</label>
				</div>
				<br>
				<div class="form-check">
					<input class="form-check-input" type="radio" id="externals" class="form-control"
						name="maintainance_type" value="external" onclick="external()" />
					<label class="form-check-label" for="externals">
						External Service Performer
					</label>
				</div>
				<br>
				<div class="form-group" id="employee" style="display:none">
					<label class="control-label">Employee</label>
					<select name="employee" class="form-control" placeholder="Employee...">
						@foreach($employees as $employee)
							<option value="{{$employee->id}}">{{$employee->name}}</option>
						@endforeach
					</select>
				</div>
				<div class="form-group" id="supplier" style="display:none">
					<label class="control-label">Supplier</label>
					<select name="supplier" class="form-control" placeholder="Supplier...">
						@foreach($suppliers as $supplier)
							<option value="{{$supplier->id}}">{{$supplier->name}}</option>
						@endforeach
					</select>
				</div>
				<!-- ----------  -->
				<div class="form-group">
					<label class="control-label">Certificate</label>
					<input type="file" class="form-control" name="certificate" value="" placeholder="Certificate..." />
				</div>
				<div class="form-group">
					<label class="control-label">Remark <span class="text-danger">*</span></label>
					<textarea class="form-control" rows="6" name="notes" placeholder="Notes..." required></textarea>
				</div>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</form>
	</div>
</div>

<!-- --------------  -->
<div id="create-new-calibration" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<form class="modal-content" method="POST" action="{{ route('new-maintainance', ['id' => $equipment->id]) }}"
			enctype="multipart/form-data">
			@csrf
			<div class="modal-header">
				<h4 class="modal-title"><i class="mdi mdi-pencil"></i> Create Calibration Log</h4>
			</div>
			<div class="modal-body">

				<div class="form-group hidden">
					<label class="control-label">Type</label>
					<input class="form-control" type="text" name="type" value="Calibration" placeholder="Type...">

				</div>

				<div class="form-group">
					<label class="control-label">Date <span class="text-danger">*</span></label>
					<input type="date" class="form-control" name="date" value="" placeholder="Date..." required />
				</div>
				<div class="form-group">
					<label class="control-label">Description <span class="text-danger">*</span></label>
					<textarea class="form-control" rows="6" name="description" placeholder="Description..."
						required></textarea>
				</div>
				<div class="form-group">
					<label class="control-label">Reference Number <span class="text-danger">*</span></label>
					<input type="text" class="form-control" name="reference" placeholder="Reference Number..."
						required />
				</div>


				<div class="form-check">
					<input class="form-check-input" type="radio" id="is-housed" class="form-control"
						name="maintainance_type" value="in_house" onclick="inhoused()" />
					<label class="form-check-label" for="is-house">
						In House Maintanance Type
					</label>
				</div>
				<br>
				<div class="form-check">
					<input class="form-check-input" type="radio" id="external" class="form-control"
						name="maintainance_type" value="external" onclick="externaled()" />
					<label class="form-check-label" for="externals">
						External Maintainance Type
					</label>
				</div>
				<br>
				<div class="form-group" id="employees" style="display:none">
					<label class="control-label">Employee</label>
					<select name="employee" class="form-control" placeholder="Employee...">
						@foreach($employees as $employee)
							<option value="{{$employee->id}}">{{$employee->name}}</option>
						@endforeach
					</select>
				</div>
				<div class="form-group" id="suppliers" style="display:none">
					<label class="control-label">Supplier</label>
					<select name="supplier" class="form-control" placeholder="Supplier...">
						@foreach($suppliers as $supplier)
							<option value="{{$supplier->id}}">{{$supplier->name}}</option>
						@endforeach
					</select>
				</div>
				<!-- ----------  -->
				<div class="form-group">
					<label class="control-label">Certificate</label>
					<input type="file" class="form-control" name="certificate" value="" placeholder="Certificate..." />
				</div>
				<div class="form-group">
					<label class="control-label">Notes <span class="text-danger">*</span></label>
					<textarea class="form-control" rows="6" name="notes" placeholder="Notes..." required></textarea>
				</div>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</form>
	</div>
</div>
<!-- verification log  -->
<div id="create-verification-log" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<form class="modal-content" method="POST" action="{{ route('add-verification', ['id' => $equipment->id]) }}"
			enctype="multipart/form-data">
			@csrf
			<div class="modal-header">
				<h4 class="modal-title"><i class="mdi mdi-pencil"></i> Create Verification Log</h4>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<label class="control-label">Reference Standard <span class="text-danger">*</span>s</label>
					<input type="text" class="form-control" name="reference" placeholder="Reference Standards..."
						required />
				</div>
				<div class="form-group hidden">
					<label class="control-label">Equipment <span class="text-danger">*</span></label>
					<input type="text" class="form-control" name="equipment" value="{{$equipment->id}}"
						placeholder="Equipment..." required />
				</div>
				<div class="form-check">
					<input class="form-check-input" type="radio" id="is-house" class="form-control"
						name="maintainance_type" value="in_house" />
					<label class="form-check-label" for="is-house">
						In House Service Performer
					</label>
				</div>
				<br>
				<div class="form-check">
					<input class="form-check-input" type="radio" id="externals" class="form-control"
						name="maintainance_type" value="external" />
					<label class="form-check-label" for="externals">
						External Service Performer
					</label>
				</div>
				<br>
				<div class="form-group hidden" id="employee">
					<label class="control-label">Employee</label>
					<select name="employee" class="form-control" placeholder="Employee...">
						@foreach($employees as $employee)
							<option value="{{$employee->id}}">{{$employee->name}}</option>
						@endforeach
					</select>
				</div>
				<div class="form-group hidden" id="supplier">
					<label class="control-label">Supplier</label>
					<select name="supplier" class="form-control" placeholder="Supplier...">
						@foreach($suppliers as $supplier)
							<option value="{{$supplier->id}}">{{$supplier->name}}</option>
						@endforeach
					</select>
				</div>
				<div class="form-group ">
					<label class="control-label">Date of Verifiation</label>
					<input class="form-control" type="date" name="date" placeholder="Date of Verification...">
				</div>

				<div class="form-group">
					<label class="control-label">Operator</label>
					<select name="operator" class="form-control" placeholder="Operator...">
						@foreach($employees as $employee)
							<option value="{{$employee->id}}">{{$employee->name}}</option>
						@endforeach
					</select>
				</div>

				<div class="form-group">
					<label class="control-label">Procedure <span class="text-danger">*</span></label>
					<textarea class="form-control" rows="4" name="procedure" placeholder="Procedure..."
						required></textarea>
				</div>

				<div class="form-group">
					<label class="control-label">Responses/Readings <span class="text-danger">*</span></label>
					<textarea class="form-control" rows="4" name="response" placeholder="Responses..."
						required></textarea>
				</div>

				<div class="form-group">
					<label class="control-label">Remarks <span class="text-danger">*</span></label>
					<textarea class="form-control" rows="4" name="remark" placeholder="Remarks..." required></textarea>
				</div>

			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</form>
	</div>
</div>
<!-- verification log end -->
<div id="repair-content" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header text-center">
				<h5 class="modal-title">{{$equipment->name}} Repaired Parts</h5>
			</div>
			<div class="modal-body">


			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
			</div>
		</div>

	</div>
</div>
<!-- parts section  -->
<div id="create-new-repairment" class="modal fade" role="dialog">
	<div class="modal-dialog modal-lg">
		<!-- Modal content-->
		<form class="modal-content" method="POST" action="{{ route('new-maintainance', ['id' => $equipment->id]) }}"
			enctype="multipart/form-data">
			@csrf
			<div class="modal-header bg-light">
				<h4 class="modal-title"><i class="mdi mdi-pencil"></i> Create Repair Log</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-md-6 col-sm-6 col-lg-6">
						<div class="form-group hidden">
							<label class="control-label">Type</label>
							<input name="type" value="Repairement">
						</div>
						<div class="form-group">
							<label class="control-label">Date <span class="text-danger">*</span></label>
							<input type="date" class="form-control" name="date" value="" placeholder="Date..."
								required />
						</div>
						<div class="form-group">
							<label class="control-label">Description <span class="text-danger">*</span></label>
							<textarea class="form-control" rows="6" name="description" placeholder="Description..."
								required></textarea>
						</div>

					</div>
					<div class="col-md-6 col-sm-6 col-lg-6">
						<div class="form-check">
							<input class="form-check-input" type="radio" id="is-house" class="form-control"
								name="maintainance_type" value="in_house" />
							<label class="form-check-label" for="is-house">
								In House Maintanance Type
							</label>
						</div>
						<br>
						<div class="form-check">
							<input class="form-check-input" type="radio" id="externals" class="form-control"
								name="maintainance_type" value="external" />
							<label class="form-check-label" for="externals">
								External Maintainance Type
							</label>
						</div>
						<br>
						<div class="form-group hidden" id="employee">
							<label class="control-label">Employee</label>
							<select name="employee" class="form-control" placeholder="Employee...">
								@foreach($employees as $employee)
									<option value="{{$employee->id}}">{{$employee->name}}</option>
								@endforeach
							</select>
						</div>
						<div class="form-group hidden" id="supplier">
							<label class="control-label">Supplier</label>
							<select name="supplier" class="form-control" placeholder="Supplier...">
								@foreach($suppliers as $supplier)
									<option value="{{$supplier->id}}">{{$supplier->name}}</option>
								@endforeach
							</select>
						</div>
						<div class="form-group">
							<label class="control-label">Certificate</label>
							<input type="file" class="form-control" name="certificate" value=""
								placeholder="Certificate..." />
						</div>
						<div class="form-group">
							<label class="control-label">Remarks <span class="text-danger">*</span></label>
							<textarea class="form-control" rows="4" name="notes" placeholder="Notes..."
								required></textarea>
						</div>
					</div>
				</div>

				<hr>
				<div class="parts-repaired">
					<h6 class="bg-light p-3 mb-2">
						<span class="btn btn-outline-primary btn-sm float-right mt-0" id="new-part"><i
								class="mdi mdi-plus"></i> Add New Part</span>
						<u>Parts Repaired</u>
					</h6>
				</div>


			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</form>
	</div>
</div>
<div id="edit-new-repairment" class="modal fade" role="dialog">
	<div class="modal-dialog modal-lg">
		<!-- Modal content-->
		<form class="modal-content" method="POST" action="{{ route('edit-maintainance') }}"
			enctype="multipart/form-data">
			@csrf
			<div class="modal-header bg-light">
				<h4 class="modal-title"><i class="mdi mdi-pencil"></i> Edit Repair Log</h4>
			</div>
			<div class="modal-body">
				<div class="modal-body2"></div>
				<div class="parts-repaired">
					<h6 class="bg-light p-3 mb-2">
						<span class="btn btn-outline-primary btn-sm float-right mt-0" id="new-part"><i
								class="mdi mdi-plus"></i> Add New Part</span>
						<u>Parts Repaired</u>
					</h6>
					<div id="parts-repair"></div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
				<a href="/equipment/{{$equipment->id}}" class="btn btn-sm btn-outline-danger">Close</a>

			</div>
		</form>
	</div>
</div>
<!-- part section  -->

<div id="add-operator-modal" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<form class="modal-content" method="POST" action="{{ route('add-operators', ['equipment' => $equipment->id]) }}"
			enctype="multipart/form-data">
			@csrf
			<div class="modal-header">
				<h4 class="modal-title"><i class="mdi mdi-account-multiple-plus"></i> Add Equipment Operators</h4>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<label class="control-label">Select Operators <span class="text-danger">*</span></label>
					<select class="form-control" name="operators[]" placeholder="Select Operators..." multiple required>
						@foreach (getUsers() as $item)
							<option value="{{	$item->id }}">{{ $item->name }}</option>
						@endforeach
					</select>
				</div>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-success"><i class="mdi mdi-content-save"></i> Save</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</form>
	</div>
</div>

<div class="carry_data" data-equipment="{{json_encode($equipment)}}"></div>



<script>
	var counter = 1
	$(function () {
		const equipmentRaw = $('.carry_data').data('equipment');
		var deleteNotificationBody = (data)=>{
			var body = $(`
				<input type="hidden" name="notification_id" value="${data.id}">
				<div class="alert alert-danger p-2 d-flex">
					<i class="mdi mdi-delete-empty"></i>
					<span class="pl-2">Confirm you want to delete ${data.notification_type} notification frequency of ${data.value} days</span>
				</div>
			`).clone();
			return body;
		}
		$('#delete-notification').on('show.bs.modal',(e)=>{
			var data = $(e.relatedTarget).data('record');
			var body = deleteNotificationBody(data);
			$('#delete-notification').find('.modal-body').empty();
			$('#delete-notification').find('.modal-body').append(body);
		})
		var addnotificationbody = (data = null) => {
			var body = $(`
			<div class="alert alert-primary p-2 d-flex">
				<i class="mdi mdi-plus"></i>
				<span class="pl-2">Update notification frequency for {{$equipment->name}}</span>
			</div>
			<input type="hidden" name="notification_id" value="${data ? data.id : 0}">
			<input type="hidden" name="equipment_id" value="{{$equipment->id}}">
			<div class="form-group">
				<label for="" class="control-label">Notification Type</label>
				<select name="notification_type" id="notification_type" class="form-control">
					<option> Select Type</option>
					<option value="calibration" ${data && data.notification_type == 'calibration' ? 'selected' : ''}>Calibration Notification</option>
					<option value="maintainance" ${data && data.notification_type == 'maintainance' ? 'selected' : ''}>Maintainance Notification</option>
					<option value="verification" ${data && data.notification_type == 'verification' ? 'selected' : ''}>Verification Notification</option>
				</select>
			</div>
			<div class="equipment-info"></div>
			<div class="form-group">
				<div class="row">
					<div class="col-md-6">
						<label for="" class="control-label">Frequency</label>
						<input type="number" name="value" value="${data ? data.value : ""}" id="" class="form-control">
					</div>
					<div class="col-md-6">
						<label for="" class="control-label">Frequency Type</label>
						<select name="frequency" id="frequency" class="form-control">
							<option value="days" selected >Days</option>
						</select>
					</div>
				</div>
				
			</div>
			
			`).clone();


			return body;
		}
		$('#add-notification').on('show.bs.modal', (e) => {
			$(body).find('.equipment-info').empty();
			$("#add-notification").find('.save-notification').removeClass('hidden');
			var action = $(e.relatedTarget).data('action');
			var data = action == 'edit' ? $(e.relatedTarget).data('record') : null;
			console.log(`data-------${data}`)
			var body = addnotificationbody(data);
			$('#add-notification').find('.modal-body').empty();
			$('#add-notification').find('.modal-body').append(body);

			$('#add-notification').find('#notification_type').on('change', (e) => {
				console.log('here 1')
				var n_type = $(body).find('#notification_type').val();
				var n_days = 0;
				if (n_type == 'calibration') {
					var n_days = equipmentRaw.calibration_days;
				}
				if (n_type == 'maintainance') {
					var n_days = equipmentRaw.maintainance_days;
				}
				if (n_type == 'verification') {
					var n_days = equipmentRaw.verification_days;
				}
				if (n_days > 0) {
					var n_body = `<div class="alert alert-primary p-2 d-flex">
					<i class="mdi mdi-alert-decagram-outline" style="font-size:20px"></i>
					<span class="pl-2">Equipment ${n_type} interval days is ${n_days}. Ensure your notification frequency is less than the interval days.</span>
					</div>`;

					$('#add-notification').find('.equipment-info').empty();
					$('#add-notification').find('.equipment-info').append(n_body);
					$("#add-notification").find('.save-notification').removeClass('hidden');
					console.log('here again')
				} else {
					var n_body = `<div class="alert alert-warning p-2 d-flex">
					<i class="mdi mdi-alert-decagram-outline" style="font-size:20px"></i>
					<span class="pl-2">Kindly set Equipment ${n_type} interval days to proceed.</span>
					</div>`;
					$('#add-notification').find('.equipment-info').empty();
					$('#add-notification').find('.equipment-info').append(n_body);
					$("#add-notification").find('.save-notification').addClass('hidden');
				}

			});
		})
		$('#delete-maintainance').on('show.bs.modal', function (e) {
			var item = $(e.relatedTarget).data('item');
			var name = $(e.relatedTarget).data('name');
			var m_text = delete_m(name, item);
			$('#delete-maintainance').find('.modal-body').empty();
			$('#delete-maintainance').find('.modal-body').append(m_text);

		})
		var delete_m = function (name, item) {
			var text_m = $(`
			<div class="alert alert-danger p-4">
						Confirm you want to delete this ${name} record !
						<input type="hidden" name="item_id" value="${item}">
					</div>
			`).clone();
			return text_m;
		}
		$('#repair-content').on('show.bs.modal', function (e) {
			var parts = $(e.relatedTarget).data('parts');
			var item_name = $(e.relatedTarget).data('item');
			console.log(item_name)
			$('#repair-content').find('.modal-body').empty();

			$.each(parts, function (i, e) {
				var text_p = `
				<li>
					<div class="panel panel-default">
						<div class="panel-body">
							<p><b>${e.name} - </b>${e.comment}</p>
						</div>
					</div>
				</li>
				`;
				$('#repair-content').find('.modal-body').append(text_p);
			});
		})
		$('#create-new-repairment').on('show.bs.modal', function () {
			$(this).find('#new-part').on('click', function () {

				var parts_text = parts_new()
				var text_ = $(parts_text).clone();
				$(text_).find('#delete-parts').on('click', function () {
					var div = $(this).parent('div');
					$(div).parent('section').remove();
					console.log('done')
				})
				$('#create-new-repairment').find('.parts-repaired').append(text_);


			})
		})
		$('#edit-new-repairment').on('show.bs.modal', function (e) {
			$(this).find('.modal-body2').empty();
			var data = $(e.relatedTarget).data('repair');
			var employees = $(e.relatedTarget).data('employees');
			var suppliers = $(e.relatedTarget).data('suppliers');
			var parts = $(e.relatedTarget).data('parts');

			$('#edit-new-repairment').find('.modal-header').empty();
			var header_ = `<h4 class="modal-title"><i class="mdi mdi-pencil"></i> Edit Repair Log</h4>`
			$('#edit-new-repairment').find('.modal-header').append(header_)
			var edit_form = edit_repair(data);
			$.each(employees, function (i, e) {
				var options = `<option value="${e.id}" ${e.id == data.employee_id ? 'selected' : ''} >${e.name}</option>`;
				$(edit_form).find('[name="employee"]').append(options);
			});
			$.each(suppliers, function (i, e) {
				var options = `<option value="${e.id}" ${e.id == data.supplier_id ? 'selected' : ''} >${e.name}</option>`;
				$(edit_form).find('[name="supplier"]').append(options);
			});
			if (data.maintainance_type === 'in-house') {
				$(edit_form).find('#is-house').prop('checked', true);
				$(edit_form).find('#supplier-').addClass('hidden');
				$(edit_form).find('#employee-').removeClass('hidden');
			}
			if (data.maintainance_type === 'external') {
				$(edit_form).find('#externals').prop('checked', true);
				$(edit_form).find('#supplier-').removeClass('hidden');
				$(edit_form).find('#employee-').addClass('hidden');
			}
			if (data.maintainance_type != 'in-house' && data.maintainance_type != 'external') {
				$(edit_form).find('#employee-').addClass('hidden');
				$(edit_form).find('#supplier-').addClass('hidden');
			}

			$(edit_form).find('#reference-number').remove();
			if (data.maintainance_type === 'in-house') {
				$(edit_form).find('#is-house').prop('checked', true);

			}
			if (data.maintainance_type === 'external') {
				$(edit_form).find('#externals').prop('checked', true);
			}
			$(edit_form).find('[name="maintainance_type"]').on('change', function () {
				if ($(edit_form).find('input[type="radio"]:checked').val() == "in_house") {
					$(edit_form).find('#supplier-').addClass('hidden');
					$(edit_form).find('#employee-').removeClass('hidden');
				}
				if ($(edit_form).find('input[type="radio"]:checked').val() == "external") {
					$(edit_form).find('#supplier-').removeClass('hidden');
					$(edit_form).find('#employee-').addClass('hidden');
				}

			});
			$('#edit-new-repairment').find('.modal-body2').append(edit_form);
			var loop_ = 0

			$('#edit-new-repairment').find('#parts-repair').empty();
			$.each(parts, function (i, e) {

				var part_text = new_part(e);
				$(part_text).find('#part-delete').on('click', function () {
					var div = $(this).parent('div');
					if (confirm("Confirm you want to delete this record!")) {
						$.ajaxSetup({
							headers: {
								'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
							}
						});
						$.ajax({
							url: '/delete/part-repaired/',
							data: {
								id: e.id
							},
							method: 'post',

							success: function (data) {
								console.log(data)
								if (data === 'success') {
									$(div).parent('div').remove();
								}
							},
							error: function (data) {
								console.log(data)
							}
						});
					}
				});
				$('#edit-new-repairment').find('#parts-repair').append(part_text);

				++loop_;
			})


		})

		var parts_new = function () {
			var text = `
				<section class="row">
					<div class="col-md-2  text-center">
						<span  class="btn mt-4 btn-outline-danger btn-sm" id="delete-parts"><i style="font-size = 15px !important" class="mdi mdi-delete-empty"></i></span>
					</div>
					<div class="col-md-5">

						<div class="form-group">
							<label class="control-label">Part Repaired <span class="text-danger">*</span></label>
							<input type="text" class="form-control" name="part[]" placeholder="Part Repaired..." required />
						</div>
					</div>
					<div class="col-md-5">
						<div class="form-group">
							<label class="control-label">Comments <span class="text-danger">*</span></label>
							<textarea class="form-control" rows="1" name="comment[]" placeholder="Comments..." required /></textarea>
						</div>
					</div>
				</section>
				`;
			return text;
		}

		var new_part = function (data) {

			var new_ = $(`
					<div class="row">
						<div class="col-md-2  text-center">
							<span  class="btn mt-4 btn-outline-danger btn-sm" id="part-delete"><i style="font-size = 15px !important" class="mdi mdi-delete-empty"></i></span>
						</div>
						<div class="col-md-5">

							<div class="form-group">
								<label class="control-label">Part Repaired <span class="text-danger">*</span></label>
								<input type="text" class="form-control" name="part[]" value="${data.name}" placeholder="Part Repaired..." required />
								<input type="hidden" value="${data.id}" name="partID">
							</div>
						</div>
						<div class="col-md-5">
							<div class="form-group">
								<label class="control-label">Comments <span class="text-danger">*</span></label>
								<textarea class="form-control" rows="1" name="comment[]" placeholder="Comments..." required />${data.comment}</textarea>
							</div>
						</div>
					</div>
				`).clone();
			return new_;


		}

		$('#create-verification-log').on('show.bs.modal', function (e) {
			$(this).find('#is-house').on('change', function () {
				console.log('test')
				if ($('#create-verification-log').find('input[type="radio"]:checked').val() == "in_house") {
					$('#create-verification-log').find('#supplier').addClass('hidden');
					$('#create-verification-log').find('#employee').removeClass('hidden');
				}

			})
			$(this).find('#externals').on('change', function () {
				console.log('test3')

				if ($('#create-verification-log').find('input[type="radio"]:checked').val() == "external") {
					$('#create-verification-log').find('#supplier').removeClass('hidden');
					$('#create-verification-log').find('#employee').addClass('hidden');
				}
			})
		});
		$('#create-new-repairment').on('show.bs.modal', function (e) {
			$(this).find('#is-house').on('change', function () {
				console.log('test')
				if ($('#create-new-repairment').find('input[type="radio"]:checked').val() == "in_house") {
					$('#create-new-repairment').find('#supplier').addClass('hidden');
					$('#create-new-repairment').find('#employee').removeClass('hidden');
				}

			})
			$(this).find('#externals').on('change', function () {
				console.log('test3')

				if ($('#create-new-repairment').find('input[type="radio"]:checked').val() == "external") {
					$('#create-new-repairment').find('#supplier').removeClass('hidden');
					$('#create-new-repairment').find('#employee').addClass('hidden');
				}
			})
		});
		$('#create-new-maintainance').on('show.bs.modal', function (e) {
			var type = $(e.relatedTarget).data('type');

			$('#select-maintainance-type').val(type);
			$('.select-maintainance-type').text(type);
		});
		$('#create-new-repairment').on('show.bs.modal', function (e) {
			var type = $(e.relatedTarget).data('type');

			$('.select-maintainance-type').text(type);
		});
		$('#edit-attachment').on('show.bs.modal', function (e) {
			var a_data = $(e.relatedTarget).data('attachment');
			console.log(a_data)
			var edit_text = edit_attach(a_data);
			var header_ = `<h5 class="modal-title"><i class="mdi mdi-pencil text-primary"></i> Edit Attachment ${a_data.title}</h5>`
			$('#edit-attachment').find('.modal-body').empty();
			$('#edit-attachment').find('.modal-header').empty();
			$('#edit-attachment').find('.modal-header').append(header_);
			$('#edit-attachment').find('.modal-body').append(edit_text);
		});
		$('#attachment-description').on('show.bs.modal', function (e) {
			var desc = $(e.relatedTarget).data('description');
			var text = `
				<h5>Attachment description.</h5>
				<hr>
				<div class="panel panel-default">
					<div class="panel-body">
						<p>${desc}</p>
					</div>
				</div>
			`;
			$('#attachment-description').find('.modal-body').empty();
			$('#attachment-description').find('.modal-body').append(text);
		})
		var edit_attach = function (data) {
			var a_form = $(`
				<div class="form-group">
					<label class="control-label">Title <span class="text-danger">*</span></label>
					<input type="text" name="title" required placeholder="Title..." value="${data.title}" class="form-control">
					<input type="hidden" name="equipment_id" value="${data.equipment_id}">
					<input type="hidden" name="attach_id" value="${data.id}">
				</div>
				<div class="form-group">
					<label class="control-label">Attachment <span class="text-danger">*</span></label>
					<input type="file" value="${data.attachment}" name="attachment" class="form-control" >
				</div>
				<div class="form-group">
					<label class="control-label">Description</label>
					<textarea class="form-control" rows="6" name="description" placeholder="Notes...">${data.description}</textarea>
				</div>
			`).clone();
			return a_form
		}

		$('#edit-calibration').on('show.bs.modal', function (e) {
			var data = $(e.relatedTarget).data('calibration');
			var employees = $(e.relatedTarget).data('employees');
			var suppliers = $(e.relatedTarget).data('suppliers');
			console.log(data);
			$('#edit-calibration').find('.modal-header').empty();
			var header_ = `<h4 class="modal-title"><i class="mdi mdi-pencil"></i> Edit Calibration Log ${data.reference_number}</h4>`
			$('#edit-calibration').find('.modal-header').append(header_)
			var edit_form = edit_main(data);
			$.each(employees, function (i, e) {
				var options = `<option value="${e.id}" ${e.id == data.employee_id ? 'selected' : ''} >${e.name}</option>`;
				$(edit_form).find('[name="employee"]').append(options);
			});
			$.each(suppliers, function (i, e) {
				var options = `<option value="${e.id}" ${e.id == data.supplier_id ? 'selected' : ''} >${e.name}</option>`;
				$(edit_form).find('[name="supplier"]').append(options);
			});
			if (data.maintainance_type === 'in-house') {
				$(edit_form).find('#is-house').prop('checked', true);

			} else {
				$(edit_form).find('#externals').prop('checked', true);
			}
			$(edit_form).find('[name="maintainance_type"]').on('change', function () {
				if ($(edit_form).find('input[type="radio"]:checked').val() == "in_house") {
					$(edit_form).find('#supplier').addClass('hidden');
					$(edit_form).find('#employee').removeClass('hidden');
				}
				if ($(edit_form).find('input[type="radio"]:checked').val() == "external") {
					$(edit_form).find('#supplier').removeClass('hidden');
					$(edit_form).find('#employee').addClass('hidden');
				}

			});
			$('#edit-calibration').find('#edit-calibration-body').empty();
			$('#edit-calibration').find('#edit-calibration-body').append(edit_form);

		})

		var edit_repair = function (data) {
			var text = $(`
			<div class="row">
					<div class="col-md-6 col-sm-6 col-lg-6">
						<div class="form-group hidden">
							<label class="control-label">Type</label>
							<input name="type" value="Repairement">
						</div>
						<input type="hidden" name="item_id" value=${data.id}>
						<div class="form-group">
							<label class="control-label">Date <span class="text-danger">*</span></label>
							<input type="date" class="form-control" name="date" value="" placeholder="Date..." required />
						</div>
						<div class="form-group">
							<label class="control-label">Description <span class="text-danger">*</span></label>
							<textarea class="form-control" rows="6" name="description" placeholder="Description..." required>${data.description}</textarea>
						</div>

					</div>
					<div class="col-md-6 col-sm-6 col-lg-6">
						<div class="form-check">
							<input class="form-check-input" type="radio" id="is-house" class="form-control" name="maintainance_type" value="in_house" />
							<label class="form-check-label" for="is-house">
								In House Maintanance Type
							</label>
						</div>
						<br>
						<div class="form-check">
							<input class="form-check-input" type="radio" id="externals" class="form-control" name="maintainance_type" value="external" />
							<label class="form-check-label" for="externals">
								External Maintainance Type
							</label>
						</div>
						<br>
						<div class="form-group hidden" id="employee-">
							<label class="control-label">Employee</label>
							<select name="employee" class="form-control" placeholder="Employee...">
								
							</select>
						</div>
						<div class="form-group hidden" id="supplier-">
							<label class="control-label">Supplier</label>
							<select name="supplier" class="form-control" placeholder="Supplier...">
								
							</select>
						</div>
						<div class="form-group">
							<label class="control-label">Certificate</label>
							<input type="file" class="form-control" name="certificate" value="" placeholder="Certificate..." />
						</div>
						<div class="form-group">
							<label class="control-label">Remarks <span class="text-danger">*</span></label>
							<textarea class="form-control" rows="4" name="notes" placeholder="Notes..." required>${data.notes}</textarea>
						</div>
					</div>
				</div>
				<hr>
			`).clone();
			return text
		}

		$('#edit-maintainance').on('show.bs.modal', function (e) {
			var data = $(e.relatedTarget).data('cal');
			var employees = $(e.relatedTarget).data('employees');
			var suppliers = $(e.relatedTarget).data('suppliers');
			console.log(data);
			$('#edit-maintainance').find('.modal-header').empty();
			var header_ = `<h4 class="modal-title"><i class="mdi mdi-pencil"></i> Edit Maintainance Log ${data.reference_number}</h4>`
			$('#edit-maintainance').find('.modal-header').append(header_)
			var edit_form = edit_main(data);
			$.each(employees, function (i, e) {
				var options = `<option value="${e.id}" ${e.id == data.employee_id ? 'selected' : ''} >${e.name}</option>`;
				$(edit_form).find('[name="employee"]').append(options);
			});
			$.each(suppliers, function (i, e) {
				var options = `<option value="${e.id}" ${e.id == data.supplier_id ? 'selected' : ''} >${e.name}</option>`;
				$(edit_form).find('[name="supplier"]').append(options);
			});
			if (data.maintainance_type === 'in-house') {
				$(edit_form).find('#is-house').prop('checked', true);
				$(edit_form).find('#supplier-').addClass('hidden');
				$(edit_form).find('#employee-').removeClass('hidden');
			} else {
				$(edit_form).find('#externals').prop('checked', true);
				$(edit_form).find('#supplier-').removeClass('hidden');
				$(edit_form).find('#employee-').addClass('hidden');
			}
			$(edit_form).find('[name="maintainance_type"]').on('change', function () {
				if ($(edit_form).find('input[type="radio"]:checked').val() == "in_house") {
					$(edit_form).find('#supplier-').addClass('hidden');
					$(edit_form).find('#employee-').removeClass('hidden');
				}
				if ($(edit_form).find('input[type="radio"]:checked').val() == "external") {
					$(edit_form).find('#supplier-').removeClass('hidden');
					$(edit_form).find('#employee-').addClass('hidden');
				}

			});
			$('#edit-maintainance').find('#edit-maintainance-body').empty();
			$('#edit-maintainance').find('#edit-maintainance-body').append(edit_form);

		})
		var edit_main = function (data) {
			var text = $(`
			
				
				<div class="form-group hidden">
					<label class="control-label">Type</label>
					<input class="form-control" type="text" name="type" value="${data.type}" placeholder="Type...">
					<input class="form-control" type="hidden" name="item_id" value="${data.id}">

				</div>

				<div class="form-group">
					<label class="control-label">Date <span class="text-danger">*</span></label>
					<input type="date" class="form-control" name="date" value="${data.date}" placeholder="Date..." required />
				</div>
				<div class="form-group">
					<label class="control-label">Description <span class="text-danger">*</span></label>
					<textarea class="form-control" rows="6" name="description" placeholder="Description..." required>${data.description}</textarea>
				</div>
				<div class="form-group" id="reference-number">
					<label class="control-label">Reference Number <span class="text-danger">*</span></label>
					<input type="text" class="form-control" name="reference" placeholder="Reference Number..." value="${data.reference_number}" required />
				</div>
				
				
				<div class="form-check">
					<input class="form-check-input" type="radio" id="is-house" class="form-control" name="maintainance_type" value="in_house" />
					<label class="form-check-label" for="is-house">
						In House Maintanance Type
					</label>
				</div>
				<br>
				<div class="form-check">
					<input class="form-check-input" type="radio" id="externals" class="form-control" name="maintainance_type" value="external"  />
					<label class="form-check-label" for="externals">
						External Maintainance Type
					</label>
				</div>
				<br>
				<div id="type_section">
					<div class="form-group" id="employee-">
						<label class="control-label">Employee</label>
						<select name="employee" class="form-control" placeholder="Employee...">
							
						</select>
					</div>
					<div class="form-group" id="supplier-">
						<label class="control-label">Supplier</label>
						<select name="supplier" class="form-control" placeholder="Supplier...">
							
						</select>
					</div>
				</div>
				
				<div class="form-group">
					<label class="control-label">Certificate</label>
					<input type="file" class="form-control" name="certificate" value="${data.certificate}" placeholder="Certificate..." />
				</div>
				<div class="form-group">
					<label class="control-label">Remark <span class="text-danger">*</span></label>
					<textarea class="form-control" rows="6" name="notes" placeholder="Notes..." required>${data.notes}</textarea>
				</div>
			
			`).clone()
			return text
		}
		var counter = 1
		$('#add-new-part').click(function (event) {
			event.preventDefault();

			$('.yoo').hide();
			var newPart = $('<div class="all">' +


				'<div class="form-group float-left" style="width:42%;"><label class="control-label">Part Repaired</label>' +
				'<input type="text" class="form-control" name = "part[]" value="" placeholder="Part Repaired" required /></div>' +
				'<div class="form-group float-right" style="width:42%" ><label class="control-label">Comments</label>' +
				'<textarea class="form-control" rows="1" name="comment[]" placeholder ="Comments..." required /></textarea></div>' +
				'<a  style="margin-right:15px;margin-left:0px" class="btn btn-danger btn-sm close" aria-label="Close" id="delete" onclick="isdelete()><span aria-hidden="true">&times;</span></a>' +
				'</div>');
			$('#add-yoo').append(newPart);
			$('#no_parts').val(counter)
			counter++
		})
		$('#is-house').click(function (event) {
			if (('#is-house').prop("checked") == true) {
				$('#employee').css({
					'display': 'block'
				});
			} else {
				$('#employee').css({
					'display': 'none'
				});
			}

		})
		$('#create-new-repairment').on('click', '#delete', function (e) {
			$(this).parent('div').remove();

		})

	});

	function external() {
		var checkbox = document.getElementById("externals");
		var text = document.getElementById("supplier");
		if (checkbox.checked == true) {
			text.style.display = "block";

			inhouse();
		} else {
			text.style.display = "none";
		}
	}

	function externaled() {
		var checkbox = document.getElementById("external");
		var text = document.getElementById("suppliers");
		if (checkbox.checked == true) {
			text.style.display = "block";

			inhoused();
		} else {
			text.style.display = "none";
		}
	}

	function inhouse() {
		var checkbox = document.getElementById("is-house");
		var text = document.getElementById("employee");
		if (checkbox.checked == true) {
			text.style.display = "block";

			external();
		} else {
			text.style.display = "none";
		}

	}

	function inhoused() {
		var checkbox = document.getElementById("is-housed");
		var text = document.getElementById("employees");
		if (checkbox.checked == true) {
			text.style.display = "block";

			externaled();
		} else {
			text.style.display = "none";
		}

	}

	function isdelete() {
		var part = document.getElementById('no_parts').value;
		part--;

		counter = part;

		document.getElementById('no_parts').value = part;
	}
</script>
@endsection