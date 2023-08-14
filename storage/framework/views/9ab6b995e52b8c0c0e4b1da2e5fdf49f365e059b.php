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
            'link' => '/prp',
            'name' => 'PRP',
            'icon' => null,
        ),
        array(
            'link' => '/prp/species/index',
            'name' => 'Species',
            'icon' => null,
        ),
        array(
            'link' => '/prp/species/show/'.$species->id,
            'name' => $species->name,
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
        <i class="mdi mdi-test-tube"></i> Species | <?php echo e($species->name); ?>

        <span class="btn btn-sm btn-default text-primary float-right" data-toggle="modal" data-target="#add-variety" data-action="add"> <i class="mdi mdi-plus"></i> Add Variety</span>
    </h5>

    <div class="card mt-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-condensed table-bordered table-sm table-stripped table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Material No</th>
                            <th>Zvam</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Speed Factor</th>
                            <th>Is Active</th>

                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $varieties; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $variety): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            
                            <td>
                                <span class="btn btn-sm btn-default" data-action="edit" data-variety="<?php echo e(json_encode($variety)); ?>" data-toggle="modal" data-target="#add-variety"><i class="mdi mdi-pencil text-primary"></i></span>
    
                                <span class="btn-sm btn btn-default text-danger" data-toggle="modal" data-target="#delete-variety" data-variety="<?php echo e(json_encode($variety)); ?>"><i class="mdi mdi-delete-empty"></i></span>
    
                            </td>
                            <td><?php echo e($variety->material_no); ?></td>
                            <td><?php echo e($variety->zvam); ?></td>
                            <td><?php echo e($variety->name); ?></td>
                            <td><?php echo e($variety->description); ?></td>
                            <td><?php echo e(getPrpNewSystemConfigurationDataByID($variety->speed_factor_id)->name ?? ''); ?></td>
                            <td class="text-center"><?php echo $variety->is_active == 1 ? '<i class="mdi mdi-check-decagram text-success"></i>' : '<i class="mdi mdi-check-decagram text-danger"></i>'; ?></td>
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

<div class="modal fade" data-species="<?php echo e(json_encode($species)); ?>" id="add-variety" data-speedfactors="<?php echo e(json_encode($speed_factors)); ?>" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo e(route('store-varieties')); ?>" method="post">
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
<div class="modal fade" id="delete-variety" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo e(route('variety-delete')); ?>" method="POST">
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
        let addEditVarietyBody = (data = false) => {
            if (data) {
                var body = $(`
                    <div class="alert alert-primary p-2 text-center">
                        <i class="mdi mdi-plus"></i> Add new Variety for <?php echo e($species->name); ?>

                    </div>
                    <div class="form-group">
                        <label class="control-label">Material Number</label>
                        <input type="text" class="form-control" name="material_no" placeholder="Material No ..." value="${data.material_no}"> 
                        <input type="hidden" name="variety_id" value="${data.id}">
                        <input type="hidden" name="species_id" value="<?php echo e($species->id); ?>">
                    </div>
                    <div class="form-group">
                        <label class="control-label">Zvam</label>
                        <input type="text" class="form-control" name="zvam" placeholder="Zvam..." value="${data.zvam}"> 
                    </div>
                    <div class="form-group">
                        <label class="control-label">Name</label>
                        <input type="text" class="form-control" name="name" placeholder="Name..." value="${data.name}"> 
                    </div>
                    <div class="form-group">
                        <label class="control-label">Description</label>
                        <input type="text" class="form-control" name="description" placeholder="Description..." value="${data.description}"> 
                    </div>
                    <div class="form-group">
                        <label class="control-label">Speed Factor</label>
                        <select name="speed_factor" id="speed_factor" class="form-control"></select>
                    </div>
                    <div class="form-group">
                       <label for="" class="control-label"><input type="checkbox" name="active" ${data.is_active == 1 ? `checked` : ``} value="1"> Active</label>
                    </div>
                    
                `).clone();
            } else {
                var body = $(`
                <div class="alert alert-primary p-2 text-center">
                        <i class="mdi mdi-plus"></i> Add new Variety for <?php echo e($species->name); ?>

                    </div>
                    <div class="form-group">
                        <label class="control-label">Material Number</label>
                        <input type="text" class="form-control" name="material_no" placeholder="Material No ..." value=""> 
                        <input type="hidden" name="variety_id" value="0">
                        <input type="hidden" name="species_id" value="<?php echo e($species->id); ?>">
                        
                    </div>
                    <div class="form-group">
                        <label class="control-label">Zvam</label>
                        <input type="text" class="form-control" name="zvam" placeholder="Zvam..." value=""> 
                    </div>
                    <div class="form-group">
                        <label class="control-label">Name</label>
                        <input type="text" class="form-control" name="name" placeholder="Name..." value=""> 
                    </div>
                    <div class="form-group">
                        <label class="control-label">Description</label>
                        <input type="text" class="form-control" name="description" placeholder="Description..." value=""> 
                    </div>
                    <div class="form-group">
                        <label class="control-label">Speed Factor</label>
                        <select name="speed_factor"  id="speed_factor" class="form-control"></select>
                    </div>
                    <div class="form-group">
                       <label for="" class="control-label"><input type="checkbox" name="active" checked value="1" id=""> Active</label>
                    </div>
                `).clone()

            }
            return body;
        }
        $('#add-variety').on('show.bs.modal', (e) => {
            let action = $(e.relatedTarget).data('action');
            let speed_factor = $('#add-variety').data('speedfactors');
            let species = $('#add-variety').data('species');
            if (action == 'add') {
                let body = addEditVarietyBody();
                $.each(speed_factor, (i, obj) => {
                    // console.log(obj);
                    $(body).find('#speed_factor').append(`<option value="${obj.id}" ${obj.id==species.speed_factor_id ? `selected` : ``}>${obj.name}</option>`);
                });
                 $('#add-variety').find('.modal-body').empty();
                 $('#add-variety').find('.modal-body').append(body);
            } else {
                let data = $(e.relatedTarget).data('variety');
                
                let body = addEditVarietyBody(data)
                $(body).find('#speed_factor').empty();
                $.each(speed_factor, (i, obj) => {
                    console.log(data);
                    $(body).find('#speed_factor').append(`<option value="${obj.id}" ${obj.id == data.speed_factor_id ? `selected`: ``} >${obj.name}</option>`);
                });

                 $('#add-variety').find('.modal-body').empty();
                 $('#add-variety').find('.modal-body').append(body);
            }
             $('#add-variety').find('#speed_factor').select2();


        });
        let deleteVarietyBody = (data) => {
            var body = $(`
                <div class="alert alert-primary d-flex p-2">
                    <i class="mdi mdi-alert-decagram mdi-36px"></i> 
                    <span class="mt-3 p-2">Confirm you want to delete ${data.name} variety.</span>
                </div>
                <input type="hidden" name="variety_id" value="${data.id}">
            `).clone();
            return body;
        }
        $('#delete-variety').on('show.bs.modal', (e) => {
            let data = $(e.relatedTarget).data('variety');
            var body = deleteVarietyBody(data);
            $('#delete-variety').find('.modal-body').empty();
            $('#delete-variety').find('.modal-body').append(body);
        });
    })
</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('prp::layouts.app',['dataTable'=>true,'select2'=>true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/dan/Documents/projects/nuve/imara-lims-v2/Modules/Prp/Resources/views/species/show.blade.php ENDPATH**/ ?>