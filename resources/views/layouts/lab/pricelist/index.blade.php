@extends('layouts.lab.layout.app', ['dataTable'=>true, 'select2'=>true])

@section('title2')
  <title>Pricelists | Lab Management</title>
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
          'link' => route('view-pricelists'),
          'name' => 'Pricelists',
          'icon' => null
        )
      );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    <h2 class="p-4">
      <i class="mdi mdi-currency-usd"></i> Pricelists
			<span class="btn btn-default text-primary btn-sm float-right" data-target="#update-pricelist-information-modal" data-toggle="modal">
				<i class="mdi mdi-plus"></i> Pricelist
			</span>
    </h2>
    <br>
    <div class="table-responsive bg-light p-4">
      <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
        <thead class="bg-light p-2">
          <tr>
						<th>#</th>
            <th>Code</th>
						<th>Description</th>
            <th>Currency</th>
            <th>Date Created</th>
            <th>Active</th>
            <th>Is Master?</th>
            <th>Document No</th>
            <th>Valid Till</th>
            <th>Revision No</th>
						<th></th>
          </tr>
        </thead>
        <tbody>
					@foreach($pricelists as $pricelist)
						<tr>
							<td valign="center">{{ $loop->iteration }}</td>
							<td nowrap>{{ $pricelist->code }}</td>
							<td nowrap>{{ $pricelist->description ?? '-' }}</td>
							<td nowrap>{{ $pricelist->currency_name }}</td>
							<td nowrap>{{ $pricelist->created_at }}</td>
							<td nowrap>{!! $pricelist->active ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-marker-check text-muted"></i>' !!}</td>
							<td nowrap>{!! $pricelist->is_master ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-marker-check text-muted"></i>' !!}</td>
							<td nowrap>{{ $pricelist->document_no }}</td>
							<td nowrap>{{ $pricelist->valid_till ?? '-' }}</td>
							<td nowrap>Rev.{{ str_pad($pricelist->revision_number, 2,"0", STR_PAD_LEFT) }}</td>
							<td nowrap>
								<span class="btn btn-default text-info btn-sm" data-pricelist="{{ json_encode($pricelist) }}" data-toggle="modal" data-target="#update-pricelist-information-modal"><i class="mdi mdi-pencil"></i></span>
								<a href="{{ route('show-pricelist', ['id'=>$pricelist->id]) }}" class="btn btn-outline-default text-success btn-sm"><i class="mdi mdi-eye"></i></a>
							</td>
						</tr>
					@endforeach
        </tbody>
      </table>
    </div>
  </main>
@endsection

@section('script2')
  <div id="update-pricelist-information-modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <!-- Modal content-->
      <form class="modal-content" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="modal-header">
          <h4 class="modal-title"></h4>
        </div>
        <div class="modal-body" id="update-pricelist-details" ></div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
	</div>
	<script>
		var pricelistForm = function(data={}){
			var action = "{{ route('update-pricelist') }}";

			if(data.id){
				action+='/'+data.id;
			}

			$('#update-pricelist-information-modal').find('form').attr('action', action);

			var row = $(`
				<div class="form-group">
					<label class="control-label">Description</label>
					<textarea class="form-control" name="description" placeholder="Description..." required>${data.description || ''}</textarea>
				</div>
				<div class="form-group">
					<label class="control-label">Currency*</label>
					<select name="currency_id" class="form-control" placeholder="Select Currency..." required>
						<option></option>
						@foreach (getCurrencies() as $p)
							<option value="{{ $p->id }}" ${data.currency_id == {{ $p->id }} ? 'selected' : '' }>{{ $p->name }}</option>
						@endforeach
					</select>
				</div>
				<div class="form-group">
					<label class="control-label">
						<input type="checkbox" name="is_master" value="1" ${data.is_master == 1 ? 'checked' : '' } /> Is Master Pricelist
					</label>
				</div>
				<div class="form-group">
					<label class="control-label">Valid Until</label>
					<input type="date" name="valid_till" value="${data.valid_till}" class="form-control"  />
				</div>
				<div class="form-group">
					<label class="control-label">
						<input type="checkbox" name="active" value="1" ${data.active == 1 ? 'checked' : '' } /> Is Active
					</label>
				</div>
			`);

			return row.clone();
		}
		$(function(){
			$('#update-pricelist-information-modal').on('show.bs.modal', function(e){
				var data = $(e.relatedTarget).data('pricelist');

				var $row = pricelistForm(data);

				$('#update-pricelist-details').html($row);

				$row.find('input.form-control, select.form-control').attr('required', true);
				$row.find('input.form-control, select.form-control').prop('required', true);

				$row.find('select.form-control').select2();

				if(data){
					$(this).find('.modal-title').html('<i class="mdi mdi-pencil-outline"></i> Edit Pricelist');
				}
				else{
					$(this).find('.modal-title').html('<i class="mdi mdi-plus"></i> Add Pricelist');
				}

			})
		});
	</script>
@endsection
