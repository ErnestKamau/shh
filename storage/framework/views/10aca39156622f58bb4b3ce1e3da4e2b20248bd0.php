

<?php $__env->startSection('title2'); ?>
<title>Registered Companies</title>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content2'); ?>
<?php
$items = array(
  array(
    'link' => route('companies'),
    'name' => 'Companies',
    'icon' => null
  ),
  array(
    'link' => '#',
    'name' => "system-users",
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
    <i class="mdi mdi-domain"></i> Registered Companies
    <button class="btn btn-primary btn-sm float-right" data-toggle="modal" data-target="#add-company"><i class="mdi mdi-plus"></i> Add</button>
  </h2>
  <div class="table-responsive bg-light p-4">
    <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
      <thead class="bg-light p-2">
        <tr>
          <th>No</th>
          <th>Logo</th>
          <th>Name</th>
          <th>Email</th>
          <th>Fax</th>
          <th>Telephone</th>
          <th>Cell phone</th>
          <th>Country</th>
          <th>Address</th>
          <th>Street</th>
          <th>Website</th>
          <th>Status</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php if(count($companies) > 0): ?>
        <?php $__currentLoopData = $companies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $company): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
          <td valign="center"><?php echo e($loop->iteration); ?></td>
          <td><img src="<?php echo e($company->logo); ?>" class="table-image" /></td>
          <td><?php echo e($company->name); ?></td>
          <td><?php echo e($company->email != '' ? $company->email:'-'); ?></td>
          <td><?php echo e($company->fax ?? '-'); ?></td>
          <td><?php echo e($company->telephone != '' ? $company->telephone :'-'); ?></td>
          <td><?php echo e($company->cell_phone != '' ? $company->cell_phone:'-'); ?></td>
          <td><?php echo e($company->country); ?></td>
          <td><?php echo e($company->address); ?></td>
          <td><?php echo e($company->street != '' ? $company->street:'-'); ?></td>
          <td><?php echo e($company->website); ?></td>
          <td class="text-center"><?php echo $company->active == 1 ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>'; ?></td>
          <td nowrap>
            <button class="btn btn-primary btn-sm" data-target="#edit-company-<?php echo e($loop->iteration); ?>" data-toggle="modal"><i class="mdi mdi-pencil-outline"></i> <small class="hidden-sm-up">Edit</small> </button>
            <button class="btn btn-danger btn-sm"><i class="mdi mdi-delete-empty"></i> <small class="hidden-sm-up">Delete</small> </button>
            <button class="btn btn-outline-warning btn-sm" data-target="#activate-company-<?php echo e($company->id); ?>" data-toggle="modal"><i style="font-size: 15px;color:black" class="mdi mdi-refresh"></i> <small class="hidden-sm-up">Activate</small> </button>
            <div class="modal fade" id="activate-company-<?php echo e($company->id); ?>">
              <div class="modal-dialog">
                <form action="<?php echo e(route('activate-company')); ?>" method="post" class="modal-content">
                  <?php echo csrf_field(); ?>
                  <div class="modal-header">
                    <h4 class="modal-title"><i class="mdi mdi-refresh text-success"></i> <?php echo $company->active == 1 ? 'Deactivate':'Activate'; ?> <?php echo e($company->name); ?></h4>
                  </div>
                  <div class="modal-body">
                    <div class="form-group">
                      <label class="control-label">
                        <input type="checkbox" name="set_default" <?php echo e($company->active == 1 ? 'checked' : ''); ?>> Set company as default company?
                      </label>
                    </div>
                    <div class="form-group">
                      <label class="control-label">
                        <input type="checkbox" name="show_reports" <?php echo e($company->show_on_reports == 1 ? 'checked' : ''); ?>> Show Company Logo on system reports?
                      </label>
                    </div>
                    <div class="form-group hidden">
                      <label class="control-label">Company ID</label>
                      <input type="number" name="company_id" value=<?php echo e($company->id); ?> id="" class="form-control">
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="submit" class="btn btn-outline-primary"><i class="mdi mdi-content-save"></i> Save</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                  </div>
                </form>
              </div>
            </div>
            <div id="edit-company-<?php echo e($loop->iteration); ?>" class="modal fade" role="dialog">
              <div class="modal-dialog">
                <!-- Modal content-->
                <form class="modal-content" method="POST" action="<?php echo e(route('edit-company', ['id'=>$company->id])); ?>" enctype="multipart/form-data">
                  <?php echo csrf_field(); ?>
                  <div class="modal-header">
                    <h4 class="modal-title"><i class="mdi mdi-pencil-outline"></i> Edit Company</h4>
                  </div>
                  <div class="modal-body">
                    <div class="form-group">
                      <label class="control-label">Company Name</label>
                      <input type="text" class="form-control" name="name" value="<?php echo e($company->name); ?>" placeholder="Company Name..." />
                    </div>
                    <div class="form-group">
                      <label class="control-label">Company Logo <small class="text-danger">*Leave blank to maintain current logo</small></label>
                      <div class="row mb-0 bg-light text-center">
                        <div class="col-xl-12 col-sm-12 text-center">
                          <img src="<?php echo e($company->logo); ?>" class="mt-4 mb-4" style="width:70%">
                        </div>
                      </div>
                      <input type="file" class="form-control" name="logo" placeholder="Company Logo..." />
                    </div>
                    <div class="form-group">
                      <label class="control-label">Email</label>
                      <input type="text" name="email" placeholder="Example.gmail.com..." id="" value="<?php echo e($company->email); ?>" class="form-control">
                    </div>
                    <div class="form-group">
                      <label class="control-label">Fax</label>
                      <input type="fax" name="fax" placeholder="Fax..." id="" value="<?php echo e($company->fax); ?>" class="form-control">
                    </div>
                    <div class="form-group">
                      <label class="control-label">Telephone</label>
                      <input type="text" name="telephone" placeholder="Telephone..." id="" value="<?php echo e($company->telephone); ?>" class="form-control">
                    </div>
                    <div class="form-group">
                      <label class="control-label">Cell Phone</label>
                      <input type="text" name="cell_phone" placeholder="Cell Phone..." id="" value="<?php echo e($company->cell_phone); ?>" class="form-control">
                    </div>
                    <div class="form-group">
                      <label class="control-label">Company Country</label>
                      <select class="form-control" name="country_id" data-placeholder>
                        <?php $__currentLoopData = $countries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($c->id); ?>" <?php echo e($c->id == $company->country_id ? 'selected' : ''); ?>><?php echo e($c->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                      </select>
                    </div>
                    <div class="form-group">
                      <label class="control-label">Company Postal Address</label>
                      <textarea class="form-control" name="address" placeholder="Company Postal Address..."><?php echo e($company->address); ?></textarea>
                    </div>
                    <div class="form-group">
                      <label class="control-label">Company Location</label>
                      <input type="text" class="form-control" name="location" value="<?php echo e($company->location); ?>" placeholder="Company Location..." />
                    </div>
                    <div class="form-group">
                      <label class="control-label">Street</label>
                      <input type="text" name="street" value="<?php echo e($company->street); ?>" placeholder="Street..." id="" class="form-control">
                    </div>
                    <div class="form-group">
                      <label class="control-label">Company Website</label>
                      <input type="text" class="form-control" name="website" value="<?php echo e($company->website); ?>" placeholder="Company Website..." />
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
    <?php if(count($companies) == 0): ?>
    <div class="alert alert-info">
      <i class="mdi mdi-alert"></i> No Companies added yet.
    </div>
    <?php endif; ?>
  </div>
</main>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script2'); ?>
<div id="add-company" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <form class="modal-content" method="POST" action="<?php echo e(route('add-companies')); ?>" enctype="multipart/form-data">
      <?php echo csrf_field(); ?>
      <div class="modal-header">
        <h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Company</h4>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label class="control-label">Company Name</label>
          <input type="text" class="form-control" name="name" placeholder="Company Name..." />
        </div>
        <div class="form-group">
          <label class="control-label">Company Logo</label>
          <input type="file" class="form-control" name="logo" placeholder="Company Logo..." />
        </div>
        <div class="form-group">
          <label class="control-label">Email</label>
          <input type="text" name="email" placeholder="Example.gmail.com..." id="" class="form-control">
        </div>
        <div class="form-group">
          <label class="control-label">Telephone</label>
          <input type="text" name="telephone" placeholder="Telephone..." id="" class="form-control">
        </div>
        <div class="form-group">
          <label class="control-label">Cell Phone</label>
          <input type="text" name="cell_phone" placeholder="Cell Phone..." id="" class="form-control">
        </div>
        <div class="form-group">
          <label class="control-label">Company Country</label>
          <select class="form-control" name="country_id" data-placeholder>
            <?php $__currentLoopData = $countries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($c->id); ?>"><?php echo e($c->name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
        </div>
        <div class="form-group">
          <label class="control-label">Company Postal Address</label>
          <textarea class="form-control" name="address" placeholder="Company Postal Address..."></textarea>
        </div>
        <div class="form-group">
          <label class="control-label">Company Location</label>
          <input type="text" class="form-control" name="location" placeholder="Company Location..." />
        </div>
        <div class="form-group">
          <label class="control-label">Street</label>
          <input type="text" name="street" placeholder="Street..." id="" class="form-control">
        </div>
        <div class="form-group">
          <label class="control-label">Company Website</label>
          <input type="text" class="form-control" name="website" placeholder="Company Website..." />
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
<?php echo $__env->make('layouts.configuration.layout.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\wamp64\www\polucon\resources\views/layouts/configuration/company/index.blade.php ENDPATH**/ ?>