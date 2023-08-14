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
        box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;
    }

    .table-responsive {
        box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;
    }

    .table-responsive-1 {
        box-shadow: rgba(100, 100, 111, 0.2) 0px 5px 20px 0px;
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
        ),
        array(
            'link' => '',
            'name' => 'Timesheet - ' . $timesheet->harvest_date,
            'icon' => null,
        ),
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
        <i class="mdi mdi-file-account-outline"></i> Week <?php echo e($harvest_week->week_no); ?> | <?php echo e($day_batch->day_code); ?> | Timesheet <?php echo e($timesheet->harvest_day); ?> - <?php echo e($timesheet->harvest_date); ?>

    </h5>
    <div class="table-responsive mt-4 p-3">
        <table class="table table-condensed table-sm table-hover table-striped table-bordered ">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Harvester Name</th>
                    <th>Harvester No</th>
                    <th>Variety</th>
                    <th>Start Time</th>
                    <th>Stop Time</th>
                    <th>Harvest Hrs</th>
                    <th>Activity</th>
                    <th>Level</th>
                    <th>Inspected</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $timesheet_details; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td>
                        <span class="btn btn-sm btn-default text-primary" data-harvester="<?php echo e(json_encode($detail->getHarvesterDetails()->name)); ?>" data-detail="<?php echo e(json_encode($detail->id)); ?>"  data-toggle="modal" data-target="#add-quality-remark"><i class="mdi mdi-checkbox-multiple-marked-outline" data-toggle="tooltip" title="Add Quality Remarks"></i></span>
                        <span class="btn btn-sm btn-default text-danger" data-detail="<?php echo e(json_encode($detail->id)); ?>" data-harvester="<?php echo e($detail->getHarvesterDetails()->name); ?>" data-target="#delete-timesheet-detail" data-toggle="modal"><i class="mdi mdi-delete-empty"></i></span>

                    </td>
                    <td><?php echo e($detail->getHarvesterDetails()->name); ?></td>
                    <td><?php echo e($detail->getHarvesterDetails()->harvester_no); ?></td>
                    <td>

                        <?php $__currentLoopData = $detail->getVarieties(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $variety): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <span data-toggle="tooltip" title="<?php echo e($variety->name); ?>"><?php echo e($variety->zvam); ?></span>, 
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </td>
                    <td><?php echo e($detail->start_time); ?></td>
                    <td><?php echo e($detail->end_time); ?></td>
                    <td><?php echo e($detail->harvest_hrs); ?></td>
                    <td><?php echo e(getActivityByID($detail->activity_id)->name); ?></td>
                    <td><?php echo e($detail->getVarietiesSpeeedFactor()); ?></td>
                    <td><?php echo $detail->hasremark ? '<i class="mdi mdi-account-check text-success"></i>' : '-'; ?></td>
                    

                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
</main>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script2'); ?>
<div class="modal fade" id="add-quality-remark" data-destination="<?php echo e(json_encode($destinations)); ?>" data-variety="<?php echo e(json_encode($timesheet->getVarieties())); ?>" data-quality="<?php echo e(json_encode($quality_remarks)); ?>" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="<?php echo e(route('quality-remark-store')); ?>" method="post">
                <?php echo csrf_field(); ?>
                <div class="card-header text-center text-bold">
                    <i class="mdi mdi-check-underline-circle-outline mdi-24px"></i> Quality Remarks Records <span id="harvester_name"></span>
                    <input type="hidden" name="detail_id" value="" id="detail_id">
                </div>
                <div class="modal-body">
                    
                    <button type="submit" class="btn btn-default btn-sm text-success"><i class="mdi mdi-content-save"></i> Save</button>

                    <span id="add-remark-trigger" class="btn btn-sm btn-default text-primary float-right mb-3"><i class="mdi mdi-plus"></i> Add Quality Remark</span>
                    <div class="table-responsive-1 mt-3">
                        <table class="table table-condensed table-hover table-bordered table-sm table-stripped" style="width: 100%;">
                            <thead class="bg-light">
                                <tr>
                                    <th>#</th>
                                    <th>Quality Remark</th>
                                    <th>Variety</th>
                                    <th>Value</th>
                                    <th>Destination</th>
                                    <th>Actual Count</th>
                                </tr>
                            </thead>
                            <tbody id="qualityTbody">

                            </tbody>

                        </table>
                    </div>


                </div>
                <div class="modal-footer">

                    <span class="btn btn-sm btn-default float-right text-danger" data-dismiss="modal">Close</span>
                </div>

            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="delete-timesheet-detail" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo e(route('timesheetDetailsDelete')); ?>" method="post">
                <?php echo csrf_field(); ?>   
                <div class="modal-body">
                   
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-outline-danger btn-sm"><i class="mdi mdi-delete-empty"></i> Yes, Delete</button>
                    <span class="btn btn-sm btn-default" data-dismiss="modal">Cancel</span>
                </div>
            </form>
        </div>
    </div>
</div>




<script>
    $(() => {
        const quality_remarks = $('#add-quality-remark').data('quality');
        const varieties = $('#add-quality-remark').data('variety')
        const destinations = $('#add-quality-remark').data('destination')
        let qualityRemarkRow = (data = false) => {
            if (data) {
                var body = $(`
                <tr>
                    <td>
                    <span class="btn btn-sm btn-default text-primary edit-mode" data-action="edit"><i class="mdi mdi-pencil"></i></span>
                    <span class="btn btn-sm btn-default text-danger delete-actual" data-remark_name="${data.quality_name}" data-remark="${data.id}"><i class="mdi mdi-delete-empty"></i></span>
                    </td>
                    <td>
                        <div class="form-group">
                            <select name="qc_id" disabled class="form-control qc_id">
                                <option value="">Select Quality Remark</option>
                            </select>
                            <input type="hidden" name="remark_ids[]" value="${data.quality_remark_id}" class="form-control remarks_id">
                            <input type="hidden" name="quality_remark_data_ids[]" class="form-control" value="${data.id}">
                        </div>
                    </td>
                    <td>
                        <div class="form-group">
                            <select name="variety_id[]" disabled id="" class="form-control variety_id">
                                <option value="">Select Variety...</option>
                            </select>
                        </div>

                    </td>
                    <td style="width:5% !important">
                        <div class="form-group">
                            <input type="text" readonly name="quality_value[]" value="${data.quality_value}" placeholder="Value..." class="form-control quality_value">
                        </div>
                    </td>
                    <td>
                        <div class="form-group">
                            <select name="destination_id[]" disabled id="" class="form-control destination_id">
                                <option value="">Select Destination...</option>
                            </select>
                            
                        </div>
                    </td>
                    <td style="width:5% !important">
                        <div class="form-group">
                            <input type="text" readonly name="actual_count[]" value="${data.actual_count || 0}" placeholder="Value..." class="form-control quality_value">
                        </div>
                    </td>
                </tr>
                `).clone();
            } else {
                var body = $(`
                <tr>
                    <td>
                    <span class="btn btn-sm btn-default text-danger delete-row"><i class="mdi mdi-delete-empty"></i></span>
                    </td>
                    <td>
                        <div class="form-group">
                            <select name="qc_id" class="form-control qc_id">
                                <option value="">Select Quality Remark</option>
                            </select>
                            <input type="hidden" name="remark_ids[]" class="form-control remarks_id">
                            <input type="hidden" name="quality_remark_data_ids[]" class="form-control" value="0">
                        </div>
                    </td>
                    <td>
                        <div class="form-group">
                            <select name="variety_id[]" id="" class="form-control variety_id">
                                <option value="">Select Variety...</option>
                            </select>
                        </div>

                    </td>
                    <td style="width:5% !important">
                        <div class="form-group">
                            <input type="text" name="quality_value[]" placeholder="Value..." class="form-control">
                        </div>
                    </td>
                    <td>
                        <div class="form-group">
                            <select name="destination_id[]" disabled id="" class="form-control destination_id">
                                <option value="">Select Destination...</option>
                            </select>
                        </div>
                    </td>
                    <td>
                        <div class="form-group">
                            <input type="text"  name="actual_count[]" value="" placeholder="Value..." class="form-control quality_value">
                        </div>
                    </td>
                </tr>
                `).clone();
            }
            $(body).find('.delete-row').on('click', (e) => {
                var parentTD = $(e.currentTarget).parent('td');
                var parentTR = $(parentTD).parent('tr');
                $(parentTR).remove();
            });
            $(body).find('.qc_id').on('change', (e) => {
                let optionValue = $(e.currentTarget).val();
                $(body).find('.remarks_id').val(optionValue);  
            });

            $.each(destinations,(i,obj)=>{
                var option = `<option value="${obj.id}" ${obj.id == data.destination ? `selected` : ``} >${obj.name}</option>`;
                $(body).find('.destination_id').append(option);
            });
            $(body).find('.destination_id').select2();

            return body;
        }

        $('#add-quality-remark').on('show.bs.modal', (e) => {
            var detail = $(e.relatedTarget).data('detail');
            var harvester_name = $(e.relatedTarget).data('harvester');
            $('#add-quality-remark').find('#harvester_name').empty();
            $('#add-quality-remark').find('#harvester_name').append(harvester_name);
            $('#add-quality-remark').find('#detail_id').val(detail);
            $('#add-quality-remark').find('#qualityTbody').empty();
            $('#add-quality-remark').find('#add-remark-trigger').on('click', () => {
                let QRowbody = qualityRemarkRow();
                var selected_quality = [];
                $.each($('#add-quality-remark').find('.remarks_id'),(i,obj)=>{
                    selected_quality.push(parseInt($(obj).val()));
                    
                });
                console.log(selected_quality);
                $.each(quality_remarks, (i, obj) => {
                    if(selected_quality.indexOf(obj.id) < 0){
                        var option = `<option value="${obj.id}">${obj.name}</option>`
                        $(QRowbody).find('.qc_id').append(option);

                    }
                })
                $.each(varieties,(i,obj)=>{
                    var opt  = `<option value="${obj.id}" >${obj.name} - ${obj.zvam}</option>`
                    $(QRowbody).find('.variety_id').append(opt)
                })
                $(QRowbody).find('.qc_id').select2();
                $(QRowbody).find('.variety_id').select2();
                
                $('#add-quality-remark').find('#qualityTbody').append(QRowbody);

            });
          
            fillDataRemarkRow(detail, (data) => {
                $.each(data, (index, elem) => {
                    var QRowbodydata = qualityRemarkRow(elem);
                    $.each(quality_remarks, (i_, obj_) => {
                        var optionV = `<option value="${obj_.id}" ${obj_.id == elem.quality_remark_id ? `selected`: ``} >${obj_.name}</option>`
                        $(QRowbodydata).find('.qc_id').append(optionV);
                    });
                    $.each(varieties,(i,obj)=>{
                        var opt  = `<option value="${obj.id}" ${obj.id == elem.variety_id ? `selected`: ``} >${obj.name} - ${obj.zvam}</option>`
                        $(QRowbodydata).find('.variety_id').append(opt)
                    })
                    $(QRowbodydata).find('.edit-mode').on('click', (eTarget) => {
                        var action = $(eTarget.currentTarget).data('action');
                        if (action == 'edit') {
                            $(QRowbodydata).find('.edit-mode').data('action', 'revert')
                            $(QRowbodydata).find('.qc_id').prop('disabled', false);
                            $(QRowbodydata).find('.quality_value').prop('readonly', false)
                            $(QRowbodydata).find('.variety_id').prop('disabled',false)
                            $(QRowbodydata).find('.destination_id').prop('disabled',false)


                        } else {
                            $(QRowbodydata).find('.edit-mode').data('action', 'edit')
                            $(QRowbodydata).find('.qc_id').prop('disabled', true);
                            $(QRowbodydata).find('.quality_value').prop('readonly', true)
                            $(QRowbodydata).find('.variety_id').prop('disabled',true)
                            $(QRowbodydata).find('.destination_id').prop('disabled',true)
                        }

                    });
                    $(QRowbodydata).find('.delete-actual').on('click', (event_target) => {
                        var remark_data_id = $(event_target.currentTarget).data('remark');
                        var remark_name = $(event_target.currentTarget).data('remark_name');
                        if (confirm(`Confirm you  want to delete ${remark_name} quality remark`)) {
                            $.ajax({
                                url: '/prp/timesheet-detail/quality-remark/delete',
                                data: {
                                    remark_id: remark_data_id
                                },
                                success: (data) => {
                                    var parentDTd = $(event_target.currentTarget).parent('td');
                                    var parentDTr = $(parentDTd).parent('tr')
                                    $(parentDTr).remove();
                                }
                            })
                        }
                    })

                    $(QRowbodydata).find('.qc_id').select2();
                    $(QRowbodydata).find('.variety_id').select2();
                    $('#add-quality-remark').find('#qualityTbody').append(QRowbodydata);

                })
            });
           

        });
        let fillDataRemarkRow = (detail_id, callback) => {
            $.ajax({
                url: `/prp/timesheet-detail/${detail_id}/get-quality-remarks`,
                method: 'GET',
                success: (data) => {
                    callback(data)
                },
                error: (data) => {
                    console.log(data);
                }
            })
        }
        let deleteDetailBody = (detail,harvester_name)=>{
            let body = $(`
                <div class="alert alert-danger p-2 d-flex">
                    <i class="mdi mdi-alert-decagram mdi-36px"></i>
                    <span class="p-2">Confirm you want to delete timesheet record for <b>${harvester_name}</b> harvester. This will also delete any quality remark that was captured under this timesheet record.</span>
                    <input type="hidden" name="detail_id" value="${detail}">
                </div>
            `).clone()
            return body;
        }
        $('#delete-timesheet-detail').on('show.bs.modal',(e)=>{
            var detail_id = $(e.relatedTarget).data('detail');
            var harvester_name = $(e.relatedTarget).data('harvester');
            
            var Dbody = deleteDetailBody(detail_id,harvester_name);
            $('#delete-timesheet-detail').find('.modal-body').empty();
            $('#delete-timesheet-detail').find('.modal-body').append(Dbody)
        })

    });
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('prp::layouts.app',['dataTable'=>true,'select2'=>true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/dan/Documents/projects/nuve/imara-lims-v2/Modules/Prp/Resources/views/Data-Proccess/Transactions/timesheets/show.blade.php ENDPATH**/ ?>