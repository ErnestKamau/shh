<?php $__env->startSection('title2'); ?>
<title> <?php echo e($equipment->name); ?> | Equipments</title>

<style type="text/css">
	.no-header th {
		color: #454545;
	}

	.hidden {
		display: none;
	}

	.btn-default {
		background-color: white !important;
		margin: 3px;
		padding: 3px !important;
		font-size: 13px !important;
	}
</style>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content2'); ?>
<main>
	<?php
	$items = array(
		array(
			'link' => route('labEquipmentIndex'),
			'name' => 'Equipment Management',
			'icon' => null
		),
		array(
			'link' => '#',
			'name' => $equipment->name,
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
		<i class="mdi mdi-tools"></i> <?php echo e($equipment->name); ?> <small class="text-muted"> | Equipment</small>
		<button class="btn btn-default float-right" data-toggle="modal" data-target="#edit-equipment"><i class="mdi mdi-pencil-box-outline text-primary"></i> Edit</button>
		<button class="btn btn-default float-right" data-toggle="modal" data-target="#create-attachment"><i class="mdi mdi-plus text-dark"></i>Attachment</button>
		<button class="btn btn-default float-right" data-toggle="modal" data-target="#add-operator-modal"><i class="mdi mdi-plus text-warning"></i> Operator</button>
		<button class="btn btn-default float-right" data-toggle="modal" data-type="Verification" data-target="#create-verification-log"><i class="mdi mdi-plus text-info"></i> Verification Log</button>
		<button class="btn btn-default float-right" data-toggle="modal" data-type="Repairment" data-target="#create-new-repairment"><i class="mdi mdi-plus text-default"></i> Repair Log</button>
		<button class="btn btn-default float-right" data-toggle="modal" data-type="Calibration" data-target="#create-new-calibration"><i class="mdi mdi-plus text-success"></i> Calibration / Servicing Log</button>
		<button class="btn btn-default float-right" data-toggle="modal" data-type="Maintainance" data-target="#create-new-maintainance"><i class="mdi mdi-plus text-danger"></i> Maintainance Log</button>
	</h2>
	<div class="row no-gutters" style="clear: both;">
		<div class="col-sm-3 p-2">
			<div class="card">
				<div class="card-body">
					<div class="p-3 center text-center align-content-center">
						<img src="<?php echo e($equipment->picture); ?>" style="max-width: 80%">
					</div>
					<h5 class="p-3 text-bold text-lg text-center bg-light-gray border-bottom">
						<?php echo e($equipment->equipment_number); ?>

					</h5>
					<p><?php echo e($equipment->description); ?></p>
					<?php if($equipment->calibration_date()['status'] == 'text-warning'): ?>
					<small class="p-3">
						<i class="mdi mdi-alert-decagram text-warning"></i> Schedule Equipment Calibration
					</small>
					<?php endif; ?>
					<?php if($equipment->calibration_date()['status'] == 'text-danger'): ?>
					<small class="p-3">
						<i class="mdi mdi-alert text-danger"></i> Equipment Calibration Required
					</small>
					<?php endif; ?>
					<?php if($equipment->maintainance_date()['status'] == 'text-warning'): ?>
					<small class="p-3">
						<i class="mdi mdi-alert-decagram text-warning"></i> Schedule Equipment Maintainance
					</small>
					<?php endif; ?>
					<?php if($equipment->maintainance_date()['status'] == 'text-danger'): ?>
					<small class="p-3">
						<i class="mdi mdi-alert text-danger"></i> Equipment Maintainance Required
					</small>
					<?php endif; ?>
					<table class="table table-condensed table-borderless table-banded table-sm table-striped no-header">
						<tr>
							<th><i class="mdi mdi-information"></i> Make</th>
							<td><?php echo e($equipment->make); ?></td>
						</tr>
						<tr>
							<th><i class="mdi mdi-information-outline"></i> Model</th>
							<td><?php echo e($equipment->model); ?></td>
						</tr>
						<tr>
							<th><i class="mdi mdi-calendar-month"></i> Purchased On</th>
							<td><?php echo e($equipment->date_purchased); ?></td>
						</tr>
						<tr>
							<th><i class="mdi mdi-ruler-square-compass"></i> Next Calibration</th>
							<td>
								<?php
								$diff = getNextCalibrationDate($equipment->id);
								?>
								<?php if($diff['diff'] >= 0): ?>
								<small><?php echo e($diff['date']); ?></small> <br>
								<?php if($diff['diff'] < $equipment->calibration_notification_in_days): ?>
									<small class="badge badge-warning ">+ <?php echo e($diff['diff']); ?></small> <br>
									<?php else: ?>
									<small class="badge badge-success ">+ <?php echo e($diff['diff']); ?></small>
									<?php endif; ?>

									<?php else: ?>
									<small><?php echo e($diff['date']); ?></small> <br>
									<small class="badge badge-danger">+ <?php echo e($diff['diff']); ?></small>
									<?php endif; ?>

							</td>
						</tr>
						<tr>
							<th><i class="mdi mdi-pipe-wrench"></i>Key: <span class="badge p-2 badge-warning">+ days</span></th>
							<td>
								Schedule for Equipment Calibration
							</td>
						</tr>
						<tr>
							<th><i class="mdi mdi-pipe-wrench"></i>Key: <span class="badge p-2 badge-danger">- days</span></th>
							<td>
								Equipment Late for Calibration
							</td>
						</tr>
					</table>
				</div>
			</div>
		</div>
		<div class="col-sm-9 p-2">
			<div class="card tab-card">
				<div class="card-header tab-card-header">
					<ul class="nav nav-tabs card-header-tabs" id="equipment-tabs" role="tablist">
						<li class="nav-item">
							<a class="nav-link active" id="maintainance-log-tab" data-toggle="tab" href="#maintainance-log" role="tab" aria-controls="maintainance-log" aria-selected="true">Maintainance Log</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" id="calibration-log-tab" data-toggle="tab" href="#calibration-log" role="tab" aria-controls="calibration-log" aria-selected="true">Calibration / Servicing Log</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" id="usage-log-tab" data-toggle="tab" href="#repairement-log" role="tab" aria-controls="Usage" aria-selected="false">Repair Log</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" id="usage-log-tab" data-toggle="tab" href="#verification-log" role="tab" aria-controls="Usage" aria-selected="false">Verification Log</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" id="equipment-operators-tab" data-toggle="tab" href="#equipment-operators" role="tab" aria-controls="Usage" aria-selected="false">Operators</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" id="equipment-attachment-tab" data-toggle="tab" href="#equipment-attachment" role="tab" aria-controls="Usage" aria-selected="false">Attachments</a>
						</li>
					</ul>
				</div>

				<div class="tab-content" id="equipment-tabs-content">
					<!-- ----------------------attach----------------------- -->
					<div class="tab-pane fade show p-3" id="equipment-attachment" role="tabpanel" aria-labelledby="one-tab">
						<h5 class="card-title">Attachments </h5>
						<div class="table-responsive">
							<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
								<thead class="bg-light p-2">
									<tr>
										<th>No</th>
										<th>Title</th>
										<th>Attachment</th>
										<th>Uploaded By</th>
										<th nowrap>Edited By</th>
										<th nowrap>Description</th>
										<th></th>
									</tr>
								</thead>
								<tbody>
									<?php $__currentLoopData = $attachments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
									<tr>

										<td><?php echo e($loop->iteration); ?></td>
										<td><?php echo e($a->title); ?></td>
										<td class="text-center"><a target="_blank" href="<?php echo e($a->attachment); ?>" class="btn btn-sm"><i class="mdi mdi-download"></i> <br> <small style="font-weight: 600 !important;">Download</small> </a></td>
										<td><?php echo e(getUserById($a->upload_by)->name ?? 'n/a'); ?></td>
										<td><?php echo e(getUserById($a->edit_by)->name ?? 'n/a'); ?></td>
										<td class="text-center"><span class="btn btn-dark btn-sm" data-description="<?php echo e($a->description); ?>" data-target="#attachment-description" data-toggle="modal" data-toggle="tooltip" title="View Description"><i class="mdi mdi-clipboard-text"></i></span></td>
										<td><span class="btn btn-outline-primary btn-sm"><i class="mdi mdi-pencil" data-attachment="<?php echo e(json_encode($a)); ?>" data-toggle="modal" data-target="#edit-attachment" data-toggle="tooltip" title="Edit"></i></span></td>
									</tr>
									<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
								</tbody>
							</table>
						</div>
					</div>
					<!-- ------------------maintainance------------------- -->
					<div class="tab-pane fade show active p-3" id="maintainance-log" role="tabpanel" aria-labelledby="one-tab">
						<h5 class="card-title">Maintainance Logs </h5>
						<div class="table-responsive">
							<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
								<thead class="bg-light p-2">
									<tr>
										<th></th>
										<th>Type</th>
										<th nowrap>Service Provider</th>
										<th nowrap>Date</th>
										<th>Certificate</th>
										<th nowrap>Overseen By</th>
										
										<th>Contact Person</th>
										<th nowrap>Notes</th>

									</tr>
								</thead>
								<tbody>
									<?php $lp = 0 ?>
									<?php $__currentLoopData = $equipment->maintainance_Calibration_logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
									<?php if($item->type=="Maintainance"): ?>
									<?php $lp += 1; ?>
									<tr>
										<td style="min-width: 70px;">
											<span class="btn btn-outline-info btn-sm" data-toggle="modal" id="edit-maintenance" data-target="#edit-maintainance" data-cal="<?php echo e(json_encode($item)); ?>" data-suppliers="<?php echo e(json_encode($suppliers)); ?>" data-employees="<?php echo e(json_encode($employees)); ?>" data-toggle="tooltip" title="Edit"> <i class="mdi mdi-pencil"></i></span>
											<span class="btn btn-sm btn-outline-danger" data-toggle="modal" data-target="#delete-maintainance" data-toggle="tooltip" data-item="<?php echo e(json_encode($item->id)); ?>" data-name="Maintainance Log" title="Delete"><i class="mdi mdi-delete-empty"></i></span>

										</td>
										<td><?php echo e($item->maintainance_type == 'in-house' ? 'In house':'External'); ?></td>
										<td>

											<?php echo e($item->maintainance_type != 'in-house' ? getSupplierByID($item->supplier_id)->name : getUserById($item->employee_id)->name); ?>


										</td>
										<td><?php echo e($item->date); ?></td>

										<td><a href="<?php echo e($item->certificate == ''  ? '#' : $item->certificate); ?>" class="btn btn-sm btn-transparent" target="_blank"><i class="mdi mdi-download text-success"></i> Download</a> </td>
										<td><?php echo e($item->overseer()->name ?? ''); ?></td>
										
										<td><?php echo e(getSupplierContactByID($item->supplier_contact_id)->name ?? '-'); ?></td>
										<td>
											<span class="btn btn-info btn-sm" data-toggle="modal" data-target="#content"><i class="mdi mdi-eye"></i></span>
											<div id="content" class="modal fade" role="dialog">
												<div class="modal-dialog">
													<!-- Modal content-->
													<div class="modal-content">

														<div class="modal-body">
															<div class="alert alert-primary p-2">
																<p><?php echo e($item->notes); ?></p>
															</div>

														</div>
														<div class="modal-footer">
															<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
														</div>
													</div>

												</div>
											</div>
										</td>

									</tr>
									<?php endif; ?>
									<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
								</tbody>
							</table>
						</div>
					</div>
					<!-- -----verify---  -->
					<div class="tab-pane fade show  p-3" id="verification-log" role="tabpanel" aria-labelledby="one-tab">
						<h5 class="card-title">Verification Logs </h5>
						<div class="table-responsive">
							<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
								<thead class="bg-light p-2">
									<tr>
										<th></th>
										<th>Type</th>
										<th>Reference Standards</th>
										<th>Service Performer</th>
										<th>Contact Person</th>
										<th nowrap>Verification Date</th>

										<th>Description</th>



									</tr>
								</thead>
								<tbody>

									<?php $__currentLoopData = $verifys; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
									<?php if($item->is_delete == 0 and $item->equipment_id ==$equipment->id): ?>

									<tr>
										<td style="min-width:70px">
											<span class="btn btn-outline-info btn-sm" data-toggle="modal" data-target="#edit-verification-<?php echo e($lp); ?>"> <i class="mdi mdi-pencil" data-toggle="tooltip" title="Edit"></i> </span>
											<a href="<?php echo e(route('delete-verification', ['id'=>$item->id])); ?>" class="btn btn-outline-danger btn-sm" data-toggle="tooltip" title="Delete"> <i class="mdi mdi-delete"></i></a>
											<div id="edit-verification-<?php echo e($lp); ?>" class="modal fade" role="dialog">
												<div class="modal-dialog">
													<!-- Modal content-->
													<form class="modal-content" method="POST" action="<?php echo e(route('edit-verification', ['id'=>$item->id])); ?>" enctype="multipart/form-data">
														<?php echo csrf_field(); ?>
														<div class="modal-header">
															<h4 class="modal-title"><i class="mdi mdi-pencil"></i> Edit Verification Log <?php echo e($lp); ?></h4>
														</div>
														<div class="modal-body">
															<div class="form-group">
																<label class="control-label">Reference Standards</label>
																<input type="text" class="form-control" name="reference" value="<?php echo e($item->reference_standard); ?>" placeholder="Reference Standards..." required />
															</div>
															<div class="form-group hidden">
																<label class="control-label">Equipment</label>
																<input type="text" class="form-control" name="equipment" value="<?php echo e($equipment->id); ?>" placeholder="equipment..." required />
															</div>
															<div class="form-group ">
																<label class="control-label">Date of Verifiation</label>
																<input class="form-control" type="date" name="date" value="<?php echo e($item->verification_date); ?>" placeholder="Date of Verification...">
															</div>

															<div class="form-group">
																<label class="control-label">Operator</label>
																<select name="operator" class="form-control" placeholder="Operator...">
																	<?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
																	<option value="<?php echo e($employee->id); ?>"><?php echo e($employee->name); ?></option>
																	<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
																</select>
															</div>

															<div class="form-group">
																<label class="control-label">Procedure</label>
																<textarea class="form-control" value="" rows="4" name="procedure" placeholder="Procedure..." required><?php echo e($item->procedure); ?></textarea>
															</div>

															<div class="form-group">
																<label class="control-label">Responses/Readings</label>
																<textarea class="form-control" rows="4" name="response" value="" placeholder="Responses..." required><?php echo e($item->response); ?></textarea>
															</div>

															<div class="form-group">
																<label class="control-label">Remarks</label>
																<textarea class="form-control" rows="4" name="remark" value="" placeholder="Remarks..." required><?php echo e($item->remarks); ?></textarea>
															</div>


														</div>
														<div class="modal-footer">
															<button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
															<button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
														</div>
													</form>
												</div>
											</div>
										</td>
										<td><?php echo e($item->maintainance_type == 'in-house' ? 'In house':'External'); ?></td>
										<td><?php echo e($item->reference_standard); ?></td>
										<td>

											<?php echo e($item->maintainance_type != 'in-house' ? getSupplierByID($item->supplier_id)->name : getUserById($item->operator_id)->name); ?>


										</td>
										<td><?php echo e(getSupplierContactByID($item->supplier_contact_id)->name ?? '-'); ?></td>
										<td><?php echo e($item->verification_date); ?></td>

										<td>
											<span style="margin-right: 30px;" class="btn btn-success btn-sm" data-toggle="modal" data-target="#view-verification-<?php echo e($lp); ?>"> <i class="mdi mdi-eye"></i> </span>

											<div id="view-verification-<?php echo e($lp); ?>" class="modal fade" role="dialog">
												<div class="modal-dialog">
													<div class="modal-content">
														<div class="modal-header">
															<h4 class="modal-title">Verification <?php echo e($lp); ?></h4>
														</div>
														<div class="modal-body">
															<h5>Verification Procedure</h5>
															<div class="panel panel-default">
																<div class="panel-body">
																	<?php echo e($item->procedure); ?>

																</div>
															</div>
															<br>

															<h5>Responses</h5>
															<div class="panel panel-default">
																<div class="panel-body">
																	<?php echo e($item->response); ?>

																</div>
															</div>
															<br>

															<h5>Remarks</h5>
															<div class="panel panel-default">
																<div class="panel-body">
																	<?php echo e($item->remarks); ?>

																</div>
															</div>
														</div>
														<div class="modal-footer">
															<button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
														</div>
													</div>
												</div>
											</div>
										</td>

									</tr>
									<?php endif; ?>
									<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
								</tbody>
							</table>
						</div>
					</div>
					<!-- -----verify end---  -->
					<!-- -------------repair----------------------------------- -->
					<div class="tab-pane fade p-3" id="repairement-log" role="tabpanel" aria-labelledby="one-tab">
						<h5 class="card-title">Repair Logs </h5>
						<div class="table-responsive">
							<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
								<thead class="bg-light p-2">
									<tr>
										<th>No</th>
										<th>Type</th>
										<th nowrap>Service Performer</th>
										<th nowrap>Date</th>

										<th>Certificate</th>
										<th nowrap>Overseen By</th>
										
										<th>Contact Person</th>
										<th nowrap>Repaired Parts</th>
										<th>Description</th>


									</tr>
								</thead>
								<tbody>

									<?php $__currentLoopData = $equipment->maintainance_Calibration_logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
									<?php if($item->type=="Repairement"): ?>
									<?php $part_ = getRepairLogParts($item->id) ?>
									<tr>
										<td style="min-width: 70px;">
											<span class="btn btn-outline-info btn-sm" data-toggle="modal" data-employees="<?php echo e(json_encode($employees)); ?>" data-suppliers="<?php echo e(json_encode($suppliers)); ?>" data-parts="<?php echo e(json_encode($part_)); ?>" data-repair="<?php echo e(json_encode($item)); ?>" data-target="#edit-new-repairment" data-backdrop="static" data-keyboard="false" data-toggle="tooltip" title="Edit"> <i class="mdi mdi-pencil"></i></span>
											<span class="btn btn-sm btn-outline-danger" data-toggle="modal" data-target="#delete-maintainance" data-toggle="tooltip" data-item="<?php echo e(json_encode($item->id)); ?>" data-name="Repair Log" title="Delete"><i class="mdi mdi-delete-empty"></i></span>

										</td>
										<td><?php echo e($item->maintainance_type == 'in-house' ? 'In house':'External'); ?></td>
										<td>

											<?php echo e($item->maintainance_type != 'in-house' ? getSupplierByID($item->supplier_id)->name ?? '-' : getUserById($item->employee_id)->name ?? '-'); ?>


										</td>
										<td><?php echo e($item->date); ?></td>

										<td><a href="<?php echo e($item->certificate != '' ? $item->certificate : '#'); ?>" class="btn btn-sm btn-transparent" target="_blank"><i class="mdi mdi-download text-success"></i> Download</a></td>
										<td><?php echo e($item->overseer()->name); ?></td>
										
										<td><?php echo e(getSupplierContactByID($item->supplier_contact_id)->name ?? '-'); ?></td>

										<td>
											<!-- --------------  -->
											<span class="btn btn-info btn-sm" data-item="<?php echo e(json_encode($equipment->name)); ?>" data-parts="<?php echo e(json_encode($part_)); ?>" data-toggle="modal" data-target="#repair-content"><i class="mdi mdi-eye"></i></span>

											<!-- --------------  -->


										</td>
										<td>
											<span style="margin-right: 30px;" class="btn btn-success btn-sm" data-toggle="modal" data-target="#view-repair-<?php echo e($lp); ?>"> <i class="mdi mdi-eye"></i> </span>
											<div id="view-repair-<?php echo e($lp); ?>" class="modal fade" role="dialog">
												<div class="modal-dialog">
													<div class="modal-content">
														<div class="modal-header">
															<h5 class="modal-title">Repair Description</h5>
														</div>
														<div class="modal-body">

															<div class="panel panel-default">
																<div class="panel-body">
																	<?php echo e($item->description); ?>

																</div>
															</div>
															<br>


														</div>
														<div class="modal-footer">
															<button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
														</div>
													</div>
												</div>
											</div>
										</td>


									</tr>
									<?php endif; ?>
									<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
								</tbody>
							</table>
						</div>
					</div>
					<!-- ---------------------------------------------------------- -->
					<div class="tab-pane fade p-3" id="equipment-operators" role="tabpanel" aria-labelledby="one-tab">
						<h5 class="card-title">
							Operators
							<button class="btn btn-primary float-right btn-sm" data-toggle="modal" data-target="#add-operator-modal"><i class="mdi mdi-plus"></i> Add</button>
						</h5>
						<div class="p-0">
							<?php $__currentLoopData = $equipment->operators; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
							<button type="button" class="btn btn-default-light btn-sm m-2" style="border: 1px solid #ccc">
								<i class="mdi mdi-account"></i> <?php echo e($item->operator()->name); ?> <small><?php echo e($item->operator()->email); ?></small>
								<i class="mdi mdi-close-circle text-danger" style="font-size: 16px; padding-top: 2px" data-toggle="modal" data-target="#delete-operator-<?php echo e($loop->iteration); ?>"></i>
							</button>
							<div id="delete-operator-<?php echo e($loop->iteration); ?>" class="modal fade" role="dialog">
								<div class="modal-dialog">
									<!-- Modal content-->
									<form class="modal-content" method="POST" action="<?php echo e(route('remove-operator', ['id'=>$item->id])); ?>" enctype="multipart/form-data">
										<?php echo csrf_field(); ?>
										<div class="modal-header">
											<h4 class="modal-title"><i class="mdi mdi-delete"></i> Remove Operator</h4>
										</div>
										<div class="modal-body">
											<div class="form-group">
												<div class="alert alert-callout alert-danger">
													<i class="fas fa-exclamation-triangle"></i> Are you sure that you want to remove this operator?
												</div>
											</div>
										</div>
										<div class="modal-footer">
											<button type="submit" class="btn btn-danger remove-operator"><i class="mdi mdi-trash"></i> Remove</button>
											<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
										</div>
									</form>
								</div>
							</div>
							<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
							<?php if(count($equipment->operators) == 0): ?>
							<div class="alert alert-info">
								<i class="mdi mdi-alert"></i> No Equipment Operators added yet.
							</div>
							<?php endif; ?>
						</div>
					</div>
					<div class="tab-pane fade p-3" id="calibration-log" role="tabpanel" aria-labelledby="one-tab">
						<h5 class="card-title">Calibration / Servicing Logs </h5>
						<div class="table-responsive">
							<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
								<thead class="bg-light p-2">
									<tr>
										<th></th>
										<th>Type</th>
										<th nowrap>Service Provider</th>
										<th nowrap>Date</th>
										<th>Certificate</th>
										<th nowrap>Overseen By</th>
										
										<th>Contact Person</th>
										
										<th nowrap>Notes</th>

									</tr>
								</thead>
								<tbody>

									<?php $__currentLoopData = $equipment->maintainance_Calibration_logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
									<?php if($item->type=="Calibration"): ?>

									<tr>
										<td style="min-width: 70px;">
											<span class="btn btn-outline-info btn-sm" data-toggle="modal" data-target="#edit-calibration" data-calibration="<?php echo e(json_encode($item)); ?>" data-employees="<?php echo e(json_encode($employees)); ?>" data-suppliers="<?php echo e(json_encode($suppliers)); ?>" data-toggle="tooltip" title="Edit"> <i class="mdi mdi-pencil"></i></span>
											<span class="btn btn-sm btn-outline-danger" data-toggle="modal" data-target="#delete-maintainance" data-toggle="tooltip" data-item="<?php echo e(json_encode($item->id)); ?>" data-name="Calibration Log" title="Delete"><i class="mdi mdi-delete-empty"></i></span>

										</td>
										<td><?php echo e($item->maintainance_type == 'in-house' ? 'In house':'External'); ?></td>
										<td>
											<?php echo e($item->maintainance_type != 'in-house' ? getSupplierByID($item->supplier_id)->name : getUserById($item->employee_id)->name); ?>

										</td>
										<td><?php echo e($item->date); ?></td>
										<td><a href="<?php echo e($item->certificate != '' ? $item->certificate : '#'); ?>" class="btn btn-sm btn-transparent" target="_blank"><i class="mdi mdi-download text-success" data-toggle="tooltip" title="Download"></i></a></td>
										<td><?php echo e($item->overseer()->name ?? ''); ?></td>
										
										<td><?php echo e(getSupplierContactByID($item->supplier_contact_id)->name ?? '-'); ?></td>
										
										<td>
											<span class="btn btn-info btn-sm" data-toggle="modal" data-target="#content-cal"><i class="mdi mdi-eye"></i></span>
											<div id="content-cal" class="modal fade" role="dialog">
												<div class="modal-dialog">
													<!-- Modal content-->
													<div class="modal-content">

														<div class="modal-body">
															<div class="modal-body">
																<div class="alert alert-primary p-2">
																	<p><?php echo e($item->notes); ?></p>
																</div>

															</div>
														</div>
														<div class="modal-footer">
															<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
														</div>
													</div>

												</div>
											</div>
										</td>

									</tr>
									<?php endif; ?>
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

<div class="modal fade" id="delete-maintainance" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<form action="<?php echo e(route('delete-logs')); ?>" method="post">
				<?php echo csrf_field(); ?>
				<div class="modal-body">

				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-outline-success btn-sm"><i class="mdi mdi-content-save"></i> Confirm</button>
					<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
				</div>
			</form>
		</div>
	</div>
</div>
<div id="attachment-description" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-body">

			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
			</div>
		</div>

	</div>
</div>
<div class="modal fade" id="create-attachment" role="dialog">
	<div class="modal-dialog">''
		<form action="<?php echo e(route('add_equipment_attachment')); ?>" enctype="multipart/form-data" method="post" class="modal-content">
			<?php echo csrf_field(); ?>
			<div class="modal-header">
				<h5 class="modal-title">Add attachment for equipment <?php echo e($equipment->name); ?></h5>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<label class="control-label">Title <span class="text-danger">*</span></label>
					<input type="text" name="title" required placeholder="Title..." class="form-control">
					<input type="hidden" name="equipment_id" value="<?php echo e($equipment->id); ?>">
					<input type="hidden" name="attach_id" value="">
				</div>
				<div class="form-group">
					<label class="control-label">Attachment <span class="text-danger">*</span></label>
					<input type="file" name="attachment" class="form-control" required>
				</div>
				<div class="form-group">
					<label class="control-label">Description</label>
					<textarea class="form-control" rows="6" name="description" placeholder="Notes..."></textarea>
				</div>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-outline-primary btn-sm"><i class="mdi mdi-content-save"></i> Save</button>
				<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
			</div>
		</form>
	</div>
</div>
<div class="modal fade" id="edit-attachment" role="dialog">
	<div class="modal-dialog">
		<form action="<?php echo e(route('add_equipment_attachment')); ?>" enctype="multipart/form-data" method="post" class="modal-content">
			<?php echo csrf_field(); ?>
			<div class="modal-header"></div>
			<div class="modal-body"></div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-outline-primary btn-sm"><i class="mdi mdi-content-save"></i> Save</button>
				<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
			</div>
		</form>
	</div>
</div>
<div id="edit-calibration" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<form class="modal-content" method="POST" action="<?php echo e(route('edit-maintainance')); ?>" enctype="multipart/form-data">
			<?php echo csrf_field(); ?>
			<div class="modal-header">
				<h4 class="modal-title"><i class="mdi mdi-pencil"></i> Edit Calibration / Servicing Log</h4>
			</div>
			<div class="modal-body" id="edit-calibration-body"></div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</form>
	</div>
</div>
<div id="edit-maintainance" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<form class="modal-content" method="POST" action="<?php echo e(route('edit-maintainance')); ?>" enctype="multipart/form-data">
			<?php echo csrf_field(); ?>
			<div class="modal-header">

			</div>
			<div class="modal-body" id="edit-maintainance-body"></div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</form>
	</div>
</div>
<div id="edit-equipment" data-backdrop="static" data-keyboard="false" class="modal fade" role="dialog">
	<div class="modal-dialog modal-xl">
		<!-- Modal content-->
		<form class="modal-content" method="POST" action="<?php echo e(route('edit-equipment', ['id'=>$equipment->id])); ?>" enctype="multipart/form-data">
			<?php echo csrf_field(); ?>
			<div class="modal-header">
				<h4 class="modal-title"><i class="mdi mdi-pencil"></i> Edit Equipment
				</h4>
				<span type="button" class="btn btn-danger btn-sm float-right" data-dismiss="modal">Close</span>

			</div>
			<div class="row">
				<div class="col-md-6 col-sm-6">
					<div class="modal-body">
						<div class="form-group">
							<label class="control-label">Name </label>
							<input type="text" class="form-control" name="name" value="<?php echo e($equipment->name); ?>" placeholder="Name..." />
						</div>
						<div class="form-group">
							<label class="control-label">Number </label>
							<input type="text" class="form-control" name="equipment_number" value="<?php echo e($equipment->equipment_number); ?>" placeholder="Equipment Number..." />
						</div>
						<div class="form-group">
							<label class="control-label">Photo <small style="color: red;">*leave it blank to maintain the present image</small></label>
							<input type="file" class="form-control" name="photo" />
						</div>
						<div class="form-group">
							<label class="control-label">Description </label>
							<textarea class="form-control" name="description" placeholder="Equipment Description..."><?php echo e($equipment->description); ?></textarea>
						</div>
						<div class="form-group">
							<label class="control-label">Serial Number </label>
							<input type="text" class="form-control" name="serial" value="<?php echo e($equipment->serial_number); ?>" placeholder="Serial Number..." />
						</div>
						<div class="form-group">
							<label class="control-label">Manufacturer</label>
							<input type="text" class="form-control" name="manufacturer" value="<?php echo e($equipment->manufacturer); ?>" placeholder="Manufacturer..." />
						</div>
						<div class="form-group">
							<label class="control-label">Make </label>
							<input type="text" class="form-control" name="make" value="<?php echo e($equipment->make); ?>" placeholder="Equipment Make..." />
						</div>
						<div class="form-group">
							<label class="control-label">Model</label>
							<input type="text" class="form-control" name="model" value="<?php echo e($equipment->model); ?>" placeholder="Equipment Model..." />
						</div>
						<div class="form-group">
							<label class="control-label">Barcode Number</label>
							<input type="text" class="form-control" name="barcode" value="<?php echo e($equipment->barcode_number); ?>" placeholder="Barcode Number..." />
						</div>
						<div class="form-group">
							<label for="" class="control-label"><input type="checkbox" <?php echo e($equipment->requires_calibration == 1 ? 'checked' : ''); ?> name="requires_calibration" id=""> Requires Calibrations</label>
						</div>
					</div>
				</div>
				<div class="col-md-6 col-sm-6">
					<div class="modal-body">
						<?php

						$locations = getAssetLocation();
						?>
						<input type="hidden" name="is_lab" value="1">
						<div class="form-group">
							<label class="control-label">Current Location</label>
							<select name="location_id" aria-readonly="true" id="" class="form-control">
								<?php $__currentLoopData = $locations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $location): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
								<?php if($location->is_active == 1): ?>
								<option value="<?php echo e($location->id); ?>" <?php echo e($equipment->asset_location_id == $location->id ? 'selected':''); ?>><?php echo e($location->name); ?></option>
								<?php endif; ?>
								<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
							</select>
						</div>
						<div class="form-group">
							<label class="control-label">Location In Laboratory</label>
							<input type="text" name="current_loc" value="<?php echo e($equipment->current_location); ?>" class="form-control">
						</div>
						<div class="form-group">
							<label class="control-label">Supplier</label>
							<select name="supplier_id" id="" class="form-control">
								<?php $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supplier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
								<option value="<?php echo e($supplier->id); ?>" <?php echo e($equipment->supplier_id == $supplier->id ? 'selected' : ''); ?>><?php echo e($supplier->name); ?></option>
								<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
							</select>
						</div>



						<div class="form-group">
							<label class="control-label">Status</label>
							<select name="status" id="assign-status" class="form-control" readonly="true" placeholder="Assign Status...">
								<?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
								<option value="<?php echo e($status); ?>" <?php echo e($equipment->status == $status ? 'selected':''); ?>><?php echo e($status); ?></option>
								<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
							</select>
						</div>
						<div class="form-group">
							<label class="control-label">Equipment Condition <span class="text-danger">*</span></label>
							<input type="text" class="form-control" name="condition" value="<?php echo e($equipment->condition); ?>" placeholder="Equipment Condition..." required />
						</div>


						<!-- -------------------- -->
						<div class="form-group">
							<label class="control-label">Date Purchased / Received / Installed </label>
							<input type="date" class="form-control" name="date_purchased" value="<?php echo e($equipment->date_purchased); ?>" placeholder="Date Purchased..." />
						</div>
						<div class="form-group">
							<div class="row no-gutters">
								<div class="col-xs-7">
									<label class="control-label">Maintainance After<small> (In Days)</small></label>
									<input type="number" min="0" class="form-control" name="maintainance_in_days" value="<?php echo e($equipment->maintainance_days); ?>" placeholder="Maintanance In Days..." />
								</div>
								<div class="col-xs-5 pl-2">
									<label class="control-label">Notification (In Days)</small></label>
									<input type="number" min="0" class="form-control" name="maintainance_notification_in_days" value="<?php echo e($equipment->maintainance_notification_in_days); ?>" placeholder="Notification Days..." />
								</div>
							</div>
						</div>
						<div class="form-group">
							<label class="control-label">Last Maintainance Date </label>
							<input type="date" name="maintainance_last_date" value="<?php echo e($equipment->maintainance_last_date); ?>" class="form-control">

						</div>
						<div class="form-group">
							<div class="row no-gutters">
								<div class="col-xs-7">
									<label class="control-label">Calibration After<small> (In Days)</small> <small class="text-danger">*</small></label>
									<input type="number" min="0" class="form-control" name="calibration_in_days" value="<?php echo e($equipment->calibration_days); ?>" placeholder="Calibration In Days..." required />
								</div>
								<div class="col-xs-5 pl-2">
									<label class="control-label">Notification (In Days)</small> <small class="text-danger">*</small></label>
									<input type="number" min="0" class="form-control" name="calibration_notification_in_days" value="<?php echo e($equipment->calibration_notification_in_days); ?>" placeholder="Notification Days..." required />
								</div>
							</div>
						</div>
						<div class="form-group">
							<label class="control-label">Last Calibration Date <small class="text-danger">*</small></label>
							<input type="date" name="calibration_last_date" value="<?php echo e($equipment->calibration_last_date); ?>" class="form-control">
						</div>
						<div class="form-group">
							<label class="control-label"><input name="active" value="1" type="checkbox" <?php echo e($equipment->active == 1 ? 'checked' : ''); ?> /> Is Active</label>
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
<div id="create-new-maintainance" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<form class="modal-content" method="POST" action="<?php echo e(route('new-maintainance', ['id'=>$equipment->id])); ?>" enctype="multipart/form-data">
			<?php echo csrf_field(); ?>
			<div class="modal-header">
				<h4 class="modal-title"><i class="mdi mdi-pencil"></i> Create <span class="select-maintainance-type"></span> Log</h4>
			</div>
			<div class="modal-body">
				<div class="form-group hidden">
					<label class="control-label">Service Provider <span class="text-danger">*</span></label>
					<select name="service_provider" id="assign-supplier" placeholder="Service Provider" class="form-control" required readonly="true">
						<?php $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supplier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
						<option value="<?php echo e($supplier->id); ?>"><?php echo e($supplier->name); ?></option>
						<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
					</select>
				</div>
				<div class="form-group hidden">
					<label class="control-label">Type</label>
					<input class="form-control" type="text" name="type" value="Maintainance" placeholder="Type...">

				</div>

				<div class="form-group">
					<label class="control-label">Date <span class="text-danger">*</span></label>
					<input type="date" class="form-control" name="date" value="" placeholder="Date..." required />
				</div>
				<div class="form-group">
					<label class="control-label">Description <span class="text-danger">*</span></label>
					<textarea class="form-control" rows="6" name="description" placeholder="Description..." required></textarea>
				</div>
				<div class="form-group">
					<label class="control-label">Reference Number </label>
					<input type="text" class="form-control" name="reference" placeholder="Reference Number..." />
				</div>
				<!-- -----------  -->

				<div class="form-check">
					<input class="form-check-input" type="radio" id="is-house" class="form-control" name="maintainance_type" value="in_house" onclick="inhouse()" />
					<label class="form-check-label" for="is-house">
						In House Service Performer
					</label>
				</div>
				<br>
				<div class="form-check">
					<input class="form-check-input" type="radio" id="externals" class="form-control" name="maintainance_type" value="external" onclick="external()" />
					<label class="form-check-label" for="externals">
						External Service Performer
					</label>
				</div>
				<br>
				<div class="form-group" id="employee" style="display:none">
					<label class="control-label">Employee</label>
					<select name="employee" class="form-control" placeholder="Employee...">
						<?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
						<option value="<?php echo e($employee->id); ?>"><?php echo e($employee->name); ?></option>
						<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
					</select>
				</div>
				<div class="form-group" id="supplier" style="display:none">
					<label class="control-label">Supplier</label>
					<select name="supplier" id="service_provider" class="form-control" placeholder="Supplier...">
						<option value="">Choose Service Provider</option>
						<?php $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supplier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
						<option value="<?php echo e($supplier->id); ?>"><?php echo e($supplier->name); ?></option>
						<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
					</select>
				</div>
				<div class="form-group hidden" id="supplier-contact">
					<label class="control-label">Contact Person</label>
					<select name="contact_person_id" id="contact_person" class="form-control"></select>
				</div>
				<div class="form-group">
					<label class="control-label">Overseen By</label>
					<select name="overseen_by" id="overseen-by" class="form-control">
						<option value="">Choose Supervisor</option>
						<?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
						<option value="<?php echo e($user->id); ?>"><?php echo e($user->name); ?></option>
						<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
					</select>
				</div>
				<!-- ----------  -->
				<div class="form-group">
					<label class="control-label">Certificate</label>
					<input type="file" class="form-control" name="certificate" value="" placeholder="Certificate..." />
				</div>
				<div class="form-group">
					<label class="control-label">Remark <span class="text-danger">*</span></label>
					<textarea class="form-control" rows="6" name="notes" placeholder="Notes..." required></textarea>
				</div>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</form>
	</div>
</div>

<!-- --------------  -->
<div id="create-new-calibration" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<form class="modal-content" method="POST" action="<?php echo e(route('new-maintainance', ['id'=>$equipment->id])); ?>" enctype="multipart/form-data">
			<?php echo csrf_field(); ?>
			<div class="modal-header">
				<h4 class="modal-title"><i class="mdi mdi-pencil"></i> Create Calibration / Servicing Log</h4>
			</div>
			<div class="modal-body">

				<div class="form-group hidden">
					<label class="control-label">Type</label>
					<input class="form-control" type="text" name="type" value="Calibration" placeholder="Type...">

				</div>

				<div class="form-group">
					<label class="control-label">Date <span class="text-danger">*</span></label>
					<input type="date" class="form-control" name="date" value="" placeholder="Date..." required />
				</div>
				<div class="form-group">
					<label class="control-label">Description <span class="text-danger">*</span></label>
					<textarea class="form-control" rows="6" name="description" placeholder="Description..." required></textarea>
				</div>
				<div class="form-group">
					<label class="control-label">Reference Number <span class="text-danger">*</span></label>
					<input type="text" class="form-control" name="reference" placeholder="Reference Number..." required />
				</div>


				<div class="form-check">
					<input class="form-check-input" type="radio" id="is-housed" class="form-control" name="maintainance_type" value="in_house" onclick="inhoused()" />
					<label class="form-check-label" for="is-house">
						In House Maintanance Type
					</label>
				</div>
				<br>
				<div class="form-check">
					<input class="form-check-input" type="radio" id="external" class="form-control" name="maintainance_type" value="external" onclick="externaled()" />
					<label class="form-check-label" for="externals">
						External Maintainance Type
					</label>
				</div>
				<br>
				<div class="form-group" id="employees" style="display:none">
					<label class="control-label">Employee</label>
					<select name="employee" class="form-control" placeholder="Employee...">
						<?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
						<option value="<?php echo e($employee->id); ?>"><?php echo e($employee->name); ?></option>
						<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
					</select>
				</div>
				<div class="form-group" id="suppliers" style="display:none">
					<label class="control-label">Service Provider</label>
					<select name="supplier" id="service_provider" class="form-control" placeholder="Supplier...">
						<option value="">Choose Supplier</option>
						<?php $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supplier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
						<option value="<?php echo e($supplier->id); ?>"><?php echo e($supplier->name); ?></option>
						<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
					</select>
				</div>
				<div class="form-group hidden" id="supplier-contact">
					<label class="control-label">Contact Person</label>
					<select name="contact_person_id" id="contact_person" class="form-control">
						
					</select>
				</div>
				<div class="form-group">
					<label class="control-label">Overseen By</label>
					<select name="overseen_by" id="overseen-by" class="form-control">
						<option value="">Choose Supervisor...</option>
						<?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
						<option value="<?php echo e($user->id); ?>"><?php echo e($user->name); ?></option>
						<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
					</select>
				</div>
				<!-- ----------  -->
				<div class="form-group">
					<label class="control-label">Certificate</label>
					<input type="file" class="form-control" name="certificate" value="" placeholder="Certificate..." />
				</div>
				<div class="form-group">
					<label class="control-label">Notes <span class="text-danger">*</span></label>
					<textarea class="form-control" rows="6" name="notes" placeholder="Notes..." required></textarea>
				</div>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</form>
	</div>
</div>
<!-- verification log  -->
<div id="create-verification-log" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<form class="modal-content" method="POST" action="<?php echo e(route('add-verification',['id'=>$equipment->id])); ?>" enctype="multipart/form-data">
			<?php echo csrf_field(); ?>
			<div class="modal-header">
				<h4 class="modal-title"><i class="mdi mdi-pencil"></i> Create Verification Log</h4>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<label class="control-label">Reference Standard <span class="text-danger">*</span></label>
					<input type="text" class="form-control" name="reference" placeholder="Reference Standards..." required />
				</div>
				<div class="form-group hidden">
					<label class="control-label">Equipment <span class="text-danger">*</span></label>
					<input type="text" class="form-control" name="equipment" value="<?php echo e($equipment->id); ?>" placeholder="Equipment..." required />
				</div>
				<div class="form-check">
					<input class="form-check-input" type="radio" id="is-house" class="form-control" name="maintainance_type" value="in_house" />
					<label class="form-check-label" for="is-house">
						In House Service Performer
					</label>
				</div>
				<br>
				<div class="form-check">
					<input class="form-check-input" type="radio" id="externals" class="form-control" name="maintainance_type" value="external" />
					<label class="form-check-label" for="externals">
						External Service Performer
					</label>
				</div>
				<br>
				<div class="form-group hidden" id="employee">
					<label class="control-label">Employee</label>
					<select name="employee" class="form-control" placeholder="Employee...">
						<?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
						<option value="<?php echo e($employee->id); ?>"><?php echo e($employee->name); ?></option>
						<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
					</select>
				</div>
				<div class="form-group hidden" id="supplier">
					<label class="control-label">Service Provider</label>
					<select name="supplier" id="service_provider" class="form-control" placeholder="Supplier...">
						<option value="">Choose Service Performer</option>
						<?php $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supplier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
						<option value="<?php echo e($supplier->id); ?>"><?php echo e($supplier->name); ?></option>
						<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
					</select>
				</div>
				<div class="form-group hidden" id="supplier-contact">
					<label class="control-label">Contact Person</label>
					<select name="contact_person_id" id="contact_person" class="form-control"></select>
				</div>
				<div class="form-group">
					<label class="control-label">Overseen By</label>
					<select name="overseen_by" id="overseen-by" class="form-control">
						<option value="">Choose Supervisor</option>
						<?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
						<option value="<?php echo e($user->id); ?>"><?php echo e($user->name); ?></option>
						<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
					</select>
				</div>
				<div class="form-group ">
					<label class="control-label">Date of Verifiation</label>
					<input class="form-control" type="date" name="date" placeholder="Date of Verification...">
				</div>

				<div class="form-group">
					<label class="control-label">Operator</label>
					<select name="operator" class="form-control" placeholder="Operator...">
						<?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
						<option value="<?php echo e($employee->id); ?>"><?php echo e($employee->name); ?></option>
						<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
					</select>
				</div>

				<div class="form-group">
					<label class="control-label">Procedure <span class="text-danger">*</span></label>
					<textarea class="form-control" rows="4" name="procedure" placeholder="Procedure..." required></textarea>
				</div>

				<div class="form-group">
					<label class="control-label">Responses/Readings <span class="text-danger">*</span></label>
					<textarea class="form-control" rows="4" name="response" placeholder="Responses..." required></textarea>
				</div>

				<div class="form-group">
					<label class="control-label">Remarks <span class="text-danger">*</span></label>
					<textarea class="form-control" rows="4" name="remark" placeholder="Remarks..." required></textarea>
				</div>

			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</form>
	</div>
</div>
<!-- verification log end -->
<div id="repair-content" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header text-center">
				<h5 class="modal-title"><?php echo e($equipment->name); ?> Repaired Parts</h5>
			</div>
			<div class="modal-body">


			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
			</div>
		</div>

	</div>
</div>
<!-- parts section  -->
<div id="create-new-repairment" class="modal fade" role="dialog">
	<div class="modal-dialog modal-lg">
		<!-- Modal content-->
		<form class="modal-content" method="POST" action="<?php echo e(route('new-maintainance', ['id'=>$equipment->id])); ?>" enctype="multipart/form-data">
			<?php echo csrf_field(); ?>
			<div class="modal-header bg-light">
				<h4 class="modal-title"><i class="mdi mdi-pencil"></i> Create Repair Log</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-md-6 col-sm-6 col-lg-6">
						<div class="form-group hidden">
							<label class="control-label">Type</label>
							<input name="type" value="Repairement">
						</div>
						<div class="form-group">
							<label class="control-label">Date <span class="text-danger">*</span></label>
							<input type="date" class="form-control" name="date" value="" placeholder="Date..." required />
						</div>
						<div class="form-group">
							<label class="control-label">Description <span class="text-danger">*</span></label>
							<textarea class="form-control" rows="6" name="description" placeholder="Description..." required></textarea>
						</div>

					</div>
					<div class="col-md-6 col-sm-6 col-lg-6">
						<div class="form-check">
							<input class="form-check-input" type="radio" id="is-house" class="form-control" name="maintainance_type" value="in_house" />
							<label class="form-check-label" for="is-house">
								In House Maintanance Type
							</label>
						</div>
						<br>
						<div class="form-check">
							<input class="form-check-input" type="radio" id="externals" class="form-control" name="maintainance_type" value="external" />
							<label class="form-check-label" for="externals">
								External Maintainance Type
							</label>
						</div>
						<br>
						<div class="form-group hidden" id="employee">
							<label class="control-label">Employee</label>
							<select name="employee" class="form-control" placeholder="Employee...">
								<?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
								<option value="<?php echo e($employee->id); ?>"><?php echo e($employee->name); ?></option>
								<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
							</select>
						</div>
						<div class="form-group hidden" id="supplier">
							<label class="control-label">Supplier</label>
							<select name="supplier" id="service_provider" class="form-control" placeholder="Supplier...">
								<option value="">Choose Service Performer</option>
								<?php $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supplier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
								<option value="<?php echo e($supplier->id); ?>"><?php echo e($supplier->name); ?></option>
								<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
							</select>
						</div>
						<div class="form-group hidden" id="supplier-contact">
							<label class="control-label">Contact Person</label>
							<select name="contact_person_id" id="contact_person" class="form-control"></select>
						</div>
						<div class="form-group">
							<label class="control-label">Overseen By</label>
							<select name="overseen_by" id="overseen-by" class="form-control">
								<option value="">Choose Supervisor...</option>
								<?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
								<option value="<?php echo e($user->id); ?>"><?php echo e($user->name); ?></option>
								<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
							</select>
						</div>
						<div class="form-group">
							<label class="control-label">Certificate</label>
							<input type="file" class="form-control" name="certificate" value="" placeholder="Certificate..." />
						</div>
						<div class="form-group">
							<label class="control-label">Remarks <span class="text-danger">*</span></label>
							<textarea class="form-control" rows="4" name="notes" placeholder="Notes..." required></textarea>
						</div>
					</div>
				</div>

				<hr>
				<div class="parts-repaired">
					<h6 class="bg-light p-3 mb-2">
						<span class="btn btn-outline-primary btn-sm float-right mt-0" id="new-part"><i class="mdi mdi-plus"></i> Add New Part</span>
						<u>Parts Repaired</u>
					</h6>
				</div>


			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</form>
	</div>
</div>
<div id="edit-new-repairment" class="modal fade" role="dialog">
	<div class="modal-dialog modal-lg">
		<!-- Modal content-->
		<form class="modal-content" method="POST" action="<?php echo e(route('edit-maintainance')); ?>" enctype="multipart/form-data">
			<?php echo csrf_field(); ?>
			<div class="modal-header bg-light">
				<h4 class="modal-title"><i class="mdi mdi-pencil"></i> Edit Repair Log</h4>
			</div>
			<div class="modal-body">
				<div class="modal-body2"></div>
				<div class="parts-repaired">
					<h6 class="bg-light p-3 mb-2">
						<span class="btn btn-outline-primary btn-sm float-right mt-0" id="new-part"><i class="mdi mdi-plus"></i> Add New Part</span>
						<u>Parts Repaired</u>
					</h6>
					<div id="parts-repair"></div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
				<a href="/equipment/<?php echo e($equipment->id); ?>" class="btn btn-sm btn-outline-danger">Close</a>

			</div>
		</form>
	</div>
</div>
<!-- part section  -->

<div id="add-operator-modal" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<form class="modal-content" method="POST" action="<?php echo e(route('add-operators', ['equipment'=>$equipment->id])); ?>" enctype="multipart/form-data">
			<?php echo csrf_field(); ?>
			<div class="modal-header">
				<h4 class="modal-title"><i class="mdi mdi-account-multiple-plus"></i> Add Equipment Operators</h4>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<label class="control-label">Select Operators <span class="text-danger">*</span></label>
					<select class="form-control" name="operators[]" placeholder="Select Operators..." multiple required>
						<?php $__currentLoopData = getUsers(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
						<option value="<?php echo e($item->id); ?>"><?php echo e($item->name); ?></option>
						<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
					</select>
				</div>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-success"><i class="mdi mdi-content-save"></i> Save</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</form>
	</div>
</div>

<script>
	var counter = 1
	$(function() {
		let getSupplierContacts = (supplier_id,callback)=>{
			$.ajax({
				url:`/getSupplierContact/${supplier_id}`,
				type:'GET',
				success:(data)=>{
					callback(data)
				},
				error:(data)=>{
					console.log(data)
				}
			})
		}
		// $('#overseen-by').select2();

		$('#create-new-calibration').on('show.bs.modal',()=>{
			$('#create-new-calibration').find('#overseen-by').select2()
			$(this).find('input[name="maintainance_type"]').on('change',(e)=>{
				if($(e.target).val() == 'in_house'){
					$('#create-new-calibration').find('#supplier-contact').addClass('hidden')
				}else{
					$('#create-new-calibration').find('#supplier-contact').removeClass('hidden')
				}
			})
			$(this).find('#service_provider').on('change',(e)=>{
				let supplier_id = $(e.target).val();
				console.log(supplier_id)
				$('#create-new-calibration').find('#contact_person').empty()
				getSupplierContacts(supplier_id,(data)=>{
					$.each(data,(i,e)=>{
						let option_body = `<option value="${e.id}">${e.name}</option>`
						
						$('#create-new-calibration').find('#contact_person').append(option_body)
					})
					$('#create-new-calibration').find('#contact_person').select2()
				})
			})
		})

		$('#delete-maintainance').on('show.bs.modal', function(e) {
			var item = $(e.relatedTarget).data('item');
			var name = $(e.relatedTarget).data('name');
			var m_text = delete_m(name, item);
			$('#delete-maintainance').find('.modal-body').empty();
			$('#delete-maintainance').find('.modal-body').append(m_text);

		})
		var delete_m = function(name, item) {
			var text_m = $(`
			<div class="alert alert-danger p-4">
						Confirm you want to delete this ${name} record !
						<input type="hidden" name="item_id" value="${item}">
					</div>
			`).clone();
			return text_m;
		}
		$('#repair-content').on('show.bs.modal', function(e) {
			var parts = $(e.relatedTarget).data('parts');
			var item_name = $(e.relatedTarget).data('item');
			console.log(item_name)
			$('#repair-content').find('.modal-body').empty();

			$.each(parts, function(i, e) {
				var text_p = `
				<li>
					<div class="panel panel-default">
						<div class="panel-body">
							<p><b>${e.name} - </b>${e.comment}</p>
						</div>
					</div>
				</li>
				`;
				$('#repair-content').find('.modal-body').append(text_p);
			});
		})
		$('#create-new-repairment').on('show.bs.modal', function() {
			$(this).find('#new-part').on('click', function() {

				var parts_text = parts_new()
				var text_ = $(parts_text).clone();
				$(text_).find('#delete-parts').on('click', function() {
					var div = $(this).parent('div');
					$(div).parent('section').remove();
					console.log('done')
				})
				$('#create-new-repairment').find('.parts-repaired').append(text_);


			})
			$('#create-new-repairment').find('#overseen-by').select2()
			$(this).find('input[name="maintainance_type"]').on('change',(e)=>{
				if($(e.target).val() == 'in_house'){
					$('#create-new-repairment').find('#supplier-contact').addClass('hidden')
				}else{
					$('#create-new-repairment').find('#supplier-contact').removeClass('hidden')
				}
			})
			$(this).find('#service_provider').on('change',(e)=>{
				let supplier_id = $(e.target).val();
				console.log(supplier_id)
				$('#create-new-repairment').find('#contact_person').empty()
				getSupplierContacts(supplier_id,(data)=>{
					$.each(data,(i,e)=>{
						let option_body = `<option value="${e.id}">${e.name}</option>`
						
						$('#create-new-repairment').find('#contact_person').append(option_body)
					})
					$('#create-new-repairment').find('#contact_person').select2()
				})
			})
		})
		$('#edit-new-repairment').on('show.bs.modal', function(e) {
			$(this).find('.modal-body2').empty();
			var data = $(e.relatedTarget).data('repair');
			var employees = $(e.relatedTarget).data('employees');
			var suppliers = $(e.relatedTarget).data('suppliers');
			var parts = $(e.relatedTarget).data('parts');

			$('#edit-new-repairment').find('.modal-header').empty();
			var header_ = `<h4 class="modal-title"><i class="mdi mdi-pencil"></i> Edit Repair Log</h4>`
			$('#edit-new-repairment').find('.modal-header').append(header_)
			var edit_form = edit_repair(data);
			$.each(employees, function(i, e) {
				var options = `<option value="${e.id}" ${e.id == data.employee_id ? 'selected' : ''} >${e.name}</option>`;
				$(edit_form).find('[name="employee"]').append(options);
			});
			$.each(suppliers, function(i, e) {
				var options = `<option value="${e.id}" ${e.id == data.supplier_id ? 'selected' : ''} >${e.name}</option>`;
				$(edit_form).find('[name="supplier"]').append(options);
			});
			if (data.maintainance_type === 'in-house') {
				$(edit_form).find('#is-house').prop('checked', true);
				$(edit_form).find('#supplier-').addClass('hidden');
				$(edit_form).find('#employee-').removeClass('hidden');
			}
			if (data.maintainance_type === 'external') {
				$(edit_form).find('#externals').prop('checked', true);
				$(edit_form).find('#supplier-').removeClass('hidden');
				$(edit_form).find('#employee-').addClass('hidden');
			}
			if (data.maintainance_type != 'in-house' && data.maintainance_type != 'external') {
				$(edit_form).find('#employee-').addClass('hidden');
				$(edit_form).find('#supplier-').addClass('hidden');
			}

			$(edit_form).find('#reference-number').remove();
			if (data.maintainance_type === 'in-house') {
				$(edit_form).find('#is-house').prop('checked', true);

			}
			if (data.maintainance_type === 'external') {
				$(edit_form).find('#externals').prop('checked', true);
			}
			$(edit_form).find('[name="maintainance_type"]').on('change', function() {
				if ($(edit_form).find('input[type="radio"]:checked').val() == "in_house") {
					$(edit_form).find('#supplier-').addClass('hidden');
					$(edit_form).find('#employee-').removeClass('hidden');
				}
				if ($(edit_form).find('input[type="radio"]:checked').val() == "external") {
					$(edit_form).find('#supplier-').removeClass('hidden');
					$(edit_form).find('#employee-').addClass('hidden');
				}

			});
			$('#edit-new-repairment').find('.modal-body2').append(edit_form);
			var loop_ = 0

			$('#edit-new-repairment').find('#parts-repair').empty();
			$.each(parts, function(i, e) {

				var part_text = new_part(e);
				$(part_text).find('#part-delete').on('click', function() {
					var div = $(this).parent('div');
					if (confirm("Confirm you want to delete this record!")) {
						$.ajaxSetup({
							headers: {
								'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
							}
						});
						$.ajax({
							url: '/delete/part-repaired/',
							data: {
								id: e.id
							},
							method: 'post',

							success: function(data) {
								console.log(data)
								if (data === 'success') {
									$(div).parent('div').remove();
								}
							},
							error: function(data) {
								console.log(data)
							}
						});
					}
				});
				$('#edit-new-repairment').find('#parts-repair').append(part_text);

				++loop_;
			})


		})

		var parts_new = function() {
			var text = `
				<section class="row">
					<div class="col-md-2  text-center">
						<span  class="btn mt-4 btn-outline-danger btn-sm" id="delete-parts"><i style="font-size = 15px !important" class="mdi mdi-delete-empty"></i></span>
					</div>
					<div class="col-md-5">

						<div class="form-group">
							<label class="control-label">Part Repaired <span class="text-danger">*</span></label>
							<input type="text" class="form-control" name="part[]" placeholder="Part Repaired..." required />
						</div>
					</div>
					<div class="col-md-5">
						<div class="form-group">
							<label class="control-label">Comments <span class="text-danger">*</span></label>
							<textarea class="form-control" rows="1" name="comment[]" placeholder="Comments..." required /></textarea>
						</div>
					</div>
				</section>
				`;
			return text;
		}

		var new_part = function(data) {

			var new_ = $(`
					<div class="row">
						<div class="col-md-2  text-center">
							<span  class="btn mt-4 btn-outline-danger btn-sm" id="part-delete"><i style="font-size = 15px !important" class="mdi mdi-delete-empty"></i></span>
						</div>
						<div class="col-md-5">

							<div class="form-group">
								<label class="control-label">Part Repaired <span class="text-danger">*</span></label>
								<input type="text" class="form-control" name="part[]" value="${data.name}" placeholder="Part Repaired..." required />
								<input type="hidden" value="${data.id}" name="partID">
							</div>
						</div>
						<div class="col-md-5">
							<div class="form-group">
								<label class="control-label">Comments <span class="text-danger">*</span></label>
								<textarea class="form-control" rows="1" name="comment[]" placeholder="Comments..." required />${data.comment}</textarea>
							</div>
						</div>
					</div>
				`).clone();
			return new_;


		}

		$('#create-verification-log').on('show.bs.modal', function(e) {
			$('#create-verification-log').find('#overseen-by').select2()
			$(this).find('#service_provider').select2()
			$(this).find('#service_provider').on('change',(e)=>{
				let supplier_id = $(e.target).val();
				console.log(supplier_id)
				$('#create-verification-log').find('#contact_person').empty()
				getSupplierContacts(supplier_id,(data)=>{
					$.each(data,(i,e)=>{
						let option_body = `<option value="${e.id}">${e.name}</option>`
						
						$('#create-verification-log').find('#contact_person').append(option_body)
					})
					$('#create-verification-log').find('#contact_person').select2()
				})
			})
			$(this).find('#is-house').on('change', function() {
				console.log('test')
				if ($('#create-verification-log').find('input[type="radio"]:checked').val() == "in_house") {
					$('#create-verification-log').find('#supplier').addClass('hidden');
					$('#create-verification-log').find('#employee').removeClass('hidden');
					$('#create-verification-log').find('#supplier-contact').addClass('hidden')
				}

			})
			$(this).find('#externals').on('change', function() {
				console.log('test3')

				if ($('#create-verification-log').find('input[type="radio"]:checked').val() == "external") {
					$('#create-verification-log').find('#supplier').removeClass('hidden');
					$('#create-verification-log').find('#employee').addClass('hidden');
					$('#create-verification-log').find('#supplier-contact').removeClass('hidden')
				}
			})
		});
		$('#create-new-repairment').on('show.bs.modal', function(e) {
			$(this).find('#is-house').on('change', function() {
				console.log('test')
				if ($('#create-new-repairment').find('input[type="radio"]:checked').val() == "in_house") {
					$('#create-new-repairment').find('#supplier').addClass('hidden');
					$('#create-new-repairment').find('#employee').removeClass('hidden');
				}

			})
			$(this).find('#externals').on('change', function() {
				console.log('test3')

				if ($('#create-new-repairment').find('input[type="radio"]:checked').val() == "external") {
					$('#create-new-repairment').find('#supplier').removeClass('hidden');
					$('#create-new-repairment').find('#employee').addClass('hidden');
				}
			})
		});
		$('#create-new-maintainance').on('show.bs.modal', function(e) {
			var type = $(e.relatedTarget).data('type');

			$('#select-maintainance-type').val(type);
			$('.select-maintainance-type').text(type);
			$('#create-new-maintainance').find('#overseen-by').select2()
			$(this).find('input[name="maintainance_type"]').on('change',(e)=>{
				if($(e.target).val() == 'in_house'){
					$('#create-new-maintainance').find('#supplier-contact').addClass('hidden')
				}else{
					$('#create-new-maintainance').find('#supplier-contact').removeClass('hidden')
				}
			})
			$(this).find('#service_provider').on('change',(e)=>{
				let supplier_id = $(e.target).val();
				console.log(supplier_id)
				$('#create-new-maintainance').find('#contact_person').empty()
				getSupplierContacts(supplier_id,(data)=>{
					$.each(data,(i,e)=>{
						let option_body = `<option value="${e.id}">${e.name}</option>`
						
						$('#create-new-maintainance').find('#contact_person').append(option_body)
					})
					$('#create-new-maintainance').find('#contact_person').select2()
				})
			})
		});
		$('#create-new-repairment').on('show.bs.modal', function(e) {
			var type = $(e.relatedTarget).data('type');

			$('.select-maintainance-type').text(type);
		});
		$('#edit-attachment').on('show.bs.modal', function(e) {
			var a_data = $(e.relatedTarget).data('attachment');
			console.log(a_data)
			var edit_text = edit_attach(a_data);
			var header_ = `<h5 class="modal-title"><i class="mdi mdi-pencil text-primary"></i> Edit Attachment ${a_data.title}</h5>`
			$('#edit-attachment').find('.modal-body').empty();
			$('#edit-attachment').find('.modal-header').empty();
			$('#edit-attachment').find('.modal-header').append(header_);
			$('#edit-attachment').find('.modal-body').append(edit_text);
		});
		$('#attachment-description').on('show.bs.modal', function(e) {
			var desc = $(e.relatedTarget).data('description');
			var text = `
				<h5>Attachment description.</h5>
				<hr>
				<div class="panel panel-default">
					<div class="panel-body">
						<p>${desc}</p>
					</div>
				</div>
			`;
			$('#attachment-description').find('.modal-body').empty();
			$('#attachment-description').find('.modal-body').append(text);
		})
		var edit_attach = function(data) {
			var a_form = $(`
				<div class="form-group">
					<label class="control-label">Title <span class="text-danger">*</span></label>
					<input type="text" name="title" required placeholder="Title..." value="${data.title}" class="form-control">
					<input type="hidden" name="equipment_id" value="${data.equipment_id}">
					<input type="hidden" name="attach_id" value="${data.id}">
				</div>
				<div class="form-group">
					<label class="control-label">Attachment <span class="text-danger">*</span></label>
					<input type="file" value="${data.attachment}" name="attachment" class="form-control" >
				</div>
				<div class="form-group">
					<label class="control-label">Description</label>
					<textarea class="form-control" rows="6" name="description" placeholder="Notes...">${data.description}</textarea>
				</div>
			`).clone();
			return a_form
		}

		$('#edit-calibration').on('show.bs.modal', function(e) {
			var data = $(e.relatedTarget).data('calibration');
			var employees = $(e.relatedTarget).data('employees');
			var suppliers = $(e.relatedTarget).data('suppliers');
			console.log(data);
			$('#edit-calibration').find('.modal-header').empty();
			var header_ = `<h4 class="modal-title"><i class="mdi mdi-pencil"></i> Edit Calibration Log ${data.reference_number}</h4>`
			$('#edit-calibration').find('.modal-header').append(header_)
			var edit_form = edit_main(data);
			$.each(employees, function(i, e) {
				var options = `<option value="${e.id}" ${e.id == data.employee_id ? 'selected' : ''} >${e.name}</option>`;
				$(edit_form).find('[name="employee"]').append(options);
			});
			$.each(suppliers, function(i, e) {
				var options = `<option value="${e.id}" ${e.id == data.supplier_id ? 'selected' : ''} >${e.name}</option>`;
				$(edit_form).find('[name="supplier"]').append(options);
			});
			if (data.maintainance_type === 'in-house') {
				$(edit_form).find('#is-house').prop('checked', true);

			} else {
				$(edit_form).find('#externals').prop('checked', true);
			}
			$(edit_form).find('[name="maintainance_type"]').on('change', function() {
				if ($(edit_form).find('input[type="radio"]:checked').val() == "in_house") {
					$(edit_form).find('#supplier').addClass('hidden');
					$(edit_form).find('#employee').removeClass('hidden');
				}
				if ($(edit_form).find('input[type="radio"]:checked').val() == "external") {
					$(edit_form).find('#supplier').removeClass('hidden');
					$(edit_form).find('#employee').addClass('hidden');
				}

			});
			$('#edit-calibration').find('#edit-calibration-body').empty();
			$('#edit-calibration').find('#edit-calibration-body').append(edit_form);

		})

		var edit_repair = function(data) {
			var text = $(`
			<div class="row">
					<div class="col-md-6 col-sm-6 col-lg-6">
						<div class="form-group hidden">
							<label class="control-label">Type</label>
							<input name="type" value="Repairement">
						</div>
						<input type="hidden" name="item_id" value=${data.id}>
						<div class="form-group">
							<label class="control-label">Date <span class="text-danger">*</span></label>
							<input type="date" class="form-control" name="date" value="" placeholder="Date..." required />
						</div>
						<div class="form-group">
							<label class="control-label">Description <span class="text-danger">*</span></label>
							<textarea class="form-control" rows="6" name="description" placeholder="Description..." required>${data.description}</textarea>
						</div>

					</div>
					<div class="col-md-6 col-sm-6 col-lg-6">
						<div class="form-check">
							<input class="form-check-input" type="radio" id="is-house" class="form-control" name="maintainance_type" value="in_house" />
							<label class="form-check-label" for="is-house">
								In House Maintanance Type
							</label>
						</div>
						<br>
						<div class="form-check">
							<input class="form-check-input" type="radio" id="externals" class="form-control" name="maintainance_type" value="external" />
							<label class="form-check-label" for="externals">
								External Maintainance Type
							</label>
						</div>
						<br>
						<div class="form-group hidden" id="employee-">
							<label class="control-label">Employee</label>
							<select name="employee" class="form-control" placeholder="Employee...">
								
							</select>
						</div>
						<div class="form-group hidden" id="supplier-">
							<label class="control-label">Supplier</label>
							<select name="supplier" class="form-control" placeholder="Supplier...">
								
							</select>
						</div>
						<div class="form-group">
							<label class="control-label">Certificate</label>
							<input type="file" class="form-control" name="certificate" value="" placeholder="Certificate..." />
						</div>
						<div class="form-group">
							<label class="control-label">Remarks <span class="text-danger">*</span></label>
							<textarea class="form-control" rows="4" name="notes" placeholder="Notes..." required>${data.notes}</textarea>
						</div>
					</div>
				</div>
				<hr>
			`).clone();
			return text
		}

		$('#edit-maintainance').on('show.bs.modal', function(e) {
			var data = $(e.relatedTarget).data('cal');
			var employees = $(e.relatedTarget).data('employees');
			var suppliers = $(e.relatedTarget).data('suppliers');
			console.log(data);
			$('#edit-maintainance').find('.modal-header').empty();
			var header_ = `<h4 class="modal-title"><i class="mdi mdi-pencil"></i> Edit Maintainance Log ${data.reference_number}</h4>`
			$('#edit-maintainance').find('.modal-header').append(header_)
			var edit_form = edit_main(data);
			$.each(employees, function(i, e) {
				var options = `<option value="${e.id}" ${e.id == data.employee_id ? 'selected' : ''} >${e.name}</option>`;
				$(edit_form).find('[name="employee"]').append(options);
			});
			$.each(suppliers, function(i, e) {
				var options = `<option value="${e.id}" ${e.id == data.supplier_id ? 'selected' : ''} >${e.name}</option>`;
				$(edit_form).find('[name="supplier"]').append(options);
			});
			if (data.maintainance_type === 'in-house') {
				$(edit_form).find('#is-house').prop('checked', true);
				$(edit_form).find('#supplier-').addClass('hidden');
				$(edit_form).find('#employee-').removeClass('hidden');
			} else {
				$(edit_form).find('#externals').prop('checked', true);
				$(edit_form).find('#supplier-').removeClass('hidden');
				$(edit_form).find('#employee-').addClass('hidden');
			}
			$(edit_form).find('[name="maintainance_type"]').on('change', function() {
				if ($(edit_form).find('input[type="radio"]:checked').val() == "in_house") {
					$(edit_form).find('#supplier-').addClass('hidden');
					$(edit_form).find('#employee-').removeClass('hidden');
				}
				if ($(edit_form).find('input[type="radio"]:checked').val() == "external") {
					$(edit_form).find('#supplier-').removeClass('hidden');
					$(edit_form).find('#employee-').addClass('hidden');
				}

			});
			$('#edit-maintainance').find('#edit-maintainance-body').empty();
			$('#edit-maintainance').find('#edit-maintainance-body').append(edit_form);

		})
		var edit_main = function(data) {
			var text = $(`
			
				
				<div class="form-group hidden">
					<label class="control-label">Type</label>
					<input class="form-control" type="text" name="type" value="${data.type}" placeholder="Type...">
					<input class="form-control" type="hidden" name="item_id" value="${data.id}">

				</div>

				<div class="form-group">
					<label class="control-label">Date <span class="text-danger">*</span></label>
					<input type="date" class="form-control" name="date" value="${data.date}" placeholder="Date..." required />
				</div>
				<div class="form-group">
					<label class="control-label">Description <span class="text-danger">*</span></label>
					<textarea class="form-control" rows="6" name="description" placeholder="Description..." required>${data.description == null ? '' : data.description}</textarea>
				</div>
				<div class="form-group" id="reference-number">
					<label class="control-label">Reference Number <span class="text-danger">*</span></label>
					<input type="text" class="form-control" name="reference" placeholder="Reference Number..." value="${data.reference_number}" required />
				</div>
				
				
				<div class="form-check">
					<input class="form-check-input" type="radio" id="is-house" class="form-control" name="maintainance_type" value="in_house" />
					<label class="form-check-label" for="is-house">
						In House Maintanance Type
					</label>
				</div>
				<br>
				<div class="form-check">
					<input class="form-check-input" type="radio" id="externals" class="form-control" name="maintainance_type" value="external"  />
					<label class="form-check-label" for="externals">
						External Maintainance Type
					</label>
				</div>
				<br>
				<div id="type_section">
					<div class="form-group" id="employee-">
						<label class="control-label">Employee</label>
						<select name="employee" class="form-control" placeholder="Employee...">
							
						</select>
					</div>
					<div class="form-group" id="supplier-">
						<label class="control-label">Supplier</label>
						<select name="supplier" class="form-control" placeholder="Supplier...">
							
						</select>
					</div>
				</div>
				
				<div class="form-group">
					<label class="control-label">Certificate</label>
					<input type="file" class="form-control" name="certificate" value="${data.certificate}" placeholder="Certificate..." />
				</div>
				<div class="form-group">
					<label class="control-label">Remark <span class="text-danger">*</span></label>
					<textarea class="form-control" rows="6" name="notes" placeholder="Notes..." required>${data.notes}</textarea>
				</div>
			
			`).clone()
			return text
		}
		var counter = 1
		$('#add-new-part').click(function(event) {
			event.preventDefault();

			$('.yoo').hide();
			var newPart = $('<div class="all">' +


				'<div class="form-group float-left" style="width:42%;"><label class="control-label">Part Repaired</label>' +
				'<input type="text" class="form-control" name = "part[]" value="" placeholder="Part Repaired" required /></div>' +
				'<div class="form-group float-right" style="width:42%" ><label class="control-label">Comments</label>' +
				'<textarea class="form-control" rows="1" name="comment[]" placeholder ="Comments..." required /></textarea></div>' +
				'<a  style="margin-right:15px;margin-left:0px" class="btn btn-danger btn-sm close" aria-label="Close" id="delete" onclick="isdelete()><span aria-hidden="true">&times;</span></a>' +
				'</div>');
			$('#add-yoo').append(newPart);
			$('#no_parts').val(counter)
			counter++
		})
		$('#is-house').click(function(event) {
			if (('#is-house').prop("checked") == true) {
				$('#employee').css({
					'display': 'block'
				});
			} else {
				$('#employee').css({
					'display': 'none'
				});
			}

		})
		$('#create-new-repairment').on('click', '#delete', function(e) {
			$(this).parent('div').remove();

		})

	});

	function external() {
		var checkbox = document.getElementById("externals");
		var text = document.getElementById("supplier");
		if (checkbox.checked == true) {
			text.style.display = "block";

			inhouse();
		} else {
			text.style.display = "none";
		}
	}

	function externaled() {
		var checkbox = document.getElementById("external");
		var text = document.getElementById("suppliers");
		if (checkbox.checked == true) {
			text.style.display = "block";
			

			inhoused();
		} else {
			text.style.display = "none";
		}
	}

	function inhouse() {
		var checkbox = document.getElementById("is-house");
		var text = document.getElementById("employee");
		if (checkbox.checked == true) {
			text.style.display = "block";

			external();
		} else {
			text.style.display = "none";
		}

	}

	function inhoused() {
		var checkbox = document.getElementById("is-housed");
		var text = document.getElementById("employees");
		if (checkbox.checked == true) {
			text.style.display = "block";

			externaled();
		} else {
			text.style.display = "none";
		}

	}

	function isdelete() {
		var part = document.getElementById('no_parts').value;
		part--;

		counter = part;

		document.getElementById('no_parts').value = part;
	}
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.lab.layout.app', ['dataTable'=>true, 'select2'=>true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/dan/Documents/projects/nuve/imara-lims-v2/resources/views/layouts/lab/lab-equipment/show.blade.php ENDPATH**/ ?>