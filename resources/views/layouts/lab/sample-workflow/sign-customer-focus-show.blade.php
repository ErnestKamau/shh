@extends('layouts.app', ['select2' => true])

@section('module-name')
    <li class="nav-item">
        <a class="nav-link module-name" href="{{ route('home') }}"><i class="mdi mdi-account"></i> Profile</a>
    </li>
@endsection

@section('title')
    <style type="text/css">
       .wrapper {
            position: relative;
            width: 400px;
            height: 200px;
            -moz-user-select: none;
            -webkit-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }

        .signature-pad {
            position: absolute;
            left: 0;
            top: 0;
            width: 400px;
            height: 200px;
            background-color: white;
        }
    </style>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
@endsection


@section('content')
    <div class="m-4 card">
        <div class="card-body">

            <div class="row border-bottom">
                <div class="col-md-3">
                    <img src="{{ $company->logo }}" style="width:80%;height:60%;"alt="">
                </div>
                <div class="col-md-9" style="text-align: left !important">
                    <div class="">
                        <span style="font-size: 30px;font-weight:600;">{{ $company->name }}</span> <br>
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
                                <td>{!! $sample->comments !!}</td>
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
                                    class="text-bold text-center text-muted">{{ $is_clustered == 0 ? $batch->invoice_amount  : $payment_detail['invoice_amount']}}
                                </span></u> 16% VAT Kshs. <u> <span
                                    class="text-bold text-muted text-center">{{ $is_clustered == 0 ? $payment_detail->vat ?? 0 : $payment_detail['vat'] }}</span></u>
                            <b>amount paid
                            </b>Kshs <u><span
                                    class="text-bold text-center text-muted">{{ $is_clustered == 0 ? $payment_detail->amount ?? 0 : $payment_detail['amount_paid'] }}</span></u>
                            Balance Kshs. <u><span
                                    class="text-bold text-center text-muted">{{ $is_clustered == 0 ? $payment_detail->balance  ?? 0 : $payment_detail['balance'] }}
                                </span></u>
                            <br> Payments to be made by <u><span
                                    class="text-bold text-muted text-center">{{ $$batch->payment_done_by ?? $customer->name}}</span> </u> Contact
                            Person <u><span
                                    class="text-bold text-center text-muted">{{ $batch->getContactPersonDetail() ?? '-' }}</span></u>
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
                <div class="customer-declaration border-bottom border-dark  mt-2 ">
                    <span><b>CUSTOMER DECLARATION</b></span>
                    @if($batch->declaration_customer_approval_date != '')
                    <div class="row mt-2">
                        <div class="col-md-12">
                            I <u> <span style="display: inline-block;width:30%" class="text-bold text-center text-muted">{{ $batch->declaration_customer_contact_name ?? '-' }}
                            </span></u>  <span style="">agree to the terms and conditions stated
                                herein.</span>
                        </div>
                        <div class="col-md-12 mt-2">COMPANY / CLIENT`S NAME: <u><span style="" class="text-bold text-muted ml-3">{{ $customer->name }}</span></u> </div>
                        <div class="col-md-6" style="text-align:left">
                            ADDRESS: <u><span class="text-bold text-muted ml-3">P.O. Box {{ $customer->postal_address }}</span></u>  <br>
                            TELEPHONE NUMBER: <u><span class="text-bold text-muted ml-3">{{ $customer->telephone1 }} / {{ $customer->telephone2 }}</span></u> 
                        </div>
                        <div class="col-md-6">
                            EMAIL: <u><span 
                                class="text-bold text-muted ml-3">{{ $customer->email }}</span></u> <br>
                            KRA PIN: <u> <span  class="text-bold text-muted ml-3">{{ $customer->vat_no }}</span></u>
                        </div>
                    </div>
                    <div class="row mt-3 mb-3">
                        <div class="col-md-4">
                            Date: <u><span class="text-bold text-muted ml-3" style="display: inline-block;width:70%">{{$batch->declaration_customer_approval_date ?  date('Y/m/d', strtotime($batch->declaration_customer_approval_date)) : '-' }}</span></u> 
                        </div>
                        <div class="col-md-4 d-flex">
                            Signature: <img src="{{ $batch->declaration_customer_signature }}" style="width: 150px;height:45px" alt="">
                        </div>
                        <div class="col-md-4">
                            Time: <u><span class="text-bold text-muted" style="display: inline-block;width:70%">{{ $batch->declaration_customer_approval_date ? date('H:i:s', strtotime($batch->declaration_customer_approval_date)) : '-' }}</span></u> 
                        </div>
                    </div>
                    @else
                        <div class="form-group mt-3">
                            <label for="" class="control-label">Contact Person <small class="text-danger">*Is required*</small></label>
                            <input type="text" id="contact-person" name="contact_person" placeholder="Contact Person`s Name..." class="form-control">
                        </div>
                        {{-- <span class="btn btn-sm btn-primary" data-target="#add-signature" data-toggle="modal">Sign Here</span> --}}
                        <div class="container bg-light p-3" style="height:100% !important">
                            <center>

                                <div class="wrapper bg-white m-3 border">
                                    <canvas id="signature-pad" class="signature-pad" width=400 height=200></canvas>
                                </div>
        
                                <button id="save-png">Save Declaration</button>
                                <button class="hidden" id="save-jpeg">Save as JPEG</button>
                                <button class="hidden" id="save-svg">Save as SVG</button>
                                <button id="draw">Draw</button>
                                <button id="erase">Erase</button>
                                <button id="clear">Clear</button>
                            </center>

                        </div>
                    @endif


                </div>

            </div>
        </div>
    </div>
@endsection

@section('script')
    <div class="modal fade" id="add-signature" data-focus="true" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="signature-pad">
                        <canvas id="signatureCanvas" width="300" height="150"></canvas>
                        <button id="clearButton">Clear</button>
                        <button id="drawButton">Draw</button>
                        <button id="saveButton">Save Signature (Base64)</button>
                        <div class="form-group">

                            <textarea id="outputBase64" class="form-control" rows="5" readonly></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <span class="btn btn-sm btn-default text-dnager" data-dismiss="modal">Close</span>
                </div>
            </div>
        </div>
    </div>
    <div class="data-carry" data-batch="{{json_encode($batch->id)}}"></div>
    <script>
        $(()=>{

            var canvas = document.getElementById('signature-pad');
            var batch_id = $('.data-carry').data('batch');
            var IsClustered = 0; 
            
    
            // Adjust canvas coordinate space taking into account pixel ratio,
            // to make it look crisp on mobile devices.
            // This also causes canvas to be cleared.
            function resizeCanvas() {
                // When zoomed out to less than 100%, for some very strange reason,
                // some browsers report devicePixelRatio as less than 1
                // and only part of the canvas is cleared then.
                var ratio = Math.max(window.devicePixelRatio || 1, 1);
                canvas.width = canvas.offsetWidth * ratio;
                canvas.height = canvas.offsetHeight * ratio;
                canvas.getContext("2d").scale(ratio, ratio);
            }
    
            window.onresize = resizeCanvas;
            resizeCanvas();
    
            var signaturePad = new SignaturePad(canvas, {
                backgroundColor: 'rgb(255, 255, 255)' // necessary for saving image as JPEG; can be removed is only saving as PNG or SVG
            });
    
            document.getElementById('save-png').addEventListener('click', function() {
                if (signaturePad.isEmpty()) {
                    return alert("Please provide a signature first.");
                }
    
                var signatureBase64 = signaturePad.toDataURL();
                $.ajaxSetup({
					headers: {
						'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
					}
				});
                $.ajax({
                    url:'/get/Table/Customer-Focus/Signing',
                    method:'POST',
                    data:{
						sample_header_id:batch_id,
						signature:signatureBase64,
                        is_batch : IsClustered,
                        contact_person:$('#contact-person').val(),
						
					},
                    success:(data)=>{
                        
                        window.location.reload();
                    },
                    error:(data)=>{
                        console.log(data);
                    }

                });
                // console.log(data);
                // window.open(data);
    
            });
    
            document.getElementById('save-jpeg').addEventListener('click', function() {
                if (signaturePad.isEmpty()) {
                    return alert("Please provide a signature first.");
                }
    
                var data = signaturePad.toDataURL('image/jpeg');
                console.log(data);
                window.open(data);
            });
    
            document.getElementById('save-svg').addEventListener('click', function() {
                if (signaturePad.isEmpty()) {
                    return alert("Please provide a signature first.");
                }
    
                var data = signaturePad.toDataURL('image/svg+xml');
                console.log(data);
                console.log(atob(data.split(',')[1]));
                window.open(data);
            });
    
            document.getElementById('clear').addEventListener('click', function() {
                signaturePad.clear();
            });
    
            document.getElementById('draw').addEventListener('click', function() {
                var ctx = canvas.getContext('2d');
                console.log(ctx.globalCompositeOperation);
                ctx.globalCompositeOperation = 'source-over'; // default value
            });
    
            document.getElementById('erase').addEventListener('click', function() {
                var ctx = canvas.getContext('2d');
                ctx.globalCompositeOperation = 'destination-out';
            });
        })
    </script>
@endsection
