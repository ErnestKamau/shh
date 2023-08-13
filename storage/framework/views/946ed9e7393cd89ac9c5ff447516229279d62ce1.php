<?php $__env->startSection('title2'); ?>
<style>
    .card {
        box-shadow: rgba(0, 0, 0, 0.15) 1.95px 1.95px 2.6px;
        text-decoration: none !important;
        color: black !important;
    }

    .card:hover {
        box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;
    }

    a {
        text-decoration: none !important;
        /* color: black !important; */
    }

    .text-bold {
        font-weight: 550;
    }

    .btn-defaultm:hover {
        box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;
    }

    .table-responsive {
        box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;
    }
</style>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content2'); ?>
<main>
    <?php
    $code_b = $has_filter == 1 ? 'all' : $day_batch->day_code;
    $items = array(
        array(
            'link' => '/prp',
            'name' => 'PRP',
            'icon' => null,
        ),
        array(
            'link' => route('packhouse-index'),
            'name' => 'PackHouse Data',
            'icon' => null,
        ),
        array(
            'link' => null,
            'name' => 'Day Batch - '. $code_b,
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
        <i class="mdi mdi-account-question"></i> Pack House Data | Week - <?php echo e($week->week_no); ?> | <?php echo e($has_filter == 1 ? 'all' : $day_batch->day_code); ?>


    </h5>
    <div class="card tab-card mt-5" style="clear:both">
        <div class="card-header tab-card-header">
            <ul class="nav nav-tabs card-header-tabs" id="personnel-tab" role="tablist">
                <li class="nav-item">
                    <a href="#timesheet-tab" class="nav-link active" id="timesheet-trigger" data-toggle="tab" role="tab" aria-controls="timesheet-tab" aria-selected="true"> <i class="mdi mdi-account-clock" style="color: black; font-size:15px"></i> Inspected Bags</a>
                </li>
                <li class="nav-item">
                    <a href="#packhouse-tab" class="nav-link" id="packhouse" data-toggle="tab" role="tab" aria-controls="packhouse-tab" aria-selected="true"> <i class="mdi mdi-check-decagram" style="color: black; font-size:15px"></i> Quality Remarks Captured</a>
                </li>
            </ul>
        </div>
        <div class="tab-content" id="batches-tabs-content">
            <div class="tab-pane fade show active p-3" id="timesheet-tab" role="tabpanel" aria-labelledby="one-tab">
                <h5 class="card-title"> <i class="mdi mdi-account-clock"></i> Inspected Bags</h5>
                <div class="table-responsive mt-4 p-3">
                    <table class="table table-condensed table-sm table-hover table-striped table-bordered ">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Variety</th>
                                <th>Qc Staff</th>
                                <th>Date</th>
                                <th>Actual Count</th>
                                <th>Expected Count</th>
                                <th>Harvest No</th>
                                <th>Harvester</th>
                                <th>Destination</th>
                                <th>Has Remark</th>

                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $batches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $batch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td>
                                    <a href="" class="btn btn-sm btn-default"><i class="mdi mdi-eye text-success"></i></a>
                                </td>
                                <td><?php echo e($batch->varietyname); ?></td>
                                <td><?php echo e($batch->capturedbyname); ?></td>
                                <td><?php echo e($batch->getDayBatchCode()->day_code); ?></td>
                                <td><?php echo e($batch->actual_count); ?></td>
                                <td><?php echo e($batch->expected_count); ?></td>
                                <td><?php echo e($batch->getHarvesterNo()->harvester_no); ?></td>
                                <td><?php echo e($batch->getHarvesterNo()->name); ?></td>
                                <td><?php echo e($batch->destinationname); ?></td>

                                <td class="text-center"><?php echo $batch->hasremark > 0 ? '<span class="btn btn-sm btn-default text-success"><i class="mdi mdi-checkbox-marked-circle-outline mdi-24px"></i></span>' : '<span class="btn btn-sm btn-default text-danger">-</span>'; ?></td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="tab-pane fade show p-3" id="packhouse-tab" role="tabpanel" aria-labbellby="one-tab">
                <h5 class="card-title"><i class="mdi mdi-check-decagram"></i> Quality Remarks Recorded</h5>
                <div class="table-responsive mt-4 p-3">
                    <table class="table table-condensed table-sm table-hover table-striped table-bordered ">
                        <thead>
                            <tr>
                                <th>Variety</th>
                                <th>Quality Remark</th>
                                <th>Quality Count</th>
                                <th>Qc Staff</th>
                                <th>Date</th>
                                <th>Harvest No</th>
                                <th>Harvester</th>
                                <th>Destination</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $qualities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $quality): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <?php $header = $quality->getPackHouseheader() ?>
                                <td><?php echo e($header->varietyname); ?></td>
                                <td><?php echo e($quality->qualityremark); ?></td>
                                <td><?php echo e($quality->quality_count); ?></td>
                                <td><?php echo e($header->capturedbyname); ?></td>
                                <td><?php echo e($quality->getDayBatchCode()->day_code); ?></td>
                                <td><?php echo e($header->getHarvesterNo()->harvester_no); ?></td>
                                <td><?php echo e($header->getHarvesterNo()->name); ?></td>
                                <td><?php echo e($header->destinationname); ?></td>
                                
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</main>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script2'); ?>


<script>

</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('prp::layouts.app',['dataTable'=>true,'select2'=>true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/dan/Documents/projects/nuve/imara-lims-v2/Modules/Prp/Resources/views/Data-Proccess/Transactions/packhouse/show.blade.php ENDPATH**/ ?>