@extends($module == "Inventory-Management" ? 'layouts.inventory.layout.app' : 'layouts.personnel.layout.app', ['dataTable'=>true, 'select2'=>true])
<?php $module_text = implode(" ", explode("-", $module)); ?>
@section('title2')
  <title>{{ $materialType->name }} - {{ $config }} | {{ $module_text }}</title>
@endsection
@section('content2')
  <main>
		<?php $systemUnitsofMeasure = getReportingUnits(); ?>
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
          'link' => route('module-pre-configs', ['config'=>$config, 'module'=>$module]),
          'name' => $config,
          'icon' => null
        ),
        array(
          'link' => null,
          'name' => $materialType->name,
          'icon' => null
        )
      );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    <h2 class="p-4">
			<i class="mdi mdi-format-list-bulleted-type"></i>{{ $materialType->name }} - {{ $config }}
    </h2>
		<div class="row no-gutters">
			<div class="col-sm-4 p-2">
				<div class="card">
					<div class="card-body">
						<h5 class="card-title"><i class="mdi mdi-pencil-outline"></i> Edit Material Type</h5>
						<form method="POST" action="{{ route('add-module-pre-configs', ['id'=>$materialType->id, 'module'=>$module, 'config'=>$config]) }}" enctype="multipart/form-data">
							@csrf
							<div class="form-group">
								<label class="control-label">Name</label>
								<input type="text" class="form-control" name="name" value="{{ $materialType->name }}" placeholder="Name..." required />
							</div>
							<div class="form-group">
								<label class="control-label">Description</label>
								<textarea type="text" class="form-control" name="description" placeholder="Description...">{{ $materialType->description }}</textarea>
							</div>
							<div class="p-0">
								<button type="submit" class="btn btn-primary float-right"><i class="mdi mdi-content-save"></i> Save</button>
							</div>
						</form>
					</div>
				</div>
			</div>
			<div class="col-sm-8">
				<div class="card tab-card">
					<div class="card-header tab-card-header">
						<ul class="nav nav-tabs card-header-tabs" id="Elements-tabs" role="tablist">
							<li class="nav-item">
								<a class="nav-link active" id="conversions-tab" data-toggle="tab" href="#Conversions" role="tab" aria-controls="Conversions" aria-selected="false">Conversions</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" id="states-tab" data-toggle="tab" href="#States" role="tab" aria-controls="States" aria-selected="false">States</a>
							</li>
						</ul>
					</div>
					<div class="tab-content" id="Elements-tabs-content">
						<div class="tab-pane show active p-3" id="Conversions" role="tabpanel" aria-labelledby="one-tab">
							<h5 class="card-title mb-3">Conversions
								<span class="btn btn-default text-primary btn-sm float-right" data-target="#add-conversion-modal" data-toggle="modal">
									<i class="mdi mdi-plus"></i> New Conversion
								</span>
							</h5>
							<div class="table-responsive">
								<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
									<thead class="bg-light p-2">
										<tr>
											<th>No</th>
											<th nowrap>UoM</th>
											<th nowrap>UoM</th>
											<th nowrap>Conversion</th>
											<th></th>
										</tr>
									</thead>
									<tbody>
										<?php $looper = 0; ?>
										@foreach ($materialType->conversions() as $conversion)
											<?php $looper++; ?>
											<tr>
												<td>{{ $looper }}</td>
												<td>{{ $conversion->uom1 }}</td>
												<td>{{ $conversion->uom2 }}</td>
												<td>{{ $conversion->conversion }}</td>
												<td>
													<span class="btn btn-default btn-sm text-primary" data-item='{{ json_encode($conversion) }}'
														data-target="#add-conversion-modal" data-toggle="modal">
														<i class="mdi mdi-pencil"></i>
													</span>

													<span class="btn btn-default btn-sm text-danger" data-item='{{ json_encode($conversion) }}'
														data-target="#delete-conversion-modal" data-toggle="modal">
														<i class="mdi mdi-delete"></i>
													</span>
												</td>
											</tr>
										@endforeach
									</tbody>
								</table>
							</div>
						</div>
						<div class="tab-pane fade p-3" id="States" role="tabpanel" aria-labelledby="one-tab">
							<h5 class="card-title mb-3">States
								<span class="btn btn-default text-primary btn-sm float-right" data-target="#add-item-state-modal" data-toggle="modal">
									<i class="mdi mdi-plus"></i> New State
								</span>
							</h5>
							<div class="table-responsive">
								<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
									<thead class="bg-light p-2">
										<tr>
											<th>No</th>
											<th nowrap>State</th>
											<th nowrap>UoM</th>
											<th nowrap>Default</th>
											<th></th>
										</tr>
									</thead>
									<tbody>
										<?php $looper = 0; ?>
										@foreach ($materialType->states() as $state)
											<?php $looper++; ?>
											<tr>
												<td>{{ $looper }}</td>
												<td>{{ $state->name }}</td>
												<td>{{ $state->uom }}</td>
												<td>{!! $state->is_default == 1 ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-marker-check text-muted"></i>' !!}</td>
												<td>
													<span class="btn btn-default btn-sm text-primary" data-item='{{ json_encode($state) }}'
														data-target="#add-item-state-modal" data-toggle="modal">
														<i class="mdi mdi-pencil"></i>
													</span>

													<span class="btn btn-default btn-sm text-danger" data-item='{{ json_encode($state) }}'
														data-target="#delete-item-state-modal" data-toggle="modal">
														<i class="mdi mdi-delete"></i>
													</span>
												</td>
											</tr>
										@endforeach
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
  </main>
@endsection

@section('script2')
	<div id="add-conversion-modal" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<!-- Modal content-->
			<form class="modal-content" method="POST" action="{{ route('add-subcategory-conversion', ['id'=>$materialType->id]) }}" enctype="multipart/form-data">
				@csrf
				<div class="modal-header">
					<h4 class="modal-title"><span class="icon"></span> Conversion</h4>
				</div>
				<div class="modal-body"></div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				</div>
			</form>
		</div>
	</div>

	<div id="add-item-state-modal" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<!-- Modal content-->
			<form class="modal-content" method="POST" action="{{ route('add-subcategory-item-state', ['id'=>$materialType->id]) }}" enctype="multipart/form-data">
				@csrf
				<div class="modal-header">
					<h4 class="modal-title"><span class="icon"></span> State</h4>
				</div>
				<div class="modal-body"></div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				</div>
			</form>
		</div>
	</div>
	<div id="delete-conversion-modal" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<!-- Modal content-->
			<form class="modal-content" method="POST" action="{{ route('delete-item-conversion') }}" enctype="multipart/form-data">
				@csrf
				<div class="modal-header">
					<h4 class="modal-title"><i class="mdi mdi-delete"></i> Remove Conversion</h4>
				</div>
				<div class="modal-body">
					<input type="hidden" name="conversion_item_id" value="" />
					<div class="form-group">
						<div class="alert alert-danger">
							<i class="mdi mdi-alert fa-2x pull-left"></i> Are you sure you want to remove this conversion from this item?
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-danger"><i class="mdi mdi-delete"></i> Remove</button>
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				</div>
			</form>
		</div>
	</div>

	<div id="delete-item-state-modal" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<!-- Modal content-->
			<form class="modal-content" method="POST" action="{{ route('delete-item-state') }}" enctype="multipart/form-data">
				@csrf
				<div class="modal-header">
					<h4 class="modal-title"><i class="mdi mdi-delete"></i> Remove State</h4>
				</div>
				<div class="modal-body">
					<input type="hidden" name="state_item_id" value="" />
					<div class="form-group">
						<div class="alert alert-danger">
							<i class="mdi mdi-alert fa-2x pull-left"></i> Are you sure you want to remove this state from this item?
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-danger"><i class="mdi mdi-delete"></i> Remove</button>
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				</div>
			</form>
		</div>
	</div>
	<script>
		var getConversionFields = function(data={}){
			var $form = $(`
				<div class="form-group">
					<label class="control-label">Initial Unit of Measure</label>
					<select class="form-control" name="uom1" required>
						<option value="">Select Unit of Measure...</option>
						@foreach ($systemUnitsofMeasure as $g)
							<option value="{{ $g['id'] }}" ${data.uom1_id == '{{ $g['id'] }}' ? 'selected' : '' }>{{ $g['name'] }}</option>
						@endforeach
					</select>
				</div>
				<div class="form-group">
					<label class="control-label">Target Unit of Measure</label>
					<select class="form-control" name="uom2" required>
						<option value="">Select Unit of Measure...</option>
						@foreach ($systemUnitsofMeasure as $g)
							<option value="{{ $g['id'] }}" ${data.uom2_id == '{{ $g['id'] }}' ? 'selected' : '' }>{{ $g['name'] }}</option>
						@endforeach
					</select>
				</div>
				<div class="form-group">
					<label class="control-label">Conversion</label>
					<input type="number" step="any" class="form-control" name="conversion" value="${data.conversion || ''}" placeholder="Conversion..." required />
				</div>
			`);

			return $form.clone();
		}

		var getItemStateFields = function(data={}){
			var $form = $(`
				<div class="form-group">
					<label class="control-label">State</label>
					<input type="text" class="form-control" name="name" value="${data.name || ''}" placeholder="Name..." required />
				</div>
				<div class="form-group">
					<label class="control-label">Unit of Measure</label>
					<select class="form-control" name="uom" required>
						<option value="">Select Unit of Measure...</option>
						@foreach ($systemUnitsofMeasure as $g)
							<option value="{{ $g['id'] }}" ${data.uom_id == '{{ $g['id'] }}' ? 'selected' : '' }>{{ $g['name'] }}</option>
						@endforeach
					</select>
				</div>
				<div class="form-group">
					<label class="control-label">
						<input type="checkbox" name="is_default" value="1" ${data.is_default == "1" || data.is_default == 1 ? 'checked' : ''} /> Is default state
					</label>
				</div>
			`);

			return $form.clone();
		}
		$(function(){
			$('#add-conversion-modal').on('show.bs.modal', function(e){
				var conversion = $(e.relatedTarget).data('item') || {};
				var $modal = $(this);

				$modal.find('.modal-title .icon').html(conversion.id ? `<i class="mdi mdi-pencil"></i>` : `<i class="mdi mdi-plus"></i>`);

				if(conversion.id){
					$modal.find('form').append(`<input type="hidden" name="conversion_item_id" value="${conversion.id}" />`)
				}

				var $form = getConversionFields(conversion);
				$modal.find('.modal-body').html($form);

				$form.find('select').select2({
					dropdownParent: $modal,
					width: '100%'
				});
			});

			$('#delete-conversion-modal').on('show.bs.modal', function(e){
				var conversion = $(e.relatedTarget).data('item');

				$(this).find('[name="conversion_item_id"]').val(conversion.id);
			});

			$('#add-item-state-modal').on('show.bs.modal', function(e){
				var $state = $(e.relatedTarget).data('item') || {};
				var $modal = $(this);

				$modal.find('.modal-title .icon').html($state.id ? `<i class="mdi mdi-pencil"></i>` : `<i class="mdi mdi-plus"></i>`);

				if($state.id){
					$modal.find('form').append(`<input type="hidden" name="state_item_id" value="${$state.id}" />`)
				}

				var $form = getItemStateFields($state);
				$modal.find('.modal-body').html($form);

				$form.find('select').select2({
					dropdownParent: $modal,
					width: '100%'
				});
			});

			$('#delete-item-state-modal').on('show.bs.modal', function(e){
				var $state = $(e.relatedTarget).data('item');
				$(this).find('[name="state_item_id"]').val($state.id);
			});
		});
	</script>
@endsection
