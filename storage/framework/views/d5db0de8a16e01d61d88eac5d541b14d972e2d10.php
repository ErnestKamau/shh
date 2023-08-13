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

   <div class="filter-data">
       <form action="<?php echo e(route('getHarvesterByPayroll')); ?>" method="post">
           <?php echo csrf_field(); ?>  
           <div class="form-group float-right">
               <input type="text" name="harvester_no" placeholder="Harvester Number" class="form-control">
           </div>
           <button type="submit" class="btn btn-sm btn-info float-right"><i class="mdi mdi-account-search"></i></button>
       </form>
   </div>

    <div class="card tab-card mt-3" style="clear:both">
        <div class="card-header tab-card-header">
            <ul class="nav nav-tabs card-header-tabs" id="personnel-tab" role="tablist">
                <li class="nav-item">
                    <a href="#groups-tab" class="nav-link active" data-toggle="tab" role="tab" aria-controls="groups-tab" aria-selected="true"> <i class="mdi mdi-account-group" style="color: black; font-size:15px"></i> Groups</a>
                </li>
                <li class="nav-item">
                    <a href="#archived-groups-tab" class="nav-link" data-toggle="tab" role="tab" aria-controls="archive-groups-tab" aria-selected="true"> <i class="mdi mdi-account-group" style="color: red; font-size:15px"></i> Archived Groups</a>
                </li>
                <li class="nav-item">
                    <a href="#qcpersonnel-tab" class="nav-link" data-toggle="tab" role="tab" aria-controls="qcpersonnel-tab" aria-selected="true"> <i class="mdi mdi-account-check" style="color: black; font-size:15px"></i> Quality Controls Personnels</a>
                </li>
            </ul>
        </div>
        <div class="tab-content" id="groups-tabs-content">
            <div class="tab-pane fade show active p-3" id="groups-tab" role="tabpanel" aria-labelledby="one-tab">
                <h5 class="card-title">
                    <i class="mdi mdi-account-group" style="color: black; font-size:15px"></i> Groups
                    <span class="btn btn-sm btn-default text-primary float-right" data-toggle="modal" data-target="#add-group" data-action="add"> <i class="mdi mdi-plus"></i> Add Harvester Group</span>
                    <span class="btn btn-sm btn-default text-warning float-right" data-toggle="modal" data-target="#convert-harvester-qc"><i class="mdi mdi-swap-vertical"></i> Convert Harvester to Qc</span>
                </h5>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-condensed table-bordered table-sm table-stripped table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Group Leader</th>
                                    <th>Status</th>

                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $groups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>

                                    <td>
                                        <span class="btn btn-sm btn-default" data-action="edit" data-hgroup="<?php echo e(json_encode($group)); ?>" data-toggle="modal" data-target="#add-group"><i class="mdi mdi-pencil text-primary"></i></span>
                                        <a href="<?php echo e(route('group-show',['group_id'=>$group->id])); ?>" class="btn btn-sm btn-deafult text-success"><i class="mdi mdi-eye"></i></a>

                                        <span class="btn-sm btn btn-default text-danger" data-toggle="modal" data-target="#delete-group" data-hgroup="<?php echo e(json_encode($group)); ?>"><i class="mdi mdi-delete-empty"></i></span>

                                    </td>
                                    <td><?php echo e($group->name); ?></td>
                                    <td><?php echo e($group->groupleaders); ?></td>
                                    <td class="text-center"><?php echo $group->is_active == 1 ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>'; ?></td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
            <div class="tab-pane fade show p-3" id="archived-groups-tab" role="tabpanel" aria-labelledby="one-tab">
                <h5 class="card-title">
                    <i class="mdi mdi-account-group" style="color: red; font-size:15px"></i> Archived Groups
                   
                </h5>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-condensed table-bordered table-sm table-stripped table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Group Leader</th>
                                    <th>created At</th>

                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $archivedgroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a_group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>

                                    <td>
                                        <span class="btn btn-sm btn-default" data-action="edit" data-hgroup="<?php echo e(json_encode($a_group)); ?>" data-toggle="modal" data-target="#add-group"><i class="mdi mdi-pencil text-primary"></i></span>
                                        <a href="<?php echo e(route('group-show',['group_id'=>$a_group->id])); ?>" class="btn btn-sm btn-deafult text-success"><i class="mdi mdi-eye"></i></a>

                                        <!-- <span class="btn-sm btn btn-default text-danger" data-toggle="modal" data-target="#delete-group" data-hgroup="<?php echo e(json_encode($group)); ?>"><i class="mdi mdi-delete-empty"></i></span> -->

                                    </td>
                                    <td><?php echo e($a_group->name); ?></td>
                                    <td><?php echo e($a_group->groupleaders); ?></td>
                                    <td ><?php echo e($a_group->created_at); ?></td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
            <div class="tab-pane fade show p-3" id="qcpersonnel-tab" role="tabpanel" aria-labbellby="one-tab">
                <h5 class="card-title">
                    <i class="mdi mdi-account-check" style="color: black; font-size:15px"></i> Quality Controls Personnels
                    <span class="btn btn-sm btn-default text-primary float-right" data-toggle="modal" data-target="#add-qc-personnel" data-action="add"><i class="mdi mdi-plus"></i> Add Qc Personnel</span>
                </h5>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-condensed table-bordered table-sm table-stripped table-hover" >
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Payroll No</th>
                                    <th>Can Login</th>
                                    <th>Group</th>
                                    <th>Group Leader</th>
                                    <th>ID Number</th>
                                    <th>Email</th>
                                    <th>Gender</th>
                                    <th>Phone No</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $qcs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $qc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td style="min-width: 10% !important;">
                                        <span class="btn btn-sm btn-default text-primary" data-toggle="modal" data-target="#add-qc-personnel" data-action="edit" data-qc="<?php echo e(json_encode($qc)); ?>"><i class="mdi mdi-pencil" data-toggle="tooltip" title="Edit"></i></span>
                                        <span class="btn btn-sm btn-default text-danger" data-toggle="modal" data-target="#delete-qc-personnel" data-qc="<?php echo e(json_encode($qc)); ?>"><i class="mdi mdi-delete-empty" data-toggle="tooltip" title="Delete"></i></span>
                                    </td>
                                    <td><?php echo e($qc->name); ?></td>
                                    <td><?php echo e($qc->payroll_no); ?></td>
                                    <td class="text-center"><?php echo $qc->can_login == 1 ? '<i class="mdi mdi-account-check text-success"></i>' : '-'; ?></td>
                                    <td><?php echo e($qc->group()->name ?? '-'); ?></td>
                                    <td><?php echo e($qc->group()->groupleaders ?? ''); ?></td>
                                    <td><?php echo e($qc->id_number); ?></td>
                                    <td><?php echo e($qc->email); ?></td>
                                    <td><?php echo e($qc->gender); ?></td>
                                    <td><?php echo e($qc->phone_no); ?></td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>



</main>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script2'); ?>

<div class="modal fade" id="convert-harvester-qc" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo e(route('changeHarvesterToQc')); ?>" method="post">
                <?php echo csrf_field(); ?>
                <div class="modal-body">
                    <div class="alert alert-warning p-2 d-flex">
                        <i class="mdi mdi-alert-decagram"></i>
                        <span class="p-2">
                            Convert a harvester to a QC by choosing below: 
                        </span>
                    </div>
                    <div class="form-group">
                        <label for="" class="control-label">Harvester</label>
                        <select name="harvester_id" id="" class="form-control">
                            <?php $__currentLoopData = $harvesters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $har): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($har->id); ?>"><?php echo e($har->harvester_no); ?> - <?php echo e($har->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="bnt btn-sm btn-outline-warning"><i class="mdi mdi-content-save"></i> Save</button>
                    <span class="btn btn-sm text-danger" data-dismiss="modal">Close</span>
                </div>
            </form>
        </div>

    </div>
</div>

<div class="modal fade" id="add-group" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo e(route('group-add')); ?>" method="post">
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

<div class="modal fade" id="add-qc-personnel" data-groups="<?php echo e(json_encode($groups)); ?>" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="<?php echo e(route('addQcPersonnel')); ?>" method="POST">
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
<div class="modal fade" id="delete-qc-personnel" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo e(route('deleteQcPersonnel')); ?>" method="POST">
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


<script>
    $(() => {
        let addEditGroupsBody = (data = false) => {
            if (data) {
                var body = $(`
                    <div class="alert alert-primary p-2 text-center">
                       <i class="mdi mdi-pencil"></i> Edit ${data.name} group
                   </div>
                   <div class="form-group">
                       <label for="" class="control-label">Name</label>
                       <input type="text" name="name" class="form-control" value="${data.name}" placeholder="Group Name...">
                       <input type="hidden" name="group_id" value="${data.id}">
                   </div>
                   <div class="form-group">
                       <label for="" class="control-label"><input type="checkbox" name="active" ${data.is_active == 1 ? `checked` : ``} id=""> Active</label>
                   </div>
                `).clone();
            } else {
                var body = $(`
                    <div class="alert alert-primary p-2 text-center">
                       <i class="mdi mdi-plus"></i> Add new harvevster group
                   </div>
                   <div class="form-group">
                       <label for="" class="control-label">Name</label>
                       <input type="text" name="name" class="form-control" placeholder="Group Name...">
                       <input type="hidden" name="group_id" value="0">
                   </div>
                   <div class="form-group">
                       <label for="" class="control-label"><input type="checkbox" name="active" checked id=""> Active</label>
                   </div>
                `).clone()

            }
            return body;
        }
        $('#add-group').on('show.bs.modal', (e) => {
            let action = $(e.relatedTarget).data('action');
            if (action == 'add') {
                let body = addEditGroupsBody();

                $('#add-group').find('.modal-body').empty();
                $('#add-group').find('.modal-body').append(body);
            } else {
                let data = $(e.relatedTarget).data('hgroup');

                let body = addEditGroupsBody(data)


                $('#add-group').find('.modal-body').empty();
                $('#add-group').find('.modal-body').append(body);
            }



        });
        let deleteGroupBody = (data) => {
            var body = $(`
                <div class="alert alert-primary d-flex p-2">
                    <i class="mdi mdi-alert-decagram mdi-36px"></i> 
                    <span class="mt-3 p-2">Confirm you want to delete ${data.name} Group . <br> This action will cascade down to all harvesters associated with the above Group</span>
                </div>
                <input type="hidden" name="group_id" value="${data.id}">
            `).clone();
            return body;
        }
        $('#delete-group').on('show.bs.modal', (e) => {
            let data = $(e.relatedTarget).data('hgroup');
            var body = deleteGroupBody(data);
            $('#delete-group').find('.modal-body').empty();
            $('#delete-group').find('.modal-body').append(body);
        });

        let addEditQCBody = (data = false) => {
            if (data) {
                var body = $(`
                    <div class="alert alert-primary p-2 text-center">
                        <i class="mdi mdi-pencil"></i> Edit ${data.name} Quality Control Personnel 
                    </div>
                    <div class="row">
                        <div class="col-sm-6 col-md-6 border-right">
                            <div class="form-group">
                                <label class="control-label">Name</label>
                                <input type="text" name="name" value="${data.name}" placeholder="Name..." class="form-control">
                                <input type="hidden" value="${data.id}" name="qc_id" class="form-control">
                                
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
                                <select name="gender" class="form-control" id="gender-fields">
                                    <option value="">Choose Gender...</option>
                                    <option value="Male" ${data.gender == 'Male'  ? 'selected' : ''}>Male</option>
                                    <option value="Female" ${data.gender == 'Female'  ? 'selected' : ''}>Female</option>
                                </select>
                               
                            </div>
                                
                            
                            <div class="form-group">
                                <label class="control-label">ID Number</label>
                                <input type="text" name="id_number" value="${data.id_number}" placeholder="ID Number..." class="form-control">
                            </div>
                            <div class="form-group">
                                <label class="control-label">Group</label>
                                <select name="group_id" class="form-control" id="groups-fields"></select>
                            </div>
                            <div class="form-group">
                                <label class="control-label"><input type="checkbox" name="active" ${data.is_active == 1 ? `checked` : ``} id=""> Active</label>
                                
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
                        <i class="mdi mdi-plus"></i> Add New Quality Control Personnel
                    </div>
                    <div class="row">
                       <div class="col-sm-6 col-md-6 border-right">
                            <div class="form-group">
                                <label class="control-label">Name</label>
                                <input type="text" name="name" value="" placeholder="Name..." class="form-control">
                                <input type="hidden" value="0" name="qc_id" class="form-control">
                                
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
                                <select name="gender" class="form-control" id="gender-fields">
                                    <option value="">Choose Gender...</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                               
                            </div>
                            <div class="form-group">
                                <label class="control-label">ID Number</label>
                                <input type="text" name="id_number" value="" placeholder="ID Number..." class="form-control">
                            </div>
                            
                            <div class="form-group">
                                <label class="control-label">Group</label>
                                <select name="group_id" class="form-control" id="groups-fields"></select>
                            </div>
                            <div class="form-group">
                                <label class="control-label"><input type="checkbox" name="active" checked id=""> Active</label>
                                
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
        };
        $('#add-qc-personnel').on('show.bs.modal', (e) => {
            let action = $(e.relatedTarget).data('action');
            let groups = $('#add-qc-personnel').data('groups')
            console.log(groups)
            if (action == 'add') {
                let body = addEditQCBody();
                $.each(groups, (i, e) => {
                    $(body).find('#groups-fields').append(`<option value="${e.id}" >${e.name}</option>`)
                });

                $('#add-qc-personnel').find('.modal-body').empty();
                $('#add-qc-personnel').find('.modal-body').append(body);
            } else {
                let data = $(e.relatedTarget).data('qc');

                let body = addEditQCBody(data)
                $.each(groups, (i, e) => {
                    $(body).find('#groups-fields').append(`<option value="${e.id}" ${e.id == data.activity_id ? `selected` : ``} >${e.name}</option>`)
                });

                $('#add-qc-personnel').find('.modal-body').empty();
                $('#add-qc-personnel').find('.modal-body').append(body);
            }
            $('#add-qc-personnel').find('.can_login').on('change', () => {
                if ($('#add-qc-personnel').find('.can_login').is(':checked')) {
                    $('#add-qc-personnel').find('#canloginrow').removeClass('hidden');
                } else {
                    $('#add-qc-personnel').find('#canloginrow').addClass('hidden');
                }
            });
            $('#add-qc-personnel').find('.password_checker').on('keyup', () => {
                $actual_pass = $('#add-qc-personnel').find('.pass').val();
                $con_pass = $('#add-qc-personnel').find('.con-pass').val();
                if ($actual_pass != $con_pass) {
                    console.log('testing')
                    $('#add-qc-personnel').find('.con-pass').addClass('red-back')
                } else {
                    $('#add-qc-personnel').find('.con-pass').removeClass('red-back')
                }
            })
            $('#add-qc-personnel').find('#groups-fields').select2()
            $('#add-qc-personnel').find('#gender-fields').select2();

        });
        let deleteQCBody = (data) => {
            var body = $(`
                <div class="alert alert-primary d-flex p-2">
                    <i class="mdi mdi-alert-decagram mdi-36px"></i> 
                    <span class="mt-3 p-2">Confirm you want to delete ${data.name} Qc Personnel .</span>
                </div>
                <input type="hidden" name="harvester_id" value="${data.id}">
            `).clone();
            return body;
        }
        $('#delete-qc-personnel').on('show.bs.modal', (e) => {
            let data = $(e.relatedTarget).data('qc');
            var body = deleteQCBody(data);
            $('#delete-qc-personnel').find('.modal-body').empty();
            $('#delete-qc-personnel').find('.modal-body').append(body);
        });
    })
</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('prp::layouts.app',['dataTable'=>true,'select2'=>true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/dan/Documents/projects/nuve/imara-lims-v2/Modules/Prp/Resources/views/harvesters/index.blade.php ENDPATH**/ ?>