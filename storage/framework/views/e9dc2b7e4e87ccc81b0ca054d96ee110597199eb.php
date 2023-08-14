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
            'link' => '/prp/payslip-generate',
            'name' => 'Payslips',
            'icon' => null,
        )

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
        <i class="mdi mdi-file-account-outline"></i> Harvester Payslips Records <?php echo e($partial->from_date); ?> - <?php echo e($partial->to_date); ?>

        
        

    </h5>
    <div class="row" style="width: 100%;">
        <?php $__currentLoopData = $harvesters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $har): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php 
        $harvester = $har->getHarvesterDetail();
        
         ?>
        <div class="col-md-4 col-sm-4 p-2">
            <div class="card" style="height:100%;box-shadow: rgba(99, 99, 99, 0.2) 0px 2px 8px 0px;">
                <div class="card-body">
                    <p class="text-primary"><b> Pollen Limited </b></p>
                    <div class="border-bottom border-dark mb-1">
                        <span class="text-danger"><b>PRP Details</b></span>
                        <span class="float-righgt"><?php echo e($har['harvester_no']); ?></span>
                        <p>
                        <span><?php echo e($har['harvester_name']); ?></span> <br>
                        <span><?php echo e($har['id_number']); ?></span> <br>
                        <span><b> <?php echo e($partial->from_date); ?> - <?php echo e($partial->to_date); ?> </b></span>
                        </p>
                    </div>
                    <table class="table table-condensed table-sm p-1">
                        <thead class="border-bottom border-dark" style="font-size:10px">
                            <tr>
                            <th style="border:solid 1px black">Total Hrs</th>
                            <th style="border:solid 1px black">Total URC</th>
                            <th style="border:solid 1px black">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $har['speed_data']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $speed): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td colspan="3"><?php echo e($speed['speed_name']); ?></td>
                            </tr>
                            <tr>
                                <td><?php echo e($speed['total_min']); ?></td>
                                <td> <?php echo e(number_format($speed['total_urc'])); ?></td>
                                <td style="text-align:right"><?php echo e(number_format($speed['amount'],2)); ?></td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td colspan="3" style="text-align:right"> <b><?php echo e(number_format($har->speedamount,2)); ?></b></td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="border-bottom border-dark">
                        <table class="table table-condensed table-sm">
                            <thead class="border-bottom border-dark" style="font-size:10px">
                                <tr>
                                    <th style="border:solid 1px black">Name</th>
                                    <th style="border:solid 1px black">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $har['quality']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $quality): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e($quality['quality']); ?></td>
                                    <td><?php echo e($quality['amount']); ?></td>

                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="quality_total">
                        <p style="text-align: right;"><b> <?php echo e(number_format($har->qualityamount,2)); ?> </b></p>
                        <p style="text-align: right;">
                            <b> <span class="mr-5">Total Due</span> <span style="text-align: right;"> <?php echo e(number_format($har->qualityamount + $har->speedamount,2)); ?></span></b>
                        </p>
                        <div class="text-center">
                            <b> ***End Of Slip***</b>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
        
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>


</main>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script2'); ?>



<script>



</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('prp::layouts.app',['dataTable'=>true,'select2'=>true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/dan/Documents/projects/nuve/imara-lims-v2/Modules/Prp/Resources/views/Data-Proccess/payslips/payslip2.blade.php ENDPATH**/ ?>