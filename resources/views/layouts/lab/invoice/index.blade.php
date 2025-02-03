@extends('layouts.lab.layout.app', ['dataTable'=>true, 'select2'=>true])



@section('title2')
<title> Sales-Order </title>
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
        <i class="mdi mdi-file-cad"></i> Sales Orders
    </h2>


    <div class="card tab-card">
        <div class="card-header tab-card-header">
            <ul class="nav nav-tabs card-header-tabs" id="Categories-tabs" role="tablist">

                <li class="nav-item">
                    <a class="nav-link" id="invoice-tab" data-toggle="tab" href="#Invoice" role="tab" aria-controls="Invoice" aria-selected="true"><i style="font-size: 20px;" class="mdi mdi-file-cad"></i> Sales Order</a>
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
                                        <option value="due_date" {{$selection == 'due_date' ? 'selected' : ''}} >Sales Order Due Date</option>
                                        <option value="invoice_date" {{$selection == 'invoice_date' ? 'selected' : ''}}> Created Date</option>
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
                                <th>#</th>
                                <th>Sales Order No</th>
                                <th>Status</th>
                                <th>Zoho ID</th>
                                <th>Amount</th>
                                <th>Batch Codes</th>
                                <th>Sample Codes</th>
                                <th>Created At</th>
                                <th>Customer</th>
                                <th>Currency</th>
                                <th>Confirmed Date</th>
                                
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($sales as $sale)
                                <tr>
                                    <td><a href="{{route('invoice-sample-header',['id'=>$sale->id])}}" class="btn btn-sm btn-default text-success"><i class="mdi mdi-eye"></i></a></td>
                                    <td> <a href="{{route('invoice-sample-header',['id'=>$sale->id])}}" class="btn btn-sm text-primary">{{$sale->invoice_number}}</a></td>
                                    <td><b>{{$sale->zoho_so_confirmed ? 'Confirmed' : 'Draft'}}</b></td>
                                    <td>{{$sale->sales_order_id  ? $sale->sales_order_id : 'N/A'}}</td>
                                    <td>{{$sale->invoicetotal}}</td>
                                    <td>{{sizeof($sale->batchcodes) > 0 ? implode(',',$sale->batchcodes) : 'N/A' }}</td>
                                    <td>{{sizeof($sale->samplecodes) > 0 ? implode(',',$sale->samplecodes) : 'N/A'}}</td>
                                    <td>{{$sale->created_at}}</td>
                                    <td>{{$sale->crmcustomer->name}}</td>
                                    <td>{{$sale->currencyinfo->name}}</td>
                                    <td>{{$sale->zoho_so_confirmed }}</td>
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