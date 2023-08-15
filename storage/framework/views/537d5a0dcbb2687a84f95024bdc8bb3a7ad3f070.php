<?php $__env->startSection('title2'); ?>
<title>Sample Types</title>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content2'); ?>
<main>
  <?php
  $items = array(
    array(
      'link' => route('dashboard-lab'),
      'name' => 'Dashboard',
      'icon' => null
    ),
    array(
      'link' => route('sample-types'),
      'name' => 'Sample Types',
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
    <i class="mdi mdi-test-tube"></i> Sample Types

  </h2>
  <br>
  <!-- ------------------------------------ -->
  <div class="card tab-card">
    <div class="card-header tab-card-header">
      <ul class="nav nav-tabs card-header-tabs" id="asset-tabs" role="tablist">
        <li class="nav-item">
          <a href="#sample-type-tab" class="nav-link active" id="all-sample-type-tab" data-toggle="tab" role="tab" aria-controls="sample-type-tab" aria-selected="true"> <i class="mdi mdi-test-tube" style=" color: black; font-size:15px"></i> Sample Types</a>
        </li>
        <li class="nav-item">
          <a href="#standards-tab" class="nav-link " id="standards-tabs" data-toggle="tab" role="tab" aria-controls="standards-tab" aria-selected="true"> <i class="mdi mdi-layers" style="color: black;font-size:15px"></i>Standards</a>
        </li>
        <li class="nav-item">
          <a href="#standard-value-tab" class="nav-link " id="standard-value-tabs" data-toggle="tab" role="tab" aria-controls="standard-value-tab" aria-selected="true"> <i class="mdi mdi-layers-off" style="color: black;font-size:15px"></i> Standard Values</a>
        </li>
      </ul>
    </div>
    <div class="tab-content" id="sample-type-tabs-content">
      <!-- all sample types  -->
      <div class="tab-pane fade show active p-3" id="sample-type-tab" role="tabpanel" aria-labelledby="one-tab">
        <h5 class="card-title">Sample Types
          <button class="btn btn-primary btn-sm float-right" data-toggle="modal" data-target="#add-sample-type"><i class="mdi mdi-plus"></i> Add</button>
        </h5>
        <div class="table-responsive bg-light p-4">
          <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
            <thead class="bg-light p-2">
              <tr>
                <th>No</th>
                <th>Code</th>
                <th>Name</th>
                <th>Disclaimer</th>
                <th>Category</th>
                <?php if(Auth::user()->company_id == 0): ?>
                <th>Company</th>
                <?php endif; ?>
                <th>Active?</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php if(count($sample_types) > 0): ?>
              <?php $__currentLoopData = $sample_types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sample_type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <tr>
                <td valign="center"><?php echo e($loop->iteration); ?></td>
                <td><?php echo e($sample_type->code); ?></td>
                <td><?php echo e($sample_type->name); ?></td>
                <td><?php echo e($sample_type->description); ?></td>
                <td><?php echo e($sample_type->category()); ?></td>
                <?php if(Auth::user()->company_id == 0): ?>
                <td><?php echo e($sample_type->company); ?></td>
                <?php endif; ?>
                <td class="text-small"><?php echo $sample_type->active == '1' ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>'; ?></td>
                <td nowrap>
                  <button class="btn btn-outline-primary btn-sm" data-target="#edit-sample_type-<?php echo e($loop->iteration); ?>" data-toggle="modal" data-toggle="tooltip" title="Edit Sample Type"><i class="mdi mdi-pencil-outline"></i> <small class="hidden-sm-up">Edit</small> </button>
                  <a class="btn btn-outline-success btn-sm" href="<?php echo e(route('sample-type', ['id'=>$sample_type->id])); ?>" data-toggle="tooltip"  title="View"><i class="mdi mdi-eye-outline"></i> <small class="hidden-sm-up">Show</small> </a>
                  <span class="btn btn-outline-danger btn-sm" data-toggle="modal" data-analysis = "<?php echo e(json_encode($sample_type->analysis_types)); ?>" data-sample = "<?php echo e(json_encode($sample_type)); ?>" data-target="#delete-sample-type" data-toggle="tooltip" title="Delete Sample Type"><i class="mdi mdi-delete-empty"></i></span>
                  <div id="edit-sample_type-<?php echo e($loop->iteration); ?>" class="modal fade" role="dialog">
                    <div class="modal-dialog">
                      <!-- Modal content-->
                      <form class="modal-content" method="POST" action="<?php echo e(route('edit-sample-type', ['id'=>$sample_type->id])); ?>" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
                        <div class="modal-header">
                          <h4 class="modal-title"><i class="mdi mdi-pencil-outline"></i> Edit Sample Type</h4>
                        </div>
                        <div class="modal-body">
                          <div class="form-group">
                            <label class="control-label">Name</label>
                            <input type="text" class="form-control" name="name" value="<?php echo e($sample_type->name); ?>" placeholder="Sample Type Name..." required />
                          </div>
                          <div class="form-group">
                            <label class="control-label">Code</label>
                            <input type="text" class="form-control" name="code" value="<?php echo e($sample_type->code); ?>" placeholder="Sample Type Code..." required />
                          </div>
                          <div class="form-group">
                            <label class="control-label">Disclaimer</label>
                            <textarea class="form-control" name="description" placeholder="Disclaimer..." required><?php echo e($sample_type->description); ?></textarea>
                          </div>
                          <div class="form-group">
                            <label for="" class="control-label">Category</label>
                            <select name="category_id" id="" class="form-control">
                              <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                              <option value="<?php echo e($category->id); ?>" <?php echo e($sample_type->sample_type_category == $category->id ? 'selected' : ''); ?>><?php echo e($category->sample_type_category); ?></option>
                              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                          </div>
                          <?php if(Auth::user()->company_id == 0): ?>
                          <div class="form-group">
                            <label class="control-label">Company</label>
                            <select class="form-control" name="company_id" data-placeholder>
                              <option value="">Select Company...</option>
                              <?php $__currentLoopData = $companies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                              <option value="<?php echo e($c->id); ?>" <?php echo e($c->id == $sample_type->company_id ? 'selected' : ''); ?>><?php echo e($c->name); ?></option>
                              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                          </div>
                          <?php endif; ?>
                          <div class="form-group">
                            <label class="control-label"><input type="checkbox" name="active" value="1" <?php echo e($sample_type->active == 1 ? 'checked' : ''); ?> /> Active</label>
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
          <?php if(count($sample_types) == 0): ?>
          <div class="alert alert-info">
            <i class="mdi mdi-alert"></i> No Sample Types added yet.
          </div>
          <?php endif; ?>
        </div>
      </div>
      <!-- end all sample types  -->

      <!-- standard tab  -->
      <div class="tab-pane fade p-3" id="standards-tab" role="tabpanel" aria-labelledby="one-tab">
        <h5 class="card-title">Standards
          <button class="btn btn-outline-primary btn-sm float-right" data-toggle="modal" data-target="#add-standard"><i class="mdi mdi-plus"></i> Add</button>
        </h5>
        <?php
        $standards = getStandards();
        $standards_value = getStandardValues();
        ?>
        <div class="table-responsive">
          <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
            <thead>
              <tr>
                <th>No</th>
                <th>Code</th>
                <th>Name</th>
                <th>Edited By</th>
                <th>Active</th>
                <th>Main Standard</th>
                <th></th>

              </tr>
            </thead>
            <tbody>
              <?php $__currentLoopData = $standards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $standard): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <tr>

                <td valign="center"><?php echo e($loop->iteration); ?></td>
                <td><?php echo e($standard->code); ?></td>
                <td><?php echo e($standard->name); ?></td>
                <?php
                $user = getUserById($standard->edited_by);

                ?>
                <td><?php echo e(isset($user->id) ? $user->name : '-'); ?></td>
                <td class="text-small"><?php echo $standard->status == '1' ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>'; ?></td>
                <td class="text-small"><?php echo $standard->main_standard == '1' ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>'; ?></td>

                <td>
                  <span class="btn btn-outline-primary btn-sm" data-toggle="modal" data-target="#edit-standard-<?php echo e($standard->id); ?>" data-toggle="tooltip" title="Edit Standard"> <i class="mdi mdi-pencil"></i></span>
                  <a class="btn btn-outline-success btn-sm" href="<?php echo e(route('view-standard',['id'=>$standard->id])); ?>" data-toggle="tooltip" title="View Standard"><i class="mdi mdi-eye"></i></a>
                  <div id="edit-standard-<?php echo e($standard->id); ?>" class="modal fade" role="dialog">
                    <div class="modal-dialog">
                      <!-- modal content  -->
                      <form class="modal-content" action="<?php echo e(route('edit-standard',['id'=>$standard->id])); ?>" method="POST" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
                        <div class="modal-header">
                          <h4 class="modal-title"><i class="mdi mdi-pencil text-primary"></i> Edit <?php echo e($standard->name); ?> standard</h4>
                        </div>
                        <div class="modal-body">
                          <div class="form-group">
                            <label class="control-label">Code</label>
                            <input type="text" name="code" class="form-control" value="<?php echo e($standard->code); ?>" placeholder="Standard Code..." required />
                          </div>
                          <div class="form-group">
                            <label class="control-label">Name</label>
                            <input type="text" name="name" class="form-control" value="<?php echo e($standard->name); ?>" placeholder="Standard Name..." required />
                          </div>
                          <div class="form-group">
                            <label class="control-label">
                              <input type="checkbox" name="active" value="1" <?php echo e($standard->status == 1 ? 'checked' : ''); ?> />
                              Is Active
                            </label>
                          </div>
                          <div class="form-check">
                            <input class="form-check-input" type="checkbox" <?php echo e($standard->main_standard == 1 ? 'checked' : ''); ?> class="form-control" name="main_standard" value="1" />
                            <label class="form-check-label" for="is-range">
                              Main Standard
                            </label>
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
      </div>
      <!-- end standard tab  -->

      <!-- standard value tab -->
      <div class="tab-pane fade p-3" id="standard-value-tab" role="tabpanel" aria-labelledby="one-tab">
        <h5 class="card-title">Standard Values
          <button class="btn btn-outline-primary btn-sm float-right" data-toggle="modal" data-target="#add-standard-value"><i class="mdi mdi-plus"></i> Add</button>
        </h5>
        <div class="table-responsive">
          <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
            <thead>
              <tr>
                <th>No</th>
                <th>Code</th>
                <th>Name</th>
                <th>Edited By</th>
                <th>Active</th>

                <th></th>

              </tr>
            </thead>
            <tbody>
              <?php $__currentLoopData = $standards_value ?? array(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <tr>

                <td valign="center"><?php echo e($loop->iteration); ?></td>
                <td><?php echo e($value->code); ?></td>
                <td><?php echo e($value->name); ?></td>
                <?php
                $user = getUserById($value->edited_by);
                ?>
                <td><?php echo e(isset($user->id) ? $user->name : '-'); ?></td>
                <td class="text-small"><?php echo $value->status == '1' ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>'; ?></td>
                <td>
                  <span class="btn btn-outline-primary btn-sm" data-toggle="modal" data-target="#edit-standard-value-<?php echo e($value->id); ?>"> <i class="mdi mdi-pencil"></i></span>
                  <div id="edit-standard-value-<?php echo e($value->id); ?>" class="modal fade" role="dialog">
                    <div class="modal-dialog">
                      <!-- modal content  -->
                      <form class="modal-content" action="<?php echo e(route('edit-standard-value',['id'=>$value->id])); ?>" method="POST" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
                        <div class="modal-header">
                          <h4 class="modal-title"><i class="mdi mdi-pencil text-primary"></i> Edit <?php echo e($value->name); ?> Standard Value</h4>
                        </div>
                        <div class="modal-body">
                          <div class="form-group">
                            <label class="control-label">Code</label>
                            <input type="text" name="code" class="form-control" value="<?php echo e($value->code); ?>" placeholder="Standard Value Code..." required />
                          </div>
                          <div class="form-group">
                            <label class="control-label">Name</label>
                            <input type="text" name="name" class="form-control" value="<?php echo e($value->name); ?>" placeholder="Standard Value Name..." required />
                          </div>

                          <div class="form-check">
                            <input class="form-check-input" type="checkbox" <?php echo e($value->active == 0 ? 'checked' : ''); ?> class="form-control" name="active" value="1" />
                            <label class="form-check-label" for="is-range">
                              Active
                            </label>
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
      </div>
      <!-- end inactive assets  -->
    </div>
  </div>
  <!-- ------------------------------------ -->

</main>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script2'); ?>
<div class="modal fade" id="delete-sample-type" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" action="<?php echo e(route ('delete-sample-type')); ?>" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>
        <div class="modal-body">

        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-outline-danger btn-sm" id="delete-btn"><i class="mdi mdi-delete-empty"></i> Delete</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
      </form>
    </div>
  </div>
</div>
<div id="add-sample-type" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <form class="modal-content" method="POST" action="<?php echo e(route('add-sample-types')); ?>" enctype="multipart/form-data">
      <?php echo csrf_field(); ?>
      <div class="modal-header">
        <h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Sample Type</h4>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label class="control-label">Name</label>
          <input type="text" class="form-control" name="name" placeholder="Sample Type Name..." required />
        </div>
        <div class="form-group">
          <label class="control-label">Code</label>
          <input type="text" class="form-control" name="code" placeholder="Sample Type Code..." required />
        </div>
        <div class="form-group">
          <label for="" class="control-label">Category</label>
          <select name="category_id" id="" class="form-control">
            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($category->id); ?>"><?php echo e($category->name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
        </div>
        <div class="form-group">
          <label class="control-label">Disclaimer</label>
          <textarea class="form-control" name="description" placeholder="Disclaimer..." required></textarea>
        </div>
        <div class="form-group">
          <label for="" class="control-label">Category</label>
          <select name="category_id" id="" class="form-control">
            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($category->id); ?>"><?php echo e($category->sample_type_category); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
        </div>
        <?php if(Auth::user()->company_id == 0): ?>
        <div class="form-group">
          <label class="control-label">Company</label>
          <select class="form-control" name="company_id" data-placeholder>
            <option value="">Select Company...</option>
            <?php $__currentLoopData = $companies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($c->id); ?>"><?php echo e($c->name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
        </div>
        <?php endif; ?>
        
        <div class="form-group">
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
<div id="add-standard" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- modal content  -->
    <form class="modal-content" action="<?php echo e(route('add-standard')); ?>" method="POST" enctype="multipart/form-data">
      <?php echo csrf_field(); ?>
      <div class="modal-header">
        <h4 class="modal-title"><i class="mdi mdi-plus text-primary"></i> Add Standard</h4>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label class="control-label">Code</label>
          <input type="text" name="code" class="form-control" value="" placeholder="Standard Code..." required />
        </div>
        <div class="form-group">
          <label class="control-label">Name</label>
          <input type="text" name="name" class="form-control" value="" placeholder="Standard Name..." required />
        </div>
        <div class="form-group">
          <label class="control-label">
            <input type="checkbox" name="active" value="1" />
            Is Active
          </label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" class="form-control" name="main_standard" value="1" />
          <label class="form-check-label" for="is-range">
            Main Standard
          </label>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary" ><i class="mdi mdi-content-save"></i> Save</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </form>
  </div>
</div>
<div id="add-standard-value" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- modal content  -->
    <form class="modal-content" action="<?php echo e(route('add-standard-value')); ?>" method="POST" enctype="multipart/form-data">
      <?php echo csrf_field(); ?>
      <div class="modal-header">
        <h4 class="modal-title"><i class="mdi mdi-plus text-primary"></i> Add Standard Value</h4>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label class="control-label">Code</label>
          <input type="text" name="code" class="form-control" value="" placeholder="Standard Value Code..." required />
        </div>
        <div class="form-group">
          <label class="control-label">Name</label>
          <input type="text" name="name" class="form-control" value="" placeholder="Standard Value Name..." required />
        </div>
        <div class="form-group">
          <label class="control-label">
            <input type="checkbox" name="active" value="1" />
            Is Active
          </label>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </form>
  </div>
</div>




<script>
  $(function(){
    $('#delete-sample-type').on('show.bs.modal',function(e){
      var sample = $(e.relatedTarget).data('sample');
      var analysis_type = $(e.relatedTarget).data('analysis');
      var text = `
      <div class="alert alert-success p-3">
        <i class="mdi mdi-alert-decagram"></i> Confirm you want to delete <b>${sample.name} Sample Type</b>
        <input type="hidden" name="sample_type_id" value="${sample.id}">
      </div>
      `;
      var text_ = `
      <div class="alert alert-danger p-3">
        <i class="mdi mdi-alert-decagram"></i> <b>${sample.name} Sample Type</b> already has analysis configured therefore cannot be deleted!
      </div>
      `;
      $(this).find('.modal-body').empty();
      if(analysis_type.length > 0){
        $(this).find('.modal-body').append(text_);
        $(this).find('#delete-btn').prop('disabled',true);
      }else{
        $(this).find('.modal-body').append(text);
        $(this).find('#delete-btn').removeAttr('disabled');
      }
      

    })
  })
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.lab.layout.app', ['dataTable'=>true, 'select2'=>true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/kimari/Projects/polucon/resources/views/layouts/lab/sample-types/index.blade.php ENDPATH**/ ?>