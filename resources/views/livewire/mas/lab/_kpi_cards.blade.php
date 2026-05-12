{{-- TAT KPI Scoreboard: 6 primary metric cards --}}
<div class="row mb-3">

    @php
        $metrics = [
            ['label' => 'Number of Params',         'value' => number_format($stats['testing_metrics']['total_params'] ?? 0),        'class' => 'metric-card',      'unit' => ''],
            ['label' => '% Tested vs Requested',    'value' => ($stats['testing_metrics']['tested_vs_requested'] ?? 0),              'class' => 'metric-card',      'unit' => '%'],
            ['label' => '% TAT Compliance (TES)',   'value' => ($stats['testing_metrics']['tat_compliance_tes'] ?? 0),               'class' => 'metric-card',      'unit' => '%'],
            ['label' => 'Avg TAT (Days)',            'value' => ($stats['testing_metrics']['avg_tat_tes'] ?? 0),                      'class' => 'metric-card',      'unit' => ' d'],
            ['label' => 'Avg Delivery TAT (Days)',  'value' => ($stats['testing_metrics']['avg_delivery_tat'] ?? 0),                 'class' => 'metric-card dark', 'unit' => ' d'],
            ['label' => '% TAT Compliance (Deliv)', 'value' => ($stats['testing_metrics']['delivery_compliance'] ?? 0),             'class' => 'metric-card dark', 'unit' => '%'],
        ];
    @endphp

    @foreach($metrics as $m)
        <div class="col-md-2 col-sm-4 col-6">
            <div class="{{ $m['class'] }}">
                <div class="metric-label">{{ $m['label'] }}</div>
                <div class="metric-value">{{ $m['value'] }}{{ $m['unit'] }}</div>
            </div>
        </div>
    @endforeach

</div>
