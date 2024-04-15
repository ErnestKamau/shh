@extends('prp::layouts.app',['dataTable'=>true,'select2'=>true])
@section('title2')
<style>
    .card {
        box-shadow: rgba(0, 0, 0, 0.15) 1.95px 1.95px 2.6px;
        text-decoration: none !important;
        color: black !important;
    }

    .card:hover {
        box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;
    }

    a {
        text-decoration: none !important;
        /* color: black !important; */
    }

    .header-area {
        text-decoration: underline;
    }

    .text-bold {
        font-weight: 550;
    }

    .btn-default {
        /* box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px; */
        box-shadow: rgba(0, 0, 0, 0.15) 1.95px 1.95px 2.6px;
    }

    .table-responsive {
        box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;
    }
</style>
@endsection
@section('content2')
<main>
    <?php
    $items = array(
        array(
            'link' => '/prp',
            'name' => 'PRP',
            'icon' => null,
        ),
        array(
            'link' => '/prp/report/Quality-Performance/Report',
            'name' => 'Quality Report',
            'icon' => null,
        ),
        array(
            'link' => '#',
            'name' => 'Harvesters Quality Report',
            'icon' => null,
        )

    )

    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    <h5 class="p-2 mt-2">
        <i class="mdi mdi-file-account-outline"></i> Harvester Based Quality Summary Reports
        <span class="btn btn-sm float-right btn-outline-primary" data-target="#generate_graph" data-toggle="modal"><i class="mdi mdi-cogs"></i> Generate Graph</span>
    </h5>

    <div class="header-5 mt-3 p-2">
        <p><b><u>Parameter Report</u></b></p>
        <div class="row p-1">
            <div class="col-md-3">
                <span class="text-muted"  style="font-weight:600" ><i class="mdi mdi-chevron-right"></i> From Date</span><br>
                <span  class="pl-3" >{{$from_date}}</span>
            </div>
            <div class="col-md-3">
                <span class="text-muted"  style="font-weight:600"><i class="mdi mdi-chevron-right"></i> To Date</span> <br>
                <span class="pl-3">{{$to_date}}</span>
            </div>
            <div class="col-md-3">
                <span class="text-muted"  style="font-weight:600"><i class="mdi mdi-chevron-right"></i> Source</span><br>
                <span class="pl-3">{{$source}}</span>
            </div>
           

        </div>
      
    </div>
    <div id="image-chart" class="hidden" style="width:100%;height:400px;margin-bottom:150px" >
        <div id="bar-chart" style="width:100%;height:400px" ></div>

    </div>
    
    <div class="table-responsive p-3 mt-3">
        <form action="">

            <table class="table table-condensed table-bordered table-hover table-sm">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Harvester No</th>
                        <th>Name</th>
                        <th>From Date</th>
                        <th>To Date</th>
                        <th>Inspected %</th>
                        @foreach($qualities as $quality)
                        <th>{{$quality}}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                   @foreach($data['data'] as $har_data)
                   <tr>
    
                       <td> <input type="checkbox" data-value="{{$har_data['har_no']}}" name="harvester_graph_select[]" value="{{$har_data['har_no']}}" class="harvester_graph"></td>
                       <td>{{$har_data['name']}}</td>
                       <td>{{$har_data['har_no']}}</td>
                       <td>{{$from_date}}</td>
                       <td>{{$to_date}}</td>
    
                       <td></td>
                       @foreach($qualities as $quality)
                       <td>{{$har_data['quality'][$quality]}}</td>
                       @endforeach
                   </tr>
                   @endforeach
                   
                </tbody>
            </table>
        </form>
    </div>

</main>
@endsection
@section('script2')
<div class="modal fade" id="generate_graph" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">

            </div>
            <div class="modal-footer">
                <span class="btn btn-sm text-danger btn-default" data-dismiss="modal">Close</span>
            </div>
        </div>
    </div>
</div>

<div id="graph_data" data-graphdata="{{json_encode($data['graph_data'])}}"></div>

<script src="http://cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.min.js" integrity="sha512-6Cwk0kyyPu8pyO9DdwyN+jcGzvZQbUzQNLI0PadCY3ikWFXW9Jkat+yrnloE63dzAKmJ1WNeryPd1yszfj7kqQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.css" integrity="sha512-fjy4e481VEA/OTVR4+WHMlZ4wcX/+ohNWKpVfb7q+YNnOCS++4ZDn3Vi6EaA2HJ89VXARJt7VvuAKaQ/gs1CbQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.js" integrity="sha512-CYkt9kgBjbQrIKQGbyezfkmhmmFwMF2VVZjdGgYJJm3KnV53Ao+aFnndz2YbmMX2Y8XGBCUzBxFTqg2LAuIJzw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<script>
$(()=>{
    var generateGraphBody = (data) => {
            var body = $(`
            <div class="alert alert-primary p-2">
                Confirm you want to generate Harvester based graph using the following harvester (s) Data
                <div class="row"></div>
            </div>

        `).clone()
            $.each(data, (i, obj) => {
                var column = `<div class="col-md-3"><i class="mdi mdi-chevron-right"></i> ${obj}</div>`;
                $(body).find('.row').append(column);
            });
            return body;
        }

        function dynamicColors() {
            var r = Math.floor(Math.random() * 255);
            var g = Math.floor(Math.random() * 255);
            var b = Math.floor(Math.random() * 255);
            return "rgba(" + r + "," + g + "," + b + ")";
        }

        function poolColors(a) {
            var pool = [];
            for (i = 0; i < a; i++) {
                pool.push(dynamicColors());
            }
            return pool;
        }
        let generateGraphData = (columns) => {
            $('#image-chart').removeClass('hidden');
            $('#bar-chart').empty();
            var graph_data = $('#graph_data').data('graphdata')
            var column = columns
    
            config = {
                data: graph_data,
                xkey: 'name',
                ykeys: column,
                labels: column,
                barColors: poolColors(column.length),
                xLabelAngle: 45,

            };

            config.element = 'bar-chart';
            Morris.Bar(config);
            $('svg').height(700);
        }

        $('#generate_graph').on('show.bs.modal', () => {
            var variety_data = [];
            if ($("input[name='harvester_graph_select[]']:checked").length > 0) {
                $.each($("input[name='harvester_graph_select[]']:checked"), (i, obj) => {
                    $value = $(obj).data('value')
                    variety_data.push($value);
                });

            };
            var body = generateGraphBody(variety_data);
            $('#generate_graph').find('.modal-body').empty();
            $('#generate_graph').find('.modal-body').append(body);
            generateGraphData(variety_data);

        })
})


</script>

@endsection
