@extends('layouts.lab.layout.app', ['dataTable'=>true,'select2'=>true])

@section('title2')
  <title>Sample Products</title>
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
          'link' => route('sample-type-category-index'),
          'name' => 'Sample Type Category',
          'icon' => null
        )
      );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    <h2 class="p-4">
      <i class="mdi mdi-cogs"></i> Sample Type Category
      <span class="btn btn-outline-primary btn-sm float-right" data-toggle="modal" data-target="#add-sample-type-category" data-action="add"><i class="mdi mdi-plus"></i> Add</span>
     
    </h2>
   <div class="card table-responsive">
    <div class="card-body">
        <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
            <thead class="bg-light p-2">
                <tr>
                    <th>#</th>
                    <th nowrap>Name</th>
                    <th>Zoho ID</th>
                    <th>Created At</th>
                    <th nowrap>Active</th>
                    
                </tr>
            </thead>
            <tbody>
                @foreach ($categories as $category)
                
                <tr>
                    <td>
                        <span class="btn btn-sm btn-default text-primary" data-toggle="modal" data-target="#add-sample-type-category" data-record="{{json_encode($category)}}" data-action="edit"><i class="mdi mdi-pencil" data-toggle="tooltip" title="Edit"></i></span>
                    </td>
                    <td>{{ $category->sample_type_category }}</td>
                    <td>{{ $category->zoho_id }}</td>
                    <td>{{ $category->created_at }}</td>
                    <td class="text-small">{!! $category->active == '1' ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>' !!}</td>
                    
                </tr>
                @endforeach
                
            </tbody>
        </table>
    </div>
   </div>
  </main>
@endsection

@section('script2')
<div class="modal fade" id="add-sample-type-category" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="{{route('sample-type-category-add')}}" method="post">
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
      var editSampleConditionBody = (data=false)=>{
        if(data){
            var body = $(`
                <div class="alert alert-primary p-2">
                    <i class="mdi mdi-pencil" style="font-size:30px"></i>
                    <span class="p-2">Edit ${data.sample_type_category} Sample Type Category by updating the information below</span>
                </div>
                <div class="form-group">
                    <label for="" class="control-label">Name</label>
                    <input type="text" value="${data.sample_type_category}"  name="name" class="form-control">
                </div>
                <div class="form-group">
                  <label for="" class="control-label">Zoho ID</label>
                  <input type="text" name="zoho_id" value="${data.zoho_id}" class="form-control">
                </div>
                <div class="form-group">
                    <label for="" class="control-label">
                        <input type="checkbox" value="1" name="active" ${data.active == 1 ? 'checked' : ''} id=""> Is Active ?
                    </label>
                </div>
                <input type="hidden" name="category_id" value="${data.id}">
            `).clone();
        }else{
            var body = $(`
                <div class="alert alert-primary p-2">
                    <i class="mdi mdi-plus" style="font-size:30px"></i>
                    <span class="p-2">Add Sample Type Category by giving the information below</span>
                </div>
                <div class="form-group">
                    <label for="" class="control-label">Name</label>
                    <input type="text" value=""  name="name" class="form-control">
                </div>
                <div class="form-group">
                  <label for="" class="control-label">Zoho ID</label>
                  <input type="text" name="zoho_id" value="" class="form-control">
                </div>
                <div class="form-group">
                    <label for="" class="control-label">
                        <input type="checkbox" value="1" name="active" checked id=""> Is Active ?
                    </label>
                </div>
                <input type="hidden" name="category_id" value="0">
            `).clone();
        }
        return body;
      }
      $('#add-sample-type-category').on('show.bs.modal',(e)=>{
        var mode  = $(e.relatedTarget).data('action');
        var data = mode == 'edit' ?  $(e.relatedTarget).data('record') : false;
        var body = editSampleConditionBody(data);
        $('#add-sample-type-category').find('.modal-body').empty();
        $('#add-sample-type-category').find('.modal-body').append(body);
      });
      
    });
  </script>
@endsection