
<?php $module_text = implode(" ", explode("-", $module)); ?>
<?php $__env->startSection('title2'); ?>
  <title><?php echo e($config); ?> | <?php echo e($module_text); ?></title>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content2'); ?>
  <main>
    <?php
      $items = array(
        array(
          'link' => route('personnel-home'),
          'name' => $module_text,
          'icon' => null
        ),
        array(
          'link' => null,
          'name' => 'Configurations',
          'icon' => null
        ),
        array(
          'link' => null,
          'name' => $config,
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
      <i class="mdi mdi-format-list-bulleted-type"></i><?php echo e($config); ?>

      <button class="btn btn-primary btn-sm float-right" data-toggle="modal" data-target="#add-config"><i class="mdi mdi-plus"></i> Add</button>
    </h2>
    <br>
    <div class="table-responsive bg-light p-4">
      <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
        <thead class="bg-light p-2">
          <tr>
            <th>No</th>
            <th>Name</th>
            <th>Description</th>
            <?php if($config  == 'Job Description'): ?>
            <th></th>
            <?php endif; ?>
            <th></th>
          </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $config_items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <tr>
                <td valign="center"><?php echo e($loop->iteration); ?></td>
                <td><?php echo e($item->name ?? ''); ?></td>
                <td><?php echo e($item->description ?? ''); ?></td>
                <?php if($config == 'Job Description'): ?>
                <td class="text-center"><a  href="<?php echo e(route('showResponsibility',['id'=>$item->id])); ?>" class="btn mdi mdi-eye btn-outline-success"></a></td>
                <?php endif; ?>
                <td>
									<span class="btn btn-sm btn-default text-info" data-target="#edit-config" data-toggle="modal" data-config = "<?php echo e(json_encode($item)); ?>">
										<i class="mdi mdi-pencil"></i>
									</span>
									<?php if($item->type == "Material Type"): ?>
										<a href="<?php echo e(route('show-material-type', ['id'=>$item->id])); ?>" class="btn btn-sm btn-default text-success">
											<i class="mdi mdi-eye"></i>
										</a>
									<?php endif; ?>
								</td>
              </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
      </table>
    </div>
  </main>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script2'); ?>
  <div id="edit-config" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
      <!-- Modal content-->
      <form class="modal-content" method="POST" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>
        <div class="modal-header">
          <h4 class="modal-title"><i class="mdi mdi-plus"></i> Add <?php echo e($config); ?></h4>
        </div>
        <div class="modal-body" id="edit-config-fields"></div>
        <div class="modal-footer">
					<input type="hidden" name="module" value="" />
          <button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
	</div>
  <div id="add-config" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
      <!-- Modal content-->
      <form class="modal-content" method="POST" action="<?php echo e(route('add-module-pre-configs', ['id'=>time(), 'config'=>$config, 'module'=>$module])); ?>" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>
        <div class="modal-header">
          <h4 class="modal-title"><i class="mdi mdi-plus"></i> Add <?php echo e($config); ?></h4>
        </div>
        <div class="modal-body">
					<div class="form-group">
						<label class="control-label">Name</label>
						<input type="text" class="form-control" name="name" value="" placeholder="Name..." required />
					</div>
					<div class="form-group">
						<label class="control-label">Description</label>
						<textarea type="text" class="form-control" name="description" placeholder="Description..."></textarea>
					</div>
        </div>
        <div class="modal-footer">
					<input type="hidden" name="module" value="<?php echo e($module); ?>" />
          <button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
	</div>
	<script>
		var returnFields = function($data){
			return $(`
				<div class="form-group">
					<label class="control-label">Name</label>
					<input type="text" class="form-control" name="name" value="${$data.name || ''}" placeholder="Name..." required />
				</div>
				<div class="form-group">
					<label class="control-label">Description</label>
					<textarea type="text" class="form-control" name="description" placeholder="Description...">${$data.description || ''}</textarea>
				</div>
			`).clone();
		}

		$(function(){
			$('#edit-config').on('show.bs.modal', function(e){
				var config = $(e.relatedTarget).data('config');

				var field = returnFields(config);

				$('#edit-config-fields').html(field);

				$(this).find('form').prop('action', '/add-module-pre-configs/'+config.id+'/<?php echo e($config); ?>/<?php echo e($module); ?>');
			})


			$('[name="has_credentials"]').on('change', function(){
				console.log(123);
				if($(this).is(':checked')){
					$("#passwords-holder").removeClass("hidden");
					$(".pass").attr('required', true);
				}
				else{
					$("#passwords-holder").addClass("hidden");
					$(".pass").removeAttr('required');
				}
			});

			$('[name="confirm_password"]').on('keyup', function(){
				var pass1 = $('[name="password"]').val();
				var pass2 = $(this).val();

				if(pass1 != pass2){
					$(this).siblings('.has-success').html('').addClass('text-success');
					$(this).siblings('.has-error').html(`<i class="mdi mdi-cancel"></i> Passwords did not match.`).addClass('text-danger');
				}
				else{
					$(this).siblings('.has-error').html('')
					$(this).siblings('.has-success').html('<i class="mdi mdi-check-circle"></i> Passwords Match!.')
				}
			});
		});
	</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make($module == "Inventory-Management" ? 'layouts.inventory.layout.app' : 'layouts.personnel.layout.app', ['dataTable'=>true, 'select2'=>true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\wamp64\www\polucon\resources\views/layouts/personnel/configs/index.blade.php ENDPATH**/ ?>