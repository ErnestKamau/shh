
<?php $__env->startSection('title2'); ?>
  <title><?php echo e($stage); ?></title>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content2'); ?>
  <main>
    <?php
      $items = array(
        array(
          'link' => route('customers-list'),
          'name' => 'CRM',
          'icon' => null
        ),
        array(
          'link' => route('complaint-workflow',['stage'=>$stage]),
          'name' => $stage,
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
      <i class="mdi mdi-comment-alert"></i><?php echo e($stage); ?>

      <?php if($stage == "Open Complaints"): ?>
      <button class="btn btn-outline-primary btn-sm float-right" data-toggle="modal" data-target="#add-complaint"><i class="mdi mdi-plus"></i> Add</button>
      <?php endif; ?>
    </h2>
	<br>
	<!-- ---------- -->
	<div class="card tab-card">
		<div class="card-header tab-card-header">
			<ul class="nav nav-tabs card-header-tabs" id="equipment-tab" role="tablist">
				<li class="nav-item">
					<a class="nav-link active" id="complaints" data-toggle="tab" href="#complaints-tab" role="tab" aria-controls="complaints" aria-selected="true"><i style="font-size: 15px;" class="mdi mdi-comment-alert"></i> <?php echo e($stage); ?></a>
				</li>
				
			</ul>
		</div>
		<div class="tab-content" id="complaints-tabs-content">
			<div class="tab-pane fade show active p-3" id="complaints-tab" role="tabpanel" aria-labelledby="one-tab">
				<h5 class="card-title"><i class="mdi mdi-comment-alert-outline"></i>Complaints</h5>
				<div class="table-responsive bg-light p-4">
					<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
						<thead class="bg-light p-2">
						<tr>
							<th>No</th>
							<th>Priority</th>
              <th nowrap>Complaint Number</th>
              <th>Complaint Type</th>
							<th>Received From</th>
              <th>Registered_by</th>
              <th>Date</th>
              <th>Created At</th>
              <?php if($stage == 'Cancelled Complaints'): ?>
              <th>Reject Stage</th>
              <?php endif; ?>
              <th>Description</th>
              <th></th>

							<!-- ---  -->
							
						</tr>
						</thead>
						<tbody>
									<?php $__currentLoopData = $complaints; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
									
										<tr>
											<td valign="center"><?php echo e($loop->iteration); ?></td>
											
                      <td><?php echo $item->priority == 'high' ? '<i style="color:red;" class="mdi mdi-star-four-points"></i><span style="color: red;">high</span>':$item->priority; ?></td>
                      <td><?php echo e($item->complaint_id); ?></td>
                      <td><?php echo e($item->type); ?></td>
											<td><?php echo e($item->received_from); ?></td>
                      <td><?php echo e($item->registered_by); ?></td>
                      <td><?php echo e($item->date); ?></td>
                      <td><?php echo e($item->created_at); ?></td>
                      <?php if($stage == 'Cancelled Complaints'): ?>
                      <?php 
                        $workflows = getComplaintWorkflow()[$item->reject_workflow];
                        
                      ?>
                      <td><?php echo e($workflows); ?></td>
                      <?php endif; ?>
											<!-- -----  -->
											
											<td class="text-center">
												<span class="btn btn-outline-dark btn-sm" data-toggle="modal" data-target="#complaint-description-<?php echo e($item->id); ?>"> <i class="mdi mdi-message-text"></i></span>
												<div id="complaint-description-<?php echo e($item->id); ?>" class="modal fade" role="dialog">
													<div class="modal-dialog">
														<!-- Modal content-->
														<div class="modal-content" >
															
															<div class="modal-header">
																<h4 class="modal-title"><i class="mdi mdi-eye"></i><?php echo e($item->complaint_id); ?> Complaint Description</h4>
															</div>
															<div class="modal-body">
                                <h5>Complaint Description.</h5>
                                <div class="pane panel-default">
                                    <div class="panel-body">
                                        <?php echo e($item->description); ?>

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
                      <td>
                      <a class="btn btn-outline-success btn-sm" href="<?php echo e(route('show-complaint', ['id'=> $item->id])); ?>">
                          <i class="mdi mdi-eye-outline"></i></a>
                      <?php if($item->complaint_workflow<5): ?>
                    
                      <span class="btn btn-outline-info btn-sm" data-toggle="modal" data-target="#edit-complaint-<?php echo e($item->id); ?>" > <i class="mdi mdi-pencil"></i> </span>
                      <?php endif; ?>
                      <div id="edit-complaint-<?php echo e($item->id); ?>" class="modal fade" role="dialog">
                        <div class="modal-dialog">

                          <form action="<?php echo e(route('edit-complaint',['id'=>$item->id])); ?>" method="post" class="modal-content" enctype="multipart/form-data">
                              <?php echo csrf_field(); ?>
                              <div class="modal-header">
                                  <h4 class="modal-title"><i class="mdi mdi-pencil"></i> Edit <?php echo e($item->complaint_id); ?> Complaint</h4>
                              </div>
                              <div class="modal-body">
                                  
                                  <div class="form-group">
                                      <label class="control-label">Priority</label>
                                      <select name="priority" class="form-control" placeholder="Operator...">
                                          <option value="high"<?php echo e($item->priority == 'high' ? 'selected' : ''); ?>>High</option>
                                          <option value="medium"<?php echo e($item->priority == 'medium' ? 'selected' : ''); ?>>Medium</option>
                                          <option value="low" <?php echo e($item->priority == 'low' ? 'selected' : ''); ?>>Low</option>
                                      </select>
                                  </div>
                                  <div class="form-group">
                                      <label class="control-label">Complaint Type</label>
                                      <select name="type" class="form-control" placeholder="Recieved From...">
                                          <?php $__currentLoopData = $complaint_types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                          <option value="<?php echo e($type->name); ?>" <?php echo e($item->type == $type->name ? 'selected' : ''); ?>><?php echo e($type->name); ?></option>
                                          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                      </select>
                                  </div>
                                  <div class="form-group">
                                      <label class="control-label">Recieved From</label>
                                      <select name="received_from" class="form-control" placeholder="Recieved From...">
                                          <?php $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                          <option value="<?php echo e($customer->name); ?>" <?php echo e($item->received_from == $customer->name ? 'selected' : ''); ?>><?php echo e($customer->name); ?></option>
                                          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                      </select>
                                  </div>
                                  <div class="form-group">
                                    <label class="control-label">Date</label>
                                    <?php
                                      $date = date("Y-m-d",strtotime($item->date));
                                    ?>
                                    <input type="date" name="date" placeholder="Complaint Date..."value="<?php echo e($date); ?>" class="form-control" required>
                                  </div>
                                  <div class="form-group" >
                                      <label class="control-label">Description</label>
                                      <textarea class="form-control" rows="4" name="description"value="" placeholder="Compliant Description..." required><?php echo e($item->description); ?></textarea>
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
										</tr>
									
									<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
								</tbody>
					</table>
				</div>
			</div>
			<!-- ---------  -->
			
			<!-- -----  -->
		</div>
	</div>
	<!-- -------------end----- -->
    
  </main>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script2'); ?>
<?php if($stage == "Open Complaints"): ?>
  <div id="add-complaint" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <!-- Modal content-->
      <form class="modal-content" method="POST" action="<?php echo e(route('add-complaint')); ?>" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>
        <div class="modal-header">
          <h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Complaint</h4>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label class="control-label">Priority</label>
                <select name="priority" id="assign-status" class="form-control" readonly="true" placeholder="Assign Priority...">
                    <option value="high">High</option>
                    <option value="medium">Medium</option>
                    <option value="low">Low</option>
                </select>
            </div>
            <div class="form-group">
                <label class="control-label">Complaint Type <span class="text-danger">*</span></label>
                <select name="type" class="form-control" required placeholder="Recieved From...">
                    <?php $__currentLoopData = $complaint_types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($type->name); ?>" ><?php echo e($type->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="form-group">
                <label class="control-label">Received from <span class="text-danger">*</span></label>
                <select name="received_from" id="assign-employee" required class="form-control" readonly="true" placeholder="Received from...">
                    <?php $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($customer->name); ?>"><?php echo e($customer->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="form-group">
              <label class="control-label">Date <span class="text-danger">*</span></label>
              <input type="date" name="date" value="" placeholder="Complaint Date..." class="form-control" required>
            </div>
            <div class="form-group" >
                <label class="control-label">Complaint description <span class="text-danger">*</span></label>
                <textarea class="form-control" rows="4" name="description"value="" placeholder="Compliant Description..." required></textarea>
            </div> 
           
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
  </div>
<?php endif; ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.crm.layout.app', ['dataTable'=>true, 'select2'=>true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\wamp64\www\polucon\resources\views/layouts/crm/complaints/open_complaint.blade.php ENDPATH**/ ?>