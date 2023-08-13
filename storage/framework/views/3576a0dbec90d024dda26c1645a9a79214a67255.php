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
        <i class="mdi mdi-account-multiple"></i> Head Count Summary

    </h5>
    <div class="card tab-card mt-2" style="clear:both">
        <div class="card-header tab-card-header">
            <ul class="nav nav-tabs card-header-tabs" id="attendance-master-tab" role="tablist">
                <li class="nav-item">
                    <a href="#attendance-tab" class="nav-link active" id="attendance-trigger" data-toggle="tab" role="tab" aria-controls="attendance-tab" aria-selected="true"> <i class="mdi mdi-account-multiple" style="font-size: 15px;"></i> Head Count Summary</a>
                </li>
                <li class="nav-item">
                    <a href="#approvers-tab" class="nav-link" id="approvers" data-toggle="tab" role="tab" aria-controls="approvers-tab" aria-selected="true"> <i class="mdi mdi-cogs" style="color: black; font-size:15px"></i> Approver Configuration</a>
                </li>
            </ul>
        </div>
        <div class="tab-content" id="masterroll-tabs-content">
            <div class="tab-pane fade show active p-3" id="attendance-tab" role="tabpanel" aria-labelledby="one-tab">
                
                <form action="<?php echo e(route('headcountindex')); ?>" class="row p-2" method="GET">

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="" class="control-label">Harvest Weeks</label>
                            <select name="harvest_week_ids[]" multiple class="form-control">
                                <option value="">Select Harvest Weeks</option>
                                <?php $__currentLoopData = $harvest_weeks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $week): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($week->id); ?>" <?php echo e(in_array($week->id,$weeks) ? 'selected' : ''); ?>>Week <?php echo e($week->week_no); ?> - <?php echo e($week->year); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <button class="btn btn-light btn-sm float-right" type="submit"><i class="mdi mdi-filter-plus"></i> Apply</button>
                    </div>

                </form>
                <div class="table-responsive mt-2 p-3">
                    <table class="table table-condensed table-sm table-hover table-striped table-bordered ">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>DayCode</th>
                                <th>Date</th>
                                <th>Group</th>
                                <th>Captured By</th>
                                <th>Status</th>

                            </tr>
                        </thead>

                        <tbody>
                            <?php $__currentLoopData = $headers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $header): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td>

                                    <a href="<?php echo e(route('headcountshow',$header->id)); ?>" class="btn btn-sm btn-default text-success"><i class="mdi mdi-eye"></i></a>
                                </td>
                                <td><?php echo e($header->getDayBatchDetails()->day_code); ?></td>
                                <td><?php echo e($header->getDayBatchDetails()->today_date); ?></td>
                                <td><?php echo e($header->getGroupDetails()->name); ?></td>
                                <td><?php echo e($header->getCreatedByUser()->name); ?></td>
                                <td class="text-center"><?php echo $header->status == 1 ? '<span class="badge-success badge-pill p-1"><i class="mdi mdi-marker-check text-success"></i> Approved</span>' : '<i class="mdi mdi-close-circle text-danger"></i>'; ?>

                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="tab-pane fade show p-3" id="approvers-tab" role="tabpanel" aria-labbellby="one-tab">
                <h5 class="card-title">
                    <span class="btn btn-sm btn-default text-primary float-right" data-toggle="modal" data-target="#add-approver" data-mode="add"><i class="mdi mdi-account-plus"></i> Add Approver</span>
                </h5>
                <div class="table-responsive mt-5">
                    <table class="table table-condensed table-sm table-hover table-striped table-bordered ">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>User</th>
                                <th>Role</th>
                                <th>Approved Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $approvers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $approver): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>

                                <td>
                                    <span class="btn btn-sm btn-default text-primary" data-toggle="modal" data-target="#add-approver" data-mode="edit" data-record="<?php echo e(json_encode($approver)); ?>"><i class="mdi mdi-pencil"></i></span>
                                </td>
                                <td><?php echo e($approver->getUserDetails()->name ?? ''); ?></td>
                                <td><?php echo e($approver->getRoleDetails()->name ?? ''); ?></td>
                                <td class="text-center"><?php echo $approver->is_active == 1? '<i class="mdi mdi-checkbox-marked-circle-outline text-success"></i>' : '<i class="mdi mdi-close-octagon text-danger"></i>'; ?> </td>

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

<div class="modal fade" id="add-approver" data-roles="<?php echo e(json_encode($roles)); ?>" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo e(route('addAttendanceHeaderApprovalConfig')); ?>" method="post">
                <?php echo csrf_field(); ?>
                <div class="modal-body">
                    
    
                </div>
                <div class="modal-footer">
                    <button class="btn btn-sm btn-outline-primary" type="submit"><i class="mdi mdi-content-save"></i> Save</button>
                    <span class="btn btn-sm btn-default" data-dismiss="modal">Cancel</span>
                </div>
    
            </form>
        </div>

    </div>
</div>

<script>
    $(() => {
        let userRoles = (id, callback) => {
            $.ajax({
                url: `/prp/get/User-Roles/${id}/Ajax`,
                method: 'GET',
                success:(data)=>{
                    callback(data)
                },
                error:(data)=>{
                    console.log(data);
                }
            })
        }

        let addEditApproverBody = (roles,data = false)=>{
            console.log($data);
            if($data){
                var body = $(`
                    <div class="alert alert-primary p-2">
                        <i class="mdi mdi-pencil"></i> Edit Attendnanxce Approver ${data.approvername}
                    </div>
                    <div class="form-group">
                        <label for="" class="control-label">Choose Role</label>
                        <select name="role_id" id="role_id" class="form-control">
                            <option value="">Select Role....</option>
                           
                        </select>
                    </div>
                    <input type="hidden" name="config_id" value="${data.id}">
                    <div class="form-group">
                        <label for="" class="control-label">Choose Approver</label>
                        <select name="approver_id" id="approver-id" class="form-control">
                            <option value="">Select Role First...</option>
                        </select>

                    </div>
                    <div class="form-group">
                        <label for="" class="control-label"><input type="checkbox" ${data.is_active == 1 ? 'checked' : ''} name="is_active"> Is Active</label>
                    </div>
                `).clone()
                

            }else{
                var body = $(`
                    <div class="alert alert-primary p-2">
                        <i class="mdi mdi-plus"></i> Add Attendnanxce Approver 
                    </div>
                    <div class="form-group">
                        <label for="" class="control-label">Choose Role</label>
                        <select name="role_id" id="role_id" class="form-control">
                            <option value="">Select Role....</option>
                           
                        </select>
                    </div>
                    <input type="hidden" name="config_id" value="0">
                    <div class="form-group">
                        <label for="" class="control-label">Choose Approver</label>
                        <select name="approver_id" id="approver-id" class="form-control">
                            <option value="">Select Role First...</option>
                        </select>

                    </div>
                    <div class="form-group">
                        <label for="" class="control-label"><input type="checkbox" checked name="is_active"> Is Active</label>
                    </div>
                `).clone()
            }

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

        $('#add-approver').on('show.bs.modal',(e)=>{
            $roles = $('#add-approver').data('roles');
            $mode = $(e.relatedTarget).data('mode')
            $data = $mode == 'edit' ? $(e.relatedTarget).data('record') : false
            $body = addEditApproverBody($roles,$data);
            if($mode == 'edit'){
                $($body).find('#role_id').trigger('change');
            }
            $('#add-approver').find('.modal-body').empty();
            $('#add-approver').find('.modal-body').append($body); 
        });

    })
</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('prp::layouts.app',['dataTable'=>true,'select2'=>true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/dan/Documents/projects/nuve/imara-lims-v2/Modules/Prp/Resources/views/Data-Proccess/masterroll/attendance_index.blade.php ENDPATH**/ ?>