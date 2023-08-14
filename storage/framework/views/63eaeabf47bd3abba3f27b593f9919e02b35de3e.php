<?php $__env->startSection('title2'); ?>
    <title>Asset-Location</title>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content2'); ?>
    <main>
        <?php 
            $items = array(
                array(
                    'link'=>route('asset-location-home'),
                    'name'=>'Asset Location',
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
            <i class="mdi mdi-map-marker"></i>Asset Location
            <button class="btn btn-primary btn-sm float-right" data-toggle="modal" data-target="#add-asset-location"><i class="mdi mdi-plus"></i> Add</button>
        </h2>
        <br>
        <div class="card tab-card">
            <div class="card-header tab-card-header">
                <ul class="nav nav-tabs card-header-tabs" id="asset-tabs" role="tablist">
                    <li class="nav-item">
                        <a href="#asset-location-tab" class="nav-link active" id="all-asset-location-tab" data-toggle="tab" role="tab" aria-controls="asset-location-tab" aria-selected="true"> <i class="mdi mdi-map-marker" style="color: black;font-size:15px"></i> Asset Locations</a>
                    </li>
                    <li class="nav-item">
                        <a href="#active-asset-location" class="nav-link " id="active-loacation-tab" data-toggle="tab" role="tab" aria-controls="active-asset-location" aria-selected="true"> <i class="mdi mdi-map-marker-check" style="color: black; font-size:15px"></i> Active Asset Locations</a>
                    </li>
                    <li class="nav-item">
                        <a href="#inactive-asset-location" class="nav-link " id="inactive-location-tab" data-toggle="tab" role="tab" aria-controls="inactive-asset-location" aria-selected="true"> <i class="mdi mdi-map-marker-off" style="color: black;font-size:15px"></i> Inactive Asset Locations</a>
                    </li>
                </ul>
            </div>
            <div class="tab-content" id="asset-location-tabs-content">
                <!-- all asset types  -->
                <div class="tab-pane fade show active p-3" id="asset-location-tab" role="tabpanel" aria-labelledby="one-tab">
                    <h5 class="card-title">All Asset Locations</h5>
                    <div class="table-responsive">
                        <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
                            <thead class="bg-light p-2">
                                <tr>
                                    <th>No</th>
                                    <th>Location Code</th>
                                    <th>Location Description</th>
                                    <th>Active</th>
                                    <th></th>
                                    
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $locations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $location): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                    <td valign="center"><?php echo e($loop->iteration); ?></td>
                                        <td><?php echo e($location->location_code); ?></td>
                                        <td><?php echo e($location->name); ?></td>
                                        <td class="text-small"><?php echo $location->is_active == '1' ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>'; ?></td>
                                        <td>
                                            <span class="btn btn-outline-primary btn-sm" data-toggle="modal" data-target="#edit-asset-location-<?php echo e($location->id); ?>"> <i class="mdi mdi-pencil"></i></span>
                                            <div id="edit-asset-location-<?php echo e($location->id); ?>" class="modal fade" role="dialog">
                                                <div class="modal-dialog">
                                                    <!-- modal content  -->
                                                    <form class="modal-content" action="<?php echo e(route('edit-asset-location', ['id'=>$location->id])); ?>" method="POST" enctype="multipart/form-data">
                                                        <?php echo csrf_field(); ?>
                                                        <div class="modal-header">
                                                            <h4 class="modal-title"><i class="mdi mdi-pencil"></i> Edit <?php echo e($location->descripton); ?> Asset Location</h4>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="form-group">
                                                                <label class="control-label">Location Code</label>
                                                                <input type="text" name="code" class="form-control" value="<?php echo e($location->location_code); ?>" placeholder="Location Code..." required/>
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="control-label">Location Description</label>
                                                                <input type="text" name="description" class="form-control" value="<?php echo e($location->name); ?>" placeholder="Location Name..." required/>
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="control-label">Active</label>
                                                                <select class="form-control" name="active" placeholder="Active..." readonly="true">
                                                                    <option value="True" <?php echo e($location->is_active == 1 ? 'selected' : ''); ?>>Active</option>
                                                                    <option value="False" <?php echo e($location->is_active == 0 ? 'selected' : ''); ?>>Not Active</option>
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
                <div class="tab-pane fade p-3" id="active-asset-location" role="tabpanel" aria-labelledby="one-tab">
                    <h5 class="card-title">Active Asset Location</h5>
                    <div class="table-responsive">
                        <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Location Code</th>
                                    <th>Location Name</th>
                                    <th>Active</th>
                                    <th></th>
                                    
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $locations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $location): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                   <?php if($location->is_active == 1): ?> 
                                   <tr>
                                        <td valign="center"><?php echo e($loop->iteration); ?></td>
                                       <td><?php echo e($location->location_code); ?></td>
                                       <td><?php echo e($location->name); ?></td>
                                       <td class="text-small"><?php echo $location->is_active == '1' ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>'; ?></td>
                                       <td>
                                            <span class="btn btn-pprimary btn-sm" data-toggle="modal" data-target="#edit-asset-location-<?php echo e($location->id); ?>"> <i class="mdi mdi-pencil"></i></span>
                                            <div id="edit-asset-location-<?php echo e($location->id); ?>" class="modal fade" role="dialog">
                                                <div class="modal-dialog">
                                                    <!-- modal content  -->
                                                    <form class="modal-content" action="<?php echo e(route('edit-asset-location', ['id'=>$location->id])); ?>" method="POST" enctype="multipart/form-data">
                                                        <?php echo csrf_field(); ?>
                                                        <div class="modal-header">
                                                            <h4 class="modal-title"><i class="mdi mdi-pencil"></i> Edit <?php echo e($location->name); ?> Asset Location</h4>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="form-group">
                                                                <label class="control-label">Location Code</label>
                                                                <input type="text" name="code" class="form-control" value="<?php echo e($location->location_code); ?>" placeholder="Location Code..." required/>
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="control-label">Location Name</label>
                                                                <input type="text" name="description" class="form-control" value="<?php echo e($location->name); ?>" placeholder="Location Name..." required/>
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="control-label">Active</label>
                                                                <select class="form-control" name="active" placeholder="Active..." readonly="true">
                                                                    <option value="True" <?php echo e($location->is_active == 1 ? 'selected' : ''); ?>>Active</option>
                                                                    <option value="False" <?php echo e($location->is_active == 0 ? 'selected' : ''); ?>>Not Active</option>
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
                                   <?php endif; ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- end active assets  -->

                <!-- in active assets  -->
                <div class="tab-pane fade p-3" id="inactive-asset-location" role="tabpanel" aria-labelledby="one-tab">
                    <h5 class="card-title">Inactive Asset Type</h5>
                    <div class="table-responsive">
                        <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Location Code</th>
                                    <th>Location Name</th>
                                    <th>Active</th>
                                    <th></th>
                                    
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $locations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $location): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                   <?php if($location->is_active == 0): ?> 
                                   <tr>
                                        2<td valign="center"><?php echo e($loop->iteration); ?></td>
                                       <td><?php echo e($location->location_code); ?></td>
                                       <td><?php echo e($location->name); ?></td>
                                       <td class="text-small"><?php echo $location->is_active == '1' ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>'; ?></td>
                                       <td>
                                            <span class="btn btn-primary btn-sm" data-toggle="modal" data-target="#edit-asset-location-<?php echo e($location->id); ?>"> <i class="mdi mdi-pencil"></i></span>
                                            <div id="edit-asset-location-<?php echo e($location->id); ?>" class="modal fade" role="dialog">
                                                <div class="modal-dialog">
                                                    <!-- modal content  -->
                                                    <form class="modal-content" action="<?php echo e(route('edit-asset-location', ['id'=>$location->id])); ?>" method="POST" enctype="multipart/form-data">
                                                        <?php echo csrf_field(); ?>
                                                        <div class="modal-header">
                                                            <h4 class="modal-title"><i class="mdi mdi-pencil"></i> Edit <?php echo e($location->name); ?> Asset Location</h4>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="form-group">
                                                                <label class="control-label">Location Code</label>
                                                                <input type="text" name="code" class="form-control" value="<?php echo e($location->location_code); ?>" placeholder="Location Code..." required/>
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="control-label">Location Name</label>
                                                                <input type="text" name="description" class="form-control" value="<?php echo e($location->name); ?>" placeholder="location Name..." required/>
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="control-label">Active</label>
                                                                <select class="form-control" name="active" placeholder="Active..." readonly="true">
                                                                    <option value="True" <?php echo e($location->is_active == 1 ? 'selected' : ''); ?>>Active</option>
                                                                    <option value="False" <?php echo e($location->is_active == 0 ? 'selected' : ''); ?>>Not Active</option>
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
    <div id="add-asset-location" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <form action="<?php echo e(route('add-asset-location')); ?>" method="POST" class="modal-content" enctype="multipart/form-data">
                <?php echo csrf_field(); ?> 
                <div class="modal-header">
                    <h4 class="modal-title"><i class="mdi mdi-plus"></i> Add Asset Location</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="control-label">Location Code</label>
                        <input type="text" name="code" class="form-control" value="" placeholder="Location Code..." required/>
                    </div>
                    <div class="form-group">
                        <label class="control-label">Location</label>
                        <input type="text" name="description" class="form-control" value="" placeholder="Location Name..." required/>
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
<?php echo $__env->make('layouts.equipment.asset.layout.app', ['dataTable'=>true, 'select2'=>true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/dan/Documents/projects/nuve/imara-lims-v2/resources/views/layouts/equipment/asset/asset-location.blade.php ENDPATH**/ ?>