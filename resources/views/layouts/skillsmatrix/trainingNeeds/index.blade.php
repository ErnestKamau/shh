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
        'link' => route('train.needs.index'),
        'name' => 'Training Needs',
        'icon' => null
    )
);
      ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    <h2 class="p-4">
        <i class="mdi mdi-account-alert-outline"></i> Training <small class="text-muted"> | Needs </small>
        <span class="btn btn-sm btn-outline-primary float-right" data-target="#add-need" data-toggle="modal"><i
                class="mdi mdi-plus"></i> Create Training Need</span>
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
                                <th>Capability Matrix</th>
                                <th>Roles</th>
                                <th>No of Users</th>
                                <th>Created By</th>
                                <th>Created At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($trainings as $train)
                               <tr>
                                    <td>
                                        <span class="btn btn-sm btn-default text-danger"><i class="mdi mdi-delete-empty"></i></span>
                                        <a href="{{route('train.needs.show',['id'=>$train->id])}}" class="btn btn-sm btn-default text-success"><i class="mdi mdi-eye"></i></a>
                                    </td>
                                    <td>{{$train->name}}</td>
                                    <td>{{$train->capability->name}}</td>
                                    <td>
                                        <?php 
                                            $rolenames = [];
                                            foreach($train->users as $t_user){
                                                array_push($rolenames,$t_user->jobdescription->name);
                                            }
                                        ?>
                                        {{implode(', ', array_unique($rolenames))}}
                                    </td>
                                    <td>{{$train->users->count()}}</td>
                                    <td>{{$train->creator->name}}</td>
                                    <td>{{$train->created_at}}</td>
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
<div class="modal fade" id="add-need" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{route('train.needs.store')}}" method="post">
                @csrf
                <div class="modal-header">
                    <h5>Add Training Need</h5>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 form-group">
                            <label for="" class="control-label">Name <small class="text-danger">*</small></label>
                            <input type="text" name="name" placeholder="Training Need Name..." id="" required
                                class="form-control">
                                <input type="hidden" name="training_header_id" value="0">
                        </div>
                        <div class="col-md-12 form-group">
                            <label for="" class="control-label">Cpabaility Matrix <small
                                    class="text-danger">*</small></label>
                            <select name="capability_id" id="" class="form-control capability_id">
                                <option value="">Select Capability Matrix </option>
                                @foreach ($capabilities as $capability)
                                    <option value="{{$capability->id}}">{{$capability->name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12 form-group">
                            <label for="" class="control-label">Users</label>
                            <select name="user_id[]" multiple id="" class="form-control user_id">
                                <option value="">Select Capability Matrix First...</option>
                            </select>
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
        var getCapabilityUsers = (matrix_id, callback) => {
            $.ajax({
                url: `/matrix/get-capability-users/${matrix_id}`,
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
       
        $('#add-need').on('show.bs.modal',()=>{
            $('#add-need').find('.capability_id').on('change',(e)=>{
                var capability_id = $('#add-need').find('.capability_id').val();
                getCapabilityUsers(capability_id,(data)=>{
                    if(data != 'err'){
                        $('#add-need').find('.user_id').empty();
                        $('#add-need').find('.user_id').append(`<option value="" >Select Staff...</option>`)
                        $.each(data,(i,obj)=>{
                            var option = `<option value="${obj.id}">${obj.user.name}</option>`;
                            $('#add-need').find('.user_id').append(option);
                        });
                        $('#add-need').find('.user_id').select2();
                    }
                })
            })
        });

    });


</script>
@endsection