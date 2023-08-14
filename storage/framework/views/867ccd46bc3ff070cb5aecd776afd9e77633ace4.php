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
            'link' => '/prp',
            'name' => 'Attendance Report',
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
        <i class="mdi mdi-account-question mdi-24px"></i> Week <?php echo e($harvest_week->week_no); ?> | <?php echo e($day->code); ?> | Attendance Report

        <?php echo $day->is_action == 1 ? '<span class="badge badge-pill p-1 ml-3 bg-success"><i class="mdi mdi-checkbox-marked-circle-outline"></i> Actioned</span>' : ' <span class="badge badge-pill p-1 ml-3 bg-primary"><i class="mdi mdi-alert-decagram"></i> Not Actioned</span>'; ?>

       
        <span class="btn btn-sm btn-outline-primary float-right" data-toggle="modal" data-target="#mark-absent"><i class="mdi mdi-cogs"></i> Mark As Absent</span>

    </h5>
    <?php if($day->is_actioned == 1): ?>
    <div class="table-responsive mt-4 p-3">
        <form action="">
            <table class="table table-condensed table-sm table-hover table-striped table-bordered ">
                <thead>
                    <tr>
                        
                        <th>Payroll No</th>
                        <th>Name</th>
                        <th>Group</th>
                        <th>ID Number</th>
                        <th>Absent Date</th>
                        <th>Day Batch</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $attendance; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $har): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($har->getHarvesterDetails()->payroll_no); ?></td>
                        <td><?php echo e($har->getHarvesterDetails()->name); ?></td>
                        <td><?php echo e($har->getHarvesterGroupDetails()->name); ?></td>
                        <td><?php echo e($har->getHarvesterDetails()->id_number); ?></td>
                        <td><?php echo e($day->harvest_date); ?></td>
                        <td><?php echo e($day->getDaybatchDetails()->day_code); ?></td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </form>
    </div>
    <?php else: ?>
    <div class="table-responsive mt-4 p-3">
        <form action="">
            <table class="table table-condensed table-sm table-hover table-striped table-bordered ">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="select-all"></th>
                        <th>Payroll No</th>
                        <th>Name</th>
                        <th>Group</th>
                        <th>ID Number</th>
                        <th>Harvest Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $harvesters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $har): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td>
                            <input type="checkbox" data-record="<?php echo e(json_encode($har)); ?>" name="harvester_id" value="<?php echo e($har->id); ?>" class="is-absent">
                        </td>
                        <td><?php echo e($har->payroll_no); ?></td>
                        <td><?php echo e($har->name); ?></td>
                        <td><?php echo e($har->group()->name); ?></td>
                        <td><?php echo e($har->id_number); ?></td>
                        <td><?php echo e($day->today_date); ?></td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </form>
    </div>
    <?php endif; ?>
</main>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script2'); ?>
<div class="modal fade" id="mark-absent" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo e(route('attendance-store')); ?>" method="post">
                <?php echo csrf_field(); ?>
                <div class="modal-body">
                    <div class="alert alert-primary p-2 d0-flex">
                        <i class="mdi mdi-alert-decagram-outline mdi-24px"></i>
                        <span class="p-2">Confirm you want to mark the following personnels as absent?</span>
                    </div>
                    <div class="form-group">
                        <input type="hidden" name="week_id" value="<?php echo e($harvest_week->id); ?>">
                        <input type="hidden" name="day_batch_id" value="<?php echo e($day->id); ?>">
                    </div>
                    <div class="absent-data row"></div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-sm btn-outline-primary"><i class="mdi mdi-thumb-up-outline"></i> Mark Absent</button>
                    <span class="btn btn-sm btn-default" data-dismiss="modal">Cancel</span>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
    $(() => {

        let selectedHarvesters = (data) => {
            $body = $(`
                <div class="col-md-6 p-2 col-sm-6">
                    <input type="checkbox" name="selected_har[]" checked value="${data.id}" id=""> ${data.name} - ${data.harvester_no}
                </div>
            `).clone();
            return $body;
        }

        $('#mark-absent').on('show.bs.modal', () => {
            $('#mark-absent').find('.absent-data').empty()
            $.each($('.is-absent'), (i, e) => {
                if ($(e).is(':checked')) {
                    $data = $(e).data('record');
                    $elem = selectedHarvesters($data);
                    $('#mark-absent').find('.absent-data').append($elem)
                }
            })
        })
        $('#select-all').on('change', () => {

            if ($('#select-all').is(':checked')) {
                console.log('test')
                $.each($('.is-absent'), (i, obj) => {
                    $(obj).prop('checked', true);
                })
                $('select[name="DataTables_Table_0_length"]').empty();
                let options = $(`
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="75">75</option>
                    <option value="100">100</option>
                    <option value="1000"selected>1000</option>
                `).clone();
                $('select[name="DataTables_Table_0_length"]').append(options);
                $('select[name="DataTables_Table_0_length"]').trigger('change')
            } else {
                $.each($('.is-absent'), (i, obj) => {
                    $(obj).prop('checked', false);
                })
            }
        });


    })
</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('prp::layouts.app',['dataTable'=>true,'select2'=>true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/dan/Documents/projects/nuve/imara-lims-v2/Modules/Prp/Resources/views/Data-Proccess/Transactions/attendance/show.blade.php ENDPATH**/ ?>