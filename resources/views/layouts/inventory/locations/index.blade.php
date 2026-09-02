@extends('layouts.inventory.layout.app', ['dataTable'=>true])

@section('title2')
  <title>Inventory Locations</title>
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
          'link' => route('inventory-locations'),
          'name' => 'Organizational Structure',
          'icon' => null
        )
      );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>

    <div class="batch-header-bar mb-3">
      <div class="batch-header-top">
        <div class="batch-title-group">
          <span class="batch-code-label">{{ inventoryLabel('organizational_structure', 'Organizational Structure') }}</span>
          <span class="batch-stage-pill">
            <i class="mdi mdi-map-marker"></i>
            {{ count($locations) }} {{ inventoryLabel('locations', 'Locations') }}
          </span>
        </div>
      </div>
      <div class="d-flex align-items-center" style="gap: 0.5rem;">
        <button class="btn btn-primary btn-sm workflow-header-receive-btn" data-toggle="modal" data-target="#add-inventory-location">
          <i class="mdi mdi-plus"></i> {{ inventoryLabel('add_location', 'Add Location') }}
        </button>
      </div>
    </div>

    <div class="workflow-board-panel">
			<div class="workflow-board-panel-body p-0">
      <div class="table-responsive">
        <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm mb-0">
        <thead class="bg-light p-2">
          <tr>
            <th>No</th>
            <th>Name</th>
            <th>Unit</th>
						<th>Level</th>
						<th>Parent Unit</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
					@foreach($locations as $location)
						<tr>
							<td valign="center">{{ $loop->iteration }}</td>
							<td>{{ $location->name }}</td>
							<td>{{ $location->locations->count() }}</td>
							<td>{{ $location->level }}</td>
							<td>{{ $location->parent()->name ?? 'Top Level' }}</td>
							<td nowrap>
								<button class="btn btn-primary btn-sm" data-target="#edit-inventory-location-{{ $loop->iteration }}" data-toggle="modal"><i class="mdi mdi-pencil-outline"></i> <small class="hidden-sm-up">Edit</small> </button>
								<button class="btn btn-danger btn-sm" data-target="#delete-inventory-location-{{ $loop->iteration }}" data-toggle="modal"><i class="mdi mdi-delete-empty"></i> <small class="hidden-sm-up">Delete</small> </button>
								<a class="btn btn-success btn-sm" href="{{ route('show-inventory-locations', ['id'=>$location->id]) }}"><i class="mdi mdi-eye-outline"></i> <small class="hidden-sm-up">Show</small> </a>
								<div id="edit-inventory-location-{{ $loop->iteration }}" class="modal fade" role="dialog">
									<div class="modal-dialog">
										<!-- Modal content-->
										<form class="modal-content" method="POST" action="{{ route('edit-inventory-location', ['id'=>$location->id]) }}" enctype="multipart/form-data">
											@csrf
											<div class="modal-header">
												<h4 class="modal-title"><i class="mdi mdi-pencil-outline"></i> Edit Location</h4>
											</div>
											<div class="modal-body">
												<div class="form-group">
													<label class="control-label">Name</label>
													<input type="text" class="form-control" name="name" value="{{ $location->name }}" placeholder="Name..." required />
												</div>
												<div class="form-group">
													<label class="control-label">Currency</label>
													<select class="form-control" name="currency" required placeholder="Currency...">
														@foreach (getCurrencies() as $item)
															<option value="{{ $item->id }}" {{ $location->currency == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
														@endforeach
													</select>
												</div>
												<div class="form-group">
													<label class="control-label">Parent Location</label>
													<select class="form-control" name="parent_location" required>
														<option value="0 0">Has No Parent Location</option>
														@foreach ($locations as $item)
															<option value="{{ $item->id }} {{ $item->level }}" {{ $location->inventory_location_id == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
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
								<div id="delete-inventory-location-{{ $loop->iteration }}" class="modal fade" role="dialog">
									<div class="modal-dialog">
										<!-- Modal content-->
										<form class="modal-content" method="POST" action="{{ route('delete-inventory-location', ['id'=>$location->id]) }}" enctype="multipart/form-data">
											@csrf
											<div class="modal-header">
												<h4 class="modal-title"><i class="mdi mdi-delete"></i> Remove Location</h4>
											</div>
											<div class="modal-body">
												<div class="form-group">
													<div class="alert alert-danger alert-callout">
														<i class="fas fa-exclamation-triangle"></i> Remove this Location
													</div>
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
  </main>
@endsection

@section('script2')
  <div id="add-inventory-location" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <!-- Modal content-->
      <form class="modal-content" method="POST" action="{{ route('add-inventory-location') }}" enctype="multipart/form-data">
        @csrf
        <div class="modal-header">
          <h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Location</h4>
        </div>
        <div class="modal-body">
          <div class="form-group">
						<label class="control-label">Name</label>
						<input type="text" class="form-control" name="name" value="" placeholder="Name..." required />
					</div>
					<div class="form-group">
						<label class="control-label">Currency</label>
						<select class="form-control" name="currency" required placeholder="Currency...">
							@foreach (getCurrencies() as $item)
								<option value="{{ $item->id }}">{{ $item->name }}</option>
							@endforeach
						</select>
					</div>
					<div class="form-group">
						<label class="control-label">Parent Location</label>
						<select class="form-control" name="parent_location" required>
							<option value="0 0">Has No Parent Location</option>
							@foreach ($locations as $item)
								<option value="{{ $item->id }} {{ $item->level }}">{{ $item->name }}</option>
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
