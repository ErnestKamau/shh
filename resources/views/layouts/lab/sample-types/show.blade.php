@extends('layouts.lab.layout.app', ['dataTable'=>true, 'select2'=>true])

@section('title2')
<title> {{ $sample_type->name }} | Sample Types</title>

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
      'link' => route('sample-types'),
      'name' => 'Sample Types',
      'icon' => null
    ),
    array(
      'link' => '#',
      'name' => $sample_type->name,
      'icon' => null
    )
  );
  ?>
  <x-bread-crumb :items="$items"></x-bread-crumb>
  <h2 class="p-4">
    <i class="mdi mdi-microscope"></i> {{ $sample_type->name }} <small class="text-muted"> | Sample Types</small>
  </h2>
  <div class="p-4">
    <div class="card tab-card">
      <div class="card-header tab-card-header">
        <ul class="nav nav-tabs card-header-tabs" id="sample-types-tabs" role="tablist">
          <li class="nav-item">
            <a class="nav-link active" id="active-analysis-tab" data-toggle="tab" href="#active-analysis-tab-content" role="tab" aria-controls="Active-Analysis" aria-selected="true">Active Analysis</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" id="inactive-analysis-tab" data-toggle="tab" href="#inactive-analysis-tab-content" role="tab" aria-controls="InActive-Analysis" aria-selected="false">In Active Analysis</a>
          </li>
         
          <li class="nav-item">
            <a class="nav-link" id="sample-analysis-stage-tab" data-toggle="tab" href="#sample-analysis-stage-tab-content" role="tab" aria-controls="Sample-Analysis-Stage" aria-selected="false">Lab Sections</a>
          </li>
          <li class="nav-item">

          </li>
          <li class="nav-item">
            <a class="nav-link" id="qualification-tab" data-toggle="tab" href="#qualification" role="tab" aria-controls="Qualifications" aria-selected="false">Certification</a>
          </li>
        </ul>
      </div>

      <div class="tab-content" id="sample-types-tabs">
        <div class="tab-pane fade show p-3" id="qualification" role="tabpanel" aria-labelledby="one-tab">
          <h5 class="card-title">
            <i class="mdi mdi-file-certificate-outline"></i>Certification
            <button class="btn btn-outline-primary btn-sm float-right" data-toggle="modal" data-target="#add-qualification"><i class="mdi mdi-plus"></i> Add</button>
          </h5>
          <div class="table-responsive bg-light p-4">
            <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
              <thead class="bg-light p-2">
                <tr>
                  <th>No</th>
                  <th nowrap>Name</th>

                  <th>Status</th>
                  <th>Edited_by</th>
                  <th nowrap>Description</th>
                  <th></th>

                  <!-- ---  -->

                </tr>
              </thead>
              <tbody>
                @foreach ($qualifications as $item)
                @if($item->status == 0)
                <?php
                $qualification_sample = getSampleTypeQualificationById($item->qualification_id)
                ?>
                <tr>
                  <td valign="center">{{ $loop->iteration }}</td>

                  <td>{{$qualification_sample->name}}</td>
                  <td>{!! $item->is_mandatory == 1 ? '<i style="color:red;" class="mdi mdi-star-four-points"></i><span style="color: red;">Mandatory</span>':'Optional' !!}</td>

                  <td>{!! $item->edited_by == '' ? 'N/a':$item->edited_by !!}</td>
                  <td class="text-center">
                    <span class="btn btn-outline-dark btn-sm" data-toggle="modal" data-target="#description-qualification-{{$item->id}}"> <i class="mdi mdi-message-text"></i></span>
                    <div id="description-qualification-{{$item->id}}" class="modal fade" role="dialog">
                      <div class="modal-dialog">
                        <!-- Modal content-->
                        <div class="modal-content">

                          <div class="modal-header">
                            <h4 class="modal-title"><i class="mdi mdi-eye"></i> {{$qualification_sample->name}} Description</h4>
                          </div>
                          <div class="modal-body">
                            <h5>{{$qualification_sample->name}} Description.</h5>
                            <div class="pane panel-default">
                              <div class="panel-body">
                                {{$qualification_sample->description}}
                              </div>
                            </div>
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </td>
                  <td>


                    <span class="btn btn-outline-info btn-sm" data-toggle="modal" data-target="#edit-qualifications-{{$item->id}}"> <i class="mdi mdi-pencil"></i> </span>

                    <div id="edit-qualifications-{{$item->id}}" class="modal fade" role="dialog">
                      <div class="modal-dialog">

                        <form action="{{route('edit-sample-type-qualification',['id'=>$item->id])}}" method="post" class="modal-content" enctype="multipart/form-data">
                          @csrf
                          <div class="modal-header">
                            <h4 class="modal-title"><i class="mdi mdi-pencil"></i> Edit {{$qualification_sample->name}} Sample-Type Certification </h4>
                          </div>
                          <div class="modal-body">
                            <div class="form-group">
                              <label class="control-label">Certificate Name</label>
                              <select name="qualification" class="form-control" aria-placeholder="Qualifications...">
                                @foreach($qualification_list as $qualification)
                                @if($qualification->status == 0 && $qualification->module_code )
                                <?php
                                $qualification_detail = getSampleTypeQualificationById($qualification->id)
                                ?>
                                <option value="{{$qualification->id}}" {{$qualification->id == $item->qualification_id ? 'selected' : ''}}>{{$qualification_detail->name}}</option>
                                @endif
                                @endforeach
                              </select>

                            </div>
                            <div class="form-group">
                              <label class="control-label">
                                <input type="checkbox" value=1 {{$item->is_mandatory == 1 ? 'checked':''}} name="mandatory">
                                Mandatory
                              </label>
                            </div>

                          </div>
                          <div class="modal-footer">
                            <button type="submit" class="btn btn-primary"> <i class="mdi mdi-content-save"></i> Update</button>
                            <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                          </div>
                        </form>
                      </div>
                    </div>
                    <span class="btn btn-outline-danger btn-sm" data-toggle="modal" data-target="#delete-qualifications-{{$item->id}}"> <i class="mdi mdi-delete-empty"></i> </span>
                    <div class="modal fade" id="delete-qualifications-{{$item->id}}">
                      <div class="modal-dialog">
                        <form action="{{route('delete-sample-type-qualification',['id'=>$item->id])}}" method="post" class="modal-content">
                          @csrf
                          <div class="modal-header">
                            <h4 class="modal-title"> <i style="color: red;" class="mdi mdi-delete-empty"></i> Delete {{$qualification_sample->name}} Sample Type Certification</h4>
                          </div>
                          <div class="modal-body">
                            <div class="panel panel-default">
                              <div class="panel-body">
                                Are you sure you want to delete <b>{{$qualification_sample->name}}</b> sample type certification!
                              </div>
                            </div>
                            <div class="form-group hidden">
                              <label class="control-label">Qualification</label>
                              <input type="text" name="qualification" value="{{$item->id}}">
                            </div>
                          </div>
                          <div class="modal-footer">
                            <button type="submit" class="btn btn-outline-primary"> <i class="mdi mdi-content-save"></i> Yes</button>
                            <button type="button" class="btn btn-outline-danger" data-dismiss="modal">Cancel</button>
                          </div>
                        </form>
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
        <div class="tab-pane fade show active p-3" id="active-analysis-tab-content" role="tabpanel" aria-labelledby="one-tab">
          <h5 class="card-title">Active Analysis Types
            <button class="btn btn-primary btn-sm float-right" data-toggle="modal" data-target="#add-analysis-type"><i class="mdi mdi-plus"></i> Add</button>
          </h5>
          <div class="table-responsive">
            <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
              <thead class="bg-light p-2">
                <tr>
                  <th></th>
                  <th>Code</th>
                  <th>Name</th>
                  <th>Short Name</th>
                  <th>Reporting Time</th>
                  <th>Description</th>
                  <th>Sample Type</th>
                  <th>Lab</th>
                  <th>Lab Section</th>
                  <th>Report Brand</th>
                  <th>Active?</th>
                  <th></th>
                </tr>
              </thead>
              <tbody id="analytes-holder">
                @if(count($analysis_types['active']) > 0)
                @foreach($analysis_types['active'] as $analysis_type)
                <tr data-element="{{ $analysis_type->id }}">
                  <td valign="center" nowrap style="font-size: 17px">
                    <span style="cursor: pointer">
                      <i class="mdi mdi-arrow-up-drop-circle move-analyte-up move-analyte" data-action="move-up"></i>
                    </span>
                    <span style="cursor: pointer">
                      <i class="mdi mdi-arrow-down-drop-circle move-analyte-down move-analyte" data-action="move-down"></i> 
                    </span>
                  </td>
                  <td>{{ $analysis_type->code }}</td>
                  <td>{{ $analysis_type->name }}</td>
                  <td>{{ $analysis_type->short_name ?? 'N/A' }}</td>
                  <td>{{ $analysis_type->reporting_time ?? 0 }}</td>
                  <td>{{ $analysis_type->description }}</td>
                  <td>{{ $analysis_type->sample_type->name }}</td>
                  <td>{{ $analysis_type->lab->name }} - {{ $analysis_type->lab->code }}</td>
                  <td>{{ $analysis_type->labsectionname }}</td>
                  <td>
                    @if($analysis_type->brand_id == 1)
                    Physical Format
                    @elseif($analysis_type->brand_id ==2)
                    Pesticide Format
                    @else
                    Standard Format
                    @endif
                  </td>
                  <td class="text-small">{!! $analysis_type->active == '1' ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>' !!}</td>
                  <td nowrap>
                    <button class="btn btn-white text-primary btn-sm" data-target="#edit-active-analysis_type-{{ $loop->iteration }}" data-toggle="modal"><i class="mdi mdi-pencil-outline"></i> <small class="hidden-sm-up">Edit</small> </button>
                    {{-- <button class="btn btn-danger btn-sm"><i class="mdi mdi-delete-empty"></i> <small class="hidden-sm-up">Delete</small> </button>  --}}
                    <a class="btn btn-white text-success btn-sm" href="{{ route('analysis-type', ['id'=>$analysis_type->id]) }}">
                      <i class="mdi mdi-eye-outline"></i> <small class="hidden-sm-up">Show</small> 
                    </a>
                    <button class="btn btn-white btn-sm" data-value="{{ route('analysis-type-clone', [$analysis_type->id]) }}" data-toggle="modal" data-target="#clone-analysis-type-modal" title="clone"><i class="mdi mdi-content-duplicate"></i></button>
                    <div id="edit-active-analysis_type-{{ $loop->iteration }}" class="modal fade" role="dialog">
                      <div class="modal-dialog">
                        <!-- Modal content-->
                        <form class="modal-content" method="POST" action="{{ route('edit-analysis-type', ['id'=>$analysis_type->id]) }}" enctype="multipart/form-data">
                          @csrf
                          <div class="modal-header">
                            <h4 class="modal-title"><i class="mdi mdi-pencil-outline"></i> Edit New Analysis Type</h4>
                          </div>
                          <div class="modal-body">
                            <div class="form-group">
                              <label class="control-label">Name</label>
                              <input type="text" class="form-control" name="name" value="{{ $analysis_type->name }}" placeholder="Analysis Type Name..." required />
                            </div>
                            <div class="form-group">
                              <label class="control-label">Short Name</label>
                              <input type="text" class="form-control" name="short_name" value="{{ $analysis_type->short_name }}" placeholder="Analysis Type Short Name..." />
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
                                <option value="{{ $sample_type->id }}" {{ $sample_type->id == $analysis_type->sample_type_id ? 'selected' : '' }}>{{ $sample_type->name }}</option>
                              </select>
                            </div>
                            <div class="form-group">
                              <label class="control-label">Reporting Time</label>
                              <input type="number" min="0" class="form-control" name="reporting_time" value="{{ $analysis_type->reporting_time }}" placeholder="Analysis Type Reporting Time..." required />
                            </div>
                            <div class="form-group">
                              <label class="control-label">Lab</label>
                              <select class="form-control" name="lab_id" required>
                                <option value="">Select Lab...</option>
                                @foreach ($labs as $c)
                                <option value="{{ $c->id }}" {{ $c->id == $analysis_type->lab_id ? 'selected' : '' }}>{{ $c->name }}</option>
                                @endforeach
                              </select>
                            </div>
                            <div class="form-group">
                              <label for="" class="control-label">Lab Section</label>
                              <select name="lab_section_id" id="" class="form-control">
                                @if($sample_type->sample_analysis_stage)
                                  @foreach($sample_type->sample_analysis_stage as $stage)
                                    @if($stage->active == 1)
                                      <option value="{{$stage->sample_analysis_stage_id}}" {{$stage->sample_analysis_stage_id == $analysis_type->lab_section_id ? 'selected' : ''}}>{{$stage->sample_analysis_stage->name}}</option>
                                    @endif
                                  @endforeach
                                @endif
                              </select>
                            </div>
                            <div class="form-group">
                              <label for="" class="control-label">Report Brand</label>
                              <select name="brand_id" id="" class="form-control">
                                <option value="">Select Report Brand</option>
                                <option value="0"{{$analysis_type->brand_id == 0 || $analysis_type->brand_id == '' ? 'selected' : ''}}>Normal</option>
                                <option value="1" {{$analysis_type->brand_id == 1 ? 'selected' : ''}}>Physical Format</option>
                                <option value="2" {{$analysis_type->brand_id == 2 ? 'selected' : ''}}>Pesticide Format</option>
                              </select>
                            </div>
                            <div class="form-group">
                              <label class="control-label"><input type="checkbox" name="active" value="1" {{ $analysis_type->active == 1 ? 'checked' : '' }} /> Active</label>
                            </div>
                            <div class="form-group">
                              <label class="control-label"><input type="checkbox" name="is_pesticide" value="1" {{ $analysis_type->is_pesticide == 1 ? 'checked' : '' }} /> Is Pesticide</label>
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
            @if(count($analysis_types['active']) == 0)
            <div class="alert alert-info">
              <i class="mdi mdi-alert"></i> No Active Analysis Types added yet.
            </div>
            @endif
          </div>
        </div>
        <div class="tab-pane fade p-3" id="inactive-analysis-tab-content" role="tabpanel" aria-labelledby="one-tab">
          <h5 class="card-title">In-Active Analysis Types <div class="btn btn-sm btn-info float-right" data-target="#add-analyte" data-toggle="modal"><i class="mdi mdi-plus"></i> Add</div>
          </h5>
          <div class="table-responsive">
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
                  <th>Lab Section</th>
                  <th>Report Brand</th>
                  <th>Active?</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                @if(count($analysis_types['inactive']) > 0)
                @foreach($analysis_types['inactive'] as $analysis_type)
                <tr>
                  <td valign="center">{{ $loop->iteration }}</td>
                  <td>{{ $analysis_type->code }}</td>
                  <td>{{ $analysis_type->name }}</td>
                  <td>{{ $analysis_type->short_name ?? 'N/A' }}</td>
                  <td>{{ $analysis_type->reporting_time ?? 0 }}</td>
                  <td>{{ $analysis_type->description }}</td>
                  <td>{{ $analysis_type->sample_type->name }}</td>
                  <td>{{ $analysis_type->lab->name }} - {{ $analysis_type->lab->code }}</td>
                  <td>{{ $analysis_type->labsectionname }}</td>
                  <td>
                    @if($analysis_type->brand_id == 1)
                    Physical Format
                    @elseif($analysis_type->brand_id ==2)
                    Pesticide Format
                    @else
                    Standard Format
                    @endif
                  </td>
                  <td class="text-small">{!! $analysis_type->active == '1' ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>' !!}</td>
                  <td nowrap>
                    <button class="btn btn-primary btn-sm" data-target="#edit-inactive-analysis_type-{{ $loop->iteration }}" data-toggle="modal"><i class="mdi mdi-pencil-outline"></i> <small class="hidden-sm-up">Edit</small> </button>
                    {{-- <button class="btn btn-danger btn-sm"><i class="mdi mdi-delete-empty"></i> <small class="hidden-sm-up">Delete</small> </button>  --}}
                    <a class="btn btn-success btn-sm" href="{{ route('analysis-type', ['id'=>$analysis_type->id]) }}"><i class="mdi mdi-eye-outline"></i> <small class="hidden-sm-up">Show</small> </a>
                    <div id="edit-inactive-analysis_type-{{ $loop->iteration }}" class="modal fade" role="dialog">
                      <div class="modal-dialog">
                        <!-- Modal content-->
                        <form class="modal-content" method="POST" action="{{ route('edit-analysis-type', ['id'=>$analysis_type->id]) }}" enctype="multipart/form-data">
                          @csrf
                          <div class="modal-header">
                            <h4 class="modal-title"><i class="mdi mdi-pencil-outline"></i> Edit New Analysis Type</h4>
                          </div>
                          <div class="modal-body">
                            <div class="form-group">
                              <label class="control-label">Name</label>
                              <input type="text" class="form-control" name="name" value="{{ $analysis_type->name }}" placeholder="Analysis Type Name..." required />
                            </div>
                            <div class="form-group">
                              <label class="control-label">Short Name</label>
                              <input type="text" class="form-control" name="short_name" value="{{ $analysis_type->short_name }}" placeholder="Analysis Type Short Name..." />
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
                                <option value="{{ $sample_type->id }}" {{ $sample_type->id == $analysis_type->sample_type_id ? 'selected' : '' }}>{{ $sample_type->name }}</option>
                              </select>
                            </div>
                            <div class="form-group">
                              <label class="control-label">Reporting Time <small class="text-muted">(in days)</small></label>
                              <input type="number" min="0" class="form-control" name="reporting_time" value="{{ $analysis_type->reporting_time }}" placeholder="Analysis Type Reporting Time..." required />
                            </div>
                            <div class="form-group">
                              <label class="control-label">Lab</label>
                              <select class="form-control" name="lab_id" required>
                                <option value="">Select Lab...</option>
                                @foreach ($labs as $c)
                                <option value="{{ $c->id }}" {{ $c->id == $analysis_type->lab_id ? 'selected' : '' }}>{{ $c->name }}</option>
                                @endforeach
                              </select>
                            </div>
                            <div class="form-group">
                              <label for="" class="control-label">Lab Section</label>
                              <select name="lab_section_id" id="" class="form-control">
                                @if($sample_type->sample_analysis_stage)
                                  @foreach($sample_type->sample_analysis_stage as $stage)
                                    @if($stage->active == 1)
                                      <option value="{{$stage->sample_analysis_stage_id}}" {{$stage->sample_analysis_stage_id == $analysis_type->lab_section_id ? 'selected' : ''}}>{{$stage->sample_analysis_stage->name}}</option>
                                    @endif
                                  @endforeach
                                @endif
                              </select>
                            </div>
                            <div class="form-group">
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
            @if(count($analysis_types['inactive']) == 0)
            <div class="alert alert-info">
              <i class="mdi mdi-alert"></i> No In-Active Analysis Types added yet.
            </div>
            @endif
          </div>
        </div>
      
        <div class="tab-pane fade p-3" id="sample-analysis-stage-tab-content" role="tabpanel" aria-labelledby="one-tab">
          <h5 class="card-title">Lab Sections <div class="btn btn-sm btn-info float-right" data-target="#add-sample-analysis-stage" data-toggle="modal"><i class="mdi mdi-plus"></i> Add</div>
          </h5>
          <div class="table-responsive">
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
                @if($sample_type->sample_analysis_stage)
                @foreach($sample_type->sample_analysis_stage as $stage)
                <tr>
                  <td valign="center">{{ $loop->iteration }}</td>
                  <td>{{ $stage->sample_analysis_stage->name }}</td>
                  <td class="text-small">{!! $stage->active == '1' ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>' !!}</td>
                  <td nowrap>
                    @if($stage->active == "1")
                    <button class="btn btn-danger btn-sm" data-target="#delete-sample_analysis_stage-{{ $loop->iteration }}" data-toggle="modal"><i class="mdi mdi-delete"></i> <small class="hidden-sm-up">Remove</small> </button>
                    @else
                    <button class="btn btn-primary btn-sm" data-target="#delete-sample_analysis_stage-{{ $loop->iteration }}" data-toggle="modal"><i class="mdi mdi-checkbox-marked-circle"></i> <small class="hidden-sm-up">Remove</small> </button>
                    @endif
                    <div id="delete-sample_analysis_stage-{{ $loop->iteration }}" class="modal fade" role="dialog">
                      <div class="modal-dialog">
                        <!-- Modal content-->
                        <form class="modal-content" method="POST" action="{{ route('update-sample-analysis-stage-to-sample-type', ['id'=>$stage->id]) }}" enctype="multipart/form-data">
                          @csrf
                          @if($stage->active == "1")
                          <div class="modal-header">
                            <h4 class="modal-title"><i class="mdi mdi-delete"></i> Deactivate Lab Section</h4>
                          </div>
                          <div class="modal-body">
                            <div class="form-group">
                              <div class="alert alert-danger">
                                <i class="mdi mdi-alert"></i> Are you sure you want to deactivate this Lab Section from this sample type?
                              </div>
                            </div>
                          </div>
                          <div class="modal-footer">
                            <button type="submit" class="btn btn-danger"><i class="mdi mdi-content-save"></i> Yes, De-Activate</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                          </div>
                          @else
                          <div class="modal-header">
                            <h4 class="modal-title"><i class="mdi mdi-checkbox-marked-circle"></i> Re-Activate Lab Section</h4>
                          </div>
                          <input type="hidden" name="active" value="1" />
                          <div class="modal-body">
                            <div class="form-group">
                              <div class="alert alert-primary">
                                <i class="mdi mdi-alert"></i> Are you sure you want to re-activate this analysis stage for this sample type?
                              </div>
                            </div>
                          </div>
                          <div class="modal-footer">
                            <button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Yes, Re-Activate</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                          </div>
                          @endif
                        </form>
                      </div>
                    </div>
                  </td>
                </tr>
                @endforeach
                @endif
              </tbody>
            </table>
            @if(!$sample_type->sample_analysis_stage || count($sample_type->sample_analysis_stage) == 0)
            <div class="alert alert-info">
              <i class="mdi mdi-alert"></i> No Lab Section added yet.
            </div>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>
</main>
@endsection
@section('script2')
<div class="modal fade" id="add-qualification" role="dialog">
  <div class="modal-dialog">
    <form action="{{route('add-sample-type-qualification',['id'=>$sample_type->id])}}" method="post" class="modal-content" enctype="multipart/form-data">
      @csrf
      <div class="modal-header">
        <h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Certification</h4>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label class="control-label">Certification Name</label>
          <select name="qualification" id="" class="form-control">
            @foreach($qualification_list as $qualification)
            @if($qualification->status == 0 && $qualification->module_code == 0)
            <option value="{{$qualification->id}}">{{$qualification->name}}</option>
            @endif
            @endforeach
          </select>
        </div>
        <div class="form-group">
          <label class="control-label">
            <input type="checkbox" name="mandatory" value=1>
            Mandatory
          </label>

        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-outline-primary"> <i class="mdi mdi-content-save"></i> Save</button>
        <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
      </div>
    </form>
  </div>
</div>
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
          <label class="control-label">Short Name</label>
          <input type="text" class="form-control" name="short_name" placeholder="Analysis Type Short Name..." />
        </div>
        <div class="form-group">
          <label class="control-label">Code</label>
          <input type="text" class="form-control" name="code" placeholder="Analysis Type Code..." required />
        </div>
        <div class="form-group">
          <label class="control-label">Reporting Time <small class="text-muted">(in days)</small></label>
          <input type="number" min="0" class="form-control" name="reporting_time" placeholder="Analysis Type Reporting Time..." required />
        </div>

        <div class="form-group">
          <label class="control-label">Description</label>
          <textarea class="form-control" name="description" placeholder="Description..." required></textarea>
        </div>
        <div class="form-group">
          <label class="control-label">Sample Type</label>
          <select class="form-control" name="sample_type_id" data-placeholder>
            <option value="{{ $sample_type->id }}">{{ $sample_type->name }}</option>
          </select>
        </div>
        <div class="form-group">
          <label class="control-label">Lab</label>
          <select class="form-control" name="lab_id" required>
            <option value="">Select Lab...</option>
            @foreach ($labs as $c)
            <option value="{{ $c->id }}">{{ $c->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="form-group">
          <label for="" class="control-label">Lab Section</label>
          <select name="lab_section_id" id="" class="form-control">
            @if($sample_type->sample_analysis_stage)
              @foreach($sample_type->sample_analysis_stage as $stage)
                @if($stage->active == 1)
                  <option value="{{$stage->sample_analysis_stage_id}}">{{$stage->sample_analysis_stage->name}}</option>
                @endif
              @endforeach
            @endif
          </select>
        </div>
        <div class="form-group">
          <label for="" class="control-label">Report Brand</label>
          <select name="brand_id" id="" class="form-control">
            <option value="">Select Report Brand</option>
            <option value="0">Normal</option>
            <option value="1">Physical Format</option>
            <option value="2">Pesticide Format</option>
          </select>
        </div>
        <div class="form-group">
          <label class="control-label"><input type="checkbox" name="active" value="1" checked /> Active</label>
        </div>
        <div class="form-group">
          <label class="control-label"><input type="checkbox" name="is_pesticide" value="1" /> Is Pesticide</label>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </form>
  </div>
</div>
<div id="add-sample-analysis-stage" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <form class="modal-content" method="POST" action="{{ route('add-sample-analysis-stage-to-sample-type', ['sample_type_id'=>$sample_type->id]) }}" enctype="multipart/form-data">
      @csrf
      <div class="modal-header">
        <h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Lab Section</h4>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label class="control-label">Lab Section</label>
          <select name="sample_analysis_stage_id" class="form-control">
            <option value="">Select Lab Section...</option>
            @foreach ($sample_analysis_stage as $stage)
            <option value="{{ $stage->id }}">{{ $stage->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="form-group">
          <input type="hidden" name="sample_type_id" value="{{ $sample_type->id }}" />
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
<div id="clone-analysis-type-modal" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <form class="modal-content" method="POST" enctype="multipart/form-data">
      @csrf
      <div class="modal-header">
        <h4 class="modal-title"><i class="mdi mdi-duplicate"></i> Clone Analysis Type</h4>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label class="control-label">Name</label>
          <input type="text" class="form-control" name="name" placeholder="Analysis Type Name..." required />
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Clone</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </form>
  </div>
</div>
<script type="text/javascript">
  $(function(){
    $('#clone-analysis-type-modal').on('shown.bs.modal', function (e) {
      var $url = $(e.relatedTarget).data('value');
      $(this).find('form').prop('action', $url);
      $(this).find('form').attr('action', $url);
    });

    var configureThemArrow = function() {
      $('#analytes-holder').find('tr').find('.move-analyte-up').addClass('text-success').removeClass('text-muted');
      $('#analytes-holder').find('tr:first-child').find('.move-analyte-up').addClass('text-muted').removeClass('text-success');

      $('#analytes-holder').find('tr').find('.move-analyte-down').addClass('text-success').removeClass('text-muted');
      $('#analytes-holder').find('tr:last-child').find('.move-analyte-down').addClass('text-muted').removeClass('text-success');
    }

    configureThemArrow();

    $('#analytes-holder').on('click', '.move-analyte:not(.text-muted)', function() {
      var action = $(this).data('action');
      var pTR = $(this).parents('tr');
      var i = pTR.index();
      console.log(pTR.data('element'))
      $.ajax({
        url: "/move-sample-type/" + action + "/{{ $sample_type->id }}/" + pTR.data('element'),
        dataType: 'json',
        beforeSend: function() {
          $('#analytes-holder').find('tr').find('.move-analyte-up').addClass('text-muted');
          $('#analytes-holder').find('tr').find('.move-analyte-down').addClass('text-muted');
        },
        success: function(js) {
          var siblingIndex = action == 'move-up' ? (i - 1) : (i + 1);
          siblingIndex = siblingIndex < 0 ? 0 : siblingIndex;

          var sibling = $('#analytes-holder').find('tr').get(siblingIndex);

          if (action == 'move-up') {
            $(sibling).before(pTR);
          } else {
            $(pTR).before(sibling);
          }

          configureThemArrow();
        }
      })



    });

    $('#add-analyte-guide').on('change', '[name="analyte_id"]', function() {
      var divider = 10 ** parseInt($(this).children('option:selected').data('step'));
      var step = (1 / divider);
      $('#add-analyte-guide').find('[name="value"]').attr("step", step);
    });

    window.setTimeout(function() {
      $('[name="equipment_id"]').trigger('change');
    }, 100);

    window.setTimeout(function() {
      var selected = $('[name="operator_id"]').each(function(e) {
        var sel = $(this).data('selected');
        $(this).val(sel).trigger('change');
      });
    }, 160);
  });
</script>
@endsection