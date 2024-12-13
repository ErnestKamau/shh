@extends($module == "Skills-Matrix" && $config == 'Roles' ? 'layouts.personnel.layout.app' : 'layouts.skillsmatrix.layout.app', ['dataTable' => true, 'select2' => true])
<?php $module_text = implode(" ", explode("-", $module)); ?>
@section('title2')
<title>{{ $module }} | {{ $module }}</title>
<?php 
    $skillcss = "";
    foreach ($proficiencies as $s_p) {
        $skillcss .= '.sp' . $s_p->id . '{ background-color : ' . $s_p->color . ' !important; }';
    }
?>
<style>
    {{$skillcss}}
    .header-fields{
        background-color: #e0e0e0 !important;
    }
    .area-header{
        background-color: #ACACAC;
    }
</style>
@endsection
@section('content2')
<main>
    <?php
$items = array(
    array(
        'link' => route('train.plan.index'),
        'name' => 'Training Plans',
        'icon' => null
    ),
    array(
        'link' => route('train.plan.show',['id'=>$plan->id]),
        'name' => $plan->name,
        'icon' => null
    )
);
      ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    <h2 class="p-4">
        <i class="mdi mdi mdi-calendar-account-outline"></i> Training Plan <small class="text-muted"> | {{$plan->name}} </small>
    
    </h2>
    <div class="card tab-card">
		<div class="card-header tab-card-header">
			<ul class="nav nav-tabs card-header-tabs" id="equipment-tab" role="tablist">
				<li class="nav-item">
					<a class="nav-link active" id="plan-initiator" data-toggle="tab" href="#plan-tab" role="tab" aria-controls="plan-tab" aria-selected="true">Training Plan</a>
				</li>
				<li class="nav-item">
					<a class="nav-link " id="pther-initiator" data-toggle="tab" href="#other-tab" role="tab" aria-controls="othe-tab" aria-selected="true">Others Trainings</a>
				</li>
			</ul>
		</div>
		<div class="tab-content" id="training-tabs-content">
			<div class="tab-pane fade show active p-3" id="plan-tab" role="tabpanel" aria-labelledby="one-tab">
				<h5 class="card-title">Training Plan</h5>
                <form action="{{route('train.plan.show',['id'=>$plan->id])}}" class="row mb-3 border-bottom"  method="post">
                    @csrf
                    <div class="col-md-12 mb-3">
                        <b class="text-muted"><i class="mdi mdi-chevron-right"></i> Proficiency Key</b> <br>
                        <div class="d-flex p-2">
                            @foreach($proficiencies as $proficiency)
                            <div class="{{$loop->iteration == 1 ? 'pl-3' : 'pl-5'}}">
                                <span class="btn btn-sm btn-default p-2 {{'sp'.$proficiency->id}}"></span> <span class="pl-2">{{$proficiency->description}}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="col-md-9 form-group">
                        <label for="" class="control-label">Staff</label>
                        <select name="selected_users[]" id="" class="form-control" multiple>
                            <option value="">Choose Staff</option>
                            @foreach($plan->trainneed->users as $role)
                                @if(count($selectedUsers) > 0)
                                    <option value="{{$role->id}}" {{in_array($role->id,$selectedUsers) ? 'selected' : ''}} >{{$role->user->name}}</option>
                                @else
                                    <option value="{{$role->id}}" {{ $loop->iteration <= 10 ? 'selected' : ''}} >{{$role->user->name}}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 form-group">
                        <button type="submit" class="btn btn-sm btn-outline-primary float-right mt-4"><i class="mdi mdi-filter"></i> Apply</button>
                    </div>
                </form>
				<div class="table-responsive bg-light p-4">
					<table class="table table-condensed table-bordered" style="min-width:120%">
						<thead class="bg-light p-2">
							<tr>
                                <th>#</th>
                                <th>Area</th>
                                <th>Competency</th>
                                @foreach ($plan->trainneed->users as $role)
                                    @if(count($selectedUsers) > 0)
                                        @if(in_array($role->id,$selectedUsers))
                                            <th>{{$role->user->first_name[0].'.'.($role->user->middle_name != '' ? $role->user->middle_name : $role->user->last_name )}}</th>
                                        @endif
                                    @else
                                        @if($loop->iteration <= 10)
                                            <th>{{$role->user->first_name[0].'.'.($role->user->middle_name != '' ? $role->user->middle_name : $role->user->last_name ) }}</th>
                                        @endif 
                                    @endif
                                @endforeach
                                <th>Training Week</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Organization / Trainer</th>
                                <th>Status</th>
                                <th>Remark</th>
							</tr>
						</thead>
						<tbody>
                            <?php $area = "";$type=""; ?>
                            @foreach ($plan->groupdetails as $detail)
                               @if($area != $detail->competency->competencyarea->name)
                                    <tr class="header-fields">
                                        <td class="area-header">#</td>
                                        <td class="area-header">{{$detail->competency->competencyarea->name}}</td>
                                        <?php 
                                            $colspan = 0;
                                            if(count($selectedUsers) > 0){
                                                $colspan = count($selectedUsers) + 1;
                                            }else{
                                                if($plan->trainneed->users->count() > 10){
                                                    $colspan = 11;
                                                }else{
                                                    $colspan = $plan->trainneed->users->count() + 1;
                                                }
                                            }
                                            $colspan +=6;
                                            $area = $detail->competency->competencyarea->name;
                                            $type = $detail->competency->competencytype->name;
                                        ?>
                                        <td colspan="{{$colspan}}">{{$detail->competency->competencytype->name}}</td>
                                    </tr>
                               @endif
                               @if($type != $detail->competency->competencytype->name)
                                    <tr class="header-fields">
                                        <td>#</td>
                                        <td></td>
                                        <?php 
                                            $colspan = 0;
                                            if(count($selectedUsers) > 0){
                                                $colspan = count($selectedUsers) + 1;
                                            }else{
                                                if($plan->trainneed->users->count() > 10){
                                                    $colspan = 11;
                                                }else{
                                                    $colspan = $plan->trainneed->users->count() + 1;
                                                }
                                            }
                                            $colspan +=6;
                                            $type = $detail->competency->competencytype->name;
                                        ?>
                                        <td colspan="{{$colspan}}">{{$detail->competency->competencytype->name}}</td>
                                    </tr>          
                               @endif
                               <tr>
                                <td> 
                                    <span class="btn btn-sm btn-default text-primary" data-target="#edit-plan-detail" data-record="{{json_encode($detail)}}" data-toggle="modal"><i class="mdi mdi-pencil" data-toggle="tooltip" title="Edit"></i></span> 
                                </td>
                                <td></td>
                                <td style="width:20%">{{$detail->competency->competencydescription->name}}</td>
                                @foreach ($plan->trainneed->users as $role)
                                    @if(count($selectedUsers) > 0)
                                        @if(in_array($role->id,$selectedUsers))
                                           <td><span class="btn btn-sm btn-default p-2 {{in_array($role->id,$detail->trainneeddetail->needtraining) ? 'sp'.$proficiencies[0] : 'sp'.$proficiencies[1] }}"></span></td>
                                        @endif
                                    @else
                                        @if($loop->iteration <= 10)
                                        <td><span class="btn btn-sm btn-default p-2 {{in_array($role->id,$detail->trainneeddetail->needtraining) ? 'sp'.$proficiencies[0]->id : 'sp'.$proficiencies[1]->id }}"></span></td>
                                        @endif 
                                    @endif
                                @endforeach
                                <td>{{'Week '.$detail->week_no ?? '-'}}</td>
                                <td>{{ $detail->training_start_date != '' ?  date('Y/m/d',strtotime($detail->training_start_date)) : ''}}</td>
                                <td>{{ $detail->training_end_date != '' ? date('Y/m/d',strtotime($detail->training_end_date)) : '' }}</td>
                                <td>{{$detail->organizer_trainer}}</td>
                                <td class="text-center">
                                     @if($detail->status == 0)
                                         <b>NOT DONE</b>
                                     @endif
                                </td>
                                <td>{{$detail->remark}}</td>
                               </tr>
                            @endforeach
							
						</tbody>
					</table>
				</div>
			</div>
			<!-- ---------  -->
			<div class="tab-pane fade show  p-3" id="other-tab" role="tabpanel" aria-labelledby="one-tab">
				<h5 class="card-title">
                    <span class="btn btn-sm btn-outline-primary float-right" data-target="#add-other-training" data-toggle="modal"><i class="mdi mdi-plus"></i> Add Other Trainings</span>
                    Other Trainings
                </h5>
				<div class="table-responsive bg-light mt-3 p-4">
					<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm" style="width:120%">
						<thead class="bg-light p-2">
							<tr>
                                <th>#</th>
                                <th>Training</th>
                                <th>Organization/Trainer</th>
								<th>Training Week</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Participants</th>
                                <th>Status</th>
                                <th>Remark</th>
							</tr>
						</thead>
						<tbody>
                            @foreach ($plan->others as $other )
                                <tr>
                                    <td>
                                        <span class="btn btn-sm btn-default text-primary" data-record="{{json_encode($other)}}" data-toggle="modal" data-target="#edit-others"><i class="mdi mdi-pencil" data-toggle="tooltip" title="Edit"></i></span>
                                        <span class="btn btn-default btn-sm text-danger" data-record="{{json_encode($other)}}" data-target="#delete-others" data-toggle="modal"><i class="mdi mdi-delete-empty" data-toggle="tooltip" title-="Delete"></i></span>
                                    </td>
                                    <td>{{$other->other_competency}}</td>
                                    <td style="width:10%">{{$other->organizer_trainer}}</td>
                                    <td style="width:5%">{{$other->week_no}}</td>
                                    <td style="width:8%">{{date('Y/m/d',strtotime($other->training_start_date))}}</td>
                                    <td style="width:8%">{{date('Y/m/d',strtotime($other->training_end_date))}}</td>
                                    <td>
                                        <?php 
                                        $staffnames = [];
                                        foreach($other->otheruser as $user){
                                            array_push($staffnames,$user->user->name);
                                        } ?>
                                    {{implode(', ',$staffnames )}}
                                    </td>
                                    <td class="text-center">
                                        @if($other->status == 0)
                                            <b>Not Done</b>
                                        @endif
                                    </td>
                                    <td>{{$other->remark}}</td>
                                </tr>
                            @endforeach
							
						</tbody>
					</table>
				</div>
			</div>
			<!-- -----  -->
		</div>
	</div>
</main>
@endsection
@section('script2')

<div class="modal fade" id="add-other-training" role="dialog">
    <div class="modal-dialog modal-lg">
        
        <div class="modal-content">
            <form action="{{route('train.plan.store.other')}}" method="post">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Other Trainings</h5>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 form-group">
                            <label for="" class="control-label">Competency Name</label>
                            <input type="text" name="name" placeholder="Competancy Name..." id="" class="form-control">
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="" class="control-label">Training Start Date</label>
                            <input type="date" name="start_date" id="" class="form-control">
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="" class="control-label">Training End Date</label>
                            <input type="date" name="end_date" id="" class="form-control">
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="" class="control-label">Training Week</label>
                            <select name="week_no" id="" class="form-control">
                                <option value="">Select Week</option>
                                @foreach(range(1,52) as $week)
                                    <option value="{{$week}}">Week {{$week}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="" class="control-label">Organizers / Trainers</label>
                            <input type="text" name="trainer" placeholder="Organizers / Trainers..." class="form-control">
                        </div>
                        <div class="col-md-12 form-group">
                            <label for="" class="control-label">Staff</label>
                            <select name="staff_ids[]" multiple id="" class="form-control">
                                <option value="">Select Staff</option>
                                @foreach($staffs as $staff)
                                <option value="{{$staff->id}}">{{$staff->name}}</option>
                                @endforeach
                            </select>
                            <input type="hidden" name="plan_id" value="{{$plan->id}}">
                            <input type="hidden" name="other_id" value="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-sm btn-outline-primary"><i class="mdi mdi-content-save"></i> Save</button>
                    <span class="btn btn-sm btn-default" data-dismiss="modal">Close</span>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="edit-others" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{route('train.plan.store.other')}}" method="post">
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
<div class="modal fade" id="edit-plan-detail" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{route('train.plan.detail.store')}}" method="post">
                @csrf
                <div class="modal-body">
                   
                </div>
                <div class="modal-footer">
                    <button class="btn btn-sm btn-outline-primary"><i class="mdi mdi-content-save"></i> Save</button>
                    <span class="btn btn-sm btn-default" data-dismiss="modal">Close</span>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="delete-others" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{route('train.plan.others.delete')}}" method="post">
                @csrf
                <div class="modal-body">
                    
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="mdi mdi-thumb-up"></i> Yes, Delete</button>
                    <span class="btn btn-sm btn-default" data-dismiss="modal">Close</span>
                </div>
            </form>
        </div>
    </div>
</div>
<script type="text/javascript">

    $(document).ready(function () {
        var geteDeleteOtherbody = (data)=>{
            var body = $(`
                <div class="alert alert-danger p-2 d-flex">
                    <i class="mdi mdi-delete-empty"></i>
                    <span class="pl-2">Confirm you want to delete <b>${data.other_competency}</b> from the training plan</span>
                    <input type="hidden" name="other_id" value="${data.id}">
                </div>
            `).clone();
            return body;
        }
        $('#delete-others').on('show.bs.modal',(e)=>{
            var data = $(e.relatedTarget).data('record');
            var body =geteDeleteOtherbody(data);
            $('#delete-others').find('.modal-body').empty();
            $('#delete-others').find('.modal-body').append(body);
        })
        var getEditOthersBody = (data)=>{
            
            var formattedStartDate = data.training_start_date ? data.training_start_date.split(" ")[0] : ''; 
            var formattedEndDate =  data.training_end_date ? data.training_end_date.split(" ")[0] : ''; 

            var body = $(`
                <div class="alert alert-primary p-2">
                    <i class="mdi mdi-pencil-box-outline"></i>
                    <span class="pl-3">Edit ${data.other_competency} Other Trainings details below:</span>
                </div>
                <div class="row">
                    <div class="col-md-12 form-group">
                        <label for="" class="control-label">Competency Name</label>
                        <input type="text" name="name" placeholder="Competancy Name..." value="${data.other_competency}" id="" class="form-control">
                    </div>
                    <div class="col-md-6 form-group">
                        <label for="" class="control-label">Training Start Date</label>
                        <input type="date" name="start_date" value="${formattedStartDate}" id="" class="form-control">
                    </div>
                    <div class="col-md-6 form-group">
                        <label for="" class="control-label">Training End Date</label>
                        <input type="date" name="end_date" value="${formattedEndDate}" id="" class="form-control">
                    </div>
                    <div class="col-md-6 form-group">
                        <label for="" class="control-label">Training Week</label>
                        <select name="week_no" id="" class="form-control week_no">
                            <option value="">Select Week</option>
                            @foreach(range(1,52) as $week)
                                <option value="{{$week}}">Week {{$week}}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-6 form-group">
                        <label for="" class="control-label">Status</label>
                        <select name="status" id="" class="form-control status">
                            <option value="">Select Status</option>
                            <option value="0">Not Done</option>
                            <option value="1">Done</option>
                            <option value="2">Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-12 form-group">
                        <label for="" class="control-label">Organizers / Trainers</label>
                        <input type="text" name="trainer" value="${data.organizer_trainer}" placeholder="Organizers / Trainers..." class="form-control">
                    </div>
                    <div class="col-md-12 form-group">
                        <label for="" class="control-label">Staff</label>
                        <select name="staff_ids[]" multiple id="" class="form-control staff_id">
                            <option value="">Select Staff</option>
                            @foreach($staffs as $staff)
                            <option value="{{$staff->id}}">{{$staff->name}}</option>
                            @endforeach
                        </select>
                        <input type="hidden" name="other_id" value="${data.id}">
                    </div>
                </div>
            `).clone();
            $(body).find('.week_no').val(data.week_no);
            $(body).find('.week_no').select2();

            $(body).find('.status').val(data.status);
            $(body).find('.status').select2();
            $(body).find('.staff_id').val(data.otheruserids);
            $(body).find('.staff_id').select2();

            return body;
        }
        $('#edit-others').on('show.bs.modal',(e)=>{
            var data = $(e.relatedTarget).data('record');
            var body =getEditOthersBody(data);
            $('#edit-others').find('.modal-body').empty();
            $('#edit-others').find('.modal-body').append(body);
        });

        
        var getEditPlanDetailBody = (data)=>{
            var formattedStartDate = data.training_start_date ?  data.training_start_date.split(" ")[0] : ''; 
            var formattedEndDate =  data.training_end_date ? data.training_end_date.split(" ")[0] : ''; 
            var body = $(`
                <div class="alert alert-primary p-2 d-flex">
                    <i class="mdi mdi-pencil-box-outline"></i>
                    <span class="pl-3">Edit ${data.competency.competencydescription.name} Training details below:</span>
                </div>
                
                <div class="form-group">
                    <label for="" class="control-label">Start Date</label>
                    <input type="date" name="start_date" value="${formattedStartDate}" id="" class="form-control">
                </div>
                <div class="form-group">
                    <label for="" class="control-label">End Date</label>
                    <input type="date" name="end_date" value="${formattedStartDate}" id="" class="form-control">
                </div>
                <div class="form-group">
                    <label for="" class="control-label">Training Week</label>
                    <select name="week_no" id="" class="form-control week_no">
                        <option value="">Select Week</option>
                        @foreach (range(1,52) as $week)
                            <option value="{{$week}}">Week {{$week}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="" class="control-label">Status</label>
                    <select name="status" id="" class="form-control status">
                        <option value="">Select Status</option>
                        <option value="0">Not Done</option>
                        <option value="1">Done</option>
                        <option value="2">Cancelled</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="" class="control-label">Trainer / Organizer</label>
                    <input type="text" name="trainer" value="${data.organizer_trainer}" placeholder="Trainer..." class="form-control">
                </div>
                <div class="form-group">
                    <label for="" class="control-label">Remark</label>
                    <textarea name="remark" id="" class="form-control">${data.remark}</textarea>
                </div>
                <input type="hidden" name="detail_id" value="${data.id}">
            `).clone();
            $(body).find('.week_no').val(data.week_no);
            $(body).find('.week_no').select2();

            $(body).find('.status').val(data.status);
            $(body).find('.status').select2();

            return body;
       }
       $('#edit-plan-detail').on('show.bs.modal',(e)=>{
            console.log('here');
         var data = $(e.relatedTarget).data('record');
         var body =getEditPlanDetailBody(data);
         $('#edit-plan-detail').find('.modal-body').empty()
         $('#edit-plan-detail').find('.modal-body').append(body);
       });
       
    });


</script>
@endsection