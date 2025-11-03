@extends('layouts.inventory.layout.app', ['dataTable'=>true, 'select2'=>true])

@section('title2')
  <title>Suppliers</title>
@endsection
@section('content2')
  <main>
    <?php
      $items = array(
        array(
          'link' => route('inventory-home'),
          'name' => 'Inventory Management',
          'icon' => null
        ),
        array(
          'link' => route('inventory-suppliers'),
          'name' => 'Suppliers',
          'icon' => null
        )
      );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    <h2 class="p-4">
      <i class="mdi mdi-account-group"></i> Suppliers
      <button class="btn btn-primary btn-sm float-right" data-toggle="modal" data-target="#add-inventory-supplier"><i class="mdi mdi-plus"></i> Add</button>
    </h2>
    <br>
    <div class="table-responsive bg-light p-4">
      <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
        <thead class="bg-light p-2">
          <tr>
            <th>No</th>
            <th></th>
            <th>Logo</th>
            <th>Name</th>
            <th>Rating <small class="text-muted">(avg.)</small></th>
            <th>Email</th>
            <th>Phone</th>
            <th>Building</th>
            <th>Street</th>
            <th>Town</th>
            <th>Postal Address</th>
            <th>PIN Number</th>
          </tr>
        </thead>
        <tbody>
          @if(count($suppliers) > 0)
            @foreach($suppliers as $supplier)
              <tr>
                <td valign="center">{{ $loop->iteration }}</td>
								<td nowrap>
                  <a class="btn btn-transparent text-success btn-sm" href="{{ route('show-inventory-supplier', ['id'=>$supplier->id]) }}"><i class="mdi mdi-eye-outline"></i> <small class="hidden-sm-up">Show</small> </a>
                  <span data-supplier="{{ $supplier }}" data-target="#delete-this-supplier" data-toggle="modal" class="btn btn-transparent text-danger btn-sm">
										<i class="mdi mdi-delete"></i> <small class="hidden-sm-up">Show</small>
									</span>
                </td>
                <td><img src="{{ $supplier->logo }}" style="width: 75px" /></td>
                <td><a href="{{ route('show-inventory-supplier', ['id'=>$supplier->id]) }}">{{ $supplier->name }}</a></td>
                <td>{{ $supplier->average_rating() }}<i class="mdi mdi-star text-success"></i></td>
								<td>{{ $supplier->email }}</td>
								<td>{{ $supplier->phone }}</td>
								<td>{{ $supplier->building }}</td>
								<td>{{ $supplier->street }}</td>
								<td>{{ $supplier->town }}</td>
								<td>{{ $supplier->address }}</td>
								<td>{{ $supplier->pin_number }}</td>
              </tr>
            @endforeach
          @endif
        </tbody>
      </table>
      @if(count($suppliers) == 0)
        <div class="alert alert-info">
          <i class="mdi mdi-alert"></i> No Suppliers added yet.
        </div>
      @endif
    </div>
  </main>
@endsection

@section('script2')
	<script>
		$(function(){
			$('#delete-this-supplier').on('show.bs.modal', function(e){
				var supplier = $(e.relatedTarget).data('supplier');

				var action = '/inventory-supplier/'+supplier.id+'/delete';

				$(this).find('form').attr('action', action);
				$(this).find('form').prop('action', action);

				$(this).find('.supplier-name').text(supplier.name);
			});
		});
	</script>
  <div id="delete-this-supplier" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <!-- Modal content-->
      <form class="modal-content" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="modal-header">
          <h4 class="modal-title"><i class="mdi mdi-delete"></i> Delete Supplier - <span class="supplier-name"></span></h4>
        </div>
        <div class="modal-body">
					<div class="alert alert-danger"><i class="mdi mdi-alert"></i> Are you sure you want to delete supplier - <span class="supplier-name"></span>?</div>
				</div>
				<div class="modal-footer">
          <button type="submit" class="btn btn-danger"><i class="mdi mdi-delete"></i> Remove</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
			</form>
		</div>
	</div>
  <div id="add-inventory-supplier" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <!-- Modal content-->
      <form class="modal-content" method="POST" action="{{ route('add-inventory-supplier') }}" enctype="multipart/form-data">
        @csrf
        <div class="modal-header">
          <h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Supplier</h4>
        </div>
        <div class="modal-body">
          <div class="form-group">
						<label class="control-label">Name</label>
						<input type="text" class="form-control" name="name" value="" placeholder="Name..." required />
					</div>
					<div class="form-group">
						<label class="control-label">Logo</label>
						<input type="file" class="form-control" name="logo"  />
					</div>
					<div class="form-group">
						<label class="control-label">Email</label>
						<input type="email" class="form-control" name="email" value="" placeholder="Email..." required />
					</div>
					<div class="form-group">
						<label class="control-label">Phone</label>
						<input type="text" class="form-control" name="phone" value="" placeholder="Phone..." required />
					</div>
					<div class="form-group">
						<label class="control-label">Building</label>
						<input type="text" class="form-control" name="building" value="" placeholder="Building..." required />
					</div>
					<div class="form-group">
						<label class="control-label">Street</label>
						<input type="text" class="form-control" name="street" value="" placeholder="Street..." required />
					</div>
					<div class="form-group">
						<label class="control-label">Town</label>
						<input type="text" class="form-control" name="town" value="" placeholder="Town..." required />
					</div>
					<div class="form-group">
						<label class="control-label">Address</label>
						<input type="text" class="form-control" name="address" value="" placeholder="Address..." required />
					</div>
					<div class="form-group">
						<label class="control-label">PIN Number</label>
						<input type="text" class="form-control" name="pin_number" value="" placeholder="PIN Number..." required />
					</div>
          <div class="form-group">
            <label class="control-label">VAT Number</label>
            <input type="text" class="form-control" name="vat_number" value="" placeholder="VAT Number..." required />
          </div>
          <div class="form-group">
            <label class="control-label">Payment Terms</label>
            <input type="text" class="form-control" name="payment_terms" value="" placeholder="Payment Terms..." required />
          </div>
          <div class="form-group">
            <label class="control-label">Payment Methods</label>
            <input type="text" class="form-control" name="payment_method" value="" placeholder="Payment Methods..." required />
          </div>
					<div class="form-group">
						<label class="control-label">Supplier Currency</label>
						<select name="default_currency" class='form-control trigger-save' data-placeholder="Select Currency...">
							<option></option>
							@foreach (getCurrencies() as $p)
								<option value="{{ $p->id }}">{{ $p->name }}</option>
							@endforeach
						</select>
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