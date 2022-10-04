@extends('layouts.configuration.layout.app', ['dataTable'=>true, 'select2'=>true])
@section('title2')
    <title>System-Configurations</title>
@endsection
@section('content2')
    <main>
        <?php 
            $items = array(
                array(
                    'link'=>route('configuration-system-home'),
                    'name'=>'Configurations',
                    'icon'=>null
                )
                );
        ?>
        <x-bread-crumb :items="$items"></x-bread-crumb>
        <h2 class="p-4">
            <i class="mdi mdi-cogs"></i> Configurations
        </h2>
        @foreach($configuration_types as $configuration)
        <?php
            $configs = getconfigByID($configuration->id)
        ?>
            <div class="card" style="padding: 10px;margin-bottom:20px">
                <h5 class="card-title"><i class="mdi mdi-cog-box"></i> {{$configuration->configuration_type}}
                <button class="btn btn-outline-primary btn-sm float-right" data-toggle="modal" data-target="#add-configuration-{{$configuration->id}}"><i class="mdi mdi-plus"></i> Add</button>
                </h5>
                <div class="table-responsive">
                    <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
                        <thead class="bg-light p-2">
                            <tr>
                                <th>No</th>
                                <th nowrap>key</th>
                                <th nowrap>Value</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($configs as $config)
                            <tr>
                                <td>{{$loop->iteration}}</td>
                                <td>{{$config->key}}</td>
                                <td>{{$config->value}}</td>
                                <td>
                                    <span class="btn btn-outline-primary btn-sm" data-toggle="modal" data-target="#edit-configuration-{{$config->id}}"><i class="mdi mdi-pencil"></i></span>
                                    <div class="modal fade" id="edit-configuration-{{$config->id}}" role="dialog">
                                        <div class="modal-dialog">
                                            <form action="{{ route('edit-configuration',['id'=>$config->id]) }}" method="post" class="modal-content" enctype="multipart/form-data">
                                            @csrf 
                                                <div class="modal-header">
                                                    <h4 class="modal-title"><i class="mdi mdi-pencil"></i> Edit {{$config->key}}</h4>

                                                </div>
                                                <div class="modal-body">
                                                    <div class="form-group">
                                                        <label class="control-label">Key</label>
                                                        <input type="text" name="key" value="{{$config->key}}" placeholder="Configuration Key..." class="form-control">
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="control-label">Value</label>
                                                        <input type="text" name="value" value="{{$config->value}}" placeholder="Configuration Value..." class="form-control">
                                                    </div>
                                                    <div class="form-group hidden">
                                                        <label class="control-label">Config ID</label>
                                                        <input type="number" name="config_id" value="{{$config->id}}">
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="submit" class="btn btn-outline-primary"> <i class="mdi mdi-content-save"></i> Update</button>
                                                    <button type="button" class="btn btn-outline-danger" data-dismiss="modal">Close</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                    <span class="btn btn-outline-danger btn-sm" data-toggle="modal" data-target="#delete-configuration-{{$config->id}}"><i class="mdi mdi-delete-empty"></i></span>
                                    <div class="modal fade" id="delete-configuration-{{$config->id}}" role="dialog">
                                        <div class="modal-dialog">
                                            <form action="{{ route('delete-configuration',['id'=>$config->id]) }}" method="post" class="modal-content" enctype="multipart/form-data">
                                            @csrf 
                                                <div class="modal-header">
                                                    <h4 class="modal-title"><i class="mdi mdi-delete-empty"></i> Delete {{$configuration->configuration_type}} configuration {{$loop->iteration}}</h4>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="panel panel-default">
                                                        <div class="panel-body">
                                                            Are you sure you want to delete <b>{{$config->key}}</b> configuration of type {{$configuration->configuration_type}}?
                                                        </div>
                                                    </div>
                                                    <div class="form-group hidden">
                                                        <label class="control-label">Config ID</label>
                                                        <input type="number" name="config_id" value="{{$config->id}}" class="form-control" placeholder="Config ID">
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="submit" class="btn btn-outline-primary"> <i class="mdi mdi-content-save"></i> Yes</button>
                                                    <button type="button" class="btn btn-outline-danger" data-dismiss="modal">Cancel</button> 
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
                <div class="modal fade" id="add-configuration-{{$configuration->id}}" role="dialog">
                    <div class="modal-dialog">
                        <form action="{{ route('add-configuration',['id'=>$configuration->id]) }}" method="post" class="modal-content" enctype="multipart/form-data">
                        @csrf 
                            <div class="modal-header">
                                <h4 class="modal-title"><i class="mdi mdi-plus"></i> Add {{$configuration->configuration_type}} Configurations.</h4>
                            </div>
                            <div class="modal-body">
                                @if($configuration->configuration_type == 'Personnel to Recieve Feedback and Complaint Notification')
                                <div class="form-group">
                                    <label class="control-label">Choose Personnel</label>
                                    <select name="name" id="" class="form-control">
                                        <?php $users = getAllUsers()?>
                                        @foreach($users as $user)
                                        
                                        <option value="{{$user->id}}">{{$user->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @else
                                <div class="form-group">
                                    <label class="control-label">Configuration Key</label>
                                    <input type="text" name="key" value="" placeholder="Configuration Key..." class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label class="control-label">Configuration Value</label>
                                    <input type="text" name="value" value=""  placeholder="Configuration Value" class="form-control" required>
                                </div>
                                @endif
                                <div class="form-group hidden">
                                    <label class="control-label">Config ID</label>
                                    <input type="number" name="config_id" value="{{$configuration->id}}" class="form-control">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-outline-primary"> <i class="mdi mdi-content-save"></i> Save</button>
                                <button type="button" class="btn btn-outline-danger" data-dismiss="modal">Close</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>  
        @endforeach  
    </main>

@endsection