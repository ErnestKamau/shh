<?php $__env->startSection('title2'); ?>
<title><?php echo e($user->name); ?> - Users | Personnel Management</title>
<style type="text/css">
	.substringed {
		cursor: pointer;
	}

	.substringed .hoverable {
		display: none;
	}

	.substringed:hover .hoverable {
		display: unset !important;
	}

	.substringed:hover .default-seen {
		display: none !important;
	}

	.substringed .default-seen {
		display: unset !important;
	}
</style>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content2'); ?>

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
			'name' => 'Personnel Profile',
			'icon' => null
		),
		array(
			'link' => '#',
			'name' => $user->name,
			'icon' => null
		)
	);
	?>
	 <?php if (isset($component)) { $__componentOriginal30091868428b09767320233ef70f89faadea10d9 = $component; } ?>
<?php $component = $__env->getContainer()->make(App\View\Components\BreadCrumb::class, ['items' => $items]); ?>
<?php $component->withName('bread-crumb'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php $component->withAttributes([]); ?> <?php if (isset($__componentOriginal30091868428b09767320233ef70f89faadea10d9)): ?>
<?php $component = $__componentOriginal30091868428b09767320233ef70f89faadea10d9; ?>
<?php unset($__componentOriginal30091868428b09767320233ef70f89faadea10d9); ?>
<?php endif; ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?> 
	<h2 class="p-4">
		<?php if(isset($user->photo) && $user->photo != ""): ?>
		<img src="<?php echo e($user->photo); ?>" style="width: 100px" />
		<?php else: ?>
		<i class="mdi mdi-account"></i>
		<?php endif; ?>
		<?php echo e($user->name); ?> | <small class="text-muted">Profile</small>
	</h2>
	<br>
	<div class="row no-gutters">
		<div class="col-sm-12 p-2">
			<div class="card tab-card">
				<div class="card-header tab-card-header">
					<ul class="nav nav-tabs card-header-tabs" id="Categories-tabs" role="tablist">
						<li class="nav-item">
							<a class="nav-link active" id="Roles-tab" data-toggle="tab" href="#Roles" role="tab" aria-controls="Roles" aria-selected="true">Roles</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" id="User-Details-tab" data-toggle="tab" href="#User-Details" role="tab" aria-controls="User-Details" aria-selected="true">User Details</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" id="User-Activity-tab" data-toggle="tab" href="#User-Activity" role="tab" aria-controls="User-Activity" aria-selected="true">User Activity</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" id="User-Work-History-tab" data-toggle="tab" href="#User-Work-History" role="tab" aria-controls="User-Work-History" aria-selected="true">Work History</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" id="User-certification-tab" data-toggle="tab" href="#User-Certifications" role="tab" aria-controls="User-Certifications" aria-selected="true">User Certifications</a>
						</li>
					</ul>
				</div>
				<div class="tab-content" id="Orders-tabs-content">
					<!-- ----------------certification------------ -->
					<div class="tab-pane show fade p-3" id="User-Certifications" role="tabpanel" aria-labelledby="one-tab">
						<h5 class="card-title">
							<i class="mdi mdi-file-certificate"></i> Certifications
							<button class="btn btn-outline-primary btn-sm float-right" data-toggle="modal" data-target="#add-certificate"><i class="mdi mdi-plus"></i> Add</button>
						</h5>
						<div class="table-responsive bg-light p-4">
							<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
								<thead class="bg-light p-2">
									<tr>
										<th>No</th>
										<th nowrap>Certificate Name</th>
										<th>Certificate</th>
										<th nowrap>Certificate Body</th>
										<th>Certificate Date</th>
										<th>Expire Date</th>
										<th nowrap>Edited By</th>
										<th nowrap>Status</th>
										<th nowrap>Description</th>
										<th></th>
										<th></th>
									</tr>
								</thead>
								<tbody>
									<?php $__currentLoopData = $certifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
									<?php
									$item = getPersonnelcertification($user->id, $cert->id);
									?>

									<?php if(isset($item[0]->id)): ?>
									<?php
									if ($item[0]->id > 0) {
										$role_certification = getRoleCertificationByID($item[0]->role_certification_id);
										$certificate = getSampleTypeQualificationById($role_certification->certification_id);
									} else {
										$certificate = getSampleTypeQualificationById($cert->certification_id);
									}
									?>
									<tr>
										<td><?php echo e($loop->iteration); ?></td>
										<td><?php echo e($certificate->name); ?></td>
										<td style="text-align: center;"><?php echo $item[0]->certificate =='' ? 'N/a': '<a href="<?php echo e($item[0]->certificate); ?>" class="btn btn-sm btn-transparent" target="_blank"><i class="mdi mdi-download text-success"></i></a>'; ?></td>
										<td><?php echo $item[0]->certificate_body == '' ? 'N/a':$item[0]->certificate_body; ?></td>
										<td><?php echo $item[0]->certificate_date == '' ? 'N/a':$item[0]->certificate_date; ?></td>
										<td><?php echo $item[0]->expire_date == '' ? 'N\a':$item[0]->expire_date; ?></td>
										<td><?php echo $item[0]->edited_by == '' ? 'N/a':$item[0]->edited_by; ?></td>
										<td class="text-small text-center"><?php echo $item[0]->status == 0 ? '<i class="mdi mdi-marker-check text-success"></i>' : '<span class="mdi mdi-close-circle text-danger"> Missing?</span> '; ?></td>
										<td class="text-center">
											<span class="btn btn-outline-dark btn-sm" data-target="#certificate-description-<?php echo e($item[0]->id); ?>" data-toggle="modal"><i class="mdi mdi-message-text"></i></span>
											<div id="certificate-description-<?php echo e($item[0]->id); ?>" class="modal fade" role="dialog">
												<div class="modal-dialog">
													<div class="modal-content">
														<div class="modal-header">
															<h4 class="modal-title"><i class="mdi mdi-eye"></i> <?php echo e($certificate->name); ?> Personnel Description</h4>
														</div>
														<div class="modal-body">
															<h5>Description</h5>
															<div class="panel panel-default">
																<div class="panel-body">
																	<?php echo e($certificate->description); ?>

																</div>
															</div>
														</div>
														<div class="modal-footer">
															<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
														</div>
													</div>
												</div>
											</div>
										</td>
										<?php if($item[0]->id > 0): ?>

										<td>
											<span class="btn btn-outline-info btn-sm" data-toggle="modal" data-target="#edit-certificate-<?php echo e($item[0]->id); ?>"><i class="mdi mdi-pencil"></i></span>
											<div id="edit-certificate-<?php echo e($item[0]->id); ?>" class="modal fade" role="dialog">
												<div class="modal-dialog">
													<form action="<?php echo e(route('edit-personnel-certification',['id'=>$item[0]->id])); ?>" method="post" class="modal-content" enctype="multipart/form-data">
														<?php echo csrf_field(); ?>
														<div class="modal-header">
															<h4 class="modal-title"><i class="mdi mdi-pencil"></i> Edit <?php echo e($certificate->name); ?> Personnel Certification</h4>
														</div>
														<div class="modal-body">
															<div class="form-group">
																<label class="control-label">Certificate Name</label>
																<select name="certificate_name" id="certificate-list" class="form-control" aria-placeholder="Certifications">
																	<?php $__currentLoopData = $certifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $certy): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
																	<?php if($cert->status == 0): ?>
																	<?php
																	$certs = getSampleTypeQualificationById($certy->certification_id)
																	?>
																	<option value="<?php echo e($cert->id); ?>" <?php echo e($item[0]->role_certification_id == $certy->id ? 'selected':''); ?>><?php echo e($certs->name); ?></option>
																	<?php endif; ?>
																	<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
																</select>
															</div>
															<div class="form-group">
																<label class="control-label">Certificate Body</label>
																<input type="text" name="certificate_body" value="<?php echo e($item[0]->certificate_body); ?>" class="form-control" placeholder="Certificate Body...">
															</div>
															<div class="form-group">
																<label class="control-label">Certificate <small style="color:red">*Leave it blank to retain the initial certificate document.</small></label>
																<input type="file" name="certificate" placeholder="Choose Certificate..." class="form-control">
															</div>
															<div class="form-group hidden">
																<label class="label-control">Cert ID</label>
																<input type="number" name="certification_id" value="<?php echo e($item[0]->id); ?>" class="form-control">
															</div>
															<div class="form-group">
																<label class="control-label">Certificate Date</label>
																<?php
																$date = date("Y-m-d", strtotime($item[0]->certificate_date));
																$expire = date("Y-m-d", strtotime($item[0]->expire_date));
																?>

																<input type="date" name="certificate_date" value="<?php echo e($date); ?>" class="form-control">
															</div>
															<div class="form-group">
																<label class="control-label">Expire Date</label>
																<input type="date" name="expire_date" value="<?php echo e($expire); ?>" class="form-control">
															</div>
														</div>
														<div class="modal-footer">
															<button type="submit" class="btn btn-primary"> <i class="md mdi-content-save"></i> Update</button>
															<button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
														</div>
													</form>
												</div>
											</div>
										</td>
										<td>
											<span class="btn btn-outline-danger btn-sm" data-target="#delete-personnel-certification-<?php echo e($item[0]->id); ?>" data-toggle="modal"><i class="mdi mdi-delete-empty"></i></span>
											<div id="delete-personnel-certification-<?php echo e($item[0]->id); ?>" class="modal fade" role="dialog">
												<div class="modal-dialog">
													<form action="<?php echo e(route('delete-personnel-certification',['id'=>$item[0]->id])); ?>" method="post" class="modal-content">
														<?php echo csrf_field(); ?>
														<div class="modal-header">
															<h4 class="modal-title"><i style="color: red;" class="mdi mdi-delete-empty"></i> Delete <?php echo e($certificate->name); ?> Personnel Certification</h4>
														</div>
														<div class="modal-body">
															<div class="panel panel-default">
																<div class="panel-body">
																	Are you sure you want to delete <b><?php echo e($certificate->name); ?></b> personnel certification ?
																</div>
															</div>
															<div class="form-group hidden">
																<label class="control-label">Certificate ID</label>
																<input type="number" name="cert_id" value="<?php echo e($item[0]->id); ?>" class="form-control">
															</div>
														</div>
														<div class="modal-footer">
															<button type="submit" class="btn btn-primary"> <i class="mdi mdi-content-save"></i> Yes</button>
															<button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
														</div>
													</form>
												</div>
											</div>
										</td>
										<?php endif; ?>
									</tr>
									<?php endif; ?>

									<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
								</tbody>
							</table>
						</div>
					</div>
					<!-- ----------------end certification------------ -->
					<div class="tab-pane show active fade p-3" id="Roles" role="tabpanel" aria-labelledby="one-tab">
						<h5 class="card-title"><i class="mdi mdi-key"></i> Roles
							<button class="btn btn-outline-primary btn-sm float-right" data-toggle="modal" data-target="#add-role-modal"><i class="mdi mdi-key-plus"></i></button>
						</h5>
						<div class="table-responsive">
							<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
								<thead>
									<tr>
										<th>#</th>
										<th nowrap>Name</th>
										<th nowrap>Description</th>
										<th></th>
									</tr>
								</thead>
								<tbody>
									<?php $__currentLoopData = $user->roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
									<tr>
										<td><?php echo e($loop->iteration); ?></td>
										<td><?php echo e($item->role->name); ?></td>
										<td><?php echo e($item->role->description); ?></td>
										<td>
											<form method="POST" class="btn btn-default text-danger submit-delete-form-btn" action="<?php echo e(route('remove-personnel-role', ['id'=>$item->id])); ?>">
												<?php echo csrf_field(); ?>
												<i class="mdi mdi-delete"></i>
											</form>
										</td>
									</tr>
									<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
								</tbody>
							</table>
						</div>
					</div>
					<form autocomplete="off" action="<?php echo e(route('add-personnel', ['id'=>$user->id])); ?>" method="POST" class="tab-pane fade p-3" id="User-Details" role="tabpanel" aria-labelledby="one-tab" enctype="multipart/form-data">
						<h5 class="card-title"><i class="mdi mdi-key"></i> User Details
							<button class="btn btn-outline-primary btn-sm float-right"><i class="mdi mdi-content-save"></i> Save</button>
						</h5>
						<input autocomplete="off" name="hidden" type="password" style="display:none;">
						<?php echo csrf_field(); ?>
						<div class="table-responsive">
							<div class="row">
								<div class="col-sm-3">
									<div class="form-group">
										<label class="control-label">Designation *</label>
										<select name="designation" class="form-control" placeholder="Designation..." required>
											<option></option>
											<?php $__currentLoopData = getModulePreconfig("Designation", "Personnel-Management"); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
											<option value="<?php echo e($item->id); ?>" <?php echo e($user->designation == $item->id ? 'selected' : ''); ?>><?php echo e($item->name); ?></option>
											<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
										</select>
									</div>
								</div>
								<div class="col-sm-3">
									<div class="form-group">
										<label class="control-label">First Name *</label>
										<input type="text" class="form-control" name="first_name" value="<?php echo e($user->first_name); ?>" placeholder="First Name..." required />
									</div>
								</div>
								<div class="col-sm-3">
									<div class="form-group">
										<label class="control-label">Middle Name</label>
										<input type="text" class="form-control" name="middle_name" value="<?php echo e($user->middle_name); ?>" placeholder="Middle Name..." />
									</div>
								</div>
								<div class="col-sm-3">
									<div class="form-group">
										<label class="control-label">Last Name</label>
										<input type="text" class="form-control" name="last_name" value="<?php echo e($user->last_name); ?>" placeholder="Last Name..." />
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-sm-4">
									<div class="form-group row no-gutters">
										<div class="col-4 text-center">
											<img src="<?php echo e($user->photo ?? '/images/user.png'); ?>" style="max-width: 100%; max-height:75px" />
										</div>
										<div class="col-8">
											<label class="control-label">Photo</label>
											<input type="file" class="form-control" name="image" />
										</div>
									</div>
								</div>
								<div class="col-sm-4">
									<div class="form-group">
										<label class="control-label">Email *</label>
										<input type="email" class="form-control" name="email" value="<?php echo e($user->email); ?>" placeholder="Email..." required />
									</div>
								</div>
								<div class="col-sm-4">
									<div class="form-group">
										<label class="control-label">Phone</label>
										<input type="text" class="form-control" name="phone" value="<?php echo e($user->phone); ?>" placeholder="Phone..." />
									</div>
								</div>
								<div class="col-sm-4">
									<div class="form-group">
										<label class="control-label">ID Number/Passport No *</label>
										<input type="text" class="form-control" name="id_number" value="<?php echo e($user->id_number); ?>" placeholder="ID Number..." required />
									</div>
								</div>
								<div class="col-sm-4">
									<div class="form-group">
										<label class="control-label">Date of Birth</label>
										<input type="date" class="form-control" name="date_of_birth" value="<?php echo e($user->date_of_birth); ?>" placeholder="Date of Birth..." />
									</div>
								</div>
								<div class="col-sm-4">
									<div class="form-group">
										<label class="control-label">Employment Date</label>
										<input type="date" class="form-control" name="employment_date" value="<?php echo e($user->employment_date); ?>" placeholder="Employment Dat..." />
									</div>
								</div>
								<div class="col-sm-4">
									<div class="form-group">
										<label class="control-label">Education Level</label>
										<select name="educational_level" class="form-control" placeholder="Education Level...">
											<option></option>
											<?php $__currentLoopData = getModulePreconfig("Educational Levels", "Personnel-Management"); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
											<option value="<?php echo e($item->id); ?>" <?php echo e($user->education_level == $item->id ? 'selected' : ''); ?>><?php echo e($item->name); ?></option>
											<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
										</select>
									</div>
								</div>
								<div class="col-sm-4">
									<div class="form-group">
										<label class="control-label">Position *</label>
										<select name="position" class="form-control" placeholder="Position..." required>
											<option></option>
											<?php $__currentLoopData = getModulePreconfig("Job Description", "Personnel-Management"); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
											<option value="<?php echo e($item->id); ?>" <?php echo e($user->position == $item->id ? 'selected' : ''); ?>><?php echo e($item->name); ?></option>
											<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
										</select>
									</div>
								</div>
								<div class="col-sm-4">
									<div class="form-group">
										<label for="" class="control-label">Lab Section</label>
										<select name="lab_section_id[]" multiple id="" class="form-control">
											<?php $__currentLoopData = $stages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
											<option value="<?php echo e($stage->id); ?>" <?php echo e(in_array($stage->id,explode(',',$user->lab_section_id)) ? 'selected' : ''); ?>><?php echo e($stage->name); ?></option>
											<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
										</select>
									</div>
								</div>
								<div class="col-sm-4">
									<div class="form-group">
										<label class="control-label">Department *</label>
										<select name="department" class="form-control" placeholder="Department..." required>
											<option></option>
											<?php $__currentLoopData = getDepartments(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
											<option value="<?php echo e($item->id); ?>" <?php echo e($user->department_id == $item->id ? 'selected' : ''); ?>><?php echo e($item->name); ?></option>
											<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
										</select>
									</div>
								</div>
								<div class="col-sm-4">
									<div class="form-group">
										<label class="control-label">KRA PIN</label>
										<input type="text" class="form-control" name="kra_pin" value="<?php echo e($user->kra_pin); ?>" placeholder="KRA PIN..." />
									</div>
								</div>
								<div class="col-sm-4">
									<div class="form-group">
										<label class="control-label">NSSF</label>
										<input type="text" class="form-control" name="nssf" value="<?php echo e($user->nssf); ?>" placeholder="NSSF..." />
									</div>
								</div>
								<div class="col-sm-4">
									<div class="form-group">
										<label class="control-label">NHIF</label>
										<input type="text" class="form-control" name="nhif" value="<?php echo e($user->nhif); ?>" placeholder="NHIF..." />
									</div>
								</div>
								<div class="col-sm-4">
									<div class="form-group">
										<label class="control-label">User License</label>
										<select name="user_license" class="form-control" placeholder="User License..." required>
											<option></option>
											<?php $__currentLoopData = getUserLicenses(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i=>$n): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
											<option value="<?php echo e($i); ?>" <?php echo e($i == $user->license_type ? 'selected' :''); ?> <?php echo e(intval($license_count[$i]) == intval(mamboSawa($i.'s')) ? 'disabled' : ''); ?>><?php echo e($n); ?> <?php echo e($license_count[$i]."/".mamboSawa($i.'s')); ?></option>
											<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
										</select>
									</div>
								</div>
								<div class="col-sm-4">
									<div class="form-group">
										<label class="control-label">Signature <span>*</span></label>
										<input type="file" name="signature" id="" class="form-control">
									</div>
								</div>
								<?php if($user->electronic_sig): ?>
								<div class="col-sm-4">
									<img src="<?php echo e($user->electronic_sig); ?>" style="max-width: 100%; max-height:70px">
								</div>
								<?php endif; ?>
							</div>
							<div class="row">
								<div class="col-sm-4">
									<div class="form-group mt-sm-5 ">
										<label class="control-label <?php echo e(auth()->user()->is_support_staff == 0 ? 'hidden' : ''); ?>"><input type="checkbox" name="active" value="1" <?php echo e($user->active == "1" ? "checked" : ""); ?> /> Active</label>
									</div>
								</div>
								<div class="col-sm-8 mt-sm-5">
									<div class="form-group hidden">
										<label class="control-label">
											<input type="checkbox" name="has_credentials" value="1" /> Create/Update User Passwords
										</label>
									</div>
								</div>

							</div>
							<div class="row hidden" id="passwords-holder">
								<div class="col-sm-4">
									<div class="form-group">
										<label>Password</label>
										<input autocomplete="off" type="password" value="" title="Must contain at least one number and one uppercase and lowercase letter, and at least 8 or more characters" class="form-control pass" name="main_password" placeholder="Password..." />
										<div class="has-success"></div>
									</div>
								</div>
								<div class="col-sm-4">
									<div class="form-group">
										<label>Confirm Password</label>
										<input type="password" value="" title="Must contain at least one number and one uppercase and lowercase letter, and at least 8 or more characters" class="form-control pass" name="confirm_password" placeholder="Confirm Password..." />
										<div class="has-error"></div>
									</div>
								</div>
							</div>
						</div>
					</form>
					<div class="tab-pane fade p-3" id="User-Activity" role="tabpanel" aria-labelledby="one-tab">
						<h5 class="card-title">User Activity </h5>
						<div class="table-responsive">
							<table id="audit-log-table" data-url="<?php echo e(route('server-side-audit_logs', ['user_id'=>$user->id])); ?>" class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm server-side">
								<thead>
									<tr>
										<th>#</th>
										<th nowrap>User</th>
										<th nowrap>Email</th>
										<th nowrap>Event</th>
										<th nowrap>Entity</th>
										<th nowrap>Entity ID</th>
										<th nowrap>IP Address</th>
										<th nowrap>URL</th>
										<th nowrap>Date</th>
										<th nowrap>Changes</th>
									</tr>
								</thead>
								<tbody></tbody>
							</table>
						</div>
					</div>
					<div class="tab-pane fade p-3" id="User-Work-History" role="tabpanel" aria-labelledby="one-tab">
						<h5 class="card-title">User Work History </h5>
						<div class="table-responsive">
							<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
								<thead>
									<tr>
										<th>#</th>
										<th nowrap>Department</th>
										<th nowrap>Job Description</th>
										<th nowrap>Start Date</th>
										<th nowrap>End Date</th>
									</tr>
								</thead>
								<tbody>
									<?php $__currentLoopData = $user->work_history(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
									<tr>
										<td><?php echo e($loop->iteration); ?></td>
										<td><?php echo e($item->department_name); ?></td>
										<td><?php echo e($item->position); ?></td>
										<td><?php echo e($item->created_at); ?></td>
										<td><?php echo trim($item->end_date) != "" ? $item->end_date : '<i class="mdi mdi-check-circle text-success"></i> Current'; ?></td>
									</tr>
									<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</main>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script2'); ?>
<div id="show-changes-modal" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<div class="modal-content">
			<?php echo csrf_field(); ?>
			<div class="modal-header">
				<h4 class="modal-title"><i class="mdi mdi-alert-decagram"></i> Audit Changes</h4>
			</div>
			<div class="modal-body">
				<table class="table-condensed table table-sm table-banded table-hover table-xs table-bordered" id="audit-changes-table">
					<thead>
						<tr>
							<th>Field</th>
							<th nowrap>New</th>
							<th nowrap>Old</th>
						</tr>
					</thead>
					<tbody></tbody>
				</table>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>
<div id="add-certificate" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<form action="<?php echo e(route('add-personnel-certification',['id'=>$user->id])); ?>" method="post" class="modal-content" enctype="multipart/form-data">
			<?php echo csrf_field(); ?>
			<div class="modal-header">
				<h4><i class="mdi mdi-plus"></i> Add Personnel Certification.</h4>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<label class="control-label">Certificate Name</label>
					<select name="certificate_name" id="certificate-list" class="form-control" aria-placeholder="Certifications">
						<?php $__currentLoopData = $certifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
						<?php if($cert->status == 0): ?>
						<?php
						$certs = getSampleTypeQualificationById($cert->certification_id)
						?>
						<option value="<?php echo e($cert->id); ?>"><?php echo e($certs->name); ?></option>
						<?php endif; ?>
						<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
					</select>
				</div>
				<div class="form-group hidden">
					<label class="control-label">Personnel ID</label>
					<input type="number" name="personnel_id" value="<?php echo e($user->id); ?>" class="form-control" placeholder="Personnel ID...">
				</div>

				<div class="form-group">
					<label class="control-label">Certificate Body</label>
					<input type="text" name="certificate_body" value="" class="form-control" placeholder="Certification Body..." required />
				</div>
				<div class="form-group">
					<label class="control-label">Certificate</label>
					<input type="file" name="certificate" class="form-control" placeholder="Upload Certificate..." required>
				</div>
				<div class="form-group">
					<label class="control-label">Certificate Date</label>
					<input type="date" name="certificate_date" value="" class="form-control" placeholder="Certificate Date..." required />
				</div>

				<div class="form-group">
					<label class="control-label">Expire Date</label>
					<input type="date" name="expire_date" value="" class="form-control" placeholder="Expire Date..." required />
				</div>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-primary"> <i class="mdi mdi-content-save"></i> Save</button>
				<button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
			</div>
		</form>
	</div>
</div>
<div id="add-role-modal" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<form class="modal-content" method="POST" action="<?php echo e(route('add-personnel-role', ['user_id'=>$user->id])); ?>" enctype="multipart/form-data">
			<?php echo csrf_field(); ?>
			<div class="modal-header">
				<h4 class="modal-title"><i class="mdi mdi-key-plus"></i> Add User Role</h4>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<label class="control-label">Select Roles</label>
					<select name="roles[]" class="form-control" placeholder="Select Approval User..." multiple required>
						<option></option>
						<?php $__currentLoopData = getRoles(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
						<option value="<?php echo e($item->id); ?>"><?php echo e($item->name); ?></option>
						<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
<script>
	$(function() {
		$('#show-changes-modal').on('show.bs.modal', function(e) {
			var auditID = $(e.relatedTarget).data('audit');
			$.ajax({
				url: '/server-side-audit_log/' + auditID + '/details',
				dataType: "json",
				beforeSend: function() {
					$('#audit-changes-table').find('tbody').html(`
						<tr>
							<td colspan="3">
								<div class="text-center p-3">
									<img src="/images/loading.gif" style="width: 100%" />
								</div>
							</td>
						</tr>
					`);
				},
				success: function(js) {
					$('#audit-changes-table').find('tbody').empty();
					var columns = js.columns;
					var oldData = js.old;
					var newData = js.new;

					if (columns.length == 0) {
						$('#audit-changes-table').find('tbody').append(`
							<tr style="color:#232323">
								<td colspan="3" class="text-center">
									<i class="mdi mdi-information"></i> No Data Available
								</td>
							</tr>
						`);
					}

					$.each(columns, function(i, c) {
						$('#audit-changes-table').find('tbody').append(`
							<tr style="color:#232323">
								<th nowrap>${c}</th>
								<td nowrap>${newData[c] ? newData[c] : '-'}</td>
								<td nowrap>${oldData[c] ? newData[c] : '-'}</td>
							</tr>
						`);
					});
				}
			})
		});

		var $url = $('#audit-log-table').data('url');
		$('#audit-log-table').DataTable({
			lengthMenu: [
				[25, 50, 100, 500, 1000, -1],
				[25, 50, 100, 500, 1000, "All"]
			],
			dom: 'Blfrtip',
			buttons: [
				'copy', 'csv', 'excel', 'pdf', 'print'
			],
			columns: [{
					data: "loop",
					"searchable": false
				},
				{
					data: "name"
				},
				{
					data: "email"
				},
				{
					data: "event"
				},
				{
					data: "entity"
				},
				{
					data: "entity_id"
				},
				{
					data: "ip_address"
				},
				{
					data: "url"
				},
				{
					data: null,
					render: function(data, type, row) {
						var dt = new Date(data.created_at);

						return dt.today() + " " + dt.timeNow();
					}
				},
				{
					data: null,
					className: "center",
					render: function(data, type, row) {
						$(row).find('td:eq(4)').attr('nowrap');
						$(row).find('td:eq(4)').prop('nowrap');
						return `<span class="btn btn-sm text-info btn-transparent" data-toggle="modal" data-audit="${data.id}" data-target="#show-changes-modal">
							<i class="mdi mdi-alert-decagram"></i> Changes
						</span>
						`;
					}
				}
			],
			destroy: true,
			processing: true,
			serverSide: true,
			ajax: $url
		});
		$('.pass').val('');
		$('[name="has_credentials"]').on('change', function() {
			if ($(this).is(':checked')) {
				$("#passwords-holder").removeClass("hidden");
				$(".pass").attr('required', true);
			} else {
				$('.pass').val('');
				$("#passwords-holder").addClass("hidden");
				$(".pass").removeAttr('required');
			}
		});

		$('[name="confirm_password"]').on('keyup', function() {
			var pass1 = $('[name="main_password"]').val();
			var pass2 = $(this).val();

			if (pass1 != pass2) {
				$(this).siblings('.has-success').html('').addClass('text-success');
				$(this).siblings('.has-error').html(`<i class="mdi mdi-cancel"></i> Passwords did not match.`).addClass('text-danger');
			} else {
				$(this).siblings('.has-error').html('')
				$(this).siblings('.has-success').html('<i class="mdi mdi-check-circle"></i> Passwords Match!.')
			}
		});
	});
</script>
<script>
	$(function() {
		$('.submit-delete-form-btn').on('click', function() {
			var form = $(this);
			if (confirm("Are you sure that you want to remove this role?")) {
				form.submit();
			}
		});
	})
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.personnel.layout.app', ['dataTable'=>true, 'select2'=>true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/kimari/Projects/polucon/resources/views/layouts/personnel/users/show.blade.php ENDPATH**/ ?>