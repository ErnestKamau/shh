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
        bottom: 20;
        left: 0;
        right: 0;

        z-index: 1000;
    }

    .parameter {
        border: solid 1 rgba(0, 0, 0, 0.35) !important;
        /* border: solid 1 black !important; */

        padding: 0 !important;

    }

    .textBold {
        font-weight: 700 !important;
    }
    
</style>

<body>


    <footer class="footer">

        <table class="" style="margin-top: 1px !important;width:100%">
           

            <tr>
                @foreach ($batch_approvers as $approver)
                    <td style="font-size: 8px !important;">
                        <b>{{ $approver->title }}</b><br>
                        <img src="{{ getCoaApproverSignature($approver->getApproverDetails()->electronic_sig) }}" style="width:80px"
                            alt="signature"><br>
                        <span>{{ $approver->getApproverDetails()->name }} -
                            {{ $approver->getApproverPositionDetails() }}</span>
                    </td>
                @endforeach

            </tr>
            <tr>
                <td colspan="{{$batch_approvers->count()}}">
                    <span style="font-size:5px">{{ $non_accredited->value }}</span>
                            
                    <div class="text-center" style="font-size:5px">
                        {{$disclaimer->value}}
                        @if($batch->sampled_by_company_personnel == 0)
                        <br>
                        <b>NB: This report relates to submitted sample(s) only. The source and markings are as provided by the customer.</b>
                        @endif
                    </div>
                </td>
            </tr>
        </table>

        <div style="margin-top:7px">
            <table style="width:100%">
                <tr>
                    <td style="width: 10%">
                        <img src="data:image/png;base64, {!! $qrcode !!}" width="60" height="60"> <br>
                        <span style="font-size: 8px !important;">Scan to Verify</span>
                        
                    </td>
                    <td style="text-align: center">
                        <div class=""><b>{{ $company->name }}</b></div>
                        <div class="company-location p-2">
                            <table style="font-size: 7px">
                                <tr>
                                    <td colspan="3"  style="font-size: 8px !important;">{{ $company->street }} - P.O. Box {{ $company->address }},
                                        {{ $company->location }}</td>
                                </tr>
                                <tr>
                                    <td  style="font-size: 8px !important;">Office: {{ $company->telephone }}</td>
                                    <td  style="font-size: 8px !important;">Tel 1: {{ explode('/', $company->cell_phone)[0] ?? '' }}</td>
                                    <td  style="font-size: 8px !important;">Email: {{ $company->email }}</td>
                                </tr>
                                <tr>
                                    <td  style="font-size: 8px !important;">Fax: {{ $company->fax }}</td>
                                    <td  style="font-size: 8px !important;">Tel2: {{ explode('/', $company->cell_phone)[0] ?? '' }}</td>
                                    <td  style="font-size: 8px !important;">Web: {{ $company->website }}</td>
                                </tr>
                               

                            </table>
                        </div>
                        <div class="text-center"  style="font-size: 8px !important;"><u><b>Member of POLUCON Group</b></u></div>
                    </td>
                    <td style="width:30%"></td>
                </tr>
            </table>
        </div>
    </footer>

    @foreach ($samples as $sample)
        <main style="margin-bottom: 180px;">
            <table class="table table-sm" style="font-size: 8px;border:solid 0 transparent !important">
                <thead style="height: 60px !important;">
                    <tr style="">
                        <th style="font-size: 6px !important;border: solid 0 transparent !important" colspan="5">
                            <table style="width: 100%;border:0px !important;">
                                <tr>
                                    <td
                                        style="font-size: 10px !important;border:solid 0 transparent !important; width:30% !important">
                                        <img src="{{ $path }}" style="height:90px;" alt="logo">
                                        
                                    </td>
                                    
                                    <td
                                        style="border: solid 0 transparent !important;text-align:right;font-size:11px !important;">
                                        {{ $sample->crm_name }} <br>
                                        P.O BOX {{ $sample->postal_address }} <br>
                                        {{ $sample->physical_address }}
                                    </td>
                                </tr>
                              
                            </table>

                        </th>
                    </tr>
                    <tr style="margin:0 !important">
                        <th colspan="5" style="border: solid 0 transparent !important;border-bottom:1px solid rgba(0, 0, 0, 0.35);padding:0 !important">
                            <table style="width: 100%;margin:0 !important;margin-bottom:2px;border: 1px solid rgba(0, 0, 0, 0.35) !important">
                                <tr  style="margin:0 !important">
                                    <td colspan="2"
                                        style=" border: 1px solid rgba(0, 0, 0, 0.35) !important; font-size:10px !important;">
                                        <b> TEST REPORT NO : R{{ substr($sample->sample_code, 1, strlen($sample->sample_code))}} {{$report_type != '' ? ' - '.$report_type : ''}}</b></td>
                                </tr>
                                <tr>
                                    <td style="width:20%;border: solid 0 transparent !important;border-right:1px solid rgba(0, 0, 0, 0.35);font-size:8px !important;border-left:1px solid rgba(0, 0, 0, 0.35)">SAMPLE</td>
                                    <td  style="padding-left:10px !important;border: solid 0 transparent !important;font-size:8px !important;border-right:1px solid rgba(0, 0, 0, 0.35)">{{ $sample->sample_type_name }}</td>
                                </tr>
                                <tr>
                                    <td style="border: solid 0 transparent !important;border-right:1px solid rgba(0, 0, 0, 0.35);font-size:8px !important;border-left:1px solid rgba(0, 0, 0, 0.35)">DATE & PLACE {{ $sample->sampled_by_company_personnel == 1 ? 'SAMPLED' : 'SUBMITTED' }}</td>
                                    @if ($sample->sampled_by_company_personnel == 1)
                                        <td style="border: solid 0 transparent !important;padding-left:10px !important;font-size:8px !important;border-right:1px solid rgba(0, 0, 0, 0.35)">{{ $sample->date_collected }} {{ $sample->sample_point_name }}</td>
                                    @else
                                        <td style="border: solid 0 transparent !important;padding-left:10px !important;font-size:8px !important;border-right:1px solid rgba(0, 0, 0, 0.35)">{{ $sample->receipt_date }} {{ $company->name }}</td>
                                    @endif
                                </tr>
                                <tr>
                                    <td style="border: solid 0 transparent !important;border-right:1px solid rgba(0, 0, 0, 0.35);font-size:8px !important;border-left:1px solid rgba(0, 0, 0, 0.35)">DATE ANALYSIS STARTED</td>
                                    <td style="border: solid 0 transparent !important;padding-left:10px !important;font-size:8px !important;border-right:1px solid rgba(0, 0, 0, 0.35)"></td>
                                </tr>
                                <tr>
                                    <td style="border: solid 0 transparent !important;border-right:1px solid rgba(0, 0, 0, 0.35);font-size:8px !important;border-left:1px solid rgba(0, 0, 0, 0.35)">SAMPLING METHOD</td>
                                    <td style="border: solid 0 transparent !important;padding-left:10px !important;font-size:8px !important;border-right:1px solid rgba(0, 0, 0, 0.35)">{{ $sample->sampling_method_name }}</td>
                                </tr>
                                <tr>
                                    <td style="border: solid 0 transparent !important;border-right:1px solid rgba(0, 0, 0, 0.35);font-size:8px !important;border-left:1px solid rgba(0, 0, 0, 0.35)">SAMPLE ID</td>
                                    <td style="border: solid 0 transparent !important;padding-left:10px !important;font-size:8px !important;border-right:1px solid rgba(0, 0, 0, 0.35)">{{ $sample->sample_code ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td style="border: solid 0 transparent !important;border-right:1px solid rgba(0, 0, 0, 0.35);font-size:8px !important;border-bottom:1px solid rgba(0, 0, 0, 0.35);border-left:1px solid rgba(0, 0, 0, 0.35)">MARKINGS</td>
                                    <td style="border: solid 0 transparent !important;padding-left:10px !important;font-size:8px !important;border-bottom:1px solid rgba(0, 0, 0, 0.35);border-right:1px solid rgba(0, 0, 0, 0.35)">{{ $sample->comments }}</td>
                                </tr>
                               
                            </table>
                        </th>
                    </tr>
                    
                    <tr style="">
                        <th class="parameter"
                            style="font-size: 9px !important; width:25% !important;vertical-align: top !important;padding:5px !important;">
                            TESTS</th>
                        <th class="parameter"
                            style="font-size:9px !important;width:25% !important;vertical-align: top !important;padding:5px !important">
                            TEST METHODS</th>
                        <th class="parameter"
                            style="font-size: 9px !important;width:10% !important;vertical-align: top !important;padding:5px !important">
                            RESULTS</th>
                        <th class="parameter"
                        style="font-size: 9px !important;width:10% !important;vertical-align: top !important;padding:5px !important">
                        UNITS</th>   
                        <th class="parameter text-center"
                            style="font-size: 9px !important;width:15% !important;vertical-align: top !important;padding:5px !important">
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
                                {{ $captured->result_reporting_symbol ?? '' }}{{ $captured->result != '' ? $captured->result : 'TBA'  }}
                            </td>  
                            <td class="parameter {{ $captured->remark == 'FAIL' ? 'textBold' : '' }}" style="font-size: 9px !important;padding-left:3px !important;">
                                {{ $captured->analyte()->reporting_unit }}
                            </td>  
                            <td class="parameter {{ $captured->remark == 'FAIL' ? 'textBold' : '' }}" style="font-size: 9px !important;padding-left:3px !important;">
                                {{ $captured->main_value }}
                            </td>                            

                        </tr>
                        @endforeach
                    @endforeach
                    <tr>
                        <td style="font-size: 9px !important;padding-left:3px !important; text-align:center ;border: 0 transparent !important" colspan="5">******<small>End of Test Results</small>*******</td>
                    </tr>
                </tbody>
            </table>

            <table style="margin:0px !important;width:100%">
                <tr style="margin:0px !important">
                    <td style="font-size:8px !important;">
                        @if($sample->header_body != '')
                        <b>Comments : </b>{{ $sample->header_body }}
                        @endif

                    </td>
                </tr>
                <tr>
                    <td style="font-size:8px !important;" ><br><b>{{strtoupper($sample->main_lab_name)}} <br>{{ $batch->approval_date ?? '-' }}</b></td>
                                   
                </tr>
                
            </table>
            @if($sample->getAccredittedStatus() >= 1)
            <div class="" style="display:inline-block;position:fixed;bottom:20;left:70%">
                <img src="{{$kebs}}" style="width:60px;height:60px" alt="">
           
                <img src="{{$kenas}}" style="width:60px;height:60px" alt="">
                
        
                <img src="{{$ilac}}" style="width:60px;height:60px" alt="">
            </div>
            @else
            <div class="" style="display:inline-block;position:fixed;bottom:20;left:70%">
                <img src="{{$kebs}}" style="width:60px;height:60px" alt="">
                    
                <img src="{{$nema}}" style="width:60px;height:60px" alt="">
                
            
                <img src="{{$ispm}}" style="width:60px;height:60px" alt="">
            </div>

            @endif
            

            @if ($loop->iteration < $samples->count())
                <div style="page-break-after: always;">
                </div>
            @endif
        </main>
    @endforeach
    <script type="text/php">
    if (isset($pdf)) {
        $text = "Page {PAGE_NUM} of {PAGE_COUNT}";
        $size = 9;
        $font = $fontMetrics->getFont("Verdana");
        $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
        $x = ($pdf->get_width() - $width) / 1;
        $y = $pdf->get_height() - 20;
        $pdf->page_text($x, $y, $text, $font, $size);
    }
    
</script>

</body>

</html>
