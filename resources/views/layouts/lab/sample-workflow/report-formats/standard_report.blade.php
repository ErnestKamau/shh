@extends('layouts.lab.layout.app', ['datePicker' => true, 'select2' => true])

@section('title2')
    <title>View COA</title>

    <style>
        .form-part-toggler {
            margin: 0px 0px 5px 0px !important;
            padding: 6px 6px 6px 6px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.09);
            cursor: pointer;
        }

        .form-part-toggler:hover {
            background-color: rgba(0, 0, 0, 0.08);
        }

        #sample-detail-rows .form-group {
            display: none;
        }

        #sample-detail-rows tr.selected-row {
            background-color: rgb(253, 220, 220);
        }

        #sample-detail-rows .text {
            display: unset;
        }

        #sample-detail-rows tr.editable .form-group {
            display: unset;
        }

        #sample-detail-rows tr.editable .text {
            display: none;
        }

        #sample-detail-rows tr {
            cursor: pointer;
        }

        .hidden {
            display: none;
        }

        .overdue-bg-color {
            background-color: rgba(240, 185, 83, 0.972) !important;
        }

        .upfront-bg-color {
            background-color: skyblue !important;
        }

        .ammend-bg-color {
            background-color: #fef764 !important;
        }

        .btn-white {
            background-color: white !important;
        }

        .text-bold {
            font-weight: 650 !important ;
        }
        td{
            padding: 3px;
            
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
        <?php $accreditted_status = $sample->getAcredditedStatus() ?>
            <div class="card m-4 mt-5" style="clear:both;box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between p-2">
                        <div class="company-logo" style="width:50%">
                            <img src="{{ $company->logo }}" style="width:20% !important" alt="">
                        </div>
                        <div class="company_details"  style="text-align: right" >
                            <p class="mt-2" style="font-size: 17px !important">
                                {{ $sample->crm_name }} <br>
                                P.O BOX {{ $sample->postal_address }} <br>
                                {{ $sample->physical_address }}
                            </p>

                        </div>
                    </div>
                    <div class="report-no p-2 border" style="font-weight: 750 !important ;">
                        TEST REPORT NO : R{{ substr($sample->sample_code, 1, strlen($sample->sample_code)) }}
                    </div>
                    <table class="border" style="width: 100%">
                        <tr>
                            <td style="width:20%;border-right:1px solid #dee2e6">SAMPLE</td>
                            <td  style="padding-left:10px !important">{{ $sample->product_name }}</td>
                        </tr>
                        <tr>
                            <td style="border-right:1px solid #dee2e6">DATE & PLACE {{ $sample->sampled_by_company_personnel == 1 ? 'SAMPLED' : 'SUBMITTED' }}</td>
                            @if ($sample->sampled_by_company_personnel == 1)
                                <td style="padding-left:10px !important">{{ $sample->date_collected }} {{ $sample->sample_point_name }}</td>
                            @else
                                <td style="padding-left:10px !important">{{ $sample->receipt_date }} {{ $company->name }}</td>
                            @endif
                        </tr>
                        <tr>
                            <td style="border-right:1px solid #dee2e6">DATE ANALYSIS STARTED</td>
                            <td style="padding-left:10px !important">{{$analysis_date->start_analysis_date ?? '-'}}</td>
                        </tr>
                        <tr>
                            <td style="border-right:1px solid #dee2e6">SAMPLING METHOD</td>
                            <td style="padding-left:10px !important">{{ $sample->sampling_method_name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td style="border-right:1px solid #dee2e6">MARKINGS</td>
                            <td style="padding-left:10px !important">{{ $sample->comments ?? 'N/A' }}</td>
                        </tr>
                    </table>

                    <div class="table-responsive mt-2">
                        <table class="table table-sm table-condensed table-bordered">
                            <thead>
                                <tr>
                                    <th>TESTS</th>
                                    <th>TEST METHODS</th>
                                    <th>RESULTS</th>
                                    <th>UNITS</th>
                                    <th>{{ $sample->main_standard_code }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $pesticideAnalysis = []?>
                                
                                @foreach ($sample->getSampleByAnalysisType($exclude_pesticides) as $analysis_type_level)
                                @if($analysis_type_level->is_pesticide == 0)
                                    @foreach ($analysis_type_level->getCapturedResults() as $captured)
                                        <?php $captured->analyte_status_contracted == 1 ? ($check_v = 1) : 0; ?>
                                        <tr class="{{ $captured->remark == 'FAIL' ? 'text-bold' : '' }}">
    
                                            <td>
                                                @if($accreditted_status == 1)
                                
                                                {!! $captured->analyte_status_contracted == 1 ? '<small>+</small>' : '' !!} {!! $captured->analyte_accredited == 0 ? '<small>*</small>' : '' !!}  {!! $captured->is_italic == 1 ? '<i>'. $captured->analyte_code.'</i>' : $captured->analyte_code !!}
                                                @else
                                                {!! $captured->analyte_status_contracted == 1 ? '<small>+</small>' : '' !!} {!! $captured->analyte_accredited == 1 ? '<small>*</small>' : '' !!}  {!! $captured->is_italic == 1 ? '<i>'. $captured->analyte_code.'</i>' : $captured->analyte_code !!}
                                                @endif
                                                </td>
                                            <td>{{ $captured->method()->name }}</td>
                                            <td>{{ $captured->result_reporting_symbol ?? '' }}{{ $captured->result }}</td>
                                            <td>{{ $captured->reporting_unit_id }}</td>
                                            <td>{{ $captured->main_value }}</td>
                                        </tr>
                                    @endforeach

                                @else
                                <?php $pesticideAnalysis[]= $analysis_type_level?>
                                @endif
                                   

                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if(sizeof($pesticideAnalysis) > 0)
                    <p><b>Pesticide Results</b></p>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead>
                                <th>TESTS</th>
                                <th>TEST METHODS</th>
                                <th>RESULTS</th>
                                <th>UNITS</th>
                                <th>{{ $sample->main_standard_code }}</th>
                            </thead>
                            <tbody>
                                @foreach ($pesticideAnalysis as $analysis_type_level)
                                    @foreach ($analysis_type_level->getCapturedResults() as $captured)
                                        <?php $captured->analyte_status_contracted == 1 ? ($check_v = 1) : 0; ?>
                                        <tr class="{{ $captured->remark == 'FAIL' ? 'text-bold' : '' }}">
    
                                            <td>
                                                @if($accreditted_status == 1)
                                
                                                {!! $captured->analyte_status_contracted == 1 ? '<small>+</small>' : '' !!} {!! $captured->analyte_accredited == 0 ? '<small>*</small>' : '' !!}  {!! $captured->is_italic == 1 ? '<i>'. $captured->analyte_code.'</i>' : $captured->analyte_code !!}
                                                @else
                                                {!! $captured->analyte_status_contracted == 1 ? '<small>+</small>' : '' !!} {!! $captured->analyte_accredited == 1 ? '<small>*</small>' : '' !!}  {!! $captured->is_italic == 1 ? '<i>'. $captured->analyte_code.'</i>' : $captured->analyte_code !!}
                                                @endif
                                                </td>
                                            <td>{{ $captured->method()->name }}</td>
                                            <td>{{ $captured->result_reporting_symbol ?? '' }}{{ $captured->result }}</td>
                                            <td>{{ $captured->reporting_unit_id }}</td>
                                            <td>{{ $captured->main_value }}</td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                    <div class="end-test-span text-center">*******<small>End of Test Results</small>*******</div>
                    <div class="comments mt-4 p-2 {{$sample->header_body != '' ? '' : 'hidden'}}">
                        <b>Comments : </b>{!! $sample->header_body !!}
                    </div>
                    <div class="comments mt-2 p-2 {{$sample->main_body != '' ? '' : 'hidden'}}">
                        <b>Recommendations : </b>{!! $sample->main_body !!}
                    </div>
                    <div class="p-3 d-flex justify-content-between mt-3">
                        <div class="lab-sect">
                            <b>{{ $sample->main_lab_name }} <br>
                                {{ $batch->approval_date ?? 'Not Approved' }}
                            </b>
                        </div>
                        @foreach($batch_approvers as $approver)
                        <div class="sig-data">
                            <b>{{$approver->title}}</b><br>
                            <img src="{{$approver->getApproverDetails()->electronic_sig}}" style="width:80px" alt=""><br>
                            <span>{{$approver->getApproverDetails()->name}} - {{$approver->getApproverPositionDetails()}}</span>
                        </div>
                        @endforeach
                       
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
