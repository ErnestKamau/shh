@extends('layouts.configuration.layout.app', ['dataTable'=>true, 'select2'=>true])
@section('title2')
    <title>System-Configuration-Types</title>
@endsection
@section('content2')
    <main>
        <?php 
            $items = array(
                array(
                    'link'=>route('configuration-type-home'),
                    'name'=>'Configuration Types',
                    'icon'=>null
                )
                );
        ?>
        <x-bread-crumb :items="$items"></x-bread-crumb>
        <h2 class="p-4">
            <i class="mdi mdi-cogs"></i> Configuration Types
            <button class="btn btn-outline-primary btn-sm float-right" data-toggle="modal" data-target="#add-configuration-type"><i class="mdi mdi-plus"></i> Add</button>
        </h2>
        <div class="card tab-card">
            <div class="card-header tab-card-header">
                <ul class="nav nav-tabs card-header-tabs" id="configuration-tabs" role="tablist">
                    <li class="nav-item">
                        <a href="#active-configuration" class="nav-link active" id="active-configuration-type" data-toggle="tab" role="tab" aria-controls="active-configuration" aria-selected="true"><i class="mdi mdi-map-marker"></i> Active Configuration Types</a>
                    </li>
                    
                </ul>
            </div>
            <div class="tab-content" id="configurations-type-tab">
                <div class="tab-pane fade show active p-3" id="active-configuration" role="tabpanel" aria-labelledby="one-tab">
                    <h5 class="card-title">Active Configuration Types</h5>
                    <div class="table-responsive">
                        <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
                            <thead class="bg-light p-2">
                                <tr>
                                    <th>No</th>
                                    <th nowarap>Name</th>
                                    <td>Date</td>
                                    <th nowrap>Status</th>
                                    <th nowrap >Description</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($configuration_types as $configuration)
                                
                                    <tr>
                                        <td valign="center">{{$loop->iteration}}</td>
                                        <td>{{$configuration->configuration_type}}</td>
                                        <td>{{$configuration->created_at}}</td>
                                        <td class="text-small">{!! $configuration->status == 0 ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>' !!}</td>
                                        <td>
                                            <span class="btn btn-outline-dark btn-sm" data-toggle="modal" data-target="#configuration-description-{{$configuration->id}}"><i class="mdi mdi-message-text"></i></span>
                                            <div class="modal fade" id="configuration-description-{{$configuration->id}}" role="dialog">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h4 class="modal-title"><i class="mdi mdi-key"></i> {{$configuration->configuration_type}} Description.</h4>
                                                        </div>
                                                        <div class="modal-body">
                                                            <h5>Description</h5>
                                                            <div class="panel panel-default">
                                                                <div class="panel-body">
                                                                    {{$configuration->description}}
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="btn btn-outline-primary btn-sm" data-target="#edit-configuration-{{$configuration->id}}" data-toggle="modal"><i class="mdi mdi-pencil"></i></span>
                                            <div class="modal fade" id="edit-configuration-{{$configuration->id}}" role="dialog">
                                                <div class="modal-dialog">
                                                    <form action="{{ route('edit-configuration-type',['id'=>$configuration->id]) }}" method="post" class="modal-content" enctype="multipart/form-data">
                                                        @csrf 
                                                        <div class="modal-header">
                                                            <h4 class="modal-title"><i class="mdi mdi-pencil"></i> Edit {{$configuration->configuration_type}}</h4>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="form-group">
                                                                <label class="control-label">Configuration Type</label>
                                                                <input type="text" name="name" class="form-control" value="{{$configuration->configuration_type}}" placeholder="Configuration Name...">
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="control-label">Description</label>
                                                                <textarea name="description" placeholder="Configuration Description" class="form-control" rows="6" value="">{{$configuration->description}}</textarea>
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="control-label">
                                                                <input type="checkbox" value=0 {{$configuration->status == 0 ? 'checked':''}} name="status">
                                                                Active
                                                                </label>
                                                            </div>
                                                            <div class="form-group hidden">
                                                                <label class="control-label">Config ID</label>
                                                                <input type="number" name="config_id" value={{$configuration->id}} class="form-control">
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="submit" class="btn btn-outline-primary"> <i class="mdi mdi-content-save"></i> Update</button>
                                                            <button type="button" class="btn btn-outline-danger" data-dismiss="modal">Close</button>
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
                <!-- ------------------------  -->
                
            </div>
        </div>
    </main>
@endsection
@section('script2')
<div class="modal fade" id="add-configuration-type" role="dialog">
    <div class="modal-dialog">
        <form action="{{ route('add-configuration-type') }}" method="post" class="modal-content" enctype="multipart/form-data">
        @csrf 
            <div class="modal-header">
                <h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Configuration Type</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="control-label">Configuration Type</label>
                    <input type="text" name="name" class="form-control" value="" placeholder="Configuration Name..." required>
                </div>
                <div class="form-group">
                    <label class="control-label">Description</label>
                    <textarea name="description" class="form-control" rows="6" placeholder="Configuration Description" value="" required></textarea>
                </div>
                
                
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-outline-primary"> <i class="mdi mdi-content-save"></i> Save</button>
                <button type="button" class="btn btn-outline-danger" data-dismiss="modal">Close</button>
            </div>
        </form>
    </div>
</div>
@endsection