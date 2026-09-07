@extends('layouts.inventory.layout.app', ['dataTable'=>true, 'select2'=>true])

@section('title2')
  <title>Stock Transfer | Inventory Management</title>
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
          'link' => route('stock-transfer-list'),
          'name' => 'Stock Transfer',
          'icon' => null
        )
      );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    <h2 class="p-4">
      <i class="mdi mdi-bank-transfer-out"></i> Stock Transfer
			<a class="btn btn-default text-primary btn-sm float-right" href="{{ route('stock-transfer-sheet', ['id'=>'new']) }}"><i class="mdi mdi-plus"></i> New Transfer</a>
    </h2>
    <br>
    <div class="table-responsive bg-light p-4">
      <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
        <thead class="bg-light p-2">
          <tr>
						<th>#</th>
            <th>Code</th>
            <th>Location</th>
						<th>Department</th>
						<th>Description</th>
            <th>Created By</th>
            <th>Created On</th>
            <th>Status</th>
						<th></th>
          </tr>
        </thead>
        <tbody>
					@foreach($transfers as $transfer)
						<tr>
							<td valign="center">{{ $loop->iteration }}</td>
							<td nowrap>{{ $transfer->code }}</td>
							<td nowrap>{{ $transfer->location }}</td>
							<td nowrap>{{ $transfer->department }}</td>
							<td nowrap>{{ $transfer->description ?? 'No items to transfer added yet.' }}</td>
							<td nowrap>{{ $transfer->user_name }}</td>
							<td nowrap>{{ $transfer->created_at }}</td>
							<td nowrap>{{ $transfer->status }}</td>
							<td nowrap>
								{{-- <span class="btn btn-outline-info btn-sm" data-taking="{{ json_encode($transfer) }}"  data-toggle="modal" data-target="#add-stock-taking"><i class="mdi mdi-pencil"></i></span> --}}
								<a href="{{ route('stock-transfer-sheet', ['id'=>$transfer->id]) }}" class="btn btn-outline-success btn-sm" data-taking="{{ json_encode($transfer) }}"><i class="mdi mdi-eye"></i></a>
							</td>
						</tr>
					@endforeach
        </tbody>
      </table>
    </div>
  </main>
@endsection

@section('script2')
  <div id="add-stock-taking" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <!-- Modal content-->
      <form class="modal-content" method="POST" action="{{ route('stock-taking-update') }}" enctype="multipart/form-data">
        @csrf
        <div class="modal-header">
          <h4 class="modal-title"><i class="mdi mdi-update"></i> Stock Taking</h4>
        </div>
        <div class="modal-body" id="stock-taking-details" ></div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
	</div>
	<script>
		var takingRow = function(data={}){
			var store_ids = data.stores ? data.stores.split(',') : [];
			console.log(store_ids)
			var action = '{{ route('stock-taking-update') }}';

			if(data.id){
				action+='/'+data.id
			}

			console.log(action);

			$('#add-stock-taking').find('form').attr('action', action)

			var row = $(`
				<div class="form-group">
					<label class="control-label">Description</label>
					<textarea class="form-control" name="description" placeholder="Description..." required>${data.description || ''}</textarea>
				</div>
				<div class="form-group">
					<label>Select Stores</label>
					<select name="stores[]" class="form-control selected-store" placeholder="Select Stores..." multiple required>
						<option></option>
						@foreach (getUserStores() as $store)
							<option value="{{ $store->id }} zZ {{ $store->name }}" ${store_ids.indexOf('{{ $store->id }}') > -1 ? 'selected' : 's'}>{{ $store->name }}</option>
						@endforeach
					</select>
				</div>
			`);

			return row.clone();
		}
		$(function(){
			$('#add-stock-taking').on('show.bs.modal', function(e){
				var data = $(e.relatedTarget).data('taking');

				var $row = takingRow(data);

				$('#stock-taking-details').html($row);

				$row.find('input.form-control, select.form-control').attr('required', true);
				$row.find('input.form-control, select.form-control').prop('required', true);

				$row.find('select.form-control').select2();
			})
		});
	</script>
@endsection
