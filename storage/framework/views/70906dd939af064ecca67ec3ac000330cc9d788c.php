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
			<div class="tab-pane fade show active p-2" id="equipments-tab" role="tabpanel" aria-labelledby="one-tab">
				<h5 class="card-title p-2">Equipments


				</h5>
				<div class="table-responsive">
					<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
						<thead class="bg-light p-2">
							<tr>
								<th style="min-width: 70px !important;"></th>

								<th>Photo</th>
								<th>Name</th>
								<th nowrap>Equipment Number</th>
								<th>Requires Calibration</th>
								<th>Lab Location</th>
								<th>Current Location</th>
								<th nowrap>Calibration Date</th>

								<th>Status</th>
								<th>Equipment Condition</th>

								<th nowrap>Serial Number</th>
								<th>Supplier</th>

								<!-- ---  -->
								<th>Manufacturer</th>


								<th nowrap>Recieved / Installed Date</th>




							</tr>
						</thead>
						<tbody>
							<?php $__currentLoopData = $equipment; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
							<?php if($item->is_disposal == 0): ?>
							<tr>
								<td>
									<a class="btn btn-outline-success btn-sm" data-toggle="tooltip" title="View Equipment" href="<?php echo e(route('labShowEquipment', ['id'=>$item->id])); ?>">
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
								<td class="text-center"> <a target="_blank" href="<?php echo e($item->picture); ?>" class="btn btn-default btn-sm text-primary"><i data-toggle="tooltip" title="View Photo" class="mdi mdi-download"></i></a></td>
								<td nowrap><a href="<?php echo e(route('labShowEquipment', ['id'=>$item->id])); ?>"><?php echo e($item->name); ?></a> </td>
								<td><?php echo e($item->equipment_number); ?></td>
								<td class="text-center"><?php echo $item->requires_calibration == '1' ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>'; ?></td>
								<td><?php echo e(getAssetLocationByid($item->asset_location_id)->name ?? ''); ?></td>
								<td><?php echo e($item->current_location); ?></td>
								<td>
									<?php if($item->requires_calibration == '1'): ?>
										<?php
										$diff = getNextCalibrationDate($item->id);
										?>

										<small><b><?php echo e($diff['date']); ?></b></small> <br>
										<?php if( $diff['diff'] >= 0): ?>

											<?php if($diff['diff'] < $item->calibration_notification_in_days): ?>
												<small class="ml-2 badge badge-warning"><i class="mdi mdi-plus"></i><?php echo e($diff['diff']); ?> days</small> <br>
											<?php else: ?>
												<small class="ml-2 badge badge-success"><i class="mdi mdi-plus"></i><?php echo e($diff['diff']); ?> days</small> <br>
											<?php endif; ?>

										<?php else: ?>
											<small class="ml-2 badge badge-danger"><i class="mdi mdi-minus"></i><?php echo e($diff['diff']); ?> days</small><br>
											<small class="p-3"><i class="mdi mdi-alert-decagram text-danger"></i> Equipment Calibrations Required</small>
										<?php endif; ?>
									<?php else: ?>
										- 
									<?php endif; ?>
								</td>

								<td class="text-small text-center"><?php echo $item->active == '1' ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>'; ?></td>

								
								<td><?php echo e($item->condition); ?></td>


								<td><?php echo e($item->serial_number); ?></td>
								<td><?php echo e(getSupplierByID($item->supplier_id)->name ?? ''); ?></td>

								<!-- -----  -->

								<td><?php echo e($item->manufacturer); ?></td>



								<!-- -----------  -->
								<td><?php echo e($item->date_purchased); ?></td>



							</tr>
							<?php endif; ?>
							<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
						</tbody>
					</table>
					<small style="font-size: 12px;"><b>Key:</b></small>
					<ul class="key">
						<li class="p-2">
						<small style="font-size: 11px;" class="badge badge-warning p-1">+ days</small> - <span style="font-size: 12px;">Should Schedule for Equipment Calibration </span>
						</li>
						<li class="p-2"><small style="font-size: 11px;" class="badge badge-danger p-1">+ days</small> - <span style="font-size: 12px;">Equipment Calibration is Required</span></li>
						
					</ul>
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
								<th>Lab Location</th>
								<th>Current Location</th>
								
								<th nowrap>Serial Number</th>
								<th>Supplier</th>
								

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
									<a class="btn btn-outline-success btn-sm" data-toggle="tooltip" title="View Equipment" href="<?php echo e(route('labShowEquipment', ['id'=>$item->id])); ?>">
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
								<td class="text-center"> <a target="_blank" href="<?php echo e($item->picture); ?>" class="btn btn-default btn-sm text-primary"><i data-toggle="tooltip" title="View Photo" class="mdi mdi-download"></i></a></td>
								<td nowrap><a href="<?php echo e(route('labShowEquipment', ['id'=>$item->id])); ?>"><?php echo e($item->name); ?></a> </td>
								<td><?php echo e($item->equipment_number); ?></td>
								<td><?php echo e(getAssetLocationByid($item->asset_location_id)->name ?? ''); ?></td>
								<td><?php echo e($item->current_location); ?></td>
								
								
								<td class="text-small"><?php echo $item->active == '1' ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>'; ?></td>

								
								<td><?php echo e($item->condition); ?></td>


								<td><?php echo e($item->serial_number); ?></td>
								<td><?php echo e(getSupplierByID($item->supplier_id)->name ?? ''); ?></td>

								<!-- -----  -->

								<td><?php echo e($item->manufacturer); ?></td>



								<!-- -----------  -->
								<td><?php echo e($item->date_purchased); ?></td>

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
<div id="add-asset-type" style="z-index: 4000 !important;" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<form action="<?php echo e(route('add-asset-type')); ?>" method="POST" class="modal-content" enctype="multipart/form-data">
			<?php echo csrf_field(); ?>
			<div class="modal-header">
				<h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Asset Type</h4>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<label class="control-label">Asset Code</label>
					<input type="text" name="code" class="form-control" value="" placeholder="Asset Code..." required />
				</div>
				<div class="form-group">
					<label class="control-label">Asset Description</label>
					<input type="text" name="description" class="form-control" value="" placeholder="Asset Description..." required />
				</div>
				<div class="form-group">
					<label class="control-label">Active</label>
					<select class="form-control" name="active" placeholder="Active..." readonly="true">
						<option value="True">Active</option>
						<option value="False">Not Active</option>
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
<div id="add-asset-location" style="z-index: 4000 !important;" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<form action="<?php echo e(route('add-asset-location')); ?>" method="POST" class="modal-content" enctype="multipart/form-data">
			<?php echo csrf_field(); ?>
			<div class="modal-header">
				<h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Asset Location</h4>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<label class="control-label">Location Code</label>
					<input type="text" name="code" class="form-control" value="" placeholder="Location Code..." required />
				</div>
				<div class="form-group">
					<label class="control-label">Location</label>
					<input type="text" name="description" class="form-control" value="" placeholder="Location Name..." required />
				</div>
				<div class="form-group">
					<label class="control-label">Active</label>
					<select class="form-control" name="active" placeholder="Active..." readonly="true">
						<option value="True">Active</option>
						<option value="False">Not Active</option>
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
<div id="add-equipment" data-backdrop="static" data-keyboard="false" class="modal fade" role="dialog">
	<div class="modal-dialog modal-xl">
		<!-- Modal content-->
		<form class="modal-content" method="POST" action="<?php echo e(route('add-equipment')); ?>" enctype="multipart/form-data">
			<?php echo csrf_field(); ?>
			<div class="modal-header">
				<h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Equipment</h4>

				<span type="button" class="btn btn-default float-right" data-dismiss="modal">Close</span>


			</div>

			<div class="row no-gutters">
				<div class="col-md-6 col-sm-6">
					<div class="modal-body">

						<div class="form-group">
							<label class="control-label">Name <span class="text-danger">*</span></label>
							<input type="text" class="form-control" name="name" value="" placeholder="Name..." required />
						</div>
						<div class="form-group">
							<label class="control-label">Number </label>
							<input type="text" class="form-control" name="equipment_number" value="" placeholder="Equipment Number..." />
						</div>
						<div class="form-group">
							<label class="control-label">Photo <span class="text-danger">*</span></label>
							<input type="file" class="form-control" name="photo" required />
						</div>
						<div class="form-group">
							<label class="control-label">Description </label>
							<textarea class="form-control" rows="3" name="description" placeholder="Equipment Description..."></textarea>
						</div>
						<div class="form-group">
							<label class="control-label">Serial Number</label>
							<input type="text" class="form-control" name="serial" placeholder="Serial Number..." />
						</div>
						<div class="form-group">
							<label class="control-label">Manufacturer </label>
							<input type="text" class="form-control" name="manufacturer" placeholder="Manufacturer..." />
						</div>
						<div class="form-group">
							<label class="control-label">Make</label>
							<input type="text" class="form-control" name="make" value="" placeholder="Equipment Make..." />
						</div>
						<div class="form-group">
							<label class="control-label">Model </label>
							<input type="text" class="form-control" name="model" value="" placeholder="Equipment Model..." />
						</div>
						<!-- ------------------ -->

						<div class="form-group">
							<label class="control-label">Barcode Number</label>
							<input type="text" class="form-control" name="barcode" placeholder="Barcode Number..." />
						</div>
						<div class="form-group">
							<label for="" class="control-label"><input type="checkbox" name="requires_calibration" id=""> Requires Calibrations</label>
						</div>

					</div>
				</div>


				<div class="col-md-6 col-sm-6">
					<div class="modal-body">
						<div class="form-group">
							<label class="control-label">Current Location <span data-target="#add-asset-location" data-toggle="modal" data-toggle="tooltip" title="Add Lab Location" class="btn-primary"><i class="mdi mdi-plus"></i></span></label>
							<select name="location_id" aria-readonly="true" id="" class="form-control">
								<?php $__currentLoopData = $locations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $location): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
								<?php if($location->is_active == 1): ?>
								<option value="<?php echo e($location->id); ?>"><?php echo e($location->name); ?></option>
								<?php endif; ?>
								<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
							</select>
						</div>
						<div class="form-group">
							<label class="control-label">Location in Lab</label>
							<input type="text" name="current_loc" value="" class="form-control">
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
							<label class="control-label">Supplier</label>
							<select name="supplier_id" id="" class="form-control">
								<?php $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supplier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
								<option value="<?php echo e($supplier->id); ?>"><?php echo e($supplier->name); ?></option>
								<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
							</select>
						</div>
						<div class="form-group">
							<label class="control-label">Date Purchased / Received </label>
							<input type="date" class="form-control" name="date_purchased" value="" placeholder="Date Purchased..." />
						</div>

						<!-- -------------------- -->

						<div class="form-group">
							<div class="row no-gutters">
								<div class="col-xs-7">
									<label class="control-label">Maintainance After<small> (In Days)</small> </label>
									<input type="number" min="0" class="form-control" name="maintainance_in_days" value="" placeholder="Maintanance In Days..." />
								</div>
								<div class="col-xs-5 pl-2">
									<label class="control-label">Notification (In Days)</small></label>
									<input type="number" min="0" class="form-control" name="maintainance_notification_in_days" value="" placeholder="Notification Days..." />
								</div>
							</div>

						</div>
						<div class="form-group">
							<label class="control-label">Last Maintainance Date</label>
							<input type="date" name="maintainance_last_date" value="" class="form-control">
							<input type="hidden" name="is_lab" value="1">
						</div>
						<div class="form-group">
							<div class="row no-gutters">
								<div class="col-xs-7">
									<label class="control-label">Calibration / Servicing After<small> (In Days)</small> <span class="text-danger">*</span></label>
									<input type="number" min="0" class="form-control" name="calibration_in_days" value="" placeholder="Calibration In Days..." required />
								</div>
								<div class="col-xs-5 pl-2">
									<label class="control-label">Notification (In Days)</small> <span class="text-danger">*</span></label>
									<input type="number" min="0" class="form-control" name="calibration_notification_in_days" value="" placeholder="Notification Days..." required />
								</div>
							</div>
						</div>
						<div class="form-group">
							<label class="control-label">Last Calibration / Servicing Date</label>
							<input type="date" name="calibration_last_date" value="" class="form-control">
						</div>
					</div>


				</div>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-primary btn-sm float-right"><i class="mdi mdi-content-save"></i> Save</button>
			</div>


		</form>
	</div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.lab.layout.app', ['dataTable'=>true, 'select2'=>true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/dan/Documents/projects/nuve/imara-lims-v2/resources/views/layouts/lab/lab-equipment/index.blade.php ENDPATH**/ ?>