<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <?php $companyDetails = getCompanyDetails(); ?>
    <title>
        Print {{Auth::user()->name}} Tasks
    </title>
    <!-- Scripts -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.11.2/css/all.min.css" integrity="sha256-+N4/V/SbAFiW1MPBCXnfnP9QSN3+Keu+NlB+0ev/YKQ=" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/MaterialDesign-Webfont/5.3.45/css/materialdesignicons.min.css"/>
    
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <style type="text/css">
        .my-small-text {
            font-size: 12px;
        }
        p {
            margin-bottom: 0px;
            margin-top: 0px;
            font-size: 15px;
        }
    </style>
</head>

<body onload="window.print()" class="container">
    <div class="card mt-5">
        <div class="card-header">
        {!! $company->show_on_reports == 1 ? '<img src='.$company->logo.' style="height: 16%;position:absolute;top:0%" class="float-left mt-3" />':'' !!}
        <div class="details float-right" style="text-align: left;">
            <p><b>Name: </b>{{$user->name}}</p>
            <p><b>Email: </b>{{$user->email}}</p>

            <p><b>Position: </b>{{$position->name}}</p>
            <p><b>Tasks: </b>{{sizeof($events)}}</p>
        </div>
        
        </div>
        <div class="card-body" style="min-height:30vh">
            <div class="table-responive">
                <table class="table table-condensed table-stripped table-bordered my-small-text">
                    <thead class="bg-light">
                        <th>No</th>
                        <th>Title</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Created By</th>
                        <th>Client</th>
                        <th>Responsible Personnel</th>
                        <th>Description</th>
                        <th>Routine</th>
                        <th>Frequency</th>
                    </thead>
                    <tbody>
                        @foreach($events as $event)
                        <tr>
                            <td>{{$loop->iteration}}</td>
                            <td>{{$event->title}}</td>
                            <td>{{$event->start_date}}</td>
                            <td>{{$event->end_date}}</td>
                            <?php
                             $create = getUserById($event->created_by);
                             $client = getCrmCustomerByID($event->client_id);
                            ?>
                            <td>{{$create->name}}</td>
                            <td>{{$client->name}}</td>
                            <td>{{$event->responsible_name}}</td>
                            <td>{{$event->description}}</td>
                            
                            <td class="text-small text-center">{!! $event->is_routine == 1 ? '<i class="mdi mdi-marker-check text-success"></i>' : '<i class="mdi mdi-close-circle text-danger"></i>' !!}</td>
                            <td>{{$event->is_routine == 1 ? $event->frequency_name : 'N/a'}}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer text-center" style="font-size: 12px;">
            {{Auth::user()->name}}
            <p>&copy; {{$company->name}}<p>
        </div>
    </div>
</body>

</html