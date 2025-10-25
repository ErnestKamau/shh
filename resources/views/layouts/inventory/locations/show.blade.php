@extends('layouts.personnel.layout.app', ['dataTable'=>true, 'select2'=>true])

@section('title2')
	<title> {{ $location->name }} | Location</title>
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
          'link' => route('inventory-home'),
          'name' => 'Inventory Management',
          'icon' => null
        ),
        array(
          'link' => route('inventory-locations'),
          'name' => 'Locations',
          'icon' => null
        ),
				array(
          'link' => '#',
          'name' => $location->name,
          'icon' => null
        )
      );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
		<h3 class="p-4">
			<i class="mdi mdi-microscope"></i> {{ $location->name }} <small class="text-muted"> | Location</small>
			<div class="btn btn-sm btn-transparent text-primary float-right m-2" data-target="#add-user-access" data-toggle="modal"><i class="mdi mdi-account-key"></i> User Access</div>
		</h3>
		<div class="bg-light p-4">
			<div class="table-responsive">
				<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
					<thead class="bg-light p-2">
						<tr>
							<th>No</th>
							<th nowrap>Name</th>
							<th nowrap>Email</th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						@foreach($location->users() as $element)
							<tr>
								<td valign="center">{{ $loop->iteration }}</td>
								<td nowrap>{{ $element->name }}</td>
								<td nowrap>{{ $element->email }}</td>
								<td nowrap>
									{{-- <button class="btn btn-primary btn-sm" data-target="#edit-analyte-{{ $loop->iteration }}" data-toggle="modal"><i class="mdi mdi-pencil-outline"></i> <small class="hidden-sm-up">Edit</small> </button> --}}
									<button class="btn btn-danger btn-sm" data-target="#remove-user-access-{{ $loop->iteration }}" data-toggle="modal"><i class="mdi mdi-delete-empty"></i> <small class="hidden-sm-up">Delete</small> </button>
									<div id="remove-user-access-{{ $loop->iteration }}" class="modal fade" role="dialog">
										<div class="modal-dialog">
											<!-- Modal content-->
											<form class="modal-content" method="POST" action="{{ route('remove-user-access', ['id'=>$location->id, 'user'=>$element->id]) }}" enctype="multipart/form-data">
												@csrf
												<div class="modal-header">
													<h4 class="modal-title"><i class="mdi mdi-delete"></i> Remove User Location</h4>
												</div>
												<div class="modal-body">
													<div class="form-group">
														<div class="alert alert-danger alert-callout">
															<i class="fas fa-exclamation-triangle"></i> Remove {{ $element->name }} from this Location?
														</div>
													</div>
												</div>
												<div class="modal-footer">
													<button type="submit" class="btn btn-danger"><i class="mdi mdi-content-save"></i> Remove</button>
													<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
												</div>
											</form>
										</div>
									</div>
								</td>
							</tr>
						@endforeach
					</tbody>
				</table>
			</div>
		</div>
	</main>
@endsection
@section('script2')
<div id="add-user-access" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<form class="modal-content" method="POST" action="{{ route('add-user-access', ['id'=>$location->id]) }}" enctype="multipart/form-data">
			@csrf
			<input type="hidden" name="category_id" value="{{ $location->id }}" />
			<div class="modal-header">
				<h4 class="modal-title"><i class="mdi mdi-account-group"></i> Add Users</h4>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<label class="control-label">Select Users</label>
					<select class="form-control" name="users[]" multiple placeholder="Select User...">
						@foreach (getUsers(true) as $item)
							<option value="{{ $item->id }}">{{ $item->name  }}</option>
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

<script type="text/javascript">
	$(function(){
		$('#create-an-order').on('show.bs.modal', function(e) {
			var subCat = {{ $location->id }}+" "+$(e.relatedTarget).data('subid');

			$('#create-an-order').find('[name="items[sub_category_id][]"]').val(subCat);
		});
	});
</script>
@endsection
