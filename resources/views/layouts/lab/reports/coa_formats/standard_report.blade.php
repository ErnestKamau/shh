<!DOCTYPE html>
<html lang="en">


<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta charset="UTF-8">

    <title>Document</title>
    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.11.2/css/all.min.css" integrity="sha256-+N4/V/SbAFiW1MPBCXnfnP9QSN3+Keu+NlB+0ev/YKQ=" crossorigin="anonymous" /> -->

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css"
        integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">

</head>

<style>
    
    @page {
        /* margin-top: 20px; */
        margin-bottom: 10px;
        margin-top: 150px;
        margin-left: 20;
        margin-right: 20;

        @bottom-center {
            content: element(footer);
        }

        @top-center {
            content: element(header);
        }

    }
    * {
        font-family: "Times New Roman", "Arial Unicode MS", Times, serif;
        font-stretch: normal;
    }

    .header {
        position: fixed;
        top: -130px;
        left: 0;
        right: 0;
        height: 50px;
        z-index: 1000;


    }

    .footer {
        position: fixed;
        bottom: 10px;
        left: 0;
        right: 0;
        z-index: 1;
    }

    .footer_signatures {
        position: fixed;
        bottom: 160px;
        left: 0;
        right: 0;
        z-index: 1000;
        /* height: 200px!important; */
        background-color: white !important;
        /*border: 1px solid red*/
    }

    .dotted-line {
        border: none;
        margin: 0% 35%;
        background-color: rgb(254, 254, 254);
        border-bottom: 3px dotted rgb(0, 0, 0);
    }


    .parameter {
        border: solid 1 rgba(0, 0, 0, 0.35) !important;
        /* border: solid 1 black !important; */

        padding: 0 !important;

    }

    .textBold {
        font-weight: 800 !important;
    }

    .footer_addr {
        font-size: 8px !important;
        font-weight: bolder !important;
    }

    

    .markings-comments p {
        margin: 0 !important;
        padding: 0 !important
    }

    .sample-info tr td {
        padding: 2px 5px !important;
        line-height: 1.3 !important;
    }
    .result-t{
        font-family: "Arial Unicode MS", "Times New Roman", sans-serif !important;
    }
    sup {
        font-size: 0.8em;
        vertical-align: super;
    }

</style>

<body>


    <footer class="footer">
        <div style="margin-top:7px">
            <table style="width:100%" style="border-top:solid 1px black;border-bottom:solid 1px black">
                <tr>
                    <td style="width: 3%" style=" vertical-align: top;display: inline-block;text-align: center;">
                        <img src="data:image/png;base64, {!! $qrcode !!}" style="margin-top: 20px" width="50"
                            height="50">
                        <span style="font-size: 7px !important;display: block;margin-top:3px">Scan to Verify</span>
                    </td>
                    <td style="width: 97%">
                        <div class="text-center" style="font-size: 7px !important;">
                            <p>This document is only valid in its entirety and your attention is drawn to the Terms and
                                Conditions. </p>
                            <p>{{ $disclaimer->value }}</p>

                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </footer>
   

    <?php
$printed_title = [];
$printed_sig = [];
$printed_pos = [];
    
    ?>
   
        <header class="header">
            <table style="width: 100%;border:0px;">
                <tr>
                    <td rowspan="2" style="border:solid 0 transparent !important;">
                        <img src="{{ $sample->getAcredditedStatus() == 1 ? $path : $without_path }}" style="height:130px;width:100%" alt="logo"> <br>
                    </td>
                </tr>
            </table>
        </header>

        <main style="margin-bottom:80px">
            <table class="table table-sm"
                style="font-size: 11px;border:solid 0 transparent !important;border-bottom:solid 2px black !important;width:100%">
                <tr>
                    <td colspan="3" style="border:solid 0 transparent !important;font-size:18px"
                        class="test-report text-center"><b class="">LABORATORY TEST REPORT</b></td>
                </tr>
                <tr>
                    <td style="border:solid 0 transparent !important;width:20% !important">
                        <b>Customer details </b> <br>
                        {{ $customer->name }} <br>
                        {{ $customer->postal_address }} <br>
                        {{ $customer->physical_address }}

                    </td>
                    <td style="border:solid 0 transparent !important;width:50% !important"></td>
                    <td style="border:solid 0 transparent !important;width:30% !important">
                        <b>Document Ref : </b> Q+/F 20 <br>
                        <b>Issue Date : </b> 26.02.2025<br>
                        <b>Version : </b> 13<br>
                        <b>Issued By : </b> Quality Manager
                    </td>
                </tr>
            </table>
            <table class="table table-sm" style="font-size: 10px;border:solid 0 transparent !important;width:100%">
                <tr>
                    <td style="border:solid 0 transparent !important;width:15%"><b>TEST REPORT REF : </b></td>
                    <td style="border:solid 0 transparent !important;width:25%">{{ $sample->report_number.(isset($ammendment->id) ? ' V'.$ammendment->version_number : '' ) ?? $sample->sample_code  }}
                    </td>
                    <td style="border:solid 0 transparent !important;width:12%"><b>SAMPLE TYPE :</b></td>
                    <td style="border:solid 0 transparent !important;width:20%">{{ $sample->sample_type_name }}</td>
                    <td style="border:solid 0 transparent !important;width:18%"><b>SAMPLE REF : </b></td>
                    <td style="border:solid 0 transparent !important;text-align:right;width:5%">{{ $sample->sample_code }}</td>
                </tr>
            </table>
            <table class="table table-sm sample-info"
                style="font-size: 10px;border:solid 0 transparent !important;border-bottom:solid 2px black !important;width:100%;margin-bottom : 0;padding-bottom:1% !important">
                <tr>
                    <td style="border:solid 0 transparent !important;width:16%"><b>Date of sampling / time : </b></td>
                    <td style="border:solid 0 transparent !important;">
                        {{ convertDateFormatReports($sample->date_collected, 'normal') . ' ' . $sample->barcode }} hrs
                    </td>
                    <td style="border:solid 0 transparent !important;width:30%"></td> 
                    <td style="border:solid 0 transparent !important;width:25%"><b>Sampling plan : </b></td>
                    <td style="border:solid 0 transparent !important;text-align:left !important;width:14%">Customer`s Discretion</td>
                </tr>
                <tr>
                    <td style="border:solid 0 transparent !important;"><b>Date of receipt / time : </b></td>
                    <td style="border:solid 0 transparent !important;">
                        {{ convertDateFormatReports($sample->receipt_date, 'normal') . ' ' . $sample->radio_active_levels }} hrs
                    </td>
                    <td style="border:solid 0 transparent !important;width:9%"></td>
                    <td style="border:solid 0 transparent !important;"><b>Temp. of receipt : </b></td>
                    <td style="border:solid 0 transparent !important; text-align:left !important">
                        {{ $sample->kra_office_ref }} {{ is_numeric($sample->kra_office_ref) ?  '°C'  : '' }}
                    </td>
                </tr>
                <tr>
                    <td style="border:solid 0 transparent !important;"><b>Date of analysis : </b></td>
                    <td style="border:solid 0 transparent !important;">
                        {{  $analysis_date->start_analysis_date != '' ? convertDateFormatReports($analysis_date->start_analysis_date, 'normal') : '-' }}
                    </td>
                    <td style="border:solid 0 transparent !important;width:9%"></td>
                    
                    <td style="border:solid 0 transparent !important;"><b>Condition of test item & Environment : </b></td>
                    <td style="border:solid 0 transparent !important;text-align:left !important;">
                        {{ $sample->sample_condition_name }}
                    </td>
                </tr>
                <tr>
                    <td colspan="5" style="border:solid 0 transparent !important;"></td>
                </tr>
            </table>
            <table class="table-sm table"
                style="font-size: 11px;border:solid 0 transparent !important;width:100%;margin:0;padding:0">
                <tr>
                    <td style="border:solid 0 transparent !important;width:22%"><b>Customer’s sample description : </b></td>
                    <td style="border:solid 0 transparent !important;">
                        {!! str_replace('<p>&nbsp;</p>', '', $sample->comments ?? 'N/A') !!}
                    </td>
                </tr>

            </table>
            <table class="table table-sm" style="font-size: 8px;border:solid 0 transparent !important;width:100%">
                <thead style="height: 60px !important; background-color: lightgray;">
                    <tr style="">
                        <th class="parameter"
                            style="font-size: 9px !important; width:20% !important;vertical-align: top !important;padding:5px !important;">
                            Test Parameters</th>
                        <th class="parameter"
                            style="font-size:9px !important;width:16% !important;vertical-align: top !important;padding:5px !important">
                            Test Method</th>
                        <th class="parameter"
                            style="font-size:9px !important;width:16% !important;vertical-align: top !important;padding:5px !important">
                            Ref. Method</th>
                        <th class="parameter"
                            style="font-size: 9px !important;width:10% !important;vertical-align: top !important;padding:5px !important">
                            Results</th>
                        @if ($batch->require_mu == 1)
                            <th class="parameter"
                                style="font-size: 9px !important;width:10% !important;vertical-align: top !important;padding:5px !important">
                                Uncertainity (+-)</th>
                        @endif
                        <th class="parameter"
                            style="font-size: 9px !important;width:10% !important;vertical-align: top !important;padding:5px !important">
                            Unit(s)</th>
                        <th class="parameter text-center"
                            style="font-size: 9px !important;width:15% !important;vertical-align: top !important;padding:5px !important">
                            Specification</th>

                        <th class="parameter text-center"
                            style="font-size: 9px !important;width:15% !important;vertical-align: top !important;padding:5px !important">
                            Rating</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sample->getSampleByAnalysisType() as $analysis_type_level)
                        {{-- <tr>
                            <td class="parameter"
                                style="font-size: 10px !important;font-weight:600;background-color:#fafafa;padding:1px !important;padding-left:2px !important;"
                                colspan="5">{{ strtoupper($analysis_type_level->analysis_type_name ?? '') }}
                            </td>
                        </tr> --}}
                        @foreach ($analysis_type_level->getCapturedResults() as $captured)
                            <tr>
                                <td class="parameter" style="font-size: 9px !important;padding-left:3px !important;">
                                    {!! $captured->analyte_accredited == 0 ? '<small>*</small>' : '' !!}
                                    {!! $captured->isitalic == 1 ? '<i>' . $captured->analyte_code . '</i>' : $captured->analyte_code !!}
                                </td>
                                <td class="parameter" style="font-size: 9px !important;padding-left:3px !important;">
                                    {{ strtoupper($captured->ltmethod->name ?? '') }}
                                </td>
                                <td class="parameter" style="font-size: 9px !important;padding-left:3px !important;">
                                    {{ strtoupper($captured->method()->name ?? '-') }}
                                </td>
                                <td class="parameter result-t {{ $captured->remark == 'FAIL' ? 'textBold text-danger' : '' }}"
                                    style="font-size: 9px !important;padding-left:3px !important;">
                                    {{ $captured->result_reporting_symbol ?? '' }}{!! $captured->result   ?  formatReportResults( $captured->result) : 'TBA' !!}
                                </td>
                                @if ($batch->require_mu == 1)
                                    <td class="parameter" style="font-size: 9px !important;padding-left:3px !important;">
                                        {{ $captured->measure_uncertanity ?? '' }}
                                    </td>
                                @endif
                                <td class="parameter" style="font-size: 9px !important;padding-left:3px !important;">
                                    {{ $captured->reporting_unit_id ?? '' }}
                                </td>
                                <td class="parameter " style="font-size: 9px !important;padding-left:3px !important;">
                                    {{ getStandardLimitValue($captured->id, $sample->main_standard,1) ?? '' }}
                                    {{ $captured->main_value == 'NS' ? '--' : ($captured->main_value ?? '') }}
                                    {{ getStandardLimitValue($captured->id, $sample->main_standard) ?? '' }}
                                </td>
                                <td class="parameter  {{ $captured->remark == 'FAIL' ? 'textBold text-danger' : '' }}"
                                    style="font-size: 9px !important;padding-left:3px !important;">
                                    {{ $captured->remark }}
                                </td>

                            </tr>
                        @endforeach
                    @endforeach
                    <tr>
                        <td style="font-size: 9px !important;padding-left:3px !important; text-align:center ;border: 0 transparent !important"
                            colspan="{{ $batch->require_mu == 1 ? 8 : 7 }}">******<small>End of Test
                                Results</small>*******</td>
                    </tr>
                </tbody>
            </table>
            @if($sample->header_body != '' || $sample->main_body != '' || $sample->notes_body != '' || isset($ammendment->id))
            <table style="margin:0px !important;width:100%">
                @if ($sample->header_body != '')
                    <tr style="margin:0px !important">
                        <td style="font-size:10px !important;">
                            <b>Comments : </b>{!! $sample->header_body !!}
                            {!! isset($ammendment->id)  ? 'This report supersides the original report' : '' !!}
                        </td>
                    </tr>
                @endif
                @if ($sample->main_body != '')
                    <tr style="margin:0px !important">
                        <td style="font-size:10px !important;">
                            <b>Recommendations : </b>{!! $sample->main_body !!}

                        </td>
                    </tr>
                @endif

                @if ($sample->notes_body != '')
                    <tr style="margin:0px !important">
                        <td style="font-size:10px !important;">
                            <b>Notes : </b>{!! $sample->notes_body !!}

                        </td>
                    </tr>
                @endif

                @if (isset($ammendment->id))
                    <tr>
                        <td style="font-size:10px !important;">
                            <b>Ammendment Reason : </b><br>
                            {{ $ammendment->reason }}
                        </td>
                    </tr>
                @endif

            </table>
            @endif
            <table class="table table-sm" style="width:100%;margin:0px">
                <tr>
                    <td style="font-size:10px !important;border:solid 0 transparent">
                        <b><u>Additional Information : </u></b>
                        <ul style="margin: 0;padding-left:15px;">
                            <li>*Indicates test(s) not in SANAS accreditation schedule.</li>
                            <li>These results only apply to the samples as received & tested; the report may not be copied
                                except in full when authorized by the
                                laboratory.</li>
                            <li>Opinions & interpretations expressed herein are outside the scope of SANAS accreditations
                                (customer specifications).</li>
                            <li>UM will be availed on customer’s request</li>
                        </ul>
                    </td>
                </tr>
            </table>
            <div class="" style="background-color:white !important;">
                @if ($batch_approvers->count() > 0)
                    <table style="margin-top: 1px !important;margin-bottom:6px!important;width:100%;">
                        <tr>
                            <!-- <td style="width:10%"></td> -->
                            @foreach ($batch_approvers as $approver)
                                <td style="font-size: 8px !important;width:23%">
                                    @if (in_array($approver->lab_section_ids, $sample['lab_sect_ids_arr']))
                                        <div style="text-align:center">
                                            <b>{{ $approver->title }}</b> <br>
                                            (<i>Technical Signatory</i>)
                                        </div>
                                    @endif
                                </td>
                            @endforeach
                           
                            <td style="width:10%"></td>
                        </tr>
                        <tr>
                            <!-- <td style="width:10%"></td> -->
                            @foreach ($batch_approvers as $approver)
                                <td style="font-size: 8px !important;width:23%; position: relative">
                                    @if (in_array($approver->lab_section_ids, $sample['lab_sect_ids_arr']))
                                        <div class="dotted-lined text-align:center" style="text-align:center;">
                                            <img src="{{ getCoaApproverSignature($approver->getApproverDetails()->electronic_sig) }}"
                                                style="height:48px;z-index:-10;position:relative;" alt="signature">

                                        </div>
                                    @endif
                                </td>
                            @endforeach
                            
                            <td style="width:10%"></td>
                        </tr>
                        <tr>
                            <!-- <td style="width:10%"></td> -->
                            @foreach ($batch_approvers as $approver)
                                <td style="font-size: 9px !important;">
                                    @if (in_array($approver->lab_section_ids, $sample['lab_sect_ids_arr']))
                                        <div style="text-align:center">{{ $approver->approvershortname }}
                                        </div>
                                    @endif
                                </td>
                            @endforeach
                            
                            <td style="width:10%"></td>
                        </tr>
                    </table>
                @endif
            </div>
           
        </main>
      
    <script type="text/php">
    if (isset($pdf)) {
        $text = "Page {PAGE_NUM} of {PAGE_COUNT}";
        $size = 9;
        $font = $fontMetrics->getFont("Verdana");
        $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
        $x = ($pdf->get_width() - $width) / 2;
        $y = $pdf->get_height() - 10;
        $pdf->page_text($x, $y, $text, $font, $size);
    }
</script>

</body>

</html>