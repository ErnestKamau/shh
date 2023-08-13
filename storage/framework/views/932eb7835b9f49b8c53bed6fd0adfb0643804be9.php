

<?php $__env->startSection('title2'); ?>
	<title> <?php echo e($supplier->name); ?> | Supplier </title>
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
			font-size: 13px !important;
		}

		.removeThis {
			z-index: 12;
			position: absolute;
			cursor: pointer;
			top: 0px;
			right: 2px;
			padding: 1px 4px;
			font-size: 12px;
			background-color: red;
			border-radius: 50%;
			color: #fff;
			box-shadow: 0px 0px 5px rgba(0,0,0,0.08);
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
          'link' => route('inventory-suppliers'),
          'name' => 'Suppliers',
          'icon' => null
        ),
				array(
          'link' => '#',
          'name' => $supplier->name,
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
			<i class="mdi mdi-mdi-user"></i> <?php echo e($supplier->name); ?> <small class="badge <?php echo e($supplier->average_rating() < 6 ? 'badge-warning' : 'badge-success'); ?>"><?php echo e($supplier->average_rating()); ?><i class="mdi mdi-star"></i> </small> <small class="text-muted"> | Suppliers</small>
		</h2>
		<div class="row no-gutters">
			<div class="col-sm-4 p-2">
				<div class="card">
					<div class="card-body">
						<h5 class="card-title"><i class="mdi mdi-pencil-outline"></i> Edit Supplier</h5>
						<form method="POST" action="<?php echo e(route('edit-inventory-supplier', ['id'=>$supplier->id])); ?>" enctype="multipart/form-data">
							<?php echo csrf_field(); ?>
							<div class="form-group">
								<label class="control-label">Name</label>
								<input type="text" class="form-control" name="name" value="<?php echo e($supplier->name); ?>" placeholder="Name..." required />
							</div>
							<div class="form-group">
								<label class="control-label">Logo</label>
								<div class="p-4">
									<img src="<?php echo e($supplier->logo); ?>" style="width: 100%" />
								</div>
								<input type="file" class="form-control" name="logo"  />
							</div>
							<div class="form-group">
								<label class="control-label">Email</label>
								<input type="email" class="form-control" name="email" value="<?php echo e($supplier->email); ?>" placeholder="Email..." required />
							</div>
							<div class="form-group">
								<label class="control-label">Phone</label>
								<input type="text" class="form-control" name="phone" value="<?php echo e($supplier->phone); ?>" placeholder="Phone..." required />
							</div>
							<div class="form-group">
								<label class="control-label">Building</label>
								<input type="text" class="form-control" name="building" value="<?php echo e($supplier->building); ?>" placeholder="Building..." required />
							</div>
							<div class="form-group">
								<label class="control-label">Street</label>
								<input type="text" class="form-control" name="street" value="<?php echo e($supplier->street); ?>" placeholder="Street..." required />
							</div>
							<div class="form-group">
								<label class="control-label">Town</label>
								<input type="text" class="form-control" name="town" value="<?php echo e($supplier->town); ?>" placeholder="Town..." required />
							</div>
							<div class="form-group">
								<label class="control-label">Address</label>
								<input type="text" class="form-control" name="address" value="<?php echo e($supplier->address); ?>" placeholder="Address..." required />
							</div>
							<div class="form-group">
								<label class="control-label">PIN Number</label>
								<input type="text" class="form-control" name="pin_number" value="<?php echo e($supplier->pin_number); ?>" placeholder="PIN Number..." required />
							</div>
							<div class="form-group">
								<label class="control-label">VAT Number</label>
								<input type="text" class="form-control" name="vat_number" value="<?php echo e($supplier->vat_number); ?>" placeholder="VAT Number..." required />
							</div>
							<div class="form-group">
								<label class="control-label">Payment Terms</label>
								<input type="text" class="form-control" name="payment_terms" value="<?php echo e($supplier->payment_terms); ?>" placeholder="Payment Terms..." required />
							</div>
							<div class="form-group">
								<label class="control-label">Payment Methods</label>
								<input type="text" class="form-control" name="payment_method" value="<?php echo e($supplier->payment_method); ?>" placeholder="Payment Methods..." required />
							</div>
							<div class="form-group">
								<label class="control-label"><input type="checkbox" name="active" value="1" <?php echo e($supplier->active == 1 ? 'checked' : ''); ?> /> Is Active?</label>
							</div>
							<div class="p-0">
								<button type="submit" class="btn btn-primary float-right"><i class="mdi mdi-content-save"></i> Save</button>
							</div>
						</form>
					</div>
				</div>
			</div>
			<div class="col-sm-8 p-2">
				<div class="card tab-card">
					<div class="card-header tab-card-header">
						<ul class="nav nav-tabs card-header-tabs" id="Categories-tabs" role="tablist">
							
							<li class="nav-item">
								<a class="nav-link active" id="Orders-tab" data-toggle="tab" href="#Orders" role="tab" aria-controls="Orders" aria-selected="true">Orders</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" id="Goods-Receipt-tab" data-toggle="tab" href="#Goods-Receipt" role="tab" aria-controls="Orders" aria-selected="true">Goods Receipt</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" id="Goods-Return-tab" data-toggle="tab" href="#Goods-Return" role="tab" aria-controls="Orders" aria-selected="true">Goods Return</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" id="Categories-tab" data-toggle="tab" href="#Categories" role="tab" aria-controls="Categories" aria-selected="true">Supplier Items</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" id="Ratings-tab" data-toggle="tab" href="#Ratings" role="tab" aria-controls="Ratings" aria-selected="true">Ratings</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" id="Contracts-tab" data-toggle="tab" href="#Contracts" role="tab" aria-controls="Contracts" aria-selected="true">Contracts</a>
							</li>
						</ul>
					</div>
					<div class="tab-content" id="Orders-tabs-content">
						<div class="tab-pane fade p-3" id="Contracts" role="tabpanel" aria-labelledby="one-tab">
							<h5 class="card-title">
								Contracts
								<div class="btn btn-sm btn-info float-right" data-target="#add-a-contract" data-toggle="modal"><i class="mdi mdi-plus"></i> Contract</div>
							</h5>
							<div class="table-responsive">
								<table class="table table-condensed my-small-text table-striped server-side table-hover table-bordered table-sm">
									<thead class="bg-light p-2">
										<tr>
											<th>No</th>
											<th>Description</th>
											<th>Item(s)</th>
											<th>Start Date</th>
											<th>End Date</th>
											<th>Status</th>
											<th>Download</th>
											<th></th>
										</tr>
									</thead>
									<tbody>
										<?php $__currentLoopData = $supplier->contracts(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
											<?php
												if(\Carbon\Carbon::parse($item['end']) < \Carbon\Carbon::today() && $item['status'] > 0){
													updateContractStatus($item['id'], 0);
													$item['status'] = 0;
												}
											?>
											<tr>
												<td><?php echo e($loop->iteration); ?></td>
												<td nowrap><?php echo e($item['description']); ?></td>
												<td nowrap><?php echo e(implode(",", array_values($item['items'] ))); ?></td>
												<td><?php echo e($item['start']); ?></td>
												<td><?php echo e($item['end']); ?></td>
												<td><?php echo $item['status'] == 0 ? '<i class="mdi mdi-check-circle text-muted"></i>' : '<i class="mdi mdi-check-circle text-success"></i>'; ?></td>
												<td nowrap><a class="btn btn-xs btn-transparent text-info btn-sm" download href="<?php echo e($item['file']); ?>"><i class="mdi mdi-download"></i></a></td>
												<td nowrap>
													<span class="btn btn-transparent btn text-primary btn-sm" data-toggle="modal" data-target="#edit-contract-modal" data-item='<?php echo e(json_encode($item)); ?>'>
														<i class="mdi mdi-pencil"></i>
													</span>
												</td>
											</tr>
										<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
									</tbody>
								</table>
							</div>
						</div>
						<div class="tab-pane fade p-3" id="Ratings" role="tabpanel" aria-labelledby="one-tab">
							<h5 class="card-title">Ratings </h5>
							<div class="table-responsive">
								<table
									class="table table-condensed my-small-text table-striped server-side table-hover table-bordered table-sm">
									<thead class="bg-light p-2">
										<tr>
											<th>No</th>
											<th>Inventory Item</th>
											<th>Date</th>
											<th>Rating</th>
											<th>Title</th>
											<th>Comments</th>
											<th>Rated By</th>
											<th></th>
										</tr>
									</thead>
									<tbody>
										<?php $__currentLoopData = $supplier->ratings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
											<tr>
												<td><?php echo e($loop->iteration); ?></td>
												<td><?php echo e($item->inventory_item->batch_code ?? ''); ?></td>
												<td><?php echo e($item->inventory_item->created_at ?? ''); ?></td>
												<td><?php echo e($item->rating); ?></td>
												<td><?php echo e($item->title); ?></td>
												<td><?php echo e($item->comments); ?></td>
												<td><?php echo e($item->creator->name); ?></td>
												<td></td>
											</tr>
										<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
									</tbody>
								</table>
							</div>
						</div>
						<div class="tab-pane fade show active p-3" id="Orders" role="tabpanel" aria-labelledby="one-tab">
							
							<div class="table-responsive">
								<table id="server-side-orders" data-parent="Request for Quotation" data-type="Purchase Orders"
									data-url="<?php echo e(route('server-side-purchase-orders', ['field'=>'supplier_id', 'fieldID'=>$supplier->id, 'type'=>'Purchase Orders'])); ?>"
									class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
									<thead class="bg-light p-2">
										<tr>
											<th></th>
											<th>GR Number</th>
											<th>Description</th>
											<th>Due Date</th>
											<th>Source</th>
											<th>Created by</th>
											<th>Date Created</th>
											<th>Status</th>
											<th></th>
										</tr>
									</thead>
									<tbody></tbody>
								</table>
								<?php if(count($supplier->purchase_orders) == 0): ?>
									<div class="alert alert-info">
										<i class="mdi mdi-alert"></i> No Orders added yet.
									</div>
								<?php endif; ?>
							</div>
						</div>
						<div class="tab-pane fade p-3" id="Goods-Receipt" role="tabpanel" aria-labelledby="one-tab">
							<h5 class="card-title">Goods Receipt</h5>
							<div class="table-responsive">
								<table id="server-side-goods-receipt" data-parent="Purchase Orders" data-type="Goods Receipt"
									data-url="<?php echo e(route('server-side-purchase-orders', ['field'=>'supplier_id', 'fieldID'=>$supplier->id, 'type'=>'Goods Receipt'])); ?>"
									class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
									<thead class="bg-light p-2">
										<tr>
											<th>No</th>
											<th>GRN Number</th>
											<th>Description</th>
											<th>Due Date</th>
											<th>Source</th>
											<th>Created by</th>
											<th>Date Created</th>
											<th>Status</th>
											<th></th>
										</tr>
									</thead>
									<tbody></tbody>
								</table>
								<?php if(count($supplier->purchase_orders) == 0): ?>
									<div class="alert alert-info">
										<i class="mdi mdi-alert"></i> No Goods Receipt added yet.
									</div>
								<?php endif; ?>
							</div>
						</div>
						<div class="tab-pane fade p-3" id="Goods-Return" role="tabpanel" aria-labelledby="one-tab">
							<h5 class="card-title">Goods Return</h5>
							<div class="table-responsive">
								<table id="server-side-goods-return" data-parent="Purchase Orders" data-type="Goods Return"
									data-url="<?php echo e(route('server-side-purchase-orders', ['field'=>'supplier_id', 'fieldID'=>$supplier->id, 'type'=>'Goods Return'])); ?>"
									class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
									<thead class="bg-light p-2">
										<tr>
											<th>No</th>
											<th>Order No</th>
											<th>Description</th>
											<th>Due Date</th>
											<th>Source</th>
											<th>Created by</th>
											<th>Date Created</th>
											<th>Status</th>
											<th></th>
										</tr>
									</thead>
									<tbody></tbody>
								</table>
								<?php if(count($supplier->purchase_orders) == 0): ?>
									<div class="alert alert-info">
										<i class="mdi mdi-alert"></i> No Goods Return added yet.
									</div>
								<?php endif; ?>
							</div>
						</div>
						<div class="tab-pane fade p-3" id="Categories" role="tabpanel" aria-labelledby="one-tab">
							<h5 class="card-title">Supplier Items <div class="btn btn-sm btn-info float-right" data-target="#add-supplier-items" data-toggle="modal"><i class="mdi mdi-plus"></i> Add Item</div></h5>
							<hr>
							<div class="table-responsive">
								<table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
									<thead class="bg-light p-2">
										<tr>
											<th>No</th>
											<th nowrap>Image</th>
											<th nowrap>Item</th>
											<th nowrap>Code</th>
											<th nowrap>Brand</th>
											<th></th>
										</tr>
									</thead>
									<tbody>
										<?php $__currentLoopData = $supplier->categories(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
											<tr>
												<td><?php echo e($loop->iteration); ?></td>
												<td data-toggle="modal" style="cursor: pointer;"
													data-target="#view-image-large" data-img="<?php echo e($item->image); ?>">
													<img src="<?php echo e(url($item->image)); ?>" style="max-height: 50px" />
												</td>
												<td><?php echo e($item->item); ?></td>
												<td><?php echo e($item->code); ?></td>
												<td><?php echo e($item->brand); ?></td>
												<td>
													<div class="btn-sm btn-block">
														<button class="btn btn-default text-danger btn-sm" data-toggle="modal" data-target="#delete-supplier-category" data-item="<?php echo e($item->id); ?>"><i class="mdi mdi-delete"></i></button>
													</div>
												</td>
											</tr>
										<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
									</tbody>
								</table>
							</div>
						</div>
						
					</div>
				</div>
			</div>
		</div>
	</main>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script2'); ?>
<div id="add-supplier-items" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<form class="modal-content" method="POST" action="<?php echo e(route('add-supplier-category', ['supplier'=>$supplier->id])); ?>" enctype="multipart/form-data">
			<?php echo csrf_field(); ?>
			<div class="modal-header">
				<h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Supplier Item</h4>
			</div>
			<div class="modal-body">
				
				<div class="form-group">
					<label class="control-label">Item</label>
					<select class="form-control" name="brands[]" required multiple placeholder="Select Item...">
						<option></option>
						<?php $__currentLoopData = getSubCategoriesByBrand(0, true); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat=>$items): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
							<optgroup label="<?php echo e($cat); ?>">
								<?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sub): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
									<option value="<?php echo e($sub['sub_id']); ?>-<?php echo e($sub['brand']); ?>"><?php echo e($sub['name']); ?></option>
								<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
							</optgroup>
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
<div id="delete-supplier-category" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<form class="modal-content" method="POST" enctype="multipart/form-data">
			<?php echo csrf_field(); ?>
			<div class="modal-header">
				<h4 class="modal-title"><i class="mdi mdi-plus"></i> Remove Supplier Item</h4>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<div class="alert alert-callout alert-danger">
						<i class="fas fa-exclamation-triangle"></i> Are you sure that you want to remove this Item?
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-danger"><i class="mdi mdi-trash"></i> Remove</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</form>
	</div>
</div>
<div id="view-image-large" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<div class="modal-content">
			<?php echo csrf_field(); ?>
			<div class="modal-header">
				<h4 class="modal-title"><i class="mdi mdi-file-image"></i> Image Preview</h4>
			</div>
			<div class="modal-body"></div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<div id="edit-an-order" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<form class="modal-content" id="edit-an-order-form" method="POST" enctype="multipart/form-data">
			<?php echo csrf_field(); ?>
			<div class="modal-header">
				<h4 class="modal-title">
					<i class="mdi mdi-pencil"></i> Edit Order <small class="text-muted" id="edit-order-number"></small>
				</h4>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<label class="control-label">Order Comments</label>
					<textarea class="form-control" id="edit_order_comments" name="order_comments" placeholder="Info for the supplier..."></textarea>
				</div>
				<fieldset class="form-group">
					<legend>Order Items</legend>
					<div id="edit-an-order-msg"></div>
					<div class="form-group add-category-btn" style="margin-top: 12px">
						<button type="button" class="btn btn-outline-info btn-sm btn-block">
							<i class="mdi mdi-plus text-primary"></i> Add Order Category
						</button>
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
<div id="accept-goods" class="modal fade" role="dialog">
	<div class="modal-dialog modal-lg">
		<!-- Modal content-->
		<form class="modal-content" id="accept-goods-form" method="POST" enctype="multipart/form-data">
			<?php echo csrf_field(); ?>
			<div class="modal-header">
				<h4 class="modal-title">
					<i class="mdi mdi-dolly"></i> Accept Order <small class="text-muted">(<span id="accept-goods-order-number"></span>)</small> Items
				</h4>
			</div>
			<div class="modal-body" id="accept-goods-items"></div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</form>
	</div>
</div>
<div id="add-a-contract" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<form class="modal-content" action="<?php echo e(route("create-supplier-contract", ['supplier'=>$supplier->id])); ?>" id="add-a-contract-form" method="POST" enctype="multipart/form-data">
			<?php echo csrf_field(); ?>
			<div class="modal-header">
				<h4 class="modal-title">
					<i class="mdi mdi-pencil"></i> Enter Contract Details
				</h4>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<label class="control-label">Description</label>
					<input type="text" class="form-control" name="description" placeholder="Description..." />
				</div>
				<div class="form-group">
					<label class="control-label">Items</label>
					<select class="form-control" name="item[]" placeholder="Select Items" multiple>
						<option value="">Select Item...</option>
						<?php $__currentLoopData = $cats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
							<optgroup label="<?php echo e($item->name); ?>">
								<?php $__currentLoopData = $item->subcategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $it): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
									<option value="<?php echo e($it->id); ?>"><em><?php echo e($item->name); ?></em> > <?php echo e($it->name); ?></option>
								<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
							</optgroup>
						<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
					</select>
				</div>
				<div class="form-group">
					<label class="control-label">Start Date</label>
					<input type="date" class="form-control" name="start" placeholder="Contract Start Date..." required />
				</div>
				<div class="form-group">
					<label class="control-label">End Date</label>
					<input type="date" class="form-control" name="end" placeholder="Contract End Date..." required />
				</div>
				<div class="form-group">
					<label class="control-label">File</label>
					<input type="file" class="form-control" name="file" required />
				</div>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</form>
	</div>
</div>
<div id="edit-contract-modal" class="modal fade" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<form class="modal-content" id="edit-contract-form" method="POST" enctype="multipart/form-data">
			<?php echo csrf_field(); ?>
			<div class="modal-header">
				<h4 class="modal-title">
					<i class="mdi mdi-pencil"></i> Edit Contract Details
				</h4>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<label class="control-label">Description</label>
					<input type="text" class="form-control" name="description" placeholder="Description..." />
				</div>
				<div class="form-group">
					<label class="control-label">Items</label>
					<select class="form-control" name="item[]" placeholder="Select Items" multiple>
						<option value="">Select Item...</option>
						<?php $__currentLoopData = $cats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
							<optgroup label="<?php echo e($item->name); ?>">
								<?php $__currentLoopData = $item->subcategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $it): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
									<option value="<?php echo e($it->id); ?>"><em><?php echo e($item->name); ?></em> > <?php echo e($it->name); ?></option>
								<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
							</optgroup>
						<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
					</select>
				</div>
				<div class="form-group">
					<label class="control-label">Start Date</label>
					<input type="date" class="form-control" name="start" placeholder="Contract Start Date..." required />
				</div>
				<div class="form-group">
					<label class="control-label">End Date</label>
					<input type="date" class="form-control" name="end" placeholder="Contract End Date..." required />
				</div>
				<div class="form-group">
					<label class="control-label">File <small class="text-muted">* Leave blank if you do not want to update document</small></label>
					<input type="file" class="form-control" name="file" />
				</div>
				<div class="form-group">
					<label class="control-label"><input type="checkbox" name="status" value="1" /> Is Active?</label>
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
	var rowHTML = `<div class="row dynamic-row" style="margin-top: 12px; position: relative">
		<div class="col-sm-7">
			<input type="hidden" name="items[order_item_id][]" />
			<label class="control-label">Category <small class="fulfilled-msg text-info"></small></label>
			<select class="form-control" name="items[sub_category_id][]" required>
				<option value="">Select Category...</option>
				<?php $__currentLoopData = $cats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
					<optgroup label="<?php echo e($item->name); ?>">
						<?php $__currentLoopData = $item->subcategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $it): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
							<option value="<?php echo e($item->id); ?> <?php echo e($it->id); ?>"><em><?php echo e($item->name); ?></em> > <?php echo e($it->name); ?></option>
						<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
					</optgroup>
				<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
			</select>
		</div>
		<div class="col-sm-5">
			<label class="control-label">Quantity <small class="fulfilled-msg text-info"></small></label>
			<input type="number" min="0" class="form-control" name="items[quantity][]" placeholder="Enter Quantity" required />
		</div>
		<span class="removeThis"><i class="mdi mdi-delete"></i></span>
	</div>`;

	var rowReceivedHTML = `<div class="row dynamic-row" style="margin-top: 12px; position: relative">
		<div class="col-sm-4">
			<input type="hidden" name="items[order_item_id][]" />
			<label class="control-label">Category <small class="fulfilled-msg text-info"></small></label>
			<select class="form-control" name="items[sub_category_id][]" required>
				<option value="">Select Category...</option>
				<?php $__currentLoopData = $cats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
					<optgroup label="<?php echo e($item->name); ?>">
						<?php $__currentLoopData = $item->subcategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $it): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
							<option value="<?php echo e($item->id); ?> <?php echo e($it->id); ?>"><em><?php echo e($item->name); ?></em> > <?php echo e($it->name); ?></option>
						<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
					</optgroup>
				<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
			</select>
		</div>
		<div class="col-sm-4">
			<label class="control-label">Quantity <small class="fulfilled-msg text-info"></small></label>
			<input type="number" min="0" class="form-control" name="items[quantity][]" placeholder="Enter Quantity" required />
		</div>
		<div class="col-sm-4">
			<label class="control-label">Price <small class="fulfilled-msg text-info"></small></label>
			<input type="number" min="0" class="form-control" name="items[price][]" placeholder="Enter Price" required />
		</div>
		<div class="col-sm-4">
			<label class="control-label">Expiry <small class="fulfilled-msg text-info"></small></label>
			<input type="date" min="0" value="2099-12-31" class="form-control" name="items[expiry][]" placeholder="Expiry date..." required />
		</div>
		<div class="col-sm-4">
			<label class="control-label">Slot <small class="fulfilled-msg text-info"></small></label>
			<select class="form-control" name="items[slot][]" required placeholder="Select Slot" required>
				<option></option>
				<?php $__currentLoopData = $stores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
					<optgroup label="<?php echo e($item->name); ?>">
						<?php $__currentLoopData = $item->slots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $it): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
							<option value="<?php echo e($item->id); ?> <?php echo e($it->id); ?>"><?php echo e($it->name); ?></option>
						<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
					</optgroup>
				<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
			</select>
		</div>
		<div class="col-sm-2 checkbox-holder pt-1">
			<label class="control-label">Receive </label><br>
			<input type="hidden" name="items[receive][]" value="0">
			<input type="checkbox" class="items-receive">
		</div>
		<div class="col-sm-2 qc-holder pt-1">
			<label class="control-label" title="Requires Quality Control">Quality Control</label><br>
			<input type="hidden" name="items[requires_qc][]" value="0">
			<input type="checkbox" class="items-requires_qc">
		</div>
	</div>`;

	$(function(){
		//triggered when edit-an-order modal is about to be shown
		$('#accept-goods').on('show.bs.modal', function(e) {

			//get data-id attribute of the clicked element
			var orderDetails = $(e.relatedTarget).data('order');
			$('#accept-goods-order-number').text(orderDetails.order_number);

			$('#accept-goods-form').prop('action', '/accept-order-items/'+orderDetails.id);

			$.ajax({
				url: "/get-order-items/inventory_order_id/"+orderDetails.id,
				dataType: "json",
				beforeSend: function(){
					$('#accept-goods-items').append(`<div class="alert alert-primary">
						<i class="fas fa-spin fa-spinner"></i> Loading order items...
					</div>`);
				},
				success: function(js){
					$('#accept-goods-items').empty();

					$.each(js, function(j,s){
						createNewReceivedRow(s);
					});

				}
			})
		});

		$('#view-image-large').on('show.bs.modal', function(e){
			var img = $(e.relatedTarget).data('img');

			$(this).find('.modal-body').html(`<img src="${img}" style="width:100%" />`);
		});

		$('#delete-supplier-category').on('show.bs.modal', function(e){
			var $item = $(e.relatedTarget).data('item');

			var form = $(this).find('form');

			form.prop('action', '/delete-supplier-category/'+$item);
			form.attr('action', '/delete-supplier-category/'+$item);
		});

		$('#edit-contract-modal').on('show.bs.modal', function(e) {
			var $item = $(e.relatedTarget).data('item');

			var editForm = $('#edit-contract-form');

			$('#edit-contract-form').prop('action', '/edit-supplier-contract/<?php echo e($supplier->id); ?>/'+$item.id);

			editForm.find('[name="description"]').val($item.description);
			editForm.find('[name="start"]').val($item.start);
			editForm.find('[name="end"]').val($item.end);
			editForm.find('[name="status"]').prop('checked', ($item.status==1));

			var items = Object.keys($item.items);

			$('#edit-contract-form').find('[name="item[]"]').val(items).trigger('change');
		});

		$('#edit-an-order').on('show.bs.modal', function(e) {

			//get data-id attribute of the clicked element
			var orderDetails = $(e.relatedTarget).data('order');
			$('#edit-order-number').text(" | "+orderDetails.order_number);
			$('#edit_order_comments').val(orderDetails.comments);
			$('#edit-an-order-form').prop('action', '/edit-order/<?php echo e($supplier->id); ?>/'+orderDetails.id);

			$.ajax({
				url: "/get-order-items/inventory_order_id/"+orderDetails.id,
				dataType: "json",
				beforeSend: function(){
					$('#edit-an-order-msg').html(`<div class="alert alert-primary">
						<i class="fas fa-spin fa-spinner"></i> Loading order items...
					</div>`);

					$('#edit-an-order').find('.dynamic-row').remove();
				},
				success: function(js){
					$('#edit-an-order-msg').html('');

					$.each(js, function(j,s){
						createNewRow($('#edit-an-order-form').find('.add-category-btn'), s);
					});

				}
			})
		});

		var createNewReceivedRow = function(data=false){
			var $row = $(rowReceivedHTML).clone(true, true);

			$receivedprop = data.fulfilled == 1 ? 'disabled' : 'readonly';

			if($receivedprop == 'disabled'){
				$row.find('.fulfilled-msg').text('Fulfilled');
				$row.find('.qc-holder').remove();
				$row.find('.checkbox-holder').html(`
					<i class="fas fa-check-circle text-success mt-4"></i> Items Delivered
				`).removeClass('col-sm-2 col-sm-4');
			}

			$row.find('.items-requires_qc').on('click', function(){
				if($(this).is(":checked")){
					$row.find('[name="items[requires_qc][]"]').val(1);
				}
				else{
					$row.find('[name="items[requires_qc][]"]').val(0);
				}
			});

			$row.find('.items-receive').on('click', function(){
				if($(this).is(":checked")){
					$row.find('[name="items[receive][]"]').val(1);
				}
				else{
					$row.find('[name="items[receive][]"]').val(0);
				}
			});

			$row.find('[name="items[order_item_id][]"]').val(data.id).prop($receivedprop, true);
			$row.find('[name="items[quantity][]"]').val(data.quantity).prop($receivedprop, true);
			$row.find('[name="items[sub_category_id][]"]').val(data.inventory_category_id+" "+data.inventory_sub_category_id).prop($receivedprop, true).trigger('change');

			$row.find('select').select2();

			$('#accept-goods-items').append($row);
		}

		var createNewRow = function($ts=false,data=false){
			var $row = $(rowHTML).clone(true, true);

			$row.find('.removeThis').on('click', function(){
				if(confirm("Are you sure you want to delete this?")){
					if(data.id){
						$.ajax({
							url: "/delete-order-items/"+data.id,
							dataType: "json",
							method: "POST",
							success: function(js){
								if(js.message){
									alert(js.message);
								}

								if(js.status){
									$row.remove();
								}
							}
						})
					}
					else{
						$row.remove();
					}
				}
			});

			if(data){
				$row.find('[name="items[order_item_id][]"]').val(data.id);
				$row.find('[name="items[quantity][]"]').val(data.quantity);
				$row.find('[name="items[sub_category_id][]"]').val(data.inventory_category_id+" "+data.inventory_sub_category_id).trigger('change');

				if(data.fulfilled != 0){
					$row.find('.fulfilled-msg').text('Fulfilled');
					$row.find('[name="items[quantity][]"]').prop('readonly', true);
					$row.find('[name="items[sub_category_id][]"]').prop('readonly', true);
				}

			}

			$row.find('select').select2();

			$ts.before($row);
		}


		$('.add-category-btn').on('click', function(){
			createNewRow($(this), false);
		});

		var tables = ['#server-side-goods-receipt', '#server-side-goods-return', '#server-side-orders'];

		$.each(tables, function(t, tb){
			var $url = $(tb).data('url');
			var parent = $(tb).data('parent');
			var ctype = $(tb).data('type');
			var serverTable = $(tb).DataTable({
				lengthMenu: [[10, 25, 50, 100, 500, 1000, -1], [10, 25, 50, 100, 500, 1000, "All"]],
				dom: 'Blfrtip',
				buttons: [
					'copy', 'csv', 'excel', 'pdf', 'print'
				],
				columns: [
					{ data: "loop", "searchable": false },
					{ data: "request_code" },
					{ data: "description" },
					{ data: "due_date" },
					{
						data: null,
						className: "center",
						render: function ( data, type, row ) {
							$(row).find('td:eq(4)').attr('nowrap');
							$(row).find('td:eq(4)').prop('nowrap');
							return `<a class="btn btn-xs text-info btn-transparent" href='/req/${parent}/${data.parent_id}'>${data.parent_request_code}</a>
							`;
						}
					},
					{ data: "created_by" },
					{ data: "created_at" },
					{ data: "status" },
					{
						data: null,
						className: "center",
						render: function ( data, type, row ) {
							$(row).find('td:eq(8)').attr('nowrap');
							$(row).find('td:eq(8)').prop('nowrap');
							return `<a class="btn btn-xs text-primary btn-transparent" href='/req/${ctype}/${data.id}'><i class="mdi mdi-eye"></i></a>
							`;
						}
					}
				],
				destroy: true,
				processing: true,
				serverSide: true,
				ajax: $url
			});
		});
	});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.inventory.layout.app', ['dataTable'=>true, 'select2'=>true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\wamp64\www\polucon\resources\views/layouts/inventory/suppliers/show.blade.php ENDPATH**/ ?>