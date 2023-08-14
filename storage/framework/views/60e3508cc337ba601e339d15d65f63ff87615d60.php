<?php $__env->startSection('title2'); ?>
<title> Sample Report</title>
<style type="text/css">
    .tab-card {
        border: 1px solid #eee;
    }

    .tab-card-header {
        background: none;
    }

    /* Default mode */
    .tab-card-header>.nav-tabs {
        border: none;
        margin: 0px;
    }

    .tab-card-header>.nav-tabs>li {
        margin-right: 2px;
    }

    .tab-card-header>.nav-tabs>li>a {
        border: 0;
        border-bottom: 2px solid transparent;
        margin-right: 0;
        color: #737373;
        padding: 2px 15px;
    }

    .tab-card-header>.nav-tabs>li>a.show {
        border-bottom: 2px solid #007bff;
        color: #007bff;
    }

    .tab-card-header>.nav-tabs>li>a:hover {
        color: #007bff;
    }

    .tab-card .nav-link.active {
        background-color: #dadccd !important;
        border: 1px solid #cccebf !important;
    }

    .tab-card-header>.tab-content {
        padding-bottom: 0;
    }

    .my-small-text {
        font-size: 13px !important;
    }

    .removeThis {
        z-index: 12;
        position: absolute;
        cursor: pointer;
        top: 0px;
        right: 2px;
        padding: 1px 4px;
        font-size: 12px;
        background-color: red;
        border-radius: 50%;
        color: #fff;
        box-shadow: 0px 0px 5px rgba(0, 0, 0, 0.08);
    }
</style>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content2'); ?>
<main>
    <?php
    $items = array(
        array(
            'link' => route('lab-home'),
            'name' => 'Lab',
            'icon' => null
        ),
        array(
            'link' => route('lab-reports-home'),
            'name' => 'Reports',
            'icon' => null
        ),
        array(
            'link' => '/lab/reports-home',
            'name' => ucwords(str_replace('_', ' ', $filter['report_name'])),
            'icon' => null
        )
    );
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
    <h3 class="p-4">
        <i class="mdi mdi-clipboard-text-multiple-outline"></i> Laboratory Reports | <?php echo e(ucwords(str_replace('_', ' ', $filter['report_name']))); ?>

        <span class="btn-btn-default btn-sm float-right"><i class="mdi mdi-printer"></i> Print Report</span>
    </h3>
    <div class="card">
        <div class="card-header">
            <span class="card-title">
                <img src="<?php echo e($company->logo); ?>" style="width: 200px;height:50px;" alt="">
                <span class="float-right">
                    <b>Date: </b><?php echo e(getTodayDate()); ?> <br>
                    <b>Email: </b><?php echo e($company->email); ?>

                </span>
            </span>
        </div>
        <div class="card-body">
            <b><u>Report Filters</u></b>
            <div class="row p-2">
                <?php $__currentLoopData = $filter; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k=>$v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if(!in_array($k,$filter_remove)): ?>
                
                <div class="col-md-3 col-lg-3 col-sm-6 mt-2">
                    <?php
                    $key = ucwords(str_replace('_', ' ', $k));
                    $value = ucwords(str_replace('_', ' ', $v));
                    ?>
                     <i class="mdi mdi-chevron-double-right"></i> <?php echo e($key); ?>: <?php echo e($value); ?>


                </div>
                <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <hr>
            <div class="table-responsive">
                <table class="table table-condensed table-hover table-bordered table-sm table-stripped" style="width: 140%;">
                    <thead class="bg-light">
                        <tr>
                            <?php $__currentLoopData = $theads; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $thead): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <th><?php echo e($thead); ?></th>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $reports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key=>$data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if($filter['group_by'] == 'none' && $filter['report_name'] != 'profit_report'): ?>
                                <?php if($filter['report_name'] == 'batch_report'): ?>
                                <tr>
                                    <td><?php echo e($data->receipt_date); ?></td>
                                    <td><?php echo e($data->batch_code); ?></td>
                                    <td><?php echo e($data->crm_name); ?></td>
                                    <td><?php echo e($data->submit_by); ?></td>
                                    <td><?php echo e($data->date_collected); ?></td>
                                    <td><?php echo e($data->approval_date); ?></td>
                                    <td><?php echo e($data->batch_scope); ?></td>
                                    <td><?php echo e($data->customer_survey); ?></td>
                                    <td><?php echo e($data->invoice_number); ?></td>
                                    <td><?php echo e($data->sample_type_name); ?></td>
                                    <td><?php echo e($data->workflow_stage); ?></td>
                                    <td><?php echo e($data->priority); ?></td>
                                    
                                </tr>
                                <?php endif; ?>
                                <?php if($filter['report_name'] == 'sample_report' || $filter['report_name'] == 'parameter_report' ): ?>
                                <tr>
                                    <td><?php echo e($data->receipt_date); ?></td>
                                    <td><?php echo e($data->sample_code); ?></td>
                                    <td><?php echo e($data->crm_name); ?></td>
                                    <td><?php echo e($data->submit_by); ?></td>
                                    <td><?php echo e($data->sample_type_name); ?></td>
                                    <td><?php echo e($data->analysis_name); ?></td>
                                    <td><?php echo e($data->analyte_name); ?></td>
                                    <td><?php echo e($data->date_collected); ?></td>
                                    <td><?php echo e($data->invoice_number); ?></td>
                                    <td><?php echo e($data->batch_scope); ?></td>
                                    <td><?php echo e($data->workflow_stage); ?></td>
                                    <td><?php echo e($data->priority); ?></td>
                                </tr>
                                <?php endif; ?>
                            <?php else: ?>
                                <?php if($filter['report_name'] =='profit_report'): ?>
                                    <tr class="bg-dark" style="color:white;font-size:10px">
                                        <td>Group By</td>
                                        <td><?php echo e($key); ?></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td style="text-align: right !important;">Cost Price : <?php echo e(array_sum($data['cost_price'])); ?></td>
                                        <td style="text-align: right !important;">Total Tax : <?php echo e(array_sum($data['tax'])); ?></td>
                                        <td style="text-align: right !important;">total Selling Price : <?php echo e(array_sum($data['selling_price'])); ?></td>
                                        <td style="text-align: right !important;">Total Profit : <?php echo e(array_sum($data['profit'])); ?> </td>
                                    </tr>
                                    <?php $__currentLoopData = $data['samples']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><?php echo e($d->sample_code); ?></td>
                                        <td style="width: 20%;"><?php echo e(getSampleTypeByID($d->sample_type_id)->name); ?></td>
                                        <td style="width:10%"><?php echo e(getCrmCustomerByID($d->crm_customer_id)->name); ?></td>
                                        <td><?php echo e($d->submit_by); ?></td>
                                        <td style="width: 15%;"><?php echo e(getAnalysisTypeID($d->analysis_type)->name); ?></td>
                                        <td><?php echo e(getInvoiceById($d->invoice_id)->invoice_number ?? '-'); ?></td>
                                        <td style="width: 15%;text-align: right !important;"><?php echo e($d->cost_price); ?></td>
                                        <td style="width: 8%;text-align: right !important;"><?php echo e($d->tax_amount); ?></td>
                                        <td style="width: 10%;text-align: right !important;"><?php echo e($d->selling_price); ?></td>
                                        <td style="width: 15%;text-align: right !important;"><?php echo e($d->selling_price - $d->cost_price); ?></td>
                                    </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                <?php endif; ?>
                                <?php if($filter['report_name'] == 'batch_report'): ?>
                                    <tr class="bg-dark" style="color:white;font-weight:600">
                                        <td>Group By</td>
                                        <td><?php echo e($key); ?></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                    <?php $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><?php echo e($d->batch_code); ?></td>
                                        <td><?php echo e($d->crm_name); ?></td>
                                        <td><?php echo e($d->sample_type_name); ?></td>
                                        <td><?php echo e($d->no_of_samples); ?></td>
                                        <td><?php echo e($d->workflow_stage); ?></td>
                                        <td><?php echo e($d->priority); ?></td>
                                    </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php endif; ?>
                                <?php if($filter['report_name'] == 'sample_report' || $filter['report_name'] == 'parameter_report'): ?>
                                    <tr class="bg-dark" style="color:white;font-weight:600">
                                        <td>Group By</td>
                                        <td><?php echo e($key); ?></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                    <?php $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><?php echo e($d->sample_code); ?></td>
                                        <td><?php echo e($d->crm_name); ?></td>
                                        <td><?php echo e($d->sample_type_name); ?></td>
                                        <td><?php echo e($d->analysis_name); ?></td>
                                        <td><?php echo e($d->analyte_name); ?></td>
                                        <td><?php echo e($d->workflow_stage); ?></td>
                                        <td><?php echo e($d->priority); ?></td>
                                    </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php endif; ?>
                            <?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>


<?php $__env->stopSection(); ?>
<?php $__env->startSection('script2'); ?>



<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.lab.layout.app', ['dataTable'=>true, 'select2'=>true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/dan/Documents/projects/nuve/imara-lims-v2/resources/views/layouts/lab/reports/show.blade.php ENDPATH**/ ?>