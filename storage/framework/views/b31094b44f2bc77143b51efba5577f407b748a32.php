<?php $__env->startSection('title2'); ?>
<title> Equipment Reports </title>
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
        <i class="mdi mdi-clipboard-text-multiple-outline"></i> Equipment Reports

    </h2>
    <div class="card mt-2" style="font-size: 12px; height:70vh" id="Filter-Form">
        <form action="<?php echo e(route('equipment-report-show')); ?>" method="post" enctype="multipart/form-data">

            <?php echo csrf_field(); ?>

            <div class="row p-1">
                <div class="col-md-3 col-lg-3 col-sm-3">
                    <div class="card-body p-0 bg-light" style="height: 69vh;">

                        <div class="logo-report text-center p-4" style="background-color: black; color:white">
                            <h5>
                                <i class="mdi mdi-clipboard-text-multiple-outline"></i><br>
                                Choose Equipments Report
                            </h5>
                        </div>
                        <div class="p-3">

                            <div class="form-check mb-5">
                                <input class="form-check-input" value="equipment_report" type="radio" name="report_name" checked id="flexRadioDefault1">
                                <label class="form-check-label" for="flexRadioDefault1">
                                    Equipment Reports
                                </label>
                            </div>
                            <hr>
                            <div class="form-check mt-4 mb-5">
                                <input class="form-check-input" value="maintainance_report" type="radio" name="report_name" id="flexRadioDefault1">
                                <label class="form-check-label" for="flexRadioDefault1">
                                    Maintainance & Service Logs Reports
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

                        <div class="row no-gutter">

                            <div class="col-xl-4 col-sm-6">
                                <div class="form-group">
                                    <label class="control-label">Choose Equipment</label>
                                    <select style="background-color: white;" class="form-control" name="equipment_id" id="equipment-ids" aria-placeholder="Choose Equipement...">
                                        <option value="all">All</option>
                                        <?php $__currentLoopData = $equipments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $equipment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($equipment->id); ?>"><?php echo e($equipment->name); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-xl-4 col-sm-6 hidden" id="log-type">
                                <div class="form-group">
                                    <label class="control-label">Equipment Logs</label>
                                    <select name="log_type" style="background-color: white;" class="form-control" aria-placeholder="Choose Sample Workflow...">
                                        <option value="all">All</option>
                                        <option value="Maintainance">Maintainance Logs</option>
                                        <option value="Calibration">Calibration Logs</option>
                                        <option value="Repairement">Repair Logs</option>
                                        <option value="Verification">Verification Logs</option>

                                    </select>
                                </div>
                            </div>
                            <div class="col-xl-4 col-sm-6 hidden" id="type">
                                <div class="form-group">
                                    <label class="control-label">Maintainance Type</label>
                                    <select name="type" style="background-color: white;" class="form-control" aria-placeholder="Choose Sample Workflow...">
                                        <option value="all">All</option>
                                        <option value="in-house">In House</option>
                                        <option value="external">External</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-xl-4 col-sm-6 hidden" id="service-provider">
                                <div class="form-group">
                                    <label class="control-label">Service Performer</label>
                                    <select name="service_provider" style="background-color: white;" class="form-control" aria-placeholder="Choose Sample Workflow...">
                                        <option value="all">All</option>
                                        <?php $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supplier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($supplier->id); ?>"><?php echo e($supplier->name); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-xl-4 col-sm-6 hidden" id="employee">
                                <div class="form-group">
                                    <label class="control-label">Employee</label>
                                    <select name="employee_id" style="background-color: white;" class="form-control" aria-placeholder="Choose Sample Workflow...">
                                        <option value="all">All</option>
                                        <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($e->id); ?>"><?php echo e($e->name); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-xl-4 col-sm-6">
                                <div class="form-group">
                                    <label class="control-label">Equipment Status</label>
                                    <select name="status" style="background-color: white;" class="form-control" id="status" aria-placeholder="Choose Sample Workflow...">
                                        <option value="active">Active</option>
                                        <option value="disposed">Disposed</option>

                                    </select>
                                </div>
                            </div>
                            <div class="col-xl-4 col-sm-6" id="asset-type">
                                <div class="form-group">
                                    <label class="control-label">Asset Type</label>
                                    <select name="asset_type" id="asset_type" style="background-color: white;" class="form-control" aria-placeholder="Choose Asset Type...">
                                        <option value="all">All</option>
                                        <?php $__currentLoopData = $asset_types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $asset): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($asset->id); ?>"><?php echo e($asset->descripton); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-xl-4 col-sm-6" id="asset-location">
                                <div class="form-group">
                                    <label class="control-label">Asset Locations</label>
                                    <select name="asset_location" style="background-color: white;" class="form-control" aria-placeholder="Choose Asset Type...">
                                        <option value="all">All</option>
                                        <?php $__currentLoopData = $asset_locations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $asset): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($asset->id); ?>"><?php echo e($asset->name); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-xl-4 col-sm-6" id="department">
                                <div class="form-group">
                                    <label class="control-label">Departments</label>
                                    <select name="department" style="background-color: white;" class="form-control" aria-placeholder="Choose Asset Type...">
                                        <option value="all">All</option>
                                        <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $department): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($department->id); ?>"><?php echo e($department->name); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-xl-4 col-sm-6 hidden" id="start-date">
                                <div class="form-group">
                                    <label class="control-label">Start Date <small class="text-danger">*takes a span of one year by default </small> </label>
                                    <input type="date" style="background-color: white;" name="start_date" class="form-control">
                                </div>

                            </div>
                            <div class="col-xl-4 col-sm-6 hidden" id="end-date">
                                <div class="form-group">
                                    <label class="control-label">End Date</label>
                                    <input type="date" style="background-color: white;" name="end_date" class="form-control">
                                </div>
                            </div>
                            <div class="col-xl-4 col-sm-6" id="group-by">
                                <div class="form-group">
                                    <label class="control-label">Group By</label>
                                    <select name="group_by" class="form-control">
                                        <option value="none">None</option>
                                        <option value="asset_type_id">Asset Type</option>
                                        <option value="is_disposal">Equipment Status</option>
                                        <option value="asset_location_id">Asset Location</option>
                                        <option value="assigned_department">Departments</option>

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
            if (report_data == 'equipment_report') {
                $($form).find('#log-type').addClass('hidden');
                $($form).find('#end-date').addClass('hidden');
                $($form).find('#start-date').addClass('hidden');
                $($form).find('#type').addClass('hidden');
                $($form).find('select[name="group_by"]').empty();
                var text = `
                    <option value="none">None</option>
                    <option value="asset_type_id">Asset Type</option>
                    <option value="is_disposal">Equipment Status</option>
                    <option value="asset_location_id">Asset Location</option>
                    <option value="assigned_department">Departments</option>             
                `;
                $($form).find('select[name="group_by"]').append(text);

                
            }else{
                
                $($form).find('#end-date').removeClass('hidden');
                $($form).find('#start-date').removeClass('hidden');
                $($form).find('#log-type').removeClass('hidden');
                $($form).find('#type').removeClass('hidden');
                
                $($form).find('select[name="group_by"]').empty();
                var text = `
                    <option value="none">None</option>
                    <option value="asset_type_id">Asset Type</option>
                    <option value="asset_location_id">Asset Location</option>
                    <option value="assigned_department">Departments</option>
                    <option value="type">Log Types</option>
                    <option value="maintainance_type ">Maintainance Type</option>                    
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
<?php echo $__env->make('layouts.equipment.asset.layout.app', ['dataTable'=>true, 'select2'=>true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/dan/Documents/projects/nuve/imara-lims-v2/resources/views/layouts/equipment/reports/index.blade.php ENDPATH**/ ?>