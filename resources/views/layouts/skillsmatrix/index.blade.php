@extends($module == "Skills-Matrix" &&  $config=='Roles' ? 'layouts.personnel.layout.app':'layouts.skillsmatrix.layout.app' , ['dataTable'=>true, 'select2'=>true])
<?php $module_text = implode(" ", explode("-", $module)); ?>
@section('title2')
<title>{{ $module }} | {{ $module }}</title>
@endsection
@section('content2')
<main>
   <?php
      $items = array(
        array(
          'link' => route('matrix'),
          'name' =>'Matrix',
          'icon' => null
        ),
        array(
          'link' => null,
          'name' => 'Configurations',
          'icon' => null
        )
      );
      ?>
   <x-bread-crumb :items="$items"></x-bread-crumb>
   <h2 class="p-4">
      <i class="mdi mdi-account-star-outline"></i> Skills <small class="text-muted">  | Matrix </small>
      <span class="btn btn-sm btn-outline-primary float-right" data-target="#add-config" data-toggle="modal"><i class="mdi mdi-plus"></i> Generate Matrix</span>
      <!-- @if($matrix_info->count() == 0 && $can_edit_skills_matrix == 1)
      <span class="btn btn-sm btn-outline-primary float-right" data-target="#add-config" data-toggle="modal"><i class="mdi mdi-plus"></i> Generate Matrix</span>
      @endif -->
   </h2>
   <div class="p-4">
      <div class="card">
         <div class="card-body">
            <div class="table-responsive">
               <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
                  <thead  class="bg-light p-2">
                     <tr>
                        <th>#</th>
                        <th>Matrix Name</th>
                        <th>Department</th>
                        <th>Roles</th>
                        <th>Created At</th>
                     </tr>
                  </thead>
                  <tbody>
                     @foreach($matrix_info as $matrix)
                     <tr>
                        <td>
                           @if($can_edit_skills_matrix == 1)
                           <span class="btn btn-sm btn-default text-primary" data-toggle="modal" data-target="#edit-matrix" data-record="{{json_encode($matrix)}}"><i class="mdi mdi-pencil" data-toggle="tooltip" data-title="Edit"></i></span>
                           @endif
                           <a href="{{route('show-matrix',['id'=>$matrix->id])}}" class="btn btn-sm btn-default test-success"><i class="mdi mdi-eye" data-toggle="tooltip" data-title="View" ></i></a>
                        </td>
                        <td>{{$matrix->name}}</td>
                        <td>{{$matrix->department}}</td>
                        <td>{{implode(', ',$matrix->jobdescription['names'])}}</td>
                        <td>{{date('Y-m-d',strtotime($matrix->created_at))}}</td>
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
<div id="add-config" class="modal fade" role="dialog">
   <div class="modal-dialog">
      <!-- Modal content-->
      <form class="modal-content" method="POST" action="{{ route('assign-matrix') }}"   enctype="multipart/form-data">
         @csrf
         <div class="modal-header">
            <h4 class="modal-title"><i class="mdi mdi-plus"></i> Generate Skills Matrix</h4>
         </div>
         <div class="modal-body">
            <div class="form-group">
               <label class="control-label">Matrix Name <span class="text-danger">*</span></label>
               <input type="text" class="form-control" name="name"  value="" placeholder="Name..." required />
            </div> 

            <div class="form-group">
               <label for="" class="control-label">Department</label>
               <select name="department_id" id="" class="form-control">
                  <option value="">Select Department</option>
                  @foreach ($departments as $department)
                     <option value="{{$department->id}}">{{$department->name}}</option>
                  @endforeach
               </select>
            </div>

            <div class="form-group">
               <label class="control-label">Matrix Roles <span class="text-danger">*</span></label>
               <select class="form-control" name="matrix_role_ids[]" data-placeholder multiple required>                
                  <option value="">Select Role</option>
                  @foreach ($roles_info as $c)
                  <option value="{{ $c->id }}">{{ $c->description }}</option>
                  @endforeach
               </select>
            </div>
            <div class="form-group">                            
               <label class="control-label"><input type="checkbox" name="status" value="1" checked/> Active</label>
            </div>
         </div>
         <div class="modal-footer">
            <input type="hidden" name="matrix_id" value="0" />
            <button type="submit" class="btn btn-outline-primary"><i class="mdi mdi-content-save"></i> Save</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
         </div>
      </form>
   </div>
</div>
<div class="modal fade" id="edit-matrix" role="dialog">
   <div class="modal-dialog">
      <div class="modal-content">
         <form action="{{route('edit-matrix')}}" method="post">
            @csrf
            <div class="modal-body">
              
            </div>
            <div class="modal-footer">
               <button type="submit" class="btn btn-outline-primary"><i class="mdi mdi-content-save"></i> Save</button>
               <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
         </form>
      </div>
   </div>
</div>
<script type="text/javascript">
			
    $(document).ready(function(){
			var editMatrixBody = (data)=>{
            var body = $(`
             <div class="alert alert-primary d-flex p-2">
                  <i class="mdi mdi-alert-decagram-outline" style="font-size:30px"></i>
                  <div class="pl-2">Edit ${data.name} skills matrixs information below. Kindly note the changes made may affect the capability matrix configuration as well as training plan and needs generated by the system.</div>
               </div>
               <div class="form-group">
                  <label class="control-label">Matrix Name <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" name="name"  value="${data.name}" placeholder="Name..." required />
               </div> 

               <div class="form-group">
                  <label for="" class="control-label">Department</label>
                  <select name="department_id" id="" class="form-control department_id">
                     <option value="">Select Department</option>
                     @foreach ($departments as $department)
                        <option value="{{$department->id}}">{{$department->name}}</option>
                     @endforeach
                  </select>
               </div>

               <div class="form-group">
                  <label class="control-label">Matrix Roles <span class="text-danger">*</span></label>
                  <select class="form-control role_id" name="matrix_role_ids[]" data-placeholder multiple required>                
                     <option value="">Select Role</option>
                     @foreach ($roles_info as $c)
                     <option value="{{ $c->id }}">{{ $c->description }}</option>
                     @endforeach
                  </select>
               </div>
               <div class="form-group">                            
                  <label class="control-label"><input type="checkbox" name="status" value="1" ${data.status == 1 ? 'checked' : '' }/> Active</label>
               </div>
               <input type="hidden" name="matrix_id" value="${data.id}" />
            `).clone();
            $(body).find('.department_id').val(data.department_id);
            $(body).find('.role_id').val(data.jobdescription['ids']);
            $(body).find('.department_id').select2();
            $(body).find('.role_id').select2();
            return body;
         }
         $('#edit-matrix').on('show.bs.modal',(e)=>{
            $('#edit-matrix').find('.modal-body').empty();
            var data = $(e.relatedTarget).data('record');
            var body =editMatrixBody(data);
            $('#edit-matrix').find('.modal-body').append(body);
         });
    });

	
</script>
@endsection

