<?php $__env->startSection('module-name'); ?>
<li class="nav-item">
    <a class="nav-link module-name" href=""><i class="mdi mdi-finance"></i> PRP Management</a>
</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('title'); ?>
<style type="text/css">
    .tab-card {
        border: 1px solid #eee;
    }

    .tab-card-header {
        background: none;
    }

    /* Default mode */
    .tab-card-header>.nav-tabs {
        border: none;
        margin: 0px;
    }

    .tab-card-header>.nav-tabs>li {
        margin-right: 2px;
    }

    .tab-card-header>.nav-tabs>li>a {
        border: 0;
        border-bottom: 2px solid transparent;
        margin-right: 0;
        color: #737373;
        padding: 2px 15px;
    }

    .tab-card-header>.nav-tabs>li>a.show {
        border-bottom: 2px solid #007bff;
        color: #007bff;
    }

    .tab-card-header>.nav-tabs>li>a:hover {
        color: #007bff;
    }

    .tab-card .nav-link.active {
        background-color: #dadccd !important;
        border: 1px solid #cccebf !important;
    }

    .tab-card-header>.tab-content {
        padding-bottom: 0;
    }

    .mdi {
        font-size: 15px;
    }

    .list-item {
        padding-left: 20% !important;
    }
</style>
<?php echo $__env->yieldContent('title2'); ?>
<?php $__env->stopSection(); ?>


<?php $__env->startSection('content'); ?>
<div class="row" id="body-row">
    <!-- Sidebar -->
    <div id="sidebar-container" class="sidebar-expanded d-none d-md-block col-sm-4 col-md-3 col-lg-2">
        <!-- d-* hiddens the Sidebar in smaller devices. Its itens can be kept on the Navbar 'Menu' -->
        <!-- Bootstrap List Group -->
        <ul class="list-group sticky-top sticky-offset">
            <div class="list-group-item p-4 text-center text-white text-ultra-bold sidebar-module-div">
                <i class="mdi mdi-finance fa-3x"></i><br>
                <span class="text-lg text-bold">PRP MANAGEMENT</span>
            </div>
            <!-- Menu with submenu -->


            <a href="/prp" class="bg-dark list-group-item list-group-item-action">
                <div class="d-flex w-100 justify-content-start align-items-center">
                    <span class="mdi mdi-view-dashboard-outline fa-fw mr-3"></span>
                    <span class="menu-collapsed">Dashboard</span>
                </div>
            </a>
            <a href="#data-menu" data-toggle="collapse" aria-expanded="false" class="bg-dark list-group-item list-group-item-action flex-column align-items-start">
                <div class="d-flex w-100 justify-content-start align-items-center">
                    <span class=" mdi mdi-cog-transfer-outline mr-3"></span>
                    <span class="menu-collapsed">Data Processing</span>
                    <span class="submenu-icon ml-auto"></span>
                </div>
            </a>
            <div id="data-menu" class="collapse sidebar-submenu">
                <a href="#data-sub-menu" data-toggle="collapse" aria-expanded="false" class="bg-dark list-group-item list-group-item-action flex-column align-items-start">
                    <div class="d-flex w-100 justify-content-start align-items-center">
                        <span class=" mdi mdi-database-import mr-3"></span>
                        <span class="menu-collapsed">Data </span>
                        <span class="submenu-icon ml-auto"></span>
                    </div>
                </a>
                <div id="data-sub-menu" class="collapse sidebar-submenu">
                    <a href="<?php echo e(route('day-batch-index')); ?>" class="list-group-item list-group-item-action bg-dark text-white  list-item">
                        <span class="menu-collapsed">
                            <i class="mdi mdi-circle-medium"></i> Prd Files
                            <small class="float-right badge badge-pill"></small>
                        </span>
                    </a>
                    <a href="<?php echo e(route('showPrdRawDataHarvesters')); ?>" class="list-group-item list-group-item-action bg-dark text-white  list-item">
                        <span class="menu-collapsed">
                            <i class="mdi mdi-circle-medium"></i> Harvester
                            <small class="float-right badge badge-pill"></small>
                        </span>
                    </a>
                </div>
                <a href="#transaction-sub-menu" data-toggle="collapse" aria-expanded="false" class="bg-dark list-group-item list-group-item-action flex-column align-items-start">
                    <div class="d-flex w-100 justify-content-start align-items-center">
                        <span class=" mdi mdi-cog-transfer-outline mr-3"></span>
                        <span class="menu-collapsed">Transactions </span>
                        <span class="submenu-icon ml-auto"></span>
                    </div>
                </a>
                <div id="transaction-sub-menu" class="collapse sidebar-submenu">
                    <a href="<?php echo e(route('timesheet-batch-index')); ?>" class="list-group-item list-group-item-action bg-dark text-white list-item ">
                        <span class="menu-collapsed">
                            <i class="mdi mdi-circle-medium"></i> Timesheets
                            <small class="float-right badge badge-pill"></small>
                        </span>
                    </a>
                    <a href="<?php echo e(route('hw-quality-remarks-index')); ?>" class="list-group-item list-group-item-action bg-dark text-white list-item ">
                        <span class="menu-collapsed">
                            <i class="mdi mdi-circle-medium"></i> Quality Remarks
                            <small class="float-right badge badge-pill"></small>
                        </span>
                    </a>
                    <a href="<?php echo e(route('attendance-index')); ?>" class="list-group-item list-group-item-action bg-dark text-white list-item ">
                        <span class="menu-collapsed">
                            <i class="mdi mdi-circle-medium"></i> Attendance Report
                            <small class="float-right badge badge-pill"></small>
                        </span>
                    </a>
                    <a href="<?php echo e(route('packhouse-index')); ?>" class="list-group-item list-group-item-action bg-dark text-white list-item ">
                        <span class="menu-collapsed">
                            <i class="mdi mdi-circle-medium"></i> Pack House
                            <small class="float-right badge badge-pill"></small>
                        </span>
                    </a>
                </div>

                <a href="#payment-sub-menu" data-toggle="collapse" aria-expanded="false" class="bg-dark list-group-item list-group-item-action flex-column align-items-start">
                    <div class="d-flex w-100 justify-content-start align-items-center">
                        <span class=" mdi mdi-account-cash mr-3"></span>
                        <span class="menu-collapsed">Payment Reports </span>
                        <span class="submenu-icon ml-auto"></span>
                    </div>
                </a>
                <div id="payment-sub-menu" class="collapse sidebar-submenu">
                    <a href="<?php echo e(route('payslip-index')); ?>" class="list-group-item list-group-item-action list-item bg-dark text-white ">
                        <span class="menu-collapsed">
                            <i class="mdi mdi-circle-medium"></i> Payslips Summary
                            <small class="float-right badge badge-pill"></small>
                        </span>
                    </a>
                    <!-- <a href="/prp/generate/payslips/<?php echo e($harvest_week->id ?? 0); ?>" class="list-group-item list-group-item-action bg-dark list-item text-white ">
                        <span class="menu-collapsed">
                            <i class="mdi mdi-circle-medium"></i> Payslips Summary
                            <small class="float-right badge badge-pill"></small>
                        </span>
                    </a> -->
                </div>

                <a href="#processing-errors-sub-menu" data-toggle="collapse" aria-expanded="false" class="bg-dark list-group-item list-group-item-action flex-column align-items-start">
                    <div class="d-flex w-100 justify-content-start align-items-center">
                        <span class=" mdi mdi-cog-sync mr-3"></span>
                        <span class="menu-collapsed">Exceptions </span>
                        <span class="submenu-icon ml-auto"></span>
                    </div>
                </a>
                <div id="processing-errors-sub-menu" class="collapse sidebar-submenu">
                    <a href="<?php echo e(route('MissingHarvesterRecordController')); ?>" class="list-group-item list-group-item-action list-item bg-dark text-white ">
                        <span class="menu-collapsed">
                            <i class="mdi mdi-circle-medium"></i> Missing Harvesters
                            <small class="float-right badge badge-pill"></small>
                        </span>
                    </a>
                    <a href="<?php echo e(route('MissingPrdRecordController')); ?>" class="list-group-item list-group-item-action bg-dark list-item text-white ">
                        <span class="menu-collapsed">
                            <i class="mdi mdi-circle-medium"></i> Missing Prd Data
                            <small class="float-right badge badge-pill"></small>
                        </span>
                    </a>
                    <a href="<?php echo e(route('MissingVarietyRecordController')); ?>" class="list-group-item list-group-item-action bg-dark list-item text-white ">
                        <span class="menu-collapsed">
                            <i class="mdi mdi-circle-medium"></i> Missing Variety
                            <small class="float-right badge badge-pill"></small>
                        </span>
                    </a>
                    <a href="<?php echo e(route('MissingTimesheetRecordController')); ?>" class="list-group-item list-group-item-action bg-dark list-item text-white ">
                        <span class="menu-collapsed">
                            <i class="mdi mdi-circle-medium"></i> Missing Timesheet
                            <small class="float-right badge badge-pill"></small>
                        </span>
                    </a>
                </div>
            </div>
            <!-- ---------------------------------master roll -------------------- -->
            <a href="#master-roll" data-toggle="collapse" aria-expanded="false" class="bg-dark list-group-item list-group-item-action flex-column align-items-start">
                <div class="d-flex w-100 justify-content-start align-items-center">
                    <span class="mdi mdi-account-check mr-3"></span>
                    <span class="menu-collapsed">Master Roll</span>
                    <span class="submenu-icon ml-auto"></span>
                </div>
            </a>
            <div id="master-roll" class="collapse sidebar-submenu">
                <a href="<?php echo e(route('headcountindex')); ?>" class="list-group-item list-group-item-action bg-dark text-white">
                    <span class="menu-collapsed">
                        <i class="mdi mdi-circle-medium"></i> Head Count Data
                        <small class="float-right badge badge-pill"></small>
                    </span>
                </a>
                <!-- <a href="" class="list-group-item list-group-item-action bg-dark text-white">
                    <span class="menu-collapsed">
                        <i class="mdi mdi-circle-medium"></i> Attendance Report
                        <small class="float-right badge badge-pill"></small>
                    </span>
                </a> -->
               
            </div>
            <!-- ---------------------------------master roll -------------------- -->

            <!-- --------------------reports-------------------------------- -->
            <a href="#all-reports-menu" data-toggle="collapse" aria-expanded="false" class="bg-dark list-group-item list-group-item-action flex-column align-items-start">
                <div class="d-flex w-100 justify-content-start align-items-center">
                    <span class="mdi mdi-finance mr-3"></span>
                    <span class="menu-collapsed">Reports</span>
                    <span class="submenu-icon ml-auto"></span>
                </div>
            </a>
            <div id="all-reports-menu" class="collapse sidebar-submenu">
                <a href="<?php echo e(route('quality-performance-report-index')); ?>" class="list-group-item list-group-item-action bg-dark text-white">
                    <span class="menu-collapsed">
                        <i class="mdi mdi-circle-medium"></i> Quality Report
                        <small class="float-right badge badge-pill"></small>
                    </span>
                </a>
                <a href="<?php echo e(route('performance-index')); ?>" class="list-group-item list-group-item-action bg-dark text-white">
                    <span class="menu-collapsed">
                        <i class="mdi mdi-circle-medium"></i> Performance Report
                        <small class="float-right badge badge-pill"></small>
                    </span>
                </a>
                
            </div>
            <!-- ------------------------------reports--------------------  -->
            <a href="<?php echo e(route('species-index')); ?>" class="bg-dark list-group-item list-group-item-action">
                <div class="d-flex w-100 justify-content-start align-items-center">
                    <span class="mdi mdi-test-tube fa-fw mr-3"></span>
                    <span class="menu-collapsed">Species</span>
                </div>
            </a>


            <a href="<?php echo e(route('group-index')); ?>" class="bg-dark list-group-item list-group-item-action">
                <div class="d-flex w-100 justify-content-start align-items-center">
                    <span class="mdi mdi-account-details fa-fw mr-3"></span>
                    <span class="menu-collapsed">Harvesters</span>
                </div>
            </a>

            <a href="<?php echo e(route('quality-index')); ?>" class="bg-dark list-group-item list-group-item-action">
                <div class="d-flex w-100 justify-content-start align-items-center">
                    <span class="mdi mdi-check-box-multiple-outline fa-fw mr-3"></span>
                    <span class="menu-collapsed">Quality Remarks</span>
                </div>
            </a>
            <!-- --------------------------personel------------------  -->
            <a href="#personnel-menu" data-toggle="collapse" aria-expanded="false" class="bg-dark list-group-item list-group-item-action flex-column align-items-start">
                <div class="d-flex w-100 justify-content-start align-items-center">
                    <span class="mdi mdi-account-multiple-outline mr-3"></span>
                    <span class="menu-collapsed">Personnel</span>
                    <span class="submenu-icon ml-auto"></span>
                </div>
            </a>
            <div id="personnel-menu" class="collapse sidebar-submenu">
                <a href="/personnel-home" class="list-group-item list-group-item-action bg-dark text-white">
                    <span class="menu-collapsed">
                        <i class="mdi mdi-circle-medium"></i> Personnel
                        <small class="float-right badge badge-pill"></small>
                    </span>
                </a>
                <a href="/organizational-roles" class="list-group-item list-group-item-action bg-dark text-white">
                    <span class="menu-collapsed">
                        <i class="mdi mdi-circle-medium"></i> Roles & Permissions
                        <small class="float-right badge badge-pill"></small>
                    </span>
                </a>
                <a href="/organizational-departments" class="bg-dark list-group-item list-group-item-action text-white">
                    <span class="menu-collapsed">
                        <i class="mdi mdi-circle-medium"></i> Departments
                        <small class="float-right badge badge-pill"></small>
                    </span>
                </a>
            </div>

            <!-- --------------------------personel------------------  -->
            <a href="#report-menu" data-toggle="collapse" aria-expanded="false" class="bg-dark list-group-item list-group-item-action flex-column align-items-start">
                <div class="d-flex w-100 justify-content-start align-items-center">
                    <span class=" mdi mdi-cog mr-3"></span>
                    <span class="menu-collapsed">Configurations</span>
                    <span class="submenu-icon ml-auto"></span>
                </div>
            </a>
            <div id="report-menu" class="collapse sidebar-submenu">
                <a href="<?php echo e(route('showSystemConfiguration')); ?>" class="list-group-item list-group-item-action bg-dark text-white">
                    <span class="menu-collapsed">
                        <i class="mdi mdi-circle-medium"></i> System Parameters
                        <small class="float-right badge badge-pill"></small>
                    </span>
                </a>
                <a href="<?php echo e(route('activities-index')); ?>" class="list-group-item list-group-item-action bg-dark text-white">
                    <span class="menu-collapsed">
                        <i class="mdi mdi-circle-medium"></i> Activities
                        <small class="float-right badge badge-pill"></small>
                    </span>
                </a>

                <?php
					$menuTotals = array("Job Description", "Designation");
				?>
				<?php $__currentLoopData = $menuTotals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
					<a href="<?php echo e(route('module-pre-configs', ['config'=>$item, 'module'=>'Personnel-Management'])); ?>" class="list-group-item list-group-item-action bg-dark text-white">
						<span class="menu-collapsed"><i class="mdi mdi-circle-medium"></i><?php echo e($item); ?></span>
					</a>
				<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                <a href="<?php echo e(route('other-configuration')); ?>" class="list-group-item list-group-item-action bg-dark text-white">
                    <span class="menu-collapsed">
                        <i class="mdi mdi-circle-medium"></i> Other Configurations
                        <small class="float-right badge badge-pill"></small>
                    </span>
                </a>
            </div>

            <div class="list-group-item copyright-lims p-4 text-center text-white">
                Copyright <?php echo e(date('Y')); ?> <span class="text-red">Imara LIMS</span>
            </div>
            <!-- Submenu content -->
        </ul>

        <!-- List Group END-->
    </div>
    <!-- sidebar-container END -->

    <!-- MAIN -->
    <div class="col-sm-8 col-md-9 col-lg-10 py-3" id="main-container-body" style="background-color: white !important;">
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
        <?php echo $__env->yieldContent('content2'); ?>
    </div>
    <!-- Main Col END -->
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
<?php echo $__env->yieldContent('script2'); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('prp::layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/dan/Documents/projects/nuve/imara-lims-v2/Modules/Prp/Resources/views/layouts/app.blade.php ENDPATH**/ ?>