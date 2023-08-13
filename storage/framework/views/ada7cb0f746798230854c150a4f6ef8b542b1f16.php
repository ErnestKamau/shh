<?php $__env->startSection('module-name'); ?>
<li class="nav-item">
	<a class="nav-link module-name" href="<?php echo e(route('dashboard-lab')); ?>"><i class="mdi mdi-flask"></i> Lab Management</a>
</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('title'); ?>
<style type="text/css">
	.tab-card {
		border: 1px solid #eee;
	}

	.tab-card-header {
		background: none;
	}

	/* Default mode */
	.tab-card-header>.nav-tabs {
		border: none;
		margin: 0px;
	}

	.tab-card-header>.nav-tabs>li {
		margin-right: 2px;
	}

	.tab-card-header>.nav-tabs>li>a {
		border: 0;
		border-bottom: 2px solid transparent;
		margin-right: 0;
		color: #737373;
		padding: 2px 15px;
	}

	.tab-card-header>.nav-tabs>li>a.show {
		border-bottom: 2px solid #007bff;
		color: #007bff;
	}

	.tab-card-header>.nav-tabs>li>a:hover {
		color: #007bff;
	}

	.tab-card .nav-link.active {
		background-color: #dadccd !important;
		border: 1px solid #cccebf !important;
	}

	.tab-card-header>.tab-content {
		padding-bottom: 0;
	}
</style>
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
				<i class="mdi mdi-flask fa-3x"></i><br>
				<span class="text-lg text-bold">LAB MANAGEMENT</span>
			</div>
			<!-- Separator with title -->
			
			<!-- /END Separator -->
			<!-- Menu with submenu -->
			<a href="<?php echo e(route('dashboard-lab')); ?>" class="bg-dark list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-desktop-mac-dashboard fa-fw mr-3"></span>
					<span class="menu-collapsed">Dashboard</span>
				</div>
			</a>
			<a href="#sample-workflow-menu" data-toggle="collapse" aria-expanded="false" class="bg-dark list-group-item list-group-item-action flex-column align-items-start">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-file-document-edit-outline mr-3"></span>
					<span class="menu-collapsed">Sample Workflow</span>
					<span class="submenu-icon ml-auto"></span>
				</div>
			</a>
			<div id="sample-workflow-menu" class="collapse sidebar-submenu">
				<?php
				$menuTotals = getSampleWorkFLowTotals();
				?>
				<?php $__currentLoopData = getSampleWorflowStages(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
				<?php if($item == 'Samples In Lab'): ?>
				<a href="<?php echo e(route('interLabTransferIndex')); ?>" class="list-group-item list-group-item-action bg-dark text-white">
					<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> Inter Lab Transfer
						<small class="float-right badge badge-pill badge-success"><?php echo e(getInterLabTotals()); ?></small></span>
				</a>
				<?php endif; ?>
				<a href="<?php echo e(route('sample-workflow', ['status'=>$item])); ?>" class="list-group-item list-group-item-action bg-dark text-white">
					<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i><?php echo e($item); ?>

						<small class="float-right badge badge-pill <?php echo e($item == "Samples Request Review" ? 'badge-danger' : 'badge-dark'); ?>"><?php echo e($menuTotals[$item] ?? 0); ?></small></span>
				</a>
				<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

			</div>
			<a href="#billing-menu" data-toggle="collapse" aria-expanded="false" class="bg-dark list-group-item list-group-item-action hidden flex-column align-items-start">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class=" fas fa-money-bill-alt mr-3"></span>
					<span class="menu-collapsed">Billing</span>
					<span class="submenu-icon ml-auto"></span>
				</div>
			</a>
			<div id="billing-menu" class="collapse sidebar-submenu hidden">

				<a href="<?php echo e(route('invoice-home')); ?>" class="list-group-item list-group-item-action bg-dark text-white">
					<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> Proforma Invoices
						<small class="float-right badge badge-pill"></small></span>
				</a>
				<a href="<?php echo e(route('tax-home')); ?>" class="list-group-item list-group-item-action bg-dark text-white">
					<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> Tax Regime
						<small class="float-right badge badge-pill"></small></span>
				</a>
				<a href="/pricelists" class="bg-dark list-group-item list-group-item-action">
					<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> Pricelists
						<small class="float-right badge badge-pill"></small></span>


				</a>

				<a href="#quotation-menu" data-toggle="collapse" aria-expanded="false" class="bg-dark list-group-item list-group-item-action flex-column align-items-start">
					<div class="d-flex w-100 justify-content-start align-items-center">
						<span class=" mdi mdi-clipboard-text-outline mr-3"></span>
						<span class="menu-collapsed">Quotation</span>
						<span class="submenu-icon ml-auto"></span>
					</div>
				</a>
				<div id="quotation-menu" class="collapse sidebar-submenu">
					<a href="<?php echo e(route('quotation-index')); ?>" class="list-group-item list-group-item-action bg-dark text-white">
						<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> All Quotes
							<small class="float-right badge badge-pill"></small></span>
					</a>
					<a href="<?php echo e(route('quotation-index',['stage'=>'Quote In Preparation'])); ?>" class="list-group-item list-group-item-action bg-dark text-white">
						<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> Quotes In Preparation
							<small class="float-right badge badge-pill"></small></span>
					</a>
					
					<a href="<?php echo e(route('quotation-index',['stage'=>'Quote In Approval'])); ?>" class="list-group-item list-group-item-action bg-dark text-white">
						<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> Quotes In Approval
							<small class="float-right badge badge-pill"></small></span>
					</a>
					<a href="<?php echo e(route('quotation-index',['stage'=>'Quote Complete'])); ?>" class="list-group-item list-group-item-action bg-dark text-white">
						<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> Finalised Quotes
							<small class="float-right badge badge-pill"></small></span>
					</a>
				</div>

			</div>
			<a href="#qc-workflow-menu" data-toggle="collapse" aria-expanded="false" class="bg-dark list-group-item list-group-item-action flex-column align-items-start hidden">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-file-certificate-outline mr-3"></span>
					<span class="menu-collapsed">Qc Workflow</span>
					<span class="submenu-icon ml-auto"></span>
				</div>
			</a>
			<div id="qc-workflow-menu" class="collapse sidebar-submenu hidden">
				<!-- <a href="" class="list-group-item list-group-item-action bg-dark text-white">
					<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> Dashboard
						<small class="float-right badge badge-pill"></small></span>
				</a> -->
				<a href="<?php echo e(route('qcWorkflowIndex')); ?>" class="list-group-item list-group-item-action bg-dark text-white">
					<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> Qc History
						<small class="float-right badge badge-pill"></small></span>
				</a>
				<a href="#" class="list-group-item list-group-item-action bg-dark text-white">
					<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> Awaiting Approval
						<small class="float-right badge badge-pill"></small></span>
				</a>
				<a href="#" class="list-group-item list-group-item-action bg-dark text-white">
					<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> Qc Reports
						<small class="float-right badge badge-pill"></small></span>
				</a>
				<a href="#" class="list-group-item list-group-item-action bg-dark text-white">
					<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> Qc Batches
						<small class="float-right badge badge-pill"></small></span>
				</a>
				<a href="<?php echo e(route('qc_configuration_index')); ?>" class="list-group-item list-group-item-action bg-dark text-white">
					<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> Configurations
						<small class="float-right badge badge-pill"></small></span>
				</a>

			</div>
			<a href="/analytes" class="bg-dark list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-molecule fa-fw mr-3"></span>
					<span class="menu-collapsed">Analytes</span>
				</div>
			</a>
			<a href="/labs" class="bg-dark list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-flask fa-fw mr-3"></span>
					<span class="menu-collapsed">Labs</span>
				</div>
			</a>
			<a href="/sample-types" class="bg-dark list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-test-tube fa-fw mr-3"></span>
					<span class="menu-collapsed">Sample Types</span>
				</div>
			</a>
			<a href="/reporting-units" class="bg-dark list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-file-document-edit fa-fw mr-3"></span>
					<span class="menu-collapsed">Reporting Units</span>
				</div>
			</a>
			<a href="/analysis-methods" class="bg-dark list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-cogs fa-fw mr-3"></span>
					<span class="menu-collapsed">Methods</span>
				</div>
			</a>

			

	<a href="/sample-analysis-stages" class="bg-dark list-group-item list-group-item-action">
		<div class="d-flex w-100 justify-content-start align-items-center">
			<span class="mdi mdi-sitemap fa-fw mr-3"></span>
			<span class="menu-collapsed">Lab Sections</span>
		</div>
	</a>
	<a href="#configuration-menu" data-toggle="collapse" aria-expanded="false" class="bg-dark list-group-item list-group-item-action flex-column align-items-start">
		<div class="d-flex w-100 justify-content-start align-items-center">
			<span class="mdi mdi-cogs mr-3"></span>
			<span class="menu-collapsed">Configurations</span>
			<span class="submenu-icon ml-auto"></span>
		</div>
	</a>
	<div id="configuration-menu" class="collapse sidebar-submenu">

		<a href="<?php echo e(route('sample-product-index')); ?>" class="list-group-item list-group-item-action bg-dark text-white">
			<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> Products
				<small class="float-right badge badge-pill"></small></span>
		</a>
		<a href="<?php echo e(route('sample_condition_index')); ?>" class="list-group-item list-group-item-action bg-dark text-white">
			<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> Sample Conditions
				<small class="float-right badge badge-pill"></small></span>
		</a>
		<a href="<?php echo e(route('sample-type-category-index')); ?>" class="list-group-item list-group-item-action bg-dark text-white">
			<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i> Sample Type Category
				<small class="float-right badge badge-pill"></small></span>
		</a>

	</div>

	<a href="/qualification-home" class="bg-dark list-group-item list-group-item-action">
		<div class="d-flex w-100 justify-content-start align-items-center">
			<span class="mdi mdi-file-certificate fa-fw mr-3"></span>
			<span class="menu-collapsed">Certifications</span>
		</div>
	</a>
	<a href="<?php echo e(route('lab-reports-home')); ?>" class="bg-dark list-group-item list-group-item-action">
		<div class="d-flex w-100 justify-content-start align-items-center">
			<span class="mdi mdi-file-certificate fa-fw mr-3"></span>
			<span class="menu-collapsed">Reports</span>
		</div>
	</a>
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
<?php echo $__env->yieldContent('script2'); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/dan/Documents/projects/nuve/imara-lims-v2/resources/views/layouts/lab/layout/app.blade.php ENDPATH**/ ?>