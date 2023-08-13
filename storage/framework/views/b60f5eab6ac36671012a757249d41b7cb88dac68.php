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
            'link' => '/prp/prd-files/index',
            'name' => 'Day Batch',
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
        <i class="mdi mdi-file-account-outline"></i> Week <?php echo e($harvest_week->week_no); ?> | Batches
        <!-- <span class="btn btn-sm btn-default text-primary float-right" data-toggle="modal" data-action="add" data-target="#add-batch-day"><i class="mdi mdi-plus"></i> Create Batch</span> -->

    </h5>
    <div class="table-responsive mt-4 p-3">
        <table class="table table-condensed table-sm table-hover table-striped table-bordered ">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Code</th>
                    <th>Date</th>
                    <th></th>

                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $headers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $header): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td>
                        <span class="btn btn-sm btn-default text-primary" data-toggle="modal" data-action="edit" data-record="<?php echo e(json_encode($header)); ?>" data-target="#add-batch-day"><i class="mdi mdi-pencil"></i></span>
                        <span class="btn btn-sm btn-default text-danger"><i class="mdi mdi-delete-empty"></i></span>
                        <a href="<?php echo e(route('day-batch-show',['id'=>$header->id,'type'=>'prd'])); ?>" class="btn btn-sm btn-default text-success"><i class="mdi mdi-eye"></i></a>
                    </td>
                    <td><?php echo e($header->day_code); ?></td>
                    <td><?php echo e($header->today_date); ?></td>
                    <td class="text-center"><?php echo $header->is_active == 1 ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>'; ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
</main>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script2'); ?>
<div class="modal fade" id="add-batch-day" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo e(route('day-batch-add')); ?>" method="post">
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

<script>
    $(() => {
       let addDayBody = (data=false)=>{
            if(data){
                var body = $(`
                    <div class="alert alert-primary p-2 d-flex">
                       <i class="mdi mdi-pencil mdi-24px"></i>
                       <span class="p-2">
                            Edit day ${data.day_code} of week <?php echo e($harvest_week->week_no); ?>

                       </span>
                    </div>
                    <div class="form-group">
                        <label class="control-label">Date</label>
                        <input type="date" value="${data.today_date}" name="date" class="form-control">
                        <input type="hidden" name="batch_id" value="${data.id}">
                    </div>
                    <div class="form-group">
                        <label class="control-label">
                            <input type="checkbox" name="is_active" ${data.is_active == 0 ? `checked` : ``} id=""> Is Active ?
                        </label>
                    </div>

                `).clone();
            }else{
                var body = $(`
                    <div class="alert alert-primary p-2 d-flex">
                       <i class="mdi mdi-plus mdi-24px"></i>
                       <span class="p-2">
                            Add new batch day for week <?php echo e($harvest_week->week_no); ?>

                       </span>
                       
                   </div>
                    <div class="form-group">
                        <label class="control-label">Date </label>
                        <input type="date" name="date" value="<?php echo e(date('Y-m-d')); ?>" class="form-control">
                        <input type="hidden" name="batch_id" value="0">
                    </div>
                    <div class="form-group">
                        <label class="control-label">
                            <input type="checkbox" name="is_active" checked id=""> Is Active ?
                        </label>
                    </div>

                `).clone();
            }
            return body;
       }

      
        $('#add-batch-day').on('show.bs.modal', (e) => {
            var action = $(e.relatedTarget).data('action');
            var data = $(e.relatedTarget).data('record');
            var addbody = action == 'edit' ? addDayBody(data) : addDayBody();
            console.log('data');
            $('#add-batch-day').find('.modal-body').empty();
            $('#add-batch-day').find('.modal-body').append(addbody);
        });

    })
</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('prp::layouts.app',['dataTable'=>true,'select2'=>true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/dan/Documents/projects/nuve/imara-lims-v2/Modules/Prp/Resources/views/Data-Proccess/Data/prd/day_header.blade.php ENDPATH**/ ?>