<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css"
        integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <style>
        .text-bold {
            font-weight: 500
        }

        u {
            border-bottom: 1px dotted #000;
            text-decoration: none;
        }
        *{
            font-size: 15px;
        }
        /* .bracket_cover:before {
            content: "(" counter(mycounter,lower-latin) ")";
        } */
        .bracket-cover {list-style-type: none;}
        .bracket_cover:before {content: "(" counter(section, lower-alpha) ") ";}
        .bracket_cover { counter-increment: section;}
    </style>
</head>

<body>
    <div class="m-4 card">
        <div class="card-body">

            <div class="row border-bottom">
                <div class="col-md-3">
                    <img src="{{ $company->logo }}" style="width:80%;height:100%;"alt="">
                </div>
                <div class="col-md-9" style="text-align: left !important">
                    <div class="">
                        <span style="font-size: 40px;font-weight:600;">{{ $company->name }}</span> <br>
                        <span>
                            {{ $company->location }} P.O. Box {{ $company->street }} Tel:
                            {{ explode(' ', $company->fax)[1] ?? '-' }} Fax: {{ explode(' ', $company->fax)[0] ?? '-' }}
                            Cell: {{ $company->cell_phone }} Wireless: {{ $company->telephone }}
                            {{ $company->location }} <br>
                            Email: {{ $company->email }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="title mt-2">
                <b class="ml-5 text-danger float-right" style="font-size: 20px">{{ pad_str($batch->id, 3) }}</b>
                <div class="text-center"><u><b style="font-size:20px">SAMPLE SUBMISSION / CUSTOMER FOCUS FOCUS
                            FORM</b></u></div>
            </div>
            <div class="row mt-3">
                <div class="col-md-6">
                    <table class="table-bordered table-sm mb-2" style="font-size:15px">
                        <tr>
                            <td>Authorized By</td>
                            <td>MANAGING DIRECTOR</td>
                        </tr>
                        <tr>
                            <td>Controlled By</td>
                            <td>LABORATORY HEAD</td>
                        </tr>
                    </table>

                    <p>
                        This form shall be completed in duplicate <br>
                        <b>(1) Original to accompany sample to the Laboratory. <br></b>
                        <b>(2) Copy to client for reference.</b>


                    </p>
                </div>
                <div class="col-md-6">
                    <div class="float-right">
                        <table class="table-bordered table-sm">
                            <tr>
                                <td> Document Ref: </td>
                                <td>{{ $docs_settings['document_ref'] }}</td>
                            </tr>
                            <tr>
                                <td>Issue: </td>
                                <td>{{ $docs_settings['issue'] }}</td>
                            </tr>
                            <tr>
                                <td>Revision: </td>
                                <td>{{ $docs_settings['revision'] }}</td>
                            </tr>
                            <tr>
                                <td>Date Of Issue:</td>
                                <td>{{ $batch->date_received }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="main-body"> 
                <table style="width:100%" class="table-sm">
                    <tr>
                        <td style="width:15%">SAMPLE DESCRIPTION:</td>
                        <td style="border-bottom: 1px dotted #000;">{{ $batch->description }}</td>
                    </tr>
                    <tr>
                        <td>CLIENT REF / LPO:</td>
                        <td style="border-bottom: 1px dotted #000;">{{ $batch->reference_number }}</td>
                    </tr>
                </table>
                <div class="sample-table mt-2">
                    <table class="table table-sm table-bordered table-condensed">
                        <thead>
                            <tr>
                                <th>Sample No</th>
                                <th>Test(s) Required</th>
                                <th>Specification</th>
                                <th>Markings</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($samples as $sample)
                                <tr>
                                    <td>{{ $sample->sample_code }}</td>
                                    <td>{{ $sample->getAnalysisRelation() ?? '-' }}</td>
                                    <td>{{ getStandardByid($sample->main_standard)->name ?? '-' }}</td>
                                    <td>{{ $sample->comments }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="batch-declarations border-bottom border-dark" style="width:100%">
                    <ol type="1">
                        <li>Are Client`s instructions clear ?  <input type="checkbox" class="ml-5" name=""
                            {{ $batch->client_instruction_clear == 1 ? 'checked' : '' }} id=""></li>
                        <li style="width:100%">Condition and quality of sample <u><span class="text-bold text-muted ml-5" style="display: inline-block;width:75%">{{ $batch->condition_quality_sample }}</u> </span></li>
                        <li>
                            <ol type="a" class="bracket-cover">
                                <li class="bracket_cover"><input type="checkbox" name=""
                                        {{ $batch->lab_capable == 1 ? 'checked' : '' }} id=""> Is the
                                    Laboratory capable of performing the requested tests?</li>
                                <li class="bracket_cover"><input type="checkbox" name=""
                                        {{ $batch->can_be_subcontracted == 1 ? 'checked' : '' }} id=""> If no
                                    can it be subcontracted to an approved Laboratory?</li>
                                <li class="bracket_cover">
                                    <input type="checkbox" name=""
                                        {{ $batch->batch_subcontracted_client_approval == 1 ? 'checked' : '' }}
                                        id=""> Is the client willing for the sample to be subcontracted?
                                </li>
                            </ol>
                        </li>
                        <div class="payment-details">
                            Analysis charges Ksh. <span class="text-bold text-muted">{{ $batch->invoice_amount }}
                            </span>16% VAT Kshs. <span
                                class="text-bold text-muted">{{ $payment_detail->vat ?? 0 }}</span> <b>amount paid
                            </b>Kshs <span class="text-bold text-muted">{{ $payment_detail->amount ?? 0 }}</span>
                            Balance Kshs. <span class="text-bold text-muted">{{ $payment_detail->balance ?? 0 }}
                            </span> Payments to be made by <span
                                class="text-bold text-muted">{{ $customer->name }}</span> Contact Person <span
                                class="text-bold text-muted">{{ $payment_detail->contactpersonname ?? '-' }}</span>
                        </div>
                        <li>Remarks / Special Instructions <span
                                class="text-bold text-muted">{{ $batch->batch_instructions }}</span></li>

                        <li>Duration of Analysis <span
                                class="text-bold text-muted">{{ $batch->days_of_analysis }}</span></li>
                    </ol>
                </div>
                <div class="batch-details border-bottom border-dark pb-2 mt-2">
                    <p>
                        Date report expected <span
                            class="text-bold text-muted">{{ date('Y-m-d', strtotime($batch->get_date('Target Date')['date'])) }}</span><br>
                        Report / Invoice to be sent to: <span class="text-bold text-muted"
                            style="">{{ $customer->name }}</span> <span style="padding-left:20% !important;">
                            By</span> <span class="text-bold text-muted" style="">Email</span> <br>
                    </p>
                    <div class="row">
                        <div class="col-md-4">
                            Sampled / Received By: <span class="text-bold text-muted">{{ $batch->sampled_by }},
                                {{ $batch->received_by }}</span>
                        </div>
                        <div class="col-md-4">
                            Sign:
                        </div>
                        <div class="col-md-4">
                            Date : <span class="text-bold text-muted">{{ $batch->date_received }}</span>
                        </div>
                    </div>
                </div>
                <div class="terms border-bottom border-dark pb-2 mt-2">
                    <span><b>TERMS AND CONDITIONS OF ANALYSIS</b></span><br>
                    <span>{!! $docs_settings['terms_condition'] !!}</span>
                </div>
                <div class="customer-declaration border-bottom border-dark mt-2 ">
                    <b>DECLARATION: TO BE FILLED BY CUSTOMER:</b>
                    <div class="row mt-2">
                        <div class="col-md-12">
                            I <span class="text-bold text-muted"
                                style="padding-left:5% !important;">{{ $batch->declaration_customer_name ?? '-' }}
                            </span> <span style="padding-left:5% !important;">agree to the terms and conditions stated
                                herein.</span>
                        </div>
                        <div class="col-md-12 mt-2">COMPANY / CLIENT`S NAME: <span
                                class="text-bold text-muted">{{ $customer->name }}</span></div>
                        <div class="col-md-6" style="text-align:left">
                            ADDRESS: <span style="" class="text-bold text-muted">P.O. Box
                                {{ $customer->postal_address }}</span> <br>
                            TELEPHONE NUMBER: <span class="text-bold text-muted"
                                style="">{{ $customer->telephone1 }} / {{ $customer->telephone2 }}</span>
                        </div>
                        <div class="col-md-6" style="text-align:right !important">
                            EMAIL: </span> <span style=""
                                class="text-bold text-muted">{{ $customer->email }}</span><br>
                            KRA PIN: </span> <span class="text-bold text-muted">{{ $customer->vat_no }}</span>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-2">
                        <div class="">
                            Date: <span
                                class="text-bold text-muted">{{ date('Y/m/d', strtotime($batch->declaration_customer_approval_date)) }}</span>
                        </div>
                        <div class="">
                            Signature: <img src="{{ $batch->declaration_customer_signature }}" alt="">
                        </div>
                        <div class="">
                            Time: <span
                                class="text-bold text-muted">{{ date('H:i:s', strtotime($batch->declaration_customer_approval_date)) }}</span>
                        </div>
                    </div>

                </div>
                <div class="review-section d-flex justify-content-between mt-2">
                    <div class="">
                        Review done by: <span class="text-bold text-muted">{{ $review_staff->name ?? '' }}</span>
                    </div>
                    <div class="">
                        Signature: <img src="{{ $review_staff->electronic_signature ?? '' }}" alt="">
                    </div>
                    <div class="">
                        Date: <span
                            class="text-bold text-muted">{{ date('Y/m/d', strtotime($batch->declaration_customer_review_approval_date)) }}</span>
                    </div>
                    <div class="">
                        Time: <span
                            class="text-bold text-muted">{{ date('H:i:s', strtotime($batch->declaration_customer_review_approval_date)) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
