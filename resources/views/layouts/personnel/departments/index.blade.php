@extends('layouts.personnel.layout.app', ['dataTable'=>true])

@section('title2')
  <title>Organizational Departments</title>
@endsection
@section('content2')
  <main>
    <?php
      $items = array(
        array(
          'link' => route('personnel-home'),
          'name' => 'Personnel Management',
          'icon' => null
        ),
        array(
          'link' => route('show-organizational-departments'),
          'name' => 'Organizational Departments',
          'icon' => null
        )
      );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    <h2 class="p-4">
      <i class="mdi mdi-format-list-bulleted-type"></i>Departments
      <button class="btn btn-primary btn-sm float-right" data-toggle="modal" data-target="#add-inventory-department"><i class="mdi mdi-plus"></i> Add</button>
    </h2>
    <br>
    <div class="table-responsive bg-light p-4">
      <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
        <thead class="bg-light p-2">
          <tr>
            <th>No</th>
            <th>Name</th>
            <th>Active</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
					@foreach($departments as $department)
						<tr>
							<td valign="center">{{ $loop->iteration }}</td>
							<td>{{ $department->name }}</td>
							<td class="text-small">{!! $department->active == '1' ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>' !!}</td>
							<td nowrap>
								<button class="btn btn-primary btn-sm" data-target="#edit-organizational-department-{{ $loop->iteration }}" data-toggle="modal"><i class="mdi mdi-pencil-outline"></i> <small class="hidden-sm-up">Edit</small> </button>
								{{-- <a class="btn btn-success btn-sm" href="{{ route('show-organizational-department', ['id'=>$department->id]) }}"><i class="mdi mdi-eye-outline"></i> <small class="hidden-sm-up">Show</small> </a> --}}
								<div id="edit-organizational-department-{{ $loop->iteration }}" class="modal fade" role="dialog">
									<div class="modal-dialog">
										<!-- Modal content-->
										<form class="modal-content" method="POST" action="{{ route('edit-inventory-department', ['id'=>$department->id]) }}" enctype="multipart/form-data">
											@csrf
											<div class="modal-header">
												<h4 class="modal-title"><i class="mdi mdi-pencil-outline"></i> Edit Department</h4>
											</div>
											<div class="modal-body">
												<div class="form-group">
													<label class="control-label">Name</label>
													<input type="text" class="form-control" name="name" value="{{ $department->name }}" placeholder="Name..." required />
												</div>
												<div class="form-group">
													<label class="control-label"><input type="checkbox" name="active" value="1" {{ $department->active == 1 ? 'checked' : '' }} /> Active</label>
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
  <div id="add-inventory-department" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <!-- Modal content-->
      <form class="modal-content" method="POST" action="{{ route('add-inventory-department', ['module'=>'organizational']) }}" enctype="multipart/form-data">
        @csrf
        <div class="modal-header">
          <h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Department</h4>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label class="control-label">Name</label>
            <input type="text" class="form-control" name="name" value="" placeholder="Name..." required />
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
