@extends('layouts.personnel.layout.app', ['dataTable'=>true, 'select2'=>true])
@section('title2')
<title>Job Description Responsibilities</title>
<style type="text/css">
    .no-header th {
        color: #454545;
    }

    .hidden {
        display: none;
    }
</style>
@endsection
@section('content2')
<main>
    <?php
    $items = array(
        array(
            'link' => route('personnel-home'),
            'name' => 'Personnel Managment',
            'icon' => null
        ),
        array(
            'link' => null,
            'name' => 'Configurations',
            'icon' => null
        ),
        array(
            'link' => '/module-pre-configs/Job Description/Personnel-Management',
            'name' => 'Job Designation',
            'icon' => null
        ),
        array(
            'link' => null,
            'name' => $designation->name,
            'icon' => null
        )
    );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    <h2 class="p-4">
        <i class="mdi mdi-format-list-bulleted-type"></i>{{ $designation->name }} Responsibilities
        <button class="btn btn-outline-primary btn-sm float-right" data-toggle="modal" data-target="#add-job-responsibility"><i class="mdi mdi-plus"></i> Add</button>
    </h2>

    <div class="card tab-card">

        <div class="card-header tab-card-header">
            <ul class="nav nav-tabs card-header-tabs" id="asset-tabs" role="tablist">
                <li class="nav-item">
                    <a href="#active-responsibility" class="nav-link active" id="active-responsibility-tab" data-toggle="tab" role="tab" aria-controls="active-responsibility" aria-selected="true"> <i class="mdi mdi-layers" style="color: black;font-size:15px"></i> Active Responsibility</a>
                </li>
                <li class="nav-item">
                    <a href="#inactive-responsibility" class="nav-link" id="inactive-responsibilty-tab" data-toggle="tab" role="tab" aria-controls="asset-type-tab" aria-selected="true"> <i class="mdi mdi-layers-off" style="color: black; font-size:15px"></i> Inactive Responsibility</a>
                </li>

            </ul>
        </div>



        <div class="tab-content" id="responsibility-tabs-content">
            <div class="tab-pane fade show active p-3" id="active-responsibility" role="tabpanel" aria-labelledby="one-tab">
                <h5 class="card-title">Active Responsibilities</h5>
                <div class="table-responsive bg-light p-4">
                    <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
                        <thead class="bg-light p-2">
                            <tr>
                                <th>No</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Created At</th>
                                <th>Edited By</th>
                                <th nowrap>Active</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($responsibility as $item)
                            @if($item->active == 1)
                            <tr>
                                <td valign="center">{{ $loop->iteration }}</td>

                                <td nowrap>{{ $item->name }}</td>
                                <td>{{ $item->description }}</td>
                                <td>{{$item->created_at}}</td>
                                <td>{{$item->edited_by == '' ? 'N/a':$item->edited}}</td>
                                <td class="text-small">{!! $item->active == '1' ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>' !!}</td>
                                <!-- -----  -->
                                <td>
                                    <span class="btn btn-outline-primary btn-sm" data-toggle="modal" data-target="#edit-designation-{{$loop->iteration}}"> <i class="mdi mdi-pencil"></i></span>
                                    <div id="edit-designation-{{$loop->iteration}}" class="modal fade" role="dialog">
                                        <div class="modal-dialog">
                                            <!-- Modal content-->
                                            <form class="modal-content" method="POST" action="{{route('editResposibility',['id'=>$item->id])}}" enctype="multipart/form-data">
                                                @csrf
                                                <div class="modal-header">
                                                    <h4 class="modal-title"><i class="mdi mdi-pencil"></i> Edit {{$item->name}} w</h4>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="form-group">
                                                        <label class="control-label">Name</label>
                                                        <select name="name" id="" class="form-group">
                                                            @foreach($configs as $config)
                                                            <option value="{{$config->id}}" {{$item->name == $config->key ? 'selected': ''}}>{{$config->key}} ({{$config->value}})</option>
                                                            @endforeach
                                                        </select>

                                                    </div>



                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" class="form-control" name="status" {{$item->active ==1 ? 'checked':''}} />
                                                        <label class="form-check-label">
                                                            Active
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
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
            <!-- ---------  -->
            <div class="tab-pane fade show  p-3" id="inactive-responsibility" role="tabpanel" aria-labelledby="one-tab">
                <h5 class="card-title">InActive Responsibility</h5>
                <div class="table-responsive bg-light p-4">
                    <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
                        <thead class="bg-light p-2">
                            <tr>
                                <th>No</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Created At</th>
                                <th>Edited By</th>
                                <th nowrap>Active</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($responsibility as $item)
                            @if($item->active == 0)
                            <tr>
                                <td valign="center">{{ $loop->iteration }}</td>

                                <td nowrap>{{ $item->name }}</td>
                                <td>{{ $item->description }}</td>
                                <td>{{$item->created_at}}</td>
                                <td>{{$item->edited_by == '' ? 'N/a':$item->edited}}</td>
                                <td class="text-small">{!! $item->active == '1' ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>' !!}</td>
                                <!-- -----  -->
                                <td>
                                    <span class="btn btn-outline-primary btn-sm" data-toggle="modal" data-target="#edit-designation-{{$loop->iteration}}"> <i class="mdi mdi-pencil"></i></span>
                                    <div id="edit-designation-{{$loop->iteration}}" class="modal fade" role="dialog">
                                        <div class="modal-dialog">
                                            <!-- Modal content-->
                                            <form class="modal-content" method="POST" action="{{route('editResposibility',['id'=>$item->id])}}" enctype="multipart/form-data">
                                                @csrf
                                                <div class="modal-header">
                                                    <h4 class="modal-title"><i class="mdi mdi-pencil"></i> Edit {{$item->name}} w</h4>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="form-group">
                                                        <label class="control-label">Name</label>
                                                        <select name="name" id="" class="form-group">
                                                            @foreach($configs as $config)
                                                            <option value="{{$config->id}}" {{$item->name == $config->key ? 'selected': ''}}>{{$config->key}} ({{$config->value}})</option>
                                                            @endforeach
                                                        </select>

                                                    </div>



                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" class="form-control" name="status" {{$item->active ==1 ? 'checked':''}} />
                                                        <label class="form-check-label">
                                                            Active
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
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
            <!-- -----  -->
        </div>
    </div>
</main>
<div class="modal fade" id="add-job-responsibility" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <form class="modal-content" method="POST" action="{{route('addResponsibilities',['id'=>$designation->id])}}" enctype="multipart/form-data">
            @csrf
            <div class="modal-header">
                <h4 class="modal-title"><i class="mdi mdi-plus text-info"></i> Add {{$designation->name}} Resposibility </h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="control-label">Name</label>
                    <select name="name" id="" class="form-control">
                        @foreach($configs as $config)
                        <option value="{{$config->id}}">{{$config->key}} ({{$config->value}})</option>
                        @endforeach
                    </select>

                </div>



                <div class="form-check">
                    <input class="form-check-input" type="checkbox" checked class="form-control" name="status" />
                    <label class="form-check-label">
                        Active
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </form>
    </div>
</div>
@endsection