<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">

<head>
	<meta charset="utf-8">
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
	<style>
		@media  print{
			@page  { 
        size: landscape;
    	}
			.card {
				clear: both; 
				page-break-after: always!important;
			}
			#print {display: none;}
			body,html {margin: 0px; padding: 0px;}
		}

		.card {
			background-color: white;
			width: 480px!important;
			height: 200px!important;
			font-size: 10px!important;
			border: none!important;
			clear: both;
			page-break-after: always!important;
		}

		body {
			background-color: white;
		}
		table{
			border-collapse: collapse;
			width: 100%!important;
			height: 100%!important;
		}
		table,tr,td{border:0.5px solid #131313!important}
		td{padding: 3px!important;}
		
	</style>
</head>

<body>
		<span id="print" onclick="window.print()" class="btn btn-success float-right m-2"><i class="mdi mdi-printer"></i> Print</span>
	<div class="pl-3">
		<?php $__currentLoopData = $labels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
		<div class="card p-2 mt-1">
			<div class="card-header p-0" style="background-color: white;border-bottom:0px">
				<?php
				$check = getSystemConfiguration('display_system_logo');
				$check_company = getActiveCompany()
				?>
				<h6 class="card-title" style="font-size:12px;font-weight:600">
					<?php echo e($check_company->name); ?>


				</h6>
			</div>
			<div class="card-body p-0">
				<table>
					<tbody>
						<?php $__currentLoopData = $item; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k=>$v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
						<tr>
							<?php if($k == "Code"): ?>
							<td><strong><?php echo e($k); ?></strong></td>
							<td style="width: 80%;"><strong><?php echo e($v); ?></strong></td>
							<?php else: ?>
							<td><?php echo e($k); ?></td>
							<td style="width: 80%;"><?php echo e($v); ?></td>
							<?php endif; ?>
						</tr>
						<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
						<tr>
							<td colspan="<?php echo e(sizeof($item)); ?>" class="text-center">
								<div class="p-2"><span class="barcode"><?php echo DNS1D::getBarcodeSVG($item['Sample Ref'], 'C128B'); ?></span> <span class="btn btn-sm btn-transparent print-barcode"><i class="mdi mdi-printer text-info"></i></span></div>
							</td>
						</tr>
					</tbody>
				</table>
			</div>

		</div>
		<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
	</div>
</body>

</html><?php /**PATH /home/dan/Documents/projects/nuve/imara-lims-v2/resources/views/layouts/lab/sample-workflow/labels.blade.php ENDPATH**/ ?>