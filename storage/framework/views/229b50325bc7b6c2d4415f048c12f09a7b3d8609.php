<?php $__env->startSection('module-name'); ?>
<li class="nav-item">
  <a class="nav-link module-name" href="<?php echo e(route('lab-home')); ?>"><i class="mdi mdi-tools"></i> Equipment Management</a>
</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('title'); ?>
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
				<i class="mdi mdi-tools fa-3x"></i><br>
				<span class="text-lg text-bold">EQUIPMENT MANAGEMENT</span>
			</div>
			<!-- Separator with title -->
			
			<!-- /END Separator -->
			<!-- Menu with submenu -->
			
			<a href="/equipment-home" class="bg-dark list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-tools fa-fw mr-1"></span>
					<span class="menu-collapsed">Equipment</span>
				</div>
			</a>
			
			<a href="/asset-type-home" class="bg-dark list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-layers-triple fa-fw mr-1"></span>
					<span class="menu-collapsed">Asset Type</span>
				</div>
			</a>
			<a href="/asset-location-home" class="bg-dark list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-map-marker fa-fw mr-1"></span>
					<span class="menu-collapsed">Asset Location</span>
				</div>
			</a>
			<a href="<?php echo e(route('equipment-report-generate')); ?>" class="bg-dark list-group-item list-group-item-action">
				<div class="d-flex w-100 justify-content-start align-items-center">
					<span class="mdi mdi-clipboard-text-multiple-outline fa-fw mr-1"></span>
					<span class="menu-collapsed">Reports</span>
				</div>
			</a>
		
			<div class="list-group-item copyright-lims p-4 text-center text-white" style="position: fixed;bottom:0">
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
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
  <?php echo $__env->yieldContent('script2'); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/dan/Documents/projects/nuve/imara-lims-v2/resources/views/layouts/equipment/asset/layout/app.blade.php ENDPATH**/ ?>