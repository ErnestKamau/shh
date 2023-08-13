<?php $__env->startSection('title2'); ?>
    <title>Asset-Type</title>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content2'); ?>
    <main>
        <?php 
            $items = array(
                array(
                    'link'=>route('asset-type-home'),
                    'name'=>'Asset Type',
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
            <i class="mdi mdi-layers-triple"></i>Asset Type
            <button class="btn btn-outline-primary btn-sm float-right" data-toggle="modal" data-target="#add-asset-type"><i class="mdi mdi-plus"></i> Add</button>
        </h2>
        <br>
        <div class="card tab-card">
            <div class="card-header tab-card-header">
                <ul class="nav nav-tabs card-header-tabs" id="asset-tabs" role="tablist">
                    <li class="nav-item">
                        <a href="#asset-type-tab" class="nav-link active" id="all-asset-type-tab" data-toggle="tab" role="tab" aria-controls="asset-type-tab" aria-selected="true"> <i class="mdi mdi-layers-triple-outline" style="color: black; font-size:15px"></i> Asset Types</a>
                    </li>
                    <li class="nav-item">
                        <a href="#active-asset-type" class="nav-link " id="active-asset-tab" data-toggle="tab" role="tab" aria-controls="active-asset-type" aria-selected="true"> <i class="mdi mdi-layers" style="color: black;font-size:15px"></i> Active Asset Types</a>
                    </li>
                    <li class="nav-item">
                        <a href="#inactive-asset-type" class="nav-link " id="inactive-asset-tab" data-toggle="tab" role="tab" aria-controls="inactive-asset-type" aria-selected="true"> <i class="mdi mdi-layers-off" style="color: black;font-size:15px"></i> Inactive Asset Types</a>
                    </li>
                </ul>
            </div>
            <div class="tab-content" id="asset-type-tabs-content">
                <!-- all asset types  -->
                <div class="tab-pane fade show active p-3" id="asset-type-tab" role="tabpanel" aria-labelledby="one-tab">
                    <h5 class="card-title">All Asset Types</h5>
                    <div class="table-responsive">
                        <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
                            <thead class="bg-light p-2">
                                <tr>
                                    <th>No</th>
                                    <th>Asset Code</th>
                                    <th>Asset Description</th>
                                    <th>Active</th>
                                    <th></th>
                                    
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td valign="center"><?php echo e($loop->iteration); ?></td>
                                        <td><?php echo e($type->asset_code); ?></td>
                                        <td><?php echo e($type->descripton); ?></td>
                                        <td class="text-small"><?php echo $type->is_active == '1' ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>'; ?></td>
                                        <td>
                                            <span class="btn btn-primary btn-sm" data-toggle="modal" data-target="#edit-asset-type-<?php echo e($type->id); ?>"> <i class="mdi mdi-pencil"></i></span>
                                            <div id="edit-asset-type-<?php echo e($type->id); ?>" class="modal fade" role="dialog">
                                                <div class="modal-dialog">
                                                    <!-- modal content  -->
                                                    <form class="modal-content" action="<?php echo e(route('edit-asset-type', ['id'=>$type->id])); ?>" method="POST" enctype="multipart/form-data">
                                                        <?php echo csrf_field(); ?>
                                                        <div class="modal-header">
                                                            <h4 class="modal-title"><i class="mdi mdi-pencil"></i> Edit <?php echo e($type->descripton); ?> Asset Type</h4>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="form-group">
                                                                <label class="control-label">Asset Code</label>
                                                                <input type="text" name="code" class="form-control" value="<?php echo e($type->asset_code); ?>" placeholder="Asset Code..." required/>
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="control-label">Asset Description</label>
                                                                <input type="text" name="description" class="form-control" value="<?php echo e($type->descripton); ?>" placeholder="Asset Description..." required/>
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="control-label">Active</label>
                                                                <select class="form-control" name="active" placeholder="Active..." readonly="true">
                                                                    <option value="True" <?php echo e($type->is_active == 1 ? 'selected' : ''); ?>>Active</option>
                                                                    <option value="False" <?php echo e($type->is_active == 0 ? 'selected' : ''); ?>>Not Active</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
                                                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
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
                <!-- end all assets types  -->

                <!-- active assets types  -->
                <div class="tab-pane fade p-3" id="active-asset-type" role="tabpanel" aria-labelledby="one-tab">
                    <h5 class="card-title">Active Asset Type</h5>
                    <div class="table-responsive">
                        <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Asset Code</th>
                                    <th>Asset Description</th>
                                    <th>Active</th>
                                    <th></th>
                                    
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                   <?php if($type->is_active == 1): ?> 
                                   <td valign="center"><?php echo e($loop->iteration); ?></td>
                                   <td><?php echo e($type->asset_code); ?></td>
                                   <td><?php echo e($type->descripton); ?></td>
                                   <td class="text-small"><?php echo $type->is_active == '1' ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>'; ?></td>
                                   <td>
                                        <span class="btn btn-primary btn-sm" data-toggle="modal" data-target="#edit-asset-type-<?php echo e($type->id); ?>"> <i class="mdi mdi-pencil"></i></span>
                                        <div id="edit-asset-type-<?php echo e($type->id); ?>" class="modal fade" role="dialog">
                                            <div class="modal-dialog">
                                                <!-- modal content  -->
                                                <form class="modal-content" action="<?php echo e(route('edit-asset-type', ['id'=>$type->id])); ?>" method="POST" enctype="multipart/form-data">
                                                    <?php echo csrf_field(); ?>
                                                    <div class="modal-header">
                                                        <h4 class="modal-title"><i class="mdi mdi-pencil"></i> Edit <?php echo e($type->descripton); ?> Asset Type</h4>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="form-group">
                                                            <label class="control-label">Asset Code</label>
                                                            <input type="text" name="code" class="form-control" value="<?php echo e($type->asset_code); ?>" placeholder="Asset Code..." required/>
                                                        </div>
                                                        <div class="form-group">
                                                            <label class="control-label">Asset Description</label>
                                                            <input type="text" name="description" class="form-control" value="<?php echo e($type->descripton); ?>" placeholder="Asset Description..." required/>
                                                        </div>
                                                        <div class="form-group">
                                                            <label class="control-label">Active</label>
                                                            <select class="form-control" name="active" placeholder="Active..." readonly="true">
                                                                <option value="True" <?php echo e($type->is_active == 0 ? 'selected' : ''); ?>>Active</option>
                                                                <option value="False" <?php echo e($type->is_active == 1 ? 'selected' : ''); ?>>Not Active</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
                                                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                   </td>
                                   <?php endif; ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- end active assets  -->

                <!-- in active assets  -->
                <div class="tab-pane fade p-3" id="inactive-asset-type" role="tabpanel" aria-labelledby="one-tab">
                    <h5 class="card-title">Inactive Asset Type</h5>
                    <div class="table-responsive">
                        <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Asset Code</th>
                                    <th>Asset Description</th>
                                    <th>Active</th>
                                    <th></th>
                                    
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                   <?php if($type->is_active == 0): ?> 
                                   <td valign="center"><?php echo e($loop->iteration); ?></td>
                                   <td><?php echo e($type->asset_code); ?></td>
                                   <td><?php echo e($type->descripton); ?></td>
                                   <td class="text-small"><?php echo $type->is_active == '1' ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>'; ?></td>
                                   <td>
                                        <span class="btn btn-info btn-sm" data-toggle="modal" data-target="#edit-asset-type-<?php echo e($type->id); ?>"> <i class="mdi mdi-pencil"></i></span>
                                        <div id="edit-asset-type-<?php echo e($type->id); ?>" class="modal fade" role="dialog">
                                            <div class="modal-dialog">
                                                <!-- modal content  -->
                                                <form class="modal-content" action="<?php echo e(route('edit-asset-type', ['id'=>$type->id])); ?>" method="POST" enctype="multipart/form-data">
                                                    <?php echo csrf_field(); ?>
                                                    <div class="modal-header">
                                                        <h4 class="modal-title"><i class="mdi mdi-pencil"></i> Edit <?php echo e($type->descripton); ?> Asset Type</h4>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="form-group">
                                                            <label class="control-label">Asset Code</label>
                                                            <input type="text" name="code" class="form-control" value="<?php echo e($type->asset_code); ?>" placeholder="Asset Code..." required/>
                                                        </div>
                                                        <div class="form-group">
                                                            <label class="control-label">Asset Description</label>
                                                            <input type="text" name="description" class="form-control" value="<?php echo e($type->descripton); ?>" placeholder="Asset Description..." required/>
                                                        </div>
                                                        <div class="form-group">
                                                            <label class="control-label"><input name="active" value="1" type="checkbox" <?php echo e($type->is_active == 0 ? 'checked' : ''); ?> /> Is Active</label>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
                                                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                   </td>
                                   <?php endif; ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- end inactive assets  -->
            </div>
        </div>
    </main>
<?php $__env->stopSection(); ?> 
<?php $__env->startSection('script2'); ?>
    <div id="add-asset-type" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <form action="<?php echo e(route('add-asset-type')); ?>" method="POST" class="modal-content" enctype="multipart/form-data">
                <?php echo csrf_field(); ?> 
                <div class="modal-header">
                    <h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Asset Type</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="control-label">Asset Code</label>
                        <input type="text" name="code" class="form-control" value="" placeholder="Asset Code..." required/>
                    </div>
                    <div class="form-group">
                        <label class="control-label">Asset Description</label>
                        <input type="text" name="description" class="form-control" value="" placeholder="Asset Description..." required/>
                    </div>
                    <div class="form-group">
                        <label class="control-label">Active</label>
                        <select class="form-control" name="active" placeholder="Active..." readonly="true">
                            <option value="True" >Active</option>
                            <option value="False" >Not Active</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary"><i class="mdi mdi-content-save"></i> Save</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
    <script>

    </script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.equipment.asset.layout.app', ['dataTable'=>true, 'select2'=>true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/dan/Documents/projects/nuve/imara-lims-v2/resources/views/layouts/equipment/asset/index.blade.php ENDPATH**/ ?>