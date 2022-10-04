@extends('layouts.crm.dashboard.layout.app', ['dataTable'=>true, 'select2'=>true])
@section('title2')
    <title>Dashboard | CRM </title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.js" integrity="sha512-s+xg36jbIujB2S2VKfpGmlC3T5V2TF3lY48DX7u2r9XzGzgPsa6wTpOQA7J9iffvdeBN0q9tKzRxVxw1JviZPg==" crossorigin="anonymous"></script>
    <style type="text/css">
        .my-card{
            position:absolute;
            left:40%;
            top:-20px;
            border-radius: 50%;
        }
        #activity-graph{
            height:250px;
        }
    </style>
@endsection
@section('content2')
<main>

    <?php 
        $items = array(
            array(
                'link'=>null,
                'name'=>'Configurations',
                'icon'=>null
            )
            );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    <h2 class="p-1">
    <i class="mdi mdi-desktop-mac-dashboard"></i> Dashboard
    </h2><br>
    <div class="row mb-3">
        <div class="col-xl-3 col-sm-6 ">
            <div class="card  bg-success text-white text-center  no-overflow" style="height:100%">
                <div class="card-body bg-success">
                    <div class="rotate">
                        <i class="mdi mdi-test-tube fa-4x"></i>
                    </div>
                    <h6 class="text-uppercase">Samples Submitted</h6>
                    <br><br>
                    <h1 class="display-4">{{ $samples_submitted }}</h1>
                </div>
            </div>
        </div>


        <div class="col-xl-3 col-sm-6">
            <div class="card bg-danger text-white text-center h-100 no-overflow">
                <div class="card-body bg-danger">
                    <div class="rotate">
                        <i class="fas fa-list fa-4x"></i>
                    </div>
                    <h6 class="text-uppercase">In Lab</h6>
                    <br><br>
                    <h1 class="display-4">{{ $samples_lab }}</h1>
                </div>
            </div>
        </div>


        <div class="col-xl-3 col-sm-6">
            <div class="card bg-info text-white text-center h-100 no-overflow">
                <div class="card-body bg-info">
                    <div class="rotate">
                        <i class="mdi mdi-test-tube fa-4x"></i>
                    </div>
                    <h6 class="text-uppercase">Samples Completed</h6>
                    <br><br>
                    <h1 class="display-4">{{$samples_complete->count() }}</h1>
                </div>
            </div>
        </div>


        <div class="col-xl-3 col-sm-6 ">
            <div class="card bg-dark text-white h-100  text-center no-overflow">
                <div class="card-body bg-dark">
                    <div class="rotate">
                        <i class="fas fa-list fa-4x"></i>
                    </div>
                    <h6 class="text-uppercase">Complaints</h6>
                    <br><br>
                    <h1 class="display-4">{{$complaint->count() }}</h1>
                </div>
            </div>
        </div>
    </div>
    <div class="row mb-3" >
        <div class="col-xl-6 col-sm-12" >
            <div class="card bg-default no-overflow">
                <div class="card-head-sm p-3 border-bottom">
                    <h5>
                        <i class="mdi mdi-map-marker"></i>  Location Map
                        <small class="float-right text-info"><i class="fas fa-calendar"></i></small>
                    </h5>
                </div>
                <div class="card-body">
                    <div id="sample-maps" style="width: 100%;height:300px"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-6 col-sm-12 " ">
            <div class="card bg-default no-overflow">
                <div class="card-head-sm p-3 border-bottom">
                    <h5>
                        <i class="fas fa-line-chart"></i> Samples tested against CRM units
                        <form method="GET" action="{{ route('client-dashboard-home') }}" class="float-right text-info">
                            <div class="input-group">
                                <input type="number" max="2100" name="unit" value="" min="2000" style="border:0px solid;border-bottom:1px solid" class="form-control" placeholder="Search by Year">
                                <div class="input-group-append">
                                <button class="btn btn-default btn-sm" type="submit ">
                                    <i class="fa fa-search"></i>
                                </button>
                                </div>
                            </div>
                        </form>
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="sample-crm-graph" style="height: 400px !important"> </canvas>
                </div>
            </div>
        </div>

      
    </div>
    <div class="row mb-3">
        <div class="col-xl-6 col-sm-12 ">
            <div class="card bg-default no-overflow">
                <div class="card-head-sm p-3 border-bottom">
                    <h5>
                        <i class="fas fa-line-chart"></i> Samples Tested By Month.
                        <form method="GET" action="{{ route('client-dashboard-home') }}" class="float-right text-info">
                            <div class="input-group">
                                <input type="number" name="month" value="" max="2100" min="2000" style="border:0px solid;border-bottom:1px solid" class="form-control" placeholder="Search by Year">
                                <div class="input-group-append">
                                <button class="btn btn-default btn-sm" type="submit">
                                    <i class="fa fa-search"></i>
                                </button>
                                </div>
                            </div>
                        </form>
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="sample-graph"></canvas>
                </div>
            </div>
        </div>
    <!-- </div> -->
   <!-- <div class="row mb-5"> -->
    <div class="col-xl-6 col-sm-12">
            <div class="card bg-default no-overflow">
                <div class="card-head-sm p-3 border-bottom">
                    <h5>
                        <i class="fas fa-line-chart"></i> Sample Types Tested
                        <form method="GET" action="{{ route('client-dashboard-home') }}" class="float-right text-info">
                            <div class="input-group">
                                <input type="number" name="sample_type" value="" max="2100" min="2000" style="border:0px solid;border-bottom:1px solid" class="form-control" placeholder="Search by Year">
                                <div class="input-group-append">
                                <button class="btn btn-default btn-sm" type="submit">
                                    <i class="fa fa-search"></i>
                                </button>
                                </div>
                            </div>
                        </form>
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="myChart"></canvas>
                </div>
            </div>
        </div>
   </div>

    <div class="card tab-card">
		<div class="card-header tab-card-header">
			<ul class="nav nav-tabs card-header-tabs" id="equipment-tab" role="tablist">
				<li class="nav-item">
					<a class="nav-link active" id="samples" data-toggle="tab" href="#samples-tab" role="tab" aria-controls="samples" aria-selected="true"><i class="mdi mdi-test-tube"></i> Samples </a>
				
			</ul>
		</div>
        <div class="table-responsive bg-light p-4">
			<table class="table table-condensed my-small-text table-bordered table-sm">
				<thead>
					<th></th>
					<th>Priority</th>
					<th>Batch Code</th>
					<th nowrap>Receipt Date</th>
					<th nowrap>Date Collected</th>
					<th nowrap>Target Date</th>
					<th nowrap>Status Days</th>
					<th>Samples</th>
					<th>Client</th>
					<th>Client Unit</th>
					<th>Lab</th>
					<th nowrap>Sample Type</th>
					<th>Ref No</th>
					<th nowrap>Tracking Stage</th>
					<th>Routine</th>
					<th>Routine Frequency</th>
					
				</thead>
				<tbody>
					@foreach ($sample_batches as $item)
                    <?php
                   
                   $a = $item->get_date('Target Date');
                   if(isset($a->id)){

                       $target_date = $item ? date('Y-m-d', strtotime($a['date'])): '';

                       $target_date = Carbon\Carbon::parse($target_date);
   
                       $now = Carbon\Carbon::now();
                       $diff = $now->diffInDays($target_date );
   
                       if ($target_date->greaterThan($now)) {
                           $diff = 0 - $diff;
                       }
                   }

               ?>
						<tr class="batch-row  crm-customer-{{ $item->client->id }}"
							data-class="{{ $item->client->id }}">
							<td>{{$loop->iteration}}</td>
							<td nowrap>{!! $item->priority != "Normal" ? '<i class="mdi mdi-star text-danger"></i>' : '' !!} {{ $item->priority }}</td>
							<td><a href="{{ route('view-batch-details', ['batch'=>$item->id, 'client'=>$item->client->id]) }}">{{ $item->batch_code }}</a></td>
							<td nowrap>{{ date('Y-m-d', strtotime($item->receipt_date)) }}</td>
							<td nowrap>{{ date('Y-m-d', strtotime($item->date_collected)) }}</td>
							<td nowrap>{{ date('Y-m-d', strtotime($target_date ?? '')) }}</td>
							<td nowrap>{{ number_format($diff ?? 0, 0) }} Day(s)</td>
							<td>{{ $item->samples->count() }}</td>
							<td nowrap>{{ $item->client->name }}</td>
							<td nowrap>{{ $item->crm_unit_name }}</td>
							<td nowrap>{{ implode(", ", $item->labs(true)) }}</td>
							<td nowrap>{{ $item->sample_type->name ?? '' }}</td>
							<td nowrap>{{ $item->reference_number ?? 'n/a' }}</td>
							<td nowrap>{{ $item->status ?? 'n/a' }}</td>
							<td>{{ $item->is_routine == 1 ? 'Yes' : 'No' }}</td>
							<td>{{ $item->is_routine == 1 ? number_format($item->routine_frequency,0).' days' : 'n/a' }}</td>
							
						</tr>
					@endforeach
				</tbody>
			</table>
    </div>
	</div>

</main>
<?php
echo '
<script type="text/javascript">
var namearr ='.json_encode($names) .';
var totalarr='. json_encode($total) .';
var result ='. json_encode($res) .';
var gps ='. json_encode($gps_count) .';
var unit_namearr ='. json_encode($unit_name) .';
</script>
';

?>
<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&key=AIzaSyBqS4AEZ-gVeXjG794Rh0eTd6yvdfMKTjg&sensor=false" type="text/javascript"></script>
<script>
    let mychart = document.getElementById('myChart').getContext('2d');
    let mychart2 = document.getElementById('sample-graph').getContext('2d');
    let mychart3 = document.getElementById('sample-crm-graph').getContext('2d');
    var total = Object.values(totalarr);
    var results = Object.values(result);
    let massPopchart = new Chart(mychart,{
        type:'bar',
        data:{
            labels:namearr,
            datasets:[{
                label:'Samples',
                data:total,
                backgroundColor: 'grey',
                hoverBorderWidth:1,
                hoverBorderColor:'#000',
                // backgr
            }]
        },
        options:{
            title:{
                display:true,
                text:'Samples tested aganist sample types',
                fontSize:15,
                fontColor:'#000'
            },
            legend:{
                display:false,
            },
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true
                    }
                }]
            }
        }
    });

    
    let massPop = new Chart(mychart2,{
        type:'bar',
        data:{
            labels:['January','February','March','April','May','June','July','August','September','October','November','December'],
            datasets:[{
                label:'Samples',
                data:results,
                backgroundColor:'rgba(54,162,235,0.6)',
                hoverBorderWidth:1,
                hoverBorderColor:'#000',
            }]
        },
        options:{
            title:{
                display:true,
                text:'Samples tested in a month',
                fontSize:15,
                fontColor:'#000'
            },
            legend:{
                display:false,
                
            },
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true
                    }
                }]
            }
        }
    })

    
    // console.log(result);
    var mapProp= {
				center:new google.maps.LatLng(-1.247125519439578,36.742261815816164),
				zoom:5.5,
			};
    map = new google.maps.Map(document.getElementById('sample-maps'),mapProp);
    console.log(gps);
    var lat = Object.keys(gps);
    for(var i=0;i<lat.length;i++){
        var latit = lat[i].substring(0,lat[i].length-1);
        
        var lati = latit.split(',');

        var latitude = parseFloat(lati[1]);
        var longitude = parseFloat(lati[0]);
        if(latitude !== NaN && longitude !== NaN){

            var marker = new google.maps.Marker({
                position: new google.maps.LatLng(latitude,longitude),
                title:`Total samples ${gps[lat[i]]}`,
            });
            marker.setMap(map)
        }
        // console.log(gps[lat[i]]);
    }


    var unit_names = Object.keys(unit_namearr);
    var unit_values = Object.values(unit_namearr);
    // console.log(unit_names)
    let masspop2 = new Chart(mychart3,{
        type:'bar',
        data:{
            labels:unit_names,
            datasets:[{
                label:'Samples',
                data:unit_values,
                backgroundColor:'rgba(255,159,64,0.6)',
                hoverBorderWidth:1,
                hoverBorderColor:'#000',
            }]

        },
        options:{
            title:{
                display:true,
                text:'Samples tested against crm units',
                fontSize:15,
                fontColor:'#000'
            },
            legend:{
                display:false,
            },
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true
                    }
                }]
            }
        }
    })
    
    
    // console.log(lat.length);
</script>
@endsection