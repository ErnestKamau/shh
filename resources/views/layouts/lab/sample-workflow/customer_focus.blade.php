<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
</head>
<body>
    <div class="row">
        <div class="col-md-3">
            <img src="{{$company->logo}}" alt="">
        </div>
        <div class="col-md-9" style="text-align: left !important">
            <p style="font-size: 25px;font-weight:600">{{$company->name}}</p>
            <p>
                {{$company->location}} P.O. Box {{$company->street}} Tel: {{explode(' ',$company->fax)[1] ?? '-'}} Fax: {{explode(' ',$company->fax)[0] ?? '-'}} Cell: {{$company->cell_phone}} Wireless: {{$company->telephone}} {{$company->location}} <br>
                Email: {{$company->email}}
            </p>
        </div>
    </div>
    <div class="title text-center">
        <u> Title: <p><b>SAMPLE SUBMISSION / CUSTOMER FOCUS FOCUS FORM</b></p></u> <span class="ml-3">{{pad_str($batch->id,3)}}</span>
    </div>
    <div class="row">
        <div class="col-md-6">
            <span>
                Authorized By: <br>
                Controlled By: 
            </span>
            <p>
                This form shall be completed in duplicate <br>
                (1) Original to accompany sample to the Laboratory. <br>
                (2) Copy to client for reference.
            </p>
        </div>
        <div class="col-md-6">
            <div class="float-right">
                <p>
                    Document Ref: {{$docs_setting['document_ref']}} <br>
                    Issue: {{$docs_settings['issue']}} <br>
                    Revision: {{$docs_settings['revision']}} <br>
                    Date Of Issue: {{$date_of_issue}} <br>
                </p>
            </div>
        </div>
    </div>
    <div class="main-body">
        <div class="sample-description d-flex">
            <span>SAMPLE DESCRIPTION: </span>
            <span class="ml-2">{{$batch->description}}</span>
        </div>
        <div class="client-ref d-flex">
            <span>CLIENT REF / LPO: </span>
            <span class="ml-2">{{$batch->reference_number}}</span>
        </div>
        <div class="sample-table">
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
                    @foreach($samples as $sample)
                    <tr>
                        <td>{{$sample->sample_code}}</td>
                        <td>{{$sample->getAnalysisRelation() ?? '-'}}</td>
                        <td>{{getStandardByid($sample->main_standard)->name ?? '-'}}</td>
                        <td>{{$sample->comments}}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="batch-declarations border-bottom">
            <ol type="1">
                <li> <input type="checkbox" name="" {{$batch->client_instruction_clear == 1 ? 'checked' :''}} id=""> Are Client`s instructions clear</li>
                <li>Condition and quality of sample <span class="detail-record">{{$batch->condition_quality_sample}}</span></li>
                <li>
                    <ol type="a">
                        <li><input type="checkbox" name="" {{$batch->lab_capable == 1 ? 'checked' : ''}} id=""> Is the Laboratory capable of performing the requested tests?</li>
                        <li><input type="checkbox" name="" {{$batch->can_be_subcontracted == 1 ? 'checked' : ''}} id=""> If no can it be subcontracted to an approved Laboratory?</li>
                        <li>
                            <input type="checkbox" name="" {{$batch->batch_subcontracted_client_approval == 1 ? 'checked' : ''}} id=""> Is the client willing for the sample to be subcontracted?
                        </li>
                    </ol>
                </li>
                <div class="payment-details">
                    Analysis charges Ksh. <span class="detail-record">{{$payment_detail->total_amount}} </span>16% VAT Kshs. <div class="detail-record">{{$payment_detail->vat}}</div> Balance Kshs. <span class="detail-record">{{$payment_detail->balance}} </span> Payments to be made by <span class="detail-record">{{$customer->name}}</span> Contact Person <span class="detail-record">{{$payment_detail->contactpersonname}}</span>
                </div>
                <li>Remarks / Special Instructions <span class="detail-record">{{$batch->batch_instructions}}</span></li>
                <?php $analysis_days = date('Y-m-d', strtotime($batch->get_date('Target Date')['date'])) - $batch->receipt_date ?>
                <li>Duration of Analysis <span class="detail-record">{{$analysis_days}}</span></li>
            </ol>
        </div>
        <div class="batch-details border-bottom">
            <p>
                Date report expected <span class="detail-record">{{date('Y-m-d', strtotime($batch->get_date('Target Date')['date']))}}</span><br>
                Report / Invoice to be sent to <span class="detail-record">{{$customer->name}}</span> By <span class="detail-record">Email</span> <br>
            </p>
            <div class="row">
                <div class="col-md-4">
                    Sampled / Received By: <span class="detail-record">{{$batch->sampled_by}}, {{$batch->received_by}}</span>
                </div>
                <div class="col-md-4">
                    Sign: 
                </div>
                <div class="col-md-4">
                    Date : <span class="detail-record">{{$batch->date_received}}</span>
                </div>
            </div>
        </div>
        <div class="terms">
            <p><b>TERMS AND CONDITIONS OF ANALYSIS</b></p>
            <p class="detail-record">{{$docs_settings['terms_condition']}}</p>
        </div>
        <div class="customer-declaration border-bottom">
            <p><b>DECLARATION: TO BE FILLED BY CUSTOMER:</b></p>
            <p>
                I span.detail-record{{$batch->declaration_customer_name}} agree to the terms and conditions stated herein. <br>
                COMPANY / CLIENT`S NAME: <span class="detail-record">{{$customer->name}}</span> <br>
                <div class="d-flex justify-content-between">
                    <div class="">
                        ADDRESS: <span class="detail-record">P.O. Box {{$customer->postal_address}}</span>
                    </div>
                    <div class="">
                        EMAIL: <span class="detail-record">{{$customer->email}}</span>
                    </div>
                </div>
                <div class="d-flex justify-content-between">
                    <div class="">
                        TELEPHONE NUMBER: <span class="detail-record">{{$customer->telephone1}} /  {{$customer->telephone2}}</span>
                    </div>
                    <div class="">
                        KRA PIN: <span class="detail-record">{{$customer->vat_no}}</span>
                    </div>
                </div>
                <div class="d-flex justify-content-between">
                    <div class="">
                        Date: <span class="detail-record">{{date('Y/m/d',strtotime($batch->declaration_customer_approval_date))}}</span>
                    </div>
                    <div class="">
                        Signature: <img src="{{$batch->declaration_customer_signature}}" alt="">
                    </div>
                    <div class="">
                        Time: <span class="detail-record">{{date('H:i:s',strtotime($batch->declaration_customer_approval_date))}}</span>
                    </div>
                </div>
            </p>
        </div>
        <div class="review-section d-flex justify-content-between">
            <div class="">
                Review done by: <span class="detail-record">{{$batch->declaration_customer_review_name}}</span>
            </div>
            <div class="">
                Signature: <img src="{{$batch->declaration_customer_review_signature}}" alt="">
            </div>
            <div class="">
                Date: <span class="detail-record">{{date('Y/m/d',strtotime($batch->declaration_customer_review_approval_date))}}</span>
            </div>
            <div class="">
                Time: <span class="detail-record">{{date('H:i:s',strtotime($batch->declaration_customer_review_approval_date))}}</span>
            </div>
        </div>
    </div>
</body>
</html>