

<?php $__env->startSection('module-name'); ?>
<li class="nav-item">
    <a class="nav-link module-name" href="<?php echo e(route('home')); ?>"><i class="mdi mdi-account"></i> Profile</a>
</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('title'); ?>
<style type="text/css">
    .substringed {
        cursor: pointer;
    }

    .substringed .hoverable {
        display: none;
    }

    .substringed:hover .hoverable {
        display: unset !important;
    }

    .substringed:hover .default-seen {
        display: none !important;
    }

    .substringed .default-seen {
        display: unset !important;
    }

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

    .datepicker {
        position: ;
    }

    #eventHistory:hover {
        transform: scale(1.01);
        box-shadow: 0 6px 15px rgba(0, 0, 0, .12), 0 4px 8px rgba(0, 0, 0, .06);
    }
</style>

<?php $__env->stopSection(); ?>


<?php $__env->startSection('content'); ?>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"></script>
<link href="https://pagecdn.io/lib/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker3.min.css" rel="stylesheet" crossorigin="anonymous">
<link href="https://pagecdn.io/lib/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker3.css" rel="stylesheet" crossorigin="anonymous">
<link href="https://pagecdn.io/lib/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.standalone.css" rel="stylesheet" crossorigin="anonymous">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/fullcalendar.css" />

<div class="row" id="body-row">
    <!-- Sidebar -->

    <!-- sidebar-container END -->

    <!-- MAIN -->
    <div class="py-3" id="main-container-body">
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
            $items = array(

                array(
                    'link' => '/',
                    'name' => 'Home',
                    'icon' => null
                ),
                array(
                    'link' => 'null',
                    'name' => Auth::user()->name,
                    'icon' => null
                ),


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
                <?php if(isset($user->photo) && $user->photo != ""): ?>
                <img src="<?php echo e($user->photo); ?>" style="width: 100px" />
                <?php else: ?>
                <i class="mdi mdi-account"></i>
                <?php endif; ?>
                <?php echo e($user->name); ?> | <small class="text-muted">Profile</small>
            </h2>
            <br>
            <div class="card">
                <form autocomplete="off" action="<?php echo e(route('add-personnel', ['id'=>$user->id])); ?>" method="POST" class="p-3" enctype="multipart/form-data">
                    <h5 class="card-title"><i class="mdi mdi-key"></i> User Details
                        <button class="btn btn-outline-primary btn-sm float-right"><i class="mdi mdi-content-save"></i> Save</button>
                    </h5>
                    <input autocomplete="off" name="hidden" type="password" style="display:none;">
                    <?php echo csrf_field(); ?>
                    <div class="table-responsive">
                        <div class="row">
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <label class="control-label">Designation *</label>
                                    <select name="designation" class="form-control" placeholder="Designation..." required>
                                        <option></option>
                                        <?php $__currentLoopData = getModulePreconfig("Designation", "Personnel-Management"); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($item->id); ?>" <?php echo e($user->designation == $item->id ? 'selected' : ''); ?>><?php echo e($item->name); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <label class="control-label">First Name *</label>
                                    <input type="text" class="form-control" name="first_name" value="<?php echo e($user->first_name); ?>" placeholder="First Name..." required />
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <label class="control-label">Middle Name</label>
                                    <input type="text" class="form-control" name="middle_name" value="<?php echo e($user->middle_name); ?>" placeholder="Middle Name..." />
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <label class="control-label">Last Name</label>
                                    <input type="text" class="form-control" name="last_name" value="<?php echo e($user->last_name); ?>" placeholder="Last Name..." />
                                </div>
                            </div>

                        </div>
                        
                        <div class="form-group hidden">
                            <label class="control-label">User License</label>
                            <select name="user_license" class="form-control" placeholder="User License..." required>
                                <option></option>
                                <?php $__currentLoopData = getUserLicenses(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i=>$n): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($i); ?>" <?php echo e($i == $user->license_type ? 'selected' :''); ?> <?php echo e(intval($license_count[$i]) == intval(mamboSawa($i.'s')) ? 'disabled' : ''); ?>><?php echo e($n); ?> <?php echo e($license_count[$i]."/".mamboSawa($i.'s')); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <input type="hidden" name="department" value="<?php echo e($user->department_id); ?>">
                        
                        <input type="hidden" name="position" value="<?php echo e($user->position); ?>">
                        <input type="hidden" name="education_level" value="<?php echo e($user->education_level); ?>">
                        <div class="form-group hidden mt-sm-5 ">
                            <label class="control-label"><input type="checkbox" name="active" value="1" <?php echo e($user->active == "1" ? "checked" : ""); ?> /> Active</label>
                        </div>
                        <br>
                        <div class="row">
                            <div class="col-sm-4">
                                <div class="form-group row no-gutters">
                                    <div class="col-4 text-center">
                                        <img src="<?php echo e($user->photo ?? '/images/user.png'); ?>" style="max-width: 100%; max-height:75px" />
                                    </div>
                                    <div class="col-8">
                                        <label class="control-label">Photo</label>
                                        <input type="file" class="form-control" name="image" />
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label class="control-label">Email *</label>
                                    <input type="email" class="form-control" name="email" value="<?php echo e($user->email); ?>" placeholder="Email..." required />
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label class="control-label">Phone</label>
                                    <input type="text" class="form-control" name="phone" value="<?php echo e($user->phone); ?>" placeholder="Phone..." />
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label class="control-label">ID Number/Passport No *</label>
                                    <input type="text" class="form-control" name="id_number" value="<?php echo e($user->id_number); ?>" placeholder="ID Number..." required />
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label class="control-label">Date of Birth</label>
                                    <input type="date" class="form-control" name="date_of_birth" value="<?php echo e($user->date_of_birth); ?>" placeholder="Date of Birth..." />
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label class="control-label">Employment Date</label>
                                    <input type="date" class="form-control" name="employment_date" value="<?php echo e($user->employment_date); ?>" placeholder="Employment Dat..." />
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label class="control-label">KRA PIN</label>
                                    <input type="text" class="form-control" name="kra_pin" value="<?php echo e($user->kra_pin); ?>" placeholder="KRA PIN..." />
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label class="control-label">NSSF</label>
                                    <input type="text" class="form-control" name="nssf" value="<?php echo e($user->nssf); ?>" placeholder="NSSF..." />
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label class="control-label">NHIF</label>
                                    <input type="text" class="form-control" name="nhif" value="<?php echo e($user->nhif); ?>" placeholder="NHIF..." />
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label for="" class="control-label">Lab Section</label>
                                    <select name="lab_section_id[]" multiple id="" class="form-control">
                                        <?php $__currentLoopData = $stages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($stage->id); ?>" <?php echo e(in_array($stage->id,explode(',',$user->lab_section_id)) ? 'selected' : ''); ?>><?php echo e($stage->name); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label class="control-label">Signature <span>*</span></label>
                                    <input type="file" name="signature" id="" class="form-control">
                                </div>
                            </div>
                            <?php if($user->electronic_sig): ?>
                            <div class="col-sm-4">
                                <img src="<?php echo e($user->electronic_sig); ?>" style="max-width: 100%; max-height:70px">
                            </div>
                            <?php endif; ?>
                        


                    </div>
                    <div class="row">

                        <div class="col-sm-8 mt-sm-5">
                            <div class="form-group hidden">
                                <label class="control-label">
                                    <input type="checkbox" name="has_credentials" value="1" /> Create/Update User Passwords
                                </label>
                            </div>
                        </div>

                    </div>
                    <div class="row hidden" id="passwords-holder">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label>Password</label>
                                <input autocomplete="off" type="password" value="" title="Must contain at least one number and one uppercase and lowercase letter, and at least 8 or more characters" class="form-control pass" name="main_password" placeholder="Password..." />
                                <div class="has-success"></div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label>Confirm Password</label>
                                <input type="password" value="" title="Must contain at least one number and one uppercase and lowercase letter, and at least 8 or more characters" class="form-control pass" name="confirm_password" placeholder="Confirm Password..." />
                                <div class="has-error"></div>
                            </div>
                        </div>
                    </div>
            </div>
            </form>
        </div>
    </div>
</div>
<!-- Main Col END -->
</div>


<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js" integrity="sha256-4iQZ6BVL4qNKlQ27TExEhBN1HFPvAvAMbFavKKosSWQ=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/fullcalendar.js"></script>
<script src="https://pagecdn.io/lib/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js" crossorigin="anonymous"></script>
<script>
    $(function() {
        $('.pass').val('');
        $('[name="has_credentials"]').on('change', function() {
            if ($(this).is(':checked')) {
                $("#passwords-holder").removeClass("hidden");
                $(".pass").attr('required', true);
            } else {
                $('.pass').val('');
                $("#passwords-holder").addClass("hidden");
                $(".pass").removeAttr('required');
            }
        });

        $('[name="confirm_password"]').on('keyup', function() {
            var pass1 = $('[name="main_password"]').val();
            var pass2 = $(this).val();

            if (pass1 != pass2) {
                $(this).siblings('.has-success').html('').addClass('text-success');
                $(this).siblings('.has-error').html(`<i class="mdi mdi-cancel"></i> Passwords did not match.`).addClass('text-danger');
            } else {
                $(this).siblings('.has-error').html('')
                $(this).siblings('.has-success').html('<i class="mdi mdi-check-circle"></i> Passwords Match!.')
            }
        });
    });
</script>
<script>
    $(function() {
        $('.submit-delete-form-btn').on('click', function() {
            var form = $(this);
            if (confirm("Are you sure that you want to remove this role?")) {
                form.submit();
            }
        });
    })
</script>


<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app',['select2'=>true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\wamp64\www\polucon\resources\views/layouts/personnel/users/user_profile.blade.php ENDPATH**/ ?>