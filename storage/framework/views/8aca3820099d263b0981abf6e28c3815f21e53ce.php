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

    .btn-default {
        /* box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px; */
        box-shadow: rgba(0, 0, 0, 0.15) 1.95px 1.95px 2.6px;
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
        <i class="mdi mdi-file-account-outline"></i>Harvester Payslips
        <a href="<?php echo e(route('generatePayslipView',['partial_id'=>$partial_header->id])); ?>" target="_blank" class="btn btn-default text-success float-right">View Payslips</a>
    </h5>

    <div class="card tab-card mt-3" style="clear:both">
        <div class="card-header tab-card-header">
            <ul class="nav nav-tabs card-header-tabs" id="personnel-tab" role="tablist">
                <li class="nav-item">
                    <a href="#payslip-tab" class="nav-link active" data-toggle="tab" role="tab" aria-controls="groups-tab" aria-selected="true"> Payslip Report </a>
                </li>
                <li class="nav-item">
                    <a href="#quality-tab" class="nav-link" data-toggle="tab" role="tab" aria-controls="qcpersonnel-tab" aria-selected="true"> Quality Report</a>
                </li>
                <li class="nav-item">
                    <a href="#speed-tab" class="nav-link" data-toggle="tab" role="tab" aria-controls="qcpersonnel-tab" aria-selected="true"> Speed Report</a>
                </li>
                <li class="nav-item">
                    <a href="#approvers-tab" class="nav-link" data-toggle="tab" role="tab" aria-controls="qcpersonnel-tab" aria-selected="true"> Approvers</a>
                </li>
            </ul>
        </div>
        <div class="tab-content" id="groups-tabs-content">
            <div class="tab-pane fade show active p-3" id="payslip-tab" role="tabpanel" aria-labelledby="one-tab">
                    
                <div class="table-responsive p-3 mt-3">
                    <table class="table table-condensed table-bordered table-hover table-sm">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Harvester No</th>
                                <th>Harvester</th>
                                <th>Total URCs</th>
                                <th>Total Min</th>
                                <th>Quality Amount</th>
                                <th>Speed Amount</th>
                                <th>Total Amount</th>
                                
                                
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $partial_harvesters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ph): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo e(route('viewPartialPayslipReport',['har_id'=>$ph->id])); ?>" class="btn btn-default btn-sm text-success"><i class="mdi mdi-eye"></i></a>
                                        <span class="btn btn-sm btn-default text-danger"><i class="mdi mdi-delete-empty"></i></span>
                                    </td>
                                    <?php $harvester = $ph->getHarvesterDetail();?>
                                    <td><?php echo e($harvester->harvester_no); ?></td>
                                    <td><?php echo e($harvester->name); ?></td>
                                    <td><?php echo e($ph->total_urcs); ?></td>
                                    <td><?php echo e($ph->totalmin); ?></td>
                                    <td style="text-align:right"><?php echo e(number_format($ph->qualityamount,2)); ?></td>
                                    <td style="text-align:right"><?php echo e(number_format($ph->speedamount,2)); ?></td>
                                    <td style="text-align:right"><?php echo e(number_format($ph->qualityamount + $ph->speedamount,2)); ?></td>
                                    
                                    
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="tab-pane fade show p-3" id="quality-tab" role="tabpanel" aria-labbellby="one-tab">
                <div class="table-responsive p-3 mt-3">
                    <table class="table table-condensed table-bordered table-hover table-sm">
                        <thead>
                            <tr>
                                
                                <th>Harvester No</th>
                                <th>Harvester Name</th>
                                <?php $__currentLoopData = $qualityColumns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $column): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <th><?php echo e($column); ?></th>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tr>
                        </thead>
                        <tbody>
                           <?php $__currentLoopData = $partial_harvesters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $har): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                           <tr>
                               <?php $harvester = $har->getHarvesterDetail();?>
                                <td><?php echo e($harvester->harvester_no); ?></td>
                                <td><?php echo e($harvester->name); ?></td>
                                <?php $__currentLoopData = $qualityIds; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <td><?php echo e(isset($har['quality_report'][$id]) ? $har['quality_report'][$id] : '-'); ?></td>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                               <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                           </tr>
                        </tbody>
                    </table>
                </div>

            </div>
            <div class="tab-pane fade show p-3" id="speed-tab" role="tabpanel" aria-labbelby="one-tab">
                <div class="table-responsive p-3 mt-3">
                    <table class="table table-condensed table-bordered table-sm table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Harvester No</th>
                                <?php $__currentLoopData = $speedColumns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s_col): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <th><?php echo e($s_col); ?></th>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $partial_harvesters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s_har): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <?php $harvester = $s_har->getHarvesterDetail();?>
                                <td><?php echo e($harvester->harvester_no); ?></td>
                                <td><?php echo e($harvester->name); ?></td>
                                <?php $__currentLoopData = $speedIds; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <td><?php echo e(isset($s_har['speed_report'][$id]) ? $s_har['speed_report'][$id]  : '-'); ?></td>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="tab-pane fade show p-3" id="approvers-tab" role="tabpanel" aria-labbelby="one-tab">
                <div class="table-responsive p-3 mt-3">
                <table class="table table-condensed table-sm table-hover table-striped table-bordered ">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>User</th>
                                <th>Role</th>
                                <th>Approved Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $approvers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $approver): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>

                                <td>
                                    <span class="btn btn-sm btn-default text-primary" data-toggle="modal" data-target="#change-approver-status" data-mode="edit" data-record="<?php echo e(json_encode($approver)); ?>"><i class="mdi mdi-thumbs-up"></i></span>
                                </td>
                                <td><?php echo e($approver->getUserDetails()->name ?? ''); ?></td>
                                <td><?php echo e($approver->getRoleDetails()->name ?? ''); ?></td>
                                <?php if($approver->is_approved == 0): ?>
                                <td class="text-warning"> Not Actioned</td>
                                <?php else: ?>
                                <td class="text-center"><?php echo $approver->is_approved == 1 ? '<i class="mdi mdi-checkbox-marked-circle-outline text-success"></i>' : '<i class="mdi mdi-close-octagon text-danger"></i>'; ?> </td>
                                <?php endif; ?>

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
<?php echo $__env->make('prp::layouts.app',['dataTable'=>true,'select2'=>true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/dan/Documents/projects/nuve/imara-lims-v2/Modules/Prp/Resources/views/Data-Proccess/payslips/show.blade.php ENDPATH**/ ?>