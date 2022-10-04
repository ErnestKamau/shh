@extends('layouts.inventory.layout.app', ['dataTable'=>true, 'select2'=>true])

@section('title2')
  <title>Stock Taking | Inventory Management</title>
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
          'link' => route('stock-taking-list'),
          'name' => 'Stock Taking',
          'icon' => null
        )
      );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    <h2 class="p-4">
      <i class="mdi mdi-format-list-bulleted-type"></i> Stock Taking
      <button class="btn btn-primary btn-sm float-right" data-toggle="modal" data-target="#add-stock-taking"><i class="mdi mdi-plus"></i> Add</button>
    </h2>
    <br>
    <div class="table-responsive bg-light p-4">
      <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
        <thead class="bg-light p-2">
          <tr>
						<th>#</th>
            <th>Code</th>
            <th>Instructions</th>
            <th>Stores</th>
            <th>Status</th>
            <th>Created By</th>
						<th>Date Created</th>
						<th>Updated By</th>
						<th>Aprroved By</th>
						<th>Date Completed</th>
						<th></th>
          </tr>
        </thead>
        <tbody>
					@foreach($takings as $taking)
						<tr>
							<td valign="center">{{ $loop->iteration }}</td>
							<td nowrap>{{ $taking->code }}</td>
							<td nowrap>
								<span class="btn btn-sm btn-trasparent text-info" data-instructions="{{ json_encode($taking->description) }}" data-target='#stock-taking-instructions-modal' data-toggle="modal">
									<i class="mdi mdi-file-alert"></i> Instructions
								</span>
							</td>
							<td nowrap>{{ $taking->store_names }}</td>
							<td>{{ $taking->status }}</td>
							<td>{{ $taking->creator }}</td>
							<td>{{ $taking->created_at }}</td>
							<td>{{ $taking->updater_name }}</td>
							<td>{{ $taking->approver_name }}</td>
							<td>{{ $taking->completed_at }}</td>
							<td nowrap>
								<span class="btn btn-outline-info btn-sm" data-taking="{{ json_encode($taking) }}"  data-toggle="modal" data-target="#add-stock-taking"><i class="mdi mdi-pencil"></i></span>
								<a href="{{ route('stock-taking-sheet', ['id'=>$taking->id]) }}" class="btn btn-outline-success btn-sm" data-taking="{{ json_encode($taking) }}"><i class="mdi mdi-eye"></i></a>
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
    <div class="modal-dialog modal-lg">
      <!-- Modal content-->
      <form class="modal-content" method="POST" action="{{ route('stock-taking-update') }}" enctype="multipart/form-data">
        @csrf
        <div class="modal-header">
          <h4 class="modal-title"><i class="mdi mdi-update"></i> Stock Taking</h4>
        </div>
        <div class="modal-body" id="stock-taking-details" ></div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary" onclick="tinyMCE.triggerSave()"><i class="mdi mdi-content-save"></i> Save</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
	</div>

  <div id="stock-taking-instructions-modal" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title"><i class="mdi mdi-file"></i> Stock Taking Instructions</h4>
        </div>
        <div class="modal-body" id="stock-taking-instructions"></div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
	</div>
	<script type="text/javascript" src="/tinymce/tinymce.min.js"></script>
	<script>
		var takingRow = function(data={}, editor_id){
			var store_ids = data.stores ? data.stores.split(',') : [];
			console.log(store_ids)
			var action = '{{ route('stock-taking-update') }}';

			if(data.id){
				action+='/'+data.id
			}

			console.log(action);

			$('#add-stock-taking').find('form').attr('action', action);

			var row = $(`
				<div class="form-group">
					<label>Select Stores</label>
					<select name="stores[]" class="form-control selected-store" placeholder="Select Stores..." multiple required>
						<option></option>
						@foreach (getUserStores(false, true) as $store)
							<option value="{{ $store->id }} zZ {{ $store->name }}" ${store_ids.indexOf('{{ $store->id }}') > -1 ? 'selected' : 's'}>{{ $store->name }}</option>
						@endforeach
					</select>
				</div>
				<div class="form-group">
					<label class="control-label">Description/Instructions</label>
					<textarea class="form-control" id="${editor_id}" name="description" placeholder="Description..." required>${data.description || ''}</textarea>
				</div>
			`);

			return row.clone();
		}
		$(function(){
			$('#stock-taking-instructions-modal').on('show.bs.modal', function(e){
				var instructions = $(e.relatedTarget).data('instructions');
				$(this).find('#stock-taking-instructions').html(`${JSON.parse(instructions)}`);
			});

			$('#add-stock-taking').on('show.bs.modal', function(e){
				var data = $(e.relatedTarget).data('taking');

				var editor_id = "editor-"+Math.round(Math.random()*10000);

				var $row = takingRow(data, editor_id);

				$('#stock-taking-details').html($row);

				tinymce.init({
					selector: "#"+editor_id
				});

				$row.find('input.form-control, select.form-control').attr('required', true);
				$row.find('input.form-control, select.form-control').prop('required', true);

				$row.find('select.form-control').select2();
			})
		});
	</script>
@endsection
