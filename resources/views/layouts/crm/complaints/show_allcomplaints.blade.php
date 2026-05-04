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
                'link'=>route('crm.complaints-manager',['stage'=>$workflow_stage]),
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
    <i class="mdi mdi-comment-alert"></i>{{$complaint->complaint_id}} General Information
    </h3>
    <br><br>
    <div class="card tab-card">
        <div class="card-header tab-card-header">
            <ul class="nav nav-tabs card-header-tabs" id="complaint-tabs" role="tablist">
                <li class="nav-item">
                    <a href="#general-tab" class="navlink active" id="general-tabs" data-toggle="tab" role="tab" aria-controls="general-tab" aria-selected="true"><i style="font-size: 12px;" class="mdi mdi-note-text"></i>General</a>
                </li>
                <li class="nav-item">
                    <a href="#notes-tab" class="navlink " id="notes-tabs" data-toggle="tab" role="tab" aria-controls="notes-tab" aria-selected="true"><i style="font-size: 12px;" class="mdi mdi-note-multiple"></i>Notes<span class="badge badge-pill badge-dark">{{$notes_total}}</span></a>
                </li>
                <li class="nav-item">
                    <a href="#attachment-tab" class="navlink " id="attachments-tabs" data-toggle="tab" role="tab" aria-controls="attachment-tab" aria-selected="true"><i style="font-size: 12px;" class="mdi mdi-attachment"></i>Attachments<span class="badge badge-pill badge-dark">{{$attach_total}}</span></a>
                </li>
                @if($complaint->complaint_workflow >2 )
                <li class="nav-item">
                    <a href="#resolution-tab" class="navlink" id="resolutions-tabs" data-toggle="tab" role="tab" aria-controls="resolution-tab" aria-selected="true"><i style="font-size: 12px;" class="mdi mdi-message-processing"></i>Resolutions<span class="badge badge-pill badge-dark">{{$res_total}}</span></a>
                </li>
                @endif
                <li class="nav-item">
                    <a href="#custody-tab" class="navlink" id="custody-tabs" data-toggle="tab" role="tab" aria-controls="custody-tab" aria-selected="true"><i style="font-size: 12px;" class="mdi mdi-account-switch"></i>Chain Of Custody</a>
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
                                <td>
                                    <span class="btn btn-outline-success btn-sm" data-toggle="modal" data-target="#comment-content-{{$custody->id}}"><i class="mdi mdi-message-text"></i></span>
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
                                                    <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
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
                                <td>
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
                                                    <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                                                </div>
                                            </div>
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
                                    <td>
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
                                                    <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                                                </div>
                                            </div>
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
                                    <td>
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
                                                    <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
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
            @endif
            <!-- -------------end resolution ------------  -->
        </div>
    </div>
</main>

@endsection

@section('script2')

@endsection