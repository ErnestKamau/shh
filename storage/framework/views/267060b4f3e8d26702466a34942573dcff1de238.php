

<?php $__env->startSection('title2'); ?>
  <title>Approval Requests | Inventory Management</title>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content2'); ?>
  <main>
		<?php
			$stages = getRequisitionWorkflow();
      $items = array(
        array(
          'link' => route('inventory-home'),
          'name' => 'Inventory Management',
          'icon' => null
        ),
        array(
          'link' => null,
          'name' => "Approval Requests",
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
      <i class="mdi mdi-format-list-checks"></i> Approval Requests
		</h3>
		<div class="bg-light">
			<div class="table-responsive">
				<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
					<thead>
						<tr>
							<th>#</th>
							<th nowrap>Code</th>
							<th nowrap>Type</th>
							<th nowrap>Priority</th>
							<th nowrap>Description</th>
							<th nowrap>Due Date</th>
						</tr>
					</thead>
					<tbody>
						<?php $__currentLoopData = $approvals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
							<tr>
								<td><?php echo e($loop->iteration); ?></td>
								<td nowrap>
									<a href="<?php echo e(route('view-request-details', ['stage'=>$a->request_type, 'id'=>$a->model_id])); ?>">
										<?php echo e($a->request_code); ?>

									</a>
								</td>
								<td nowrap><?php echo e($a->request_type); ?></td>
								<td nowrap><?php echo e($a->priority); ?></td>
								<td nowrap><?php echo e($a->description ?? 'No items set'); ?></td>
								<td nowrap><?php echo e($a->due_date); ?></td>
							</tr>
						<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
					</tbody>
				</table>
			</div>
		</div>
  </main>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.inventory.layout.app', ['dataTable'=>true, 'select2'=>true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\wamp64\www\polucon\resources\views/layouts/inventory/approvals/index.blade.php ENDPATH**/ ?>