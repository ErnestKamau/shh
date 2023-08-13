<?php $__env->startSection('title2'); ?>
<title> Sample Report</title>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('content2'); ?>
<main>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.js" integrity="sha512-s+xg36jbIujB2S2VKfpGmlC3T5V2TF3lY48DX7u2r9XzGzgPsa6wTpOQA7J9iffvdeBN0q9tKzRxVxw1JviZPg==" crossorigin="anonymous"></script>
    <?php
    $items = array(
        array(
            'link' => route('dashboard-lab'),
            'name' => 'Dashboard',
            'icon' => null
        ),
        array(
            'link' => route('lab-reports-home'),
            'name' => 'Lab Reports',
            'icon' => null
        ),
        array(
            'link' => null,
            'name' => $filter['report_name'],
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
    <h5 class="p-4">
        <i class="mdi mdi-clipboard-text-multiple-outline"></i> Lab Reports | <?php echo e($filter['report_name']); ?>

        
    </h5>
  
    <div class="card mt-4 p-0">
        <div class="card-header p-2">
            <!-- <div class="text-center text-primary">
           
            </div> -->
            <div class="card-title text-center mt-0">
                <img src="<?php echo e($company->logo); ?>" class="float-left" style="height: 60px" />

                <div class="float-right mt-3" style="text-align: right;">
                    Date : <?php echo e(date('Y-m-d')); ?> <br>
                    Company Name: <?php echo e($company->name); ?>

                </div>


            </div>

        </div>
        <div class="card-body p-3">
            <h5 class="mb-2 text-primary mt-0 text-center"><b>Laboratory Report</b></h5>
            <h6><b><u>Report Filters</u></b></h6>
            <div class="row no-gutter mb-3 p-4">
                <?php $__currentLoopData = $filter; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k=>$v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php $array_s = ['_token','species','grouped','virus','client',] ?>
                <?php if(!in_array($k,$array_s)): ?>
                <?php
                $t = str_replace('_', ' ', $k);
                $c = ucwords($t);
                if ($k == 'group_by' && $v != 'none') {
                    $y = str_replace('_', ' ', $v);
                    $v = ucwords($y);
                }
                ?>
                <div class="col-sm-3 col-lg-3 col-md-3 p-2">
                    <span><i class="mdi mdi-chevron-right"></i> <?php echo e($c); ?> : <?php echo e($v == ''? '-':$v); ?></span>
                </div>
                <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>


            </div>
            <hr>
            <?php if($filter['group_by'] == 'virus'): ?>
            <div class="table-responsive mt-3">
                <table class="table table-condensed table-hover table-bordered table-striped table-sm">
                    <thead>
                        <tr>
                            <th>Virus</th>
                            <th>Jan</th>
                            <th>Feb</th>
                            <th>March</th>
                            <th>April</th>
                            <th>May</th>
                            <th>June</th>
                            <th>July</th>
                            <th>Aug</th>
                            <th>Sep</th>
                            <th>Oct</th>
                            <th>Nov</th>
                            <th>Dec</th>
                            <th>Total Samples </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $samples; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key=>$data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($key); ?></td>
                            <td><?php echo e($data['Jan']); ?></td>
                            <td><?php echo e($data['Feb']); ?></td>
                            <td><?php echo e($data['March']); ?></td>
                            <td><?php echo e($data['April']); ?></td>
                            <td><?php echo e($data['May']); ?></td>
                            <td><?php echo e($data['June']); ?></td>
                            <td><?php echo e($data['July']); ?></td>
                            <td><?php echo e($data['Aug']); ?></td>
                            <td><?php echo e($data['Sept']); ?></td>
                            <td><?php echo e($data['Oct']); ?></td>
                            <td><?php echo e($data['Nov']); ?></td>
                            <td><?php echo e($data['Dec']); ?></td>
                            <td><?php echo e($data['total']); ?></td>

                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="table-responsive mt-3">
                <table class="table table-condensed table-hover table-bordered  table-sm">
                    <thead>
                        <tr>
                            <th>Week</th>
                            <th>Virus</th>
                            <th>No of Samples</th>
                        </tr>
                    </thead>
                    <tbody>
                       <?php $__currentLoopData = $samples; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr class="bg-light">
                                <td > <b>Client : </b> <?php echo e($record['name']); ?></td>
                                <td></td>
                                <td></td>
                            </tr>
                            <?php $__currentLoopData = $record['data']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $week_batch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr class="bg-light">
                                    <td><b>Week :</b> <?php echo e($week_batch['week']); ?> - <?php echo e($week_batch['year']); ?>  </td>
                                    <td><b>Total Samples Requested :</b> <?php echo e($week_batch['total_count']); ?></td>
                                    <td></td>
                                </tr>
                                <?php $__currentLoopData = $week_batch['analytes']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td></td>
                                        <td><?php echo e($data['analyte_code']); ?></td>
                                        <td><?php echo e($data['count']); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?> 
                            
                       <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                    </tbody>
                </table>
            </div>
            <?php endif; ?>
          
        </div>

    </div>


</main>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script2'); ?>
<script>

</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.lab.layout.app', ['dataTable'=>true, 'select2'=>true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/dan/Documents/projects/nuve/imara-lims-v2/resources/views/layouts/lab/reports/virus_show.blade.php ENDPATH**/ ?>