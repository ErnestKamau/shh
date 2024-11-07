@extends('layouts.app', ['dataTable' => true, 'select2' => true])

@section('module-name')
<li class="nav-item">
    <a class="nav-link module-name" href="{{ route('lab-home') }}"><i class="mdi mdi-flask"></i> System Planner</a>
</li>
@endsection

@section('title')
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

    .card-widget {
        border-radius: 15px 20px !important;
        margin: 0.5% !important;
    }

    .display-4 {
        font-size: 20px !important;
    }

    .text-uppercase {
        font-size: 12px
    }
    .btn-white{
        background-color: white !important;
    }
</style>

@endsection


@section('content')





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
                <span class="btn btn-primary btn-sm mt-2" data-target="#create-event" data-toggle="modal"><i
                        class="mdi mdi-plus"></i> Create</span>


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
                    <input class="form-check-input" type="radio" name="exampleRadios" id="complete-task"
                        value="option2">
                    <label class="form-check-label" for="complete-task">
                        Completed Tasks
                    </label>
                </div>
                <div class="form-check ml-2 mb-2">
                    <input class="form-check-input" type="radio" name="exampleRadios" id="upcoming-task"
                        value="option2">
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
                    <input class="form-check-input" type="radio" name="exampleRadios" id="cancelled-task"
                        value="option2">
                    <label class="form-check-label" for="complete-task">
                        Cancelled Tasks
                    </label>
                </div>
                <div class="form-check ml-2 mb-2">
                    <input class="form-check-input" checked type="radio" name="exampleRadios" id="all-task"
                        value="option2">
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
                        @foreach($responsible as $r)
                            <option value="{{$r->id}}">{{$r->name}}</option>
                        @endforeach
                    </select>
                </div>
                <small class=""><i class="mdi mdi-square" style="color:#33691e"></i> Completed <i class="mdi mdi-square"
                        style="color: #2196f3;"></i> Upcoming <i class="mdi mdi-square" style="color:#e65100"></i>
                    Delayed <i class="mdi mdi-square" style="color:#c62828 ;"></i> Cancelled</small>
                <hr>
                <?php
$user_events = getUserEvents();
                ?>
                <span class="has-floating-badge"><i class="mdi mdi-calendar-text-outline mr-2"></i>No of Tasks
                    <small class="floating-badge ml-2">{{$user_events->count()}}</small>
                </span><br><br>
                <a href="{{route('printUserEvents')}}" style="border-radius: 20px; color:black; border-bottom:5px black"
                    class=""><i class="mdi mdi-printer mr-2"></i> <u>Print Tasks?</u></a> <br>
                <hr>
                <i class="mdi mdi-account-check mr-2"></i> {{Auth::user()->name}}

            </div>
        </div>

        <!-- List Group END-->
    </div>
    <!-- sidebar-container END -->

    <!-- MAIN -->
    <div class="col-sm-8 col-md-9 col-lg-9 py-3" id="main-container-body">
        <div id="message-section" style="padding: 10px 10px 0px 10px !important">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li><i class="fas fa-exclamation-triangle"></i> {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @if (\Session::has('success') || \Session::has('error'))
                @if (\Session::has('success'))
                    <div class="alert alert-success center text-lg alert-callout">
                        <i class="fas fa-thumbs-up"></i> {{ Session::get('success') }}
                    </div>
                @endif
                @if (\Session::has('error'))
                    <div class="alert alert-danger center text-lg alert-callout">
                        <i class="fas fa-exclamation-triangle"></i> {{ Session::get('error') }}
                    </div>
                @endif
            @endif
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
            <x-bread-crumb :items="$items"></x-bread-crumb>
            <div class="mb-2 mt-2 d-flex justify-content-around">


                <div class="card card-widget text-white text-center  no-overflow"
                    style="height:100%;background-color:#2196f3">
                    <div class="card-body">
                        <div class="rotate">
                            <i class="mdi mdi-calendar-arrow-right fa-4x"></i>
                        </div>
                        <h6 class="text-uppercase">Upcoming Events</h6>

                        <h1 class="display-4">{{$statusCounts['Upcoming'] ?? 0}}</h1>
                    </div>
                </div>




                <div class="card card-widget text-white text-center  no-overflow"
                    style="height:100%;background-color:#2e7d32">
                    <div class="card-body">
                        <div class="rotate">
                            <i class="mdi mdi-checkbox-multiple-marked-circle fa-4x"></i>
                        </div>
                        <h6 class="text-uppercase">Completed Events</h6>

                        <h1 class="display-4">{{$statusCounts['Complete'] ?? 0}}</h1>
                    </div>
                </div>




                <div class="card card-widget text-white text-center  no-overflow"
                    style="height:100%;background-color:#e1b200">
                    <div class="card-body">
                        <div class="rotate">
                            <i class="mdi mdi-calendar-clock fa-4x"></i>
                        </div>
                        <h6 class="text-uppercase">Delayed Events</h6>

                        <h1 class="display-4">{{$statusCounts['Delayed'] ?? 0}}</h1>
                    </div>
                </div>



                <div class="card card-widget text-white text-center  no-overflow"
                    style="height:100%;background-color:#e65100">
                    <div class="card-body">
                        <div class="rotate">
                            <i class="mdi mdi-calendar-arrow-right fa-4x"></i>
                        </div>
                        <h6 class="text-uppercase">Expired Events</h6>

                        <h1 class="display-4">{{$statusCounts['Expired'] ?? 0}}</h1>
                    </div>
                </div>



                <div class="card card-widget text-white text-center  no-overflow"
                    style="height:100%;background-color:#c62828">
                    <div class="card-body">
                        <div class="rotate">
                            <i class="mdi mdi-calendar-remove fa-4x"></i>
                        </div>
                        <h6 class="text-uppercase">Cancelled Events</h6>

                        <h1 class="display-4">{{$statusCounts['Cancelled'] ?? 0}}</h1>

                    </div>
                </div>


                <div class="card card-widget text-white text-center  no-overflow"
                    style="height:100%;background-color:blue">
                    <div class="card-body">
                        <div class="rotate">
                            <i class="mdi mdi-calendar-arrow-right fa-4x"></i>
                        </div>
                        <h6 class="text-uppercase">Ongoing Events</h6>

                        <h1 class="display-4">{{$ong}}</h1>
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
                            <a href="#event-calendar" data-toggle="tab" role="tab" aria-selected="true"
                                class="nav-link active"><i class="mdi mdi-calendar-month"></i> Calendar View</a>
                        </li>
                        <li class="nav-item">
                            <a href="#event-list" data-toggle="tab" role="tab" aria-selected="true" class="nav-link"><i
                                    class="mdi mdi-calendar-text-outline"></i> List View</a>
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
                                        <th>Logistics</th>
                                        <th>Notification Sent to Personnel</th>
                                        <th>Client Notified</th>

                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($data as $event)
                                        <tr>
                                            <td>
                                                <span class="btn btn-sm btn-default" data-toggle="modal"
                                                    data-toggle="tooltip" title="Edit Event" data-target="#event-modal"
                                                    data-events="{{json_encode($event->id)}}"><i
                                                        class="mdi mdi-pencil"></i></span>
                                                <span class="btn btn-default btn-sm" data-toggle="modal"
                                                    data-target="#delete-event" data-events="{{json_encode($event)}}"
                                                    data-toggle="tooltip" title="Delete Event"><i
                                                        class="mdi mdi-delete-empty text-danger"></i></span>
                                            </td>
                                            <td>{{$event->title}}</td>
                                            <td>{{$event->status}}</td>
                                            <td>{{$event->start_date}} {{$event->start_time}}</td>
                                            <td>{{$event->end_date}} {{$event->end_time}}</td>
                                            <td>{{getCrmCustomerByID($event->client_id)->name}}</td>
                                            <td>{{getUserById($event->responsible_id)->name}}</td>
                                            <td>{{getfrequency((int) $event->frequency)}}</td>
                                            <td class="text-center">
                                                @if($event->latitude != '')
                                                    <a target="_blank" href="https://www.google.com/maps/search/?api=1&query={{$event->latitude}},{{$event->longitude}}" class="btn btn-sm btn-default btn-white">Map <i class="mdi mdi-google-maps"></i></a>
                                                @else
                                                    {{$event->location}}
                                                @endif
                                            </td>
                                            @if($event->attachment == '')
                                                <td class="text-center">-</td>
                                            @else
                                                <td class="text-center"><a target="_blank" href="{{$event->attachment}}"
                                                        class="btn btn-sm btn-default"><i class="mdi mdi-download"></i></a>
                                                </td>
                                            @endif
                                            <td class="text-center"><span class="btn btn-default btn-sm" data-toggle="modal" data-target="#view-description" data-record="{{json_encode($event)}}"><i class="mdi mdi-eye" data-toggle="tooltip" title="Description"></i></span></td>
                                            <td class="text-center"><span class="btn btn-default btn-sm" data-toggle="modal" data-target="#view-logistics" data-record="{{json_encode($event)}}" ><i class="mdi mdi-eye" data-toggle="tooltip" title="Logistics"></i></span></td>       
                                            <td class="text-small text-center">
                                                {!! $event->notification_sent == '1' ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>' !!}
                                            </td>
                                            <td class="text-small text-center">
                                                {!! $event->is_client_notify == '1' ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>' !!}
                                            </td>

                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane fade show active" id="event-calendar" role="tabpanel"
                        aria-labelledby="one-tab">
                        <h5 class="card-title alert alert-info">
                            <i class="mdi mdi-calendar"></i><span class="btn btn-default" id="view-text">Month
                                View</span>
                            <div class="nav-item dropdown float-right">
                                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown"
                                    style="color:black;font-size:14px" role="button" data-toggle="dropdown"
                                    aria-haspopup="true" aria-expanded="false">
                                    <i class="mdi mdi-compare-vertical"></i> Change View
                                </a>
                                <div class="dropdown-menu" style="font-size: 13px;" aria-labelledby="navbarDropdown">
                                    <span class="dropdown-item btn" id="change-view"><i
                                            class="mdi mdi-subdirectory-arrow-right"></i> Week View</span>
                                    <span class="dropdown-item btn" id="month-view"><i
                                            class="mdi mdi-subdirectory-arrow-right"></i> Month View</span>
                                </div>
                            </div>
                        </h5>

                        <div id='calendar' data-event="{{json_encode($events)}}" class="p-2"
                            style="background-color: white;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Main Col END -->

</div>


@endsection

@section('script')
<div class="modal fade" id="view-description" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                
            </div>
            <div class="modal-footer">
                <span class="btn btn-sm btn-default" data-dismiss="modal">Close</span>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="view-logistics" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
               
            </div>
            <div class="modal-footer">
                <span class="btn btn-sm btn-default" data-dismiss="modal">Close</span>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="create-event" role="dialog">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form action="{{route('full-calendar-create')}}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h4 class="modal-title"><i class="mdi mdi-plus text-primary"></i> Create Event</h4>
                </div>
                <div class="modal-body ">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="control-label"><i class="mdi mdi-inbox-full mr-3"></i> Title <span
                                        class="text-danger">*</span></label>
                                <textarea name="title" id="" class="form-control" required></textarea>
                            </div>
                        </div>
                        <div class="form-group col-md-3">
                            <label class="control-label"><i class="mdi mdi-alarm-check"></i> Start Date <span
                                    class="text-danger">*</span></label>
                            <input type="date" name="start_date" id="" class="form-control" required>
                        </div>
                        <div class="form-group col-md-3">
                            <label class="control-label"><i class="mdi mdi-clock-start"></i> Start Time</label>
                            <input type="time" name="start_time" value="00:00" class="form-control ">
                        </div>
                        <div class="col-md-3 form-group">
                            <label class="control-label"><i class="mdi mdi-alarm-check mr-3"></i> End Date <span
                                    class="text-danger">*</span></label>
                            <input type="date" name="end_date" id="" class="form-control" required>
                        </div>
                        <div class="col-md-3 form-group">
                            <label class="control-label"><i class="mdi mdi-clock-start"></i> End Time</label>
                            <input type="time" name="end_time" value="00:00" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="" class="control-label"><i
                                        class="mdi mdi-account-multiple-outline mr-3"></i> Client <span
                                        class="text-danger">*</span></label>
                                <select name="client_id" id="" class="form-control select2"
                                    aria-selected="true" multiple>
                                    @foreach($clients as $client)
                                        <option value="{{$client->id}}">{{$client->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label"><i class="mdi mdi-account-supervisor mr-3"></i> Responsible
                                    Personnel <span class="text-danger">*</span></label>
                                <select name="responsible_id[]" class="form-control ml-3 select2"
                                    aria-placeholder="Select Personnel..." required aria-selected="true" multiple>
                                    <option value="">Choose Responsible Personnel</option>
                                    @foreach($users as $user)
                                        <option value="{{$user->id}}">{{$user->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="" class="control-label"><i class="mdi mdi-calendar-heart mr-3"></i> Event
                                    Status <span class="text-danger">*</span></label>
                                <select name="event_status" class="form-control ml-3" required>
                                    <option value=""></option>
                                    <option value="Upcoming">Upcoming</option>
                                    <option value="Complete">Complete</option>
                                    <option value="Delayed">Delayed</option>
                                    <option value="Cancelled">Cancelled</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="" class="control-label"><i class="mdi mdi-paperclip mr-3"></i>
                                    Attachment</label>
                                <input type="file" name="attachment" id="" class="form-control ml-3">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="" class="control-label"
                                    style="display: flex; justify-content: space-between; align-items: center;">
                                    <span> <i class="mdi mdi-map-marker mr-3"></i> Location</span>
                                    <small class="btn btn-sm initiate-map" style="font-size:10px"><u><b>Choose From
                                                Map?</b></u></small>
                                </label>
                                <input type="text" name="location" style="width:95%" class="form-control ml-3"
                                    placeholder="Location...">
                                <input type="hidden" name="latitude" value="" id="latitude">
                                <input type="hidden" name="longitude" value="" id="longitude">

                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" class="form-control" name="is_routine"
                                    id="is-routine" />
                                <label class="form-check-label"> Event Frequency ? </label>
                            </div>
                        </div>
                        <div class="form-group col-md-4 hidden" id="frequency-field">
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
                        <div class="col-md-4">
                            <div class="form-check mt-4 pb-4">
                                <input class="form-check-input" type="checkbox" class="form-control" name="notification"
                                    id="my-task" />
                                <label class="form-check-label">Send Instant Notification ? </label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check mt-4 pb-4">
                                <input class="form-check-input" type="checkbox" checked class="form-control"
                                    name="notify_client" id="my-task" />
                                <label class="form-check-label"> Notify Client ? </label>
                            </div>
                        </div>
                        <div class="form-group col-md-12">
                            <label class="control-label"><i class="mdi mdi-bell-ring mr-3"></i> Notification</label>
                            <div class="notification">
                                <div class="row ml-3">
                                    <div class="col-sm-4">
                                        <input type="text" value="Email" disabled class="form-control">
                                    </div>
                                    <div class="col-sm-4">
                                        <input type="number" name="duration[]" placeholder="Duration..." id="" class="form-control ">
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
                            <span class="btn btn-default btn-sm mt-2" id="add-notification"
                                style="border-radius: 20px;"><u>Add Notification</u></span>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="" class="control-label"><i class="mdi mdi-view-headline mr-3"></i>
                                    Description <span class="text-danger">*</span></label>
                                <textarea class="form-control ml-3" rows="5" name="description"
                                    placeholder="Description..." required /></textarea>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="" class="control-label"><i class="mdi mdi-view-headline mr-3"></i> Logistics
                                    Detail</label>
                                <textarea name="logistics" id="" class="form-control editor"></textarea>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div id="contain-maps" class="hidden border-top pt-3 mt-3">
                                <!-- <input type="hidden" name="latitude" value="" id="create-latitude">
                                <input type="hidden" name="longitude" value="" id="create-longitude"> -->

                                <span class="btn btn-sm btn-outline-danger float-right destroy-map mb-3">Close
                                    Map</span>
                                <div id="maps-sect" class="" style="width: 100%;height: 70vh">

                                </div>

                            </div>
                        </div>

                    </div>

                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-outline-primary btn-sm mdi mdi-content-save">Save</button>
                    <span class="btn btn-outline-danger btn-sm float-right" style="float: right !important;"
                        data-dismiss="modal">Close</span>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="event-modal" data-clients="{{json_encode($clients)}}"
    data-responsible_personnel="{{json_encode($users)}}" role="dialog">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">

                </h5>
            </div>
            <div class="card tab-card">
                <div class="card-header tab-card-header">
                    <ul class="nav nav-tabs card-header-tabs" id="event-edit-tab" role="tablist">
                        <li class="nav-item">
                            <a href="#EventDetails-tab" class="nav-link" id="event-details" data-toggle="tab" role="tab"
                                aria-controls="EventDetails-tab" aria-selected="true"><i
                                    class="mdi mdi-calendar-text"></i> Event Details</a>
                        </li>
                        <li class="nav-item">
                            <a href="#EventHistory-tab" class="nav-link" id="event-history" data-toggle="tab" role="tab"
                                aria-controls="EventHistory-tab" aria-selected="true"><i class="mdi mdi-history"></i>
                                Event History</a>
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
            <form action="{{route('delete-events')}}" method="post">
                @csrf
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js"
    integrity="sha256-4iQZ6BVL4qNKlQ27TExEhBN1HFPvAvAMbFavKKosSWQ=" crossorigin="anonymous"></script>


<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/fullcalendar.js"></script>
<!-- <script src="https://pagecdn.io/lib/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js" crossorigin="anonymous"></script> -->
<?php
echo '
<script type="text/javascript">
var current_user =' . json_encode(Auth::user()->id) . ';
</script>
';
?>

<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&key=AIzaSyBqS4AEZ-gVeXjG794Rh0eTd6yvdfMKTjg&sensor=false"
    type="text/javascript"></script>
<script type="text/javascript" src="/tinymce/tinymce.min.js"></script>

<script>
    $(document).ready(function () {

        tinymce.init({
            selector: 'textarea.editor'
        });

        var viewDescriptionBody = (data)=>{
            var body = $(`
                <div class="alert alert-defualt p-2">
                    <div class="alert-header bg-light p-2 border-bottom">
                        <b>${data.title} : Description</b>
                    </div>
                    <div class="alert-body mt-2">${data.description}</div>
                </div>
            `).clone();
            return body;
        }
        var viewLogisticsBody = (data)=>{
            var body =$(`
                <div class="alert alert-default p-2">
                    <div class="alert-header bg-light p-2 border-bottom">
                        <b>${data.title} : Logistics</b>
                    </div>
                    <div class="alert-body mt-2">
                        ${data.logistics}
                    </div>
                </div>
            `).clone();
            return body;
        }
        $('#view-description').on('show.bs.modal',(e)=>{
            var record = $(e.relatedTarget).data('record');
            var body = viewDescriptionBody(record);
            $('#view-description').find('.modal-body').empty();
            $('#view-description').find('.modal-body').append(body);

        });
        $('#view-logistics').on('show.bs.modal',(e)=>{
            var record = $(e.relatedTarget).data('record');
            var body =viewLogisticsBody(record);
            $('#view-logistics').find('.modal-body').empty();
            $('#view-logistics').find('.modal-body').append(body);
        });
        var viewEventBody = (event,personnel, clients, history)=>{
            var body = $(`
            <div class="tab-pane fade show active p-2" id="EventDetails-tab" role="tabpanel" aria-labelledby="one-tab">
                <form action="{{route('editEvent')}}" method="post">
                    @csrf
                    <div class="modal-body">
                        <span class="btn btn-sm btn-outline-primary float-right initiate-edit-mode"><i class="mdi mdi-pencil"></i>
                            Edit Mode</span>
                        <div class="row" style="clear:both">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label"><i class="mdi mdi-inbox-full mr-3"></i> Title <span
                                            class="text-danger">*</span></label>
                                    <textarea name="title" id="" class="form-control" disabled required>${event.title}</textarea>
                                </div>
                            </div>
                            <div class="form-group col-md-3">
                                <label class="control-label"><i class="mdi mdi-alarm-check"></i> Start Date <span
                                        class="text-danger">*</span></label>
                                <input type="date" name="start_date" value="${event.start_date}" disabled id="" class="form-control"
                                    required>
                            </div>
                            <div class="form-group col-md-3">
                                <label class="control-label"><i class="mdi mdi-clock-start"></i> Start Time</label>
                                <input type="time" name="start_time" value="${event.start_time}" disabled class="form-control ">
                            </div>
                            <div class="col-md-3 form-group">
                                <label class="control-label"><i class="mdi mdi-alarm-check mr-3"></i> End Date <span
                                        class="text-danger">*</span></label>
                                <input type="date" name="end_date" id="" value="${event.end_date}" disabled class="form-control" required>
                            </div>
                            <div class="col-md-3 form-group">
                                <label class="control-label"><i class="mdi mdi-clock-start"></i> End Time</label>
                                <input type="time" name="end_time" value="${event.end_time}" disabled class="form-control">
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="" class="control-label"><i class="mdi mdi-account-multiple-outline mr-3"></i> Client <span
                                            class="text-danger">*</span></label>
                                    <select name="client_id" id="" class="form-control select2" aria-selected="true" multiple>
                    
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label"><i class="mdi mdi-account-supervisor mr-3"></i> Responsible Personnel <span
                                            class="text-danger">*</span></label>
                                    <select name="responsible_id[]" class="form-control ml-3 select2" aria-placeholder="Select Personnel..."
                                        required aria-selected="true" multiple>
                                        <option value="">Choose Responsible Personnel</option>
                    
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="" class="control-label"><i class="mdi mdi-calendar-heart mr-3"></i> Event
                                        Status <span class="text-danger">*</span></label>
                                    <select name="event_status" class="form-control ml-3" required>
                                        <option value=""></option>
                                        <option value="Upcoming">Upcoming</option>
                                        <option value="Complete">Complete</option>
                                        <option value="Delayed">Delayed</option>
                                        <option value="Cancelled">Cancelled</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="" class="control-label"><i class="mdi mdi-paperclip mr-3"></i>
                                        Attachment</label>
                                    <div class="ml-2"><a href="${event.attachment}" target="_blank"
                                            class="btn btn-default bg-light btn-sm"><i class="mdi mdi-download"></i> Download</a></div>
                                </div>
                            </div>
                    
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="" class="control-label"
                                        style="display: flex; justify-content: space-between; align-items: center;">
                                        <span> <i class="mdi mdi-map-marker mr-3"></i> Location</span>
                                    </label>
                                    <div class="${event.latitude == null ? 'hidden' : ''} ml-2">
                                        <a href="https://www.google.com/maps/search/?api=1&query=${event.latitude},${event.longitude}" target="_blank" class="btn btn-sm bg-light btn-default"><i class="mdi mdi-google-maps"></i> Got to
                                            Map</a>
                                    </div>
                                    <input type="text" name="location" value="${event.location || ''}" disabled style="width:95%"
                                        class="form-control ml-3 ${event.latitude == null ? '' : 'hidden'}" placeholder="Location...">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" class="form-control" name="is_routine"
                                        id="is-routine" />
                                    <label class="form-check-label"> Event Frequency ? </label>
                                </div>
                            </div>
                            <div class="form-group col-md-4 hidden" id="frequency-field">
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
                    
                    
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="" class="control-label"><i class="mdi mdi-view-headline mr-3"></i>
                                        Description <span class="text-danger">*</span></label>
                                    <div class="p-2 bg-light">
                                        ${event.description}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="" class="control-label"><i class="mdi mdi-view-headline mr-3"></i> Logistics
                                        Detail</label>
                                    <div class="p-2 bg-light">
                                        ${event.logistics}
                                    </div>
                                </div>
                            </div>
                    
                        </div>
                    </div>
                    <div class="modal-footer">
                        <span class="btn btn-sm btn-default" data-dismiss="modal">Close</span>
                    </div>
                </form>
            </div>
            <div class="tab-pane fade p-2" id="EventHistory-tab" role="tabpanel" aria-labelledby="one-tab">
                <div class="card p-5" id="history-card" style="height: 100vh; overflow: auto; align-content:center">


                </div>
            </div>

            `).clone();
            if (event.is_routine == 1) {
                $(body).find('#is_routine').prop('checked', true);
                $(body).find('#frequency-field-edit').removeClass('hidden');

            }
            $(body).find('#frequecy-set').val(event.frequency);
            $(body).find("#frequecy-set-edit").select2()
            $(body).find('#frequecy-set').attr('disabled',true)
            $(body).find('select[name="event_status"]').val(event.status)
            $(body).find('select[name="event_status"]').select2();
            $(body).find('select[name="event_status"]').attr('disabled',true);
            $.each(clients, function (i, e) {
                var option_ = `<option value="${e.id}" ${e.id == event.client_id ? `selected` : ''} >${e.name}</option>`
                $(body).find('select[name="client_id"]').append(option_);
            });
            $(body).find('select[name="client_id"]').select2();
            $(body).find('select[name="client_id"]').attr('disabled',true);
            var responsible_ = event.responsible_id.split(',')
            $.each(personnel, function (i, e) {
                console.log(e.id);
                var option_ = `<option value="${e.id}" ${responsible_.includes(e.id.toString()) ? `selected` : ''} >${e.name}</option>`
                $(body).find('select[name="responsible_id[]"]').append(option_);
            });
            $(body).find('select[name="responsible_id[]"]').select2();
            $(body).find('select[name="responsible_id[]"]').attr('disabled',true)
            $.each(history, function (i, e) {
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


                $(body).find('#history-card').append(historyCard);
            });
            $(body).find('.initiate-edit-mode').on('click',()=>{
                mode = 'edit';
                console.log('here--------1');
                $('#event-modal').find('.tab-content').empty();
                getEventBody(mode,event.id,personnel,clients);
            });
            return body
        }

        var eventBody = function (event, personnel, clients, history, notification) {
            var body_ = $(`
                <div class="tab-pane fade show active p-2" id="EventDetails-tab" role="tabpanel" aria-labelledby="one-tab">
                <form action="{{route('editEvent')}}" method="post">
                @csrf
                <div class="modal-body">
                        <span class="btn btn-sm btn-outline-primary float-right initiate-view-mode"><i class="mdi mdi-eye"></i> View Mode</span> 
                            <div class="row mt-2" style="clear:both">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="control-label"><i class="mdi mdi-inbox-full mr-3"></i> Title <span
                                                class="text-danger">*</span></label>
                                        <textarea name="title" id="" class="form-control" required>${event.title}</textarea>
                                    </div>
                                    <input type="hidden" name="event_id" value="${event.id}">
                                </div>
                                <div class="form-group col-md-3">
                                    <label class="control-label"><i class="mdi mdi-alarm-check"></i> Start Date <span
                                            class="text-danger">*</span></label>
                                    <input type="date" name="start_date" value="${event.start_date}" id="" class="form-control" required>
                                </div>
                                <div class="form-group col-md-3">
                                    <label class="control-label"><i class="mdi mdi-clock-start"></i> Start Time</label>
                                    <input type="time" name="start_time" value="${event.start_time}" class="form-control ">
                                </div>
                                <div class="col-md-3 form-group">
                                    <label class="control-label"><i class="mdi mdi-alarm-check mr-3"></i> End Date <span
                                            class="text-danger">*</span></label>
                                    <input type="date" name="end_date" id="" value="${event.end_date}" class="form-control" required>
                                </div>
                                <div class="col-md-3 form-group">
                                    <label class="control-label"><i class="mdi mdi-clock-start"></i> End Time</label>
                                    <input type="time" name="end_time" value="${event.end_time}" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="" class="control-label"><i class="mdi mdi-account-multiple-outline mr-3"></i> Client <span class="text-danger">*</span></label>
                                        <select name="client_id" id="" class="form-control select2" aria-selected="true" multiple>
                                            
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="control-label"><i class="mdi mdi-account-supervisor mr-3"></i> Responsible Personnel <span class="text-danger">*</span></label>
                                        <select name="responsible_id[]" class="form-control ml-3 select2" aria-placeholder="Select Personnel..." required aria-selected="true" multiple>
                                            <option value="">Choose Responsible Personnel</option>
                                            
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="" class="control-label"><i class="mdi mdi-calendar-heart mr-3"></i> Event
                                            Status <span class="text-danger">*</span></label>
                                        <select name="event_status" class="form-control ml-3" required>
                                            <option value=""></option>
                                            <option value="Upcoming">Upcoming</option>
                                            <option value="Complete">Complete</option>
                                            <option value="Delayed">Delayed</option>
                                            <option value="Cancelled">Cancelled</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="" class="control-label"><i class="mdi mdi-paperclip mr-3"></i>
                                            Attachment</label>
                                        <input type="file" name="attachment" id="" class="form-control ml-3">
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="" class="control-label"
                                            style="display: flex; justify-content: space-between; align-items: center;">
                                            <span> <i class="mdi mdi-map-marker mr-3"></i> Location</span>
                                            <small class="btn btn-sm initiate-map" style="font-size:10px"><u><b>Choose From Map?</b></u></small>
                                        </label>
                                        <input type="text" name="location" value="${event.location || ''}"  style="width:95%" class="form-control ml-3"
                                            placeholder="Location...">
                                        <input type="hidden" name="latitude" value="${event.latitude || ''}" id="latitude_edit">
                                        <input type="hidden" name="longitude" value="${event.longitude || ''}" id="longitude_edit">

                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check mt-4">
                                        <input class="form-check-input" type="checkbox" class="form-control" name="is_routine"
                                            id="is-routine" />
                                        <label class="form-check-label"> Event Frequency ? </label>
                                    </div>
                                </div>
                                <div class="form-group col-md-4 hidden" id="frequency-field">
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
                                <div class="col-md-4">
                                    <div class="form-check mt-4 pb-4">
                                        <input class="form-check-input" type="checkbox" class="form-control" name="notification"
                                            id="my-task" />
                                        <label class="form-check-label">Send Instant Notification ? </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check mt-4 pb-4">
                                        <input class="form-check-input" type="checkbox" class="form-control"
                                            name="notify_client" id="my-task" />
                                        <label class="form-check-label"> Notify Client ? </label>
                                    </div>
                                </div>
                                <div class="form-group col-md-12">
                                    <label class="control-label"><i class="mdi mdi-bell-ring mr-3"></i> Notification</label>
                                    <div class="notification-edit">
                                        <div class="row ml-3">
                                            <div class="col-sm-4">
                                                <input type="text" value="Email" disabled class="form-control">
                                            </div>
                                            <div class="col-sm-4">
                                                <input type="number" name="duration[]" placeholder="Duration..." id="" class="form-control ">
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
                                    <span class="btn btn-default btn-sm mt-2" id="add-notification"
                                        style="border-radius: 20px;"><u>Add Notification</u></span>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="" class="control-label"><i class="mdi mdi-view-headline mr-3"></i>
                                            Description <span class="text-danger">*</span></label>
                                        <textarea class="form-control ml-3" rows="5" name="description"
                                            placeholder="Description..." required />${event.description}</textarea>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="" class="control-label"><i class="mdi mdi-view-headline mr-3"></i> Logistics
                                            Detail</label>
                                        <textarea name="logistics" id="logistics" class="form-control editor">${event.logistics}</textarea>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div id="contain-maps" class="hidden border-top pt-3 mt-3">
                                        <span class="btn btn-sm btn-outline-danger float-right destroy-map mb-3">Close
                                            Map</span>
                                        <div id="maps-sect-" class="" style="width: 100%;height: 70vh">

                                        </div>

                                    </div>
                                </div>

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
            $(body_).find('.destroy-map').on('click', (e) => {
                $(body_).find('#contain-maps').addClass('hidden');
                $(body_).find('.initiate-map').removeClass('hidden');
            });
            $(body_).find('.initiate-map').on('click', (e) => {
                $(body_).find('#contain-maps').removeClass('hidden');
                $(body_).find('.initiate-map').addClass('hidden');
                mapsGraph(true,event.latitude,event.longitude);
            });
            if (event.is_routine == 1) {
                $(body_).find('#is_routine').prop('checked', true);
                $(body_).find('#frequency-field-edit').removeClass('hidden');

            }
            $(body_).find("#frequecy-set-edit").select2()
            $(body_).find('select[name="event_status"]').val(event.status)
            $(body_).find('select[name="event_status"]').select2();
            $.each(clients, function (i, e) {
                var option_ = `<option value="${e.id}" ${e.id == event.client_id ? `selected` : ''} >${e.name}</option>`
                $(body_).find('select[name="client_id"]').append(option_);
            });
            $(body_).find('select[name="client_id"]').select2();
            var responsible_ = event.responsible_id.split(',')
            $.each(personnel, function (i, e) {
                console.log(e.id);
                var option_ = `<option value="${e.id}" ${responsible_.includes(e.id.toString()) ? `selected` : ''} >${e.name}</option>`
                $(body_).find('select[name="responsible_id[]"]').append(option_);
            });
            $(body_).find('select[name="responsible_id[]"]').select2();
            $.each(history, function (i, e) {
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
            $(body_).find('.notification-edit').empty();
            $.each(notification, function (i, e) {

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
                                <option value="Minutes" ${e.rate == 'Minutes' ? `selected` : ``}>Minutes</option>
                                <option value="Hours" ${e.rate == 'Hours' ? `selected` : ``}>Hours</option>
                                <option value="Days" ${e.rate == 'Days' ? `selected` : ``}>Days</option>
                                <option value="Weeks" ${e.rate == 'Weeks' ? `selected` : ``}>Weeks</option>
                                <option value="Months" ${e.rate == 'Months' ? `selected` : ``}>Months</option>
                                <option value="Years" ${e.rate == 'Years' ? `selected` : ``}>Years</option>
                            </select>
                        </div>
                    </div>                                        
                `;
                $(body_).find('.notification-edit').append(notifcation_);
            });
            $(body_).find('#is_routine').on('change', function () {
                if (this.checked) {
                    $('#frequency-set').attr('required');
                    $(body_).find('#frequency-field-edit').removeClass('hidden');
                } else {
                    $('#frequency-set').removeAttr('required');
                    $(body_).find('#frequency-field-edit').addClass('hidden');
                }
            });
            $(body_).find('.initiate-view-mode').on('click',()=>{
                mode = 'view';
                $('#event-modal').find('.tab-content').empty();
                getEventBody(mode,event.id,personnel,clients);
            });
           
            return body_;

        }
        var getEventAjax = (eventID,callback)=>{
            $.ajax({
                url: '/get/event/id/' + eventID,
                type: 'GET',
                success: function (data) {
                    callback(data)
                } 
            })
        }
        var getEventBody = (mode,event_id,personnel,clients)=>{
            console.log(`mode -------------- ${mode} ------------- event ID ----------- ${event_id}`)
            getEventAjax(event_id,(data)=>{
                if(mode == "edit"){
                    var event_body = eventBody(data.event, personnel, clients, data.history, data.notification)
                    var header_ = `<i class="mdi mdi-calendar-text"></i> ${data.event.title} Information`;
                    $('#event-modal').find('.modal-title').empty();
                    $('#event-modal').find('.modal-title').append(header_);
                    $('#event-modal').find('.tab-content').empty();
                    $('#event-modal').find('.tab-content').append(event_body);
                    if(data.event.latitude != ""){
                        $('#event-modal').find('#contain-maps').removeClass('hidden');
                        $('#event-modal').find('.initiate-map').addClass('hidden');
                        mapsGraph(true,data.event.latitude,data.event.longitude);
                    }
                    tinymce.init({
                        selector: 'textarea#logistics'
                    });
                }else{
                    var event_body =viewEventBody(data.event,personnel, clients, data.history)
                    var header_ = `<i class="mdi mdi-calendar-text"></i> ${data.event.title} Information`;
                    $('#event-modal').find('.modal-title').empty();
                    $('#event-modal').find('.modal-title').append(header_);
                    $('#event-modal').find('.tab-content').empty();
                    $('#event-modal').find('.tab-content').append(event_body);
                }
                return "success";
            })
        }
        $('#event-modal').on('show.bs.modal', function (e) {
            var eventID = $(e.relatedTarget).data('events') || $('#event-modal').data('events');
            var personnel = $(this).data('responsible_personnel');
            var clients = $(this).data('clients');
            $(this).find('.modal-title').empty();
            var mode = 'view';
            $('#event-modal').find('.tab-content').empty();
            getEventBody(mode,eventID,personnel,clients);
            console.log('here');
            $('.initiate-edit-mode').on('click',()=>{
                mode = 'edit';
                console.log('here--------2');
                $('#event-modal').find('.tab-content').empty();
                getEventBody(mode,eventID,personnel,clients);

            });
            $('#event-modal').find('.initiate-view-mode').on('click',(e)=>{
                mode = 'view';
                $('#event-modal').find('.tab-content').empty();
                getEventBody(mode,eventID,personnel,clients);

            });
        
        
        })

        $('#change-view').on('click', function () {
            $('#calendar').fullCalendar('changeView', 'listWeek');
            var text = 'Week View';
            $('#view-text').empty();
            $('#view-text').append(text);


        });
        $('#month-view').on('click', function () {
            $('#calendar').fullCalendar('changeView', 'month');
            var text = 'Month View';
            $('#view-text').empty();
            $('#view-text').append(text);

        })


        $('#delete-event').on('show.bs.modal', function (e) {
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
            $(this).find('#delete-future').on('change', function () {
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

        var showPersonnelTask = function (personnel, event) {

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

        $('body').on('click', 'button.fc-prev-button', function () {
            var tglCurrent = $('#calendar').fullCalendar('getDate');
            var date = new Date(tglCurrent);
            // console.log(date.toLocaleDateString());

            // console.log(date.getUTCMonth());
            var year = date.getFullYear();
            var month = date.getMonth();
            // alert('Year is '+year+' Month is '+month);
        });
        $('#my-task').on('change', function () {
            if ($('#my-task').is(':checked')) {
                $('#calendar').fullCalendar('rerenderEvents');
            } else {
                $('#calendar').fullCalendar('rerenderEvents');
            }
        });
        $('#complete-task').on('change', function () {
            $('#calendar').fullCalendar('rerenderEvents');

        });
        $('#responsible_personnel').on('change', function () {
            personnel_selected = $(this).val();
            $('#calendar').fullCalendar('rerenderEvents');
            personnel_selected = false;
        })
        $('#upcoming-task').on('change', function () {
            $('#calendar').fullCalendar('rerenderEvents');
        });
        $('#delayed-task').on('change', function () {
            $('#calendar').fullCalendar('rerenderEvents');
        });
        $('#cancelled-task').on('change', function () {
            $('#calendar').fullCalendar('rerenderEvents');
        });
        $('#all-task').on('change', function () {
            $('#calendar').fullCalendar('rerenderEvents');
        });

        $('#select-date').on('change', function (event) {
            // console.log(this.value);

            $('#calendar').fullCalendar('gotoDate', this.value);
        });
        $('#add-notification').click(function (e) {

            var $row = $(`
                <div class="row ml-2">
                    <div class="col-md-4"><input type="text"  value="Email"  style="margin: 8px;" disabled class="form-control"></div>
                    <div class="col-md-4"><input type="number" name="duration[]" placeholder="Duration..."  style="margin: 8px;" id="" class="form-control "></div>
                    <div class="col-md-4 d-flex">
                        <select name="rate[]" id=""  style="width:90%;margin: 8px;" class="form-control rate">
                            <option value="Minutes">Minutes</option>
                            <option value="Hours">Hours</option>
                            <option value="Days">Days</option>
                            <option value="Weeks">Weeks</option>
                            <option value="Months">Months</option>
                            <option value="Years">Years</option>
                        </select>
                        <button class="btn btn-sm btn-default" id="delete-row" style="margin: 0px;" ><i class="mdi mdi-do-not-disturb"></i></button>            
                    </div>
                </div>
            `);
            $('.notification').find('.rate').select2()
            $('.notification').append($row);
        });
        $('#add-notification-edit').click(function (e) {
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

        $('#create-event').on('click', '#delete-row', function (e) {
            // console.log('tt')
            $(this).closest('.row').remove();

        });
        var mapsGraph = function (gps = null,latitude = null,longitude = null,notDragable = null) {
            if (gps) {
                var mapProp = {
                    center: new google.maps.LatLng(-4.05466, 39.66359),
                    zoom: 5.5,
                };
                map = new google.maps.Map(document.getElementById('maps-sect-'), mapProp);

                // var lat = Object.keys(gps);
                // console.log(lat);
                var marker = new google.maps.Marker({
                    position: new google.maps.LatLng(latitude, longitude),
                    title: `Event Location`,
                    draggable: notDragable ? false : true,

                });
                marker.setMap(map);
                if(!notDragable){
                    marker.addListener('drag', function (event) {
                        console.log('start')
                        document.getElementById('latitude_edit').value = event.latLng.lat();
                        // $('#create-event').find('.latitude').val(event.latLng.lat())
                        // console.log(event.latLng.lat())
                        document.getElementById('longitude_edit').value = event.latLng.lng()
                        // $('#create-event').find('.longitude').val(event.latLng.lng())
                    });
                    marker.addListener('dragend', function (event) {
                        console.log('start2')
                        document.getElementById('latitude_edit').value = event.latLng.lat();
                        // $('#create-event').find('.latitude').val(event.latLng.lat())
                        console.log(event.latLng.lat())
                        // $('#create-event').find('.longitude').val(event.latLng.lng())
                        document.getElementById('longitude_edit').value = event.latLng.lng()
                        console.log(event.latLng.lng())
                    });
                }
            } else {

                var mapProp = {
                    center: new google.maps.LatLng(-4.05466, 39.66359),
                    zoom: 6.5,
                };
                map = new google.maps.Map(document.getElementById('maps-sect'), mapProp);

                var marker = new google.maps.Marker({
                    position: mapProp.center,
                    // icon:'pinkball.png'
                    draggable: true,
                });

                marker.setMap(map);
                marker.addListener('drag', function (event) {
                    console.log('start')
                    document.getElementById('latitude').value = event.latLng.lat();
                    // $('#create-event').find('.latitude').val(event.latLng.lat())
                    // console.log(event.latLng.lat())
                    document.getElementById('longitude').value = event.latLng.lng()
                    // $('#create-event').find('.longitude').val(event.latLng.lng())
                });
                marker.addListener('dragend', function (event) {
                    console.log('start2')
                    document.getElementById('latitude').value = event.latLng.lat();
                    // $('#create-event').find('.latitude').val(event.latLng.lat())
                    console.log(event.latLng.lat())
                    // $('#create-event').find('.longitude').val(event.latLng.lng())
                    document.getElementById('longitude').value = event.latLng.lng()
                    console.log(event.latLng.lng())
                });
            }

        }
        $('#create-event').on('show.bs.modal', function () {
            $('#create-event').find('.intiate-map').removeClass('hidden');

            $('#create-event').find('#is-routine').on('change', function () {
                if ($('#is-routine').prop('checked')) {
                    console.log('test1');
                    $('#create-event').find('#frequency-field').removeClass('hidden');
                } else {
                    console.log('test2');
                    $('#create-event').find('#frequency-field').addClass('hidden');
                }

            });
            $('#create-event').find('.initiate-map').on('click', (e) => {
                console.log('here again')
                $('#create-event').find('#contain-maps').removeClass('hidden');
                $('#create-event').find('.initiate-map').addClass('hidden');
                mapsGraph();
            });
            $('#create-event').find('.destroy-map').on('click', (e) => {
                $('#create-event').find('#contain-maps').addClass('hidden');
                $('#create-event').find('.initiate-map').removeClass('hidden');
            });

        })


    });

    var fill_calendar = function (personnel_selected, data, list_view) {
        var calendar = $('#calendar').fullCalendar({
            header: {
                left: 'prev,today',
                center: 'title',
                right: 'next'

            },




            displayEventTime: true,

            eventRender: function (event, element, view) {
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


            eventClick: function (event) {
                var modal_class = '#event-modal';
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
        setInterval(function () {
            $(".success").fadeOut();
        }, 1000);
    }
</script>

@endsection
