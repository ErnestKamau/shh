@extends('layouts.lab.layout.app', ['dataTable'=>true,'select2'=>true])

@section('title2')
  <title>Analysis Methods</title>
@endsection
@section('content2')
  <main>
    <?php
      $items = array(
        array(
          'link' => route('lab-home'),
          'name' => 'Lab Management',
					'icon' => null
				),
				array(
          'link' => route('analysis-methods'),
          'name' => 'Methods',
          'icon' => null
        )
      );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    <h2 class="p-4">
      <i class="mdi mdi-cogs"></i> Methods
      {{-- <span class="btn btn-sm btn-white"><i class="mdi mdi-file-import-outline"></i> Import</span> --}}
      <button class="btn btn-primary btn-sm float-right" data-toggle="modal" data-target="#add-method"><i class="mdi mdi-plus"></i> Add</button>
    </h2>
    
    <div class="table-responsive bg-light p-4">
      <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
        <thead class="bg-light p-2">
          <tr>
            <th>No</th>
            <th>Code</th>
            <th>Name</th>
            <th>Description</th>
            <th>Reference</th>
            <th>Elements</th>
            <th>Type</th>
            <th>Active?</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @if(count($methods) > 0)
            @foreach($methods as $method)
              <tr>
                <td valign="center">{{ $loop->iteration }}</td>
                <td>{{ $method->code }}</td>
                <td>{{ $method->name }}</td>
                <td>{{ $method->description }}</td>
                <td>{{ $method->referencemethod->name ?? '-' }}</td>
                <td>{{ number_format($method->analytes()->count()) }}</td>
                
                <td>{{ $method->methodtype->value ?? 'Not Set' }}</td>
                <td class="text-small">{!! $method->active == '1' ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>' !!}</td>
                <td nowrap>
                  <button class="btn btn-primary btn-sm" data-target="#edit-method" data-toggle="modal" data-record="{{ json_encode($method) }}"><i class="mdi mdi-pencil-outline"></i> <small class="hidden-sm-up">Edit</small> </button>
                  <a class="btn btn-success btn-sm" href="{{ route('edit-analysis-method', ['id'=>$method->id]) }}"><i class="mdi mdi-eye-outline"></i> <small class="hidden-sm-up">Show</small> </a>
                </td>
              </tr>
            @endforeach
          @endif
        </tbody>
      </table>
      @if(count($methods) == 0)
        <div class="alert alert-info">
          <i class="mdi mdi-alert"></i> No Analysis Methods added yet.
        </div>
      @endif
    </div>
  </main>
@endsection

@section('script2')
  <div id="add-method" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <!-- Modal content-->
      <form class="modal-content" method="POST" action="{{ route('add-analysis-methods') }}" enctype="multipart/form-data">
        @csrf
        <div class="modal-header">
          <h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Method</h4>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label class="control-label">Name</label>
            <input type="text" class="form-control" name="name" placeholder="Analysis Method Name..." required />
          </div>
          <div class="form-group">
            <label class="control-label">Code</label>
            <input type="text" class="form-control" name="code" placeholder="Analysis Method Code..." required />
          </div>
          <div class="form-group">
            <label class="control-label">Description</label>
            <textarea class="form-control" name="description" placeholder="Description..." required></textarea>
          </div>
          <div class="form-group">
            <label for="" class="control-label">Method Type</label>
            <select name="method_type_id" data-ltmid="{{ json_encode($ltm_id->value) }}" id="" class="form-control method_type_id">
              <option value="">Select Type</option>
              @foreach ($method_types as $m_type)
                <option value="{{ $$m_type->id }}">{{ $$m_type->value }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-group reference_method_id hidden">
            <label for="" class="control-label">Reference Methods</label>
            <select name="reference_method_id" id="" class="form-control">
              <option value="">Select Reference</option>
              @foreach($references as $ref)
                <option value="{{ $ref->id }}">{{ $ref->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-group">
            <label class="control-label"><input type="checkbox" name="active" value="1" checked /> Active</label>
          </div>
          
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
  </div>
  <div id="edit-method" class="modal fade" role="dilaog">
    <div class="modal-dialog">
      <div class="modal-content">
        <form action="{{ route('edit-analysis-method') }}" method="post">
          @csrf 
          <div class="modal-body">
            
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-sm btn-outline-primary"><i class="mdi mdi-content-save"></i> Save</button>
            <span class="btn btn-sm btn-default" data-dismiss="modal">Close</span>
          </div>
        </form>
      </div>
    </div>
  </div>
  <script>
    $(()=>{

      const ltm_id = $('#add-method').find('.method_type_id').data('ltmid');

      $('#add-method').on('show.bs.modal',(e)=>{
        $('#add-method').find('.method_type_id').on('change',()=>{
          
          var method = $('#add-method').find('.method_type_id').val();
          if(method == ltm_id){
            $('#add-method').find('.reference_method_id').removeClass('hidden');
          }else{
            $('#add-method').find('.reference_method_id').addClass('hidden');
          }
        })
      })
      var editMethodBody = (data)=>{
        var body = $(`
        <div class="alert alert-primary p-2 d-flex">
            <i class="mdi mdi-pencil" style="fomt-size:20px"></i>
            <span class="pl-2">Edit ${data.name} ${data.is_sampling_method == 0 ? (data.is_ltm == 0 ? 'Reference' : 'Laboratory Test') : 'Sampling'} Method</span>
        </div>
        <div class="form-group">
          <label class="control-label">Name</label>
          <input type="text" class="form-control" name="name" value="${data.name}" placeholder="Analysis Method Name..." required />
        </div>
        <div class="form-group">
          <label class="control-label">Code</label>
          <input type="text" class="form-control" name="code" value="${data.code}" placeholder="Analysis Method Code..." required />
        </div>
        <div class="form-group">
          <label class="control-label">Description</label>
          <textarea class="form-control" name="description" placeholder="Description..." required>${data.description}</textarea>
        </div>
        <div class="form-group">
          <label for="" class="control-label">Method Type</label>
          <select name="method_type_id" id="" data-ltmid="{{ json_encode($ltm_id->value) }} class="form-control method_type_id">
            <option value="">Select Type</option>
            @foreach ($method_types as $m_type)
              <option value="{{ $$m_type->id }}">{{ $$m_type->value }}</option>
            @endforeach
          </select>
        </div>
        <div class="form-group reference_method_id ${data.is_ltm == 1 ? '' : 'hidden'}">
          <label for="" class="control-label">Reference Methods</label>
          <select name="reference_method_id" id="" class="form-control reference_method">
            <option value="">Select Reference</option>
            @foreach($references as $ref)
              <option value="{{ $ref->id }}">{{ $ref->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="form-group">
          <label class="control-label"><input type="checkbox" name="active" value="1" ${data.active == 1 ? 'checked' : ''} /> Active</label>
        </div>
        <input type="hidden" name="method_id" value="${data.id}">
        `).clone();
        $(body).find('.method_type_id').val(data.method_type_id);
        $(body).find('.reference_method').val(data.reference_type_id);
        $(body).find('.reference_method').select2();
        $(body).find('.method_type_id').select2();
        $(body).find('.method_type_id').on('change',(e)=>{
            var value = $(body).find('.method_type_id').val();
            if(value == ltm_id){
              $(body).find('.reference_method_id').removeClass('hidden');
            }else{
              $(body).find('.reference_method_id').addClass('hidden');
            }
        })
        return body;
      }
      $('#edit-method').on('show.bs.modal',(e)=>{
        var data = $(e.relatedTarget).data('record');
        console.log(data.name)
        var body = editMethodBody(data);
        $('#edit-method').find('.modal-body').empty();
        $('#edit-method').find('.modal-body').append(body);
      })
    })
  </script>
@endsection