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
    setHarvestWeekSessionVariable();
    $items = array(
        array(
            'link' => '/prp',
            'name' => 'PRP',
            'icon' => null,
        ),
        array(
            'link' => '/prp/show/harvest-Weeks/List',
            'name' => 'Harvest Weeks',
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
    <h5 class="p-3" style="font-weight: 550;">

        <i class="mdi mdi-format-list-bulleted mdi-24px mr-2"></i> Harvest Weeks
        <span class="btn text-primary btn-sm btn-default float-right" data-toggle="modal" data-target="#create_week"><i class="mdi mdi-calendar-plus"></i> Create Week</span>
    </h5>

   <div class="table-responsive p-3">
       <table class="table table-condensed table-bordered table-sm table-stripped table-hover">
           <thead>
               <tr>
                   <th>#</th>
                   <th>Name</th>
                   <th>Year</th>
                   <th>Start Date</th>
                   <th>End Date</th>
                   <th>Status</th>
                   <th>Closed By</th>
               </tr>
           </thead>
           <tbody>
               <?php $__currentLoopData = $weeks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $week): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
               <tr>
                   <td>
                       <span class="btn btn-sm btn-default text-primary" data-target="#edit-week" data-toggle="modal" data-record="<?php echo e(json_encode($week)); ?>"><i class="mdi mdi-pencil" data-toggle="tooltip" title="Edit"></i></span>
                       <span class="btn btn-sm btn-default text-danger" data-target="#delete-week" data-toggle="modal" data-record="<?php echo e(json_encode($week)); ?>"><i class="mdi mdi-delete-empty" data-toggle="tooltip" title="delete"></i></span>
                   </td>
                   <td>Week <?php echo e($week->week_no); ?></td>
                   <td><?php echo e($week->year); ?></td>
                   <td><?php echo e($week->start_date ?? $week->created_at); ?></td>
                   <td><?php echo e($week->end_date ?? '-'); ?></td>
                   <td><?php echo e($week->weekstatus); ?></td>
                   <td><?php echo e(getUserById($week->closed_by)->name ?? '-'); ?></td>
               </tr>
               <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
           </tbody>
       </table>
   </div>



</main>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script2'); ?>
<div class="modal fade" id="edit-week" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo e(route('EditHarvestWeek')); ?>" method="post">
                <?php echo csrf_field(); ?>
                <div class="modal-body">
                    
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-sm btn-outline-primary"><i class="mdi mdi-content-save"></i> Save</button>
                    <span class="btn btn-sm btn-default text-danger" data-dismiss="modal">Close</span>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="delete-week" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo e(route('delete-harvest-week')); ?>" method="post">
                <?php echo csrf_field(); ?> 
                <div class="modal-body">
                  
                </div>
            </form>
        </div>
    </div>
</div>
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

        let editHarvestWeekBody = (data)=>{
            var body = $(`
                <div class="alert alert-primary p-2 d-flex">
                    <i class="mdi mdi-pencil mdi-36"></i>
                    <span class="p-2">
                        Edit Harvest Week ${data.week_no} Data Below.
                    </span>
                </div>
                <div class="form-group">
                    <label for="" class="control-label">Week No</label>
                    <input type="text" name="week_no" value="${data.week_no}" class="form-control">
                </div>
                <div class="form-group">
                    <label for="" class="control-label">Year</label>
                    <input type="text" name="year" value="${data.year}" class="form-control">
                </div>
                <div class="form-group">
                    <label for="" class="control-label">Status</label>
                    <select name="status" id="" class="form-control">
                        <option value="0" ${data.status == 0 ? `selected` : ``}>Active</option>
                        <option value="1" ${data.status == 1 ? `selected` : ``}>Partially Closed</option>
                        <option value="2" ${data.status == 2 ? `selected` : ``}>Closed</option> 
                    </select>
                </div>
                <input type="hidden" name="week_id" value="${data.id}">
            `).clone();
            return body;
        }
        $('#edit-week').on('show.bs.modal',(e)=>{
            var record = $(e.relatedTarget).data('record');
            var body = editHarvestWeekBody(record)
            $('#edit-week').find('.modal-body').empty();
            $('#edit-week').find('.modal-body').append(body);
        });
        let deleteHarvestWeekBody = (data)=>{
            var body = $(`
                <div class="alert alert-danger p-2 d-flex">
                    <i class="mdi mdi-delete-empty mdi-36"></i>
                    <span class="p-2">Confirm you want to delete <b>Soft Delete</b> Harvest Week ${data.week_no} </span>
                </div>
                <input type="hidden" name="harvest_week_id" value="${data.id}">
            `).clone();
            return body;
        }
        $('#delete-week').on('show.bs.modal',(e)=>{
            var record = $(e.relatedTarget).data('record');
            var body = deleteHarvestWeekBody(record);
            $('#delete-week').find('.modal-body').empty();
            $('#delete-week').find('.modal-body').append(body);


        })

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
                                <select name="action" id="status-select" class="form-control">
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
       
    })
</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('prp::layouts.app',['dataTable'=>true,'select2'=>true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/dan/Documents/projects/nuve/imara-lims-v2/Modules/Prp/Resources/views/harvest_week_list.blade.php ENDPATH**/ ?>