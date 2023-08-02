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
                <b class="ml-5 text-danger float-right" style="font-size: 30px">{{ pad_str($batch->id, 3) }}</b>
                <div class="text-center"><u><b style="font-size:20px">SAMPLE SUBMISSION / CUSTOMER FOCUS
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
                                <td>{{$docs_settings['date_issue']}}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="main-body"> 
                <table style="width:100%" class="table-sm">
                    <tr>
                        <td style="width:15%">SAMPLE DESCRIPTION:</td>
                        <td style="border-bottom: 1px dotted #000;">{{$is_clustered == 0 ? $batch->description : $sample_types }}</td>
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
                                <th>Batch No</th>
                                <th>Sample Type</th>
                                <th>Test(s) Required</th>
                                <th>Specification</th>
                                <th>Markings</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($samples as $sample)
                                <tr>
                                    <td>{{ $sample->sample_code }}</td>
                                    <td>{{$sample->batch_code}}</td>
                                    <td>{{$sample->sample_type_name}}</td>
                                    <td>{{ $sample->getAnalysisRelation() ?? '-' }}</td>
                                    <td>{{ $sample->main_standard_code ?? '-'}}</td>
                                    <td>{{ $sample->comments }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="batch-declarations border-bottom border-dark" style="width:100%">
                    <ol type="1" style="padding:16px !important">
                        <li>Are Client`s instructions clear ?  <input type="checkbox" class="ml-5" name=""
                            {{ $batch->client_instruction_clear == 1 ? 'checked' : '' }} id=""></li>
                        <li style="width:100%">Condition and quality of sample <u><span class="text-bold text-muted ml-5" style="display: inline-block;width:82%">{{ $batch->condition_quality_sample }}</u> </span></li>
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
                            Analysis charges Ksh. <u><span style="display: inline-block;width:10%" class="text-bold text-center text-muted">{{ $is_clustered == 0 ? $batch->invoice_amount  : $payment_detail['invoice_amount']}}
                            </span></u> 16% VAT Kshs. <u> <span
                                class="text-bold text-muted text-center" style="display: inline-block;width:10%" >{{ $is_clustered == 0 ? $payment_detail->vat ?? 0 : $payment_detail['vat'] }}</span></u> <b>amount paid
                            </b>Kshs <u><span style="display: inline-block;width:10%"  class="text-bold text-center text-muted">{{ $is_clustered == 0 ? $payment_detail->amount ?? 0 : $payment_detail['amount_paid'] }}</span></u> Balance Kshs. <u><span style="display: inline-block;width:10%" class="text-bold text-center text-muted">{{ $is_clustered == 0 ? $payment_detail->balance ?? 0  : $payment_detail['balance'] }}
                            </span></u>
                             <br> Payments to be made by <u><span class="text-bold text-muted text-center" style="display: inline-block;width:30%">{{ $customer->name }}</span> </u> Contact Person <u><span style="display: inline-block;width:30%" class="text-bold text-center text-muted">{{ $batch->getContactPersonDetail() ?? '-' }}</span></u> 
                        </div>
                        <li>Remarks / Special Instructions <u><span style="display: inline-block;width:60%" class="text-bold ml-5 text-muted">{{ $batch->batch_instructions }}</span></u> </li>

                        <li>Duration of Analysis <u><span
                            class="text-bold text-muted ml-5" style="display: inline-block;width:50%">{{ $batch->days_of_analysis }} Working Days</span></u> </li>
                    </ol>
                </div>
                <div class="batch-details border-bottom border-dark pb-2 mt-2">
                    <p>
                        Date report expected <u><span class="text-bold text-muted ml-5" style="display: inline-block;width:75%">{{ date('Y-m-d', strtotime($batch->get_date('Target Date')['date'])) }}</span></u><br>
                        Report / Invoice to be sent to: <u><span style="display: inline-block;width:30%" class="text-bold text-center text-muted"
                            style="">{{ $customer->name }}</span></u> <span>
                            By</span> <u><span style="display: inline-block;width:33%" class="text-bold text-muted" style="">Email</span> </u><br>
                    </p>
                    <div class="row">
                        <div class="col-md-4">
                            Sampled / Received By: <u><span style="display: inline-block;width:40%;font-size:11px" class="text-bold text-center text-muted">{{ $batch->sampling_officer_name }},
                                {{ $batch->receiving_officer_name}}</span></u>
                        </div>
                        <div class="col-md-4">
                            Sign:
                        </div>
                        <div class="col-md-4">
                            Date : <u><span style="display: inline-block;width:60%" class="text-bold text-muted"> {{ $batch->receipt_date }}</span></u> 
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
                            I <u><span class="text-bold text-center text-muted"
                                style="!important; display: inline-block;width:50%">{{ $batch->declaration_customer_contact_name ?? '-' }}
                            </span></u> <span style="">agree to the terms and conditions stated
                                herein.</span>
                        </div>
                        <div class="col-md-12 mt-2">COMPANY / CLIENT`S NAME: <u><span style="display: inline-block;width:70%" class="text-bold text-muted ml-3">{{ $customer->name }}</span></u> </div>
                        <div class="col-md-6" style="text-align:left">
                            ADDRESS: <u><span style="display: inline-block;width:70%" class="text-bold text-muted ml-3">P.O. Box {{ $customer->postal_address }}</span></u>  <br>
                            TELEPHONE NUMBER: <u><span style="display: inline-block;width:60%" class="text-bold text-muted ml-3">{{ $customer->telephone1 }} / {{ $customer->telephone2 }}</span></u> 
                        </div>
                        <div class="col-md-6">
                            EMAIL: <u><span style="display: inline-block;width:70%"
                                class="text-bold text-muted ml-3">{{ $customer->email }}</span></u> <br>
                            KRA PIN: <u> <span style="display: inline-block;width:70%" class="text-bold text-muted ml-3">{{ $customer->vat_no }}</span></u>
                        </div>
                    </div>

                    <div class="row mt-3 mb-3">
                        <div class="col-md-4">
                            Date: <u><span class="text-bold text-muted ml-3" style="display: inline-block;width:70%">{{$batch->declaration_customer_approval_date ?  date('Y/m/d', strtotime($batch->declaration_customer_approval_date)) : '-' }}</span></u> 
                        </div>
                        <div class="col-md-4">
                            Signature: {!! $batch->declaration_customer_approval_date !='' ? ' <img src="'.$batch->declaration_customer_signature.'" style="width:150px;height:45px" alt="">' : '-'!!}
                        </div>
                        <div class="col-md-4">
                            Time: <u><span class="text-bold text-muted" style="display: inline-block;width:70%">{{ $batch->declaration_customer_approval_date ? date('H:i:s', strtotime($batch->declaration_customer_approval_date)) : '-' }}</span></u> 
                        </div>
                    </div>

                </div>
                <div class="review-section row mt-2">
                    <div class="col-md-3">
                        Review done by: <u> <span style="display: inline-block;width:40%" class="text-bold text-muted ml-3">{{ $review_staff->name ?? '-'}}</span></u>
                    </div>
                    <div class="col-md-3">
                        Signature: <img src="{{ $review_staff->electronic_signature ?? '' }}" style="width:150px;height:45px" alt="">
                    </div>
                    <div class="col-md-3">
                        Date: <u><span style="display: inline-block;width:50%" class="text-bold text-muted ml-3">{{ date('Y/m/d', strtotime($batch->created_at)) }}</span></u> 
                    </div>
                    <div class="col-md-3">
                        Time: <u><span style="display: inline-block;width:70%"
                            class="text-bold text-muted ml-3">{{ date('H:i:s', strtotime($batch->created_at)) }}</span></u> 
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
