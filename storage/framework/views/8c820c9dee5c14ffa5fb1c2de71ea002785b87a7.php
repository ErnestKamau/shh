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

    .btn-default:hover {
        box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;
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
            'link' => '/prp/Timesheets/Header/index',
            'name' => 'Timesheet Batch',
            'icon' => null,
        ),
        array(
            'link' => '/prp/day-batch/show/' . $day_batch->id . '/type/timesheets',
            'name' => 'Timesheets',
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
    <h5 class="p-2 mt-2"><i class="mdi mdi-account-clock"></i> Timesheets</h5>

    <div class="card tab-card mt-2" style="clear:both">
        <div class="card-header tab-card-header">
            <ul class="nav nav-tabs card-header-tabs" id="personnel-tab" role="tablist">
                <li class="nav-item">
                    <a href="#timesheet-tab" class="nav-link active" id="timesheet-trigger" data-toggle="tab" role="tab" aria-controls="timesheet-tab" aria-selected="true"> <i class="mdi mdi-account-clock" style="color: black; font-size:15px"></i> Timesheets</a>
                </li>
                
            </ul>
        </div>
        <div class="tab-content" id="batches-tabs-content">
            <div class="tab-pane fade show active p-3" id="timesheet-tab" role="tabpanel" aria-labelledby="one-tab">
                <h5 class="card-title"> <i class="mdi mdi-account-clock"></i> Week <?php echo e($harvest_week->week_no); ?> | <?php echo e($day_batch->day_code); ?> | Timesheets</h5>
                <div class="table-responsive mt-4 p-3">
                    <table class="table table-condensed table-sm table-hover table-striped table-bordered ">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th>Group</th>
                                <th>Block</th>
                                <th>Qc Personnel</th>
                                <th>Variety</th>
                                <th>Start Time</th>
                                <th>Stop Time</th>
                                <th>Harvest Hrs</th>
                                <th>Harvester Codes</th>
                                <th>Level</th>

                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $headers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $header): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td>
                                    <span class="btn btn-sm btn-default text-primary" data-hcodes="<?php echo e(json_encode(explode(',',$header->harvester_ids))); ?>" data-toggle="modal" data-action="edit" data-record="<?php echo e(json_encode($header)); ?>" data-target="#add-batch-day"><i class="mdi mdi-pencil"></i></span>
                                    <span class="btn btn-sm btn-default text-danger"><i class="mdi mdi-delete-empty"></i></span>
                                    <a href="<?php echo e(route('timesheet-show',['id'=>$header->id])); ?>" class="btn btn-sm btn-default text-success"><i class="mdi mdi-eye"></i></a>
                                </td>
                                <td><?php echo e($header->harvest_date); ?></td>
                                <td><?php echo e($header->groupname); ?></td>
                                <td><?php echo e($header->blockname); ?></td>
                                <td><?php echo e($header->mobileQcPersonnel()); ?></td>
                                <td>
                                    <?php $__currentLoopData = $header->getVarieties(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $variety): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <span data-toggle="tooltip" title="<?php echo e($variety->name); ?>"><?php echo e($variety->zvam); ?></span>, 
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </td>
                                
                                
                                <td><?php echo e($header->start_time); ?></td>
                                <td><?php echo e($header->end_time); ?></td>
                                <td><?php echo e($header->harvest_hrs); ?></td>
                                <td><?php echo e($header->getHarvesterNames()); ?></td>
                                <td><?php echo e($header->getVarietiesSpeeedFactor()); ?></td>

                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
           
        </div>
    </div>


</main>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script2'); ?>
<div class="modal fade" data-groups="<?php echo e(json_encode($groups)); ?>" data-blocks="<?php echo e(json_encode($blocks)); ?>" data-activities="<?php echo e(json_encode($activities)); ?>" data-varieties="<?php echo e(json_encode($varieties)); ?>" id="add-batch-day" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo e(route('timesheet-batch-store')); ?>" method="post">
                <?php echo csrf_field(); ?>
                <div class="modal-body">
                    <div class="form-group">

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-sm btn-outline-primary"><i class="mdi mdi-content-save"></i> Save</button>
                    <span class="btn btn-sm btn-default" data-dismiss="modal">Close</span>
                </div>

            </form>
        </div>
    </div>
</div>



<script>
    $(() => {
        let addDayBody = (data = false) => {
            if (data) {
                var body = $(`
               
                <div class="alert alert-primary p-2 d-flex">
                    <i class="mdi mdi-pencil mdi-24px"></i>
                    <span class="p-2">Edit ${data.harvest_date} timesheet record</span>
                </div>
                    <div class="form-group">
                        <label for="" class="control-label">Date</label>
                        <input type="date" name="harvest_date" value="${data.harvest_date}" class="form-control">
                        <input type="hidden" name="timesheet_id" value="${data.id}">
                        <input type="hidden" name="batch_id" value="<?php echo e($day_batch->id); ?>">
                    </div>
                    <div class="form-group">
                        <label class="control-label">Variety</label>
                        <select name="variety_id" id="varieties" class="form-control">
                            
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="" class="control-label">Start Time</label>
                        <input type="time" name="start_time" value="${data.start_time}" id="" class="form-control diff-time">

                    </div>

                    <div class="form-group">
                        <label for="" class="control-label">Stop Time</label>
                        <input type="time" name="end_time" value="${data.end_time}" id="" class="form-control diff-time">

                    </div>
                    <div class="form-group">
                        <label for="" class="control-label">Harvest Hours</label>
                        <input type="text" name="harvest_hrs" value="${data.harvest_hrs}" id="harvest_hrs" class="form-control" readonly>
                        <input type="hidden" name="actual_hrs" value="${data.actual_hrs}" id="actual_hrs">
                        <input type="hidden" name="actual_min" value="${data.actual_min}" id="actual_min">

                    </div>
                    <div class="form-group">
                        <label class="control-label">Harvester Group</label>
                        <select name="group_id" id="group_id" class="form-control">
                            
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="" class="control-label">Harvesters</label>
                        <select name="harvester_ids[]" multiple id="harvester_id" class="form-control">
                           
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="" class="control-label">Block</label>
                        <select name="block_id" id="block_id" class="form-control">
                           
                        </select>
                        
                    </div>
                    <div class="form-group">
                        <label for="" class="control-label">Activity</label>
                        <select name="activity_id"  id="activity_id" class="form-control">
                           
                        </select>
                    </div>

                `).clone();
            } else {
                var body = $(`
                <div class="alert alert-primary p-2 d-flex">
                    <i class="mdi mdi-plus mdi-24px"></i>
                    <span class="p-2">Add A new timesheet record</span>
                </div>
                    <div class="form-group">
                        <label for="" class="control-label">Date</label>
                        <input type="date" name="harvest_date" class="form-control">
                        <input type="hidden" name="timesheet_id" value="0">
                        <input type="hidden" name="batch_id" value="<?php echo e($day_batch->id); ?>">
                    </div>
                   
                    <div class="form-group">
                        <label class="control-label">Variety</label>
                        <select name="variety_id" id="varieties" class="form-control">
                            
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="" class="control-label">Start Time</label>
                        <input type="time" name="start_time" id="" class="form-control diff-time">

                    </div>

                    <div class="form-group">
                        <label for="" class="control-label">Stop Time</label>
                        <input type="time" name="end_time" id="" class="form-control diff-time">

                    </div>
                    <div class="form-group">
                        <label for="" class="control-label">Harvest Hours</label>
                        <input type="text" name="harvest_hrs" id="harvest_hrs" class="form-control" readonly>
                        <input type="hidden" name="actual_hrs" value="" id="actual_hrs">
                        <input type="hidden" name="actual_min" value="" id="actual_min">

                    </div>
                    <div class="form-group">
                        <label class="control-label">Harvester Group</label>
                        <select name="group_id" id="group_id" class="form-control">
                            
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="" class="control-label">Harvesters</label>
                        <select name="harvester_ids[]" id="harvester_id" multiple id="harvesters" class="form-control">
                           <option value="">Choose Group First...</opton>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="" class="control-label">Block</label>
                        <select name="block_id" id="block_id" class="form-control">
                           
                        </select>
                        
                    </div>
                    <div class="form-group">
                        <label for="" class="control-label">Activity</label>
                        <select name="activity_id"  id="activity_id" class="form-control">
                           
                        </select>
                    </div>

                `).clone();
            }
            return body;
        }


        $('#add-batch-day').on('show.bs.modal', (e) => {
            var action = $(e.relatedTarget).data('action');
            var data = $(e.relatedTarget).data('record');
            var groups = $('#add-batch-day').data('groups');
            $blocks = $('#add-batch-day').data('blocks');
            var varieties = $('#add-batch-day').data('varieties');
            var h_codes = $(e.relatedTarget).data('hcodes') || [];
            h_codes = h_codes.map(i => parseInt(i));

            var activities = $('#add-batch-day').data('activities');

            var addbody = action == 'edit' ? addDayBody(data) : addDayBody();
            $(addbody).find('#activity_id').empty();
            $.each(activities, (i, e) => {
                if (action == 'edit') {
                    $(addbody).find('#activity_id').append(`
                <option value="${e.id}" ${e.id == data.activity_id ? `selected`  :``}>${e.name}</option>
                `);
                } else {
                    $(addbody).find('#activity_id').append(`<option value="${e.id}">${e.name}</option>`);
                }

            })

            $(addbody).find('#harvesters').empty();
            $(addbody).find('#group_id').empty();
            $(addbody).find('#group_id').append('<option value="">Choose Group...</option>')
            $(addbody).find('#harvesters').append(` <option value="">Choose Group First...</option>`)
            $.each(groups, (i, e) => {

                if (action == 'edit') {
                    $(addbody).find('#group_id').append(`
                <option value="${e.id}" ${e.id == data.group_id ? `selected`  :``}>${e.name}</option>
                `);
                } else {
                    $(addbody).find('#group_id').append(
                        `<option value="${e.id}">${e.name}</option>`
                    );
                }

            });
            $(addbody).find('#block_id').empty('<option value="">Choose Block...</option>');
            $(addbody).find('#block_id').append('')
            $.each($blocks, (i, e) => {

                if (action == 'edit') {
                    $(addbody).find('#block_id').append(`
                <option value="${e.id}" ${e.id == parseInt(data.block) ? `selected`  :``}>${e.name}</option>
                `);
                } else {
                    $(addbody).find('#block_id').append(`
                    <option value="${e.id}">${e.name}</option>

                `);
                }

            });
            $(addbody).find('#varieties').empty();
            $(addbody).find('#varieties').append(` <option value="">Choose Variety</option>`)
            $.each(varieties, (i, e) => {
                if (action == 'edit') {
                    $(addbody).find('#varieties').append(`
                <option value="${e.zvam}" ${e.id == data.variety_id  ? `selected`  :``}>${e.name} (${e.zvam})</option>
                `);
                } else {
                    $(addbody).find('#varieties').append(`<option value="${e.zvam}">${e.name} (${e.zvam})</option>`);
                }
            });

            $(addbody).find('#varieties').select2();
            $(addbody).find('#harvester_id').select2();
            $(addbody).find('#group_id').select2();
            $(addbody).find('#block_id').select2();
            $(addbody).find('#activity_id').select2();

            $(addbody).find('.diff-time').on('change', () => {
                var start_time = $(addbody).find('input[name="start_time"]').val();
                var end_time = $(addbody).find('input[name="end_time"]').val();
                var startArr = start_time.split(':');
                var endArr = end_time.split(':');
                if (end_time > start_time) {
                    var start_date = new Date(2021, 1, 1, startArr[0], startArr[1], 00);
                    var end_date = new Date(2021, 1, 1, endArr[0], endArr[1], 00);
                    var minutes = getDateDiff(start_date, end_date);
                    if (minutes != '') {
                        var hrs = Math.ceil(minutes / 60);
                        var min = minutes % 60;
                        $(addbody).find('#harvest_hrs').val(`${hrs}hrs ${min}min`)
                        $(addbody).find('#actual_hrs').val(hrs);
                        $(addbody).find('#actual_min').val(min);
                    }

                }
            })

            var getDateDiff = (dt1, dt2) => {
                var diff = (dt2.getTime() - dt1.getTime()) / 1000;
                diff /= 60;
                return Math.abs(Math.round(diff));
            }

            console.log('data');
            $('#add-batch-day').find('.modal-body').empty();
            $('#add-batch-day').find('.modal-body').append(addbody);
            $('#add-batch-day').find('#group_id').on('change', (e) => {
                $.ajax({
                    url: `/prp/get/harvesters/by-groupID/${$(e.currentTarget).val()}`,
                    method: 'GET',
                    success: (data) => {
                        $('#add-batch-day').find('#harvester_id').empty()
                        $('#add-batch-day').find('#harvester_id').append(`<option value="">Choose Harvester...</option>`)
                        $.each(data, (i, elem) => {
                            $('#add-batch-day').find('#harvester_id').append(`<option value="${elem.id}">${elem.name} - (${elem.harvester_no})</option>`)
                        });
                        $(addbody).find('#harvester_id').select2();

                    },
                    error: (data) => {
                        console.log(data);
                    }
                })
            })
        });


    });
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('prp::layouts.app',['dataTable'=>true,'select2'=>true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/dan/Documents/projects/nuve/imara-lims-v2/Modules/Prp/Resources/views/Data-Proccess/Transactions/timesheets/index.blade.php ENDPATH**/ ?>