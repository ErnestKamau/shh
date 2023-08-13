<?php $__env->startSection('title2'); ?>
<title> Laboratory Reports </title>
<style type="text/css">
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

    .form-check {
        margin: 15px;
        margin-left: 0px;
    }
</style>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content2'); ?>
<main>
    <?php
    $items = array(
        array(
            'link' => 'equipment-home',
            'name' => 'Equipment',
            'icon' => null
        ),
        array(
            'link' => null,
            'name' => 'Reports',
            'icon' => null
        ),

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
    <h2 class="p-4">
        <i class="mdi mdi-clipboard-text-multiple-outline"></i> Laboratory Reports

    </h2>
    <div class="card mt-2" style="font-size: 12px; height:70vh" id="Filter-Form">
        <form action="<?php echo e(route('lab-report-show')); ?>" method="post" enctype="multipart/form-data">

            <?php echo csrf_field(); ?>

            <div class="row p-1">
                <div class="col-md-3 col-lg-3 col-sm-3">
                    <div class="card-body p-0 bg-light" style="height: 69vh;">

                        <div class="logo-report text-center p-4" style="background-color: black; color:white">
                            <h5>
                                <i class="mdi mdi-clipboard-text-multiple-outline"></i><br>
                                Choose Laboratory
                            </h5>
                        </div>
                        <div class="p-3">

                            <div class="form-check mb-4 ">
                                <input class="form-check-input" value="batch_report" type="radio" name="report_name" checked id="flexRadioDefault1">
                                <label class="form-check-label" for="flexRadioDefault1">
                                    Batch Reports
                                </label>
                            </div>
                            <hr>
                    
                            <div class="form-check mb-4 mt-3">
                                <input class="form-check-input" value="sample_report" type="radio" name="report_name" id="flexRadioDefault1">
                                <label class="form-check-label" for="flexRadioDefault1">
                                   Sample Reports
                                </label>
                            </div>
                            <hr>
                            <div class="form-check mb-4 mt-3">
                                <input class="form-check-input" value="parameter_report" type="radio" name="report_name" id="flexRadioDefault1">
                                <label class="form-check-label" for="flexRadioDefault1">
                                   Parameter Reports
                                </label>
                            </div>
                            <hr>
                            <div class="form-check mb-4 mt-3">
                                <input class="form-check-input" value="profit_report" type="radio" name="report_name" id="flexRadioDefault1">
                                <label class="form-check-label" for="flexRadioDefault1">
                                    Gross Profit Reports 
                                </label>
                            </div>
                            <hr>

                           


                        </div>
                    </div>
                </div>
                <div class="col-md-9 col-lg-9 col-sm-9">
                    <div class="card-header" style="background-color: white;">

                        <div style="font-size: 12px;" class="card-title">
                            <span class="text-danger">*Choose filters to apply on your report.</span>
                            <button type="submit" class="btn btn-outline-success btn-sm float-right "><i class="mdi mdi-cogs"></i> Generate Report</button>

                        </div>
                    </div>
                    <div class="card-body" id="filter-form-data">
                        <div class="row">
                            <div class="col-md-4 col-sm-4 col-lg-4">
                                <div class="form-group">
                                    <label class="control-label">Client</label>
                                    <select name="client_id" id="client" class="form-control">
                                        <option value="all">All</option>
                                        <?php $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $client): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($client->id); ?>"><?php echo e($client->name); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-4 col-lg-4">
                                <div class="form-group">
                                    <label class="control-label">Sample Type</label>
                                    <select name="sample_type_id" id="sample_type" class="form-control">
                                        <option value="">Choose Sample Type</option>
                                        <option value="all">All</option>
                                        <?php $__currentLoopData = $sample_types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($type->id); ?>"><?php echo e($type->name); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-4 col-lg-4">
                                <div class="form-group">
                                    <label class="control-label">Sample Workflow</label>
                                    <select name="workflow" id="workflow" class="form-control">
                                        <option value="all">All</option>
                                        <?php $__currentLoopData = getSampleWorflowStages(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php if($stage != 'All Samples'): ?>
                                                <option value="<?php echo e($stage); ?>"><?php echo e($stage); ?></option>
                                            <?php endif; ?>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-4 col-lg-4">
                                <div class="form-group">
                                    <label class="control-label">Status</label>
                                    <select name="status" id="status" class="form-control">
                                        <option value="all">All</option>
                                        <option value="High">High</option>
                                        <option value="Normal">Normal</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-4 col-lg-4 hidden" id="analysisType">
                                <div class="form-group">
                                    <label class="control-label">Analysis Type</label>
                                    <select name="analysis_type" id="analysis-type" class="form-control">
                                        <option value="">Choose Analysis Type...</option>
                                       
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-4 col-lg-4 hidden" id="analytes">
                                <div class="form-group">
                                    <label class="control-label">Analytes</label>
                                    <select name="analyte_id" id="analyte" class="form-control">
                                        <option value="">Choose Analyte...</option>
                                        <option value="all">All</option>
                                        <?php $__currentLoopData = $analytes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $analyte): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                           <option value="<?php echo e($analyte->id); ?>"><?php echo e($analyte->name); ?> (<?php echo e($analyte->code); ?>)</option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-4 col-lg-4 hidden" id="currency">
                                <div class="form-group">
                                    <label class="control-label">Currency</label>
                                    <select name="currency_id" id="Currency" class="form-control">
                                       <option value="">Choose Currency</option>
                                        <?php $__currentLoopData = $currency; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                           <option value="<?php echo e($c->id); ?>"><?php echo e($c->name); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4 col-sm-4 col-lg-4">
                                <div class="form-group">
                                    <label class="control-label"> <small>(Lab Receiption Date)</small> Start Date</label>
                                    <input type="date" name="start_date" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-4 col-lg-4">
                                <div class="form-group">
                                    <label class="control-label"> <small>(Lab Receiption Date)</small> End Date</label>
                                    <input type="date" name="end_date" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-4 col-lg-4">
                                <div class="form-group">
                                    <label class="control-label">Group By</label>
                                    <select name="group_by" id="group-by" class="form-control">
                                        <option value="none">None</option>
                                        <option value="crm_customer_id">Client</option>
                                        <option value="sample_type_id">Sample Type</option>
                                        
                                    </select>
                                </div>
                            </div>
                        </div>      
                    </div>
                </div>
            </div>
        </form>
    </div>
</main>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script2'); ?>
<script>
    $(function() {
        $("input[name='report_name']").trigger('change');
        $("input[name='report_name']").on('change', function() {
            console.log('test');
            var report_data = $("input[name='report_name']:checked").val();
            var $form = $('#filter-form-data');
            if (report_data == 'batch_report') {
                $($form).find('#analysisType').addClass('hidden');
                $($form).find('#analytes').addClass('hidden');
                $($form).find('#currency').addClass('hidden');
                
                $($form).find('select[name="group_by"]').empty();
                var text = `
                <option value="none">None</option>
                <option value="crm_customer_id">Client</option>
                <option value="sample_type_id">Sample Type</option>
                          
                `;
                $($form).find('select[name="group_by"]').append(text);

                
            }
            if(report_data == 'sample_report' || report_data == 'parameter_report'){
                
                $($form).find('#analysisType').removeClass('hidden');
                $($form).find('#analytes').removeClass('hidden');
                $($form).find('#currency').addClass('hidden');
               
                
                $($form).find('select[name="group_by"]').empty();
                var text = `
                <option value="none">None</option>
                <option value="crm_customer_id">Client</option>
                <option value="sample_type_id">Sample Type</option>
                <option value="analysis_type_id">Analysis Type</option>
                                  
                `;
                $($form).find('select[name="group_by"]').append(text);
                $('#sample_type').on('change',(e)=>{
                    value = $('#sample_type').val();
                    if(value != ''){
                        $('#analysis-type').empty();
                        option = '<option value="">Loading Analysis Types...</option>'
                        $('#analysis-type').append(option);
                        $('#analysis-type').select2()
                        $.ajax({
                            url:`/getAnalysisTypeBySampleTypeAjax/${value}`,
                            method:'GET',
                            success:(data)=>{
                                $('#analysis-type').empty();
                                if(value == 'all'){
                                    option = `<option value="all">ALL ANALYSIS</option>`;
                                    $('#analysis-type').append(option);
                                    
                                    $.each(data,(i,obj)=>{
                                        option = `<option value="${obj}">${obj} ${value == 'all' ? `ANALYSIS` : ``}</option>`
                                        $('#analysis-type').append(option);
                                    });
                                }else{
                                    option = `<option value="all">ALL ANALYSIS</option>`;
                                    $('#analysis-type').append(option);
                                    $.each(data,(i,obj)=>{
                                        option = `<option value="${obj.id}">${obj.name}</option>`
                                        $('#analysis-type').append(option);
                                    });
                                }
                                $('#analysis-type').select2()
                            },
                            error:(data)=>{
                                console.log(data);
                            }
                        });
                    }
                })

            }if(report_data == 'profit_report'){
                $($form).find('#analysisType').removeClass('hidden');
                $($form).find('#analytes').removeClass('hidden');
                $($form).find('#currency').removeClass('hidden');
               
                
                $($form).find('select[name="group_by"]').empty();
                var text = `
                <option value="none">None</option>
                <option value="crm_customer_id">Client</option>
                <option value="sample_type_id">Sample Type</option>
                <option value="analysis_type_id">Analysis Type</option>
                
                `;
                $($form).find('select[name="group_by"]').append(text);
            }
            $($form).find('select[name="type"]').on('change', function() {
                var value_ = $(this).val();
                if (value_ == 'in-house') {
                    $($form).find('#employee').removeClass('hidden');
                    $($form).find('#service-provider').addClass('hidden');
                }
                if (value_ == 'external') {
                    $($form).find('#employee').addClass('hidden');
                    $($form).find('#service-provider').removeClass('hidden');
                }
                if (value_ == 'all') {
                    $($form).find('#employee').addClass('hidden');
                    $($form).find('#service-provider').addClass('hidden');
                }

            })

           
            // console.log(test);
        });
       
    });
</script>



<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.lab.layout.app', ['dataTable'=>true, 'select2'=>true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/dan/Documents/projects/nuve/imara-lims-v2/resources/views/layouts/lab/reports/index.blade.php ENDPATH**/ ?>