<!DOCTYPE html>
<html lang="en">


<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Document</title>
    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.11.2/css/all.min.css" integrity="sha256-+N4/V/SbAFiW1MPBCXnfnP9QSN3+Keu+NlB+0ev/YKQ=" crossorigin="anonymous" /> -->

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css"
        integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.js"
        integrity="sha512-s+xg36jbIujB2S2VKfpGmlC3T5V2TF3lY48DX7u2r9XzGzgPsa6wTpOQA7J9iffvdeBN0q9tKzRxVxw1JviZPg=="
        crossorigin="anonymous"></script>
</head>

<style>
    @page {
        margin: 20px;
    }

    @page {
        margin-top: 20px;

        @bottom-center {
            content: element(footer);
        }

        @top-center {
            content: element(header);
        }

    }



    .header {
        position: running(header);
        /* top: 0px;
        left: 0;
        right: 0;
        height: 50;
        z-index: 1000; */


    }

    /* .header::before{
        position: running(header);
    } */
    .footer {
        position: fixed;
        bottom: 110;
        left: 0;
        right: 0;

        z-index: 1000;
    }

    .parameter {
        border: solid 1 rgba(0, 0, 0, 0.35) !important;
        padding: 0 !important;

    }

    .textBold {
        font-weight: 700 !important;
    }
</style>

<body>


    <footer class="footer">

        <table class="container" style="margin-top: 1px !important;">

            <tr>
                @foreach ($batch_approvers as $approver)
                    <td style="font-size: 8px !important;">
                        <b>{{ $approver->title }}</b><br>
                        <img src="{{ $approver->getApproverDetails()->electronic_sig }}" style="width:80px"
                            alt=""><br>
                        <span>{{ $approver->getApproverDetails()->name }} -
                            {{ $approver->getApproverPositionDetails() }}</span>
                    </td>
                @endforeach

            </tr>
        </table>

        <div style="margin-top:5px">
            <table style="width:100%">
                <tr>
                    <td>
                        <img src="data:image/png;base64, {!! $qrcode !!}" width="60" height="60">
                        Scan to Verify
                    </td>
                    <td>
                        <div class="text-center"><b>{{ $company->name }}</b></div>
                        <div class="company-location p-2">
                            <table>
                                <tr>
                                    <td colspan="3">{{ $company->street }} - P.O. Box {{ $company->address }},
                                        {{ $company->location }}</td>
                                </tr>
                                <tr>
                                    <td>Office: {{ $company->telephone }}</td>
                                    <td>Tel 1: {{ explode('/', $company->cell_phone)[0] ?? '' }}</td>
                                    <td>Email: {{ $company->email }}</td>
                                </tr>
                                <tr>
                                    <td>Fax: {{ $company->fax }}</td>
                                    <td>Tel2: {{ explode('/', $company->cell_phone)[0] ?? '' }}</td>
                                    <td>Web: {{ $company->website }}</td>
                                </tr>

                            </table>
                        </div>
                        <div class="text-center"><b>Member of POLUCON Group</b></div>
                    </td>
                    <td>

                    </td>
                </tr>
            </table>
        </div>
    </footer>

    @foreach ($samples as $sample)
        <main style="margin-bottom: 130px;">


            <table class="table table-sm table-bordered" style="font-size: 8px;">
                <thead style="height: 80px !important;">
                    <tr style="border: solid 1px black !important;">
                        <th style="font-size: 6px !important;border: solid 0 transparent !important" colspan="5">
                            <table
                                style="width: 100%;border:0px; padding-bottom:2px !important; border-bottom: solid 2px #0000ff !important">
                                <tr>
                                    <td
                                        style="font-size: 10px !important;border:solid 0 transparent !important; width:30% !important">
                                        <img src="{{ $path }}" style="height:60px;" alt="logo">
                                        
                                    </td>
                                    
                                    <td
                                        style="border: solid 0 transparent !important;text-align:right;font-size:9px !important; ">
                                        {{ $sample->crm_name }} <br>
                                        P.O BOX {{ $sample->postal_address }} <br>
                                        {{ $sample->physical_address }}
                                    </td>
                                </tr>
                            </table>

                        </th>
                    </tr>

                    <tr>
                        <th style="font-size: 6px !important;border: solid 0 transparent !important;border-bottom: solid 1 rgba(0, 0, 0,0.35) !important"
                            colspan="5">
                            <table style="width: 100%; border:0px">
                                <tr>
                                    <td colspan="2"
                                        style=" border: 1px solid black !important; font-size:10px !important;">
                                        <b> TEST REPORT NO : R{{ substr($sample->sample_code, 1, strlen($sample->sample_code)) }}</b></td>
                                </tr>
                                <tr>
                                    <td style="width:20%;border-right:1px solid #dee2e6">SAMPLE</td>
                                    <td  style="padding-left:10px !important">{{ $sample->sample_type_name }}</td>
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
                                    <td style="padding-left:10px !important"></td>
                                </tr>
                                <tr>
                                    <td style="border-right:1px solid #dee2e6">SAMPLING METHOD</td>
                                    <td style="padding-left:10px !important">{{ $sample->sampling_method_name }}</td>
                                </tr>
                                <tr>
                                    <td style="border-right:1px solid #dee2e6">MARKINGS</td>
                                    <td style="padding-left:10px !important">{{ $sample->comments }}</td>
                                </tr>
                            </table>
                        </th>
                    </tr>
                    <tr>
                        <th class="parameter"
                            style="font-size: 9px !important; width:25% !important;vertical-align: top !important;padding-left:3px !important">
                            TESTS</th>
                        <th class="parameter"
                            style="font-size:9px !important;width:25% !important;vertical-align: top !important;padding-left:3px !important">
                            TEST METHODS</th>
                        <th class="parameter"
                            style="font-size: 9px !important;width:10% !important;vertical-align: top !important;padding-left:3px !important">
                            RESULTS</th>
                        <th class="parameter"
                        style="font-size: 9px !important;width:10% !important;vertical-align: top !important;padding-left:3px !important">
                        UNITS</th>   
                        <th class="parameter text-center"
                            style="font-size: 9px !important;width:15% !important;vertical-align: top !important;padding-left:3px !important">
                            {{ $sample->main_standard_code }}</th>
                        
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sample->getSampleByAnalysisType() as $analysis_type_level)
                        <tr>
                            <td class="parameter"
                                style="font-size: 10px !important;font-weight:600;background-color:#fafafa;padding:1px !important;padding-left:2px !important;"
                                colspan="5">{{ strtoupper($analysis_type_level->analysis_type_name) }}</td>
                        </tr>
                        @foreach ($analysis_type_level->getCapturedResults() as $captured)
                        <tr>
                            <td class="parameter {{ $captured->remark == 'FAIL' ? 'textBold' : '' }}" style="font-size: 9px !important;padding-left:3px !important;">
                                {!! $captured->analyte_status_contracted == 1 ? '<small>*</small>' : '' !!} {{ $captured->analyte_code }}
                            </td>  
                            <td class="parameter {{ $captured->remark == 'FAIL' ? 'textBold' : '' }}" style="font-size: 9px !important;padding-left:3px !important;">
                                {{ $captured->method()->name }}
                            </td>  
                            <td class="parameter {{ $captured->remark == 'FAIL' ? 'textBold' : '' }}" style="font-size: 9px !important;padding-left:3px !important;">
                                {{ $captured->result_reporting_symbol ?? '' }}{{ $captured->result }}
                            </td>  
                            <td class="parameter {{ $captured->remark == 'FAIL' ? 'textBold' : '' }}" style="font-size: 9px !important;padding-left:3px !important;">
                                
                            </td>  
                            <td class="parameter {{ $captured->remark == 'FAIL' ? 'textBold' : '' }}" style="font-size: 9px !important;padding-left:3px !important;">
                                
                            </td>                            

                        </tr>
                        @endforeach
                    @endforeach
                    <tr>
                        <td style="font-size: 9px !important;padding-left:3px !important; text-align:center" colspan="5">******<small>End of Test Results</small>*******</td>
                    </tr>
                </tbody>
            </table>

            <table style="margin:0px !important">
                <tr style="margin:0px !important">
                    <td style="font-size:8px !important;">
                        <b>Comments : </b>{{ $sample->header_body }}

                    </td>
                </tr>
                <tr>
                    <td style="font-size: 8px !important;">
                        
                        <span>{{ $non_accredited->value }}</span>
                        
                        <div class="" style="text-align:center;padding-left:3px;padding-right:3px;">
                            {{$disclaimer->value}}
                            @if($batch->sampled_by_company_personnel == 0)
                            <br>
                            <b>NB: This report relates to submitted sample(s) only. The source and markings are as provided by the customer.</b>
                            @endif
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <b>{{ $sample->main_lab_name }} <br>
                            {{ $batch->approval_date ?? '-' }}
                        </b>
                    </td>
                </tr>
            </table>

            @if ($loop->iteration < $samples->count())
                <div style="page-break-after: always;">
                </div>
            @endif
        </main>
    @endforeach
    <script type="text/php">
    if (isset($pdf)) {
        $text = "Page {PAGE_NUM} of {PAGE_COUNT}";
        $size = 10;
        $font = $fontMetrics->getFont("Verdana");
        $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
        $x = ($pdf->get_width() - $width) / 2;
        $y = $pdf->get_height() - 30;
        $pdf->page_text($x, $y, $text, $font, $size);
    }
    
</script>

</body>

</html>
