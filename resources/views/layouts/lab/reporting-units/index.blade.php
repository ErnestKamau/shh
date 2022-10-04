@extends('layouts.lab.layout.app', ['dataTable'=>true])

@section('title2')
  <title>Reporting Units</title>
@endsection
@section('content2')
  <main>
    <?php
      $items = array(
        array(
          'link' => route('lab-home'),
          'name' => 'Lab Management',
					'icon' => null
				),
				array(
          'link' => route('reporting-units'),
          'name' => ' Reporting Units',
          'icon' => null
        )
      );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    <h2 class="p-4">
      <i class="mdi mdi-file-document-edit"></i> Reporting Units <small class="text-muted">{{ isset($selectSampleType) ? " | ".$selectSampleType->name : ''  }}</small>
      <button class="btn btn-primary btn-sm float-right" data-toggle="modal" data-target="#add-reporting-unit"><i class="mdi mdi-plus"></i> Add</button>
    </h2>
    <br>
    <div class="table-responsive bg-light p-4">
      <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
        <thead class="bg-light p-2">
          <tr>
            <th>No</th>
            <th>Name</th>
            <th>Active?</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @if(count($reporting_units) > 0)
            @foreach($reporting_units as $reporting_unit)
              <tr>
                <td valign="center">{{ $loop->iteration }}</td>
                <td>{{ $reporting_unit->name }}</td>
                <td class="text-small">{!! $reporting_unit->active == '1' ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>' !!}</td>
                <td nowrap>
                  <button class="btn btn-primary btn-sm" data-target="#edit-reporting_unit-{{ $loop->iteration }}" data-toggle="modal"><i class="mdi mdi-pencil-outline"></i> <small class="hidden-sm-up">Edit</small> </button>
                  <div id="edit-reporting_unit-{{ $loop->iteration }}" class="modal fade" role="dialog">
                    <div class="modal-dialog">
                      <!-- Modal content-->
                      <form class="modal-content" method="POST" action="{{ route('edit-reporting-unit', ['id'=>$reporting_unit->id]) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-header">
                          <h4 class="modal-title"><i class="mdi mdi-pencil-outline"></i> Edit Reporting Unit</h4>
                        </div>
                        <div class="modal-body">
                          <div class="form-group">
                            <label class="control-label">Name</label>
                            <input type="text" class="form-control" name="name" value="{{ $reporting_unit->name }}" placeholder="Reporting Unit..." required />
                          </div>
                          <div class="form-group">
                            <label class="control-label"><input type="checkbox" name="active" value="1" {{ $reporting_unit->active == 1 ? 'checked' : '' }} /> Active</label>
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
      @if(count($reporting_units) == 0)
        <div class="alert alert-info">
          <i class="mdi mdi-alert"></i> No Reporting Units added yet.
        </div>
      @endif
    </div>
  </main>
@endsection

@section('script2')
  <div id="add-reporting-unit" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <!-- Modal content-->
      <form class="modal-content" method="POST" action="{{ route('add-reporting-unit') }}" enctype="multipart/form-data">
        @csrf
        <div class="modal-header">
          <h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Reporting Unit</h4>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label class="control-label">Name</label>
            <input type="text" class="form-control" name="name" placeholder="Reporting Unit..." required />
          </div>
          <div class="form-group">
            <label class="control-label"><input type="checkbox" name="active" value="1" checked /> Active</label>
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