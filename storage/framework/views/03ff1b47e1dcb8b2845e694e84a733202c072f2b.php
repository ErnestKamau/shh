

<?php $__env->startSection('module-name'); ?>
<li class="nav-item">
    <a class="nav-link module-name" href="<?php echo e(route('lab-home')); ?>"><i class="mdi mdi-flask"></i> System Planner</a>
</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('title'); ?>
<title>Sample Planner</title>
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

    .datepicker {
        position: ;
    }

    #eventHistory:hover {
        transform: scale(1.01);
        box-shadow: 0 6px 15px rgba(0, 0, 0, .12), 0 4px 8px rgba(0, 0, 0, .06);
    }

    .fc-today {
        background-color: yellow !important;
    }
    .card-widget{
        border-radius: 15px 20px !important;
        margin: 0.5% !important;
    }
    .display-4{
        font-size: 20px !important;
    }
    .text-uppercase{
        font-size:12px
    }
</style>

<?php $__env->stopSection(); ?>


<?php $__env->startSection('content'); ?>





<div class="row" id="body-row">
    <!-- Sidebar -->
    <div id="sidebar-container" class="sidebar-expanded d-none d-md-block col-sm-4 col-md-3 col-lg-3">
        <!-- d-* hiddens the Sidebar in smaller devices. Its itens can be kept on the Navbar 'Menu' -->
        <!-- Bootstrap List Group -->
        <div class="card sticky-top sticky-offset" style="background-color: white;">
            <div class="card-header bg-dark text-center" style="color: white;">
                <h5 class="card-title">
                    <i class="mdi mdi-calendar-text fa-3x"></i><br>
                    <span class="text-lg text-bold">System Planner</span>

                </h5>
            </div>
            <div class="card-body p-4">
                <span class="btn btn-primary btn-sm mt-2" data-target="#create-event" data-toggle="modal"><i class="mdi mdi-plus"></i> Create</span>
                <div class="modal fade" id="create-event" role="dialog">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <form action="<?php echo e(route('full-calendar-create')); ?>" method="post" enctype="multipart/form-data">
                                <?php echo csrf_field(); ?>
                                <div class="modal-header">
                                    <h4 class="modal-title">
                                        <i class="mdi mdi-plus text-primary"></i> Create Event

                                    </h4>
                                </div>
                                <div class="modal-body ">
                                    <div class="row">
                                        <div class="col-sm-6 col-md-6 col-xl-6 col-lg-6">
                                            <div class="form-group">
                                                <label class="control-label"><i class="mdi mdi-inbox-full mr-3"></i> Title <span class="text-danger">*</span></label>
                                                <input type="text" style="width:95%" name="title" placeholder="Title..." class="form-control ml-3 bg-light" required>
                                            </div>
                                            <div class="form-group">
                                                <div class="row">
                                                    <div class="col-sm-6 col-xl-6 col-md-6">

                                                        <label class="control-label"><i class="mdi mdi-alarm-check mr-3"></i> Start Date <span class="text-danger">*</span></label>
                                                        <input type="date" style="width:95%" name="start_date" id="" class="form-control ml-3 bg-light" required>
                                                    </div>
                                                    <div class="col-sm-6 col-xl-6 col-md-6">
                                                        <label class="control-label"><i class="mdi mdi-clock-start"></i> Start Time</label>
                                                        <input type="time" name="start_time" value="00:00" class="form-control ">
                                                    </div>
                                                    <br>
                                                    <div class="col-sm-6 col-xl-6 col-md-6">
                                                        <label class="control-label"><i class="mdi mdi-alarm-check mr-3"></i> End Date <span class="text-danger">*</span></label>
                                                        <input type="date" style="width:95%" name="end_date" id="" class="form-control ml-3 bg-light" required>
                                                    </div>
                                                    <div class="col-sm-6 col-md-6">
                                                        <label class="control-label"><i class="mdi mdi-clock-start"></i> End Time</label>
                                                        <input type="time" name="end_time" value="00:00" class="form-control">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" class="form-control" name="is_routine" id="is-routine" />
                                                <label class="form-check-label">
                                                    Event Frequency ?
                                                </label>
                                            </div>

                                            <br>
                                            <div class="form-group hidden" id="frequency-field">
                                                <label class="control-label">Frequency <span class="text-danger">*</span></label>
                                                <select name="frequency" id="frequecy-set" class="form-control">
                                                    <option value="" disabled>Select Frequency</option>
                                                    <option value="1">Daily</option>
                                                    <option value="7">Weekly</option>
                                                    <option value="30">Monthly</option>
                                                    <option value="90">Quarterly</option>
                                                    <option value="180">Semi Annually</option>
                                                    <option value="365">Annually</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label class="control-label"><i class="mdi mdi-account-supervisor mr-3"></i> Responsible Personnel <span class="text-danger">*</span></label>

                                                <select name="responsible_id[]" class="form-control ml-3 select2" aria-placeholder="Select Personnel..." style="width:95%" required aria-selected="true" multiple>
                                                    <option value="">Choose Responsible Personnel</option>
                                                    <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($user->id); ?>"><?php echo e($user->name); ?></option>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </select>

                                            </div>

                                            <div class="form-group">
                                                <label class="control-label"><i class="mdi mdi-bell-ring mr-3"></i> Notification</label>
                                                <div class="notification">
                                                    <div class="row ml-3">
                                                        <div class="col-sm-4">
                                                            <input type="text" value="Email" disabled class="form-control">
                                                        </div>
                                                        <div class="col-sm-4">
                                                            <input type="number" name="duration[]" id="" class="form-control ">
                                                        </div>
                                                        <div class="col-sm-4">
                                                            <select name="rate[]" id="" class="form-control">
                                                                <option value="Minutes">Minutes</option>
                                                                <option value="Hours">Hours</option>
                                                                <option value="Days">Days</option>
                                                                <option value="Weeks">Weeks</option>
                                                                <option value="Months">Months</option>
                                                                <option value="Years">Years</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <span class="btn btn-default btn-sm mt-2" id="add-notification" style="border-radius: 20px;"><u>Add Notification</u></span>
                                            </div>

                                        </div>
                                        <div class="col-sm-6 col-md-6 col-xl-6 col-lg-6">
                                            <div class="form-group">
                                                <label for="" class="control-label"><i class="mdi mdi-account-multiple-outline mr-3"></i> Client <span class="text-danger">*</span></label>
                                                <select name="client_id" id="" style="width:95%" class="form-control ml-3 bg-light select2" aria-selected="true" multiple>
                                                    <?php $__currentLoopData = $clients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $client): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($client->id); ?>"><?php echo e($client->name); ?></option>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label for="" class="control-label"><i class="mdi mdi-map-marker mr-3"></i> Location</label>
                                                <input type="text" name="location" style="width:95%" class="form-control ml-3 bg-light" placeholder="Location...">
                                            </div>
                                            <div class="form-group">
                                                <label for="" class="control-label"><i class="mdi mdi-calendar-heart mr-3"></i> Event Status <span class="text-danger">*</span></label>
                                                <select name="event_status" class="form-control ml-3 bg-light" style="width:95%" required>
                                                    <option value=""></option>
                                                    <option value="Upcoming">Upcoming</option>
                                                    <option value="Complete">Complete</option>
                                                    <option value="Delayed">Delayed</option>
                                                    <option value="Cancelled">Cancelled</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label for="" class="control-label"><i class="mdi mdi-view-headline mr-3"></i> Description <span class="text-danger">*</span></label>
                                                <textarea class="form-control ml-3 bg-light" style="width:95%" rows="5" name="description" placeholder="Description..." required /></textarea>
                                            </div>
                                            <div class="form-group">
                                                <label for="" class="control-label"><i class="mdi mdi-paperclip mr-3"></i> Attachment</label>
                                                <input type="file" name="attachment" style="width:95%" id="" class="form-control ml-3 bg-light">
                                            </div>

                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" class="form-control" name="notification" id="my-task" />
                                                <label class="form-check-label">
                                                    Send Instant Notification ?
                                                </label>
                                            </div>
                                            <br>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" checked class="form-control" name="notify_client" id="my-task" />
                                                <label class="form-check-label">
                                                    Notify Client ?
                                                </label>
                                            </div>

                                        </div>
                                    </div>






                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-outline-primary btn-sm mdi mdi-content-save">Save</button>
                                    <span class="btn btn-outline-danger btn-sm float-right" style="float: right !important;" data-dismiss="modal">Close</span>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="form-group date-choose mt-3">
                    <input type="date" name="select_date" value="" id="select-date" class="form-control">


                </div>
                <hr>
                <i class="mdi mdi-eye"></i> <b>View</b> <br>
                <div class="form-check ml-2 mb-2">
                    <input class="form-check-input" type="radio" name="exampleRadios" id="my-task" value="option1">
                    <label class="form-check-label" for="my-task">
                        My Tasks
                    </label>
                </div>
                <div class="form-check ml-2 mb-2">
                    <input class="form-check-input" type="radio" name="exampleRadios" id="complete-task" value="option2">
                    <label class="form-check-label" for="complete-task">
                        Completed Tasks
                    </label>
                </div>
                <div class="form-check ml-2 mb-2">
                    <input class="form-check-input" type="radio" name="exampleRadios" id="upcoming-task" value="option2">
                    <label class="form-check-label" for="complete-task">
                        Upcoming Tasks
                    </label>
                </div>

                <div class="form-check ml-2 mb-2">
                    <input class="form-check-input" type="radio" name="exampleRadios" id="delayed-task" value="option2">
                    <label class="form-check-label" for="complete-task">
                        Delayed Tasks
                    </label>
                </div>

                <div class="form-check ml-2 mb-2">
                    <input class="form-check-input" type="radio" name="exampleRadios" id="cancelled-task" value="option2">
                    <label class="form-check-label" for="complete-task">
                        Cancelled Tasks
                    </label>
                </div>
                <div class="form-check ml-2 mb-2">
                    <input class="form-check-input" checked type="radio" name="exampleRadios" id="all-task" value="option2">
                    <label class="form-check-label" for="complete-task">
                        All Tasks
                    </label>
                </div>

                <br>
                <div class="form-group">
                    <?php $responsible = getUsers() ?>
                    <label class="control-label">Personnel</label>
                    <select name="getResponsiblePersonnel" id="responsible_personnel" class="form-control">
                        <option value="">Select Personnel...</option>
                        <?php $__currentLoopData = $responsible; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($r->id); ?>"><?php echo e($r->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <small class=""><i class="mdi mdi-square" style="color:#33691e"></i> Completed <i class="mdi mdi-square" style="color: #2196f3;"></i> Upcoming <i class="mdi mdi-square" style="color:#e65100"></i> Delayed <i class="mdi mdi-square" style="color:#c62828 ;"></i> Cancelled</small>
                <hr>
                <?php
                $user_events = getUserEvents();
                ?>
                <span class="has-floating-badge"><i class="mdi mdi-calendar-text-outline mr-2"></i>No of Tasks
                    <small class="floating-badge ml-2"><?php echo e($user_events->count()); ?></small>
                </span><br><br>
                <a href="<?php echo e(route('printUserEvents')); ?>" style="border-radius: 20px; color:black; border-bottom:5px black" class=""><i class="mdi mdi-printer mr-2"></i> <u>Print Tasks?</u></a> <br>
                <hr>
                <i class="mdi mdi-account-check mr-2"></i> <?php echo e(Auth::user()->name); ?>


            </div>
        </div>

        <!-- List Group END-->
    </div>
    <!-- sidebar-container END -->

    <!-- MAIN -->
    <div class="col-sm-8 col-md-9 col-lg-9 py-3" id="main-container-body">
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
                    'link' => route('full-calendar'),
                    'name' => 'Calendar',
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
            <div class="mb-2 mt-2 d-flex justify-content-around">


                <div class="card card-widget text-white text-center  no-overflow" style="height:100%;background-color:#2196f3">
                    <div class="card-body">
                        <div class="rotate">
                            <i class="mdi mdi-calendar-arrow-right fa-4x"></i>
                        </div>
                        <h6 class="text-uppercase">Upcoming Events</h6>

                        <h1 class="display-4"><?php echo e($up); ?></h1>
                    </div>
                </div>




                <div class="card card-widget text-white text-center  no-overflow" style="height:100%;background-color:#2e7d32">
                    <div class="card-body">
                        <div class="rotate">
                            <i class="mdi mdi-checkbox-multiple-marked-circle fa-4x"></i>
                        </div>
                        <h6 class="text-uppercase">Completed Events</h6>

                        <h1 class="display-4"><?php echo e($c); ?></h1>
                    </div>
                </div>




                <div class="card card-widget text-white text-center  no-overflow" style="height:100%;background-color:#e1b200">
                    <div class="card-body">
                        <div class="rotate">
                            <i class="mdi mdi-calendar-clock fa-4x"></i>
                        </div>
                        <h6 class="text-uppercase">Delayed Events</h6>

                        <h1 class="display-4"><?php echo e($dl); ?></h1>
                    </div>
                </div>



                <div class="card card-widget text-white text-center  no-overflow" style="height:100%;background-color:#e65100">
                    <div class="card-body">
                        <div class="rotate">
                            <i class="mdi mdi-calendar-arrow-right fa-4x"></i>
                        </div>
                        <h6 class="text-uppercase">Expired Events</h6>

                        <h1 class="display-4"><?php echo e($exp); ?></h1>
                    </div>
                </div>



                <div class="card card-widget text-white text-center  no-overflow" style="height:100%;background-color:#c62828">
                    <div class="card-body">
                        <div class="rotate">
                            <i class="mdi mdi-calendar-remove fa-4x"></i>
                        </div>
                        <h6 class="text-uppercase">Cancelled Events</h6>

                        <h1 class="display-4"><?php echo e($canc); ?></h1>

                    </div>
                </div>


                <div class="card card-widget text-white text-center  no-overflow" style="height:100%;background-color:blue">
                    <div class="card-body">
                        <div class="rotate">
                            <i class="mdi mdi-calendar-arrow-right fa-4x"></i>
                        </div>
                        <h6 class="text-uppercase">Ongoing Events</h6>

                        <h1 class="display-4"><?php echo e($ong); ?></h1>
                    </div>
                </div>

            </div>


            <div id="loader-body">
                <!-- <center>

                    <img src="/images/loading.gif" class="mt-2" height="20%" width="20%" alt="">
                </center> -->
            </div>

            <div class="card tab-card" id="body-content">
                <div class="card-header tab-card-header">
                    <ul class="nav nav-tabs card-header-tabs" id="event-tabs" role="tablist">

                        <li class="nav-item">
                            <a href="#event-calendar" data-toggle="tab" role="tab" aria-selected="true" class="nav-link active"><i class="mdi mdi-calendar-month"></i> Calendar View</a>
                        </li>
                        <li class="nav-item">
                            <a href="#event-list" data-toggle="tab" role="tab" aria-selected="true" class="nav-link"><i class="mdi mdi-calendar-text-outline"></i> List View</a>
                        </li>
                    </ul>
                </div>
                <div class="tab-content" id="event-tab-content">
                    <div class="tab-pane show fade p-3" id="event-list" role="tabpanel" aria-labelledby="one-tab">
                        <div class="table-responsive">
                            <table class="table table-condensed table-striped table-hover table-sm table-bordered">
                                <thead class="bg-light">
                                    <tr>
                                        <th style="min-width: 70px !important;"></th>
                                        <th style="min-width:150px !important">Title</th>
                                        <th>Status</th>
                                        <th style="min-width:150px !important">Start Date</th>
                                        <th style="min-width:150px !important">End Date</th>
                                        <th>Client</th>
                                        <th>Responsible Personnel</th>
                                        <th>Event Frequency</th>
                                        <th>Location</th>
                                        <th>Attachment</th>
                                        <th>Description</th>
                                        <th>Notification Sent to Personnel</th>
                                        <th>Client Notified</th>

                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td>
                                            <span class="btn btn-sm btn-default" data-toggle="modal" data-toggle="tooltip" title="Edit Event" data-target="#event-modal" data-events="<?php echo e(json_encode($event->id)); ?>"><i class="mdi mdi-pencil"></i></span>
                                            <span class="btn btn-default btn-sm" data-toggle="modal" data-target="#delete-event" data-events="<?php echo e(json_encode($event)); ?>" data-toggle="tooltip" title="Delete Event"><i class="mdi mdi-delete-empty text-danger"></i></span>
                                        </td>
                                        <td><?php echo e($event->title); ?></td>
                                        <td><?php echo e($event->status); ?></td>
                                        <td><?php echo e($event->start_date); ?> <?php echo e($event->start_time); ?></td>
                                        <td><?php echo e($event->end_date); ?> <?php echo e($event->end_time); ?></td>
                                        <td><?php echo e(getCrmCustomerByID($event->client_id)->name); ?></td>
                                        <td><?php echo e(getUserById($event->responsible_id)->name); ?></td>
                                        <td><?php echo e(getfrequency((int) $event->frequency)); ?></td>
                                        <td><?php echo e($event->location); ?></td>
                                        <?php if($event->attachment == ''): ?>
                                        <td class="text-center">-</td>
                                        <?php else: ?>
                                        <td class="text-center"><a target="_blank" href="<?php echo e($event->attachment); ?>" class="btn btn-sm btn-default"><i class="mdi mdi-download"></i></a> </td>
                                        <?php endif; ?>
                                        <td class="text-center"><span class="btn-outline-success btn-sm"><i class="mdi mdi-eye"></i></span></td>
                                        <td class="text-small text-center"><?php echo $event->notification_sent == '1' ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>'; ?></td>
                                        <td class="text-small text-center"><?php echo $event->is_client_notify == '1' ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>'; ?></td>

                                    </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane fade show active" id="event-calendar" role="tabpanel" aria-labelledby="one-tab">
                        <h5 class="card-title alert alert-info">
                            <i class="mdi mdi-calendar"></i><span class="btn btn-default" id="view-text">Month View</span>
                            <div class="nav-item dropdown float-right">
                                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" style="color:black;font-size:14px" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="mdi mdi-compare-vertical"></i> Change View
                                </a>
                                <div class="dropdown-menu" style="font-size: 13px;" aria-labelledby="navbarDropdown">
                                    <span class="dropdown-item btn" id="change-view"><i class="mdi mdi-subdirectory-arrow-right"></i> Week View</span>
                                    <span class="dropdown-item btn" id="month-view"><i class="mdi mdi-subdirectory-arrow-right"></i> Month View</span>
                                </div>
                            </div>
                        </h5>

                        <div id='calendar' data-event="<?php echo e(json_encode($events)); ?>" class="p-2" style="background-color: white;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Main Col END -->

</div>


<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
<div class="modal fade" id="event-modal" data-clients="<?php echo e(json_encode($clients)); ?>" data-responsible_personnel="<?php echo e(json_encode($users)); ?>" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">

                </h5>
            </div>
            <div class="card tab-card">
                <div class="card-header tab-card-header">
                    <ul class="nav nav-tabs card-header-tabs" id="event-edit-tab" role="tablist">
                        <li class="nav-item">
                            <a href="#EventDetails-tab" class="nav-link" id="event-details" data-toggle="tab" role="tab" aria-controls="EventDetails-tab" aria-selected="true"><i class="mdi mdi-calendar-text"></i> Event Details</a>
                        </li>
                        <li class="nav-item">
                            <a href="#EventHistory-tab" class="nav-link" id="event-history" data-toggle="tab" role="tab" aria-controls="EventHistory-tab" aria-selected="true"><i class="mdi mdi-history"></i> Event History</a>
                        </li>
                    </ul>
                </div>
                <div class="tab-content" id="EventDetails">
                </div>

            </div>

        </div>
    </div>
</div>
<div id="delete-event" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo e(route('delete-events')); ?>" method="post">
                <?php echo csrf_field(); ?>
                <div class="modal-body">

                    <div id="delete-params"></div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-outline-primary btn-sm mdi mdi-content-save">Delete</button>
                    <button type="button" class="btn btn-outline-danger btn-sm" data-dismiss="modal">Cancel</button>
                </div>


            </form>
        </div>
    </div>
</div>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/fullcalendar.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js" integrity="sha256-4iQZ6BVL4qNKlQ27TExEhBN1HFPvAvAMbFavKKosSWQ=" crossorigin="anonymous"></script>


<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/fullcalendar.js"></script>
<!-- <script src="https://pagecdn.io/lib/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js" crossorigin="anonymous"></script> -->
<?php
echo '
<script type="text/javascript">
var current_user =' . json_encode(Auth::user()->id) . ';
</script>
';
?>
<!-- <script>
    $(function(){
        $('#calendar').fullCalendar({
            editable:true,
            header: {
                left:'prev,next,today',
                center:'title',
                right:'month,agendaWeek,agendaDay',
            }
        });
    })
</script> -->

<script>
    $(document).ready(function() {

        var eventBody = function(event, personnel, clients, history, notification) {
            var body_ = $(`
                    <div class="tab-pane fade show active p-2" id="EventDetails-tab" role="tabpanel" aria-labelledby="one-tab">
                        <form action="<?php echo e(route('editEvent')); ?>" method="post">
                            <?php echo csrf_field(); ?>


                            <div class="modal-body">


                                <div class="row mb-3 p-3">
                                    <div class="col-md-6 col-sm-6 col-xl-6">
                                        <div class="form-group">
                                            <label for="" class="control-label"><i class="mdi mdi-inbox-full"></i> Title</label>
                                            <input type="text" name="title" value="${event.title}" class="form-control">
                                        </div>
                                        <div class="form-group">
                                            <div class="row">
                                                <div class="col-sm-6 col-md-6">

                                                    <label for="" class="control-label"><i class="mdi mdi-calendar"></i> Start Date</label>
                                                    <input type="date" name="start_date" value="${event.start_date}" class="form-control ">
                                                </div>
                                                <div class="col-sm-6 col-xl-6 col-md-6">
                                                    <label class="control-label"><i class="mdi mdi-clock-start"></i> Start Time</label>
                                                    <input type="time" name="start_time" value="${event.start_time}" class="form-control ">
                                                </div>
                                                <br>
                                            </div>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" class="form-control" name="is_routine" id="is_routine"/>
                                            <label class="form-check-label">
                                                Event Frequency ?
                                            </label>
                                        </div>

                                        <br>
                                        <div class="form-group hidden" id="frequency-field-edit">
                                            <label class="control-label">Frequency <span class="text-danger">*</span></label>
                                            <select name="frequency" id="frequecy-set-edit" class="form-control  ml-3">
                                                <option value="" disabled>Select Frequency</option>
                                                <option value="1" ${event.frequency == '1' ? `selected` :``}>Daily</option>
                                                <option value="7" ${event.frequency == '7' ? `selected` :``}>Weekly</option>
                                                <option value="30" ${event.frequency == 30 ? `selected` :``}>Monthly</option>
                                                <option value="90" ${event.frequency == 90 ? `selected` :``}>Quarterly</option>
                                                <option value="180" ${event.frequency == 180 ? `selected` : ``}>Semi Annually</option>
                                                <option value="365" ${event.frequency == 365 ? `selected` : ``}>Annually</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="" class="control-label"><i class="mdi mdi-account-multiple-outline"></i> Client</label>
                                            <select name="client_id" id="" style="width:95%" class="form-control">
                                                
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label class="control-label"><i class="mdi mdi-bell-ring"></i> Notifications</label>
                                            <div class="notification-edit">
                                                            
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-sm-6 col-xl-6">
                                        <div class="form-group">
                                            <label for="" class="control-label"><i class="mdi mdi-map-marker"></i> Location</label>
                                            <input type="text" name="location" style="width:95%" value="${event.location}" class="form-control" placeholder="Location...">
                                        </div>
                                        <div class="form-group">

                                           
                                            <label class="control-label"><i class="mdi mdi-account-supervisor"></i> Responsible Personnel</label>
                                            <select name="responsible_id[]" class="form-control select2" multiple aria-placeholder="Select Personnel..." style="width:95% !important">
                                                
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <div class="row">
                                                <div class="col-sm-6 col-md-6">

                                                    <label class="control-label"><i class="mdi mdi-calendar"></i> End Date <span class="text-danger">*</span></label>
                                                    <input type="date" style="width:95%" name="end_date" value="${event.end_date}" id="" class="form-control" required>
                                                </div>
                                                <div class="col-sm-6 col-md-6">

                                                    <label class="control-label"><i class="mdi mdi-clock-start"></i> End Time</label>
                                                    <input type="time" name="end_time" value="${event.end_time}" class="form-control ">


                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="" class="control-label"><i class="mdi mdi-paperclip mr-3"></i> Attachment</label>
                                            <input type="file" name="attachment" style="width:95%" id="" class="form-control ml-3">
                                        </div>




                                    </div>

                                </div>


                                <div class="form-group">
                                    <label class="control-label"><i class="mdi mdi-view-headline"></i> Description</label>
                                    <textarea class="form-control ml-3 " style="width:95%" rows="5" name="description" placeholder="Description..." required />${event.description == 'undefined' ? `` : event.description }</textarea>
                                </div>
                                <hr>
                                <input type="hidden" name="event_id" value="${event.id}">
                                <div class="form-group">
                                    <label for="" class="control-label"><i class="mdi mdi-calendar-heart"></i> Event Status <span class="text-danger">*</span></label>
                                    <select name="event_status" class="form-control ml-3 " style="width:95% !important" required>
                                        <option value="" Disabled>Select Status</option>
                                        <option value="Upcoming" ${event.status == 'Upcoming' ? 'selected' : ''}>Upcoming</option>
                                        <option value="Complete" ${event.status == 'Complete' ? 'selected' : ''}>Complete</option>
                                        <option value="Delayed" ${event.status == 'Delayed' ? 'selected' : ''}>Delayed</option>
                                        <option value="Cancelled" ${event.status == 'Cancelled' ? 'selected' : ''}>Cancelled</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="" class="control-label"><i class="mdi mdi-view-headline"></i> Remark <span class="text-danger">*</span></label>
                                    <textarea class="form-control ml-3 " style="width:95%" rows="3" name="remark" placeholder="Description..." required /></textarea>
                                </div>

                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-outline-primary btn-sm mdi mdi-content-save">Save</button>
                                <button type="button" class="btn btn-outline-danger btn-sm" data-dismiss="modal">Close</button>
                            </div>

                        </form>


                    </div>
                    <div class="tab-pane fade p-2" id="EventHistory-tab" role="tabpanel" aria-labelledby="one-tab">
                        <div class="card p-5" id="history-card" style="height: 100vh; overflow: auto; align-content:center">
                           

                        </div>
                    </div>
            `).clone();
            if (event.is_routine == 1) {
                $(body_).find('#is_routine').prop('checked', true);
                $(body_).find('#frequency-field-edit').removeClass('hidden');

            }
            $(body_).find("#frequecy-set-edit").select2()
            $(body_).find('select[name="event_status"]').select2();
            $.each(clients, function(i, e) {
                var option_ = `<option value="${e.id}" ${e.id == event.client_id ? `selected` : ''} >${e.name}</option>`
                $(body_).find('select[name="client_id"]').append(option_);
            });
            $(body_).find('select[name="client_id"]').select2();
            var responsible_ = event.responsible_id.split(',')
            console.log(responsible_);
            $.each(personnel, function(i, e) {
                console.log(e.id);
                var option_ = `<option value="${e.id}" ${responsible_.includes(e.id.toString()) ? `selected` : ''} >${e.name}</option>`
                $(body_).find('select[name="responsible_id[]"]').append(option_);
            });
            $(body_).find('select[name="responsible_id[]"]').select2();

            $.each(history, function(i, e) {

                var historyCard = $(`
                    <div class="card bg-light mb-3" id="eventHistory" style="box-shadow: 3px 5px #888888;font-size:12px;width:100%">
                        <div class="row p-1 mb-0">
                            <div class="col-sm-6 col-lg-6"><b>
                                    <p style="font-size: 12px;"><i class="mdi mdi-clock-in"></i> ${e.created_at}</p>
                                </b></div>
                            <div class="col-sm-6 col-lg-6" style="text-align:right">
                                <b>
                                    <p style="font-size: 12px;"><i class="mdi mdi-account-arrow-right"></i>${e.name}</p>
                                </b>
                            </div>
                        </div>
                        <div class="content p-2  mt-0">
                            <p><b>Status: </b>${e.status} <br><b>Remark: </b>${e.remark}</p>
                        </div>
                    </div>
                `).clone();


                $(body_).find('#history-card').append(historyCard);
            });
            $.each(notification, function(i, e) {

                var notifcation_ = `
                    <div class="row ml-3">
                        <div class="col-sm-4">
                            <input type="text" value="Email" disabled class="form-control">
                        </div>
                        <div class="col-sm-4">
                            <input type="number" value="${e.duration}" name="duration[]" id="" class="form-control ">
                            <input type="hidden" name="notification_id[]" value="${e.id}">
                        </div>
                        <div class="col-sm-4">
                            <select name="rate[]" id="" class="form-control">
                                <option value="Minutes" ${e.rate == 'Minutes' ? `selected`:``}>Minutes</option>
                                <option value="Hours" ${e.rate == 'Hours' ? `selected`:``}>Hours</option>
                                <option value="Days" ${e.rate == 'Days' ? `selected`:``}>Days</option>
                                <option value="Weeks" ${e.rate == 'Weeks' ? `selected`:``}>Weeks</option>
                                <option value="Months" ${e.rate == 'Months' ? `selected`:``}>Months</option>
                                <option value="Years" ${e.rate == 'Years' ? `selected` :``}>Years</option>
                            </select>
                        </div>
                    </div>                                        
                `;
                $(body_).find('.notification-edit').append(notifcation_);
            });
            $(body_).find('#is_routine').on('change', function() {
                if (this.checked) {
                    $('#frequency-set').attr('required');
                    $(body_).find('#frequency-field-edit').removeClass('hidden');
                } else {
                    $('#frequency-set').removeAttr('required');
                    $(body_).find('#frequency-field-edit').addClass('hidden');
                }
            })
            return body_;

        }
        $('#event-modal').on('show.bs.modal', function(e) {
            var eventID = $(e.relatedTarget).data('events') || $('#event-modal').data('events');
            var personnel = $(this).data('responsible_personnel');
            var clients = $(this).data('clients');
            $(this).find('.modal-title').empty();

            $('#event-modal').find('.tab-content').empty();
            $.ajax({
                url: '/get/event/id/' + eventID,
                type: 'GET',
                success: function(data) {
                    var event_body = eventBody(data.event, personnel, clients, data.history, data.notification)
                    var header_ = `<i class="mdi mdi-calendar-text"></i> ${data.event.title} Information`;
                    $('#event-modal').find('.modal-title').append(header_);
                    $('#event-modal').find('.tab-content').append(event_body)
                }
            })
        })

        $('#change-view').on('click', function() {
            $('#calendar').fullCalendar('changeView', 'listWeek');
            var text = 'Week View';
            $('#view-text').empty();
            $('#view-text').append(text);


        });
        $('#month-view').on('click', function() {
            $('#calendar').fullCalendar('changeView', 'month');
            var text = 'Month View';
            $('#view-text').empty();
            $('#view-text').append(text);

        })


        $('#delete-event').on('show.bs.modal', function(e) {
            var event = $(e.relatedTarget).data('events');
            var text = $(`
                 <div class="alert alert-danger">
                    <i class="mdi mdi-alert-decagram"></i> Confrim you want to delete event - ${event.title}
                </div>
                
                <div class="form-group">
                    
                    <input type="checkbox" id="delete-future" name="delete_future"> Delete all future recurring events!
                    
                    <input type="hidden" name="event_id" value="${event.id}">
                </div>
                <div id="delete-recurring"><div>
                
            `);
            $('#delete-event').find('#delete-params').empty();
            $('#delete-event').find('#delete-params').append(text);
            $(this).find('#delete-future').on('change', function() {
                $('#delete-event').find('#delete-recurring').empty();
                if ($('#delete-event').find('#delete-future').is(':checked')) {

                    var addtext = $(`
                        <div class="alert alert-info">
                            <i class="mdi mdi-alert-decagram"></i> All future recurring events will be deleted!   
                        </div>
                        `);
                    $('#delete-event').find('#delete-recurring').append(addtext);
                }

            });
            // console.log(event);
        })






        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        var showPersonnelTask = function(personnel, event) {

            var ids = event.responsible_id.split(',');
            if (ids.indexOf('' + personnel) > -1) {
                return true;
            } else {
                return false;
            }
        }
        var personnel_selected = false;
        var events_data = $('#calendar').data('event')
        fill_calendar(personnel_selected, events_data);
        // var calendar = $('#calendar').fullCalendar();

        $('body').on('click', 'button.fc-prev-button', function() {
            var tglCurrent = $('#calendar').fullCalendar('getDate');
            var date = new Date(tglCurrent);
            // console.log(date.toLocaleDateString());

            // console.log(date.getUTCMonth());
            var year = date.getFullYear();
            var month = date.getMonth();
            // alert('Year is '+year+' Month is '+month);
        });
        $('#my-task').on('change', function() {
            if ($('#my-task').is(':checked')) {
                $('#calendar').fullCalendar('rerenderEvents');
            } else {
                $('#calendar').fullCalendar('rerenderEvents');
            }
        });
        $('#complete-task').on('change', function() {
            $('#calendar').fullCalendar('rerenderEvents');

        });
        $('#responsible_personnel').on('change', function() {
            personnel_selected = $(this).val();
            $('#calendar').fullCalendar('rerenderEvents');
            personnel_selected = false;
        })
        $('#upcoming-task').on('change', function() {
            $('#calendar').fullCalendar('rerenderEvents');
        });
        $('#delayed-task').on('change', function() {
            $('#calendar').fullCalendar('rerenderEvents');
        });
        $('#cancelled-task').on('change', function() {
            $('#calendar').fullCalendar('rerenderEvents');
        });
        $('#all-task').on('change', function() {
            $('#calendar').fullCalendar('rerenderEvents');
        });

        $('#select-date').on('change', function(event) {
            // console.log(this.value);

            $('#calendar').fullCalendar('gotoDate', this.value);
        });
        $('#add-notification').click(function(e) {

            var $row = $(`
                <div class="notify ml-3 mt-3" style="display:flex;flex-wrap: nowrap;">
                    <input type="text"  value="Email"  style="width:30%;margin: 8px;" disabled class="form-control">
                    <input type="number" name="duration[]"  style="width:30%;margin: 8px;" id="" class="form-control ">
                    <select name="rate[]" id=""  style="width:30%;margin: 8px;" class="form-control">
                        <option value="Minutes">Minutes</option>
                        <option value="Hours">Hours</option>
                        <option value="Days">Days</option>
                        <option value="Weeks">Weeks</option>
                        <option value="Months">Months</option>
                        <option value="Years">Years</option>
                    </select>
                    <button class="btn btn-sm btn-default" id="delete-row" style="margin: 0px;" ><i class="mdi mdi-do-not-disturb"></i></button>            
                </div>
            `);
            $('.notification').append($row);
        });
        $('#add-notification-edit').click(function(e) {
            // console.log('test');
            var $row = $(`
                <div class="notify ml-3 mt-3" style="display:flex;flex-wrap: nowrap;">
                    <input type="text"  value="Email"  style="width:30%;margin: 8px;" disabled class="form-control">
                    <input type="number" name="duration[]"  style="width:30%;margin: 8px;" id="" class="form-control ">
                    <select name="rate[]" id=""  style="width:30%;margin: 8px;" class="form-control">
                        <option value="Minutes">Minutes</option>
                        <option value="Hours">Hours</option>
                        <option value="Days">Days</option>
                        <option value="Weeks">Weeks</option>
                        <option value="Months">Months</option>
                        <option value="Years">Years</option>
                    </select>
                    <button class="btn btn-sm btn-default" id="delete-row" style="margin: 0px;" ><i class="mdi mdi-do-not-disturb"></i></button>            
                </div>
            `);
            $('.notification-edit').append($row);

        })

        $('#create-event').on('click', '#delete-row', function(e) {
            // console.log('tt')
            $(this).parent('div').remove();

        });
        $('#create-event').on('show.bs.modal', function() {
            console.log('test');
            $('#create-event').find('#is-routine').on('change', function() {
                if ($('#is-routine').prop('checked')) {
                    console.log('test1');
                    $('#create-event').find('#frequency-field').removeClass('hidden');
                } else {
                    console.log('test2');
                    $('#create-event').find('#frequency-field').addClass('hidden');
                }

            })
        })


    });

    var fill_calendar = function(personnel_selected, data, list_view) {
        var calendar = $('#calendar').fullCalendar({
            header: {
                left: 'prev,today',
                center: 'title',
                right: 'next'

            },




            displayEventTime: true,

            eventRender: function(event, element, view) {
                if (event.allDay === 'true') {
                    event.allDay = true;
                } else {
                    event.allDay = false;
                }
                if ($('#my-task').is(':checked')) {


                    var ids = event.responsible_id.split(',');
                    if (ids.indexOf('' + current_user) > -1) {
                        return true;
                    } else {
                        return false;
                    }
                }

                if ($('#complete-task').is(':checked')) {
                    return event.status === 'Complete';
                }
                if ($('#upcoming-task').is(':checked')) {
                    return event.status === 'Upcoming';
                }
                if ($('#delayed-task').is(':checked')) {
                    return event.status === 'Delayed';
                }
                if ($('#cancelled-task').is(':checked')) {
                    return event.status === 'Cancelled';
                }
                if (personnel_selected !== false) {
                    var ids = event.responsible_id.split(',');
                    if (ids.indexOf('' + current_user) > -1) {
                        return true;
                    } else {
                        return false;
                    }
                }



            },


            events: data,


            eventClick: function(event) {
                var modal_class = '#event-modal';
                console.log('--------------------------')
                console.log(event.id);
                console.log('--------------------------*')
                $(modal_class).data('events', event.id);
                $(modal_class).modal('show');
                // console.log(modal_class)


            },
            gotoDate: '2018-12-12',
        });
    }

    function deleterow() {
        // console.log('btn');
        var parent = $(this).parent('.row');
        // console.log(parent);
    }

    function addFrequency(item) {
        var str_arr = item.split('-');
        var field_id = '#frequency-field-edit-' + str_arr[1];
        $(field_id).toggle();

    }

    function displayMessage(message) {
        $(".response").html("" + message + "");
        setInterval(function() {
            $(".success").fadeOut();
        }, 1000);
    }
</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app',['dataTable'=>true,'select2'=>true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\wamp64\www\polucon\resources\views/layouts/configuration/system/fullcalendar_.blade.php ENDPATH**/ ?>