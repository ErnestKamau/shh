<?php $__env->startSection('title2'); ?>
<style>
    /*  */
</style>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content2'); ?>
<main>
    <?php
    $items = array(
        array(
            'link' => '/prp',
            'name' => 'PRP',
            'icon' => null,
        ),
        array(
            'link' => '/prp/payslips/index',
            'name' => 'Payslips',
            'icon' => null,
        ),
        array(
            'link' => '/prp/get/partial-report/harvesters/'.$partial_header->id,
            'name' => 'Payslips Report '.$partial_header->from_date.' - '.$partial_header->to_date,
            'icon' => null,
        ),
        array(
            'link' => '/prp/view/Partial-Payslip/'.$h_partial->id.'/Report',
            'name' => $h_partial->getHarvesterDetail()->name,
            'icon' => null,
        ),

    )

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
    <h5 class="p-2 mt-2">
        <?php $harvester = $h_partial->getHarvesterDetail(); ?>
        <i class="mdi mdi-file-account-outline"></i> <?php echo e($harvester->name); ?> | <?php echo e($partial_header->from_date); ?> - <?php echo e($partial_header->to_date); ?> Payslip Report
    </h5>
    
    <?php $__currentLoopData = $response; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $res): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="card m-2" style="box-shadow: rgba(99, 99, 99, 0.2) 0px 2px 8px 0px;">
    <div class="card-header m-2">
        <h5><?php echo e($res['week']); ?> Payslip Report</h5>
    </div>
        <div class="card-body">
            <p><b><u>Speed Report</u></b></p>
            
            <div class="table-responsive mt-3">
                <table class="table table-condensed table-bordered table-sm table-hover">
                    <thead>
                        <tr>
                            <th>Week</th>
                            <th>Speed Factor</th>
                            <th>Total Urcs</th>
                            <th>Total Min</th>
                            <th>Floor</th>
                            <th>Ceiling</th>
                            <th>Speed</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $res['speed']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $speed): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($res['week']); ?></td>
                            <td><?php echo e($speed->speedfactor); ?></td>
                            <td><?php echo e($speed->total_urc); ?></td>
                            <td><?php echo e($speed->total_min); ?></td>
                            <td><?php echo e($speed->speed_floor); ?></td>
                            <td><?php echo e($speed->speed_ceiling); ?></td>
                            <td><?php echo e(number_format($speed->speed,1)); ?></td>
                            <td style="text-align:right !important"><?php echo e(number_format($speed->amount,2)); ?></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <tr style="background-color: rgb(209, 233, 206)!important;">
                            <td><b>Speed Total</b></td>
                            <td colspan="7" style="text-align:right"><b><?php echo e(number_format($res['speed_amount'],2)); ?></b></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="quality mt-3">
                <p><b><u>Quality Report</u></b></p>
                <div class="table-responsive mt-2">
                    <table class="table table-condensed table-bordered table-hover ">
                        <thead>
                            <tr>
                                <th>Week</th>
                                <th>Quality</th>
                                <th>Quality Percentage</th>
                                <th>Quality Count</th>
                                <th>Allocated Amount</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $res['quality']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $quality): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><?php echo e($res['week']); ?></td>
                                <td><?php echo e($quality->quality); ?></td>
                                
                                <td><?php echo e($quality->quality_perc); ?></td>
                                <td><?php echo e($quality->quality_value); ?></td>
                                <td style="text-align:right"><?php echo e(number_format($quality->quality_allocated_amount,2)); ?></td>
                                <td style="text-align:right"><?php echo e(number_format($quality->amount,2)); ?></td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <tr style="background-color: rgb(209, 233, 206)!important;">
                                <td><b>Quality Amount</b></td>
                                <td colspan="5" style="text-align:right"><b><?php echo e(number_format($res['quality_amount'],2)); ?></b> </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="card-footer m-2">
            <div class="" style="text-align:right">
                <b>Total Amount: <?php echo e(number_format($res['speed_amount'] + $res['quality_amount'],2)); ?></b>
                
            </div>
        </div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
   


</main>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script2'); ?>



<script>



</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('prp::layouts.app',['dataTable'=>true,'select2'=>true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/dan/Documents/projects/nuve/imara-lims-v2/Modules/Prp/Resources/views/Data-Proccess/payslips/showdetailpayslips.blade.php ENDPATH**/ ?>