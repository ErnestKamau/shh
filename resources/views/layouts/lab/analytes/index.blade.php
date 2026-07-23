@extends('layouts.lab.layout.app', ['dataTable'=>true, 'select2'=>true])

@section('title2')
  <title>Analytes</title>
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
          'link' => route('analytes'),
          'name' => 'Analytes',
          'icon' => null
        ),
      );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    <h2 class="p-4">
      <i class="mdi mdi-molecule"></i> Analytes
      <button class="btn btn-primary btn-sm float-right" data-toggle="modal" data-target="#add-analyte"><i class="mdi mdi-plus"></i> Add</button>
    </h2>
    <br>
    <div class="table-responsive bg-light p-4">
      <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
        <thead class="bg-light p-2">
			<tr>
			<th>#</th>
            <th>No</th>
            <th>Code</th>
            <th>Name</th>
            <th nowrap>Common Name</th>
            <th nowrap>Decimal Places</th>
            <th nowrap>Equivalent Weight</th>
            <th nowrap>Reporting Symbol</th>
            <th nowrap>Reporting Unit</th>
            <th>Method</th>
            <th>Equipment</th>
			<th>Font Italic</th>
            <th nowrap>Non Detectable</th>
            <th nowrap>Non Accredited</th>
            <th nowrap>Show on Report</th>
            <th>Active?</th>
          </tr>
        </thead>
        <tbody>
					@foreach($analytes as $analyte)
						<?php
							$methods = $analyte->methods();
							$equipments = $analyte->equipments();
						?>
						<tr>
							<td nowrap>
								<button class="btn btn-default text-primary btn-sm" data-methods='{{ json_encode(array_values($methods)) }}' data-equipments='{{ json_encode(array_values($equipments)) }}' data-analyte='{{ json_encode($analyte) }}' data-target="#edit-analyte" data-toggle="modal"><i class="mdi mdi-pencil-outline" data-toggle="tooltip" title="Edit Analyte"></i> <small class="hidden-sm-up">Edit</small> </button>
								{{-- <button class="btn btn-danger btn-sm"><i class="mdi mdi-delete-empty"></i> <small class="hidden-sm-up">Delete</small> </button> --}}
							</td>
							<td valign="center">{{ $loop->iteration }} </td>
							<td>{{ $analyte->code }}</td>
							<td><span class="text-primary btn" style="padding: 0px !important;font-size:13px" data-methods='{{ json_encode(array_values($methods)) }}' data-equipments='{{ json_encode(array_values($equipments)) }}' data-analyte='{{ json_encode($analyte) }}' data-target="#edit-analyte" data-toggle="modal">{{ $analyte->name }}</span> </td>
							<td>{{ $analyte->common_name }}</td>
							<td>{{ $analyte->decimal_places }}</td>
							<td>{{ number_format($analyte->equivalent_weight, $analyte->decimal_places) }}</td>
							<td>{{ $analyte->reporting_symbol }}</td>
							<td>{{ $analyte->reporting_unit }}</td>
							<td>{{ implode(", ", array_keys($methods)) ?? '-' }}</td>
							<td>{{ implode(", ", array_keys($equipments)) ?? '-' }}</td>
							<td class="text-small">{!! $analyte->is_italic == '1' ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>' !!}</td>
							<td class="text-small">{!! $analyte->non_detectable == '1' ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>' !!}</td>
							<td class="text-small">{!! $analyte->non_accredited == '1' ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>' !!}</td>
							<td class="text-small">{!! $analyte->show_on_report == '1' ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>' !!}</td>
							<td class="text-small">{!! $analyte->active == '1' ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>' !!}</td>
							
						</tr>
					@endforeach
        </tbody>
      </table>
    </div>
  </main>
@endsection

@section('script2')
  <div id="add-analyte" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
      <!-- Modal content-->
      <form class="modal-content modal-lg" method="POST" action="{{ route('add-analytes') }}" enctype="multipart/form-data">
        @csrf
        <div class="modal-header">
          <h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Analyte</h4>
        </div>
        <div class="modal-body row">
          <div class="col-sm-6">
            <div class="form-group">
              <label class="control-label">Analyte Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="name" placeholder="Analyte Name..." required />
            </div>
            <div class="form-group">
              <label class="control-label">Common Name</label>
              <input type="text" class="form-control" name="common_name" placeholder="Common Name..." />
            </div>
            <div class="form-group">
              <label class="control-label">Report Display <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="code" placeholder="Report Display..." required />
			</div>
			<div class="form-group">
              <label class="control-label">Equivalent Weight</label>
              <input type="text" class="form-control" name="equivalent_weight" placeholder="Equivalent Weight..." />
            </div>
            <div class="form-group">
              <label class="control-label">Analyte Decimal Places <span class="text-danger">*</span></label>
              <input type="number" min="0" step="1" class="form-control" name="decimal_places" value="0" placeholder="Analyte Decimal Places..." required />
            </div>
            <div class="form-group">
              <label class="control-label">Analyte Reporting Symbol</label>
              <input type="text" class="form-control" name="reporting_symbol" placeholder="Analyte Reporting Symbol..." />
            </div>
            <div class="form-group">
              <label class="control-label">Reporting Unit</label>
              <select class="form-control" name="reporting_unit">
                <option value="">Select Reporting Unit...</option>
                @foreach (getReportingUnits() as $g)
                  <option value="{{ $g['name'] }}">{{ $g['name'] }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="form-group">
              <label class="control-label">Method</label>
              <select class="form-control" name="method[]" multiple placeholder="Select Method...">
                <option></option>
                @foreach (getMethods() as $g)
                  <option value="{{ $g['id'] }}">{{ $g['name'] }}</option>
                @endforeach
              </select>
            </div>
            <div class="form-group">
              <label class="control-label">Equipment </label>
              <select class="form-control" name="equipment_id[]" multiple placeholder="Select Equipment...">
                <option></option>
                @foreach (getEquipment() as $g)
                  <option value="{{ $g['id'] }}">{{ $g['name'] }}</option>
                @endforeach
              </select>
            </div>
			<div class="form-group">
				<label for="" class="control-label"><input type="checkbox" name="is_italic" value="1" id=""> Report Font Italic</label>
			</div>
            <div class="form-group">
              <label class="control-label"><input type="checkbox" name="non_detectable" value="1" />  Not Detectable</label>
            </div>
            <div class="form-group">
              <label class="control-label"><input type="checkbox" name="non_accredited" value="1" />  Accredited</label>
            </div>
            <div class="form-group">
              <label class="control-label"><input type="checkbox" name="show_on_report" value="1" checked /> Show on Report</label>
            </div>
            <div class="form-group">
              <label class="control-label"><input type="checkbox" name="active" value="1" checked /> Active</label>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
	</div>
	<div id="edit-analyte" class="modal fade" role="dialog">
		<div class="modal-dialog modal-lg">
			<!-- Modal content-->
			<form class="modal-content" method="POST" enctype="multipart/form-data">
				@csrf
				<div class="modal-header">
					<h4 class="modal-title"><i class="mdi mdi-pencil-outline"></i> Edit Analyte</h4>
				</div>
				<div class="modal-body row"></div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				</div>
			</form>
		</div>
	</div>
	<script>
		$(function(){
			var formBody = function($analyte, $methods, $equipments){
				var $fB = $(`
					<div class="col-sm-6">
						<div class="form-group">
							<label class="control-label">Analyte Name <span class="text-danger">*</span></label>
							<input type="text" class="form-control" name="name" value="${ $analyte.name }" placeholder="Analyte Name..." required />
						</div>
						<div class="form-group">
							<label class="control-label">Common Name <span class="text-danger">*</span></label>
							<input type="text" class="form-control" name="common_name" value="${ $analyte.common_name }" placeholder="Analyte Name..." required />
						</div>
						<div class="form-group">
							<label class="control-label">Report Display <span class="text-danger">*</span></label>
							<input type="text" class="form-control" name="code" value="${ $analyte.code }" placeholder="Report Display..." required />
						</div>
						<div class="form-group">
							<label class="control-label">Equivalent Weight</label>
							<input type="text" class="form-control" name="equivalent_weight" value="${$analyte.equivalent_weight == null ? '' : $analyte.equivalent_weight}" placeholder="Equivalent Weight..." />
						</div>
						<div class="form-group">
							<label class="control-label">Analyte Decimal Places <span class="text-danger">*</span></label>
							<input type="number" min="0" step="1" class="form-control" name="decimal_places" value="${ $analyte.decimal_places }" placeholder="Analyte Decimal Places..." required />
						</div>
						<div class="form-group">
							<label class="control-label">Analyte Reporting Symbol</label>
							<input type="text" class="form-control" name="reporting_symbol" value="${ $analyte.reporting_symbol }" placeholder="Analyte Reporting Symbol..." />
						</div>
						<div class="form-group">
							<label class="control-label">Reporting Unit</label>
							<select class="form-control" name="reporting_unit">
								<option value="">Select Reporting Unit...</option>
								@foreach (getReportingUnits() as $g)
									<option value="{{ $g['name'] }}" ${ $analyte.reporting_unit == '{{ $g['name'] }}' ? 'selected' : '' }>{{ $g['name'] }}</option>
								@endforeach
							</select>
						</div>
					</div>
					<div class="col-sm-6">
						<div class="form-group">
							<label class="control-label">Method</label>
							<select class="form-control" name="method[]" multiple placeholder="Select Method...">
								<option></option>
								@foreach (getMethods() as $g)
									<option value="{{ $g['id'] }}" ${ $methods.indexOf({{ $g['id'] }}) > -1 ? 'selected' : '' }>{{ $g['name'] }}</option>
								@endforeach
							</select>
						</div>
						<div class="form-group">
							<label class="control-label">Equipment</label>
							<select class="form-control" name="equipment_id[]" multiple placeholder="Select Equipment...">
								<option></option>
								@foreach (getEquipment() as $g)
									<option value="{{ $g['id'] }}" ${ $equipments.indexOf({{ $g['id'] }}) > -1 ? 'selected' : '' }>{{ $g['name'] }}</option>
								@endforeach
							</select>
						</div>
						<div class="form-group">
							<label for="" class="control-label"><input type="checkbox" value="1" name="is_italic" ${$analyte.is_italic == 1 ? 'checked' : ''} id=""> Report Font Italic</label>
						</div>
						<div class="form-group">
							<label class="control-label"><input type="checkbox" name="non_detectable" value="1" ${ $analyte.non_detectable == 1 ? 'checked' : '' } />  Not Detectable</label>
						</div>
						<div class="form-group">
							<label class="control-label"><input type="checkbox" name="non_accredited" value="1" ${ $analyte.non_accredited == 1 ? 'checked' : '' } /> Accredited</label>
						</div>
						<div class="form-group">
							<label class="control-label"><input type="checkbox" name="show_on_report" value="1" ${ $analyte.show_on_report == 1 ? 'checked' : '' } /> Show on Report</label>
						</div>
						<div class="form-group">
							<label class="control-label"><input type="checkbox" name="active" value="1" ${ $analyte.active == 1 ? 'checked' : '' } /> Active</label>
						</div>
					</div>
				`);

				return $fB.clone();
			}

			$('#edit-analyte').on('show.bs.modal', function (e) {
				var $analyte = $(e.relatedTarget).data('analyte');
				var $methods = $(e.relatedTarget).data('methods');
				var $equipments = $(e.relatedTarget).data('equipments');

				var $fBText = formBody($analyte, $methods, $equipments);

				$fBText.find('select').select2();

				$(this).find('form').prop('action', '/analyte/'+$analyte.id)

				$(this).find('.modal-body').html($fBText);


			})
		})
	</script>
@endsection