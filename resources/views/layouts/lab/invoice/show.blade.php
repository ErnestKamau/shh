@extends('layouts.lab.layout.app', ['dataTable'=>true, 'select2'=>true])


@section('title2')
<title> Invoice-Show </title>
<style type="text/css">
    .tab-card {
        border: 1px solid #eee;
    }

    .tab-card-header {
        background: none;
    }

    /* Default mode */
    .tab-card-header>.nav-tabs {
        border: none;
        margin: 0px;
    }

    .tab-card-header>.nav-tabs>li {
        margin-right: 2px;
    }

    .tab-card-header>.nav-tabs>li>a {
        border: 0;
        border-bottom: 2px solid transparent;
        margin-right: 0;
        color: #737373;
        padding: 2px 15px;
    }

    .tab-card-header>.nav-tabs>li>a.show {
        border-bottom: 2px solid #007bff;
        color: #007bff;
    }

    .tab-card-header>.nav-tabs>li>a:hover {
        color: #007bff;
    }

    .tab-card .nav-link.active {
        background-color: #dadccd !important;
        border: 1px solid #cccebf !important;
    }

    .tab-card-header>.tab-content {
        padding-bottom: 0;
    }

    .my-small-text {
        font-size: 13px !important;
    }

    .removeThis {
        z-index: 12;
        position: absolute;
        cursor: pointer;
        top: 0px;
        right: 2px;
        padding: 1px 4px;
        font-size: 12px;
        background-color: red;
        border-radius: 50%;
        color: #fff;
        box-shadow: 0px 0px 5px rgba(0, 0, 0, 0.08);
    }

    .generate {
        box-shadow: 10px 10px 5px black;
        width: 29%;
        height: 10%;
        position: absolute;
        font-size: 20px;
        margin-bottom: 3rem;
        left: 35%;
        color: black;
        padding-top: 20px;


    }
</style>
@endsection
@section('content2')
<main>
    <?php
    $items = array(
        array(
            'link' => route('dashboard-lab'),
            'name' => 'Dashboard',
            'icon' => null
        ),

        array(
            'link' => route('invoice-home'),
            'name' => 'Proforma Invoice',
            'icon' => null
        ),
        array(
            'link' => null,
            'name' => 'Proforma Invoices - ' . $invoice->invoice_number,
            'icon' => null
        ),
    );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    <h2 class="p-4">
        <i class="mdi mdi-file-cad"></i>Batch-Code|{{$header->batch_code}}
        <span class="btn btn-sm btn-outline-primary float-right" data-toggle="modal" data-target="#add-tax-invoice"><i class="mdi mdi-plus"></i> Add Tax Inoice No</span>
        
    </h2>
    <div class="invoice-section">
        <div class="card p-3 mb-3">
            <span class="card-title" style="font-weight: 600;"><u><i class="mdi mdi-file-cad"></i> Batch {{$header->batch_code}}</u></span>
            <div class="row ml-3">
                <div class="col-md-3 col-sm-3 col-lg-3">
                    <i class="mdi mdi-chevron-double-right"></i> Priority : {!! $header->priority == 'High' ? '<span class="mdi mdi-star text-danger">High</span>':$header->priority !!}
                </div>
                <div class="col-md-3 col-sm-3 col-lg-3">
                    <i class="mdi mdi-chevron-double-right"></i> Batch Code : {{$header->batch_code}}
                </div>
                <div class="col-md-3 col-sm-3 col-lg-3">
                    <i class="mdi mdi-chevron-double-right"></i> Reference Number : {{$header->reference_number}}
                </div>
                <div class="col-md-3 col-sm-3 col-lg-3">
                    <i class="mdi mdi-chevron-double-right"></i> Customer : {{ getCrmCustomerByID($header->crm_customer_id)->name }}
                </div>
                <div class="col-md-3 col-sm-3 col-lg-3">
                    <i class="mdi mdi-chevron-double-right"></i> Sample Type : {{ getSampleTypeByID($header->sample_type_id)->name}}
                </div>
                <div class="col-md-3 col-sm-3 col-lg-3">
                    <i class="mdi mdi-chevron-double-right"></i> Receipt Date : {{$header->receipt_date}}
                </div>
                <div class="col-md-3 col-sm-3 col-lg-3">
                    <i class="mdi mdi-chevron-double-right"></i> Date Collected : {{$header->date_collected}}
                </div>
                <div class="col-md-3 col-sm-3 col-lg-3">
                    <i class="mdi mdi-chevron-double-right"></i> Samples : {{$details->count()}}
                </div>
                <div class="col-md-3 col-sm-3 col-lg-3">
                    <i class="mdi mdi-chevron-double-right"></i> Status : {{$header->status}}
                </div>
                <div class="col-md-3 col-sm-3 col-lg-3">
                    <i class="mdi mdi-chevron-double-right"></i> Tax Invoice No : {{$invoice->tax_invoice ?? '-'}}
                </div>
            </div>

        </div>
        <div class="card tab-card">
            <div class="card-header tab-card-header">
                <ul class="nav nav-tabs card-header-tabs" id="invoices-tabs" role="tablist">
                    <li class="nav-item">
                        <a href="#invoice-details-tab" data-toggle="tab" role="tab" aria-controls="invoice-details-tab" aria-selected="true" class="nav-link" id="invoice-details"><i class="mdi mdi-file-cad"></i> Invoice Details</a>
                    </li>
                    <li class="nav-item">
                        <a href="#invoice-payment-details" data-toggle="tab" role="tab" aria-controls="invoice-payment-details" aria-selected="true" class="nav-link" id="invoice-details-payment"><i class="mdi mdi-credit-card-check"></i> Payment Details</a>
                    </li>
                </ul>
            </div>
            <div class="tab-content" id="invoice-tabs-content">
                <div class="tab-pane fade show active p-3" id="invoice-details-tab" role="tabpanel" aria-labelledby="one-tab">
                    @if(isset($invoice->id))
                    <h5 class="mb-5">

                        <span class="btn btn-sm btn-default float-right text-info" data-target="#edit-invoice-details" data-toggle="modal"><i class="mdi mdi-pencil-box-multiple-outline"></i> Edit</span>
                        <span class="btn btn-outline-default float-right btn-sm text-warning" data-toggle="modal" data-target="#send-invoice"> <i class="mdi mdi-share-all"></i> Email Invoice</span>
                        <span class="btn btn-outline-default float-right mr-2 btn-sm text-primary" data-toggle="modal" data-target="#upload-invoice"><i class="mdi mdi-upload"></i> Upload Invoice</span>
                        <span data-target="#print-invoice" data-toggle="modal" class="btn btn-outline-default text-success float-right mr-2 btn-sm"><i class="mdi mdi-printer-check"></i> Print / Export Invoice</span>
                    </h5>
                    <div class="modal fade" id="upload-invoice">
                        <div class="modal-dialog">
                            <form action="{{ route('upload-invoice') }}" method="post" enctype="multipart/form-data" class="modal-content">
                                @csrf
                                <div class="modal-header">
                                    <h4 class="modal-title"><i class="mdi mdi-upload"></i> Upload Invoice {{$invoice->invoice_number}}</h4>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label class="control-label">Choose Invoice</label>
                                        <input type="file" name="invoice" class="form-control" required />
                                    </div>
                                    <div class="form-group hidden">
                                        <label class="control-label">Invoice Id</label>
                                        <input type="number" name="invoice_id" value={{$invoice->id}} class="form-control">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-outline-primary "> <i class="mdi mdi-content-save"></i> Submit</button>
                                    <button type="button" class="btn btn-outline-danger " data-dismiss="modal">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- <br><br> -->
                    <div class="card ">
                        <?php
                        $company = getActiveCompany();
                        $ids_details_arr = explode(',', $invoice->sample_details_ids);

                        $prices = explode(',', $invoice->sample_details_prices);
                        $invoice_details = getInvoiceDetails($invoice->id);
                        $taxes = getTotaltaxAmount($invoice->id);
                        $currency = getPricelistCurrency($invoice->id);
                        $active = getActiveCompany();
                        ?>

                        <h5 class="card-title bg-light p-2" style="height:60%">
                            <img src="{{$active->logo}}" style="height: 40px" />
                            <small class="float-right mt-2" style="font-weight: 700;">Invoice Number: {{$invoice->invoice_number}}</small>

                        </h5>
                        <div class="card-body">
                            <div class="row no-gutters">

                                <div class="col-xl-6 col-sm-6">
                                    <p><b>Name: </b>{{$company->name}} <br>
                                        <b>Company Address: </b> {{$company->address}} <br>
                                        <b>Phone: </b>{{$company->telephone ?? 'N/a'}} <br>
                                        <b> Website: </b>{{$company->website}}</p>
                                </div>
                                <div class="col-xl-6 col-sm-6">
                                    <?php $batches = getInvoiceBatches($invoice->id);
                                    $b_arr = [];
                                    foreach ($batches as $b) {
                                        array_push($b_arr, $b->batch_code);
                                    }
                                    ?>
                                    <p style="text-align: right"><b>Customer: </b>{{$customer->name}} <br>
                                        <b>Reference No : </b>{{$invoice->reference_number ?? 'N/a'}} <br>
                                        <b>Phone: </b>{{$customer->telephone1 ?? 'N/a'}} <br>
                                        <b>Email: </b> {{$customer->email}}

                                    </p>
                                </div>
                            </div>

                            <div class=" mt-4 p-2" style="">
                                <div class="table-responsive">
                                    <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm" id="invoice-table">
                                        <thead class="bg-light p-2">
                                            <tr>
                                                <th class="text-center"> Item No</th>
                                                <th class="text-center">Description</th>
                                                <th class="text-center">Quantity</th>
                                                <th class="text-right">Unit Price ({{$currency->name}})</th>
                                                <th class="text-right">Tax</th>
                                                <th class="text-right">Amount ({{$currency->name}})</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                            @foreach($invoice_details as $detail)

                                            <tr>
                                                <td>{{$loop->iteration}}</td>
                                                <td>{{$detail->analysis_type_name}}</td>
                                                <td>{{$detail->quantity}}</td>
                                                <td>
                                                    <p class="float-right"> {{number_format($detail->selling_price,2) }}</p>
                                                </td>
                                                <td>
                                                    <p class="float-right">{!! $detail->tax_rate != '0' ? $detail->tax_rate.'%' : 'Tax Exempted' !!}</p>
                                                </td>
                                                <td>
                                                    <?php
                                                    $detail_total = $detail->selling_amount * $detail->quantity;
                                                    ?>
                                                    <p class="float-right">{{ number_format($detail_total,2) }}</p>
                                                </td>
                                            </tr>
                                            @endforeach

                                        </tbody>

                                    </table>
                                </div>
                                <div class="row">
                                    <div class="col-lg-5 col-sm-6 ml-auto">
                                        <table class="table table-clear">
                                            <tbody>

                                                <tr>
                                                    <?php $invoice_subtotal = $invoice->total - $invoice->total_tax; ?>
                                                    <td style="font-size: 1rem;font-weight:600;">Subtotal <small><b>({{$currency->name}})</b></small> : </td>
                                                    <td>
                                                        <p class="float-right">{{number_format($invoice_subtotal,2)}}</p>
                                                    </td>

                                                </tr>

                                                <tr>

                                                    <td style="font-size: 1rem;font-weight:600;">Total Tax <small><b>({{$currency->name}})</b></small> : </td>
                                                    <td>
                                                        <p class="float-right">{{number_format($invoice->total_tax,2)}}</p>
                                                    </td>

                                                </tr>
                                                <tr>

                                                    <td style="font-size: 1rem;font-weight:600;">Total Amount <small><b>({{$currency->name}})</b></small>: </td>
                                                    <td>
                                                        <p class="float-right">{{number_format($invoice->total,2) }}</p>
                                                    </td>
                                                </tr>
                                                <tr>

                                                    <td class="mb-0"><b class="mb-0">Due Date :</b></td>
                                                    <td class="mb-0">
                                                        <p class="float-right mb-0">{{$invoice->due_date}}</p>
                                                    </td>

                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-center" style="font-size: 25px;font-weight:570">
                            <i class="mdi mdi-check-circle"></i><br>

                            Thank you !

                        </div>


                    </div>


                    @else
                    <a href="{{route('generate-invoice',['id'=>$header->id])}}">
                        <div class="card p-3 generate btn-warning">
                            <span class="mdi mdi-certificate"> Generate Invoice</span>

                            <!-- <div class="row no-gutter">
                                   <div class="col-sm-3" style="font-size: 5rem;"></div>
                                   <div class="col-sm-9 pt-4" style="font-size: 2rem;"></div>
                               </div> -->
                        </div>
                    </a>
                    <div class="card p-3">
                        <h5 class="card-title"><i class="mdi mdi-test-tube"></i>Samples</h5>
                        <div class="table-responsive">
                            <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
                                <thead class="bg-light p-2">
                                    <tr>
                                        <th>No</th>
                                        <th>Sample Code</th>
                                        <th>Sample Header</th>
                                        <th>Analysis Type</th>
                                        <th>Barcode</th>
                                        <th>Created At</th>

                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($details as $detail)
                                    <?php
                                    $name = array();
                                    if (strlen($detail->analysis_type_id) > 1) {
                                        $ids = explode(',', $detail->analysis_type_id);
                                        foreach ($ids as $id) {

                                            $analysis = getAnalysisTypeID($id);
                                            array_push($name, $analysis->name);
                                        }
                                    } else {
                                        $analysis = getAnalysisTypeID($detail->analysis_type_id);
                                        array_push($name, $analysis->name);
                                    }
                                    $name_str = implode(',', $name);
                                    ?>
                                    <tr>
                                        <td>{{$loop->iteration}}</td>
                                        <td>{{$detail->sample_code}}</td>
                                        <td>{{$header->batch_code}}</td>
                                        <td>{{$name_str}}</td>
                                        <td>{{$detail->barcode}}</td>
                                        <td>{{$detail->created_at}}</td>

                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>


                    @endif
                </div>
                <div class="tab-pane fade p-3" id="invoice-payment-details" role="tabpanel" aria-labelledby="one-tab">
                    <h5>
                        <i class="mdi mdi-credit-card"></i> Payment Details
                        @if(isset($invoice->id))
                        <span class="mdi mdi-plus btn btn-outline-primary btn-sm float-right mb-2" data-target="#add-payment-details" data-toggle="modal"> Add</span>
                        @endif
                    </h5>
                    <div class="table-responsive">
                        <table class="table table-condensed table-bordered table-sm table-hover stripped table-bordered my-small-text" style="width: 100%;">
                            <thead class="bg-light p-2">
                                <tr>
                                    <th>No</th>
                                    <th nowrap>Payment Method</th>
                                    <th>Amount</th>
                                    <th>Reference No</th>
                                    <th>Transanction No</th>
                                    <th>Credit Days</th>
                                    <td>Received By</td>
                                    <th nowrap></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($payments as $payment)
                                @if($payment->is_delete == 0)
                                <tr>
                                    <td>{{$loop->iteration}}</td>
                                    <td>{{$payment->payment_method}}</td>
                                    <td style="text-align: right;">{{number_format($payment->amount,2) }}</td>
                                    <td>{{$payment->ref_no}}</td>
                                    <td>{{$payment->transaction_no}}</td>
                                    <td>{{$payment->credit_days}}</td>
                                    <?php $received = getUserById($payment->received_by) ?>
                                    <td>{{$received->name}}</td>
                                    <td>
                                        <span class="btn btn-outline-default text-primary mdi mdi-pencil btn-sm" data-toggle="modal" data-target="#edit-payment-detail-{{$payment->id}}" data-toggle="tooltip" title="Edit Payment Detail">
                                        </span>
                                        <span class="btn btn-outline-default text-danger mdi mdi-delete-empty btn-sm" data-toggle="modal" data-target="#delete-payment-detail-{{$payment->id}}" data-toggle="tooltip" title="Delete Payment Detail"></span>

                                        <div class="modal fade" id="delete-payment-detail-{{$payment->id}}" role="dialog">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form action="{{ route('payment-detail-delete') }}" enctype="multipart/form-data" method="post">
                                                        @csrf
                                                        <div class="modal-body">
                                                            <input type="hidden" name="payment_id" value="{{$payment->id}}">
                                                            <div class="alert alert-danger">
                                                                <i class="mdi mdi-alert"></i> Confirm you want to delete payment detail {{$loop->iteration}}
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="submit" class="btn btn-outline-primary btn-sm "> <i class="mdi mdi-content-save"></i> Confirm</button>
                                                            <button type="button" class="btn btn-outline-danger " data-dismiss="modal">Cancel</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="modal fade" id="edit-payment-detail-{{$payment->id}}" role="dialog">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form action="{{ route('payment-detail-edit') }}" enctype="multipart/form-data" method="post">
                                                        @csrf
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">
                                                                <i class="mdi mdi-pencil text-primary"></i> Edit Payment {{$loop->iteration}}
                                                            </h5>
                                                        </div>
                                                        <div class="modal-body">
                                                            <input type="hidden" name="payment_id" value="{{$payment->id}}">
                                                            <div class="form-group">
                                                                <label class="control-label">Payment Method <span class="text-danger">*</span></label>
                                                                <select name="method" id="select-payment-method" class="form-control" aria-placeholder="Select Payment Method..." required>
                                                                    <option value="Mpesa" {{$payment->payment_method == 'Mpesa' ? 'selected':''}}>Mpesa</option>
                                                                    <option value="Cheque" {{$payment->payment_method == 'Cheque' ? 'selected':''}}>Cheque</option>
                                                                    <option value="Cash" {{$payment->payment_method == 'Cash' ? 'selected':''}}>Cash</option>
                                                                    <option value="Credit" {{$payment->payment_method == 'Credit' ? 'selected':''}}></option>
                                                                    <option value="EFT" {{$payment->payment_method == 'EFT' ? 'selected':''}}></option>
                                                                    <option value="Free" {{$payment->payment_method == 'Free' ? 'selected':''}}></option>
                                                                </select>
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="control-label">Amount <span class="text-danger">*</span></label>
                                                                <input type="number" step=".01" value="{{$payment->amount}}" name="amount" placeholder="Amount... " class="form-control">
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="control-label">Credit Days</label>
                                                                <input type="number" name="credit_days" value="{{$payment->credit_days}}" id="" class="form-control">
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="control-label">Reference No</label>
                                                                <input type="text" class="form-control" name="ref_no" value="{{$payment->ref_no}}" placeholder="Reference Number...">
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="control-label">Transaction No</label>
                                                                <input type="text" class="form-control" name="transaction_number" value="{{$payment->transaction_no}}" placeholder="Reference Amount...">
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="submit" class="btn btn-outline-primary "> <i class="mdi mdi-content-save"></i> Save</button>
                                                            <button type="button" class="btn btn-outline-danger " data-dismiss="modal">Cancel</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>



</main>

@endsection
@section('script2')
<div class="modal fade" id="add-payment-details" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('payment-detail-add')}}" enctype="multipart/form-data" method="post">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="mdi mdi-plus text-primary"></i> Add Payment
                    </h5>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="invoice_id" value="{{$invoice->id}}">
                    <div class="form-group">
                        <label class="control-label">Payment Method <span class="text-danger">*</span></label>
                        <select name="method" id="select-payment-method" class="form-control" aria-placeholder="Select Payment Method..." required>
                            <option value="">Choose Payment Method...</option>
                            <option value="Mpesa">Mpesa</option>
                            <option value="Cheque">Cheque</option>
                            <option value="Cash">Cash</option>
                            <option value="Credit">Credit</option>
                            <option value="EFT">EFT</option>
                            <option value="Free">Free</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="control-label">Amount ({{$currency->name}}) <span class="text-danger">*</span></label>
                        <input type="number" step=".01" value="" name="amount" placeholder="Amount... " class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="control-label">Credit Days</label>
                        <input type="number" name="credit_days" value="{{$customer->credit_days}}" id="" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="control-label">Reference No</label>
                        <input type="text" class="form-control" name="ref_no" value="" placeholder="Reference Amount...">
                    </div>
                    <div class="form-group">
                        <label class="control-label">Transaction No</label>
                        <input type="text" class="form-control" name="transaction_number" value="" placeholder="Reference Amount...">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-outline-primary "> <i class="mdi mdi-content-save"></i> Save</button>
                    <button type="button" class="btn btn-outline-danger " data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="send-invoice">
    <div class="modal-dialog">
        <form action="{{route('email-invoice')}}" method="post" class="modal-content">
            @csrf

            <div class="modal-body">

                <div class="alert alert-info">
                    <i class="mdi mdi-share-all"></i> Confirm you want to email the report to {{$customer->name}}.
                </div>
                <input type="hidden" name="header_id" value="{{$header->id}}">
                <input type="hidden" name="invoice_id" value="{{$invoice->id}}">
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-outline-primary "> <i class="mdi mdi-content-save"></i> Submit</button>
                <button type="button" class="btn btn-outline-danger " data-dismiss="modal">Cancel</button>
            </div>
        </form>
    </div>
</div>
<div class="modal fade" id="print-invoice">
    <div class="modal-dialog">
        <form action="{{route('print-invoice',['id'=>$invoice->id])}}" target="_blank" method="post" class="modal-content">
            @csrf

            <div class="modal-body">
                <input type="hidden" name="invoice_id" value="{{$invoice->id}}">
                <div class="alert alert-success">
                    <i class="mdi mdi-checkbox-marked-circle"></i> Confirm you want to print invoice {{$invoice->invoice_number}}
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" id="submit-invoice" class="btn btn-outline-success btn-sm"> <i class="mdi mdi-content-save"></i> confirm</button>
                <button type="button" id="print-invoice-close" class=" btn btn-outline-danger btn-sm " data-dismiss="modal">Cancel</button>
            </div>
        </form>
    </div>
</div>
<div class="modal fade" id="edit-invoice-details" data-backdrop="static" data-keyboard="false" role="dialog">
    <div class="modal-dialog modal-lg">
        <form action="{{route('edit_invoice')}}" method="POST" enctype="multipart/form-data" class="modal-content">
            @csrf
            <input type="hidden" name="invoice_id" value="{{$invoice->id}}">
            <div class="modal-body">
                <div class="alert alert-info text-center">
                    <i class="mdi mdi-alert-decagram"></i> Edit Invoice {{$invoice->invoice_number}}
                </div>
                <table class="table table-condensed table-sm table-bordered">
                    <thead>
                        <th>No</th>
                        <th>Description</th>
                        <th>Qty</th>
                        <th>Cost Price ({{$currency->name}})</th>
                        <th>Unit Price ({{$currency->name}}) <small class="text-danger">(excluding tax *)</small></th>
                        <th>Tax</th>
                    </thead>
                    <tbody>
                        
                        @foreach($invoice_details as $detail)
                        <tr>
                            <input type="hidden" name="detail_id[]" value="{{$detail->id}}">
                            <td>{{$loop->iteration}}</td>
                            <td class="bg-light">{{$detail->analysis_type_name}}</td>
                            <td class="bg-light">1</td>
                            <td>
                                <input type="text" style="text-align: right;" name="cost_price[]" value="{{$detail->cost_price}}" class="form-control">
                                </td>
                            <td >
                                <?php $detail_total = $detail->selling_amount * $detail->quantity;?>
                                <input type="text" style="text-align: right;" name="selling_price[]" value="{{$detail->selling_price}}" class="form-control">
                            </td>
                            <td   class="bg-light">
                                <p class="float-right" style="text-align: right;">{!! $detail->tax_rate != '0' ? $detail->tax_rate.'%' : 'Tax Exempted' !!}</p>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-outline-primary btn-sm"> <i class="mdi mdi-content-save"></i> Save</button>
                <button type="button" class="btn btn-default btn-sm " data-dismiss="modal">Cancel</button>
            </div>
        </form>
    </div>
</div>
<div id="add-tax-invoice" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <form action="{{route('add_tax_invoice')}}" method="post" class="modal-content">
            @csrf
            <div class="modal-body">
                <div class="alert alert-primary p-2">
                    <i class="mdi mdi-plus"></i> Add or Update tax invoice number below:
                </div>
                <div class="form-group">
                    <label class="control-label">Tax Invoice Number</label>
                    <input type="text" name="tax_invoice" value="{{$invoice->tax_invoice}}" class="form-control">
                    <input type="hidden" name="invoice_id" value="{{$invoice->id}}">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-primary btn-sm" type="post"><i class="mdi mdi-content-save"></i> Save</button>
                <span class="btn btn-sm btn-default" data-dismiss="modal">Close</span>
            </div>
        </form>
    </div>
</div>
<script>
    $(document).ready(function() {
        function fetch_user() {
            $.ajax({
                url: '/chat/userdetails',
                method: 'POST',
                success: function(data) {
                    $('#user_details').html(data);
                }
            })
        }
        $('#submit-invoice').on('click', function() {
            $('#print-invoice-close').trigger('click');
        });
        var table = $('#invoice-table').DataTable();
        table.destroy();
        $('#invoice-table').DataTable({

            "paging": false,
            "ordering": false,
            "info": false,
            "searching": false
        });
    })
</script>
@endsection