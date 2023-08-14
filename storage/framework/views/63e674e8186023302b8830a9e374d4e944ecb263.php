

<?php $__env->startSection('title2'); ?>
  <title>Labs</title>
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
          'link' => route('labs'),
          'name' => 'Labs',
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
      <i class="mdi mdi-flask"></i> Labs
      <button class="btn btn-primary btn-sm float-right" data-toggle="modal" data-target="#add-lab"><i class="mdi mdi-plus"></i> Add</button>
    </h2>
    <br>
    <div class="table-responsive bg-light p-4">
      <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
        <thead class="bg-light p-2">
          <tr>
            <th>No</th>
            <th>Code</th>
            <th>Name</th>
            <th>Start Sample</th>
            <th>Address</th>
            <th>Website</th>
            <th>Fax</th>
            <th>Email</th>
            <th nowrap>Phone 1</th>
            <th nowrap>Phone 2</th>
            <th nowrap>Phone 3</th>
            <th>Internal Lab?</th>
            <th>Active?</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php if(count($labs) > 0): ?>
            <?php $__currentLoopData = $labs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lab): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <tr>
                <td valign="center"><?php echo e($loop->iteration); ?></td>
                <td><?php echo e($lab->code); ?></td>
                <td><?php echo e($lab->name); ?></td>
                <td><?php echo e($lab->start_sample_no ?? '-'); ?></td>
                <td><?php echo e($lab->address); ?></td>
                <td><?php echo e($lab->website); ?></td>
                <td><?php echo e($lab->fax); ?></td>
                <td><?php echo e($lab->email); ?></td>
                <td><?php echo e($lab->phone1 ?? '-'); ?></td>
                <td><?php echo e($lab->phone2 ?? '-'); ?></td>
                <td><?php echo e($lab->phone3 ?? '-'); ?></td>
                <td class="text-small"><?php echo $lab->is_external == '0' ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>'; ?></td>
                <td class="text-small"><?php echo $lab->active == '1' ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>'; ?></td>
                <td nowrap>
                  <button class="btn btn-primary btn-sm" data-target="#edit-lab-<?php echo e($loop->iteration); ?>" data-toggle="modal"><i class="mdi mdi-pencil-outline"></i> <small class="hidden-sm-up">Edit</small> </button>
                  <a class="btn btn-success btn-sm" href="<?php echo e(route('show-lab-analysis-types', ['labid'=>$lab->id])); ?>"><i class="mdi mdi-eye-outline"></i> <small class="hidden-sm-up">Show</small> </a>
                  <div id="edit-lab-<?php echo e($loop->iteration); ?>" class="modal fade" role="dialog">
                    <div class="modal-dialog modal-lg">
                      <!-- Modal content-->
                      <form class="modal-content" method="POST" action="<?php echo e(route('edit-lab', ['id'=>$lab->id])); ?>" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
                        <div class="modal-header">
                          <h4 class="modal-title"><i class="mdi mdi-pencil-outline"></i> Edit Lab</h4>
                        </div>
                        <div class="modal-body row">
                          <div class="col-sm-6">
                            <div class="form-group">
                              <label class="control-label">Lab Name</label>
                              <input type="text" class="form-control" name="name" value="<?php echo e($lab->name); ?>" placeholder="Lab Name..." required />
                            </div>
                            <div class="form-group">
                              <label class="control-label">Lab Code</label>
                              <input type="text" class="form-control" name="code" value="<?php echo e($lab->code); ?>" placeholder="Lab Code..." required />
                            </div>
                            <div class="form-group">
                              <label class="control-label">Start Sample No</label>
                              <input type="text" class="form-control" name="start_sample_no" value="<?php echo e($lab->start_sample_no); ?>" placeholder="Lab Start Sample No..." required />
                            </div>

                            <div class="form-group">
                              <label class="control-label">Lab Postal Address</label>
                              <textarea class="form-control" name="address" placeholder="Lab Postal Address..." required><?php echo e($lab->address); ?></textarea>
                            </div>
                            <div class="form-group">
                              <label class="control-label">Lab Location</label>
                              <input type="text" class="form-control" name="location" value="<?php echo e($lab->location); ?>" placeholder="Lab Location..." required />
                            </div>
                            <div class="form-group">
                              <label class="control-label">Lab Website</label>
                              <input type="text" class="form-control" name="website" value="<?php echo e($lab->website); ?>" placeholder="Lab Website..." />
                            </div>
                          </div>
                          <div class="col-sm-6">
                            <div class="form-group">
                              <label class="control-label">Lab Fax</label>
                              <input type="fax" class="form-control" name="fax" value="<?php echo e($lab->fax); ?>" placeholder="Lab Fax..." />
                            </div>
                            <div class="form-group">
                              <label class="control-label">Lab Email</label>
                              <input type="email" class="form-control" name="email" value="<?php echo e($lab->email); ?>" placeholder="Lab Email..." required />
                            </div>
                            <div class="form-group">
                              <label class="control-label">Lab Phone 1</label>
                              <input type="tel" class="form-control" name="phone1" value="<?php echo e($lab->phone1); ?>" placeholder="Lab Phone 1..." required />
                            </div>
                            <div class="form-group">
                              <label class="control-label">Lab Phone 2</label>
                              <input type="tel" class="form-control" name="phone2" value="<?php echo e($lab->phone2); ?>" placeholder="Lab Phone 2..." />
                            </div>
                            <div class="form-group">
                              <label class="control-label">Lab Phone 3</label>
                              <input type="tel" class="form-control" name="phone3" value="<?php echo e($lab->phone3); ?>" placeholder="Lab Phone 3..." />
                            </div>
                            <div class="form-group">
                              <label class="control-label"><input type="checkbox" name="is_external" value="1" <?php echo e($lab->is_external == 1 ? 'checked' : ''); ?> /> Is an External Lab?</label>
                            </div>
                            <div class="form-group">
                              <label class="control-label"><input type="checkbox" value="1" name="active" <?php echo e($lab->active == 1 ? 'checked' : ''); ?> /> Is Active?</label>
                            </div>
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
      <?php if(count($labs) == 0): ?>
        <div class="alert alert-info">
          <i class="mdi mdi-alert"></i> No Labs added yet.
        </div>
      <?php endif; ?>
    </div>
  </main>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script2'); ?>
  <div id="add-lab" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
      <!-- Modal content-->
      <form class="modal-content" method="POST" action="<?php echo e(route('add-labs')); ?>" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>
        <div class="modal-header">
          <h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Lab</h4>
        </div>
        <div class="modal-body row">
          <div class="col-sm-6">
            <div class="form-group">
              <label class="control-label">Lab Name</label>
              <input type="text" class="form-control" name="name" placeholder="Lab Name..." required />
            </div>
            <div class="form-group">
              <label class="control-label">Lab Code</label>
              <input type="text" class="form-control" name="code" placeholder="Lab Code..." required />
            </div>
            <div class="form-group">
              <label class="control-label">Start Sample No</label>
              <input type="text" class="form-control" name="start_sample_no" value="" placeholder="Lab Start Sample No..." required />
            </div>
            <div class="form-group">
              <label class="control-label">Lab Postal Address</label>
              <textarea class="form-control" name="address" placeholder="Lab Postal Address..." required required ></textarea>
            </div>
            <div class="form-group">
              <label class="control-label">Lab Location</label>
              <input type="text" class="form-control" name="location" placeholder="Lab Location..." required />
            </div>
            <div class="form-group">
              <label class="control-label">Lab Website</label>
              <input type="text" class="form-control" name="website" placeholder="Lab Website..."  />
            </div>
          </div>
          <div class="col-sm-6">
            <div class="form-group">
              <label class="control-label">Lab Fax</label>
              <input type="text" class="form-control" name="fax" placeholder="Lab Fax..." />
            </div>
            <div class="form-group">
              <label class="control-label">Lab Email</label>
              <input type="email" class="form-control" name="email" placeholder="Lab Email..." required />
            </div>
            <div class="form-group">
              <label class="control-label">Lab Phone 1</label>
              <input type="tel" class="form-control" name="phone1" value="" placeholder="Lab Phone 1..." required />
            </div>
            <div class="form-group">
              <label class="control-label">Lab Phone 2</label>
              <input type="tel" class="form-control" name="phone2" value="" placeholder="Lab Phone 2..." />
            </div>
            <div class="form-group">
              <label class="control-label">Lab Phone 3</label>
              <input type="tel" class="form-control" name="phone3" value="" placeholder="Lab Phone 3..." />
            </div>
            <div class="form-group">
              <label class="control-label"><input type="checkbox" value="1" name="is_external" /> Is an External Lab?</label>
            </div>
            <div class="form-group">
              <label class="control-label"><input type="checkbox" value="1" name="active" checked /> Is Active?</label>
            </div>
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
<?php echo $__env->make('layouts.lab.layout.app', ['dataTable'=>true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\wamp64\www\polucon\resources\views/layouts/lab/index.blade.php ENDPATH**/ ?>