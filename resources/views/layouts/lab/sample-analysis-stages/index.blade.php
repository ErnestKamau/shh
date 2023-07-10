@extends('layouts.lab.layout.app', ['dataTable'=>true,'select2'=>true])

@section('title2')
  <title>Sample Types</title>
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
          'link' => route('sample-analysis-stages'),
          'name' => 'Sample Tracking Stages',
          'icon' => null
        )
      );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    <h2 class="p-4">
      <i class="mdi mdi-sitemap"></i> Lab Section
      <button class="btn btn-primary btn-sm float-right" data-toggle="modal" data-target="#add-sample-analysis-stage"><i class="mdi mdi-plus"></i> Add</button>
    </h2>
    <br>
    <div class="table-responsive bg-light p-4">
      <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
        <thead class="bg-light p-2">
          <tr>
            <th>No</th>
            <th>Name</th>
            <th>Code</th>
            <th>Workflow</th>
            <th>Level</th>
            <th>Section Head</th>
            <th>Lab</th>
            <th>Active?</th>

            <th></th>
          </tr>
        </thead>
        <tbody>
          @if(count($sampleAnalysisStage) > 0)
            @foreach($sampleAnalysisStage as $stage)
              <tr>
                <td valign="center">{{ $loop->iteration }}</td>
                <td>{{ $stage->name }}</td>
                <td>{{$stage->code}}</td>
                <td>{{ $stage->sample_workflow }}</td>
                <td>{{ $stage->level }}</td>
                <td>{{ $stage->getSectionHead()->name ?? '-'}}</td>
                <td>{{ $stage->getLabDetails()->name ?? '-' }}</td>
                <td class="text-small">{!! $stage->active == '1' ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>' !!}</td>
                <td nowrap>
                  <button class="btn btn-primary btn-sm" data-target="#edit-stage-{{ $loop->iteration }}" data-toggle="modal"><i class="mdi mdi-pencil-outline"></i> <small class="hidden-sm-up">Edit</small> </button>
                  <div id="edit-stage-{{ $loop->iteration }}" class="modal fade" role="dialog">
                    <div class="modal-dialog">
                      <!-- Modal content-->
                      <form class="modal-content" method="POST" action="{{ route('update-sample_analysis_stage', ['id'=>$stage->id]) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-header">
                          <h4 class="modal-title"><i class="mdi mdi-pencil-outline"></i> Edit Sample Analysis Stage</h4>
                        </div>
                        <div class="modal-body">
                          <div class="form-group">
                            <label class="control-label">Name</label>
                            <input type="text" class="form-control" name="name" value="{{ $stage->name }}" placeholder="Sample Type Name..." required />
                          </div>
                          <div class="form-group">
                            <label class="control-label">Code</label>
                            <input type="text" class="form-control" name="code" value="{{ $stage->code }}" placeholder="Lab Section Name..." required />
                          </div>
                          <div class="form-group">
                            <label for="" class="control-label">Section Head</label>
                            <select name="section_head_id" id="" class="form-control">
                              @foreach($users as $user)
                              <option value="{{$user->id}}" {{$user->id == $stage->section_head_id ? 'selected' : ''}}>{{$user->name}}</option>
                              @endforeach
                            </select>

                          </div>
                          <div class="form-group">
                            <label for="" class="control-label">Labs</label>
                            <select name="lab_id" id="" class="form-control">
                              <option value="">Choose Lab...</option>
                              @foreach($labs as $lab)
                              <option value="{{$lab->id}}" {{$lab->id == $stage->lab_id ? 'selected' : ''}}>{{$lab->code}} - {{$lab->name}}</option>
                              @endforeach
                            </select>
                          </div>
													<div class="row">
														<div class="col-sm-8">
															<label class="control-label">Sample Worflow</label>
															<select class="form-control" name="sample_workflow" placeholder="Select Sample Workflow..." required>
																<option></option>
																@foreach (getSampleWorflowStages() as $g)
																	<option value="{{ $g }}" {{ $stage->sample_workflow == $g ? 'selected' : '' }}>{{ $g }}</option>
																@endforeach
															</select>
														</div>
														<div class="col-sm-4">
															<label class="control-label">Stage Level</label>
                            	<input type="number" min="1" class="form-control" name="level" value="{{ $stage->level }}" placeholder="Stage Level..." required />
														</div>
                            
													</div>
                          
                          <div class="form-group mt-2">
                            <label class="control-label"><input type="checkbox" name="active" value="1" {{ $stage->active == 1 ? 'checked' : '' }} /> Active</label>
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
      @if(count($sampleAnalysisStage) == 0)
        <div class="alert alert-info">
          <i class="mdi mdi-alert"></i> No Sample Analysis Stage added yet.
        </div>
      @endif
    </div>
  </main>
@endsection

@section('script2')
  <div id="add-sample-analysis-stage" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <!-- Modal content-->
      <form class="modal-content" method="POST" action="{{ route('add-sample-analysis-stage') }}" enctype="multipart/form-data">
        @csrf
        <div class="modal-header">
          <h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Sample Analysis Stage</h4>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label class="control-label">Name</label>
            <input type="text" class="form-control" name="name" placeholder="Sample Analysis Stage Name..." required />
          </div>
          <div class="form-group">
            <label class="control-label">Code</label>
            <input type="text" class="form-control" name="code" value="" placeholder="Lab Section Code..." required />
          </div>
          <div class="form-group">
            <label for="" class="control-label">Section Head</label>
            <select name="section_head_id" id="" class="form-control">
              @foreach($users as $user)
              <option value="{{$user->id}}">{{$user->name}}</option>
              @endforeach
            </select>

          </div>
          <div class="form-group">
            <label for="" class="control-label">Labs</label>
            <select name="lab_id" id="" class="form-control">
              <option value="">Choose Lab...</option>
              @foreach($labs as $lab)
              <option value="{{$lab->id}}">{{$lab->code}} - {{$lab->name}}</option>
              @endforeach
            </select>
          </div>
					<div class="row">
						<div class="col-sm-8">
							<label class="control-label">Sample Worflow</label>
							<select class="form-control" name="sample_workflow" placeholder="Select Sample Workflow..." required>
								<option></option>
								@foreach (getSampleWorflowStages() as $g)
									<option value="{{ $g }}">{{ $g }}</option>
								@endforeach
							</select>
						</div>
						<div class="col-sm-4">
							<label class="control-label">Stage Level</label>
							<input type="number" min="1" class="form-control" name="level" placeholder="Stage Level..." required />
						</div>
					</div>
					<div class="form-group mt-2">
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