@extends($module == "Inventory-Management" ? 'layouts.inventory.layout.app' : 'layouts.personnel.layout.app', ['dataTable'=>true, 'select2'=>true])
<?php $module_text = implode(" ", explode("-", $module)); ?>
@section('title2')
  <title>Currency Conversion | {{ $module_text }}</title>
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
          'name' => 'Currency Conversion',
          'icon' => null
        )
      );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    <h2 class="p-4">
      <i class="mdi mdi-format-list-bulleted-type"></i> Currency Conversion
      <button class="btn btn-primary btn-sm float-right" data-toggle="modal" data-target="#add-config"><i class="mdi mdi-plus"></i> Add</button>
    </h2>
    <br>
    <div class="table-responsive bg-light p-4">
      <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
        <thead class="bg-light p-2">
          <tr>
            <th>No</th>
            <th>Currency 1</th>
            <th>Currency 2</th>
            <th>Conversion Rate</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
            @foreach($currencies as $item)
              <tr>
                <td valign="center">{{ $loop->iteration }}</td>
                <td>{{ $item->currency_1_name }}</td>
                <td>{{ $item->currency_2_name }}</td>
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
    <div class="modal-dialog modal-lg">
      <!-- Modal content-->
      <form class="modal-content" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="modal-header">
          <h4 class="modal-title"><i class="mdi mdi-plus"></i> Edit Currency Conversion</h4>
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
    <div class="modal-dialog modal-lg">
      <!-- Modal content-->
      <form class="modal-content" method="POST" action="{{ route('add-currency-conversions') }}" enctype="multipart/form-data">
        @csrf
        <div class="modal-header">
          <h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Currency Conversion</h4>
        </div>
        <div class="modal-body">
					<div class="form-group">
						<label class="control-label">Currency 1</label>
						<select class="form-control" name="currency_1" required placeholder="Currency..." required>
							<option></option>
							@foreach (getCurrencies() as $item)
								<option value="{{ $item->id }}">{{ $item->name }}</option>
							@endforeach
						</select>
					</div>
					<div class="form-group">
						<label class="control-label">Currency 2</label>
						<select class="form-control" name="currency_2" required placeholder="Currency..." required>
							<option></option>
							@foreach (getCurrencies() as $item)
								<option value="{{ $item->id }}">{{ $item->name }}</option>
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
					<label class="control-label">Currency 1</label>
					<select class="form-control" name="currency_1" required placeholder="Currency...">
						<option></option>
						@foreach (getCurrencies() as $item)
							<option value="{{ $item->id }}" ${$data.currency_1 == {{$item->id}} ? 'selected' : ''}>{{ $item->name }}</option>
						@endforeach
					</select>
				</div>
				<div class="form-group">
					<label class="control-label">Currency 2</label>
					<select class="form-control" name="currency_2" required placeholder="Currency...">
						<option></option>
						@foreach (getCurrencies() as $item)
							<option value="{{ $item->id }}" ${$data.currency_2 == {{$item->id}} ? 'selected' : ''}>{{ $item->name }}</option>
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

				var field = returnFields(config);

				$('#edit-config-fields').html(field);
				$('#edit-config-fields').find('select').select2();

				$(this).find('form').prop('action', '/add-currency-conversions');
			})


			$('[name="has_credentials"]').on('change', function(){
				console.log(123);
				if($(this).is(':checked')){
					$("#passwords-holder").removeClass("hidden");
					$(".pass").attr('required', true);
				}
				else{
					$("#passwords-holder").addClass("hidden");
					$(".pass").removeAttr('required');
				}
			});

			$('[name="confirm_password"]').on('keyup', function(){
				var pass1 = $('[name="password"]').val();
				var pass2 = $(this).val();

				if(pass1 != pass2){
					$(this).siblings('.has-success').html('').addClass('text-success');
					$(this).siblings('.has-error').html(`<i class="mdi mdi-cancel"></i> Passwords did not match.`).addClass('text-danger');
				}
				else{
					$(this).siblings('.has-error').html('')
					$(this).siblings('.has-success').html('<i class="mdi mdi-check-circle"></i> Passwords Match!.')
				}
			});
		});
	</script>
@endsection
