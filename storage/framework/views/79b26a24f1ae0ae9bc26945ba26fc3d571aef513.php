<?php $__env->startSection('module-name'); ?>
	<li class="nav-item">
		<a class="nav-link module-name" href="<?php echo e(route('personnel-home')); ?>"><i class="mdi mdi-account-group"></i> Personnel Management</a>
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
				<i class="mdi mdi-account-group fa-3x"></i><br>
				<span class="text-lg text-bold">PERSONNEL MANAGEMENT</span>
			</div>
			<!-- Separator with title -->
			
			<!-- /END Separator -->
			<!-- Menu with submenu -->


			<a href="/personnel-home" class="bg-dark list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-account-group-outline fa-fw mr-3"></span>
					<span class="menu-collapsed">Personnel</span>
				</div>
			</a>
			<a href="/organizational-departments" class="bg-dark list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-home-group fa-fw mr-3"></span>
					<span class="menu-collapsed">Departments</span>
				</div>
			</a>
			<a href="/organizational-roles" class="bg-dark list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-account-key fa-fw mr-3"></span>
					<span class="menu-collapsed">Roles</span>
				</div>
			</a>
			<!-- <a href="/organizational-locations" class="bg-dark list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-map-marker fa-fw mr-3"></span>
					<span class="menu-collapsed">Organizational Structure</span>
				</div>
			</a> -->
			<a href="<?php echo e(route('get-audit-logs')); ?>" class="bg-dark list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-file-search fa-fw mr-3"></span>
					<span class="menu-collapsed">Audit Trail</span>
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
					$menuTotals = array("Educational Levels", "Job Description", "Designation");
				?>
				<?php $__currentLoopData = $menuTotals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
					<a href="<?php echo e(route('module-pre-configs', ['config'=>$item, 'module'=>'Personnel-Management'])); ?>" class="list-group-item list-group-item-action bg-dark text-white">
						<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i><?php echo e($item); ?></span>
					</a>
				<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
					<a href="<?php echo e(route('personnel-certification-home')); ?>" class="list-group-item list-group-item-action bg-dark text-white">
					<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i>Certifications</span>
					</a>
			</div>
			<div class="list-group-item copyright-lims p-4 text-center text-white" style="position: fixed; bottom:0">
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
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/kimari/Projects/polucon/resources/views/layouts/personnel/layout/app.blade.php ENDPATH**/ ?>