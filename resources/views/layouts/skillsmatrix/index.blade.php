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
                        <th>Created At</th>
                        <th>Align Roles</th>
                     </tr>
                  </thead>
                  <tbody>
                     
                     @if(count($matrix_information['active']) > 0)
                     @foreach($matrix_information['active'] as $matrix_info)
                     <?php
                        $roles = explode(',',$matrix_info->matrix_role_ids);
                        $roles_information = getMatrixRoles($roles);
                        
                        ?>
                     <tr data-element="{{ $matrix_info->id }}">
                        <td valign="center">{{ $loop->iteration }}</td>
                        <td>{{ $matrix_info->name }}</td>
                        <td>{{ $matrix_info->department}}</td>
                        <td>{{ $matrix_info->training_year}}</td>
                        <td> 
                           @if($roles_information)  
                           @foreach($roles_information as $role)					
                           {{ $loop->iteration }}. {{$role->description}}<br/>				
                           @endforeach
                           @endif
                        </td>
                        <td class="text-small">{!! $matrix_info->status == '1' ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>' !!}</td>
                        <td>
                           @if($can_edit_skills_matrix)
                           <button class="btn btn-primary btn-sm" data-target="#edit-active-matrix-{{ $matrix_info->id }}" data-toggle="modal"><i class="mdi mdi-pencil-outline"></i> <small class="hidden-sm-up">Edit</small> </button>
                           @endif 
                           <a class="btn btn-outline-success btn-sm" href="{{ route('matrix-config', ['module'=>$matrix_info->id]) }}"  title="View"><i class="mdi mdi-eye-outline"></i> <small class="hidden-sm-up">Show</small> </a>
                           <div id="edit-active-matrix-{{ $matrix_info->id}}" class="modal fade" role="dialog">
                              <div class="modal-dialog">
                                 <!-- Modal content-->
                                 <form class="modal-content edit" method="POST" action="{{ route('edit-matrix', ['condition'=>$matrix_info->id]) }}" enctype="multipart/form-data">
                                    @csrf
                                    <div class="modal-header">
                                       <h4 class="modal-title"><i class="mdi mdi-pencil-outline"></i> Edit  Matrix - {{ $matrix_info->name }}</h4>
                                    </div>
                                    <div class="modal-body">
                                       <div class="form-group">
                                          <label class="control-label">Matrix Name<span class="text-danger">*</span></label>
                                          <input type="text" class="form-control" id="matrix_name" name="name"  value="{{$matrix_info->name}}" placeholder="Name..." required />
                                       </div>
                                       <div class="form-group">
                                          <label class="control-label">Department<span class="text-danger">*</span></label>
                                          <select class="form-control" name="department_id" data-placeholder required>
                                             <option value="">Select Department...</option>
                                             @foreach ($departments as $c)
                                             <option value="{{ $c->id }}" {{ $c->id == $matrix_info->department_id ? 'selected' : '' }}>{{ $c->name }}</option>
                                             @endforeach
                                          </select>
                                       </div>

                                       <div class="form-group">
                                          <label class="control-label">Select Training Year<span class="text-danger">*</span></label>
                                          <select class="form-control" name="training_year" data-placeholder required>
                                             @for ($year = (int)date('Y'); date('Y', strtotime('+5 year')) >= $year; $year++)
                                                @if ($year == $matrix_info->training_year)
                                                <option selected value="{{ $year }}">{{ $year }}</option>
                                                @else
                                                <option value="{{ $year }}">{{ $year }}</option>
                                                @endif
                                             @endfor
                                          </select>
                                       </div>

                                       <div class="form-group">
                                          <label class="control-label">Matrix Align Roles<span class="text-danger">*</span></label>
                                          <select class="form-control" id="selMulti" name="matrix_role_ids[]" data-placeholder multiple required>                                              
                                             @foreach ($roles_info as $c)
                                             <option value="{{ $c->id }}" {{ in_array($c->id, $roles) ? 'selected' : '' }} >{{ $c->description }}</option>
                                             @endforeach
                                          </select>
                                       </div>
                                       <div class="form-group">                            
                                          <label class="control-label"><input type="checkbox" name="status" value="1" {{ $matrix_info->status == 1 ? 'checked' : '' }} /> Active</label>
                                       </div>
                                    </div>
                                    <div class="modal-footer">                                          
                                       <button type="submit" data-roles="{{$matrix_info->matrix_role_ids}}"  class="btn btn-primary update_matrix"><i class="mdi mdi-content-save"></i> Save</button>
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
               <label class="control-label">Matrix Job Descriptions <span class="text-danger">*</span></label>
               <select class="form-control" name="matrix_role_ids[]" data-placeholder multiple required>                
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
            <button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
         </div>
      </form>
   </div>
</div>
<script type="text/javascript">
			
    $(document).ready(function(){
			
    });

	
</script>
@endsection

