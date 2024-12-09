@extends($module == "Skills-Matrix" && $config == 'Roles' ? 'layouts.personnel.layout.app' : 'layouts.skillsmatrix.layout.app', ['dataTable' => true, 'select2' => true])
<?php $module_text = implode(" ", explode("-", $module)); ?>
@section('title2')
<title>{{ $module }} | {{ $module }}</title>
@endsection
@section('content2')
<main>
    <?php
$items = array(
    array(
        'link' => route('capability-index'),
        'name' => 'Capability Matrix',
        'icon' => null
    )
);
      ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    <h2 class="p-4">
        <i class="mdi mdi mdi-account-check-outline"></i> Capabilty <small class="text-muted"> | Matrix </small>
        <span class="btn btn-sm btn-outline-primary float-right" data-target="#add-capability" data-toggle="modal"><i
                class="mdi mdi-plus"></i> Create Matrix</span>
    </h2>
    <div class="p-4">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table
                        class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
                        <thead class="bg-light p-2">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Skills Matrix</th>
                                <th>Roles</th>
                                <th>Created By</th>
                                <th>Created At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($capabilities as $capability)
                                <tr>
                                    <td>
                                        <span class="btn btn-sm btn-deafault text-primary" data-toggle="modal" data-target="#edit-capability" data-record="{{json_encode($capability)}}"><i class="mdi mdi-pencil"></i></span>
                                        <a href="{{route('capability.show',['id'=>$capability->id])}}" class="btn btn-sm btn-default text-success"><i class="mdi mdi-eye"></i></a>
                                    </td>
                                    <td>{{$capability->name}}</td>
                                    <td>{{$capability->skillmatrix->name}}</td>
                                    <td>
                                        <?php 
                                        $rolenames = [];
                                        foreach($capability->grouproles as $g_role){
                                            array_push($rolenames,$g_role->jobdescription->name);
                                        }
                                        ?>
                                        {{implode(', ',$rolenames)}}
                                    </td>
                                    <td>{{$capability->creator->name}}</td>
                                    <td>{{$capability->created_at}}</td>
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
<div class="modal fade" id="add-capability" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{route('capability.add')}}" method="post">
                @csrf
                <div class="modal-header">
                    <h5>Add Capability Matrix</h5>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="" class="control-label">Name <small class="text-danger">*</small></label>
                            <input type="text" name="name" placeholder="Matrix Name..." id="" required
                                class="form-control">
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="" class="control-label">Skills Matrix <small
                                    class="text-danger">*</small></label>
                            <select name="skills_matrix_id" id="" class="form-control skills_matrix_id">
                                <option value="">Select Skills Matrix</option>
                                @foreach ($skillmatrixs as $s_matrix)
                                    <option value="{{$s_matrix->id}}">{{$s_matrix->name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12 form-group">
                            <label for="" class="control-label">Roles</label>
                            <select name="role_id[]" multiple id="" class="form-control role_id">
                                <option value="">Select Skills Matrix First...</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <p><u>Personnel Selected</u></p>
                            <div class="table-responsive">
                                <table class="table table-bordered table-condensed">
                                    <thead>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Role</th>
                                        <th>Code</th>
                                    </thead>
                                    <tbody class="personnel-tbody">

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-sm btn-outline-primary"><i class="mdi mdi-content-save"></i>
                        Save</button>
                    <span class="btn btn-sm btn-default" data-dismiss="modal">Close</span>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">

    $(document).ready(function () {
        var getMatrixRoles = (matrix_id, callback) => {
            $.ajax({
                url: `/matrix/get/role/${matrix_id}/ajax`,
                type: 'GET',
                success: (data) => {
                    callback(data);
                },
                error: (err) => {
                    console.log(err)
                    callback('err');
                }
            })
        }
        var getUsersByPosition = (positions, callback) => {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                url: `/matrix/get/user/position/ajax`,
                type: `POST`,
                dataType: 'json',
                data: {
                    positions: positions
                },
                success: (data) => {
                    callback(data);
                },
                error: (err) => {
                    console.log(err);
                    callback('err');
                }
            })
        }
        var getPersonnelTr = (user,obj,loop)=>{
            console.log(loop)
            var tr = $(`
            <tr>
                <td><span class="btn btn-sm btn-default remove-staff" data-user="${user.name}"><i data-toggle="tooltip" title="Remove Staff" class="mdi mdi-delete-empty"></i></span></td>
                <td>${user.name}</td>
                <td>${obj.jobdescription.name}</td>
                <td>
                    <div class="form-group">
                        <input type="text" name="code[]" value="${obj.jobdescription.name.replaceAll(" ","")}${loop == 1 ? "" : '-'+loop}" id="" class="form-control">
                        <input type="hidden" name="user_id[]" value="${user.id}">
                        <input type="hidden" name="jobdescription_id[]" value="${user.position}">
                        <input type="hidden" name="skill_matrix_role_id[]" value="${obj.id}">
                        <input type="hidden" name="capability_user_id[]" value="0">

                    </div>

                </td>
            </tr>
            `).clone();
            $(tr).find('.remove-staff').on('click',function(){
                var name = $(this).data('user');
                if(confirm(`Confirm you want to remove ${name} from this capability matrix`)){
                    $(this).closest('tr').remove();
                }else{
                    alert('Action cancelled!');
                }
            })
            return tr;
        }
        $('#add-capability').on('show.bs.modal', (e) => {
            $('#add-capability').find('.skills_matrix_id').on('change', (e) => {
                var matrix_id = $('#add-capability').find('.skills_matrix_id').val();
                getMatrixRoles(matrix_id, (data) => {
                    if (data != 'err') {
                        $('#add-capability').find('.role_id').empty();
                        $('#add-capability').find('.role_id').append(`<option value="">Select Role</option>`);
                        $('#add-capability').find('.personnel-tbody').empty();
                        $.each(data['roles'], (i, obj) => {
                            var option = `<option value="${obj.id}" selected >${obj.jobdescription.name}</option>`;
                            $('#add-capability').find('.role_id').append(option);
                            var loop = 1;
                            $.each(obj.users,(e,user)=>{
                                var tr = getPersonnelTr(user,obj,loop)
                                ++loop;
                                $('#add-capability').find('.personnel-tbody').append(tr);
                            })
                        });
                        
                        

                    }
                })
            });
            $('#add-capability').find('.role_id').on('change', (e) => {
                var positions = $('#add-capability').find('.role_id').val();
                console.log('am here');
                getUsersByPosition(positions, (data) => {
                    if (data != 'err') {
                        $('#add-capability').find('.personnel-tbody').empty()
                        $.each(data['roles'], (i, obj) => {
                            var loop = 1;
                            $.each(obj.users,(e,user)=>{
                                var tr = getPersonnelTr(user,obj,loop)
                                $('#add-capability').find('.personnel-tbody').append(tr);
                                ++loop;
                            })
                        });
                    }
                })
            })
            

        })
    });


</script>
@endsection