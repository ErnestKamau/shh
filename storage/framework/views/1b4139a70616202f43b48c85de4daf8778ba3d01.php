<?php $__env->startSection('title2'); ?>
<title>Equipment</title>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content2'); ?>
<main>
	<?php
	$items = array(
		array(
			'link' => route('equipment-home'),
			'name' => 'Equipment Management',
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
		<i class="mdi mdi-tools"></i> Equipment
		<button class="btn btn-primary btn-sm float-right" data-toggle="modal" data-target="#add-equipment"><i class="mdi mdi-plus"></i> Add</button>
	</h2>
	<br>
	<!-- ---------- -->
	<div class="card tab-card">
		<div class="card-header tab-card-header">
			<ul class="nav nav-tabs card-header-tabs" id="equipment-tab" role="tablist">
				<li class="nav-item">
					<a class="nav-link active" id="equipments" data-toggle="tab" href="#equipments-tab" role="tab" aria-controls="equipments" aria-selected="true">Equipments</a>
				</li>
				<li class="nav-item">
					<a class="nav-link " id="disposed-equipment-tab" data-toggle="tab" href="#disposed-equipment" role="tab" aria-controls="disposed-equipments" aria-selected="true">Disposed Equipments</a>
				</li>
			</ul>
		</div>
		<div class="tab-content" id="equipments-tabs-content">
			<div class="tab-pane fade show active p-3" id="equipments-tab" role="tabpanel" aria-labelledby="one-tab">
				<h5 class="card-title">Equipments</h5>
				<div class="table-responsive bg-light p-4">
					<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
						<thead class="bg-light p-2">
							<tr>
								<th style="min-width: 70px !important;"></th>
								<th>Photo</th>
								<th>Name</th>
								<th nowrap>Equipment Number</th>
								<th>Make</th>
								<th>Model</th>
								<!-- ---  -->
								<th nowrap>Serial Number</th>

								<th>Manufacturer</th>


								<th>Assigned Department</th>
								<th>Assigned Employee</th>

								<!-- ------  -->
								<th nowrap>Purchased On</th>
								<th nowrap>Calibration Date</th>
								<th nowrap>Maintainance Date</th>
								<th>Status</th>
								
							</tr>
						</thead>
						<tbody>
							<?php $__currentLoopData = $equipment; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
							<?php if($item->is_disposal == 0): ?>
							<tr>
							<td>
							<a class="btn btn-outline-success btn-sm" data-toggle="tooltip" title="View Equipment" href="<?php echo e(route('view-equipment', ['id'=>$item->id])); ?>">
										<i class="mdi mdi-eye-outline"></i></a>
									<span class="btn btn-outline-danger btn-sm" data-toggle="modal" data-target="#dispose-equipment" data-toggle="tooltip" title="Dispose Equipment"> <i class="mdi mdi-delete"></i></span>
									<div id="dispose-equipment" class="modal fade" role="dialog">
										<div class="modal-dialog">
											<!-- Modal content-->
											<form class="modal-content" method="POST" action="<?php echo e(route('dispose-equipment',['id'=>$item->id])); ?>" enctype="multipart/form-data">
												<?php echo csrf_field(); ?>
												<div class="modal-header">
													<h4 class="modal-title"><i class="mdi mdi-pencil"></i> Dispose Equipment <?php echo e($item->name); ?></h4>
												</div>
												<div class="modal-body">
													<div class="form-group">
														<label class="control-label">Disposing Employee</label>
														<select name="employee" id="dispose-employee" placeholder="Dispose Employee..." class="form-control" readonly="true">
															<?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
															<option value="<?php echo e($employee->id); ?>"><?php echo e($employee->name); ?></option>
															<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
														</select>
													</div>

													<div class="form-group">
														<label class="control-label">Date</label>
														<input type="date" class="form-control" name="date" max=<?php echo date('Y-m-d'); ?> value="" placeholder="Disposal Date..." required />
													</div>

													<div class="form-group">
														<label class="control-label">Reason Of Disposal</label>
														<textarea class="form-control" rows="6" name="comment" placeholder="Reason..." required></textarea>
													</div>
												</div>
												<div class="modal-footer">
													<button type="submit" class="btn btn-outline-primary btn-sm"><i class="mdi mdi-content-save"></i> Save</button>
													<button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
												</div>
											</form>
										</div>
									</div>
								</td>
								<td><img src="<?php echo e($item->picture); ?>" style="width: 125px" /></td>
								<td nowrap><a href="<?php echo e(route('view-equipment', ['id'=>$item->id])); ?>"><?php echo e($item->name); ?></a> </td>
								<td><?php echo e($item->equipment_number); ?></td>
								<td><?php echo e($item->make); ?></td>
								<td><?php echo e($item->model); ?></td>
								<!-- -----  -->
								<td><?php echo e($item->serial_number); ?></td>

								<td><?php echo e($item->manufacturer); ?></td>

								<td><?php echo e(getInventoryDepartmentByid($item->assigned_department)->name ?? '-'); ?></td>
								<td>
									<?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
									<?php if($employee->id == $item->assigned_employee_id): ?>
									<?php echo e($employee->name); ?>

									<?php endif; ?>
									<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
								</td>

								<!-- -----------  -->
								<td><?php echo e($item->date_purchased); ?></td>
								<td>
									<?php echo e($item->calibration_date()['date']->toDateString()); ?>

									<small class="ml-2 badge <?php echo e($item->calibration_date()['status']); ?>"><i class="mdi mdi-plus"></i><?php echo e(number_format(intval($item->calibration_date()['remaining_days']))); ?> days</small>
									<br>
									<?php if($item->calibration_date()['status'] == 'text-warning'): ?>
									<small class="p-3">
										<i class="mdi mdi-alert-decagram text-warning"></i> Schedule Equipment Calibration
									</small>
									<?php endif; ?>
									<?php if($item->calibration_date()['status'] == 'text-danger'): ?>
									<small class="p-3">
										<i class="mdi mdi-alert text-danger"></i> Equipment Calibration Required
									</small>
									<?php endif; ?>
								</td>
								<td>
									<?php echo e($item->maintainance_date()['date']->toDateString()); ?>

									<small class="ml-2 badge <?php echo e($item->maintainance_date()['status']); ?>"><i class="mdi mdi-plus"></i><?php echo e(number_format(intval($item->maintainance_date()['remaining_days']))); ?> days</small>
									<br>
									<?php if($item->maintainance_date()['status'] == 'text-warning'): ?>
									<small class="p-3">
										<i class="mdi mdi-alert-decagram text-warning"></i> Schedule Equipment Maintainance
									</small>
									<?php endif; ?>
									<?php if($item->maintainance_date()['status'] == 'text-danger'): ?>
									<small class="p-3">
										<i class="mdi mdi-alert text-danger"></i> Equipment Maintainance Required
									</small>
									<?php endif; ?>
								</td>
								<td class="text-small"><?php echo $item->active == '1' ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>'; ?></td>
								
								
							</tr>
							<?php endif; ?>
							<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
						</tbody>
					</table>
				</div>
			</div>
			<!-- ---------  -->
			<div class="tab-pane fade show  p-3" id="disposed-equipment" role="tabpanel" aria-labelledby="one-tab">
				<h5 class="card-title">Disposed Equipments</h5>
				<div class="table-responsive bg-light p-4">
					<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
						<thead class="bg-light p-2">
							<tr>
								<th style="min-width: 70px !important;"></th>
								<th>Photo</th>
								<th>Name</th>
								<th nowrap>Equipment Number</th>
								<th nowrap>Serial Number</th>
								<th>Make</th>
								<th>Model</th>
								<!-- ---  -->
								<th>Manufacturer</th>
								<th nowrap>Disposed Date</th>
								<th>Disposing Employee</th>
								<th nowrap>Warranty Date</th>
								<th></th>
								
								<!-- ------  -->


							</tr>
						</thead>
						<tbody>
							<?php $__currentLoopData = $equipment; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
							<?php if($item->is_disposal == 1): ?>
							<tr>
								<td>
								<a class="btn btn-outline-success btn-sm" href="<?php echo e(route('view-equipment', ['id'=>$item->id])); ?>" data-toggle="tooltip" title="View Equipment">
										<i class="mdi mdi-eye-outline"></i></a>
										<a class="btn btn-outline-primary btn-sm" href="<?php echo e(route('revert-equipment', ['id'=>$item->id])); ?>" data-toggle="tooltip" title="Edit">
										<i class="mdi mdi-pencil"></i></a>
								</td>
								<td><img src="<?php echo e($item->picture); ?>" style="width: 125px" /></td>
								<td nowrap><a href="<?php echo e(route('view-equipment', ['id'=>$item->id])); ?>"><?php echo e($item->name); ?></a> </td>
								<td><?php echo e($item->equipment_number); ?></td>
								<td><?php echo e($item->serial_number); ?></td>
								<td><?php echo e($item->make); ?></td>
								<td><?php echo e($item->model); ?></td>
								<!-- -----  -->
								<td><?php echo e($item->manufacturer); ?></td>
								<td><?php echo e($item->dispose_date); ?></td>
								<td>
									<?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
									<?php if($employee->id == $item->employee_dispose_id): ?>
									<?php echo e($employee->name); ?>

									<?php endif; ?>
									<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
								</td>
								<td><?php echo e($item->warranty_date); ?></td>
								<td>
									<span class="btn btn-info btn-sm" data-toggle="modal" data-target="#dispose-content">Reason</span>
									<div id="dispose-content" class="modal fade" role="dialog">
										<div class="modal-dialog">
											<!-- Modal content-->
											<div class="modal-content">
												<div class="modal-header">
													<h4 class="modal-title"><?php echo e($item->name); ?> Disposal Information.</h4>
												</div>
												<div class="modal-body">
													<h5>Reason For Disposal</h5>
													<div class="panel panel-default">
														<div class="panel-body ">
															<p><?php echo e($item->comment); ?></p>
														</div>
													</div>
												</div>
												<div class="modal-footer">
													<button type="button" class="btn btn-outline-danger btn-sm" data-dismiss="modal">Close</button>
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
			<!-- -----  -->
		</div>
	</div>
	<!-- -------------end----- -->

</main>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script2'); ?>
<div id="add-equipment" class="modal fade" role="dialog">
	<div class="modal-dialog modal-xl">
		<!-- Modal content-->
		<form class="modal-content" method="POST" action="<?php echo e(route('add-equipment')); ?>" enctype="multipart/form-data">
			<?php echo csrf_field(); ?>
			<div class="modal-header">
				<h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Equipment</h4>
			</div>
			
				<div class="row no-gutters">
					<div class="col-md-6 col-sm-6">
						<div class="modal-body">

							<div class="form-group">
								<label class="control-label">Name <span class="text-danger">*</span></label>
								<input type="text" class="form-control" name="name" value="" placeholder="Name..." required />
							</div>
							<div class="form-group">
								<label class="control-label">Number <span class="text-danger">*</span></label>
								<input type="text" class="form-control" name="equipment_number" value="" placeholder="Equipment Number..." required />
							</div>
							<div class="form-group">
								<label class="control-label">Photo <span class="text-danger">*</span></label>
								<input type="file" class="form-control" name="photo" required />
							</div>
							<div class="form-group">
								<label class="control-label">Description <span class="text-danger">*</span></label>
								<textarea class="form-control" name="description" placeholder="Equipment Description..." required></textarea>
							</div>
							<div class="form-group">
								<label class="control-label">Make <span class="text-danger">*</span></label>
								<input type="text" class="form-control" name="make" value="" placeholder="Equipment Make..." required />
							</div>
							<div class="form-group">
								<label class="control-label">Model <span class="text-danger">*</span></label>
								<input type="text" class="form-control" name="model" value="" placeholder="Equipment Model..." required />
							</div>
							<!-- ------------------ -->
							<div class="form-group">
								<label class="control-label">Serial Number</label>
								<input type="text" class="form-control" name="serial" placeholder="Serial Number..."/>
							</div>
							<div class="form-group">
								<label class="control-label">Barcode Number</label>
								<input type="text" class="form-control" name="barcode" placeholder="Barcode Number..."/>
							</div>
							<div class="form-group">
								<label class="control-label">Manufacturer </label>
								<input type="text" class="form-control" name="manufacturer" placeholder="Manufacturer..."  />
							</div>
						</div>
					</div>


					<div class="col-md-6 col-sm-6">
						<div class="modal-body">
							<?php
								$asset_types = getAssetTypes();
								$locations = getAssetLocation();
							?>
							<div class="form-group">
								<label class="control-label">Asset Type </label>
								<select name="asset_type_id" aria-readonly="true" aria-placeholder="Choose Asset Type" class="form-control">
									<?php $__currentLoopData = $asset_types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
									<?php if($type->is_active == 1): ?>
									<option value="<?php echo e($type->id); ?>"><?php echo e($type->asset_code); ?> (<?php echo e($type->descripton); ?>)</option>
									<?php endif; ?>
									<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
								</select>
							</div>
							<div class="form-group">
								<label class="control-label">Asset Location </label>
								<select name="location_id" aria-readonly="true" id="" class="form-control">
									<?php $__currentLoopData = $locations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $location): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
									<?php if($location->is_active == 1): ?>
									<option value="<?php echo e($location->id); ?>"><?php echo e($location->name); ?></option>
									<?php endif; ?>
									<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
								</select>
							</div>
							<div class="form-group">
								<label class="control-label">Status </label>
								<select name="status" id="assign-status" class="form-control" readonly="true" placeholder="Assign Status...">
									<?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
									<option value="<?php echo e($status); ?>"><?php echo e($status); ?></option>
									<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
								</select>
							</div>
							<div class="form-group">
								<label class="control-label">Equipment Condition <span class="text-danger">*</span></label>
								<input type="text" class="form-control" name="condition" placeholder="Equipment Condition..." required />
							</div>
							<div class="form-group">
								<label class="control-label">Assign department <span class="text-danger">*</span></label>
								<select name="department" id="" class="form-control" placeholder="Assign Department..." required>
									<option value="">Choose Department...</option>
									<?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $department): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
									<option value="<?php echo e($department->id); ?>"><?php echo e($department->name); ?></option>
									<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
								</select>
								
							</div>
							<div class="form-group">
								<label class="control-label">Assign Employee </label>
								<select name="employee" id="assign-employee" class="form-control" readonly="true" placeholder="Assign Employee">
									<?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
									<option value="<?php echo e($employee->id); ?>"><?php echo e($employee->name); ?></option>
									<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
								</select>
							</div>
							<div class="form-group">
								<label class="control-label">Warranty Date <span class="text-danger">*</span></label>
								<input type="date" class="form-control" name="warranty" placeholder="Warranty Date..." required />
							</div>
							<!-- -------------------- -->
							<div class="form-group">
								<label class="control-label">Date Purchased <span class="text-danger">*</span></label>
								<input type="date" class="form-control" name="date_purchased" value="" placeholder="Date Purchased..." required />
							</div>
							<div class="form-group">
								<div class="row no-gutters">
									<div class="col-xs-7">
										<label class="control-label">Maintainance After<small>(In Days)</small> <span class="text-danger">*</span></label>
										<input type="number" min="0" class="form-control" name="maintainance_in_days" value="" placeholder="Maintanance In Days..." required />
									</div>
									<div class="col-xs-5 pl-2">
										<label class="control-label">Notification (In Days)</small> <span class="text-danger">*</span></label>
										<input type="number" min="0" class="form-control" name="maintainance_notification_in_days" value="" placeholder="Notification Days..." required />
									</div>
								</div>
							</div>
							<div class="form-group">
								<div class="row no-gutters">
									<div class="col-xs-7">
										<label class="control-label">Calibration After<small>(In Days)</small> <span class="text-danger">*</span></label>
										<input type="number" min="0" class="form-control" name="calibration_in_days" value="" placeholder="Calibration In Days..." required />
									</div>
									<div class="col-xs-5 pl-2">
										<label class="control-label">Notification (In Days)</small> <span class="text-danger">*</span></label>
										<input type="number" min="0" class="form-control" name="calibration_notification_in_days" value="" placeholder="Notification Days..." required />
									</div>
								</div>
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
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.equipment.asset.layout.app', ['dataTable'=>true, 'select2'=>true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/dan/Documents/projects/nuve/imara-lims-v2/resources/views/layouts/equipment/index.blade.php ENDPATH**/ ?>