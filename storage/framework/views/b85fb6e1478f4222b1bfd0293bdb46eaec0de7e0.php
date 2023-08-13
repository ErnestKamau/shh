<div class="m-3">
	<nav aria-label="breadcrumb">
		<ol class="breadcrumb">
			<?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
				<li class="breadcrumb-item">
					<a <?php echo $lastIndex == $loop->iteration ? '' : 'href="'.$item['link'].'"'; ?> class="<?php echo e($lastIndex == $loop->iteration ? 'text-default' : ''); ?>">
						<?php if($item['icon']): ?>
							<i class="<?php echo e($item['icon']); ?>"></i>
						<?php endif; ?>
						<?php if($item['name']): ?>
							<?php echo e($item['name']); ?>

						<?php endif; ?>
					</a>
				</li>
			<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
		</ol>
	</nav>
</div><?php /**PATH /Users/kimari/Projects/polucon/resources/views/components/bread-crumb.blade.php ENDPATH**/ ?>