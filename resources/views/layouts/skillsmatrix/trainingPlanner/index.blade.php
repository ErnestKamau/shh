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
        'link' => route('train.plan.index'),
        'name' => 'Training Plans',
        'icon' => null
    )
);
      ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    <h2 class="p-4">
        <i class="mdi mdi mdi-calendar-account-outline"></i> Training <small class="text-muted"> | Plans </small>
        <span class="btn btn-sm btn-outline-primary float-right" data-target="#add-plan" data-toggle="modal"><i
                class="mdi mdi-plus"></i> Create Plan</span>
    </h2>
    <div class="p-4">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm"
                        style="width:140%">
                        <thead class="bg-light p-2">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Training Needs</th>
                                <th>Roles</th>
                                <th>Has Other Trainings</th>
                                <th>Created By</th>
                                <th>Created At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($plans as $plan)
                                                        <tr>
                                                            <td>
                                                                <span class="btn btn-sm btn-deafault text-primary" data-toggle="modal"
                                                                    data-target="#edit-plan" data-record="{{json_encode($plan)}}"><i
                                                                        class="mdi mdi-pencil" data-toggle="tooltip" title="Edit"></i></span>
                                                                <span class="btn btn-sm btn-default text-danger" data-record="{{json_encode($plan)}}" data-target="#delete-plan" data-toggle="modal"><i class="mdi mdi-delete-empty" data-toggle="tooltip" title="Delete"></i></span>
                                                                <a href="{{route('train.plan.show', ['id' => $plan->id])}}"
                                                                    class="btn btn-sm btn-default text-success"><i class="mdi mdi-eye"></i></a>
                                                            </td>
                                                            <td>{{$plan->name}}</td>
                                                            <td>{{$plan->trainneed->name}}</td>
                                                            <td>
                                                                <?php 
                                                                        $rolenames = [];
                                foreach ($plan->trainneed->users as $t_user) {
                                    array_push($rolenames, $t_user->jobdescription->name);
                                }
                                                                    ?>
                                                                {{implode(', ', $rolenames)}}
                                                            </td>
                                                            <td class="text-center">
                                                                {!! $plan->others->count() > 0 ? '<i class="mdi mdi-checkbox-marked-circle-outline text-success"></i>' : '<i class="mdi mdi-close-circle-outline text-danger"></i>' !!}
                                                            </td>
                                                            <td>{{$plan->creator->name}}</td>
                                                            <td>{{$plan->created_at}}</td>
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
<div class="modal fade" id="add-plan" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{route('train.plan.store')}}" method="post">
                @csrf
                <div class="modal-header">
                    <h5>Add Training Plan</h5>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 form-group">
                            <label for="" class="control-label">Name <small class="text-danger">*</small></label>
                            <input type="text" name="name" placeholder="Training Name..." id="" required
                                class="form-control">
                        </div>
                        <div class="col-md-12 form-group">
                            <label for="" class="control-label">Training Needs <small
                                    class="text-danger">*</small></label>
                            <select name="training_need_header_id" id="" class="form-control skills_matrix_id">
                                <option value="">Select Training Needs</option>
                                @foreach ($needs as $need)
                                    <option value="{{$need->id}}">{{$need->name}}</option>
                                @endforeach
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
<div class="modal fade" id="edit-plan" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{route('train.plan.edit')}}" method="post">
                @csrf
                <div class="modal-body">

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
<div class="modal fade" id="delete-plan" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{route('train.plan.delete')}}" method="post">
                @csrf
                <div class="modal-body">
                   
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="mdi mdi-thumb-up"></i> Yes, Delete</button>
                    <span class="btn btn-sm btn-default" data-dismis="modal">Close</span>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">

    $(document).ready(function () {
        var deletePlanBody = (data)=>{
            var body = $(`
                <div class="alert alert-danger p-2 d-flex">
                    <i class="mdi mdi-delete-empty" style="font-size:20px"></i>
                    <span class="pl-2">Confirm you want to delete ${data.name} Training plan.</span>
                </div>
                <input type="hidden" name="plan_id" value="${data.id}">
            `).clone();
            return body;
        }
        var editPlanBody = (data) => {
            var body = $(`
            <div class="alert alert-primary p-2 d-flex">
                <i class="mdi mdi-pencil-box-outline" style="font-size:20px"></i>
                <span class="pl-2">Edit Training Plan details below:</span>
            </div>
            <div class="form-group">
                <label for="" class="control-label">Name</label>
                <input type="text" name="name" placeholder="Plan Name..." value="${data.name}" id="" class="form-control">
            </div>
            <input type="hidden" name="plan_id" value="${data.id}">
        `).clone();
            return body;
        }
        $('#edit-plan').on('show.bs.modal',(e)=>{
            var data = $(e.relatedTarget).data('record');
            var body  =editPlanBody(data);
            $('#edit-plan').find('.modal-body').empty();
            $('#edit-plan').find('.modal-body').append(body)
        });
        $('#delete-plan').on('show.bs.modal',(e)=>{
            var data = $(e.relatedTarget).data('record');
            var body =deletePlanBody(data);
            $('#delete-plan').find('.modal-body').empty();
            $('#delete-plan').find('.modal-body').append(body);
        })
    });


</script>
@endsection