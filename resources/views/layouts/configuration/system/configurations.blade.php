@extends('layouts.configuration.layout.app', ['dataTable'=>true, 'select2'=>true])
@section('title2')
    <title>{{ __('system.system_configurations') }}</title>
@endsection
@section('content2')
    <main>
        <?php 
            $items = array(
                array(
                    'link'=>route('configuration-system-home'),
                    'name'=>__('system.configurations'),
                    'icon'=>null
                )
                );
        ?>
        <x-bread-crumb :items="$items"></x-bread-crumb>
        <h2 class="p-4">
            <i class="mdi mdi-cogs"></i> {{ __('system.configurations') }}
        </h2>
        @foreach($configuration_types as $configuration)
        <?php
            $configs = getconfigByID($configuration->id)
        ?>
            <div class="card" style="padding: 10px;margin-bottom:20px">
                <h5 class="card-title"><i class="mdi mdi-cog-box"></i> {{$configuration->configuration_type}}
                @can('system.configurations.add')
                <button class="btn btn-outline-primary btn-sm float-right" data-toggle="modal" data-target="#add-configuration-{{$configuration->id}}"><i class="mdi mdi-plus"></i> {{ __('system.add') }}</button>
                @endcan
                </h5>
                <div class="table-responsive">
                    <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
                        <thead class="bg-light p-2">
                            <tr>
                                <th>{{ __('system.no') }}</th>
                                <th nowrap>{{ __('system.key') }}</th>
                                <th nowrap>{{ __('system.value') }}</th>
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
                                    @can('system.configurations.edit')
                                    <span class="btn btn-outline-primary btn-sm" data-toggle="modal" data-target="#edit-configuration-{{$config->id}}"><i class="mdi mdi-pencil"></i></span>
                                    <div class="modal fade" id="edit-configuration-{{$config->id}}" role="dialog">
                                        <div class="modal-dialog">
                                            <form action="{{ route('edit-configuration',['id'=>$config->id]) }}" method="post" class="modal-content" enctype="multipart/form-data">
                                            @csrf 
                                                <div class="modal-header">
                                                    <h4 class="modal-title"><i class="mdi mdi-pencil"></i> {{ __('system.edit') }} {{$config->key}}</h4>

                                                </div>
                                                <div class="modal-body">
                                                    <div class="form-group">
                                                        <label class="control-label">{{ __('system.key') }}</label>
                                                        <input type="text" name="key" value="{{$config->key}}" placeholder="{{ __('system.configuration_key_placeholder') }}" class="form-control">
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="control-label">{{ __('system.value') }}</label>
                                                        <input type="text" name="value" value="{{$config->value}}" placeholder="{{ __('system.configuration_value_placeholder') }}" class="form-control">
                                                    </div>
                                                    <div class="form-group hidden">
                                                        <label class="control-label">{{ __('system.config_id') }}</label>
                                                        <input type="number" name="config_id" value="{{$config->id}}">
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="submit" class="btn btn-outline-primary"> <i class="mdi mdi-content-save"></i> {{ __('system.update') }}</button>
                                                    <button type="button" class="btn btn-outline-danger" data-dismiss="modal">{{ __('system.close') }}</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                    @endcan
                                    @can('system.configurations.delete')
                                    <span class="btn btn-outline-danger btn-sm" data-toggle="modal" data-target="#delete-configuration-{{$config->id}}"><i class="mdi mdi-delete-empty"></i></span>
                                    <div class="modal fade" id="delete-configuration-{{$config->id}}" role="dialog">
                                        <div class="modal-dialog">
                                            <form action="{{ route('delete-configuration',['id'=>$config->id]) }}" method="post" class="modal-content" enctype="multipart/form-data">
                                            @csrf 
                                                <div class="modal-header">
                                                    <h4 class="modal-title"><i class="mdi mdi-delete-empty"></i> {{ __('system.delete') }} {{$configuration->configuration_type}} {{ __('system.configuration') }} {{$loop->iteration}}</h4>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="panel panel-default">
                                                        <div class="panel-body">
                                                            {{ __('system.confirm_delete_configuration_of_type', ['key' => $config->key, 'type' => $configuration->configuration_type]) }}
                                                        </div>
                                                    </div>
                                                    <div class="form-group hidden">
                                                        <label class="control-label">{{ __('system.config_id') }}</label>
                                                        <input type="number" name="config_id" value="{{$config->id}}" class="form-control" placeholder="{{ __('system.config_id') }}">
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="submit" class="btn btn-outline-primary"> <i class="mdi mdi-content-save"></i> {{ __('system.yes') }}</button>
                                                    <button type="button" class="btn btn-outline-danger" data-dismiss="modal">{{ __('system.cancel') }}</button> 
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                    @endcan
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
                                <h4 class="modal-title"><i class="mdi mdi-plus"></i> {{ __('system.add_configuration_for_type', ['type' => $configuration->configuration_type]) }}</h4>
                            </div>
                            <div class="modal-body">
                                @if($configuration->configuration_type == 'Personnel to Recieve Feedback and Complaint Notification')
                                <div class="form-group">
                                    <label class="control-label">{{ __('system.choose_personnel') }}</label>
                                    <select name="name" id="" class="form-control">
                                        <?php $users = getAllUsers()?>
                                        @foreach($users as $user)
                                        
                                        <option value="{{$user->id}}">{{$user->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @else
                                <div class="form-group">
                                    <label class="control-label">{{ __('system.configuration_key') }}</label>
                                    <input type="text" name="key" value="" placeholder="{{ __('system.configuration_key_placeholder') }}" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label class="control-label">{{ __('system.configuration_value') }}</label>
                                    <input type="text" name="value" value=""  placeholder="{{ __('system.configuration_value') }}" class="form-control" required>
                                </div>
                                @endif
                                <div class="form-group hidden">
                                    <label class="control-label">{{ __('system.config_id') }}</label>
                                    <input type="number" name="config_id" value="{{$configuration->id}}" class="form-control">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-outline-primary"> <i class="mdi mdi-content-save"></i> {{ __('system.save') }}</button>
                                <button type="button" class="btn btn-outline-danger" data-dismiss="modal">{{ __('system.close') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>  
        @endforeach  
    </main>

@endsection