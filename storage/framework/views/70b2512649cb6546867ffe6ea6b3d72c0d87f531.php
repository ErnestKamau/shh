<?php $__env->startSection('title2'); ?>
    <title>System-Configuration-Types</title>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content2'); ?>
    <main>
        <?php 
            $items = array(
                array(
                    'link'=>route('configuration-type-home'),
                    'name'=>'Configuration Types',
                    'icon'=>null
                )
                );
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
        <h2 class="p-4">
            <i class="mdi mdi-cogs"></i> Configuration Types
            <button class="btn btn-outline-primary btn-sm float-right" data-toggle="modal" data-target="#add-configuration-type"><i class="mdi mdi-plus"></i> Add</button>
        </h2>
        <div class="card tab-card">
            <div class="card-header tab-card-header">
                <ul class="nav nav-tabs card-header-tabs" id="configuration-tabs" role="tablist">
                    <li class="nav-item">
                        <a href="#active-configuration" class="nav-link active" id="active-configuration-type" data-toggle="tab" role="tab" aria-controls="active-configuration" aria-selected="true"><i class="mdi mdi-map-marker"></i> Active Configuration Types</a>
                    </li>
                    
                </ul>
            </div>
            <div class="tab-content" id="configurations-type-tab">
                <div class="tab-pane fade show active p-3" id="active-configuration" role="tabpanel" aria-labelledby="one-tab">
                    <h5 class="card-title">Active Configuration Types</h5>
                    <div class="table-responsive">
                        <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
                            <thead class="bg-light p-2">
                                <tr>
                                    <th>No</th>
                                    <th nowarap>Name</th>
                                    <td>Date</td>
                                    <th nowrap>Status</th>
                                    <th nowrap >Description</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $configuration_types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $configuration): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                
                                    <tr>
                                        <td valign="center"><?php echo e($loop->iteration); ?></td>
                                        <td><?php echo e($configuration->configuration_type); ?></td>
                                        <td><?php echo e($configuration->created_at); ?></td>
                                        <td class="text-small"><?php echo $configuration->status == 0 ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>'; ?></td>
                                        <td>
                                            <span class="btn btn-outline-dark btn-sm" data-toggle="modal" data-target="#configuration-description-<?php echo e($configuration->id); ?>"><i class="mdi mdi-message-text"></i></span>
                                            <div class="modal fade" id="configuration-description-<?php echo e($configuration->id); ?>" role="dialog">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h4 class="modal-title"><i class="mdi mdi-key"></i> <?php echo e($configuration->configuration_type); ?> Description.</h4>
                                                        </div>
                                                        <div class="modal-body">
                                                            <h5>Description</h5>
                                                            <div class="panel panel-default">
                                                                <div class="panel-body">
                                                                    <?php echo e($configuration->description); ?>

                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="btn btn-outline-primary btn-sm" data-target="#edit-configuration-<?php echo e($configuration->id); ?>" data-toggle="modal"><i class="mdi mdi-pencil"></i></span>
                                            <div class="modal fade" id="edit-configuration-<?php echo e($configuration->id); ?>" role="dialog">
                                                <div class="modal-dialog">
                                                    <form action="<?php echo e(route('edit-configuration-type',['id'=>$configuration->id])); ?>" method="post" class="modal-content" enctype="multipart/form-data">
                                                        <?php echo csrf_field(); ?> 
                                                        <div class="modal-header">
                                                            <h4 class="modal-title"><i class="mdi mdi-pencil"></i> Edit <?php echo e($configuration->configuration_type); ?></h4>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="form-group">
                                                                <label class="control-label">Configuration Type</label>
                                                                <input type="text" name="name" class="form-control" value="<?php echo e($configuration->configuration_type); ?>" placeholder="Configuration Name...">
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="control-label">Description</label>
                                                                <textarea name="description" placeholder="Configuration Description" class="form-control" rows="6" value=""><?php echo e($configuration->description); ?></textarea>
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="control-label">
                                                                <input type="checkbox" value=0 <?php echo e($configuration->status == 0 ? 'checked':''); ?> name="status">
                                                                Active
                                                                </label>
                                                            </div>
                                                            <div class="form-group hidden">
                                                                <label class="control-label">Config ID</label>
                                                                <input type="number" name="config_id" value=<?php echo e($configuration->id); ?> class="form-control">
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="submit" class="btn btn-outline-primary"> <i class="mdi mdi-content-save"></i> Update</button>
                                                            <button type="button" class="btn btn-outline-danger" data-dismiss="modal">Close</button>
                                                        </div>

                                                    </form>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- ------------------------  -->
                
            </div>
        </div>
    </main>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script2'); ?>
<div class="modal fade" id="add-configuration-type" role="dialog">
    <div class="modal-dialog">
        <form action="<?php echo e(route('add-configuration-type')); ?>" method="post" class="modal-content" enctype="multipart/form-data">
        <?php echo csrf_field(); ?> 
            <div class="modal-header">
                <h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Configuration Type</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="control-label">Configuration Type</label>
                    <input type="text" name="name" class="form-control" value="" placeholder="Configuration Name..." required>
                </div>
                <div class="form-group">
                    <label class="control-label">Description</label>
                    <textarea name="description" class="form-control" rows="6" placeholder="Configuration Description" value="" required></textarea>
                </div>
                
                
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-outline-primary"> <i class="mdi mdi-content-save"></i> Save</button>
                <button type="button" class="btn btn-outline-danger" data-dismiss="modal">Close</button>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.configuration.layout.app', ['dataTable'=>true, 'select2'=>true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/dan/Documents/projects/nuve/imara-lims-v2/resources/views/layouts/configuration/system/configurationType.blade.php ENDPATH**/ ?>