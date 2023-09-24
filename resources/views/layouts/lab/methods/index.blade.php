@extends('layouts.lab.layout.app', ['dataTable'=>true,])

@section('title2')
  <title>Analysis Methods</title>
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
          'link' => route('analysis-methods'),
          'name' => 'Methods',
          'icon' => null
        )
      );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    <h2 class="p-4">
      <i class="mdi mdi-cogs"></i> Analysis Methods
      {{-- <span class="btn btn-sm btn-white"><i class="mdi mdi-file-import-outline"></i> Import</span> --}}
      <button class="btn btn-primary btn-sm float-right" data-toggle="modal" data-target="#add-method"><i class="mdi mdi-plus"></i> Add</button>
    </h2>
    <br>
    <div class="table-responsive bg-light p-4">
      <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
        <thead class="bg-light p-2">
          <tr>
            <th>No</th>
            <th>Code</th>
            <th>Name</th>
            <th>Description</th>
            <th>Elements</th>
            <th>Type</th>
            <th>Active?</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @if(count($methods) > 0)
            @foreach($methods as $method)
              <tr>
                <td valign="center">{{ $loop->iteration }}</td>
                <td>{{ $method->code }}</td>
                <td>{{ $method->name }}</td>
                <td>{{ $method->description }}</td>
                <td>{{ number_format($method->analytes()->count()) }}</td>
                
                <td>{!! $method->is_sampling_method == 0 ? '<span>Analysis Method</span>' : '<span>Sampling Method</span>'  !!}</td>
                <td class="text-small">{!! $method->active == '1' ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>' !!}</td>
                <td nowrap>
                  <button class="btn btn-primary btn-sm" data-target="#edit-method-{{ $loop->iteration }}" data-toggle="modal"><i class="mdi mdi-pencil-outline"></i> <small class="hidden-sm-up">Edit</small> </button>
                  <a class="btn btn-success btn-sm" href="{{ route('edit-analysis-method', ['id'=>$method->id]) }}"><i class="mdi mdi-eye-outline"></i> <small class="hidden-sm-up">Show</small> </a>
                  <div id="edit-method-{{ $loop->iteration }}" class="modal fade" role="dialog">
                    <div class="modal-dialog">
                      <!-- Modal content-->
                      <form class="modal-content" method="POST" action="{{ route('edit-analysis-method', ['id'=>$method->id]) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-header">
                          <h4 class="modal-title"><i class="mdi mdi-pencil-outline"></i> Edit Analysis Method</h4>
                        </div>
                        <div class="modal-body">
                          <div class="form-group">
                            <label class="control-label">Name</label>
                            <input type="text" class="form-control" name="name" value="{{ $method->name }}" placeholder="Analysis Method Name..." required />
                          </div>
                          <div class="form-group">
                            <label class="control-label">Code</label>
                            <input type="text" class="form-control" name="code" value="{{ $method->code }}" placeholder="Analysis Method Code..." required />
                          </div>
                          <div class="form-group">
                            <label class="control-label">Description</label>
                            <textarea class="form-control" name="description" placeholder="Description..." required>{{ $method->description }}</textarea>
                          </div>
                          <div class="form-group">
                            <label class="control-label"><input type="checkbox" name="active" value="1" {{ $method->active == 1 ? 'checked' : '' }} /> Active</label>
                          </div>
                          <div class="form-group">
                            <label class="control-label"><input type="checkbox" name="is_sampling_method" value="1" {{ $method->is_sampling_method == 1 ? 'checked' :'' }} /> Is Sampling Method</label>
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
      @if(count($methods) == 0)
        <div class="alert alert-info">
          <i class="mdi mdi-alert"></i> No Analysis Methods added yet.
        </div>
      @endif
    </div>
  </main>
@endsection

@section('script2')
  <div id="add-method" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <!-- Modal content-->
      <form class="modal-content" method="POST" action="{{ route('add-analysis-methods') }}" enctype="multipart/form-data">
        @csrf
        <div class="modal-header">
          <h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Analysis Method</h4>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label class="control-label">Name</label>
            <input type="text" class="form-control" name="name" placeholder="Analysis Method Name..." required />
          </div>
          <div class="form-group">
            <label class="control-label">Code</label>
            <input type="text" class="form-control" name="code" placeholder="Analysis Method Code..." required />
          </div>
          <div class="form-group">
            <label class="control-label">Description</label>
            <textarea class="form-control" name="description" placeholder="Description..." required></textarea>
          </div>
          <div class="form-group">
            <label class="control-label"><input type="checkbox" name="active" value="1" checked /> Active</label>
          </div>
          <div class="form-group">
            <label class="control-label"><input type="checkbox" name="is_sampling_method" value="1" /> Is Sampling Method</label>
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