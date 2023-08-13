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



    .text-bold {
        font-weight: 550;
    }

    .span-header {
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
    $group = getPRPHarvesterGroupsByID($harvester->group_id);
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
            'link' => '/prp/group/show/' . $harvester->group_id,
            'name' => $group->name,
            'icon' => null,
        ),
        array(
            'link' => '/harvester/show/' . $harvester->id,
            'name' => $harvester->name,
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
        <i class="mdi mdi-account-multiple"></i> <?php echo e($harvester->name); ?> Harvesters
        <span class="btn btn-sm btn-default text-primary float-right" data-harvester="<?php echo e(json_encode($harvester)); ?>" data-toggle="modal" data-target="#add-harvester" data-action="edit"> <i class="mdi mdi-pencil"></i> Edit Harvester</span>
        <span class="btn btn-sm btn-default text-danger float-right" data-harvester="<?php echo e(json_encode($harvester)); ?>" data-toggle="modal" data-target="#delete-harvester"> <i class="mdi mdi-delete-empty"></i> Delete Harvester</span>
    </h5>

    <div class="card mt-4">
        <div class="card-body">
            <div class="row border-bottom border-dark p-3">
                <div class="col-sm-6 col-md-6 border-dark border-right">
                    <div class="image" style="width: 40%;">
                        <?php if($harvester->photo_url): ?>
                        <img src="data:image/png;base64,<?php echo e($harvester->photo_url); ?>" style="width:100%;height:100%;object-fit: cover;overflow: hidden;border-radius: 20px !important;" alt="">
                        <?php else: ?>
                        <img src="/images/no-logo.png" style="width:100%;height:100%;object-fit: cover;overflow: hidden;border-radius: 20px !important;" alt="">
                        <?php endif; ?>
                        
                    </div>
                    <div class="row mt-4">
                        <div class="col-6">
                            <p>
                                <span class="span-header">Name</span><br>
                                <span class="span-footer"><?php echo e($harvester->name); ?></span>

                            </p>
                        </div>
                        <div class="col-6">
                            <p>
                                <span class="span-header">Harvester No</span><br>
                                <span class="span-footer"><?php echo e($harvester->harvester_no ?? '-'); ?></span>

                            </p>
                        </div>
                        <div class="col-6">
                            <p>
                                <span class="span-header">Employee No</span><br>
                                <span class="span-footer"><?php echo e($harvester->employee_no ?? '-'); ?></span>

                            </p>
                        </div>
                        <div class="col-6">
                            <p>
                                <span class="span-header">Payroll No</span><br>
                                <span class="span-footer"><?php echo e($harvester->payroll_no ?? '-'); ?></span>

                            </p>
                        </div>
                        <div class="col-6">
                            <p>
                                <span class="span-header">Email</span><br>
                                <span class="span-footer"><?php echo e($harvester->email ?? '-'); ?></span>

                            </p>
                        </div>
                        <div class="col-6">
                            <p>
                                <span class="span-header">Phone No</span><br>
                                <span class="span-footer"><?php echo e($harvester->phone_number ?? '-'); ?></span>

                            </p>
                        </div>
                        <div class="col-6">
                            <p>
                                <span class="span-header">ID Number</span><br>
                                <span class="span-footer"><?php echo e($harvester->id_number ?? '-'); ?></span>

                            </p>
                        </div>
                        <div class="col-6">
                            <p>
                                <span class="span-header">Gender</span><br>
                                <span class="span-footer"><?php echo e($harvester->gender); ?></span>

                            </p>
                        </div>
                        <div class="col-6">
                            <p>
                                <span class="span-header">Status</span><br>
                                <?php if($harvester->is_active): ?>
                                <span class="badge badge-success p-2">Active</span>
                                <?php else: ?>
                                <span class="badge badge-danger p-2">In Active</span>
                                <?php endif; ?>


                            </p>
                        </div>
                        <div class="col-6">
                            <p>
                                <span class="span-header">Activity</span><br>
                                <span class="span-footer"><?php echo e(getActivityByID($harvester->activity_id)->name ?? '-'); ?></span>

                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-6">
                    <div class="detail">
                        <canvas id="myChart" width="400" height="400"></canvas>
                    </div>
                </div>
            </div>
            <h5 class="mt-4">Payslips Summary</h5>
            <div class="table-responsive mt-4">
                <table class="table table-condensed table-sm table-bordered table-hover table-sm">
                    <thead>
                        <tr>
                            <th>Week No</th>
                            <th>Total URCs</th>
                            <th>Easy Speed</th>
                            <th>Medium Speed</th>
                            <th>Difficult Speed</th>
                            <th>Total QR Counts</th>
                            <th>QR Bonus</th>
                            <th>Speed Bonus</th>
                            <th>Total Bonus</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $payslips; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payslip): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td>Week <?php echo e($payslip->weekno); ?></td>
                            <td><?php echo e($payslip->total_urc); ?></td>
                            <td><?php echo e($payslip->easyspeed); ?></td>
                            <td><?php echo e($payslip->mediumspeed); ?></td>
                            <td><?php echo e($payslip->difficultspeed); ?></td>
                            <td><?php echo e($payslip->totalqrcount); ?></td>
                            <td><?php echo e($payslip->quality_amount); ?></td>
                            <td><?php echo e($payslip->speed_amount); ?></td>
                            <td><?php echo e($payslip->total_bonus); ?></td>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.1/chart.min.js" integrity="sha512-QSkVNOCYLtj73J4hbmVoOV6KVZuMluZlioC+trLpewV8qMjsWqlIQvkn1KGX2StWvPMdWGBqim1xlC8krl1EKQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<div class="hidden collect-data" data-easy="<?php echo e(json_encode($easy_values)); ?>" data-difficult="<?php echo e(json_encode($difficult_values)); ?>" data-medium="<?php echo e(json_encode($medium_values)); ?>" data-weeks="<?php echo e(json_encode($harvest_weeks)); ?>"></div>
<script>
    $(() => {
        let addEditHarvesterBody = (data) => {

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

            return body;
        }
        $('#add-harvester').on('show.bs.modal', (e) => {
            let action = $(e.relatedTarget).data('action');
            let activities = $('#add-harvester').data('activities')

            let data = $(e.relatedTarget).data('harvester');

            let body = addEditHarvesterBody(data)
            $.each(activities, (i, e) => {
                $(body).find('#activity-fields').append(`<option value="${e.id}" ${e.id == data.activity_id ? `selected` : ``} >${e.name}</option>`)
            });

            $('#add-harvester').find('.modal-body').empty();
            $('#add-harvester').find('.modal-body').append(body);

            $('#add-harvester').find('#activity-fields').select2()
            $('.can_login').on('change', () => {
                if ($('#add-harvester').find('.can_login').is(':checked')) {
                    $('#add-harvester').find('#canloginrow').removeClass('hidden');
                } else {
                    $('#add-harvester').find('#canloginrow').addClass('hidden');
                }
                console.log('test');
            });
            $('#add-harvester').find('.password_checker').on('keyup', () => {
                $actual_pass = $('#add-harvester').find('.pass').val();
                $con_pass = $('#add-harvester').find('.con-pass').val();
                if ($actual_pass != $con_pass) {
                    
                    $('#add-harvester').find('.con-pass').addClass('red-back')
                } else {
                    $('#add-harvester').find('.con-pass').removeClass('red-back')
                }
            })
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

        let weeks = $('.collect-data').data('weeks');
        let easy = $('.collect-data').data('easy');
        let difficult = $('.collect-data').data('difficult');
        let medium = $('.collect-data').data('medium');

        const ctx = document.getElementById('myChart').getContext('2d');
        const labels = weeks;
        const data = {
            labels: labels,
            datasets: [{
                    label: 'Difficult Performance Chart',
                    data: difficult,
                    fill: false,
                    borderColor: 'red',
                    tension: 0.1
                },
                {
                    label: 'Medium Performance Chart',
                    data: medium,
                    fill: false,
                    borderColor: 'orange',
                    tension: 0.1
                },
                {
                    label: 'Easy Performance Chart',
                    data: easy,
                    fill: false,
                    borderColor: 'green',
                    tension: 0.1
                }

            ]
        };
        const config = {
            type: 'line',
            data: data,
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        };
        console.log(config)
        const myChart = new Chart(ctx, config);

    })
</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('prp::layouts.app',['dataTable'=>true,'select2'=>true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/dan/Documents/projects/nuve/imara-lims-v2/Modules/Prp/Resources/views/harvesters/show_detail.blade.php ENDPATH**/ ?>