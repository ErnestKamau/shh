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
        /* margin-top: 20px; */
        margin-bottom: 10px;
        margin-top: 100px;
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
        font: 'Arial Narrow', Arial, sans-serif;
        font-stretch: condensed;
    }

    .header {
        position: fixed;
        top: -80px;
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
        bottom: 180px;
        left: 0;
        right: 0;
        z-index: 1000;
        /* height: 200px!important; */
        background-color: white!important;
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

    .stamp-section {
        position: fixed;
        bottom: 145px;
        right: -4px!important;
        z-index: 1100!important;
    }
    .markings-comments p{
        margin: 0 !important;
        padding : 0 !important
    }
</style>

<body>


    <footer class="footer">
        <table style="width:100%;">
            <tr>
                <td>
                    <div class="text-center" style="font-size:8px">

                        <div class="disclaimer" style="height: 60px;width:auto"></div>

                        @if ($batch->sampled_by_company_personnel == 0)
                            <span class="text-center"><b>NB: This report relates to submitted sample(s) only. The source
                                    and/or markings are as provided by the customer.</b><span>
                        @endif
                    </div>
                </td>
            </tr>
        </table>

        <div style="margin-top:7px">
            <table style="width:100%">
                <tr>
                    <td style="width: 3%" style=" vertical-align: top;display: inline-block;text-align: center;">
                        <img src="data:image/png;base64, {!! $qrcode !!}" style="margin-top: 20px"
                            width="50" height="50">
                        <span style="font-size: 7px !important;display: block;margin-top:3px">Scan to Verify</span>

                    </td>
                </tr>
            </table>
        </div>
    </footer>
    <div class="main-lab" style="position:fixed;bottom:22%;left:1%;font-size:8px;z-index:1100!important;">
        <b>{{ strtoupper($main_lab) }}</b><br>
        <b>{{ $batch->approval_date != '' && $batch->prelim_report_status != 2 ? convertDateFormatReports($batch->approval_date, 'dateShortMonth') : '-' }}</b>
    </div>
    <header class="header">
        <table style="width: 100%;border:0px;">
            <tr>
                <td style="font-size: 10px !important;border:solid 0 transparent !important; width:30% !important">
                    <img src="{{ $path }}" style="height:70px;" alt="logo">

                </td>

                <td style="border: solid 0 transparent !important;text-align:right;font-size:11px !important;">
                    {{ $customer->name }} <br>
                    {{ $customer->postal_address }} <br>
                    {{-- {{ $customer->physical_address }} --}}
                </td>
            </tr>

        </table>
    </header>

    @if (isset($is_stamp->id))
        <div class="stamp-section">
            <img src="{{ $stamp }}" style="height:160px; z-index:1000;position: relative;" alt="">
        </div>
    @endif

    <?php
    $printed_title = [];
    $printed_sig = [];
    $printed_pos = [];
    
    ?>
    @foreach ($samples as $sample)
        <div class="footer_signatures" style="background-color:white !important">
            @if ($batch_approvers->count() > 0)
                <table style="margin-top: 1px !important;margin-bottom:6px!important;width:100%;">
                    <tr>

                        <td style="width:10%"></td>
                        @foreach ($batch_approvers as $approver)
                            <td style="font-size: 8px !important;width:23%">
                                @if (in_array($approver->lab_section_ids,$sample['lab_sect_ids_arr']))
                                    <div style="text-align:center">
                                        <b>{{ $approver->title }}</b>
                                    </div>
                                @endif
                            </td>
                        @endforeach
                        @if (sizeof($sample['lab_sect_ids_arr']) < 3)
                            @foreach (range(1, 3 - sizeof($sample['lab_sect_ids_arr'])) as $indx)
                                <td style="font-size: 8px !important;width:23%"></td>
                            @endforeach
                        @endif
                        <td style="width:10%"></td>
                    </tr>
                    <tr>
                        <td style="width:10%"></td>
                        @foreach ($batch_approvers as $approver)
                            <td style="font-size: 8px !important;width:23%">
                                @if (in_array($approver->lab_section_ids,$sample['lab_sect_ids_arr']))
                                    <div class="dotted-line text-align:center"
                                        style="text-align:center; width:fit-content!important;">
                                        <img src="{{ getCoaApproverSignature($approver->getApproverDetails()->electronic_sig) }}"
                                            style="height:48px;z-index:-10;position:relative;" alt="signature">
                                    </div>
                                @endif
                            </td>
                        @endforeach
                        @if (sizeof($sample['lab_sect_ids_arr']) < 3)
                            @foreach (range(1, 3 - sizeof($sample['lab_sect_ids_arr'])) as $indx)
                                <td style="font-size: 8px !important;width:23%"></td>
                            @endforeach
                        @endif
                        <td style="width:10%"></td>
                    </tr>
                    <tr>
                        <td style="width:10%"></td>
                        @foreach ($batch_approvers as $approver)
                            <td style="font-size: 9px !important;">
                                @if (in_array($approver->lab_section_ids,$sample['lab_sect_ids_arr']))
                                    <div style="text-align:center">{{ $approver->approvershortname }} -
                                        <i>{{ $approver->getApproverPositionDetails() }}</i>
                                    </div>
                                @endif
                            </td>
                        @endforeach
                        @if (sizeof($sample['lab_sect_ids_arr']) < 3)
                            @foreach (range(1, 3 - sizeof($sample['lab_sect_ids_arr'])) as $indx)
                                <td style="font-size: 8px !important;width:23%"></td>
                            @endforeach
                        @endif
                        <td style="width:10%"></td>
                    </tr>
                </table>
            @endif
        </div>
        @if ($sample->getAccredittedCount() >= 1)
            <div class="" style="display:inline-block;position:fixed;bottom:2px;right:1%">
                <img src="{{ $kenas }}" style="width:auto;height:100px" alt="">
            </div>
        @else
            <div class="" style="display:inline-block;position:fixed;bottom:2px;right:1%">
                <img src="{{ $nema }}" style="width:auto;height:100px" alt="">
            </div>
        @endif
        @if ($sample['is_accreddited_status'] == 1)
            <img src="{{ $polucon_disclaimer_not }}" style="width:auto;height:55px;position:fixed;bottom:110px;"
                alt="">
        @else
            <img src="{{ $polucon_disclaimer }}" style="width:auto;height:55px;position:fixed;bottom:110px;"
                alt="">
        @endif

        <main style="margin-bottom:280px">
            <table class="table table-sm" style="font-size: 8px;border:solid 0 transparent !important">
                <thead style="height: 60px !important;">
                    <tr style="margin:0 !important;">
                        <th colspan="{{ $batch->require_mu == 1 ? 6 : 5 }}"
                            style="border: solid 0 transparent !important;border-bottom:1px solid rgba(0, 0, 0, 0.35);padding:0 !important ;margin:0 !important;">
                            <table
                                style="width: 100%;margin:0 !important;margin-bottom:2px;border: 1px solid rgba(0, 0, 0, 0.35) !important">
                                <tr style="margin:0 !important">
                                    <td colspan="2"
                                        style=" border: 1px solid rgba(0, 0, 0, 0.35) !important; font-size:10px !important;">
                                        <b> TEST REPORT NO :
                                            R{{ substr($sample->sample_code, 1, strlen($sample->sample_code)) }}{{ $sample->ammendment_number > 1 ? '-V' . $sample->ammendment_number : '' }}
                                            {{ $report_type != '' ? ' - ' . $report_type : '' }}</b>
                                    </td>
                                </tr>
                                <tr>
                                    <td
                                        style="width:20%;border: solid 0 transparent !important;border-right:0px solid rgba(0, 0, 0, 0.35);font-size:8px !important;border-left:1px solid rgba(0, 0, 0, 0.35);padding:2px;padding-left:4px">
                                        SAMPLE</td>
                                    <td
                                        style="border: solid 0 transparent !important;padding:2px;padding-left:10px !important;font-size:8px !important;border-right:1px solid rgba(0, 0, 0, 0.35);border-left:0px solid rgba(0, 0, 0, 0.35) !important">
                                        {{ strtoupper($sample->product_name) }}</td>
                                </tr>
                                <tr>
                                    <td
                                        style="border: solid 0 transparent !important;border-right:1px solid rgba(0, 0, 0, 0.35);font-size:8px !important;border-left:1px solid rgba(0, 0, 0, 0.35);padding:2px;padding-left:4px">
                                        DATE & PLACE
                                        {{ $sample->sampled_by_company_personnel == 1 ? 'SAMPLED' : 'SUBMITTED' }}</td>
                                    @if ($sample->sampled_by_company_personnel == 1)
                                        <td
                                            style="border: solid 0 transparent !important;padding:2px;padding-left:10px !important;font-size:8px !important;border-right:1px solid rgba(0, 0, 0, 0.35);border-left:1px solid rgba(0, 0, 0, 0.35);">
                                            {{ convertDateFormatReports($sample->date_collected, 'dateShortMonth') ?? '' }}
                                            {{ $sample->sample_point_name ?? 'N/A' }}</td>
                                    @else
                                        <td
                                            style="border: solid 0 transparent !important;padding:2px;padding-left:10px !important;font-size:8px !important;border-right:1px solid rgba(0, 0, 0, 0.35);border-left:1px solid rgba(0, 0, 0, 0.35);">
                                            {{ convertDateFormatReports($sample->receipt_date, 'dateShortMonth') ?? '' }}
                                            {{ $company->name ?? '' }}</td>
                                    @endif
                                </tr>
                                <tr>
                                    <td
                                        style="border: solid 0 transparent !important;border-right:1px solid rgba(0, 0, 0, 0.35);font-size:8px !important;border-left:1px solid rgba(0, 0, 0, 0.35);padding:2px;padding-left:4px">
                                        DATE ANALYSIS STARTED</td>
                                    <td
                                        style="border: solid 0 transparent !important;padding:2px;padding-left:10px !important;font-size:8px !important;border-right:1px solid rgba(0, 0, 0, 0.35);border-left:1px solid rgba(0, 0, 0, 0.35);">
                                        {{ $analysis_date->start_analysis_date != '' ? convertDateFormatReports($analysis_date->start_analysis_date, 'dateShortMonth') : '-' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td
                                        style="border: solid 0 transparent !important;border-right:1px solid rgba(0, 0, 0, 0.35);font-size:8px !important;border-left:1px solid rgba(0, 0, 0, 0.35);padding:2px;padding-left:4px">
                                        SAMPLING METHOD</td>
                                    <td
                                        style="border: solid 0 transparent !important;padding:2px;padding-left:10px !important;font-size:8px !important;border-right:1px solid rgba(0, 0, 0, 0.35);border-left:1px solid rgba(0, 0, 0, 0.35);">
                                        {{ $sample->sampling_method_code ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td
                                        style="border: solid 0 transparent !important;border-right:1px solid rgba(0, 0, 0, 0.35);font-size:8px !important;border-left:1px solid rgba(0, 0, 0, 0.35);padding:2px;padding-left:4px">
                                        SAMPLE ID</td>
                                    <td
                                        style="border: solid 0 transparent !important;padding:2px;padding-left:10px !important;font-size:8px !important;border-right:1px solid rgba(0, 0, 0, 0.35);border-left:1px solid rgba(0, 0, 0, 0.35);">
                                        {{ $sample->sample_code ?? '-' }}{{ $sample->ammendment_number > 1 ? '-V' . $sample->ammendment_number : '' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td 
                                        style="border: solid 0 transparent !important;border-right:1px solid rgba(0, 0, 0, 0.35);font-size:8px !important;border-bottom:1px solid rgba(0, 0, 0, 0.35);border-left:1px solid rgba(0, 0, 0, 0.35);padding:2px;padding-left:4px">
                                        MARKINGS</td>
                                    <td
                                    class="markings-comments" style="border: solid 0 transparent !important;padding:2px;padding-left:10px !important;font-size:8px !important;border-bottom:1px solid rgba(0, 0, 0, 0.35);border-right:1px solid rgba(0, 0, 0, 0.35);border-left: 1px solid rgba(0, 0, 0, 0.35);">
                                        {!! str_replace('<p>&nbsp;</p>', '', $sample->comments ?? 'N/A') !!}</td>
                                </tr>

                            </table>
                        </th>
                    </tr>

                    <tr style="">
                        <th class="parameter"
                            style="font-size: 9px !important; width:20% !important;vertical-align: top !important;padding:5px !important;">
                            TEST</th>
                        <th class="parameter"
                            style="font-size:9px !important;width:16% !important;vertical-align: top !important;padding:5px !important">
                            TEST METHOD</th>
                        <th class="parameter"
                            style="font-size: 9px !important;width:10% !important;vertical-align: top !important;padding:5px !important">
                            RESULTS</th>
                        @if ($batch->require_mu == 1)
                            <th class="parameter"
                                style="font-size: 9px !important;width:10% !important;vertical-align: top !important;padding:5px !important">
                                UNCERTAINITY (+-)</th>
                        @endif
                        <th class="parameter"
                            style="font-size: 9px !important;width:10% !important;vertical-align: top !important;padding:5px !important">
                            UNIT</th>
                        <th class="parameter text-center"
                            style="font-size: 9px !important;width:15% !important;vertical-align: top !important;padding:5px !important">
                            {{ $sample->main_standard_code ?? '' }}</th>
                        @if($sample->secondary_standard > 0)  
                        <th class="parameter text-center"
                            style="font-size: 9px !important;width:15% !important;vertical-align: top !important;padding:5px !important">
                            {{ $sample->sec_standard_code ?? '' }}</th>                      
                        @endif
                        @if($sample->third_standard_id > 0)  
                        <th class="parameter text-center"
                            style="font-size: 9px !important;width:15% !important;vertical-align: top !important;padding:5px !important">
                            {{ $sample->third_standard_code ?? '' }}</th>                      
                        @endif
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
                                    @if ($sample['is_accreddited_status'] == 1)
                                        {!! $captured->analyte_status_contracted == 1 ? '<small>+</small>' : '' !!} {!! $captured->analyte_accredited == 0 ? '<small>*</small>' : '' !!} {!! $captured->isitalic == 1 ? '<i>' . $captured->analyte_code . '</i>' : $captured->analyte_code !!}
                                    @else
                                        {!! $captured->analyte_status_contracted == 1 ? '<small>+</small>' : '' !!} {!! $captured->analyte_accredited == 1 ? '<small>*</small>' : '' !!} {!! $captured->isitalic == 1 ? '<i>' . $captured->analyte_code . '</i>' : $captured->analyte_code !!}
                                    @endif
                                </td>
                                <td class="parameter" style="font-size: 9px !important;padding-left:3px !important;">
                                    {{ strtoupper($captured->method()->name ?? '') }}
                                </td>
                                <td class="parameter {{ $captured->remark == 'FAIL' ? 'textBold' : '' }}"
                                    style="font-size: 9px !important;padding-left:3px !important;">
                                    {{ $captured->result_reporting_symbol ?? '' }}{{ $captured->result != '' ? formatReportResults($captured->result) : 'TBA' }}
                                </td>
                                @if ($batch->require_mu == 1)
                                    <td class="parameter"
                                        style="font-size: 9px !important;padding-left:3px !important;">
                                        {{ $captured->measure_uncertanity ?? '' }}
                                    </td>
                                @endif
                                <td class="parameter" style="font-size: 9px !important;padding-left:3px !important;">
                                    {{ $captured->reporting_unit_id ?? '' }}
                                </td>
                                <td class="parameter " style="font-size: 9px !important;padding-left:3px !important;">
                                    {{ $captured->main_value == 'NS' ? '--' : ($captured->main_value ?? '') }}
                                    {{ getStandardLimitValue($captured->id, $sample->main_standard) ?? '' }}
                                </td>
                                @if($sample->secondary_standard >0)
                                <td class="parameter " style="font-size: 9px !important;padding-left:3px !important;">
                                    {{ $captured->secondary_value == 'NS' ? '--' : ($captured->secondary_value ?? '') }}
                                    {{ getStandardLimitValue($captured->id, $sample->secondary_standard) ?? '' }}
                                </td>
                                @endif
                                @if($sample->third_standard_id > 0)
                                <td class="parameter " style="font-size: 9px !important;padding-left:3px !important;">
                                    {{ $captured->third_value == 'NS' ? '--' : ($captured->third_value ?? '') }}
                                    {{ getStandardLimitValue($captured->id, $sample->third_standard_id) ?? '' }}
                                </td>
                                @endif

                            </tr>
                        @endforeach
                    @endforeach
                    <tr>
                        <td style="font-size: 9px !important;padding-left:3px !important; text-align:center ;border: 0 transparent !important"
                            colspan="{{ $batch->require_mu == 1 ? 6 : 5 }}">******<small>End of Test
                                Results</small>*******</td>
                    </tr>
                </tbody>
            </table>

            <table style="margin:0px !important;width:100%">
                @if ($sample->header_body != '')
                    <tr style="margin:0px !important">
                        <td style="font-size:8px !important;">
                            <b>Comments : </b>{!! $sample->header_body !!}

                        </td>
                    </tr>
                @endif
                @if ($sample->main_body != '')
                    <tr style="margin:0px !important">
                        <td style="font-size:8px !important;">
                            <b>Recommendations : </b>{!! $sample->main_body !!}

                        </td>
                    </tr>
                @endif
                @if ($sample->notes_body != '')
                    <tr style="margin:0px !important">
                        <td style="font-size:8px !important;">
                            <b>Notes : </b>{!! $sample->notes_body !!}

                        </td>
                    </tr>
                @endif

                @if ($sample->ammendment_number > 1)
                    <tr>
                        <td style="font-size:8px !important;">
                            {{ $ammendment->reason }}
                        </td>
                    </tr>
                @endif
            </table>
        </main>
        @if ($loop->iteration < $samples->count())
            <div style="page-break-after: always;">
            </div>
        @endif
    @endforeach
    <script type="text/php">
    if (isset($pdf)) {
        $text = "Page {PAGE_NUM} of {PAGE_COUNT}";
        $size = 9;
        $font = $fontMetrics->getFont("Verdana");
        $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
        $x = ($pdf->get_width() - $width) / 1;
        $y = $pdf->get_height() - 10;
        $pdf->page_text($x, $y, $text, $font, $size);
    }
</script>

</body>

</html>
