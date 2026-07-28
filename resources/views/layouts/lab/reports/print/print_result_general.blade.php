<!DOCTYPE html>
<html lang="en">


<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Document</title>
    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.11.2/css/all.min.css" integrity="sha256-+N4/V/SbAFiW1MPBCXnfnP9QSN3+Keu+NlB+0ev/YKQ=" crossorigin="anonymous" /> -->

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.js" integrity="sha512-s+xg36jbIujB2S2VKfpGmlC3T5V2TF3lY48DX7u2r9XzGzgPsa6wTpOQA7J9iffvdeBN0q9tKzRxVxw1JviZPg==" crossorigin="anonymous"></script>
</head>

<style>
    @page { margin-left: 15px; }
    body { margin-left: 0px; }
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
        bottom: 130;
        left: 0;
        right: 0;

        z-index: 1000;
    }

    .parameter {
        border: solid 1 rgba(0, 0, 0, 0.35) !important;
        padding: 0 !important;
        
    }
</style>

<body>

    
    <footer class="footer">

        <table class="container" style="margin-top: 1px !important;">
            <tr>
                <td style="font-size: 11px !important;">
                    
                    <img src="{{$responsible_personnel['approve_s']}}" style="width:80px;height:50px" alt="Signature"> <br>

                   <b>{{strtoupper($responsible_personnel['approve_u'])}}</b> <br>
                   <u>{{strtoupper($responsible_personnel['approve_p'])}}</u>
                </td>
                <td style="font-size: 11px !important; text-align:right; margin-right:30px !important">
                    
                    <img src="{{$responsible_personnel['verify_s']}}" style="width:80px;height:50px" alt="Signature"> <br>
                   <b> {{strtoupper($responsible_personnel['verify_u'])}}</b><br>
                   <u>{{strtoupper($responsible_personnel['verify_p'])}}</u>
                </td>
            </tr>
        </table>

        <br>
        <br>
        
        <div class="">
            <table style="width:100%">
                <tr>
                    <td style="font-size: 9px !important;width:100%">
                        REVISION [03] ISSUE DATE: 23.09.2016 | Authorized by: GM | Approved by: TM<br>
                        AQL-LMS-A-780-001 LABORATORY TEST REPORT
                    </td>

                    <td style="text-align:right">
                        <img src="data:image/png;base64, {!! $qrcode !!}" width="60" height="60">
                    </td>

                </tr>

            </table>
        </div>
    </footer>

    @foreach($batch_result as $key => $view)
    <main style="margin-bottom: 160px;">
        
            
            <table class="table table-sm table-bordered" style="font-size: 8px;">
                <thead style="height: 80px !important;">
                    <tr style="border: solid 1px black !important;">
                        <th style="font-size: 6px !important;border: solid 0 transparent !important" colspan="6">
                            <table style="width: 100%;border:0px; padding-bottom:2px !important; border-bottom: solid 2px #0000ff !important">
                                <tr>
                                    <td style="font-size: 10px !important;border:solid 0 transparent !important; width:30% !important">
                                        <img src="{{ $path }}" style="height:60px;" alt="logo"> <br><br>
                                        ISO 17025:2005 COMPANY
                                    </td>
                                    <td style="border:solid 0 transparent !important;font-size: 10px !important; color:blue;font-weight:800; margin-right:0px !important;  ">
                                        <center>
                                            <img src="{{$kenas}}" style="width: 80px; height:80px" alt=""> <br>
                                            <span style="color: #0000ff;">LABORATORY TEST REPORT</span> </b>
                                        </center>
                                    </td>
                                    
                                    <td style="border: solid 0 transparent !important;text-align:right;font-size:9px !important; ">
                                        {{$company->name}} <br>
                                        P.O Box {{$company->address}} <br>
                                        {{$company->location}} <br>
                                        {{$company->street}} <br>
                                        Email: {{$company->email}} <br>
                                        Website: {{$company->website}} <br>
                                        Tel: {{$company->telephone}} Cell: {{$company->cell_phone}} <br>
                                    </td>
                                </tr>

                            </table>

                        </th>
                    </tr>

                    <tr>
                        <th style="font-size: 6px !important;border: solid 0 transparent !important;border-bottom: solid 1 rgba(0, 0, 0,0.35) !important" colspan="6">
                            <table style="width: 100%; border:0px">
                                <tr>
                                    <td colspan="4" style=" border: 0 transparent !important; font-size:10px !important;"><b>{{strtoupper(date('F jS, Y',strtotime($date))) }}</b></td>
                                </tr>
                                <tr>
                                    <td style="border: 0 transparent !important; font-size:11px !important; width:20% ">
                                        <b> Description of Sample:</b> <br>
                                        <b>Sample Source:</b> <br>
                                        <b>Submitted By:</b> <br>
                                        <b>Customer Contact: </b> <br>
                                        <b>Sampled By: </b><br>
                                    </td>
                                    <td style="border: 0 transparent !important; font-size:11px !important;text-align:left; width:42% ">
                                        {{strtoupper($view['sample_type_name'])}} <br>
                                        {{strtoupper($view['source'])}} <br>
                                        {{strtoupper($view['submit'])}} <br>
                                        {{strtoupper($view['customer'])}} <br>
                                        {{strtoupper($view['sampled_by'])}}
                                    </td>



                                    <td style="border: 0 transparent !important; font-size:11px !important; text-align:left;width:22%">
                                        <b>Date of Sampling: </b> <br>
                                        <b>Date Sample Received:</b> <br>
                                        <b>Date of Analysis: </b> <br>
                                        <b>Date of Report Issue: </b> <br>
                                        <b>Sample ID: </b>
                                    </td>
                                    <td style="border: 0 transparent !important; font-size:11px !important;text-align:right;width:15% ">
                                        {{date('d/m/Y',strtotime($view['sampling_date'])) }} <br>
                                        {{date('d/m/Y',strtotime($view['received'])) }} <br>
                                        {{date('d/m/Y',strtotime($view['analysis_date']))}} <br>
                                        {{$view['report_issue'] == '' ? '-': date('d/m/Y',strtotime($view['report_issue'])) }} <br>
                                        @if($view['ammendment'] > 1)
                                        {{$key}}-V{{$view['ammendment']}}
                                        @else
                                        {{$key}}
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </th>
                    </tr>
                    <tr>
                        <th class="parameter" style="font-size: 12px !important; width:25% !important;vertical-align: top !important;padding-left:3px !important">PARAMETERS</th>
                        <th class="parameter" style="font-size:12px !important;width:25% !important;vertical-align: top !important;padding-left:3px !important">METHOD</th>
                        <th class="parameter" style="font-size: 12px !important;width:10% !important;vertical-align: top !important;padding-left:3px !important">VALUES</th>
                        <th class="parameter text-center" style="font-size: 12px !important;width:15% !important;vertical-align: top !important;padding-left:3px !important">{{$view['main_standard']}}</th>
                        <th class="parameter text-center" style="font-size: 12px !important;width:15% !important;vertical-align: top !important;padding-left:3px !important">{{$view['secondary_standard']}}</th>
                        <th class="parameter text-center" style="font-size: 12px !important;width:10% !important;vertical-align: top !important;">REMARKS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $i = 0;
                    $analysis_type_name = ''
                    ?>
                    @foreach($view['samples'] as $sample )

                    <?php $i++ ?>
                    @if($analysis_type_name != $sample->analysis_type_name)
                    <tr>
                        <td class="parameter" style="font-size: 11px !important;font-weight:600;background-color:#fafafa;padding:1px !important;padding-left:2px !important;" colspan="6">{{strtoupper($sample->analysis_type_name)}}</td>
                    </tr>
                    <?php
                    $analysis_type_name = $sample->analysis_type_name;
                    ?>
                    @endif

                    <tr>
                        <td class="parameter" style="font-size: 10px !important;padding-left:3px !important;">
                            @if($sample->subcontracted == 1)
                            {{$sample->analyte_name}} ({{$sample->reporting_unit}} {{$sample->analyte_code}}) <span style="font-size: 15px;">*</span>
                            @else
                            @if($sample->accredited == 1)
                            {{$sample->analyte_name}} ({{$sample->reporting_unit}} {{$sample->analyte_code}}) <img src="{{$tick}}" alt="tick" height="8" width="8">
                            @else
                            {{$sample->analyte_name}} ({{$sample->reporting_unit}} {{$sample->analyte_code}})
                            @endif
                            @endif
                        </td>
                        <td class="parameter" style="font-size: 10px !important;padding-left:3px !important;">{{$sample->method_name}}</td>
                        <td class="parameter text-center" style="font-size: 10px !important;">{{$sample->reporting_symbol}} {{$sample->result}}</td>
                        <td class="parameter text-center" style="font-size: 10px !important;">{{$sample->guide ?? '-'}}</td>
                        <td class="parameter text-center" style="font-size: 10px !important;">{{$sample->seond_guide ?? '-'}}</td>
                        <td class="parameter text-center" style="font-size: 10px !important;">{{ format_result_remark($sample->remarks) }}</td>
                    </tr>

                    @endforeach
                </tbody>
            </table>
        
        <table style="margin:0px !important">
            <tr style="margin:0px !important">
                <td style="font-size:10px !important;">
                <u><b>Notes and Disclaimer </b><br></u> KS EAS 153:2018 - Packaged Drinking Water —Specification, KS EAS 12:2018 - Potable Water —Specification, ANSI/AAMI/ISO 23500-3:2019 - Preparation and quality management of fluids for haemodialysis and related therapies—Part 3: Water for haemodialysis and related therapies, EMCR 2006 -Environmental Management and Co-ordination (Water Quality) Regulations 2006, WHO 4th EDITION - World Health Organization Guidelines for Drinking - Water Quality.  ISE - Ion Selective Electrode, NS - No Set Standard, ND - Not Detected, Parameters marked with (*) are Subcontracted while <img src="{{$tick}}" height="8" width="8" alt=""> are Accredited.
                   
                </td>
            </tr>
            <tr>
                <td style="font-size: 10px !important;">
                    The test report shall not be reproduced except in full without written approval from Aqualytic Laboratories Ltd. <br> {{$batch->sampled_by_company_personnel == 0 ? 'The laboratory will not be held responsible for any sampling error.': ''}} 
                </td>
            </tr>
            <tr>
                <td>

                    <b style="font-size:12px !important;margin-top:6px !important;"><u>COMMENTS</u></b> <br>
                    <span style="font-size:11px !important">{!! $view['comment'] !!}</span>
                </td>
            </tr>
            <br>
        </table>

        @if($loop->iteration < sizeof($batch_result) ) <div style="page-break-after: always;">
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
        $y = $pdf->get_height() - 50;
        $pdf->page_text($x, $y, $text, $font, $size);
    }
</script>
</body>

</html>