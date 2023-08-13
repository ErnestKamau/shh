<?php $__env->startSection('title2'); ?>
<style>
    .col-xl-4 {
        padding: 1.5%;
    }

    .text-p {
        color: rgba(6, 124, 75, 1);
        font-weight: 600;
    }



    .border-0 {
        padding: 10px;
    }

    .branch-head {
        font-size: 20px;
        font-weight: 600;
    }

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

    .col-sm-6 {
        margin-bottom: 5%;
        padding: 1%;
    }

    .card-error {
        background-color: rgba(6, 124, 75, 0.2) !important;
    }

    .card-error .text-p {
        font-size: 13px;
    }

    .small-card {
        width: 60%;
        padding: 10px;
    }
</style>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content2'); ?>
<main>
    <?php

    $items = array(
        array(
            'link' => '',
            'name' => 'PRP',
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
    <div class="p-3 d-flex flex-wrap">
        <span style="font-weight: 550;font-size:18px">
            <i class="mdi mdi-view-dashboard-outline mdi-24px mr-2"></i> Dashboard <span id="week_data"></span>
        </span>

        <form action="<?php echo e(route('changeHarvestWeek')); ?>" method="post" class="d-flex flex-wrap" style="margin:auto">
            <?php echo csrf_field(); ?>
            <?php $active_week = session()->get('harvestWeekSessionVariable'); ?>
            <div class="form-group p-2">
                <select name="harvest_week_id" id="" class="form-control">
                    <option value="">Choose Harvest Week</option>
                    <?php $__currentLoopData = $harvest_weeks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $h_week): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($h_week->id); ?>" <?php echo e($h_week->id == $active_week->id ? 'selected': ''); ?>>Week <?php echo e($h_week->week_no); ?> - <?php echo e($h_week->weekstatus); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <button type="submit" class="btn btn-sm ml-3" style="height: 70%;"><i class="mdi mdi-cloud-sync text-success mdi-24px"></i></button>
        </form>

        <a href="<?php echo e(route('showharvestWeeksList')); ?>" class="btn btn-default text-warning btn-sm"><i class="mdi mdi-format-list-bulleted"></i> List Weeks</a>
        <span class="btn text-primary btn-sm btn-default" data-toggle="modal" data-target="#create_week"><i class="mdi mdi-calendar-plus"></i> Create Week</span>
    </div>

    <div class="row">
        <a href="<?php echo e(route('showPrdRawDataHarvesters')); ?>" class="col-xl-4 col-md-6 border-0">
            <div class="col-md-12 card">
                <div class="card-body">

                    <div class="lead">
                        <span class="btn btn-default" style="border-radius: 50%;background-color:rgba(6, 124, 75, 0.2);"> <i class="mdi mdi-account-multiple mdi-24px"></i></span>

                        <h4 class="float-right text-p" id="harvesters-count">0</h4>
                    </div>
                    <h6 class="mt-5">Harvesters</h6>

                </div>

            </div>
        </a>
        <a href="<?php echo e(route('timesheet-batch-index')); ?>" class="col-xl-4 col-md-6  border-0">
            <div class="col-md-12 card">
                <div class="card-body">

                    <div class="lead">
                        <span class="btn btn-default" style="border-radius: 50%;background-color:rgba(6, 124, 75, 0.2);"> <i class="mdi mdi-account-clock mdi-24px"></i></span>

                        <h4 class="float-right text-p" id="timesheet-count">0</h4>
                    </div>
                    <h6 class="mt-5">Timesheets</h6>

                </div>
            </div>
        </a>
        <a href="<?php echo e(route('hw-quality-remarks-index')); ?>" class="col-xl-4 col-md-6  border-0">
            <div class="col-md-12 card">

                <div class="card-body">

                    <div class="lead">
                        <span class="btn btn-default" style="border-radius: 50%;background-color:rgba(6, 124, 75, 0.2);"> <i class="mdi mdi-check-box-multiple-outline mdi-24px"></i></span>

                        <h4 class="float-right text-p" id="types-count">0</h4>
                    </div>
                    <h6 class="mt-5">Quality Remarks</h6>

                </div>
            </div>
        </a>
    </div>

    <div class="other mt-5">
        <div class="branches-content" style="display: flex;">
            <div class="part-one" style="width: 40%;">
          
                <a href="<?php echo e(route('payslip-index')); ?>" class="branches card" style="background-color: rgba(245, 129, 30, 0.3);">
                    <div class="card-body p-4">
                        <div class="">
                            <span class="float-right"><i class="mdi mdi-account-cash-outline mdi-48px" style="color: rgba(245, 129, 30, 1);"></i></span>
                            <span class="branch-head" style="color: rgba(232, 69, 69, 1);">Payslips</span>
                        </div>
                        <br>
                        <span style="color: rgba(232, 69, 69, 0.8);font-size: 17px;">
                            Process, View and Print Payslips <br> for week
                        </span>
                    </div>

                </a>
                <a href="<?php echo e(route('payslip-index')); ?>" class="service-product card mt-5" style="background-color: rgba(6, 124, 75, 1);color: white;">
                    <div class="card-body p-4">
                        <div class="">

                            <span class="float-right"><i class="mdi mdi-cogs mdi-48px"></i></span>
                            <span class="branch-head">Harvester Payslip List</span>
                        </div>
                        <br>
                        <span style="font-size: 17px;">
                            View and Print Harvester Payslip List
                        </span>
                    </div>
                </a>
            </div>
            <div class="in-large-view" style="width: 5%;"></div>
            <div class="part-two " style="width: 55%;">
                <div style="background-color: white;width: 100%;min-height: 100%;">
                    <h6 class=""> <span style="font-weight: 550 !important;font-size: 18px;">Speed Chart</span> </h6>
                    <div id="bar-chart" style="min-height:250px !important"></div>
                    <h6 class=""><span style="font-weight: 550 !important;font-size: 18px;">Harvest Week Details</span></h6>


                </div>
            </div>

        </div>
        <div class="d-flex mt-2 justify-content-around">
            <div class="small-card">
                <div class="card card-error">
                    <div class="card-body">
                        Gh Inspected Bags <span id="gh_inspected_count" class="float-right text-p">0</span>

                    </div>
                </div>
            </div>
            <div class="small-card">
                <div class="card card-error">
                    <div class="card-body">
                        Packhouse Inspected Bags <span id="pack_inspected_count" class="float-right text-p">0</span>
                    </div>
                </div>
            </div>
            <div class="small-card">
                <div class="card card-error">
                    <div class="card-body">
                        Total Inspected Bags <span class="float-right text-p" id="total_inspected_count">0</span>
                    </div>

                </div>
            </div>
            
        </div>
        <div class="d-flex mt-2 justify-content-around">
            <div class="small-card">
                <div class="card card-error">
                    <div class="card-body">
                        PRD Files <span id="prd_file_count" class="float-right text-p">0</span>

                    </div>
                </div>
            </div>
            <div class="small-card">
                <div class="card card-error">
                    <div class="card-body">
                        Species <span id="quality_count" class="float-right text-p">0</span>
                    </div>
                </div>
            </div>
            <div class="small-card">
                <div class="card card-error">
                    <div class="card-body">
                        Varieties <span class="float-right text-p" id="destination_count">0</span>
                    </div>

                </div>
            </div>
            <div class="small-card">
                <div class="card card-error">
                    <div class="card-body">
                        Groups <span class="float-right text-p" id="variety_count">0</span>
                    </div>

                </div>
            </div>
            <div class="small-card">
                <div class="card card-error">
                    <div class="card-body">
                        No of Urcs <span class="float-right text-p" id="urcs_count">0</span>
                    </div>

                </div>
            </div>
        </div>
        
    </div>



</main>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script2'); ?>
<div class="modal fade" id="create_week" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo e(route('create-update-harvest-week')); ?>" method="post">
                <?php echo csrf_field(); ?>
                <div class="modal-body">


                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-outline-primary btn-sm"><i class="mdi mdi-content-save"></i> Save</button>
                    <span class="btn btn-sm btn-default" data-dismiss="modal">Close</span>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="http://cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js"></script>

<!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script> -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.min.js" integrity="sha512-6Cwk0kyyPu8pyO9DdwyN+jcGzvZQbUzQNLI0PadCY3ikWFXW9Jkat+yrnloE63dzAKmJ1WNeryPd1yszfj7kqQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.css" integrity="sha512-fjy4e481VEA/OTVR4+WHMlZ4wcX/+ohNWKpVfb7q+YNnOCS++4ZDn3Vi6EaA2HJ89VXARJt7VvuAKaQ/gs1CbQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.js" integrity="sha512-CYkt9kgBjbQrIKQGbyezfkmhmmFwMF2VVZjdGgYJJm3KnV53Ao+aFnndz2YbmMX2Y8XGBCUzBxFTqg2LAuIJzw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
    $(() => {

        let getActiveWeek = (callback) => {
            $.ajax({
                url: "/prp/get/Next/Week-No/Data/Ajax",
                method: "GET",
                success: (data) => {
                    callback(data);
                },
                error: (data) => {
                    console.log(data);
                }
            })
        }
        let createWeekBody = (data = false) => {
            if (data) {
                if (data.is_data == 1) {
                    var body = $(`
                        <div class="alert alert-primary p-2 d-flex">
                            <i class="mdi mdi-alert-decagram mdi-24px"></i>
                            <div class="p-2">
                                Kindly confirm you want to generate a new harvest week. <br>
                                Week ${data.week} was found to be active. Kindly choose below the status you wish to assign the previous week. <br><br>
                                <select name="action" required id="status-select" class="form-control">
                                    <option value="">Choose Status</option>
                                    <option value="1">Partially Closed</option>
                                    <option value="2">Closed</option>
                                </select>
                                <p class="mt-3"> Confirm week data below.</p>
                                <div class="form-group mt-2">
                                    <label class="control-label">Week <span class="text-danger">*</span> No</label>
                                    <input type="text" value="${parseInt(data.week) + 1}" name="week_no" required class="form-control">
                                </div>
                                <div class="form-group">
                                    <label class="control-label">Year <span class="text-danger">*</span></label>
                                    <input type="text" name="year" value="<?php echo e(date('Y')); ?>" required class="form-control">
                                </div>
                                <input type="hidden" name="week_id" value="0">
                            </div>
                            
                        </div>
                       
                        
                    `).clone();
                } else {
                    var body = $(`
                        <div class="alert alert-primary p-2 d-flex">
                            <i class="mdi mdi-alert-decagram mdi-24px"></i>
                            <div class="p-2">
                                Kindly confirm you want to generate a new harvest week. <br>
                                The week no will start after week no ${data.week} as set in the configurations.<br>
                                <p class="mt-3"> Confirm week data below.</p>
                                <input type="hidden" name="week_id" value="0">
                                <div class="form-group mt-2">
                                    <label class="control-label">Week <span class="text-danger">*</span> No</label>
                                    <input type="text" name="week_no" value="${parseInt(data.week) + 1}" required class="form-control">
                                </div>
                                <div class="form-group">
                                    <label class="control-label">Year <span class="text-danger">*</span></label>
                                    <input type="text" name="year" value="<?php echo e(date('Y')); ?>" required class="form-control">
                                </div>
                            </div>
                            
                        </div>
                       
                        
                    `).clone();
                }
            } else {
                var body = $(`
                    <div class="alert alert-primary p-2 d-flex">
                        <i class="mdi mdi-alert-decagram mdi-24px"></i>
                        <span class="p-2">
                            Kindly confirm you want to generate a new harvest week.
                            <input type="hidden" name="week_id" value="0">
                        </span>
                    </div>
                    
                `).clone();
            }
            return body;

        }
        $('#create_week').on('show.bs.modal', () => {
            console.log('data');
            getActiveWeek((data) => {
                var CBody = createWeekBody(data);
                $(CBody).find('#status-select').select2();
                $('#create_week').find('.modal-body').empty();
                $('#create_week').find('.modal-body').append(CBody);
            });

        })
        let getCurrentweekOnSession = (callback) => {
            $.ajax({
                url: '/prp/get/harvest-week/session/Variable/Ajax',
                method: 'GET',
                success: (data) => {
                    callback(data);
                },
                error: (data) => {
                    console.log(data);
                }
            });
        }
        getCurrentweekOnSession((data) => {

            $('#week_data').empty();
            $('#week_data').append(` | Week ${data.week_no}`)
            $('#active_year').val(data.year);
            $('#active_week').val(data.week_no);
            getNoOfHarvesters(data.id);
            getNoOfTimesheets(data.id);
            getNoOfQualityRemarks(data.id);
            getNoOfPrdFiles(data.id);
            populateSpeedChart(data.id);
            getInspectedBagsReport(data.id)
        });

        let getNoOfHarvesters = (week_id) => {
            $.ajax({
                url: `/prp/get/HarvestWeek/Harvesters/${week_id}/Ajax`,
                method: 'GET',
                success: (data) => {
                    $('#harvesters-count').empty();
                    $('#harvesters-count').append(data);
                },
                error: (data) => {
                    console.log(data);
                }
            });
        }
        let getNoOfQualityRemarks = (week_id) => {
            $.ajax({
                url: `/prp/get/HarvestWeek/QualityRemarks/${week_id}/Ajax`,
                method: 'GET',
                success: (data) => {
                    $('#types-count').empty();
                    $('#types-count').append(data);
                },
                error: (data) => {
                    console.log(data);
                }
            });
        }
        let getNoOfTimesheets = (week_id) => {
            $.ajax({
                url: `/prp/get/HarvestWeek/Timesheets/${week_id}/Ajax`,
                method: 'GET',
                success: (data) => {
                    $('#timesheet-count').empty();
                    $('#timesheet-count').append(data);
                },
                error: (data) => {
                    console.log(data);
                }
            });
        }
        let getNoOfPrdFiles = (week_id) => {
            $.ajax({
                url: `/prp/get/HarvestWeek/PrdFiles/${week_id}/Ajax`,
                method: 'GET',
                success: (data) => {
                    $('#prd_file_count').empty();
                    $('#prd_file_count').append(data['prds']);

                    $('#quality_count').empty();
                    $('#quality_count').append(data['species']);

                    $('#destination_count').empty();
                    $('#destination_count').append(data['variety']);

                    $('#variety_count').empty();
                    $('#variety_count').append(data['groups']);

                    $('#urcs_count').empty();
                    $('#urcs_count').append(data['urcs']);
                },
                error: (data) => {
                    console.log(data);
                }
            });
        }
        let populateSpeedChart = (week_id) => {
            var colors = ['green', '#ffbf00', '#d2222d'];
            $.ajax({
                url: `/prp/fetch/speeds/params/${week_id}/ajax`,
                method: "GET",
                success: (data) => {

                    config = {
                        data: data,
                        xkey: 'name',
                        ykeys: ['max_speed', 'average', 'low_speed'],
                        labels: ['Highest Speed', 'Average Speed', 'Lowest Speed'],
                        fillOpacity: 0.6,
                        hideHover: 'auto',
                        behaveLikeLine: true,
                        resize: true,
                        pointFillColors: ['#ffffff'],
                        pointStrokeColors: ['black'],
                        lineColors: ['gray', 'red'],
                        barColors: colors

                    };

                    config.element = 'bar-chart';
                    Morris.Bar(config);

                },
                error: (data) => {
                    console.log(data);
                }
            })
        }
        let getInspectedBagsReport = (week_id)=>{
            $.ajax({
                url:`/prp/get/InspectedBags/${week_id}/ajax`,
                method:'GET',
                success:(data)=>{
                    $('#total_inspected_count').empty();
                    $('#total_inspected_count').append(data['total'])

                    $('#gh_inspected_count').empty();
                    $('#gh_inspected_count').append(data['gh']);

                    $('#pack_inspected_count').empty();
                    $('#pack_inspected_count').append(data['pack'])

                }
            })
        }
    })
</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('prp::layouts.app',['select2'=>true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/dan/Documents/projects/nuve/imara-lims-v2/Modules/Prp/Resources/views/index.blade.php ENDPATH**/ ?>