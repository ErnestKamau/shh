@extends($module == "Skills-Matrix" && $config == 'Roles' ? 'layouts.personnel.layout.app' : 'layouts.skillsmatrix.layout.app', ['dataTable' => true, 'select2' => true])
<?php $module_text = implode(" ", explode("-", $module)); ?>
@section('title2')
<title>{{ $module }} | {{ $module }}</title>
<?php 
    $skillcss = "";
foreach ($skills_proficiency as $s_p) {
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
        'link' => route('matrix'),
        'name' => 'Matrix',
        'icon' => null
    ),
    array(
        'link' => null,
        'name' => $matrix->name,
        'icon' => null
    )
);
      ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    <h2 class="pl-4 pr-4 pb-2">
        <i class="mdi mdi-account-star-outline"></i> Skills Matrix <small class="text-muted"> | {{$matrix->name}}
        </small>
    </h2>
    <div class="pl-4 pr-4">
        <div class="card">
            <div class="card-body">
                <b><u>Matric information</u></b>
                <div class="row border-bottom mt-2 pb-3">
                    <div class="col-md-4">
                        <i class="mdi mdi-chevron-right"></i> <b>Department</b> <br>
                        <span class="pl-3">{{$matrix->department}}</span>

                    </div>
                    <div class="col-md-4">
                        <i class="mdi mdi-chevron-right"></i> <b>Created At</b> <br>
                        <span class="pl-3">{{date('Y-m-d', strtotime($matrix->created_at))}}</span>

                    </div>
                    <div class="col-md-4">
                        <i class="mdi mdi-chevron-right"></i> <b>Status</b> <br>
                        {!! $matrix->status == 1 ? '<span class="badge badge-pill badge-success p-2 ml-3">Active</span>' : '<span class="badge badge-pill badge-danger p-2 ml-3">In Active</span>' !!}
                    </div>
                    <div class="col-md-12 mt-2">
                        <i class="mdi mdi-chevron-right"></i> <b>Roles</b> <br>
                        <span class="pl-3">{{implode(', ', $matrix->jobdescription['names'])}}</span>

                    </div>
                    <div class="col-md-12 mt-2">
                        <i class="mdi mdi-chevron-right"></i> <b>Proficiency Key</b>
                        <div class="d-flex">
                            @foreach ($skills_proficiency as $skp)
                                <div class="proficiency-key p-2 mr-5">
                                    <span class="btn btn-sm btn-default p-2 mr-2" style="background-color:{{$skp->color}}"></span> {{$skp->description}}
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="table-responsive mt-3">
                    <form action="{{route('create-matrix')}}" method="post">
                        @csrf
                        <input type="hidden" name="matrix_id" value="{{$matrix->id}}">
                        <div class="btn-save ">
                            <button type="submit" class="btn btn-sm btn-default bg-light float-right mb-3"><i class="mdi mdi-content-save"></i> Save Matrix</button>
                        </div>
                        <table class="table table-condensed  my-small-text table-bordered">
                            <thead class="bg-light p-2">
                                <tr>
                                    <th style="width:30%" rowspan="2">Area</th>
                                    <th rowspan="2" style="width:65%">Competency Types & Descriptions</th>
                                    <th class="text-center" style="width:5%" colspan="{{$roles->count()}}">
                                        {{$matrix->department}}
                                    </th>
                                </tr>
                                <tr>
                                    @foreach ($roles as $role)
                                        <th>{{$role->jobdescription->name}}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody id="competency-table">
                                <?php $area_checker = ""; $type_checker = "" ?>
                                @foreach($details as $detail)
                                    @if($detail->competencyarea->name != $area_checker)
                                        <tr class="header-fields">
                                            <td class="area-header">
                                                <span class="btn btn-sm btn-default text-danger" data-target="#delete-competency" data-toggle="modal" data-entity="area" data-entityid="{{$detail->competencyarea->id}}" data-area="{{$detail->competencyarea->name}}" ><i data-toggle="tooltip" title="Delete Competency Area" class="mdi mdi-delete-empty"></i></span>
                                                {{$detail->competencyarea->name}}
                                            </td>
                                            <td colspan="{{$roles->count() + 1}}">
                                                <span class="btn btn-sm btn-default text-danger" data-target="#delete-competency" data-toggle="modal" data-entity="type" data-entityid="{{$detail->competencytype->id}}" data-area="{{$detail->competencyarea->name}}" data-type="{{$detail->competencytype->name}}" ><i data-toggle="tooltip" title="Delete Competency Type" class="mdi mdi-delete-empty"></i></span>
                                                {{$detail->competencytype->name}}
                                            </td>
                                        </tr>
                                        <?php $area_checker = $detail->competencyarea->name; $type_checker = $detail->competencytype->name ?>
                                    @endif
                                    @if($detail->competencytype->name != $type_checker)
                                        <tr class="header-fields">
                                            <td></td>
                                            <td colspan="{{$roles->count() + 1}}">
                                                <span class="btn btn-sm btn-default text-danger" data-target="#delete-competency" data-toggle="modal" data-entity="type" data-entityid="{{$detail->competencytype->id}}" data-area="{{$detail->competencyarea->name}}" data-type="{{$detail->competencytype->name}}" ><i data-toggle="tooltip" title="Delete Competency Type" class="mdi mdi-delete-empty"></i></span>
                                                {{$detail->competencytype->name}}
                                            </td>
                                        </tr>
                                        <?php $type_checker = $detail->competencytype->name ?>
                                    @endif
                                    <tr>
                                        <td></td>
                                        <td>
                                            <span class="btn btn-sm btn-default text-danger" data-target="#delete-competency" data-toggle="modal" data-entity="description" data-entityid="{{$detail->id}}" data-area="{{$detail->competencyarea->name}}" data-type="{{$detail->competencytype->name}}" data-description="{{$detail->competencydescription->name}}" ><i data-toggle="tooltip" title="Delete Competency Type" class="mdi mdi-delete-empty"></i></span>
                                            {{$detail->competencydescription->name}}
                                        </td>
                                        @foreach ($detail->roles as $d_role)
                                            <td>
                                                <span class="btn btn-rounded btn-default p-2 {{'sp'.$d_role->proficiency_id}}" data-toggle="modal" data-target="#edit-proficiency" data-record="{{json_encode($d_role->id)}}" data-proficiency="{{$d_role->proficiency_id}}" data-competency="{{$detail->competencydescription->name}}" data-role="{{$d_role->role->name}}"></span>
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                                <tr class="header-fields">
                                    <td class="area-header">
                                        <div class="d-flex">
                                            <div class="add-area-field">
                                                <span class="btn btn-sm btn-default text-warning add-area" data-code="1"><i
                                                        data-toggle="tooltip" data-title="Add Areas"
                                                        class="mdi mdi-plus"></i></span>
                                            </div>
                                            <select name="header_area_id[]" id="" style="width:95%"
                                                class="form-control area1">
                                                <option value="">Select Area</option>
                                                @foreach ($competence_areas as $area)
                                                    <option value="{{$area->id}}">{{$area->description}}</option>
                                                @endforeach
                                            </select>
    
                                        </div>
                                    </td>
                                    <td colspan="{{$roles->count() + 1}}">
                                        <div class="d-flex">
                                            <div class="add-area-field">
                                                <span class="btn btn-sm btn-default text-primary add-type" data-code="1"><i
                                                        data-toggle="tooltip" data-title="Add Competency Type"
                                                        class="mdi mdi-plus"></i></span>
                                            </div>
                                            <select name="header_competency_id[]" id="" class="form-control type1">
                                                <option value="">Select Competency Area</option>
                                                @foreach($competence_types as $c_type)
                                                    <option value="{{$c_type->id}}">{{$c_type->description}}</option>
                                                @endforeach
                                            </select>
                                            <div class="add-area-field">
                                                <span class="btn btn-sm btn-default text-success add-description"><i
                                                        class="mdi mdi-plus" data-toggle="tooltip"
                                                        data-title="Add Competency Description"></i></span>
                                            </div>
    
                                        </div>
    
                                    </td>
    
                                </tr>
                            </tbody>
    
                        </table>

                    </form>
                </div>
            </div>
        </div>

    </div>
</main>
@endsection
@section('script2')
<div class="modal fade" id="delete-competency" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{route('delete-matrix-detail')}}" class="delete-competency-form" method="post">
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

<div class="modal fade" id="edit-proficiency" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{route('edit-matrix-detail-role')}}" method="post">
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

<div class="modal fade" id="give-proficiency" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="#" method="post">
                <div class="modal-body">

                </div>
                <div class="modal-footer">
                    <span class="btn btn-sm btn-outline-primary submit-proficiency"><i class="mdi mdi-content-save"></i>
                        Save</span>
                    <span class="btn btn-sm btn-default" data-dismiss="modal">Close</span>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">

    $(document).ready(function () {
        let skillProficiency = <?php echo json_encode($skills_proficiency); ?>
        
        var spclassarr = [];
        $.each(skillProficiency, (i, obj) => {
            spclassarr.push(`sp${obj.id}`);
        })
        var editproficiencyBody = (competency,role,proficiency,detail_role_id)=>{
            var body = $(`
                <div class="alert alert-primary p-2">Edit ${competency} Competency proficiency for ${role} Role below :</div>
                <div class="form-group">
                    <label for="" class="control-label">Choose Proficiency</label>
                    <select name="proficiency_id" id="" class="form-control proficiency_id">
                       <option value="">Select Proficiency...</option> 
                    </select>
                </div>
                <input type="hidden" name="matrix_detail_role_id" value="${detail_role_id}">

            `).clone();
            
            $.each(skillProficiency,(i,obj)=>{
                var option = `<option value="${obj.id}" >${obj.description}</option>`;
                $(body).find('.proficiency_id').append(option);
            });
            $(body).find('.proficiency_id').val(proficiency);
            $(body).find('.proficiency_id').select2();
            return body;
        }
        $('#edit-proficiency').on('show.bs.modal',(e)=>{
            var competency = $(e.relatedTarget).data('competency');
            var proficiency = $(e.relatedTarget).data('proficiency');
            var detail_role_id = $(e.relatedTarget).data('record');
            var role = $(e.relatedTarget).data('role');
            var body = editproficiencyBody(competency,role,proficiency,detail_role_id);
            $('#edit-proficiency').find('.modal-body').empty();
            $('#edit-proficiency').find('.modal-body').append(body);
        });
        var deleteCompetencyBody = (entity,entity_id,area,c_type=null,c_description = null)=>{
            if(entity == 'area'){
                var body = $(`
                    <div class="alert alert-danger p-2 d-flex">
                        <i class="mdi mdi-delete-empty" style="font-size:30px"></i>
                        <span class="pl-2">Confirm you want to delete competency area ${area}. Note that this action will delete all competencies tied to this area.</span>
                    </div>
                    <input type="hidden" name="entity_id" value="${entity_id}">
                    <input type="hidden" name="matrix_id" value="{{$matrix->id}}">
                    <input type="hidden" name="entity" value="${entity}">
                `).clone();
            }else if(entity == "type"){
                var body  = $(`
                    <div class="alert alert-danger p-2 d-flex">
                        <i class="mdi mdi-delete-empty" style="font-size:30px"></i>
                        <span class="pl-2">Confirm you want to delete Competency Type <b>${c_type}</b> from Competency Area ${area} . Note that this action will delete all competencies tied to this type.</span>
                    </div>
                    <input type="hidden" name="entity_id" value="${entity_id}">
                    <input type="hidden" name="matrix_id" value="{{$matrix->id}}">
                    <input type="hidden" name="entity" value="${entity}">
                `).clone()
            }else{
                var body = $(`
                    <div class="alert alert-danger p-2 d-flex">
                        <i class="mdi mdi-delete-empty" style="font-size:30px"></i>
                        <span class="pl-2">Confirm you want to delete competency <b>${C_description} from Competency Area ${area}</b> . Note that this action will delete all Proficiencies tied to this competencies on the diffrent roles.</span>
                    </div>
                `).clone();
            }
            return body;
        }
        $('#delete-competency').on('show.bs.modal',(e)=>{
            var entity = $(e.relatedTarget).data('entity');
            var area = $(e.relatedTarget).data('area');
            var entity_id = $(e.relatedTarget).data('entityid');
            var c_type =null;
            var c_description = null;
            c_type = entity != "area" ? $(e.relatedTarget).data('type') :c_type;
            c_description = entity == 'description' ? $(e.relatedTarget).data('description') :c_description;
            var body =deleteCompetencyBody(entity,entity_id,area,c_type,c_description);
            $('#delete-competency').find('.modal-body').empty();
            $('#delete-competency').find('.modal-body').append(body);
            // $('#delete-competency').find('.delete-competency-form').on('submit',(e)=>{
            //     e.preventDefault();
            //     for
            // })
        })
        var addDescription = (type_id, area_id, typecode, areacode) => {
            var body = $(`
            <tr>
                <td>
                    <input type="hidden" value="0" name="detail_id[]">
                    <input type="hidden" value="${area_id}" name="area_id[]" class="area${areacode}">
                </td>
                <td>
                    <input type="hidden" name="competency_id[]" class="type${typecode}" value="${type_id}">

                    <div class="d-flex">
                        <span class="btn btn-sm btn-default text-danger remove-description"><i class="mdi mdi-delete-empty"></i></span>
                        <select name="competency_description[]" id="" class="form-control c_description">
                            <option value="">Select Competency Description</option>
                            @foreach ($competence_description as $c_desc)
                                <option value="{{$c_desc->id}}">{{$c_desc->description}}</option>
                            @endforeach
                        </select>
                    </div>
                </td>
                @foreach ($roles as $role)
                    <td class="text-center">
                        <span class="btn btn-rounded btn-default bg-light p-2 proficiency-indicator" data-record="{{json_encode($role)}}" data-toggle="modal" data-target="#give-proficiency"></span>
                        <input type="hidden" class="proficiency_id" name="role[{{$role->id}}][]" value="">
                    </td>
                @endforeach
                
            </tr>
            `).clone();
            $(body).find('.c_description').select2();
            $(body).find('.remove-description').on('click', function () {
                $(this).closest('tr').remove();
            })
            return body;
        }
        $('.add-description').on('click', function () {
            var trElem = $(this).closest('tr');
            var typecode = $(trElem).find('.add-type').data('code');
            var areacode = $(trElem).find('.add-area').data('code');
            var competencyTypeId = $(trElem).find('select[name="header_competency_id[]"]').val();
            var areaId = $(trElem).find('select[name="header_area_id[]"]').val();
            if (competencyTypeId == '' || areaId == '') {
                if (competencyTypeId == '') {
                    alert('Kindly choose the Competency Type first');
                }
                if (areaId == '') {
                    alert('Kindly choose the Competency Area first');
                }
            } else {
                var body = addDescription(competencyTypeId, areaId, typecode, areacode);
                // $('#competency-table').append(body);
                $(this).closest('tr.header-fields').after(body);

            }
        });
        var getAreaBody = (typecode, areacode) => {
            var body = $(`
            <tr class="header-fields">
                <td class="area-header">
                    <div class="d-flex">
                        <div class="add-area-field">
                            <span class="btn btn-sm btn-default text-warning add-area" data-code="${areacode}"><i data-toggle="tooltip"
                                    data-title="Add Areas" class="mdi mdi-plus"></i></span>
                        </div>
                        <select name="header_area_id[]" id="" style="width:95%" class="form-control area${areacode}">
                            <option value="">Select Area</option>
                            @foreach ($competence_areas as $area)
                                <option value="{{$area->id}}">{{$area->description}}</option>
                            @endforeach
                        </select>
                         <div class="add-area-field">
                            <span class="btn btn-sm btn-default text-danger delete-area" data-code="${areacode}"><i data-toggle="tooltip"
                                    data-title="Delete Area" class="mdi mdi-delete-empty"></i></span>
                        </div>

                    </div>
                </td>
                <td colspan="{{$roles->count() + 1}}">
                    <div class="d-flex">
                        <div class="add-area-field">
                            <span class="btn btn-sm btn-default text-primary add-type type${typecode}" data-code="${typecode}"><i data-toggle="tooltip"
                                    data-title="Add Competency Type" class="mdi mdi-plus"></i></span>
                        </div>
                        <select name="header_competency_id[]" id="" class="form-control">
                            <option value="">Select Competency Area</option>
                            @foreach($competence_types as $c_type)
                                <option value="{{$c_type->id}}">{{$c_type->description}}</option>
                            @endforeach
                        </select>
                        <div class="add-area-field">
                            <span class="btn btn-sm btn-default text-success add-description"><i class="mdi mdi-plus"
                                    data-toggle="tooltip" data-title="Add Competency Description"></i></span>
                        </div>

                    </div>

                </td>

            </tr>
            `).clone();

            $(body).find('select[name="header_area_id[]"]').select2();
            $(body).find('select[name="header_competency_id[]"]').select2();

            $(body).find('.add-description').on('click', function () {
                var trElem = $(this).closest('tr');
                var typecode = $(trElem).find('.add-type').data('code');
                var areacode = $(trElem).find('.add-area').data('code');
                var competencyTypeId = $(trElem).find('select[name="header_competency_id[]"]').val();
                var areaId = $(trElem).find('select[name="header_area_id[]"]').val();
                if (competencyTypeId == '' || areaId == '') {
                    if (competencyTypeId == '') {
                        alert('Kindly choose the Competency Type first');
                    }
                    if (areaId == '') {
                        alert('Kindly choose the Competency Area first');
                    }
                } else {
                    var body = addDescription(competencyTypeId, areaId, typecode, areacode);
                    // $('#competency-table').append(body);
                    $(this).closest('tr.header-fields').after(body);

                }
            });

            $(body).find('.add-area').on('click', function () {
                console.log('here area ....');
                var trElem = $(this).closest('tr');
                var typecode = $(trElem).find('.add-type').data('code') + 1;
                var areacode = $(trElem).find('.add-area').data('code') + 1;
                var body = getAreaBody(typecode, areacode);
                $('#competency-table').append(body);
            });
            $(body).find('.delete-area').on('click', function () {
                var areacode = $(this).data('code');
                $(`.area${areacode}`).each(function () {
                    $(this).closest('tr').remove();
                })
            });
            $(body).find('.add-type').on('click', function () {
                console.log('here');
                var trElem = $(this).closest('tr');
                var typecode = $(trElem).find('.add-type').data('code') + 1;
                var areacode = $(trElem).find('.add-area').data('code');
                var area_id = $(trElem).find('select[name="header_area_id[]"]').val()
                console.log('here 2');
                if (area_id == '') {
                    alert('Kindly select the Competency Area first.')
                } else {
                    var body = getTypeBody(typecode, areacode, area_id);
                    console.log('another')
                    $('#competency-table').append(body);
                }

            });

            return body;
        }
        var getTypeBody = (typecode, areacode, area_id) => {
            var body = $(`
            <tr class="header-fields">
                <td>
                    <input type="hidden" value="${area_id}" name="header_area_id[]" class="area${areacode}">
                </td>
                <td colspan="{{$roles->count() + 1}}">
                    <div class="d-flex">
                        <div class="add-area-field d-flex">
                            <span class="btn btn-sm btn-default text-danger delete-type" data-code="${typecode}"><i data-toggle="tooltip"
                                    data-title="Delete Competency Type" class="mdi mdi-delete-empty"></i></span>
                            <span class="btn btn-sm btn-default text-primary add-type type${typecode}" data-code="${typecode}"><i data-toggle="tooltip"
                                    data-title="Add Competency Type" class="mdi mdi-plus"></i></span>
                        </div>
                        <select name="header_competency_id[]" id="" class="form-control">
                            <option value="">Select Competency Area</option>
                            @foreach($competence_types as $c_type)
                                <option value="{{$c_type->id}}">{{$c_type->description}}</option>
                            @endforeach
                        </select>
                        <div class="add-area-field">
                            <span class="btn btn-sm btn-default text-success add-description"><i class="mdi mdi-plus"
                                    data-toggle="tooltip" data-title="Add Competency Description"></i></span>
                        </div>

                    </div>

                </td>

            </tr>
            `).clone();
            $(body).find('.delete-type').on('click', function () {
                $(this).closest('tr').remove();
            })
            $(body).find('select[name="header_competency_id[]"]').select2()

            $(body).find('.add-type').on('click', function () {
                var trElem = $(this).closest('tr');
                var typecode = $(trElem).find('.add-type').data('code') + 1;
                var areacode = $(trElem).find('.add-area').data('code');
                var area_id = $(trElem).find('input[name="header_area_id[]"]').val()
                console.log('here ---------------------- 1')
                if (area_id == '') {
                    alert('Kindly select the Competency Area first.')
                } else {
                    var body = getTypeBody(typecode, areacode, area_id);
                    console.log('another')
                    $('#competency-table').append(body);
                }

            });
            $(body).find('.add-description').on('click', function () {
                var trElem = $(this).closest('tr');
                var typecode = $(trElem).find('.add-type').data('code');
                var areacode = $(trElem).find('.add-area').data('code');
                var competencyTypeId = $(trElem).find('select[name="header_competency_id[]"]').val();
                var areaId = $(trElem).find('input[name="header_area_id[]"]').val();
                if (competencyTypeId == '' || areaId == '') {
                    if (competencyTypeId == '') {
                        alert('Kindly choose the Competency Type first');
                    }
                    if (areaId == '') {
                        alert('Kindly choose the Competency Area first');
                    }
                } else {
                    var body = addDescription(competencyTypeId, areaId, typecode, areacode);
                    // $('#competency-table').append(body);
                    $(this).closest('tr.header-fields').after(body);

                }
            });
            return body;
        }
        $('.add-area').on('click', function () {
            console.log('here area ....');
            var trElem = $(this).closest('tr');
            var typecode = $(trElem).find('.add-type').data('code') + 1;
            var areacode = $(trElem).find('.add-area').data('code') + 1;
            var body = getAreaBody(typecode, areacode);
            $('#competency-table').append(body);
        });
        $('.delete-area').on('click', function () {
            var areacode = $(this).data('code');
            $(`.area${areacode}`).each(function () {
                $(this).closest('tr').remove();
            })
        });
        $('.add-type').on('click', function () {
            var trElem = $(this).closest('tr');
            var typecode = $(trElem).find('.add-type').data('code') + 1;
            var areacode = $(trElem).find('.add-area').data('code');
            
            var area_id = $(trElem).find('select[name="header_area_id[]"]').val()
            console.log('here 3')
            if (area_id == '') {
                alert('Kindly select the Competency Area first.')
            } else {
                var body = getTypeBody(typecode, areacode, area_id);
                console.log('another')
                $('#competency-table').append(body);
            }

        });
        var getproficiencyBody = (data) => {
            var body = $(`
                <div class="alert alert-primary p-2">Select skill proficiency for <b>${data.jobdescription.name}</b> </div>
                <div class="form-group">
                    <label for="" class="control-label">Choose Proficiency</label>
                    <select name="proficiency_id" id="" class="form-control">
                        <option value="">Select Proficiency</option>
                        @foreach($skills_proficiency as $sp)
                            <option value="{{$sp->id}}" style="background-color:{{$sp->color}}" data-color="{{$sp->color}}">{{$sp->description}}</option>
                        @endforeach
                    </select>
                </div>
            `).clone();
            $(body).find('select[name="proficiency_id"]').select2();
            return body;
        }
        $('#give-proficiency').on('show.bs.modal', function (e) {
            var data = $(e.relatedTarget).data('record');
            var tdElem = $(e.relatedTarget).closest('td');

            var body = getproficiencyBody(data);
            $('#give-proficiency').find('.modal-body').empty();
            $('#give-proficiency').find('.modal-body').append(body);
            $('#give-proficiency').find('.submit-proficiency').one('click', (e) => {
                var proficiency_id = $('#give-proficiency').find('select[name="proficiency_id"]').val();
                var spclass = `sp${proficiency_id}`;

                $(tdElem).find('.proficiency-indicator').removeClass('bg-light');

                $.each(spclassarr, (i, obj) => {
                    $(tdElem).find('.proficiency-indicator').removeClass(obj);
                })

                $(tdElem).find('.proficiency-indicator').addClass(spclass);
                $(tdElem).find('.proficiency_id').val(proficiency_id);
                $('#give-proficiency').modal('hide');
            });
        });

    });


</script>
@endsection