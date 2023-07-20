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
          'link' => route('sample-product-index'),
          'name' => 'Sample Conditions',
          'icon' => null
        )
      );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    <h2 class="p-4">
      <i class="mdi mdi-cogs"></i> Sample Products
      <span class="btn float-right btn-outline-primary btn-sm" data-toggle="modal" data-target="#add-sample-product"><i class="mdi mdi-plus"></i> Add Product</span>
     
    </h2>
   <div class="card table-responsive">
    <div class="card-body">
        <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
            <thead class="bg-light p-2">
                <tr>
                    <th>#</th>
                    <th nowrap>Name</th>
                    <th>Created At</th>
                    <th nowrap>Active</th>
                    
                </tr>
            </thead>
            <tbody>
                @foreach ($products as $product)
                
                <tr>
                    <td>
                        <span class="btn btn-sm btn-default text-primary" data-toggle="modal" data-target="#edit-sample-product" data-record="{{json_encode($product)}}"><i class="mdi mdi-pencil" data-toggle="tooltip" title="Edit"></i></span>
                    </td>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->created_at }}</td>
                    <td class="text-small">{!! $product->active == '1' ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>' !!}</td>
                    
                </tr>
                @endforeach
                
            </tbody>
        </table>
    </div>
   </div>
  </main>
@endsection

@section('script2')
<div class="modal fade" id="add-sample-product" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="{{route('add-customer-product')}}" method="post">
        @csrf  
        <div class="modal-body">
            <div class="alert alert-primary p-2">
                <i class="mdi mdi-plus" style="font-size:30px"></i>
                <span class="p-2">Add Sample Product by providing the information below</span>
            </div>
            <div class="form-group">
                <label for="" class="control-label">Name</label>
                <input type="text"  name="name" class="form-control">
            </div>
            <div class="form-group">
                <label for="" class="control-label">
                    <input type="checkbox" name="active" value="1" checked id=""> Is Active ?
                </label>
            </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-sm btn-outline-primary"><i class="mdi mdi-content-save"></i> Save</button>
          <span class="btn btn-sm btn-default" data-dismiss="modal">Close</span>
        </div>
      </form>
    </div>
  </div>
</div>
<div class="modal fade" id="edit-sample-product" role="dialog">
    <div class="modal-dialog">
      <div class="modal-content">
        <form action="{{route('edit-customer-product')}}" method="post">
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
      var editSampleConditionBody = (data)=>{
        var body = $(`
            <div class="alert alert-primary p-2">
                <i class="mdi mdi-pencil" style="font-size:30px"></i>
                <span class="p-2">Edit ${data.name} Sample Condition by updating the information below</span>
            </div>
            <div class="form-group">
                <label for="" class="control-label">Name</label>
                <input type="text" value="${data.name}"  name="name" class="form-control">
            </div>
            <div class="form-group">
                <label for="" class="control-label">
                    <input type="checkbox" name="active" value="1" ${data.active == 1 ? 'checked' : ''} id=""> Is Active ?
                </label>
            </div>
            <input type="hidden" name="product_id" value="${data.id}">
        `).clone();
        return body;
      }
      $('#edit-sample-product').on('show.bs.modal',(e)=>{
        var data =  $(e.relatedTarget).data('record');
        var body = editSampleConditionBody(data);
        $('#edit-sample-product').find('.modal-body').empty();
        $('#edit-sample-product').find('.modal-body').append(body);
      });
      
    });
  </script>
@endsection