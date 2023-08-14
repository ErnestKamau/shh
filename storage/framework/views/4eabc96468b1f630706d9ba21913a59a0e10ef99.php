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

    .header-area{
        text-decoration: underline;
    }

    .text-bold {
        font-weight: 550;
    }
    .btn-default:hover{
        box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;
    }
    .table-responsive{
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
            'link' => '/prp/show/PrdRawData/Harvesters',
            'name' => 'Harvesters',
            'icon' => null,
        ))
        
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
        <i class="mdi mdi-account-multiple"></i> Harvesters
    </h5>
    <div class="table-responsive mt-4 p-3">
        <table class="table table-condensed table-sm table-hover table-striped table-bordered ">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Harvester No</th>
                    <th>Variety</th>
                    <th>Site No</th>
                    <th>Destination</th>
                    <th>No of Cuttings</th>
                    <th>Name</th>
                    <th>Has Exception</th>

                </tr>
            </thead>
            <tbody>
            <?php $__currentLoopData = $harvesters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $har): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($loop->iteration); ?></td>
                    <td><?php echo e($har->harvester_no); ?></td>
                    <td></td>
                    <td><?php echo e($har->site_no); ?></td>
                    <td><?php echo e($har->destination); ?></td>
                    <td><?php echo e($har->getTotalURC()); ?></td>
                    <td class="text-center"><?php echo e($har->harvester_id  == 0 ? '-': getPrpHarvesterdetailById($har->harvester_id)->name); ?></td>
                    <td><?php echo $har->exception == 1 ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>'; ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
</main>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script2'); ?>


<?php $__env->stopSection(); ?>
<?php echo $__env->make('prp::layouts.app',['dataTable'=>true,'select2'=>true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/dan/Documents/projects/nuve/imara-lims-v2/Modules/Prp/Resources/views/Data-Proccess/Data/prd/harvesters_index.blade.php ENDPATH**/ ?>