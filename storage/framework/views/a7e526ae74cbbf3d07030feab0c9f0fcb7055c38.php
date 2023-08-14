

<?php $__env->startSection('title2'); ?>
  <title>Analysis Types | <?php echo e(isset($selected_lab) ? " | ".$selected_lab->name : ''); ?></title>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content2'); ?>
  <main>
    <?php
      $items = array(
        array(
          'link' => route('lab-home'),
          'name' => 'Lab Management',
					'icon' => null
				),
				array(
          'link' => isset($selected_lab) ? route('show-lab-analysis-types', ['labid'=>$selected_lab->id]) : route('analysis-types'),
          'name' => 'Analysis Types',
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
      <i class="mdi mdi-microscope"></i> Analysis Types <small class="text-muted"><?php echo e(isset($selected_lab) ? " | ".$selected_lab->name : ''); ?></small>
      <button class="btn btn-primary btn-sm float-right" data-toggle="modal" data-target="#add-analysis-type"><i class="mdi mdi-plus"></i> Add</button>
    </h2>
    <br>
    <div class="table-responsive bg-light p-4">
      <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
        <thead class="bg-light p-2">
          <tr>
            <th>No</th>
            <th>Code</th>
            <th>Name</th>
            <th>Short Name</th>
            <th>Reporting Time</th>
            <th>Description</th>
            <th>Sample Type</th>
            <th>Lab</th>
            <th>Active?</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php if(count($analysis_types) > 0): ?>
            <?php $__currentLoopData = $analysis_types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $analysis_type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <tr>
                <td valign="center"><?php echo e($loop->iteration); ?></td>
                <td><?php echo e($analysis_type->code); ?></td>
                <td><?php echo e($analysis_type->name); ?></td>
                <td><?php echo e($analysis_type->short_name ?? 'N/A'); ?></td>
                <td><?php echo e($analysis_type->reporting_time ?? 0); ?></td>
                <td><?php echo e($analysis_type->description); ?></td>
                <td><?php echo e($analysis_type->sample_type->name); ?></td>
                <td><?php echo e($analysis_type->lab->name); ?> - <?php echo e($analysis_type->lab->code); ?></td>
                <td class="text-small"><?php echo $analysis_type->active == '1' ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>'; ?></td>
                <td nowrap>
                  <button class="btn btn-primary btn-sm" data-target="#edit-analysis_type-<?php echo e($loop->iteration); ?>" data-toggle="modal"><i class="mdi mdi-pencil-outline"></i> <small class="hidden-sm-up">Edit</small> </button>
                  
                  <a class="btn btn-success btn-sm" href="<?php echo e(route('analysis-type', ['id'=>$analysis_type->id])); ?>"><i class="mdi mdi-eye-outline"></i> <small class="hidden-sm-up">Show</small> </a>
                  <div id="edit-analysis_type-<?php echo e($loop->iteration); ?>" class="modal fade" role="dialog">
                    <div class="modal-dialog">
                      <!-- Modal content-->
                      <form class="modal-content" method="POST" action="<?php echo e(route('edit-analysis-type', ['id'=>$analysis_type->id])); ?>" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
                        <div class="modal-header">
                          <h4 class="modal-title"><i class="mdi mdi-pencil-outline"></i> Edit Analysis Type</h4>
                        </div>
                        <div class="modal-body">
                          <div class="form-group">
                            <label class="control-label">Name</label>
                            <input type="text" class="form-control" name="name" value="<?php echo e($analysis_type->name); ?>" placeholder="Analysis Type Name..." required />
                          </div>
                          <div class="form-group">
                            <label class="control-label">Code</label>
                            <input type="text" class="form-control" name="code" value="<?php echo e($analysis_type->code); ?>" placeholder="Analysis Type Code..." required />
                          </div>
                          <div class="form-group">
                            <label class="control-label">Description</label>
                            <textarea class="form-control" name="description" placeholder="Description..." required><?php echo e($analysis_type->description); ?></textarea>
                          </div>
                          <div class="form-group">
                            <label class="control-label">Sample Type</label>
                            <select class="form-control" name="sample_type_id" data-placeholder>
                              <option value="">Select Sample Type...</option>
                              <?php $__currentLoopData = $sample_types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sam): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($sam->id); ?>" <?php echo e($sam->id == $analysis_type->sample_type_id ? 'selected' : ''); ?>><?php echo e($sam->name); ?></option>
                              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                          </div>
                          <div class="form-group">
                            <label for="" class="control-label">Report Band</label>
                            <select name="brand_id" id="" class="form-control">
                              <option value="">Select Band</option>
                              <option value="0"<?php echo e($analysis_type->brand_id == 0 || $analysis_type->brand_id == '' ? 'selected' : ''); ?>>Normal</option>
                              <option value="1" <?php echo e($analysis_type->brand_id == 1 ? 'selected' : ''); ?>>Physical Format</option>
                              <option value="2" <?php echo e($analysis_type->brand_id == 2 ? 'selected' : ''); ?>>Pesticide Format</option>
                            </select>
                          </div>
													<div class="form-group">
														<label class="control-label">Reporting Time <small class="text-muted">(in days)</small></label>
														<input type="number" min="0" class="form-control" name="reporting_time" value="<?php echo e($analysis_type->reporting_time); ?>" placeholder="Analysis Type Reporting Time..." required />
													</div>
                          <div class="form-group">
                            <input type="hidden" name="lab_id" value="<?php echo e($selected_lab->id); ?>" />
                            <label class="control-label"><input type="checkbox" name="active" value="1" <?php echo e($analysis_type->active == 1 ? 'checked' : ''); ?> /> Active</label>
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
      <?php if(count($analysis_types) == 0): ?>
        <div class="alert alert-info">
          <i class="mdi mdi-alert"></i> No Analysis Types added yet.
        </div>
      <?php endif; ?>
    </div>
  </main>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script2'); ?>
  <div id="add-analysis-type" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <!-- Modal content-->
      <form class="modal-content" method="POST" action="<?php echo e(route('add-analysis-types')); ?>" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>
        <div class="modal-header">
          <h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Analysis Type</h4>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label class="control-label">Name</label>
            <input type="text" class="form-control" name="name" placeholder="Analysis Type Name..." required />
          </div>
          <div class="form-group">
            <label class="control-label">Code</label>
            <input type="text" class="form-control" name="code" placeholder="Analysis Type Code..." required />
          </div>
          <div class="form-group">
            <label class="control-label">Description</label>
            <textarea class="form-control" name="description" placeholder="Description..." required></textarea>
          </div>
          <div class="form-group">
            <label class="control-label">Sample Type</label>
            <select class="form-control" name="sample_type_id" data-placeholder>
              <?php $__currentLoopData = $sample_types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sam): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($sam->id); ?>"><?php echo e($sam->name); ?></option>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
          </div>
					<div class="form-group">
						<label class="control-label">Reporting Time <small class="text-muted">(in days)</small></label>
						<input type="number" min="0" class="form-control" name="reporting_time" placeholder="Analysis Type Reporting Time..." required />
					</div>
          <div class="form-group">
            <input type="hidden" name="lab_id" value="<?php echo e($selected_lab->id); ?>" />
            <label class="control-label"><input type="checkbox" name="active" value="1" checked /> Active</label>
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
<?php echo $__env->make('layouts.lab.layout.app', ['dataTable'=>true, 'select2'=>true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\wamp64\www\polucon\resources\views/layouts/lab/analysis-types/index.blade.php ENDPATH**/ ?>