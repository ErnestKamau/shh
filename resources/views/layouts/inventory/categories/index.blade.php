@extends('layouts.inventory.layout.app', ['dataTable'=>true, 'select2'=>true])

@section('title2')
  <title>Inventory Categories</title>
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
          'link' => route('inventory-categories'),
          'name' => 'Categories',
          'icon' => null
        )
      );

			$userCanAdd = \Auth::user()->can('inventory.components.categories.add');
			$userCanEdit = \Auth::user()->can('inventory.components.categories.edit');
			$userCanDelete = \Auth::user()->can('inventory.components.categories.delete');
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    <h3 class="p-4">
      <i class="mdi mdi-format-list-bulleted-type"></i>Categories

			@if($userCanAdd)
      	<button class="btn btn-primary btn-sm float-right" data-toggle="modal" data-target="#add-inventory-category"><i class="mdi mdi-plus"></i> Add</button>
			@endif
			<span class="btn btn-transparent btn-sm float-right" data-toggle="modal" data-target="#jump-to-item-modal">
				<i class="mdi mdi-home-search"></i>
				Find Item
			</span>
		</h3>
		<div class="bg-light p-4">
			<div class="table-responsive">
				<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
					<thead class="bg-light p-2">
						<tr>
							<th>No</th>
							<th>Image</th>
							<th>Name</th>
							<th>Description</th>
							<th>Available</th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						@if(count($categories) > 0)
							@foreach($categories as $category)
								<tr>
									<td valign="center">{{ $loop->iteration }}</td>
									<td><img src="{{ $category->image }}" style="width: 125px" /></td>
									<td><a href="{{ route('show-inventory-category', ['id'=>$category->id]) }}"> {{ ucwords($category->name) }}</a></td>
									<td>{{ $category->description }}</td>
									<td>
										@if(intval($category->minimum_level) > intval($category->available()['available']))
											<span class="pl-2 pr-2 pt-1" title="Requires Restocking">
												<i class="mdi mdi-alert text-danger"></i>
											</span>
										@endif
										{{ number_format($category->available()['available'])." ".$category->unit_type }} <small class="text-muted">(+{{ number_format($category->available()['pending'])." ".$category->unit_type }} pending)</small>
									</td>
									<td nowrap>
										@if($userCanEdit)
											<button class="btn btn-transparent text-primary btn-sm" data-target="#edit-inventory-category-{{ $loop->iteration }}" data-toggle="modal"><i class="mdi mdi-pencil-outline"></i> <small class="hidden-sm-up">Edit</small> </button>
										@endif
										<a class="btn btn-transparent text-success btn-sm" href="{{ route('show-inventory-category', ['id'=>$category->id]) }}"><i class="mdi mdi-eye-outline"></i> <small class="hidden-sm-up">Show</small> </a>
										@if($userCanEdit)
										<div id="edit-inventory-category-{{ $loop->iteration }}" class="modal fade" role="dialog">
											<div class="modal-dialog">
												<!-- Modal content-->
												<form class="modal-content" method="POST" action="{{ route('edit-inventory-category', ['id'=>$category->id]) }}" enctype="multipart/form-data">
													@csrf
													<div class="modal-header">
														<h4 class="modal-title"><i class="mdi mdi-pencil-outline"></i> Edit Inventory Category</h4>
													</div>
													<div class="modal-body">
														<div class="form-group">
															<label class="control-label">Name</label>
															<input type="text" class="form-control" name="name" value="{{ $category->name }}" placeholder="Name..." required />
														</div>
														<div class="form-group">
															<label class="control-label">Description</label>
															<textarea class="form-control" name="description" placeholder="Description..." required>{{ $category->description }}</textarea>
														</div>
														<div class="form-group">
															<label class="control-label">Image</label>
															<input type="file" class="form-control" name="image"  />
														</div>
													</div>
													<div class="modal-footer">
														<button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
														<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
													</div>
												</form>
											</div>
										</div>
										@endif
										@if($userCanDelete)
											<button class="btn btn-transparent text-danger btn-sm" data-id="{{ $category->id }}" data-toggle="modal" data-target="#delete-this-category-modal">
												<i class="mdi mdi-delete-empty"></i>
												<small class="hidden-sm-up">Delete</small>
											</button>
										@endif
									</td>
								</tr>
							@endforeach
						@endif
					</tbody>
				</table>
				@if(count($categories) == 0)
					<div class="alert alert-info">
						<i class="mdi mdi-alert"></i> No Inventory Categories added yet.
					</div>
				@endif
			</div>
		</div>
  </main>
@endsection

@section('script2')
	<div id="jump-to-item-modal" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<!-- Modal content-->
			<div class="modal-content" method="POST" enctype="multipart/form-data">
				@csrf
				<div class="modal-header">
					<h4 class="modal-title"><i class="mdi mdi-home-search"></i> Find Item</h4>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<label class="control-label">Select Item</label>
						<select class="form-control" id="selected-item" name="item" data-placeholder="Select Item..."></select>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>
	@if($userCanAdd)
	<div id="add-inventory-category" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <!-- Modal content-->
      <form class="modal-content" method="POST" action="{{ route('add-inventory-category') }}" enctype="multipart/form-data">
        @csrf
        <div class="modal-header">
          <h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Inventory Category</h4>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label class="control-label">Name</label>
            <input type="text" class="form-control" name="name" value="" placeholder="Name..." required />
          </div>
          <div class="form-group">
            <label class="control-label">Description</label>
            <textarea class="form-control" name="description" placeholder="Description..." required></textarea>
          </div>
          <div class="form-group">
            <label class="control-label">Image</label>
            <input type="file" class="form-control" name="image" required />
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
  </div>
	@endif
	@if($userCanDelete)
  <div id="delete-this-category-modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <!-- Modal content-->
      <form class="modal-content" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="modal-header">
          <h4 class="modal-title"><i class="mdi mdi-delete"></i> Delete Category</h4>
        </div>
        <div class="modal-body">
          <div class="alert alert-danger">
            <i class="mdi mdi-delete"></i> Proceed with removing this category?
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-danger"><i class="mdi mdi-trash"></i> Yes, Delete</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
  </div>
	@endif
	<script>
		$(function(){
			$('#delete-this-category-modal').on('show.bs.modal', function(e){
				var $form = $(this).find('form');
				var id = $(e.relatedTarget).data('id');

				$form.prop('action', '/inventory-category/'+id+'/delete');
				$form.attr('action', '/inventory-category/'+id+'/delete');
			});

			$('#selected-item').on('change', function(){
				window.location = '/show-inventory-items/fetch-category/'+$(this).val();
			});

			$('#selected-item').select2({
				ajax: {
					url: '{{ route("get_items_via_ajax") }}',
					data: function (params) {
						var query = {
							search: params.term,
							page: params.page || 1
						}
						return query;
					}
				},
				placeholder: 'Please Select Inventory Item...'
			});
		});
	</script>
@endsection
