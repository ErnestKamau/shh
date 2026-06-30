@extends('layouts.equipment.layout.app', ['dataTable'=>true])

@section('title2')
<title>Equipment Checks</title>

<style>
    /* Equipment Daily Log View Styling */
    .eq-hero-card {
        background: linear-gradient(135deg, #ffffff 0%, #f8f9ff 100%);
        border-radius: 16px !important;
        box-shadow: 0 2px 8px rgba(0, 89, 187, 0.08) !important;
    }

    .eq-kicker {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #9ca3af;
        font-weight: 600;
    }

    .eq-hero-title {
        font-size: 28px;
        font-weight: 700;
        color: #1f2937;
        line-height: 1.3;
    }

    .eq-main-card {
        background: #ffffff;
        border-radius: 16px !important;
        border: 1px solid #e5e7eb !important;
        overflow: hidden;
    }

    .eq-main-header {
        background: #f9fafb !important;
        border-bottom: 1px solid #e5e7eb !important;
        padding: 0 !important;
    }

    .eq-main-tabs {
        border: none !important;
        margin: 0;
        padding: 0 20px;
    }

    .eq-main-tabs .nav-link {
        color: #6b7280 !important;
        border: none !important;
        border-bottom: 3px solid transparent !important;
        padding: 16px 12px !important;
        font-weight: 500;
        font-size: 14px;
        transition: all 0.3s ease;
    }

    .eq-main-tabs .nav-link:hover {
        color: #0059bb !important;
        border-bottom-color: #0059bb !important;
    }

    .eq-main-tabs .nav-link.active {
        color: #0059bb !important;
        border-bottom-color: #0059bb !important;
        background: transparent !important;
    }

    .eq-main-body {
        padding: 0 !important;
    }

    .tab-content .tab-pane {
        padding: 20px;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .eq-hero-title {
            font-size: 20px;
        }

        .eq-main-tabs {
            padding: 0 12px;
        }

        .eq-main-tabs .nav-link {
            padding: 12px 8px !important;
            font-size: 12px;
        }
    }
</style>
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
            'name' => 'Equipment Checks',
            'icon' => null,
        ],
    ];
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>

    <!-- Hero Header -->
    <div class="row mb-4 px-4 pt-4">
        <div class="col-12">
            <div class="card border-0 eq-hero-card">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap: 12px;">
                        <div>
                            <div class="eq-kicker mb-1">Equipment Monitoring</div>
                            <h2 class="mb-1 eq-hero-title">
                                <i class="mdi mdi-notebook-check-outline text-primary"></i>
                                Equipment Checks
                            </h2>
                            <p class="text-muted mb-0">Record and monitor equipment readings</p>
                        </div>
                        <div class="d-flex align-items-center" style="gap: 8px;">
                            <span class="badge badge-light border px-3 py-2">Frequency Based</span>
                            <a href="{{ route('equipment-home') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="mdi mdi-arrow-left"></i> Back
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
        <!-- Frequency Tabs Container -->
        <div class="card shadow-sm border-0 eq-main-card">
            <div class="card-header bg-light border-0 eq-main-header">
                <ul class="nav nav-tabs eq-main-tabs" id="freqTabs" role="tablist">
                    @foreach($freqLabels as $freq => $label)
                    @php $count = isset($equipmentByFrequency[$freq]) ? $equipmentByFrequency[$freq]->count() : 0; @endphp
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $freq === $defaultTab ? 'active' : '' }}"
                           id="freq-tab-{{ $freq }}"
                           data-toggle="tab"
                           data-target="#freq-pane-{{ $freq }}"
                           type="button"
                           role="tab"
                           aria-controls="freq-pane-{{ $freq }}"
                           aria-selected="{{ $freq === $defaultTab ? 'true' : 'false' }}">
                            {{ $label }}
                            <span class="badge {{ $count > 0 ? 'badge-primary' : 'badge-secondary' }} ml-2">{{ $count }}</span>
                        </button>
                    </li>
                    @endforeach
                </ul>
            </div>
            <div class="card-body eq-main-body p-0">

                <!-- Tab Panes -->
                <div class="tab-content" id="freqTabContent">
                    @foreach($freqLabels as $freq => $label)
                    @php $items2 = isset($equipmentByFrequency[$freq]) ? $equipmentByFrequency[$freq] : collect(); @endphp
                    <div class="tab-pane fade {{ $freq === $defaultTab ? 'show active' : '' }}"
                         id="freq-pane-{{ $freq }}"
                         role="tabpanel"
                         aria-labelledby="freq-tab-{{ $freq }}">
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
                                            <a href="{{ route('view-equipment', ['equipmentId' => $item->id, 'from' => 'equipment-checks']) }}">{{ $item->name }}</a>
                                        </td>
                                        <td>{{ $item->equipment_number }}</td>
                                        <td>{{ $item->make }}</td>
                                        <td>{{ $item->model }}</td>
                                        <td>{{ $item->serial_number }}</td>
                                        <td>{{ getInventoryDepartmentByid($item->assigned_department)?->name ?? $item->assigned_department ?? '-' }}</td>
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
                                            <a class="btn btn-outline-success btn-sm" href="{{ route('view-equipment', ['equipmentId' => $item->id, 'from' => 'equipment-checks']) }}" data-toggle="tooltip" title="View Equipment">
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
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</main>

<style>
    /* Equipment Daily Log View Styling */
    .eq-hero-card {
        background: linear-gradient(135deg, #ffffff 0%, #f8f9ff 100%);
        border-radius: 16px !important;
        box-shadow: 0 2px 8px rgba(0, 89, 187, 0.08) !important;
    }

    .eq-kicker {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #9ca3af;
        font-weight: 600;
    }

    .eq-hero-title {
        font-size: 28px;
        font-weight: 700;
        color: #1f2937;
        line-height: 1.3;
    }

    .eq-main-card {
        background: #ffffff;
        border-radius: 16px !important;
        border: 1px solid #e5e7eb !important;
        overflow: hidden;
    }

    .eq-main-header {
        background: #f9fafb !important;
        border-bottom: 1px solid #e5e7eb !important;
        padding: 0 !important;
    }

    .eq-main-tabs {
        border: none !important;
        margin: 0;
        padding: 0 20px;
    }

    .eq-main-tabs .nav-link {
        color: #6b7280 !important;
        border: none !important;
        border-bottom: 3px solid transparent !important;
        padding: 16px 12px !important;
        font-weight: 500;
        font-size: 14px;
        transition: all 0.3s ease;
    }

    .eq-main-tabs .nav-link:hover {
        color: #0059bb !important;
        border-bottom-color: #0059bb !important;
    }

    .eq-main-tabs .nav-link.active {
        color: #0059bb !important;
        border-bottom-color: #0059bb !important;
        background: transparent !important;
    }

    .eq-main-body {
        padding: 0 !important;
    }

    .tab-content .tab-pane {
        padding: 20px;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .eq-hero-title {
            font-size: 20px;
        }

        .eq-main-tabs {
            padding: 0 12px;
        }

        .eq-main-tabs .nav-link {
            padding: 12px 8px !important;
            font-size: 12px;
        }
    }
</style>
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

<style>
    /* Equipment Daily Log View Styling */
    .eq-hero-card {
        background: linear-gradient(135deg, #ffffff 0%, #f8f9ff 100%);
        border-radius: 16px !important;
        box-shadow: 0 2px 8px rgba(0, 89, 187, 0.08) !important;
    }

    .eq-kicker {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #9ca3af;
        font-weight: 600;
    }

    .eq-hero-title {
        font-size: 28px;
        font-weight: 700;
        color: #1f2937;
        line-height: 1.3;
    }

    .eq-main-card {
        background: #ffffff;
        border-radius: 16px !important;
        border: 1px solid #e5e7eb !important;
        overflow: hidden;
    }

    .eq-main-header {
        background: #f9fafb !important;
        border-bottom: 1px solid #e5e7eb !important;
        padding: 0 !important;
    }

    .eq-main-tabs {
        border: none !important;
        margin: 0;
        padding: 0 20px;
    }

    .eq-main-tabs .nav-link {
        color: #6b7280 !important;
        border: none !important;
        border-bottom: 3px solid transparent !important;
        padding: 16px 12px !important;
        font-weight: 500;
        font-size: 14px;
        transition: all 0.3s ease;
    }

    .eq-main-tabs .nav-link:hover {
        color: #0059bb !important;
        border-bottom-color: #0059bb !important;
    }

    .eq-main-tabs .nav-link.active {
        color: #0059bb !important;
        border-bottom-color: #0059bb !important;
        background: transparent !important;
    }

    .eq-main-body {
        padding: 0 !important;
    }

    .tab-content .tab-pane {
        padding: 20px;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .eq-hero-title {
            font-size: 20px;
        }

        .eq-main-tabs {
            padding: 0 12px;
        }

        .eq-main-tabs .nav-link {
            padding: 12px 8px !important;
            font-size: 12px;
        }
    }
</style>
@endsection



<!-- Removed duplicate - CSS already added above in main content -->
