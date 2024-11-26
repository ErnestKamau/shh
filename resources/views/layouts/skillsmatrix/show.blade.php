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
                    <div class="col-md-12">
                        <i class="mdi mdi-chevron-right"></i> <b>Roles</b> <br>
                        <span class="pl-3">{{implode(', ', $matrix->jobdescription['names'])}}</span>
                        
                    </div>
                </div>

                <div class="table-responsive mt-3">
                    <table class="table table-condensed my-small-text table-striped table-hover table-bordered">
                        <thead class="bg-light p-2">
                            <tr>
                                <th style="width:20%" rowspan="2">Area</th>
                                <th rowspan="2">Competency</th>
                                <th class="text-center" style="width:20%" colspan="{{$roles->count()}}">{{$matrix->department}}</th>
                            </tr>
                            <tr>
                                @foreach ($roles as $role)
                                    <th>{{$role->jobdescription->name}}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <select name="area_id" id="" class="form-control">
                                        <option value="">Select Area</option>
                                        @foreach ($competence_areas as $area)
                                            <option value="{{$area->id}}">{{$area->description}}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <select name="competency_id" id="" class="form-control">
                                        <option value="">Select Competency Area</option>
                                        @foreach($competence_types as $c_type)
                                        <option value="{{$c_type->id}}">{{$c_type->description}}</option>
                                        @endforeach
                                    </select>
                                    
                                </td>
                                @foreach ($roles as $role)
                                    <td class="text-center"><span class="btn btn-rounded btn-default bg-light p-2" data-toggle="modal" data-target="#give-proficiency"></span></td>
                                @endforeach
                            </tr>
                        </tbody>

                    </table>
                </div>
            </div>
        </div>

    </div>
</main>
@endsection
@section('script2')

<div class="modal fade" id="give-proficiency" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <div class="form-group">
                    <label for="" class="control-label">Choose Proficiency</label>
                    <select name="proficiency_id" id="" class="form-control">
                        @foreach($skills_proficiency as $sp)
                            <option value="{{$sp->id}}"><span class="btn btn btn-sm btn-default p-2" style="background-color:{{$sp->color}}"></span></option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">

    $(document).ready(function () {

    });


</script>
@endsection