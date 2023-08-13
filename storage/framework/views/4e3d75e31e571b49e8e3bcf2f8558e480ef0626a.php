<?php $__env->startSection('title2'); ?>
  <title>Dashboard | Inventory Management</title>
  <style type="text/css">
    .my-card
    {
      position:absolute;
      left:40%;
      top:-20px;
      border-radius:50%;
    }

		#activity-graph{
			height: 250px;
		}
  </style>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content2'); ?>
  <main>
    <h2 class="p-4">
      <i class="mdi mdi-desktop-mac-dashboard"></i> Dashboard
    </h2>
    <br>
    <div class="row p-4 no-gutters">
      <div class="col-xl-3 col-sm-6 pr-1">
        <div class="card bg-success text-white h-100 no-overflow">
          <div class="card-body bg-success">
            <div class="rotate">
              <i class="fas fa-list fa-4x"></i>
            </div>
            <h6 class="text-uppercase">Categories</h6>
            <h1 class="display-4"><?php echo e(number_format($categoriesNo)); ?></h1>
          </div>
        </div>
      </div>
      <div class="col-xl-3 col-sm-6 pr-1 pl-1">
        <div class="card text-white bg-danger h-100 no-overflow">
          <div class="card-body bg-danger">
            <div class="rotate">
              <i class="fas fa-users fa-4x"></i>
            </div>
            <h6 class="text-uppercase">Suppliers</h6>
            <h1 class="display-4"><?php echo e(number_format($suppliers)); ?></h1>
          </div>
        </div>
      </div>
      <div class="col-xl-3 col-sm-6 pr-1 pl-1">
        <div class="card text-white bg-info h-100 no-overflow">
          <div class="card-body bg-info">
            <div class="rotate">
              <i class="fas fa-sitemap fa-4x"></i>
            </div>
            <h6 class="text-uppercase">Departments</h6>
            <h1 class="display-4"><?php echo e(number_format($departments)); ?></h1>
          </div>
        </div>
      </div>
      <div class="col-xl-3 col-sm-6 pl-1">
				<div class="card text-white bg-warning h-100 no-overflow">
          <div class="card-body">
            <div class="rotate">
              <i class="fas fa-info-circle fa-4x"></i>
            </div>
            <h6 class="text-uppercase">Restock Notifications</h6>
            <h1 class="display-4"><?php echo e(number_format(getRestockNotifications(false, true))); ?></h1>
          </div>
        </div>
      </div>
		</div>
		<div class="p-4">
			<div class="card">
				<div class="card-head-sm p-3 border-bottom">
					<h5>
						<i class="fas fa-line-chart"></i> Inventory Activity
						<small class="float-right text-info"><i class="fas fa-calendar"></i> Last 30 Days</small>
					</h5>
				</div>
				<div class="card-body">
					<div id="activity-graph" data-json='<?php echo e(json_encode($activity)); ?>'></div>
				</div>
			</div>
		</div>
  </main>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script2'); ?>
	<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.css">
	<script src="//cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js"></script>
	<script src="//cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.min.js"></script>
	<script>
		$(function(){

			Morris.Bar({
				element: 'activity-graph',
				data: $('#activity-graph').data('json'),
				xkey: 'category',
				barColors: ['#26bd40', '#bd2626'],
				ykeys: ['stock_in', 'stock_out'],
				labels: ['Stock In', 'Stock Out']
			});
		});
	</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.inventory.layout.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/dan/Documents/projects/nuve/imara-lims-v2/resources/views/layouts/inventory/index.blade.php ENDPATH**/ ?>