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
            'link' => '/prp/payslips/index',
            'name' => 'Payslips',
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
        <i class="mdi mdi-file-account-outline"></i> Payment Summary Reports
        
    </h5>
    <div class="card tab-card mt-3">
        <div class="card-header tab-card-header">
            <ul class="nav nav-tabs card-header-tabs" id="summary-tabs"  role="tablist">
                <li class="nav-item m-2">
                    <a href="#summary-report" role="tab" data-toggle="tab" class="nav-link active" aria-controls="groups-tab" aria-selected="true">Payslip Summary Report</a>
                </li>
                <li class="nav-item m-2">
                    <a href="#approvers" role="tab" data-toggle="tab" class="nav-link" aria-controls="groups-tab" aria-selected="true">Approvers Config</a>
                </li>
            </ul>
        </div>
        <div class="tab-content" id="summary-tabs-content">
            <div class="tab-pane fade show active p-3" id="summary-report" role="tabpanel" aria-labelledby="one-tab">
                <div class="filter-group">
                    <form action="<?php echo e(route('createPartialReportHeader')); ?>" class="row p-2" method="POST">
                        <?php echo csrf_field(); ?>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="" class="control-label">Harvest Weeks</label>
                                <select name="week_id[]" multiple id="week_ids" class="form-control">
                                    <?php $__currentLoopData = $weeks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $week): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($week->id); ?>">Week <?php echo e($week->week_no); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="" class="control-label">From Date</label>
                                <input type="date" name="from_date" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                    <label for="" class="control-label">To Date</label>
                                    <input type="date" name="to_date" class="form-control">
                                </div>
                            </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="" class="control-label">Exclude Dates </label>
                                <select name="exclude_date[]" id="exclude_date" multiple class="form-group"></select>
                            </div>
                        </div>
                        <input type="hidden" name="has_filter" value="1" class="form-control">
                        <div class="col-md-2">
                            <button class="btn btn-sm btn-outline-primary float-right mt-3"><i class="mdi mdi-filter-plus-outline"></i> Apply</button>
                        </div>
                    </form>
                </div>
                <div class="table-responsive p-3 mt-3">
                    <table class="table table-condensed table-bordered table-hover table-sm">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Harvester Weeks</th>
                                <th>Harvesters</th>
                                <th>From Date</th>
                                <th>To Date</th>
                                <th>Exclude Dates</th>
                                <th>Status</th>
                                <th>Created At</th>
                                <th>Created By</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $partials; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $partial): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo e(route('getPartialReportData',['id'=>$partial->id])); ?>" class="btn btn-default btn-sm text-success"><i class="mdi mdi-eye"></i></a>
                                    
                                    </td>
                                    <td><?php echo e($partial->weeks); ?></td>
                                    <td><?php echo e($partial->getharvestersCount()); ?></td>
                                    <td><?php echo e($partial->from_date); ?></td>
                                    <td><?php echo e($partial->to_date); ?></td>
                                    <td><?php echo e($partial->excludeddates); ?></td>
                                    <td><?php echo e($partial->statusname); ?></td>
                                    <td><?php echo e($partial->created_at); ?></td>
                                    <td><?php echo e($partial->creator); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="tab-pane fade show p-3" id="approvers" role="tabpanel" aria-labbellby="one-tab">
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

<div class="modal fade" id="generate_payslips" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo e(route('payslip-generate')); ?>" method="post">
                <?php echo csrf_field(); ?>
                <div class="modal-body">
                   
                    <div class="alert alert-success p-2 d-flex">
                        <i class="mdi mdi-alert-decagram mdi-24px"></i>
                        <span class="p-2">Confirm you want to Compile Data and Generate Payslips for week <?php echo e($harvest_week->week_no); ?></span>
                    </div>
                    <input type="hidden" name="harvest_week_id" value="<?php echo e($harvest_week->id); ?>">
                    <input type="hidden" name="is_repeat" value="0">
                    <div class="form-group">
                        <label for="" class="control-label"><input type="checkbox" name="notification" id=""> Send Email Notification on Completion</label>
                    </div>
                    
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-sm btn-outline-success"><i class="mdi mdi-content-save"></i> Yes, Generate</button>.
                    <span class="btn btn-sm btn-default" data-dismiss="modal">Close</span>
                </div>
            </form>
        </div>
    </div>
</div>
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
$(()=>{
    $('#week_ids').on('change',(e)=>{

        var value =  $('#week_ids').val()
        if(value.length > 0){
            $.ajax({
                url:'/prp/get/Day-Batch-By-HarvestWeek',
                method:'POST',
                data:{"week_ids" :value,'_token': "<?php echo e(csrf_token()); ?>"},
                success:(data)=>{
                    console.log(data);
                    $('#exclude_date').empty()
                    $.each(data,(i,obj)=>{
                        var option = `<option value="${obj.id}">${obj.day_code} - ${obj.today_date}</option>`;
                        $('#exclude_date').append(option);
                        $('#exclude_date').select2();
                    });
                },
                error:(data)=>{
                    console.log(data);
                }
            })
        }else{
            $('#exclude_date').empty()
        }
    });
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
                        <i class="mdi mdi-pencil"></i> Edit Payslip Report Approver ${data.approvername}
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
                    <input type="hidden" name="is_payslip" value="1">
                    <div class="form-group">
                        <label for="" class="control-label"><input type="checkbox" ${data.is_active == 1 ? 'checked' : ''} name="is_active"> Is Active</label>
                    </div>
                `).clone()
                

            }else{
                var body = $(`
                    <div class="alert alert-primary p-2">
                        <i class="mdi mdi-plus"></i> Add Payslip Report Approver 
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
                    <input type="hidden" name="is_payslip" value="1">
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

<?php echo $__env->make('prp::layouts.app',['dataTable'=>true,'select2'=>true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/dan/Documents/projects/nuve/imara-lims-v2/Modules/Prp/Resources/views/Data-Proccess/payslips/harvester_payslips.blade.php ENDPATH**/ ?>