@extends('layouts.lab.layout.app', ['dataTable'=>true, 'select2'=>true])

@section('title2')
  <title> {{ $analysis_method->name }} | Analysis Methods</title>
  <style type="text/css">
    .tab-card {
      border:1px solid #eee;
    }

    .tab-card-header {
      background:none;
    }
    /* Default mode */
    .tab-card-header > .nav-tabs {
      border: none;
      margin: 0px;
    }
    .tab-card-header > .nav-tabs > li {
      margin-right: 2px;
    }
    .tab-card-header > .nav-tabs > li > a {
      border: 0;
      border-bottom:2px solid transparent;
      margin-right: 0;
      color: #737373;
      padding: 2px 15px;
    }

    .tab-card-header > .nav-tabs > li > a.show {
      border-bottom:2px solid #007bff;
      color: #007bff;
    }
    .tab-card-header > .nav-tabs > li > a:hover {
      color: #007bff;
    }

    .tab-card .nav-link.active{
      background-color: #dadccd !important;
      border: 1px solid #cccebf !important;
    }

    .tab-card-header > .tab-content {
      padding-bottom: 0;
    }

    .my-small-text{
      font-size: 12px !important;
    }

  </style>
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
        ),
				array(
          'link' => '#',
          'name' => $analysis_method->name,
          'icon' => null
        )
      );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    <h2 class="p-4">
      <i class="mdi mdi-cogs"></i> {{ $analysis_method->name }} <small class="text-muted"> | {{ $analysis_method->is_sampling_method == 0 && $analysis_method->is_ltm == 0 ? 'Analysis Method' : ($analysis_method->is_sampling_method == 1 ? 'Sampling Method' : 'Laboratory Test Method') }}</small>
    </h2>
    <div class="row no-gutters">
      <div class="col-sm-4 p-2">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title"><i class="mdi mdi-pencil-outline"></i> Edit Analysis Method</h5>
            <form method="POST" action="{{ route('edit-analysis-method', ['id'=>$analysis_method->id]) }}" enctype="multipart/form-data">
              @csrf
              <div class="form-group">
                <label class="control-label">Name</label>
                <input type="text" class="form-control" name="name" value="{{ $analysis_method->name }}" placeholder="Analysis Method Name..." required />
              </div>
              <div class="form-group">
                <label class="control-label">Code</label>
                <input type="text" class="form-control" name="code" value="{{ $analysis_method->code }}" placeholder="Analysis Method Code..." required />
              </div>
              <div class="form-group">
                <label class="control-label">Description</label>
                <textarea class="form-control" name="description" placeholder="Description..." required>{{ $analysis_method->description }}</textarea>
              </div>
              @if(Auth::user()->company_id == 0)
                <div class="form-group">
                  <label class="control-label">Company</label>
                  <select class="form-control" name="company_id" data-placeholder>
                    <option value="">Select Company...</option>
                    @foreach ($companies as $c)
                      <option value="{{ $c->id }}" {{ $c->id == $analysis_method->company_id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                  </select>
                </div>
              @endif
              <div class="form-group">
                <label class="control-label"><input type="checkbox" name="active" value="1" {{ $analysis_method->active == 1 ? 'checked' : '' }} /> Active</label>
              </div>
              <div class="form-group">
                <label class="control-label"><input type="checkbox" name="is_sampling_method" value="1" {{ $analysis_method->is_sampling_method == 1 ? 'checked' :'' }} /> Is Sampling Method</label>
              </div>
              <div class="form-group">
                <label class="control-label"><input type="checkbox" name="is_ltm" value="1" {{ $analysis_method->is_ltm == 1 ? 'checked' :'' }} /> Is Laboratory Test Method</label>
              </div>
              <div class="p-0">
                <button type="submit" class="btn btn-primary float-right"><i class="mdi mdi-content-save"></i> Save</button>
              </div>
            </form>
          </div>
        </div>
      </div>
      <div class="col-sm-8 p-2">
        <div class="card tab-card">
          <div class="card-header tab-card-header">
            <ul class="nav nav-tabs card-header-tabs" id="Elements-tabs" role="tablist">
              <li class="nav-item">
                <a class="nav-link active" id="Elements-tab" data-toggle="tab" href="#Elements" role="tab" aria-controls="Elements" aria-selected="true">Analytes</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" id="reagents-tab" data-toggle="tab" href="#reagents" role="tab" aria-controls="Reagents" aria-selected="false">Reagents</a>
              </li>
            </ul>
          </div>

          <div class="tab-content" id="Elements-tabs-content">
            <div class="tab-pane fade show active p-3" id="Elements" role="tabpanel" aria-labelledby="one-tab">
							<h5 class="card-title">Analytes
								{{-- <div class="btn btn-sm btn-info float-right" data-target="#add-analyte" data-toggle="modal"><i class="mdi mdi-plus"></i> Add</div> --}}
							</h5>
							<hr>
              <div class="table-responsive">
                <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
                  <thead class="bg-light p-2">
                    <tr>
											<th>No</th>
											<th>Code</th>
											<th>Name</th>
											<th nowrap>Common Name</th>
											<th nowrap>Decimal Places</th>
											<th nowrap>Equivalent Weight</th>
											<th nowrap>Reporting Symbol</th>
											<th nowrap>Reporting Unit</th>
											<th>Equipment</th>
											<th>Analysis</th>
											<th nowrap>Non Detectable</th>
											<th nowrap>Non Accredited</th>
											<th nowrap>Show on Report</th>
											<th nowrap>Is Manual</th>
								 			<th>Active?</th>
                    </tr>
                  </thead>
                  <tbody>
										@foreach($analysis_method->analytes() as $analyte)
                    
											<tr>
												<td valign="center">{{ $loop->iteration }}</td>
												<td>{{ $analyte->analyte->code ?? '' }}</td>
												<td>{{ $analyte->analyte->name ?? '' }}</td>
												<td>{{ $analyte->analyte->common_name ?? '' }}</td>
												<td>{{ $analyte->analyte->decimal_places }}</td>
												<td>{{ number_format($analyte->analyte->equivalent_weight ?? 0, $analyte->analyte->decimal_places ?? 0) }}</td>
												<td>{{ $analyte->analyte->reporting_symbol ?? '' }}</td>
												<td>{{ $analyte->analyte->reporting_unit ?? '' }}</td>
												<td>{{ $analyte->equipment->name ?? ''}}</td>
												<td>{{ $analyte->analysis_type->name }}</td>
												<td class="text-small">{!! isset($analyte->analyte->non_detectable) && $analyte->analyte->non_detectable == '1' ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>' !!}</td>
												<td class="text-small">{!! isset($analyte->analyte->non_accredited) && $analyte->analyte->non_accredited == '1' ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>' !!}</td>
												<td class="text-small">{!! isset($analyte->analyte->show_on_report) && $analyte->analyte->show_on_report == '1' ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>' !!}</td>
												<td class="text-small">{!! isset($analyte->analyte->is_manual) && $analyte->analyte->is_manual == '1' ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>' !!}</td>
												<td class="text-small">{!! isset($analyte->analyte->active) && $analyte->analyte->active == '1' ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>' !!}</td>
											</tr>
										@endforeach
									</tbody>
								</table>
							</div>
						</div>
            <form class="tab-pane fade p-3" method="POST" action="{{ route('update-method-reagents', ['method_id'=>$analysis_method->id]) }}" id="reagents" role="tabpanel" aria-labelledby="one-tab">
							@csrf
							<h5 class="card-title">Reagents
								<button class="btn btn-sm btn-success" ><i class="mdi mdi-content-save"></i> Save</button>
								<div class="btn btn-sm btn-info float-right" data-target="#reagent-modal" data-toggle="modal"><i class="mdi mdi-plus"></i> Add</div>
							</h5>
							<hr>
              <div class="table-responsive">
								{{-- {{ json_encode($analysis_method->reagents()) }} --}}
                <table id="reagents-table" class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
                  <thead class="bg-light p-2">
                    <tr>
											<th>Reagent</th>
											<th nowrap>Reporting Unit</th>
											<th>Quantity</th>
											<th></th>
                    </tr>
                  </thead>
                  <tbody id="reagents-holder" data-reagents="{{ json_encode($analysis_method->reagents()) }}"></tbody>
								</table>
							</div>
						</form>
          </div>
        </div>
      </div>
    </div>
  </main>
@endsection
@section('script2')
<div id="add-analyte" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <form class="modal-content" method="POST" action="{{ route('add-analysis-method-elements') }}" enctype="multipart/form-data">
      @csrf
      <input type="hidden" name="analysis_method_id" value="{{ $analysis_method->id }}" />
      <div class="modal-header">
        <h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Analysis Method Element</h4>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label class="control-label">Analyte</label>
          <select class="form-control" name="analyte_id" required>
            <option value="">Select Analyte</option>
            @foreach ($analytes as $a)
              <option value="{{ $a->id }}">{{ $a->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="form-group">
          <label class="control-label">Decimal Places</label>
          <input type="number" min="1" step="1" class="form-control" name="quantity" value="0" placeholder="Quantity..." required />
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
<div id="reagent-modal" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
      @csrf
      <input type="hidden" name="analysis_method_id" value="{{ $analysis_method->id }}" />
      <div class="modal-header">
        <h4 class="modal-title"><i class="mdi mdi-plus"></i>Add Reagent</h4>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label class="control-label">Reagent</label>
          <select class="form-control inventory-item" name="item_id" required>
						<option value="">Select Reagents</option>
						<?php $reagents_category_id = 20004; ?>
            @foreach (getInventoryItems($reagents_category_id) ?? array() as $a)
              <option value="{{ $a->id }}" data-reporting='{{ $a->unit_type }}'>{{ $a->name }}</option>
            @endforeach
          </select>
				</div>
				<div class="form-group form-group-sm">
					<label class="control-label">Reporting Unit</label>
					<input type="text" class="form-control reporting-unit" name="reporting_unit" readonly placeholder="Reporting Unit..." />
				</div>
        <div class="form-group">
          <label class="control-label">Quantity</label>
          <input type="number" class="form-control quantity" name="quantity" value="0" placeholder="Quantity..." required />
        </div>
        <div class="form-group">
          <label class="control-label"><input type="checkbox" name="active" value="1" checked /> Active</label>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary modify-reagent-btn" data-dismiss="modal"><i class="mdi mdi-content-save"></i> Save</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<script>
	var reagents = $('#reagents-holder').data('reagents') || {};

	var getTableRow = function($data){
		var $row = $(`<tr class="reagent-${$data.reagent_id}">
			<td nowrap>
				<span class="reagent">${$data.reagent_name}</span>
				<input type="hidden" name="reagent[]" value="${$data.reagent_id}" />
			</td>
			<td nowrap>
				<span class="unit">${$data.reagent_unit}</span>
				<input type="hidden" name="unit_type[]" value="${$data.reagent_unit}" />
			</td>
			<td nowrap>
				<span class="quantity">${$data.quantity}</span>
				<input type="hidden" name="quantity[]" value="${$data.quantity}" />
			</td>
			<td nowrap>
				<span class="btn btn-sm btn-default text-info" data-target="#reagent-modal" data-toggle="modal"
					data-reagent='${JSON.stringify($data)}'><i class="mdi mdi-pencil"></i></span>
				<span class="btn btn-sm btn-default text-danger delete-row"><i class="mdi mdi-delete"></i></span>
			</td>
		</tr>`);
		return $row.clone();
	}
	$(function(){
		var addTableRow = function(){
			$('#reagents-holder').empty();
			$('#reagents-table').DataTable().clear().destroy();
			var reagentCheck = {};
			reagents = reagents.length == 0 ? {} : reagents;
			$.each(reagents, function(re, reagent){
				console.log(re, reagent)
				if(reagentCheck[reagent.reagent_id] == undefined){
					var r = getTableRow(reagent);
					$('#reagents-holder').append(r);
					reagentCheck[reagent.reagent_id] = true;

					r.on('click', '.delete-row', function(){
						if(confirm("Are you sure you want to delete?")){
							r.remove();
						}
					})
				}
			});

			$('#reagents-table').dataTable({
				dom: 'Blfrtip',
        buttons: [
          'copy', 'csv', 'excel', 'pdf', 'print'
        ],
			});
		}

		addTableRow() //creates any reagents that available for the method at load
		$('.modify-reagent-btn').on('click', function(){
			var parentModalBody = $('#reagent-modal').find('.modal-body');
			var reagent = {
				reagent_name : parentModalBody.find('select.inventory-item').children('option:selected').text(),
				reagent_unit : parentModalBody.find('input.reporting-unit').val(),
				reagent_id : parentModalBody.find('select.inventory-item').children('option:selected').val(),
				quantity : parentModalBody.find('input.quantity').val()
			}

			reagents[reagent.reagent_id]  = reagent;

			addTableRow();

			parentModalBody.find('input.quantity').val('');
			parentModalBody.find('select.inventory-item').val('').trigger('change');
		});

		$('#reagent-modal').on('show.bs.modal', function(e){
			var data = $(e.relatedTarget).data('reagent');
			var parentModalBody = $('#reagent-modal').find('.modal-body');
			if(data!=undefined && data.reagent_id){
				parentModalBody.find('input.quantity').val(data.quantity || '0');
				parentModalBody.find('select.inventory-item').val(data.reagent_id || '').trigger('change');
			}
		});

		$('select.inventory-item').on('change', function(){
			var val = $(this).children('option:selected').data('reporting');
			console.log(val)
			$('input.reporting-unit').val(val).trigger('change');
		});
	});
</script>
@endsection