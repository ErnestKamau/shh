@extends('layouts.skillsmatrix.layout.app', ['dataTable' => true, 'select2' => true])
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
        'link' => route('train.needs.index'),
        'name' => 'Training Needs',
        'icon' => null
    ),
    array(
        'link' => route('train.needs.show',['id'=>$train_header->id]),
        'name' => $train_header->name,
        'icon' => null
    )
);
      ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    <h2 class="p-4">
        <i class="mdi mdi mdi-account-alert-outline"></i> Training Needs <small class="text-muted"> | {{$train_header->name}} </small>
    </h2>
    <div class="pl-4 pr-4">
        <form action="{{route('train.needs.show',['id'=>$train_header->id])}}" method="post" class="card">
            @csrf
            <div class="row mt-2 card-body">
                <div class="col-md-12">
                    <p><b><u>Training Needs Information </u></b></p> 
                </div>
                <div class="col-md-12 p-2">
                    <b class="text-muted"><i class="mdi mdi-chevron-right"></i> Capability Matrix</b> <br>
                    <span class="pl-3">{{$train_header->capability->name}}</span>
                </div>
                <div class="col-md-4 p-2">
                    <b class="text-muted"><i class="mdi mdi-chevron-right"></i> Created By</b> <br>
                    <span class="pl-3">{{$train_header->creator->name}}</span>
                </div>
                <div class="col-md-4 p-2">
                    <b class="text-muted"><i class="mdi mdi-chevron-right"></i> Created At</b> <br>
                    <span class="pl-3">{{$train_header->created_at}}</span>
                </div>
                <div class="col-md-4 p-2">
                    <b class="text-muted"><i class="mdi mdi-chevron-right"></i> No of Staff</b> <br>
                    <span class="pl-3">{{$train_header->users->count()}}</span>
                </div>
                <div class="col-md-12 p-2">
                    <b class="text-muted"><i class="mdi mdi-chevron-right"></i> Proficiencies Key</b> <br>
                    <div class="d-flex mt-2">
                        @foreach ($proficiencies as $proficiency)
                            <div class="prof {{$loop->iteration == 1 ? 'pl-3' : 'pl-5'}}">
                                <span class="btn btn-sm btn-default p-2 sp{{$proficiency->id}}"></span> <span class="pl-3">{{$proficiency->description}}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="col-md-12 pb-3 border-bottom">
                    <b class="text-muted"><i class="mdi mdi-chevron-right"></i> Roles</b> <br>
                    <?php 
                        $rolenames = [];
                        foreach($train_header->users as $t_user){
                            array_push($rolenames,$t_user->jobdescription->name);
                        }
                    ?>                 
                    <span class="pl-3">{{implode(', ',$rolenames)}}</span>
                </div>
                <div class="col-md-12 mt-3">
                    <p><u><b>Apply Filter</b></u></p>
                </div>
                <div class="col-md-9">
                    <div class="form-group">
                        <label for="" class="control-label">Choose Staff</label>
                        <select name="user_ids[]" multiple id="" class="form-control">
                            <option value="">Choose staff...</option>
                            @foreach ($train_header->users as $g_role)
                                <option value="{{$g_role->id}}" {{count($selectedUsers) > 0 ? (in_array($g_role->id,$selectedUsers) ? 'selected' : '') : ($loop->iteration <= 10 ? 'selected' : '') }} >{{$g_role->user->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-sm btn-outline-primary mt-4 float-right"><i class="mdi mdi-filter-outline"></i> Apply</button>
                </div>
            </div>

        </form>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table
                        class="table table-condensed table-bordered">
                        <thead class="bg-light p-2">
                            <tr>
                                <th>Area</th>
                                <th>Competencies</th>
                                @foreach($train_header->users as $role)
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
                            </tr>
                        </thead>
                        <tbody>
                            <?php $area = "";$type=""; ?>
                            @foreach ($train_header->details as $detail )
                                @if($detail->capabilitydetail->competency->competencyarea->name != $area)
                                <tr class="header-fields">
                                    <td class="area-header">{{$detail->capabilitydetail->competency->competencyarea->name }}</td>
                                    <td>{{$detail->capabilitydetail->competency->competencytype->name }}</td>
                                    <td colspan="{{count($selectedUsers) > 0 ? count($selectedUsers) +1 : 11}}"></td>
                                    <?php $area = $detail->capabilitydetail->competency->competencyarea->name;$type=$detail->capabilitydetail->competency->competencytype->name; ?>
                                </tr>
                                @endif
                                @if($detail->capabilitydetail->competency->competencytype->name != $type)
                                <tr class="header-fields">
                                    <td></td>
                                    <td>{{$detail->capabilitydetail->competency->competencytype->name }}</td>
                                    <td colspan="{{count($selectedUsers) > 0 ? count($selectedUsers) +1 : 11}}"></td>
                                    <?php $type=$detail->capabilitydetail->competency->competencytype->name; ?>
                                </tr>
                                @endif
                                <tr>
                                    <td></td>
                                    <td>{{$detail->capabilitydetail->competency->competencydescription->name}}</td>
                                    @foreach($train_header->users as $role)
                                        @if(count($selectedUsers) > 0)
                                            @if(in_array($role->id,$selectedUsers))
                                                <td><span class="btn btn-rounded btn-default p-2 {{in_array($role->id,$detail->needtraining) ? 'sp'.$proficiencies[0]->id : 'sp'.$proficiencies[1]->id }}" data-toggle="tooltip" title="{{in_array($role->id,$detail->needtraining) ? $proficiencies[0]->description : $proficiencies[1]->description   }}" ></span></td>
                                            @endif
                                        @else
                                            @if($loop->iteration <= 10)
                                            <td><span class="btn btn-rounded btn-default p-2 {{in_array($role->id,$detail->needtraining) ? 'sp'.$proficiencies[0]->id : 'sp'.$proficiencies[1]->id }}" data-toggle="tooltip" title="{{in_array($role->id,$detail->needtraining) ? $proficiencies[0]->description : $proficiencies[1]->description   }}"></span></td>
                                            @endif 
                                        @endif
                                    @endforeach
                                    
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
<div class="modal fade" id="add-proficiency" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="#" method="post">
                <div class="modal-body">
                    
                </div>
                <div class="modal-footer">
                    <span class="btn btn-outline-primary btn-sm submit-proficiency"><i class="mdi mdi-content-save"></i> Save</span>
                    <span class="btn btn-sm btn-default" data-dismiss="modal">Close</span>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">

    $(document).ready(function () {
        let skillProficiency = <?php echo json_encode($proficiencies); ?>
        // var skillProficiency = [];
        var spclassarr = [];
        $.each(skillProficiency, (i, obj) => {
            spclassarr.push(`sp${obj.id}`);
        });
        var addproficiencyBody = (name,competency)=>{
            var body = $(`
                <div class="alert alert-primary p-2 d-flex">
                    <i class="mdi mdi-alert-decagram-outline"></i>
                    <span class="pl-2">Select the capability proficiency for <b>${name}</b> for <b>${competency}</b> competency</span>
                </div>
                <div class="form-group">
                    <label for="" class="control-label">Choose Proficiency</label>
                    <select name="proficiency_id" class="proficiency_id">
                        <option value="">Select Proficiency</option>
                        @foreach ($proficiencies as $proficiency)
                            <option value="{{$proficiency->id}}">{{$proficiency->description}}</option>
                        @endforeach
                    </select>
                </div>
            `).clone();
            $(body).find('.proficiency_id').select2();
            return body;
        }
        $('#add-proficiency').on('show.bs.modal',(e)=>{
            var tdElem = $(e.relatedTarget).closest('td');
            var body = addproficiencyBody();
            $('#add-proficiency').find('.modal-body').empty();
            $('#add-proficiency').find('.modal-body').append(body);
            console.log('hereeee   0')
            $('#add-proficiency').find('.submit-proficiency').one('click',()=>{
                console.log('here ---')
                var proficiency_id = $('#add-proficiency').find('.proficiency_id').val();
                $.each(spclassarr, (i, obj) => {
                    $(tdElem).find('.proficiency-indicator').removeClass(obj);
                })
                $(tdElem).find('.proficiency-indicator').removeClass('bg-light')
                console.log(proficiency_id);

                $(tdElem).find('.proficiency-indicator').addClass(`sp${proficiency_id}`);
                $(tdElem).find('.proficiency_id').val(proficiency_id);
                $('#add-proficiency').modal('hide');
            });

        })
    });


</script>
@endsection