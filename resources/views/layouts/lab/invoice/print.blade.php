<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <?php $companyDetails = getCompanyDetails(); ?>
    <title>
        Print Invoice | {{$invoice->invoice_number}}
    </title>
    <!-- Scripts -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.11.2/css/all.min.css" integrity="sha256-+N4/V/SbAFiW1MPBCXnfnP9QSN3+Keu+NlB+0ev/YKQ=" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/MaterialDesign-Webfont/5.3.45/css/materialdesignicons.min.css" />

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <style type="text/css">
        .my-small-text {
            font-size: 12px;
        }
    </style>
</head>

<body onload="window.print()">
    <div class="container">
        <div class="card mt-5 mb-5 ">
            <?php
            $company = getActiveCompany();
            $ids_details_arr = explode(',', $invoice->sample_details_ids);

            $prices = explode(',', $invoice->sample_details_prices);
            $invoice_details = getInvoiceDetails($invoice->id);

            $currency = $invoice->display_currency ?? $invoice->currencyinfo ?? getPricelistCurrency($invoice->id);
            $currencyLabel = $invoice->currency_label ?? ($currency->code ?? $currency->name ?? 'N/A');
            $invoiceTotal = $invoice->resolvedTotalFromDetails();
            $invoiceTax = $invoice->resolvedTaxFromDetails();
            $invoiceSubtotal = $invoiceTotal - $invoiceTax;
            ?>

            <h5 class="card-title bg-light p-2" style="height:60%">
                <img src="{{$company->logo}}" style="height: 40px" />
                <small class="float-right mt-2" style="font-weight: 700;">Invoice Number: {{$invoice->invoice_number}}</small>

            </h5>
            <div class="card-body">
                <div class="row no-gutters">

                    <div class="col-xl-6 col-sm-6">
                        <?php $batches = getInvoiceBatches($invoice->id);
                        $b_arr = [];
                        foreach ($batches as $b) {
                            array_push($b_arr, $b->batch_code);
                        }
                        ?>
                        <p><b>Name: </b>{{$company->name}} <br>
                            <b>Company Address: </b> {{$company->address}} <br>
                            <b>Phone: </b>{{$company->telephone ?? 'N/a'}} <br>
                            <b> Website: </b>{{$company->website}}

                        </p>
                    </div>
                    <div class="col-xl-6 col-sm-6">

                        <p style="text-align: right;"><b>Customer: </b>{{$customer->name}} <br>
                            <b>Reference No : </b>{{$invoice->reference_number}} <br>
                            <b>Phone: </b>{{$customer->telephone1 ?? 'N/a'}} <br>
                            <b>Email: </b> {{$customer->email}}

                        </p>
                    </div>
                </div>

                <div class="mt-2 p-2" style="">
                    <div class="table-responsive">
                        <table class="table table-condensed my-small-text table-striped table-hover table-bordered table-sm">
                            <thead class="bg-light p-2">
                                <tr>
                                    <th class="text-center"> Item No</th>
                                    <th class="text-center">Description</th>
                                    <th class="text-center">Quantity</th>
                                    <th class="text-right">Unit Price ({{ $currencyLabel }})</th>
                                    <th class="text-right">Tax</th>
                                    <th class="text-right">Amount ({{ $currencyLabel }})</th>
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
                                        <p class="float-right">{{ number_format((float) $detail->total, 2) }}</p>
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
                                        <td style="font-size: 1rem;font-weight:600;">Subtotal <small><b>({{ $currencyLabel }})</b></small> : </td>
                                        <td>
                                            <p class="float-right">{{ number_format($invoiceSubtotal, 2) }}</p>
                                        </td>

                                    </tr>

                                    <tr>

                                        <td style="font-size: 1rem;font-weight:600;">Total Tax <small><b>({{ $currencyLabel }})</b></small> : </td>
                                        <td>
                                            <p class="float-right">{{ number_format($invoiceTax, 2) }}</p>
                                        </td>

                                    </tr>
                                    <tr>

                                        <td style="font-size: 1rem;font-weight:600;">Total Amount <small><b>({{ $currencyLabel }})</b></small>: </td>
                                        <td>
                                            <p class="float-right">{{ number_format($invoiceTotal, 2) }}</p>
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

    </div>
</body>



</html>