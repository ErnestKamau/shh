@extends('layouts.equipment.layout.app', ['dataTable'=>true])

@section('title2')
<title>Equipment Daily Log</title>
@endsection

@section('content2')
<main>
    <?php
    $items = [
        [
            'link' => route('equipment-home'),
            'name' => 'Equipment Management',
            'icon' => null,
        ],
        [
            'link' => null,
            'name' => 'Daily Log',
            'icon' => null,
        ],
    ];
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>

    <h2 class="p-4">
        <i class="mdi mdi-notebook-check-outline"></i> Equipment Daily Log
    </h2>

    @php
        $freqLabels = [
            1 => 'Once a Day',
            2 => 'Twice a Day',
            3 => 'Three Times a Day',
            4 => 'Four Times a Day',
            5 => 'Five Times a Day',
            6 => 'Six Times a Day',
        ];
        // Determine the first tab that has equipment (default to 1)
        $defaultTab = 1;
        foreach ($freqLabels as $freq => $label) {
            if (!empty($equipmentByFrequency[$freq]) && $equipmentByFrequency[$freq]->count() > 0) {
                $defaultTab = $freq;
                break;
            }
        }
    @endphp

    <div class="px-4 pb-4">
        <!-- Frequency Tabs -->
        <ul class="nav nav-tabs" id="freqTabs" role="tablist" style="border-bottom: 2px solid #dee2e6;">
            @foreach($freqLabels as $freq => $label)
            @php $count = isset($equipmentByFrequency[$freq]) ? $equipmentByFrequency[$freq]->count() : 0; @endphp
            <li class="nav-item" role="presentation">
                <a class="nav-link {{ $freq === $defaultTab ? 'active' : '' }}"
                   id="freq-tab-{{ $freq }}"
                   data-toggle="tab"
                   href="#freq-pane-{{ $freq }}"
                   role="tab"
                   aria-controls="freq-pane-{{ $freq }}"
                   aria-selected="{{ $freq === $defaultTab ? 'true' : 'false' }}">
                    {{ $label }}
                    <span class="badge {{ $count > 0 ? 'badge-primary' : 'badge-secondary' }} ml-1">{{ $count }}</span>
                </a>
            </li>
            @endforeach
        </ul>

        <!-- Tab Panes -->
        <div class="tab-content" id="freqTabContent">
            @foreach($freqLabels as $freq => $label)
            @php $items2 = isset($equipmentByFrequency[$freq]) ? $equipmentByFrequency[$freq] : collect(); @endphp
            <div class="tab-pane fade {{ $freq === $defaultTab ? 'show active' : '' }}"
                 id="freq-pane-{{ $freq }}"
                 role="tabpanel"
                 aria-labelledby="freq-tab-{{ $freq }}">

                <div class="card border-0 shadow-sm mt-0" style="border-top-left-radius:0; border-top-right-radius:0;">
                    <div class="card-body p-0">
                        @if($items2->isEmpty())
                        <div class="text-center text-muted py-5">
                            <i class="mdi mdi-information-outline" style="font-size:2rem;"></i>
                            <p class="mt-2 mb-0">No equipment configured for <strong>{{ $label }}</strong> logging.</p>
                            <small>Assign a logging frequency to equipment in the Equipment Management module.</small>
                        </div>
                        @else
                        <div class="table-responsive">
                            <table class="table table-condensed table-striped table-hover table-bordered table-sm my-small-text" id="daily-log-table-{{ $freq }}">
                                <thead class="bg-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Equipment Name</th>
                                        <th nowrap>Equipment Number</th>
                                        <th>Make</th>
                                        <th>Model</th>
                                        <th nowrap>Serial Number</th>
                                        <th>Assigned Department</th>
                                        <th>Assigned Employee</th>
                                        <th nowrap>Nature of Results</th>
                                        <th nowrap>Value Type</th>
                                        <th>Value</th>
                                        <th>Tolerance</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($items2 as $index => $item)
                                    @php
                                        $valueDisplay = '-';
                                        if ($item->daily_log_value_type === 'constant') {
                                            $valueDisplay = $item->daily_log_expected_value
                                                . ($item->daily_log_reporting_unit ? ' ' . $item->daily_log_reporting_unit : '');
                                        } elseif ($item->daily_log_value_type === 'range') {
                                            $valueDisplay = $item->daily_log_expected_min . ' – ' . $item->daily_log_expected_max
                                                . ($item->daily_log_reporting_unit ? ' ' . $item->daily_log_reporting_unit : '');
                                        }
                                        $toleranceDisplay = ($item->daily_log_tolerance && $item->daily_log_nature === 'quantitative')
                                            ? '±' . $item->daily_log_tolerance . '%'
                                            : '-';
                                    @endphp
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td nowrap>
                                            <a href="{{ route('view-equipment', ['equipmentId' => $item->id, 'from' => 'daily-log']) }}">{{ $item->name }}</a>
                                        </td>
                                        <td>{{ $item->equipment_number }}</td>
                                        <td>{{ $item->make }}</td>
                                        <td>{{ $item->model }}</td>
                                        <td>{{ $item->serial_number }}</td>
                                        <td>{{ getInventoryDepartmentByid($item->assigned_department)->name ?? '-' }}</td>
                                        <td>{{ optional(getUserById($item->assigned_employee_id))->name ?? '-' }}</td>
                                        <td>{{ $item->daily_log_nature ? ucfirst($item->daily_log_nature) : '-' }}</td>
                                        <td>{{ $item->daily_log_value_type ? ucfirst($item->daily_log_value_type) : '-' }}</td>
                                        <td nowrap>{{ $valueDisplay }}</td>
                                        <td>{{ $toleranceDisplay }}</td>
                                        <td class="text-center">
                                            {!! $item->active
                                                ? '<i class="mdi mdi-marker-check text-success" title="Active"></i>'
                                                : '<i class="mdi mdi-close-circle text-danger" title="Inactive"></i>' !!}
                                        </td>
                                        <td nowrap>
                                            <a class="btn btn-outline-success btn-sm" href="{{ route('view-equipment', ['equipmentId' => $item->id, 'from' => 'daily-log']) }}" data-toggle="tooltip" title="View Equipment">
                                                <i class="mdi mdi-eye-outline"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</main>
@endsection

@section('script2')
<script>
$(document).ready(function () {
    var freqLabels = {1:'Once a Day',2:'Twice a Day',3:'Three Times a Day',4:'Four Times a Day',5:'Five Times a Day',6:'Six Times a Day'};

    // Initialize DataTables for each non-empty frequency table
    @foreach($freqLabels as $freq => $label)
    @if(isset($equipmentByFrequency[$freq]) && $equipmentByFrequency[$freq]->count() > 0)
    $('#daily-log-table-{{ $freq }}').DataTable({
        pageLength: 25,
        order: [[1, 'asc']],
        columnDefs: [{ orderable: false, targets: [0, 13] }]
    });
    @endif
    @endforeach

    // Adjust DataTable columns on tab switch to fix column widths
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        var targetPane = $(e.target).attr('href');
        var table = $(targetPane).find('table.dataTable');
        if (table.length) {
            $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
        }
    });
});
</script>
@endsection


