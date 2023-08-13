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
            'link' => '/prp/head-count-summary/index',
            'name' => 'Head Count Summary',
            'icon' => null,
        ),
        array(
            'link' => '/prp/head-count-summary/show/' . $header->id,
            'name' => 'Head Count Summary',
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
        <i class="mdi mdi-account-multiple"></i> Head Count Summary | <?php echo e($header->getDayBatchDetails()->today_date); ?>


    </h5>

    <div class="card tab-card mt-5" style="clear:both">
        <div class="card-header tab-card-header">
            <ul class="nav nav-tabs card-header-tabs" id="attendance-master-tab" role="tablist">
                <li class="nav-item">
                    <a href="#attendance-tab" class="nav-link active" id="attendance-trigger" data-toggle="tab" role="tab" aria-controls="attendance-tab" aria-selected="true"> <i class="mdi mdi-account-multiple" style="font-size: 15px;"></i> Head Count Summary</a>
                </li>
                <li class="nav-item">
                    <a href="#approvers-tab" class="nav-link" id="approvers" data-toggle="tab" role="tab" aria-controls="approvers-tab" aria-selected="true"> <i class="mdi mdi-checkbox-marked-circle-outline" style="color: black; font-size:15px"></i> Approvers</a>
                </li>
            </ul>
        </div>
        <div class="tab-content" id="masterroll-tabs-content">
            <div class="tab-pane fade show active p-3" id="attendance-tab" role="tabpanel" aria-labelledby="one-tab">
                <h5 class="card-title"> <i class="mdi mdi-account-multiple"></i> Head Count Summary | <?php echo e($header->getDayBatchDetails()->today_date); ?></h5>
                <div class="table-responsive mt-3">
                    <table class="table table-condensed table-sm table-hover table-striped table-bordered ">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Harvester No</th>
                                <th>Name</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Group</th>
                                <th>Reason</th>
                                <th>Captured By</th>


                            </tr>
                        </thead>

                        <tbody>
                            <?php $__currentLoopData = $details; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td>
                                    
                                    <?php if($header->status == 0): ?>
                                    <span class="btn btn-sm btn-default text-primary" data-record="<?php echo e(json_encode($detail)); ?>" data-target="#edit-attendance" data-toggle="modal"><i class="mdi mdi-pencil"></i></span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo e($detail->getHarvesterDetails()->harvester_no); ?></td>
                                <td><?php echo e($detail->getHarvesterDetails()->name); ?></td>
                                <td><span class="badge badge-light badge-pill p-1"><i class="mdi mdi-alert-decagram-outline"></i> <?php echo e($detail->detailstatus); ?></span> </td>
                                <td><?php echo e($detail->today_date); ?></td>
                                <td><?php echo e($detail->getGroupDetails()->name); ?></td>
                                <td><?php echo e($detail->getAttendanceReasonDetails()->name ?? '-'); ?></td>
                                <td><?php echo e($detail->getCaptureByDetails()->name); ?></td>

                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="tab-pane fade show p-3" id="approvers-tab" role="tabpanel" aria-labbellby="one-tab">
                <h5 class="card-title"><i class="mdi mdi-checkbox-marked-circle-outline"></i> Approvers</h5>
                <div class="table-responsive mt-4">
                    <table class="table table-condensed table-sm table-hover table-striped table-bordered ">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>User</th>
                                <th>Role</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $approvers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $approver): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>

                                <td>
                                    <?php if($approver->status == 0): ?>
                                    <span class="btn btn-sm btn-default text-primary" data-record="<?php echo e(json_encode($approver)); ?>" data-toggle="modal" data-target="#edit-approver"><i class="mdi mdi-pencil"></i></span>
                                    <span class="btn btn-sm btn-default text-success" data-record="<?php echo e(json_encode($approver)); ?>" data-status="1"  data-toggle="modal" data-target="#change-status-data"><i class="mdi mdi-thumb-up"></i></span>
                                    <span class="btn btn-sm btn-default text-danger" data-record="<?php echo e(json_encode($approver)); ?>" data-status="1" data-toggle="modal" data-target="#change-status-data"><i class="mdi mdi-thumb-down"></i></span>
                                    <?php endif; ?>

                                </td>
                                <td><?php echo e($approver->getUserDetails()->name); ?></td>
                                <td><?php echo e($approver->getRoleDetails()->name); ?></td>
                                <td><span class="badge badge-light badge-pill p-1"><i class="mdi mdi-alert-decagram"></i> <?php echo e($approver->approvalstatus); ?></span></td>
                                
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
<div class="modal fade" id="edit-attendance" data-reasons="<?php echo e(json_encode($reasons)); ?>" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo e(route('updateAttendanceReport')); ?>" method="POST">
                <?php echo csrf_field(); ?>  
                <div class="modal-body">
                   
                </div>
                <div class="modal-footer">
                    <button class="btn btn-sm btn-outline-primary"><i class="mdi mdi-content-save"></i> Save</button>
                    <span class="btn btn-sm btn-default" data-dismiss="modal">Close</span>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="edit-approver" data-roles="<?php echo e(json_encode($roles)); ?>" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo e(route('editAttendanceApproverData')); ?>" method="post">
                <?php echo csrf_field(); ?>
                <div class="modal-body">
                   
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-sm btn-outline-primary"><i class="mdi mdi-content`-save"></i> Save</button>
                    <button class="btn btn-sm btn-default" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="change-status-data" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo e(route('changeAttendanceHeaderApprovalStatus')); ?>" method="post">
            <?php echo csrf_field(); ?>
                <div class="modal-body">
                   

                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-sm btn-outline-primary"><i class="mdi mdi-content`-save"></i> Save</button>
                    <button class="btn btn-sm btn-default" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>



<script>

    $(()=>{
        const reasons = $('#edit-attendance').data('reasons');
        let editBody = (data = false,roles)=>{
            
            var body = $(`
                <div class="form-group">
                    <label class="control-label">Role</label>
                    <select name="role_id" id="role_id" class="form-group">
                        
                    </select>
                </div>
                <div class="form-group">
                    <label class="control-label">Approver</label>
                    <select name="approver_id" id="approver-id" class="form-group">
                        
                    </select>
                </div>
                <input type="hidden" name="config_id" value="${data.id}">
                <div class="form-group">
                    <label class="control-label"><input type="checkbox" ${data.is_active == 1 ? `selected` : ``} name="is_active" id=""> Is Active</label>
                    
                </div>

            `).clone();
            
            $.each(roles,(i,obj)=>{
                $option = `<option value="${obj.id}" ${data != false && obj.id == data.role_id ? `selected` : ``}>${obj.name}</option>`;
                $(body).find('#role_id').append($option);
            });

            $(body).find('#role_id').on('change',(e)=>{
                $value = $(e.currentTarget).val();
                $(body).find('#approver-id').empty();
                userRoles($value,(records)=>{
                    // console.log(records);
                    $.each(records,(i,obj)=>{
                        $option = `<option value="${obj.user_id}" ${data != false && data.user_id == obj.user_id ? `selected` : ``}>${obj.username}</option>`
                        $(body).find('#approver-id').append($option);
                    });
                    $(body).find('#approver-id').select2();
                });
            });
            $(body).find('#role_id').select2();
            $(body).find('#approver-id').select2();

            return body;
        }
        $('#edit-approver').on('show.bs.modal',(e)=>{
            $data = $(e.relatedTarget).data('record');
            $roles = $('#edit-approver').data('roles');
            $body = editBody($data)
            $('#edit-approver').find('.modal-body').empty();
            $('#edit-approver').find('.modal-body').append($body);
            $($body).find('#role_id').trigger('change');
            
        });

        let changeStatusBody = (status,data)=>{
            if(status == 1){
                var body = $(`
                    <div class="alert alert-primary p-2 d-flex">
                        <i class="mdi mdi-alert-decagram mdi-24px"></i>
                        <span class="p-2">Confirm you want to <b>Approve</b> the <?php echo e($header->getDayBatchDetails()->today_date); ?> Head Count Summary</span>
                    </div>
                    <input type="hidden" name="approval_id" value="${data.id}">
                    <input type="hidden" name="status" value="${status}">
                   
                `).clone()
            }else{
                var body = $(`
                    <input type="hidden" name="approval_id" value="${data.id}">
                    <input type="hidden" name="status" value="${status}">
                    <div class="alert alert-danger p-2 d-flex">
                        <i class="mdi mdi-alert-decagram mdi-24px"></i>
                        <span class="p-2">Confirm you want to <b>Reject</b> the <?php echo e($header->getDayBatchDetails()->today_date); ?> Head Count Summary</span>
                    </div>
                `).clone()
            }
            return body
        }

        $('#change-status-data').on('show.bs.modal',(e)=>{
            $data = $(e.relatedTarget).data('record');
            $status = $(e.relatedTarget).data('status');
            $body = changeStatusBody($status,$data)
            $('#change-status-data').find('.modal-body').empty();
            $('#change-status-data').find('.modal-body').append($body);
        });
        let editAttendanceBody = (data)=>{
            var body = $(`
                <div class="alert alert-primary p-2 d-flex">
                    <i class="mdi mdi-pencil mdi-24px"></i>
                    <span class="p-2">Edit ${data.harvestername} Attendance Record</span>
                </div>
                <div class="form-group">
                    <label for="" class="control-label">Harvester</label>
                    <input type="text" disabled value="${data.harvestername}" class="form-control">
                </div>
                <div class="form-group">
                    <label for="" class="control-label">Status</label>
                    <select name="status" id="" class="form-control status">
                        <option value="">Status...</option>
                        <option value="0" ${data.status == 0 ? `selected`: ``}>Absent</option>
                        <option value="1" ${data.status == 1 ? `selected`: ``}>Present</option>
                        <option value="2" ${data.status == 2 ? `selected`: ``}>Absent With Reason</option>
                    </select>
                </div>
                <div class="form-group hidden reason-field">
                    <label for="" class="control-label">Reason</label>
                    <select name="reason_id" id="reason_id" class="form-control">
                        <option value=""></option>
                    </select>
                </div>
                <input type="hidden" name="attendance_id" value="${data.id}">
            `).clone()
            $.each(reasons,(i,obj)=>{
                var optionBody = `<option value="${obj.id}" ${obj.id == data.attendance_reason_id ? `selected` : ``}>${obj.name}</option>`
                $(body).find('#reason_id').append(optionBody);
            });
            $(body).find('.status').select2()
            $(body).find('#reason_id').select2()

           
            return body;
        }
        $('#edit-attendance').on('show.bs.modal',(e)=>{
            var record = $(e.relatedTarget).data('record');
            var body = editAttendanceBody(record);
            $('#edit-attendance').find('.modal-body').empty();
            $('#edit-attendance').find('.modal-body').append(body)
            // $('#edit-attendance').find('#reason_id').select2()
            $('#edit-attendance').find('.status').on('change',(e)=>{
                var value = $('#edit-attendance').find('.status').val();
                console.log(value)
                if(value == 2){
                    
                    $('#edit-attendance').find('.reason-field').removeClass('hidden');
                }else{
                    
                    $('#edit-attendance').find('.reason-field').addClass('hidden');
                }
            })

        })
    })

</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('prp::layouts.app',['dataTable'=>true,'select2'=>true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/dan/Documents/projects/nuve/imara-lims-v2/Modules/Prp/Resources/views/Data-Proccess/masterroll/attendance_show.blade.php ENDPATH**/ ?>