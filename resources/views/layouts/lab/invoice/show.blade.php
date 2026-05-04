@extends('layouts.lab.layout.app', ['dataTable' => true, 'select2' => true])


@section('title2')
<title> Draft Invoice - Show </title>
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
        'name' => 'Draft Invoices',
        'icon' => null
    ),
    array(
        'link' => null,
        'name' => 'Draft Invoice - ' . $invoice->invoice_number,
        'icon' => null
    ),
);
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    <h2 class="p-4">
        <i class="mdi mdi-file-cad"></i> Draft Invoice |{{$invoice->invoice_number}}
    </h2>
    <div class="invoice-section">
        <div class="card p-3 mb-3">
            <span class="card-title" style="font-weight: 600;"><u><i class="mdi mdi-file-cad"></i> Draft Invoice No
                    {{$invoice->invoice_number}}</u></span>
            <div class="row ml-3">
                <div class="col-md-6">
                    <b class="span-header text-muted"><i class="mdi mdi-chevron-right"></i> Batch(es)</b><br>
                    <span class="span-body">{{implode(', ',$invoice->batchcodes)}}</span>
                </div>
                <div class="col-md-6">
                    <b class="span-header text-muted"><i class="mdi mdi-chevron-right"></i> Sample(s)</b><br>
                    <span class="span-body">{{implode(', ',$invoice->samplecodes)}}</span>
                </div>
                <div class="col-md-4">
                    <b class="span-header text-muted"><i class="mdi mdi-chevron-right"></i> Zoho Draft Invoice ID</b><br>
                    <span class="span-body">{{$invoice->sales_order_id}}</span>
                </div>
                <div class="col-md-4">
                    <b class="span-header text-muted"><i class="mdi mdi-chevron-right"></i> Document Status</b><br>
                    <span class="span-body">{!! $invoice->zoho_so_confirmed ? 'CONFIRMED' : 'DRAFT' !!}</span>
                </div>
                <div class="col-md-4">
                    <b class="span-header text-muted"><i class="mdi mdi-chevron-right"></i> Customer</b><br>
                    <span class="span-body">{{$invoice->crmcustomer->name}}</span>
                </div>
                <div class="col-md-4">
                    <b class="span-header text-muted"><i class="mdi mdi-chevron-right"></i> Amount</b><br>
                    <span class="span-body">{{$invoice->invoicetotal}}</span>
                </div>
                <div class="col-md-4">
                    <b class="span-header text-muted"><i class="mdi mdi-chevron-right"></i> Currency</b><br>
                    <span class="span-body">{{$invoice->currencyinfo->name}}</span>
                </div>
                <div class="col-md-4">
                    <b class="span-header text-muted"><i class="mdi mdi-chevron-right"></i> Created At</b><br>
                    <span class="span-body">{{$invoice->created_at}}</span>
                </div>
                <div class="col-md-4">
                    <b class="span-header text-muted"><i class="mdi mdi-chevron-right"></i> Document Zoho Status</b><br>
                    <span class="span-body">{!! $invoice->sales_order_id != '' ? 'SENT TO ZOHO' : 'NOT SENT' !!}</span>
                </div>
            </div>

        </div>
        <div class="card tab-card">
            <div class="card-header tab-card-header">
                <ul class="nav nav-tabs card-header-tabs" id="invoices-tabs" role="tablist">
                    <li class="nav-item">
                        <a href="#invoice-details-tab" data-toggle="tab" role="tab" aria-controls="invoice-details-tab"
                            aria-selected="true" class="nav-link" id="invoice-details"><i class="mdi mdi-file-cad"></i>
                            Invoice Details</a>
                    </li>
                </ul>
            </div>
            <div class="tab-content" id="invoice-tabs-content">
                <div class="tab-pane fade show active p-3" id="invoice-details-tab" role="tabpanel"
                    aria-labelledby="one-tab">
                    <div class="table-responsive">
                        <table
                            class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm"
                            id="invoice-table">
                            <thead class="bg-light p-2">
                                <tr>
                                    <th class="text-center"> Item</th>
                                    <th class="text-center">Description</th>
                                    <th class="text-center">Quantity</th>
                                    <th class="text-right">Unit Price ({{$invoice->currencyinfo->name}})</th>
                                    <th class="text-right">Amount ({{$invoice->currencyinfo->name}})</th>
                                </tr>
                            </thead>
                            <tbody>

                                @foreach($invoice->details as $detail)

                                    <tr>
                                        <td>{{$loop->iteration}}</td>
                                        <td>{{$detail->analysis_type_name}}</td>
                                        <td>{{$detail->analysis_title}}</td>
                                        <td>{{$detail->quantity}}</td>
                                        <td>{{number_format($detail->final_unit_price,2)}}</td>
                                        <td>{{ number_format($detail->total,2)}}</td>
                                    </tr>
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

<script>
    $(document).ready(function () {

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