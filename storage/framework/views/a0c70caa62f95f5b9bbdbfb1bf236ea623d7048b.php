

<?php $__env->startSection('title2'); ?>
  <title>Organizational Departments</title>
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
          'link' => route('show-organizational-departments'),
          'name' => 'Organizational Departments',
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
      <i class="mdi mdi-format-list-bulleted-type"></i>Departments
      <button class="btn btn-primary btn-sm float-right" data-toggle="modal" data-target="#add-inventory-department"><i class="mdi mdi-plus"></i> Add</button>
    </h2>
    <br>
    <div class="table-responsive bg-light p-4">
      <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
        <thead class="bg-light p-2">
          <tr>
            <th>No</th>
            <th>Name</th>
            <th>Active</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
					<?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $department): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
						<tr>
							<td valign="center"><?php echo e($loop->iteration); ?></td>
							<td><?php echo e($department->name); ?></td>
							<td class="text-small"><?php echo $department->active == '1' ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>'; ?></td>
							<td nowrap>
								<button class="btn btn-primary btn-sm" data-target="#edit-organizational-department-<?php echo e($loop->iteration); ?>" data-toggle="modal"><i class="mdi mdi-pencil-outline"></i> <small class="hidden-sm-up">Edit</small> </button>
								
								<div id="edit-organizational-department-<?php echo e($loop->iteration); ?>" class="modal fade" role="dialog">
									<div class="modal-dialog">
										<!-- Modal content-->
										<form class="modal-content" method="POST" action="<?php echo e(route('edit-inventory-department', ['id'=>$department->id])); ?>" enctype="multipart/form-data">
											<?php echo csrf_field(); ?>
											<div class="modal-header">
												<h4 class="modal-title"><i class="mdi mdi-pencil-outline"></i> Edit Department</h4>
											</div>
											<div class="modal-body">
												<div class="form-group">
													<label class="control-label">Name</label>
													<input type="text" class="form-control" name="name" value="<?php echo e($department->name); ?>" placeholder="Name..." required />
												</div>
												<div class="form-group">
													<label class="control-label"><input type="checkbox" name="active" value="1" <?php echo e($department->active == 1 ? 'checked' : ''); ?> /> Active</label>
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
  </main>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script2'); ?>
  <div id="add-inventory-department" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <!-- Modal content-->
      <form class="modal-content" method="POST" action="<?php echo e(route('add-inventory-department', ['module'=>'organizational'])); ?>" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>
        <div class="modal-header">
          <h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Department</h4>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label class="control-label">Name</label>
            <input type="text" class="form-control" name="name" value="" placeholder="Name..." required />
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

<?php echo $__env->make('layouts.personnel.layout.app', ['dataTable'=>true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\wamp64\www\polucon\resources\views/layouts/personnel/departments/index.blade.php ENDPATH**/ ?>