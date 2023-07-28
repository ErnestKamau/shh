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
        .header {
            position: fixed;
            top: 0px;
            left: 0;
            right: 0;
            height: 100px;
            z-index: 1000;
        }
        main{
            position: fixed;
            top:250;
            left: 0;
            right: 0;
        }

        @bottom-center {
            content: element(footer);
        }

        @top-center {
            content: element(header);
        }

    }


    .header {
        position: fixed;
        top: 0px;
        left: 0;
        right: 0;
        height: 100px;
        z-index: 1000;


    }

    /* .header::before{
        position: running(header);
    } */
    .footer {
        position: fixed;
        bottom: 140;
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

        <table class="" style="margin-top: 1px !important; border-bottom:1px solid black;width:100%">

            <tr>
                @foreach ($batch_approvers as $approver)
                    <td style="font-size: 8px !important;">
                        <b>{{ $approver->title }}</b><br>
                        <img src="{{ getCoaApproverSignature($approver->getApproverDetails()->electronic_sig) }}"
                            style="width:80px" alt="signature"><br>
                        <span>{{ $approver->getApproverDetails()->name }} -
                            {{ $approver->getApproverPositionDetails() }}</span>
                    </td>
                @endforeach

            </tr>
        </table>

        <div style="margin-top:10px">
            <table style="width:100%">
                <tr>
                    <td style="width: 10%">
                        <img src="data:image/png;base64, {!! $qrcode !!}" width="60" height="60"> <br>
                        <span style="font-size: 8px !important;">Scan to Verify</span>

                    </td>
                    <td style="text-align: center">
                        <div class=""><b>{{ $company->name }}</b></div>
                        <div class="company-location p-2">
                            <table style="font-size: 7px;width:100%">
                                <tr>
                                    <td colspan="3" style="font-size: 8px !important;">{{ $company->street }} - P.O.
                                        Box {{ $company->address }},
                                        {{ $company->location }}</td>
                                </tr>
                                <tr>
                                    <td style="font-size: 8px !important;">Office: {{ $company->telephone }}</td>
                                    <td style="font-size: 8px !important;">Tel 1:
                                        {{ explode('/', $company->cell_phone)[0] ?? '' }}</td>
                                    <td style="font-size: 8px !important;">Email: {{ $company->email }}</td>
                                </tr>
                                <tr>
                                    <td style="font-size: 8px !important;">Fax: {{ $company->fax }}</td>
                                    <td style="font-size: 8px !important;">Tel2:
                                        {{ explode('/', $company->cell_phone)[0] ?? '' }}</td>
                                    <td style="font-size: 8px !important;">Web: {{ $company->website }}</td>
                                </tr>


                            </table>
                        </div>
                        <div class="text-center" style="font-size: 8px !important;"><u><b>Member of POLUCON
                                    Group</b></u></div>
                    </td>
                    <td style="width:30%"></td>
                </tr>
            </table>
        </div>
    </footer>
    <header class="header">
        <table style="width: 100%;border:0px;">
            <tr>
                <td style="font-size: 10px !important;border:solid 0 transparent !important; width:30% !important">
                    <img src="{{ $path }}" style="height:90px;" alt="logo">

                </td>

                <td style="border: solid 0 transparent !important;text-align:right;font-size:11px !important;">
                    {{ $customer->name }} <br>
                    P.O BOX {{ $customer->postal_address }} <br>
                    {{ $customer->physical_address }}
                </td>
            </tr>

        </table>
    </header>

    @foreach ($samples as $sample)
        <main style="margin-bottom: 200px !important;">
            <div class="test-report p-2"
                style=" border: 1px solid rgba(0, 0, 0, 0.35) !important; font-size:10px !important;width:100%">
                <b>TEST REPORT NO : R{{ substr($sample->sample_code, 1, strlen($sample->sample_code)) }}</b>
            </div>
            
            <table class="table table-sm table-bordered" style="width:100%;font-size: 8px">
                <tr>
                    <td style="text-align: center" colspan="4"><b>Analysis Certificate</b></td>
                </tr>
                <tr>
                    <td><b>SHIPPER</b></td>
                    <td>{{ $batch->importer_address }}</td>
                    <td><b>VESSEL NAME</b></td>
                    <td>{{ $batch->declared_commodity_code }}</td>
                </tr>
                <tr>
                    <td><b>CONSIGNEE</b></td>
                    <td>{{ $batch->radio_active_levels }}</td>
                    <td><b>BL NUMBER</b></td>
                    <td>{{ $batch->declared_amount }}</td>
                </tr>
                <tr>
                    <td><b>NOTIFY PARTY (1)</b></td>
                    <td>{{ $batch->kra_office_ref }}</td>
                    <td><b>PORT OF LOADING</b></td>
                    <td>{{ $batch->how_sample_was_obtained }}</td>

                </tr>
                <tr>
                    <td><b>NOTIFY PARTY (2)</b></td>
                    <td>{{ $batch->kra_office_station }}</td>
                    <td><b>PORT OF DISCHARGE</b></td>
                    <td>{{ $batch->sample_appearance_description }}</td>
                </tr>
                <tr>
                    <td><b>GOODS DESCRIPTION</b></td>
                    <td>{{ $batch->description }}</td>
                    <td><b>DATE SAMPLED</b></td>
                    <td>{{ $batch->date_collected }}</td>
                </tr>
                <tr>
                    <td><b>QUANTITY</b></td>
                    <td>{{ $batch->net_quantity_and_unit_of_quantity }}</td>
                    <td><b>PLACE SAMPLED</b></td>
                    <td>{{ $batch->where_sample_was_obtained }}</td>
                </tr>
                <tr>
                    <td><b>START DATE OF ANALYSIS</b></td>
                    <td>{{ $batch->receipt_date }}</td>
                    <td><b>SAMPLED BY</b></td>
                    <td>{{ $batch->sampling_officer_name }}</td>
                </tr>
                <tr>
                    <td><b>FINISH DATE OF ANALYSIS</b></td>
                    <td>{{ date('Y-m-d') }}</td>
                    <td><b>SAMPLING METHOD</b></td>
                    <td>{{ $sample->sample_method_name }}</td>
                </tr>
            </table>
            @foreach ($sample['getBrandOuts'] as $key => $brands)
                @if ($key == 'normal' && sizeof($brands) > 0)
                    <table class="table table-sm table-bordered" style="font-size: 8px;width:100%">
                        <thead>
                            <tr>
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
                            @foreach ($brands as $analysis_type_level)
                                <tr>
                                    <td class="parameter"
                                        style="font-size: 10px !important;font-weight:600;background-color:#fafafa;padding:1px !important;padding-left:2px !important;"
                                        colspan="5">
                                        {{ strtoupper($analysis_type_level->analysis_type_name) }}</td>
                                </tr>
                                @foreach ($analysis_type_level->getCapturedResults() as $captured)
                                    <tr>
                                        <td class="parameter {{ $captured->remark == 'FAIL' ? 'textBold' : '' }}"
                                            style="font-size: 9px !important;padding-left:3px !important;">
                                            {!! $captured->analyte_status_contracted == 1 ? '<small>*</small>' : '' !!} {{ $captured->analyte_code }}
                                        </td>
                                        <td class="parameter {{ $captured->remark == 'FAIL' ? 'textBold' : '' }}"
                                            style="font-size: 9px !important;padding-left:3px !important;">
                                            {{ $captured->method()->name }}
                                        </td>
                                        <td class="parameter {{ $captured->remark == 'FAIL' ? 'textBold' : '' }}"
                                            style="font-size: 9px !important;padding-left:3px !important;">
                                            {{ $captured->result_reporting_symbol ?? '' }}{{ $captured->result }}
                                        </td>
                                        <td class="parameter {{ $captured->remark == 'FAIL' ? 'textBold' : '' }}"
                                            style="font-size: 9px !important;padding-left:3px !important;">
                                            {{ $captured->analyte()->reporting_unit }}
                                        </td>
                                        <td class="parameter {{ $captured->remark == 'FAIL' ? 'textBold' : '' }}"
                                            style="font-size: 9px !important;padding-left:3px !important;">
                                            {{ $captured->main_value }}
                                        </td>

                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                @elseif($key == 'physical' && sizeof($brands) > 0)
                    <table class="table-sm table-bordered" style="font-size: 8px;width:100%">
                        <thead>
                            <tr style="">
                                <th class="parameter"
                                    style="font-size: 9px !important; width:25% !important;vertical-align: top !important;padding:5px !important;">
                                    TESTS</th>

                                <th class="parameter"
                                    style="font-size: 9px !important;width:10% !important;vertical-align: top !important;padding:5px !important">
                                    RESULTS</th>

                                <th class="parameter text-center"
                                    style="font-size: 9px !important;width:15% !important;vertical-align: top !important;padding:5px !important">
                                    {{ $sample->main_standard_code }}</th>

                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($brands as $analysis_type_level)
                                <tr>
                                    <td class="parameter"
                                        style="font-size: 10px !important;font-weight:600;background-color:#fafafa;padding:1px !important;padding-left:2px !important;"
                                        colspan="3">
                                        {{ strtoupper($analysis_type_level->analysis_type_name) }}</td>
                                </tr>
                                @foreach ($analysis_type_level->getCapturedResults() as $captured)
                                    <tr>
                                        <td class="parameter {{ $captured->remark == 'FAIL' ? 'textBold' : '' }}"
                                            style="font-size: 9px !important;padding-left:3px !important;">
                                            {!! $captured->analyte_status_contracted == 1 ? '<small>*</small>' : '' !!} {{ $captured->analyte_code }}
                                        </td>

                                        <td class="parameter {{ $captured->remark == 'FAIL' ? 'textBold' : '' }}"
                                            style="font-size: 9px !important;padding-left:3px !important;">
                                            {{ $captured->result_reporting_symbol ?? '' }}{{ $captured->result }}
                                        </td>

                                        <td class="parameter {{ $captured->remark == 'FAIL' ? 'textBold' : '' }}"
                                            style="font-size: 9px !important;padding-left:3px !important;">
                                            {{ $captured->main_value }}
                                        </td>

                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                @elseif($key == 'pesticide' && sizeof($brands) > 0)
                    <table class="table-sm table-bordered" style="font-size: 8px;width:100%">
                        <thead>
                            <tr style="">
                                <th class="parameter"
                                    style="font-size: 9px !important; width:25% !important;vertical-align: top !important;padding:5px !important;">
                                    S/N</th>
                                <th class="parameter"
                                    style="font-size: 9px !important; width:25% !important;vertical-align: top !important;padding:5px !important;">
                                    PESTICIDE</th>

                                <th class="parameter"
                                    style="font-size: 9px !important;width:10% !important;vertical-align: top !important;padding:5px !important">
                                    RESULTS (ppm)</th>

                                <th class="parameter text-center"
                                    style="font-size: 9px !important;width:15% !important;vertical-align: top !important;padding:5px !important">
                                    LIMITS</th>
                                <th class="parameter"
                                    style="font-size: 9px !important; width:25% !important;vertical-align: top !important;padding:5px !important;">
                                    S/N</th>
                                <th class="parameter"
                                    style="font-size: 9px !important; width:25% !important;vertical-align: top !important;padding:5px !important;">
                                    PESTICIDE</th>

                                <th class="parameter"
                                    style="font-size: 9px !important;width:10% !important;vertical-align: top !important;padding:5px !important">
                                    RESULTS (ppm)</th>

                                <th class="parameter text-center"
                                    style="font-size: 9px !important;width:15% !important;vertical-align: top !important;padding:5px !important">
                                    LIMITS</th>

                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($brands as $analysis_type_level)
                                <tr>
                                    <td class="parameter"
                                        style="font-size: 10px !important;font-weight:600;background-color:#fafafa;padding:1px !important;padding-left:2px !important;"
                                        colspan="8">
                                        {{ strtoupper($analysis_type_level->analysis_type_name) }}</td>
                                </tr>
                                <?php $counter = 1; ?>
                                @foreach ($analysis_type_level->getCapturedResults() as $captured)
                                    <tr>
                                        <td class="parameter {{ $captured[0]->remark == 'FAIL' ? 'textBold' : '' }}"
                                            style="font-size: 9px !important;padding-left:3px !important;">
                                            {{ $counter }}
                                        </td>
                                        <?php $counter = $counter + 1; ?>
                                        <td class="parameter {{ $captured[0]->remark == 'FAIL' ? 'textBold' : '' }}"
                                            style="font-size: 9px !important;padding-left:3px !important;">
                                            {!! $captured->analyte_status_contracted == 1 ? '<small>*</small>' : '' !!} {{ $captured->analyte_code }}
                                        </td>

                                        <td class="parameter {{ $captured[0]->remark == 'FAIL' ? 'textBold' : '' }}"
                                            style="font-size: 9px !important;padding-left:3px !important;">
                                            {{ $captured->result_reporting_symbol ?? '' }}{{ $captured->result }}
                                        </td>

                                        <td class="parameter {{ $captured[0]->remark == 'FAIL' ? 'textBold' : '' }}"
                                            style="font-size: 9px !important;padding-left:3px !important;">
                                            {{ $captured->main_value }}
                                        </td>

                                        <td class="parameter {{ $captured[1]->remark == 'FAIL' ? 'textBold' : '' }}"
                                            style="font-size: 9px !important;padding-left:3px !important;">
                                            {{ $counter }}
                                        </td>
                                        <?php $counter = $counter + 1; ?>
                                        <td class="parameter {{ $captured[1]->remark == 'FAIL' ? 'textBold' : '' }}"
                                            style="font-size: 9px !important;padding-left:3px !important;">
                                            {!! $captured->analyte_status_contracted == 1 ? '<small>*</small>' : '' !!} {{ $captured->analyte_code }}
                                        </td>

                                        <td class="parameter {{ $captured[1]->remark == 'FAIL' ? 'textBold' : '' }}"
                                            style="font-size: 9px !important;padding-left:3px !important;">
                                            {{ $captured->result_reporting_symbol ?? '' }}{{ $captured->result }}
                                        </td>

                                        <td class="parameter {{ $captured[1]->remark == 'FAIL' ? 'textBold' : '' }}"
                                            style="font-size: 9px !important;padding-left:3px !important;">
                                            {{ $captured->main_value }}
                                        </td>

                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                @endif
            @endforeach
            <div class="end-test"  style="font-size: 9px !important; text-align:center ;border: 0 transparent !important;width:100%">
                ******<small>End of Test Results</small>*******</td>
            </div>
            <div class="comments" style="font-size: 8px !important;width:100%">
                <b>Comments : </b>{{ $sample->header_body }} 
            </div>
            <div class="accreddited-reasons">
                <span>{{ $non_accredited->value }}</span>

                <div class="" style="">
                    {{ $disclaimer->value }}
                    @if ($batch->sampled_by_company_personnel == 0)
                        <br>
                        <b>NB: This report relates to submitted sample(s) only. The source and markings are as
                            provided by the customer.</b>
                    @endif
                </div>
            </div>
            @if ($sample->getAccredittedStatus() >= 1)
                <div class="" style="display:inline-block;position:fixed;bottom:50;left:70%">
                    <img src="{{ $kebs }}" style="width:60px;height:60px" alt="">

                    <img src="{{ $kenas }}" style="width:60px;height:60px" alt="">


                    <img src="{{ $ilac }}" style="width:60px;height:60px" alt="">
                </div>
            @else
                <div class="" style="display:inline-block;position:fixed;bottom:50;left:70%">
                    <img src="{{ $kebs }}" style="width:60px;height:60px" alt="">

                    <img src="{{ $nema }}" style="width:60px;height:60px" alt="">


                    <img src="{{ $ispm }}" style="width:60px;height:60px" alt="">
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
