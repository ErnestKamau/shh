

<?php $__env->startSection('title2'); ?>
  <title><?php echo e($stage); ?> | Inventory Management</title>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content2'); ?>
  <main>
		<?php
			$stages = getRequisitionWorkflow();
      $items = array(
        array(
          'link' => route('inventory-home'),
          'name' => 'Inventory Management',
          'icon' => null
        ),
        array(
          'link' => route('go_to_stage', ['stage'=>$stage]),
          'name' => $stage,
          'icon' => null
        )
			);

			$assistant_sup_roles = getConfigByName('assistant_supervisor_role_id');
			$assistant_sup_role_id = count($assistant_sup_roles) > 0 ? $assistant_sup_roles[0]->value : 0;

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
    <h3 class="p-4">
			<i class="mdi mdi-format-list-checks"></i> <?php echo e($stage); ?>

			<?php if(in_array($stage, array("Material Requisition", "Request to Store", "General Requisition")) && Auth::user()->hasRole($assistant_sup_role_id, true)): ?>
				<a class="btn btn-primary btn-sm float-right" href="<?php echo e(route('view-request-details', ['stage'=>$stage, 'id'=>time()])); ?>">
					<i class="mdi mdi-plus"></i> Create Request
				</a>
			<?php endif; ?>
		</h3>
		<div class="bg-light">
			<div class="card tab-card">
				<div class="card-header tab-card-header">
					<ul class="nav nav-tabs card-header-tabs" id="Requests-tabs" role="tablist">
						<li class="nav-item">
							<a class="nav-link active" id="Requests-tab" data-toggle="tab" href="#Requests" role="tab" aria-controls="Requests" aria-selected="true">Requests</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" id="Approvals-tab" data-toggle="tab" href="#Approvals" role="tab" aria-controls="approvals" aria-selected="true">Approval Configuration</a>
						</li>
					</ul>
				</div>
				<div class="tab-content" id="Requests-tabs-content">
					<div class="tab-pane fade show active p-3" id="Requests" role="tabpanel" aria-labelledby="one-tab">
						<h5 class="card-title mb-3">Requests</h5>
						<div class="table-responsive">
							<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
								<thead>
									<tr>
										<th>#</th>
										<th nowrap>Code</th>
										<th nowrap>Priority</th>
										<th nowrap>Status</th>
										<th nowrap>Description</th>
										<th nowrap>Due Date</th>
										<?php if(array_search($stage, $stages) > 0): ?>
											<th nowrap>Source</th>
										<?php endif; ?>
										<?php if($stage == "Material Issuance"): ?>
											<th nowrap>Source</th>
										<?php endif; ?>
										<?php if($stage == "Purchase Orders"): ?>
											<th nowrap>Supplier</th>
										<?php endif; ?>
										<th nowrap>Created By</th>
										<th nowrap>Purchasing Unit</th>
										<th nowrap>Department</th>
										<th nowrap>Created On</th>
										<th nowrap>Approvals</th>
										<th nowrap>Total Value</th>
									</tr>
								</thead>
								<tbody>
									<?php $__currentLoopData = $list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
										<tr>
											<td><?php echo e($loop->iteration); ?></td>
											<td nowrap>
												<a href="<?php echo e(route('view-request-details', ['stage'=>$stage, 'id'=>$l->id])); ?>">
													<?php echo e($l->request_code); ?>

												</a>
											</td>
											<td nowrap><?php echo e($l->priority); ?></td>
											<td nowrap><?php echo e($l->status); ?> <small><?php echo e($l->approval_status); ?></small></td>
											<td nowrap><?php echo e($l->description ?? 'No items set'); ?></td>
											<td nowrap><?php echo e($l->due_date); ?></td>
											<?php if((array_search($stage, $stages) > 0) || $stage == "Material Issuance"): ?>
												<td nowrap>
													<?php if(isset($l->parent_request) && $l->parent_request != ''): ?>
													<a href="<?php echo e(route('view-request-details', ['stage'=>$l->parent_request, 'id'=>$l->parent_request_id])); ?>">
														<?php echo e(rtrim($l->parent_request, 's')."-".$l->parent_request_id); ?>

													</a>
													<?php else: ?>
														-
													<?php endif; ?>
												</td>
											<?php endif; ?>
											<?php if($stage == "Purchase Orders"): ?>
												<td nowrap><?php echo e($l->supplier()->name ?? '-'); ?></td>
											<?php endif; ?>
											<td nowrap><?php echo e($l->creator()->name); ?></td>
											<td nowrap><?php echo e($l->creator()->location()->name); ?></td>
											<td nowrap><?php echo e($l->creator()->department()->name); ?></td>
											<td nowrap><?php echo e($l->created_at); ?></td>
											<td nowrap><?php echo e($l->done_approvals()->count()."/".getStageApprovals('Requisition', $stage)->count()); ?></td>
											<td nowrap><?php echo e($l->currency." ".number_format($l->net_value, 2)); ?></td>
										</tr>
									<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
								</tbody>
							</table>
						</div>
					</div>
					<div class="tab-pane fade p-3" id="Approvals" role="tabpanel" aria-labelledby="one-tab">
						<h5 class="card-title mb-3">Approvals Configuration
							<button class="btn btn-outline-primary btn-sm float-right"
								data-toggle="modal" data-target="#add-approval-modal"><i class="mdi mdi-key-plus"></i></button>
						</h5>
						<div class="table-responsive">
							<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
								<thead>
									<tr>
										<th>#</th>
										<th nowrap>Title</th>
										<th nowrap>Users</th>
										<th></th>
									</tr>
								</thead>
								<tbody>
									<?php $__currentLoopData = $approvals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $approval): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
										<tr>
											<td><?php echo e($loop->iteration); ?></td>
											<td><?php echo e($approval->title); ?></td>
											<td>
												<?php $users = array(); ?>
												<?php $__currentLoopData = $approval->user_roles(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
													<?php $users[] = '<span class="my-small-text">
															<i class="mdi mdi-account"></i> '.$user->name.' <small class="ext-mutedt"><'.$user->email.'></small>
														</span>';
													?>
												<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
												<?php echo implode(", ", $users); ?>

											</td>
											<td>
												<button class="btn btn-primary btn-sm" data-target="#edit-approval-<?php echo e($loop->iteration); ?>" data-toggle="modal"><i class="mdi mdi-pencil-outline"></i> <small class="hidden-sm-up">Edit</small> </button>
												<div id="edit-approval-<?php echo e($loop->iteration); ?>" class="modal fade" role="dialog">
													<div class="modal-dialog">
														<!-- Modal content-->
														<form class="modal-content" method="POST" action="<?php echo e(route('edit-approval-to-stage', ['id'=>$approval->id])); ?>" enctype="multipart/form-data">
															<?php echo csrf_field(); ?>
															<div class="modal-header">
																<h4 class="modal-title"><i class="mdi mdi-pencil-outline"></i> Edit Approval</h4>
															</div>
															<div class="modal-body">
																<div class="form-group">
																	<label class="control-label">Name</label>
																	<input type="text" class="form-control" name="name" value="<?php echo e($approval->title); ?>" placeholder="Name..." required />
																</div>
																<div class="form-group">
																	<label class="control-label">Select Role</label>
																	<select name="role_id" class="form-control" placeholder="Select Approval User..." required>
																		<option></option>
																		<?php $__currentLoopData = getRoles(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
																			<option value="<?php echo e($item->id); ?>" <?php echo e($item->id == $approval->role_id ? 'selected' : ''); ?>><?php echo e($item->name); ?></option>
																		<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
																	</select>
																</div>
																<div class="form-group">
																	<label class="control-label">Level</label>
																	<input type="number" min="1" class="form-control" name="level" value="<?php echo e($approval->level); ?>" placeholder="Name..." required />
																</div>
															</div>
															<div class="modal-footer">
																<button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
																<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
															</div>
														</form>
													</div>
												</div>
											</td>
										</tr>
									<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>

		</div>
  </main>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script2'); ?>
  <div id="add-approval-modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <!-- Modal content-->
      <form class="modal-content" method="POST" action="<?php echo e(route('add-approval-to-stage', ['stage'=>$stage])); ?>" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>
        <div class="modal-header">
          <h4 class="modal-title"><i class="mdi mdi-plus"></i> Add New Approval</h4>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label class="control-label">Approval Title</label>
						<input type="text" class="form-control" name="name" value="" placeholder="Name..." required />
						<input type="hidden" name="for" value="Requisition" />
          </div>
          <div class="form-group">
						<label class="control-label">Select Role</label>
						<select name="role_id" class="form-control" placeholder="Select Approval User..." required>
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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.inventory.layout.app', ['dataTable'=>true, 'select2'=>true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\wamp64\www\polucon\resources\views/layouts/inventory/requisition/index.blade.php ENDPATH**/ ?>