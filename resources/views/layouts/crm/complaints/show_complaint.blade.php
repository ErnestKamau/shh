@extends('layouts.crm.layout.app', ['dataTable'=>true, 'select2'=>true])
@section('title2')
  <title>{{$workflow_stage}}</title>
@endsection
@section('content2')
<?php
    $chainOfCustody = getComplaintChainOfCustody($complaint->id);
    $attachments = getComplaintAttachment($complaint->id);
    $notes = getComplaintNotes($complaint->id);
    $resolutions = getComplaintsResolutions($complaint->id);
    $users = getUsers($all=true);
    $res_total = getComplaintsResolutionTotal($complaint->id);
    $notes_total= getComplaintNotesTotal($complaint->id);
    $attach_total = getComplaintAttachmentTotal($complaint->id);
?>
<main>
    <?php
        $items = array(
            array(
                'link' => route('customers-list'),
                'name' => 'CRM',
                'icon' => null
              ),
            array(
                'link'=>route('complaint-workflow',['stage'=>$workflow_stage]),
                'name'=>$workflow_stage,
                'icon'=>null
            ),
            array(
                'link'=>null,
                'name'=> $complaint->complaint_id,
                'icon'=>null
            )
        );
    
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    <h3 class="p-4">
        <i class="mdi mdi-comment-alert"></i>{{$workflow_stage}}<br>
        @if($workflow_stage == "Log & Intake")
        <button class="btn btn-outline-secondary btn-sm float-right" data-toggle="modal" data-target="#request-complaint-approval"><i class="mdi mdi-comment-check"></i> Request Approval</button>
        @endif
        @if($workflow_stage == "Active Investigations")
        <button class="btn btn-outline-success btn-sm float-right" style="margin-left:1rem" data-toggle="modal" data-target="#approve-complaint"><i class="mdi mdi-comment-check"></i> Approve Complaint</button>
        <button class="btn btn-outline-warning btn-sm float-right"style="margin-left:1rem" data-toggle="modal" data-target="#return-complaint"><i class="mdi mdi-comment-arrow-left"></i> Return Complaint</button>
        <button class="btn btn-outline-danger btn-sm float-right" style="margin-left:1rem" data-toggle="modal" data-target="#reject-complaint"><i class="mdi mdi-comment-remove"></i> Reject Complaint</button>
        @endif
        @if($workflow_stage == "Verification Review & CAPA"  && $res_total>0)
        <button class="btn btn-outline-secondary btn-sm float-right" data-toggle="modal" data-target="#request-complaint-approval"><i class="mdi mdi-comment-check"></i> Request Resolution Approval</button>
        @endif
        @if($workflow_stage == "Pending Closure")
        <button class="btn btn-outline-success btn-sm float-right" style="margin-left:1rem" data-toggle="modal" data-target="#approve-resolution"><i class="mdi mdi-comment-check"></i> Approve Resolutions</button>
        <button class="btn btn-outline-warning btn-sm float-right"style="margin-left:1rem" data-toggle="modal" data-target="#return-resolution"><i class="mdi mdi-comment-arrow-left"></i> Return Resolutions</button>
        
        @endif
    </h3>
    <br><br>
    <div class="card tab-card">
        <div class="card-header tab-card-header">
            <ul class="nav nav-tabs card-header-tabs" id="complaint-tabs" role="tablist">
                <li class="nav-item">
                    <a href="#general-tab" class="navlink active" id="general-tabs" data-toggle="tab" role="tab" aria-controls="general-tab" aria-selected="true"><i style="font-size:12px;" class="mdi mdi-note-text"></i>General</a>
                </li>
                <li class="nav-item">
                    <a href="#notes-tab" class="navlink " id="notes-tabs" data-toggle="tab" role="tab" aria-controls="notes-tab" aria-selected="true"><i style="font-size:12px;" class="mdi mdi-note-multiple"></i>Notes<span class="badge badge-pill badge-dark"> {{$notes_total}}</span></a>
                </li>
                <li class="nav-item">
                    <a href="#attachment-tab" class="navlink " id="attachments-tabs" data-toggle="tab" role="tab" aria-controls="attachment-tab" aria-selected="true"><i style="font-size:12px;" class="mdi mdi-attachment"></i>Attachments<span class="badge badge-pill badge-dark">{{$attach_total}}</span></a>
                </li>
                @if($complaint->complaint_workflow >2 )
                <li class="nav-item">
                    <a href="#resolution-tab" class="navlink" id="resolutions-tabs" data-toggle="tab" role="tab" aria-controls="resolution-tab" aria-selected="true"><i style="font-size:12px " class="mdi mdi-message-processing"></i>Resolutions<span class="badge badge-pill badge-dark">{{$res_total}}</span></a>
                </li>
                @endif
                <li class="nav-item">
                    <a href="#custody-tab" class="navlink" id="custody-tabs" data-toggle="tab" role="tab" aria-controls="custody-tab" aria-selected="true"><i style="font-size:12px" class="mdi mdi-account-switch"></i>Chain Of Custody</a>
                </li>
            </ul>
        </div>
        <div class="tab-content" id="complaint-tabs-content">
            <!-- ----------general tab -------  -->
            <div class="tab-pane fade show active p-3" id="general-tab" role="tabpanel" aria-labelledby="one-tab">
                <h5 class="card-title">General Information</h5>
                <div class="card " style="padding-left: 8rem;padding-right:8rem">
                    <div class="card-body">
                        <div class="p-3 center" >
                            <h4 class="p-3 text-bold text-lg text-center bg-light-gray border-bottom">
                               Complaint {{$complaint->complaint_id}}
                            </h4>
                            <br>
                            <div class="row text-center">
                                <div class="col-md-6">
                                    <p><b>Complaint ID:</b> {{$complaint->complaint_id}}</p>
                                </div>
                                <div class="col-md-6" >
                                    <p><b>Priority:</b> {{$complaint->priority}}</p>
                                </div>
                            </div>
                            <div class="row text-center">
                                <div class="col-md-6">
                                    <p><b>Recieved From:</b> {{$complaint->received_from}}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><b>Registered By:</b> {{$complaint->registered_by}}</p>
                                </div>
                            </div>
                            <div class="row text-center">
                                <div class="col-md-6">
                                    <p><b>Complaint Type:</b> {{$complaint->type}}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><b>Date:</b> {{$complaint->date}}</p>
                                </div>
                            </div>
                            
                            <br>

                            <h5 class="text-center"><b>Description</b></h5>
                            <div class="panel panel-default">
                                <div class="panel-body">
                                {{$complaint->description}}
                                </div>
                            </div>

                           
                            
                        </div>
                    </div>
                </div>
            </div>
            <!-- -------------end general---------- -->

            <!-- ------------------------------chain ------  -->
            <div class="tab-pane fade show p-3" id="custody-tab" role="tabpanel" aria-labelledby="one-tab">
                <h5 class="card-title">
                    {{$complaint->complaint_id}} Chain Of Custody
                </h5>
                <div class="table-responsive">
                    <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
                        <thead class="bg-light p-2">
                            <tr>
                                <th>No</th>
                                <th>Complaint</th>
                                <th>Workflow Stage</th>
                                <th nowrap>User Responsible</th>
                                <th>Date</th>
                                <th nowrap>Action</th>
                                <th>Comments</th>

                            </tr>
                        </thead>
                        <tbody>

                            @foreach($chainOfCustody as $custody)
                            <tr>

                                <td>{{$loop->iteration}}</td>
                                <td>{{$complaint->complaint_id}}</td>
                                <td>
                                    <?php 
                                        $stage_flows = is_numeric($custody->workflow_stage)
                                            ? (getComplaintWorkflow()[$custody->workflow_stage] ?? $custody->workflow_stage)
                                            : $custody->workflow_stage;
                                    ?>
                                    {{ $stage_flows }}
                                </td>
                                <td>
                                    <?php 
                                        $taker = getUserById($custody->action_taker_id);
                                    ?>
                                    {{$taker->name}}
                                </td>
                                <td>{{$custody->created_at}}</td>
                                <td>{{$custody->action}}</td>
                                <td class="text-center">
                                    <span class="btn btn-outline-sucess btn-sm" data-toggle="modal" data-target="#comment-content-{{$custody->id}}"><i class="mdi mdi-message-text"></i></span>
                                    <div class="modal fade" id="comment-content-{{$custody->id}}" role="dialog">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h3 class="modal-title">Chain Of Custody {{$loop->iteration}} Comment</h3>
                                                </div>
                                                <div class="modal-body">
                                                    <h5>Comment</h5>
                                                    <div class="panel panel-default">
                                                        <div class="panel-body">
                                                            <p>{{$custody->comments}}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-outline-danger" data-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div> 
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- -----------------end chain ------------  -->

            <!-- --------notes tab------  -->
            <div class="tab-pane fade show p-3" id="notes-tab" role="tabpanel" aria-labelledby="one-tab">
                <h5 class="card-title">
                    Notes
                    <button class="btn btn-outline-primary btn-sm float-right" data-toggle="modal" data-target="#add-notes"><i class="mdi mdi-note-plus"></i> Add</button>
                </h5>
                <div class="table-responsive">
                    <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
                        <thead class="bg-light p-2">
                            <tr>
                                <th>No</th>
                                <th nowrap> Note Type</th>
                                <th nowrap> Created By</th>
                                <th>Date</th>
                                <th>Notes</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($notes as $note)
                            @if($note->is_delete==0)
                            <tr>
                                <td>{{$loop->iteration}}</td>
                                <td>{{$note->type}}</td>
                                <td>{{$note->created_by}}</td>
                                <td>{{$note->created_at}}</td>
                                <td class="text-center">
                                    <span class="btn btn-outline-success btn-sm" data-toggle="modal" data-target="#notes-content-{{$note->id}}"><i class="mdi mdi-message-text"></i></span>
                                    <div class="modal fade" id="notes-content-{{$note->id}}" role="dialog">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h3 class="modal-title">{{$complaint->complaint_id}} Notes {{$loop->iteration}}</h3>
                                                </div>
                                                <div class="modal-body">
                                                    <h5>{{$complaint->complaint_id}} Notes</h5>
                                                    <div class="panel panel-default">
                                                        <div class="panel-body">
                                                            <p>{{$note->notes}}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-outline-danger" data-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="btn btn-outline-info btn-sm" data-toggle="modal" data-target="#edit-notes-{{$note->id}}"><i class="mdi mdi-pencil"></i></span>
                                    <div class="modal fade" id="edit-notes-{{$note->id}}" role="dialog">
                                        <div class="modal-dialog">
                                            <form action="{{ route('edit-notes',['id'=>$note->id]) }}" method="post" class="modal-content" enctype="multipart/form-data">
                                                @csrf 
                                                <div class="modal-header">
                                                    <h4 class="modal-title"><i class="mdi mdi-pencil"></i> Edit {{$complaint->complaint_id}} Notes {{$loop->iteration}}</h4>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="form-group">
                                                        <label class="control-label">Type</label>
                                                        <input type="text" name="type" id="types" class="form-control" value="{{$note->type}}" placeholder="Notes Type" required/>
                                                    </div>
                                                    <div class="form-group ">
                                                        <label class="control-label">Status</label>
                                                        <select class="form-control" id="select-status" name="status" placeholder="Status..." readonly="true">
                                                            <option value=0 {{ $note->is_delete == 0 ? 'selected' : '' }}>Active</option>
                                                            <option value=1 {{ $note->is_delete == 1 ? 'selected' : '' }}>Deleted</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="control-label">Notes</label>
                                                        <textarea class="form-control" name="notes" row="5" placeholder="Notes..." required >{{$note->notes}}</textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="submit" class="btn btn-outline-primary"><i class="mdi mdi-content-save"></i> Save</button>
                                                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
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
            <!-- -------end notes- ------  -->
            <!-- -------------attachments ----  -->
            <div class="tab-pane fade show p-3" id="attachment-tab" role="tabpanel" aria-labelledby="one-tab">
                <h5 class="card-title">
                    {{$complaint->complaint_id}} Attachments
                    
                    <button class="btn btn-outline-primary btn-sm float-right" data-toggle="modal" data-target="#add-attachment"><i class="mdi mdi-plus"></i> Add</button>
                </h5>
                <div class="table-responsive">
                    <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
                        <thead class="bg-light p-2">
                            <tr>
                                <th>No</th>
                                <th>Title</th>
                                <th>Type</th>
                                <th>File</th>
                                <th>Registered By</th>
                                <th>Date</th>
                                <th>Description</th>
                                <th></th>
                                
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($attachments as $attachment)
                            @if($attachment->is_delete == 0)
                                <tr>
                                    <td>{{$loop->iteration}}</td>
                                    <td>{{$attachment->title}}</td>
                                    <td>{{$attachment->type}}</td>
                                    <td><a href="{{$attachment->file_path}}" class="btn btn-transparent"target="_blank"><i class="mdi mdi-download text-success"></i> Download</a></td>
                                    <td>{{$attachment->posted_by}}</td>
                                    <td>{{$attachment->created_at}}</td>
                                    
                                    <td class="text-center">
                                    <span class="btn btn-outline-success btn-sm" data-toggle="modal" data-target="#attachment-content-{{$attachment->id}}"><i class="mdi mdi-message-text"></i></span>
                                    <div class="modal fade" id="attachment-content-{{$attachment->id}}" role="dialog">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h3 class="modal-title">Attachment {{$loop->iteration}} Description</h3>
                                                </div>
                                                <div class="modal-body">
                                                    <h5>Description</h5>
                                                    <div class="panel panel-default">
                                                        <div class="panel-body">
                                                            <p>{{$attachment->description}}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-outline-danger" data-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>     
                                    </td>
                                    <td class="text-center">
                                        <span class="btn btn-outline-info btn-sm" data-target="#edit-attachment-{{$attachment->id}}" data-toggle="modal"><i class="mdi mdi-pencil"></i></span>
                                        <div id="edit-attachment-{{$attachment->id}}" class="modal fade" role="dialog">
                                            <div class="modal-dialog">
                                                <form action="{{ route('edit-attachment',['id'=>$attachment->id]) }}" class="modal-content" method="POST" enctype="multipart/form-data">
                                                    @csrf 
                                                    <div class="modal-header">
                                                        <h4 class="modal-title"><i class="mdi mdi-pencil"></i> Edit {{$attachment->title}} Attachment</h4>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="form-group">
                                                            <label class="control-label">Title</label>
                                                            <input type="text" name="title" class="form-control" value="{{$attachment->title}}" placeholder="Title...">
                                                        </div>
                                                        <div class="form-group">
                                                            <label class="control-label">Type</label>
                                                            <input type="text" name="type" class="form-control" value="{{$attachment->type}}" placeholder="Type...">
                                                        </div>
                                                        <div class="form-group ">
                                                            <label class="control-label">Status</label>
                                                            <select class="form-control" id="select-status" name="status" placeholder="Status..." readonly="true">
                                                                <option value=0 {{ $attachment->is_delete == 0 ? 'selected' : '' }}>Active</option>
                                                                <option value=1 {{ $attachment->is_delete == 1 ? 'selected' : '' }}>Deleted</option>
                                                            </select>
                                                        </div>
                                                        <div class="form-group">
                                                            <label class="control-label">File</label>
                                                            <input type="file" class="form-control" name="certificate" placeholder="Certificate..." />
                                                        </div>
                                                        <div class="form-group">
                                                            <label class="control-label">Description</label>
                                                            <textarea class="form-control" name="description" row="3" placeholder="Description..." required >{{$attachment->description}}</textarea>
                                                        </div>

                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="submit" class="btn btn-outline-primary"><i class="mdi mdi-content-save"></i> Save</button>
                                                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
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
            <!-- ---------end attachmment -----  -->
            <!-- ------------------resolution-------  -->
            @if($complaint->complaint_workflow > 2)

            <div class="tab-pane fade show p-3" id="resolution-tab" role="tabpanel" aria-labelledby="one-tab">
                <h5 class="card-title">
                    {{$complaint->complaint_id}} Resolutions
                    @if($workflow_stage == "Verification Review & CAPA")
                    <button class="btn btn-outline-primary btn-sm float-right" data-toggle="modal" data-target="#add-resolution"><i class="mdi mdi-plus"></i> Add</button>  
                    @endif
                </h5>
                <div class="table-responsive">
                    <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
                        <thead class="bg-light p-2">
                            <tr>
                                <th>No</th>
                                <th>CAR No</th>
                                <th>Officer Responsible</th>
                                <th>Registered By</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Action</th>
                                <th></th>
                                
                                
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($resolutions as $resolution)
                                <tr>
                                    <td>{{$loop->iteration}}</td>
                                    <td>{{$resolution->car_no}}</td>
                                    <td>{{$resolution->officer_responsible}}</td>
                                    <td>{{$resolution->registered_by}}</td>
                                    <td>{{$resolution->created_at}}</td>
                                    <td class="text-small">{!! $resolution->approve == 0 ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>' !!}</td>
                                    <td class="text-center">
                                        <span class="btn btn-outline-success btn-sm" data-target="#resolution-notes-{{$resolution->id}}" data-toggle="modal"><i class="mdi mdi-message-text"></i></span>
                                        <div class="modal fade" id="resolution-notes-{{$resolution->id}}" role="dialog">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h3 class="modal-title">{{$resolution->car_no}} Action </h3>
                                                </div>
                                                <div class="modal-body">
                                                    <h5>{{$resolution->car_no}} Action</h5>
                                                    <div class="panel panel-default">
                                                        <div class="panel-body">
                                                            <p>{{$resolution->action}}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-outline-danger" data-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    </td>
                                    <td class="text-center">
                                    
                                        <span class="btn btn-outline-primry btn-sm" data-toggle="modal" data-target="#edit-resolution-{{$resolution->id}}"><i class="mdi mdi-pencil"></i></span>
                                        <div class="modal fade" id="edit-resolution-{{$resolution->id}}" role="dialog">
                                            <div class="modal-dialog">
                                                <form action="{{ route('edit-resolution',['id'=>$resolution->id]) }}" method="post" class="modal-content" enctype="multipart/form-data">
                                                    @csrf 
                                                    <div class="modal-header">
                                                        <h4 class="modal-title"><i class="mdi mdi-pencil"></i> Edit Resolution {{$resolution->car_no }}</h4>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="form-group ">
                                                            <label class="control-label">Status</label>
                                                            <select class="form-control" id="select-status" name="status" placeholder="Status..." readonly="true">
                                                                <option value=0 {{ $resolution->reject == 0 ? 'selected' : '' }}>Active</option>
                                                                <option value=1 {{ $resolution->reject == 1 ? 'selected' : '' }}>Delete</option>
                                                            </select>
                                                        </div>
                                                        <div class="form-group ">
                                                            <label class="control-label">Officer Responsible</label>
                                                            <select class="form-control" id="select-status" name="officer_responsible" placeholder="Status..." readonly="true">
                                                                @foreach($users as $user)
                                                                <option value={{$user->name}} {{ $resolution->officer_responsible == $user->name ? 'selected' : '' }}>{{$user->name}}</option>
                                                                @endforeach
                                                                
                                                            </select>
                                                        </div>
                                                        <div class="form-group">
                                                            <label class="control-label">Action</label>
                                                            <textarea name="action" rows="8" class="form-control" placeholder="Resolution action notes...">{{$resolution->action}}</textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="submit" class="btn btn-outline-primary"><i class="mdi mdi-content-save"></i> Save</button>
                                                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                                    </div>
                                                    
                                                </form>
                                            </div>
                                        </div>
                                    </td>
                               
                                </tr>
                            @endforeach

                        </tbody>
                    </table>
                </div>
            </div>
            @endif
            <!-- -------------end resolution ------------  -->
        </div>
    </div>
</main>

@endsection

@section('script2')
<!-- -----request approval ---  -->

<div class="modal fade" id="request-complaint-approval" role="dialog">
    <div class="modal-dialog">
        <form action="{{ route('approve-complaint',['id'=>$complaint->id]) }}" method="post" class="modal-content" enctype="multipart/form-data">
            @csrf 
            <div class="modal-header">
                <h4 class="modal-title">Request {{$complaint->complaint_id}} Approval</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="control-label">Comment</label>
                    <textarea class="form-control" name="comment" row="3" placeholder="Comment..." ></textarea>
                </div>          
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-outline-primary"><i class="mdi mdi-content-save"></i> Save</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </form>
    </div>
</div>

<!-- -----end request approval ---- -->
<!-- --------------------reject approval ----  -->
<div class="modal fade" id="reject-complaint" role="dialog">
    <div class="modal-dialog">
        <form action="{{ route('reject-complaint',['id'=>$complaint->id]) }}" method="post" class="modal-content" enctype="multipart/form-data">
            @csrf 
            <div class="modal-header">
                <h4 class="modal-title">Reject {{$complaint->complaint_id}} Complaint</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="control-label">Comment</label>
                    <textarea class="form-control" name="comment" row="3" placeholder="Comment..." ></textarea>
                </div>          
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-outline-primary"><i class="mdi mdi-content-save"></i> Save</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </form>
    </div>
</div>
<!-- -----------end reject approval--------  -->

<!-- ---------reverse ---------  -->
<div class="modal fade" id="return-complaint" role="dialog">
    <div class="modal-dialog">
        <form action="{{ route('reverse-complaint',['id'=>$complaint->id]) }}" method="post" class="modal-content" enctype="multipart/form-data">
            @csrf 
            <div class="modal-header">
                <h4 class="modal-title">Reverse {{$complaint->complaint_id}} Complaint</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="control-label">Comment</label>
                    <textarea class="form-control" name="comment" row="3" placeholder="Comment..." ></textarea>
                </div>          
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-outline-primary"><i class="mdi mdi-content-save"></i> Save</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </form>
    </div>
</div>
<!-- -------------end reverse -------  -->
<!-- ---------approve -----  -->
<div class="modal fade" id="approve-complaint" role="dialog">
    <div class="modal-dialog">
        <form action="{{ route('approve-complaint',['id'=>$complaint->id]) }}" method="post" class="modal-content" enctype="multipart/form-data">
            @csrf 
            <div class="modal-header">
                <h4 class="modal-title">Approve {{$complaint->complaint_id}} Complaint</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="control-label">Comment</label>
                    <textarea class="form-control" name="comment" row="3" placeholder="Comment..." ></textarea>
                </div>          
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-outline-primary"><i class="mdi mdi-content-save"></i> Save</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </form>
    </div>
</div>
<!-- ----------end approve ----  -->
<!-- ------------------resolution ------  -->
<div class="modal fade" id="resolution-complaint" role="dialog">
    <div class="modal-dialog">
        <form action="" method="post" class="modal-content" enctype="multipart/form-data">
            @csrf 
            <div class="modal-header">
                <h4 class="modal-title">Move {{$complaint->complaint_id}} To Complaint Resolution </h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="control-label">Comment</label>
                    <textarea class="form-control" name="comment" row="3" placeholder="Comment..." ></textarea>
                </div>          
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-outline-primary"><i class="mdi mdi-content-save"></i> Save</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </form>
    </div>
</div>
<!-- ----------end resolution ------ -->

<!-- ----------add notes ---------  -->
<div class="modal fade" id="add-notes" role="dialog">
    <div class="modal-dialog">
        <form action="{{ route('add-notes',['id'=>$complaint->id]) }}" method="post" class="modal-content" enctype="multipart/form-data">
            @csrf 
            <div class="modal-header">
                <h4 class="modal-title">Add Notes For {{$complaint->complaint_id}}</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="control-label">Type</label>
                    <input type="text" name="type" class="form-control" value="" placeholder="Type...">
                </div>
                <div class="form-group">
                    <label class="control-label">Notes</label>
                    <textarea class="form-control" name="notes" row="3" placeholder="Comment..." ></textarea>
                </div>          
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-outline-primary"><i class="mdi mdi-content-save"></i> Save</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </form>
    </div>
</div>
<!-- -----------end notes ---------  -->
<!-- --------------------attachmment -------  -->
<div class="modal fade" id="add-attachment" role="dialog">
    <div class="modal-dialog">
        <form action="{{ route('add-attachment',['id'=>$complaint->id]) }}" method="post" class="modal-content" enctype="multipart/form-data">
            @csrf 
            <div class="modal-header">
                <h4 class="modal-title">Add Attachment For {{$complaint->complaint_id}}</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="control-label">Title</label>
                    <input type="text" name="title" class="form-control" value="" placeholder="Title...">
                </div>
                <div class="form-group">
                    <label class="control-label">Type</label>
                    <input type="text" name="type" class="form-control" value="" placeholder="Type...">
                </div>
                <div class="form-group">
                    <label class="control-label">File</label>
                    <input type="file" class="form-control" name="certificate" placeholder="Certificate..." />
                </div>
                <div class="form-group">
                    <label class="control-label">Description</label>
                    <textarea class="form-control" name="description" row="3" placeholder="Description..." ></textarea>
                </div>          
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-outline-primary"><i class="mdi mdi-content-save"></i> Save</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </form>
    </div>
</div>
<!-- ----------end attachment -------  -->
<!-- -------------- add resolution -----------  -->
<div class="modal fade" id="add-resolution" role="dialog">
    <div class="modal-dialog">
        <form action="{{ route('add-resolution',['id'=>$complaint->id]) }}" method="post" class="modal-content" enctype="multipart/form-data">
            @csrf 
            <div class="modal-header">
                <h4 class="modal-title">Add Resolution For {{$complaint->complaint_id}}</h4>
            </div>
            <div class="modal-body">
                <div class="form-group ">
                    <label class="control-label">Officer Responsible</label>
                    <select class="form-control" id="select-status" name="officer_responsible" placeholder="Status..." readonly="true">
                        @foreach($users as $user)
                        <option value={{$user->name}}>{{$user->name}}</option>
                        @endforeach
                        
                    </select>
                </div>
                <div class="form-group">
                    <label class="control-label">Action</label>
                    <textarea class="form-control" name="action" row="15" placeholder="Complaint Resolution Notes..." ></textarea>
                </div>          
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-outline-primary"><i class="mdi mdi-content-save"></i> Save</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </form>
    </div>
</div>
<!-- -------------end resolution--------------  -->

<!-- ---------request resolution approval ---------  -->
<div class="modal fade" id="request-resolution-approval" role="dialog">
    <div class="modal-dialog">
        <form action="{{ route('approve-complaint',['id'=>$complaint->id]) }}" method="post" class="modal-content" enctype="multipart/form-data">
            @csrf 
            <div class="modal-header">
                <h4 class="modal-title">Request {{$complaint->complaint_id}} Resolutions Approval</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="control-label">Comment</label>
                    <textarea class="form-control" name="comment" row="3" placeholder="Comment..." ></textarea>
                </div>          
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-outline-primary"><i class="mdi mdi-content-save"></i> Save</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </form>
    </div>
</div>
<!-- ---------end request resolution approval ---------  -->

<!-- --------------approve resolution -------  -->
<div class="modal fade" id="approve-resolution" role="dialog">
    <div class="modal-dialog">
        <form action="{{ route('approve-complaint',['id'=>$complaint->id]) }}" method="post" class="modal-content" enctype="multipart/form-data">
            @csrf 
            <div class="modal-header">
                <h4 class="modal-title">Approve {{$complaint->complaint_id}} Resolutions</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="control-label">Comment</label>
                    <textarea class="form-control" name="comment" row="3" placeholder="Comment..." ></textarea>
                </div>          
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-outline-primary"><i class="mdi mdi-content-save"></i> Save</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </form>
    </div>
</div>
<!-- ------------end approve resolution -------------  -->
<!-- ----------------return resolution------------  -->
<div class="modal fade" id="return-resolution" role="dialog">
    <div class="modal-dialog">
        <form action="{{ route('reverse-complaint',['id'=>$complaint->id]) }}" method="post" class="modal-content" enctype="multipart/form-data">
            @csrf 
            <div class="modal-header">
                <h4 class="modal-title">Reverse {{$complaint->complaint_id}} Resolutions</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="control-label">Comment</label>
                    <textarea class="form-control" name="comment" row="3" placeholder="Comment..." ></textarea>
                </div>          
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-outline-primary"><i class="mdi mdi-content-save"></i> Save</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </form>
    </div>
</div>
<!-- ------------------- end-return resolution ------  -->

@endsection