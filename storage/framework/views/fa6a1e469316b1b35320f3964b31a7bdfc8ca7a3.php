
<?php $__env->startSection('title2'); ?>
  <title>Qualifications</title>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content2'); ?>
  <main>
    <?php
      $items = array(
        array(
          'link' => route('lab-home'),
          'name' => 'Lab',
          'icon' => null
        ),
        array(
          'link' => route('qualification-home'),
          'name' => 'Qualification',
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
      <i class="mdi mdi-file-certificate"></i>Certifications
      
      <button class="btn btn-outline-primary btn-sm float-right" data-toggle="modal" data-target="#add-qualification"><i class="mdi mdi-plus"></i> Add</button>
      
    </h2>
	<br>
	<!-- ---------- -->
	<div class="card tab-card">
		<div class="card-header tab-card-header">
			<ul class="nav nav-tabs card-header-tabs" id="qualifications-tab" role="tablist">
				<li class="nav-item">
					<a class="nav-link active" id="qualifications" data-toggle="tab" href="#all-qualification-tab" role="tab" aria-controls="qualifications" aria-selected="true"><i style="font-size: 15px;" class="mdi mdi-file-certificate"></i> Cerifications</a>
                </li>
                <li class="nav-item">
					<a class="nav-link " id="archivedqualifications" data-toggle="tab" href="#archived-qualification-tab" role="tab" aria-controls="archivedqualifications" aria-selected="true"><i style="font-size: 15px;color:red" class="mdi mdi-file-certificate"></i> Archive Certifications</a>
				</li>
				
			</ul>
		</div>
		<div class="tab-content" id="complaints-tabs-content">
			<div class="tab-pane fade show active p-3" id="all-qualification-tab" role="tabpanel" aria-labelledby="one-tab">
				<h5 class="card-title"><i class="mdi mdi-file-certificate-outline"></i>Certifications</h5>
				<div class="table-responsive bg-light p-4">
					<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
						<thead class="bg-light p-2">
						<tr>
							<th>No</th>
                            <th nowrap>Name</th>
                           
                            <th>Created</th>
                            <th>Status</th>
                            <th>Edit By</th>
                            <th nowrap>Description</th>
                            <th></th>

                            <!-- ---  -->
                            
						</tr>
						</thead>
						<tbody>
                            <?php $__currentLoopData = $qualifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if($item->module_code == 0 ): ?>
                            
                                <tr>
                                    <td valign="center"><?php echo e($loop->iteration); ?></td>
                                    
                                    <td><?php echo e($item->name); ?></td>
                                    
                                    <td><?php echo e($item->created_at); ?></td>           
                                    <td class="text-small"><?php echo $item->status == 0 ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>'; ?></td>
                                    <td><?php echo $item->edited_by == '' ? 'N/a':$item->edited_by; ?></td>
                                    <td class="text-center">
                                        <span class="btn btn-outline-dark btn-sm" data-toggle="modal" data-target="#qualification-description-<?php echo e($item->id); ?>"> <i class="mdi mdi-message-text"></i></span>
                                        <div id="qualification-description-<?php echo e($item->id); ?>" class="modal fade" role="dialog">
                                            <div class="modal-dialog">
                                                <!-- Modal content-->
                                                <div class="modal-content" >
                                                    
                                                    <div class="modal-header">
                                                        <h4 class="modal-title"><i class="mdi mdi-eye"></i>Certification <?php echo e($loop->iteration); ?> Description</h4>
                                                    </div>
                                                    <div class="modal-body">
                                                    <h5>Certification Description.</h5>
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
                                    
                                    
                                    <span class="btn btn-outline-info btn-sm" data-toggle="modal" data-target="#edit-qualification-<?php echo e($item->id); ?>" > <i class="mdi mdi-pencil"></i> </span>
                                    
                                    <div id="edit-qualification-<?php echo e($item->id); ?>" class="modal fade" role="dialog">
                                        <div class="modal-dialog">

                                        <form action="<?php echo e(route('edit-qualification',['id'=>$item->id])); ?>" method="post" class="modal-content" enctype="multipart/form-data">
                                            <?php echo csrf_field(); ?>
                                            <div class="modal-header">
                                                <h4 class="modal-title"><i class="mdi mdi-pencil"></i> Edit Certification <?php echo e($loop->iteration); ?></h4>
                                            </div>
                                            <div class="modal-body">
                                                <div class="form-group">
                                                    <label class="control-label">Name</label>
                                                    <input type="text" name="name" class="form-control" value="<?php echo e($item->name); ?>" placeholder="Qualification Name...">
                                                </div>
                                                <div class="form-group hidden">
                                                    <label class="control-label">Current Name</label>
                                                    <input type="text" name="current" class="form-control" value="<?php echo e($item->name); ?>">
                                                </div>

                                                <div class="form-group">
                                                    <label class="control-label">Status</label>
                                                    <select name="status" class="form-control" placeholder="Operator...">
                                                        <option value=0 <?php echo e($item->status == 0 ? 'selected' : ''); ?>>Active</option>
                                                        <option value=1 <?php echo e($item->status == 1 ? 'selected' : ''); ?>>Archived</option>
                                                        
                                                    </select>
                                                </div>      
                                                <div class="form-group" >
                                                    <label class="control-label">Description</label>
                                                    <textarea class="form-control" rows="4" name="description"value="" placeholder="Qualification Description..." required><?php echo e($item->description); ?></textarea>
                                                </div> 
                                            </div>
                                            <div class="modal-footer">
                                                <button type="submit" class="btn btn-primary"> <i class="mdi mdi-content-save"></i> Update</button>
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
			<!-- ---------  -->
			<div class="tab-pane fade show p-3" id="archived-qualification-tab" role="tabpanel" aria-labelledby="one-tab">
				<h5 class="card-title"><i class="mdi mdi-file-certificate-outline"></i>Archived Certifications</h5>
				<div class="table-responsive bg-light p-4">
					<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
						<thead class="bg-light p-2">
						<tr>
							<th>No</th>
                            <th nowrap>Name</th>
                            <th>Created</th>                 
                            <th>Status</th>
                            <th>Edited_by</th>
                            <th nowrap>Description</th>
                            <th></th>

                            <!-- ---  -->
                            
						</tr>
						</thead>
						<tbody>
                            <?php $__currentLoopData = $qualifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if($item->status == 1 && $item->module_code == 0): ?>
                                <tr>
                                    <td valign="center"><?php echo e($loop->iteration); ?></td>
                                    
                                    <td><?php echo e($item->name); ?></td>
                                    <td><?php echo e($item->created_at); ?></td> 
                                    <td class="text-small"><?php echo $item->status == 0 ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>'; ?></td>          
                                    <td><?php echo e($item->edited_by); ?></td>
                                    <td class="text-center">
                                        <span class="btn btn-outline-dark btn-sm" data-toggle="modal" data-target="#description-qualification-<?php echo e($item->id); ?>"> <i class="mdi mdi-message-text"></i></span>
                                        <div id="description-qualification-<?php echo e($item->id); ?>" class="modal fade" role="dialog">
                                            <div class="modal-dialog">
                                                <!-- Modal content-->
                                                <div class="modal-content" >
                                                    
                                                    <div class="modal-header">
                                                        <h4 class="modal-title"><i class="mdi mdi-eye"></i>Qualification <?php echo e($loop->iteration); ?> Description</h4>
                                                    </div>
                                                    <div class="modal-body">
                                                    <h5>Certification Description.</h5>
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
                                    
                                    
                                    <span class="btn btn-outline-info btn-sm" data-toggle="modal" data-target="#edit-qualifications-<?php echo e($item->id); ?>" > <i class="mdi mdi-pencil"></i> </span>
                                    
                                    <div id="edit-qualifications-<?php echo e($item->id); ?>" class="modal fade" role="dialog">
                                        <div class="modal-dialog">

                                        <form action="<?php echo e(route('edit-qualification',['id'=>$item->id])); ?>" method="post" class="modal-content" enctype="multipart/form-data">
                                            <?php echo csrf_field(); ?>
                                            <div class="modal-header">
                                                <h4 class="modal-title"><i class="mdi mdi-pencil"></i> Edit Certification <?php echo e($loop->iteration); ?></h4>
                                            </div>
                                            <div class="modal-body">
                                                <div class="form-group">
                                                    <label class="control-label">Name</label>
                                                    <input type="text" name="name" class="form-control" value="<?php echo e($item->name); ?>" placeholder="Qualification Name...">
                                                </div>
                                                <div class="form-group hidden">
                                                    <label class="control-label">Name</label>
                                                    <input type="text" name="current" class="form-control" value="<?php echo e($item->name); ?>" >                                                
                                                </div>
                                                <div class="form-group">
                                                    <label class="control-label">Status</label>
                                                    <select name="status" class="form-control" placeholder="Operator...">
                                                        <option value = 0 <?php echo e($item->status == 0 ? 'selected' : ''); ?>>Active</option>
                                                        <option value = 1 <?php echo e($item->status == 1 ? 'selected' : ''); ?>>Archived</option>
                                                        
                                                    </select>
                                                </div>
                                              
                                                <div class="form-group" >
                                                    <label class="control-label">Description</label>
                                                    <textarea class="form-control" rows="4" name="description"value="" placeholder="Qualification Description..." required><?php echo e($item->description); ?></textarea>
                                                </div> 
                                            </div>
                                            <div class="modal-footer">
                                                <button type="submit" class="btn btn-primary"> <i class="mdi mdi-content-save"></i> Update</button>
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

  <div id="add-qualification" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <!-- Modal content-->
      <form class="modal-content" method="POST" action="<?php echo e(route('add-qualification')); ?>" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>
        <div class="modal-header">
          <h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Certification</h4>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label class="control-label">Name</label>
                <input type="text" name="name" class="form-control" placeholder="Qualification Name...">
            </div>
            
            <div class="form-group" >
                <label class="control-label">Description</label>
                <textarea class="form-control" rows="4" name="description"value="" placeholder="Qualification Description..." required></textarea>
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
<?php echo $__env->make('layouts.lab.layout.app', ['dataTable'=>true, 'select2'=>true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\wamp64\www\polucon\resources\views/layouts/lab/qualifications/index.blade.php ENDPATH**/ ?>