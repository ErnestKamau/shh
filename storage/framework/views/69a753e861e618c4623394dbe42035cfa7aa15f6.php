

<?php $__env->startSection('title2'); ?>
  <title>Suppliers</title>
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
          'link' => route('inventory-suppliers'),
          'name' => 'Suppliers',
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
      <i class="mdi mdi-account-group"></i> Suppliers
      <button class="btn btn-primary btn-sm float-right" data-toggle="modal" data-target="#add-inventory-supplier"><i class="mdi mdi-plus"></i> Add</button>
    </h2>
    <br>
    <div class="table-responsive bg-light p-4">
      <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
        <thead class="bg-light p-2">
          <tr>
            <th>No</th>
            <th>Logo</th>
            <th>Name</th>
            <th>Rating <small class="text-muted">(avg.)</small></th>
            <th>Email</th>
            <th>Phone</th>
            <th>Building</th>
            <th>Street</th>
            <th>Town</th>
            <th>Postal Address</th>
            <th>PIN Number</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php if(count($suppliers) > 0): ?>
            <?php $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supplier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <tr>
                <td valign="center"><?php echo e($loop->iteration); ?></td>
                <td><img src="<?php echo e($supplier->logo); ?>" style="width: 75px" /></td>
                <td><?php echo e($supplier->name); ?></td>
                <td><?php echo e($supplier->average_rating()); ?><i class="mdi mdi-star text-success"></i></td>
								<td><?php echo e($supplier->email); ?></td>
								<td><?php echo e($supplier->phone); ?></td>
								<td><?php echo e($supplier->building); ?></td>
								<td><?php echo e($supplier->street); ?></td>
								<td><?php echo e($supplier->town); ?></td>
								<td><?php echo e($supplier->address); ?></td>
								<td><?php echo e($supplier->pin_number); ?></td>
								<td nowrap>
                  <a class="btn btn-success btn-sm" href="<?php echo e(route('show-inventory-supplier', ['id'=>$supplier->id])); ?>"><i class="mdi mdi-eye-outline"></i> <small class="hidden-sm-up">Show</small> </a>
                </td>
              </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          <?php endif; ?>
        </tbody>
      </table>
      <?php if(count($suppliers) == 0): ?>
        <div class="alert alert-info">
          <i class="mdi mdi-alert"></i> No Suppliers added yet.
        </div>
      <?php endif; ?>
    </div>
  </main>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script2'); ?>
  <div id="add-inventory-supplier" class="modal fade" role="dialog">
    <div class="modal-dialog">
      <!-- Modal content-->
      <form class="modal-content" method="POST" action="<?php echo e(route('add-inventory-supplier')); ?>" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>
        <div class="modal-header">
          <h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Supplier</h4>
        </div>
        <div class="modal-body">
          <div class="form-group">
						<label class="control-label">Name</label>
						<input type="text" class="form-control" name="name" value="" placeholder="Name..." required />
					</div>
					<div class="form-group">
						<label class="control-label">Logo</label>
						<input type="file" class="form-control" name="logo"  />
					</div>
					<div class="form-group">
						<label class="control-label">Email</label>
						<input type="email" class="form-control" name="email" value="" placeholder="Email..." required />
					</div>
					<div class="form-group">
						<label class="control-label">Phone</label>
						<input type="text" class="form-control" name="phone" value="" placeholder="Phone..." required />
					</div>
					<div class="form-group">
						<label class="control-label">Building</label>
						<input type="text" class="form-control" name="building" value="" placeholder="Building..." required />
					</div>
					<div class="form-group">
						<label class="control-label">Street</label>
						<input type="text" class="form-control" name="street" value="" placeholder="Street..." required />
					</div>
					<div class="form-group">
						<label class="control-label">Town</label>
						<input type="text" class="form-control" name="town" value="" placeholder="Town..." required />
					</div>
					<div class="form-group">
						<label class="control-label">Address</label>
						<input type="text" class="form-control" name="address" value="" placeholder="Address..." required />
					</div>
					<div class="form-group">
						<label class="control-label">PIN Number</label>
						<input type="text" class="form-control" name="pin_number" value="" placeholder="PIN Number..." required />
					</div>
          <div class="form-group">
            <label class="control-label">VAT Number</label>
            <input type="text" class="form-control" name="vat_number" value="" placeholder="VAT Number..." required />
          </div>
          <div class="form-group">
            <label class="control-label">Payment Terms</label>
            <input type="text" class="form-control" name="payment_terms" value="" placeholder="Payment Terms..." required />
          </div>
          <div class="form-group">
            <label class="control-label">Payment Methods</label>
            <input type="text" class="form-control" name="payment_method" value="" placeholder="Payment Methods..." required />
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

<?php echo $__env->make('layouts.inventory.layout.app', ['dataTable'=>true, 'select2'=>true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\wamp64\www\polucon\resources\views/layouts/inventory/suppliers/index.blade.php ENDPATH**/ ?>