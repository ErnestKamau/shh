@extends('layouts.lab.layout.app', ['dataTable'=>true, 'select2'=>true])

@section('title2')
  <title>Analysis Types | {{ isset($selected_lab) ? " | ".$selected_lab->name : ''  }}</title>
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
          'link' => isset($selected_lab) ? route('show-lab-analysis-types', ['labid'=>$selected_lab->id]) : route('analysis-types'),
          'name' => 'Analysis Types',
          'icon' => null
        )
      );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    <h2 class="p-4">
      <i class="mdi mdi-microscope"></i> Analysis Types <small class="text-muted">{{ isset($selected_lab) ? " | ".$selected_lab->name : ''  }}</small>
      <button class="btn btn-primary btn-sm float-right" data-toggle="modal" data-target="#add-analysis-type"><i class="mdi mdi-plus"></i> Add</button>
    </h2>
    <br>
    <div class="table-responsive bg-light p-4">
      <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
        <thead class="bg-light p-2">
          <tr>
            <th>No</th>
            <th>Code</th>
            <th>Name</th>
            <th>Short Name</th>
            <th>Reporting Time</th>
            <th>Description</th>
            <th>Sample Type</th>
            <th>Lab</th>
            <th>Active?</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @if(count($analysis_types) > 0)
            @foreach($analysis_types as $analysis_type)
              <tr>
                <td valign="center">{{ $loop->iteration }}</td>
                <td>{{ $analysis_type->code }}</td>
                <td>{{ $analysis_type->name }}</td>
                <td>{{ $analysis_type->short_name ?? 'N/A' }}</td>
                <td>{{ $analysis_type->reporting_time ?? 0 }}</td>
                <td>{{ $analysis_type->description }}</td>
                <td>{{ $analysis_type->sample_type->name }}</td>
                <td>{{ $analysis_type->lab->name }} - {{ $analysis_type->lab->code }}</td>
                <td class="text-small">{!! $analysis_type->active == '1' ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>' !!}</td>
                <td nowrap>
                  <button class="btn btn-primary btn-sm" data-target="#edit-analysis_type-{{ $loop->iteration }}" data-toggle="modal"><i class="mdi mdi-pencil-outline"></i> <small class="hidden-sm-up">Edit</small> </button>
                  {{-- <button class="btn btn-danger btn-sm"><i class="mdi mdi-delete-empty"></i> <small class="hidden-sm-up">Delete</small> </button>  --}}
                  <a class="btn btn-success btn-sm" href="{{ route('analysis-type', ['id'=>$analysis_type->id]) }}"><i class="mdi mdi-eye-outline"></i> <small class="hidden-sm-up">Show</small> </a>
                  <div id="edit-analysis_type-{{ $loop->iteration }}" class="modal fade" role="dialog">
                    <div class="modal-dialog">
                      <!-- Modal content-->
                      <form class="modal-content" method="POST" action="{{ route('edit-analysis-type', ['id'=>$analysis_type->id]) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-header">
                          <h4 class="modal-title"><i class="mdi mdi-pencil-outline"></i> Edit Analysis Type</h4>
                        </div>
                        <div class="modal-body">
                          <div class="form-group">
                            <label class="control-label">Name</label>
                            <input type="text" class="form-control" name="name" value="{{ $analysis_type->name }}" placeholder="Analysis Type Name..." required />
                          </div>
                          <div class="form-group">
                            <label class="control-label">Code</label>
                            <input type="text" class="form-control" name="code" value="{{ $analysis_type->code }}" placeholder="Analysis Type Code..." required />
                          </div>
                          <div class="form-group">
                            <label class="control-label">Description</label>
                            <textarea class="form-control" name="description" placeholder="Description..." required>{{ $analysis_type->description }}</textarea>
                          </div>
                          <div class="form-group">
                            <label class="control-label">Sample Type</label>
                            <select class="form-control" name="sample_type_id" data-placeholder>
                              <option value="">Select Sample Type...</option>
                              @foreach ($sample_types as $sam)
                                <option value="{{ $sam->id }}" {{ $sam->id == $analysis_type->sample_type_id ? 'selected' : '' }}>{{ $sam->name }}</option>
                              @endforeach
                            </select>
                          </div>
													<div class="form-group">
														<label class="control-label">Reporting Time <small class="text-muted">(in days)</small></label>
														<input type="number" min="0" class="form-control" name="reporting_time" value="{{ $analysis_type->reporting_time }}" placeholder="Analysis Type Reporting Time..." required />
													</div>
                          <div class="form-group">
                            <input type="hidden" name="lab_id" value="{{ $selected_lab->id }}" />
                            <label class="control-label"><input type="checkbox" name="active" value="1" {{ $analysis_type->active == 1 ? 'checked' : '' }} /> Active</label>
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
      @if(count($analysis_types) == 0)
        <div class="alert alert-info">
          <i class="mdi mdi-alert"></i> No Analysis Types added yet.
        </div>
      @endif
    </div>
  </main>
@endsection

@section('script2')
  <div id="add-analysis-type" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <!-- Modal content-->
      <form class="modal-content" method="POST" action="{{ route('add-analysis-types') }}" enctype="multipart/form-data">
        @csrf
        <div class="modal-header">
          <h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Analysis Type</h4>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label class="control-label">Name</label>
            <input type="text" class="form-control" name="name" placeholder="Analysis Type Name..." required />
          </div>
          <div class="form-group">
            <label class="control-label">Code</label>
            <input type="text" class="form-control" name="code" placeholder="Analysis Type Code..." required />
          </div>
          <div class="form-group">
            <label class="control-label">Description</label>
            <textarea class="form-control" name="description" placeholder="Description..." required></textarea>
          </div>
          <div class="form-group">
            <label class="control-label">Sample Type</label>
            <select class="form-control" name="sample_type_id" data-placeholder>
              @foreach ($sample_types as $sam)
                <option value="{{ $sam->id }}">{{ $sam->name }}</option>
              @endforeach
            </select>
          </div>
					<div class="form-group">
						<label class="control-label">Reporting Time <small class="text-muted">(in days)</small></label>
						<input type="number" min="0" class="form-control" name="reporting_time" placeholder="Analysis Type Reporting Time..." required />
					</div>
          <div class="form-group">
            <input type="hidden" name="lab_id" value="{{ $selected_lab->id }}" />
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