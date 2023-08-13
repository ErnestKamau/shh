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
        <i class="mdi mdi-account-question"></i> Pack House Data | Week - <?php echo e($week->week_no); ?> | Day Batches
    </h5>
    <div class="filter mt-3 border-top p-2">
        <form action="<?php echo e(route('packhouse-show')); ?>" method="get">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="" class="control-label">Day Batch</label>
                        <select name="day_batch_id" id="" class="form-control">
                            <option value="all">All</option>
                            <?php $__currentLoopData = $days; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d_batch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($d_batch->id); ?>"><?php echo e($d_batch->day_code); ?> <small>(<?php echo e($d_batch->today_date); ?>)</small></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                        </select>
                    </div>
                </div>
                <input type="hidden" name="use_filter" value="1">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="" class="control-label">Qc Personnel</label>
                        <select name="qc_personnel_id" id="" class="form-control">
                            <option value="all">All</option>
                            <?php $__currentLoopData = $qcs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $qc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($qc->qcuserid); ?>"><?php echo e($qc->harvester_no); ?> - <?php echo e($qc->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="" class="control-label">Variety</label>
                        <select name="variety_id" id="" class="form-control">
                            <option value="all">All</option>
                            <?php $__currentLoopData = $varieties; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($v->id); ?>"><?php echo e($v->zvam); ?> - <?php echo e($v->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="" class="control-label">Destinations</label>
                        <select name="destination_id" id="" class="form-control">
                            <option value="all">All</option>
                            <?php $__currentLoopData = $destinations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $des): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($des->id); ?>"><?php echo e($des->value); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </div>
            </div>
            <input type="hidden" name="has_filter" value="1">
            <input type="hidden" name="harvest_week_id" value="<?php echo e($week->id); ?>">
            <div class="footer">
                <button type="submit" class="btn btn-sm btn-outline-primary float-right"><i class="mdi mdi-filter-plus"></i> Apply Filter</button>
            </div>
        </form>
    </div>
    <div class="table-responsive mt-5 p-3">
        <table class="table table-condensed table-sm table-hover table-striped table-bordered ">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Code</th>
                    <th>Date</th>
                    <th>Has Remarks</th>
                    
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $days; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td>
                        <a href="<?php echo e(route('packhouse-show',[$day->id])); ?>" class="btn btn-sm btn-default text-success"><i class="mdi mdi-eye" data-toggle="tooltip" title="View"></i></a>

                    </td>
                    <td><?php echo e($day->day_code); ?></td>
                    <td><?php echo e($day->today_date); ?></td>
                    
                    <td><?php echo $day->getPackhouseRemarkData() > 0 ? '<i class="mdi mdi-checkbox-marked-circle-outline text-success"></i>'  : '-'; ?></td>
                    

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
<?php echo $__env->make('prp::layouts.app',['dataTable'=>true,'select2'=>true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/dan/Documents/projects/nuve/imara-lims-v2/Modules/Prp/Resources/views/Data-Proccess/Transactions/packhouse/index.blade.php ENDPATH**/ ?>