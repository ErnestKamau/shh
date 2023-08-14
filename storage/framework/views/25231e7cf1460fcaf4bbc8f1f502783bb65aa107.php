

<?php $__env->startSection('title2'); ?>
<title><?php echo e($role->name); ?> - Roles | Personnel Management</title>
<style>
	.checkboxer{
		cursor: pointer;
	}
	.checkboxer.inner{
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
		<i class="mdi mdi-key"></i> <?php echo e($role->name); ?> | <small class="text-muted">Roles</small>
		
	</h2>
	<br>
	<?php
		$moduleRules = getModulePermissions();

		$moduleNames = array_keys($moduleRules);
	?>
	<div class="card tab-card">
		<div class="card-header tab-card-header">
			<ul class="nav nav-tabs card-header-tabs" id="roles" role="tablist">
				<li class="nav-item">
					<a href="#role-permissions" class="nav-link active" data-toggle="tab" role="tab" aria-controls="role-general" aria-selected="true" id="role-general"><i class="mdi mdi-key"></i>Roles Permissions</a>
				</li>
				<li class="nav-item">
					<a href="#role-certification" class="nav-link" id="role-certificate" data-toggle="tab" role="tab" aria-selected="true" aria-controls="role-certificate"><i class="mdi mdi-file-certificate-outline"></i>Role Certification</a>
				</li>
			</ul>
		</div>
		<div class="tab-content" id="role-information-tabs">
			<div class="tab-pane fade show active p-3" id="role-permissions" role="tabpanel" aria-labelledby="one-tab">
				<h5 class="card-title"> <i class="mdi mdi-key"></i>Roles Permissions
				<button class="btn btn-outline-info float-right" onclick="submitForm()"><i class="mdi mdi-content-save"></i> Save</button>
				</h5>

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
													if($k == "components"){
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
			<div class="tab-pane fade show p-3" id="role-certification" role="tabpanel" aria-labelledby="one-tab">
				<h5 class="card-title"><i class="mdi mdi-file-certificate-outline"></i> Certifications
				<button class="btn btn-outline-info float-right" data-toggle="modal" data-target="#add-certification"><i class="mdi mdi-plus"></i> Add</button>
				<div id="add-certification" class="modal fade" role="dialog">
					<div class="modal-dialog">
						<form action="<?php echo e(route('add-role-certification',['id'=>$role->id])); ?>" method="post" class="modal-content" enctype="multipart/form-data">
							<?php echo csrf_field(); ?> 
							<div class="modal-header">
								<h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Certification</h4>
							</div>		
							<div class="modal-body">
								<div class="form-group">
									<label class="control-label">Certficate Name</label>
									<select name="certificate" id="list_certificate" class="form-control">
										<?php $__currentLoopData = $certifications_list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
										<?php if($cert->status == 0): ?>
											<option value="<?php echo e($cert->id); ?>"><?php echo e($cert->name); ?></option>
										<?php endif; ?>
										<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
									</select>
								</div>
								<div class="form-group">
									<label class="control-label">
										<input type="checkbox" name="mandatory" value=1>
										Mandatory
									</label>
								</div>
							</div>	
							<div class="modal-footer">
							<button type="submit" class="btn btn-outline-primary"> <i class="mdi mdi-content-save"></i> Save</button>
							<button type="button" class="btn btn-outline-danger" data-dismiss="modal">Close</button>
							</div>				
						</form>
					</div>
				</div>

				</h5>
				<div class="table-responsive bg-light p-4">
					<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
						<thead class="bg-light p-2">
							<tr>
								<th>No</th>
								<th>Level</th>
								<th>Name</th>
								<th>Created</th>
								<th>Status</th>
								<th>Edited By</th>
								<th nowrap >Description</th>
								<th></th>
								
								

							</tr>
						</thead>
						<tbody>
							<?php $__currentLoopData = $certifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
							<?php if($item->status == 0): ?>
							<?php 
								$certification = getSampleTypeQualificationById($item->certification_id)
							?>
							<tr>
								<td><?php echo e($loop->iteration); ?></td>
								<td><?php echo $item->is_mandatory == 1 ? '<i style="color:red;" class="mdi mdi-star-four-points"></i><span style="color: red;">Mandatory</span>':'Optional'; ?></td>                 
								<td><?php echo e($certification->name); ?></td>
								<td><?php echo e($item->created_at); ?></td>
								<td class="text-small"><?php echo $item->status == 0 ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>'; ?></td>
								<td><?php echo $item->edited_by == '' ? 'N/a':$item->edited_by; ?></td>
								<td class="text-center">
									<span class="btn btn-outline-dark btn-sm" data-toggle="modal" data-target="#certificate-description-<?php echo e($item->id); ?>"><i class="mdi mdi-message-text"></i></span>
									<div id="certificate-description-<?php echo e($item->id); ?>" class="modal fade" role="dialog">
										<div class="modal-dialog">
											<div class="modal-content">
												<div class="modal-header">
													<h4 class="modal-title"><i class="mdi mdi-eye"></i> <?php echo e($certification->name); ?> Description</h4>
												</div>
												<div class="modal-body">
													<h5>Description</h5>
													<div class="pane panel-default">
														<div class="panel-body">
															<?php echo e($certification->description); ?>

														</div>
													</div>
												</div>
												<div class="modal-footer">
												<button type="button" class="btn btn-outline-danger" data-dismiss="modal">Close</button>
												</div>
											</div>
										</div>
									</div>
								</td>
								<td>
									<span class="btn btn-outline-info btn-sm" data-toggle="modal" data-target="#certificate-edit-<?php echo e($item->id); ?>"><i class="mdi mdi-pencil"></i></span>
									<div id="certificate-edit-<?php echo e($item->id); ?>" class="modal fade" role="dialog">
										<div class="modal-dialog">
											<form action="<?php echo e(route('edit-role-certification',['id'=>$item->id])); ?>" method="post" class="modal-content" enctype="multipart/form-data" >
												<?php echo csrf_field(); ?> 
												<div class="modal-header">
													<h4 class="modal-title"><i class="mdi mdi-pencil"></i> Edit <?php echo e($certification->name); ?> Role Certification</h4>
												</div>
												<div class="modal-body">

													<div class="form-group">
														<label class="control-label">Certificate Name</label>
														<select name="certification" class="form-control" aria-placeholder="Certification Name...">
															<?php $__currentLoopData = $certifications_list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $list): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
															<?php if($list->status == 0): ?>
															
																<option value="<?php echo e($list->id); ?>" <?php echo e($list->id == $item->certification_id ? 'selected':''); ?> ><?php echo e($list->name); ?></option>
															<?php endif; ?>
															<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
														</select>
													</div>
													<div class="form-group">
														<label class="control-label">
															<input type="checkbox" name="mandatory" value=1 <?php echo e($item->is_mandatory == 1 ? 'checked':''); ?> id="is_mandatory">
															Mandatory
														</label>
													</div>
												</div>
												<div class="modal-footer">
													<button type="submit" class="btn btn-primary"> <i class="mdi mdi-content-save"></i> Update</button>
													<button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
												</div>
											</form>
										</div>
									</div>
									<span class="btn btn-outline-danger btn-sm" data-target="#delete-certificate-<?php echo e($item->id); ?>" data-toggle="modal"><i class="mdi mdi-delete-empty"></i></span>
									<div id="delete-certificate-<?php echo e($item->id); ?>" class="modal fade" role="dialog">
										<div class="modal-dialog">
											<form action="<?php echo e(route('delete-role-certification',['id'=>$item->id])); ?>" method="post" class="modal-content">
												<?php echo csrf_field(); ?> 
												<div class="modal-header">
													<h4 class="modal-title"><i class="mdi mdi-delete-empty"></i> Delete <?php echo e($certification->name); ?> Role Certification.</h4>
												</div>
												<div class="modal-body">
													<div class="panel panel-default">
														<div class="panel-body">

															Are you sure you want to delete <b><?php echo e($certification->name); ?></b> Role Certification?
														</div>
													</div>
													<div class="form-group hidden">
														<label class="control-label">Certificate ID</label>
														<input type="number" name="cert_id" class="form-control" value="<?php echo e($item->id); ?>">
													</div>
												</div>
												<div class="modal-footer">
													<button type="submit" class="btn btn-outline-primary"> <i class="mdi mdi-content-save"></i> Yes</button>
													<button type="button" class="btn btn-outline-danger" data-dismiss="modal">Cancel</button>
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
		</div>
	</div>
</main>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
<script>
	var submitForm = function(){
		var btn = $(`<button type="submit">SAVE</button>`);
		$('#role-rights-form').append(btn);
		btn.trigger('click');
	}
	$(function(){
		$('.value-holder').each(function(){
			var cB = $(this).parent('.checkboxer');
			var val = $(this).val();

			if(val == "true"){
				cB.find('i').addClass('fa-toggle-on text-success').removeClass('fa-toggle-off text-muted');
				cB.addClass('checked');
			}
		});


		$('.checkboxer').on('click', function(){
			$(this).toggleClass('checked');
			if($(this).hasClass('checked')){
				$(this).find('i').addClass('fa-toggle-on text-success').removeClass('fa-toggle-off text-muted');
				var $val = true;
			}
			else{
				$(this).find('i').removeClass('fa-toggle-on text-success').addClass('fa-toggle-off text-muted');
				var $val = false;
			}

			$(this).find('input').val($val);

			if(!$(this).hasClass('inner')){
				var toggler = false;
				if($(this).hasClass('toggler')){
					var parentPane = $(this).parents('.components-holder');
					toggler = true;
				}
				else{
					var parentPane = $(this).parents('.tab-pane');
				}

				var cCheckBox = parentPane.find('.checkboxer.inner, .checkboxer.toggler');

				if(!$val){
					cCheckBox.removeClass('checked');
					if(!toggler){
						cCheckBox.find('i').removeClass('text-success').addClass('text-muted');
					}
					else{
						cCheckBox.find('i').removeClass('text-success fa-toggle-on').addClass('text-muted fa-toggle-off');
					}
				}
				else{
					cCheckBox.addClass('checked');
					if(!toggler){
						cCheckBox.find('i.fa-toggle-on').removeClass('text-muted').addClass('text-success');
					}
					else{
						cCheckBox.find('i').addClass('text-success fa-toggle-on').removeClass('text-muted fa-toggle-off');
					}
				}
			}
		});

		$('.checkboxer.inner, .checkboxer.toggler').on('click', function(){
			var isToggler = $(this).hasClass('toggler');
			var parentPane = $(this).parents('.tab-pane');
			var parentCheckBoxer = $(this).parents('.tab-pane').find('.checkboxer').not('.inner');
			if(parentPane.find('.inner.checkboxer').find('i.fa-toggle-on.text-success') == 0){
				parentCheckBoxer.find('i').removeClass('text-success fa-toggle-on').addClass('text-muted fa-toggle-off');
				parentCheckBoxer.removeClass('checked');
				if(!isToggler){
					$(this).parents('.components-holder').find('.toggler').find('i').removeClass('text-success fa-toggle-on').addClass('text-muted fa-toggle-off');
				}

				parentCheckBoxer.find('input').val(false)
			}
			else{
				parentCheckBoxer.find('i').addClass('text-success fa-toggle-on').removeClass('text-muted fa-toggle-off');
				parentCheckBoxer.addClass('checked');

				if(!isToggler){
					$(this).parents('.components-holder').find('.toggler').find('i').addClass('text-success fa-toggle-on').removeClass('text-muted fa-toggle-off');
				}

				parentCheckBoxer.find('input').val(true)
			}
		});
	});
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.personnel.layout.app', ['dataTable'=>true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\wamp64\www\polucon\resources\views/layouts/personnel/roles/show.blade.php ENDPATH**/ ?>