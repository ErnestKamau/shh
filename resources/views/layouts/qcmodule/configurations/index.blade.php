@extends('layouts.lab.layout.app', ['dataTable'=>true, 'select2'=>true])

@section('title2')
<title>Quality Control | Qc</title>
@endsection
@section('content2')
<main>
    <?php
    $items = array(
        array(
            'link' => route('dashboard-lab'),
            'name' => 'Dashboard',
            'icon' => null
        ),
        array(
            'link' => route('qc_configuration_index'),
            'name' => 'Qc - Configuration',
            'icon' => null
        )
    );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    <h5 class="p-4">
        <i class="mdi mdi-file-certificate-outline"></i> QC Module | Configurations
    </h5>
    <div class="card tab-card" style="box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;">
        <div class="card-header tab-card-header">
            <ul class="nav nav-tabs card-header-tabs" id="qc-config-tabs" role="tablist">
                <li class="nav-item">
                    <a href="#standards-tab" class="nav-link active" id="standards-tabs" data-toggle="tab" role="tab" aria-controls="standards-tab" aria-selected="true"> <i class="mdi mdi-layers" style="color: black;font-size:15px"></i> Standards</a>
                </li>
                <li class="nav-item">
                    <a href="#qc-types-tab" class="nav-link " id="qc-types-tabs" data-toggle="tab" role="tab" aria-controls="qc-types-tab" aria-selected="true"> <i class="mdi mdi-compare-vertical" style="color: black;font-size:15px"></i> Qc Types</a>
                </li>
                <li class="nav-item">
                    <a href="#qc-schemes-tabs" class="nav-link " id="qc-schemes-tab" data-toggle="tab" role="tab" aria-controls="qc-schemes-tabs" aria-selected="true"> <i class="mdi mdi-file-tree" style="color: black;font-size:15px"></i> Qc Schemes</a>
                </li>
                <li class="nav-item">
                    <a href="#qc-approval-tabs" class="nav-link " id="qc-approval-tab" data-toggle="tab" role="tab" aria-controls="qc-schemes-tabs" aria-selected="true"> <i class="mdi mdi-account-check" style="color: black;font-size:15px"></i> Approval Configurations</a>
                </li>
                
            </ul>
        </div>
        <div class="tab-content" id="sample-type-tabs-content">

            <div class="tab-pane fade show p-3" id="qc-approval-tabs" role="tabpanel" aria-labelledby="one-tab">
                <h5 class="card-title">
                    <i class="mdi mdi-account-check"></i> Approval Configuration
                    <span class="btn btn-default text-primary btn-sm float-right" style="box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;"  data-toggle="modal" data-target="#add-qc-approver"><i class="mdi mdi-plus"></i> Approver</span>
                </h5>
                <div class="table-responsive mt-4">
                    <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Created By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($approvals as $approval)
                            <tr>
                                <td>
                                    <span class="btn btn-sm btn-default text-primary" data-target="#edit-approver" data-toggle="modal" data-record="{{json_encode($approval)}}"><i class="mdi mdi-pencil"></i></span>
                                   <a class="btn btn-default btn-sm text-danger" href="{{route('deleteQcApprovvers',['id'=>$approval->id])}}"><i class="mdi mdi-delete-empty" data-toggle="tooltip" title="Delete"></i></a>
                           
                                </td>
                                <td>{{$approval->name}}</td>
                                <td>{{$approval->creator}}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- standard tab  -->
            <div class="tab-pane fade show active p-3" id="standards-tab" role="tabpanel" aria-labelledby="one-tab">
                <h5 class="card-title">
                    <i class="mdi mdi-layers"></i> Standards
                    <span class="btn btn-default btn-sm float-right text-primary" style="box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;" data-toggle="modal" data-target="#add-standard" data-mode="add"><i class="mdi mdi-plus"></i> Add</span>
                </h5>

                <div class="table-responsive mt-4">
                    <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Created By</th>
                                <th>Qc Type</th>
                                <th>Qc Schemes</th>
                                <th>Active</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($standards as $standard)
                            <tr>
                                <td>
                                    <span class="btn btn-default btn-sm text-primary" data-mode="edit" data-record="{{json_encode($standard)}}" data-toggle="modal" data-target="#add-standard"><i class="mdi mdi-pencil" data-toggle="tooltip" title="Edit"></i></span>
                                    <a href="{{route('qc_StandardShow',['id'=>$standard->id])}}" class="btn btn-default btn-sm text-success"><i class="mdi mdi-eye" data-toggle="tooltip" title="View"></i></a>
                                    <span class="btn btn-default btn-sm text-danger" data-record="{{json_encode($standard)}}" data-toggle="modal" data-target="#delete-standards"><i class="mdi mdi-delete-empty" data-toggle="tooltip" title="Delete"></i></span>
                                </td>
                                <td>
                                <a href="{{route('qc_StandardShow',['id'=>$standard->id])}}" class="">{{$standard->code}}</a></td>
                                <td>{{$standard->name}}</td>
                                <td>{{$standard->creator()->name ?? '-'}}</td>
                                <td>{{$standard->getQcType()->name}}</td>
                                <td>{{$standard->qcschemenames}}</td>
                                <td>{!! $standard->status == 1 ? '<span class="text-success"><i class="mdi mdi-checkbox-marked-circle-outline"></i></span>' : '-' !!}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- end standard tab  -->

            <!-- qc types tab -->
            <div class="tab-pane fade p-3" id="qc-types-tab" role="tabpanel" aria-labelledby="one-tab">
                <h5 class="card-title">
                    <i class="mdi mdi-compare-vertical"></i> Qc Types
                    <span class="btn btn-default text-primary btn-sm float-right" style="box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;" data-toggle="modal" data-mode="add" data-target="#add-qctypes"><i class="mdi mdi-plus"></i> Add</span>
                </h5>
                <div class="table-responsive mt-4">
                    <table class="table table-condensed table-striped table-hover table-bordered table-sm" style="width: 110% !important;">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th >Name</th>
                                <th>Code</th>
                                <th>Created By</th>
                                <th>Has Standards</th>
                                <th>Has Connfigured Samples</th>
                                <th>Use Existing Samples</th>
                                <th>Active</th>

                            </tr>
                        </thead>
                        <tbody>
                            @foreach($qc_types as $type)
                            <tr>
                                <td style="width:7% !important">
                                    <span class="btn btn-default btn-sm text-primary" data-mode="edit" data-record="{{json_encode($type)}}" data-toggle="modal" data-target="#add-qctypes"><i class="mdi mdi-pencil" data-toggle="tooltip" title="Edit"></i></span>
                                    <span class="btn btn-default btn-sm text-danger" data-record="{{json_encode($type)}}" data-toggle="modal" data-target="#delete-qc-type"><i class="mdi mdi-delete-empty" data-toggle="tooltip" title="Delete"></i></span>
                                </td>
                                <td>{{$type->name}}</td>
                                <td>{{$type->code}}</td>
                                <td style="width:15% !important">{{$type->creator()->name}}</td>
                                <td style="width:5% !important" class="text-center">{!! $type->has_standards == 1 ? '<span class="text-success"><i class="mdi mdi-checkbox-marked-circle-outline mdi-24px"></i></span>' : '-' !!} </td>
                                <td class="text-center" style="width:5% !important">{!! $type->has_configured_samples == 1 ? '<span class="text-success"><i class="mdi mdi-checkbox-marked-circle-outline mdi-24px"></i></span>' : '-' !!}</td>
                                <td class="text-center" style="width:5% !important">{!! $type->use_existing_sample == 1 ? '<span class="text-success"><i class="mdi mdi-checkbox-marked-circle-outline mdi-24px"></i></span>' : '-' !!}</td>
                                <td  class="text-center" style="width:5% !important">{!! $type->is_active == 1 ? '<span class="text-success"><i class="mdi mdi-checkbox-marked-circle-outline mdi-24px"></i></span>' : '-' !!}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <!--  qc schemes  -->
            <div class="tagb-pane fade p-3" id="qc-schemes-tabs" role="tabpanel" aria-labelledby="one-tab">
                <h5 class="card-title">
                    <i class="mdi mdi-file-tree"></i> Qc Schemes
                    <span class="btn btn-default text-primary btn-sm float-right" style="box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px" data-mode="add" data-toggle="modal" data-target="#add-scheme"><i class="mdi mdi-plus"></i> Add Qc Schemes</span>
                </h5>
                <div class="table-responsive mt-4">
                    <table class="table table-sm table-condensed table-bordered table-hover table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($qc_schemes as $scheme)
                            <tr>
                                <td>
                                    <span class="btn btn-sm btn-default text-primary" data-mode="edit" data-record="{{json_encode($scheme)}}" data-toggle="modal" data-target="#add-scheme"><i class="mdi mdi-pencil"></i></span>
                                    <span class="btn btn-sm btn-default text-danger" data-toggle="modal"data-record="{{json_encode($scheme)}}"  data-target="#delete-scheme"><i class="mdi mdi-delete-empty"></i></span>
                                </td>
                                <td>{{$scheme->code}}</td>
                                <td>{{$scheme->name}}</td>
                                <td  class="text-center" style="width:5% !important">{!! $scheme->is_active == 1 ? '<span class="text-success"><i class="mdi mdi-checkbox-marked-circle-outline mdi-24px"></i></span>' : '-' !!}</td>
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
<div class="modal fade" id="edit-approver" data-staff="{{json_encode($staffs)}}" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="" method="post">
                <div class="modal-body">
                    
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-sm btn-outline-info"><i class="mdi mdi-content-save"></i> Save</button>
                    <span class="btn btn-sm btn-default" data-dismiss="modal">Close</span>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="add-qc-approver" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{route('addQcApprovvers')}}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-primary p-2 d-flex">
                        <i class="mdi mdi-alert-decagram" style="font-size: 25px;"></i>
                        <span class="p-2">
                            Add QC Report Approvers Below
                        </span>
                    </div>
                    <div class="form-group">
                        <label for="" class="control-label">Personnel</label>
                        <select name="personnel_id" id="" class="form-control">
                            @foreach($staffs as $staff)
                            <option value="{{$staff->id}}">{{$staff->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-sm btn-outline-info"><i class="mdi mdi-content-save"></i> Save</button>
                    <span class="btn btn-sm btn-default" data-dismiss="modal">Close</span>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="add-scheme" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{route('MaintainQcSchemes')}}" method="POST" enctype="multipart/form-data">
                @csrf 
                <div class="modal-header">
                
                </div>
                <div class="modal-body">
    
                   
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-sm btn-outline-info"><i class="mdi mdi-content-save"></i> Save</button>
                    <span class="btn btn-sm btn-default" data-dismiss="modal">Close</span>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="delete-scheme" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{route('DeleteQcSchemes')}}" method="POST" enctype="multipart/form-data">
                @csrf 

                <div class="modal-body">
                    
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="mdi mdi-delete-empty"></i> Yes, Delete</button>
                    <span class="btn btn-sm btn-default" data-dismiss="modal">Close</span>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="add-standard" data-schemes="{{json_encode($qc_schemes)}}" data-qctypes="{{json_encode($qc_types)}}" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{route('qc_addQcStandard')}}" method="post">
                @csrf 
                <div class="modal-header">
                    <h5 class="card-header" style="width: 100%;"><i class="mdi mdi-plus"></i> Add Qc Standard</h5>
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
<div class="modal fade" id="add-qctypes" role="dialog">
    <div class="modal-dialog">
        
        <div class="modal-content">
            <form action="{{ route('qc_createQcTypes') }}" method="post">
                @csrf 
                <div class="modal-header">
                    <h5 class="card-header" style="width: 100%;"><i class="mdi mdi-plus"></i> Add Qc Type</h5>
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

<div class="modal fade" id="delete-standards" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('qc_deleteQcStandard') }}" method="post">
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
<div class="modal fade" id="delete-qc-type" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('qc_deleteQCTypes') }}" method="post">
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



<script>
    $(()=>{
        let editApprover = (data)=>{
            var staff = $('#edit-approver').data('staff');
            var body = $(`
                <div class="alert alert-primary p-2 d-flex">
                    <i class="mdi mdi-alert-decagram" style="font-size: 25px;"></i>
                    <span class="p-2">
                        Edit ${data.name} QC Report Approvers Below
                    </span>
                </div>
                <div class="form-group">
                    <label for="" class="control-label">Personnel</label>
                    <select name="personnel_id" id="personnel_id" class="form-control">
                        
                    </select>
                </div>
                <input type="hidden" name="approver_id" value="${data.id}">
            `).clone();
            $.each(staff,(i,obj)=>{
                var option = `<option value="${obj.id}" ${obj.id == data.personnel_id ? 'selected' : '' }>${obj.name}<option/>`;
                $(body).find('#personnel_id').append(option);
            })
            return body;
        }
        $('#edit-approver').on('show.bs.modal',(e)=>{
            var data = $(e.relatedTarget).data('record');
            var body = editApprover(data);
            $('#edit-approver').find('.modal-body').empty();
            $('#edit-approver').find('.modal-body').append(body);

        })
        let deleteQcTypeBody = (data)=>{
            var body = $(`
                <div class="alert alert-danger d-flex p-2">
                    <i class="mdi mdi-delete-empty mdi-36px"></i>
                    <span class="p-2">
                        Confirm you want to delete <b>${data.name}</b> Quality Control Type
                    </span>
                </div>
                <input type="hidden" name="qc_type_id" value="${data.id}">
            `).clone();
            return body;
        }
        let deleteStandardBody = (data)=>{
            var body = $(`
                <div class="alert alert-danger d-flex p-2">
                    <i class="mdi mdi-delete-empty mdi-36px"></i>
                    <span class="p-2">
                        Confirm you want to delete <b>${data.name}</b> Quality Control Type
                    </span>
                </div>
                <input type="hidden" name=:"standard_id" value="${data.id}">
            `).clone();
            return body;
        }
        $('#delete-standards').on('show.bs.modal',(e)=>{
            var data = $(e.relatedTarget).data('record');
            var body = deleteStandardBody(data);
            $('#delete-standards').find('.modal-body').empty();
            $('#delete-standards').find('.modal-body').append(body);
        });
        $('#delete-qc-type').on('show.bs.modal',(e)=>{
            var data = $(e.relatedTarget).data('record');
            var body = deleteQcTypeBody(data);
            $('#delete-qc-type').find('.modal-body').empty();
            $('#delete-qc-type').find('.modal-body').append(body);
        });
        let addQcTypesBody = (data)=>{
            var body = $(`
                <div class="form-group">
                    <label for="" class="control-label">Name</label>
                    <input type="text" name="name" value="${data ? data.name : ''}" placeholder="Name.." class="form-control">
                </div>
                <div class="form-group">
                    <label for="" class="control-label">Code</label>
                    <input type="text" placeholder="Code.." value="${data ? data.code : ''}" name="code" class="form-control">
                </div>
                <div class="form-group">
                    <label for="" class="control-label"><input type="checkbox"  name="has_standards" id="has_standards" > Has Standards</label>
                </div>
                <div class="form-group">
                    <label for="" class="control-label"><input type="checkbox" name="has_configured_samples" id="has_configured_samples" > Has Configured Samples</label>
                </div>
                <div class="form-group">
                    <label for="" class="control-label"><input type="checkbox" name="use_existing_sample" id="use_existing_samples" > Use Existing Samples</label>
                </div>
                <div class="form-group">
                    <label for="" class="control-label"><input type="checkbox" name="is_active" id="is_active"> Is Active</label>
                </div>
                <input type="hidden" name="qc_type_id" value="${data && data.id ? data.id : ``}">
            `).clone();
            if(data){
                if(data.has_standards ==1){
                    $(body).find('#has_standards').prop('checked',true);
                }
                if(data.is_active == 1){
                    $(body).find('#is_active').prop('checked',true);
                }
                if(data.has_configured_samples ==1){
                    $(body).find('#has_configured_samples').prop('checked',true);
                }
                if(data.use_existing_sample == 1){
                    $(body).find('#use_existing_samples').prop('checked',true);
                }
            }else{
                $(body).find('#is_active').prop('checked',true);
            }
            return body;
        }
        $('#add-qctypes').on('show.bs.modal',(e)=>{
            var mode = $(e.relatedTarget).data('mode');
            var data = mode == 'add' ? false : $(e.relatedTarget).data('record');
            var body = addQcTypesBody(data);
            $('#add-qctypes').find('.modal-body').empty();
            $('#add-qctypes').find('.modal-body').append(body);
        })
        let addStandardBody = (data=false)=>{
            console.log(data);
            var qcTypes = $('#add-standard').data('qctypes');
            var qcSchemes = $('#add-standard').data('schemes')
            var body = $(`
                <div class="form-group">
                    <label for="" class="control-label">Name</label>
                    <input type="text" value="${data ? data.name : ''}" name="name" placeholder="Name..." class="form-control">
                </div>
                <div class="form-group">
                    <label for="" class="control-label">Code</label>
                    <input type="text" value="${data ? data.code : ''}" name="code" placeholder="Code.." class="form-control">
                </div>
                <div class="form-group">
                    <label for="" class="control-label">Qc Types</label>
                    <select name="qc_type_id" id="" class="form-control qc-select"></select>
                </div>
                <div class="form-group">
                    <label for="" class="control-label">Qc Schemes</label>
                    <select name="qc_scheme_ids[]" multiple id="" class="form-control qc_scheme_id"></select>
                </div>
                <div class="form-group">
                    <label for="" class="control-label"><input type="checkbox" name="is_active" id="is_active"> Is Active</label>
                </div>
                <input type="hidden" name="standard_id" value="${data ? data.id : 0}">
            `).clone()
            if(data){
                if(data.status == 1){
                    $(body).find('#is_active').prop('checked',true)
                }
            }else{
                $(body).find('#is_active').prop('checked',true)
            }
            $.each(qcSchemes,(i,obj)=>{
                var idQcSch = obj.id + '';
                var option = `<option value="${obj.id}" ${data && data.qcschemeidsarr.indexOf(idQcSch) >= 0 ? `selected` : ''} >${obj.name}</option>`;
                $(body).find('.qc_scheme_id').append(option)
            })
            $.each(qcTypes,(i,obj)=>{
                var option = `<option value="${obj.id}" ${data && data.qc_type_id == obj.id ? `selected` : ''} >${obj.name}</option>`;
                $(body).find('.qc-select').append(option)
            });
            $(body).find('.qc-select').select2()
            $(body).find('.qc_scheme_id').select2()
            return body;

        }
        $('#add-standard').on('show.bs.modal',(e)=>{
            $mode = $(e.relatedTarget).data('mode');
            $data = $mode == 'add' ? false : $(e.relatedTarget).data('record');
            var body = addStandardBody($data);
            $('#add-standard').find('.modal-body').empty();
            $('#add-standard').find('.modal-body').append(body);
        });

        let addSchemeBody = (data)=>{
            var body = $(`
                <div class="form-group">
                    <label for="" class="control-label">Name</label>
                    <input type="text" name="name" value="${data ? data.name : ''}" class="form-control">
                </div>
                <div class="form-group">
                    <label for="" class="control-label">Code</label>
                    <input type="text" name="code" value="${data ? data.code : ''}" class="form-control">
                </div>
                <div class="form-group">
                    <label for="" class="control-label"><input type="checkbox" ${data && data.is_active == 1 ? 'checked' : ''} name="is_active" id=""> Is Active</label>
                </div>
                <input type="hidden" name="scheme_id" value="${data ? data.id : 0}">
            `).clone();
            return body;
        }
        let DeleteSchemeBody = (data)=>{
           var body = $(`
                <div class="alert alert-danger p-2 d-flex">
                    <i class="mdi mdi-delete-empty" style="font-size: 24px;"></i>
                    <span class="p-2">Confirm you want to delete ${data.name} Qc Scheme</span>
                </div>
                <input type="hidden" name="scheme_id" value="${data.id}">
           `).clone()
           return body;
        }

        $('#add-scheme').on('show.bs.modal',(e)=>{
            var mode = $(e.relatedTarget).data('mode');
            var data  = mode == 'edit' ? $(e.relatedTarget).data('record') : false;
            var body = addSchemeBody(data);
            $('#add-scheme').find('.modal-body').empty();
            $('#add-scheme').find('.modal-body').append(body);
            $header = mode == 'add' ? `<h5 class="modal-title"><i class="mdi mdi-plus"></i> Add Qc Scheme</h5>` : `<h5 class="modal-title"><i class="mdi mdi-pencil"></i> Edit ${data.name} QC Scheme</h5>`
            $('#add-scheme').find('.modal-header').empty();
            $('#add-scheme').find('.modal-header').append($header);
        });
        $('#delete-scheme').on('show.bs.modal',(e)=>{
            var record = $(e.relatedTarget).data('record');
            var body = DeleteSchemeBody(record);
            $('delete-scheme').find('.modal-body').empty();
            $('delete-scheme').find('.modal-body').append(body);
        })


    })
</script>
@endsection