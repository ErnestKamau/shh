

<?php $__env->startSection('title2'); ?>
	<title> <?php echo e($category->name); ?> | Inventory Category</title>
	<style type="text/css">
		.tab-card {
			border:1px solid #eee;
		}

		.tab-card-header {
			background:none;
		}
		/* Default mode */
		.tab-card-header > .nav-tabs {
			border: none;
			margin: 0px;
		}
		.tab-card-header > .nav-tabs > li {
			margin-right: 2px;
		}
		.tab-card-header > .nav-tabs > li > a {
			border: 0;
			border-bottom:2px solid transparent;
			margin-right: 0;
			color: #737373;
			padding: 2px 15px;
		}

		.tab-card-header > .nav-tabs > li > a.show {
			border-bottom:2px solid #007bff;
			color: #007bff;
		}
		.tab-card-header > .nav-tabs > li > a:hover {
			color: #007bff;
		}

		.tab-card .nav-link.active{
			background-color: #dadccd !important;
			border: 1px solid #cccebf !important;
		}

		.tab-card-header > .tab-content {
			padding-bottom: 0;
		}

		.my-small-text{
			font-size: 12px !important;
		}

	</style>
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
          'link' => route('inventory-categories'),
          'name' => 'Categories',
          'icon' => null
        ),
				array(
          'link' => '#',
          'name' => $category->name,
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
			<i class="mdi mdi-format-list-bulleted"></i> Inventory Items
			<div class="btn btn-sm btn-transparent text-primary float-right m-2" data-target="#edit-category-modal" data-toggle="modal"><i class="mdi mdi-share"></i> Edit</div>
			<div class="btn btn-sm btn-transparent text-success float-right m-2" data-target="#add-sub-category" data-toggle="modal"><i class="mdi mdi-plus"></i> Add Item</div>
		</h3>
		<div class="p-4 bg-light">
			<div class="table-responsive">
				<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
					<thead class="bg-light p-2">
						<tr>
							<th>No</th>
							<th nowrap>Image</th>
							<th nowrap>Name</th>
							<th nowrap>Cat/Lot No</th>
							<th nowrap>Sub Category</th>
							<th nowrap>Stock</th>
							<th nowrap>Classification</th>
							<th nowrap>Maximum Order Quantity</th>
							<th nowrap>UoM</th>
							<th nowrap>Secondary UoM</th>
							<th nowrap>Cash Price</th>
							<th nowrap>Credit Price</th>
							<th nowrap>Description</th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						<?php if($category->subcategories->count() > 0): ?>
							<?php $__currentLoopData = $category->subcategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $element): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
								<tr>
									<td valign="center"><?php echo e($loop->iteration); ?></td>
									<td><img src="<?php echo e($element->image); ?>" style="width: 100px" /></td>
									<td nowrap>
									<a class="" href="<?php echo e(route('show-inventory-items', ['category'=>$category->id,'id'=>$element->id])); ?>"> <?php echo e($element->name); ?></a>
									</td>
									<td><?php echo e($element->sap_code ?? '-'); ?></small></td>
									<td><?php echo e(getsystemconfigbyid($element->sub_category_id)->value ?? '-'); ?></td>
									<td nowrap>
										<span class="badge badge-primary" style="margin-right: 10px; padding: 3px 5px !important; font-size: 11px!important">
											<?php echo e(number_format($element->available_stock)); ?> In Stock
										</span>
										<?php if($element->requires_reorder == 1): ?>
											<a class="badge badge-danger" href="<?php echo e(route('go_to_stage', ['stage'=>'Material Requisition'])); ?>"><i class="mdi mdi-cart-arrow-down"></i> ReOrder</a>
										<?php endif; ?>
									</td>
									<td><?php echo e(getInventoryItemClassification($element->item_classification ?? 0)); ?></td>
									<td><?php echo e($element->maximum_order_quantity); ?></td>
									<td><?php echo e($element->unit_type); ?></td>
									<td><?php echo e($element->secondary_unit_type); ?></td>
									<td><?php echo e($element->unit_price); ?></td>
									<td><?php echo e($element->unit_price_credit); ?></td>
									<td><?php echo e($element->description); ?></td>
									<td nowrap>
										
										<a class="btn btn-success btn-sm" href="<?php echo e(route('show-inventory-items', ['category'=>$category->id,'id'=>$element->id])); ?>"><i class="mdi mdi-eye-outline"></i> <small class="hidden-sm-up">Show</small> </a>
									</td>
								</tr>
							<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
						<?php endif; ?>
					</tbody>
				</table>
				<?php if($category->subcategories->count() == 0): ?>
					<div class="alert alert-info">
						<i class="mdi mdi-alert"></i> No Sub Categories added yet.
					</div>
				<?php endif; ?>
			</div>
		</div>
	</main>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script2'); ?>
<div id="edit-category-modal" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<form class="modal-content" method="POST" action="<?php echo e(route('edit-inventory-category', ['id'=>$category->id])); ?>" enctype="multipart/form-data">
			<?php echo csrf_field(); ?>
			<input type="hidden" name="category_id" value="<?php echo e($category->id); ?>" />
			<div class="modal-header">
				<h4 class="modal-title"><i class="mdi mdi-pencil"></i> Edit Category</h4>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<label class="control-label">Name</label>
					<input type="text" class="form-control" name="name" value="<?php echo e($category->name); ?>" placeholder="Name..." required />
				</div>
				<div class="form-group">
					<label class="control-label">Description</label>
					<textarea class="form-control" name="description" placeholder="Description..." required><?php echo e($category->description); ?></textarea>
				</div>
				<div class="form-group">
					<div class="row">
						<div class="col-sm-4">
							<img src="<?php echo e($category->image); ?>" style="width: 100%" />
						</div>
						<div class="col-sm-8">
							<label class="control-label">Image</label>
							<input type="file" class="form-control" name="image"  />
						</div>
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
<div id="add-sub-category" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<form class="modal-content" method="POST" action="<?php echo e(route('add-inventory-sub-category')); ?>" enctype="multipart/form-data">
			<?php echo csrf_field(); ?>
			<input type="hidden" name="category_id" value="<?php echo e($category->id); ?>" />
			<div class="modal-header">
				<h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Item</h4>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<label class="control-label">Name</label>
					<input type="text" class="form-control" name="name" value="" placeholder="Name..." required />
				</div>
				<div class="form-group">
					<label class="control-label">CAT/Lot No</label>
					<input type="text" class="form-control" name="sap_code" placeholder="SAP Code..." />
				</div>
				<div class="form-group">
					<label class="control-label">Description</label>
					<textarea class="form-control" name="description" placeholder="Description..." required></textarea>
				</div>
				<div class="form-group">
					<label class="control-label">Sub Categories</label>
					<select name="sub_category_id" id="" class="form-control">
						<option value=""> Select Sub Category</option>
						<?php $__currentLoopData = getInventorySubs(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sub): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
	  					<option value="<?php echo e($sub->id); ?>"><?php echo e($sub->value); ?></option>
						<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
					</select>
				</div>
				<div class="form-group">
					<label class="control-label">Image</label>
					<input type="file" class="form-control" name="image"  />
				</div>
				<div class="form-group">
					<label class="control-label">Manufacturer</label>
					<input type="text" class="form-control" name="manufacturer" value="" placeholder="Manufacturer..." />
				</div>
				<div class="form-group">
					<label class="control-label">Maximum Order Quantity</label>
					<input type="number" min="0" class="form-control" name="maximum_order_quantity" value="" placeholder="Maximum Order Quantity..." required />
				</div>
				<div class="form-group">
					<label class="control-label">Item Classification</label>
					<select class="form-control" name="item_classification">
						<option value="">Select Item Classification...</option>
						<?php $__currentLoopData = getInventoryItemClassification(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g=>$c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
							<option value="<?php echo e($g); ?>"><?php echo e($c); ?></option>
						<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
					</select>
				</div>
				<div class="form-group">
					<label class="control-label">Unit of Measure</label>
					<select class="form-control" name="unit_type">
						<option value="">Select Unit of Measure...</option>
						<?php $__currentLoopData = getReportingUnits(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
							<option value="<?php echo e($g['name']); ?>"><?php echo e($g['name']); ?></option>
						<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
					</select>
				</div>
				<div class="form-group">
					<label class="control-label">Issuing Unit of Measure</label>
					<select class="form-control" name="secondary_unit_type">
						<option value="">Select Unit of Measure...</option>
						<?php $__currentLoopData = getReportingUnits(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
							<option value="<?php echo e($g['name']); ?>"><?php echo e($g['name']); ?></option>
						<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
					</select>
				</div>
				<div class="form-group">
					<label class="control-label">Cash Price</label>
					<input type="number" min="0" class="form-control" name="unit_price" value="" placeholder="Cash Price..."/>
				</div>
				<div class="form-group">
					<label class="control-label">Credit Price</label>
					<input type="number" min="0" class="form-control" name="unit_price_credit" value="" placeholder="Credit Price..."/>
				</div>
				<div class="form-group">
					<label class="control-label">Annual Consumption</label>
					<input type="number" min="0" class="form-control" name="annual_consumption" placeholder="Annual Consumption..." required />
				</div>
				<div class="form-group">
					<label class="control-label">Working Days</label>
					<input type="number" min="0" class="form-control" name="working_days" placeholder="Working Days..." required />
				</div>
				<div class="form-group">
					<label class="control-label">Estimated variation in demand as a %of average consumption</label>
					<input type="number" class="form-control" name="estimated_variation_in_demand_average_consumption" placeholder="Estimated variation in demand as a %of average consumption..." required />
				</div>
				<div class="form-group">
					<label class="control-label">Internal Lead Time</label>
					<input type="number" class="form-control" name="internal_lead_time" placeholder="Internal Lead Time..." required />
				</div>
				<div class="form-group">
					<label class="control-label">External Lead Time</label>
					<input type="number" class="form-control" name="external_lead_time" placeholder="External Lead Time..." required />
				</div>
				<div class="form-group">
					<label class="control-label">Material Type</label>
					<select class="form-control" name="material_type_id" placeholder="Select Material Type...">
						<option value="0">Non Specific</option>
						<?php $__currentLoopData = getModulePreconfig('Material Type', 'Inventory-Management'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $material_type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
							<option value="<?php echo e($material_type['id']); ?>"><?php echo e($material_type['name']); ?></option>
						<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
					</select>
				</div>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</form>
	</div>
</div>
<div id="create-an-order" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<form class="modal-content" method="POST" action="<?php echo e(route('create-order')); ?>" enctype="multipart/form-data">
			<?php echo csrf_field(); ?>
			<div class="modal-header">
				<h4 class="modal-title"><i class="mdi mdi-plus"></i> Create an Order</h4>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<label class="control-label">Supplier</label>
					<select name="supplier_id" class="form-control select2" placeholder="Select Supplier..." required>
						<option></option>
					</select>
				</div>
				<div class="form-group">
					<label class="control-label">Order Comments</label>
					<textarea class="form-control" name="order_comments" placeholder="Info for the supplier..."></textarea>
				</div>
				<fieldset class="form-group">
					<legend>Order Items</legend>
					<div class="row">
						<div class="col-sm-12">
							<label class="control-label">Quantity</label>
							<input type="hidden" name="items[sub_category_id][]" />
							<input type="number" min="0" class="form-control" name="items[quantity][]" placeholder="Enter Quantity" required />
						</div>
					</div>
				</fieldset>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</form>
	</div>
</div>
<script type="text/javascript">
	$(function(){
		$('#create-an-order').on('show.bs.modal', function(e) {
			var subCat = <?php echo e($category->id); ?>+" "+$(e.relatedTarget).data('subid');

			$('#create-an-order').find('[name="items[sub_category_id][]"]').val(subCat);
		});

		$('.set-sub-suppliers').on('click', function(){
			var $suppliers = $(this).data('suppliers');
			console.log($suppliers);
			$('[name="supplier_id"]').html(`<option></option>`);

			$.each($suppliers, function(s, sup){
				$('[name="supplier_id"]').append(`<option value="${sup.id}">${sup.name}</option>`);
			});
		});
	});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.inventory.layout.app', ['dataTable'=>true, 'select2'=>true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\wamp64\www\polucon\resources\views/layouts/inventory/categories/show.blade.php ENDPATH**/ ?>