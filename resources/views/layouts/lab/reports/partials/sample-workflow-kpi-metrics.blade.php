@php
    $metrics = $metrics ?? [];
    $registrationKpi = $registrationKpiPeriod ?? [];
    $laboratoryKpi = $laboratoryKpiPeriod ?? [];
    $kpiStart = $registrationKpi['start_date'] ?? now()->startOfMonth()->toDateString();
    $kpiEnd = $registrationKpi['end_date'] ?? now()->endOfMonth()->toDateString();

    $cards = [
        [
            'key' => 'total',
            'label' => 'Total Active Jobs',
            'sublabel' => 'All active batches',
            'icon' => 'mdi-clipboard-text-multiple',
            'color' => '#3498db',
            'link' => route('sample-workflow'),
        ],
        [
            'key' => 'samples_receiving',
            'label' => 'Samples Receiving',
            'sublabel' => 'Registration pipeline',
            'icon' => 'mdi-truck-delivery',
            'color' => '#17a2b8',
            'link' => route('sample-workflow', ['status' => 'Samples Receiving']),
        ],
        [
            'key' => 'request_review',
            'label' => 'Request Review',
            'sublabel' => 'Awaiting review',
            'icon' => 'mdi-clipboard-check-outline',
            'color' => '#6f42c1',
            'link' => route('sample-workflow', ['status' => 'Samples Request Review']),
        ],
        [
            'key' => 'samples_in_lab',
            'label' => 'Samples In Lab',
            'sublabel' => 'Testing in progress',
            'icon' => 'mdi-flask',
            'color' => '#fd7e14',
            'link' => route('sample-workflow', ['status' => 'Samples In Lab']),
        ],
        [
            'key' => 'verification',
            'label' => 'Verification',
            'sublabel' => 'Results verification',
            'icon' => 'mdi-check-decagram',
            'color' => '#20c997',
            'link' => route('sample-workflow', ['status' => 'Sample Verification']),
        ],
        [
            'key' => 'approval',
            'label' => 'Approval',
            'sublabel' => 'Pending approval',
            'icon' => 'mdi-stamper',
            'color' => '#28a745',
            'link' => route('sample-workflow', ['status' => 'Sample Approval']),
        ],
        [
            'key' => 'completed',
            'label' => 'Completed',
            'sublabel' => 'Finished samples',
            'icon' => 'mdi-check-all',
            'color' => '#6c757d',
            'link' => route('sample-workflow', ['status' => 'Completed Sample']),
        ],
        [
            'key' => 'portal_submitted',
            'label' => 'Portal Submitted',
            'sublabel' => 'TRF / portal queue',
            'icon' => 'mdi-web',
            'color' => '#e83e8c',
            'link' => route('sample-workflow', ['status' => 'Samples Receiving']),
        ],
    ];
@endphp

<div class="sample-workflow-kpi-dashboard px-4 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="mb-1"><i class="mdi mdi-chart-box-outline"></i> Sample Workflow Overview</h5>
            <p class="text-muted mb-0" style="font-size: 0.9rem;">Key statistics across registration and laboratory workflow</p>
        </div>
        <p class="mb-0 text-primary" style="font-size: 0.95rem; font-weight: 500;">
            <i class="mdi mdi-calendar"></i> {{ $metrics['as_of'] ?? now()->format('l, F j, Y') }}
        </p>
    </div>

    <div class="row mb-3">
        @foreach(array_slice($cards, 0, 4) as $card)
            <div class="col-md-3 col-sm-6 mb-3">
                @include('layouts.lab.reports.partials.sample-workflow-metric-card', ['card' => $card, 'metrics' => $metrics])
            </div>
        @endforeach
    </div>

    <div class="row mb-2">
        @foreach(array_slice($cards, 4, 4) as $card)
            <div class="col-md-3 col-sm-6 mb-3">
                @include('layouts.lab.reports.partials.sample-workflow-metric-card', ['card' => $card, 'metrics' => $metrics])
            </div>
        @endforeach
    </div>

    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-2">
            <div class="quotation-metric-mini">
                <span class="text-muted">Ready for reception</span>
                <strong>{{ $metrics['ready_for_reception'] ?? 0 }}</strong>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-2">
            <div class="quotation-metric-mini">
                <span class="text-muted">Registered this month</span>
                <strong>{{ $metrics['this_month_registered'] ?? 0 }}</strong>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4" style="border-radius: 8px;">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                <div>
                    <h6 class="mb-1"><i class="mdi mdi-clipboard-plus-outline"></i> KPI — Registration</h6>
                    <p class="text-muted mb-0" style="font-size: 0.85rem;">Scheduled, collected, registration rate and clients for the selected period</p>
                </div>
                <form method="GET" action="{{ route('lab-reports-home') }}" class="form-inline lab-kpi-date-form">
                    <input type="hidden" name="kpi_start_date" id="kpi_start_date_hidden" value="{{ $kpiStart }}">
                    <input type="hidden" name="kpi_end_date" id="kpi_end_date_hidden" value="{{ $kpiEnd }}">
                    <label class="mr-2 mb-0 text-muted" style="font-size: 0.85rem;">From</label>
                    <input type="date" class="form-control form-control-sm mr-2 lab-kpi-start" value="{{ $kpiStart }}">
                    <label class="mr-2 mb-0 text-muted" style="font-size: 0.85rem;">To</label>
                    <input type="date" class="form-control form-control-sm mr-2 lab-kpi-end" value="{{ $kpiEnd }}">
                    <button type="submit" class="btn btn-sm btn-outline-primary"><i class="mdi mdi-filter"></i> Apply</button>
                </form>
            </div>

            <div class="row mb-3">
                <div class="col-md-2 col-sm-4 col-6 mb-2">
                    <div class="quotation-metric-mini flex-column align-items-start">
                        <span class="text-muted">Scheduled</span>
                        <strong style="font-size: 1.25rem;">{{ $registrationKpi['samples_scheduled'] ?? 0 }}</strong>
                    </div>
                </div>
                <div class="col-md-2 col-sm-4 col-6 mb-2">
                    <div class="quotation-metric-mini flex-column align-items-start">
                        <span class="text-muted">Collected</span>
                        <strong style="font-size: 1.25rem;">{{ $registrationKpi['samples_collected'] ?? 0 }}</strong>
                    </div>
                </div>
                <div class="col-md-2 col-sm-4 col-6 mb-2">
                    <div class="quotation-metric-mini flex-column align-items-start">
                        <span class="text-muted">Registration Rate</span>
                        <strong style="font-size: 1.25rem;">{{ ($registrationKpi['samples_scheduled'] ?? 0) + ($registrationKpi['samples_collected'] ?? 0) > 0 ? ($registrationKpi['registration_rate_percent'] ?? 0).'%' : '—' }}</strong>
                    </div>
                </div>
                <div class="col-md-2 col-sm-4 col-6 mb-2">
                    <div class="quotation-metric-mini flex-column align-items-start">
                        <span class="text-muted">Clients Registered</span>
                        <strong style="font-size: 1.25rem;">{{ $registrationKpi['clients_registered'] ?? 0 }}</strong>
                    </div>
                </div>
            </div>

            <div class="d-flex flex-wrap align-items-center">
                <form method="GET" action="{{ route('lab.kpi.registration.summary.export') }}" class="d-inline mr-2 mb-2 lab-kpi-export-form">
                    <input type="hidden" name="start_date" value="{{ $kpiStart }}">
                    <input type="hidden" name="end_date" value="{{ $kpiEnd }}">
                    <button type="submit" class="btn btn-success btn-sm">
                        <i class="mdi mdi-file-excel"></i> Download Summary Excel
                    </button>
                </form>
                <form method="GET" action="{{ route('lab.kpi.registration.detail.export') }}" class="d-inline mb-2 lab-kpi-export-form">
                    <input type="hidden" name="start_date" value="{{ $kpiStart }}">
                    <input type="hidden" name="end_date" value="{{ $kpiEnd }}">
                    <button type="submit" class="btn btn-outline-success btn-sm">
                        <i class="mdi mdi-file-table"></i> Download Detail Excel
                    </button>
                </form>
                <small class="text-muted ml-2 mb-2">Per-sample detail includes job/batch no., sampler, equipment, parameters, etc.</small>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4" style="border-radius: 8px;">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                <div>
                    <h6 class="mb-1"><i class="mdi mdi-flask-outline"></i> KPI — Laboratory (Samples In Lab / Testing)</h6>
                    <p class="text-muted mb-0" style="font-size: 0.85rem;">Jobs received, completed, pending, data entry and review status</p>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-2 col-sm-4 col-6 mb-2">
                    <div class="quotation-metric-mini flex-column align-items-start">
                        <span class="text-muted">Jobs Received</span>
                        <strong style="font-size: 1.25rem;">{{ $laboratoryKpi['jobs_received'] ?? 0 }}</strong>
                    </div>
                </div>
                <div class="col-md-2 col-sm-4 col-6 mb-2">
                    <div class="quotation-metric-mini flex-column align-items-start">
                        <span class="text-muted">Jobs Completed</span>
                        <strong style="font-size: 1.25rem;">{{ $laboratoryKpi['jobs_completed'] ?? 0 }}</strong>
                    </div>
                </div>
                <div class="col-md-2 col-sm-4 col-6 mb-2">
                    <div class="quotation-metric-mini flex-column align-items-start">
                        <span class="text-muted">Jobs Pending</span>
                        <strong style="font-size: 1.25rem;">{{ $laboratoryKpi['jobs_pending'] ?? 0 }}</strong>
                    </div>
                </div>
                <div class="col-md-2 col-sm-4 col-6 mb-2">
                    <div class="quotation-metric-mini flex-column align-items-start">
                        <span class="text-muted">Data Entry Complete</span>
                        <strong style="font-size: 1.25rem;">{{ $laboratoryKpi['data_entry_complete'] ?? 0 }}</strong>
                    </div>
                </div>
                <div class="col-md-2 col-sm-4 col-6 mb-2">
                    <div class="quotation-metric-mini flex-column align-items-start">
                        <span class="text-muted">Review Pending</span>
                        <strong style="font-size: 1.25rem;">{{ $laboratoryKpi['review_pending'] ?? 0 }}</strong>
                    </div>
                </div>
                <div class="col-md-2 col-sm-4 col-6 mb-2">
                    <div class="quotation-metric-mini flex-column align-items-start">
                        <span class="text-muted">Review Approved</span>
                        <strong style="font-size: 1.25rem;">{{ $laboratoryKpi['review_approved'] ?? 0 }}</strong>
                    </div>
                </div>
            </div>

            <div class="d-flex flex-wrap align-items-center">
                <form method="GET" action="{{ route('lab.kpi.laboratory.summary.export') }}" class="d-inline mr-2 mb-2 lab-kpi-export-form">
                    <input type="hidden" name="start_date" value="{{ $kpiStart }}">
                    <input type="hidden" name="end_date" value="{{ $kpiEnd }}">
                    <button type="submit" class="btn btn-success btn-sm">
                        <i class="mdi mdi-file-excel"></i> Download Summary Excel
                    </button>
                </form>
                <form method="GET" action="{{ route('lab.kpi.laboratory.detail.export') }}" class="d-inline mb-2 lab-kpi-export-form">
                    <input type="hidden" name="start_date" value="{{ $kpiStart }}">
                    <input type="hidden" name="end_date" value="{{ $kpiEnd }}">
                    <button type="submit" class="btn btn-outline-success btn-sm">
                        <i class="mdi mdi-file-table"></i> Download Detail Excel
                    </button>
                </form>
                <small class="text-muted ml-2 mb-2">Detail export: one row per sample in lab with data entry, review and final report status</small>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var startInputs = document.querySelectorAll('.lab-kpi-start');
        var endInputs = document.querySelectorAll('.lab-kpi-end');
        var startHidden = document.getElementById('kpi_start_date_hidden');
        var endHidden = document.getElementById('kpi_end_date_hidden');

        if (startInputs.length === 0 || endInputs.length === 0) {
            return;
        }

        function syncKpiDates() {
            var startVal = startInputs[0].value;
            var endVal = endInputs[0].value;

            if (startHidden) {
                startHidden.value = startVal;
            }
            if (endHidden) {
                endHidden.value = endVal;
            }

            document.querySelectorAll('.lab-kpi-export-form input[name="start_date"]').forEach(function (el) {
                el.value = startVal;
            });
            document.querySelectorAll('.lab-kpi-export-form input[name="end_date"]').forEach(function (el) {
                el.value = endVal;
            });
        }

        startInputs.forEach(function (el) {
            el.addEventListener('change', syncKpiDates);
        });
        endInputs.forEach(function (el) {
            el.addEventListener('change', syncKpiDates);
        });
    });
</script>

<style>
    .sample-workflow-kpi-dashboard .quotation-kpi-card {
        background: #fff;
        border: 1px solid #e9ecef;
        border-left: 4px solid var(--metric-color, #3498db);
        border-radius: 8px;
        padding: 1rem 1.15rem;
        height: 100%;
        transition: box-shadow 0.2s ease, transform 0.2s ease;
    }

    .sample-workflow-kpi-dashboard .quotation-kpi-card:hover {
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
        transform: translateY(-1px);
    }

    .sample-workflow-kpi-dashboard .quotation-kpi-card a {
        color: inherit;
        text-decoration: none;
    }

    .sample-workflow-kpi-dashboard .quotation-kpi-value {
        font-size: 1.75rem;
        font-weight: 700;
        margin-bottom: 0;
        line-height: 1.2;
    }

    .sample-workflow-kpi-dashboard .quotation-kpi-label {
        font-size: 0.9rem;
        font-weight: 600;
        margin-bottom: 0.15rem;
        color: #343a40;
    }

    .sample-workflow-kpi-dashboard .quotation-kpi-sublabel {
        font-size: 0.78rem;
        color: #6c757d;
        margin-bottom: 0;
    }

    .sample-workflow-kpi-dashboard .quotation-kpi-icon {
        font-size: 1.75rem;
        opacity: 0.85;
    }

    .sample-workflow-kpi-dashboard .quotation-metric-mini {
        background: #f8f9fa;
        border-radius: 6px;
        padding: 0.65rem 0.85rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
</style>
