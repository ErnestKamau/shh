<?php $__env->startSection('title2'); ?>
    <title>View COA</title>

    <style>
        .form-part-toggler {
            margin: 0px 0px 5px 0px !important;
            padding: 6px 6px 6px 6px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.09);
            cursor: pointer;
        }

        .form-part-toggler:hover {
            background-color: rgba(0, 0, 0, 0.08);
        }

        #sample-detail-rows .form-group {
            display: none;
        }

        #sample-detail-rows tr.selected-row {
            background-color: rgb(253, 220, 220);
        }

        #sample-detail-rows .text {
            display: unset;
        }

        #sample-detail-rows tr.editable .form-group {
            display: unset;
        }

        #sample-detail-rows tr.editable .text {
            display: none;
        }

        #sample-detail-rows tr {
            cursor: pointer;
        }

        .hidden {
            display: none;
        }

        .overdue-bg-color {
            background-color: rgba(240, 185, 83, 0.972) !important;
        }

        .upfront-bg-color {
            background-color: skyblue !important;
        }

        .ammend-bg-color {
            background-color: #fef764 !important;
        }

        .btn-white {
            background-color: white !important;
        }

        .text-bold {
            font-weight: 650 !important ;
        }
        td{
            padding: 3px;
            
        }
        
    </style>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content2'); ?>
    <main>
        <?php
        $items = [
            [
                'link' => route('dashboard-lab'),
                'name' => 'Dashboard',
                'icon' => null,
            ],
            [
                'link' => route('sample-workflow', ['status' => 'All Samples']),
                'name' => 'Sample Workflow',
                'icon' => null,
            ],
            [
                'link' => route('sample-workflow', ['status' => $status]),
                'name' => $status,
                'icon' => null,
            ],
            [
                'link' => '/sample-workflow/batch/' . $batch->id . '/details',
                'name' => $batch->batch_code,
                'icon' => null,
            ],
            [
                'link' => '#',
                'name' => 'COA',
                'icon' => null,
            ],
        ];
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
        <h4 class="p-2">
            <span class="float-left"><i class="mdi mdi-file-document-edit"></i> <?php echo e($batch->batch_code); ?> COA</span>

        </h4>
        
        <?php $check_v = 0; ?>
        <?php $__currentLoopData = $samples; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sample): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="card m-4 mt-5" style="clear:both;box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between p-2">
                        <div class="company-logo" style="width:50%">
                            <img src="<?php echo e($company->logo); ?>" style="width:20% !important" alt="">
                        </div>
                        <div class="company_details"  style="text-align: right" >
                            <p class="mt-2" style="font-size: 17px !important">
                                <?php echo e($sample->crm_name); ?> <br>
                                P.O BOX <?php echo e($sample->postal_address); ?> <br>
                                <?php echo e($sample->physical_address); ?>

                            </p>

                        </div>
                    </div>
                    <div class="report-no p-2 border" style="font-weight: 750 !important ;">
                        TEST REPORT NO : R<?php echo e(substr($sample->sample_code, 1, strlen($sample->sample_code))); ?>

                    </div>
                    <table class="border" style="width: 100%">
                        <tr>
                            <td style="width:20%;border-right:1px solid #dee2e6">SAMPLE</td>
                            <td  style="padding-left:10px !important"><?php echo e($sample->sample_type_name); ?></td>
                        </tr>
                        <tr>
                            <td style="border-right:1px solid #dee2e6">DATE & PLACE <?php echo e($sample->sampled_by_company_personnel == 1 ? 'SAMPLED' : 'SUBMITTED'); ?></td>
                            <?php if($sample->sampled_by_company_personnel == 1): ?>
                                <td style="padding-left:10px !important"><?php echo e($sample->date_collected); ?> <?php echo e($sample->sample_point_name); ?></td>
                            <?php else: ?>
                                <td style="padding-left:10px !important"><?php echo e($sample->receipt_date); ?> <?php echo e($company->name); ?></td>
                            <?php endif; ?>
                        </tr>
                        <tr>
                            <td style="border-right:1px solid #dee2e6">DATE ANALYSIS STARTED</td>
                            <td style="padding-left:10px !important"></td>
                        </tr>
                        <tr>
                            <td style="border-right:1px solid #dee2e6">SAMPLING METHOD</td>
                            <td style="padding-left:10px !important"><?php echo e($sample->sampling_method_name); ?></td>
                        </tr>
                        <tr>
                            <td style="border-right:1px solid #dee2e6">MARKINGS</td>
                            <td style="padding-left:10px !important"><?php echo e($sample->comments); ?></td>
                        </tr>
                    </table>

                    <div class="table-responsive mt-2">
                        <table class="table table-sm table-condensed table-bordered">
                            <thead>
                                <tr>
                                    <th>TESTS</th>
                                    <th>TEST METHODS</th>
                                    <th>RESULTS</th>
                                    <th>UNITS</th>
                                    <th><?php echo e($sample->main_standard_code); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $sample->getSampleByAnalysisType(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $analysis_type_level): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr class="bg-light">
                                        <td colspan="5" class="text-muted"><b><?php echo e($analysis_type_level->analysis_type_name); ?></b></td>
                                    </tr>

                                    <?php $__currentLoopData = $analysis_type_level->getCapturedResults(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $captured): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php $captured->analyte_status_contracted == 1 ? ($check_v = 1) : 0; ?>
                                        <tr class="<?php echo e($captured->remark == 'FAIL' ? 'text-bold' : ''); ?>">

                                            <td><?php echo $captured->analyte_status_contracted == 1 ? '<small>*</small>' : ''; ?> <?php echo e($captured->analyte_code); ?></td>
                                            <td><?php echo e($captured->method()->name); ?></td>
                                            <td><?php echo e($captured->result_reporting_symbol ?? ''); ?><?php echo e($captured->result); ?></td>
                                            <td><?php echo e($captured->analyte()->reporting_unit); ?></td>
                                            <td><?php echo e($captured->main_value); ?></td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="end-test-span text-center">*******<small>End of Test Results</small>*******</div>
                    <div class="comments mt-2 p-2">
                        <b>Comments : </b><?php echo e($sample->header_body); ?>

                    </div>
                    <div class="p-3 d-flex justify-content-between mt-2">
                        <div class="lab-sect">
                            <b><?php echo e($sample->main_lab_name); ?> <br>
                                <?php echo e($batch->approval_date ?? '-'); ?>

                            </b>
                        </div>
                        <?php $__currentLoopData = $batch_approvers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $approver): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="sig-data">
                            <b><?php echo e($approver->title); ?></b><br>
                            <img src="<?php echo e($approver->getApproverDetails()->electronic_sig); ?>" style="width:80px" alt=""><br>
                            <span><?php echo e($approver->getApproverDetails()->name); ?> - <?php echo e($approver->getApproverPositionDetails()); ?></span>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                       
                    </div>
                    <div class="disclaimer-section p-2 mt-2">

                        <?php if($check_v == 1): ?>
                            <span><?php echo e($non_accredited->value); ?></span>
                        <?php endif; ?>
                        <div class="text-center pl-2 pr-2">
                            <?php echo e($disclaimer->value); ?>

                            <?php if($batch->sampled_by_company_personnel == 0): ?>
                            <br>
                            <b>NB: This report relates to submitted sample(s) only. The source and markings are as provided by the customer.</b>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="company_details mt-5 p-2 hidden">
                        <div class="row">
                            <div class="col-md-2 pt-3">
                                <?php echo QrCode::size(100)->generate(Request::url().'?template_id='.$standard_report.'&batch_id='.$batch->id); ?> <br>
                                
                                Scan to Verify
                            </div>
                            <div class="col-md-6">
                                <div class="text-center"><b><?php echo e($company->name); ?></b></div>
                                <div class="company-location p-2">
                                    <table>
                                        <tr>
                                            <td colspan="3"><?php echo e($company->street); ?> - P.O. Box <?php echo e($company->address); ?>, <?php echo e($company->location); ?></td>
                                        </tr>
                                        <tr>
                                            <td>Office: <?php echo e($company->telephone); ?></td>
                                            <td>Tel 1: <?php echo e(explode('/',$company->cell_phone)[0] ?? ''); ?></td>
                                            <td>Email: <?php echo e($company->email); ?></td>
                                        </tr>
                                        <tr>
                                            <td>Fax: <?php echo e($company->fax); ?></td>
                                            <td>Tel2: <?php echo e(explode('/',$company->cell_phone)[0] ?? ''); ?></td>
                                            <td>Web: <?php echo e($company->website); ?></td>
                                        </tr>
                                        
                                    </table>
                                </div>
                                <div class="text-center"><b>Member of POLUCON Group</b></div>
                            </div>
                            <div class="col-md-4"></div>

                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </main>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script2'); ?>
    <script src="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/js/bootstrap4-toggle.min.js"></script>
    <script></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.lab.layout.app', ['datePicker' => true, 'select2' => true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/kimari/Projects/polucon/resources/views/layouts/lab/sample-workflow/report-formats/standard_report.blade.php ENDPATH**/ ?>