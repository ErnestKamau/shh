<?php $__env->startSection('title2'); ?>
    <title>System-Configurations</title>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content2'); ?>
    <main>
        <?php 
            $items = array(
                array(
                    'link'=>route('configuration-system-home'),
                    'name'=>'Configurations',
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
            <i class="mdi mdi-cogs"></i> Configurations
        </h2>
        <?php $__currentLoopData = $configuration_types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $configuration): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
            $configs = getconfigByID($configuration->id)
        ?>
            <div class="card" style="padding: 10px;margin-bottom:20px">
                <h5 class="card-title"><i class="mdi mdi-cog-box"></i> <?php echo e($configuration->configuration_type); ?>

                <button class="btn btn-outline-primary btn-sm float-right" data-toggle="modal" data-target="#add-configuration-<?php echo e($configuration->id); ?>"><i class="mdi mdi-plus"></i> Add</button>
                </h5>
                <div class="table-responsive">
                    <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
                        <thead class="bg-light p-2">
                            <tr>
                                <th>No</th>
                                <th nowrap>key</th>
                                <th nowrap>Value</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $configs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $config): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><?php echo e($loop->iteration); ?></td>
                                <td><?php echo e($config->key); ?></td>
                                <td><?php echo e($config->value); ?></td>
                                <td>
                                    <span class="btn btn-outline-primary btn-sm" data-toggle="modal" data-target="#edit-configuration-<?php echo e($config->id); ?>"><i class="mdi mdi-pencil"></i></span>
                                    <div class="modal fade" id="edit-configuration-<?php echo e($config->id); ?>" role="dialog">
                                        <div class="modal-dialog">
                                            <form action="<?php echo e(route('edit-configuration',['id'=>$config->id])); ?>" method="post" class="modal-content" enctype="multipart/form-data">
                                            <?php echo csrf_field(); ?> 
                                                <div class="modal-header">
                                                    <h4 class="modal-title"><i class="mdi mdi-pencil"></i> Edit <?php echo e($config->key); ?></h4>

                                                </div>
                                                <div class="modal-body">
                                                    <div class="form-group">
                                                        <label class="control-label">Key</label>
                                                        <input type="text" name="key" value="<?php echo e($config->key); ?>" placeholder="Configuration Key..." class="form-control">
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="control-label">Value</label>
                                                        <input type="text" name="value" value="<?php echo e($config->value); ?>" placeholder="Configuration Value..." class="form-control">
                                                    </div>
                                                    <div class="form-group hidden">
                                                        <label class="control-label">Config ID</label>
                                                        <input type="number" name="config_id" value="<?php echo e($config->id); ?>">
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="submit" class="btn btn-outline-primary"> <i class="mdi mdi-content-save"></i> Update</button>
                                                    <button type="button" class="btn btn-outline-danger" data-dismiss="modal">Close</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                    <span class="btn btn-outline-danger btn-sm" data-toggle="modal" data-target="#delete-configuration-<?php echo e($config->id); ?>"><i class="mdi mdi-delete-empty"></i></span>
                                    <div class="modal fade" id="delete-configuration-<?php echo e($config->id); ?>" role="dialog">
                                        <div class="modal-dialog">
                                            <form action="<?php echo e(route('delete-configuration',['id'=>$config->id])); ?>" method="post" class="modal-content" enctype="multipart/form-data">
                                            <?php echo csrf_field(); ?> 
                                                <div class="modal-header">
                                                    <h4 class="modal-title"><i class="mdi mdi-delete-empty"></i> Delete <?php echo e($configuration->configuration_type); ?> configuration <?php echo e($loop->iteration); ?></h4>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="panel panel-default">
                                                        <div class="panel-body">
                                                            Are you sure you want to delete <b><?php echo e($config->key); ?></b> configuration of type <?php echo e($configuration->configuration_type); ?>?
                                                        </div>
                                                    </div>
                                                    <div class="form-group hidden">
                                                        <label class="control-label">Config ID</label>
                                                        <input type="number" name="config_id" value="<?php echo e($config->id); ?>" class="form-control" placeholder="Config ID">
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="submit" class="btn btn-outline-primary"> <i class="mdi mdi-content-save"></i> Yes</button>
                                                    <button type="button" class="btn btn-outline-danger" data-dismiss="modal">Cancel</button> 
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
                <div class="modal fade" id="add-configuration-<?php echo e($configuration->id); ?>" role="dialog">
                    <div class="modal-dialog">
                        <form action="<?php echo e(route('add-configuration',['id'=>$configuration->id])); ?>" method="post" class="modal-content" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?> 
                            <div class="modal-header">
                                <h4 class="modal-title"><i class="mdi mdi-plus"></i> Add <?php echo e($configuration->configuration_type); ?> Configurations.</h4>
                            </div>
                            <div class="modal-body">
                                <?php if($configuration->configuration_type == 'Personnel to Recieve Feedback and Complaint Notification'): ?>
                                <div class="form-group">
                                    <label class="control-label">Choose Personnel</label>
                                    <select name="name" id="" class="form-control">
                                        <?php $users = getAllUsers()?>
                                        <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        
                                        <option value="<?php echo e($user->id); ?>"><?php echo e($user->name); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                                <?php else: ?>
                                <div class="form-group">
                                    <label class="control-label">Configuration Key</label>
                                    <input type="text" name="key" value="" placeholder="Configuration Key..." class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label class="control-label">Configuration Value</label>
                                    <input type="text" name="value" value=""  placeholder="Configuration Value" class="form-control" required>
                                </div>
                                <?php endif; ?>
                                <div class="form-group hidden">
                                    <label class="control-label">Config ID</label>
                                    <input type="number" name="config_id" value="<?php echo e($configuration->id); ?>" class="form-control">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-outline-primary"> <i class="mdi mdi-content-save"></i> Save</button>
                                <button type="button" class="btn btn-outline-danger" data-dismiss="modal">Close</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>  
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>  
    </main>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.configuration.layout.app', ['dataTable'=>true, 'select2'=>true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/dan/Documents/projects/nuve/imara-lims-v2/resources/views/layouts/configuration/system/configurations.blade.php ENDPATH**/ ?>