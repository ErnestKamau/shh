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

    .header-area {
        text-decoration: underline;
    }

    .text-bold {
        font-weight: 550;
    }

    .btn-default:hover {
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
    $items = array(
        array(
            'link' => '/prp',
            'name' => 'PRP',
            'icon' => null,
        ),
        array(
            'link' => '/prp/harvest-week/quality-remarks/index',
            'name' => 'Quality Remarks',
            'icon' => null,
        ),
        array(
            'link' => 'quality-remarks/harvester/' . $harvester_quality->id . '/show',
            'name' => 'Harvester ' . $harvester_quality->getHarvesterDetails()->harvester_no . '',
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
        <i class="mdi mdi-file-account-outline"></i> Week <?php echo e($harvest_week->week_no); ?> | Quality Remarks | Harvester <?php echo e($harvester_quality->getHarvesterDetails()->harvester_no); ?>

    </h5>
    <div class="table-responsive mt-4 p-3">
        <table class="table table-condensed table-sm table-hover table-striped table-bordered ">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Harvester No</th>
                    <th>Harvester Name</th>
                    <td>Source</td>
                    <th>Group</th>
                    <th>Activity</th>
                    <th>Quality Remark</th>
                    <th>Quality Value</th>
                    <th>Date</th>

                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $quality_remarks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $remark): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($loop->iteration); ?></td>
                    <td><?php echo e($remark->getHarvesterDetails()->harvester_no); ?></td>
                    <td><?php echo e($remark->getHarvesterDetails()->name); ?></td>
                    <td><?php echo e($remark->source); ?></td>
                    <td><?php echo e(getPRPHarvesterGroupsByID($remark->getHarvesterDetails()->group_id)->name); ?></td>
                    <td><?php echo e($remark->gettActivityDetails()->name ?? ''); ?></td>
                    <td><?php echo e($remark->getQualityRemarkName()); ?></td>
                    <td><?php echo e($remark->quality_value); ?></td>
                    <td><?php echo e($remark->getTimesheeetHeaderDetails()->harvest_date); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php $__currentLoopData = $packhouse; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $hst): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td> <?php echo e($loop->iteration); ?></td>
                    <td><?php echo e($hst->harvesterno); ?></td>
                    <td><?php echo e($hst->harvestername); ?></td>
                    <td><?php echo e($hst->source); ?></td>
                    <td><?php echo e($hst->harvestergroup); ?></td>
                    <td><?php echo e($hst->activity); ?></td>
                    <td><?php echo e($hst->quality); ?></td>
                    <td><?php echo e($hst->count_value); ?></td>
                    <td><?php echo e($hst->capturedate); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
</main>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script2'); ?>


<script>

</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('prp::layouts.app',['dataTable'=>true,'select2'=>true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/dan/Documents/projects/nuve/imara-lims-v2/Modules/Prp/Resources/views/Data-Proccess/Transactions/quality/show.blade.php ENDPATH**/ ?>