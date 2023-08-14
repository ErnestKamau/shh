
<?php $__env->startSection('title2'); ?>
  <title>Customer Feedback</title>
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
          'link' => route('feedback-home'),
          'name' => 'Customer Feedback',
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
      <i class="mdi mdi-file-account"></i>Customer Feedback
      
      <button class="btn btn-outline-primary btn-sm float-right" data-toggle="modal" data-target="#add-feedback"><i class="mdi mdi-plus"></i> Add</button>
      
    </h2>
	<br>
	<!-- ---------- -->
	<div class="card tab-card">
		<div class="card-header tab-card-header">
			<ul class="nav nav-tabs card-header-tabs" id="customer-feedback-tab" role="tablist">
				<li class="nav-item">
					<a class="nav-link active" id="feedbacks" data-toggle="tab" href="#all-feedback-tab" role="tab" aria-controls="feedbacks" aria-selected="true"><i style="font-size: 15px;" class="mdi mdi-file-account"></i> Feedbacks</a>
                </li>
                <li class="nav-item">
					<a class="nav-link " id="archivefeedbacks" data-toggle="tab" href="#archived-feedback-tab" role="tab" aria-controls="archivedfeedbacks" aria-selected="true"><i style="font-size: 15px;color:red" class="mdi mdi-file-account"></i> Archive Feedbacks</a>
				</li>
				
			</ul>
		</div>
		<div class="tab-content" id="complaints-tabs-content">
			<div class="tab-pane fade show active p-3" id="all-feedback-tab" role="tabpanel" aria-labelledby="one-tab">
				<h5 class="card-title"><i class="mdi mdi-file-account-outline"></i>Customer Feedbacks</h5>
				<div class="table-responsive bg-light p-4">
					<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
						<thead class="bg-light p-2">
						<tr>
                            <th>No</th>
                            <th>Code</th>
                            <th nowrap>Received From</th>
                            <th>User Type</th>
                            <th nowrap>Registered By</th>
                            <th>Date</th>
                            <th>Created At</th>
                            <th>Status</th>
                            <th>Feedback</th>
                            <th></th>

                            <!-- ---  -->
                            
						</tr>
						</thead>
						<tbody>
                            <?php $__currentLoopData = $feedbacks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            
                                <tr>
                                    <td valign="center"><?php echo e($loop->iteration); ?></td>
                                    <td><?php echo e($item->code); ?></td>
                                    <td><?php echo e($item->received_from); ?></td>
                                    <td><?php echo e($item->user_type); ?></td>           
                                    <td><?php echo e($item->registered_by); ?></td>
                                    <td><?php echo e($item->date); ?></td>
                                    <td><?php echo e($item->created_at); ?></td>
                                    <td class="text-small"><?php echo $item->status == 0 ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>'; ?></td>
                                    
                                    <!-- -----  -->
                                    
                                    <td class="text-center">
                                        <span class="btn btn-outline-dark btn-sm" data-toggle="modal" data-target="#feedback-description-<?php echo e($item->id); ?>"> <i class="mdi mdi-message-text"></i></span>
                                        <div id="feedback-description-<?php echo e($item->id); ?>" class="modal fade" role="dialog">
                                            <div class="modal-dialog">
                                                <!-- Modal content-->
                                                <div class="modal-content" >
                                                    
                                                    <div class="modal-header">
                                                        <h4 class="modal-title"><i class="mdi mdi-eye"></i>Fedback <?php echo e($loop->iteration); ?> Description</h4>
                                                    </div>
                                                    <div class="modal-body">
                                                    <h5>Feedback Description.</h5>
                                                    <div class="pane panel-default">
                                                        <div class="panel-body">
                                                            <?php echo e($item->feedback); ?>

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
                                    
                                    
                                    <span class="btn btn-outline-info btn-sm" data-toggle="modal" data-target="#edit-feedback-<?php echo e($item->id); ?>" > <i class="mdi mdi-pencil"></i> </span>
                                    
                                    <div id="edit-feedback-<?php echo e($item->id); ?>" class="modal fade" role="dialog">
                                        <div class="modal-dialog">

                                        <form action="<?php echo e(route('edit-feedback',['id'=>$item->id])); ?>" method="post" class="modal-content" enctype="multipart/form-data">
                                            <?php echo csrf_field(); ?>
                                            <div class="modal-header">
                                                <h4 class="modal-title"><i class="mdi mdi-pencil"></i> Edit <?php echo e($item->id); ?> Complaint</h4>
                                            </div>
                                            <div class="modal-body">
                                                
                                                <div class="form-group">
                                                    <label class="control-label">Status</label>
                                                    <select name="status" class="form-control" placeholder="Operator...">
                                                        <option value=0 <?php echo e($item->status == 0 ? 'selected' : ''); ?>>Active</option>
                                                        <option value=1 <?php echo e($item->status == 1 ? 'selected' : ''); ?>>Archived</option>
                                                        
                                                    </select>
                                                </div>
                                               
                                                <div class="form-group">
                                                    <label class="control-label">Recieved From<small style="color: red;">(*customers)</small></label>
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
                                                    <label class="control-label">Feedback</label>
                                                    <textarea class="form-control" rows="4" name="feedback"value="" placeholder="Feedback Description..." required><?php echo e($item->feedback); ?></textarea>
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
			<div class="tab-pane fade show p-3" id="archived-feedback-tab" role="tabpanel" aria-labelledby="one-tab">
				<h5 class="card-title"><i class="mdi mdi-file-account-outline"></i>Archived Customer Feedbacks</h5>
				<div class="table-responsive bg-light p-4">
					<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
						<thead class="bg-light p-2">
						<tr>
							<th>No</th>
                            <th nowrap>Received From</th>
                            <th>User Type</th>
                            <th nowrap>Registered By</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Feedback</th>
                            <th></th>

                            <!-- ---  -->
                            
						</tr>
						</thead>
						<tbody>
                            <?php $__currentLoopData = $feedbacks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if($item->status == 1): ?>
                                <tr>
                                    <td valign="center"><?php echo e($loop->iteration); ?></td>
                                    
                                    <td><?php echo e($item->received_from); ?></td>
                                    <td><?php echo e($item->user_type); ?></td>           
                                    <td><?php echo e($item->registered_by); ?></td>
                                    <td><?php echo e($item->date); ?></td>
                                    
                                    <td class="text-small"><?php echo $item->status == 0 ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>'; ?></td>
                                    
                                    <!-- -----  -->
                                    
                                    <td class="text-center">
                                        <span class="btn btn-outline-dark btn-sm" data-toggle="modal" data-target="#feedback-description-<?php echo e($item->id); ?>"> <i class="mdi mdi-message-text"></i></span>
                                        <div id="feedback-description-<?php echo e($item->id); ?>" class="modal fade" role="dialog">
                                            <div class="modal-dialog">
                                                <!-- Modal content-->
                                                <div class="modal-content" >
                                                    
                                                    <div class="modal-header">
                                                        <h4 class="modal-title"><i class="mdi mdi-eye"></i>Feedback <?php echo e($loop->iteration); ?> Description</h4>
                                                    </div>
                                                    <div class="modal-body">
                                                    <h5>Feedback Description.</h5>
                                                    <div class="pane panel-default">
                                                        <div class="panel-body">
                                                            <?php echo e($item->feedback); ?>

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
                                    
                                    
                                    <span class="btn btn-outline-info btn-sm" data-toggle="modal" data-target="#edit-feedback-<?php echo e($item->id); ?>" > <i class="mdi mdi-pencil"></i> </span>
                                    
                                    <div id="edit-feedback-<?php echo e($item->id); ?>" class="modal fade" role="dialog">
                                        <div class="modal-dialog">

                                        <form action="<?php echo e(route('edit-feedback',['id'=>$item->id])); ?>" method="post" class="modal-content" enctype="multipart/form-data">
                                            <?php echo csrf_field(); ?>
                                            <div class="modal-header">
                                                <h4 class="modal-title"><i class="mdi mdi-pencil"></i> Edit <?php echo e($item->id); ?> Complaint</h4>
                                            </div>
                                            <div class="modal-body">
                                                
                                                <div class="form-group">
                                                    <label class="control-label">Status</label>
                                                    <select name="status" class="form-control" placeholder="Operator...">
                                                        <option value = 0 <?php echo e($item->status == 0 ? 'selected' : ''); ?>>Active</option>
                                                        <option value = 1 <?php echo e($item->status == 1 ? 'selected' : ''); ?>>Archived</option>
                                                        
                                                    </select>
                                                </div>
                                               
                                                <div class="form-group">
                                                    <label class="control-label">Recieved From<small style="color: red;">(*customers)</small></label>
                                                    <select name="received_from" class="form-control" placeholder="Recieved From...">
                                                        <?php $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($customer->name); ?>" <?php echo e($item->received_from == $customer->name ? 'selected' : ''); ?>><?php echo e($customer->name); ?></option>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label class="control-label">Date</label>
                                                    <?php
                                                        $date_arch = date("Y-m-d",strtotime($item->date));
                                                    ?>
                                                    <input type="date" name="date" placeholder="Complaint Date..."value="<?php echo e($date); ?>" class="form-control" required>
                                                </div>
                                                <div class="form-group" >
                                                    <label class="control-label">Feedback</label>
                                                    <textarea class="form-control" rows="4" name="feedback"value="" placeholder="Feedback Description..." required><?php echo e($item->feedback); ?></textarea>
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

  <div id="add-feedback" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <!-- Modal content-->
      <form class="modal-content" method="POST" action="<?php echo e(route('add-feedback')); ?>" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>
        <div class="modal-header">
          <h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Feedback</h4>
        </div>
        <div class="modal-body">
            
            <div class="form-group">
            <label class="control-label">Status</label>
            <select name="status" class="form-control" placeholder="Operator...">
                <option value=0 >Active</option>
                <option value= 1 >Archived</option>
                
            </select>
            </div>
            
            <div class="form-group">
                <label class="control-label">Recieved From <small style="color: red;">(customers)*</small></label>
                <select name="received_from" class="form-control" required placeholder="Recieved From...">
                    <?php $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($customer->name); ?>" ><?php echo e($customer->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="form-group">
                <label class="control-label">Date <span class="text-danger">*</span></label>
                <input type="date" name="date" placeholder="Complaint Date..."value="" class="form-control" required>
            </div>
            <div class="form-group" >
                <label class="control-label">Feedback <span class="text-danger">*</span></label>
                <textarea class="form-control" rows="4" name="feedback"value="" placeholder="Feedback Description..." required></textarea>
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
<?php echo $__env->make('layouts.crm.layout.app', ['dataTable'=>true, 'select2'=>true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\wamp64\www\polucon\resources\views/layouts/crm/complaints/customer_feedback.blade.php ENDPATH**/ ?>