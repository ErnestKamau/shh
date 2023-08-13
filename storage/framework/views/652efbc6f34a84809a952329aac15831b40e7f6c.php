<?php $__env->startSection('title2'); ?>
<title>Dashboard | Lab </title>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.js" integrity="sha512-s+xg36jbIujB2S2VKfpGmlC3T5V2TF3lY48DX7u2r9XzGzgPsa6wTpOQA7J9iffvdeBN0q9tKzRxVxw1JviZPg==" crossorigin="anonymous"></script>
<style type="text/css">
    .my-card {
        position: absolute;
        left: 40%;
        top: -20px;
        border-radius: 50%;
    }

    #activity-graph {
        height: 250px;
    }
</style>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content2'); ?>
<main>

    <?php
    $items = array(
        array(
            'link' => null,
            'name' => 'Configurations',
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
    <h2 class="p-1">
        <i class="mdi mdi-desktop-mac-dashboard"></i> Dashboard | Laboratory
        <div class="dropleft float-right">

            <span style="font-size:15px;border-radius: 3em;border-color: white;background-color:white;position: 0 0;size: 100%;" class="float-right mr-5 mt-3  p-2 btn dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">Notifications
                <small class="float-right badge badge-pill badge-primary mt-1 ml-1"><?php echo e($notifications->count()); ?></small></span>

            <div class="dropdown-menu p-2" style="max-height: 70vh; overflow:auto" aria-labelledby="dropdownMenuButton">
                <?php $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $note): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a class="dropdown-item card mb-2" style="box-shadow: 2px 2px 2px 2px;" href="<?php echo e(route('view-batch-details',['batch'=>$note->batch_id])); ?>">
                    <div class="card-bodys">
                        <?php echo e($note->notification); ?>

                        <br>
                        <span class="float-right mb-0 mt-3"><?php echo e($note->created_at); ?></span>
                    </div>
                </a>

                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            </div>
        </div>
    </h2><br>
    <div class="row mb-3">
        <div class="col-xl-3 col-sm-6 ">
            <a href="/sample-workflow/Samples Reception/stage" class="card  bg-success text-white text-center  no-overflow" style="height:100%">
                <div class="card-body bg-success">
                    <div class="rotate">
                        <i class="mdi mdi-test-tube fa-4x"></i>
                    </div>
                    <h6 class="text-uppercase">Samples In Reception</h6>
                    <br><br>
                    <h1 class="display-4"><?php echo e($samples_reception); ?></h1>
                </div>
            </a>
        </div>


        <div class="col-xl-3 col-sm-6">
            <a href="/sample-workflow/Sample Verification/stage" class="card bg-danger text-white text-center h-100 no-overflow">
                <div class="card-body bg-danger">
                    <div class="rotate">
                        <i class="fas fa-list fa-4x"></i>
                    </div>
                    <h6 class="text-uppercase">Samples In Verification</h6>
                    <br><br>
                    <h1 class="display-4"><?php echo e($samples_verification); ?></h1>
                </div>
            </a>
        </div>


        <div class="col-xl-3 col-sm-6">
            <a href="/sample-workflow/Samples In Lab/stage" class="card bg-info text-white text-center h-100 no-overflow">
                <div class="card-body bg-info">
                    <div class="rotate">
                        <i class="mdi mdi-test-tube fa-4x"></i>
                    </div>
                    <h6 class="text-uppercase">Samples In Lab</h6>
                    <br><br>
                    <h1 class="display-4"><?php echo e($samples_lab); ?></h1>
                </div>
            </a>
        </div>


        <div class="col-xl-3 col-sm-6 ">
            <a href="/sample-workflow/Sample Approval" class="card bg-dark text-white h-100  text-center no-overflow">
                <div class="card-body bg-dark">
                    <div class="rotate">
                        <i class="fas fa-list fa-4x"></i>
                    </div>
                    <h6 class="text-uppercase">Samples In Approval</h6>
                    <br><br>
                    <h1 class="display-4"><?php echo e($samples_approval); ?></h1>
                </div>
            </a>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-xl-6 col-sm-12">
            <div class="card bg-default no-overflow">
                <div class="card-head-sm p-3 border-bottom">
                    <h5>
                        <i class="mdi mdi-map-marker"></i> Location Map
                        <small class="float-right text-info"><i class="fas fa-calendar"></i></small>
                    </h5>
                </div>
                <div class="card-body">
                    <div id="sample-maps" style="width: 100%;height:300px"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-6 col-sm-12 " ">
            <div class=" card bg-default no-overflow">
            <div class="card-head-sm p-3 border-bottom">
                <h5>
                    <i class="fas fa-line-chart"></i> Samples By Client
                    <form class="float-right text-info">
                        <div class="input-group">
                            <input type="number" max="2100" name="unit" value="" min="2000" style="border:0px solid;border-bottom:1px solid" id="yearSubmitFormClient" data-url="getSamplesByCustomer" data-chart="mychart3" data-graphfunction="Crmgraph" class=" form-control" data-divid="sample-crm-graph" placeholder="Search by Year">

                        </div>
                    </form>
                </h5>
            </div>
            <div class="card-body">
                <canvas id="sample-crm-graph" style="width: 100%;height:300px"> </canvas>
            </div>
        </div>
    </div>


    </div>
    <div class="row mb-3">
        <div class="col-xl-6 col-sm-12 ">
            <div class="card bg-default no-overflow">
                <div class="card-head-sm p-3 border-bottom">
                    <h5>
                        <i class="fas fa-line-chart"></i> Samples By Month.
                        <form class="float-right text-info">
                            <div class="input-group">
                                <input type="number" name="month" value="" max="2100" min="2000" style="border:0px solid;border-bottom:1px solid" class="form-control" id="yearSubmitFormMonth" data-url="getSamplesByMonth" data-chart="mychart2" data-graphfunction="SamplesGraph" data-divid="sample-graph" placeholder="Search by Year">

                            </div>
                        </form>
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="sample-graph" style="width: 100%;height:300px"></canvas>
                </div>
            </div>
        </div>
        <!-- </div> -->
        <!-- <div class="row mb-5"> -->
        <div class="col-xl-6 col-sm-12">
            <div class="card bg-default no-overflow">
                <div class="card-head-sm p-3 border-bottom">
                    <h5>
                        <i class="fas fa-line-chart"></i> Sample Types
                        <form class="float-right text-info">
                            <div class="input-group">
                                <input type="number" name="sample_type" value="" max="2100" min="2000" style="border:0px solid;border-bottom:1px solid" class="form-control" id="yearSubmitForm" data-url="getsamplesBySampletype" data-chart="'mychart" data-divid="myChart" data data-graphfunction="SampletypeGraph" placeholder="Search by Year">

                            </div>
                        </form>
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="myChart" style="width: 100%;height:300px"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="card tab-card">
        <div class="card-header tab-card-header">
            <ul class="nav nav-tabs card-header-tabs" id="equipment-tab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="samples" data-toggle="tab" href="#samples-tab" role="tab" aria-controls="samples" aria-selected="true"><i class="mdi mdi-test-tube"></i> Batch (es) </a>

            </ul>
        </div>
        <div class="table-responsive bg-light p-4">
            <u>
                <small class="text-danger" style="font-weight:900">*First 100 batches*</small>

            </u>

            <table class="table table-condensed my-small-text table-bordered table-sm">
                <thead>
                    <th></th>
                    <th>Priority</th>
                    <th>Batch Code</th>
                    <th nowrap>Receipt Date</th>
                    <th nowrap>Date Collected</th>
                    <th nowrap>Target Date</th>
                    <th nowrap>Status Days</th>
                    <th>Samples</th>
                    <th>Client</th>
                    <th>Client Unit</th>
                    <th>Lab</th>
                    <th nowrap>Sample Type</th>
                    <th>Ref No</th>
                    <th nowrap>Tracking Stage</th>
                    <th>Routine</th>
                    <th>Routine Frequency</th>

                </thead>
                <tbody>
                    <?php $__currentLoopData = $samples; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php

                    $a = $item->get_date('Target Date');
                    if (isset($a->id)) {

                        $target_date = $item ? date('Y-m-d', strtotime($a['date'])) : '';

                        $target_date = Carbon\Carbon::parse($target_date);

                        $now = Carbon\Carbon::now();
                        $diff = $now->diffInDays($target_date);

                        if ($target_date->greaterThan($now)) {
                            $diff = 0 - $diff;
                        }
                    }

                    ?>
                    <tr class="batch-row ">
                        <td><?php echo e($loop->iteration); ?></td>
                        <td nowrap><?php echo $item->priority != "Normal" ? '<i class="mdi mdi-star text-danger"></i>' : ''; ?> <?php echo e($item->priority); ?></td>
                        <td><a href="<?php echo e(route('view-batch-details', ['batch'=>$item->id])); ?>"><?php echo e($item->batch_code); ?></a></td>
                        <td nowrap><?php echo e(date('Y-m-d', strtotime($item->receipt_date))); ?></td>
                        <td nowrap><?php echo e(date('Y-m-d', strtotime($item->date_collected))); ?></td>
                        <td nowrap><?php echo e(date('Y-m-d', strtotime($target_date ?? ''))); ?></td>
                        <td nowrap><?php echo e(number_format($diff ?? 0, 0)); ?> Day(s)</td>
                        <td><?php echo e($item->samples->count()); ?></td>
                        <td nowrap><?php echo e($item->client->name); ?></td>
                        <td nowrap><?php echo e($item->crm_unit_name); ?></td>
                        <td nowrap><?php echo e(implode(", ", $item->labs(true))); ?></td>
                        <td nowrap><?php echo e($item->sample_type->name ?? ''); ?></td>
                        <td nowrap><?php echo e($item->reference_number ?? 'n/a'); ?></td>
                        <td nowrap><?php echo e($item->tracking_stage()->name ?? 'n/a'); ?></td>
                        <td><?php echo e($item->is_routine == 1 ? 'Yes' : 'No'); ?></td>
                        <td><?php echo e($item->is_routine == 1 ? number_format($item->routine_frequency,0).' days' : 'n/a'); ?></td>

                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </div>

</main>

<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&key=AIzaSyBqS4AEZ-gVeXjG794Rh0eTd6yvdfMKTjg&sensor=false" type="text/javascript"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
    $(function() {

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



        var sampletypeGraph = function(data, mychart) {
            var total = Object.values(data);
            var namearr = Object.keys(data);
            let massPopchart = new Chart(mychart, {
                type: 'bar',
                data: {
                    labels: namearr,
                    datasets: [{
                        label: 'Batch (es)',
                        data: total,
                        backgroundColor: poolColors(total.length),
                        borderColor: poolColors(total.length),
                        hoverBorderWidth: 1,
                        hoverBorderColor: '#000',
                        // backgr
                    }]
                },
                options: {
                    title: {
                        display: true,
                        text: 'Samples Types',
                        fontSize: 15,
                        fontColor: '#000'
                    },
                    legend: {
                        display: false,
                    },
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: true
                            }
                        }]
                    }
                }
            });

        }


        var samplesGraph = function(samples, chart2) {
            var results = Object.values(samples);

            let massPop = new Chart(chart2, {
                type: 'bar',
                data: {
                    labels: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
                    datasets: [{
                        label: 'Batch (es)',
                        data: results,
                        backgroundColor: 'rgba(54,162,235,0.6)',
                        hoverBorderWidth: 1,
                        hoverBorderColor: '#000',
                    }]
                },
                options: {
                    title: {
                        display: true,
                        text: 'Samples by month',
                        fontSize: 15,
                        fontColor: '#000'
                    },
                    legend: {
                        display: false,

                    },
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: true
                            }
                        }]
                    }
                }
            })

        }


        var mapsGraph = function(gps) {
            var mapProp = {
                center: new google.maps.LatLng(-1.247125519439578, 36.742261815816164),
                zoom: 5.5,
            };
            map = new google.maps.Map(document.getElementById('sample-maps'), mapProp);
            var lat = Object.keys(gps);
            console.log(lat);
            for (var i = 0; i < lat.length; i++) {


                var lati = lat[i].split(',');
                // console.log(latit);

                var latitude = parseFloat(lati[1]);
                var longitude = parseFloat(lati[0]);

                // console.log(gps[lat[i]]);
                var marker = new google.maps.Marker({
                    position: new google.maps.LatLng(latitude, longitude),
                    title: `Total samples ${gps[lat[i]]}`,
                });
                marker.setMap(map)
            }

        }
        // console.log(result);


        var CrmGraph = function(data, mychart3) {

            var unit_names = Object.keys(data);
            var unit_values = Object.values(data);

            // console.log(unit_names)
            let masspop2 = new Chart(mychart3, {
                type: 'bar',
                data: {
                    labels: unit_names,
                    datasets: [{
                        label: 'Samples',
                        data: unit_values,
                        backgroundColor: poolColors(unit_values.length),
                        borderColor: poolColors(unit_values.length),
                        hoverBorderWidth: 1,
                        hoverBorderColor: '#000',
                    }]

                },
                options: {
                    title: {
                        display: true,
                        text: 'Samples by client',
                        fontSize: 15,
                        fontColor: '#000'
                    },
                    legend: {
                        display: false,
                    },
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: true
                            }
                        }]
                    }
                }
            })
        }
        let mychart = document.getElementById('myChart').getContext('2d');
        let mychart2 = document.getElementById('sample-graph').getContext('2d');
        let mychart3 = document.getElementById('sample-crm-graph').getContext('2d');

        $.ajax({
            url: "<?php echo e(route('getSamplesByCustomer')); ?>",
            success: function(data) {
                // console.log(data);
                CrmGraph(data, mychart3)

            },
            error: function(data) {
                console.log(data);
            }
        })
        $.ajax({
            url: "<?php echo e(route('getSamplesByGps')); ?>",
            success: function(data) {
                // console.log(data);
                mapsGraph(data)

            },
            error: function(data) {
                console.log(data);
            }
        })
        $.ajax({
            url: "<?php echo e(route('getSamplesByMonth')); ?>",
            success: function(data) {
                // console.log(data);
                samplesGraph(data, mychart2)

            },
            error: function(data) {
                console.log(data);
            }
        })
        $.ajax({
            url: "<?php echo e(route('getsamplesBySampletype')); ?>",
            success: function(data) {
                console.log('Sampletypes');
                console.log(data);
                sampletypeGraph(data, mychart)

            },
            error: function(data) {
                console.log(data);
            }
        })
        $('#yearSubmitForm').on('change', function(e) {
            console.log('testing hard')
            var year = $(this).val();

            $.ajax({
                url: "<?php echo e(route('getsamplesBySampletype')); ?>",
                data: {
                    year : year
                },
                type: "GET",
                success: function(data) {
                    $('myChart').empty();
                    sampletypeGraph(data, mychart)

                },
                error: function(data) {
                    console.log(data);
                }
            })
        });
        $('#yearSubmitFormClient').on('change', function(e) {
            console.log('testing hard')
            var year = $(this).val();

            $.ajax({
                url: "<?php echo e(route('getSamplesByCustomer')); ?>",
                data: {
                    year : year
                },
                type: "GET",
                success: function(data) {
                    $('#sample-crm-graph').empty();
                    CrmGraph(data, mychart3);

                },
                error: function(data) {
                    console.log(data);
                }
            })
        });
        $('#yearSubmitFormMonth').on('change', function(e) {
            console.log('testing hard')
            var year = $(this).val();

            $.ajax({
                url: '<?php echo e(route("getSamplesByMonth")); ?>',
                data: {
                    year : year
                },
                type: "GET",
                success: function(data) {
                    $('#sample-graph').empty();
                    samplesGraph(data, mychart2)

                },
                error: function(data) {
                    console.log(data);
                }
            })
        });

    });




    // console.log(lat.length);
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.lab.layout.app', ['dataTable'=>true, 'select2'=>true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/kimari/Projects/polucon/resources/views/layouts/lab/dashboard.blade.php ENDPATH**/ ?>