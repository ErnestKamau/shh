<?php $__env->startSection('title2'); ?>
<style>
   

    
    h5{
        font-weight: 600 !important;
    }
    .col-md-6{
        margin-bottom: 3% !important;
        padding: 1% !important;
    }
    .col-md-12{
        padding: 1% !important;
    }
    .control-label,.text-bold{
        font-size: 13px;
        font-weight: 550;
    }
    .badge-white{
        background-color: white !important;
    }
    .btn-default{
        box-shadow: rgba(17, 17, 26, 0.1) 0px 4px 16px, rgba(17, 17, 26, 0.1) 0px 8px 24px, rgba(17, 17, 26, 0.1) 0px 16px 56px;
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
            'link' => '/prp/',
            'name' => 'Quality Remarks Performance Report - Index',
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
    <h5 class="p-2 mt-3" >
        <i class="mdi mdi-finance mdi-24px"></i> Quality Remarks Performance Report
       
        
    
    </h5>
    <form action="<?php echo e(route('generateQualityPerformanceReport')); ?>" method="POST">
        <?php echo csrf_field(); ?> 
        <div class="card border-0 mt-4">
            
            <div class="row">
                    <div class="col-md-3">
                        <div class="card-body p-0 bg-light" style="height: 73vh;">
    
                            <div class="logo-report text-center p-4" style="background-color: #C0C0C0; color:white">
                                <h5>
                                <i class="mdi mdi-finance mdi-24px"></i><br>
                                    Quality Reports
                                </h5>
                            </div>
                            <div class="p-3">
    
                                <div class="form-check border-bottom pb-5">
                                    <input class="form-check-input" value="week_report" type="radio" name="report_name" checked id="flexRadioDefault1">
                                    <label class="form-check-label" for="flexRadioDefault1">
                                        Week Base Report
                                    </label>
                                </div>
                                
                                <div class="form-check border-bottom pb-5 pt-3">
                                    <input class="form-check-input" value="group_report" type="radio" name="report_name" id="flexRadioDefault1">
                                    <label class="form-check-label" for="flexRadioDefault1">
                                        Group Base Report
                                    </label>
                                </div>
                                
                                <div class="form-check border-bottom pb-5 pt-3">
                                    <input class="form-check-input" value="harvester_report" type="radio" name="report_name" id="flexRadioDefault1">
                                    <label class="form-check-label" for="flexRadioDefault1">
                                        Harvester Base Report
                                    </label>
                                </div>
                                <div class="form-check border-bottom pb-5 pt-3">
                                    <input class="form-check-input" value="variety_report" type="radio" name="report_name" id="flexRadioDefault1">
                                    <label class="form-check-label" for="flexRadioDefault1">
                                        Variety Base Report
                                    </label>
                                </div>
                                <div class="form-check pb-5 pt-3">
                                    <input class="form-check-input" value="parameter_report" type="radio" name="report_name" id="flexRadioDefault1">
                                    <label class="form-check-label" for="flexRadioDefault1">
                                        Quality Parameter Base Report
                                    </label>
                                </div>
                                
    
                            </div>
                        </div>
                    </div>
                    <div class="col-md-9 p-2">
                        <div class="card" style="box-shadow: rgba(0, 0, 0, 0.24) 0px 3px 8px;">
                            <div class="card-header" style="background-color: white;">
        
                                <div style="font-size: 12px;" class="card-title">
                                    <span class="text-danger">*Choose filters to apply on your report.</span>
                                    <button type="submit" class="btn btn-outline-success btn-sm float-right "><i class="mdi mdi-cogs"></i> Generate Report</button>
        
                                </div>
                            </div>
                            <div class="card-body" id="filter-form-data">
        
                                <div class="row no-gutter">
        
                                    <div class="col-xl-4 col-sm-6">
                                        <div class="form-group">
                                            <label for="" class="control-label text-muted">Harvest Week</label>
                                            <select name="week_id[]" id="week_id" multiple class="form-control">
                                                <option value="all" selected>All</option>
                                                <?php $__currentLoopData = $weeks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $week): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($week->id); ?>"><?php echo e($week->year); ?> - Week <?php echo e($week->week_no); ?></option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                
                                            </select>
        
                                        </div>
                                    </div>

                                    <div class="col-xl-4 col-sm-6">
                                        <div class="form-group">
                                            <label for="" class="control-label">From Date</label>
                                            <input type="date" name="from_date" id="" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-xl-4 col-sm-6">
                                        <div class="form-group">
                                            <label for="" class="control-label">To Date</label>
                                            <input type="date" name="to_date" id="" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-xl-4 col-sm-6 hidden" id="group_field">
                                        <div class="form-group">
                                            <label for="" class="control-label text-muted">Harvester Groups</label>
                                            <select name="group_id[]" multiple id="group_id" class="form-control">
                                                <option value="all" selected >All</option>
                                                <?php $__currentLoopData = $groups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($group->id); ?>"><?php echo e($group->name); ?></option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                
                                            </select>
        
                                        </div>
                                    </div>
        
                                    <div class="col-xl-4 col-sm-6 hidden" id="harvester_field">
                                        <div class="form-group">
                                            <label for="" class="control-label text-muted">Harvester</label>
                                            <select name="harvester_id[]" multiple id="harvester_id" class="form-control">
                                                <option value="all" selected>All</option>
                                                <?php $__currentLoopData = $harvesters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $har): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($har->id); ?>"><?php echo e($har->name); ?> (<small><?php echo e($har->harvester_no); ?> </small>)</option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                
                                            </select>
        
                                        </div>
                                    </div>
        
        
        
        
                                    <div class="col-xl-4 col-sm-6 hidden" id="variety-field">
                                        <div class="form-group">
                                            <label for="" class="control-label text-muted">Varieties</label>
                                            <select name="variety_id[]" multiple id="variety_id" class="form-control">
                                                <option value="all" selected >All</option>
                                                <?php $__currentLoopData = $varieties; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $var): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($var->id); ?>"><?php echo e($var->zvam); ?> <?php echo e($var->name); ?></option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </select>
        
                                        </div>
                                    </div>
                                    <div class="col-xl-4 col-sm-6">
                                        <div class="form-group">
                                            <label for="" class="control-label text-muted">Qualities</label>
                                            <select name="qualities_id[]" multiple id="qualities_id" class="form-control">
                                                <option value="all" selected >All</option>
                                                <?php $__currentLoopData = $qualities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $quality): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($quality->id); ?>"><?php echo e($quality->name); ?></option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </select>
        
                                        </div>
                                    </div>
                                    <div class="col-xl-4 col-sm-6">
                                        <div class="form-group">
                                            <label for="" class="control-label text-muted"> Activities</label>
                                            <select name="activity_id[]" multiple id="activity_id" class="form-control">
                                                <option value="all" selected >All</option>
                                                <?php $__currentLoopData = $activities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ctivity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($ctivity->id); ?>"><?php echo e($ctivity->name); ?></option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </select>
        
                                        </div>
                                    </div>
                                    <div class="col-xl-4 col-sm-6">
                                        <div class="form-group">
                                            <label for="" class="control-label text-muted"> Source</label>
                                            <select name="source" id="source" class="form-control">
                                                <option value="all" selected>All</option>
                                                <option value="1">Green House</option>
                                                <option value="2">Pack House</option>
                                            </select>
        
                                        </div>
                                    </div>
        
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
        </div>
</main>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script2'); ?>

<script>
    $(()=>{
        $('#group_id').on('change',(e)=>{
            getHarvesterByGroupID($(e.currentTarget).val(),(data)=>{
                $('#harvester_id').empty();
                $('#harvester_id').append(`<option value="all" >All</option>`);
                $.each(data,(i,elem)=>{
                    $('#harvester_id').append(`<option value="${elem.id}">${elem.name} (<small>${elem.harvester_no}</small>)</option>`)
                });
                $('#harvester_id').select2();
            })

        });
        let getHarvesterByGroupID = (ids,callback)=>{
            $.ajax({
                url:'/prp/report/get/Harvesters-By-GroupIDS',
                method:'GET',
                data:{
                    groups:ids,
                },
                success:(data)=>{
                    callback(data);
                },
                error:(data)=>{
                    console.log(data);
                }
            })
        }
        $("input[name='report_name']").on('change', function() {
            var report_data = $("input[name='report_name']:checked").val();

            var $form = $('#filter-form-data');
            if (report_data == 'week_report') {
                $form.find('#group_field').addClass('hidden');
                $form.find('#harvester_field').addClass('hidden');
                $form.find('#variety-field').addClass('hidden')
            }
            if(report_data == 'group_report'){
                $form.find('#group_field').removeClass('hidden');
                $form.find('#harvester_field').addClass('hidden');
                $form.find('#variety-field').addClass('hidden')
            }
            if(report_data == 'harvester_report'){
                $form.find('#group_field').removeClass('hidden');
                $form.find('#harvester_field').removeClass('hidden');
                $form.find('#variety-field').addClass('hidden')
            }
            if(report_data == 'variety_report'){
                $form.find('#variety-field').removeClass('hidden')
                $form.find('#group_field').addClass('hidden');
                $form.find('#harvester_field').addClass('hidden');
            }
            if(report_data == 'parameter_report'){
                $form.find('#group_field').removeClass('hidden');
                $form.find('#harvester_field').removeClass('hidden');
                $form.find('#variety-field').addClass('hidden')
            }
            // console.log(test);
        })
    })
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('prp::layouts.app',['dataTable'=>true,'select2'=>true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/dan/Documents/projects/nuve/imara-lims-v2/Modules/Prp/Resources/views/reports/qualityPerformanceReport/index.blade.php ENDPATH**/ ?>