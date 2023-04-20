@extends('layouts.inventory.layout.app', ['dataTable'=>true, 'select2'=>true])

@section('title2')
  <title>{{ $stage }} | Inventory Management</title>
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
          'link' => "General Requisition",
          'name' => null,
          'icon' => null
        )
			);

			$assistant_sup_roles = getConfigByName('assistant_supervisor_role_id');
			$assistant_sup_role_id = count($assistant_sup_roles) > 0 ? $assistant_sup_roles[0]->value : 0;

    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    <h3 class="p-4">
			<i class="mdi mdi-format-list-checks"></i> {{ $stage }}
			@if(in_array($stage, array("General Requisition")))
				<a class="btn btn-primary btn-sm float-right" href="{{ route('general-requisition-view') }}">
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
						<form class="row" method="GET">
							<div class="col-sm-5">
								<div class="form-group">
									<input type="date" name="start_date" value="{{ $start_date->format('Y-m-d') }}" class="form-control form-control-lg" />
								</div>
							</div>
							<div class="col-sm-5">
								<div class="form-group">
									<input type="date" name="end_date" value="{{ $end_date->format('Y-m-d')  }}" class="form-control form-control-lg" />
								</div>
							</div>
							<div class="col-sm-2">
								<div class="form-group">
									<button class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
								</div>
							</div>
						</form>
						<div class="table-responsive">
							<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
								<thead>
									<tr>
										<th>#</th>
										<th nowrap>Code</th>
										<th nowrap>Requester</th>
										<th nowrap>Department</th>
										<th nowrap>Laboratory</th>
										<th nowrap>Purpose</th>
										<th nowrap>Status</th>
										<th nowrap>Date Required</th>
									</tr>
								</thead>
								<tbody>
									@foreach ($requests as $l)
										<tr>
											<td>{{ $loop->iteration }}</td>
											<td nowrap>
												<a href="{{ route('general-requisition-view', $l->id) }}">
													{{ $l->code }}
												</a>
											</td>
											<td nowrap>{{ $l->requester->name }}</td>
											<td nowrap>{{ $l->requesting_department }}</td>
											<td nowrap>{{ $l->laboratory }}</td>
											<td nowrap>{{ $l->description }}</td>
											<td nowrap>{{ $l->status }}</td>
											<td nowrap>{{ $l->date_required }}</td>
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
      <form class="modal-content" method="POST" action="{{ route('add-approval-to-stage', ['stage'=>'General Requisition']) }}" enctype="multipart/form-data">
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
