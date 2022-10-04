@extends($module == "Inventory-Management" ? 'layouts.inventory.layout.app' : 'layouts.personnel.layout.app', ['dataTable'=>true, 'select2'=>true])
<?php $module_text = implode(" ", explode("-", $module)); ?>
@section('title2')
  <title>Unit of Measure Conversions | {{ $module_text }}</title>
@endsection
@section('content2')
  <main>
    <?php
      $items = array(
        array(
          'link' => route('personnel-home'),
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
          'name' => 'UoM Conversion',
          'icon' => null
        )
			);

			$reportingUnits = getReportingUnits();
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    <h2 class="p-4">
      <i class="mdi mdi-format-list-bulleted-type"></i> UoM Conversion
      <button class="btn btn-primary btn-sm float-right" data-toggle="modal" data-target="#add-config"><i class="mdi mdi-plus"></i> Add</button>
    </h2>
    <br>
    <div class="table-responsive bg-light p-4">
      <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
        <thead class="bg-light p-2">
          <tr>
            <th>No</th>
            <th>UoM 1</th>
            <th>UoM 2</th>
            <th>Conversion Rate</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
            @foreach($uoms as $item)
              <tr>
                <td valign="center">{{ $loop->iteration }}</td>
                <td>{{ $item->uom1 }}</td>
                <td>{{ $item->uom2 }}</td>
                <td>{{ $item->ratio }}</td>
                <td>
									<span class="btn btn-sm btn-info" data-target="#edit-config" data-toggle="modal" data-config = "{{ json_encode($item) }}">
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
    <div class="modal-dialog">
      <!-- Modal content-->
      <form class="modal-content" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="modal-header">
          <h4 class="modal-title"><i class="mdi mdi-pencil"></i> Edit UoM Conversion</h4>
        </div>
        <div class="modal-body" id="edit-config-fields"></div>
        <div class="modal-footer">
					<input type="hidden" name="module" value="" />
          <button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
	</div>
  <div id="add-config" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <!-- Modal content-->
      <form class="modal-content" method="POST" action="{{ route('add-uom-conversion') }}" enctype="multipart/form-data">
        @csrf
        <div class="modal-header">
          <h4 class="modal-title"><i class="mdi mdi-plus"></i> Add UoM Conversion</h4>
        </div>
        <div class="modal-body">
					<div class="form-group">
						<label class="control-label">UoM 1</label>
						<select class="form-control" name="uom1" required placeholder="UoM..." required>
							<option></option>
							@foreach ($reportingUnits as $item)
								<option value="{{ $item['name'] }}">{{ $item['name'] }}</option>
							@endforeach
						</select>
					</div>
					<div class="form-group">
						<label class="control-label">UoM 2</label>
						<select class="form-control" name="uom2" required placeholder="UoM..." required>
							<option></option>
							@foreach ($reportingUnits as $item)
								<option value="{{ $item['name'] }}">{{ $item['name'] }}</option>
							@endforeach
						</select>
					</div>
					<div class="form-group">
						<label class="control-label">Conversion Rate</label>
						<input type="number" step="any" class="form-control" name="ratio" placeholder="Conversion Rate..." required />
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
				<input type="hidden" name="conversion_id" value="${$data.id}" />
				<div class="form-group">
					<label class="control-label">UoM 1</label>
					<select class="form-control" name="uom1" required placeholder="UoM 1...">
						<option></option>
						@foreach ($reportingUnits as $item)
							<option value="{{ $item['name'] }}" ${$data.uom1 == '{{$item['name']}}' ? 'selected' : ''}>{{ $item['name'] }}</option>
						@endforeach
					</select>
				</div>
				<div class="form-group">
					<label class="control-label">UoM 2</label>
					<select class="form-control" name="uom2" required placeholder="UoM 2...">
						<option></option>
						@foreach ($reportingUnits as $item)
							<option value="{{ $item['name'] }}" ${$data.uom2 == '{{$item['name']}}' ? 'selected' : ''}>{{ $item['name'] }}</option>
						@endforeach
					</select>
				</div>
				<div class="form-group">
					<label class="control-label">Ratio</label>
					<input type="number" step="any" class="form-control" value="${$data.ratio}" name="ratio" placeholder="Ratio..." />
				</div>
			`).clone();
		}

		$(function(){
			$('#edit-config').on('show.bs.modal', function(e){
				var config = $(e.relatedTarget).data('config');

				console.log(config);

				var field = returnFields(config);

				$('#edit-config-fields').html(field);

				$('#edit-config-fields').find('select').select2();

				$(this).find('form').prop('action', '/add-uom-conversion');
			})
		});
	</script>
@endsection
