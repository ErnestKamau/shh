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
            'link' => '/prp/report/Quality-Performance/Report',
            'name' => 'Quality Report',
            'icon' => null,
        ),
        array(
            'link' => '#',
            'name' => 'Week Quality Report',
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
        <i class="mdi mdi-file-account-outline"></i> Week Based Quality Summary Reports
    </h5>
    
    <div class="header-5 mt-3 p-2">
        <p><b><u>Parameter Report</u></b></p>
        <div class="row p-1">
            <div class="col-md-3">
                <span class="text-muted"  style="font-weight:600" ><i class="mdi mdi-chevron-right"></i> From Date</span><br>
                <span class="pl-3" ><?php echo e($from_date); ?></span>
            </div>
            <div class="col-md-3">
                <span class="text-muted"  style="font-weight:600"><i class="mdi mdi-chevron-right"></i> To Date</span> <br>
                <span class="pl-3"><?php echo e($to_date); ?></span>
            </div>
            <div class="col-md-3">
                <span class="text-muted"  style="font-weight:600"><i class="mdi mdi-chevron-right"></i> Source</span><br>
                <span class="pl-3"><?php echo e($source); ?></span>
            </div>
           

        </div>
      
    </div>
    <div id="image-chart" style="width:100%;height:400px;margin-bottom:150px" >
        <div id="bar-chart" style="width:100%;height:400px" ></div>

    </div>
    
    <div class="table-responsive p-3 mt-4">
        <table class="table table-condensed table-bordered table-hover table-sm">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Harvester Week</th>
                    <th>Year</th>
                    <th>From Date</th>
                    <th>To Date</th>
                    <th>Total Count</th>
                    <?php $__currentLoopData = $qualities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $quality): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <th><?php echo e($quality); ?></th>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tr>
            </thead>
            <tbody>
               <?php $__currentLoopData = $data['data']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $week_data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
               <tr>
                   <td><?php echo e($loop->iteration); ?></td>
                   <td>Week <?php echo e($week_data['name']); ?></td>
                   <td><?php echo e($week_data['year']); ?></td>
                   <td><?php echo e($from_date); ?></td>
                   <td><?php echo e($to_date); ?></td>
                   <td><?php echo e($week_data['quality_total']); ?></td>
                   <?php $__currentLoopData = $qualities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $quality): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <td><?php echo e($week_data['quality'][$quality] ?? 0); ?></td>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
               </tr>
               <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
               
            </tbody>
        </table>
    </div>

</main>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script2'); ?>
<div id="graph_data" data-graphdata = "<?php echo e(json_encode($data['graph_data'])); ?>" data-columns = "<?php echo e(json_encode($data['columns'])); ?>" ></div>
<script src="http://cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.min.js" integrity="sha512-6Cwk0kyyPu8pyO9DdwyN+jcGzvZQbUzQNLI0PadCY3ikWFXW9Jkat+yrnloE63dzAKmJ1WNeryPd1yszfj7kqQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.css" integrity="sha512-fjy4e481VEA/OTVR4+WHMlZ4wcX/+ohNWKpVfb7q+YNnOCS++4ZDn3Vi6EaA2HJ89VXARJt7VvuAKaQ/gs1CbQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.js" integrity="sha512-CYkt9kgBjbQrIKQGbyezfkmhmmFwMF2VVZjdGgYJJm3KnV53Ao+aFnndz2YbmMX2Y8XGBCUzBxFTqg2LAuIJzw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<script>
$(()=>{
    function dynamicColors() {
        var r = Math.floor(Math.random() * 255);
        var g = Math.floor(Math.random() * 255);
        var b = Math.floor(Math.random() * 255);
        return "rgba(" + r + "," + g + "," + b + ")";
    }

    function poolColors(a) {
        var pool = [];
        for (i = 0; i < a; i++) {
            pool.push(dynamicColors());
        }
        return pool;
    }
    var graph_data = $('#graph_data').data('graphdata')
    var column = $('#graph_data').data('columns')
    console.log(column)
    config = {
        data: graph_data,
        xkey: 'name',
        ykeys: column,
        labels: column,
        barColors: poolColors(column.length),
        xLabelAngle: 45,

    };

    config.element = 'bar-chart';
    Morris.Bar(config);
    $('svg').height(700);
})


</script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('prp::layouts.app',['dataTable'=>true,'select2'=>true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/dan/Documents/projects/nuve/imara-lims-v2/Modules/Prp/Resources/views/reports/qualityPerformanceReport/weekbase.blade.php ENDPATH**/ ?>