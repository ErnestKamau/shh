@extends($module == "Skills-Matrix" &&  $config=='Roles' ? 'layouts.personnel.layout.app':'layouts.skillsmatrix.layout.app' , ['dataTable'=>true, 'select2'=>true])
@section('title2')
<title>{{ $training_info->name }} | {{ $training_info->name }}</title>
@endsection
@section('content2')
<style type="text/css">
	.topology-row{
		clear: both !important;
		font-size: 12px;
		cursor: pointer;
		padding-left:5px;
	}

	.topology-row.add-new .topology-text{
		border: none !important;
	}

	.topology-row .topology-text{
		clear: both !important;
		font-weight: 300;
		color: #121212;
		border-bottom: 1px dashed #eee;
	}

	.topology-row .topology-text .text{
		padding: 5px 10px;
		margin-top: 3px;
		border: 1px solid #d5d5d5;
		background-color: #fdfdfd;
		box-shadow: 0px 0px 7px #ededed;
		border-radius: 4px;
	}

	.topology-row .topology-text .left-icon{
		padding: 7px 8px 5px;
		margin-right: 5px;
		border-right: 1px solid #d5d5d5;
		float: left;
	}

	.topology-row .topology-text .right-icon{
		padding: 7px 8px 5px;
		float: right;
		border-left: 1px solid #dadada;
	}

	.topology-row .topology-text .left-icon:hover, .topology-row .topology-text .right-icon:hover{
		background-color: rgba(0,0,0,0.1);
	}

	.topology-row .topology-body{
		display: none;
	}

	.topology-row .topology-body.visible{
		display: unset !important;
		clear: both !important;
	}

	.topology-body .topology-row{
		margin-left: 22px;
	}

	.position-table .table-bordered td,.position-table .table-bordered th {
		/*border: 1px solid #000000*/
	}
	.position-table .table-bordered td {
		font-size: 13px
	}
	.position-table thead tr th {
	    text-align: center;
		font-size: 15px;
	}
	.position-table tbody tr td.laboratory span {
	    font-size: 25px;
		transform: rotate(-90deg);
		font-weight: bold;
        display: inline-block
	}
	.lead-value {
    display: inline-block;
    width: 100%;
    text-align: center;
    float: left;
    height: 16px;
    
	}
	.lead-value-skills_proficiency{	
	display: inline-block;
    width: 50%;
    text-align: center;
    float: left;
    height: 16px;
    
	}
	.lead-proficiency {
		display: inline-block;
		width: 50%;
		text-align: center;
		float: left;
		height: 20px;
		padding: 3px 0;
		min-width: 55px;
	}
	.CircelcolorDisplay {
		border: 1px solid #000;
		border-radius: 50%;
		width: 16px;
		height: 16px;
		display: inline-block;
   }
   .SmallColorDisplay {
	margin-bottom: 0px !important;
    border-radius: 50%;
    width: 16px;
    height: 16px;    
    display: inline-block;
    cursor: pointer;
   }
   .RoleCircelcolorDisplay {
		border: 1px solid #000;
		border-radius: 50%;
		width: 16px;
		height: 16px;
		display: inline-block;
   }
   .RoleSmallColorDisplay {
	border-radius: 50%;
    width: 16px;
    height: 16px;   
    display: inline-block;
    cursor: pointer;
   }
   .role_position{
	font-weight: normal;
    font-size: 12px;
    font-style: italic;
   }
   canvas {
	  background-color: #eee;
	}
	

	ul.listing{
		padding:0px;
		margin:0px
	}
	.listing li{
		list-style:none;
		float:left;
		display:inline-block;
	}
	.listing li:first-child{
		width:31px
	}
	.role-lead-proficiency,.lead-proficiency{
		position: relative;
	}
	.role-lead-proficiency_cls,.lead-proficiency-cls{
		position: absolute;
    right: 0px;
    top: 2px;
    
	}
	.history .modal-dialog{
		max-width: 900px;
	}

	.chart_info{
		margin-bottom: 10px !important;
	}
	.chart_info li:first-child{
		width: auto
	}
	.chart_info li{
		float: none;
		display: block;
		position: relative;
		padding: 2px 0px 2px 20px;
	}
	.chart_info .rounds{
		content: '';
		display: block;
		height: 12px;
		width:12px;
		border-radius: 50%;
		background: #000;
		position: absolute;
		left: 0px;
		top:5px
	}

	.pt_small{
		padding-top: 15px !important
	}
	.p_0{
		padding:0px !important
	}
	.inline_cls{
		display: flex;
		align-items: center;
	}
	.padding_box{
		padding:0px 16px;
	}
	.positions_set{
		top:0px !important;
	}
	.table tbody tr td:last-child .role_proficiency{
		padding:0px 5px !important;
	}
</style>
<main>
	<?php
	$items = array(
        array(
          'link' => route('matrix'),
          'name' => $module,
          'icon' => null
        ),
        array(
          'link' => null,
          'name' => $training_info->name,
          'icon' => null
        )
      );
	?>
	<x-bread-crumb :items="$items"></x-bread-crumb>
   <h2 class="p-4">
      <i class="mdi mdi-microscope"></i> {{$training_info->name}} <small class="text-muted">  | Configuration</small>
   </h2>
   <div class="p-4">
      <div class="card tab-card">
         <div class="card-header tab-card-header">
            <ul class="nav nav-tabs card-header-tabs" id="matrix-types-tabs" role="tablist">
				@if(count($users) > 0)
				<li class="nav-item">
					<a class="nav-link" id="matrix-role-capability-tab" data-toggle="tab" href="#matrix-role-capability-tab-content" role="tab" aria-controls="Capability-Analysis" aria-selected="false">{{$training_info->training_plan_name}} </a>
				</li>
			   @endif  	
               <li class="nav-item">
                  <a class="nav-link" id="matrix-config-tab" data-toggle="tab" href="#matrix-config-tab-content" role="tab" aria-controls="Matrix-Analysis" aria-selected="true">Training Configuration</a>
               </li>
               <li class="nav-item">
                  <a class="nav-link active" id="matrix-role-tab" data-toggle="tab" href="#matrix-role-tab-content" role="tab" aria-controls="Matrix Roles-Analysis" aria-selected="false">Training Indicator</a>
               </li>
			  
            </ul>
         </div>
         <div class="tab-content" id="matrix-types-tabs">			
			@if(count($users) > 0)
			<div class="tab-pane fade p-3" id="matrix-role-capability-tab-content" role="tabpanel" aria-labelledby="one-tab">              
	  			<!--table-responsive -->   
				<?php
					$total_users = count($users);    
				?>  
				<div class="table-responsive bg-light p-5 position-table pt_small">
						<form autocomplete="off" id="frm_competence_history_date" action="{{ route('training-config', ['module'=>$training_info->id]) }}" method="GET" enctype="multipart/form-data">
							@csrf
							<div class="row">
								<div class="col-sm-6">
									<div class="form-group">
										@if($competence_history_date =="")		
											<label class="control-label col text-left p_0">On current date <b><?php echo date("F j, Y",strtotime($current_dt));?></b> below are training plan configration..</label>
										@else  	
											<label class="control-label col text-left p_0">On date <b><?php echo date("F j, Y",strtotime($current_dt));?></b> below was the training plan configration..</label>
										@endif
										<input type="date" max="<?php echo date('Y-m-d');?>" class="form-control col text-right" id="competence_history_date" name="competence_history_date" value="{{$current_dt}}" placeholder="Date of Birth..." />
									</div>
								</div>
								<div class="col-lg-4 col-md-4 col-sm-12 offset-2">
										<ul class="listing chart_info">
										@if(count($proficiency) > 0)
											@foreach($proficiency as $proficiency_info)
											<li class="red" ><span class="rounds" style="background-color: {{ $proficiency_info->color ?? '' }};"></span><span>{{$proficiency_info->description}}</span></li>	
											@endforeach
										@endif  											
										</ul>
								</div>
							</div>
						</form>
						<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
						<thead>
							<tr>
								<th border="0"></th>
								<th border="0"></th>
								<th colspan="{{$total_users+2}}" class="text-center">{{$training_info->department}}</th>
							</tr>
							<tr>
								<th width="10%">Area</th>
								<th>Competence</th>
								@if(count($users) > 0)
									@foreach($users as $user)
									<th>{{ $user->name }}
										<div class="role_position">({{ $user->position_name }})<div>
									</th>
									@endforeach
								@endif  								
							</tr>
						</thead>
						<tbody>
						@if(count($users_role_topologies) > 0)
            			 @foreach($users_role_topologies as $user_role_topology)	
						     @if($user_role_topology->level == 1)								
								<tr>
									<td colspan="{{$total_users+2}}"><span><i><b>{{$user_role_topology->name}}</b></i></span></td>
								</tr>
							  @endif  
							  @if($user_role_topology->level == 2)								
							  <tr>
							    <td></td> 
								<td colspan="{{$total_users+1}}"><b>{{$user_role_topology->name}}</b></td>
							  </tr>
							  @endif  
							  @if($user_role_topology->level == 3)								
							  	<tr>
									<td></td>
								 	<td>{{$user_role_topology->name}}</td>									 									
									   @if(count($users) > 0)
										@foreach($users as $user)     
										<?php
									 		$roles_response = getUserTrainingRolesValues($user->id,$user->position,$user_role_topology->id,$training_info->id,$competence_history_date);
										    $roles_info = $roles_response['role_values'];
											$role_total_cnt = $roles_response['role_total_cnt'];
										 ?>                    
										<td class="text-center" id="{{ $user->id }}">                           
											<div  class="role_proficiency padding_box" id="{{$user_role_topology->id }}_{{ $user->id }}"  data-roleinfo="{{$user->position_name }}__{{ $user->position }}">
												@if($can_edit_skills_matrix == 1)	
												<div class="lead-value">
												@else
												<div class="lead-value" style="width: 100%;">
												@endif 
												    <ul class="listing">
														<li>
														<div class="RoleCircelcolorDisplay" id="user_role_proficiency_update_{{$user_role_topology->id }}_{{ $user->id }}" style="background-color: {{ $roles_info->color ?? '' }};"></div>
														</li>
														@if($role_total_cnt > 1)
														<li>
														<i class="fa fa-history" aria-hidden="true" data-target="#modal-competence-history" data-training_id="{{$training_info->id}}" data-history_competence_description="{{$user_role_topology->name}}" data-history_user_name="{{$user->name}}" data-user_role_topology="{{$user_role_topology->id}}" data-user_id="{{$user->id}}" data-toggle="modal"></i>
														</li>
													@endif 
													</ul>                              
												</div>
												@if($can_edit_skills_matrix == 1 && $competence_history_date =="")		
												<div class="role-lead-proficiency">
													<div style="display:none" class="role-lead-proficiency_cls positions_set" id="role-lead-proficiency_{{$user_role_topology->id }}_{{ $user->id }}">
														@foreach($proficiency as $proficiency_info)
															<div class="RoleSmallColorDisplay"  data-role_auto_id="{{$user_role_topology->id}}"  data-training_matrix_id="{{$training_info->id}}"  data-user_id="{{$user->id}}"    data-color="{{$proficiency_info->color}}" data-color_code="{{$proficiency_info->id}}" title="Click to {{$proficiency_info->description}}"  style="background-color: {{ $proficiency_info->color ?? '' }};"></div>
														@endforeach
													</div>
												</div>
												@endif  	
											</div>
										</td>
										@endforeach
									@endif  									
								</tr>	
							  @endif  	
							@endforeach
          				  @endif  						
						</tbody>
					</table>
				</div>
				<!--table-responsive -->                 
            </div>
			@endif  

			<div class="tab-pane fade p-3" id="matrix-config-tab-content" role="tabpanel" aria-labelledby="one-tab">
				<div class="table-responsive">
				@if($can_edit_skills_matrix)
					<div class="pb-2">
						<small class="btn btn-flat btn-danger btn-sm" data-target="#modal-add-topology" data-toggle="modal">
							<i class="fas fa-plus"></i> Add Top Level Area Competence
						</small>
					</div>
					<br>
				@endif  	
					<div id="topology-holder" style="mt-2"></div>
				</div>               
            </div>
			
            <div class="tab-pane fade show active p-3" id="matrix-role-tab-content" role="tabpanel" aria-labelledby="one-tab">              
	  			<!--table-responsive -->   
				<?php
					$total_roles = count($roles);     
				?>  				
				<div class="table-responsive bg-light p-5 position-table pt_small">	
					<div class="row">
						<div class="col-lg-8 col-md-8 col-sm-12">
							<div class="alert alert-success alert-dismissible" id="update_successfully" style="display:none;">
							<i class="fa fa-check-circle"></i> <span id="success_msg"></span><button type="button" class="close" data-dismiss="alert">×</button>
							</div>
						</div>						
						<div class="col-lg-4 col-md-4 col-sm-12">
								<ul class="listing chart_info">
									@if(count($proficiency) > 0)
										@foreach($proficiency as $proficiency_info)
										<li class="red" ><span class="rounds" style="background-color: {{ $proficiency_info->color ?? '' }};"></span><span>{{$proficiency_info->description}}</span></li>	
										@endforeach
									@endif  	
								</ul>
						</div>
					</div>				
					<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
						<thead>
							<tr>
								<th border="0"></th>
								<th border="0"></th>
								<th colspan="{{$total_roles+3}}" class="text-center">{{$training_info->department}}</th>
								
							</tr>
							<tr>
								<th width="8%">Area</th>
								<th style="width: 22%;">Competence</th>
								@if(count($roles) > 0)
									@foreach($roles as $role)
									<th>{{ $role->description }}</th>
									@endforeach
								@endif  	
								<th style="width: 10%;">Training week-{{$training_info->training_year}}</th>	
								<th style="width: 30%;">Organization/Trainer</th>
								<th class="text-left" style="width: 10%;">Status</th>							
							</tr>
						</thead>
						<tbody>
						@if(count($role_topologies) > 0)
            			 @foreach($role_topologies as $role_topology)	
						     @if($role_topology->level == 1)								
								<tr>
									<td colspan="{{$total_roles+5}}"><span><i><b>{{$role_topology->name}}</b></i></span></td>
								</tr>
							  @endif  
							  @if($role_topology->level == 2)								
							  <tr>
							    <td></td> 
								<td colspan="{{$total_roles+1}}"><b>{{$role_topology->name}}</b></td>
								<td></td>
								<td></td>	
								<td></td>								
							  </tr>
							  @endif  
							  @if($role_topology->level == 3)								
							  	<tr>
									<td></td>
								 	<td id="ajax_competence_td_{{$role_topology->id}}">{{$role_topology->name}}</td>
									 <?php
									 	$roles_info = getTrainingRolesValues($role_topology->id,$role_topology->skills_training_id);								
									 ?>
									   @if(count($roles_info) > 0)
										@foreach($roles_info as $role)                         
										<td class="text-center" id="{{ $role->role_id }}">                                 
											<div  class="proficiency inline_cls" id="{{$role->role_auto_id }}_{{ $role->role_id }}">
												@if($can_edit_skills_matrix == 1)	
												<div class="lead-value-skills_proficiency">
												@else
												<div class="lead-value-skills_proficiency" style="width: 100%;">
												@endif 
													<div class="CircelcolorDisplay" id="proficiency_update_{{$role->role_auto_id }}_{{ $role->role_id }}" style="background-color: {{ $role->color ?? '' }};"></div>
												</div>
												@if($can_edit_skills_matrix == 1)				
												<div class="lead-proficiency">
													<div style="display:none" class="lead-proficiency-cls" id="lead-proficiency_{{$role->role_auto_id }}_{{ $role->role_id }}">
														@foreach($proficiency as $proficiency_info)
															<div class="SmallColorDisplay"  data-role_auto_id="{{$role->role_auto_id}}"  data-color_id="{{$proficiency_info->id}}" data-role_id="{{$role->role_id}}" data-color="{{$proficiency_info->color}}" title="Click to {{$proficiency_info->description}}"  style="background-color: {{ $proficiency_info->color ?? '' }};"></div>
														@endforeach
													</div>
												</div>
												@endif 
											</div>
										</td>
										@endforeach
									@endif 
									<?php
										$week_text = "";
										$status = "";
									    if(!empty($role_topology->week_number)){											
											$week_info_status = getStartAndEndDate($role_topology->week_number,$training_info->training_year);											
											$week_text =  $week_info_status['dates'][0]." to ".$week_info_status['dates'][1]; 
											$status = $week_info_status['status'];
										}

										$training_type_mode = "";
										$trainer_name = "";
									    if(!empty($role_topology->trainer_id)){	
											$training_type_mode =  "Type - ".$role_topology->training_type." / Mode - ".$role_topology->training_mode; 
											$trainer_name = getTrainerName($role_topology->training_type,$role_topology->trainer_id);	
										}									 																								
									 ?>
									 @if($can_edit_skills_matrix == 1)	
									<td class="text-center" style="padding:2px 20px !important;"
										id="ajax_week_td_{{$role_topology->id}}" title="{{$week_text}}" original="{{$role_topology->week_number}}">
										<span id="ajax_week_select_{{$role_topology->id}}" style="cursor:pointer;" data-skills_training_auto_id="{{$role_topology->id}}"  data-skills_training_id="{{$role_topology->skills_training_id}}" 
										 data-week_number="{{$role_topology->week_number}}"
										class="showWeekEdit"> {{$role_topology->week_number ?: 'Add Week'}}</span>
										<span id="ajax_week_span_{{$role_topology->id}}"></span>
									</td>									
									<td class="text-center" style="padding:2px 20px !important;"
										id="ajax_trainer_td_{{$role_topology->id}}" title="{{$training_type_mode}}" data-original="{{$role_topology->id}}__{{$role_topology->training_type}}__{{$role_topology->training_mode}}__{{$role_topology->trainer_id}}">
										<span id="ajax_trainer_select_{{$role_topology->id}}" data-target="#modal-assign-trainer" data-toggle="modal" data-skills_training_auto_id="{{$role_topology->id}}"   style="cursor:pointer;"  
										 class="showTrainerEdit"> {{$trainer_name ?: 'Add Trainer'}}</span>
										<span id="ajax_trainer_span_{{$role_topology->id}}"></span>
									</td>	
									@else
									  <td class="text-center" style="padding:2px 20px !important;">{{$role_topology->week_number ?: ''}}</td>
									  <td class="text-center" style="padding:2px 20px !important;">{{$trainer_name ?: ''}}</td>									  
									@endif  			
									<td class="text-left" style="padding:0px 2px !important;" id="ajax_status_td_{{$role_topology->id}}">										
										<span id="ajax_status_span_{{$role_topology->id}}">{{$status}}</span>
									</td>					
								</tr>	
							  @endif  	
							@endforeach
          				  @endif  						
						</tbody>
					</table>
				</div>
				<!--table-responsive -->                 
            </div>				
         </div>
      </div>
   </div>
</main>
@endsection
@section('script2')
	<div id="modal-assign-trainer" class="modal fade trainer" role="dialog">
		<div class="modal-dialog">
			<!-- Modal content-->
			<div class="modal-content">
				@csrf
				<div class="modal-header">
					<h4 class="modal-title"><i class="mdi mdi-pencil-outline"></i> Add/Edit Trainer / Organization</h4>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<label class="control-label">Select Training Type<span class="text-danger">*</span></label>
						<select required class="form-control" name="training_type" id="training_type" data-placeholder>
						
						</select>
					</div>
					<div class="form-group">
						<label class="control-label">Select Training Mode<span class="text-danger">*</span></label>
						<select required class="form-control" name="training_mode" id="training_mode" data-placeholder>
						
						</select>
					</div>
					<div class="form-group">
						<label class="control-label">Select Organization/Trainer<span class="text-danger">*</span></label>
						<select required class="form-control" name="trainer_id" id="trainer_id" data-placeholder>					
						</select>
					</div>
					<span id="users_html" style="display:none"></span>
					<span id="suppliers_html" style="display:none"></span>
					<input type="hidden" id="skills_training_auto_id" name="skills_training_auto_id" value="">
					
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-primary" id="save_trainer"><i class="mdi mdi-content-save"></i> Save</button>
					<button type="button" class="btn btn-default" id="cancel_save_trainer" data-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>
	<div id="modal-competence-history" class="modal fade history" role="dialog">
			<div class="modal-dialog">
				<!-- Modal content-->
				<form class="modal-content" method="POST" action="" enctype="multipart/form-data">
					@csrf
					<div class="modal-header">
						<h4 class="modal-title"><i class="mdi mdi-plus"></i> Competence History</h4>					
					</div>
					<div class="modal-body">
						<canvas id="chartJSContainer" style="height: 200px; width: 50%;"></canvas> 			
					</div>				
					<div class="modal-footer">					
						<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
					</div>
				</form>
			</div>
	</div>  
	<div id="modal-add-competence-type" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<!-- Modal content-->
			<form class="modal-content" method="POST" enctype="multipart/form-data">
				@csrf
				<div class="modal-header">
					<h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Competence Type</h4>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<label for="name">Select Competence Type</label>
						<!--<input type="text" class="form-control" id="name" name="name" placeholder="Name...">-->
						<select class="form-control" id="competence_type_id" name="competence_type_id" required>
						<option value="">Select Competence Type...</option>
						@foreach ($competence_types as $type)
						<option value="{{ $type->id }}">{{ $type->description }}</option>
						@endforeach
						</select>
					</div>
				</div>
				<input type="hidden" id="competence_area_id" name="competence_area_id" value="">
				<input type="hidden" id="competence_description_id" name="competence_description_id" value="">
				<div class="modal-footer">
					<button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				</div>
			</form>
		</div>
	</div>
	<div id="modal-add-competence-description" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<!-- Modal content-->
			<form class="modal-content" method="POST" enctype="multipart/form-data">
				@csrf
				<div class="modal-header">
					<h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Competence Description</h4>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<label for="name">Select Competence Description</label>
						<!--<input type="text" class="form-control" id="name" name="name" placeholder="Name...">-->
						<select class="form-control" id="competence_description_id" name="competence_description_id" required>
						<option value="">Select Competence Description...</option>
						@foreach ($competence_description as $description_info)
						<option value="{{ $description_info->id }}">{{ $description_info->description }}</option>
						@endforeach
						</select>
					</div>
				</div>
				<input type="hidden" id="competence_desc_type_id" name="competence_type_id" value="">
				<input type="hidden" id="competence_desc_area_id" name="competence_area_id" value="">
				<div class="modal-footer">
					<button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				</div>
			</form>
		</div>
	</div>
	<div id="modal-add-topology" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<!-- Modal content-->
			<form class="modal-content" method="POST" action="" enctype="multipart/form-data">
				@csrf
				<div class="modal-header">
					<h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Area Competence</h4>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<label for="name">Select Competence Area</label>
						<!--<input type="text" class="form-control" id="name" name="name" placeholder="Name...">-->
						<select class="form-control" id="competence_area_id" name="competence_area_id" required>
						<option value="">Select Competence Area...</option>
						@foreach ($competence_areas as $area)
						<option value="{{ $area->id }}">{{ $area->description }}</option>
						@endforeach
						</select>
					</div>
				</div>
				<input type="hidden" id="competence_type_id" name="competence_type_id" value="">
				<input type="hidden" id="competence_description_id" name="competence_description_id" value="">
				<div class="modal-footer">
					<button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				</div>
			</form>
		</div>
	</div>	
	<div id="modal-remove-topology" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<!-- Modal content-->
			<form class="modal-content" method="POST" enctype="multipart/form-data">
				@csrf
				<div class="modal-header">
					<h4 class="modal-title"><i class="mdi mdi-delete"></i> Remove Topology Item</h4>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<div class="alert alert-danger alert-callout">
							<i class="fas fa-exclamation-triangle fa-1x"></i> Are you sure that you want to remove this Training Configuration?
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-primary"><i class="mdi mdi-delete"></i> Remove</button>
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				</div>
			</form>
		</div>
	</div>	
	
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js"  crossorigin="anonymous"></script>

	<script type="text/javascript">

		var getTopologyRow = function(data){
			var $row = $(`
				<div class="topology-row" data-name="${data.name}" data-competence_area_id="${data.competence_area_id}" data-competence_type_id="${data.competence_type_id}" data-id="${data.id}" data-parent="${data.parent}" data-level="${data.level}"></div>
			`);
			var $text = $(`
				<div class="topology-text"></div>
			`);
			var can_edit_skills_matrix = '{{$can_edit_skills_matrix}}';		

			$text.append(`<div class="left-icon text-muted dropdown-toggler"><i class="fas fa-plus"></i> </div> `);

			$text.find('.dropdown-toggler').on('click', function(){
				var parnt = $(this).parents('.topology-row').first();
				var bdy = parnt.find('.topology-body');

				bdy.toggleClass('visible');

				if(bdy.is(':visible')){
					$(this).find('i').removeClass('fa-plus').addClass('fa-minus');				
					fetchTopology(data.id, bdy,data.level);
				}
				else{
					$(this).find('i').removeClass('fa-minus').addClass('fa-plus');
				}
			});
			if(can_edit_skills_matrix=='1'){
				$text.append(`<div class="right-icon delete-topology" data-target="#modal-remove-topology" data-toggle="modal"><i class="fas fa-trash text-danger"></i></div>`);
			}
			$text.append(`<div class="text">${data.name}</div>`);
			$row.append($text);
			$row.append(`<div class="topology-body"><span class="md md-plus"></span> Add Topology Item</div>`);
			return $row;
		}

		var fetchTopology = function(parentID, parent,level=0){

			var can_edit_skills_matrix = '{{$can_edit_skills_matrix}}';			
			$.ajax({
				url: "/training-config-topology/"+parentID+"/{{$training_info->id}}",
				dataType: "json",
				beforeSend: function(){
					parent.html('<span class="text-center"><i class="fas fa-spin fa-spinner"></i> Fetching Topology...</span>');
				},
				success: function(js){
					parent.empty();
					$.each(js, function(j,s){
						var $row = getTopologyRow(s);
						parent.append($row);
					});
																		
					if(level == 1 &&  can_edit_skills_matrix=='1'){
						parent.append(`<div style="padding: 7px 0px" class="topology-row text-info" data-target="#modal-add-competence-type" data-toggle="modal">
							<span class="md md-add" style="margin-left: 6px"></span> Add Competence Type
						</div>`);
					}

					if(level == 2 &&  can_edit_skills_matrix=='1'){
						parent.append(`<div style="padding: 7px 0px" class="topology-row text-info" data-target="#modal-add-competence-description" data-toggle="modal">
							<span class="md md-add" style="margin-left: 6px"></span> Add Competence Description
						</div>`);
					}
				}
			});
		}

   		$(function(){
			$("#save_trainer").click(function(){
				var training_type = $('#training_type').val();
				var training_mode = $('#training_mode').val();
				var trainer_id =$('#trainer_id').val();
				var skills_training_auto_id = $('#skills_training_auto_id').val();
				
				$.ajax({
					url: "{{ route('update-trainner')}}",
					dataType: 'json',
					data: {                   
						training_type: training_type,
						training_mode: training_mode,
						trainer_id: trainer_id,
						skills_training_auto_id: skills_training_auto_id												
					},
					type: "POST",
					success: function(response){
						$("#cancel_save_trainer").trigger( "click" );
						var element = $("#ajax_trainer_td_"+skills_training_auto_id);
            			var original_trainer = element.attr("data-original");
						var new_trainer_name = response.new_trainer_name;							
						$("#ajax_trainer_select_"+skills_training_auto_id).prop('title', response.training_type_mode);			
						$("#ajax_trainer_select_"+skills_training_auto_id).text(response.trainer_name);	
						var bgColor = $("#ajax_competence_td_"+skills_training_auto_id).css("background-color");
						if(original_trainer==new_trainer_name){
							$("#ajax_trainer_td_"+skills_training_auto_id).css("background-color",bgColor);	
						}else{
							$("#ajax_trainer_td_"+skills_training_auto_id).css("background-color", "#f5c1bb");	
						}
						$("#success_msg").text("Success: You have updated trainer successfully.");	
						$("#update_successfully").show().delay(3000).fadeOut();								
					}
				})
				return false;				
			})		

			$('#training_type').on('change', function() {
				if(this.value=='External'){				
					$('#trainer_id').html($('#suppliers_html').html());
				}else{
					$('#trainer_id').html($('#users_html').html());
				}
			});

			$(document).on('click', '[data-target="#modal-assign-trainer"]', function(){					
				var curRow = $(this).first();			
				var skills_training_auto_id = curRow.data('skills_training_auto_id');
				$.ajax({
					url: "{{ route('assign-trainner')}}",
					dataType: 'json',
					data: {                   
						skills_training_auto_id: skills_training_auto_id										
					},
					type: "POST",
					success: function(response){
						$('#training_type').html(response.training_type_option_html);
						$('#training_mode').html(response.training_mode_option_html);
						$('#trainer_id').html(response.trainers_html);
						$('#users_html').html(response.users_html);
						$('#suppliers_html').html(response.supplier_html);
						$('#skills_training_auto_id').val(skills_training_auto_id);
					}
				}) 
			});
			
			$(document).on('click', '[data-target="#modal-add-topology"]', function(){							
				var parentRow = $(this).parents('.topology-row').first();
				var parentId = parentRow.data('id');
				var level = parentRow.data('level');
				parentId = parentId || 0;
				level = level || 0;				
				$(this).find('.card-head').find('.modal-title').html('<i class="mdi mdi-plus"></i> '+parentId == 0 ? 'Creating Top Level Topology' : 'Creating Level '+level+' Topology');
				$('.modal-content').attr('action', '/training-config-topology/'+parentId+'/{{$training_info->id}}');				
			});

			$(document).on('click', '[data-target="#modal-add-competence-description"]', function(e){
				 
				var parentRow =$(this).parents('.topology-row').first();						
				var parentId = parentRow.data('id');
				var level = parentRow.data('level');			
				parentId = parentId || 0;
				level = level || 0;
				
				var competence_area = parentRow.data('competence_area_id');
				var competence_type = parentRow.data('competence_type_id');
							
				$('#competence_desc_area_id').val(competence_area);
				$('#competence_desc_type_id').val(competence_type);
			
				$(this).find('.card-head').find('.modal-title').html('<i class="mdi mdi-plus"></i> '+parentId == 0 ? 'Creating Top Level Topology' : 'Creating Level '+level+' Topology');

				$('.modal-content').attr('action', '/training-config-topology/'+parentId+'/{{$training_info->id}}');				
			});

			//$(document).on('show.bs.modal', '[data-target="#modal-add-competence-type"]', function(e){
			$(document).on('click', '[data-target="#modal-add-competence-type"]', function(){				
				var parentRow = $(this).parents('.topology-row').first();						
				var parentId = parentRow.data('id');
				var level = parentRow.data('level');
				var competence_area = parentRow.data('competence_area_id');
				parentId = parentId || 0;
				level = level || 0;

				$('#competence_area_id').val(competence_area);

				$(this).find('.card-head').find('.modal-title').html('<i class="mdi mdi-plus"></i> '+parentId == 0 ? 'Creating Top Level Topology' : 'Creating Level '+level+' Topology');

				$('.modal-content').attr('action', '/training-config-topology/'+parentId+'/{{$training_info->id}}');					
			});

			$(document).on('click', '[data-target="#modal-remove-topology"]', function(){			
				var parentRow = $(this).parents('.topology-row').first();
				var mainId = parentRow.data('id');
				var level = parentRow.data('level');
				var name = parentRow.data('name');
				mainId = mainId || 0;
				level = level || 0;

				$(this).find('.card-head').find('.modal-title').html('<i class="mdi mdi-delete"></i> Remove Topology : '+name);				
				$('.modal-content').attr('action', '/training-config-topology/'+mainId+'/{{$training_info->id}}/remove');					
			});

			fetchTopology(0, $('#topology-holder'));
    	});

		function show_original_week(skills_training_auto_id,orginal_val) {
			$("#ajax_week_select_"+skills_training_auto_id).html(orginal_val);
			$("#ajax_week_span_"+skills_training_auto_id).html('');
			$("#ajax_week_select_"+skills_training_auto_id).show();
			$("#cancel_show_week__"+skills_training_auto_id).remove();
			$("#save_analyte__"+skills_training_auto_id).remove();	
		}

		function save_new_week(skills_training_auto_id,old_week_number) {				
			var new_week_id = document.getElementById(skills_training_auto_id).value;	
			if(new_week_id>0){
				$("#ajax_week_td_"+skills_training_auto_id).css("background", "#FFF url(https://dev.ieqas.ie/admin/view/image/loaderIcon.gif) no-repeat center center");
				$.ajax({
					url: "{{ route('save-new-week') }}",
					dataType: 'json',
					data: {                   
						new_week_id: new_week_id,
						skills_training_auto_id:skills_training_auto_id									
					},
					type: "POST",
					success: function(response){
						$("#ajax_week_select_"+skills_training_auto_id).html(new_week_id);
						$("#ajax_status_span_"+skills_training_auto_id).html(response.status);	

						$("#ajax_week_span_"+skills_training_auto_id).html('');
						$("#ajax_week_select_"+skills_training_auto_id).show();
						$("#cancel_show_week__"+skills_training_auto_id).remove();
						$("#save_analyte__"+skills_training_auto_id).remove();							
						$("#ajax_week_select_"+skills_training_auto_id).data('week_number', new_week_id);
						$("#ajax_week_select_"+skills_training_auto_id).attr('title',response.title); 
						$("#ajax_week_td_"+skills_training_auto_id).css("background", "none");	
						var bgColor = $("#ajax_competence_td_"+skills_training_auto_id).css("background-color");
						if(old_week_number==new_week_id){
							$("#ajax_week_td_"+skills_training_auto_id).css("background-color",bgColor);	
							$("#ajax_status_td_"+skills_training_auto_id).css("background-color",bgColor);	
						}else{
							$("#ajax_week_td_"+skills_training_auto_id).css("background-color", "#f5c1bb");
							$("#ajax_status_td_"+skills_training_auto_id).css("background-color", "#f5c1bb");	
						}
						$("#success_msg").text("Success: You have updated training week plan Successfully.");	
						$("#update_successfully").show().delay(3000).fadeOut();						
					}
				}) 
			}else{
				show_original_week(skills_training_auto_id,old_week_number);
			}					
		}
	</script>	
	<script>
		$(document).ready(function(){			

			$('#competence_history_date').change(function() { 
				$( "#frm_competence_history_date" ).submit();
			});
					
			$('.proficiency').mouseover(function() {
				$('#lead-proficiency_'+this.id).show();
			});

			$('.proficiency').mouseleave(function() {
				$('#lead-proficiency_'+this.id).hide();
			});     

			$('.role_proficiency').mouseover(function() {
				$('#role-lead-proficiency_'+this.id).show();
			});

			$('.role_proficiency').mouseleave(function() {
				$('#role-lead-proficiency_'+this.id).hide();
			});    

			$.ajaxSetup({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				}
			});	
		
			$(document).on('click', '[data-target="#modal-competence-history"]', function(){		
				var training_id = $(this).data('training_id');
				var user_role_topology = $(this).data('user_role_topology');
				var user_id = $(this).data('user_id');
				var history_competence_description = $(this).data('history_competence_description');
				var history_user_name = $(this).data('history_user_name');
				
				$.ajax({
					url: "{{route('training-competence')}}",
					dataType: 'json',
					data: {                   
						skills_training_id: training_id,
						user_role_topology: user_role_topology,
						user_id: user_id												
					},
					type: "GET",
					success: function(response){
						var options = {
						type: 'line',
						data: {
							xLabels: response.competence_update_time,			
							yLabels: response.arr_proficiency,
							datasets: [{
							label: 'Competence Description :-'+history_competence_description+' User Name:-'+history_user_name,			
							data:  response.competence_role_name,
							backgroundColor : response.role_colors,
							borderColor :  response.role_colors,
							borderWidth: 1,
							lineTension: 0,
							fill: false,
							}]
						},
						options: {
							scales: {
							yAxes: [{
								type: 'category',
								ticks: {
								reverse: true
								},				
							}],
							xAxes: [{
								ticks: {
									autoSkip: false,
									maxRotation: 20,
									minRotation: 20
								}
							}]
							}
						}
						}				
					var ctx = document.getElementById('chartJSContainer').getContext('2d');
					new Chart(ctx, options);	
									
					}
				})  
						
				
			});					

			$(".showWeekEdit").click(function(){
				var skills_training_auto_id = $(this).data('skills_training_auto_id');				
				var week_number = $(this).data('week_number');											
				$.ajax({
					url: "{{ route('get-week-listing') }}",
					dataType: 'json',
					data: {                   
						week_number: week_number,
						skills_training_auto_id:skills_training_auto_id									
					},
					type: "POST",
					success: function(response){
						$("#ajax_week_span_"+skills_training_auto_id).html(response.weeks_html);
						$("#ajax_week_select_"+skills_training_auto_id).hide();				
					}
				})  
			})		
		
			$(".SmallColorDisplay").click(function(){
				var role_auto_id = $(this).data('role_auto_id');
				var color = $(this).data('color');		
				var color_id = $(this).data('color_id');	
				var role_id = $(this).data('role_id');	
							
				$.ajax({
					url: "{{ route('update-training-Config') }}",
					dataType: 'json',
					data: {                   
						role_auto_id: role_auto_id,
						color_id: color_id,
						color: color,
						role_id: role_id												
					},
					type: "POST",
					success: function(response){
						$("#proficiency_update_"+response.description_id).css({"background-color":response.color});
						//alert("Proficiency Updated Successfully");
					}
				})  
			})

			$(".RoleSmallColorDisplay").click(function(){

				var parentRow = $(this).parents().parents().parents().first();	
				var role_info = parentRow.data('roleinfo');
				var role_info_txt = role_info.split('__');

				var role_auto_id = $(this).data('role_auto_id');
				var training_matrix_id = $(this).data('training_matrix_id');		
				var user_id = $(this).data('user_id');	
				var role_name = role_info_txt[0];	
				var color = $(this).data('color');
				var color_code = $(this).data('color_code');
				var role_id = role_info_txt[1];
					
							
				$.ajax({
					url: "{{ route('update-training-user-role-matrix-Config') }}",
					dataType: 'json',
					data: {                   
						role_auto_id: role_auto_id,
						training_matrix_id: training_matrix_id,
						user_id: user_id,
						role_name: role_name,
						role_id: role_id,
						color: color,
						color_code: color_code																	
					},
					type: "POST",
					success: function(response){
						$("#user_role_proficiency_update_"+response.description_id).css({"background-color":response.color});
						if(response.error_description!=''){
							alert(response.error_description);						
						}						
					}
				})  
			})
		});
	</script>
@endsection