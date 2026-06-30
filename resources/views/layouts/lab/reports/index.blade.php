@extends('layouts.lab.layout.app')
@section('title2')
<title> Laboratory Reports — Sample Workflow KPIs </title>
@endsection
@section('content2')
<main>
    <?php
    $items = array(
        array(
            'link' => 'equipment-home',
            'name' => 'Equipment',
            'icon' => null
        ),
        array(
            'link' => null,
            'name' => 'Reports',
            'icon' => null
        ),

    );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    <h2 class="p-4 pb-2">
        <i class="mdi mdi-clipboard-text-multiple-outline"></i> Sample Workflow KPIs
    </h2>

    @include('layouts.lab.reports.partials.sample-workflow-kpi-metrics', [
        'metrics' => $metrics ?? [],
        'registrationKpiPeriod' => $registrationKpiPeriod ?? null,
        'laboratoryKpiPeriod' => $laboratoryKpiPeriod ?? null,
    ])

    {{--
    Legacy Batch / Sample / Profit report generator — dormant while KPI dashboard is active.
    Full preserved markup: resources/views/layouts/lab/reports/index-legacy-preserved.blade.php
    Restore by copying that file over index.blade.php and reverting SamplesReportsController::index().
    --}}
</main>
@endsection

{{-- Legacy script2 section preserved in index-legacy-preserved.blade.php --}}
