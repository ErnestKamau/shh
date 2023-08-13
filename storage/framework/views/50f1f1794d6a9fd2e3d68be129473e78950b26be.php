<?php $__env->startSection('module-name'); ?>
<li class="nav-item">
  <a class="nav-link module-name" href="<?php echo e(route('inventory-home')); ?>"><i class="mdi mdi-package-variant"></i> Inventory Management</a>
</li>
<li class="nav-item pt-1">
	<div class="btn-group mt-2">
		<button class="btn btn-transparent btn-sm dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
			<i class="mdi mdi-map-marker"></i>
			<?php if(getCurrentUserLocation()): ?>
				Location <small class="text-muted"> > </small> <?php echo e(getCurrentUserLocation()->name); ?>

			<?php else: ?>
				Select Location
			<?php endif; ?>
		</button>
		<div class="dropdown-menu" id="location-selector">
			<?php $__currentLoopData = viewableLocations(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key=>$item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
				<?php if(isset($item->level)): ?>
					<a class="dropdown-item" href="<?php echo e(route('set-user-location', ['id'=>$item->id])); ?>">
						<?php echo e($key); ?>

					</a>
				<?php else: ?>
					<?php $__currentLoopData = $item; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key1=>$item1): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
						<?php if(isset($item1->level)): ?>
							<a class="dropdown-item" href="<?php echo e(route('set-user-location', ['id'=>$item1->id])); ?>">
								<?php echo e($key); ?> <small class="text-muted"> > </small> <?php echo e($key1); ?>

							</a>
						<?php else: ?>
							<?php $__currentLoopData = $item1; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key2=>$item2): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
								<?php if(isset($item2->level)): ?>
									<a class="dropdown-item" href="<?php echo e(route('set-user-location', ['id'=>$item2->id])); ?>">
										<?php echo e($key); ?> <small class="text-muted"> > </small> <?php echo e($key1); ?> <small class="text-muted"> > </small> <?php echo e($key2); ?>

									</a>
								<?php else: ?>
									<?php $__currentLoopData = $item2; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key3=>$item3): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
										<a class="dropdown-item" href="<?php echo e(route('set-user-location', ['id'=>$item3->id])); ?>">
											<?php echo e($key); ?> <small class="text-muted"> > </small> <?php echo e($key1); ?> <small class="text-muted"> > </small> <?php echo e($key2); ?> <small class="text-muted"> > </small> <?php echo e($key3); ?>

										</a>
									<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
								<?php endif; ?>
							<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
						<?php endif; ?>
					<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
					<div class="dropdown-divider"></div>
				<?php endif; ?>
			<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
		</div>
	</div>
</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('alerts'); ?>
<li class="nav-item dropdown">
	<a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
		<i class="mdi mdi-bell-ring"></i>
		<?php
			$alertsArray = array();
			$alerters = 0;
			$restockAlerts = getRestockNotifications(true);
			$alerts = 0;

			if(intval($restockAlerts['count']) > 0){
				$alerters+=1;
				$alerts += intval($restockAlerts['count']);
				$alertsArray['Restock'] = $restockAlerts;
			}
		?>
		Alerts <?php echo $alerts > 0 ? '<small class="badge badge-danger badge">'.number_format($alerts).' '.($alerts == 10 ? '+' : '').'</small>' : ''; ?>

	</a>
	<div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton" style="width: 250px;overflow-x: hidden; ">
		<?php $__currentLoopData = $alertsArray; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alert=>$data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
			<?php if($data['count'] > 0): ?>
				<span class="dropdown-header" style="text-overflow: ellipsis; whitespace: nowrap"><?php echo e($alert); ?> Alerts</span>
				<?php $__currentLoopData = $data['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
					<a class="dropdown-item" href="<?php echo e($item['alert_url']); ?>">
						<small><?php echo e($item['url_name']); ?></small>
					</a>
				<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
				<?php if($loop->iteration != $alerters): ?>
					<div class="dropdown-divider"></div>
				<?php endif; ?>
			<?php endif; ?>

			<?php if(gettype($alertsArray) == "array" && count($alertsArray) == 0): ?>
				<span class="text-muted"><i class="mdi mdi-information-circle"></i> No Alerts</span>
			<?php endif; ?>
		<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

	</div>
</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('title'); ?>
  <?php echo $__env->yieldContent('title2'); ?>
<?php $__env->stopSection(); ?>


<?php $__env->startSection('content'); ?>
<div class="row" id="body-row">
	<!-- Sidebar -->
	<div id="sidebar-container" class="sidebar-expanded d-none d-md-block col-sm-4 col-md-3 col-lg-2">
		<!-- d-* hiddens the Sidebar in smaller devices. Its itens can be kept on the Navbar 'Menu' -->
		<!-- Bootstrap List Group -->
		<ul class="list-group sticky-top sticky-offset">
			<div class="list-group-item p-4 text-center text-white text-ultra-bold sidebar-module-div">
				<i class="mdi mdi-package-variant fa-3x"></i><br>
				<span class="text-lg text-bold">INVENTORY MANAGEMENT</span>
			</div>
			<!-- Separator with title -->
			
			<!-- /END Separator -->
			<!-- Menu with submenu -->


			<a href="/inventory-home" class="bg-dark list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-desktop-mac-dashboard fa-fw mr-3"></span>
					<span class="menu-collapsed">Dashboard</span>
				</div>
			</a>
			<a href="<?php echo e(route('my-approvals')); ?>" class="bg-dark list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-draw fa-fw mr-3"></span>
					<span class="menu-collapsed">Approval Requests
						<small class="float-right badge badge-danger mt-1 ml-3"><?php echo e(count(pendingApprovals())); ?></small>
					</span>
				</div>
			</a>
			<a href="<?php echo e(route('general-requisition-list')); ?>" class="bg-dark list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-file fa-fw mr-3"></span>
					<span class="menu-collapsed">General Requisitions</span>
				</div>
			</a>
			<a href="#request-to-order" data-toggle="collapse" aria-expanded="false" class="bg-dark list-group-item list-group-item-action flex-column align-items-start">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-file-tree mr-3"></span>
					<span class="menu-collapsed">Request to Order</span>
					<span class="submenu-icon ml-auto"></span>
				</div>
			</a>
			<div id="request-to-order" class="collapse sidebar-submenu">
				<?php
					$menuTotals = getRequisitionWorkflowTotals();
				?>
				<?php $__currentLoopData = getRequisitionWorkflow(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
					<a href="<?php echo e(route('go_to_stage', ['stage'=>$item])); ?>" class="list-group-item list-group-item-action bg-dark text-white">
					<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i><?php echo e($item); ?>

						<small class="float-right badge badge-pill"><?php echo e($menuTotals[$item] ?? 0); ?></small>
					</span>
					</a>
				<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
			</div>
			<a href="#request-to-store" data-toggle="collapse" aria-expanded="false" class="bg-dark list-group-item list-group-item-action flex-column align-items-start">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-file-tree mr-3"></span>
					<span class="menu-collapsed">Request to Store</span>
					<span class="submenu-icon ml-auto"></span>
				</div>
			</a>
			<div id="request-to-store" class="collapse sidebar-submenu">
				<?php $__currentLoopData = getRequestToStoreWorkflow(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
					<a href="<?php echo e(route('go_to_stage', ['stage'=>$item])); ?>" class="list-group-item list-group-item-action bg-dark text-white">
					<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i><?php echo e($item); ?>

						<small class="float-right badge badge-pill"><?php echo e($menuTotals[$item] ?? 0); ?></small>
					</span>
					</a>
				<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
			</div>
			
			<a href="/inventory-categories" class="bg-dark list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-format-list-bulleted-type fa-fw mr-3"></span>
					<span class="menu-collapsed">Categories</span>
				</div>
			</a>
			<a href="/inventory-activity" class="bg-dark list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-chart-areaspline fa-fw mr-3"></span>
					<span class="menu-collapsed">Inventory Movement</span>
				</div>
			</a>
			<a href="/inventory-departments" class="bg-dark list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-home-group fa-fw mr-3"></span>
					<span class="menu-collapsed">Departments</span>
				</div>
			</a>
			<a href="/inventory-suppliers" class="bg-dark list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-account-group fa-fw mr-3"></span>
					<span class="menu-collapsed">Suppliers</span>
				</div>
			</a>
			<a href="/inventory-stores" class="bg-dark list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-package-variant-closed fa-fw mr-3"></span>
					<span class="menu-collapsed">Store</span>
				</div>
			</a>
			<a href="<?php echo e(route('stock-taking-list')); ?>" class="bg-dark list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-file-replace fa-fw mr-3"></span>
					<span class="menu-collapsed">Stock Taking</span>
				</div>
			</a>
			<a href="<?php echo e(route('stock-transfer-list')); ?>" class="bg-dark list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-bank-transfer-out fa-fw mr-3"></span>
					<span class="menu-collapsed">Stock Transfer</span>
				</div>
			</a>
			<a href="<?php echo e(route('inventory-reports')); ?>" class="bg-dark list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-file-chart fa-fw mr-3"></span>
					<span class="menu-collapsed">Reports</span>
				</div>
			</a>
			<a href="#sample-workflow-menu" data-toggle="collapse" aria-expanded="false" class="bg-dark list-group-item list-group-item-action flex-column align-items-start">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-file-document-edit-outline mr-3"></span>
					<span class="menu-collapsed">Configurations</span>
					<span class="submenu-icon ml-auto"></span>
				</div>
			</a>
			<div id="sample-workflow-menu" class="collapse sidebar-submenu">
				<?php
					$menuTotals = array("Material Type", "Currency");
				?>
				<?php $__currentLoopData = $menuTotals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
					<a href="<?php echo e(route('module-pre-configs', ['config'=>$item, 'module'=>'Inventory-Management'])); ?>" class="list-group-item list-group-item-action bg-dark text-white">
						<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i><?php echo e($item); ?></span>
					</a>
				<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
				<a href="<?php echo e(route('view-currency-conversions')); ?>" class="list-group-item list-group-item-action bg-dark text-white">
					<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i>Currency Conversion</span>
				</a>
				<a href="<?php echo e(route('view-uom-conversions')); ?>" class="list-group-item list-group-item-action bg-dark text-white">
					<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i>UoM Conversion</span>
				</a>
			</div>


			<div class="list-group-item copyright-lims p-4 text-center text-white">
				Copyright <?php echo e(date('Y')); ?> <span class="text-red">Imara LIMS</span>
			</div>
			<!-- Submenu content -->
		</ul>

		<!-- List Group END-->
	</div>
	<!-- sidebar-container END -->

	<!-- MAIN -->
	<div class="col-sm-8 col-md-9 col-lg-10 py-3" id="main-container-body">
		<div id="message-section" style="padding: 10px 10px 0px 10px !important">
			<?php if($errors->any()): ?>
				<div class="alert alert-danger">
					<ul>
						<?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
							<li><i class="fas fa-exclamation-triangle"></i> <?php echo e($error); ?></li>
						<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
					</ul>
				</div>
			<?php endif; ?>
			<?php if(\Session::has('success') || \Session::has('error')): ?>
				<?php if(\Session::has('success')): ?>
					<div class="alert alert-success center text-lg alert-callout">
						<i class="fas fa-thumbs-up"></i> <?php echo e(Session::get('success')); ?>

					</div>
				<?php endif; ?>
				<?php if(\Session::has('error')): ?>
					<div class="alert alert-danger center text-lg alert-callout">
						<i class="fas fa-exclamation-triangle"></i> <?php echo e(Session::get('error')); ?>

					</div>
				<?php endif; ?>
			<?php endif; ?>
		</div>
		<?php echo $__env->yieldContent('content2'); ?>
	</div>
	<!-- Main Col END -->
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
	<script>
		$(function(){
			<?php if(!getCurrentUserLocation()): ?>
				var loc = $('#location-selector').find('.dropdown-item').attr('href');
				window.location.href = loc;
			<?php endif; ?>
		});
	</script>
	<?php echo $__env->yieldContent('script2'); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/kimari/Projects/polucon/resources/views/layouts/inventory/layout/app.blade.php ENDPATH**/ ?>