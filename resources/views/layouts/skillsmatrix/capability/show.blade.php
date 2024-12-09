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
        'link' => route('capability-index'),
        'name' => 'Capability Matrix',
        'icon' => null
    ),
    array(
        'link' => route('capability.show',['id'=>$capability->id]),
        'name' => $capability->name,
        'icon' => null
    )
);
      ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    <h2 class="p-4">
        <i class="mdi mdi mdi-account-check-outline"></i> Capabilty <small class="text-muted"> | {{$capability->name}} </small>
    </h2>
    <div class="pl-4 pr-4">
        <form action="{{route('capability.show',['id'=>$capability->id])}}" method="post" class="card">
            @csrf
            <div class="row mt-2 card-body">
                <div class="col-md-12">
                    <p><b><u>Matrix Information </u></b></p> 
                </div>
                <div class="col-md-4 p-2">
                    <b class="text-muted"><i class="mdi mdi-chevron-right"></i> Created By</b> <br>
                    <span class="pl-3">{{$capability->creator->name}}</span>
                </div>
                <div class="col-md-4 p-2">
                    <b class="text-muted"><i class="mdi mdi-chevron-right"></i> Created At</b> <br>
                    <span class="pl-3">{{$capability->created_at}}</span>
                </div>
                <div class="col-md-4 p-2">
                    <b class="text-muted"><i class="mdi mdi-chevron-right"></i> No of Staff</b> <br>
                    <span class="pl-3">{{sizeof($capability->roles)}}</span>
                </div>
                <div class="col-md-12 pb-3 border-bottom">
                    <b class="text-muted"><i class="mdi mdi-chevron-right"></i> Roles</b> <br>
                    <span class="pl-3">{{implode(', ',$capability_roles)}}</span>
                </div>
                <div class="col-md-12 mt-3">
                    <p><u><b>Apply Filter</b></u></p>
                </div>
                <div class="col-md-9">
                    <div class="form-group">
                        <label for="" class="control-label">Choose Staff</label>
                        <select name="role_id[]" multiple id="" class="form-control">
                            <option value="">Choose staff...</option>
                            @foreach ($capability->roles as $g_role)
                                <option value="{{$g_role->id}}" {{count($selectedUsers) > 0 ? (in_array($g_role->id,$selectedUsers) ? 'selected' : '') : ($loop->iteration <= 10 ? 'selected' : '') }} >{{$g_role->user->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-sm btn-outline-primary mt-5 float-right"><i class="mdi mdi-filter-outline"></i> Apply</button>
                </div>
            </div>

        </form>
        <div class="card">
            <div class="card-body">
                <form action="{{route('capability.detail.store')}}" method="post">
                    @csrf
                    <input type="hidden" name="capability_id" value="{{$capability->id}}">
                    <div class="submit-header">
                        <button type="submit" class="btn btn-outline-primary btn-sm float-right mb-3"><i class="mdi mdi-content-save"></i> Save Matrix</button>
                    </div>
                    <div class="table-responsive">
                        <table
                            class="table table-condensed table-bordered">
                            <thead class="bg-light p-2">
                                <tr>
                                    <th>#</th>
                                    <th>Area</th>
                                    <th>Competencies</th>
                                    @foreach($capability->roles as $role)
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
                               @foreach ($competencies as $competency)
                                    @if($area != $competency->competencyarea->name)
                                        <tr class="header-fields">
                                            <td class="area-header" ></td>
                                            <td class="area-header">{{$competency->competencyarea->name}}</td>
                                            <td colspan="{{count($selectedUsers) > 0 ? count($selectedUsers) +1 : 11}}">{{$competency->competencytype->name}}</td>
                                            <?php $area = $competency->competencyarea->name; $type =  $competency->competencytype->name  ?>
                                        </tr>
                                    @endif
                                    @if($type != $competency->competencytype->name)
                                        <tr class="header-fields">
                                            <td></td>
                                            <td></td>
                                            <td colspan="{{count($selectedUsers) > 0 ? count($selectedUsers) + 1 : 11}}">{{$competency->competencytype->name}}</td>
                                            <?php $type =  $competency->competencytype->name  ?>
                                        </tr>
                                    @endif
                                    <tr>
                                        <td></td>
                                        <td>
                                        </td>
                                        <td>{{$competency->competencydescription->name}}</td>
                                        @foreach ($capability->roles as $c_role)
                                            @if(count($selectedUsers) > 0)
                                                @if(in_array($c_role->id,$selectedUsers))
                                                <td>
                                                    <input type="hidden" name="capability_user_id[]" value="{{$c_role->id}}">
                                                    
                                                    <input type="hidden" name="competency_id[]" value="{{$competency->id}}">

                                                    <input type="hidden" name="skill_matrix_role_id[]" value="{{$c_role->skill_matrix_role_id}}">
                                                    
                                                    <?php
                                                        $usersInCompetency = $competencyUserIds[$competency->id] ?? [];

                                                        // Find the user with the specified user_id
                                                        $proficientuser = collect($usersInCompetency)->firstWhere('user_id', $c_role->id);
                                                        
                                                        if ($proficientuser) {
                                                            $proficiencyClass= $proficientuser['proficiency_id'];
                                                            $existProficientId = $proficientuser['id'];
                                                        } else {
                                                            $proficiencyClass = null;
                                                            $existProficientId = null;
                                                        } 
                                                     ?>
                                                     <input type="hidden" name="proficiency_id[]" class="proficiency_id" value="{{$proficiencyClass  ? $proficiencyClass : 0 }}">
                                                     <input type="hidden" name="competency_detail[]" value="{{$existProficientId ? $existProficientId : 0 }}">
                                                    <span class="btn btn-rounded btn-default p-2 {{$proficiencyClass ? 'sp'.$proficiencyClass : 'bg-light'}} proficiency-indicator" data-toggle="modal" data-target="#add-proficiency" data-competency="{{$competency->competencydescription->name}}" data-user="{{$c_role->user->name}}"></span>
                                                </td>
                                                @endif
                                            @else
                                                @if($loop->iteration <= 10)
                                                    <td>
                                                        <?php
                                                            $usersInCompetency = $competencyUserIds[$competency->id] ?? [];

                                                            // Find the user with the specified user_id
                                                            $proficientuser = collect($usersInCompetency)->firstWhere('user_id', $c_role->id);
                                                            
                                                            if ($proficientuser) {
                                                                $proficiencyClass= $proficientuser['proficiency_id'];
                                                                $existProficientId = $proficientuser['id'];
                                                            } else {
                                                                $proficiencyClass = null;
                                                                $existProficientId = null;
                                                            } 
                                                        ?>
                                                        <input type="hidden" name="capability_user_id[]" value="{{$c_role->id}}">
                                                        <input type="hidden" name="proficiency_id[]" class="proficiency_id" value="">
                                                        <input type="hidden" name="skill_matrix_role_id[]" value="{{$c_role->skill_matrix_role_id}}">
                                                        <input type="hidden" name="competency_detail[]" value="{{$existProficientId ? $existProficientId : 0 }}">
                                                        <span class="btn btn-rounded btn-default p-2 {{$proficiencyClass ? 'sp'.$proficiencyClass : 'bg-light'}} proficiency-indicator" data-toggle="modal" data-target="#add-proficiency" data-competency="{{$competency->competencydescription->name}}" data-user="{{$c_role->user->name}}"></span>
                                                    </td>
                                                @endif 
                                            @endif
                                        
                                        @endforeach
                                    </tr>
                               
                               @endforeach
                            </tbody>
                        </table>
                    </div>
                </form>
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