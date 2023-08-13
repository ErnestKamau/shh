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
            'link' => '',
            'name' => 'Other Configurations',
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
        <i class="mdi mdi-cog mdi-24px"></i> Other Configurations 
        

        <span class="btn btn-sm btn-default text-primary float-right" data-toggle="modal" data-target="#add-config-header" data-action="add"> <i class="mdi mdi-plus"></i> Add Configuration Header</span>
    </h5>

    <div class="card tab-card mt-4">
        <div class="card-header tab-card-header">
            <ul class="nav nav-tabs card-header-tabs" role="tablist">
                <?php $__currentLoopData = $config_headers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $header): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li class="nav-item">
                    <a href="#<?php echo e($header->tab_name); ?>" class="nav-link <?php echo e($loop->iteration ==1 ? 'active' : ''); ?>" data-toggle="tab" role="tab" aria-selected="true"><?php echo e($header->name); ?></a>
                </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
        <div class="tab-content">
            <?php $__currentLoopData = $config_headers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $headers): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="tab-pane fade show p-3 <?php echo e($loop->iteration ==1 ? 'active' : ''); ?>" role="tabpanel" aria-labelledby="one-tab" id="<?php echo e($headers->tab_name); ?>">
                <h5 class="card-title">
                    <i class="mdi mdi-cogs mdi-24px"></i> <?php echo e($headers->name); ?>

                    <span class="btn btn-sm text-primary" data-toggle="modal" data-target="#add-config-header" data-action="edit" data-header="<?php echo e(json_encode($headers)); ?>"><i class="mdi mdi-pencil"></i></span>
                    <span class="btn btn-default text-success btn-sm float-right" data-header="<?php echo e(json_encode($headers->id)); ?>" data-name="<?php echo e(json_encode($headers->name)); ?>" data-toggle="modal" data-action="add" data-target="#add-configuration"> <i class="mdi mdi-plus"></i> Add <?php echo e($headers->name); ?></span>
                </h5>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-condensed table-bordered table-sm table-stripped table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Code</th>
                                    <th>Value</th>
                                    <th>Level</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $headers['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>

                                    <td>
                                        <span class="btn btn-sm btn-default" data-action="edit" data-header="<?php echo e(json_encode($item->system_configuration_type_id)); ?>" data-config="<?php echo e(json_encode($item)); ?>" data-toggle="modal" data-target="#add-configuration"><i class="mdi mdi-pencil text-primary"></i></span>
                                        <span class="btn btn-sm btn-default" data-config="<?php echo e(json_encode($item)); ?>" data-toggle="modal" data-target="#delete-configuration"><i class="mdi mdi-delete-empty text-danger"></i></span>
    
                                    </td>
                                    <td><?php echo e($item->name); ?></td>
                                    <td><?php echo e($item->code); ?></td>
                                    <td><?php echo e($item->value); ?></td>
                                    <td><?php echo e($item->level); ?></td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>


    </div>

</main>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script2'); ?>

<div class="modal fade" id="add-configuration" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo e(route('store-configuration-data')); ?>" method="post">
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

<div class="modal fade" id="add-config-header" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo e(route('store-configuration-types')); ?>" method="post">
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
        let addEdititemBody = (header, name, data = false) => {
            if (data) {
                var body = $(`
                    <div class="alert alert-primary text-center p-2">
                        <i class="mdi mdi-plus"></i> Edit ${name} Configuration
                    </div>
                    <div class="form-group">
                        <label class="control-label">Name</label>
                        <input type="text" value="${data.name}" name="name" placeholder="Name..." class="form-control">
                        <input type="hidden" name="config_id" value="${data.id}">
                        <input type="hidden" name="header_id" value="${header}">
                    </div>
                    <div class="form-group">
                        <label class="control-label">Code</label>
                        <input type="text" name="code" value="${data.code}" placeholder="Code..." class="form-control">
                    </div> 
                    <div class="form-group">
                        <label class="control-label">Value</label>
                        <input type="text" name="value" value="${data.value}" placeholder="Value..." class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="control-label">Level</label>
                        <input type="text" name="level" placeholder="Level" value="${data.level}" class="form-control">
                    </div>
                `).clone();
            } else {
                var body = $(`
                    <div class="alert alert-primary text-center p-2">
                        <i class="mdi mdi-plus"></i> Add ${name} Configuration
                    </div>
                    <div class="form-group">
                        <label class="control-label">Name</label>
                        <input type="text" name="name" placeholder="Name..." class="form-control">
                        <input type="hidden" name="config_id" value="0">
                        <input type="hidden" name="header_id" value="${header}">
                    </div>
                    <div class="form-group">
                        <label class="control-label">Code</label>
                        <input type="text" name="code" placeholder="Code..." class="form-control">
                    </div> 
                    <div class="form-group">
                        <label class="control-label">Value</label>
                        <input type="text" name="value" placeholder="Value..." class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="control-label">Level</label>
                        <input type="text" name="level" placeholder="Level" class="form-control">
                    </div>
                `).clone()

            }
            return body;
        }
        let addEditConfigBody = (data=false)=>{
            if(data){
                var body = $(`
                    <div class="alert alert-primary p-2 text-center">
                        <i class="mdi mdi-pencil"></i> Edit ${data.name}
                    </div>
                    <div class="form-group">
                        <label for="" class="control-label"></label>
                        <input type="text" name="name" placeholder="Name..." value="${data.name}" class="form-control">
                        <input type="hidden" name="config_id" value="${data.id}">
                    </div>
                    <div class="form-group">
                        <label for="" class="control-label">Code</label>
                        <input type="text" name="code" placeholder="Code..." value="${data.code}" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="" class="control-label"><input type="checkbox" ${data.is_active == 1 ? `checked` : ``} name="active" checked id=""> Active</label>

                    </div>
                `).clone()

            }else{
                var body = $(`
                    <div class="alert alert-primary p-2 text-center">
                        <i class="mdi mdi-plus"></i> Add Configuration Header
                    </div>
                    <div class="form-group">
                        <label for="" class="control-label"></label>
                        <input type="text" name="name" placeholder="Name..." class="form-control">
                        <input type="hidden" name="config_id" value="0">
                    </div>
                    <div class="form-group">
                        <label for="" class="control-label">Code</label>
                        <input type="text" name="code" placeholder="Code..." class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="" class="control-label"><input type="checkbox" name="active" checked id=""> Active</label>

                    </div>
                `).clone()
            }
            return body;
        }
        $('#add-configuration').on('show.bs.modal', (e) => {
            let action = $(e.relatedTarget).data('action');
            if (action == 'add') {
                var name = $(e.relatedTarget).data('name')
                var header = $(e.relatedTarget).data('header');
                console.log(header);
                let body = addEdititemBody(header, name);
                $('#add-configuration').find('.modal-body').empty();
                $('#add-configuration').find('.modal-body').append(body);
            } else {
                let data = $(e.relatedTarget).data('config');
                var header = $(e.relatedTarget).data('header');

                let body = addEdititemBody(header, data.name, data)
                $('#add-configuration').find('.modal-body').empty();
                $('#add-configuration').find('.modal-body').append(body);
            }


        });
        $('#add-config-header').on('show.bs.modal', (e) => {
            let action = $(e.relatedTarget).data('action');
            if (action == 'add') {
                console.log('data '+action);
               
              
                let body = addEditConfigBody();
                $('#add-config-header').find('.modal-body').empty();
                $('#add-config-header').find('.modal-body').append(body);
            } else {
                let data = $(e.relatedTarget).data('header');
                let body = addEditConfigBody(data)
                $('#add-config-header').find('.modal-body').empty();
                $('#add-config-header').find('.modal-body').append(body);
            }


        });
        
    })
</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('prp::layouts.app',['dataTable'=>true,'select2'=>true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/dan/Documents/projects/nuve/imara-lims-v2/Modules/Prp/Resources/views/configuration/other_configurations.blade.php ENDPATH**/ ?>