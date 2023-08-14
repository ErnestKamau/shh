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
        <i class="mdi mdi-file-account-outline"></i> Week <?php echo e($harvest_week->week_no); ?> | Harvester Payslips Records
        <a href="<?php echo e(route('printGeneratedPayslips',['id'=>$harvest_week->id])); ?>" target="_blank" class="btn btn-sm btn-default text-success float-right"><i class="mdi mdi-printer"></i> Print Payslips</a>
        

    </h5>
    <div class="row" style="width: 100%;">
        <?php $__currentLoopData = $payslip_headers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payslip): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <p class="text-primary"><b> Pollen Limited </b></p>
                        <div class="info border-bottom border-dark mb-2">
                            <span class="text-danger"><b> PRP Details </b></span>
                            <span class="float-right"><b> <?php echo e($payslip->getHarvesterDetails()->payroll_no); ?> </b></span>
                            <p><span><?php echo e($payslip->getHarvesterDetails()->name); ?></span> <br>
                                <span><?php echo e($payslip->getHarvesterDetails()->id_number); ?></span> <br>
                                <span><b> <?php echo e($harvest_week->year); ?> <?php echo e($harvest_week->week_no); ?> </b></span>
                            </p>
                        </div>
                        <div class="speed_section">
                            <table class="table table-condensed table-sm ">
                                <thead class="border-bottom border-dark" style="font-size:10px">
                                    <tr>
                                        <th style="border:solid 1px black">Total Hrs</th>
                                        <th style="border:solid 1px black">Total URC</th>
                                        <th style="border:solid 1px black">Speed / TTI Rrmks</th>
                                        <th style="border:solid 1px black">Amount Due</th>
                                    </tr>
                                </thead>
                                <tbody id="harvest_speed_section">
                                    <tr>
                                        <td colspan="4"><u><b> Harvest Speed PRP </b></u></td>
                                    </tr>
                                    <?php $speeds = $payslip->getSpeedData() ?>
                                    <?php $__currentLoopData = $speeds; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td colspan="4" class="border-0"><u><b><small> <?php echo e($s->name); ?> @ <?php echo e($s->allocated_ceiling_amount); ?> </small></u></b></td>
                                    </tr>
                                    <tr>
                                        <td class="border-0 text-center"><?php echo e($s->total_hrs); ?></td>
                                        <td class="border-0 text-center"><?php echo e($s->total_urc); ?></td>
                                        <td class="border-0 text-center"><?php echo e($s->speed); ?> <span class="ml-3"><?php echo e(number_format($s->speed_percentage,2)); ?>%</span> </td>
                                        <td class="border-0" style="text-align:right"><?php echo e(number_format($s->speed_amount_ass,2)); ?></td>
                                    </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="4" class="border-top border-dark" style="text-align:right"><?php echo e(number_format($payslip->speed_amount,2)); ?></td>
                                    </tr>
                                </tfoot>
                            </table>

                        </div>
                        <div class="quality_section border-bottom border-dark">
                            <p style="margin-bottom:4%"><u><b> Quality Remarks PRP </b></u></p>
                            <?php $qualities = $payslip->getqualityData()?>
                            <?php $__currentLoopData = $qualities['qualities']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $q): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="row mb-2">
                                <div class="col-md-4"><?php echo e($q->name); ?></div>
                                <div class="col-md-4"><?php echo e($q->allocated_perc); ?> - 100%</div>
                                <div class="col-md-4" style="text-align:right"><?php echo e($q->total_amount); ?></div>
                            </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>


                        </div>
                        <div class="quality_total">
                            <p style="text-align: right;"><b> <?php echo e($qualities['total']); ?> </b></p>
                            <p style="text-align: right;">
                                <b> <span class="mr-5">Total Due</span> <span style="text-align: right;"> <?php echo e(number_format($qualities['total'] + $payslip->speed_amount,2)); ?></span></b>
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
<?php echo $__env->make('prp::layouts.app',['dataTable'=>true,'select2'=>true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/dan/Documents/projects/nuve/imara-lims-v2/Modules/Prp/Resources/views/Data-Proccess/payslips/payslips.blade.php ENDPATH**/ ?>