<?php $__env->startSection('title2'); ?>
<style>
    .table-responsive {
        box-shadow: rgba(0, 0, 0, 0.15) 1.95px 1.95px 2.6px;
        text-decoration: none !important;
        color: black !important;
    }

    .table-responsive:hover {
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

    .badge-active {
        text-align: left !important;
        background-color: white;
        width: 90%;
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
        ),
        array(
            'link' => '/prp/quality-remark/index',
            'name' => 'Quality Remarks',
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
    <h5 class="p-2">
        <i class="mdi mdi-check-box-multiple-outline"></i> Quality Remarks

        <span class="btn btn-outline-success float-right btn-sm mr-2" data-target="#affect-week" data-toggle="modal"><i class="mdi mdi-cloud-sync"></i> Sync To Active Week</span>

    </h5>

    <div class="border-bottom mt-2 p-3 bg-light ">
        <b class="pl-2 pt-2"><u>Activities Allocations</u></b>
        <div class="card-body">
            <div class="row">
                <?php $__currentLoopData = $activities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="col-md-3">
                    <i class="mdi mdi-chevron-right"></i>
                    <span class="badge badge-active p-2"><i class="mdi mdi-account-clock-outline"></i> <?php echo e($activity->name); ?> <span class="float-right mt-1 p-1"><?php echo e($activity->use_prp_system == 0 ? $activity->prp_bonus : $system_param->prp_bonus); ?> /=</span></span>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>
    <form action="<?php echo e(route('quality-store')); ?>" class="p-3" method="post">
        <?php echo csrf_field(); ?>
        <div class="header mt-2">
            <button class="btn btn-sm btn-default text-success" type="submit"><i class="mdi mdi-content-save"></i> Save</button>
            <span class="btn btn-sm float-right btn-default text-primary" data-activities="<?php echo e(json_encode($activities)); ?>" id="quality-remark-add"><i class="mdi mdi-plus"></i> Add Quality Remark</span>
        </div>
        <div class="table-responsive mt-2" style="clear:both">
            <table class="table table-condensed table-bordered table-stripped table-hover" style="width: 100%;">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Activity</th>
                        <th>Amount %</th>
                        <th>Actual</th>
                        <th>Pay 100%</th>
                        <th>Pay 75%</th>
                        <th>Pay 50%</th>
                        <th>Pay 25%</th>
                        <th>Pay Zero</th>
                    </tr>
                </thead>
                <tbody id="remark-body">

                </tbody>
            </table>
        </div>

    </form>
</main>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script2'); ?>
<div class="modal fade" id="affect-week" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo e(route('affectWeekDataTrigger')); ?>" action="post">
                <?php echo csrf_field(); ?>
                <div class="modal-body">
                    <div class="alert alert-success d-flex">
                        <i class="mdi mdi-alert-decagram mdi-36px"></i>
                        <div class="p-2">
                            Confirm you want to implement the current configurations to the active harvest weeks: <br><br>
                            <div class="bg-white p-2">
                                <?php $__currentLoopData = $harvest_weeks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $week_): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="form-group">
                                    <?php
                                        $status_name = '';
                                        $status_name = $week_->status == 0 ? 'Active' : $status_name; 
                                        $status_name = $week_->status == 1 ? 'Partially Closed' : $status_name;
                                    ?>
                                    <label for="" class="control-label"><input checked type="checkbox" name="week_ids[]" class="mr-2" value="<?php echo e($week_->id); ?>" id=""> Harvest Week <?php echo e($week_->week_no); ?> - Status <?php echo e($status_name); ?></label>
                                </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                            </div>
                            
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-sm btn-outline-success"><i class="mdi mdi-thumb-up"></i> Yes, Affect</button>
                    <span class="btn btn-sm btn-default" data-dismiss="modal">Cancel</span>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="delete-group" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo e(route('group-delete')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <div class="modal-body">


                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-sm btn-outline-primary"><i class="mdi mdi-content-save"></i> Save</button>
                    <span class="btn btn-default text-danger" data-dismiss="modal">Close</span>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="delete-record-modal" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo e(route('delete-quality-remark')); ?>" method="post">
                <?php echo csrf_field(); ?> 
                <div class="modal-body">
                    
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="mdi mdi-delete-empty"></i> Yes,Delete</button>
                    <span class="btn btn-sm btn-default" data-dismiss="modal">Close</span>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(() => {
        let DeleteRecordData = (data_id,data_name)=>{
            let bodyVar = $(`
                <div class="alert alert-danger p-2 d-flex">
                    <i class="mdi mdi-alert-decagram-outline mdi-24px"></i>
                    <span class="mt-2 p-2">Confirm you want to delete ${data_name} quality remark</span>
                    <input type="hidden" name="quality_id" value="${data_id}">
                </div>
            `).clone();
            return bodyVar;
        }
        $('#delete-record-modal').on('show.bs.modal',(e)=>{
            let recordname  = $(e.relatedTarget).data('recordname');
            let record_id  = $(e.relatedTarget).data('recordid');
            let body = DeleteRecordData(record_id,recordname);
            $('#delete-record-modal').find('.modal-body').empty();
            $('#delete-record-modal').find('.modal-body').append(body);
        })
        let rowdata = (data = false) => {
            if (data) {
                var body = $(`
                <tr class="rowid">
                    <td style="width:13%">
                        <span class="btn btn-default btn-sm text-primary edit-record" data-action="edit"><i class="mdi mdi-pencil" data-toggle="tooltip" title="Edit"></i></span>
                        <span class="btn btn-sm btn-default text-success duplicate-record" data-recordname="${data.name}" data-recordid="${data.id}"><i class="mdi mdi-content-duplicate" data-toggle="tooltip" title="duplicate"></i></span>
                        <span class="btn btn-sm btn-default text-danger delete-actual-record" data-toggle="modal" data-target="#delete-record-modal"  data-recordname="${data.name}" data-recordid="${data.id}"><i class="mdi mdi-delete-empty" data-toggle="tooltip" title="Delete"></i></span>
                        <input type="hidden" name="quality_ids[]" value="${data.id}">
                    </td>
                    <td style="width:17%"><input type="text" name="name[]" readonly value="${data.name}" class="form-control edit-data" id="name-field" placeholder="Name..."></td>
                    <td style="width:15%">
                        <select name="activity_ids[]" disabled id="" class="form-control activity-field actual-check">
                            
                        </select>
                        <input type="hidden" name="activity_id[]" class="activity_id" value="${data.activity_id}">
                    </td>
                    <td><input type="text" name="amount_allocated_perc[]" readonly value="${data.amount_allocated_perc}" class="form-control edit-data actual-check perc-field" placeholder="Amt %..."></td>
                    <td style="width:10%"><input type="text" name="actual" readonly class="form-control actual-field" value="${data.actual_amount}"></td>
                    <td><input type="text" name="pay_100[]" readonly class="form-control edit-data" id="100-field" value="${data.pay_100}" placeholder="Pay 100..."></td>
                    <td><input type="text" name="pay_75[]" readonly class="form-control edit-data" id="75-field" value="${data.pay_75}" placeholder="Pay 75..."></td>
                    <td><input type="text" name="pay_50[]" readonly class="form-control edit-data" id="50-field" value="${data.pay_50}" placeholder="Pay 50..."></td>
                    <td><input type="text" name="pay_25[]" readonly class="form-control edit-data" id="25-field" value="${data.pay_25}" placeholder="Pay 25..."></td>
                    <td><input type="text" name="pay_zero[]" readonly class="form-control edit-data" id="zero-field" value="${data.pay_zero}" placeholder="Pay 0..."></td>
                    
                </tr>
            `).clone();

                let activities = $('#quality-remark-add').data('activities');
                $.each(activities, (i, e) => {
                    var option = ` <option ${e.id == data.activity_id ? `selected` : ``} value="${e.id}">${e.name}</option>`
                    $(body).find('.activity-field').append(option)
                });


            } else {
                var body = $(`
                    <tr class="rowid">
                        <td style="width:13%">
                            <span class="btn btn-default btn-sm text-primary edit-record hidden"><i class="mdi mdi-pencil" data-toggle="tooltip" title="Duplicate"></i></span>
                            <span class="btn btn-sm btn-default text-success duplicate-record hidden"><i class="mdi mdi-content-duplicate" data-toggle="tooltip" title="duplicate"></i></span>
                            <span class="btn btn-sm btn-default text-danger delete-record"><i class="mdi mdi-delete-empty" data-toggle="tooltip" title="Delete"></i></span>
                            <input type="hidden" name="quality_ids[]" value="0">
                        </td>
                        <td style="width:17%"><input type="text" name="name[]" class="form-control" id="name-field" placeholder="Name..."></td>
                        <td style="width:15%">
                            <select name="activity_ids[]" id="" class="form-control activity-field actual-check">
                                <option value="">Choose Activity</option>
                                <?php $__currentLoopData = $activities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option data-amount="<?php echo e($activity->use_prp_system == 0  ? $activity->prp_bonus : $system_param->prp_bonus); ?>" value="<?php echo e($activity->id); ?>"><?php echo e($activity->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <input type="hidden" name="activity_id[]" class="activity_id" value="0">
                        </td>
                        <td><input type="text" name="amount_allocated_perc[] " class="form-control actual-check perc-field" placeholder="Amt %..."></td>
                        <td style="width:10%"><input type="text" name="actual" readonly class="form-control actual-field" value="0"></td>
                        <td><input type="text" name="pay_100[]" class="form-control" id="100-field" placeholder="Pay 100..."></td>
                        <td><input type="text" name="pay_75[]" class="form-control" id="75-field" placeholder="Pay 75..."></td>
                        <td><input type="text" name="pay_50[]" class="form-control" id="50-field" placeholder="Pay 50..."></td>
                        <td><input type="text" name="pay_25[]" class="form-control" id="25-field" placeholder="Pay 25..."></td>
                        <td><input type="text" name="pay_zero[]" class="form-control" id="zero-field" placeholder="Pay 0..."></td>
                        
                    </tr>
                `).clone();
            }
            return body;
        }
        let calculateRemarkActual = (data_id, percentage, callback) => {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({
                url: '/prp/calculate/Actual-Remark-Amount/Ajax',
                method: 'POST',
                data: {
                    activity_id: data_id,
                    remark_percentage: percentage
                },
                success: (data) => {
                    callback(data);
                },
                error: (data) => {
                    console.log(data);
                }
            })
        }
        let fillDataToRow = (data = false) => {
            if (data) {
                var Qbody = rowdata(data);
            } else {
                var Qbody = rowdata();
            }
            $(Qbody).find('.delete-record').on('click', (e) => {
                var parentTD = $(e.currentTarget).parent('td');
                $(parentTD).parent('tr').remove();

            });
            $(Qbody).find('.activity-field').select2();
            
            if (data) {
                $(Qbody).find('.activity-field').select2({
                    "readonly": true
                });
            }
            $(Qbody).find('edit-record').on('click', (e) => {

                var parentTD = $(e.currentTarget).parent('td');
                var parentTR = $(parentTD).parent('tr');
                $.each($(parentTR).find('.form-control'), (i, e) => {
                    $(e).prop('readonly', 'False');
                })

            });
            $(Qbody).find('.actual-check').on('change', (e) => {
                var activity_id = $(Qbody).find('.activity-field').val();
                $(Qbody).find('.activity_id').val(activity_id);
                var remark_percentage = $(Qbody).find('.perc-field').val();

                if (activity_id, remark_percentage != '') {
                    calculateRemarkActual(activity_id, remark_percentage, (data) => {
                        $(Qbody).find('.actual-field').val(data);

                    })
                }

            });
            $(Qbody).find('.edit-record').on('click', (e) => {
                let parentTD = $(e.currentTarget).parent('td');
                let parentTR = $(parentTD).parent('tr');
                let action = $(e.currentTarget).data('action');
                if (action == 'edit') {
                    $.each($(parentTR).find('.edit-data'), (i, obj_e) => {
                        $(obj_e).prop('readonly', false);
                    });
                    $(parentTR).find('.activity-field').prop('disabled', false);
                    $(e.currentTarget).data('action', 'rEdit');
                } else {
                    $.each($(parentTR).find('.edit-data'), (i, obj_e) => {
                        $(obj_e).prop('readonly', true);
                    });
                    $(parentTR).find('.activity-field').prop('disabled', true);
                    $(e.currentTarget).data('action', 'edit');
                }
            })

            return Qbody
        }
        $('#quality-remark-add').on('click', () => {
            let Qbody = fillDataToRow();

            $('#remark-body').append(Qbody);
        });
        let serveQualityRemarks = (callback) => {
            $.ajax({
                url: '/prp/serve/quality-remarks/ajax',
                method: 'GET',
                success: (data) => {
                    callback(data);
                },
                error: (data) => {
                    console.log(data)
                }
            })
        };
        serveQualityRemarks((data) => {
            $.each(data, (i, obj) => {
                var rowbody = fillDataToRow(obj);
                $('#remark-body').append(rowbody);
            });
        });

    })
</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('prp::layouts.app',['dataTable'=>true,'select2'=>true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/dan/Documents/projects/nuve/imara-lims-v2/Modules/Prp/Resources/views/remarks/index.blade.php ENDPATH**/ ?>