

<?php $__env->startSection('title2'); ?>
  <title>Inventory Categories</title>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content2'); ?>
  <main>
    <?php
      $items = array(
        array(
          'link' => route('inventory-home'),
          'name' => 'Inventory Management',
          'icon' => null
        ),
        array(
          'link' => route('inventory-categories'),
          'name' => 'Categories',
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
    <h3 class="p-4">
      <i class="mdi mdi-format-list-bulleted-type"></i>Categories
      <button class="btn btn-primary btn-sm float-right" data-toggle="modal" data-target="#add-inventory-category"><i class="mdi mdi-plus"></i> Add</button>
		</h3>
		<div class="bg-light p-4">
			<div class="table-responsive">
				<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
					<thead class="bg-light p-2">
						<tr>
							<th>No</th>
							<th>Image</th>
							<th>Name</th>
							<th>Description</th>
							<th>Available</th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						<?php if(count($categories) > 0): ?>
							<?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
								<tr>
									<td valign="center"><?php echo e($loop->iteration); ?></td>
									<td><img src="<?php echo e($category->image); ?>" style="width: 125px" /></td>
									<td><?php echo e($category->name); ?></td>
									<td><?php echo e($category->description); ?></td>
									<td>
										<?php if(intval($category->minimum_level) > intval($category->available()['available'])): ?>
											<span class="pl-2 pr-2 pt-1" title="Requires Restocking">
												<i class="mdi mdi-alert text-danger"></i>
											</span>
										<?php endif; ?>
										<?php echo e(number_format($category->available()['available'])." ".$category->unit_type); ?> <small class="text-muted">(+<?php echo e(number_format($category->available()['pending'])." ".$category->unit_type); ?> pending)</small>
									</td>
									<td nowrap>
										<button class="btn btn-primary btn-sm" data-target="#edit-inventory-category-<?php echo e($loop->iteration); ?>" data-toggle="modal"><i class="mdi mdi-pencil-outline"></i> <small class="hidden-sm-up">Edit</small> </button>
										
										<a class="btn btn-success btn-sm" href="<?php echo e(route('show-inventory-category', ['id'=>$category->id])); ?>"><i class="mdi mdi-eye-outline"></i> <small class="hidden-sm-up">Show</small> </a>
										<div id="edit-inventory-category-<?php echo e($loop->iteration); ?>" class="modal fade" role="dialog">
											<div class="modal-dialog">
												<!-- Modal content-->
												<form class="modal-content" method="POST" action="<?php echo e(route('edit-inventory-category', ['id'=>$category->id])); ?>" enctype="multipart/form-data">
													<?php echo csrf_field(); ?>
													<div class="modal-header">
														<h4 class="modal-title"><i class="mdi mdi-pencil-outline"></i> Edit Inventory Category</h4>
													</div>
													<div class="modal-body">
														<div class="form-group">
															<label class="control-label">Name</label>
															<input type="text" class="form-control" name="name" value="<?php echo e($category->name); ?>" placeholder="Name..." required />
														</div>
														<div class="form-group">
															<label class="control-label">Description</label>
															<textarea class="form-control" name="description" placeholder="Description..." required><?php echo e($category->description); ?></textarea>
														</div>
														<div class="form-group">
															<label class="control-label">Image</label>
															<input type="file" class="form-control" name="image"  />
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
						<?php endif; ?>
					</tbody>
				</table>
				<?php if(count($categories) == 0): ?>
					<div class="alert alert-info">
						<i class="mdi mdi-alert"></i> No Inventory Categories added yet.
					</div>
				<?php endif; ?>
			</div>
		</div>
  </main>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script2'); ?>
  <div id="add-inventory-category" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <!-- Modal content-->
      <form class="modal-content" method="POST" action="<?php echo e(route('add-inventory-category')); ?>" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>
        <div class="modal-header">
          <h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Inventory Category</h4>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label class="control-label">Name</label>
            <input type="text" class="form-control" name="name" value="" placeholder="Name..." required />
          </div>
          <div class="form-group">
            <label class="control-label">Description</label>
            <textarea class="form-control" name="description" placeholder="Description..." required></textarea>
          </div>
          <div class="form-group">
            <label class="control-label">Image</label>
            <input type="file" class="form-control" name="image" required />
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

<?php echo $__env->make('layouts.inventory.layout.app', ['dataTable'=>true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\wamp64\www\polucon\resources\views/layouts/inventory/categories/index.blade.php ENDPATH**/ ?>