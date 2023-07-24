@extends('layouts.app', ['select2' => true])

@section('module-name')
    <li class="nav-item">
        <a class="nav-link module-name" href="{{ route('home') }}"><i class="mdi mdi-account"></i> Profile</a>
    </li>
@endsection

@section('title')
    <style type="text/css">
         .signature-pad {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 50px;
            
        }

        #signatureCanvas {
            border: 1px solid #000;
            margin-bottom: 20px;
            background-color: blue
        }

        button {
            padding: 10px 20px;
            margin: 5px;
            cursor: pointer;
        }

        textarea {
            width: 100%;
        }
    </style>
@endsection


@section('content')
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
                                <td>{{ $docs_settings['date_issue'] }}</td>
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
                    <ol type="1" style="padding:16px !important">
                        <li>Are Client`s instructions clear ? <input type="checkbox" class="ml-5" name=""
                                {{ $batch->client_instruction_clear == 1 ? 'checked' : '' }} id=""></li>
                        <li style="width:100%">Condition and quality of sample <u><span class="text-bold text-muted ml-5"
                                    style="display: inline-block;width:82%">{{ $batch->condition_quality_sample }}</u>
                            </span></li>
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
                            Analysis charges Ksh. <u><span
                                    class="text-bold text-center text-muted">{{ $batch->invoice_amount }}
                                </span></u> 16% VAT Kshs. <u> <span
                                    class="text-bold text-muted text-center">{{ $payment_detail->vat ?? 0 }}</span></u>
                            <b>amount paid
                            </b>Kshs <u><span
                                    class="text-bold text-center text-muted">{{ $payment_detail->amount ?? 0 }}</span></u>
                            Balance Kshs. <u><span
                                    class="text-bold text-center text-muted">{{ $payment_detail->balance ?? 0 }}
                                </span></u>
                            <br> Payments to be made by <u><span
                                    class="text-bold text-muted text-center">{{ $customer->name }}</span> </u> Contact
                            Person <u><span
                                    class="text-bold text-center text-muted">{{ $payment_detail->contactpersonname ?? '-' }}</span></u>
                        </div>
                        <li>Remarks / Special Instructions <u><span
                                    class="text-bold ml-5 text-muted">{{ $batch->batch_instructions }}</span></u> </li>

                        <li>Duration of Analysis <u><span class="text-bold text-muted ml-5">{{ $batch->days_of_analysis }}
                                    Working Days</span></u> </li>
                    </ol>
                </div>
                <div class="batch-details border-bottom border-dark pb-2 mt-2">
                    <p>
                        Date report expected <u><span
                                class="text-bold text-muted ml-5">{{ date('Y-m-d', strtotime($batch->get_date('Target Date')['date'])) }}</span></u><br>
                        Report / Invoice to be sent to: <u><span class="text-bold text-center text-muted"
                                style="">{{ $customer->name }}</span></u> <span>
                            By</span> <u><span class="text-bold text-muted" style="">Email</span> </u><br>
                    </p>
                    <div class="row">
                        <div class="col-md-4">
                            Sampled / Received By: <u><span style="font-size:8px"
                                    class="text-bold text-center text-muted">{{ $batch->sampling_officer_name }},
                                    {{ $batch->receiving_officer_name }}</span></u>
                        </div>
                        <div class="col-md-4">
                            Sign:
                        </div>
                        <div class="col-md-4">
                            Date : <u><span class="text-bold text-muted"> {{ $batch->receipt_date }}</span></u>
                        </div>
                    </div>
                </div>
                <div class="terms border-bottom border-dark pb-2 mt-2">
                    <span><b>TERMS AND CONDITIONS OF ANALYSIS</b></span><br>
                    <span>{!! $docs_settings['terms_condition'] !!}</span>
                </div>
                <div class="customer-declaration border-bottom border-dark mt-2 ">
                    <b>DECLARATION: TO BE FILLED BY CUSTOMER:</b>

                    <form action="" method="">
                        <div class="signature-pad">
                            <canvas id="signatureCanvas" width="400" height="200"></canvas>
                            <button id="clearButton">Clear</button>
                            <button id="saveButton">Save Signature (Base64)</button>
                            <div class="form-group">

                                <textarea id="outputBase64" class="form-control" rows="5" readonly></textarea>
                            </div>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
@endsection

@section('script')
<script>
    window.addEventListener("load", () => {
        const canvas = document.getElementById("signatureCanvas");
        const clearButton = document.getElementById("clearButton");
        const saveButton = document.getElementById("saveButton");
        const outputBase64 = document.getElementById("outputBase64");

        const ctx = canvas.getContext("2d");
        ctx.lineWidth = 2;
        ctx.strokeStyle = "#000";

        let isDrawing = false;
        let points = [];

        function startDrawing(event) {
            isDrawing = true;
            points = [];
            addPoint(event);
        }

        function stopDrawing() {
            isDrawing = false;
        }

        function addPoint(event) {
            const rect = canvas.getBoundingClientRect();
            const x = event.clientX - rect.left;
            const y = event.clientY - rect.top;
            points.push({
                x,
                y
            });
        }

        function drawPoints() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.beginPath();
            points.forEach((point, index) => {
                if (index === 0) {
                    ctx.moveTo(point.x, point.y);
                } else {
                    ctx.lineTo(point.x, point.y);
                }
            });
            ctx.stroke();
        }

        function clearCanvas() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            points = [];
        }

        function saveSignatureAsBase64() {
            const dataURL = canvas.toDataURL("image/png");
            const base64Signature = dataURL.split(",")[1];
            outputBase64.value = base64Signature;
            console.log(dataURL);
            window.open(dataURL);


        }

        canvas.addEventListener("mousedown", startDrawing);
        canvas.addEventListener("mousemove", (event) => {
            if (isDrawing) {
                addPoint(event);
                drawPoints();
            }
        });
        canvas.addEventListener("mouseup", stopDrawing);
        canvas.addEventListener("mouseleave", stopDrawing);

        clearButton.addEventListener("click", clearCanvas);
        saveButton.addEventListener("click", saveSignatureAsBase64);
    });
</script>
@endsection
