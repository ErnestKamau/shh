@extends('layouts.inventory.layout.app', ['dataTable'=>true, 'select2'=>true])

@section('title2')
  <title>{{ $stage }} | Inventory Management</title>
@endsection
@section('content2')
  <main>
		<?php
			$stages = getRequisitionWorkflow();
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

			$assistant_sup_roles = getConfigByName('assistant_supervisor_role_id');
			$assistant_sup_role_id = count($assistant_sup_roles) > 0 ? $assistant_sup_roles[0]->value : 0;

    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    <h3 class="p-4">
			<i class="mdi mdi-format-list-checks"></i> {{ $stage }}
			@if(in_array($stage, array("Material Requisition", "Request to Store", "General Requisition")) && Auth::user()->hasRole($assistant_sup_role_id, true))
				<a class="btn btn-primary btn-sm float-right" href="{{ route('view-request-details', ['stage'=>$stage, 'id'=>time()]) }}">
					<i class="mdi mdi-plus"></i> Create Request
				</a>
			@endif
		</h3>
		<div class="bg-light">
			<div class="card tab-card">
				<div class="card-header tab-card-header">
					<ul class="nav nav-tabs card-header-tabs" id="Requests-tabs" role="tablist">
						<li class="nav-item">
							<a class="nav-link active" id="Requests-tab" data-toggle="tab" href="#Requests" role="tab" aria-controls="Requests" aria-selected="true">Requests</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" id="Approvals-tab" data-toggle="tab" href="#Approvals" role="tab" aria-controls="approvals" aria-selected="true">Approval Configuration</a>
						</li>
					</ul>
				</div>
				<div class="tab-content" id="Requests-tabs-content">
					<div class="tab-pane fade show active p-3" id="Requests" role="tabpanel" aria-labelledby="one-tab">
						<h5 class="card-title mb-3">Requests</h5>
						<div class="table-responsive">
							<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
								<thead>
									<tr>
										<th>#</th>
										<th nowrap>Code</th>
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
										<th nowrap>Purchasing Unit</th>
										<th nowrap>Department</th>
										<th nowrap>Created On</th>
										<th nowrap>Approvals</th>
										<th nowrap>Total Value</th>
									</tr>
								</thead>
								<tbody>
									@foreach ($list as $l)
										<tr>
											<td>{{ $loop->iteration }}</td>
											<td nowrap>
												<a href="{{ route('view-request-details', ['stage'=>$stage, 'id'=>$l->id]) }}">
													{{ $l->request_code }}
												</a>
											</td>
											<td nowrap>{{ $l->priority }}</td>
											<td nowrap>{{ $l->status }} <small>{{ $l->approval_status }}</small></td>
											<td nowrap>{{ $l->description ?? 'No items set' }}</td>
											<td nowrap>{{ $l->due_date }}</td>
											@if ((array_search($stage, $stages) > 0) || $stage == "Material Issuance")
												<td nowrap>
													@if(isset($l->parent_request) && $l->parent_request != '')
													<a href="{{ route('view-request-details', ['stage'=>$l->parent_request, 'id'=>$l->parent_request_id]) }}">
														{{ rtrim($l->parent_request, 's')."-".$l->parent_request_id }}
													</a>
													@else
														-
													@endif
												</td>
											@endif
											@if ($stage == "Purchase Orders")
												<td nowrap>{{ $l->supplier()->name }}</td>
											@endif
											<td nowrap>{{ $l->creator()->name }}</td>
											<td nowrap>{{ $l->creator()->location()->name }}</td>
											<td nowrap>{{ $l->creator()->department()->name }}</td>
											<td nowrap>{{ $l->created_at }}</td>
											<td nowrap>{{ $l->done_approvals()->count()."/".getStageApprovals('Requisition', $stage)->count() }}</td>
											<td nowrap>{{ $l->currency." ".number_format($l->net_value, 2) }}</td>
										</tr>
									@endforeach
								</tbody>
							</table>
						</div>
					</div>
					<div class="tab-pane fade p-3" id="Approvals" role="tabpanel" aria-labelledby="one-tab">
						<h5 class="card-title mb-3">Approvals Configuration
							<button class="btn btn-outline-primary btn-sm float-right"
								data-toggle="modal" data-target="#add-approval-modal"><i class="mdi mdi-key-plus"></i></button>
						</h5>
						<div class="table-responsive">
							<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
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
																			<option value="{{ $item->id }}" {{ $item->id == $approval->role_id ? 'selected' : '' }}>{{ $item->name }}</option>
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
@endsection
