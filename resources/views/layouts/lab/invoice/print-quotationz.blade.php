<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <?php $companyDetails = getCompanyDetails(); ?>
    <title>
        Print Quotation | {{$header->quote_number}}
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

<body onload="window.print()" class="container">
    <div class="card p-3 mt-2" id="quotation-document">
        <div class="card-header p-0" style="border-bottom: 1px solid #0000ff;background-color:white ">
            <div class="header p-3">

                {!! $company->show_on_reports == 1 ? '<img src='.$company->logo.' style="position:absolute;width:260px;" class="float-right mt-3" />':'' !!}
                <div class="company-info float-right" style="font-size: 12px;">

                    <p style="text-align: right;">

                        {{$company->name}} <br>
                        {{$company->address ?? '-'}} <br>
                        {{$company->location ?? '-'}} <br>
                        {{$company->street ?? '-'}} <br>
                        Email: {{$company->email ?? '-'}} <br>
                        Website: {{$company->website ?? '-'}} <br>
                        Tel: {{$company->telephone}} Cell: {{$company->cell_phone ?? '-'}}
                    </p>

                </div>
            </div>


        </div>
        <div class="card-body p-3">
            <h5 class="text-center mt-0 mb-0"><b>QUOTATION</b></h5>
            <div class="quote_header p-0" style="font-size: 11px;">
                <div class="customer-details float-left">
                    <p>{{$header->name}} <br>
                        {{$header->physical_address}} <br>
                        {{$header->postal_address}}</p>

                    <p>{{$header->first_name}} {{$header->middle_name}} {{$header->last_name}} <br>
                        {{$header->mobile}} <br>
                        {{$header->email}}</p>
                </div>
                <div class="quotation_detail float-right">
                    <p style="text-align: right;"><b>Quotation Number: </b>{{$header->quote_number}} <br>
                        Quote Date: {{$header->quote_date}} <br>
                        Expiring Date: {{$header->expiring_date}} <br>
                        Prepared By : {{$header->prepared_by}} <br>
                        Position: {{$header->position}} <br>
                        Phone: {{$header->phone ?? '-'}} <br>
                        Email: {{$header->prepared_by_email}}</p>
                </div>
            </div>
            <table class="table  table-condensed my-small-text table-striped table-hover table-bordered table-md">
                <thead style="background-color: #75ee4a !important; ">
                    <th>No</th>
                    <th>Sample Type</th>
                    <th nowrap>Part No</th>
                    <th nowrap>Description</th>

                    <th nowrap>Quantity</th>
                    <th nowrap>Unit Price</th>
                    <th nowrap>Tax</th>
                    <th>Extended Price</th>

                </thead>
                <tbody>
                    @foreach($details as $detail)
                    <tr>
                        <td>{{$loop->iteration}}</td>
                        <td style="min-width: 130px;">{{$detail->sample_type_name}}</td>
                        <td>{{$detail->part_no_final}}</td>
                        @if($header->quotation_type == 'General')
                        <td>
                            @if($detail->photo_url != '')
                            <img src="{{$detail->photo_url}}" style="height:120px; width:auto" alt="image">
                            @endif
                            <p><b>Item Name :</b> {{$detail->item_name}}</p>
                            <p><b>Description :</b> {{$detail->description}}</p>
                        </td>
                        @else
                        <td>
                            <p class="mb-0"><b>{{$detail->sample_type_name}}</b></p>
                            <p class="mb-0"><b>Description:</b></p>
                            @foreach($detail->default as $da)
                            <span>{{$da}}, </span>
                            @endforeach
                            @foreach($detail->sub_acc as $sb)
                            <span>{{$sb}}* <img src="/images/tick.png" height="8" width="8" alt="">, </span>
                            @endforeach
                            @foreach($detail->sub_analytes as $sa)
                            <span>{{$sa}}*, </span>

                            @endforeach
                            @foreach($detail->acc_analytes as $acc)
                            <span class="">{{$acc}} <img src="/images/tick.png" height="8" width="8" alt="">, </span>
                            @endforeach

                        </td>
                        @endif
                        <td class="text-center">{{$detail->quantity}}</td>
                        <td style="text-align: right;">{{number_format($detail->unit_price)}}</td>
                        <td style="text-align: right;">{{number_format($detail->tax)}}</td>
                        <td style="text-align: right;">{{number_format($detail->extended_price)}}</td>

                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="row">
                <div class="col-lg-5 col-sm-6 ml-auto">
                    <table class="table table-clear table-sm">
                        <tbody>
                            <tr>
                                <td style="font-size: 12px; font-weight:600">
                                    Sub Total
                                </td>
                                <td>
                                    <b class="float-right" style="font-size: 11px;">{{number_format($header->sub_total,2)}}</b>
                                </td>
                            </tr>
                            <tr>
                                <td style="font-size: 12px; font-weight:600">
                                    Tax
                                </td>
                                <td>
                                    <b class="float-right" style="font-size: 11px;">
                                        {{number_format($header->tax,2)}}
                                    </b>
                                </td>
                            </tr>
                            <tr>
                                <td style="font-size: 12px; font-weight:600">
                                    Total - {{$header->currency_name}}
                                </td>
                                <td>
                                    <b class="float-right" style="font-size: 11px;">
                                        {{number_format($header->total_amount,2)}}
                                    </b>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <hr>
            <div class="terms" style="font-size: 11px;">
                <h4 style="font-size: 13px; font-weight:800" class="mb-2">Terms of Sale</h4>
                <p><b>Prices: </b>{{$terms_array['prices']}} {{$header->currency_name}} <br>
                    <b>Service Delivery: </b>{{$header->service_delivery}} <br>
                    <b>Payments: </b>{{$header->payments}} <br>
                    <b>Quote Specification: </b>{{$header->quote_specification}} <br>
                    <b>Approved By: </b>{{getUserById($header->approved_by)->name}}</p>

                <h5 style="font-size: 12px; font-weight:800" class="mt-2">Additional Information</h5>
                <p>{{$header->additional_info}} while <img src="/images/tick.png" height="8" width="8" alt=""> are Accreditted.<br>
                    <span style="font-weight: 510;">{{$header->payment_info}} </span> </p>
                <div class="bank text-center">
                    <p>
                        Cheques made payable to <b>{{$company->name}}</b> <br>
                        <b>Bank Details: </b>Bank Name: <b>{{$bankarr['bank_name']}}</b> Account No: <b>{{$bankarr['account_no']}}</b> Swift Code: <b>{{$bankarr['swift_code']}}</b> <br>
                        Bank Code: <b>{{$bankarr['bank_code']}}</b> Branch Code: <b>{{$bankarr['branch_code']}}</b> <br>
                        <b>Mobile Remittance: </b>Mpesa Paybill: <b>{{$bankarr['paybill']}}</b> Account Name: <b>{{$bankarr['account']}}</b> <br>
                        <span style="font-size: 30px; font-weight:600">-</span> End Of Document <span style="font-size: 30px; font-weight:600">-</span>
                    </p>
                </div>
            </div>
        </div>

    </div>
</body>

</html>