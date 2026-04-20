@php
    $layout = $module == "Inventory-Management"
        ? 'layouts.inventory.layout.app'
        : ($module == 'Lab-Management' ? 'layouts.lab.layout.app' : 'layouts.personnel.layout.app');
@endphp
@extends($layout, ['dataTable'=>true, 'select2'=>true])
<?php $module_text = implode(" ", explode("-", $module)); ?>
@section('title2')
  <title>{{ $config }} | {{ $module_text }}</title>
@endsection
@section('content2')
  <main>
    <?php
      $homeRoute = $module == 'Lab-Management' ? route('lab-home') : route('personnel-home');
      $items = array(
        array(
          'link' => $homeRoute,
          'name' => $module_text,
          'icon' => null
        ),
        array(
          'link' => null,
          'name' => 'Configurations',
          'icon' => null
        ),
        array(
          'link' => null,
          'name' => $config,
          'icon' => null
        )
      );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    <h2 class="p-4">
      <i class="mdi mdi-map-marker-radius"></i>{{ $config }}
      <button class="btn btn-primary btn-sm float-right" data-toggle="modal" data-target="#add-config"><i class="mdi mdi-plus"></i> Add</button>
    </h2>
    <br>
    <div class="table-responsive bg-light p-4">
      <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
        <thead class="bg-light p-2">
          <tr>
            <th>No</th>
            <th>Key</th>
            <th>Value</th>
            <th>Description</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
            @foreach($zones as $item)
              <tr>
                <td valign="center">{{ $loop->iteration }}</td>
                <td>{{ $item->key ?? '' }}</td>
                <td>{{ $item->value ?? '' }}</td>
                <td>{{ $item->description ?? '' }}</td>
                <td>
                  <span class="btn btn-sm btn-default text-info" data-target="#edit-config" data-toggle="modal" data-config="{{ json_encode($item) }}">
                    <i class="mdi mdi-pencil"></i>
                  </span>
                </td>
              </tr>
            @endforeach
        </tbody>
      </table>
    </div>
  </main>
@endsection

@section('script2')
  <div id="edit-config" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
      <form class="modal-content" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="modal-header">
          <h4 class="modal-title"><i class="mdi mdi-pencil"></i> Edit {{ $config }}</h4>
        </div>
        <div class="modal-body" id="edit-config-fields"></div>
        <div class="modal-footer">
          <input type="hidden" name="module" value="{{ $module }}" />
          <button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
	</div>
  <div id="add-config" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
      <form class="modal-content" method="POST" action="{{ route('add-module-pre-configs', ['id'=>time(), 'config'=>$config, 'module'=>$module]) }}" enctype="multipart/form-data">
        @csrf
        <div class="modal-header">
          <h4 class="modal-title"><i class="mdi mdi-plus"></i> Add {{ $config }}</h4>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label class="control-label">Key</label>
            <input type="text" class="form-control" name="name" value="" placeholder="Zone key..." required />
          </div>
          <div class="form-group">
            <label class="control-label">Value</label>
            <input type="text" class="form-control" name="value" value="" placeholder="Zone value..." required />
          </div>
          <div class="form-group">
            <label class="control-label">Description</label>
            <textarea class="form-control" name="description" placeholder="Description..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <input type="hidden" name="module" value="{{ $module }}" />
          <button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
	</div>
  <script>
    var returnFields = function($data){
      return $(`
        <div class="form-group">
          <label class="control-label">Key</label>
          <input type="text" class="form-control" name="name" value="${$data.key || ''}" placeholder="Zone key..." required />
        </div>
        <div class="form-group">
          <label class="control-label">Value</label>
          <input type="text" class="form-control" name="value" value="${$data.value || ''}" placeholder="Zone value..." required />
        </div>
        <div class="form-group">
          <label class="control-label">Description</label>
          <textarea class="form-control" name="description" placeholder="Description...">${$data.description || ''}</textarea>
        </div>
      `).clone();
    }

    $(function(){
      $('#edit-config').on('show.bs.modal', function(e){
        var config = $(e.relatedTarget).data('config');
        var field = returnFields(config);
        $('#edit-config-fields').html(field);
        $(this).find('form').prop('action', '/add-module-pre-configs/'+config.id+'/{{ $config }}/{{ $module }}');
      })
    });
  </script>
@endsection
