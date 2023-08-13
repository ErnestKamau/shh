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
    .red-back{
        border:1px solid red !important
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
            'link' => '/prp/group/index',
            'name' => 'Harvester Group',
            'icon' => null,
        ),
        array(
            'link' => '/prp/group/show' . $group->id,
            'name' => $group->name . ' Harvesters',
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
        <i class="mdi mdi-account-multiple"></i> <?php echo e($group->name); ?> Harvesters
        <span class="btn btn-sm btn-default btn-default-h text-primary float-right" data-toggle="modal" data-target="#add-harvester" data-action="add"> <i class="mdi mdi-plus"></i> Add Harvester</span>
        <span class="btn btn-sm btn-default btn-default-h text-success float-right" data-toggle="modal" data-target="#upload-harvesters"><i class="mdi mdi-cloud-upload-outline"></i> Upload Harvesters</span>
        <span class="btn btn-sm btn-default btn-default-h text-warning float-right" data-toggle="modal" data-target="#switch-harvester"><i class="mdi mdi-swap-vertical"></i> Switch Harvester Group</span>
    </h5>

    <div class="card mt-4" style="clear:both !important">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-condensed table-bordered table-sm table-stripped table-hover" style="width: 130%;">
                    <thead>
                        <tr>
                            <th><input type="checkbox" name="" class="select-all" id=""></th>
                            <th>Harvester No</th>
                            <th>Name</th>
                            <th>Employee No</th>
                            <th>Payroll No</th>
                            <th>Group Leader</th>
                            <th>Qc</th>
                            <th>Can Login</th>
                            <th>Gender</th>
                            <th>ID Number</th>
                            <th>Activity</th>
                            <th>Email</th>
                            <th>Status</th>

                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $harvesters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $har): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>

                            <td style="width: 10% !important;">
                                <span class="btn btn-sm btn-default float-right" data-action="edit" data-harvester="<?php echo e(json_encode($har)); ?>" data-toggle="modal" data-target="#add-harvester"><i class="mdi mdi-pencil text-primary"></i></span>
                                <a href="<?php echo e(route('harvester-show',['harvester_id'=>$har->id])); ?>" class="btn btn-sm btn-deafult text-success float-right"><i class="mdi mdi-eye"></i></a>

                                <span class="btn-sm btn btn-default text-danger float-right" data-toggle="modal" data-target="#delete-harvester" data-harvester="<?php echo e(json_encode($har)); ?>"><i class="mdi mdi-delete-empty"></i></span>

                                <input type="checkbox" name="selected-har" class="selected-har float-left" data-record="<?php echo e(json_encode($har)); ?>">

                            </td>
                            <td><?php echo e($har->harvester_no ?? '-'); ?></td>
                            <td><?php echo e($har->name ?? '-'); ?></td>
                            <td><?php echo e($har->employee_no ?? '-'); ?></td>
                            <td><?php echo e($har->payroll_no ?? '-'); ?></td>
                            <td class="text-center"><?php echo $har->is_group_leader == 1 ? '<i class="mdi mdi-account-check text-success"></i>' : '-'; ?></td>
                            <td class="text-center"><?php echo $har->is_qc_staff == 1 ? '<i class="mdi mdi-account-check text-success"></i>' : '-'; ?></td>
                            <td class="text-center"><?php echo $har->can_login == 1 ? '<i class="mdi mdi-account-check text-success"></i>' : '-'; ?></td>
                            <td><?php echo e($har->gender ?? '-'); ?></td>
                            <td><?php echo e($har->id_number ?? '-'); ?></td>
                            <td><?php echo e(getActivityByID($har->activity_id)->name ?? '-'); ?></td>
                            <td><?php echo e($har->email ?? '-'); ?></td>

                            <td class="text-center"><?php echo $har->is_active == 1 ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>'; ?></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</main>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script2'); ?>

<div class="modal fade" data-activities="<?php echo e(json_encode($activities)); ?>" id="add-harvester" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="<?php echo e(route('harvest-add')); ?>" method="post" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <div class="modal-body">


                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-sm btn-outline-primary"><i class="mdi mdi-content-save"></i> Save</button>
                    <span class="btn btn-sm btn-default" data-dismiss="modal">Close</span>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="delete-harvester" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo e(route('harvester-delete')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <div class="modal-body">


                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-sm btn-outline-primary"><i class="mdi mdi-thumb-up"></i> Yes, Delete</button>
                    <span class="btn btn-default text-danger" data-dismiss="modal">Close</span>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="upload-harvesters" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo e(route('harvesterDetailsBulks')); ?>" method="post" enctype="multipart/form-data">
                <?php echo csrf_field(); ?> 
               
                <div class="modal-body">
                    <div class="alert alert-success p-2 d-flex">
                        <i class="mdi mdi-cloud-upload-outline mdi-36px"></i>
                        <span class="p-2 mt-2">
                            Upload bulk harvester data for <?php echo e($group->name); ?> below!
                        </span>
                    </div>
                    <div class="form-group">
                        <label for="" class="control-label">Choose File <small class="text-danger">(Excel only!)</small></label>
                        <input type="file" name="file" id="" class="form-control">
                        <input type="hidden" name="group_id" value="<?php echo e($group->id); ?>">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="submit" class="btn btn-sm btn-success mt-2"><i class="mdi mdi-cloud-upload-outline"></i> Upload</button>
                    <span class="btn btn-sm btn-default text-danger" data-dismiss="modal">Close</span>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="switch-harvester" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo e(route('switchHarvesterGroup')); ?>" method="POST" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>  
                <div class="modal-body">
                    <div class="alert alert-warning p-2 d-flex">
                        <i class="mdi mdi-swap-vertical mdi-24px"></i>
                        <span class="p-2">Switch the below selected harvesters to diffrent Group</span>
                    </div>
                    <div class="form-group">
                        <label for="" class="control-label">Group</label>
                        <select name="group_id" id="">
                            <option value="">Choose Group...</option>
                            <?php $__currentLoopData = $groups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group_): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($group_->id); ?>"><?php echo e($group_->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="header text-bold mt-2">Selected Harvesters: </div>
                    <div class="row mt-3" id="selected-harvesters"></div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-sm btn-sm text-warning"><i class="mdi mdi-thumb-up"></i> Yes, Switch</button>
                    <span class="btn btn-sm btn-default" data-dismiss="modal">Close</span>
                </div>
            </form>
        </div>
    </div>
</div>




<script>
    $(() => {
        let addEditHarvesterBody = (data = false) => {
            if (data) {
                var body = $(`
                    <div class="alert alert-primary p-2 text-center">
                        <i class="mdi mdi-pencil"></i> Edit ${data.name} harvester 
                    </div>
                    <div class="row">
                        <div class="col-sm-6 col-md-6 border-right">
                            <div class="form-group">
                                <label class="control-label">Name</label>
                                <input type="text" name="name" value="${data.name}" placeholder="Name..." class="form-control">
                                <input type="hidden" value="${data.id}" name="harvester_id" class="form-control">
                                <input type="hidden" value="<?php echo e($group->id); ?>" name="group_id" class="form-control">
                            </div>
                            <div class="form-group">
                                <label class="control-label">Harvester Number</label>
                                <input type="text" name="harvester_no" value="${data.harvester_no}" placeholder="Harvester Number..." class="form-control">
                            </div>
                            <div class="form-group">
                                <label class="control-label">Employee Number</label>
                                <input type="text" name="employee_no" value="${data.employee_no}" placeholder="Employee Number..." class="form-control">
                            </div>
                            <div class="form-group">
                                <label class="control-label">Payroll Number</label>
                                <input type="text" name="payroll_no" value="${data.payroll_no}" placeholder="Payroll Number..." class="form-control">
                            </div>
                            <div class="form-group">
                                <label class="control-label">Email</label>
                                <input type="text" name="email" value="${data.email}" placeholder="Email..." class="form-control">
                            </div>
                            <div class="form-group">
                                <label class="control-label">Phone Number</label>
                                <input type="text" name="phone_no" value="${data.phone_no}" placeholder="Phone Number..." class="form-control">
                            </div>
                           
                        </div>
                        <div class="col-sm-6 col-md-6">

                            <div class="form-group">
                                <label for="" class="control-label">Profile Photo </label>
                                <input type="file" name="photo_url" id="" class="form-control">
                            </div>
                            <div class="form-group">
                                <label class="control-label">Gender</label>
                                <input type="text" name="gender" value="${data.gender}" placeholder="Gender..." class="form-control">
                            </div>
                            <div class="form-group">
                                <label class="control-label">ID Number</label>
                                <input type="text" name="id_number" value="${data.id_number}" placeholder="ID Number..." class="form-control">
                            </div>
                            <div class="form-group">
                                <label class="control-label">Activity</label>
                                <select name="activity_id" class="form-control" id="activity-fields"></select>
                            </div>
                            <div class="form-group">
                                <label class="control-label"><input type="checkbox" name="active" ${data.is_active == 1 ? `checked` : ``} id=""> Active</label>
                                
                            </div>
                            <div class="form-group">
                                <label for="" class="control-label"><input ${data.is_group_leader == 1 ? 'checked' :''} type="checkbox" name="is_group_leader" id=""> Is Group Leader</label>
                            </div>
                            <div class="form-group">
                                <label for="" class="control-label"><input class="can_login" ${data.can_login == 1 ? 'checked' :''} type="checkbox" name="can_login" id=""> Can Login</label>
                            </div>
                          
                        </div>
                    </div>
                    <div class="row hidden" id="canloginrow">
                        <div class="form-group col-sm-6">
                            <label class="control-label">Password</label>
                            <input type="text"  name="password" value="" placeholder="Password..." class="form-control password_checker pass">
                        </div>
                        <div class="form-group col-sm-6">
                            <label class="control-label">Confirm Password</label>
                            <input type="text" name="confirm_password" value="" placeholder="Confirm Password..." class="form-control password_checker con-pass">
                        </div>

                    </div>
                    
                `).clone();
            } else {
                var body = $(`
                    <div class="alert alert-primary p-2 text-center">
                        <i class="mdi mdi-plus"></i> Add new harvester to <?php echo e($group->name); ?>

                    </div>
                    <div class="row">
                       <div class="col-sm-6 col-md-6 border-right">
                            <div class="form-group">
                                <label class="control-label">Name</label>
                                <input type="text" name="name" value="" placeholder="Name..." class="form-control">
                                <input type="hidden" value="0" name="harvester_id" class="form-control">
                                <input type="hidden" value="<?php echo e($group->id); ?>" name="group_id" class="form-control">
                            </div>
                            <div class="form-group">
                                <label class="control-label">Harvester Number</label>
                                <input type="text" name="harvester_no" value="" placeholder="Harvester Number..." class="form-control">
                            </div>
                            <div class="form-group">
                                <label class="control-label">Employee Number</label>
                                <input type="text" name="employee_no" value="" placeholder="Employee Number..." class="form-control">
                            </div>
                            <div class="form-group">
                                <label class="control-label">Payroll Number</label>
                                <input type="text" name="payroll_no" value="" placeholder="Payroll Number..." class="form-control">
                            </div>
                            <div class="form-group">
                                <label class="control-label">Email</label>
                                <input type="text" name="email" value="" placeholder="Email..." class="form-control">
                            </div>
                            <div class="form-group">
                                <label class="control-label">Phone Number</label>
                                <input type="text" name="phone_no" value="" placeholder="Phone Number..." class="form-control">
                            </div>
                            
                       </div>
                       <div class="col-sm-6 col-md-6">
                            <div class="form-group">
                                <label for="" class="control-label">Profile Photo</label>
                                <input type="file" name="photo_url" id="" class="form-control">
                            </div>
                            <div class="form-group">
                                <label class="control-label">Gender</label>
                                <input type="text" name="gender" value="" placeholder="Gender..." class="form-control">
                            </div>
                            <div class="form-group">
                                <label class="control-label">ID Number</label>
                                <input type="text" name="id_number" value="" placeholder="ID Number..." class="form-control">
                            </div>
                            
                            <div class="form-group">
                                <label class="control-label">Activity</label>
                                <select name="activity_id" class="form-control" id="activity-fields"></select>
                            </div>
                            <div class="form-group">
                                <label class="control-label"><input type="checkbox" name="active" checked id=""> Active</label>
                                
                            </div>
                            <div class="form-group">
                                <label for="" class="control-label"><input type="checkbox" name="is_group_leader" id=""> Is Group Leader</label>
                            </div>
                            <div class="form-group">
                                <label for="" class="control-label"><input type="checkbox" class="can_login" name="can_login" id=""> Can Login</label>
                            </div>
                           
                       </div>
                   </div>
                   
                   <div class="row hidden" id="canloginrow">
                        <div class="form-group col-sm-6">
                            <label class="control-label">Password</label>
                            <input type="text"  name="password" value="" placeholder="Password..." class="form-control password_checker pass">
                        </div>
                        <div class="form-group col-sm-6">
                            <label class="control-label">Confirm Password</label>
                            <input type="text" name="confirm_password" value="" placeholder="Confirm Password..." class="form-control password_checker con-pass">
                        </div>

                    </div>
                    
                `).clone()

            }
            return body;
        }
        $('#add-harvester').on('show.bs.modal', (e) => {
            let action = $(e.relatedTarget).data('action');
            let activities = $('#add-harvester').data('activities')
            if (action == 'add') {
                let body = addEditHarvesterBody();
                $.each(activities, (i, e) => {
                    $(body).find('#activity-fields').append(`<option value="${e.id}" >${e.name}</option>`)
                });

                $('#add-harvester').find('.modal-body').empty();
                $('#add-harvester').find('.modal-body').append(body);
            } else {
                let data = $(e.relatedTarget).data('harvester');

                let body = addEditHarvesterBody(data)
                $.each(activities, (i, e) => {
                    $(body).find('#activity-fields').append(`<option value="${e.id}" ${e.id == data.activity_id ? `selected` : ``} >${e.name}</option>`)
                });

                $('#add-harvester').find('.modal-body').empty();
                $('#add-harvester').find('.modal-body').append(body);
            }
            $('#add-harvester').find('.can_login').on('change', () => {
                if($('#add-harvester').find('.can_login').is(':checked')){
                    $('#add-harvester').find('#canloginrow').removeClass('hidden');
                }else{
                    $('#add-harvester').find('#canloginrow').addClass('hidden');  
                }
            });
            $('#add-harvester').find('.password_checker').on('keyup',()=>{
                $actual_pass = $('#add-harvester').find('.pass').val();
                $con_pass = $('#add-harvester').find('.con-pass').val();
                if($actual_pass != $con_pass){
                    console.log('testing')
                    $('#add-harvester').find('.con-pass').addClass('red-back')
                }else{
                    $('#add-harvester').find('.con-pass').removeClass('red-back')
                }
            })
            $('#add-harvester').find('#activity-fields').select2()

        });
        let deleteHarvesterBody = (data) => {
            var body = $(`
                <div class="alert alert-primary d-flex p-2">
                    <i class="mdi mdi-alert-decagram mdi-36px"></i> 
                    <span class="mt-3 p-2">Confirm you want to delete ${data.name} Harvester .</span>
                </div>
                <input type="hidden" name="harvester_id" value="${data.id}">
            `).clone();
            return body;
        }
        $('#delete-harvester').on('show.bs.modal', (e) => {
            let data = $(e.relatedTarget).data('harvester');
            var body = deleteHarvesterBody(data);
            $('#delete-harvester').find('.modal-body').empty();
            $('#delete-harvester').find('.modal-body').append(body);
        });

        let selectedHarvesters = (data)=>{
            $body = $(`
                <div class="col-md-6 p-2 col-sm-6">
                    <input type="checkbox" name="selected_har[]" checked value="${data.id}" id=""> ${data.name} - ${data.harvester_no}
                </div>
            `).clone();
            return $body;
        }

        $('#switch-harvester').on('show.bs.modal',()=>{
            $('#switch-harvester').find('#selected-harvesters').empty()
            $.each($('.selected-har'),(i,e)=>{
                if ($(e).is(':checked')){
                    $data = $(e).data('record');
                    $elem = selectedHarvesters($data);
                    $('#switch-harvester').find('#selected-harvesters').append($elem)
                }
            })
        })
    })
</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('prp::layouts.app',['dataTable'=>true,'select2'=>true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/dan/Documents/projects/nuve/imara-lims-v2/Modules/Prp/Resources/views/harvesters/show.blade.php ENDPATH**/ ?>