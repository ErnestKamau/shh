@extends('layouts.lab.layout.app', ['datePicker' => true, 'select2' => true])

@section('title2')
    <title>View COA</title>

    <style>
        .hidden {
            display: none;
        }

        .text-bold {
            font-weight: 650 !important;
        }

        td {
            padding: 3px;

        }

        .text-right {
            text-align: right !important;
        }

        .col-md-3 {
            padding: 0.4%;
        }
    </style>
@endsection
@section('content2')
    <main>
        <?php
    $items = [
        [
            'link' => route('dashboard-lab'),
            'name' => 'Dashboard',
            'icon' => null,
        ],
        [
            'link' => route('sample-workflow', ['status' => 'All Samples']),
            'name' => 'Sample Workflow',
            'icon' => null,
        ],
        [
            'link' => route('sample-workflow', ['status' => $status]),
            'name' => $status,
            'icon' => null,
        ],
        [
            'link' => '/sample-workflow/batch/' . $batch->id . '/details',
            'name' => $batch->batch_code,
            'icon' => null,
        ],
        [
            'link' => '#',
            'name' => 'COA',
            'icon' => null,
        ],
    ];
            ?>
        <x-bread-crumb :items="$items"></x-bread-crumb>
        <h4 class="p-2">
            <span class="float-left"><i class="mdi mdi-file-document-edit"></i> {{ $batch->batch_code }} COA</span>

        </h4>

        <?php $check_v = 0; ?>
        @foreach ($samples as $sample)
            <?php    $accreditted_status = $sample->getAcredditedStatus(); ?>
            <div class="card m-4 mt-5" style="clear:both;box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;">
                <div class="card-body p-4">
                    <div class="row border-bottom pb-3">
                        <div class="col-md-12 text-center" style="font-size:20px"><b>LABORATORY TEST REPORT</b></div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-md-2">
                            <span class="text-bold">TEST REPORT REF : </span>
                        </div>
                        <div class="col-md-2">
                            <span class="text-right">{{ $sample->report_number }}</span>
                        </div>
                        <div class="col-md-2">
                            <span class="text-bold">SAMPLE TYPE : </span>
                        </div>
                        <div class="col-md-2">
                            <span class="text-right">{{ $sample->sample_type_name }}</span>
                        </div>
                        <div class="col-md-2"> <span class="text-bold">SAMPLE REF : </span></div>
                        <div class="col-md-2">
                            <span class="text-right">{{ $sample->sample_code }}</span>
                        </div>
                    </div>
                    <div class="row mt-3 border-bottom">
                        <div class="col-md-3"><span class="text-bold">Date of sampling / time : </span></div>
                        <div class="col-md-3">
                            <span>{{ convertDateFormatReports($sample->date_collected, 'normal') . ' ' . $sample->barcode }}</span>
                        </div>
                        <div class="col-md-3"><span class="text-bold">Sampling Plan</span></div>
                        <div class="col-md-3"><span>Customer`s Discretion</span></div>
                        <div class="col-md-3"><span class="text-bold">Date of receipt / time : </span></div>
                        <div class="col-md-3">
                            <span>{{ convertDateFormatReports($sample->receipt_date, 'normal') . ' ' . $sample->radio_active_levels }}
                                hrs</span></div>
                        <div class="col-md-3"><span class="text-bold">Temp. of receipt : </span></div>
                        <div class="col-md-3"><span>{{ $sample->kra_office_ref }}
                                {{ is_numeric($sample->kra_office_ref) ? '°C' : '' }}</span></div>
                        <div class="col-md-3"><span class="text-bold">Date of analysis : </span></div>
                        <div class="col-md-3">
                            <span>{{  $analysis_date->start_analysis_date != '' ? convertDateFormatReports($analysis_date->start_analysis_date, 'normal') : '-' }}</span>
                        </div>
                        <div class="col-md-3"><span class="text-bold">Condition of test item & Environment</span></div>
                        <div class="col-md-3"><span>{{ $sample->sample_condition_name }}</span></div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-3"><span class="text-bold">Customer`s sample description : </span></div>
                        <div class="col-md-9">{!! str_replace('<p>&nbsp;</p>', '', $sample->comments ?? 'N/A') !!}</div>
                    </div>
                    <div class="table-responsive mt-2">
                        <table class="table table-sm table-condensed table-bordered">
                            <thead>
                                <tr>
                                    <th>Test Parameters</th>
                                    <th>Test Methods</th>
                                    <th>Ref. Method</th>
                                    <th>Results</th>
                                    <th>Unit(s)</th>
                                    <th>Specification</th>
                                    <th>Rating</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($sample->getSampleByAnalysisType() as $analysis_type_level)
                                    @foreach ($analysis_type_level->getCapturedResults() as $captured)
                                        <tr>
                                            <td class="parameter">
                                                {!! $captured->analyte_accredited == 0 ? '<small>*</small>' : '' !!}
                                                {!! $captured->isitalic == 1 ? '<i>' . $captured->analyte_code . '</i>' : $captured->analyte_code !!}
                                            </td>
                                            <td class="parameter">
                                                {{ strtoupper($captured->ltmethod->name ?? '') }}
                                            </td>
                                            <td class="parameter">
                                                {{ strtoupper($captured->method()->name ?? '-') }}
                                            </td>
                                            <td class="parameter {{ $captured->remark == 'FAIL' ? 'text-bold text-danger' : '' }}">
                                                {{ $captured->result_reporting_symbol ?? '' }}{!! $captured->result != '' ? formatReportResults($captured->scienctific_result) : 'TBA' !!}
                                            </td>
                                            @if ($batch->require_mu == 1)
                                                <td class="parameter">
                                                    {{ $captured->measure_uncertanity ?? '' }}
                                                </td>
                                            @endif
                                            <td class="parameter">
                                                {{ $captured->reporting_unit_id ?? '' }}
                                            </td>
                                            <td class="parameter ">
                                                {{ $captured->main_value == 'NS' ? '--' : ($captured->main_value ?? '') }}
                                                {{ getStandardLimitValue($captured->id, $sample->main_standard) ?? '' }}
                                            </td>
                                            <td class="parameter  {{ $captured->remark == 'FAIL' ? 'text-bold text-danger' : '' }}">
                                                {{ $captured->remark }}
                                            </td>

                                        </tr>
                                    @endforeach
                                @endforeach
                                <tr>
                                    <td style="font-size: 12px !important;padding-left:3px !important; text-align:center ;border: 0 transparent !important"
                                        colspan="{{ $batch->require_mu == 1 ? 8 : 7 }}">******<small>End of Test
                                            Results</small>*******</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="comments mt-2 p-2 {{$sample->header_body != '' ? '' : 'hidden'}}">
                        <b>Comments : </b>{!! $sample->header_body !!}
                    </div>
                    <div class="comments mt-2 p-2 {{$sample->main_body != '' ? '' : 'hidden'}}">
                        <b>Recommendations : </b>{!! $sample->main_body !!}
                    </div>

                </div>
            </div>
        @endforeach
    </main>
@endsection

@section('script2')
    <script src="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/js/bootstrap4-toggle.min.js"></script>
    <script></script>
@endsection