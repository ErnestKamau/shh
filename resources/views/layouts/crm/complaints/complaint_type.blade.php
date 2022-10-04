@extends('layouts.crm.layout.app', ['dataTable'=>true, 'select2'=>true])
@section('title2')
    <title>Complaint-Type</title>
@endsection
@section('content2')
    <main>
        <?php 
            $items = array(
                array(
                    'link'=>route('complaint-type-home'),
                    'name'=>'Complaint Type',
                    'icon'=>null
                )
                );
        ?>
        <x-bread-crumb :items="$items"></x-bread-crumb>
        <h2 class="p-4">
            <i class="mdi mdi-message-cog"></i>Complaint Type
            <button class="btn btn-outline-primary btn-sm float-right" data-toggle="modal" data-target="#add-complaint-type"><i class="mdi mdi-plus"></i> Add</button>
        </h2>
        <br>
        <div class="card tab-card">
            <div class="card-header tab-card-header">
                <ul class="nav nav-tabs card-header-tabs" id="complaint-tabs" role="tablist">
                    <li class="nav-item">
                        <a href="#complaint-type-tab" class="nav-link active" id="all-complaint-type-tab" data-toggle="tab" role="tab" aria-controls="complaint-type-tab" aria-selected="true"> <i class="mdi mdi-message-cog" style="color: black; font-size:15px"></i> Complaint Types</a>
                    </li>
                    <li class="nav-item">
                        <a href="#active-complaint-type" class="nav-link " id="active-complaint-tab" data-toggle="tab" role="tab" aria-controls="active-complaint-type" aria-selected="true"> <i class="mdi mdi-message-cog" style="color: green;font-size:15px"></i> Active Complaint Types</a>
                    </li>
                    <li class="nav-item">
                        <a href="#inactive-complaint-type" class="nav-link " id="inactive-complaint-tab" data-toggle="tab" role="tab" aria-controls="inactive-complaint-type" aria-selected="true"> <i class="mdi mdi-message-cog" style="color: red;font-size:15px"></i> Archived Complaint Types</a>
                    </li>
                </ul>
            </div>
            <div class="tab-content" id="complaint-type-tabs-content">
                <!-- all asset types  -->
                <div class="tab-pane fade show active p-3" id="complaint-type-tab" role="tabpanel" aria-labelledby="one-tab">
                    <h5 class="card-title">All Complaints Types</h5>
                    <div class="table-responsive">
                        <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
                            <thead class="bg-light p-2">
                                <tr>
                                    <th>No</th>
                                    <th>Complaint Name</th>
                                    <th>Complaint Description</th>
                                    <th>Active</th>
                                    <th></th>
                                    
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($types as $type)
                                    <tr>
                                        <td valign="center">{{ $loop->iteration }}</td>
                                        <td>{{ $type->name }}</td>
                                        <td>{{ $type->description }}</td>
                                        <td class="text-small text-center">{!! $type->status == 'active' ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>' !!}</td>
                                        <td class="text-center">
                                            <span class="btn btn-outline-dark btn-sm" data-toggle="modal" data-target="#edit-asset-type-{{ $type->id }}"> <i class="mdi mdi-pencil"></i></span>
                                            <div id="edit-asset-type-{{$type->id}}" class="modal fade" role="dialog">
                                                <div class="modal-dialog">
                                                    <!-- modal content  -->
                                                    <form class="modal-content" action="{{ route('edit-complaint-type', ['id'=>$type->id]) }}" method="POST" enctype="multipart/form-data">
                                                        @csrf
                                                        <div class="modal-header">
                                                            <h4 class="modal-title"><i class="mdi mdi-pencil"></i> Edit {{$type->name}} Complaint Type</h4>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="form-group">
                                                                <label class="control-label">Complaint Name</label>
                                                                <input type="text" name="complaint_name" class="form-control" value="{{$type->name}}" placeholder="Complaint Name..." required/>
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="control-label">Complaint Description</label>
                                                                <input type="text" name="description" class="form-control" value="{{$type->description}}" placeholder="Complaint Description..." required/>
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="control-label">Status</label>
                                                                <select class="form-control" name="status" placeholder="Active..." readonly="true">
                                                                    <option value="active" {{ $type->status == 'active' ? 'selected' : '' }}>Active</option>
                                                                    <option value="archive" {{ $type->status == 'archive' ? 'selected' : '' }}>Archived</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Update</button>
                                                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- end all assets types  -->

                <!-- active assets types  -->
                <div class="tab-pane fade p-3" id="active-complaint-type" role="tabpanel" aria-labelledby="one-tab">
                    <h5 class="card-title">Active Complaint Type</h5>
                    <div class="table-responsive">
                        <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Complaint Name</th>
                                    <th>Complaint Description</th>
                                    <th>Status</th>
                                    <th></th>
                                    
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($types as $type)
                                   @if($type->status == 'active') 
                                   <tr>

                                       <td valign="center">{{ $loop->iteration }}</td>
                                       <td>{{ $type->name}}</td>
                                       <td>{{ $type->description }}</td>
                                       <td class="text-small text-center">{!! $type->status == 'active' ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>' !!}</td>
                                       <td class="text-center">
                                            <span class="btn btn-outline-dark btn-sm" data-toggle="modal" data-target="#edit-complaint-type-{{ $type->id }}"> <i class="mdi mdi-pencil"></i></span>
                                            <div id="edit-complaint-type-{{$type->id}}" class="modal fade" role="dialog">
                                                <div class="modal-dialog">
                                                    <!-- modal content  -->
                                                    <form class="modal-content" action="{{ route('edit-complaint-type', ['id'=>$type->id]) }}" method="POST" enctype="multipart/form-data">
                                                        @csrf
                                                        <div class="modal-header">
                                                            <h4 class="modal-title"><i class="mdi mdi-pencil"></i> Edit {{$type->name}} Complaint Type</h4>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="form-group">
                                                                <label class="control-label">Complaint Name</label>
                                                                <input type="text" name="complaint_name" class="form-control" value="{{$type->name}}" placeholder="Complaint Name..." required/>
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="control-label">Complaint Description</label>
                                                                <input type="text" name="description" class="form-control" value="{{$type->description}}" placeholder="Complaint Description..." required/>
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="control-label">Status</label>
                                                                <select class="form-control" name="status" placeholder="Active..." readonly="true">
                                                                    <option value="active" {{ $type->status== 'active' ? 'selected' : '' }}>Active</option>
                                                                    <option value="archive" {{ $type->status == 'archive' ? 'selected' : '' }}>Archive</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Update</button>
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
                <!-- end active assets  -->

                <!-- in active assets  -->
                <div class="tab-pane fade p-3" id="inactive-complaint-type" role="tabpanel" aria-labelledby="one-tab">
                    <h5 class="card-title">Archived Complaint Type</h5>
                    <div class="table-responsive">
                        <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Complaint Name</th>
                                    <th>Complaint Description</th>
                                    <th>Status</th>
                                    <th></th>
                                    
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($types as $type)
                                   @if($type->status == 'archive') 
                                   <tr>

                                       <td valign="center">{{ $loop->iteration }}</td>
                                       <td>{{ $type->name}}</td>
                                       <td>{{ $type->description }}</td>
                                       <td class="text-small text-center">{!! $type->status == 'active' ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>' !!}</td>
                                       <td class="text-center">
                                            <span class="btn btn-outline-dark btn-sm" data-toggle="modal" data-target="#edit-complaint-type-{{ $type->id }}"> <i class="mdi mdi-pencil"></i></span>
                                            <div id="edit-complaint-type-{{$type->id}}" class="modal fade" role="dialog">
                                                <div class="modal-dialog">
                                                    <!-- modal content  -->
                                                    <form class="modal-content" action="{{ route('edit-complaint-type', ['id'=>$type->id]) }}" method="POST" enctype="multipart/form-data">
                                                        @csrf
                                                        <div class="modal-header">
                                                            <h4 class="modal-title"><i class="mdi mdi-pencil"></i> Edit {{$type->name}} Asset Type</h4>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="form-group">
                                                                <label class="control-label">Complaint Name</label>
                                                                <input type="text" name="complaint_name" class="form-control" value="{{$type->name}}" placeholder="Complaint Name..." required/>
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="control-label">Complaint Description</label>
                                                                <input type="text" name="description" class="form-control" value="{{$type->description}}" placeholder="Complaint Description..." required/>
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="control-label">Status</label>
                                                                <select class="form-control" name="status" placeholder="Active..." readonly="true">
                                                                    <option value="active" {{ $type->status== 'active' ? 'selected' : '' }}>Active</option>
                                                                    <option value="archive" {{ $type->status == 'archive' ? 'selected' : '' }}>Archive</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Update</button>
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
                <!-- end inactive assets  -->
            </div>
        </div>
    </main>
@endsection 
@section('script2')
    <div id="add-complaint-type" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <form action="{{ route('add-complaint-type') }}" method="POST" class="modal-content" enctype="multipart/form-data">
                @csrf 
                <div class="modal-header">
                    <h4 class="modal-title"><i class="mdi mdi-plus"></i> Add complaint Type</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="control-label">Complaint Name</label>
                        <input type="text" name="complaint_name" class="form-control" value="" placeholder="Complaint Name..." required/>
                    </div>
                    <div class="form-group">
                        <label class="control-label">Complaint Description</label>
                        <input type="text" name="description" class="form-control" value="" placeholder="Complaint Description..." required/>
                    </div>
                    <div class="form-group">
                        <label class="control-label">Status</label>
                        <select class="form-control" name="status" placeholder="Active..." readonly="true">
                            <option value="active" >Active</option>
                            <option value="archive" >Archive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-outline-primary"><i class="mdi mdi-content-save"></i> Save</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
    <script>

    </script>
@endsection