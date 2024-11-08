@extends($module == "Skills-Matrix" &&  $config=='Roles' ? 'layouts.personnel.layout.app':'layouts.skillsmatrix.layout.app' , ['dataTable'=>true, 'select2'=>true])
@section('title2')
<title>{{ $matrix_info->name }} | {{ $matrix_info->name }}</title>
@endsection
@section('content2')
<link rel="stylesheet" href="/css/skills_matrix.css" />
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
          'name' => $matrix_info->name,
          'icon' => null
        )
      );
	?>
	<x-bread-crumb :items="$items"></x-bread-crumb>	
   <h2 class="p-4">
      <i class="mdi mdi-microscope"></i> {{$matrix_info->name}} <small class="text-muted">  | Configuration</small> 
	  <button title="Print" class="print-link no-print fr print_btn btn" onclick="jQuery('#ele1').print()"><i class="mdi mdi-printer-check"></i></button>
   </h2>
   <div class="p-4">
      <div class="card tab-card">
         <div class="card-header tab-card-header">
            <ul class="nav nav-tabs card-header-tabs" id="matrix-types-tabs" role="tablist">
				<li class="nav-item">
                  <a class="nav-link active" id="matrix-role-tab" data-toggle="tab" href="#matrix-role-tab-content" role="tab" aria-controls="Matrix Roles-Analysis" aria-selected="false">{{$matrix_info->name}}</a>
               </li>
				@if(count($users) > 0)
				<li class="nav-item">
					<a class="nav-link" id="matrix-role-capability-tab" data-toggle="tab" href="#matrix-role-capability-tab-content" role="tab" aria-controls="Capability-Analysis" aria-selected="false">Capability Matrix</a>
				</li>
			   @endif  	
			   <li class="nav-item">
                  <a class="nav-link" id="training-needs-matrix-config-tab" data-toggle="tab" href="#training-needs-matrix-config-tab-content" role="tab" aria-controls="Training-Needs" aria-selected="true">Training Needs</a>
               </li>  
			   <li class="nav-item">
                  <a class="nav-link" id="training-plan-matrix-config-tab" data-toggle="tab" href="#training-plan-matrix-config-tab-content" role="tab" aria-controls="Training-Needs" aria-selected="true">Training plan</a>
               </li>  			   
               <li class="nav-item">
                  <a class="nav-link" id="matrix-config-tab" data-toggle="tab" href="#matrix-config-tab-content" role="tab" aria-controls="Matrix-Analysis" aria-selected="true">Matrix Configuration</a>
               </li> 
			   
            </ul>
         </div>
         <div class="tab-content" id="matrix-types-tabs">
			
		 	<div class="tab-pane fade show active p-3" id="matrix-role-tab-content" role="tabpanel" aria-labelledby="one-tab">              
	  			<!--table-responsive -->   
				<?php
					$total_roles = count($roles);     
				?>  				
				<div class="table-responsive bg-light p-5 position-table pt_small">	
					<div class="row">
							<div class="col-lg-4 col-md-4 col-sm-12 offset-8">
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
								<th colspan="{{$total_roles+2}}" class="text-center">{{$matrix_info->department}}</th>
							</tr>
							<tr>
								<th width="10%">Area</th>
								<th>Competence</th>
								@if(count($roles) > 0)
									@foreach($roles as $role)
									<th>{{ $role->description }}</th>
									@endforeach
								@endif  								
							</tr>
						</thead>
						<tbody>
						@if(count($role_topologies) > 0)
            			 @foreach($role_topologies as $role_topology)	
						     @if($role_topology->level == 1)								
								<tr>
									<td colspan="{{$total_roles+2}}"><span><i><b>{{$role_topology->name}}</b></i></span></td>
								</tr>
							  @endif  
							  @if($role_topology->level == 2)								
							  <tr>
							    <td></td> 
								<td colspan="{{$total_roles+1}}"><b>{{$role_topology->name}}</b></td>
							  </tr>
							  @endif  
							  @if($role_topology->level == 3)								
							  	<tr>
									<td></td>
								 	<td>{{$role_topology->name}}</td>
									 <?php
									 	$roles_info = getMatrixRolesValues($role_topology->id,$role_topology->skills_matrix_id);
									 ?>
									   @if(count($roles_info) > 0)
										@foreach($roles_info as $role)                         
										<td class="text-center" id="{{ $role->role_id }}">                                 
											<div  class="proficiency" id="{{$role->role_auto_id }}_{{ $role->role_id }}">
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
															<div class="SmallColorDisplay"  data-role_auto_id="{{$role->role_auto_id}}"  data-color_id="{{$proficiency_info->id}}" data-role_id="{{$role->role_id}}" data-color="{{$proficiency_info->color}}" data-code="{{$proficiency_info->code}}" title="Click to {{$proficiency_info->description}}"  style="background-color: {{ $proficiency_info->color ?? '' }};"></div>
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
			
			@if(count($users) > 0)
			<div class="tab-pane fade p-3" id="matrix-role-capability-tab-content" role="tabpanel" aria-labelledby="one-tab">              
	  			<!--table-responsive Capability Matrix-->   
				<?php	
					$total_users = count($users);    
				?>  
				<div class="table-responsive bg-light p-5 position-table">
						<form autocomplete="off" id="frm_competence_history_date" action="{{ route('matrix-config', ['module'=>$matrix_info->id]) }}" method="GET" enctype="multipart/form-data">
							@csrf
							<div class="row">
								<div class="col-sm-6">
									<div class="form-group">
										@if($competence_history_date =="")		
											<label class="control-label col text-left">On current date <b><?php echo date("F j, Y",strtotime($current_dt));?></b> below are competence role configration..</label>
										@else  	
											<label class="control-label col text-left">On date <b><?php echo date("F j, Y",strtotime($current_dt));?></b> below was the competence role configration..</label>
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
								<th colspan="{{$total_users+2}}" class="text-center">{{$matrix_info->department}}</th>
							</tr>
							<tr>
								<th width="10%">Area</th>
								<th>Competence</th>
								@if(count($users) > 0)
									@foreach($users as $user)
									<th>{{ $user->position_name }}
										<div class="role_position">({{ $user->name }})<div>
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
											$roles_response = getUserRolesValues($user->id,$user->position,$user_role_topology->id,$matrix_info->id,$competence_history_date);
											$roles_info = $roles_response['role_values'];
											$role_total_cnt = $roles_response['role_total_cnt'];
										 ?>                    
										<td class="text-center" id="{{ $user->id }}">                           
											<div  class="role_proficiency" id="{{$user_role_topology->id }}_{{ $user->id }}"  data-roleinfo="{{$user->position_name }}__{{ $user->position }}">
												@if($can_edit_skills_matrix == 1)	
												<div class="lead-value">
												@else
												<div class="lead-value-user">
												@endif 
												    <ul class="listing">
														<li>
														<div class="RoleCircelcolorDisplay" id="user_role_proficiency_update_{{$user_role_topology->id }}_{{ $user->id }}" style="background-color: {{ $roles_info->color ?? '' }};"></div>
														</li>
														@if($role_total_cnt > 1)
														<li>
														<i class="fa fa-history" aria-hidden="true" data-target="#modal-competence-history" data-matrix_id="{{$matrix_info->id}}" data-history_competence_description="{{$user_role_topology->name}}" data-history_user_name="{{$user->name}}" data-user_role_topology="{{$user_role_topology->id}}" data-user_id="{{$user->id}}" data-toggle="modal"></i>
														</li>
													@endif 
													</ul>                              
												</div>													
												<div class="role-lead-proficiency">
													<div style="display:none" @if($can_edit_skills_matrix == 1)	class="role-lead-proficiency_cls" @else class="single-role-lead-proficiency_cls" @endif  id="role-lead-proficiency_{{$user_role_topology->id }}_{{ $user->id }}">
														@foreach($proficiency as $proficiency_info)
															<div class="RoleSmallColorDisplay"  data-role_auto_id="{{$user_role_topology->id}}"  data-skills_matrix_id="{{$matrix_info->id}}"  data-user_id="{{$user->id}}"    data-color="{{$proficiency_info->color}}" data-color_code="{{$proficiency_info->id}}"  data-code="{{$proficiency_info->code}}" title="Click to {{$proficiency_info->description}}"  style="background-color: {{ $proficiency_info->color ?? '' }};"></div>
														@endforeach
													</div>
												</div>											
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
			
			<div class="tab-pane fade p-3" id="training-needs-matrix-config-tab-content" role="tabpanel" aria-labelledby="one-tab">
				@if(count($users) > 0)
	  				<!--table-responsive Training Needs-->   
				<?php	
							
					$total_users = count($users);    
				?>  
					<div class="table-responsive bg-light p-5 position-table pt_small">
							<form autocomplete="off" id="frm_training_needs_competence_history_date" action="{{ route('matrix-config', ['module'=>$matrix_info->id]) }}" method="GET" enctype="multipart/form-data">
								@csrf
								<div class="row">
									<div class="col-sm-6">
										<div class="form-group">
											@if($competence_history_date =="")		
												<label class="control-label col text-left p_0">On current date <b><?php echo date("F j, Y",strtotime($current_dt));?></b> below are training need configration..</label>
											@else  	
												<label class="control-label col text-left p_0">On date <b><?php echo date("F j, Y",strtotime($current_dt));?></b> below was the training need configration..</label>
											@endif
											<input type="date" max="<?php echo date('Y-m-d');?>" class="form-control col text-right" id="training_needs_competence_history_date" name="competence_history_date" value="{{$current_dt}}" placeholder="Date of Birth..." />
										</div>
									</div>
									<div class="col-lg-4 col-md-4 col-sm-12 offset-2">
											<ul class="listing chart_info">
											@if(count($traning_need_proficiency) > 0)
												@foreach($traning_need_proficiency as $proficiency_info)
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
									<th colspan="{{$total_users+2}}" class="text-center">{{$matrix_info->department}}</th>
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
							@if(count($users_training_need_topologies) > 0)
							@foreach($users_training_need_topologies as $user_role_topology)	
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
												$roles_response = getUserTrainingRolesValues($user->id,$user->position,$user_role_topology->id,$matrix_info->id,$competence_history_date);
												$roles_info = $roles_response['role_values'];
												$role_total_cnt = $roles_response['role_total_cnt'];
											?>                    
											<td class="text-center" id="{{ $user->id }}">                           
												<div  class="training_needs padding_box" id="{{$user_role_topology->id }}_{{ $user->id }}"  data-roleinfo="{{$user->position_name }}__{{ $user->position }}">
													@if($can_edit_skills_matrix == 1)	
													<div class="lead-value">
													@else
													<div class="lead-value" style="width: 100%;">
													@endif 
														<div class="RoleCircelcolorDisplay" id="user_role_proficiency_update_{{$user_role_topology->id }}_{{ $user->id }}" style="background-color: {{ $roles_info->color ?? '' }};"></div>                             
													</div>													
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
				@endif  			         
            </div>	

			<div class="tab-pane fade p-3" id="training-plan-matrix-config-tab-content" role="tabpanel" aria-labelledby="one-tab">
			@if(count($users) > 0)			       
	  			<!--table-responsive Training Plans-->   
				<?php				
				
					$total_users = count($users);    
				?>  
					<div class="table-responsive bg-light p-2 position-table pt_small overflow_hidden">
							<div class="row">
								<div class="col-lg-8 col-md-8 col-sm-12">
									<div class="alert alert-success alert-dismissible" id="update_successfully" style="display:none;">
									<i class="fa fa-check-circle"></i> <span id="success_msg"></span><button type="button" class="close" data-dismiss="alert">×</button>
									</div>
								</div>						
								<div class="col-lg-4 col-md-4 col-sm-12">
										<ul class="listing chart_info">
											@if(count($traning_need_proficiency) > 0)
												@foreach($traning_need_proficiency as $proficiency_info)
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
									<th colspan="{{$total_users+3}}" class="text-center">{{$matrix_info->department}}</th>
								</tr>
								<tr>
									<th width="10%">Area</th>
									<th style="width: 24%;">Competence</th>
									@if(count($users) > 0)
										@foreach($users as $user)
										<th><!--{{ $user->name }}-->
											<div class="role_position"><b>{{ $user->name }}</b><br/>({{ $user->position_name }})<div>
										</th>
										@endforeach
									@endif  
									<th style="width: 13%;">Training <br/>week-{{$matrix_info->training_year}}</th>	
									<th style="width: 32%;">Trainer</th>
									<th class="text-left" style="width: 10%;">Training Phase <br/>/ Comments</th>									
								</tr>
							</thead>
							<tbody>
							@if(count($users_training_need_topologies) > 0)
							@foreach($users_training_need_topologies as $role_topology)	
								@if($user_role_topology->level == 1)								
									<tr>
										<td colspan="{{$total_users+5}}"><span><i><b>{{$role_topology->name}}</b></i></span></td>
									</tr>
								@endif  
								@if($role_topology->level == 2)								
								<tr>
									<td></td> 
									<td colspan="{{$total_users+1}}"><b>{{$role_topology->name}}</b></td>
									<td></td>
									<td></td>	
									<td></td>		
								</tr>
								@endif  
								@if($role_topology->level == 3)	
								   <?php
								 	$no_action  = 0;	  
								   ?>
									<tr>
										<td></td>
										<td>{{$role_topology->name}}</td>									 									
										@if(count($users) > 0)
											@foreach($users as $user)     
											<?php 											
												$roles_response = getUserTrainingRolesValues($user->id,$user->position,$role_topology->id,$matrix_info->id,$competence_history_date);
												$roles_info = $roles_response['role_values'];
												$role_total_cnt = $roles_response['role_total_cnt'];
												if(isset($roles_info) && $roles_info->code=='1'){
													$no_action  = 1;	
												}	
																							
											?>                    
											<td class="text-center" id="{{ $user->id }}">                           
												<div  class="training_needs padding_box" id="{{$role_topology->id }}_{{ $user->id }}"  data-roleinfo="{{$user->position_name }}__{{ $user->position }}">
													@if($can_edit_skills_matrix == 1)	
													<div class="lead-value">
													@else
													<div class="lead-value" style="width: 100%;">
													@endif 
														<div class="RoleCircelcolorDisplay" id="user_role_proficiency_update_{{$role_topology->id }}_{{ $user->id }}" style="background-color: {{ $roles_info->color ?? '' }};"></div>                             
													</div>													
												</div>
											</td>
											@endforeach
										@endif  
										<?php
										
										$week_text = "";
										$status = "";
									    if(!empty($role_topology->week_number)){											
											$week_info_status = getStartAndEndDate($role_topology->week_number,$matrix_info->training_year);											
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
									 @if($can_edit_skills_matrix == 1 && $no_action==1)	
										<td class="text-center" style="padding:2px 20px !important;"
											id="ajax_week_td_{{$role_topology->id}}" title="{{$week_text}}" original="{{$role_topology->week_number}}">
											<span id="ajax_week_select_{{$role_topology->id}}" style="cursor:pointer;" data-skills_matrix_auto_id="{{$role_topology->id}}"  data-skills_training_id="{{$role_topology->skills_training_id}}" 
											data-week_number="{{$role_topology->week_number}}"
											class="showWeekEdit"> {{$role_topology->week_number ?: 'Add Week'}}</span>
											<span id="ajax_week_span_{{$role_topology->id}}"></span>
										</td>									
										<td class="text-center" 
											id="ajax_trainer_td_{{$role_topology->id}}" title="{{$training_type_mode}}" data-original="{{$role_topology->id}}__{{$role_topology->training_type}}__{{$role_topology->training_mode}}__{{$role_topology->trainer_id}}">
											<span id="ajax_trainer_select_{{$role_topology->id}}" data-target="#modal-assign-trainer" data-toggle="modal" data-skills_matrix_auto_id="{{$role_topology->id}}"   style="cursor:pointer;"  
											class="showTrainerEdit"> {{$trainer_name ?: 'Add Trainer'}}</span><br/>
											<!--<span id="ajax_trainer_span_{{$role_topology->id}}">({{$training_type_mode}})</span>-->
										</td>	
										@else
										<td class="text-center" style="padding:2px 20px !important;">{{$role_topology->week_number ?: ''}}</td>
										<td class="text-center" style="padding:2px 20px !important;">{{$trainer_name ?: ''}}</td>									  
										@endif  			
										<td class="text-small"  id="ajax_status_td_{{$role_topology->id}}">
											<span  id="ajax_training_phase_span_{{$role_topology->id}}">{{$role_topology->training_phase ?: 'No Phase'}}</span>
											&nbsp;<span id="ajax_training_comment_span_{{$role_topology->id}}" class="btn btn-outline-dark btn-sm" data-target="#modal-add-comments" data-toggle="modal" data-training_phase="{{$role_topology->training_phase}}" data-matrix_id="{{$matrix_info->id}}" data-skills_matrix_auto_id="{{$role_topology->id}}"> <i class="mdi mdi-eye-outline"></i> <small class="hidden-sm-up">Show</small></span>
									   </td>										  									
									</tr>	
								@endif  	
								@endforeach
							@endif  						
							</tbody>
						</table>
					</div>
					<!--table-responsive --> 
				@endif    	   				             
            </div>				
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
         </div>
      </div>
   </div>
</main>
@endsection
@section('script2')

<div id="modal-add-comments" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<!-- Modal content-->
			<div class="modal-content training_info" >                               
				<div class="modal-header">
					<h4 class="modal-title"><i class="mdi mdi-eye"></i>Training  Comments</h4>
				</div>
				<div class="modal-body">                                          
					<div class="pane panel-default">
						<div class="panel-body">
							<div class="form-group">
								<label class="control-label">Select Training Phase<span class="text-danger">*</span></label>
								<select required class="form-control" name="training_phase" id="training_phase" data-placeholder>
								
								</select>
							</div>
							<div class="form-group" id="add_training_latest_comment">
									<label class="control-label">Add Training Latest Comment</label>
									<textarea class="form-control" row="5" id="skills_comment" name="comment" placeholder="Add Comment..."></textarea>
							</div>
							<div class="form-group comments_box" id="training_comment">
								                                          
							</div>  
						</div>
					</div>
					<input type="hidden" id="skills_matrix_auto_id" name="skills_matrix_auto_id" value="">
					<input type="hidden" id="matrix_id" name="matrix_id" value="">
				</div>
				<div class="modal-footer">
				   <button type="submit" class="btn btn-primary"  id="save_phase_comments"><i class="mdi mdi-content-save"></i> Save</button>
					<button type="button" class="btn btn-default" id="cancel_save_comments" data-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>	
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
					<input type="hidden" id="skills_matrix_auto_id" name="skills_matrix_auto_id" value="">
					
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
							<i class="fas fa-exclamation-triangle fa-1x"></i> Are you sure that you want to remove this Matrix Configuration? <br/>This will remove all the 
							Configurations from the Skills Matrix / Capability matrix / Training needs / Training plan
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
				url: "/matrix-config-topology/"+parentID+"/{{$matrix_info->id}}",
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
			$("#save_phase_comments").click(function(){				
				var skills_comment =$('#skills_comment').val();
				var skills_matrix_auto_id = $('#skills_matrix_auto_id').val();
				var matrix_id = $('#matrix_id').val();
				var training_phase = $('#training_phase').val();				
				$.ajax({
					url: "{{ route('assign-skills-phase-comments')}}",
					dataType: 'json',
					data: {                   
						comment: skills_comment,
						matrix_id: matrix_id,						
						skills_matrix_auto_id: skills_matrix_auto_id,
						training_phase:training_phase												
					},
					type: "POST",
					success: function(response){
						$("#cancel_save_comments").trigger( "click" );
						if(response.is_phase_update){
							$("#ajax_status_td_"+skills_matrix_auto_id).css("background-color", "#f5c1bb");	
							$("#ajax_training_phase_span_"+skills_matrix_auto_id).text(training_phase);
							$("#ajax_training_comment_span_"+skills_matrix_auto_id).data('training_phase', training_phase);
						}else{
							$("#ajax_status_td_"+skills_matrix_auto_id).css("background-color",bgColor);								
							
						}
						$('#skills_comment').val('');
						$("#success_msg").text("Success: You have updated Training Phase/ Comments successfully.");	
						$("#update_successfully").show().delay(3000).fadeOut();						
					}
				})
				return false;				
			})	

			$(document).on('click', '[data-target="#modal-add-comments"]', function(){
				var can_edit_skills_matrix = '{{$can_edit_skills_matrix}}';
				var curRow = $(this).first();			
				var skills_matrix_auto_id = curRow.data('skills_matrix_auto_id');
				var matrix_id = curRow.data('matrix_id');
				var training_phase = curRow.data('training_phase');
				if(can_edit_skills_matrix=='1'){
					$('#add_training_latest_comment').show();
					$('#save_phase_comments').show();
				}else{
					$('#add_training_latest_comment').hide();
					$('#save_phase_comments').hide();					
					$('#training_phase').prop('disabled', 'disabled');
				}

				
				$.ajax({
					url: "{{ route('get-skills-phase-comments')}}",
					dataType: 'json',
					data: {                   
						skills_matrix_auto_id: skills_matrix_auto_id,
						matrix_id: matrix_id,
						training_phase:training_phase																
					},
					type: "POST",
					success: function(response){
						$('#training_comment').html(response.comments_html);	
						$("#training_phase").html(response.training_phase_html);					
						$('#skills_matrix_auto_id').val(skills_matrix_auto_id);
						$('#matrix_id').val(matrix_id);
					}
				}) 
			});

			$("#save_trainer").click(function(){
				var training_type = $('#training_type').val();
				var training_mode = $('#training_mode').val();
				var trainer_id =$('#trainer_id').val();
				var skills_matrix_auto_id = $('#skills_matrix_auto_id').val();
				
				$.ajax({
					url: "{{ route('update-trainner')}}",
					dataType: 'json',
					data: {                   
						training_type: training_type,
						training_mode: training_mode,
						trainer_id: trainer_id,
						skills_matrix_auto_id: skills_matrix_auto_id												
					},
					type: "POST",
					success: function(response){
						$("#cancel_save_trainer").trigger( "click" );
						var element = $("#ajax_trainer_td_"+skills_matrix_auto_id);
            			var original_trainer = element.attr("data-original");
						var new_trainer_name = response.new_trainer_name;							
						$("#ajax_trainer_select_"+skills_matrix_auto_id).prop('title', response.training_type_mode);			
						$("#ajax_trainer_select_"+skills_matrix_auto_id).text(response.trainer_name);	
						var bgColor = $("#ajax_competence_td_"+skills_matrix_auto_id).css("background-color");
						if(original_trainer==new_trainer_name){
							$("#ajax_trainer_td_"+skills_matrix_auto_id).css("background-color",bgColor);	
						}else{
							$("#ajax_trainer_td_"+skills_matrix_auto_id).css("background-color", "#f5c1bb");	
						}
					//	$("#ajax_trainer_span_"+skills_matrix_auto_id).text(response.training_type_mode);
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
				var skills_matrix_auto_id = curRow.data('skills_matrix_auto_id');
				$.ajax({
					url: "{{ route('assign-trainner')}}",
					dataType: 'json',
					data: {                   
						skills_matrix_auto_id: skills_matrix_auto_id										
					},
					type: "POST",
					success: function(response){
						$('#training_type').html(response.training_type_option_html);
						$('#training_mode').html(response.training_mode_option_html);
						$('#trainer_id').html(response.trainers_html);
						$('#users_html').html(response.users_html);
						$('#suppliers_html').html(response.supplier_html);
						$('#skills_matrix_auto_id').val(skills_matrix_auto_id);
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
				$('.modal-content').attr('action', '/matrix-config-topology/'+parentId+'/{{$matrix_info->id}}');				
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
				$('.modal-content').attr('action', '/matrix-config-topology/'+parentId+'/{{$matrix_info->id}}');				
			});
		
			$(document).on('click', '[data-target="#modal-add-competence-type"]', function(){				
				var parentRow = $(this).parents('.topology-row').first();						
				var parentId = parentRow.data('id');
				var level = parentRow.data('level');
				var competence_area = parentRow.data('competence_area_id');
				parentId = parentId || 0;
				level = level || 0;
				$('#competence_area_id').val(competence_area);
				$(this).find('.card-head').find('.modal-title').html('<i class="mdi mdi-plus"></i> '+parentId == 0 ? 'Creating Top Level Topology' : 'Creating Level '+level+' Topology');
				$('.modal-content').attr('action', '/matrix-config-topology/'+parentId+'/{{$matrix_info->id}}');					
			});

			$(document).on('click', '[data-target="#modal-remove-topology"]', function(){			
				var parentRow = $(this).parents('.topology-row').first();
				var mainId = parentRow.data('id');
				var level = parentRow.data('level');
				var name = parentRow.data('name');
				mainId = mainId || 0;
				level = level || 0;

				$(this).find('.card-head').find('.modal-title').html('<i class="mdi mdi-delete"></i> Remove Topology : '+name);				
				$('.modal-content').attr('action', '/matrix-config-topology/'+mainId+'/{{$matrix_info->id}}/remove');					
			});

			fetchTopology(0, $('#topology-holder'));
    });

	function show_original_week(skills_matrix_auto_id,orginal_val) {
		$("#ajax_week_select_"+skills_matrix_auto_id).html(orginal_val);
		$("#ajax_week_span_"+skills_matrix_auto_id).html('');
		$("#ajax_week_select_"+skills_matrix_auto_id).show();
		$("#cancel_show_week__"+skills_matrix_auto_id).remove();
		$("#save_analyte__"+skills_matrix_auto_id).remove();	
	}

	function save_new_week(skills_matrix_auto_id,old_week_number) {				
		var new_week_id = document.getElementById(skills_matrix_auto_id).value;	
		if(new_week_id>0){
			$("#ajax_week_td_"+skills_matrix_auto_id).css("background", "#FFF url(https://dev.ieqas.ie/admin/view/image/loaderIcon.gif) no-repeat center center");
			$.ajax({
				url: "{{ route('save-new-week') }}",
				dataType: 'json',
				data: {                   
					new_week_id: new_week_id,
					skills_matrix_auto_id:skills_matrix_auto_id									
				},
				type: "POST",
				success: function(response){
					$("#ajax_week_select_"+skills_matrix_auto_id).html(new_week_id);
					$("#ajax_week_span_"+skills_matrix_auto_id).html('');
					$("#ajax_week_select_"+skills_matrix_auto_id).show();
					$("#cancel_show_week__"+skills_matrix_auto_id).remove();
					$("#save_analyte__"+skills_matrix_auto_id).remove();							
					$("#ajax_week_select_"+skills_matrix_auto_id).data('week_number', new_week_id);
					$("#ajax_week_select_"+skills_matrix_auto_id).attr('title',response.title); 
					$("#ajax_week_td_"+skills_matrix_auto_id).css("background", "none");	
					var bgColor = $("#ajax_competence_td_"+skills_matrix_auto_id).css("background-color");
					if(old_week_number==new_week_id){
						$("#ajax_week_td_"+skills_matrix_auto_id).css("background-color",bgColor);	
						//$("#ajax_status_td_"+skills_matrix_auto_id).css("background-color",bgColor);	
					}else{
						$("#ajax_week_td_"+skills_matrix_auto_id).css("background-color", "#f5c1bb");
						//$("#ajax_status_td_"+skills_matrix_auto_id).css("background-color", "#f5c1bb");	
					}
					$("#success_msg").text("Success: You have updated training week plan Successfully.");	
					$("#update_successfully").show().delay(3000).fadeOut();						
				}
			}) 
		}else{
			show_original_week(skills_matrix_auto_id,old_week_number);
		}					
	}
	</script>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
	<script src="/js/jQuery.print.js"></script>   
	<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js"  crossorigin="anonymous"></script>
	<script>
		$(document).ready(function(){

			$('#competence_history_date').change(function() { 
				$( "#frm_competence_history_date" ).submit();
			});

			$('#training_needs_competence_history_date').change(function() { 
				$( "#frm_training_needs_competence_history_date" ).submit();
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

			$('.training_needs').mouseover(function() {
				$('#training-needs-proficiency_'+this.id).show();
			});

			$('.training_needs').mouseleave(function() {
				$('#training-needs-proficiency_'+this.id).hide();
			});    			

			$.ajaxSetup({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				}
			});

			$(document).on('click', '[data-target="#modal-competence-history"]', function(){		
				var matrix_id = $(this).data('matrix_id');
				var user_role_topology = $(this).data('user_role_topology');
				var user_id = $(this).data('user_id');
				var history_competence_description = $(this).data('history_competence_description');
				var history_user_name = $(this).data('history_user_name');
				
				$.ajax({
					url: "{{route('matrix-competence')}}",
					dataType: 'json',
					data: {                   
						matrix_id: matrix_id,
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
				var skills_matrix_auto_id = $(this).data('skills_matrix_auto_id');				
				var week_number = $(this).data('week_number');											
				$.ajax({
					url: "{{ route('get-week-listing') }}",
					dataType: 'json',
					data: {                   
						week_number: week_number,
						skills_matrix_auto_id:skills_matrix_auto_id									
					},
					type: "POST",
					success: function(response){
						$("#ajax_week_span_"+skills_matrix_auto_id).html(response.weeks_html);
						$("#ajax_week_select_"+skills_matrix_auto_id).hide();				
					}
				})  
			})				
		
			$(".SmallColorDisplay").click(function(){
				var role_auto_id = $(this).data('role_auto_id');
				var color = $(this).data('color');		
				var color_id = $(this).data('color_id');	
				var role_id = $(this).data('role_id');	
				var code = $(this).data('code');	
			
				$.ajax({
					url: "{{ route('update-matrix-Config') }}",
					dataType: 'json',
					data: {                   
						role_auto_id: role_auto_id,
						color_id: color_id,
						color: color,
						role_id: role_id,
						code: code																		
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
				var skills_matrix_id = $(this).data('skills_matrix_id');		
				var user_id = $(this).data('user_id');	
				var role_name = role_info_txt[0];	
				var color = $(this).data('color');
				var color_code = $(this).data('color_code');
				var code = $(this).data('code');
				var role_id = role_info_txt[1];	
				$.ajax({
					url: "{{ route('update-user-role-matrix-Config') }}",
					dataType: 'json',
					data: {                   
						role_auto_id: role_auto_id,
						skills_matrix_id: skills_matrix_id,
						user_id: user_id,
						role_name: role_name,
						role_id: role_id,
						color: color,
						color_code: color_code,
						code:code																	
					},
					type: "POST",
					success: function(response){
						$("#user_role_proficiency_update_"+response.description_id).css({"background-color":response.color});
						if(response.error_description!=''){
							alert(response.error_description);						
						}	
						if(response.sucess_msg!=''){
							alert(response.sucess_msg);	
							location.reload();					
						}						
					}
				})  
				
			})
		});
	</script>
@endsection