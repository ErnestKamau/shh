@extends('layouts.lab.layout.app', ['dataTable'=>true, 'select2'=>true])



@section('title2')
<title> Proforma-Invoice </title>
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
            'link' => null,
            'name' => 'Billing',
            'icon' => null
        ),
        array(
            'link' => route('invoice-home'),
            'name' => 'Proforma Invoices',
            'icon' => null
        ),



    );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    <h2 class="p-4">
        <i class="mdi mdi-file-cad"></i>Proforma Invoices
    </h2>


    <div class="card tab-card">
        <div class="card-header tab-card-header">
            <ul class="nav nav-tabs card-header-tabs" id="Categories-tabs" role="tablist">

                <li class="nav-item">
                    <a class="nav-link" id="invoice-tab" data-toggle="tab" href="#Invoice" role="tab" aria-controls="Invoice" aria-selected="true"><i style="font-size: 20px;" class="mdi mdi-file-cad"></i> Proforma Invoices</a>
                </li>

            </ul>
        </div>
        <div class="tab-content" id="Invoice-tabs-content">

            <div class="tab-pane fade show active p-3" id="Invoice" role="tabpanel" aria-labelledby="one-tab">
                <div class="form">
                    <form action="/invoice-home" >
                        <b><u>Filter Data By Date :</u></b>
                        <div class="row mt-3 mb-4 border-bottom pl-3">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="" class="control-label">Start Date</label>
                                    <input type="date" class="form-control" name="start_date" value = "{{$start}}" id="" placeholder="Start Date">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="" class="control-label">End Date </label>
                                    <input type="date" class="form-control" value="{{$end}}" name="end_date" id="" placeholder="End Date">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="control-label">Selection Date</label>
                                    <select name="selection_date" id="" class="form-control">
                                        <option value="receipt_date" {{$selection == 'receipt_date' ? 'selected' : ''}} >Batch Receipt Date</option>
                                        <option value="invoice_date" {{$selection == 'invoice_date' ? 'selected' : ''}}>Invoice Created Date</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-outline-warning btn-sm float-right mt-3">Apply</button>
                            </div>
                        </div>

                    </form>
                </div>

                <div class="table-responsive">
                    <table data-filename="ProformaInvoice-{{$start}}-to-{{$end}}" class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
                        <thead class="bg-light p-2">
                            <tr>
                                <th>No</th>
                                <th>Priority</th>
                                <th>Batch Code</th>
                                <th>Sample Codes</th>
                                <th>Receipt Date</th>
                                <th>Approval Date</th>
                                <th>Tax Invoice No</th>
                                <th>Batch Scope</th>
                                <th>Customer Survey</th>
                                <th>Customer</th>
                                <th>Sample Type</th>
                                <th>Reference No</th>
                                <th>Status</th>
                                <th>Invoice No</th>

                                <th nowrap>Payment Method</th>
                                <th>Payment Ref No</th>
                                <th>Transaction No</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($headers as $header)
                            <tr>
                                <td>{{$loop->iteration}}</td>
                                <td>{!! $header->priority == 'High' ? '<span class="mdi mdi-star text-danger"><span>High':$header->priority !!}</td>
                                <td><a href="{{route('invoice-sample-header',['id'=>$header->id])}}">{{$header->batch_code}}</a></td>
                                <td>{{getSampleCodesBySampleHeaderID($header->id)}}</td>
                                <td>{{$header->receipt_date}}</td>
                                <td>{{$header->approval_date ?? '-'}}</td>
                                <td>{{getInvoiceById($header->invoice_id)->tax_invoice ?? '-'}}</td>
                                <td>{{$header->batch_scope}}</td>
                                <td>{{$header->customer_survey}}</td>
                                <td>
                                    <?php
                                    $customer = getCrmCustomerByID($header->crm_customer_id);
                                    $sample = getSampleTypeByID($header->sample_type_id);
                                    ?>
                                    {{$customer->name}}
                                </td>
                                <td>
                                    {{$sample->name}}
                                </td>
                                <td>{{$header->reference_number}}</td>
                                <td>{{$header->status}}</td>
                                <td nowrap>{{$header->invoice_number != '' ? $header->invoice_number : 'N/a'}}</td>
                                <td nowrap>{{$header->payment_method != '' ?$header->payment_method :  'N/a'}}</td>
                                <td nowrap>{{$header->p_ref_no != '' ? $header->p_ref_no: 'N/a'}}</td>
                                <td nowrap>{{$header->transaction != '' ? $header->transaction : 'N/a'}}</td>
                                <td nowrap style="text-align: right;">{{number_format($header->get_invoice_total(),2)}}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</main>
@endsection
@section('script2')



@endsection