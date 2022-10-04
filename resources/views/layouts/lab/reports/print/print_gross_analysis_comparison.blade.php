<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <?php $companyDetails = getCompanyDetails(); ?>
    <title> Gross Profit Analysis Comparison Report </title>
    <!-- Scripts -->

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/MaterialDesign-Webfont/5.3.45/css/materialdesignicons.min.css"/>
    

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.js" integrity="sha512-s+xg36jbIujB2S2VKfpGmlC3T5V2TF3lY48DX7u2r9XzGzgPsa6wTpOQA7J9iffvdeBN0q9tKzRxVxw1JviZPg==" crossorigin="anonymous"></script>

    <style type="text/css">
        

        .my-small-text {
            font-size: 13px !important;
        }

       
        .dt-buttons {
            display: none;
        }
    </style>
    <style type="text/css">
        .my-small-text {
            font-size: 12px;
        }
        .filters {
            display: flex;
            flex-wrap: nowrap;
        }
    </style>
</head>

<body>
    <main>
        <?php
        $check = getSystemConfiguration('display_system_logo');
        
        ?>
        <div class="card">
            <div class="card-header">
                <h4 class="card-title" style="height: 40px;">

                <img src="/images/imara-sys.png" style="height:80%" class="float-right">
                
                <center class="ml-5" style="font-weight: 900;">Lab Reports | Gross Profit Analysis Reports </center>
                <img src='{{$check_company_logo[0]->logo}}' style="height: 2.5%;position:absolute;top:0%" class="float-left mt-2" />

                </h4>
                <hr>
                <h5 class="mb-1 mt-1" style="margin-right: 10%; font-weight:600;font-size:15px">Report Filters: </h5>
               
            </div>
        </div>
        </div>
        <div class="card-body">
            <div class="card tab-card">
                <div class=" bg-default no-overflow">
                    <div class="card-head-sm p-3 border-bottom">
                        <h5 class="card-title">Analysis Types Comparison Graph</h5>

                    </div>
                    <div class="card-body">
                        <canvas id="gross-analysis-comparison" style="height:70% !important"></canvas>
                    </div>
                </div>
            </div>
            <div class="table-responsive mt-5">
                <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
                    <h5>Gross Profit Analysis Types Report</h5>

                    <thead class="bg-light p-2">
                        <tr>
                            <th>No</th>
                            <th>Analysis Type</th>
                            <th>Samples Submited</th>

                            <th>Currency</th>
                            <th>Total Cost Price</th>
                            <th>Total Selling Price</th>
                            <th>Total Profit</th>
                            <th>Profit Margin</th>

                        </tr>
                    </thead>
                    <tbody>


                        @foreach($analysis_types as $analysis)
                        <tr>

                            <td>{{$loop->iteration}}</td>
                            <td>{{$analysis->name}}</td>
                            <td>{{$analysis->samples}}</td>
                            <td>{{$analysis->currency}}</td>
                            <td class="text-right">{{number_format($analysis->total_costing ,2) }}</td>
                            <td class="text-right">{{number_format($analysis->total_selling,2) }}</td>
                            <td class="text-right">{{number_format($analysis->profits ,2)}}</td>
                            <td>{{number_format($analysis->profit_margin,3)}}%</td>

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
var analysis_data =' . json_encode($analysis_data) . ';
</script>
';
    ?>
    
</body>
<script>
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

    let mychart = document.getElementById('gross-analysis-comparison').getContext('2d');
    var analysis_names = Object.keys(analysis_data);
    var analysis_profit = Object.values(analysis_data);


    let massPopChart = new Chart(mychart, {
        type: 'bar',
        data: {
            labels: analysis_names,
            datasets: [{
                label: 'Profit',
                data: analysis_profit,
                backgroundColor: poolColors(analysis_profit.length),
                borderColor: poolColors(analysis_profit.length),
                hoverBorderWidth: 1,
                hoverBorderColor: '#000',
            }],
        },
        options: {
            title: {
                display: true,
                text: 'Gross Analysis Comparison',
                fontSize: 15,
                fontColor: '#000',
            },
            legend: {
                display: false,
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

    $(function() {
        $('#add-filter').click(function(event) {
            $('#Filter-Form').toggle();
        });
    });
</script>

</html>