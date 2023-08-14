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
        <i class="mdi mdi-file-account-outline"></i> Payment Summary Reports
        
    </h5>
    <div class="filter-group">
        <form action="<?php echo e(route('performance-index')); ?>" class="p-2" method="GET">
            <div class="row">

                <div class="col-md-3">
                    <div class="form-group">
                        <label for="" class="control-label">Report Type</label>
                        <select name="report_type" id="report_type" class="form-control">
                            <option value="week_base" <?php echo $report_type == 'Week' ? 'selected' : ''; ?> >Week Report</option>
                            <option value="group_base">Group Report</option>
                            <option value="harvester_base">Harvester Report</option>
                            <option value="variety_base">Variety Report</option>
                        </select>
                    </div>
                </div>
    
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="" class="control-label">Harvest Weeks</label>
                        <select name="week_ids[]" multiple id="week_ids" class="form-control">
                            <option value="all" selected>All</option>
                            <?php $__currentLoopData = $weeks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $week): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($week->id); ?>">Week <?php echo e($week->week_no); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </div>


                
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="" class="control-label">From Date</label>
                        <input type="date" name="from_date" class="form-control">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="" class="control-label">To Date</label>
                        <input type="date" name="to_date" class="form-control">
                    </div>
                </div>
                <div class="col-md-3 group-field hidden">
                    <div class="form-group">
                        <label for="" class="control-label">Harvest Groups</label>
                        <select name="group_ids[]" multiple id="group_ids" class="form-control">
                            <option value="all" selected>All</option>
                            <?php $__currentLoopData = $weeks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $week): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            
                            <option value="<?php echo e($week->id); ?>">Week <?php echo e($week->week_no); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-3 harvester-field hidden">
                    <div class="form-group">
                        <label for="" class="control-label">Harvesters</label>
                        <select name="harvester_ids[]" multiple id="harvester_ids" class="form-control">
                            <option value="all" selected>All</option>
                            <?php $__currentLoopData = $weeks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $week): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($week->id); ?>">Week <?php echo e($week->week_no); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-3 variety-field hidden">
                    <div class="form-group">
                        <label for="" class="control-label">Varieties</label>
                        <select name="variety_ids[]" id="" class="form-control">
                            <option value="all" selected>All</option>
                            <?php $__currentLoopData = $varieties; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $variety): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($variety->id); ?>"><?php echo e($variety->name); ?> - <?php echo e($variety->zvam); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </div>
                <input type="hidden" name="has_filter" value="true" class="form-control">
            </div>
           
            <div class="submit-area mt-3">
                <button type="submit" class="btn btn-sm btn-outline-primary float-right"><i class="mdi mdi-filter-plus-outline"></i> Apply</button>
            </div>
        </form>
    </div>
    <div class="table-responsive p-3 mt-5">
        <table class="table table-condensed table-bordered table-hover table-sm">
            <thead>
                <tr>
                    <?php if($report_type == 'Group'): ?>
                    <th>Group</th>
                    <?php elseif($report_type == 'Harvester'): ?>
                    <th>Name</th>
                    <th>Harvester No</th>
                    <?php elseif($report_type == 'Variety'): ?>
                    <th>Name</th>
                    <th>Variety Code</th>
                    <?php else: ?>
                    <th>Year</th>
                    <th>Harvester Week</th>
                    <?php endif; ?>
                    <th>Report Type</th>
                    <th>From Date</th>
                    <th>To Date</th>
                    <?php $__currentLoopData = $speedColumns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $spc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <th><?php echo e($spc); ?></th>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <th>Average Speed</th>
                    <th>Quality Count</th>
                    
                   
                </tr>
            </thead>
            <tbody>
               <?php $__currentLoopData = $dataArr; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
               <tr>
                    <?php if($report_type == 'Group'): ?>
                    <td><?php echo e($data['group']); ?></td>
                    <?php elseif($report_type == 'Harvester'): ?>
                    <td><?php echo e($data['name']); ?></td>
                    <td><?php echo e($data['har_no']); ?></td>
                    <?php elseif($report_type == 'Variety'): ?>
                    <td><?php echo e($data['name']); ?></td>
                    <td><?php echo e($data['code']); ?></td>
                    <?php else: ?>
                    <td><?php echo e($data['year']); ?></td>
                   <td>Week <?php echo e($data['week']); ?></td>
                    <?php endif; ?>
                   
                   <td><?php echo e($report_type); ?> Report</td>
                   <td><?php echo e($from_date); ?></td>
                   <td><?php echo e($to_date); ?></td>
                   <?php $speedArr = [];?>
                   <?php $__currentLoopData = $speedIds; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php 
                        $total_urcs = isset($data['speeds'][$sp]) ? array_sum($data['speeds'][$sp]['total_cuttings']) : 0;
                        $total_mins =  isset($data['speeds'][$sp]) ? array_sum($data['speeds'][$sp]['total_min']) : 0;
                        $speed = $total_urcs > 0 ? number_format($total_urcs/$total_mins,1) : 0;
                        isset($data['speeds'][$sp]) ? array_push($speedArr,$speed) : '';
                     ?>
                        <td>Urcs <?php echo e($total_urcs); ?> | Min <?php echo e($total_mins); ?> | Speed <?php echo e($speed); ?></td>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <td><?php echo e(number_format(array_sum($speedArr)/sizeof($speedArr),1)); ?></td>
                    <td><?php echo e(array_sum($data['quality_counts'])); ?></td>
               </tr>
               <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
               
            </tbody>
        </table>
    </div>

</main>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script2'); ?>


<script>
$(()=>{
    $('#report_type').on('change',(e)=>{

        var value =  $('#report_type').val()
        if(value == 'week_base'){
            $('.group-field').addClass('hidden');
            $('.harvester-field').addClass('hidden');
            $('.variety-field').addClass('hidden')

        }
        if(value == 'group_base'){
            $('.group-field').removeClass('hidden');
            $('.harvester-field').addClass('hidden');
            $('.variety-field').addClass('hidden')
            
        }
        if(value == 'harvester_base'){
            $('.group-field').removeClass('hidden');
            $('.harvester-field').removeClass('hidden');
            $('.variety-field').addClass('hidden')
            
        }
        if(value == 'variety_base'){
            $('.group-field').removeClass('hidden');
            $('.harvester-field').removeClass('hidden');
            $('.variety-field').removeClass('hidden')
            
        }
    })
})


</script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('prp::layouts.app',['dataTable'=>true,'select2'=>true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/dan/Documents/projects/nuve/imara-lims-v2/Modules/Prp/Resources/views/reports/performanceReport/index.blade.php ENDPATH**/ ?>