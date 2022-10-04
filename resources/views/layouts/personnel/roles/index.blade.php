@extends('layouts.personnel.layout.app', ['dataTable'=>true, 'select2'=>true])

@section('title2')
  <title>Roles | Personnel Management</title>
@endsection
@section('content2')
  <main>
    <?php
      $items = array(
        array(
          'link' => route('organizational-roles'),
          'name' => 'Personnel Management',
          'icon' => null
        ),
        array(
          'link' => null,
          'name' => 'Roles',
          'icon' => null
        )
      );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    <h2 class="p-4">
      <i class="mdi mdi-key-change"></i>Roles
      <button class="btn btn-primary btn-sm float-right" data-toggle="modal" data-target="#add-role"><i class="mdi mdi-plus"></i> Add</button>
    </h2>
    <br>
    <div class="table-responsive bg-light p-4">
      <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
        <thead class="bg-light p-2">
          <tr>
            <th>No</th>
            <th>Name</th>
            <th>Description</th>
            <th>Active</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
            @foreach($roles as $item)
              <tr>
                <td valign="center">{{ $loop->iteration }}</td>
                <td>{{ $item->name }}</td>
                <td>{{ $item->description }}</td>
								<td class="text-small">{!! $item->active == '1' ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>' !!}</td>
                <td nowrap>
									<button class="btn btn-primary btn-sm" data-target="#edit-role-{{$item->id}}" data-toggle="modal" data-role='{{ json_encode($item) }}'><i class="mdi mdi-pencil-outline"></i> <small class="hidden-sm-up">Edit</small> </button>
                  <a class="btn btn-success btn-sm" href="{{ route('view-organizational-role', ['id'=>$item->id]) }}"><i class="mdi mdi-eye-outline"></i> <small class="hidden-sm-up">Show</small> </a>

                  <div id="edit-role-{{$item->id}}" class="modal fade" role="dialog">
                    <div class="modal-dialog">
                      <!-- Modal content-->
                      <form class="modal-content" method="POST" enctype="multipart/form-data" action="{{ route('edit-organizational-role', ['id'=>$item->id]) }}">
                        @csrf
                        <div class="modal-header">
                          <h4 class="modal-title"><i class="mdi mdi-pencil"></i> Edit Role {{$item->name}}</h4>
                        </div>
                        <div class="modal-body" id="edit-role-fields">
                          <div class="form-group">
                            <label class="control-label">Name</label>
                            <input type="text" class="form-control" name="name" value="{{ $item->name }}" placeholder="Name..." required />
                          </div>
                          <div class="form-group">
                            <label class="control-label">Description</label>
                            <textarea class="form-control" name="description" placeholder="Description..." required>{{ $item->description }}</textarea>
                          </div>
                          <div class="form-group">
                            <label class="control-label"><input type="checkbox" name="active" value="1" checked /> Active</label>
													</div>
													<div class="form-group">
														<label class="control-label">Role Level</label>
														<input type="number" class="form-control" name="level" value="{{ $item->level }}" placeholder="Level..." required />
													</div>
                        </div>
                        <div class="modal-footer">
                          <button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
                          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        </div>
                        </div>
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
  </main>
@endsection

@section('script2')
	<script>
		var getEditRoleForm = function($data){
			var formGrp = $(`
				<div class="form-group">
					<label class="control-label">Name</label>
					<input type="text" class="form-control" name="name" value="${ $role.name }" placeholder="Name..." required />
				</div>
				<div class="form-group">
					<label class="control-label">Description</label>
					<textarea class="form-control" name="description" placeholder="Description..." required>${ $role.description }</textarea>
				</div>
			`);

			return $formGrp.clone();
		}

		// $(function(){
		// 	$('#edit-role').on('show.bs.modal', function (e) {
		// 		var url = "/edit-organizational-role/"+$data.item;

		// 		$(this).find('form').prop('action', url);
		// 		var $role = $(e.relatedTarget).data('role');
		// 		$('#edit-role-fields').html(getEditRoleForm($role));
		// 	});
		// });
	</script>

  <div id="add-role" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <!-- Modal content-->
      <form class="modal-content" method="POST" action="{{ route('add-organizational-role') }}" enctype="multipart/form-data">
        @csrf
        <div class="modal-header">
          <h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Role</h4>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label class="control-label">Name</label>
            <input type="text" class="form-control" name="name" value="" placeholder="Name..." required />
          </div>
          <div class="form-group">
            <label class="control-label">Description</label>
            <textarea class="form-control" name="description" value="" placeholder="Description..." required></textarea>
					</div>
					<div class="form-group">
						<label class="control-label"><input type="checkbox" name="active" value="1" checked /> Active</label>
					</div>
					<div class="form-group">
            <label class="control-label">Role Level</label>
            <input type="number" class="form-control" name="level" value="1" placeholder="Level..." required />
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
  </div>
@endsection
