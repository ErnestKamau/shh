

<?php $__env->startSection('title2'); ?>
<title>Inventory Movement</title>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content2'); ?>

<main>
	<?php
		$iitems = array(
			array(
				'link' => route('inventory-home'),
				'name' => 'Inventory Management',
				'icon' => null
			),
			array(
				'link' => route('inventory-activity'),
				'name' => 'Inventory Movement',
				'icon' => null
			)
		);
	?>
	 <?php if (isset($component)) { $__componentOriginal30091868428b09767320233ef70f89faadea10d9 = $component; } ?>
<?php $component = $__env->getContainer()->make(App\View\Components\BreadCrumb::class, ['items' => $iitems]); ?>
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
		<i class="mdi mdi-format-list-bulleted-type"></i>Inventory Movement
	</h2>
	<br>
	<div class="table-responsive bg-light p-4">
		<table id="inventory-movement" data-url="<?php echo e(route('get-stock-movement')); ?>" class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
			<thead class="bg-light p-2">
				<tr>
					<th>No</th>
					<th>Category</th>
					<th>Code</th>
					<th>Item</th>
					<th>Brand</th>
					<th>Store</th>
					<th>Slot</th>
					<th>Department</th>
					<th><?php echo e(__('Stock In')); ?></th>
					<th><?php echo e(__('Stock Out')); ?></th>
					<th>Type</th>
					<th>Date</th>
				</tr>
			</thead>
			<tbody>
				<?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
					<tr>
						<td><?php echo e($loop->iteration); ?></td>
						<td nowrap><?php echo e($item->category); ?></td>
						<td nowrap><?php echo e($item->code); ?></td>
						<td nowrap><?php echo e($item->sub_category); ?></td>
						<td nowrap><?php echo e($item->brand); ?></td>
						<td nowrap><?php echo e($item->store); ?></td>
						<td nowrap><?php echo e($item->slot); ?></td>
						<td nowrap><?php echo e($item->department); ?></td>
						<td nowrap><?php echo e(number_format($item->stock_in ?? 0)); ?> <small class="text-muted"><?php echo e($item->unit_type); ?></small> <?php echo e($item->status); ?> <?php echo $item->status == 'pending' ? '<small class="text-muted">Pending</small>' : ''; ?></td>
						<td nowrap><?php echo e(number_format($item->stock_out ?? 0)); ?> <small class="text-muted"><?php echo e($item->unit_type); ?></small></td>
						<td nowrap><?php echo e($item->entity_code); ?></td>
						<td nowrap><?php echo e($item->created_at); ?></td>
					</tr>
				<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
			</tbody>
		</table>
	</div>
</main>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script2'); ?>
<script>
	$(function(){
		// var tables = ["#inventory-movement"];
		// $.each(tables, function(t, tb){
		// 	var $url = $(tb).data('url');
		// 	var serverTable = $(tb).DataTable({
		// 		lengthMenu: [[10, 25, 50, 100, 500, 1000, -1], [10, 25, 50, 100, 500, 1000, "All"]],
		// 		dom: 'Blfrtip',
		// 		buttons: [
		// 			'copy', 'csv', 'excel', 'pdf', 'print'
		// 		],
		// 		columns: [
		// 			{ data: "loop", "searchable": false },
		// 			{ data: "request_code" },
		// 			{ data: "description" },
		// 			{ data: "due_date" },
		// 			{
		// 				data: null,
		// 				className: "center",
		// 				render: function ( data, type, row ) {
		// 					$(row).find('td:eq(4)').attr('nowrap');
		// 					$(row).find('td:eq(4)').prop('nowrap');
		// 					return `<a class="btn btn-xs text-info btn-transparent" href='/req/${parent}/${data.parent_id}'>${data.parent_request_code}</a>
		// 					`;
		// 				}
		// 			},
		// 			{ data: "created_by" },
		// 			{ data: "created_at" },
		// 			{ data: "status" },
		// 			{
		// 				data: null,
		// 				className: "center",
		// 				render: function ( data, type, row ) {
		// 					$(row).find('td:eq(8)').attr('nowrap');
		// 					$(row).find('td:eq(8)').prop('nowrap');
		// 					return `<a class="btn btn-xs text-primary btn-transparent" href='/req/${ctype}/${data.id}'><i class="mdi mdi-eye"></i></a>
		// 					`;
		// 				}
		// 			}
		// 		],
		// 		destroy: true,
		// 		processing: true,
		// 		serverSide: true,
		// 		ajax: $url
		// 	});
		// });
	});

</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.inventory.layout.app', ['dataTable'=>true, 'select2'=>true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\wamp64\www\polucon\resources\views/layouts/inventory/activity/index.blade.php ENDPATH**/ ?>