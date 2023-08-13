<?php $__env->startSection('title2'); ?>
<title><?php echo e($role->name); ?> - Roles | Personnel Management</title>
<style>
	.checkboxer {
		cursor: pointer;
	}

	.checkboxer.inner {
		font-size: 14px;
	}
</style>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content2'); ?>

<?php
$items = array(
	array(
		'link' => route('organizational-roles'),
		'name' => 'Personnel Management',
		'icon' => null
	),
	array(
		'link' => route('organizational-roles'),
		'name' => 'Roles',
		'icon' => null
	),
	array(
		'link' => '#',
		'name' => $role->name,
		'icon' => null
	)
);
?>
<main>
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
		<i class="mdi mdi-key"></i> <?php echo e($role->name); ?> | <small class="text-muted">Roles Permissions</small>
		<button class="btn btn-outline-info float-right" onclick="submitForm()"><i class="mdi mdi-content-save"></i> Save</button>

	</h2>
	<?php
	$moduleRules = getModulePermissions();

	$moduleNames = array_keys($moduleRules);
	?>
	<div class="card mt-3">
		<div class="card-body">
			<form class="row no-gutters" id="role-rights-form" method="POST" action="<?php echo e(route('save-role-rights', ['id'=>$role->id])); ?>">
				<?php echo csrf_field(); ?>
				<div class="col-sm-12 p-2">
					<div class=" tab-card">
						<div class=" card-header tab-card-header">
							<ul class="nav nav-tabs card-header-tabs" id="roles-tabs" role="tablist">
								<?php $__currentLoopData = $moduleNames; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
								<li class="nav-item">
									<a class="nav-link <?php echo e($loop->iteration == 1 ? 'active' : ''); ?>" id="<?php echo e($key); ?>-tab" data-toggle="tab" href="#<?php echo e($key); ?>" role="tab" aria-controls="<?php echo e($key); ?>" aria-selected="true"><?php echo e($key); ?></a>
								</li>
								<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

							</ul>
						</div>
						<div class="tab-content" id="roles-tabs-content">
							<?php $__currentLoopData = $moduleNames; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
							<div class="tab-pane fade <?php echo e($loop->iteration == 1 ? 'show active' : ''); ?> p-3" id="<?php echo e($key); ?>" role="tabpanel" aria-labelledby="one-tab">
								<h5 class="card-title">
									<span class="checkboxer">
										<i class="fas fa-toggle-off text-muted"></i>
										<input type="hidden" class="value-holder" name="permissions[<?php echo e($key); ?>][permission]" value="<?php echo e($permissions[$key]['permission'] ?? 'false'); ?>" />
									</span>
									<?php echo e($key); ?> Module
								</h5>
								<div class="table-responsive">
									<pre><?php
											$permCats = $moduleRules[$key];
											$components = array();
											foreach ($permCats as $k => $value) {
												if ($k == "components") {
													$components = $value;
												}
											}
											$actions = array("Add", "Edit", "View", "Delete");
											?></pre>
									<div class="row">
										<?php $__currentLoopData = $components; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
										<div class="border-left-0 col-sm-6 components-holder">
											<h6>
												
												<b><?php echo e($item); ?></b>
											</h6>
											<table class="table table-condensed table my-small-text">
												<tr>
													<?php $__currentLoopData = $actions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
													<td>
														<span class="checkboxer inner">
															<i class="fas fa-toggle-off text-muted"></i>
															<input type="hidden" class="value-holder" name="permissions[<?php echo e($key); ?>][components][<?php echo e($item); ?>][<?php echo e($a); ?>]" value="<?php echo e($permissions[$key]['components'][$item][$a] ?? 'false'); ?>" />
														</span>
														<?php echo e($a); ?>

													</td>
													<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
												</tr>
											</table>
										</div>
										<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
									</div>
								</div>
							</div>
							<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
						</div>
					</div>
				</div>
			</form>

		</div>

	</div>
</main>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
<script>
	var submitForm = function() {
		var btn = $(`<button type="submit">SAVE</button>`);
		$('#role-rights-form').append(btn);
		btn.trigger('click');
	}
	$(function() {
		$('.value-holder').each(function() {
			var cB = $(this).parent('.checkboxer');
			var val = $(this).val();

			if (val == "true") {
				cB.find('i').addClass('fa-toggle-on text-success').removeClass('fa-toggle-off text-muted');
				cB.addClass('checked');
			}
		});


		$('.checkboxer').on('click', function() {
			$(this).toggleClass('checked');
			if ($(this).hasClass('checked')) {
				$(this).find('i').addClass('fa-toggle-on text-success').removeClass('fa-toggle-off text-muted');
				var $val = true;
			} else {
				$(this).find('i').removeClass('fa-toggle-on text-success').addClass('fa-toggle-off text-muted');
				var $val = false;
			}

			$(this).find('input').val($val);

			if (!$(this).hasClass('inner')) {
				var toggler = false;
				if ($(this).hasClass('toggler')) {
					var parentPane = $(this).parents('.components-holder');
					toggler = true;
				} else {
					var parentPane = $(this).parents('.tab-pane');
				}

				var cCheckBox = parentPane.find('.checkboxer.inner, .checkboxer.toggler');

				if (!$val) {
					cCheckBox.removeClass('checked');
					if (!toggler) {
						cCheckBox.find('i').removeClass('text-success').addClass('text-muted');
					} else {
						cCheckBox.find('i').removeClass('text-success fa-toggle-on').addClass('text-muted fa-toggle-off');
					}
				} else {
					cCheckBox.addClass('checked');
					if (!toggler) {
						cCheckBox.find('i.fa-toggle-on').removeClass('text-muted').addClass('text-success');
					} else {
						cCheckBox.find('i').addClass('text-success fa-toggle-on').removeClass('text-muted fa-toggle-off');
					}
				}
			}
		});

		$('.checkboxer.inner, .checkboxer.toggler').on('click', function() {
			var isToggler = $(this).hasClass('toggler');
			var parentPane = $(this).parents('.tab-pane');
			var parentCheckBoxer = $(this).parents('.tab-pane').find('.checkboxer').not('.inner');
			if (parentPane.find('.inner.checkboxer').find('i.fa-toggle-on.text-success') == 0) {
				parentCheckBoxer.find('i').removeClass('text-success fa-toggle-on').addClass('text-muted fa-toggle-off');
				parentCheckBoxer.removeClass('checked');
				if (!isToggler) {
					$(this).parents('.components-holder').find('.toggler').find('i').removeClass('text-success fa-toggle-on').addClass('text-muted fa-toggle-off');
				}

				parentCheckBoxer.find('input').val(false)
			} else {
				parentCheckBoxer.find('i').addClass('text-success fa-toggle-on').removeClass('text-muted fa-toggle-off');
				parentCheckBoxer.addClass('checked');

				if (!isToggler) {
					$(this).parents('.components-holder').find('.toggler').find('i').addClass('text-success fa-toggle-on').removeClass('text-muted fa-toggle-off');
				}

				parentCheckBoxer.find('input').val(true)
			}
		});
	});
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('prp::layouts.app',['dataTable'=>true,'select2'=>true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/dan/Documents/projects/nuve/imara-lims-v2/resources/views/layouts/personnel/roles/show.blade.php ENDPATH**/ ?>