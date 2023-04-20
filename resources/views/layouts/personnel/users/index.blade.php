@extends('layouts.personnel.layout.app', ['dataTable'=>true, 'select2'=>true])

@section('title2')
  <title>Personnel | Personnel Management</title>
@endsection
@section('content2')
  <main>
    <?php
      $items = array(
        array(
          'link' => route('personnel-home'),
          'name' => 'Personnel Management',
          'icon' => null
        ),
        array(
          'link' => null,
          'name' => 'Personnel',
          'icon' => null
        )
      );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    <h2 class="p-4">
			<i class="mdi mdi-format-list-bulleted-type"></i>Personnel
			<small class="label badge-pill bg-white my-small-text pt-1 pl-4 pr-4 pb-1 mr-1">
				<i class="mdi mdi-account-group text-info"></i> Shared <span class="badge badge-info badge-pill">{{ $license_count['shared_user']."/".mamboSawa('shared_users') }}</span>
			</small>
			<small class="label badge-pill bg-white my-small-text pt-1 pl-4 pr-4 pb-1">
				<i class="mdi mdi-account text-success"></i> Named <span class="badge badge-success badge-pill">{{ $license_count['named_user']."/".mamboSawa('named_users') }}</span>
			</small>
      <button class="btn btn-primary btn-sm float-right" data-toggle="modal" data-target="#add-personnel"><i class="mdi mdi-plus"></i> Add</button>
    </h2>
	<br>
	<div class="card tab-card">
		<div class="card-header tab-card-header">
			<ul class="nav nav-tabs card-header-tabs" id="personnel-tab" role="tablist">
				<li class="nav-item">
					<a href="#active-personnel-tab" class="nav-link active" id="active-personel" data-toggle="tab" role="tab" aria-controls="activepersonel" aria-selected="true" > <i class="mdi mdi-account" style="color: black; font-size:15px"></i> Active Personnel</a>
				</li>
				<li class="nav-item">
					<a href="#deactive-personnel-tab" class="nav-link" id="deactive-personnel" data-toggle="tab" role="tab" aria-controls="deactivepersonel" aria-selected="true" > <i class="mdi mdi-account-lock" style="color: black; font-size:15px"></i> Deactivated Personnel</a>
				</li>
			</ul>
		</div>
		<div class="tab-content" id="personel-tabs-content">
			<div class="tab-pane fade show active p-3" id="active-personnel-tab" role="tabpanel" aria-labelledby="one-tab" >
				<h5 class="card-title"> <i class="mdi mdi-account"></i> Active Personnel</h5>
				<div class="table-responsive bg-light p-4">
				  <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
					<thead class="bg-light p-2">
					  <tr>
						<th>No</th>
						<th>Designation</th>
						<th>First Name</th>
						<th>Middle Name</th>
						<th>Last Name</th>
						<th>Department</th>
						<th>JD</th>
						<th>Email</th>
						<th>Employment Date</th>
						<th>License Type</th>
						<th>Active</th>

					  </tr>
					</thead>
					<tbody>
					<?php $i = 1;?>
						@foreach($users as $item)
						@if($item->active ==1)
						
						  <tr>
						  <td nowrap style="width: 90px !important;">
							  {{$i}}
							  <a class="btn btn-success btn-sm" data-toggle="tooltip" title="View" href="{{ route('view-personnel', ['id'=>$item->id]) }}"><i class="mdi mdi-eye-outline"></i> <small class="hidden-sm-up">Show</small> </a>
							  @if(auth()->user()->CheckDeactivatePersonnel())
								<span class="btn btn-danger btn-sm {{auth()->user()->is_support_staff == 0 ? 'hidden' : ''}}" data-toggle="modal" data-target="#lock-user-{{$item->id}}" data-toggle="tooltip" title="Deactivate Personnel"><i class="mdi mdi-account-lock"></i></span>
								<div id="lock-user-{{$item->id}}" class="modal fade" role="dialog">
									<div class="modal-dialog">
										<form class="modal-content" method="POST" action="{{ route('personnel-state',['id'=>$item->id]) }}" enctype="multipart/form-data" >
											@csrf
											<div class="modal-header">
												<h3 class="modal-title"> <i class="mdi mdi-account-lock"></i> Deactivate {{ $item->first_name }} {{$item->last_name}} </h3>
											</div>
											<div class="modal-body">
											  <div class="form-group">
												  <label class="control-label">State</label>
												  <select name="state" id="select-state" class="form-control" aria-readonly="true">
													  <option value="active" {{ $item->active == 1 ? 'selected' : '' }}>Activate</option>
													  <option value="deactive" {{ $item->active == 0 ? 'selected' : '' }}>Deactivate</option>
												  </select>
											  </div>
											</div>
											<div class="modal-footer">
												<button type="submit" class="btn btn-primary"> <i class="mdi mdi-content-save"></i> Save</button>
												<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
											</div>
										</form>
									</div>
								</div>
								@endif
							  <!-- --------------------reset password---------- -->
								<span data-toggle="modal" data-target="#reset-password-{{$item->id}}" class="btn btn-info btn-sm" data-toggle="tooltip" title="Reset Password" ><i class="mdi mdi-key-change"></i> <small class="hidden-sm-up">Reset Password</small> </span>

								<div id="reset-password-{{$item->id}}" class="modal fade" role="dialog">
									<div class="modal-dialog">
										<form action="{{ route('reset-personnel',['id'=>$item->id]) }}" method="POST" class="modal-content">
											@csrf
											<div class="modal-header">
												<h3 class="modal-title"> <i class="mdi mdi-key-change"></i> Reset {{$item->first_name}} {{$item->last_name}} Password! </h3>
											</div>
											<div class="modal-body">
												<div class="form-group">
													<label class="control-label">Password</label>

													<input type="password" id="pass"  placeholder="Type Password..." class="form-control" required pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{6,}" name="password" onchange="form.pwd2.pattern = RegExp.escape(this.value);">
												</div>
												<div class="form-group">
													<label class="control-label">Confirm Password</label>

													<input type="password" id="passcon" class="form-control" placeholder="Confirm Password..." required onkeyup="activecheck()" name="con_password">
												</div>
											</div>
											<div class="modal-footer">
												<button type="submit" class="btn btn-primary"> <i class="mdi mdi-content-save"></i> Save </button>
												<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
											</div>
										</form>
									</div>
								</div>
							</td>
							<td>{{ $item->designation }}</td>
							<td>{{ $item->first_name }}</td>
							<td>{{ $item->middle_name }}</td>
							<td>{{ $item->last_name }}</td>
							<td>{{ $item->department_name }}</td>
							<td>{{ $item->position }}</td>
							<td>{{ $item->email }}</td>
							<td>{{ $item->employment_date }}</td>
							<td>{{ $item->license_type }}</td>
							<td class="text-small">{!! $item->active == '1' ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>' !!}</td>
							
						  </tr>
						  <?php $i++ ;?>
						  @endif
						@endforeach
					</tbody>
				  </table>
				</div>
			</div>
			<div class="tab-pane fade show p-3" id="deactive-personnel-tab" role="tabpanel" aria-labbellby="one-tab" >
				<h5 class="card-title"> <i class="mdi mdi-account-lock"></i> Deactivated Perssonels</h5>
				<div class="table-responsive bg-light p-4">
				  <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
					<thead class="bg-light p-2">
					  <tr>
						<th>No</th>
						<th>Designation</th>
						<th>First Name</th>
						<th>Middle Name</th>
						<th>Last Name</th>
						<th>Department</th>
						<th>JD</th>
						<th>Email</th>
						<th>Employment Date</th>
						<th>Active</th>
						


					  </tr>
					</thead>
					<tbody>
						<?php $z = 1?>
						@foreach($users as $item)
						@if($item->active == 0)
						  <tr>
							<td valign="center">{{ $z}}</td>
							<td>{{ $item->designation }}</td>
							<td>{{ $item->first_name }}</td>
							<td>{{ $item->middle_name }}</td>
							<td>{{ $item->last_name }}</td>
							<td>{{ $item->department_name }}</td>
							<td>{{ $item->position }}</td>
							<td>{{ $item->email }}</td>
							<td>{{ $item->employment_date }}</td>
							<td class="text-small">{!! $item->active == '1' ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>' !!}</td>
							<td nowrap>
							  <a class="btn btn-success btn-sm" href="{{ route('view-personnel', ['id'=>$item->id]) }}"><i class="mdi mdi-eye-outline"></i> <small class="hidden-sm-up">Show</small> </a>
							  <span class="btn btn-danger btn-sm" data-toggle="modal" data-target="#lock-user-{{$item->id}}"><i class="mdi mdi-lock-open-variant"></i></span>
							  <div id="lock-user-{{$item->id}}" class="modal fade" role="dialog">
								  <div class="modal-dialog">
									  <form class="modal-content" method="POST" action="{{ route('personnel-state',['id'=>$item->id]) }}" enctype="multipart/form-data" >
										  @csrf
										  <div class="modal-header">
											  <h3 class="modal-title"> <i class="mdi mdi-account-lock"></i> Activate {{ $item->first_name }} {{$item->last_name}} </h3>
										  </div>
										  <div class="modal-body">
											<div class="form-group">
												<label class="control-label">State</label>
												<select name="state" id="select-state" class="form-control" aria-readonly="true">
													<option value="active" {{ $item->active == 1 ? 'selected' : '' }}>Activate</option>
													<option value="deactive" {{ $item->active == 0 ? 'selected' : '' }}>Deactivate</option>
												</select>
											</div>
										  </div>
										  <div class="modal-footer">
											  <button type="submit" class="btn btn-primary"> <i class="mdi mdi-content-save"></i> Save</button>
											  <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
										  </div>
									  </form>
								  </div>
							  </div>
							  <!-- --------------------reset password---------- -->
								<a data-toggle="modal" data-target="#reset-password-{{$item->id}}" class="btn btn-info btn-sm" ><i class="mdi mdi-key-change"></i> <small class="hidden-sm-up">Reset Password</small> </a>

								<div id="reset-password-{{$item->id}}" class="modal fade" role="dialog">
									<div class="modal-dialog">
										<form action="{{ route('reset-personnel',['id'=>$item->id]) }}" method="POST" class="modal-content">
											@csrf
											<div class="modal-header">
												<h3 class="modal-title"> <i class="mdi mdi-account-key"></i> Reset {{$item->first_name}} {{$item->last_name}} Password! </h3>
											</div>
											<div class="modal-body">
												<div class="form-group">
													<label class="control-label">Password</label>
													<input type="password" id="pass"  placeholder="Type Password..." class="form-control" required pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{6,}" name="password" onchange="form.pwd2.pattern = RegExp.escape(this.value);">
												</div>
												<div class="form-group">
													<label class="control-label">Confirm Password</label>
													<input type="password" id="passcon" class="form-control" placeholder="Confirm Password..." required onkeyup="activecheck()" name="con_password">
												</div>
											</div>
											<div class="modal-footer">
												<button type="submit" class="btn btn-primary"> <i class="mdi mdi-content-save"></i> Save </button>
												<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
											</div>
										</form>
									</div>
								</div>
							</td>
						  </tr>
						  <?php $z++?>
						  @endif
						@endforeach
					</tbody>
				  </table>
				</div>
			</div>
		</div>
	</div>
  </main>
@endsection

@section('script2')
  <div id="add-personnel" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
      <!-- Modal content-->
      <form autocomplete="off" class="modal-content" method="POST" action="{{ route('add-personnel', ['id'=>time()]) }}" enctype="multipart/form-data">
				@csrf
				<input autocomplete="off" name="hidden" type="password" style="display:none;">
        <div class="modal-header">
          <h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Personnel</h4>
        </div>
        <div class="modal-body">
					<div class="row">
						<div class="col-sm-6">
							<div class="form-group">
								<label class="control-label">Designation <span class="text-danger">*</span></label>
								<select name="designation" class="form-control" placeholder="Designation..." required>
									<option></option>
									@foreach (getModulePreconfig("Designation", "Personnel-Management") as $item)
										<option value="{{ $item->id }}">{{ $item->name }}</option>
									@endforeach
								</select>
							</div>
							<div class="form-group">
								<label class="control-label">First Name <span class="text-danger">*</span></label>
								<input type="text" class="form-control" name="first_name" value="" placeholder="First Name..." required />
							</div>
							<div class="form-group">
								<label class="control-label">Middle Name</label>
								<input type="text" class="form-control" name="middle_name" value="" placeholder="Middle Name..." />
							</div>
							<div class="form-group">
								<label class="control-label">Last Name</label>
								<input type="text" class="form-control" name="last_name" value="" placeholder="Last Name..." />
							</div>
							<div class="form-group">
								<label class="control-label">Photo</label>
								<input type="file" class="form-control" name="image" />
							</div>
							<div class="form-group">
								<label class="control-label">Email <span class="text-danger">*</span></label>
								<input type="email" class="form-control" name="email" value="" placeholder="Email..." required />
							</div>
							<div class="form-group">
								<label class="control-label">Phone</label>
								<input type="text" class="form-control" name="phone" value="" placeholder="Phone..." />
							</div>
							<div class="form-group">
								<label class="control-label">ID Number/Passport No <span class="text-danger">*</span></label>
								<input type="text" class="form-control" name="id_number" value="" placeholder="ID Number..." required />
							</div>
							<div class="form-group">
								<label class="control-label">Date of Birth</label>
								<input type="date" class="form-control" name="date_of_birth" value="" placeholder="Date of Birth..." />
							</div>
						</div>
						<div class="col-sm-6">
							<div class="form-group">
								<label class="control-label">Employment Date</label>
								<input type="date" class="form-control" name="employment_date" value="" placeholder="Employment Dat..." />
							</div>
							<div class="form-group">
								<label class="control-label">Education Level</label>
								<select name="educational_level" class="form-control" placeholder="Education Level...">
									<option></option>
									@foreach (getModulePreconfig("Educational Levels", "Personnel-Management") as $item)
										<option value="{{ $item->id }}">{{ $item->name }}</option>
									@endforeach
								</select>
							</div>
							<div class="form-group">
								<label class="control-label">Position <span class="text-danger">*</span></label>
								<select name="position" class="form-control" placeholder="Position..." required>
									<option></option>
									@foreach (getModulePreconfig("Job Description", "Personnel-Management") as $item)
										<option value="{{ $item->id }}">{{ $item->name }}</option>
									@endforeach
								</select>
							</div>
							<div class="form-group">
								<label class="control-label">Department <span class="text-danger">*</span></label>
								<select name="department" class="form-control" placeholder="Department..." required>
									<option></option>
									@foreach (getDepartments() as $item)
										<option value="{{ $item->id }}">{{ $item->name }}</option>
									@endforeach
								</select>
							</div>
							<div class="form-group">
								<label class="control-label">KRA PIN</label>
								<input type="text" class="form-control" name="kra_pin" value="" placeholder="KRA PIN..." />
							</div>
							<div class="form-group">
								<label class="control-label">NSSF</label>
								<input type="text" class="form-control" name="nssf" value="" placeholder="NSSF..." />
							</div>
							<div class="form-group">
								<label class="control-label">NHIF</label>
								<input type="text" class="form-control" name="nhif" value="" placeholder="NHIF..." />
							</div>
							<div class="form-group">
								<label class="control-label">Signature</label>
								<input type="file" name="signature" class="form-control">
							</div>
							<div class="form-group">
								<label class="control-label">User License <span class="text-danger">*</span></label>
								<select name="user_license" class="form-control" placeholder="User License..." required>
									<option></option>
									@foreach (getUserLicenses() as $i=>$n)
										<option value="{{ $i }}" {{ intval($license_count[$i]) == intval(mamboSawa($i.'s')) ? 'disabled' : '' }}>{{ $n }} {{ $license_count[$i]."/".mamboSawa($i.'s') }}</option>
									@endforeach
								</select>
							</div>

							<div class="form-group">
								<label class="control-label">
									<input type="checkbox" name="has_credentials" value="1" /> Create User Passwords
								</label>
							</div>
							<div class="hidden" id="passwords-holder">
								<div class="form-group">
									<label>Password</label>
									<input autocomplete="off" type="password" title="Must contain at least one number and one uppercase and lowercase letter, and at least 8 or more characters" class="form-control pass" name="main_password" placeholder="Password..." />
									<div class="has-success"></div>
								</div>
								<div class="form-group" >
									<label>Confirm Password</label>
									<input type="password" title="Must contain at least one number and one uppercase and lowercase letter, and at least 8 or more characters" class="form-control pass" name="confirm_password" placeholder="Confirm Password..." />
									<div class="has-error"></div>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label"><input type="checkbox" name="active" value="1" checked /> Active</label>
							</div>
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
	<script>


		// polyfill for RegExp.escape
		if(!RegExp.escape) {
		RegExp.escape = function(s) {
			return String(s).replace(/[\\^$*+?.()|[\]{}]/g, '\\$&');
		};
		}
		function activecheck(){

			var text2 = document.getElementById('passcon')
			var passwd_1 = document.querySelector('#pass')
			var passwd_2 = document.querySelector('#passcon')
			var pass = passwd_1.value
			var passcd = passwd_2.value
			console.log(passcd)
			for (i=0;i<passcd.length;i++){
				if(passcd[i]!= pass[i]){
					text2.style.boxShadow = '2px 3px 3px 2px red'
					break
				}else if(pass == passcd){
					text2.style.boxShadow = '2px 3px 3px 2px green'
				}else{
					text2.style.boxShadow = '2px 3px 3px 2px skyblue'
				}
			}

		}

		$(function(){
			$('.pass').val('');
			$('[name="has_credentials"]').on('change', function(){
				console.log(123);
				if($(this).is(':checked')){
					$("#passwords-holder").removeClass("hidden");
					$(".pass").attr('required', true);
				}
				else{
					$('.pass').val('');
					$("#passwords-holder").addClass("hidden");
					$(".pass").removeAttr('required');
				}
			});

			$('[name="confirm_password"]').on('keyup', function(){
				var pass1 = $('[name="main_password"]').val();
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
