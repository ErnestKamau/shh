@extends('layouts.workorder.layout.app', ['dataTable'=>true])

@section('title2')
  <title>Services</title>
@endsection
@section('content2')
  <main>
    <?php
			$items = array(
				array(
					'link' => route('workorder-home'),
					'name' => 'Work Order Management',
					'icon' => null
				),
				array(
					'link' => '#',
					'name' => 'Services',
					'icon' => null
				)
			);
		?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    <h3 class="p-4">
      <i class="mdi mdi-toolbox-outline"></i> Services
      <button class="btn btn-primary btn-sm float-right" data-toggle="modal" data-target="#add-workorder-service"><i class="mdi mdi-plus"></i> Add</button>
		</h3>
		<div class="bg-light p-4">
			<div class="table-responsive">
				<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
					<thead class="bg-light p-2">
						<tr>
							<th>No</th>
							<th>Name</th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						@if(count($services) > 0)
							@foreach($services as $service)
								<tr>
									<td valign="center">{{ $loop->iteration }}</td>
									<td>{{ $service->name }}</td>
									<td>
										<span class="btn btn-sm btn-primary" data-service="{{ json_encode($service) }}" data-target="#edit-workorder-service" data-toggle="modal">
											<i class="mdi mdi-pencil"></i>
										</span>
									</td>
								</tr>
							@endforeach
						@endif
					</tbody>
				</table>
			</div>
		</div>
  </main>
@endsection
@section('script2')
  <div id="add-workorder-service" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <!-- Modal content-->
      <form class="modal-content" method="POST" action="{{ route('add-workorder-service') }}" enctype="multipart/form-data">
        @csrf
        <div class="modal-header">
          <h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Service</h4>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label class="control-label">Name</label>
            <input type="text" class="form-control" name="name" value="" placeholder="Name..." required />
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
  </div>
  <div id="edit-workorder-service" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <!-- Modal content-->
      <form class="modal-content" method="POST"  enctype="multipart/form-data">
        @csrf
        <div class="modal-header">
          <h4 class="modal-title"><i class="mdi mdi-pencil"></i> Edit Service</h4>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label class="control-label">Name</label>
            <input type="text" class="form-control" name="name" value="" placeholder="Name..." required />
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
  </div>
	<script>
		$(function(){
			$('#edit-workorder-service').on('show.bs.modal', function(e){
				var target = $(e.relatedTarget).data('service');
				$(this).find('form').prop('action', '/add-workorder-service/'+target.id);
				$(this).find('form').attr('action', '/add-workorder-service/'+target.id);

				$(this).find('form').find('[name="name"]').val(target.name);
			});
		});
	</script>
@endsection
