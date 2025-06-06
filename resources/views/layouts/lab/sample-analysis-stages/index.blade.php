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
     
    </h2>
    
    <div class="card tab-card">
      <div class="card-header tab-card-header">
        <ul class="nav nav-tabs card-header-tabs" id="asset-tabs" role="tablist">
          <li class="nav-item">
            <a href="#sample-type-tab" class="nav-link active" id="lab-sec-tab" data-toggle="tab" role="tab" aria-controls="lab-sec-tab" aria-selected="true"> <i class="mdi mdi-sitemap" style=" color: black; font-size:15px"></i> Lab Sections</a>
          </li>
          <li class="nav-item">
            <a href="#sample-stages-tab" class="nav-link" id="lab-sec-tab" data-toggle="tab" role="tab" aria-controls="lab-sec-tab" aria-selected="true"> <i class="mdi mdi-sitemap" style=" color: black; font-size:15px"></i> Sample Stages</a>
          </li>
          <li class="nav-item">
            <a href="#lab-sections-tab" class="nav-link" id="lab-sec-tab" data-toggle="tab" role="tab" aria-controls="lab-sec-tab" aria-selected="true"> <i class="mdi mdi-account-check" style=" color: black; font-size:15px"></i> Verifier Configuration</a>
          </li>
          
        </ul>
      </div>
      <div class="tab-content" id="sample-type-tabs-content">
      <!-- sample stages -->
       <div class="tab-pane fade show p-3" id="sample-stages-tab" role="tabpanel" aria-labelledby="one-tab">
        <h5 class="card-title">
          Sample Stages
          <span class="btn btn-sm btn-outline-primary float-right" data-toggle="modal" data-target="#add-sample-analysis-stage"><i class="mdi mdi-plus"></i> Add</span>
        </h5>
        <div class="table-responsive bg-light p-3">
          <table class="table table-sm table-condensed table-stripped ">
            <thead>
            <th>#</th>
                  <th>Name</th>
                  <th>Code</th>
                  <th>Workflow</th>
                  <th>Level</th>
                  <th>Active</th>
                  <th>Is System Stage</th>
            </thead>
            <tbody>
              @foreach ($sampleAnalysisStage as $stage)
                <tr>
                  <td>
                    <span class="btn btn-sm btn-default text-primary" data-record="{{ json_encode($stage) }}" data-toggle="modal" data-target="#edit-sample-stage"><i class="mdi mdi-pencil" data-toggle="tooltip" title="Edit"></i></span>
                    <span class="btn btn-sm btn-default text-danger" data-record="{{ json_encode($stage) }}" data-toggle="modal" data-target="#delete-stages"><i class="mdi mdi-delete-empty" data-toggl="tooltip" title="Delete"></i> </span>
                  </td>
                  <td>{{ $stage->name }}</td>
                  <td>{{$stage->code}}</td>
                  <td>{{ $stage->sample_workflow }}</td>
                  <td>{{ $stage->level }}</td>
                  <td class="text-small">{!! $stage->active == '1' ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>' !!}</td>
                  <td class="text-center">{!! $stage->is_system == 1 ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>' !!}</td>
                </tr>
                
              @endforeach
            </tbody>
          </table>
        </div>

       </div>
        <!-- all lab sections  -->
        <div class="tab-pane fade show active p-3" id="sample-type-tab" role="tabpanel" aria-labelledby="one-tab">
          <h5 class="card-title">Lab Sections
            <button class="btn btn-primary btn-sm float-right" data-toggle="modal" data-target="#add-lab-section"><i class="mdi mdi-plus"></i> Add</button>
          </h5>
          <div class="table-responsive p-3 bg-light">
            <table class="table table-sm table-condensed table-stripped ">
              <thead class="bg-light p-2">
                <tr>
                  <th></th>
                  <th>Name</th>
                  <th>Code</th>
                  <th>Section Head</th>
                  <th>Lab</th>
                  <th>Active?</th>
                </tr>
              </thead>
              <tbody>
                
                  @foreach($labsections as $sections)
                    <tr>
                      <td >
                        <span class="btn btn-sm btn-default text-primary" data-toggle="modal" data-target="#edit-lab-section" data-toggle="modal" data-record="{{ json_encode($sections) }}" ><i class="mdi mdi-pencil" data-togle="tooltip" title="Pencil"></i></span>
                        <span class="btn btn-sm btn-default text-danger" data-toggle="modal" data-target="#delete-stages" data-record="{{ json_encode($sections) }}"><i class="mdi mdi-delete-empty" data-toggle="tooltip" title="Delete"></i></span>
                      </td>
                      <td>{{ $sections->name }}</td>
                      <td>{{$sections->code}}</td>
                      <td>{{ $sections->getSectionHead()->name ?? '-'}}</td>
                      <td>{{ $sections->getLabDetails()->name ?? '-' }}</td>
                      <td class="text-small">{!! $sections->active == '1' ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>' !!}</td>
                    </tr>
                  @endforeach
                
              </tbody>
            </table>
          </div>
        </div>
        <!-- labsection approvers tab  -->
        <div class="tab-pane fade p-3" id="lab-sections-tab" role="tabpanel" aria-labelledby="one-tab">
          <h5 class="card-title">Verifier Configuration
            <button class="btn btn-outline-primary btn-sm float-right" data-action="add" data-toggle="modal" data-target="#add-approver"><i class="mdi mdi-plus"></i> Add</button>
          </h5>
          <div class="table-responsive p-3 bg-light">
            <table class="table table-condensed my-small-text table-bordered table-sm">
              <thead>
                <tr>
                  <th></th>
                  <th>Name</th>
                  <th>Title</th>
                  <th>Sections</th>
                </tr>
              </thead>
              <tbody>
                 @foreach($approvers as $approver)       
                 <tr>
                    <td>
                      <span class="btn btn-default btn-sm text-primary" data-record="{{json_encode($approver)}}" data-action="edit" data-target="#add-approver" data-toggle="modal"><i class="mdi mdi-pencil" data-toggle="tooltip" title="Edit"></i></span>
                      <span class="btn btn-default btn-sm text-danger" data-record="{{json_encode($approver)}}" data-target="#delete-approver" data-toggle="modal"><i class="mdi mdi-delete-empty" data-toggle="tooltip" title="Delete"></i></span>

                    </td>
                    <td>{{$approver->approvername}}</td>
                    <td>{{$approver->title}}</td>
                    <td>{{$approver->sectionname}}</td>
                 </tr>
                 @endforeach
              </tbody>
            </table>
          </div>
         
        </div>
      </div>
    </div>
  </main>
@endsection

@section('script2')

<div class="modal fade" id="add-approver" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="{{route('addSectionApproval')}}" method="post">
        @csrf  
        <div class="modal-body">
         
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-sm btn-outline-primary"><i class="mdi mdi-content-save"></i> Save</button>
          <span class="btn btn-sm btn-default" data-dismiss="modal">Close</span>
        </div>
      </form>
    </div>
  </div>
</div>
<div class="modal fade" id="delete-approver" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="{{route('deleteSectionApproval')}}" method="post">
        @csrf  
        <div class="modal-body">
         
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-outline-danger btn-sm"><i class="mdi mdi-outline-danger"></i> Delete</button>
          <span class="btn btn-sm btn-default" data-dismiss="modal">Close</span>
        </div>
      </form>
    </div>
  </div>
</div>
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
          <input type="text" class="form-control" name="code" value="" placeholder="Stage Code..." required />
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
          <label for="" class="control-label"><input type="checkbox" value="1" name="is_system" id=""> Is System Stage</label>
        </div>
        <input type="hidden" name="is_sample_stage" value="1">
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
<div id="add-lab-section" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <form class="modal-content" method="POST" action="{{ route('add-sample-analysis-stage') }}" enctype="multipart/form-data">
      @csrf
      <div class="modal-header">
        <h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Lab Setction</h4>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label class="control-label">Name</label>
          <input type="text" class="form-control" name="name" placeholder="Lab Section Name..." required />
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
<div class="modal fade" id="edit-sample-stage" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="{{ route('update-sample_analysis_stage') }}" method="post">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">Edit Sample Stage</h5>
        </div>
        <div class="modal-body">
          
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-sm btn-outline-primary"><i class="mdi mdi-content-save"></i> Save</button>
          <span class="btn btn-sm btn-default" data-dismiss="modal">Close</span>
        </div>

      </form>
    </div>
  </div>
</div>
<div class="modal fade" id="edit-lab-section" role="dialog">
  <div class="modal-dialog" role="dialog">
    <div class="modal-content">
      <form action="{{ route('update-sample_analysis_stage') }}" method="post">
        @csrf
        <div class="modal-header">
          <h5>Edit Lab Section</h5>
        </div>
        <div class="modal-body">
          
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-sm btn-outline-primary"><i class="mdi mdi-content-save"></i> Save</button>
          <span class="btn btn-sm btn-default" data-dismiss="modal">Close</span>
        </div>
      </form>
    </div>
  </div>
</div>
<div class="modal fade" id="delete-stages" role="dialog">
    <div class="modal-dialog">
      <div class="modal-content">
        <form action="<?= route('delete-stage') ?>" method="post">
          @csrf 
          <div class="modal-body">
            
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="mdi mdi-thumbs-up"></i> Yes, Delete</button>
            <span class="btn btn-sm btn-default" data-dismiss="modal">Close</span>
          </div>
        </form>
      </div>
    </div>
</div>
  <script>
    $(()=>{
      var editSampleStage = (data)=>{
        var body = $(`
        <div class="form-group">
            <label class="control-label">Name</label>
            <input type="text" class="form-control" name="name" value="${data.name}" placeholder="Sample Analysis Stage Name..." required />
          </div>
          <div class="form-group">
            <label class="control-label">Code</label>
            <input type="text" class="form-control" value="${data.code}" name="code" value="" placeholder="Lab Section Code..." required />
          </div>
          <div class="row">
            <div class="col-sm-8">
              <label class="control-label">Sample Worflow</label>
              <select class="form-control sample_workflow" name="sample_workflow" placeholder="Select Sample Workflow..." required>
                <option></option>
                @foreach (getSampleWorflowStages() as $g)
                  <option value="{{ $g }}">{{ $g }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-sm-4">
              <label class="control-label">Stage Level</label>
              <input type="number" min="1" value="${data.level}" class="form-control" name="level" placeholder="Stage Level..." required />
            </div>
          </div>
          <div class="form-group mt-2">
            <label for="" class="control-label"><input type="checkbox" value="1" ${data.is_system == 1 ? 'checked' : ''} name="is_system" id=""> Is System Stage</label>
          </div>
          <input type="hidden" name="is_sample_stage" value="1">
          <input type="hidden" name="record_id" value="${data.id}}">

          <div class="form-group">
            <label class="control-label"><input type="checkbox" name="active" value="1" checked /> Active</label>
          </div>
        `).clone();
        $(body).find('.sample_workflow').val(data.sample_workflow);
        return body
      }
      $('#edit-sample-stage').on('show.bs.modal',(e)=>{
        var data = $(e.relatedTarget).data('record');
        var body =editSampleStage(data);
        $('#edit-sample-stage').find('.modal-body').empty();
        $('#edit-sample-stage').find('.modal-body').append(body)
      })
      var editLabsectionBody = (data)=>{
        var body = $(`
          <div class="form-group">
            <label class="control-label">Name</label>
            <input type="text" class="form-control" value="${data.name}" name="name" placeholder="Sample Analysis Stage Name..." required />
          </div>
          
          <div class="form-group">
            <label class="control-label">Code</label>
            <input type="text" class="form-control" value="${data.code}" name="code" value="" placeholder="Lab Section Code..." required />
          </div>
          <div class="form-group">
            <label for="" class="control-label">Section Head</label>
            <select name="section_head_id" id="" class="form-control section_head_id">
              @foreach($users as $user)
              <option value="{{$user->id}}">{{$user->name}}</option>
              @endforeach
            </select>

          </div>
          <div class="form-group">
            <label for="" class="control-label">Labs</label>
            <select name="lab_id" id="" class="form-control lab_id">
              <option value="">Choose Lab...</option>
              @foreach($labs as $lab)
              <option value="{{$lab->id}}">{{$lab->code}} - {{$lab->name}}</option>
              @endforeach
            </select>
          </div>
          <div class="form-group">
            <label class="control-label"><input type="checkbox" name="active" value="1" ${data.active  == 1 ? 'checked' : ''} /> Active</label>
          </div>
          <input type="hidden" name="record_id" value="${data.id}}">
        `).clone();
        $(body).find('.lab_id').val(data.lab_id);
        $(body).find('.section_head_id').val(data.section_head_id);
        return body;
      }

      $('#edit-lab-section').on('show.bs.modal',(e)=>{
        var data = $(e.relatedTarget).data('record');
        var body = editLabsectionBody(data);
        $('#edit-lab-section').find('.modal-body').empty();
        $('#edit-lab-section').find('.modal-body').append(body);
      })

      let deleteStageBody = (data)=>{
        var body = $(`
         <div class="alert alert-danger p-2 d-flex">
            <i class="mdi mdi-delete-empty"></i>
            <span class="pl-2">Confirm you want to delete ${data.name} ${data.is_sample_stage == 1 ? 'Sample Stage' : 'Labsection'} .</span>
          </div>
          <input type="hidden" name="record_id" value="${data.id}">
        `).clone();
        return body;
      }
      $('#delete-stages').on('show.bs.modal',(e)=>{
        var stage = $(e.relatedTarget).data('record');
        var body = deleteStageBody(stage);
        $('#delete-stages').find('.modal-body').empty();
        $('#delete-stages').find('.modal-body').append(body);
      });
      let deleteAppoverBody = (data)=>{
        var body = $(`
          <div class="alert alert-danger p-2 d-flex">
            <i class="mdi mdi-delete-empty" style="font-size: 25px"></i>
            <span class="p-2">Confirm you want to delete ${data.approvername} Lab Section Approver</span>
          </div>
          <input type="hidden" name="approver_id" value="${data.id}">
        `).clone();
        return body;
      }
      let addApproverBody = (data=false)=>{
        if(data){
          var body = $(`
          <div class="alert alert-primary p-2 d-flex">
            <i class="mdi mdi-plus" style="font-size:25px"></i>
            <div class="p-2">
              Edit ${data.approvername} Lab Sections approvers by providing the information below:
            </div>
          </div>
          <div class="form-group">
            <label for="" class="control-label">Title</label>
            <input type="text" value="${data.title}" name="title" placeholder="Title..." class="form-control">
          </div>
          <input type="hidden" name="approver_id" value="${data.id}">
          <div class="form-group">
            <label for="" class="control-label">Approver</label>
            <select name="user_id" id="user_id_field" class="form-control">
              @foreach($users as $user)
              <option value="{{$user->id}}">{{$user->name}}</option>
              @endforeach
            </select>
          </div>
          <div class="form-group">
            <label for="" class="control-label">Sections</label>
            <select name="section_ids[]" multiple id="section_ids" class="form-control">
              @foreach($labsections as $stage)
              <option value="{{$stage->id}}">{{$stage->code}} - {{$stage->name}}</option>
              @endforeach
            </select>
          </div>
          `).clone();
          $(body).find('#user_id_field').val(data.user_id);
          $(body).find('#section_ids').val(data.sectionarr);
         

        }else{
          var body = $(`
          <div class="alert alert-primary p-2 d-flex">
            <i class="mdi mdi-plus" style="font-size:25px"></i>
            <div class="p-2">
              Add Lab Sections approvers by providing the information below:
            </div>
          </div>
          <div class="form-group">
            <label for="" class="control-label">Title</label>
            <input type="text" name="title" placeholder="Title..." class="form-control">
          </div>
          <input type="hidden" name="approver_id" value="0">
          <div class="form-group">
            <label for="" class="control-label">Approver</label>
            <select name="user_id" id="user_id_field" class="form-control">
              @foreach($users as $user)
              <option value="{{$user->id}}">{{$user->name}}</option>
              @endforeach
            </select>
          </div>
          <div class="form-group">
            <label for="" class="control-label">Sections</label>
            <select name="section_ids[]" multiple id="section_ids" class="form-control">
              @foreach($sampleAnalysisStage as $stage)
              <option value="{{$stage->id}}">{{$stage->code}} - {{$stage->name}}</option>
              @endforeach
            </select>
          </div>
          `).clone();
        }
        $(body).find('#section_ids').select2();
          $(body).find('#user_id_field').select2();
        return body;
      }
      $('#add-approver').on('show.bs.modal',(e)=>{
        var mode = $(e.relatedTarget).data('action');
        var data = mode == 'add' ? [] : $(e.relatedTarget).data('record');
        var body = mode == 'add' ? addApproverBody() : addApproverBody(data);
        $('#add-approver').find('.modal-body').empty();
        $('#add-approver').find('.modal-body').append(body);
      });
      $('#delete-approver').on('show.bs.modal',(e)=>{
        var data = $(e.relatedTarget).data('record')
        var body = deleteAppoverBody(data);
        $('#delete-approver').find('.modal-body').empty();
        $('#delete-approver').find('.modal-body').append(body);

      })
    });
  </script>
@endsection