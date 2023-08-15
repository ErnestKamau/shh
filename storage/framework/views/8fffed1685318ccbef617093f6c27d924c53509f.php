<?php $__env->startSection('title2'); ?>
  <title>Roles | Personnel Management</title>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content2'); ?>
  <main>
    <?php
      $items = array(
        array(
          'link' => route('organizational-roles'),
          'name' => 'Personnel Management',
          'icon' => null
        ),
        array(
          'link' => null,
          'name' => 'Roles',
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
      <i class="mdi mdi-key-change"></i>Roles
      <button class="btn btn-primary btn-sm float-right" data-toggle="modal" data-target="#add-role"><i class="mdi mdi-plus"></i> Add</button>
    </h2>
    <br>
    <div class="table-responsive bg-light p-4">
      <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
        <thead class="bg-light p-2">
          <tr>
            <th>No</th>
            <th>Name</th>
            <th>Description</th>
            <th>Active</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <tr>
                <td valign="center"><?php echo e($loop->iteration); ?></td>
                <td><?php echo e($item->name); ?></td>
                <td><?php echo e($item->description); ?></td>
								<td class="text-small"><?php echo $item->active == '1' ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>'; ?></td>
                <td nowrap>
									<button class="btn btn-primary btn-sm" data-target="#edit-role-<?php echo e($item->id); ?>" data-toggle="modal" data-role='<?php echo e(json_encode($item)); ?>'><i class="mdi mdi-pencil-outline"></i> <small class="hidden-sm-up">Edit</small> </button>
                  <a class="btn btn-success btn-sm" href="<?php echo e(route('view-organizational-role', ['id'=>$item->id])); ?>"><i class="mdi mdi-eye-outline"></i> <small class="hidden-sm-up">Show</small> </a>

                  <div id="edit-role-<?php echo e($item->id); ?>" class="modal fade" role="dialog">
                    <div class="modal-dialog">
                      <!-- Modal content-->
                      <form class="modal-content" method="POST" enctype="multipart/form-data" action="<?php echo e(route('edit-organizational-role', ['id'=>$item->id])); ?>">
                        <?php echo csrf_field(); ?>
                        <div class="modal-header">
                          <h4 class="modal-title"><i class="mdi mdi-pencil"></i> Edit Role <?php echo e($item->name); ?></h4>
                        </div>
                        <div class="modal-body" id="edit-role-fields">
                          <div class="form-group">
                            <label class="control-label">Name</label>
                            <input type="text" class="form-control" name="name" value="<?php echo e($item->name); ?>" placeholder="Name..." required />
                          </div>
                          <div class="form-group">
                            <label class="control-label">Description</label>
                            <textarea class="form-control" name="description" placeholder="Description..." required><?php echo e($item->description); ?></textarea>
                          </div>
                          <div class="form-group">
                            <label class="control-label"><input type="checkbox" name="active" value="1" checked /> Active</label>
													</div>
													<div class="form-group">
														<label class="control-label">Role Level</label>
														<input type="number" class="form-control" name="level" value="<?php echo e($item->level); ?>" placeholder="Level..." required />
													</div>
                        </div>
                        <div class="modal-footer">
                          <button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
                          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        </div>
                        </div>
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
	<script>
		var getEditRoleForm = function($data){
			var formGrp = $(`
				<div class="form-group">
					<label class="control-label">Name</label>
					<input type="text" class="form-control" name="name" value="${ $role.name }" placeholder="Name..." required />
				</div>
				<div class="form-group">
					<label class="control-label">Description</label>
					<textarea class="form-control" name="description" placeholder="Description..." required>${ $role.description }</textarea>
				</div>
			`);

			return $formGrp.clone();
		}

		// $(function(){
		// 	$('#edit-role').on('show.bs.modal', function (e) {
		// 		var url = "/edit-organizational-role/"+$data.item;

		// 		$(this).find('form').prop('action', url);
		// 		var $role = $(e.relatedTarget).data('role');
		// 		$('#edit-role-fields').html(getEditRoleForm($role));
		// 	});
		// });
	</script>

  <div id="add-role" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <!-- Modal content-->
      <form class="modal-content" method="POST" action="<?php echo e(route('add-organizational-role')); ?>" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>
        <div class="modal-header">
          <h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Role</h4>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label class="control-label">Name</label>
            <input type="text" class="form-control" name="name" value="" placeholder="Name..." required />
          </div>
          <div class="form-group">
            <label class="control-label">Description</label>
            <textarea class="form-control" name="description" value="" placeholder="Description..." required></textarea>
					</div>
					<div class="form-group">
						<label class="control-label"><input type="checkbox" name="active" value="1" checked /> Active</label>
					</div>
					<div class="form-group">
            <label class="control-label">Role Level</label>
            <input type="number" class="form-control" name="level" value="1" placeholder="Level..." required />
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

<?php echo $__env->make('layouts.personnel.layout.app', ['dataTable'=>true, 'select2'=>true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/kimari/Projects/polucon/resources/views/layouts/personnel/roles/index.blade.php ENDPATH**/ ?>