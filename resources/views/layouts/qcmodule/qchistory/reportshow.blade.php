@extends('layouts.lab.layout.app', ['dataTable' => true, 'datePicker' => true, 'select2' => true])

@section('title2')
    <title>{{ $status ?? 'QC Reports' }} | QC Report</title>

    <style>
        .hidden {
            display: none;
        }


        .btn-white {
            background-color: white !important;
        }

        .text-bold {
            font-weight: 550;
        }
    </style>
@endsection
@section('content2')
    <main>
        <?php
    $items = array(
        array(
            'link' => route('dashboard-lab'),
            'name' => 'Dashboard',
            'icon' => null
        ),
        array(
            'link' => route('qc-reports'),
            'name' => 'QC Report',
            'icon' => null
        ),
        array(
            'link' => route('qc-result-show',['result_id'=>$results->id]),
            'name' => $results->analyte->analyte_code,
            'icon' => null
        )

    );
        ?>
        <x-bread-crumb :items="$items"></x-bread-crumb>
        <h4 class="p-2">
            <span><i class="mdi mdi-file-document-edit"></i> Qc Reports | {{ $results->analyte->code }}</span>

        </h4>

        <div class="card mt-4" style="box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;">
            <div class="card-header" style="font-size:20px; font-weight:580">
                Data
            </div>
            <div class="card-body">
                <canvas id="qcChart"></canvas>

            </div>
        </div>
    </main>
@endsection

@section('script2')

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const results = <?= json_encode($data) ?>; // Sample results
        const mean = <?= json_encode($results->robust_mean) ?>;
        const median = <?= json_encode($results->robust_median) ?>;
        const sd = <?= json_encode(number_format($results->robust_standard_deviation,4)) ?>;
        const cv = <?= number_format($results->robust_cv,4) ?>;
        const cvPercent = <?= number_format($results->robust_cv_percentage,4) ?>;

        const innerUpperLimit = mean + parseFloat(sd);  // e.g. 102.5
        const outerUpperLimit = mean + 2 * sd;  // e.g. 105
        const innerLowerLimit = mean - sd;  // e.g. 97.5
        const outerLowerLimit = mean - 2 * sd;  // e.g. 95

        console.log(innerUpperLimit)

        const labels = <?= json_encode($labels) ?>;

        const ctx = document.getElementById('qcChart').getContext('2d');
        const qcChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Results',
                        data: results,
                        borderColor: 'blue',
                        backgroundColor: 'blue',
                        fill: false,
                        tension: 0.3,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                    },
                    {
                        label: 'Mean',
                        data: Array(results.length).fill(mean),
                        borderColor: 'green',
                        borderDash: [5, 5],
                        fill: false,
                        pointRadius: 0,
                    },
                    {
                        label: 'Median',
                        data: Array(results.length).fill(median),
                        borderColor: 'purple',
                        borderDash: [2, 2],
                        fill: false,
                        pointRadius: 0,
                    },
                    {
                        label: 'Inner Upper Limit (+1 SD)',
                        data: Array(results.length).fill(innerUpperLimit),
                        borderColor: 'orange',
                        borderDash: [5, 5],
                        fill: false,
                        pointRadius: 0,
                    },
                    {
                        label: 'Outer Upper Limit (+2 SD)',
                        data: Array(results.length).fill(outerUpperLimit),
                        borderColor: 'red',
                        borderDash: [5, 5],
                        fill: false,
                        pointRadius: 0,
                    },
                    {
                        label: 'Inner Lower Limit (-1 SD)',
                        data: Array(results.length).fill(innerLowerLimit),
                        borderColor: 'orange',
                        borderDash: [5, 5],
                        fill: false,
                        pointRadius: 0,
                    },
                    {
                        label: 'Outer Lower Limit (-2 SD)',
                        data: Array(results.length).fill(outerLowerLimit),
                        borderColor: 'red',
                        borderDash: [5, 5],
                        fill: false,
                        pointRadius: 0,
                    },
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: `QC Chart | Mean: ${mean}, SD: ${sd}, CV: ${cv} (${cvPercent}%)`,
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    },
                    legend: {
                        position: 'bottom'
                    }
                },
                scales: {
                    y: {
                        title: {
                            display: true,
                            text: 'Measurement'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Sample'
                        }
                    }
                }
            }
        });
    </script>

@endsection