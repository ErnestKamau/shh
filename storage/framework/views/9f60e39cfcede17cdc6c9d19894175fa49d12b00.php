<?php $__env->startSection('module-name'); ?>
    <li class="nav-item">
        <a class="nav-link module-name" style="font-size:12px !important" href="<?php echo e(route('home')); ?>"><i class="mdi mdi-file-document"></i> Customerr Focus</a>
    </li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('title'); ?>
    <style type="text/css">

    </style>
<?php $__env->stopSection(); ?>


<?php $__env->startSection('content'); ?>

    <div id="body-row" class="p-3" style="width:100%;height:100vh">
        <!-- Sidebar -->

        <!-- sidebar-container END -->

        <!-- MAIN -->
        <div class="" id="main-container-body">
            <div id="message-section" style="padding: 10px 10px 0px 10px !important">
                <?php if($errors->any()): ?>
                    <div class="alert alert-danger">
                        <ul>
                            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li><i class="fas fa-exclamation-triangle"></i> <?php echo e($error); ?></li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                <?php endif; ?>
                <?php if(\Session::has('success') || \Session::has('error')): ?>
                    <?php if(\Session::has('success')): ?>
                        <div class="alert alert-success center text-lg alert-callout">
                            <i class="fas fa-thumbs-up"></i> <?php echo e(Session::get('success')); ?>

                        </div>
                    <?php endif; ?>
                    <?php if(\Session::has('error')): ?>
                        <div class="alert alert-danger center text-lg alert-callout">
                            <i class="fas fa-exclamation-triangle"></i> <?php echo e(Session::get('error')); ?>

                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            <div class="">
                <?php
                $items = [
                    [
                        'link' => '/',
                        'name' => 'Home',
                        'icon' => null,
                    ],
                    [
                        'link' => 'null',
                        'name' => 'Customer Focus Signing',
                        'icon' => null,
                    ],
                ];
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
               
                
                <center>
                    <div class="card mt-5" style="width: 50%">
                        <div class="card-header" style="font-size:20px">
                            <i class="mdi mdi-file-document"></i> Customer Focus Signing
                        </div>
                        <div class="card-body">
                            <form autocomplete="off" action="<?php echo e(route('generateTabletCustomerFocusIndex')); ?>" method="get"
                                class="p-3" enctype="multipart/form-data">
                                <?php echo csrf_field(); ?>


                                <div class="alert alert-primary p-2 d-flex">
                                    <i class="mdi mdi-alert-decagram-outline" style="font-size:30px"></i>
                                    <span class="p-2">
                                        Kindly provide the sample / job number below to get the Customer Focus of the
                                        specified sample
                                    </span>
                                </div>

                                <div class="form-group">
                                    <label for="" class="control-label">Sample Number</label>
                                    <input type="text" name="sample_no" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label for="" class="control-label"><input type="checkbox" name="is_clustered" value="1" id=""> Is Clustered?</label>
                                </div>
                                <div class="submit-area mt-3">
                                    <button type="submit" style="box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;"
                                        class="btn btn-block btn-default"><i
                                            class="mdi mdi-cloud-search-outline"></i> Search</button>
                                </div>


                            </form>


                        </div>
                    </div>
                </center>
            </div>
        </div>
        <!-- Main Col END -->
    </div>


<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
    <script></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', ['select2' => true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/dan/Documents/projects/nuve/imara-lims-v2/resources/views/layouts/lab/sample-workflow/sign-customer-focus-index.blade.php ENDPATH**/ ?>