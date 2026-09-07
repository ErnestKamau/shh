@extends('layouts.inventory.layout.app')

@section('title2')
  <title>Inventory Departments</title>
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
          'link' => route('show-inventory-departments'),
          'name' => 'Departments',
          'icon' => null
        )
      );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>

    <div class="batch-header-bar mb-3">
      <div class="batch-header-top">
        <div class="batch-title-group">
          <span class="batch-code-label">{{ inventoryLabel('departments', 'Departments') }}</span>
          <span class="batch-stage-pill">
            <i class="mdi mdi-domain"></i>
            {{ count($departments) }} {{ inventoryLabel('departments', 'Departments') }}
          </span>
        </div>
      </div>
      <div class="d-flex align-items-center" style="gap: 0.5rem;">
        <button class="btn btn-primary btn-sm workflow-header-receive-btn" data-toggle="modal" data-target="#add-inventory-department">
          <i class="mdi mdi-plus"></i> {{ inventoryLabel('add_department', 'Add Department') }}
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
            <th>Active</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @if(count($departments) > 0)
            @foreach($departments as $department)
              <tr>
                <td valign="center">{{ $loop->iteration }}</td>
                <td>{{ $department->name }}</td>
								<td class="text-small">{!! $department->active == '1' ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>' !!}</td>
                <td nowrap>
                  <button class="btn btn-primary btn-sm" data-target="#edit-inventory-department-{{ $loop->iteration }}" data-toggle="modal"><i class="mdi mdi-pencil-outline"></i> <small class="hidden-sm-up">Edit</small> </button>
                  <a class="btn btn-success btn-sm" href="{{ route('show-inventory-department', ['id'=>$department->id]) }}"><i class="mdi mdi-eye-outline"></i> <small class="hidden-sm-up">Show</small> </a>
                  <div id="edit-inventory-department-{{ $loop->iteration }}" class="modal fade" role="dialog">
                    <div class="modal-dialog">
                      <!-- Modal content-->
                      <form class="modal-content" method="POST" action="{{ route('edit-inventory-department', ['id'=>$department->id]) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-header">
                          <h4 class="modal-title"><i class="mdi mdi-pencil-outline"></i> Edit Inventory Department</h4>
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
          @endif
        </tbody>
      </table>
      @if(count($departments) == 0)
        <div class="alert alert-info">
          <i class="mdi mdi-alert"></i> No Inventory Departments added yet.
        </div>
      @endif
    </div>
  </main>
@endsection

@section('script2')
  <div id="add-inventory-department" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <!-- Modal content-->
      <form class="modal-content" method="POST" action="{{ route('add-inventory-department') }}" enctype="multipart/form-data">
        @csrf
        <div class="modal-header">
          <h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Inventory Department</h4>
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
