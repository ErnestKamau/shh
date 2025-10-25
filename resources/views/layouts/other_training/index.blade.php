@extends($module == "Training"  ? 'layouts.personnel.layout.app':'layouts.skillsmatrix.layout.app' , ['dataTable'=>true, 'select2'=>true])
<?php $module_text = implode(" ", explode("-", $module)); ?>
@section('title2')
<title>{{ $module }} | {{ $module }}</title>
@endsection
@section('content2')
<style>
   .training_info{
      height:600px;
   overflow-x:auto;
   }
   .details{
   padding: 6px 8px;
   border: 1px solid #ddd;
   border-radius: 3px;
   /* margin-bottom: 6px; */
   border-bottom: 0px;
   }  
   .details h6 {
      font-size: 14px;
      font-weight: 600;
      color: #656565;
   } 
   .details p{margin-bottom:8px !important;}  
   table.model_tables {
   font-family: arial, sans-serif;
   border-collapse: collapse;
   width: 100%;
   }

   table.model_tables td,  table.model_tables th {
   border: 1px solid #dddddd;
   text-align: left;
   padding: 8px;
   }

   table.model_tables tr:nth-child(even) {
   background-color: #dddddd;
   }
   .comments_box h6{
      font-size:13px
   }
   .comments_box .form-control{
      height:inherit;
   }
   .comments_box p{
      display:flex;
      float: left;
      width:100%;
   }
   .comments_box p > span{
      padding: 0px 9px;
      float: left;
   }
   .cl{
      clear:both;
   }
</style>
<main>
   <?php
      $items = array(
        array(
          'link' => route('other-training'),
          'name' =>'Training',
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
      <i class="mdi mdi-microscope"></i> {{ $module }}<small class="text-muted">  | Training Types</small>
   </h2>
   <div class="p-4">
      <div class="card tab-card">
         <div class="card-header tab-card-header">
            <ul class="nav nav-tabs card-header-tabs" id="training-types-tabs" role="tablist">
               <li class="nav-item">
                  <a class="nav-link active" id="active-training-tab" data-toggle="tab" href="#active-training-tab-content" role="tab" aria-controls="Active-Analysis" aria-selected="true">Active Other Training</a>
               </li>             
               <li class="nav-item">
                  <a class="nav-link" id="inactive-training-tab" data-toggle="tab" href="#inactive-training-tab-content" role="tab" aria-controls="InActive-Analysis" aria-selected="false">In Active Other Training</a>
               </li>              
            </ul>
         </div>
         <div class="tab-content" id="training-types-tabs">
            <div class="tab-pane fade show active p-3" id="active-training-tab-content" role="tabpanel" aria-labelledby="one-tab">               
               <h5 class="card-title">Active Other Training
                 @if($can_edit_skills_matrix)
                  <button class="btn btn-primary btn-sm float-right" data-toggle="modal" data-target="#add-config"><i class="mdi mdi-plus"></i> Add</button>
                  @endif   
               </h5>
             
               <div class="table-responsive">
                  <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
                     <thead class="bg-light p-2">
                        <tr>
                           <th>No</th>
                           <th>Training Name</th>  
                           <th>Description</th>   
                           <th>Department</th>
                           <th>Organization Trainer</th>                         
                           <th>Year</th>
                           <th>Training Week</th>                          
                           <th>Participants</th>
                           <th>Training Phase/Comments</th>
                           <th></th>
                        </tr>
                     </thead>
                     <tbody id="matrix-desc-holder">
                        @if(count($training_information['active']) > 0)
                        @foreach($training_information['active'] as $training_info)
                        <?php       
                        $dept_array = array();                  
                        $dept_information = getTrainingDepartments($training_info->department_id);  
                        $dept_array = explode(',',$training_info->department_id);  
                        $participant_cnt =  getTrainingParticipantCnt($training_info->id);  
                        $previous_comments = getTrainingPreviousComments($training_info->id);  
                       
                        $training_type_mode = "";
                        $trainer_name = "";
                           if(!empty($training_info->trainer_id)){	
                           $training_type_mode =  "Type - ".$training_info->training_type." / Mode - ".$training_info->training_mode; 
                           $trainer_name = getTrainerName($training_info->training_type,$training_info->trainer_id);	
                        }	    
                        $week_text = "";
                        $status = "";
                           if(!empty($training_info->week_number)){											
                           $week_info_status = getStartAndEndDate($training_info->week_number,$training_info->training_year);											
                           $week_text =  $week_info_status['dates'][0]." to ".$week_info_status['dates'][1]; 
                           $status = $week_info_status['status'];
                        }

                        ?>
                        <tr data-element="{{ $training_info->id }}">
                           <td valign="center">{{ $loop->iteration }}</td>
                           <td>{{ $training_info->name }}</td>
                           <td>
                              @if(strlen($training_info->description)>30)  
                                 <?php if(strlen($training_info->description)>30) echo substr($training_info->description, 0, 32)."...";?>
                              @else  
                                {{$training_info->description}}
                              @endif
                           </td>
                           <td>
                              @if($dept_information)  
                              @foreach($dept_information as $dept)	                              		
                              {{ $loop->iteration }}. {{$dept->name}}<br/>				
                              @endforeach
                             @endif    
                           </td>
                           <td class="text-center" style="padding:2px 20px !important;"
										title="{{$training_type_mode}}" >
										<span><b>{{ $trainer_name ?: '' }}</b><br/><i>({{$training_type_mode}})</i></span>
                              								
									</td>	                          
                           <td class="text-center">{{ $training_info->training_year }}</td>
                           <td class="text-center"><b>{{ $training_info->week_number }}</b><br/><i>({{$week_text}})</i></td> 
                           <td class="text-center">{{ $participant_cnt }}</td>
                           <td class="text-small">{{$training_info->training_phase  }}&nbsp;<span class="btn btn-outline-dark btn-sm" data-toggle="modal" data-target="#comment-description-{{$training_info->id}}"> <i class="mdi mdi-eye-outline"></i> <small class="hidden-sm-up">Show</small></span>
                              </td>
                           <td >
                              <div style="display:flex;">
                              @if($can_edit_skills_matrix)
                              <button class="btn btn-primary btn-sm mr-1"  data-target="#edit-active-training-{{ $training_info->id }}" data-toggle="modal"><i class="mdi mdi-pencil-outline"></i> <small class="hidden-sm-up">Edit</small> </button>
                              @endif 
                              <span class="btn btn-outline-dark btn-sm" data-toggle="modal" data-target="#feedback-description-{{$training_info->id}}"> <i class="mdi mdi-eye-outline"></i> <small class="hidden-sm-up">Show</small></span>
                              
                              <div id="comment-description-{{$training_info->id}}" class="modal fade" role="dialog">
                                 <div class="modal-dialog">
                                    <!-- Modal content-->
                                    <div class="modal-content training_info" >                               
                                          <div class="modal-header">
                                             <h4 class="modal-title"><i class="mdi mdi-eye"></i>Training  Comments</h4>
                                          </div>
                                          <div class="modal-body">                                          
                                          <div class="pane panel-default">
                                             <div class="panel-body">
                                                <div class="form-group comments_box">
                                                   <h6>Previous Comments.</h6>
                                                   <div class="form-control">
                                                      @if($previous_comments)  
                                                         @foreach($previous_comments as $previous_comment) 
                                                         <p>{{ $loop->iteration }}.<span><?php echo html_entity_decode($previous_comment->comment, ENT_QUOTES, 'UTF-8');?></span></p>				
                                                         @endforeach
                                                      @endif 
                                                      <div class="cl"></div> 
                                                   </div>                                               
                                                </div>  
                                             </div>
                                          </div>
                                          </div>
                                          <div class="modal-footer">
                                             <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                          </div>
                                    </div>
                                 </div>
                              </div>

                              <div id="feedback-description-{{$training_info->id}}" class="modal fade" role="dialog">
                                 <div class="modal-dialog">
                                    <!-- Modal content-->
                                    <div class="modal-content training_info" >                                                 
                                          <?php
                                           $participant_details = getTrainingParticipantDetails($training_info->id,$training_info->department_id);  
                                          ?>                                                                            
                                          <div class="modal-header">
                                             <h4 class="modal-title"><i class="mdi mdi-eye"></i>Training  Details</h4>
                                          </div>
                                          <div class="modal-body">                                          
                                          <div class="pane panel-default">
                                             <div class="panel-body">
                                                <div class="details">
                                                   <h6>Training Name.</h6>
                                                   <p> {{$training_info->name}}</p>
                                                </div>
                                                <div class="details">
                                                   <h6>Training Description.</h6>
                                                   <p> {{$training_info->description}}</p>
                                                </div>
                                                <div class="details">
                                                   <h6>Training Department..</h6>
                                                   <p>  @if($dept_information)  
                                                   @foreach($dept_information as $dept)	                              		
                                                   {{ $loop->iteration }}. {{$dept->name}}<br/>				
                                                   @endforeach
                                                @endif     </p>
                                                </div>
                                                <div class="details">
                                                   <h6>Training Type.</h6>
                                                   <p> {{$training_type_mode}}</p>
                                                </div>
                                                <div class="details">
                                                   <h6>Organization Trainer.</h6>
                                                   <p> {{ $trainer_name ?: '' }}</p>
                                                </div>
                                                <div class="details">
                                                   <h6>Training Year.</h6>
                                                   <p> {{ $training_info->training_year  }}</p>
                                                </div>
                                                <div class="details">
                                                   <h6>Training Week.</h6>
                                                   <p> {{ $training_info->week_number ?: '' }} (<i>({{$week_text}})</i>)</p>
                                                </div>
                                                <div class="details">
                                                   <h6>Previous Comments.</h6>
                                                   <p> @if($previous_comments)  
                                                         @foreach($previous_comments as $previous_comment) 
                                                         <p>{{ $loop->iteration }}.<span><?php echo html_entity_decode($previous_comment->comment, ENT_QUOTES, 'UTF-8');?></span></p>				
                                                         @endforeach
                                                      @endif 
                                                      <div class="cl"></div> </p>
                                                </div>                                             

                                                <div class="details">
                                                   <h6>Status.</h6>
                                                   <p> {{ $status ?: '' }}</p>
                                                </div>                                                         
                                               
                                                @if(count($participant_details) > 0)                                                
                                                <table class="model_tables">
                                                <tr>
                                                   <th>Department</th>
                                                   <th>Participant Name</th>
                                                   <th>Is available?</th>
                                                </tr>
                                                @foreach($participant_details as $participant_info)
                                                <tr>
                                                   <td>Laboratory</td>
                                                   <td>{{$participant_info->name}}</td>
                                                   <td>{{ $participant_info->is_attended ? 'Yes' : 'No' }}</td>
                                                </tr>       
                                                @endforeach                                        
                                                </table>                                              
                                                @endif 
                                             </div>
                                          </div>
                                          </div>
                                          <div class="modal-footer">
                                             <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                          </div>
                                    </div>
                                 </div>
                              </div>

                              </div>
                              <div id="edit-active-training-{{ $training_info->id}}" class="modal fade" role="dialog">
                                 <div class="modal-dialog">
                                    <!-- Modal content-->
                                    <form class="modal-content" method="POST" action="{{ route('update-other-training', ['condition'=>$training_info->id]) }}" enctype="multipart/form-data">
                                       @csrf
                                       <div class="modal-header">
                                          <h4 class="modal-title"><i class="mdi mdi-pencil-outline"></i> Edit Training - {{ $training_info->name }}</h4>
                                       </div>
                                       <div class="modal-body">
                                          <div class="form-group">
                                             <label class="control-label">Training Name<span class="text-danger">*</span></label>
                                             <input type="text" class="form-control" name="name"  value="{{$training_info->name}}" placeholder="Name..." required />
                                          </div>

                                          <div class="form-group">
                                                <label class="control-label">Training Description<span class="text-danger">*</span></label>
                                                <textarea class="form-control" row="5" name="description" placeholder="Training Description..." required>{{$training_info->description}}</textarea>
                                          </div>

                                          <div class="form-group">
                                             <label class="control-label">Department<span class="text-danger">*</span></label>
                                             <select class="form-control" name="department_id[]" multiple data-placeholder required>
                                                <option value="">Select Company...</option>
                                                @foreach ($departments as $c)
                                                <option  {{ in_array($c->id, $dept_array) ? 'selected' : '' }} value="{{ $c->id }}">{{ $c->name }}</option>
                                                @endforeach
                                             </select>
                                          </div>
                                          <div class="form-group">
                                             <label class="control-label">Select Training Year<span class="text-danger">*</span></label>
                                             <select class="form-control" name="training_year" data-placeholder required>
                                                <option value="">Select Company...</option>
                                                @for ($year = (int)date('Y'); date('Y', strtotime('+5 year')) >= $year; $year++)
                                                <option {{ $year == $training_info->training_year ? 'selected' : '' }} value="{{ $year }}">{{ $year }}</option>
                                                @endfor
                                             </select>
                                          </div>

                                          <div class="form-group">
                                             <label class="control-label">Select Training Week<span class="text-danger">*</span></label>
                                             <select class="form-control" name="week_number" data-placeholder required>
                                             <option selected value="">Select Week</option>   
                                                @for ($week =1; $week<= 52; $week++)                    
                                                   <option {{ $week == $training_info->week_number ? 'selected' : '' }}  value="{{ $week }}">{{ $week }}</option>                   
                                                @endfor
                                             </select>
                                          </div>

                                          <div class="form-group">
                                                <label class="control-label">Select Training Phase<span class="text-danger">*</span></label>
                                                <select required class="form-control" name="training_phase" data-placeholder required>		
                                                   <option value="">Select Training Phase</option>
                                                   @foreach ($training_phases as $training_phase)
                                                   <option  {{ $training_phase == $training_info->training_phase ? 'selected' : '' }} value="{{ $training_phase }}">{{ $training_phase }}</option>
                                                   @endforeach
                                                </select>
                                          </div>

                                          <div class="form-group">
                                                <label class="control-label">Add Training Latest Comment</label>
                                                <textarea class="form-control" row="5" name="comment" placeholder="Add Comment..."></textarea>
                                          </div>

                                         <div class="form-group comments_box">
                                             <h6>Previous Comments.</h6>
                                               <div class="form-control">
                                                @if($previous_comments)  
                                                   @foreach($previous_comments as $previous_comment) 
                                                   <p>{{ $loop->iteration }}.<span><?php echo html_entity_decode($previous_comment->comment, ENT_QUOTES, 'UTF-8');?></span></p>				
                                                   @endforeach
                                                @endif 
                                                <div class="cl"></div> 
                                               </div>                                               
                                          </div>  

                                          <div class="form-group">
                                                <label class="control-label">Select Training Type<span class="text-danger">*</span></label>
                                                <select required class="form-control" name="training_type" id="training_type" data-placeholder required>		
                                                   <option value="">Select Training Type</option>
                                                   @foreach ($training_types as $training_type)
                                                   <option  {{ $training_type == $training_info->training_type ? 'selected' : '' }} value="{{ $training_type }}">{{ $training_type }}</option>
                                                   @endforeach
                                                </select>
                                          </div>
                                          <div class="form-group">
                                             <label class="control-label">Select Training Mode<span class="text-danger">*</span></label>
                                             <select required class="form-control" name="training_mode" id="training_mode" data-placeholder required>      
                                             <option value="">Select Training Mode</option>
                                                   @foreach ($training_modes as $training_mode)
                                                   <option {{ $training_mode == $training_info->training_mode ? 'selected' : '' }} value="{{ $training_mode }}">{{ $training_mode }}</option>
                                                   @endforeach         
                                             </select>
                                          </div>           

                                          
                                          @if ($training_info->training_type=='Inhouse')                                             
                                          <div class="form-group">
                                                <label class="control-label">Select Organization/Trainer<span class="text-danger">*</span></label>
                                                <select required class="form-control" name="trainer_id" id="trainer_id" data-placeholder required>	
                                                  <option value="">Select User</option>
                                                    @foreach ($users as $user)
                                                   <option {{ $user->id == $training_info->trainer_id ? 'selected' : '' }} value="{{ $user->id }}">{{ $user->name }}</option>
                                                   @endforeach   				
                                                </select>
                                          </div>
                                          @else
                                          <div class="form-group">
                                                <label class="control-label">Select Organization/Trainer<span class="text-danger">*</span></label>
                                                <select required class="form-control" name="trainer_id" id="trainer_id" data-placeholder required>	
                                                <option value="">Select User</option>
                                                  @foreach ($suppliers as $supplier)                                                
                                                   <option {{ $supplier->id == $training_info->trainer_id ? 'selected' : '' }} value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                                   @endforeach   				
                                                </select>
                                          </div>
                                          @endif

                                          <span id="users_html" style="display:none">{{ $users_html }}</span>
                                          <span id="suppliers_html" style="display:none">{{ $supplier_html }}</span>
                                         
                                           <div class="form-group">                            
                                             <label class="control-label"><input type="checkbox" name="status" value="1" {{ $training_info->status == 1 ? 'checked' : '' }} /> Active</label>
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
                  @if(count($training_information['active']) == 0)
                  <div class="alert alert-info">
                     <i class="mdi mdi-alert"></i> No Active Other Training added yet.
                  </div>
                  @endif
               </div>
            </div>
            <div class="tab-pane fade p-3" id="inactive-training-tab-content" role="tabpanel" aria-labelledby="one-tab">
               <h5 class="card-title">In Active Other Training
                     @if($can_edit_skills_matrix)
                     <button class="btn btn-primary btn-sm float-right" data-toggle="modal" data-target="#add-config"><i class="mdi mdi-plus"></i> Add</button>
                     @endif   
               </h5>
               <div class="table-responsive">
                  <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
                     <thead class="bg-light p-2">
                        <tr>
                        <th>No</th>
                           <th>Training Name</th>  
                           <th>Description</th>   
                           <th>Department</th>
                           <th>Organization Trainer</th>                         
                           <th>Year</th>
                           <th>Training Week</th>                          
                           <th>Participants</th>
                           <th>Training Phase/Comments</th>
                           <th></th>
                        </tr>
                     </thead>
                     <tbody id="matrix-desc-holder">
                        @if(count($training_information['inactive']) > 0)
                        @foreach($training_information['inactive'] as $training_info)
                        <?php       
                        $dept_array = array();                  
                        $dept_information = getTrainingDepartments($training_info->department_id);  
                        $dept_array = explode(',',$training_info->department_id);  
                        $participant_cnt =  getTrainingParticipantCnt($training_info->id);  
                        $previous_comments = getTrainingPreviousComments($training_info->id);  
                       
                        $training_type_mode = "";
                        $trainer_name = "";
                           if(!empty($training_info->trainer_id)){	
                           $training_type_mode =  "Type - ".$training_info->training_type." / Mode - ".$training_info->training_mode; 
                           $trainer_name = getTrainerName($training_info->training_type,$training_info->trainer_id);	
                        }	    
                        $week_text = "";
                        $status = "";
                           if(!empty($training_info->week_number)){											
                           $week_info_status = getStartAndEndDate($training_info->week_number,$training_info->training_year);											
                           $week_text =  $week_info_status['dates'][0]." to ".$week_info_status['dates'][1]; 
                           $status = $week_info_status['status'];
                        }

                        ?>
                        <tr data-element="{{ $training_info->id }}">
                           <td valign="center">{{ $loop->iteration }}</td>
                           <td>{{ $training_info->name }}</td>
                           <td>
                              @if(strlen($training_info->description)>30)  
                                 <?php if(strlen($training_info->description)>30) echo substr($training_info->description, 0, 32)."...";?>
                              @else  
                                {{$training_info->description}}
                              @endif
                           </td>
                           <td>
                              @if($dept_information)  
                              @foreach($dept_information as $dept)	                              		
                              {{ $loop->iteration }}. {{$dept->name}}<br/>				
                              @endforeach
                             @endif    
                           </td>
                           <td class="text-center" style="padding:2px 20px !important;"
										title="{{$training_type_mode}}" >
										<span><b>{{ $trainer_name ?: '' }}</b><br/><i>({{$training_type_mode}})</i></span>
                              								
									</td>	                          
                           <td class="text-center">{{ $training_info->training_year }}</td>
                           <td class="text-center"><b>{{ $training_info->week_number }}</b><br/><i>({{$week_text}})</i></td> 
                           <td class="text-center">{{ $participant_cnt }}</td>
                           <td class="text-small">{{$training_info->training_phase  }}&nbsp;<span class="btn btn-outline-dark btn-sm" data-toggle="modal" data-target="#comment-description-{{$training_info->id}}"> <i class="mdi mdi-eye-outline"></i> <small class="hidden-sm-up">Show</small></span>
                           <td>
                           <div style="display:flex;">
                              @if($can_edit_skills_matrix)
                              <button class="btn btn-primary btn-sm mr-1"  data-target="#edit-active-training-{{ $training_info->id }}" data-toggle="modal"><i class="mdi mdi-pencil-outline"></i> <small class="hidden-sm-up">Edit</small> </button>
                              @endif 
                              <span class="btn btn-outline-dark btn-sm" data-toggle="modal" data-target="#feedback-description-{{$training_info->id}}"> <i class="mdi mdi-eye-outline"></i> <small class="hidden-sm-up">Show</small></span>
                                 
                              <div id="comment-description-{{$training_info->id}}" class="modal fade" role="dialog">
                                 <div class="modal-dialog">
                                    <!-- Modal content-->
                                    <div class="modal-content training_info" >                               
                                          <div class="modal-header">
                                             <h4 class="modal-title"><i class="mdi mdi-eye"></i>Training  Comments</h4>
                                          </div>
                                          <div class="modal-body">                                          
                                          <div class="pane panel-default">
                                             <div class="panel-body">
                                                <div class="form-group comments_box">
                                                   <h6>Previous Comments.</h6>
                                                   <div class="form-control">
                                                      @if($previous_comments)  
                                                         @foreach($previous_comments as $previous_comment) 
                                                         <p>{{ $loop->iteration }}.<span><?php echo html_entity_decode($previous_comment->comment, ENT_QUOTES, 'UTF-8');?></span></p>				
                                                         @endforeach
                                                      @endif 
                                                      <div class="cl"></div> 
                                                   </div>                                               
                                                </div>  
                                             </div>
                                          </div>
                                          </div>
                                          <div class="modal-footer">
                                             <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                          </div>
                                    </div>
                                 </div>
                              </div>
                              
                              <div id="feedback-description-{{$training_info->id}}" class="modal fade" role="dialog">
                                 <div class="modal-dialog">
                                    <!-- Modal content-->
                                    <div class="modal-content training_info" >                                                 
                                          <?php
                                           $participant_details = getTrainingParticipantDetails($training_info->id,$training_info->department_id);  
                                          ?>                                                                            
                                          <div class="modal-header">
                                             <h4 class="modal-title"><i class="mdi mdi-eye"></i>Training  Details</h4>
                                          </div>
                                          <div class="modal-body">                                          
                                          <div class="pane panel-default">
                                             <div class="panel-body">
                                                <div class="details">
                                                   <h6>Training Name.</h6>
                                                   <p> {{$training_info->name}}</p>
                                                </div>
                                                <div class="details">
                                                   <h6>Training Description.</h6>
                                                   <p> {{$training_info->description}}</p>
                                                </div>
                                                <div class="details">
                                                   <h6>Training Department..</h6>
                                                   <p>  @if($dept_information)  
                                                   @foreach($dept_information as $dept)	                              		
                                                   {{ $loop->iteration }}. {{$dept->name}}<br/>				
                                                   @endforeach
                                                @endif     </p>
                                                </div>
                                                <div class="details">
                                                   <h6>Training Type.</h6>
                                                   <p> {{$training_type_mode}}</p>
                                                </div>
                                                <div class="details">
                                                   <h6>Organization Trainer.</h6>
                                                   <p> {{ $trainer_name ?: '' }}</p>
                                                </div>
                                                <div class="details">
                                                   <h6>Training Year.</h6>
                                                   <p> {{ $training_info->training_year  }}</p>
                                                </div>
                                                <div class="details">
                                                   <h6>Training Week.</h6>
                                                   <p> {{ $training_info->week_number ?: '' }} (<i>({{$week_text}})</i>)</p>
                                                </div>

                                                <div class="details">
                                                   <h6>Status.</h6>
                                                   <p> {{ $status ?: '' }}</p>
                                                </div>                                                         
                                               
                                                @if(count($participant_details) > 0)                                                
                                                <table class="model_tables">
                                                <tr>
                                                   <th>Department</th>
                                                   <th>Participant Name</th>
                                                   <th>Is available?</th>
                                                </tr>
                                                @foreach($participant_details as $participant_info)
                                                <tr>
                                                   <td>Laboratory</td>
                                                   <td>{{$participant_info->name}}</td>
                                                   <td>{{ $participant_info->is_attended ? 'Yes' : 'No' }}</td>
                                                </tr>       
                                                @endforeach                                        
                                                </table>                                              
                                                @endif 
                                             </div>
                                          </div>
                                          </div>
                                          <div class="modal-footer">
                                             <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                          </div>
                                    </div>
                                 </div>
                              </div>
                                 
                              </div>
                              <div id="edit-active-training-{{ $training_info->id}}" class="modal fade" role="dialog">
                              <div class="modal-dialog">
                                    <!-- Modal content-->
                                    <form class="modal-content" method="POST" action="{{ route('update-other-training', ['condition'=>$training_info->id]) }}" enctype="multipart/form-data">
                                       @csrf
                                       <div class="modal-header">
                                          <h4 class="modal-title"><i class="mdi mdi-pencil-outline"></i> Edit Training - {{ $training_info->name }}</h4>
                                       </div>
                                       <div class="modal-body">
                                          <div class="form-group">
                                             <label class="control-label">Training Name<span class="text-danger">*</span></label>
                                             <input type="text" class="form-control" name="name"  value="{{$training_info->name}}" placeholder="Name..." required />
                                          </div>

                                          <div class="form-group">
                                                <label class="control-label">Training Description<span class="text-danger">*</span></label>
                                                <textarea class="form-control" row="5" name="description" placeholder="Training Description..." required>{{$training_info->description}}</textarea>
                                          </div>

                                          <div class="form-group">
                                             <label class="control-label">Department<span class="text-danger">*</span></label>
                                             <select class="form-control" name="department_id[]" multiple data-placeholder required>
                                                <option value="">Select Company...</option>
                                                @foreach ($departments as $c)
                                                <option  {{ in_array($c->id, $dept_array) ? 'selected' : '' }} value="{{ $c->id }}">{{ $c->name }}</option>
                                                @endforeach
                                             </select>
                                          </div>
                                          <div class="form-group">
                                             <label class="control-label">Select Training Year<span class="text-danger">*</span></label>
                                             <select class="form-control" name="training_year" data-placeholder required>
                                                <option value="">Select Company...</option>
                                                @for ($year = (int)date('Y'); date('Y', strtotime('+5 year')) >= $year; $year++)
                                                <option {{ $year == $training_info->training_year ? 'selected' : '' }} value="{{ $year }}">{{ $year }}</option>
                                                @endfor
                                             </select>
                                          </div>

                                          <div class="form-group">
                                             <label class="control-label">Select Training Week<span class="text-danger">*</span></label>
                                             <select class="form-control" name="week_number" data-placeholder required>
                                             <option selected value="">Select Week</option>   
                                                @for ($week =1; $week<= 52; $week++)                    
                                                   <option {{ $week == $training_info->week_number ? 'selected' : '' }}  value="{{ $week }}">{{ $week }}</option>                   
                                                @endfor
                                             </select>
                                          </div>

                                          <div class="form-group">
                                                <label class="control-label">Select Training Phase<span class="text-danger">*</span></label>
                                                <select required class="form-control" name="training_phase" data-placeholder required>		
                                                   <option value="">Select Training Phase</option>
                                                   @foreach ($training_phases as $training_phase)
                                                   <option  {{ $training_phase == $training_info->training_phase ? 'selected' : '' }} value="{{ $training_phase }}">{{ $training_phase }}</option>
                                                   @endforeach
                                                </select>
                                          </div>

                                          <div class="form-group">
                                                <label class="control-label">Add Training Latest Comment</label>
                                                <textarea class="form-control" row="5" name="comment" placeholder="Add Comment..."></textarea>
                                          </div>

                                         <div class="form-group comments_box">
                                             <h6>Previous Comments.</h6>
                                               <div class="form-control">
                                                @if($previous_comments)  
                                                   @foreach($previous_comments as $previous_comment) 
                                                   <p>{{ $loop->iteration }}.<span><?php echo html_entity_decode($previous_comment->comment, ENT_QUOTES, 'UTF-8');?></span></p>				
                                                   @endforeach
                                                @endif 
                                                <div class="cl"></div> 
                                               </div>                                               
                                          </div>  

                                          <div class="form-group">
                                                <label class="control-label">Select Training Type<span class="text-danger">*</span></label>
                                                <select required class="form-control" name="training_type" id="training_type" data-placeholder required>		
                                                   <option value="">Select Training Type</option>
                                                   @foreach ($training_types as $training_type)
                                                   <option  {{ $training_type == $training_info->training_type ? 'selected' : '' }} value="{{ $training_type }}">{{ $training_type }}</option>
                                                   @endforeach
                                                </select>
                                          </div>
                                          <div class="form-group">
                                             <label class="control-label">Select Training Mode<span class="text-danger">*</span></label>
                                             <select required class="form-control" name="training_mode" id="training_mode" data-placeholder required>      
                                             <option value="">Select Training Mode</option>
                                                   @foreach ($training_modes as $training_mode)
                                                   <option {{ $training_mode == $training_info->training_mode ? 'selected' : '' }} value="{{ $training_mode }}">{{ $training_mode }}</option>
                                                   @endforeach         
                                             </select>
                                          </div>           

                                          
                                          @if ($training_info->training_type=='Inhouse')                                             
                                          <div class="form-group">
                                                <label class="control-label">Select Organization/Trainer<span class="text-danger">*</span></label>
                                                <select required class="form-control" name="trainer_id" id="trainer_id" data-placeholder required>	
                                                  <option value="">Select User</option>
                                                    @foreach ($users as $user)
                                                   <option {{ $user->id == $training_info->trainer_id ? 'selected' : '' }} value="{{ $user->id }}">{{ $user->name }}</option>
                                                   @endforeach   				
                                                </select>
                                          </div>
                                          @else
                                          <div class="form-group">
                                                <label class="control-label">Select Organization/Trainer<span class="text-danger">*</span></label>
                                                <select required class="form-control" name="trainer_id" id="trainer_id" data-placeholder required>	
                                                <option value="">Select User</option>
                                                  @foreach ($suppliers as $supplier)                                                
                                                   <option {{ $supplier->id == $training_info->trainer_id ? 'selected' : '' }} value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                                   @endforeach   				
                                                </select>
                                          </div>
                                          @endif

                                          <span id="users_html" style="display:none">{{ $users_html }}</span>
                                          <span id="suppliers_html" style="display:none">{{ $supplier_html }}</span>
                                         
                                           <div class="form-group">                            
                                             <label class="control-label"><input type="checkbox" name="status" value="1" {{ $training_info->status == 1 ? 'checked' : '' }} /> Active</label>
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
                  @if(count($training_information['inactive']) == 0)
                  <div class="alert alert-info">
                     <i class="mdi mdi-alert"></i> No In-Active Other Training added yet.
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
<div id="edit-config" class="modal fade" role="dialog">
   <div class="modal-dialog modal-lg">
      <!-- Modal content-->
      <form class="modal-content" method="POST" enctype="multipart/form-data">
         @csrf
         <div class="modal-header">
            <h4 class="modal-title"><i class="mdi mdi-plus"></i> Add </h4>
         </div>
         <div class="modal-body" id="edit-config-fields"></div>
         <div class="modal-footer">
            <input type="hidden" name="module" value="" />
            <button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
         </div>
      </form>
   </div>
</div>
<div id="add-config" class="modal fade" role="dialog">
   <div class="modal-dialog modal-lg">
      <!-- Modal content-->
      <form class="modal-content" method="POST" action="{{ route('assign-other-training') }}"   enctype="multipart/form-data">
         @csrf
         <div class="modal-header">
            <h4 class="modal-title"><i class="mdi mdi-plus"></i> Add {{ $module }}</h4>
         </div>
         <div class="modal-body">
            <div class="form-group">
               <label class="control-label">Training Name<span class="text-danger">*</span></label>
               <input type="text" class="form-control" name="name"  value="" placeholder="Name..." required />
            </div>

            <div class="form-group">
						<label class="control-label">Training Description<span class="text-danger">*</span></label>
						<textarea class="form-control" name="description" placeholder="Training Description..." required></textarea>
			   </div>

            <div class="form-group">
               <label class="control-label">Department<span class="text-danger">*</span></label>
               <select class="form-control" name="department_id[]" multiple data-placeholder required>
                  <option value="">Select Company...</option>
                  @foreach ($departments as $c)
                  <option value="{{ $c->id }}">{{ $c->name }}</option>
                  @endforeach
               </select>
            </div>
            <div class="form-group">
               <label class="control-label">Select Training Year<span class="text-danger">*</span></label>
               <select class="form-control" name="training_year" data-placeholder required>
                  <option value="">Select Company...</option>
                  @for ($year = (int)date('Y'); date('Y', strtotime('+5 year')) >= $year; $year++)
                  <option value="{{ $year }}">{{ $year }}</option>
                  @endfor
               </select>
            </div>

            <div class="form-group">
               <label class="control-label">Select Training Week<span class="text-danger">*</span></label>
               <select class="form-control" name="week_number" data-placeholder required>
               <option selected value="">Select Week</option>   
                  @for ($week =1; $week<= 52; $week++)                    
                     <option value="{{ $week }}">{{ $week }}</option>                   
                  @endfor
               </select>
            </div>

            <div class="form-group">
                  <label class="control-label">Select Training Phase<span class="text-danger">*</span></label>
                  <select required class="form-control" name="training_phase" data-placeholder required>		
                     <option value="">Select Training Phase</option>
                     @foreach ($training_phases as $training_phase)
                     <option  value="{{ $training_phase }}">{{ $training_phase }}</option>
                     @endforeach
                  </select>
            </div>

            <div class="form-group">
                  <label class="control-label">Add Training Latest Comment</label>
                  <textarea class="form-control" row="5" name="comment" placeholder="Add Comment..."></textarea>
            </div>

            <div class="form-group">
						<label class="control-label">Select Training Type<span class="text-danger">*</span></label>
						<select required class="form-control" name="training_type" id="add_training_type" data-placeholder required>		
                     <option value="">Select Training Type</option>
                     @foreach ($training_types as $training_type)
                     <option value="{{ $training_type }}">{{ $training_type }}</option>
                     @endforeach
                  </select>
            </div>
            <div class="form-group">
               <label class="control-label">Select Training Mode<span class="text-danger">*</span></label>
               <select required class="form-control" name="training_mode" id="training_mode" data-placeholder required>      
               <option value="">Select Training Mode</option>
                     @foreach ($training_modes as $training_mode)
                     <option value="{{ $training_mode }}">{{ $training_mode }}</option>
                     @endforeach         
               </select>
            </div>           
            
            <div class="form-group">
						<label class="control-label">Select Organization/Trainer<span class="text-danger">*</span></label>
						<select required class="form-control" name="trainer_id" id="add_trainer_id" data-placeholder>					
						</select>
					</div>
				<span id="users_html" style="display:none">{{ $users_html }}</span>
				<span id="suppliers_html" style="display:none">{{ $supplier_html }}</span>

          		     
            <div class="form-group">                            
               <label class="control-label"><input type="checkbox" name="status" value="1" checked/> Active</label>
            </div>
         </div>
         <div class="modal-footer">          
            <button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
         </div>
      </form>
   </div>
</div>
<script type="text/javascript"><!--  
	$(document).ready(function(){	
      $('#training_type').on('change', function() {    
         if(this.value=='External'){			           
            $('#trainer_id').html($('#suppliers_html').text());
         }else{
            $('#trainer_id').html($('#users_html').text());
         }
      });

      $('#add_training_type').on('change', function() {           
         if(this.value=='External'){			         
            $('#add_trainer_id').html($('#suppliers_html').text());
         }else{
            $('#add_trainer_id').html($('#users_html').text());
         }
      });
     
   });
  </script> 
@endsection